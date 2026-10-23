#!/usr/bin/env python3
"""Food-only authenticated load and CRUD stress against a disposable CI database."""
from __future__ import annotations

import concurrent.futures
import json
import os
import re
import socket
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
BASE = os.environ.get("FOOD_STRESS_BASE_URL", "http://127.0.0.1:8080/")
SEED_PATH = ROOT / "benchmark-results" / "food-system-seed.json"


class Metrics:
    def __init__(self) -> None:
        self.lock = threading.Lock()
        self.rows: dict[str, list[dict]] = defaultdict(list)
        self.failures: list[dict] = []

    def add(self, label: str, elapsed_ms: float, status: int, error: str = "", nbytes: int = 0) -> None:
        with self.lock:
            self.rows[label].append({"elapsed_ms": round(elapsed_ms, 2), "status": status, "bytes": nbytes})
            if error or status < 200 or status >= 400:
                self.failures.append({"route": label, "status": status, "error": error[:300]})

    def fail(self, label: str, message: str) -> None:
        with self.lock:
            self.failures.append({"route": label, "status": 0, "error": message[:300]})


def api_path(page: str, route: str, params: dict | None = None) -> str:
    query = {"page": page, "food_api": route}
    if params:
        query.update({k: str(v) for k, v in params.items()})
    return "food-api?" + urllib.parse.urlencode(query)


def request(path: str, metrics: Metrics, label: str, data: dict | None = None,
            csrf: str = "", session_id: str = "") -> tuple[int, str]:
    url = urllib.parse.urljoin(BASE, path)
    headers = {"User-Agent": "Food-System-Isolated-Stress/1.0", "Accept": "application/json"}
    if session_id:
        headers["Cookie"] = "PHPSESSID=" + session_id
    body = None
    if data is not None:
        body = json.dumps(data, ensure_ascii=False).encode("utf-8")
        headers["Content-Type"] = "application/json; charset=utf-8"
        headers["X-CSRF-Token"] = csrf
    req = urllib.request.Request(url, data=body, headers=headers)
    started = time.perf_counter()
    status, text, error = 0, "", ""
    try:
        with urllib.request.urlopen(req, timeout=60) as response:
            status = int(response.status)
            raw = response.read(4 * 1024 * 1024)
            text = raw.decode("utf-8", errors="replace")
            size = len(raw)
    except urllib.error.HTTPError as exc:
        status = int(exc.code)
        try:
            raw = exc.read(4096)
            text = raw.decode("utf-8", errors="replace")
            size = len(raw)
        except OSError:
            size = 0
        error = f"HTTP {status}: {text[:160]}"
    except Exception as exc:  # surface network and timeout problems in the report
        error = f"{type(exc).__name__}: {exc}"
        size = 0
    metrics.add(label, (time.perf_counter() - started) * 1000, status, error, size)
    return status, text


def expect(condition: bool, label: str, message: str, metrics: Metrics) -> None:
    if not condition:
        metrics.fail(label, message)


def parse_json(text: str, label: str, metrics: Metrics) -> dict:
    try:
        data = json.loads(text)
        if not isinstance(data, dict):
            raise ValueError("expected JSON object")
        return data
    except (json.JSONDecodeError, ValueError) as exc:
        metrics.fail(label, f"invalid JSON: {exc}; body={text[:160]!r}")
        return {}



def find_order(items: object, date: str, status: str | None = None) -> dict | None:
    if not isinstance(items, list):
        return None
    for item in items:
        if isinstance(item, dict) and item.get("food_date") == date and (status is None or item.get("status") == status):
            return item
    return None


