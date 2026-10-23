#!/usr/bin/env python3
"""Heavy authenticated read/API resilience test for a disposable synthetic system.

250 independent synthetic admin sessions log in concurrently. Each session then
hits a deterministic slice of every registered page and read API. The loopback-only
server and the seeded manifest guard keep this test away from production/staging.
"""
from __future__ import annotations

import argparse
import concurrent.futures
import json
import os
import signal
import subprocess
import sys
import threading
import time
import urllib.parse
from collections import Counter, defaultdict
from datetime import date, datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "tools"))
from full_system_route_smoke import FATAL_MARKERS, RouteSmoke  # noqa: E402


PAGES = [
    ("dashboard", "dashboard"), ("profile", "profile"), ("queue", "queue"),
    ("ticket detail", "ticket&id=1"), ("new ticket", "new-ticket"),
    ("local accounts", "local-accounts"), ("settings", "settings"),
    ("reports", "reports"), ("analytics", "analytics"), ("supervisor", "supervisor"),
    ("inventory form", "inventory"), ("inventory diagnostics", "inventory-diagnostics"),
    ("domain scan page", "domain-scan"), ("asset list", "assets"),
    ("asset detail", "asset&id=1"), ("asset history", "asset-history&id=1"),
    ("CD/DVD", "cd-dvd"), ("CD/DVD history", "cd-dvd-history"),
    ("traffic control", "traffic-control"), ("governance", "governance"),
    ("organization", "organization"), ("holidays", "holidays"),
    ("knowledge", "knowledge"), ("notifications", "notifications"),
    ("search", "search&q=synthetic"), ("backup page (read-only)", "backup"),
    ("chatbot administration", "chatbot-admin"), ("services", "services"),
    ("system health", "system-health"), ("logs", "logs"), ("activity log", "activity-log"),
    ("help", "help"), ("food ticket", "food-ticket"), ("food order", "food-order"),
    ("food personnel link", "food-personnel-link"), ("change password", "change-password"),
    ("audit", "audit"),
]


def api_routes(jalali_month: str) -> list[tuple[str, str, int]]:
    today = date.today().isoformat()
    routes: list[tuple[str, str, int]] = []
    for route in ("health", "session", "config", "monitoring", "employees", "guest-cards",
                  "guest-cap", "food-groups", "orders"):
        routes.append((f"food-ticket:{route}", f"index.php?page=food-ticket&food_api={urllib.parse.quote(route)}", 200))
    menu = [
        ("food-menu/status", ""), ("food-menu/month", "month=" + urllib.parse.quote(jalali_month)),
        ("food-menu/catalog", ""), ("food-menu/orders", "date=" + today),
        ("food-menu/cancel-requests", ""), ("food-menu/groups", ""),
        ("food-menu/statistics", f"from={today}&to={today}"),
    ]
    for route, query in menu:
        suffix = "&" + query if query else ""
        encoded_route = urllib.parse.quote(route, safe="/-")
        routes.append((f"food-ticket:{route}", f"index.php?page=food-ticket&food_api={encoded_route}{suffix}", 200))
    for route, query in (("month", "month=" + urllib.parse.quote(jalali_month)), ("my-orders", ""),
                         ("search-users", "q=loadtest"), ("day-visit", "date=" + today)):
        suffix = "&" + query if query else ""
        routes.append((f"food-order:{route}", f"index.php?page=food-order&food_api={route}{suffix}", 200))
    routes.append(("food-order:guest-requests (expected deny)", "index.php?page=food-order&food_api=guest-requests", 403))
    routes.append(("traffic:invalid-code lookup", "index.php?page=traffic-control&traffic_api=lookup&code=000", 200))
    return routes


class ResourceSampler:
    def __init__(self) -> None:
        self.stop = threading.Event()
        self.cpu_peak = 0.0
        self.rss_peak_mb = 0.0

    def run(self) -> None:
        while not self.stop.wait(1):
            try:
                ps = subprocess.run(["ps", "-C", "php", "-o", "%cpu=,rss="], capture_output=True,
                                    text=True, timeout=3, check=False)
                cpu = rss_kb = 0.0
                for line in ps.stdout.splitlines():
                    fields = line.split()
                    if len(fields) == 2:
                        cpu += float(fields[0])
                        rss_kb += float(fields[1])
                self.cpu_peak = max(self.cpu_peak, cpu)
                self.rss_peak_mb = max(self.rss_peak_mb, rss_kb / 1024)
            except (OSError, ValueError, subprocess.SubprocessError):
                continue


