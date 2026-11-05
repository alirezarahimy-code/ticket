"""ممیزی سلامت باید جزئی از سیستم لاگ باشد، نه سامانه‌ای موازی.

این تست‌ها دو چیز را تضمین می‌کنند:

۱. ممیزی واقعاً از مسیر همیشگی لاگ عبور می‌کند (system_logs، log_issues،
   logging_health_alerts) و نه یک کانال اختصاصی.
۲. منطق بررسی فقط یک‌جا زندگی می‌کند، تا نسخهٔ خط فرمان و نسخهٔ کرون از هم
   جدا نیفتند.
"""

from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MODULE = ROOT / "lib/logging-audit.php"
CRON = ROOT / "cron/check_logging_health.php"
CLI = ROOT / "tools/system_audit.php"
CATALOG = ROOT / "lib/log-catalog.php"


def read(path: Path) -> str:
    return path.read_text(encoding="utf-8")


# ---------------------------------------------------------------------------
# یکپارچگی با سیستم لاگ
# ---------------------------------------------------------------------------


def test_audit_module_exists_inside_the_logging_library():
    assert MODULE.is_file(), "ماژول ممیزی باید کنار بقیهٔ ماژول‌های لاگ در lib/ باشد"
    assert MODULE.name.startswith("logging-"), (
        "نام فایل باید نشان دهد ممیزی جزئی از سیستم لاگ است، نه ابزاری جدا"
    )


def test_audit_records_through_the_standard_logging_entry_points():
    src = read(MODULE)
    assert "log_event(" in src, "ممیزی باید رخدادهایش را با log_event ثبت کند"
    assert "logging_issue_upsert(" in src, (
        "یافته‌ها باید در log_issues تجمیع شوند تا کنار بقیهٔ مشکلات دیده شوند"
    )


def test_audit_feeds_the_existing_health_alert_channel():
    """هشدار باید از همان مسیری برود که بقیهٔ هشدارهای سلامت می‌روند."""
    src = read(MODULE)
    assert "array &$alerts" in src, (
        "ممیزی باید آرایهٔ $alerts کرون سلامت را پر کند، نه کانال اعلان جداگانه بسازد"
    )
    assert "notify_user(" not in src, (
        "ممیزی نباید مستقیم اعلان بفرستد؛ این کار مسیر موازی می‌سازد. "
        "cron/check_logging_health.php همهٔ $alerts را یکجا تحویل می‌دهد."
    )


def test_health_cron_runs_the_audit():
    src = read(CRON)
    assert "logging-audit.php" in src, "کرون سلامت باید ماژول ممیزی را بارگذاری کند"
    assert "logging_audit_run_and_record" in src
    assert "$alerts" in src


def test_audit_failure_is_never_silent():
    """ممیزی‌ای که بی‌صدا بیفتد، دقیقاً همان مشکلی است که قرار بود پیدا کند."""
    src = read(CRON)
    match = re.search(
        r"catch\s*\(\s*Throwable\s+\$auditException\s*\)\s*\{(.*?)\n\}", src, re.S
    )
    assert match, "گرفتن خطای ممیزی در کرون پیدا نشد"
    body = match.group(1)
    assert "$alerts[" in body, "شکست ممیزی باید هشدار تولید کند، نه سکوت"


def test_no_parallel_audit_tool_remains():
    """ابزار پایتونی قبلی باید حذف شده باشد تا دو منبع حقیقت نداشته باشیم."""
    assert not (ROOT / "tools/system_audit.py").exists(), (
        "ابزار ممیزی موازی هنوز هست؛ منطق بررسی باید فقط در lib/logging-audit.php باشد"
    )
    assert not (ROOT / "tools/audit-baseline.json").exists(), (
        "خط مبنا باید در storage همین نصب باشد، نه در کد"
    )


def test_cli_is_a_thin_wrapper_not_a_second_implementation():
    src = read(CLI)
    assert "logging-audit.php" in src, "خط فرمان باید همان موتور را بارگذاری کند"
    # هیچ بررسی‌ای نباید در خط فرمان دوباره تعریف شود.
    redefined = re.findall(r"function\s+logging_audit_check_\w+", src)
    assert not redefined, f"خط فرمان بررسی را دوباره تعریف کرده: {redefined}"


# ---------------------------------------------------------------------------
# کاتالوگ رویداد
# ---------------------------------------------------------------------------


def test_audit_event_codes_are_registered_in_the_catalog():
    """کد ثبت‌نشده در کاتالوگ به SYS-LEGACY-001 تنزل پیدا می‌کند و گم می‌شود."""
    catalog = read(CATALOG)
    for code in ("OPS-AUDIT-001", "OPS-AUDIT-002", "OPS-AUDIT-003", "OPS-AUDIT-004"):
        assert f"'{code}'" in catalog, f"کد {code} در lib/log-catalog.php تعریف نشده"