def self_order_flow(index: int, session: dict, seed: dict, metrics: Metrics) -> bool:
    label = f"self_vu_{index + 1:03d}"
    csrf = str(session["csrf"])
    session_id = str(session["session_id"])
    body = {
        "food_date": seed["dates"]["self"],
        "calendar_item_id": int(seed["calendar_item_ids"]["self"]),
        "meal_type": "lunch",
    }
    status, text = request(api_path("food-order", "order"), metrics, "self.order_create", body, csrf, session_id)
    created = parse_json(text, label + ".order_create", metrics)
    if status != 200 or not created.get("ok") or int(created.get("order_id", 0)) < 1:
        expect(False, label + ".order_create", f"unexpected create response HTTP {status}: {text[:200]}", metrics)
        return False
    order_id = int(created["order_id"])

    status, text = request(api_path("food-order", "my-orders", {"from": seed["dates"]["self"], "to": seed["dates"]["self"]}), metrics, "self.my_orders_readback", session_id=session_id)
    readback = parse_json(text, label + ".my_orders_readback", metrics)
    active = find_order(readback.get("items"), seed["dates"]["self"], "active")
    if status != 200 or active is None or int(active.get("id", 0)) != order_id:
        expect(False, label + ".my_orders_readback", f"created active order {order_id} not present (HTTP {status})", metrics)
        return False

    status, text = request(api_path("food-order", "cancel"), metrics, "self.order_cancel", {"order_id": order_id}, csrf, session_id)
    cancelled = parse_json(text, label + ".order_cancel", metrics)
    if status != 200 or not cancelled.get("ok"):
        expect(False, label + ".order_cancel", f"cancel failed HTTP {status}: {text[:200]}", metrics)
        return False

    status, text = request(api_path("food-order", "my-orders", {"from": seed["dates"]["self"], "to": seed["dates"]["self"]}), metrics, "self.my_orders_cancel_readback", session_id=session_id)
    readback = parse_json(text, label + ".my_orders_cancel_readback", metrics)
    row = find_order(readback.get("items"), seed["dates"]["self"], "cancelled")
    if status != 200 or row is None or int(row.get("id", 0)) != order_id:
        expect(False, label + ".my_orders_cancel_readback", f"cancelled order {order_id} not present (HTTP {status})", metrics)
        return False
    return True


