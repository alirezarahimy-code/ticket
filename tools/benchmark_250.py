#!/usr/bin/env python3
"""Isolated, authenticated 250-VU benchmark for CI. Uses only local synthetic data."""
from __future__ import annotations

import argparse
import concurrent.futures
import http.cookiejar
import json
import os
import random
import re
import signal
import statistics
import subprocess
import threading
import time
import urllib.error
import urllib.parse
import urllib.request
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def percentile(values: list[float], p: float) -> float:
    if not values:
        return 0.0
    ordered = sorted(values)
    idx = min(len(ordered) - 1, max(0, int(round((p / 100) * (len(ordered) - 1)))))
    return ordered[idx]


class Metrics:
    def __init__(self) -> None:
        self.lock = threading.Lock()
        self.rows: dict[str, list[dict]] = defaultdict(list)
        self.failures: list[dict] = []
        self.php_cpu_peak = 0.0
        self.php_rss_peak_mb = 0.0
        self.stop_sampling = threading.Event()

    def record(self, route: str, elapsed_ms: float, status: int, error: str = '') -> None:
        row = {'elapsed_ms': round(elapsed_ms, 2), 'status': status}
        with self.lock:
            self.rows[route].append(row)
            if error or status < 200 or status >= 400:
                self.failures.append({'route': route, 'status': status, 'error': error[:240]})

    def sample_php(self) -> None:
        while not self.stop_sampling.wait(1):
            try:
                result = subprocess.run(
                    ['ps', '-C', 'php', '-o', '%cpu=,%mem=,rss='],
                    check=False, capture_output=True, text=True, timeout=2,
                )
                cpu = rss_kb = 0.0
                for line in result.stdout.splitlines():
                    parts = line.split()
                    if len(parts) >= 3:
                        cpu += float(parts[0])
                        rss_kb += float(parts[2])
                with self.lock:
                    self.php_cpu_peak = max(self.php_cpu_peak, cpu)
                    self.php_rss_peak_mb = max(self.php_rss_peak_mb, rss_kb / 1024)
            except (OSError, ValueError, subprocess.SubprocessError):
                continue


def request(opener: urllib.request.OpenerDirector, base: str, path: str,
            metrics: Metrics, label: str, data: dict[str, str] | None = None) -> tuple[int, str]:
    url = urllib.parse.urljoin(base, path)
    body = urllib.parse.urlencode(data).encode() if data is not None else None
    headers = {'User-Agent': 'Ticket-250VU-CI-Benchmark/1.0'}
    if body is not None:
        headers['Content-Type'] = 'application/x-www-form-urlencoded'
    req = urllib.request.Request(url, data=body, headers=headers)
    started = time.perf_counter()
    status, text = 0, ''
    error = ''
    try:
        with opener.open(req, timeout=30) as response:
            status = int(response.status)
            text = response.read(2 * 1024 * 1024).decode('utf-8', errors='replace')
    except urllib.error.HTTPError as exc:
        status = int(exc.code)
        try:
            text = exc.read(4096).decode('utf-8', errors='replace')
        except OSError:
            pass
        error = f'HTTP {status}'
    except Exception as exc:  # network/timeouts are recorded, never hidden
        error = f'{type(exc).__name__}: {exc}'
    metrics.record(label, (time.perf_counter() - started) * 1000, status, error)
    return status, text


