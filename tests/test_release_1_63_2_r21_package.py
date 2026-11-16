from __future__ import annotations

import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

from download_page_helpers import check_download_page_lists_only_latest_package

ROOT = Path(__file__).resolve().parents[1]
DOWNLOADS = ROOT / "downloads"
NAME = "ticket-update-1.63-to-1.63.2-r21-assets-tenter-20261106.zip"
PACKAGE = DOWNLOADS / NAME
REQUIRED_FILES = {
    "VERSION",
    "food-ticket.php",
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
            if relative == "VERSION":
                continue
            source = ROOT / relative
            if not source.is_file():
                source = DOWNLOADS / Path(relative).name
            assert source.is_file(), relative
            assert data == source.read_bytes(), f"package drift: {relative}"

        source_delete = archive.read("files/tools/test_source_delete_offline.php").decode("utf-8")
        source_time = archive.read("files/tools/test_source_time_delete_offline.php").decode("utf-8")
        food = archive.read("files/food-ticket.php").decode("utf-8")
        asset_names = archive.read("files/asset-names.php").decode("utf-8")
        assert "food_ticket_source_minimal_where" in food
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

    asset_manifest = (DOWNLOADS / "RELEASE-ASSETS-1.63.2.txt").read_text(encoding="ascii")
    asset_names = [
        line.strip()
        for line in asset_manifest.splitlines()
        if line.strip() and not line.lstrip().startswith("#")
    ]
    assert asset_names == [NAME]
    assert (DOWNLOADS / asset_names[0]).is_file()

    release_notes = DOWNLOADS / "RELEASE-NOTES-1.63.2.md"
    assert release_notes.is_file()
    release_note_text = release_notes.read_text(encoding="utf-8")
    assert release_note_text.startswith("# نسخهٔ ۱.۶۳.۲")
    assert NAME in release_note_text
    assert digest in release_note_text
    assert "۱.۶۲.۸۸" in release_note_text
    assert "مستقیماً" in release_note_text

    check_download_page_lists_only_latest_package()
    page = (DOWNLOADS / "index.html").read_text(encoding="utf-8")
    assert "نسخهٔ ۱.۶۳.۲" in page
    assert NAME in page
