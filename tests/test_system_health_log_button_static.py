from pathlib import Path
from zipfile import ZipFile
import json

ROOT = Path(__file__).resolve().parents[1]
LINK_TEXT = "دانلود گزارش خوانا و امن (Excel)"
EXPORT_URL = "index.php?page=system-health&amp;export=safe"


def test_current_system_logs_page_shows_safe_export_and_readable_events():
    source = (ROOT / "index.php").read_text(encoding="utf-8")
    logs_start = source.index("if ($page === 'activity-log') {")
    logs_end = source.index("if ($page === 'backup') {", logs_start)
    logs_page = source[logs_start:logs_end]
    assert LINK_TEXT in logs_page
    assert EXPORT_URL in logs_page
    assert "system_health_humanize_event" in logs_page
    assert "system_health_request_label" in logs_page
    assert "خطاها و هشدارها (پیشنهادی)" in logs_page
    assert "s.level IN ('error', 'warning')" in logs_page


def test_update_zip_contains_button_in_the_actual_unified_log_page():
    package = ROOT / "downloads/ticket-update-1.62.24-readable-system-health-logs-20261026.zip"
    with ZipFile(package) as archive:
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.24"
        assert manifest["requires_version"] == "1.62.22"
        assert "index.php" in manifest["files"]
        page = archive.read("files/index.php").decode("utf-8")
        logs_start = page.index("if ($page === 'activity-log') {")
        logs_end = page.index("if ($page === 'backup') {", logs_start)
        logs_page = page[logs_start:logs_end]
        assert LINK_TEXT in logs_page
        assert EXPORT_URL in logs_page
        assert "خطاها و هشدارها (پیشنهادی)" in logs_page
        assert "system_health_humanize_event" in logs_page