def manager_crud(session: dict, seed: dict, metrics: Metrics) -> dict:
    marker = seed["marker"]
    passed = 0
    csrf = str(session["csrf"])
    session_id = str(session["session_id"])

    def post(route: str, body: dict, label: str, ticket: bool = True) -> tuple[int, dict]:
        page = "food-ticket" if ticket else "food-order"
        status, text = request(api_path(page, route), metrics, label, body, csrf, session_id)
        return status, parse_json(text, label, metrics)

    draft_name = f"Food Stress Draft Item {marker}"
    final_name = f"Food Stress Synthetic Item {marker}"
    status, data = post("food-menu/catalog", {"food_name": draft_name, "category": "سایر", "active": True}, "crud.catalog_create")
    created_item = next((x for x in data.get("items", []) if isinstance(x, dict) and x.get("food_name") == draft_name), None)
    expect(status == 200 and data.get("ok") and created_item is not None, "crud.catalog_create", f"catalog create failed HTTP {status}", metrics)
    if status == 200 and created_item:
        passed += 1
        item_id = int(created_item["id"])
        status, data = post("food-menu/catalog", {"id": item_id, "food_name": final_name, "category": "سایر", "active": False}, "crud.catalog_update")
        updated = next((x for x in data.get("items", []) if isinstance(x, dict) and x.get("id") == item_id), None)
        expect(status == 200 and data.get("ok") and updated is not None and not bool(updated.get("active")), "crud.catalog_update", f"catalog update/deactivate failed HTTP {status}", metrics)
        if status == 200 and updated is not None and not bool(updated.get("active")):
            passed += 1

    day_body = {"date": seed["dates"]["menu"], "meal_type": "lunch", "food_ids": [int(seed["food_ids"][0])], "note": "Food Stress draft " + marker, "order_status": "open"}
    status, data = post("food-menu/day", day_body, "crud.menu_day_create")
    expect(status == 200 and data.get("ok"), "crud.menu_day_create", f"menu-day create failed HTTP {status}: {data.get('error', '')}", metrics)
    if status == 200 and data.get("ok"):
        passed += 1
    day_body.update({"food_ids": [int(seed["food_ids"][0]), int(seed["food_ids"][1])], "note": "Food Stress API day " + marker})
    status, data = post("food-menu/day", day_body, "crud.menu_day_update")
    expect(status == 200 and data.get("ok"), "crud.menu_day_update", f"menu-day update failed HTTP {status}: {data.get('error', '')}", metrics)
    if status == 200 and data.get("ok"):
        passed += 1

    group_title = "Food Stress Group " + marker
    group_uid = "FGSTRESS_" + re.sub(r"[^A-Za-z0-9]", "", marker)[-24:]
    group_body = {"title": group_title, "description": "Synthetic food-only group CRUD", "l_uid": group_uid, "active": True}
    status, data = post("food-groups", group_body, "crud.group_create")
    group = next((x for x in data.get("groups", []) if isinstance(x, dict) and x.get("title") == group_title), None)
    expect(status == 200 and data.get("ok") and group is not None, "crud.group_create", f"food group create failed HTTP {status}: {text_preview(data)}", metrics)
    group_id = int(group.get("id", 0)) if group else 0
    if status == 200 and group_id > 0:
        passed += 1
        status, data = post("food-groups", {**group_body, "id": group_id, "description": "Synthetic food-only group updated"}, "crud.group_update")
        updated = next((x for x in data.get("groups", []) if isinstance(x, dict) and int(x.get("id", 0)) == group_id), None)
        expect(status == 200 and data.get("ok") and updated is not None, "crud.group_update", f"food group update failed HTTP {status}", metrics)
        if status == 200 and updated is not None:
            passed += 1

        # Exercise the food-group absence status reader while the synthetic group exists.
        status, text = request(api_path("food-ticket", "absence-status", {"group_id": group_id, "date": seed["dates"]["menu"]}), metrics, "crud.group_absence_read", session_id=session_id)
        absence = parse_json(text, "crud.group_absence_read", metrics)
        expect(status == 200 and not absence.get("error"), "crud.group_absence_read", f"absence status failed HTTP {status}", metrics)
        if status == 200 and not absence.get("error"):
            passed += 1

        status, data = post("food-groups", {"action": "delete", "id": group_id}, "crud.group_delete")
        exists = any(isinstance(x, dict) and int(x.get("id", 0)) == group_id for x in data.get("groups", []))
        expect(status == 200 and data.get("ok") and not exists, "crud.group_delete", f"food group delete failed HTTP {status}", metrics)
        if status == 200 and not exists:
            passed += 1

    card_number = "FS" + __import__("hashlib").sha256(marker.encode()).hexdigest()[:20].upper()
    status, data = post("guest-cards", {"cardNumber": card_number, "guestName": "Food Stress Guest", "dailyLimit": 2, "active": True}, "crud.guest_card_create")
    card = next((x for x in data.get("items", []) if isinstance(x, dict) and str(x.get("cardNumber", "")).upper() == card_number), None)
    expect(status == 200 and data.get("ok") and card is not None, "crud.guest_card_create", f"guest-card create failed HTTP {status}", metrics)
    if status == 200 and card is not None:
        passed += 1
        status, data = post("guest-cards", {"id": int(card["id"]), "cardNumber": card_number, "guestName": "Food Stress Guest Updated", "dailyLimit": 3, "active": True}, "crud.guest_card_update")
        updated = next((x for x in data.get("items", []) if isinstance(x, dict) and int(x.get("id", 0)) == int(card["id"])), None)
        expect(status == 200 and data.get("ok") and updated is not None and updated.get("name") == "Food Stress Guest Updated", "crud.guest_card_update", f"guest-card update failed HTTP {status}", metrics)
        if status == 200 and updated is not None and updated.get("name") == "Food Stress Guest Updated":
            passed += 1
        status, data = post("guest-cards", {"action": "delete", "id": int(card["id"])}, "crud.guest_card_delete")
        exists = any(isinstance(x, dict) and int(x.get("id", 0)) == int(card["id"]) for x in data.get("items", []))
        expect(status == 200 and data.get("ok") and not exists, "crud.guest_card_delete", f"guest-card delete failed HTTP {status}", metrics)
        if status == 200 and not exists:
            passed += 1

    return {"passed": passed}


def text_preview(data: dict) -> str:
    return str(data.get("error", data.get("message", "response did not contain expected group")))[:180]


