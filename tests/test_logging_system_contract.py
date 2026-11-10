"""قرارداد محافظ سامانهٔ لاگ‌گیری مرکزی (از نسخهٔ ۱.۶۲.۷۷).

چرا این فایل وجود دارد
----------------------
دو بستهٔ به‌روزرسانی پیاپی، بی‌آنکه کسی متوجه شود، این آسیب‌ها را وارد کردند:

* `bootstrap.php` را بدون بارگذاری هستهٔ لاگ تحویل دادند ⇒ خطای مرگبار در هر درخواست.
* تابعی (`security_log_access_denied`) را ۱۸ بار صدا زدند که هیچ‌جا تعریف نشده بود.
* `require` به فایلی دادند که در بسته نبود (`logging-dashboard.php`).
* قابلیت‌های نسخهٔ ۱.۶۲.۷۵ (گروه‌های دسترسی عمومی، دامنهٔ دید اعلان‌ها، مسیریابی
  مبتنی بر درخواست‌کننده) را از `index.php` حذف کردند.
* فایل‌های مهاجرت را با شماره‌های از پیش اشغال‌شده (۰۱۹–۰۲۵) دوباره فرستادند.

این آزمون همهٔ آن اشتباه‌ها را به‌صورت ایستا می‌گیرد. **هر بستهٔ به‌روزرسانی تازه باید
پیش از انتشار این آزمون را سبز کند.** اگر آزمونی اینجا قرمز شد، یعنی بسته چیزی را
خراب کرده است؛ آزمون را تضعیف نکنید، بسته را درست کنید.
"""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

# ─────────────────────────── ۱) حضور اجباری فایل‌ها ───────────────────────────
REQUIRED_RUNTIME_FILES = [
    "bootstrap.php",
    "index.php",
    "logging-dashboard.php",
    "system-health.php",
    "lib/logging-core.php",
    "lib/log-catalog.php",
    "lib/logging-csrf.php",
    "lib/logging-replay.php",
    "lib/logging-integrity.php",
    "lib/logging-diagnostics.php",
    "lib/activity-audit.php",
    "assets/logging-dashboard.css",
    "assets/logging-dashboard.js",
    "cron/check_logging_health.php",
    "cron/deliver_logging_issue_notifications.php",
    "cron/export_logging_agent_bundle.php",
    "cron/export_logging_integrity.php",
    "cron/rebuild_logging_issues.php",
    "cron/replay_logging_spool.php",
    "cron/verify_logging_integrity.php",
    "cron/verify_logging_release.php",
]

REQUIRED_MIGRATIONS = [
    "024-central-logging.sql",
    "025-logging-issues-alerts.sql",
    "026-logging-legacy-result-unknown.sql",
    "027-logging-correlation-ids.sql",
    "028-logging-fingerprint-v2.sql",
    "029-activity-result-default-unknown.sql",
    "030-logging-notification-idempotency-and-ticket-history.sql",
]

# تنها شمارهٔ مهاجرتی که عمداً دو فایل دارد (نسخهٔ اصلی و rollback آن).
KNOWN_DUPLICATE_MIGRATION_NUMBERS = {"017"}

# حداقل تعداد نشانگر هر قابلیت که از نسخه‌های ۱.۶۲.۷۴/۷۵ به ارث رسیده و
# هیچ بستهٔ لاگینگی حق حذفش را ندارد.
FEATURE_FLOOR = [
    # Business logic may move out of the legacy front controller during module extraction.
    # Keep the same minimums, but search the application PHP surface for these symbols.
    ("@app", "access_group", 15),
    ("index.php", "user_access_group", 7),
    ("index.php", "user-access-groups.php", 1),
    ("index.php", "notification_ticket_visibility", 2),
    ("@app", "$requesterId", 2),
    ("@app", "choose_handling_agent", 3),
    ("lib/activity-audit.php", "access_group_created", 1),
    ("lib/activity-audit.php", "access_group_updated", 1),
    ("lib/activity-audit.php", "access_group_deleted", 1),
    ("lib/activity-audit.php", "save_audit_required", 1),
]

# فایل‌های برنامه‌ای که برای تحلیل فراخوانی‌ها اسکن می‌شوند.
APP_DIRS = ("lib", "actions", "cron")
APP_ROOT_FILES = ("index.php", "bootstrap.php", "logging-dashboard.php", "system-health.php")

REQUIRE_RE = re.compile(r"(?:require|include)(?:_once)?\s*(?:\(\s*)?__DIR__\s*\.\s*'(/[^']+)'")
DEFINE_RE = re.compile(r"^\s*function\s+([a-zA-Z_]\w*)", re.M)
GUARD_RE = re.compile(r"function_exists\(\s*'([a-zA-Z_]\w*)'")
# فقط توابع خودِ سامانه؛ نام‌هایی که داخل رشتهٔ SQL می‌آیند (مثل log_issues) حذف می‌شوند.
CALL_RE = re.compile(r"(?<![\$>:\w])((?:logging|security_log|audit)_[a-z0-9_]+)\s*\(")
# نام جدول‌های SQL (مثل `INSERT INTO logging_health_alerts (`) فراخوانی تابع نیستند.
SQL_CONTEXT_RE = re.compile(r"(?:INTO|FROM|JOIN|UPDATE|TABLE|EXISTS)\s+`?$", re.I)


