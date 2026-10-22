#!/usr/bin/env python3
"""Concurrent end-to-end write-path stress test on a disposable synthetic database only."""
from __future__ import annotations

import argparse
import concurrent.futures
import http.cookiejar
import json
import os
import re
import signal
import subprocess
import threading
import time
import urllib.error
import urllib.parse
import urllib.request
from collections import Counter, defaultdict
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def percentile(values: list[float], p: float) -> float:
    if not values:
        return 0.0
    ordered = sorted(values)
    return ordered[min(len(ordered) - 1, max(0, int(round((p / 100) * (len(ordered) - 1)))))]


class Recorder:
    def __init__(self) -> None:
        self.lock = threading.Lock()
        self.rows: dict[str, list[dict]] = defaultdict(list)
        self.errors: list[dict] = []
        self.php_cpu_peak = 0.0
        self.php_rss_peak_mb = 0.0
        self.stop = threading.Event()

    def add(self, operation: str, elapsed_ms: float, status: int, ok: bool, detail: str = '', expected_rejection: bool = False) -> dict:
        row = {'operation': operation, 'elapsed_ms': round(elapsed_ms, 2), 'http_status': status, 'ok': ok, 'expected_rejection': expected_rejection}
        with self.lock:
            self.rows[operation].append(row)
            if not ok and not expected_rejection:
                self.errors.append({**row, 'detail': detail[:300]})
        return row

    def sample_php(self) -> None:
        while not self.stop.wait(1):
            try:
                result = subprocess.run(['ps', '-C', 'php', '-o', '%cpu=,%mem=,rss='], capture_output=True, text=True, timeout=2)
                cpu = rss_kb = 0.0
                for line in result.stdout.splitlines():
                    fields = line.split()
                    if len(fields) >= 3:
                        cpu += float(fields[0])
                        rss_kb += float(fields[2])
                with self.lock:
                    self.php_cpu_peak = max(self.php_cpu_peak, cpu)
                    self.php_rss_peak_mb = max(self.php_rss_peak_mb, rss_kb / 1024)
            except (OSError, ValueError, subprocess.SubprocessError):
                pass


def request(opener: urllib.request.OpenerDirector, base: str, path: str,
            data: dict[str, str] | None = None, timeout: int = 60) -> tuple[int, str, str, float, str]:
    url = urllib.parse.urljoin(base, path)
    body = urllib.parse.urlencode(data).encode() if data is not None else None
    headers = {'User-Agent': 'Ticket-Operations-Stress-CI/1.0'}
    if body is not None:
        headers['Content-Type'] = 'application/x-www-form-urlencoded'
    req = urllib.request.Request(url, data=body, headers=headers)
    started = time.perf_counter()
    status, text, final_url, error = 0, '', url, ''
    try:
        with opener.open(req, timeout=timeout) as response:
            status = int(response.status)
            final_url = response.geturl()
            text = response.read(2 * 1024 * 1024).decode('utf-8', errors='replace')
    except urllib.error.HTTPError as exc:
        status = int(exc.code)
        final_url = exc.geturl()
        try:
            text = exc.read(8192).decode('utf-8', errors='replace')
        except OSError:
            pass
        error = f'HTTP {status}'
    except Exception as exc:
        error = f'{type(exc).__name__}: {exc}'
    return status, text, final_url, (time.perf_counter() - started) * 1000, error


def hidden_value(html: str, name: str) -> str:
    match = re.search(r'<input\b(?=[^>]*\bname=["\']' + re.escape(name) + r'["\'])(?=[^>]*\bvalue=["\']([^"\']*)["\'])[^>]*>', html, re.I)
    if match:
        return match.group(1)
    match = re.search(r'<input\b(?=[^>]*\bvalue=["\']([^"\']*)["\'])(?=[^>]*\bname=["\']' + re.escape(name) + r'["\'])[^>]*>', html, re.I)
    return match.group(1) if match else ''


def input_value(html: str, name: str) -> str:
    return hidden_value(html, name)


def is_login_page(html: str) -> bool:
    return 'class="auth-layout"' in html or 'class="auth-form"' in html


