from __future__ import annotations

import hashlib
import json
import re
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
NAME = "ticket-update-1.62.24-cumulative-20261027.zip"
PACKAGE = ROOT / "downloads" / NAME


def test_cumulative_package_is_valid_and_targets_16220():
    assert PACKAGE.is_file()
    with ZipFile(PACKAGE) as archive:
        names = archive.namelist()
        assert len(names) == len(set(names))
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.24"
        assert manifest["requires_version"] == "1.62.20"
        assert manifest["name"] == "release-162-24-cumulative-from-162-20"
        assert archive.read("files/VERSION").decode().strip() == "1.62.24"
        listed = set(manifest["files"])
        packaged = {name[6:] for name in names if name.startswith("files/") and not name.endswith("/")}
        assert listed == packaged
        assert len(listed) == 67
        assert manifest["migrations"] == [
            "009-traffic-destination-multi-approver.sql",
            "010-org-liaison-units.sql",
        ]
        for migration in manifest["migrations"]:
            assert f"migrations/{migration}" in names
            assert f"files/migrations/{migration}" in names
            assert "ADD COLUMN IF NOT EXISTS" in archive.read(f"migrations/{migration}").decode()

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    sidecar = (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
    assert digest in sidecar


def test_cumulative_bundle_contains_the_complete_16224_file_set():
    with ZipFile(PACKAGE) as archive:
        names = set(archive.namelist())
        manifest = json.loads(archive.read("manifest.json"))
        for path in [
            "food-order.php", "food-ticket.php", "index.php", "system-health.php",
            "assets/food-menu.js", "assets/food-menu.css", "food-ticket-web/index.html",
            "food-ticket-web/assets/food-menu.js", "food-ticket-web/assets/food-menu.css",
        ]:
            assert path in manifest["files"]
            assert f"files/{path}" in names
        assert archive.read("files/assets/food-menu.js") == archive.read("files/food-ticket-web/assets/food-menu.js")
        assert archive.read("files/assets/food-menu.css") == archive.read("files/food-ticket-web/assets/food-menu.css")


def test_cumulative_bundle_contains_16221_food_report_and_route_permissions():
    with ZipFile(PACKAGE) as archive:
        api = archive.read("files/food-order.php").decode("utf-8")
        ticket = archive.read("files/food-ticket.php").decode("utf-8")
        menu = archive.read("files/assets/food-menu.js").decode("utf-8")
        panel = archive.read("files/food-ticket-web/assets/food-menu.js").decode("utf-8")
        html = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        css = archive.read("files/assets/food-menu.css")
        panel_css = archive.read("files/food-ticket-web/assets/food-menu.css")

    report_start = api.index("function food_order_printed_delivery_report(")
    report_end = api.index("function food_order_export_printed_delivery_report(", report_start)
    report = api[report_start:report_end]
    assert "FROM food_ticket_events e" in report
    assert "e.print_status = 'printed'" in report
    assert "e.event_type = 'printed' AND e.user_id IS NOT NULL AND e.delivery_status = 'delivered'" in report
    assert "OR e.event_type = 'guest'" in report
    assert "food-menu/printed-delivery" in ticket
    assert "food-menu/printed-delivery-export" in ticket
    assert "food.order_close" in ticket
    assert "function renderPrintedDeliveryReport()" in menu
    assert "data-fm-delivery-field" in menu
    assert "data-fm-delivery-sort" in menu
    assert "نمایش بر اساس منو" in menu
    assert "delivered-report-print" in menu and "delivered-report-export" in menu
    assert "تأیید مستقل دریافت غذا نیست" in menu
    assert "function renderPrintedDeliveryReport()" in panel
    assert menu == panel
    assert css == panel_css
    assert "fm-print-delivered-report" in css.decode("utf-8")
    assert "assets/food-menu.js" in html


def test_cumulative_bundle_contains_16222_through_16224_system_log_fixes():
    with ZipFile(PACKAGE) as archive:
        health = archive.read("files/system-health.php").decode("utf-8")
        index = archive.read("files/index.php").decode("utf-8")
        assert "system_health_export_safe_xlsx" in health
        assert "system_health_humanize_event" in health
        assert "خلاصهٔ قابل‌فهم" in health
        assert "export=safe" in health
        assert "system-health&amp;export=safe" in index
        assert "دانلود گزارش خوانا و امن (Excel)" in index
        assert "شرح رخداد" in index


def test_update_manifest_paths_are_safe_for_the_app_updater():
    with ZipFile(PACKAGE) as archive:
        for name in archive.namelist():
            assert ".." not in name
            assert "\\" not in name
            assert not name.startswith("/")
            assert re.match(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$", name)


def test_download_page_points_to_the_cumulative_archive_and_hash():
    page = (ROOT / "downloads" / "index.html").read_text(encoding="utf-8")
    assert NAME in page
    assert f"{NAME}.sha256" in page
    assert "۱.۶۲.۲۰" in page and "۱.۶۲.۲۴" in page