def bulk_lifecycle(session: dict, seed: dict, metrics: Metrics) -> dict:
    csrf = str(session["csrf"])
    session_id = str(session["session_id"])
    ids = [int(x) for x in seed["employee_ids"]]
    chunks = [ids[i:i + 100] for i in range(0, len(ids), 100)]
    operations = [("order", "Food-only CI synthetic bulk order"), ("cancel", "Food-only CI synthetic bulk cancellation")]
    totals: dict[str, int] = {}
    for operation, reason in operations:
        succeeded = failed = total = 0
        for chunk in chunks:
            body = {
                "date": seed["dates"]["bulk"],
                "meal_type": "lunch",
                "operation": operation,
                "employee_ids": chunk,
                "item_id": int(seed["calendar_item_ids"]["bulk"]),
                "reason": reason,
            }
            status, text = request(api_path("food-ticket", "food-menu/bulk-apply"), metrics, "bulk." + operation, body, csrf, session_id)
            data = parse_json(text, "bulk." + operation, metrics)
            succeeded += int(data.get("succeeded", 0))
            failed += int(data.get("failed", 0))
            total += int(data.get("total", 0))
            if status != 200 or data.get("total") != len(chunk) or int(data.get("failed", -1)) != 0:
                metrics.fail("bulk." + operation, f"batch HTTP {status}; expected {len(chunk)} successes, got {data.get('succeeded')}/{data.get('total')}; {text[:200]}")
        totals[operation] = succeeded
        if failed or total != len(ids) or succeeded != len(ids):
            metrics.fail("bulk." + operation + ".summary", f"incomplete: succeeded={succeeded}, failed={failed}, total={total}")
    snapshot_status, snapshot_text = request(
        api_path("food-ticket", "food-menu/bulk-snapshot", {"date": seed["dates"]["bulk"], "meal_type": "lunch"}),
        metrics, "bulk.snapshot_readback", session_id=session_id,
    )
    snapshot = parse_json(snapshot_text, "bulk.snapshot_readback", metrics)
    rows = snapshot.get("items", snapshot.get("employees", []))
    if snapshot_status != 200 or not isinstance(rows, list):
        metrics.fail("bulk.snapshot_readback", f"bulk snapshot response invalid HTTP {snapshot_status}")
    return {"verified": totals.get("order", 0) == 250 and totals.get("cancel", 0) == 250, "ordered": totals.get("order", 0), "cancelled": totals.get("cancel", 0), "snapshot_rows": len(rows) if isinstance(rows, list) else 0}


def build_read_routes(seed: dict, group_id: int) -> list[tuple[str, str]]:
    date_self = seed["dates"]["self"]
    date_bulk = seed["dates"]["bulk"]
    date_menu = seed["dates"]["menu"]
    month = seed["jalali_month"]
    return [
        ("food_order.month", api_path("food-order", "month", {"month": month, "meal_type": "lunch"})),
        ("food_order.my_orders", api_path("food-order", "my-orders", {"from": date_self, "to": date_bulk})),
        ("food_order.search_users", api_path("food-order", "search-users", {"q": "foodstress"})),
        ("food_menu.status", api_path("food-ticket", "food-menu/status")),
        ("food_menu.month", api_path("food-ticket", "food-menu/month", {"month": month, "meal_type": "lunch"})),
        ("food_menu.catalog", api_path("food-ticket", "food-menu/catalog")),
        ("food_menu.orders", api_path("food-ticket", "food-menu/orders", {"date": date_self, "meal_type": "lunch"})),
        ("food_menu.group_orders", api_path("food-ticket", "food-menu/group-orders", {"date": date_self, "meal_type": "lunch", "group_id": "all"})),
        ("food_menu.bulk_snapshot", api_path("food-ticket", "food-menu/bulk-snapshot", {"date": date_bulk, "meal_type": "lunch"})),
        ("food_menu.groups", api_path("food-ticket", "food-menu/groups")),
        ("food_menu.cancel_requests", api_path("food-ticket", "food-menu/cancel-requests")),
        ("food_admin.food_groups", api_path("food-ticket", "food-groups", {"date": date_menu})),
        ("food_admin.monitoring", api_path("food-ticket", "monitoring", {"from": date_self, "to": date_bulk})),
        ("food_admin.employees", api_path("food-ticket", "employees")),
        ("food_admin.guest_cards", api_path("food-ticket", "guest-cards")),
        ("food_admin.absent", api_path("food-ticket", "absent", {"from": date_self, "to": date_bulk})),
    ]