def test_audit_catalog_entries_are_complete():
    """هر تعریف باید به ادمین بگوید چه شده، چرا، و چه کند."""
    catalog = read(CATALOG)
    for code in ("OPS-AUDIT-001", "OPS-AUDIT-002", "OPS-AUDIT-003", "OPS-AUDIT-004"):
        line = next(ln for ln in catalog.splitlines() if f"'{code}'" in ln)
        for field in ("category", "severity", "title", "user_message",
                      "admin_explanation", "probable_cause", "recommended_action"):
            assert f"'{field}'" in line, f"{code} فیلد {field} را ندارد"


def test_structural_problems_are_critical_and_regressions_are_warnings():
    """شدت باید با معنای یافته بخواند، وگرنه هشدارها بی‌اثر می‌شوند."""
    catalog = read(CATALOG)
    critical = next(ln for ln in catalog.splitlines() if "'OPS-AUDIT-003'" in ln)
    assert "'severity'=>'critical'" in critical.replace(" ", ""), (
        "ناسازگاری ساختاری یعنی این نصب خراب است و باید critical باشد"
    )
    warning = next(ln for ln in catalog.splitlines() if "'OPS-AUDIT-002'" in ln)
    assert "'severity'=>'warning'" in warning.replace(" ", "")


def test_audit_codes_are_used_by_the_module():
    src = read(MODULE)
    for code in ("OPS-AUDIT-001", "OPS-AUDIT-002", "OPS-AUDIT-003", "OPS-AUDIT-004"):
        assert code in src, f"کد {code} تعریف شده ولی ماژول از آن استفاده نمی‌کند"


# ---------------------------------------------------------------------------
# محتوای بررسی‌ها
# ---------------------------------------------------------------------------


def test_structural_checks_cover_the_defect_that_went_unnoticed():
    """بررسی‌ها باید دقیقاً همان نقصی را بگیرند که ماه‌ها خاموش ماند."""
    src = read(MODULE)
    for table in ("log_issues", "logging_health_alerts", "schema_updates"):
        assert f"'{table}'" in src, f"جدول {table} در فهرست جدول‌های لازم نیست"
    for column in ("fingerprint", "must_change_password", "hostname_key", "idempotency_key"):
        assert f"'{column}'" in src, f"ستون {column} در فهرست ستون‌های لازم نیست"


def test_checks_inspect_the_live_database_not_just_files():
    """ارزش اصلی ممیزیِ داخل سامانه، دیدن پایگاه دادهٔ همین نصب است."""
    src = read(MODULE)
    assert "information_schema" in src or "db_table_exists" in src
    assert "schema_updates" in src, "مهاجرت‌های اجرانشدهٔ همین نصب باید بررسی شوند"


def test_every_check_explains_why_it_matters():
    """بررسی بدون توضیح فقط نویز تولید می‌کند."""
    src = read(MODULE)
    functions = re.findall(r"function (logging_audit_check_\w+)\(\)", src)
    assert len(functions) >= 6, f"بررسی‌های کمی پیدا شد: {functions}"
    blocks = re.split(r"function logging_audit_check_\w+\(\)", src)[1:]
    for name, block in zip(functions, blocks):
        assert "'rationale'" in block, f"{name} فیلد rationale ندارد"


def test_structural_checks_have_no_budget():
    """بررسی ساختاری نباید با خط مبنا خاموش شود."""
    src = read(MODULE)
    match = re.search(r"function logging_audit_structural_checks\(\): array\s*\{(.*?)\}", src, re.S)
    assert match, "فهرست بررسی‌های ساختاری پیدا نشد"
    for key in ("db_schema_tables", "db_schema_columns", "db_pending_migrations"):
        assert key in match.group(1), f"{key} باید ساختاری باشد و بودجه نداشته باشد"


def test_baseline_lives_in_storage_not_in_the_repository():
    """خط مبنا مخصوص هر نصب است؛ در کد گذاشتنش یعنی همه یک عدد بگیرند."""
    src = read(MODULE)
    assert "storage/logs/audit-baseline.json" in src
    assert not (ROOT / "storage/logs/audit-baseline.json").exists(), (
        "فایل خط مبنای یک نصب نباید کامیت شود"
    )


def test_ci_runs_the_audit_without_a_database():
    workflow = read(ROOT / ".github/workflows/ci.yml")
    assert "system_audit.php" in workflow, "CI باید ممیزی را اجرا کند"
    assert "--no-db" in workflow, (
        "در CI پایگاه داده نیست؛ بدون --no-db اجرا شکست می‌خورد"
    )