def read(relative: str) -> str:
    return (ROOT / relative).read_text(encoding="utf-8", errors="replace")


def app_php_files() -> list[Path]:
    files = [ROOT / name for name in APP_ROOT_FILES]
    for folder in APP_DIRS:
        files.extend(sorted((ROOT / folder).rglob("*.php")))
    return [p for p in files if p.is_file()]


def all_php_files() -> list[Path]:
    """همهٔ فایل‌های PHP سامانه؛ بایگانی‌ها، ابزارهای جانبی و تست‌ها کنار گذاشته می‌شوند."""
    skip = {"updates", "downloads", "storage", "vendor", "node_modules", ".git", "tests", "tools"}
    return [p for p in ROOT.rglob("*.php") if not set(p.relative_to(ROOT).parts) & skip]


# ──────────────────────────────── آزمون‌ها ────────────────────────────────
def test_every_logging_runtime_file_is_present() -> None:
    missing = [name for name in REQUIRED_RUNTIME_FILES if not (ROOT / name).is_file()]
    assert missing == [], (
        "این فایل‌های سامانهٔ لاگ حذف شده‌اند؛ بستهٔ به‌روزرسانی ناقص است:\n  - "
        + "\n  - ".join(missing)
    )


def test_logging_migrations_exist_and_numbers_are_unique() -> None:
    folder = ROOT / "migrations"
    names = sorted(p.name for p in folder.glob("*.sql"))
    missing = [m for m in REQUIRED_MIGRATIONS if m not in names]
    assert missing == [], "مهاجرت‌های لاگ‌گیری گم شده‌اند:\n  - " + "\n  - ".join(missing)

    bad_pattern = [n for n in names if not re.match(r"^\d{3}-[a-z0-9-]+\.sql$", n)]
    assert bad_pattern == [], "نام این مهاجرت‌ها با الگوی نصب‌کننده نمی‌خواند: " + str(bad_pattern)

    seen: dict[str, list[str]] = {}
    for name in names:
        seen.setdefault(name[:3], []).append(name)
    clashes = {
        number: files
        for number, files in seen.items()
        if len(files) > 1 and number not in KNOWN_DUPLICATE_MIGRATION_NUMBERS
    }
    assert clashes == {}, (
        "دو فایل مهاجرت با یک شماره وجود دارد — همین باعث خرابی به‌روزرسانی می‌شود:\n"
        + "\n".join(f"  {number}: {files}" for number, files in sorted(clashes.items()))
    )


def test_bootstrap_loads_the_logging_core_before_its_dependents() -> None:
    bootstrap = read("bootstrap.php")
    assert "lib/logging-core.php" in bootstrap, (
        "bootstrap.php هستهٔ لاگ را بارگذاری نمی‌کند؛ هر فراخوانی logging_* خطای مرگبار می‌دهد"
    )
    def require_line(target: str) -> int:
        """شمارهٔ خطِ دستور require واقعی (نه ذکر نام در توضیحات سرآیند)."""
        for number, line in enumerate(bootstrap.splitlines(), 1):
            stripped = line.lstrip()
            if stripped.startswith(("require", "include")) and target in line:
                return number
        return -1

    core_line = require_line("lib/logging-core.php")
    assert core_line > 0, "bootstrap.php دستور require برای lib/logging-core.php ندارد"
    for dependent in ("lib/logging-csrf.php", "lib/activity-audit.php"):
        dependent_line = require_line(dependent)
        if dependent_line > 0:
            assert core_line < dependent_line, (
                f"{dependent} در خط {dependent_line} پیش از هستهٔ لاگ (خط {core_line}) بارگذاری می‌شود"
            )


def test_no_php_file_requires_a_file_that_does_not_exist() -> None:
    broken: list[str] = []
    for path in all_php_files():
        for match in REQUIRE_RE.finditer(path.read_text(encoding="utf-8", errors="replace")):
            target = (path.parent / match.group(1).lstrip("/")).resolve()
            if not target.exists():
                broken.append(f"{path.relative_to(ROOT)} → {match.group(1)}")
    assert broken == [], (
        "این require ها به فایلی اشاره می‌کنند که در سامانه نیست (خطای مرگبار هنگام اجرا):\n  - "
        + "\n  - ".join(broken)
    )


