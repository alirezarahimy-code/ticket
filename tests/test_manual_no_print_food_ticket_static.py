from __future__ import annotations

import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
NAME = "ticket-update-1.62.35-profile-and-attendance-reconciliation-20261027.zip"
PACKAGE = ROOT / "downloads" / NAME


def test_wrong_emergency_implementation_is_removed_from_root_sources():
    order = (ROOT / "food-order.php").read_text(encoding="utf-8")
    ticket = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
    js = (ROOT / "food-ticket-web/assets/food-menu.js").read_text(encoding="utf-8")
    assert "food_order_manual_issue_ticket_no_print" not in order
    assert "food-menu/emergency/issue-ticket" not in order
    assert "food-menu/emergency/issue-ticket" not in ticket
    assert "issue-manual-ticket" not in js
    assert "ثبت فیش دستی بدون چاپ" not in js


def test_existing_settings_manual_attendance_bug_fix_package():
    assert PACKAGE.is_file()
    assert not (ROOT / "downloads/ticket-update-1.62.29-manual-ticket-without-physical-print-20261027.zip").exists()
    assert not (ROOT / "downloads/ticket-update-1.62.30-admin-settings-manual-ticket-20261027.zip").exists()
    assert not (ROOT / "downloads/ticket-update-1.62.31-manual-attendance-existing-no-food-conversion-20261027.zip").exists()
    assert not (ROOT / "downloads/ticket-update-1.62.32-manual-ticket-counts-as-delivered-20261027.zip").exists()
    assert not (ROOT / "downloads/ticket-update-1.62.33-manual-delivery-and-technical-profile-20261027.zip").exists()
    assert not (ROOT / "downloads/ticket-update-1.62.34-attendance-delivery-and-system-profile-20261027.zip").exists()
    expected = {
        "VERSION",
        "food-order.php",
        "food-ticket.php",
        "food-ticket-groups.php",
        "food-ticket-web/assets/food-manual-attendance.js",
        "food-ticket-web/assets/food-menu.js",
        "food-ticket-web/index.html",
        "permissions.php",
        "index.php",
        "actions/settings-actions.php",
        "assets/style.css",
        "asset-profile.php",
        "asset-inventory-page.php",
    }
    with ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.35"
        assert manifest["requires_version"] == "1.62.25"
        assert len(manifest["files"]) == len(set(manifest["files"]))
        assert manifest["migrations"] == ["011-manual-attendance-delivery.sql", "012-manual-attendance-clears-absences.sql"]
        migration = archive.read("migrations/011-manual-attendance-delivery.sql").decode("utf-8")
        assert "source_uid = 'manual-attendance'" in migration
        assert "event_type = 'printed'" in migration and "print_status = 'printed'" in migration
        assert "delivery_status = 'delivered'" in migration
        absence_migration = archive.read("migrations/012-manual-attendance-clears-absences.sql").decode("utf-8")
        assert "food_ticket_daily_absence" in absence_migration
        assert "event.source_uid = 'manual-attendance'" in absence_migration
        assert "event.event_type IN ('printed', 'no_food')" in absence_migration
        assert set(manifest["files"]) == expected
        assert {n[6:] for n in archive.namelist() if n.startswith("files/")} == expected
        assert archive.read("files/VERSION").decode().strip() == "1.62.35"

        groups = archive.read("files/food-ticket-groups.php").decode("utf-8")
        panel = archive.read("files/food-ticket-web/assets/food-manual-attendance.js").decode("utf-8")
        host = archive.read("files/food-ticket.php").decode("utf-8")
        panel_index = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        main_index = archive.read("files/index.php").decode("utf-8")
        settings_actions = archive.read("files/actions/settings-actions.php").decode("utf-8")
        permissions = archive.read("files/permissions.php").decode("utf-8")
        profile = archive.read("files/asset-profile.php").decode("utf-8")
        inventory_component = archive.read("files/asset-inventory-page.php").decode("utf-8")
        app_index = archive.read("files/index.php").decode("utf-8")

        # This cumulative package also carries the current system-profile form modules.
        assert "function asset_profile_render_sections" in profile
        assert "function asset_profile_fill_from_asset" in profile
        assert "function asset_profile_collection_status_summary" in profile
        assert "اجازهٔ CREATE/ALTER" in profile
        assert "asset_profile_render_sections($profile)" in app_index
        assert "asset_profile_fill_from_asset(asset_profile_fetch($inventoryId), $asset)" in app_index
        assert "inventory_render_assets_content($assetRows, $assetSearch, $networkFilter, $canImportAssets, $canPingAssets, $canExtractAssets, $canConfigureNetworkScan, $networkScanEnabled, $canSelectAssets, $onlineCount, $collectedCount);" in app_index
        assert "index.php?page=inventory&id=" in inventory_component

        # This is a fix to the pre-existing panel settings flow, not a duplicate main-system form.
        assert "FOOD_TICKET_CAN_MANUAL_TICKET" in host
        assert "'code' => 'food.manual_ticket'" in permissions
        assert "const isReplaceableNoTicket" in panel
        assert "u.today_record.event_type === 'no_food'" in panel
        assert "S.manualWithTicket && canIssueManualTicket()" in panel
        assert "فقط در حالت «صدور فیش»" not in main_index
        assert 'id="food-manual-ticket"' not in main_index
        assert "save_manual_food_ticket" not in settings_actions
        assert "food-manual-attendance.js?v=5" in panel_index

        # The POST is gated server-side and upgrades the existing no_food event in place.
        assert "food_ticket_is_primary_admin($actor)" in groups
        assert "user_can($actor, 'food.manual_ticket')" in groups
        assert "$replaceNoFood" in groups and "converted_from_no_food" in groups
        assert "WHERE id = ? AND event_type = \"no_food\"" in groups
        assert "event_type = \"printed\", print_status = ?" in groups
        assert "$physicalPrint ? 'pending' : 'printed'" in groups
        assert "فیش و تحویل غذا در سامانه «چاپ‌شده/تحویل‌شده» ثبت شد؛ چاپ فیزیکی انجام نشد." in groups
        save = groups[groups.index("function food_ticket_api_manual_attendance_save("):groups.index("function food_ticket_api_manual_attendance_save_guest(")]
        assert "if ($withTicket && !$physicalPrint && function_exists('food_ticket_mark_delivered_on_print'))" in save
        assert "'delivery_status' => ($withTicket && !$physicalPrint) ? 'delivered' : 'pending'" in save
        assert "food_ticket_mark_delivered_on_print($id)" in (ROOT / "food-ticket-runtime.php").read_text(encoding="utf-8")
        assert "food_ticket_manual_attendance_clear_absence($userId, $targetDate, $actor, $reason)" in save
        assert "'absence_cleared' => $absenceCleared" in save
        assert "در سامانه چاپ‌شده؛ چاپ فیزیکی انجام نشد" in panel
        assert "1.37.32" in host and "PANEL_VERSION='1.37.32'" in panel_index

        for name in expected:
            if name.endswith(".php") or name.endswith(".js"):
                content = archive.read("files/" + name).decode("utf-8", "ignore")
                assert "food-menu/emergency/issue-ticket" not in content

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    assert digest in (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
    page = (ROOT / "downloads" / "index.html").read_text(encoding="utf-8")
    current = "ticket-update-1.62.36-manual-ticket-record-reconciliation-20261027.zip"
    assert current in page and f"{current}.sha256" in page
    assert "۱.۶۲.۳۴" in page and "۱.۶۲.۳۵" in page and "۱.۶۲.۳۶" in page
    assert "۱.۶۲.۳۵ به‌تنهایی نیز مدرک اصلاح رکوردهای زنده نیست" in page
