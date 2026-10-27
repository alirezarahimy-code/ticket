from __future__ import annotations

import json
import re
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
ORDER = (ROOT / "food-order.php").read_text(encoding="utf-8")
TICKET = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
UI = (ROOT / "food-ticket-web/assets/food-menu.js").read_text(encoding="utf-8")
PANEL = (ROOT / "food-ticket-web/index.html").read_text(encoding="utf-8")
PACKAGE = ROOT / "downloads/ticket-update-1.62.29-manual-ticket-without-physical-print-20261027.zip"


def test_manual_issue_records_printed_event_without_physical_print():
    start = ORDER.index("function food_order_emergency_issue_ticket_no_print")
    end = ORDER.index("/** مسیرهای «سفارش اضطراری»", start)
    fn = ORDER[start:end]
    assert "food_order_user_can($actor, 'food.emergency')" in fn
    assert "o.status = 'active'" in fn and "o.food_date = ?" in fn
    assert "food_order_verify_employee_national_code" in fn
    assert "'ticket_key' => $ticketKey" in fn
    assert "'event_type' => 'printed'" in fn and "'print_status' => 'printed'" in fn
    assert "UPDATE food_ticket_events SET event_type = \"printed\", print_status = \"printed\"" in fn
    assert "issued_without_physical_print" in fn and "physical_print' => false" in fn
    assert "food_ticket_enqueue_print" not in fn
    assert "food_ticket_send_to_printer" not in fn
    assert "SET delivery_status" not in fn
    assert "food_order_log" in fn and "food_order_audit" in fn


def test_manual_issue_api_is_permission_gated_and_ui_is_explicit():
    assert "'food-menu/emergency/issue-ticket' => 'food.emergency'" in TICKET
    assert "'food-menu/emergency/issue-ticket'" in ORDER
    assert "food_order_emergency_issue_ticket_no_print" in ORDER
    assert "ثبت فیش دستی بدون چاپ" in UI
    assert "food-menu/emergency/issue-ticket" in UI
    assert "فیش این سفارش در سامانه" in UI
    assert "const PANEL_VERSION='1.37.14'" in PANEL
    assert 'assets/food-menu.js?v=19' in PANEL


def test_manual_issue_hotfix_manifest_is_complete():
    assert PACKAGE.is_file()
    expected = {
        "VERSION",
        "food-order.php",
        "food-ticket.php",
        "food-ticket-web/assets/food-menu.js",
        "food-ticket-web/index.html",
    }
    with ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.29"
        assert manifest["requires_version"] == "1.62.25"
        assert manifest["migrations"] == []
        assert set(manifest["files"]) == expected
        assert {name[6:] for name in archive.namelist() if name.startswith("files/")} == expected
        assert archive.read("files/VERSION").decode().strip() == "1.62.29"

    page = (ROOT / "downloads/index.html").read_text(encoding="utf-8")
    name = PACKAGE.name
    assert name in page and f"{name}.sha256" in page
    assert "۱.۶۲.۲۹" in page
