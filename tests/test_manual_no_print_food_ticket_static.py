from __future__ import annotations

import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
ORDER = (ROOT / "food-order.php").read_text(encoding="utf-8")
TICKET = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
UI = (ROOT / "food-ticket-web/assets/food-menu.js").read_text(encoding="utf-8")
PERMISSIONS = (ROOT / "permissions.php").read_text(encoding="utf-8")
NAME = "ticket-update-1.62.30-admin-settings-manual-ticket-20261027.zip"
PACKAGE = ROOT / "downloads" / NAME


def test_manual_issue_is_strictly_admin_only_and_audited():
    start = ORDER.index("function food_order_manual_issue_ticket_no_print")
    end = ORDER.index("/** مسیرهای «سفارش اضطراری»", start)
    fn = ORDER[start:end]
    assert "user_is_primary_admin($actor)" in fn
    assert "user_can($actor, 'food.manual_ticket')" in fn
    assert "o.status = 'active'" in fn and "o.food_date = ?" in fn
    assert "hash_equals($national, $orderNational)" in fn
    assert "علت ثبت دستی باید بین ۵ تا ۵۰۰ نویسه باشد" in fn
    assert "'event_type' => 'printed'" in fn and "'print_status' => 'printed'" in fn
    assert "issued_without_physical_print" in fn and "physical_print' => false" in fn
    assert "food_ticket_enqueue_print" not in fn
    assert "food_ticket_send_to_printer" not in fn
    assert "SET delivery_status" not in fn
    assert "food_order_audit" in fn


def test_emergency_menu_exposure_was_removed_and_settings_is_the_only_ui():
    assert "food-menu/emergency/issue-ticket" not in TICKET
    assert "issue-manual-ticket" not in UI
    assert "ثبت فیش دستی" not in UI
    assert "'code' => 'food.manual_ticket'" in PERMISSIONS
    assert "user_is_primary_admin($user)" in PERMISSIONS
    assert "food.manual_ticket" in PERMISSIONS
    assert "if ($role !== 'primary_admin')" in PERMISSIONS
    assert "1.37.15" in (ROOT / "food-ticket.php").read_text(encoding="utf-8")


def test_admin_settings_hotfix_package_and_role_controls():
    assert PACKAGE.is_file()
    assert not (ROOT / "downloads/ticket-update-1.62.29-manual-ticket-without-physical-print-20261027.zip").exists()
    expected = {
        "VERSION",
        "food-order.php",
        "food-ticket.php",
        "food-ticket-web/assets/food-menu.js",
        "food-ticket-web/index.html",
        "permissions.php",
        "index.php",
        "actions/settings-actions.php",
        "assets/style.css",
    }
    with ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.30"
        assert manifest["requires_version"] == "1.62.25"
        assert manifest["migrations"] == []
        assert set(manifest["files"]) == expected
        assert {n[6:] for n in archive.namelist() if n.startswith("files/")} == expected
        assert archive.read("files/VERSION").decode().strip() == "1.62.30"
        page = archive.read("files/index.php").decode("utf-8")
        actions = archive.read("files/actions/settings-actions.php").decode("utf-8")
        js = archive.read("files/food-ticket-web/assets/food-menu.js").decode("utf-8")
        order = archive.read("files/food-order.php").decode("utf-8")
        ticket = archive.read("files/food-ticket.php").decode("utf-8")
        assert "food-menu/emergency/issue-ticket" not in order
        assert "food-menu/emergency/issue-ticket" not in ticket
        assert "id=\"food-manual-ticket\"" in page
        assert "user_is_primary_admin($user) && user_can($user, 'food.manual_ticket')" in page
        assert "name=\"action\" value=\"save_manual_food_ticket\"" in page
        assert "if ($action === 'save_manual_food_ticket')" in actions
        assert "require_primary_admin()" in actions
        assert "food_order_manual_issue_ticket_no_print" in actions
        assert "issue-manual-ticket" not in js

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    assert digest in (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
    page = (ROOT / "downloads" / "index.html").read_text(encoding="utf-8")
    assert NAME in page and f"{NAME}.sha256" in page
    assert "۱.۶۲.۲۹" in page and "۱.۶۲.۳۰" in page