def user_flow(vu: int, args: argparse.Namespace, metrics: Metrics,
              test_start: float, test_end: float, month: str) -> None:
    scheduled = test_start + args.ramp_up * (vu / max(1, args.vus - 1))
    time.sleep(max(0.0, scheduled - time.monotonic()))
    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    login_path = 'index.php?page=login'
    status, html = request(opener, args.base_url, login_path, metrics, 'login-form')
    if status != 200:
        metrics.record('login', 0, status, 'login form unavailable')
        return
    match = re.search(r'name=["\']csrf["\']\s+value=["\']([^"\']+)', html, re.I)
    if not match:
        match = re.search(r'value=["\']([^"\']+)["\']\s+name=["\']csrf["\']', html, re.I)
    if not match:
        metrics.record('login', 0, 0, 'CSRF field not found on login page')
        return
    status, post_response = request(
        opener, args.base_url, login_path, metrics, 'login',
        {'csrf': match.group(1), 'action': 'login', 'username': f'loadtest-{vu + 1:03d}', 'password': args.password},
    )
    if status != 200 or 'name="username"' in post_response or "name='username'" in post_response:
        metrics.record('authenticated-session', 0, status, 'login did not produce an authenticated page')
        return

    # Read-only route/API mix. The only writes are the normal login bookkeeping on this disposable DB.
    routes = [
        ('dashboard', 'index.php?page=dashboard'),
        ('new-ticket-form', 'index.php?page=new-ticket'),
        ('asset-list', 'index.php?page=assets'),
        ('inventory', 'index.php?page=inventory'),
        ('food-admin-page', 'index.php?page=food-ticket'),
        ('food-admin-config-api', 'index.php?page=food-ticket&food_api=config'),
        ('food-order-page', 'index.php?page=food-order'),
        ('food-order-month-api', f'index.php?page=food-order&food_api=month&month={month}'),
        ('traffic-control', 'index.php?page=traffic-control'),
        ('cd-dvd', 'index.php?page=cd-dvd'),
        ('reports', 'index.php?page=reports'),
        ('notification-feed', 'index.php?action=notification_feed'),
    ]
    while time.monotonic() < test_end:
        label, path = random.choice(routes)
        status, response_body = request(opener, args.base_url, path, metrics, label)
        if status != 200:
            metrics.record(label + '-validation', 0, status, 'expected HTTP 200')
        elif label == 'notification-feed' and not response_body.strip().startswith('{'):
            metrics.record(label + '-validation', 0, status, 'expected JSON response')
        elif 'name="username"' in response_body or "name='username'" in response_body:
            metrics.record(label + '-validation', 0, status, 'session unexpectedly returned to the login page')
        time.sleep(random.uniform(args.think_time * 0.7, args.think_time * 1.3))


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument('--base-url', default='http://127.0.0.1:8080/')
    parser.add_argument('--vus', type=int, default=250)
    parser.add_argument('--ramp-up', type=int, default=30)
    parser.add_argument('--duration', type=int, default=90, help='steady-state seconds after ramp-up')
    parser.add_argument('--think-time', type=float, default=1.0)
    parser.add_argument('--password', default=os.environ.get('BENCHMARK_PASSWORD', ''))
    parser.add_argument('--output-dir', default='benchmark-results')
    parser.add_argument('--skip-start-server', action='store_true')
    parser.add_argument('--php-workers', type=int, default=32)
    args = parser.parse_args()
    if args.vus != 250:
        parser.error('this benchmark is intentionally fixed at 250 virtual users')
    if len(args.password) < 16:
        parser.error('BENCHMARK_PASSWORD must be set for the disposable synthetic accounts')

    server = None
    server_log = open('/tmp/ticket-php-benchmark.log', 'w', encoding='utf-8')
    if not args.skip_start_server:
        env = os.environ.copy()
        env['PHP_CLI_SERVER_WORKERS'] = str(args.php_workers)
        server = subprocess.Popen(
            ['php', '-S', '127.0.0.1:8080', '-t', str(ROOT)],
            cwd=ROOT, env=env, stdout=server_log, stderr=subprocess.STDOUT,
            start_new_session=True,
        )
        deadline = time.monotonic() + 45
        while time.monotonic() < deadline:
            if server.poll() is not None:
                raise RuntimeError('PHP test server exited before becoming ready; see /tmp/ticket-php-benchmark.log')
            try:
                with urllib.request.urlopen(args.base_url + 'index.php?page=login', timeout=2) as response:
                    if response.status == 200:
                        break
            except Exception:
                time.sleep(0.5)
        else:
            raise RuntimeError('PHP test server did not become ready within 45 seconds')

    metrics = Metrics()
    sampler = threading.Thread(target=metrics.sample_php, daemon=True)
    sampler.start()
    test_start = time.monotonic() + 0.5
    test_end = test_start + args.ramp_up + args.duration
    month = datetime.now(timezone.utc).strftime('%Y-%m')
    wall_started = time.time()
    try:
        with concurrent.futures.ThreadPoolExecutor(max_workers=args.vus) as pool:
            futures = [pool.submit(user_flow, vu, args, metrics, test_start, test_end, month) for vu in range(args.vus)]
            for future in concurrent.futures.as_completed(futures):
                future.result()
    finally:
        metrics.stop_sampling.set()
        sampler.join(timeout=2)
        if server is not None:
            try:
                os.killpg(server.pid, signal.SIGTERM)
                server.wait(timeout=10)
            except (ProcessLookupError, subprocess.TimeoutExpired):
                try:
                    os.killpg(server.pid, signal.SIGKILL)
                except ProcessLookupError:
                    pass
        server_log.close()

    elapsed = max(0.001, time.time() - wall_started)
    totals = []
    for route, rows in sorted(metrics.rows.items()):
        times = [float(row['elapsed_ms']) for row in rows]
        status_counts: dict[str, int] = defaultdict(int)
        for row in rows:
            status_counts[str(row['status'])] += 1
        totals.append({
            'route': route, 'count': len(rows), 'rps': round(len(rows) / elapsed, 2),
            'p50_ms': round(percentile(times, 50), 2), 'p95_ms': round(percentile(times, 95), 2),
            'p99_ms': round(percentile(times, 99), 2), 'max_ms': round(max(times, default=0), 2),
            'statuses': dict(status_counts),
        })
    request_count = sum(len(rows) for rows in metrics.rows.values())
    report = {
        'started_utc': datetime.fromtimestamp(wall_started, timezone.utc).isoformat(),
        'environment': 'ephemeral GitHub Actions runner; isolated MariaDB; PHP CLI server with configured worker pool',
        'target': '127.0.0.1 only; no production/staging host was contacted',
        'virtual_users': args.vus, 'ramp_up_seconds': args.ramp_up, 'steady_state_seconds': args.duration,
        'think_time_seconds': args.think_time, 'php_workers': args.php_workers,
        'elapsed_wall_seconds': round(elapsed, 2), 'requests': request_count,
        'overall_rps': round(request_count / elapsed, 2), 'failed_observations': len(metrics.failures),
        'php_cpu_percent_sum_peak': round(metrics.php_cpu_peak, 2),
        'php_rss_mb_sum_peak': round(metrics.php_rss_peak_mb, 2),
        'routes': totals, 'failures_sample': metrics.failures[:100],
        'limitations': [
            'LDAP/Active Directory authentication and Windows WMI collectors were not exercised; LDAP is disabled in the isolated config.',
            'The food management page and MySQL-backed APIs were exercised, but external Microsoft Access/ODBC attendance/order sources and physical printers were not available.',
            'Results describe this ephemeral CI runner and its PHP/MariaDB setup, not production capacity or production latency.',
            'All synthetic users and generated records exist only in the disposable CI database; no real operational data was used.',
        ],
    }
    output = Path(args.output_dir)
    output.mkdir(parents=True, exist_ok=True)
    json_path = output / 'benchmark-250.json'
    md_path = output / 'benchmark-250.md'
    json_path.write_text(json.dumps(report, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    lines = [
        '# گزارش آزمون بار ۲۵۰ کاربر هم‌زمان', '',
        f"- زمان UTC: {report['started_utc']}",
        f"- محیط: {report['environment']}", f"- مقصد: {report['target']}",
        f"- کاربران مجازی: {args.vus}; ramp-up: {args.ramp_up}s; پایداری: {args.duration}s; think time: {args.think_time}s",
        f"- کل درخواست‌ها: {request_count}; throughput کل: {report['overall_rps']} درخواست/ثانیه; مشاهده‌های خطا: {len(metrics.failures)}",
        f"- اوج مجموع CPU پردازه‌های PHP: {report['php_cpu_percent_sum_peak']}%; اوج مجموع RSS پردازه‌ها: {report['php_rss_mb_sum_peak']} MB", '',
        '| مسیر | تعداد | RPS | p50 ms | p95 ms | p99 ms | بیشینه ms | کدهای HTTP |',
        '|---|---:|---:|---:|---:|---:|---:|---|',
    ]
    for row in totals:
        lines.append(f"| {row['route']} | {row['count']} | {row['rps']} | {row['p50_ms']} | {row['p95_ms']} | {row['p99_ms']} | {row['max_ms']} | {row['statuses']} |")
    lines.extend(['', '## محدودیت‌ها', *[f'- {item}' for item in report['limitations']], ''])
    md_path.write_text('\n'.join(lines), encoding='utf-8')
    print(md_path.read_text(encoding='utf-8'))
    print(f'JSON report: {json_path}')
    print(f'PHP server log: /tmp/ticket-php-benchmark.log')
    return 1 if metrics.failures else 0


if __name__ == '__main__':
    raise SystemExit(main())
