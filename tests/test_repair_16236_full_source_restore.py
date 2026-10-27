from __future__ import annotations

import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
ZIP_PATH = ROOT / "downloads/ticket-repair-1.62.36-full-source-restore-20261027.zip"
SOURCE_ARCHIVES = [
    ROOT / "downloads/ticket-update-1.62.24-cumulative-20261027.zip",
    ROOT / "downloads/ticket-update-1.62.25-system-health-accuracy-20261027.zip",
    ROOT / "downloads/ticket-update-1.62.26-asset-profile-form-fix-20261027.zip",
    ROOT / "downloads/ticket-update-1.62.27-food-log-levels-20261027.zip",
    ROOT / "downloads/ticket-update-1.62.28-technical-profile-form-refresh-20261027.zip",
    ROOT / "downloads/ticket-update-1.62.35-profile-and-attendance-reconciliation-20261027.zip",
    ROOT / "downloads/ticket-update-1.62.36-manual-ticket-record-reconciliation-20261027.zip",
]
EXPECTED_MIGRATIONS = [
    "009-traffic-destination-multi-approver.sql",
    "010-org-liaison-units.sql",
    "011-manual-attendance-delivery.sql",
    "012-manual-attendance-clears-absences.sql",
    "013-manual-attendance-record-backfill.sql",
]
PRESERVED_MAIN_FEATURES = {
    "index.php": ["$canExtractLocalServer"],
    "asset-inventory-page.php": ["$canExtractLocalServer", "domain_scan_test_selected"],
    "food-ticket-templates.php": ["group_title", "food_ticket_tpl_group_template"],
    "asset-profile.php": ["function asset_profile_fill_from_asset"],
}


def reconstructed_release_files() -> dict[str, bytes]:
    result: dict[str, bytes] = {}
    for source in SOURCE_ARCHIVES:
        with ZipFile(source) as archive:
            for name in archive.namelist():
                if name.startswith("files/"):
                    result[name.removeprefix("files/")] = archive.read(name)
    return result


def reconstructed_migrations() -> dict[str, bytes]:
    result: dict[str, bytes] = {}
    for source in (SOURCE_ARCHIVES[0], SOURCE_ARCHIVES[-2], SOURCE_ARCHIVES[-1]):
        with ZipFile(source) as archive:
            for name in archive.namelist():
                if name.startswith("migrations/") and name.endswith(".sql"):
                    result[Path(name).name] = archive.read(name)
    return result


def test_repair_package_rebuilds_the_release_overlay_not_the_old_snapshot():
    expected_files = reconstructed_release_files()
    assert len(expected_files) == 71
    with ZipFile(ZIP_PATH) as archive:
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.36"
        assert manifest["requires_version"] == "1.62.36"
        assert manifest["files"] == sorted(expected_files)
        assert archive.testzip() is None
        assert archive.read("files/VERSION").decode().strip() == "1.62.36"
        for name, expected in expected_files.items():
            actual = archive.read(f"files/{name}")
            if name in PRESERVED_MAIN_FEATURES:
                for marker in PRESERVED_MAIN_FEATURES[name]:
                    assert marker.encode() in actual, (name, marker)
            else:
                assert actual == expected, name
        expected_entries = {
            "manifest.json",
            *(f"files/{name}" for name in expected_files),
            *(f"migrations/{name}" for name in EXPECTED_MIGRATIONS),
        }
        assert set(archive.namelist()) == expected_entries
        assert "files/food-ticket-order-source.php" not in archive.namelist()


def test_repair_package_carries_all_idempotent_migrations_in_order():
    expected = reconstructed_migrations()
    assert set(expected) == set(EXPECTED_MIGRATIONS)
    with ZipFile(ZIP_PATH) as archive:
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["migrations"] == EXPECTED_MIGRATIONS
        for name, content in expected.items():
            assert archive.read(f"migrations/{name}") == content, name


def test_restored_package_has_the_missing_ui_and_server_flows():
    with ZipFile(ZIP_PATH) as archive:
        food_html = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        food_order = archive.read("files/food-order.php").decode("utf-8")
        groups = archive.read("files/food-ticket-groups.php").decode("utf-8")
        index = archive.read("files/index.php").decode("utf-8")
        ticket = archive.read("files/food-ticket.php").decode("utf-8")
        menu_js = archive.read("files/assets/food-menu.js").decode("utf-8")

    for marker in (
        "food-capitalio-dashboard-r42.js",
        "food-ticket-global-theme-r42.css",
        "food-manual-attendance.js",
        "emergency-window-settings-form",
    ):
        assert marker in food_html
    for marker in (
        "food_order_bulk_api_handle",
        "food_order_selected_orders_range",
        "food_order_export_selected_orders_range",
        "food_order_group_orders",
        "food_order_emergency_window_grant",
    ):
        assert marker in food_order
    for marker in (
        "food_ticket_api_manual_attendance",
        "food_ticket_api_manual_attendance_save_many",
        "food_ticket_manual_attendance_clear_absence",
    ):
        assert marker in groups
    assert "inspection_food_reminder_markup" in index
    assert "ticket_org_manager_rank_sql" in index
    assert "food_ticket_render_safe_replace" in ticket
    assert "renderBulkOperations" in menu_js
    assert "searchSelectedReportEmployees" in menu_js


def test_download_pages_point_to_corrected_package_and_avoid_false_no_migration_claims():
    archive_page = (ROOT / "downloads/index.html").read_text()
    direct_page = (ROOT / "downloads/ticket-repair-1.62.36-full-source-restore-20261027.html").read_text()
    for page in (archive_page, direct_page):
        assert "ticket-repair-1.62.36-full-source-restore-20261027.zip" in page
        assert "مهاجرت" in page
        assert "از snapshot ناقص" in page or "snapshot ناقص" in page
    assert "هیچ migration یا تغییر پایگاه‌داده ندارد" not in archive_page
    assert "فقط چند فایل مربوط به ثبت دستی و گزارش داده را داشت" not in direct_page


def test_repo_snapshot_matches_the_reconstructed_16236_payload():
    assert (ROOT / "VERSION").read_text().strip() == "1.62.36"
    with ZipFile(ZIP_PATH) as archive:
        manifest = json.loads(archive.read("manifest.json"))
        for name in manifest["files"]:
            assert (ROOT / name).read_bytes() == archive.read(f"files/{name}"), name
        for name in manifest["migrations"]:
            assert (ROOT / "migrations" / name).read_bytes() == archive.read(f"migrations/{name}"), name
        for name, markers in PRESERVED_MAIN_FEATURES.items():
            payload = archive.read(f"files/{name}")
            for marker in markers:
                assert marker.encode() in payload, (name, marker)
    report = (ROOT / "docs/RELEASE-1.62.36-FA.md").read_text(encoding="utf-8")
    assert "گزارش رسمی محتویات نسخهٔ ۱.۶۲.۳۶" in report
    assert "فهرست دقیق ۷۱ مسیر" in report


def test_restore_package_checksum_matches():
    expected = (ROOT / "downloads/ticket-repair-1.62.36-full-source-restore-20261027.zip.sha256").read_text().split()[0]
    actual = hashlib.sha256(ZIP_PATH.read_bytes()).hexdigest()
    assert actual == expected
