from pathlib import Path
from zipfile import ZipFile
import json

ROOT = Path(__file__).resolve().parents[1]
HEALTH = (ROOT / "system-health.php").read_text(encoding="utf-8")
INDEX = (ROOT / "index.php").read_text(encoding="utf-8")
PACKAGE = ROOT / "downloads/ticket-update-1.62.24-readable-system-health-logs-20261026.zip"


def test_health_status_rules_include_warnings_and_basic_checks():
    start = HEALTH.index("function system_health_summary")
    end = HEALTH.index("function system_health_apply_check_state", start)
    summary = HEALTH[start:end]
    assert "warnings_24h" in summary
    assert "$summary['warnings_24h'] > 0" in summary
    assert "نیازمند رسیدگی فوری" in summary
    assert "به معنی ازکارافتادن سامانه نیست" in summary
    assert "GROUP BY context, message, level" in HEALTH
    assert "function system_health_event_totals" in HEALTH
    assert "food_ticket_events" in HEALTH
    assert "قابلیت در تنظیمات خاموش است و نبودنش مشکل سامانه محسوب نمی‌شود" in HEALTH


def test_health_ui_and_export_use_plain_language_without_raw_user_fields():
    assert "گزارش سادهٔ وضعیت فنی" in HEALTH
    assert "یک رخداد فنی نیازمند بررسی ثبت شده است" in HEALTH
    assert "رخدادهای ۷ روز گذشته" in HEALTH or "خطاها و هشدارهای ۷ روز گذشته" in HEALTH
    start = HEALTH.index("function system_health_export_safe_xlsx")
    end = HEALTH.index("function system_health_render", start)
    export = HEALTH[start:end]
    for private_field in ["request_uri", "$row['technical']", "$row['note']", "user_id", "rawTitle"]:
        assert private_field not in export
    assert "گزارش قابل‌فهم سلامت سامانه" in export
    assert "رخدادهای قابل‌فهم برای پیگیری" in export
    assert "علت احتمالی به زبان ساده" in export
    assert "پیشنهاد بعدی" in export
    assert "خلاصهٔ قابل‌فهم" in export and "رخدادها" in export


def test_system_logs_page_displays_safe_export_and_friendly_descriptions():
    assert "خروجی فعالیت کاربران (ممیزی؛ شامل اطلاعات کاربران)" in INDEX
    assert "دانلود گزارش خوانا و امن (Excel)" in INDEX
    assert "system_health_humanize_event" in INDEX
    assert "system_health_request_label" in INDEX
    assert "جزئیات تخصصی (ممکن است شامل دادهٔ حساس باشد)" in INDEX


def test_16224_package_contains_the_readable_health_and_logs_pages():
    with ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.24"
        assert manifest["requires_version"] == "1.62.22"
        assert "index.php" in manifest["files"]
        assert "system-health.php" in manifest["files"]
        assert archive.read("files/VERSION").decode().strip() == "1.62.24"
        health = archive.read("files/system-health.php").decode("utf-8")
        logs = archive.read("files/index.php").decode("utf-8")
        assert "گزارش خوانا و امن (Excel)" in logs
        assert "گزارش قابل‌فهم سلامت سامانه" in health
        assert "خلاصهٔ قابل‌فهم" in health
