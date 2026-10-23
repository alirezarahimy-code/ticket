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
        status, body, error, final_url = 0, "", "", url
        try:
            with self.opener.open(request, timeout=30) as response:
                status = int(response.status)
                final_url = response.geturl()
                body = response.read(2 * 1024 * 1024).decode("utf-8", errors="replace")
        except urllib.error.HTTPError as exc:
            status = int(exc.code)
            final_url = exc.geturl()
            body = exc.read(2 * 1024 * 1024).decode("utf-8", errors="replace")
            error = "" if status in expected else f"HTTP {status}"
        except Exception as exc:  # noqa: BLE001 - record transport failures as test results
            error = f"{type(exc).__name__}: {exc}"
        latency_ms = round((time.perf_counter() - started) * 1000, 2)
        fatal = next((marker for marker in FATAL_MARKERS if marker in body), "")
        ok = not error and status in expected and not fatal
        if not ok:
            error = "; ".join(part for part in (error, f"unexpected status {status}" if status not in expected else "", f"PHP marker: {fatal}" if fatal else "") if part)
        result = {"name": label, "method": method, "path": path, "final_url": final_url, "status": status,
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
        for route, query in [
            ("absent", f"from={today}&to={today}"),
            ("guest-requests-report", f"from={today}&to={today}&deputy_id=all"),
            ("food-manual-attendance", f"date={today}"),
            ("orders", f"date={today}"),
        ]:
            self.get_json(f"food-ticket API GET {route}", f"index.php?page=food-ticket&food_api={route}&{query}")
        for route, query in [
            ("food-menu/group-orders", f"date={today}&group_id=all"),
            ("food-menu/bulk-snapshot", f"date={today}&meal_type=lunch"),
            ("food-menu/emergency/people", ""),
        ]:
            encoded = urllib.parse.quote(route, safe="/-")
            self.get_json(f"food-order menu API GET {route}", f"index.php?page=food-ticket&food_api={encoded}" + ("&" + query if query else ""))
        self.get_json("traffic API invalid national code is rejected safely",
                      "index.php?page=traffic-control&traffic_api=lookup&code=000")
        _, body, _ = self.request("chatbot API validates empty synthetic question before provider use", "index.php?page=chatbot-api",
                                  "POST", urllib.parse.urlencode({"csrf": self.csrf, "question": ""}).encode(),
                                  {"Content-Type": "application/x-www-form-urlencoded"}, expected=(400,))
        try:
            payload = json.loads(body)
        except json.JSONDecodeError:
            payload = None
        if not isinstance(payload, dict) or payload.get("ok") is not False or not payload.get("error"):
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "chatbot input validation did not return a structured 400 JSON error"

    def role_matrix(self) -> None:
        """Exercise representative allowed/denied boundaries for every application role."""
        accounts = {
            "primary_admin": (self, "loadtest-001"),
            "supervisor": (None, "loadtest-role-supervisor"),
            "support_manager": (None, "loadtest-role-support_manager"),
            "inspector": (None, "loadtest-role-inspector"),
            "manager": (None, "loadtest-role-manager"),
            "agent": (None, "loadtest-role-agent"),
            "user": (None, "loadtest-requester"),
            "local_user": (None, "loadtest-role-local_user"),
            "guard": (None, "loadtest-role-guard"),
        }
        allow = {
            "food-groups": {"primary_admin", "support_manager", "inspector"},
            "employees": {"primary_admin", "support_manager"},
            "config": {"primary_admin"},
            "food-menu/catalog": {"primary_admin", "support_manager"},
            "traffic lookup": {"primary_admin", "supervisor", "support_manager", "inspector", "manager", "agent", "guard"},
            "food-order/my-orders": set(accounts),
            "new-ticket": {"primary_admin", "supervisor", "support_manager", "inspector", "manager", "agent", "user"},
        }
        for role, (existing, username) in accounts.items():
            client = existing or RouteSmoke(self.base, self.password, username, self.jalali_month)
            if existing is None:
                try:
                    client.login()
                except Exception as exc:  # preserve each role's authentication failure in the report
                    self.results.extend(client.results)
                    self.results.append({"name": f"role-matrix:{role}:login", "method": "POST", "path": "synthetic role account",
                                         "status": 0, "latency_ms": 0, "ok": False, "error": f"{type(exc).__name__}: {exc}"})
                    continue
            food_access_roles = {"primary_admin", "support_manager", "inspector"}
            probes = [("food-ticket page", "index.php?page=food-ticket", "html")]
            if role in food_access_roles:
                probes.extend([
                    ("food-groups", "index.php?page=food-ticket&food_api=food-groups", "json"),
                    ("employees", "index.php?page=food-ticket&food_api=employees", "json"),
                    ("config", "index.php?page=food-ticket&food_api=config", "json"),
                    ("food-menu/catalog", "index.php?page=food-ticket&food_api=food-menu/catalog", "json"),
                ])
            probes.extend([
                ("traffic lookup", "index.php?page=traffic-control&traffic_api=lookup&code=000", "json"),
                ("food-order/my-orders", "index.php?page=food-order&food_api=my-orders", "json"),
                ("new-ticket", "index.php?page=new-ticket", "html"),
            ])
            allow["food-ticket page"] = food_access_roles
            for name, path, kind in probes:
                expected_status = 200 if role in allow[name] else 403
                if kind == "json":
                    payload = client.get_json(f"role-matrix:{role}:{name}", path, expected=(expected_status,))
                    row = client.results[-1]
                    if expected_status == 200 and (not isinstance(payload, (dict, list)) or row["status"] != 200):
                        row["ok"] = False
                        row["error"] = row.get("error") or "permitted role did not receive a JSON success response"
                    if expected_status == 403 and (not isinstance(payload, dict) or not payload.get("error")):
                        row["ok"] = False
                        row["error"] = "denied role did not receive a JSON authorization error"
                else:
                    _, body, _ = client.request(f"role-matrix:{role}:{name}", path, expected=(expected_status,))
                    row = client.results[-1]
                    if expected_status == 200 and ('class="auth-layout"' in body or 'class="auth-form"' in body):
                        row["ok"] = False
                        row["error"] = "permitted role unexpectedly received the login page"
            if existing is None:
                self.results.extend(client.results)

    def inventory_api_lifecycle(self) -> None:
        token = os.environ.get("BENCHMARK_INVENTORY_TOKEN", "")
        payload = {
            "asset_tag": "CI-INVENTORY-" + os.urandom(5).hex().upper(),
            "hostname": "CI-INVENTORY-SYNTHETIC",
            "serial_number": "SYNTHETIC-" + os.urandom(4).hex().upper(),
            "computer_type": "Desktop", "manufacturer": "Synthetic", "model": "CI model",
            "operating_system": "Synthetic OS", "cpu": "Synthetic CPU", "memory_mb": 8192,
            "ip_address": "192.0.2.10", "mac_address": "02:00:00:00:00:01",
            "disks": [], "software": [],
            "hardware": {
                "processor": [{"Name": "Synthetic CPU", "NumberOfCores": 4}],
                "operating_system": [{"Caption": "Synthetic OS", "OSArchitecture": "64-bit"}],
                "bios": [{"SerialNumber": "SYNTHETIC-BIOS"}],
                "physical_disks": [{"Model": "Synthetic disk", "Size": 1024}],
                "memory_modules": [{"Capacity": 8589934592, "Speed": 3200}],
                "graphics": [{"Name": "Synthetic graphics", "AdapterRAM": 1073741824}],
                "network_adapters": [{"Name": "Synthetic adapter", "MACAddress": "02:00:00:00:00:01"}],
            },
        }
        body = json.dumps(payload).encode()
        headers = {"Content-Type": "application/json"}
        self.request("inventory API rejects missing device token", "api/inventory.php", "POST", body, headers, expected=(401,))
        if len(token) < 32:
            self.results.append({"name": "inventory API synthetic token fixture", "method": "SETUP", "path": "CI fixture",
                                 "status": 0, "latency_ms": 0, "ok": False, "error": "BENCHMARK_INVENTORY_TOKEN missing"})
            return
        invalid_headers = {**headers, "X-Inventory-Token": token}
        self.request("inventory API validates empty hardware payload", "api/inventory.php", "POST", b"{}", invalid_headers, expected=(422,))
        _, response, _ = self.request("inventory API creates synthetic hardware snapshot", "api/inventory.php", "POST", body, invalid_headers)
        try:
            created = json.loads(response)
        except json.JSONDecodeError:
            created = {}
        asset_id = int(created.get("asset_id", 0)) if isinstance(created, dict) else 0
        if not isinstance(created, dict) or not created.get("ok") or not asset_id:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "valid synthetic inventory payload was not stored"
            return
        payload["hostname"] = "CI-INVENTORY-SYNTHETIC-UPDATED"
        _, response, _ = self.request("inventory API updates existing synthetic asset idempotently", "api/inventory.php", "POST",
                                      json.dumps(payload).encode(), invalid_headers)
        try:
            updated = json.loads(response)
        except json.JSONDecodeError:
            updated = {}
        if not isinstance(updated, dict) or not updated.get("ok") or int(updated.get("asset_id", 0)) != asset_id:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "repeat inventory collection created a different asset or failed"
            return
        _, html, _ = self.request("inventory synthetic asset detail read-back", f"index.php?page=asset&id={asset_id}")
        if "CI-INVENTORY-SYNTHETIC-UPDATED" not in html:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "updated synthetic hostname was not visible in asset detail"

    @staticmethod
    def _synthetic_iranian_national_code() -> str:
        prefix = str(100000001 + int.from_bytes(os.urandom(4), "big") % 899999999)
        checksum = sum(int(prefix[i]) * (10 - i) for i in range(9)) % 11
        check_digit = checksum if checksum < 2 else 11 - checksum
        return prefix + str(check_digit)

    def traffic_control_lifecycle(self) -> None:
        national_code = self._synthetic_iranian_national_code()
        original_name = "Synthetic Traffic Visitor " + os.urandom(3).hex().upper()
        headers = {"Content-Type": "application/x-www-form-urlencoded"}
        create = {
            "csrf": self.csrf, "action": "traffic_create", "full_name": original_name,
            "national_code": national_code, "phone": "09123456789", "company": "Synthetic CI",
            "meeting_with": "Synthetic unit", "description": "Disposable end-to-end traffic test",
        }
        status, html, _ = self.request("traffic handler creates synthetic visit", "index.php?page=traffic-control", "POST",
                                       urllib.parse.urlencode(create).encode(), headers)
        create_result = self.results[-1]
        if status != 200 or 'class="auth-layout"' in html:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "traffic create handler did not complete authenticated POST"
            return
        lookup_path = "index.php?page=traffic-control&traffic_api=lookup&code=" + national_code
        found = self.get_json("traffic API reads back synthetic visit", lookup_path)
        visit_id = int(found.get("record_id", 0)) if isinstance(found, dict) else 0
        if not isinstance(found, dict) or not found.get("found") or found.get("full_name") != original_name or visit_id < 1:
            self.results[-1]["ok"] = False
            visible = re.sub(r"\s+", " ", re.sub(r"<[^>]*>", " ", html)).strip()
            self.results[-1]["error"] = ("traffic lookup did not return the synthetic row created by the handler; "
                                         f"create redirect={create_result.get('final_url', '')}; response={visible[-500:]}")
            return
        _, print_html, _ = self.request("traffic print view renders synthetic visitor (no printer invoked)",
                                        f"index.php?page=traffic-control-print&id={visit_id}")
        if "برگ ملاقات مراجعین" not in print_html or original_name not in print_html:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "synthetic print-view HTML did not render expected visitor data"

        updated_name = original_name + " updated"
        update = {**create, "action": "traffic_update", "record_id": str(visit_id), "full_name": updated_name,
                  "visit_date": date.today().isoformat(), "entry_time": "08:00", "exit_time": ""}
        status, html, _ = self.request("traffic handler updates synthetic visit and history", "index.php?page=traffic-control", "POST",
                                       urllib.parse.urlencode(update).encode(), headers)
        if status != 200 or 'class="auth-layout"' in html:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "traffic update handler did not complete authenticated POST"
            return
        updated = self.get_json("traffic API verifies synthetic visit update", lookup_path)
        if not isinstance(updated, dict) or updated.get("full_name") != updated_name:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "traffic update was not reflected by API read-back"
            return

        exit_payload = {"csrf": self.csrf, "action": "traffic_set_exit", "record_id": str(visit_id), "return_tab": "history"}
        status, html, _ = self.request("traffic handler registers synthetic exit", "index.php?page=traffic-control", "POST",
                                       urllib.parse.urlencode(exit_payload).encode(), headers)
        if status != 200 or 'class="auth-layout"' in html:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "traffic exit handler did not complete authenticated POST"
            return
        exited = self.get_json("traffic API confirms synthetic exit", lookup_path)
        if not isinstance(exited, dict) or exited.get("needs_exit_only"):
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "traffic API still reports the synthetic visitor as inside"
            return

        delete_payload = {"csrf": self.csrf, "action": "traffic_delete", "record_id": str(visit_id)}
        status, html, _ = self.request("traffic handler removes synthetic visit", "index.php?page=traffic-control", "POST",
                                       urllib.parse.urlencode(delete_payload).encode(), headers)
        if status != 200 or 'class="auth-layout"' in html:
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "traffic delete handler did not complete authenticated POST"
            return
        deleted = self.get_json("traffic API confirms synthetic visit deletion", lookup_path)
        if not isinstance(deleted, dict) or deleted.get("found"):
            self.results[-1]["ok"] = False
            self.results[-1]["error"] = "deleted synthetic traffic row remained visible"

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
        smoke.role_matrix()
        smoke.inventory_api_lifecycle()
        smoke.traffic_control_lifecycle()
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
        "scope": "authenticated app-wide page/API/selected role and write-lifecycle smoke on disposable synthetic MariaDB only",
        "synthetic_only": True,
        "account": args.username,
        "pages_tested": sum(str(row["name"]).startswith("page:") for row in smoke.results),
        "api_reads_tested": sum(row["method"] == "GET" and ("food_api=" in row["path"] or "traffic_api=" in row["path"]) for row in smoke.results),
        "role_checks_tested": sum(str(row["name"]).startswith(("requester:", "role-matrix:")) for row in smoke.results),
        "expected_authorization_rejections": sum(bool(row.get("expected_rejection")) for row in smoke.results),
        "http_error_responses": sum(int(row["status"]) >= 400 for row in smoke.results),
        "unexpected_http_error_responses": sum(int(row["status"]) >= 400 and not row.get("expected_rejection") for row in smoke.results),
        "application_error_payloads": sum(bool(row.get("application_error")) for row in smoke.results),
        "food_ticket_group_write_lifecycle_tested": all(any(row["name"] == name and row["ok"] for row in smoke.results) for name in ("food-ticket synthetic POST create group", "food-ticket absence-status for synthetic group", "food-ticket synthetic POST delete group")),
        "inventory_api_write_lifecycle_tested": all(any(row["name"] == name and row["ok"] for row in smoke.results) for name in ("inventory API creates synthetic hardware snapshot", "inventory API updates existing synthetic asset idempotently", "inventory synthetic asset detail read-back")),
        "traffic_control_write_lifecycle_tested": all(any(row["name"] == name and row["ok"] for row in smoke.results) for name in ("traffic handler creates synthetic visit", "traffic API reads back synthetic visit", "traffic print view renders synthetic visitor (no printer invoked)", "traffic handler updates synthetic visit and history", "traffic handler registers synthetic exit", "traffic handler removes synthetic visit")),

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
        "limitations": ["All nine application roles receive representative permission-boundary requests, not a complete role-by-route/action Cartesian matrix.",
                        "Inventory writes use a synthetic fixture payload; no WMI/CIM collection or real device was contacted.",
                        "Traffic, inventory, food-order, food-group, ticket, and CD/DVD flows are tested selectively; this is not every write handler or complete module workflow.",
                        "LDAP/AD, Access/ODBC, printers, physical hardware, external providers, cron/queue workers, and production/staging capacity are not exercised.",
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
             f"- Inventory agent create/update/read-back: {'passed' if report['inventory_api_write_lifecycle_tested'] else 'not passed'}; traffic create/update/exit/delete: {'passed' if report['traffic_control_write_lifecycle_tested'] else 'not passed'}",
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