def manager_read_worker(index: int, session: dict, routes: list[tuple[str, str]], metrics: Metrics) -> int:
    label = f"manager_vu_{index + 1:03d}"
    session_id = str(session["session_id"])
    checked = 0
    start = (index * 4) % len(routes)
    for offset in range(4):
        route_label, path = routes[(start + offset) % len(routes)]
        status, text = request(path, metrics, "manager_read." + route_label, session_id=session_id)
        try:
            payload = json.loads(text)
        except json.JSONDecodeError:
            payload = None
        valid = status == 200 and isinstance(payload, dict) and not payload.get("error")
        if not valid:
            metrics.fail("manager_read." + route_label, f"expected successful food API JSON; HTTP {status}; {text[:180]}")
        else:
            checked += 1
    return checked


def percentile(values: list[float], p: float) -> float:
    if not values:
        return 0.0
    values = sorted(values)
    position = (len(values) - 1) * p / 100
    low = int(position)
    high = min(low + 1, len(values) - 1)
    return values[low] + (values[high] - values[low]) * (position - low)


def write_report(metrics: Metrics, self_verified: int, bulk: dict, manager_reads: dict, crud: dict, php_server: subprocess.Popen | None) -> dict:
    with metrics.lock:
        rows = {key: list(value) for key, value in metrics.rows.items()}
        failures = list(metrics.failures)
    route_summary = {}
    for route, samples in sorted(rows.items()):
        durations = [float(row["elapsed_ms"]) for row in samples]
        statuses = defaultdict(int)
        for row in samples:
            statuses[str(row["status"])] += 1
        route_summary[route] = {
            "requests": len(samples),
            "status_counts": dict(statuses),
            "latency_ms": {"p50": round(percentile(durations, 50), 2), "p95": round(percentile(durations, 95), 2), "p99": round(percentile(durations, 99), 2), "max": round(max(durations, default=0), 2)},
            "response_bytes_total": sum(int(row["bytes"]) for row in samples),
        }
    all_samples = [row for samples in rows.values() for row in samples]
    elapsed = max((time.perf_counter() - RUN_STARTED), 0.001)
    report = {
        "synthetic_only": True,
        "scope": "food order and food management handlers/APIs only; disposable MariaDB; general ITSM ticket/CD-DVD/inventory endpoints excluded",
        "timestamp_utc": datetime.now(timezone.utc).isoformat(),
        "concurrency": {"self_order_users": 250, "manager_read_users": 250, "php_workers": int(os.environ.get("PHP_CLI_SERVER_WORKERS", "32"))},
        "summary": {
            "http_requests": len(all_samples),
            "throughput_requests_per_second": round(len(all_samples) / elapsed, 2),
            "http_success_responses": sum(1 for row in all_samples if 200 <= int(row["status"]) < 400),
            "failed_checks": len(failures),
            "self_order_lifecycles_verified": self_verified,
            "bulk_orders_created": int(bulk.get("ordered", 0)),
            "bulk_orders_cancelled": int(bulk.get("cancelled", 0)),
            "manager_read_responses_validated": int(manager_reads.get("validated", 0)),
            "manager_crud_checks_passed": int(crud.get("passed", 0)),
        },
        "routes": route_summary,
        "failures": failures[:200],
        "php_server_returncode": php_server.poll() if php_server else None,
    }
    out = ROOT / "benchmark-results" / "food-system-run.json"
    out.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    md = [
        "# Food system isolated stress report",
        "",
        "- Scope: food ordering and food management APIs only; synthetic records/accounts; disposable MariaDB.",
        "- Excluded: general ITSM tickets, CD/DVD, inventory, production/staging, and physical printer/attendance connections.",
        f"- Concurrent synthetic users: 250 ordering users + 250 food managers; PHP workers: {report['concurrency']['php_workers']}.",
        f"- HTTP requests: {report['summary']['http_requests']} ({report['summary']['throughput_requests_per_second']} req/s); successful responses: {report['summary']['http_success_responses']}.",
        f"- Verified self order/read/cancel/read lifecycles: {self_verified}/250.",
        f"- Manager bulk orders/cancellations: {bulk.get('ordered', 0)}/{bulk.get('cancelled', 0)}.",
        f"- Manager read responses validated: {manager_reads.get('validated', 0)}; CRUD checks passed: {crud.get('passed', 0)}.",
        f"- Failed checks: {len(failures)}.",
        "",
        "## API latency (milliseconds)",
        "",
        "| Route | Requests | p50 | p95 | p99 | max |",
        "|---|---:|---:|---:|---:|---:|",
    ]
    for route, summary in route_summary.items():
        lat = summary["latency_ms"]
        md.append(f"| `{route}` | {summary['requests']} | {lat['p50']} | {lat['p95']} | {lat['p99']} | {lat['max']} |")
    if failures:
        md.extend(["", "## Failures", ""])
        md.extend(f"- `{x['route']}` (HTTP {x['status']}): {x['error']}" for x in failures[:50])
    (ROOT / "benchmark-results" / "food-system-stress.md").write_text("\n".join(md) + "\n", encoding="utf-8")
    return report