def media_payload(csrf: str, media: str, direction: str, serial: str, date: str,
                  unit_id: int, info_type: str, token: str, index: int) -> dict[str, str]:
    data = {
        'csrf': csrf, 'action': 'cd_dvd_create', 'record_id': '0', 'media': media,
        'direction': direction, 'serial': serial, 'info_type': info_type,
        'event_date': date, 'department_id': str(unit_id),
        'info_desc': f'Synthetic isolated benchmark {token} record {index}.',
    }
    if direction == 'IN':
        data.update({'brought_by_type': 'external', 'brought_by': f'Synthetic external {token}-{index}'})
    else:
        data.update({'receiver': f'Synthetic receiver {index}', 'receiver_unit': 'Benchmark Synthetic Unit', 'exit_sheet': str(index + 1)})
    return data


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument('--base-url', default='http://127.0.0.1:8080/')
    parser.add_argument('--vus', type=int, default=250)
    parser.add_argument('--php-workers', type=int, default=32)
    parser.add_argument('--password', default=os.environ.get('BENCHMARK_PASSWORD', ''))
    parser.add_argument('--seed', default='benchmark-results/operation-seed.json')
    parser.add_argument('--output-dir', default='benchmark-results')
    parser.add_argument('--skip-start-server', action='store_true')
    args = parser.parse_args()
    if args.vus != 250:
        parser.error('this operations stress test is intentionally fixed at 250 virtual users')
    if len(args.password) < 16:
        parser.error('BENCHMARK_PASSWORD must be set for the disposable synthetic accounts')

    seed = json.loads(Path(args.seed).read_text(encoding='utf-8'))
    if not seed.get('synthetic_only') or seed.get('account_count') != 250 or len(seed.get('asset_ids', [])) < 250 or len(seed.get('user_ids', [])) != 250:
        parser.error('Synthetic seed manifest is missing or does not match the 250-user workload.')
    token = os.urandom(4).hex().upper()
    test_started_utc = datetime.now(timezone.utc)
    test_started_db = test_started_utc.strftime('%Y-%m-%d %H:%M:%S')
    recorder = Recorder()
    server = None
    log_path = Path('/tmp/ticket-operations-benchmark.log')
    server_log = log_path.open('w', encoding='utf-8')
    if not args.skip_start_server:
        env = os.environ.copy()
        env['PHP_CLI_SERVER_WORKERS'] = str(args.php_workers)
        server = subprocess.Popen(['php', '-S', '127.0.0.1:8080', '-t', str(ROOT)], cwd=ROOT, env=env,
                                  stdout=server_log, stderr=subprocess.STDOUT, start_new_session=True)
        deadline = time.monotonic() + 45
        while time.monotonic() < deadline:
            if server.poll() is not None:
                raise RuntimeError(f'PHP server exited; inspect {log_path}')
            try:
                with urllib.request.urlopen(args.base_url + 'index.php?page=login', timeout=2) as response:
                    if response.status == 200:
                        break
            except Exception:
                time.sleep(0.5)
        else:
            raise RuntimeError('PHP test server did not become ready within 45 seconds')

    sampler = threading.Thread(target=recorder.sample_php, daemon=True)
    sampler.start()
    barrier = threading.Barrier(args.vus, timeout=120)
    pair_lock = threading.Lock()
    pair_results: dict[int, list[dict]] = defaultdict(list)
    worker_errors: list[str] = []
    worker_error_lock = threading.Lock()
    outgoing_index: dict[tuple[str, str], int] = {}
    for media in ('CD', 'DVD'):
        inds = [i for i in range(args.vus) if (i % 2 == 0 and media == 'CD') or (i % 2 == 1 and media == 'DVD')]
        outs = [i for i in inds if i % 4 in (2, 3)]
        for n, i in enumerate(outs):
            outgoing_index[(media, str(i))] = (1 if media == 'CD' else 1001) + n

    def worker(vu: int) -> None:
        jar = http.cookiejar.CookieJar()
        opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
        try:
            status, login_html, _, ms, error = request(opener, args.base_url, 'index.php?page=login')
            csrf_login = hidden_value(login_html, 'csrf')
            login_form_ok = status == 200 and not error and bool(csrf_login) and is_login_page(login_html)
            recorder.add('login-form', ms, status, login_form_ok,
                         error or ('login form/CSRF unavailable' if not login_form_ok else ''))
            if not csrf_login:
                raise RuntimeError('CSRF field absent on login form')
            status, html, _, ms, error = request(opener, args.base_url, 'index.php?page=login', {
                'csrf': csrf_login, 'action': 'login', 'username': f'loadtest-{vu + 1:03d}', 'password': args.password,
            })
            login_ok = status == 200 and not error and not is_login_page(html)
            recorder.add('login-post', ms, status, login_ok, error or ('authentication failed' if not login_ok else ''))
            if not login_ok:
                raise RuntimeError('synthetic login did not authenticate')

            status, ticket_form, _, ms, error = request(opener, args.base_url, 'index.php?page=new-ticket')
            ticket_csrf = hidden_value(ticket_form, 'csrf')
            ticket_ok = status == 200 and not error and not is_login_page(ticket_form) and bool(ticket_csrf)
            recorder.add('prepare-ticket', ms, status, ticket_ok, error or ('ticket form/CSRF unavailable' if not ticket_ok else ''))
            status, dvd_html, _, ms, error = request(opener, args.base_url, 'index.php?page=cd-dvd')
            dvd_csrf = hidden_value(dvd_html, 'csrf')
            date = input_value(dvd_html, 'event_date')
            type_match = re.search(r'<select[^>]*name=["\']info_type["\'][^>]*>\s*<option[^>]*value=["\']([^"\']+)', dvd_html, re.I)
            info_type = type_match.group(1) if type_match else ''
            dvd_ok = status == 200 and not error and not is_login_page(dvd_html) and bool(dvd_csrf and date and info_type)
            recorder.add('prepare-cd-dvd', ms, status, dvd_ok, error or ('CD/DVD form fields unavailable' if not dvd_ok else ''))
            if not (ticket_ok and dvd_ok):
                raise RuntimeError('could not prepare the forms')

            try:
                barrier.wait()
            except threading.BrokenBarrierError as exc:
                raise RuntimeError('concurrent start barrier failed') from exc

            media = 'CD' if vu % 2 == 0 else 'DVD'
            direction = 'IN' if vu % 4 in (0, 1) else 'OUT'
            if direction == 'IN':
                serial = f'T{token}{vu + 1:04d}'
            else:
                serial = f'P86-{outgoing_index[(media, str(vu))]:04d}'
            ticket_data = {
                'csrf': ticket_csrf, 'action': 'create_ticket', 'subject': f'OPS-{token}-{vu + 1:04d}',
                'description': f'Synthetic concurrent end-to-end ticket stress test {token}, worker {vu + 1}.',
                'service_id': str(seed['service_id']), 'service_group': 'it', 'ticket_type': 'incident',
                'requesting_unit_id': str(seed['org_unit_id']), 'department_id': str(seed['department_id']),
                'asset_id': str(seed['asset_ids'][vu]), 'priority': 'normal',
            }
            status, html, final_url, ms, error = request(opener, args.base_url, 'index.php?page=new-ticket', ticket_data)
            query = urllib.parse.parse_qs(urllib.parse.urlparse(final_url).query)
            ticket_id = int((query.get('id') or ['0'])[0]) if (query.get('id') or [''])[0].isdigit() else 0
            created = status == 200 and not error and ticket_id > 0 and not is_login_page(html)
            recorder.add('ticket-create', ms, status, created, error or ('create handler did not redirect to a ticket' if not created else ''))

            def post_cd(operation_index: int, serial_value: str, direction_value: str, pair_id: int | None = None) -> dict:
                payload = media_payload(dvd_csrf, media if pair_id is None else ('CD' if pair_id < 30 else 'DVD'),
                                        direction_value, serial_value, date, int(seed['org_unit_id']), info_type, token, operation_index)
                code, body, _, duration, err = request(opener, args.base_url, 'index.php?page=cd-dvd', payload)
                ok = code == 200 and not err and 'گردش رسانه با شماره ثبت' in body and not is_login_page(body)
                duplicate_rejected = pair_id is not None and code == 200 and not err and (
                    'این شماره از سازمان خارج شده است.' in body or 'این رسانه در حال حاضر داخل سازمان ثبت شده است.' in body
                )
                detail = '' if ok else (err or re.sub(r'<[^>]+>', ' ', body)[:240])
                row = recorder.add('cd-dvd-' + direction_value.lower(), duration, code, ok, detail,
                                   expected_rejection=duplicate_rejected)
                if pair_id is not None:
                    with pair_lock:
                        pair_results[pair_id].append({'vu': vu, 'ok': ok, 'status': code, 'serial': serial_value,
                                                      'expected_rejection': duplicate_rejected, 'detail': detail})
                return row

            regular_row = post_cd(vu + 1, serial, direction)

            if vu < 120:
                pair_id = vu // 2
                pair_direction = 'IN' if pair_id < 30 else 'OUT'
                pair_media = 'CD' if pair_id < 30 else 'DVD'
                pair_serial_number = 701 + pair_id if pair_media == 'CD' else 1701 + (pair_id - 30)
                pair_serial = f'T{token}P{pair_id:03d}' if pair_direction == 'IN' else f'P86-{pair_serial_number:04d}'
                # Let the pair's two workers reach their duplicate attempt together.
                pair_barrier = pair_barriers[pair_id]
                pair_barrier.wait(timeout=60)
                post_cd(1000 + pair_id, pair_serial, pair_direction, pair_id)

            if created:
                update = {
                    'csrf': ticket_csrf, 'action': 'update_ticket', 'ticket_id': str(ticket_id),
                    'status': 'resolved', 'assigned_to': str(seed['user_ids'][vu]),
                    'update_note': f'Synthetic resolution for isolated benchmark {token}, worker {vu + 1}.'
                }
                status2, html2, _, ms2, error2 = request(opener, args.base_url, f'index.php?page=ticket&id={ticket_id}', update)
                resolved = status2 == 200 and not error2 and 'تغییرات درخواست ذخیره شد' in html2 and not is_login_page(html2)
                recorder.add('ticket-resolve', ms2, status2, resolved, error2 or ('resolution handler did not confirm saved change' if not resolved else ''))
        except Exception as exc:
            with worker_error_lock:
                worker_errors.append(f'vu={vu + 1}: {type(exc).__name__}: {exc}')
            try:
                barrier.abort()
            except Exception:
                pass

    # One pair-level barrier prevents the two duplicate attempts from drifting apart.
    pair_barriers = {pair_id: threading.Barrier(2, timeout=60) for pair_id in range(60)}
    wall_start = time.time()
    try:
        with concurrent.futures.ThreadPoolExecutor(max_workers=args.vus) as pool:
            futures = [pool.submit(worker, vu) for vu in range(args.vus)]
            for future in concurrent.futures.as_completed(futures):
                future.result()
    finally:
        recorder.stop.set()
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

    elapsed = max(0.001, time.time() - wall_start)
    server_text = log_path.read_text(encoding='utf-8', errors='replace') if log_path.exists() else ''
    runtime_lines = [line.strip() for line in server_text.splitlines() if re.search(r'PHP (?:Fatal error|Parse error|Warning|Notice)', line, re.I)]
    pair_summary = []
    pair_invariant_failures = []
    for pair_id in range(60):
        rows = sorted(pair_results.get(pair_id, []), key=lambda row: row['vu'])
        success_count = sum(1 for row in rows if row['ok'])
        summary = {'pair_id': pair_id, 'direction': 'IN' if pair_id < 30 else 'OUT', 'media': 'CD' if pair_id < 30 else 'DVD',
                   'attempts_recorded': len(rows), 'successes': success_count, 'serial': rows[0]['serial'] if rows else ''}
        pair_summary.append(summary)
        if len(rows) != 2 or success_count != 1:
            pair_invariant_failures.append({**summary, 'details': rows})

    # The isolated database is queried independently after HTTP operations; counts are not inferred from HTTP alone.
    db_report = {'collection_status': 'not-run'}
    try:
        probe = subprocess.run(['php', str(ROOT / 'tools' / 'benchmark_operations_report.php'), token, test_started_db],
                               cwd=ROOT, capture_output=True, text=True, timeout=60)
        db_report = json.loads(probe.stdout) if probe.returncode == 0 else {'collection_status': 'failed', 'stderr': probe.stderr[-2000:]}
    except Exception as exc:
        db_report = {'collection_status': 'failed', 'error': f'{type(exc).__name__}: {exc}'}

    totals = []
    for operation, rows in sorted(recorder.rows.items()):
        times = [row['elapsed_ms'] for row in rows]
        counts = Counter(str(row['http_status']) for row in rows)
        totals.append({'operation': operation, 'attempts': len(rows), 'successes': sum(1 for row in rows if row['ok']),
                       'http_statuses': dict(counts), 'p50_ms': round(percentile(times, 50), 2),
                       'p95_ms': round(percentile(times, 95), 2), 'p99_ms': round(percentile(times, 99), 2),
                       'max_ms': round(max(times, default=0), 2)})
    expected_tickets = args.vus
    ticket_create = sum(row['successes'] for row in totals if row['operation'] == 'ticket-create')
    ticket_resolve = sum(row['successes'] for row in totals if row['operation'] == 'ticket-resolve')
    regular_media_attempts = args.vus
    duplicate_attempts = 120
    repeated_direction_count = int(db_report.get('cd_dvd', {}).get('consecutive_same_direction_pairs', -1))
    invariant_failures = []
    if ticket_create != expected_tickets:
        invariant_failures.append(f'ticket creates confirmed by HTTP: {ticket_create}/{expected_tickets}')
    if ticket_resolve != expected_tickets:
        invariant_failures.append(f'ticket resolutions confirmed by HTTP: {ticket_resolve}/{expected_tickets}')
    if int(db_report.get('tickets', {}).get('resolved_for_run', -1)) != expected_tickets:
        invariant_failures.append('database final resolved-ticket count did not equal 250')
    if int(db_report.get('tickets', {}).get('created_for_run', -1)) != expected_tickets:
        invariant_failures.append('database-created ticket count did not equal 250')
    if int(db_report.get('tickets', {}).get('ticket_numbers_assigned', -1)) != expected_tickets or int(db_report.get('tickets', {}).get('unique_ticket_numbers', -1)) != expected_tickets:
        invariant_failures.append('ticket numbers were missing or not unique in the final database state')
    if int(db_report.get('cd_dvd', {}).get('records_for_run', -1)) != regular_media_attempts + duplicate_attempts // 2:
        # Conflict requests intentionally permit exactly one save per same-serial pair.
        invariant_failures.append('database CD/DVD record count did not match 250 regular writes plus one winner per 60 pairs')
    if repeated_direction_count != 0:
        invariant_failures.append(f'CD/DVD same-serial repeated-direction integrity violations: {repeated_direction_count}')
    if int(db_report.get('unexpected_application_system_error_count', -1)) != 0:
        invariant_failures.append('unexpected application/system errors were recorded during the test')
    if pair_invariant_failures:
        invariant_failures.append(f'{len(pair_invariant_failures)} concurrent duplicate pairs did not yield exactly one successful response')
    if db_report.get('collection_status') != 'ok':
        invariant_failures.append('independent database verification failed')
    if worker_errors:
        invariant_failures.append(f'{len(worker_errors)} virtual-user worker exceptions were recorded')

    report = {
        'started_utc': test_started_utc.isoformat(),
        'environment': 'ephemeral CI runner; disposable MariaDB; PHP CLI worker pool; synthetic identities/data only',
        'target': '127.0.0.1 only; production and staging were not contacted',
        'scenario': '250 concurrent sessions; each creates and resolves one ticket through application POST handlers and records one CD/DVD event; 60 additional same-serial duplicate races (30 IN, 30 OUT) test serial-state integrity.',
        'virtual_users': args.vus, 'php_workers': args.php_workers,
        'elapsed_wall_seconds': round(elapsed, 2), 'logical_operations': sum(len(rows) for rows in recorder.rows.values()),
        'overall_logical_operations_per_second': round(sum(len(rows) for rows in recorder.rows.values()) / elapsed, 2),
        'php_cpu_percent_sum_peak': round(recorder.php_cpu_peak, 2), 'php_rss_mb_sum_peak': round(recorder.php_rss_peak_mb, 2),
        'php_runtime_warning_notice_count': len(runtime_lines), 'php_runtime_error_samples': runtime_lines[:40],
        'http_application_operation_failures': len(recorder.errors), 'http_failure_samples': recorder.errors[:100],
        'operations': totals, 'database_verification': db_report,
        'worker_exceptions': worker_errors[:100],
        'duplicate_race_pairs': pair_summary, 'duplicate_race_invariant_failures': pair_invariant_failures,
        'invariant_failures': invariant_failures,
        'limitations': [
            'This run directly exercises the ticket create/update and CD/DVD create handlers through authenticated HTTP POSTs; it is not exhaustive coverage of every module or every route.',
            'Food external Access/ODBC sources, LDAP/Active Directory, physical media, printers, and production/staging capacity were not exercised.',
            'The database and all test accounts/records are disposable synthetic CI fixtures; no production or staging traffic/data was used.',
            'HTTP response, application confirmation, PHP logs, and independently queried final database state are reported separately.',
        ],
    }
    output = Path(args.output_dir)
    output.mkdir(parents=True, exist_ok=True)
    (output / 'benchmark-operations.json').write_text(json.dumps(report, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    lines = [
        '# گزارش آزمون نوشتنی هم‌زمان تیکت و CD/DVD', '',
        f"- شروع UTC: {report['started_utc']}", f"- محیط: {report['environment']}", f"- مقصد: {report['target']}",
        f"- سناریو: {report['scenario']}", f"- کاربران هم‌زمان: {args.vus}; workerهای PHP: {args.php_workers}; زمان: {elapsed:.2f}s",
        f"- عملیات منطقی: {report['logical_operations']}; throughput منطقی: {report['overall_logical_operations_per_second']} عملیات/ثانیه",
        f"- اوج مجموع CPU پردازه‌های PHP: {report['php_cpu_percent_sum_peak']}%; اوج مجموع RSS: {report['php_rss_mb_sum_peak']} MB",
        f"- خطاهای عملیات HTTP/تأیید برنامه: {len(recorder.errors)}; هشدار/Notice/Fatal در PHP log: {len(runtime_lines)}",
        '', '| عملیات | تلاش | موفقیت تأییدشده | p50 ms | p95 ms | p99 ms | بیشینه ms | HTTP |',
        '|---|---:|---:|---:|---:|---:|---:|---|',
    ]
    for row in totals:
        lines.append(f"| {row['operation']} | {row['attempts']} | {row['successes']} | {row['p50_ms']} | {row['p95_ms']} | {row['p99_ms']} | {row['max_ms']} | {row['http_statuses']} |")
    lines += ['', '## بررسی مستقل دادهٔ نهایی', '', '```json', json.dumps(db_report, ensure_ascii=False, indent=2), '```', '',
              '## یکپارچگی رقابت سریال CD/DVD', '',
              f"- جفت‌های هم‌زمان تکراری: {len(pair_summary)}؛ جفت‌هایی که دقیقاً یک درخواست موفق داشتند: {sum(1 for p in pair_summary if p['successes'] == 1 and p['attempts_recorded'] == 2)}",
              f"- رخدادهای متوالیِ هم‌جهت برای یک سریال در دادهٔ نهایی: {repeated_direction_count}",
              f"- شکست‌های invariant: {json.dumps(invariant_failures, ensure_ascii=False)}", '',
              '## محدودیت‌ها', *[f'- {item}' for item in report['limitations']], '']
    (output / 'benchmark-operations.md').write_text('\n'.join(lines), encoding='utf-8')
    print('\n'.join(lines[:20]))
    return 1 if invariant_failures or recorder.errors or runtime_lines else 0


if __name__ == '__main__':
    raise SystemExit(main())