def percentile(values: list[float], pct: float) -> float:
    if not values:
        return 0.0
    values = sorted(values)
    return round(values[min(len(values) - 1, int(round((pct / 100) * (len(values) - 1))))], 2)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="http://127.0.0.1:8082/")
    parser.add_argument("--password", default=os.environ.get("BENCHMARK_PASSWORD", ""))
    parser.add_argument("--jalali-month", default=os.environ.get("BENCHMARK_JALALI_MONTH", ""))
    parser.add_argument("--seed", default="benchmark-results/operation-seed.json")
    parser.add_argument("--output-dir", default="benchmark-results")
    parser.add_argument("--vus", type=int, default=250)
    parser.add_argument("--reads-per-user", type=int, default=12)
    parser.add_argument("--php-workers", type=int, default=32)
    args = parser.parse_args()

    parsed = urllib.parse.urlparse(args.base_url)
    if parsed.scheme != "http" or parsed.hostname not in {"127.0.0.1", "localhost"}:
        parser.error("resilience test is restricted to an HTTP loopback target")
    if args.vus != 250 or not 1 <= args.reads_per_user <= 24 or args.php_workers != 32:
        parser.error("use the fixed synthetic profile: 250 VUs, 32 PHP workers, 1..24 reads per user")
    if len(args.password) < 16 or not args.jalali_month:
        parser.error("BENCHMARK_PASSWORD and BENCHMARK_JALALI_MONTH are required from the synthetic seed")
    try:
        seed = json.loads(Path(args.seed).read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as exc:
        parser.error(f"synthetic seed manifest unavailable: {exc}")
    if (not seed.get("synthetic_only") or seed.get("account_count") != 250
            or len(seed.get("user_ids", [])) != 250):
        parser.error("refusing to run without the manifest for exactly 250 synthetic-only accounts")

    routes: list[tuple[str, str, int, str]] = []
    routes.extend((f"page:{label}", "index.php?page=" + query, 200, "html") for label, query in PAGES)
    routes.extend((label, path, status, "json") for label, path, status in api_routes(args.jalali_month))
    if len(routes) < 50:
        parser.error("route catalog unexpectedly incomplete")

    started_utc = datetime.now(timezone.utc)
    server_log_path = Path("/tmp/ticket-system-resilience.log")
    server_log = server_log_path.open("w", encoding="utf-8")
    env = os.environ.copy()
    env["PHP_CLI_SERVER_WORKERS"] = str(args.php_workers)
    server = subprocess.Popen(["php", "-S", f"127.0.0.1:{parsed.port or 8082}", "-t", str(ROOT)], cwd=ROOT,
                              env=env, stdout=server_log, stderr=subprocess.STDOUT, start_new_session=True)
    try:
        deadline = time.monotonic() + 60
        while time.monotonic() < deadline:
            if server.poll() is not None:
                raise RuntimeError(f"loopback PHP server exited; inspect {server_log_path}")
            try:
                import urllib.request
                with urllib.request.urlopen(args.base_url + "index.php?page=login", timeout=2) as response:
                    if response.status == 200:
                        break
            except Exception:
                time.sleep(0.25)
        else:
            raise RuntimeError(f"loopback PHP server did not start within 60s; inspect {server_log_path}")

        sampler = ResourceSampler()
        sampler_thread = threading.Thread(target=sampler.run, daemon=True)
        sampler_thread.start()
        barrier = threading.Barrier(args.vus, timeout=180)
        result_lock = threading.Lock()
        all_results: list[dict] = []
        worker_errors: list[str] = []
        endpoint_hits: Counter[str] = Counter()
        started = time.perf_counter()

        def worker(vu: int) -> None:
            client = RouteSmoke(args.base_url, args.password, f"loadtest-{vu + 1:03d}", args.jalali_month)
            try:
                client.login()
                if any(not row["ok"] for row in client.results):
                    raise RuntimeError("synthetic login or CSRF check failed")
                barrier.wait()
                first = (vu * args.reads_per_user) % len(routes)
                for offset in range(args.reads_per_user):
                    label, path, expected_status, kind = routes[(first + offset) % len(routes)]
                    status, body, latency = client.request(
                        f"resilience {kind}: {label}", path, expected=(expected_status,)
                    )
                    row = client.results[-1]
                    if status != expected_status:
                        row["ok"] = False
                    if kind == "html":
                        if any(marker in body for marker in FATAL_MARKERS):
                            row["ok"] = False
                            row["error"] = "PHP fatal/runtime marker in HTML response"
                        if 'class="auth-layout"' in body or 'class="auth-form"' in body:
                            row["ok"] = False
                            row["error"] = "authenticated session unexpectedly returned login page"
                    else:
                        try:
                            payload = json.loads(body)
                            if expected_status == 403:
                                if not isinstance(payload, dict) or not payload.get("error"):
                                    row["ok"] = False
                                    row["error"] = "expected authorization rejection lacked JSON error payload"
                                else:
                                    row["expected_rejection"] = True
                            elif isinstance(payload, dict) and "error" in payload:
                                row["ok"] = False
                                row["error"] = "unexpected API application error: " + str(payload.get("error", ""))[:220]
                        except json.JSONDecodeError:
                            row["ok"] = False
                            row["error"] = "API response was not valid JSON"
                    row["endpoint"] = label
                    row["latency_ms"] = latency
                    endpoint_hits[label] += 1
            except Exception as exc:  # noqa: BLE001 - collect every VU failure for the report
                with result_lock:
                    worker_errors.append(f"vu={vu + 1}: {type(exc).__name__}: {exc}")
                try:
                    barrier.abort()
                except Exception:
                    pass
            finally:
                with result_lock:
                    all_results.extend(client.results)

        with concurrent.futures.ThreadPoolExecutor(max_workers=args.vus) as pool:
            futures = [pool.submit(worker, vu) for vu in range(args.vus)]
            for future in concurrent.futures.as_completed(futures):
                future.result()
        elapsed = round(time.perf_counter() - started, 3)
        sampler.stop.set()
        sampler_thread.join(timeout=5)
    finally:
        try:
            os.killpg(server.pid, signal.SIGTERM)
        except (ProcessLookupError, PermissionError):
            pass
        try:
            server.wait(timeout=10)
        except subprocess.TimeoutExpired:
            server.kill()
            server.wait(timeout=5)
        server_log.close()

    log = server_log_path.read_text(encoding="utf-8", errors="replace") if server_log_path.exists() else ""
    php_log_lines = [line for line in log.splitlines() if any(marker.lower() in line.lower() for marker in
                      ("PHP Fatal error", "PHP Parse error", "PHP Warning", "PHP Notice", "PHP Deprecated", "Uncaught Error", "Uncaught TypeError", "Uncaught PDOException"))]
    db_log_lines = [line for line in log.splitlines() if any(marker.lower() in line.lower() for marker in
                     ("SQLSTATE", "PDOException", "MySQL server has gone away", "deadlock found"))]
    read_results = [row for row in all_results if str(row.get("name", "")).startswith("resilience ")]
    failures = [row for row in all_results if not row.get("ok", False)]
    latency_by_endpoint: dict[str, list[float]] = defaultdict(list)
    status_counts: Counter[str] = Counter()
    login_results = [row for row in all_results if str(row.get("name", "")).startswith(("login form", "synthetic account login"))]
    for row in read_results:
        latency_by_endpoint[str(row.get("endpoint", "unknown"))].append(float(row.get("latency_ms", 0)))
        status_counts[str(row.get("status", 0))] += 1
    login_latencies = [float(row.get("latency_ms", 0)) for row in login_results]
    all_request_results = [*login_results, *read_results]
    all_status_counts = Counter(str(row.get("status", 0)) for row in all_request_results)
    endpoint_summary = []
    for label, _path, expected_status, _kind in routes:
        values = latency_by_endpoint.get(label, [])
        endpoint_summary.append({
            "route": label, "expected_status": expected_status, "requests": len(values),
            "p50_ms": percentile(values, 50), "p95_ms": percentile(values, 95),
            "p99_ms": percentile(values, 99), "max_ms": round(max(values), 2) if values else 0,
        })
    expected_reads = args.vus * args.reads_per_user
    missing_routes = [row["route"] for row in endpoint_summary if row["requests"] == 0]
    errors = [*worker_errors, *(str(row.get("error", "unknown failure")) for row in failures)]
    passed = not errors and len(read_results) == expected_reads and not missing_routes and not php_log_lines and not db_log_lines
    latencies = [float(row.get("latency_ms", 0)) for row in read_results]
    report = {
        "started_utc": started_utc.isoformat(),
        "target": "127.0.0.1 only; isolated ephemeral MariaDB; synthetic accounts and fixtures",
        "synthetic_only": True, "production_or_staging_contacted": False,
        "virtual_users": args.vus, "php_workers": args.php_workers,
        "reads_per_user": args.reads_per_user, "read_requests_expected": expected_reads,
        "read_requests_completed": len(read_results), "route_catalog_size": len(routes),
        "routes_with_requests": len(routes) - len(missing_routes), "missing_routes": missing_routes,
        "login_requests": len(login_results), "total_http_requests": len(all_request_results),
        "login_latency_ms": {"p50": percentile(login_latencies, 50), "p95": percentile(login_latencies, 95),
                             "p99": percentile(login_latencies, 99), "max": round(max(login_latencies), 2) if login_latencies else 0},
        "http_status_counts": dict(all_status_counts), "read_http_status_counts": dict(status_counts),
        "expected_authorization_rejections": sum(bool(row.get("expected_rejection")) for row in read_results),
        "unexpected_http_errors": sum(int(row.get("status", 0)) >= 400 and not row.get("expected_rejection") for row in all_request_results),
        "api_payload_errors": sum("API" in str(row.get("error", "")) or "JSON" in str(row.get("error", "")) for row in failures),
        "php_runtime_warning_notice_fatal_lines": len(php_log_lines), "php_runtime_log_samples": php_log_lines[:30],
        "database_sql_error_lines": len(db_log_lines), "database_log_samples": db_log_lines[:30],
        "worker_failures": worker_errors[:100], "request_failures": failures[:100],
        "elapsed_seconds": elapsed, "throughput_requests_per_second": round(len(read_results) / elapsed, 2) if elapsed else 0,
        "latency_ms": {"p50": percentile(latencies, 50), "p95": percentile(latencies, 95),
                       "p99": percentile(latencies, 99), "max": round(max(latencies), 2) if latencies else 0},
        "php_cpu_percent_sum_peak": round(sampler.cpu_peak, 2), "php_rss_mb_sum_peak": round(sampler.rss_peak_mb, 2),
        "passed": passed, "endpoint_metrics": endpoint_summary,
        "limitations": [
            "All 250 VUs are synthetic primary-admin accounts; this is not a complete role/permission matrix.",
            "The 3,000-request phase is read-only apart from synthetic login sessions; write stress is separately covered by the ticket/CD-DVD and food lifecycle tests.",
            "LDAP/AD, Access/ODBC, printers, physical media, hardware collection, external providers, and production/staging capacity are not exercised.",
        ],
    }
    output = Path(args.output_dir)
    output.mkdir(parents=True, exist_ok=True)
    json_path = output / "system-resilience.json"
    md_path = output / "system-resilience.md"
    json_path.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    lines = [
        "# سامانهٔ آزمون تاب‌آوری مسیرها و APIها (محیط مصنوعی)", "",
        f"- مقصد: `{report['target']}`", f"- کاربر هم‌زمان: {args.vus}; worker PHP: {args.php_workers}",
        f"- مسیرهای ثبت‌شده: {len(routes)}؛ مسیرهای دارای ترافیک: {report['routes_with_requests']}; درخواست‌های خواندنی مورد انتظار/انجام‌شده: {expected_reads}/{len(read_results)}؛ کل HTTP با ورود: {report['total_http_requests']}",
        f"- وضعیت: {'PASS' if passed else 'FAIL'}; خطاهای درخواست: {len(failures)}; شکست worker: {len(worker_errors)}; مسیر بی‌ترافیک: {len(missing_routes)}",
        f"- HTTP error غیرمنتظره: {report['unexpected_http_errors']}; رد دسترسی مورد انتظار: {report['expected_authorization_rejections']}; خطای payload/API: {report['api_payload_errors']}",
        f"- ورود: p95={report['login_latency_ms']['p95']} ms؛ خواندن مسیرها/API: p50/p95/p99/max={report['latency_ms']['p50']}/{report['latency_ms']['p95']}/{report['latency_ms']['p99']}/{report['latency_ms']['max']} ms",
        f"- PHP runtime warning/notice/fatal log: {len(php_log_lines)}; SQL/DB log: {len(db_log_lines)}",
        f"- زمان: {elapsed}s; throughput: {report['throughput_requests_per_second']} req/s; latency p50/p95/p99/max: {report['latency_ms']['p50']}/{report['latency_ms']['p95']}/{report['latency_ms']['p99']}/{report['latency_ms']['max']} ms",
        f"- اوج مجموع CPU پردازه‌های PHP: {report['php_cpu_percent_sum_peak']}%; RSS: {report['php_rss_mb_sum_peak']} MB", "",
        "| مسیر | تعداد | HTTP مورد انتظار | p50 ms | p95 ms | p99 ms | بیشینه ms |",
        "|---|---:|---:|---:|---:|---:|---:|",
    ]
    for row in endpoint_summary:
        lines.append(f"| {row['route']} | {row['requests']} | {row['expected_status']} | {row['p50_ms']} | {row['p95_ms']} | {row['p99_ms']} | {row['max_ms']} |")
    lines += ["", "## محدودیت‌ها", *[f"- {item}" for item in report["limitations"]], ""]
    md_path.write_text("\n".join(lines), encoding="utf-8")
    print(f"System resilience: {len(read_results)}/{expected_reads} reads across {len(routes)} routes; failures={len(failures) + len(worker_errors)}; PHP logs={len(php_log_lines)}; DB logs={len(db_log_lines)}")
    print(f"Latency p95={report['latency_ms']['p95']}ms; throughput={report['throughput_requests_per_second']} req/s; report={md_path}")
    return 0 if passed else 1


if __name__ == "__main__":
    raise SystemExit(main())
