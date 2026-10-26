from pathlib import Path
from zipfile import ZipFile
import json
import re

ROOT = Path(__file__).resolve().parents[1]
LINK_TEXT = "دانلود گزارش فنی پالایش‌شده برای پشتیبانی"
EXPORT_URL = "index.php?page=system-health&amp;export=safe"


def test_current_source_logs_page_shows_safe_export_link():
    source = (ROOT / "index.php").read_text(encoding="utf-8")
    logs_start = source.index("if ($page === 'logs') {", source.index("if ($page === 'roles')") if "if ($page === 'roles')" in source else 0)
    logs_end = source.index("if ($page !== 'dashboard')", logs_start)
    logs_page = source[logs_start:logs_end]
    assert LINK_TEXT in logs_page
    assert EXPORT_URL in logs_page
    assert "user_can($logUser, 'system.health')" in logs_page


def test_update_zip_adds_button_to_unified_system_logs_page():
    package = ROOT / "downloads/ticket-update-1.62.23-safe-export-from-system-logs-20261026.zip"
    with ZipFile(package) as archive:
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.23"
        assert manifest["requires_version"] == "1.62.22"
        assert "index.php" in manifest["files"]
        page = archive.read("files/index.php").decode("utf-8")
        logs_start = page.index("if ($page === 'activity-log') {")
        next_page = re.search(r"\\nif \\(\\$page === '[^']+'\\) \\{", page[logs_start + 1 :])
        logs_end = logs_start + 1 + next_page.start() if next_page else len(page)
        logs_page = page[logs_start:logs_end]
        assert LINK_TEXT in logs_page
        assert EXPORT_URL in logs_page
        assert "user_can($logUser, 'system.health')" in logs_page
