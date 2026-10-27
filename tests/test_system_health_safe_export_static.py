from pathlib import Path
from zipfile import ZipFile
import json

ROOT = Path(__file__).resolve().parents[1]
TEXT = (ROOT / "system-health.php").read_text(encoding="utf-8")


def test_safe_export_is_reachable_and_labelled_for_people():
    assert "export=safe" in TEXT
    assert "دانلود گزارش خوانا و امن (Excel)" in TEXT
    assert "system_health_export_safe_xlsx($user)" in TEXT
    assert "system-health-readable-' . date" in TEXT


def test_export_omits_user_linked_and_raw_log_fields():
    start = TEXT.index("function system_health_export_safe_xlsx")
    end = TEXT.index("function system_health_render", start)
    export = TEXT[start:end]
    for private_field in ["request_uri", "technical", "note", "user_id", "$row['cause']", "$row['action']"]:
        assert private_field not in export
    assert "SELECT full_name FROM users" in export
    assert "SELECT username FROM users" in export
    assert "system_health_humanize_event($row)" in export
    assert "['error', 'warning']" in export
    assert "user_id" not in export


def test_humanizer_uses_safe_categories_and_common_masks():
    start = TEXT.index("function system_health_humanize_event")
    end = TEXT.index("/** URL خام درخواست", start)
    humanizer = TEXT[start:end]
    assert "system_error_explain($technical !== '' ? $technical : $rawTitle)" in humanizer
    assert "علت دقیق از روی اطلاعات قابل‌نمایش مشخص نیست" in humanizer
    assert "مرجع فنی این رخداد" in humanizer
    start = TEXT.index("function system_health_export_safe_text")
    end = TEXT.index("/** خروجی امن Excel", start)
    sanitizer = TEXT[start:end]
    for marker in ["[کاربر]", "[ایمیل]", "[شناسه]", "[شماره تماس]", "[IP]", "[نشانی وب]", "[مخفی]"]:
        assert marker in sanitizer


def test_health_warning_is_explained_without_claiming_outage():
    assert "به‌تنهایی به معنی ازکارافتادن سامانه نیست" in TEXT
    assert "نیازمند رسیدگی فوری" in TEXT
    assert "رخداد فنی یا کندی ثبت شده است" in TEXT


def test_published_update_contains_the_readable_safe_export():
    package = ROOT / "downloads/ticket-update-1.62.24-readable-system-health-logs-20261026.zip"
    with ZipFile(package) as archive:
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.24"
        assert manifest["requires_version"] == "1.62.22"
        assert "index.php" in manifest["files"]
        assert "system-health.php" in manifest["files"]
        assert archive.read("files/VERSION").decode().strip() == "1.62.24"
        page = archive.read("files/system-health.php").decode("utf-8")
        assert "دانلود گزارش خوانا و امن (Excel)" in page
        assert "خلاصهٔ قابل‌فهم" in page
        assert "export=safe" in page