def test_every_logging_function_called_is_defined_or_guarded() -> None:
    defined: set[str] = set()
    for path in all_php_files():
        defined |= set(DEFINE_RE.findall(path.read_text(encoding="utf-8", errors="replace")))

    unresolved: list[str] = []
    for path in app_php_files():
        text = path.read_text(encoding="utf-8", errors="replace")
        guarded = set(GUARD_RE.findall(text))
        for match in CALL_RE.finditer(text):
            name = match.group(1)
            if name in defined or name in guarded:
                continue
            if SQL_CONTEXT_RE.search(text[max(0, match.start() - 24):match.start()]):
                continue  # نام جدول داخل رشتهٔ SQL است، نه تابع
            entry = f"{path.relative_to(ROOT)} → {name}()"
            if entry not in unresolved:
                unresolved.append(entry)
    assert unresolved == [], (
        "این توابع صدا زده می‌شوند ولی نه تعریف شده‌اند و نه با function_exists گارد دارند:\n  - "
        + "\n  - ".join(unresolved)
    )


def test_security_log_access_denied_is_available_wherever_it_is_used() -> None:
    definitions = [
        p.relative_to(ROOT).as_posix()
        for p in all_php_files()
        if "function security_log_access_denied" in p.read_text(encoding="utf-8", errors="replace")
    ]
    assert definitions, (
        "تابع security_log_access_denied تعریف نشده است؛ هر صفحهٔ دارای بررسی دسترسی "
        "خطای مرگبار می‌دهد (این دقیقاً همان باگ دو بستهٔ قبلی بود)"
    )


def test_logging_update_must_not_delete_1_62_75_features() -> None:
    losses: list[str] = []
    app_source = None
    for relative, marker, floor in FEATURE_FLOOR:
        if relative == "@app":
            if app_source is None:
                app_source = "\n".join(
                    path.read_text(encoding="utf-8", errors="replace")
                    for path in all_php_files()
                )
            source = app_source
        else:
            source = read(relative)
        found = source.count(marker)
        if found < floor:
            losses.append(f"{relative}: «{marker}» باید حداقل {floor} بار بیاید ولی {found} بار آمده")
    assert losses == [], (
        "بستهٔ به‌روزرسانی قابلیت‌های نسخه‌های پیشین را حذف کرده است:\n  - " + "\n  - ".join(losses)
    )


def test_logging_dashboard_is_reachable_from_the_reports_menu() -> None:
    # The shared navigation now owns menu links; index.php still owns the legacy route.
    index = read("index.php")
    navigation = read("lib/render.php")
    assert "index.php?page=logging-dashboard" in navigation, "لینک داشبورد لاگ از منوی مشترک حذف شده است"
    assert "$page === 'logging-dashboard'" in index, "مسیر صفحهٔ داشبورد لاگ از index.php حذف شده است"
    assert "require_once __DIR__ . '/logging-dashboard.php';" in index, "فایل داشبورد از ورودی اصلی بارگذاری نمی‌شود"
    assert "function logging_dashboard_render" in read("logging-dashboard.php")


def test_columns_written_by_the_logging_core_exist_after_migrations() -> None:
    schema = read("schema.sql")
    added: dict[str, set[str]] = {}
    for path in sorted((ROOT / "migrations").glob("*.sql")):
        sql = path.read_text(encoding="utf-8", errors="replace")
        for table, column in re.findall(
            r"TABLE_NAME\s*=\s*'(\w+)'\s*AND\s*COLUMN_NAME\s*=\s*'(\w+)'", sql, re.I
        ):
            added.setdefault(table, set()).add(column)
        for table, column in re.findall(
            r"ALTER TABLE\s+`?(\w+)`?\s+ADD COLUMN\s+`?(\w+)`?", sql, re.I
        ):
            added.setdefault(table, set()).add(column)

    def schema_columns(table: str) -> set[str]:
        block = re.search(
            r"CREATE TABLE IF NOT EXISTS\s+" + table + r"\s*\((.*?)\n\)", schema, re.S | re.I
        )
        if not block:
            return set()
        columns = set()
        for line in block.group(1).splitlines():
            match = re.match(
                r"\s*`?(\w+)`?\s+(?:BIGINT|INT|TINYINT|SMALLINT|MEDIUMINT|VARCHAR|CHAR|TEXT"
                r"|LONGTEXT|MEDIUMTEXT|DATETIME|TIMESTAMP|DATE|DECIMAL|FLOAT|DOUBLE|ENUM|JSON|BLOB)",
                line,
                re.I,
            )
            if match and match.group(1).upper() not in {"KEY", "PRIMARY", "UNIQUE", "INDEX"}:
                columns.add(match.group(1))
        return columns

    core = read("lib/logging-core.php")
    gaps: list[str] = []
    for table in ("activity_logs", "system_logs"):
        for insert in re.finditer(r"INSERT (?:IGNORE )?INTO\s+" + table + r"\s*\(([^)]*)\)", core, re.I):
            wanted = {c.strip().strip("`") for c in insert.group(1).split(",") if c.strip()}
            have = schema_columns(table) | added.get(table, set())
            missing = sorted(wanted - have)
            if missing:
                gaps.append(f"{table}: {missing}")
    assert gaps == [], (
        "هستهٔ لاگ در ستون‌هایی می‌نویسد که نه در schema.sql هستند و نه مهاجرتی آن‌ها را می‌سازد "
        "(هر درج لاگ شکست می‌خورد):\n  - " + "\n  - ".join(gaps)
    )
