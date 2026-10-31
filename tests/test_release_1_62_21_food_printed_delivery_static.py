from __future__ import annotations

import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.21-food-printed-delivery-20261026.zip"
PACKAGE = ROOT / "downloads" / PACKAGE_NAME


def test_release_package_is_cumulative_from_16220_and_integral():
    assert PACKAGE.is_file()
    with zipfile.ZipFile(PACKAGE) as archive:
        names = archive.namelist()
        assert len(names) == len(set(names))
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.21"
        assert manifest["name"] == "release-162-21-food-printed-delivery"
        assert manifest["requires_version"] == "1.61.9"
        assert manifest["migrations"] == [
            "009-traffic-destination-multi-approver.sql",
            "010-org-liaison-units.sql",
        ]
        assert len(manifest["files"]) == 66
        assert len(set(manifest["files"])) == 66
        for rel in manifest["files"]:
            assert f"files/{rel}" in names, rel
        assert archive.read("files/VERSION").decode().strip() == "1.62.21"
        for migration in manifest["migrations"]:
            assert f"migrations/{migration}" in names
            assert f"files/migrations/{migration}" in names

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    sidecar = (ROOT / "downloads" / f"{PACKAGE_NAME}.sha256").read_text(encoding="utf-8")
    assert digest in sidecar


def test_api_reads_event_records_with_the_requested_status_rules():
    with zipfile.ZipFile(PACKAGE) as archive:
        php = archive.read("files/food-order.php").decode("utf-8")
        ticket = archive.read("files/food-ticket.php").decode("utf-8")
    start = php.index("function food_order_printed_delivery_report(")
    end = php.index("function food_order_export_printed_delivery_report(", start)
    report = php[start:end]
    assert "FROM food_ticket_events e" in report
    assert "e.punch_date BETWEEN ? AND ?" in report
    assert "e.print_status = 'printed'" in report
    assert "e.event_type = 'printed' AND e.user_id IS NOT NULL AND e.delivery_status = 'delivered'" in report
    assert "OR e.event_type = 'guest'" in report
    assert "food_calendar_items" in report and "menuMap" in report
    assert "'employee_count' => count($employeeIds)" in report
    assert "'guest_count' => $guestCount" in report
    assert "'food-menu/printed-delivery'" in ticket
    assert "'food-menu/printed-delivery-export'" in ticket
    assert "food.order_close" in ticket


def test_report_is_after_statistics_with_selectable_fields_and_menu_view():
    with zipfile.ZipFile(PACKAGE) as archive:
        js = archive.read("files/food-ticket-web/assets/food-menu.js").decode("utf-8")
        page = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        css = archive.read("files/food-ticket-web/assets/food-menu.css").decode("utf-8")
    stats_start = js.index("function renderStatistics()")
    stats_end = js.index("function bulkOperationTitle(", stats_start)
    statistics = js[stats_start:stats_end]
    assert "+ renderPrintedDeliveryReport()" in statistics
    assert "loadPrintedDeliveryReport(from, to)" in js
    assert "data-fm-delivery-field" in js
    assert "data-fm-delivery-sort" in js
    assert "data-view=\"menu\"" in js
    assert "deliveredReportByMenuHtml" in js
    assert "delivered-report-export" in js and "delivered-report-print" in js
    assert "تأیید مستقل دریافت غذا نیست" in js
    assert "assets/food-menu.css?v=34" in page
    assert "assets/food-menu.js?v=33" in page
    assert "const PANEL_VERSION='1.37.26'" in page
    assert "fm-print-delivered-report" in css


def test_packaged_panel_refs_resolve_and_assets_are_mirrored():
    with zipfile.ZipFile(PACKAGE) as archive:
        names = set(archive.namelist())
        html = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        css = archive.read("files/food-ticket-web/assets/food-menu.css")
        js = archive.read("files/food-ticket-web/assets/food-menu.js")
        assert archive.read("files/assets/food-menu.css") == css
        assert archive.read("files/assets/food-menu.js") == js
        for ref in set(re.findall(r'(?:src|href)="(assets/[^"?]+)', html)):
            assert f"files/food-ticket-web/{ref}" in names, ref
