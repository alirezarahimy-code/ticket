from __future__ import annotations

import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

from download_page_helpers import check_download_page_lists_only_latest_package

ROOT = Path(__file__).resolve().parents[1]
DOWNLOADS = ROOT / "downloads"
NAME = "ticket-update-1.63-to-1.63.2-r24-global-notice-fix-20261106.zip"
PACKAGE = DOWNLOADS / NAME
REPORT = "CHANGE-REPORT-FA-1.63.2-r24-global-notice-fix.md"
# r24 is cumulative from r23; only this patch refreshes the listed source files.
REFRESHED_IN_R24 = {
    "food-ticket.php",
    "food-ticket-release-notes.php",
    "food-ticket-web/index.html",
    "tests/test_selftest_panel_render.js",
    "tests/js/food-absent-report.test.js",
}
REQUIRED_FILES = {
    "VERSION",
    "food-ticket.php",
    "food-ticket-release-notes.php",
    "food-reconciliation.php",
    "food-ticket-web/index.html",
    "food-ticket-web/assets/food-menu.js",
    "assets/food-menu.js",
    "tests/test_selftest_panel_render.js",
    "tests/js/food-absent-report.test.js",
    "tests/test_food_absent_report_static.py",
    "tests/test_food_reconciliation_audit_static.py",
    "tools/test_food_reconciliation_disposition_offline.php",
    "tools/test_manual_guest_delivery_offline.php",
    "tools/test_food_aggregate_report_offline.php",
    REPORT,
}


def test_1632_r24_is_a_valid_cumulative_package_with_global_notice_fix():
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
            if relative in REFRESHED_IN_R24:
                source = ROOT / relative
            elif relative == REPORT:
                source = DOWNLOADS / REPORT
            else:
                source = ROOT / relative
                if not source.is_file():
                    source = DOWNLOADS / Path(relative).name
            assert source.is_file(), relative
            assert data == source.read_bytes(), f"package drift: {relative}"

        panel = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        server = archive.read("files/food-ticket.php").decode("utf-8")
        release_notes = archive.read("files/food-ticket-release-notes.php").decode("utf-8")
        assert "PANEL_VERSION='1.37.57'" in panel
        assert "FOOD_TICKET_PANEL_VERSION = '1.37.57'" in server
        assert "'notification_id' => '1.63.2-r24'" in release_notes
        assert "'title' => 'اصلاح چاپ گزارش غایبین و اعلان تغییرات'" in release_notes
        assert "رفع اختلال بارگذاری پنل و نمایش اعلان تغییرات" not in release_notes
        assert "function panelViewHtml(content){return releaseNotesBanner()+versionBanner()+content}" in panel
        assert "screen.innerHTML=panelViewHtml((pages[view]||dashboardView)());" in panel
        assert "let content=pages[active]?pages[active]():'';" in panel
        assert "let content=releaseNotesBanner()+versionBanner()" not in panel
        assert "printWindow.addEventListener('load',printWhenReady,{once:true})" in panel

        report = archive.read("files/" + REPORT).decode("utf-8")
        assert "۱.۶۳.۲-r24" in report
        assert "فقط در بدنهٔ `settingsForm()` ساخته می‌شد" in report
        assert "PHP/DB، نصب روی سرور مقصد" in report

        forbidden = ("config.php", ".env", "storage/", "uploads/")
        assert not any(path == forbidden[0] or path == forbidden[1] or path.startswith(forbidden[2:]) for path in files)

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    sidecar = (DOWNLOADS / f"{NAME}.sha256").read_text(encoding="ascii")
    assert digest in sidecar
    assert NAME in sidecar

    assets = (DOWNLOADS / "RELEASE-ASSETS-1.63.2.txt").read_text(encoding="ascii").splitlines()
    assert assets == [NAME]
    release_notes = (DOWNLOADS / "RELEASE-NOTES-1.63.2.md").read_text(encoding="utf-8")
    assert NAME in release_notes and digest in release_notes
    assert "1.63.2-r24" in release_notes
    check_download_page_lists_only_latest_package()
    page = (DOWNLOADS / "index.html").read_text(encoding="utf-8")
    assert "۱.۶۳.۲-r24" in page and NAME in page and digest in page
    assert f"https://github.com/alirezarahimy-code/ticket/releases/download/v1.63.2-r24/{NAME}" in page