RUN_STARTED = time.perf_counter()


def main() -> int:
    global RUN_STARTED
    seed = json.loads(SEED_PATH.read_text(encoding="utf-8"))
    employee_sessions = seed.get("employee_sessions", [])
    manager_sessions = seed.get("manager_sessions", [])
    if len(employee_sessions) != 250 or len(manager_sessions) != 250:
        raise SystemExit("Expected 250 prebuilt synthetic food-user sessions and 250 manager sessions.")
    metrics = Metrics()
    server_log_path = Path("/tmp/food-system-php-server.log")
    server_log = server_log_path.open("w", encoding="utf-8")
    env = os.environ.copy()
    env.setdefault("PHP_CLI_SERVER_WORKERS", "32")
    server = subprocess.Popen(["php", "-S", "0.0.0.0:8080", str(ROOT / "tools" / "food_system_router.php")], cwd=ROOT, env=env, stdout=server_log, stderr=subprocess.STDOUT)
    try:
        RUN_STARTED = time.perf_counter()
        parsed = urllib.parse.urlparse(BASE)
        host, port = parsed.hostname or "127.0.0.1", parsed.port or 80
        deadline = time.monotonic() + 120
        while time.monotonic() < deadline:
            if server.poll() is not None:
                raise RuntimeError(f"PHP development server exited early; see {server_log_path}")
            try:
                with socket.create_connection((host, port), timeout=2):
                    break
            except OSError:
                time.sleep(0.25)
        else:
            raise RuntimeError(f"PHP development server did not become ready; see {server_log_path}")

        # Authenticated sessions are synthetic and prebuilt; never invoke general login or inventory collection.
        main_session = manager_sessions[0]
        crud = manager_crud(main_session, seed, metrics)
        bulk = bulk_lifecycle(main_session, seed, metrics)

        with concurrent.futures.ThreadPoolExecutor(max_workers=250) as pool:
            self_results = list(pool.map(
                lambda pair: self_order_flow(pair[0], pair[1], seed, metrics),
                enumerate(employee_sessions),
            ))
        self_verified = sum(bool(x) for x in self_results)
        if self_verified != 250:
            metrics.fail("self.lifecycle.summary", f"verified {self_verified}/250 user self-order lifecycles")

        routes = build_read_routes(seed, group_id=0)
        with concurrent.futures.ThreadPoolExecutor(max_workers=250) as pool:
            values = list(pool.map(lambda i: manager_read_worker(i, manager_sessions[i], routes, metrics), range(250)))
        manager_reads = {"validated": sum(values), "virtual_users": 250, "reads_per_user": 4}
        if manager_reads["validated"] != 1000:
            metrics.fail("manager.reads.summary", f"validated {manager_reads['validated']}/1000 food manager reads")

        report = write_report(metrics, self_verified, bulk, manager_reads, crud, server)
        print(json.dumps(report, ensure_ascii=False, indent=2))
        return 0 if not report["failures"] and self_verified == 250 and bulk.get("verified") and manager_reads.get("validated") == 1000 and crud.get("passed", 0) >= 6 else 1
    finally:
        if server.poll() is None:
            server.terminate()
            try:
                server.wait(timeout=10)
            except subprocess.TimeoutExpired:
                server.kill()
                server.wait(timeout=5)
        server_log.close()


if __name__ == "__main__":
    raise SystemExit(main())
