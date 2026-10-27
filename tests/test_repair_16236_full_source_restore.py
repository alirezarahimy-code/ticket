from __future__ import annotations

import hashlib
import json
import subprocess
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
ZIP_PATH = ROOT / "downloads/ticket-repair-1.62.36-full-source-restore-20261027.zip"
EXPECTED_FILES = [
    "VERSION",
    "assets/food-menu.css",
    "assets/food-menu.js",
    "assets/style.css",
    "food-order.php",
    "food-ticket-groups.php",
    "food-ticket-order-source.php",
    "food-ticket.php",
    "food-ticket-web/assets/food-menu.css",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/index.html",
    "index.php",
]


def test_restore_package_reinstalls_16236_without_database_migrations():
    with ZipFile(ZIP_PATH) as archive:
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.36"
        assert manifest["requires_version"] == "1.62.36"
        assert manifest["migrations"] == []
        assert manifest["files"] == EXPECTED_FILES
        assert archive.testzip() is None
        assert archive.read("files/VERSION").decode().strip() == "1.62.36"
        assert set(archive.namelist()) == {"manifest.json", *(f"files/{name}" for name in EXPECTED_FILES)}


def test_restore_package_contains_the_pre_report_16236_files():
    with ZipFile(ZIP_PATH) as archive:
        for name in EXPECTED_FILES:
            if name == "VERSION":
                expected = b"1.62.36\n"
            else:
                expected = subprocess.check_output(["git", "show", f"62dc749:{name}"], cwd=ROOT)
            assert archive.read(f"files/{name}") == expected, name


def test_download_page_warns_that_original_16236_zip_was_not_a_full_restore():
    page = (ROOT / "downloads/index.html").read_text()
    assert "ticket-repair-1.62.36-full-source-restore-20261027.html" in page
    assert "فایل‌های داشبورد" in page


def test_restore_package_checksum_matches():
    expected = (ROOT / "downloads/ticket-repair-1.62.36-full-source-restore-20261027.zip.sha256").read_text().split()[0]
    actual = hashlib.sha256(ZIP_PATH.read_bytes()).hexdigest()
    assert actual == expected
