from __future__ import annotations

import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
DOWNLOADS = ROOT / "downloads"
NAME = "ticket-update-1.63-to-1.63.2-r21-assets-tenter-20261106.zip"
PACKAGE = DOWNLOADS / NAME
# The original r21 archive is immutable; these files were refreshed by r22.
REFRESHED_AFTER_R21 = {
    # Files refreshed while advancing the cumulative package through r22/r23.
    "food-reconciliation.php",
    "assets/food-menu.js",
    "food-ticket-web/assets/food-menu.js",
    "tools/test_food_reconciliation_disposition_offline.php",
    "food-ticket.php",
    "food-ticket-release-notes.php",
    "food-ticket-web/index.html",
}
REQUIRED_FILES = {
    "VERSION",
    "food-ticket.php",
    "food-ticket-release-notes.php",
    "food-reconciliation.php",
    "assets/food-menu.js",
    "food-ticket-web/assets/food-menu.js",
    "tools/test_food_reconciliation_disposition_offline.php",
    "tools/test_food_aggregate_report_offline.php",
    "food-ticket-web/index.html",
    "help-guide.php",
    "chatbot.php",
    "lib/release-notes.php",
    "lib/asset-dedupe.php",
    "tools/asset_dedupe.php",
    "tools/repair_source_rows.php",
    "tools/test_source_delete_offline.php",
    "tools/test_source_time_delete_offline.php",
    "tools/test_asset_dedupe_offline.php",
    "tools/test_asset_names_offline.php",
    "tests/test_asset_inventory_flow_sqlite.php",
    "modules/request-center/Pages/create.php",
    "tools/collect_server_inventory.ps1",
    "tools/collect_via_smb.ps1",
    "tools/diag_extract.php",
    "tools/RUN-DIAG-EXTRACT.bat",
    "tools/GET-DIAG-EXTRACT.bat",
    "docs/GUIDE-FA-install-1.63.2.md",
    "CHANGE-REPORT-FA-1.63.2-r21-assets-tenter.md",
}


def test_1632_r21_is_a_valid_cumulative_installable_package():
    assert PACKAGE.is_file()
    with ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        entries = archive.namelist()
        assert len(entries) == len(set(entries))
        manifest = json.loads(archive.read("manifest.json"))
        files = manifest["files"]
        assert manifest["version"] == "1.63.2"
        assert manifest["requires_version"] == "1.63"
        assert manifest["migrations"] == []
        assert manifest["release_status"] == "INSTALLABLE_OFFLINE_VERIFIED"
        assert files == sorted(set(files))
        assert REQUIRED_FILES <= set(files)
        assert archive.read("files/VERSION").decode("ascii").strip() == manifest["version"]
        assert set(manifest["sha256"]) == set(files)

        actual_files = {
            entry.removeprefix("files/")
            for entry in entries
            if entry.startswith("files/") and not entry.endswith("/")
        }
        assert actual_files == set(files)
        for relative in files:
            data = archive.read("files/" + relative)
            assert hashlib.sha256(data).hexdigest() == manifest["sha256"][relative]
            if relative == "VERSION" or relative in REFRESHED_AFTER_R21:
                continue
            source = ROOT / relative
            if not source.is_file():
                source = DOWNLOADS / Path(relative).name
            assert source.is_file(), relative
            assert data == source.read_bytes(), f"package drift: {relative}"

        source_delete = archive.read("files/tools/test_source_delete_offline.php").decode("utf-8")
        source_time = archive.read("files/tools/test_source_time_delete_offline.php").decode("utf-8")
        food = archive.read("files/food-ticket.php").decode("utf-8")
        reconciliation = archive.read("files/food-reconciliation.php").decode("utf-8")
        food_release_notes = archive.read("files/food-ticket-release-notes.php").decode("utf-8")
        panel = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        menu_js = archive.read("files/food-ticket-web/assets/food-menu.js").decode("utf-8")
        guide = archive.read("files/help-guide.php").decode("utf-8")
        chatbot = archive.read("files/chatbot.php").decode("utf-8")
        asset_names = archive.read("files/asset-names.php").decode("utf-8")
        assert "food_ticket_source_minimal_where" in food
        assert "'absent-report' => $fpCan('food.reports')" in food
        assert "PANEL_VERSION='1.37.55'" in panel and "function absentReportView()" in panel
        assert "food_reconciliation_expected_remaining_assert" in reconciliation
        assert "if ($sum !== $remaining)" in reconciliation
        assert "leftover_expected_remaining: m.remaining" in menu_js and "sum !== remaining" in menu_js
        assert "'1.63.2' =>" in food_release_notes and "گزارش تجمیعی غایبین" in food_release_notes
        assert "food_ticket_aggregate_identity_sets" in food
        assert "$identities[$key] = ['date' => $date, 'pc' => $personnel" in food
        assert "$identities[$key] = ['date' => $date, 'nat' => $national" not in food
        assert "$expected = (int) ($diagnostics['expected_absent_count'] ?? 0);" in food
        assert "گزارش مستقل غایبین ناهار" in guide
        assert "food_absent_lunch_report" in chatbot
        assert "food_ticket_source_time_where" in food
        assert "fallback ساعت بازهٔ یک‌ثانیه‌ای" in source_delete
        assert "ثانیهٔ آخر تا نیمه‌شب روز بعد" in source_time
        assert "asset_identity_serial_value" in asset_names
        assert "asset_identity_mac_value" in asset_names

        forbidden = ("config.php", ".env", "storage/", "uploads/")
        assert not any(path == forbidden[0] or path == forbidden[1] or path.startswith(forbidden[2:]) for path in files)

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    sidecar = (DOWNLOADS / f"{NAME}.sha256").read_text(encoding="ascii")
    assert digest in sidecar

    # r21 remains a verifiable historical artifact, but r22 is now the current download.
    with ZipFile(PACKAGE) as archive:
        archived_report = archive.read("files/CHANGE-REPORT-FA-1.63.2-r21-assets-tenter.md").decode("utf-8")
    assert "۱.۶۳.۲-r21" in archived_report
    assert "مهاجرت دیتابیس: **ندارد**" in archived_report
