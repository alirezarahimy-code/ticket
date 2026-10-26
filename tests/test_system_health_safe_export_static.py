from pathlib import Path
from zipfile import ZipFile
import json


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "system-health.php"
TEXT = SOURCE.read_text(encoding="utf-8")


def test_health_export_is_reachable_and_labelled_safe():
    assert "export=safe" in TEXT
    assert "دانلود گزارش فنی پالایش‌شده برای پشتیبانی" in TEXT
    assert "system_health_export_safe_xlsx($user)" in TEXT
    assert "system-health-safe-' . date" in TEXT


def test_export_omits_user_linked_and_raw_log_fields():
    start = TEXT.index("function system_health_export_safe_xlsx")
    end = TEXT.index("function system_health_render", start)
    export = TEXT[start:end]
    for private_field in ["request_uri", "technical", "note", "user_id", "$r['cause']", "$r['action']"]:
        assert private_field not in export
    assert "SELECT full_name FROM users" in export
    assert "SELECT username FROM users" in export
    assert "system_error_explain($rawTitle)" in export
    assert "خطای نامشخص سامانه" in export
    assert "رخداد فنی؛ متن خام برای حفظ حریم خصوصی حذف شد" in export
    assert "['error', 'warning']" in export


def test_safe_export_masks_common_identifiers_and_warning_is_explained():
    start = TEXT.index("function system_health_export_safe_text")
    end = TEXT.index("/** خروجی امن Excel", start)
    sanitizer = TEXT[start:end]
    for marker in ["[کاربر]", "[ایمیل]", "[شناسه]", "[شماره تماس]", "[IP]", "[نشانی وب]", "[مخفی]"]:
        assert marker in sanitizer
    assert "به‌تنهایی به معنی ازکارافتادن یا خراب‌شدن سامانه نیست" in TEXT
    assert "دست‌کم یک خطا یا کندی" in TEXT


def test_published_update_contains_the_button_and_cumulative_report_files():
    package = ROOT / "downloads/ticket-update-1.62.22-safe-system-health-export-20261026.zip"
    with ZipFile(package) as archive:
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.22"
        assert manifest["requires_version"] == "1.62.20"
        assert "food-order.php" in manifest["files"]
        assert "system-health.php" in manifest["files"]
        assert archive.read("files/VERSION").decode().strip() == "1.62.22"
        page = archive.read("files/system-health.php").decode("utf-8")
        assert "دانلود گزارش فنی پالایش‌شده برای پشتیبانی" in page
        assert "export=safe" in page
