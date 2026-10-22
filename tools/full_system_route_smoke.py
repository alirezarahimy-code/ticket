#!/usr/bin/env python3
"""Authenticated route/API smoke checks against a disposable synthetic database only.

This intentionally checks representative page/API handlers and one real food-order
create/cancel lifecycle. It is not a substitute for role-matrix or external-device tests.
"""
from __future__ import annotations

import argparse
import http.cookiejar
import json
import os
import re
import subprocess
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
from datetime import date
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
FATAL_MARKERS = ("Fatal error", "Parse error", "Uncaught TypeError", "Uncaught Error", "Uncaught PDOException")


class RouteSmoke:
    def __init__(self, base: str, password: str, username: str, jalali_month: str):
        self.base = base.rstrip("/") + "/"
        self.password = password
        self.username = username
        self.jalali_month = jalali_month
        self.jar = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
        self.results: list[dict] = []
        self.csrf = ""

    def request(self, label: str, path: str, method: str = "GET", data: bytes | None = None,
                headers: dict[str, str] | None = None, expected: tuple[int, ...] = (200,)) -> tuple[int, str, float]:
        url = urllib.parse.urljoin(self.base, path)
        request = urllib.request.Request(url, data=data, headers=headers or {}, method=method)
        started = time.perf_counter()
        status, body, error = 0, "", ""
        try:
            with self.opener.open(request, timeout=30) as response:
                status = int(response.status)
                body = response.read(2 * 1024 * 1024).decode("utf-8", errors="replace")
        except urllib.error.HTTPError as exc:
            status = int(exc.code)
            body = exc.read(2 * 1024 * 1024).decode("utf-8", errors="replace")
            error = "" if status in expected else f"HTTP {status}"
        except Exception as exc:  # noqa: BLE001 - record transport failures as test results
            error = f"{type(exc).__name__}: {exc}"
        latency_ms = round((time.perf_counter() - started) * 1000, 2)
        fatal = next((marker for marker in FATAL_MARKERS if marker in body), "")
        ok = not error and status in expected and not fatal
        if not ok:
            error = "; ".join(part for part in (error, f"unexpected status {status}" if status not in expected else "", f"PHP marker: {fatal}" if fatal else "") if part)
        result = {"name": label, "method": method, "path": path, "status": status,
                  "latency_ms": latency_ms, "ok": ok, "error": error}
        if status >= 400 and status in expected:
            result["expected_rejection"] = True
        self.results.append(result)
        return status, body, latency_ms

    def login(self) -> None:
        status, html, _ = self.request("login form / CSRF", "index.php?page=login")
        match = re.search(r'<input\b(?=[^>]*\bname=["\']csrf["\'])(?=[^>]*\bvalue=["\']([^"\']+)["\'])[^>]*>', html, re.I)
        if not match:
            raise RuntimeError(f"Synthetic login CSRF field not present (HTTP {status}).")
        self.csrf = match.group(1)
        payload = urllib.parse.urlencode({"csrf": self.csrf, "action": "login", "username": self.username,
                                          "password": self.password}).encode()
        status, html, _ = self.request("synthetic account login", "index.php?page=login", "POST", payload,
                                       {"Content-Type": "application/x-www-form-urlencoded"})
        if status != 200 or 'class="auth-layout"' in html or 'class="auth-form"' in html:
            raise RuntimeError(f"Synthetic account authentication failed (HTTP {status}).")
        match = re.search(r'<input\b(?=[^>]*\bname=["\']csrf["\'])(?=[^>]*\bvalue=["\']([^"\']+)["\'])[^>]*>', html, re.I)
        if match:
            self.csrf = match.group(1)

    def pages(self) -> None:
        # IDs 1 are seeded in the disposable database by benchmark_seed.php.
        pages = [
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
        for label, query in pages:
            self.request(f"page: {label}", "index.php?page=" + query)

    def get_json(self, label: str, path: str, expected: tuple[int, ...] = (200,)) -> dict | list | None:
        _, body, _ = self.request(label, path, expected=expected)
        try:
            payload = json.loads(body)
            if isinstance(payload, dict) and "error" in payload:
                # Expected 4xx authorization is distinct from an unexpected HTTP-200 business failure.
                self.results[-1]["application_error"] = str(payload.get("error", ""))[:300]
                if self.results[-1]["status"] in expected and self.results[-1]["status"] != 200:
                    self.results[-1]["expected_rejection"] = True
                elif self.results[-1]["ok"]:
                    self.results[-1]["ok"] = False
                    self.results[-1]["error"] = "HTTP 200 with application error payload"
            return payload
        except json.JSONDecodeError:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = (self.results[-1]["error"] + "; " if self.results[-1]["error"] else "") + "non-JSON response"
            return None

    def apis(self) -> None:
        month = urllib.parse.quote(self.jalali_month)
        food_ticket = ["health", "session", "config", "monitoring", "employees", "guest-cards",
                       "guest-cap", "food-groups", "orders"]
        for route in food_ticket:
            self.get_json(f"food-ticket API GET {route}", f"index.php?page=food-ticket&food_api={urllib.parse.quote(route)}")
        today = date.today().isoformat()
        food_ticket_menu = [
            ("food-menu/status", ""), ("food-menu/month", f"month={month}"),
            ("food-menu/catalog", ""), ("food-menu/orders", f"date={today}"),
            ("food-menu/cancel-requests", ""), ("food-menu/groups", ""),
            ("food-menu/statistics", f"from={today}&to={today}"),
        ]
        for route, query in food_ticket_menu:
            suffix = "&" + query if query else ""
            self.get_json(f"food-ticket API GET {route}", f"index.php?page=food-ticket&food_api={urllib.parse.quote(route, safe='/-')}{suffix}")
        food_order = [
            ("month", f"month={month}"), ("my-orders", ""),
            ("search-users", "q=loadtest"), ("day-visit", "date=" + date.today().isoformat()),
        ]
        for route, query in food_order:
            suffix = "&" + query if query else ""
            self.get_json(f"food-order API GET {route}", f"index.php?page=food-order&food_api={urllib.parse.quote(route, safe='/-')}{suffix}")
        self.get_json("food-order guest requests correctly rejects a non-deputy synthetic admin",
                      "index.php?page=food-order&food_api=guest-requests", expected=(403,))
        self.get_json("traffic API invalid national code is rejected safely",
                      "index.php?page=traffic-control&traffic_api=lookup&code=000")

    def food_ticket_group_lifecycle(self) -> None:
        l_uid = "BENCHSMOKE-" + os.urandom(5).hex().upper()
        headers = {"Content-Type": "application/json", "X-CSRF-Token": self.csrf}
        payload = json.dumps({"title": "Synthetic route smoke group", "l_uid": l_uid,
                              "description": "Disposable CI fixture", "status": "active"}).encode()
        _, body, _ = self.request("food-ticket synthetic POST create group", "index.php?page=food-ticket&food_api=food-groups",
                                  "POST", payload, headers)
        try:
            created = json.loads(body)
        except json.JSONDecodeError:
            created = {}
        groups = created.get("groups", []) if isinstance(created, dict) else []
        group = next((row for row in groups if isinstance(row, dict) and row.get("l_uid") == l_uid), None)
        group_id = int(group.get("id", 0)) if group else 0
        if not isinstance(created, dict) or not created.get("ok") or not group_id:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "group creation handler did not return the synthetic group"
            return

        absence = self.get_json("food-ticket absence-status for synthetic group",
                                f"index.php?page=food-ticket&food_api=absence-status&group_id={group_id}&date={date.today().isoformat()}")
        if not isinstance(absence, dict) or int(absence.get("group_id", 0)) != group_id:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "absence-status did not return the selected synthetic group"

        delete_body = json.dumps({"action": "delete", "id": group_id}).encode()
        _, body, _ = self.request("food-ticket synthetic POST delete group", "index.php?page=food-ticket&food_api=food-groups",
                                  "POST", delete_body, headers)
        try:
            deleted = json.loads(body)
        except json.JSONDecodeError:
            deleted = {}
        remaining = deleted.get("groups", []) if isinstance(deleted, dict) else []
        deleted_ok = isinstance(deleted, dict) and bool(deleted.get("ok")) and not any(isinstance(row, dict) and int(row.get("id", 0)) == group_id for row in remaining)
        if not deleted_ok:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "synthetic group deletion was not confirmed by the handler response"

    def requester_permission_checks(self) -> None:
        requester = RouteSmoke(self.base, self.password, "loadtest-requester", self.jalali_month)
        try:
            requester.login()
            requester.request("requester: ticket creation is allowed", "index.php?page=new-ticket")
            requester.request("requester: settings administration denied", "index.php?page=settings", expected=(403,))
            requester.request("requester: food-ticket administration denied", "index.php?page=food-ticket", expected=(403,))
            self.results.extend(requester.results)
        except Exception as exc:  # noqa: BLE001 - keep role-test failure visible in the report
            self.results.extend(requester.results)
            self.results.append({"name": "requester role login/permission matrix", "method": "GET", "path": "synthetic role fixture",
                                 "status": 0, "latency_ms": 0, "ok": False, "error": f"{type(exc).__name__}: {exc}"})

    def food_order_lifecycle(self) -> None:
        month_data = self.get_json("food-order calendar used for write test",
                                   f"index.php?page=food-order&food_api=month&month={urllib.parse.quote(self.jalali_month)}")
        if not isinstance(month_data, dict) or not isinstance(month_data.get("days"), dict):
            self.results.append({"name": "food-order create/cancel lifecycle", "method": "POST", "path": "synthetic fixture",
                                 "status": 0, "latency_ms": 0, "ok": False, "error": "calendar API did not return day fixtures"})
            return
        today = str(month_data.get("today", date.today().isoformat()))
        candidates = [day for day in month_data["days"].values()
                      if isinstance(day, dict) and str(day.get("date", "")) >= today
                      and day.get("order_status") == "open" and day.get("items")]
        if not candidates:
            self.results.append({"name": "food-order create/cancel lifecycle", "method": "POST", "path": "synthetic fixture",
                                 "status": 0, "latency_ms": 0, "ok": False, "error": "no future open synthetic calendar item"})
            return
        target = candidates[0]
        item = target["items"][0]
        payload = json.dumps({"employee_id": 1, "food_date": target["date"],
                              "calendar_item_id": int(item["item_id"]), "meal_type": "lunch"}).encode()
        headers = {"Content-Type": "application/json", "X-CSRF-Token": self.csrf}
        _, body, _ = self.request("food-order synthetic POST create", "index.php?page=food-order&food_api=order",
                                  "POST", payload, headers)
        try:
            created = json.loads(body)
        except json.JSONDecodeError:
            created = {}
        order_id = int(created.get("order_id", 0)) if isinstance(created, dict) else 0
        if not order_id or not created.get("ok"):
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "synthetic order create did not return a successful order_id"
            return
        payload = json.dumps({"order_id": order_id}).encode()
        _, body, _ = self.request("food-order synthetic POST cancel", "index.php?page=food-order&food_api=cancel",
                                  "POST", payload, headers)
        cancel_http_ok = bool(self.results[-1]["ok"])
        try:
            cancelled = json.loads(body)
        except json.JSONDecodeError:
            cancelled = {}
        if not isinstance(cancelled, dict) or not cancelled.get("ok"):
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "synthetic order cancellation did not confirm success"
            return
        date_query = urllib.parse.urlencode({"from": target["date"], "to": target["date"]})
        persisted = self.get_json("food-order cancelled state read-back", f"index.php?page=food-order&food_api=my-orders&{date_query}")
        items = persisted.get("items", []) if isinstance(persisted, dict) else []
        state_ok = any(isinstance(row, dict) and int(row.get("id", 0)) == order_id and row.get("status") == "cancelled" for row in items)
        self.results.append({"name": "food-order cancelled state persisted in API/DB read-back", "method": "GET",
                             "path": "index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>",
                             "status": 200 if cancel_http_ok else 0, "latency_ms": 0, "ok": state_ok,
                             "error": "cancelled order row was not present in read-back" if not state_ok else ""})


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="http://127.0.0.1:8081/")
    parser.add_argument("--password", default=os.environ.get("BENCHMARK_PASSWORD", ""))
    parser.add_argument("--username", default="loadtest-001")
    parser.add_argument("--jalali-month", default=os.environ.get("BENCHMARK_JALALI_MONTH", ""))
    parser.add_argument("--output", default="benchmark-results/full-system-route-smoke.json")
    parser.add_argument("--markdown", default="benchmark-results/full-system-route-smoke.md")
    parser.add_argument("--skip-start-server", action="store_true")
    args = parser.parse_args()
    if len(args.password) < 16 or not args.jalali_month:
        parser.error("BENCHMARK_PASSWORD and BENCHMARK_JALALI_MONTH must come from the synthetic seed step")

    server = None
    server_log_path = Path("/tmp/ticket-full-system-routes.log")
    server_log = server_log_path.open("w", encoding="utf-8")
    if not args.skip_start_server:
        env = os.environ.copy()
        env["PHP_CLI_SERVER_WORKERS"] = "8"
        server = subprocess.Popen(["php", "-S", "127.0.0.1:8081", "-t", str(ROOT)], cwd=ROOT,
                                  env=env, stdout=server_log, stderr=subprocess.STDOUT, start_new_session=True)
        deadline = time.monotonic() + 60
        while time.monotonic() < deadline:
            if server.poll() is not None:
                raise RuntimeError(f"PHP smoke server exited; see {server_log_path}")
            try:
                with urllib.request.urlopen(args.base_url + "index.php?page=login", timeout=2) as response:
                    if response.status == 200:
                        break
            except Exception:
                time.sleep(0.25)
        else:
            raise RuntimeError(f"PHP smoke server did not start within 60s; see {server_log_path}")

    smoke = RouteSmoke(args.base_url, args.password, args.username, args.jalali_month)
    started = time.perf_counter()
    fatal: str | None = None
    try:
        smoke.login()
        smoke.pages()
        smoke.apis()
        smoke.food_ticket_group_lifecycle()
        smoke.food_order_lifecycle()
        smoke.requester_permission_checks()
    except Exception as exc:  # noqa: BLE001 - retain diagnostics in JSON/markdown report
        fatal = f"{type(exc).__name__}: {exc}"
    finally:
        if server is not None:
            server.terminate()
            try:
                server.wait(timeout=10)
            except subprocess.TimeoutExpired:
                server.kill()
        server_log.close()

    server_log_text = server_log_path.read_text(encoding="utf-8", errors="replace") if server_log_path.exists() else ""
    php_runtime_lines = [line for line in server_log_text.splitlines() if re.search(r"PHP (?:Fatal error|Parse error|Warning|Notice|Deprecated)|Uncaught (?:Error|Exception|TypeError)", line, re.I)]
    database_error_lines = [line for line in server_log_text.splitlines() if re.search(r"SQLSTATE|PDOException|MySQL server has gone away|deadlock found", line, re.I)]
    critical_php_lines = [line for line in php_runtime_lines if re.search(r"PHP (?:Fatal error|Parse error)|Uncaught (?:Error|Exception|TypeError)", line, re.I)]
    for line in critical_php_lines:
        smoke.results.append({"name": "PHP runtime log critical error", "method": "RUNTIME", "path": "PHP server log",
                              "status": 0, "latency_ms": 0, "ok": False, "error": line[:500]})
    failed = [row for row in smoke.results if not row["ok"]]
    report = {
        "scope": "authenticated representative route/API smoke on disposable synthetic MariaDB only",
        "synthetic_only": True,
        "account": args.username,
        "pages_tested": sum(str(row["name"]).startswith("page:") for row in smoke.results),
        "api_reads_tested": sum(row["method"] == "GET" and ("food_api=" in row["path"] or "traffic_api=" in row["path"]) for row in smoke.results),
        "role_checks_tested": sum(str(row["name"]).startswith("requester:") for row in smoke.results),
        "expected_authorization_rejections": sum(bool(row.get("expected_rejection")) for row in smoke.results),
        "http_error_responses": sum(int(row["status"]) >= 400 for row in smoke.results),
        "unexpected_http_error_responses": sum(int(row["status"]) >= 400 and not row.get("expected_rejection") for row in smoke.results),
        "application_error_payloads": sum(bool(row.get("application_error")) for row in smoke.results),
        "food_ticket_group_write_lifecycle_tested": all(any(row["name"] == name and row["ok"] for row in smoke.results) for name in ("food-ticket synthetic POST create group", "food-ticket absence-status for synthetic group", "food-ticket synthetic POST delete group")),
        "php_runtime_log_error_lines": len(php_runtime_lines),
        "database_error_log_lines": len(database_error_lines),
        "php_runtime_log_samples": php_runtime_lines[:20],
        "database_error_log_samples": database_error_lines[:20],
        "food_order_write_lifecycle_tested": any(row["name"] == "food-order synthetic POST create" and row["ok"] for row in smoke.results)
                                                  and any(row["name"] == "food-order synthetic POST cancel" and row["ok"] for row in smoke.results),
        "total_checks": len(smoke.results), "passed": len(smoke.results) - len(failed), "failed": len(failed),
        "elapsed_seconds": round(time.perf_counter() - started, 3),
        "p50_latency_ms": sorted(row["latency_ms"] for row in smoke.results)[len(smoke.results) // 2] if smoke.results else None,
        "max_latency_ms": max((row["latency_ms"] for row in smoke.results), default=None),
        "fatal": fatal, "results": smoke.results,
        "limitations": ["No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.",
                        "Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.",
                        "Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.",
                        "No production endpoint or operational account was used."],
        "server_log": str(server_log_path),
    }
    Path(args.output).parent.mkdir(parents=True, exist_ok=True)
    Path(args.output).write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    lines = ["# Full-system route/API smoke (isolated synthetic data)", "",
             f"- Scope: {report['scope']}", f"- Account: `{args.username}` (synthetic)",
             f"- Pages: {report['pages_tested']}; API reads: {report['api_reads_tested']}; requester permission checks: {report['role_checks_tested']}; total checks: {report['total_checks']}",
             f"- HTTP errors: {report['http_error_responses']} (unexpected: {report['unexpected_http_error_responses']}; expected authorization rejections: {report['expected_authorization_rejections']}); JSON error payloads: {report['application_error_payloads']}; PHP runtime log lines: {report['php_runtime_log_error_lines']}; DB/SQL log lines: {report['database_error_log_lines']}",
             f"- Passed: {report['passed']}; failed: {report['failed']}",
             f"- Food-order create/cancel/read-back: {'passed' if report['food_order_write_lifecycle_tested'] else 'not passed'}; food-ticket group create/status/delete: {'passed' if report['food_ticket_group_write_lifecycle_tested'] else 'not passed'}",
             f"- Expected access rejections: {report['expected_authorization_rejections']}",
             f"- Elapsed: {report['elapsed_seconds']} s; median latency: {report['p50_latency_ms']} ms; max latency: {report['max_latency_ms']} ms", "",
             "| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |", "|---|---|---|---:|---:|---|"]
    for row in smoke.results:
        lines.append(f"| {'PASS' if row['ok'] else 'FAIL'} | {row['method']} | `{row['path']}` | {row['status']} | {row['latency_ms']} | {row.get('error', '')} |")
    lines += ["", "## Explicit limitations", *[f"- {item}" for item in report["limitations"]], ""]
    Path(args.markdown).write_text("\n".join(lines), encoding="utf-8")
    print(f"Full-system route smoke: {report['passed']}/{report['total_checks']} passed; {report['failed']} failed; report={args.markdown}")
    if fatal:
        print(f"Fatal test setup/runtime error: {fatal}", file=sys.stderr)
        return 2
    return 1 if failed else 0


if __name__ == "__main__":
    raise SystemExit(main())
