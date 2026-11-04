"""آزمون‌های ثابت «پنجرهٔ کاری Worker» و «تب تقویم تعطیلات زیر تنظیمات».

این آزمون‌ها کد را اجرا نمی‌کنند؛ فقط ثابت می‌کنند سیم‌کشی سر جای خودش هست.
هدف: جلوگیری از عقب‌گرد خاموشِ آپدیت‌های بعدی روی همین سه قابلیت.
"""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

WORKER = (ROOT / "cron/food_ticket_worker.php").read_text(encoding="utf-8")
WINDOW = (ROOT / "food-ticket-window.php").read_text(encoding="utf-8")
TICKET = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
ENV = (ROOT / "food-ticket-env.php").read_text(encoding="utf-8")
INDEX = (ROOT / "index.php").read_text(encoding="utf-8")
BOARD = (ROOT / "holiday-board.php").read_text(encoding="utf-8")
ACTIONS = (ROOT / "actions/holiday-actions.php").read_text(encoding="utf-8")
PANEL = (ROOT / "food-ticket-web/index.html").read_text(encoding="utf-8")
MIGRATION = (ROOT / "migrations/031-food-worker-window.sql").read_text(encoding="utf-8")
SCHEMA = (ROOT / "schema.sql").read_text(encoding="utf-8")
EMPTY_DB = (ROOT / "install-empty-db.sql").read_text(encoding="utf-8")
UPDATER = (ROOT / "system-update.php").read_text(encoding="utf-8")

WINDOW_COLUMNS = [
    "window_enabled",
    "window_days",
    "window_start",
    "window_end",
    "window_margin_before_minutes",
    "window_margin_after_minutes",
    "idle_poll_seconds",
    "active_poll_ms",
    "persistent_source_connection",
]


# ───────────────────────── مهاجرت ۰۳۱ ─────────────────────────


def _css_rule(css: str, selector: str) -> str:
    """متن یک قاعدهٔ CSS را برمی‌گرداند تا تست به ترتیب خصیصه‌ها حساس نباشد."""
    marker = selector + "{"
    assert marker in css, selector
    start = css.index(marker) + len(marker)
    return css[start : css.index("}", start)]


def test_migration_031_is_the_only_new_file() -> None:
    names = sorted(p.name for p in (ROOT / "migrations").glob("*.sql"))
    assert "031-food-worker-window.sql" in names
    prefixes = [n[:3] for n in names]
    # تنها شمارهٔ تکراری مجاز ۰۱۷ است (قرارداد AGENTS.md).
    duplicates = {p for p in prefixes if prefixes.count(p) > 1}
    assert duplicates <= {"017"}, duplicates


def test_migration_adds_every_window_column() -> None:
    for column in WINDOW_COLUMNS:
        assert f"ADD COLUMN {column} " in MIGRATION, column


def test_migration_is_idempotent_for_every_column() -> None:
    """هر ستون باید پیش از ALTER با information_schema بررسی شود."""
    guards = MIGRATION.count("FROM information_schema.COLUMNS")
    assert guards >= len(WINDOW_COLUMNS), guards
    assert MIGRATION.count("PREPARE ftw_stmt FROM") == MIGRATION.count("DEALLOCATE PREPARE ftw_stmt")
    assert MIGRATION.count("ADD COLUMN") == len(WINDOW_COLUMNS)


def test_migration_defaults_match_php_defaults() -> None:
    """پیش‌فرض SQL و پیش‌فرض PHP نباید از هم جدا بیفتند."""
    expected = {
        "window_enabled": "0",
        "window_days": "''6,7,1,2,3''",
        "window_start": "''11:00:00''",
        "window_end": "''14:00:00''",
        "window_margin_before_minutes": "30",
        "window_margin_after_minutes": "15",
        "idle_poll_seconds": "60",
        "active_poll_ms": "500",
        "persistent_source_connection": "1",
    }
    for column, default in expected.items():
        pattern = rf"ADD COLUMN {column} [^']*?DEFAULT {re.escape(default)}"
        assert re.search(pattern, MIGRATION), column


def test_fresh_install_schema_has_every_window_column() -> None:
    """install.php فقط schema.sql را اجرا می‌کند و سراغ migrations/ نمی‌رود؛
    بدون این ستون‌ها یک نصب تازه قابلیت پنجره را اصلاً نمی‌دید."""
    for sql, label in ((SCHEMA, "schema.sql"), (EMPTY_DB, "install-empty-db.sql")):
        table = sql[sql.index("CREATE TABLE IF NOT EXISTS food_ticket_config") :]
        table = table[: table.index("ENGINE=InnoDB")]
        for column in WINDOW_COLUMNS:
            assert f"{column} " in table, f"{label} ← {column}"


def test_fresh_install_defaults_match_the_migration() -> None:
    """نصب تازه و نصب ارتقایی باید دقیقاً یک رفتار بدهند."""
    expected = {
        "window_enabled": "0",
        "window_days": "'6,7,1,2,3'",
        "window_start": "'11:00:00'",
        "window_end": "'14:00:00'",
        "window_margin_before_minutes": "30",
        "window_margin_after_minutes": "15",
        "idle_poll_seconds": "60",
        "active_poll_ms": "500",
        "persistent_source_connection": "1",
    }
    for sql, label in ((SCHEMA, "schema.sql"), (EMPTY_DB, "install-empty-db.sql")):
        for column, default in expected.items():
            pattern = rf"{column} [^,]*?DEFAULT {re.escape(default)}"
            assert re.search(pattern, sql), f"{label} ← {column}"


def test_updater_enforces_the_incremental_prerequisite() -> None:
    """بستهٔ افزایشی روی نسخهٔ پایین‌تر = نصب نیمه‌کارهٔ بی‌صدا."""
    assert "function system_update_requires_violation(" in UPDATER
    assert "system_update_requires_violation($current, $manifest['requires_version'] ?? null, $mode, $ignoreRequires)" in UPDATER
    assert "bool $ignoreRequires = false" in UPDATER


def test_updater_prerequisite_has_an_explicit_escape_hatch() -> None:
    guard = UPDATER[UPDATER.index("function system_update_requires_violation(") :][:1800]
    assert "if ($ignore || $mode === 'downgrade') {" in guard
    # بسته‌های قدیمی بدون این کلید نباید مسدود شوند.
    assert "return null; // بسته‌های قدیمی" in guard
    assert 'name="ignore_requires"' in INDEX
    assert "!empty($_POST['ignore_requires'])" in (
        ROOT / "actions/backup-actions.php"
    ).read_text(encoding="utf-8")


def test_window_disabled_by_default_so_upgrades_keep_old_behaviour() -> None:
    """نصب موجود پس از آپدیت باید دقیقاً مثل قبل کار کند تا ادمین پنجره را روشن کند."""
    assert re.search(r"ADD COLUMN window_enabled TINYINT\(1\) NOT NULL DEFAULT 0", MIGRATION)
    assert re.search(r"'window_enabled'\s*=>\s*0", WINDOW)


# ───────────────────── ماژول پنجره و اتصال آن ─────────────────────


def test_window_module_exposes_the_public_helpers() -> None:
    for fn in (
        "food_ticket_window_defaults",
        "food_ticket_window_days",
        "food_ticket_window_time",
        "food_ticket_window_state",
        "food_ticket_window_next_open",
        "food_ticket_window_is_holiday",
        "food_ticket_window_save",
    ):
        assert f"function {fn}(" in WINDOW, fn


def test_window_module_is_loaded_before_config_defaults() -> None:
    load = TICKET.index("/food-ticket-window.php'")
    config = TICKET.index("function food_ticket_config(")
    assert load < config, "پنجره باید پیش از food_ticket_config() بارگذاری شود"


def test_config_reads_window_columns_defensively() -> None:
    """نصبی که مهاجرت ۰۳۱ را اجرا نکرده نباید با Unknown column بشکند."""
    assert "food_ticket_config_has_column" in TICKET
    assert "function food_ticket_config_has_column(" in ENV
    assert "foreach (array_keys(food_ticket_window_defaults()) as $windowColumn)" in TICKET


def test_window_reuses_the_single_holiday_calendar() -> None:
    """تعطیلات نباید در دو جا نگهداری شوند."""
    assert "food_ticket_auto_receipt_holiday" in WINDOW
    assert "function food_ticket_auto_receipt_holiday(" in (
        ROOT / "food-ticket-auto-receipts.php"
    ).read_text(encoding="utf-8")


def test_window_save_only_writes_existing_columns() -> None:
    assert "if (!food_ticket_config_has_column($column)) {" in WINDOW
    assert "UPDATE food_ticket_config SET ' . implode(', ', $sets)" in WINDOW


def test_save_config_persists_window_without_breaking_printer_save() -> None:
    assert "food_ticket_window_save($data)" in TICKET
    saved = TICKET[TICKET.index("$windowSaved = []") : TICKET.index("food_ticket_config(true);\n    save_audit")]
    assert "try {" in saved and "catch (Throwable $windowError)" in saved


# ──────────────────────── دروازهٔ ورکر ────────────────────────


def test_worker_gates_access_behind_the_window() -> None:
    assert "food_ticket_window_state($config, $tehranNow)" in WORKER
    assert "$readSource = !$queueOnly && ($windowOpen || $finalSweepPending);" in WORKER
    assert "if ($readSource) {" in WORKER


def test_worker_never_kills_itself_outside_the_window() -> None:
    """خاموش کردن پروسه ⇒ حلقهٔ ری‌استارت تسک ویندوز. فقط بیکار می‌شود."""
    gate = WORKER[WORKER.index("دروازهٔ پنجرهٔ کاری") : WORKER.index("$cycle['window']")]
    assert "exit(" not in gate
    assert "break;" not in gate
    assert "$cycle['idle'] = true;" in WORKER


def test_worker_still_drains_the_print_queue_outside_the_window() -> None:
    """فیشی که چاپگر نپذیرفته نباید تا فردا معطل بماند."""
    assert "$foodWorkerPendingPrints" in WORKER
    assert "$drainQueue = $queueOnly || $readSource || $pendingPrints > 0;" in WORKER
    assert "if ($drainQueue) {" in WORKER


def test_queue_only_mode_never_touches_access() -> None:
    """--queue-only فقط صف چاپ را تخلیه می‌کند و نباید درگیر پنجره شود."""
    assert "$readSource = !$queueOnly && ($windowOpen || $finalSweepPending);" in WORKER
    assert "$drainQueue = $queueOnly || $readSource || $pendingPrints > 0;" in WORKER


def test_worker_sweeps_once_after_every_restart() -> None:
    """تسک ویندوز هر ۶ ساعت Worker را تازه می‌کند؛ نباید ترددی بین دو اجرا گم شود."""
    assert "$previousWindowOpen = null;" in WORKER
    assert "if ($previousWindowOpen !== false && !$windowOpen) {" in WORKER


def test_worker_runs_a_final_sweep_when_the_window_closes() -> None:
    """ردیف‌های روز قبل هرگز دوباره خوانده نمی‌شوند؛ جارو تنها فرصت آخرین تردد است."""
    assert "if ($previousWindowOpen !== false && !$windowOpen) {" in WORKER
    assert "$finalSweepPending = true;" in WORKER
    assert "$cycle['final_sweep'] = true;" in WORKER


def test_auto_receipts_are_not_blocked_by_the_window() -> None:
    """کار روزانهٔ ثبت فیش خودکار باید حتی در روز تعطیل اجرا شود."""
    auto = WORKER.index("food_ticket_auto_receipts_run(")
    gate = WORKER.index("دروازهٔ پنجرهٔ کاری")
    assert auto < gate, "auto-receipts باید پیش از دروازه اجرا شود"


# ─────────────────── کاهش تأخیر چاپ ───────────────────


def test_worker_no_longer_reconnects_mysql_every_cycle() -> None:
    """db(true) در هر چرخه = یک TCP connect کامل در هر چرخه."""
    # خطوط توضیحی کنار گذاشته می‌شوند تا فقط کد اجرایی سنجیده شود.
    code = "\n".join(l for l in WORKER.splitlines() if not l.lstrip().startswith("//"))
    assert code.count("db(true)") == 1, "db(true) فقط یک بار و فقط در مسیر خطا"
    first = code.index("db()->query('SELECT 1')")
    assert "db(true)" not in code[:first], "db(true) نباید پیش از آزمون اتصال صدا زده شود"
    assert code.index("db(true);") > code.index("} catch (Throwable) {")


def test_worker_caches_settings_with_a_ttl() -> None:
    assert "const FOOD_WORKER_CONFIG_TTL = 10;" in WORKER
    assert "($nowFloat - $configLoadedAt) >= FOOD_WORKER_CONFIG_TTL" in WORKER
    # setting_forget_cache باید به همان ریتم گره بخورد، نه هر چرخه.
    block = WORKER[WORKER.index("$nowFloat = microtime(true);") : WORKER.index("دروازهٔ پنجرهٔ کاری")]
    assert "setting_forget_cache();" in block


def test_odbc_handle_is_pooled_and_releasable() -> None:
    for fn in ("food_ticket_odbc_pool", "food_ticket_odbc_release", "food_ticket_odbc_pool_handle"):
        assert f"function {fn}(" in TICKET, fn
    # بستن اتصال در پایان هر چرخه نباید هندل نگه‌داشته‌شده را ببندد.
    close = TICKET[TICKET.index("function food_ticket_access_close(") :][:700]
    assert "food_ticket_odbc_pool()" in close and "return;" in close


def test_pool_is_released_on_error_and_when_the_window_closes() -> None:
    assert "food_ticket_odbc_pool($readSource && !empty($windowState['persistent_source']))" in WORKER
    assert "food_ticket_odbc_release();" in WORKER
    assert "(int) ($cycle['errors'] ?? 0) > 0 && function_exists('food_ticket_odbc_release')" in WORKER


def test_pool_handle_expires_so_a_dead_connection_cannot_persist() -> None:
    assert "(time() - $pooledAt) > 600" in TICKET


def test_worker_has_three_distinct_poll_rhythms() -> None:
    sleep = WORKER[WORKER.index("سه ریتم:") :]
    assert "usleep(min(200000, $activeUs));" in sleep  # رگبار تردد
    assert "$idleSeconds = max(5, (int) ($windowState['idle_poll_seconds'] ?? 60));" in sleep
    assert "usleep($activeUs);" in sleep


def test_idle_sleep_is_chunked_so_max_minutes_still_works() -> None:
    sleep = WORKER[WORKER.index("سه ریتم:") :]
    assert "for ($slept = 0; $slept < $idleSeconds; $slept++)" in sleep
    assert "$maxMinutes > 0 && (time() - $startedAt) >= $maxMinutes * 60" in sleep


# ───────────────── تب تقویم تعطیلات زیر تنظیمات ─────────────────


def test_holiday_board_markup_lives_in_one_place() -> None:
    assert "function holiday_board_render(" in BOARD
    assert "require_once __DIR__ . '/holiday-board.php';" in INDEX
    # صفحهٔ مستقل باید همان تابع را صدا بزند، نه markup تکراری.
    page = INDEX[INDEX.index("if ($page === 'holidays') {") :][:900]
    assert "holiday_board_render($user);" in page
    assert "data-year-holiday-board" not in page


def test_settings_page_has_a_holidays_tab() -> None:
    assert "$canHolidaysView = user_can($user, 'holidays.view');" in INDEX
    assert """$tabLinks .= '<a href="#holidays">تقویم و تعطیلات</a>';""" in INDEX
    assert """echo '<div class="settings-panel" id="holidays">';""" in INDEX
    assert "holiday_board_render($user, 'settings');" in INDEX


def test_holiday_tab_respects_the_view_permission() -> None:
    panel = INDEX[INDEX.index("""echo '<div class="settings-panel" id="holidays">';""") - 300 :][:400]
    assert "if ($canHolidaysView) {" in panel
    assert "user_can($user, 'holidays.manage')" in BOARD


def test_holiday_actions_return_to_the_originating_tab() -> None:
    assert "$holidayReturnTo = static function (): string {" in ACTIONS
    assert "index.php?page=settings#holidays" in ACTIONS
    assert "redirect('index.php?page=holidays');" not in ACTIONS
    assert ACTIONS.count("redirect($holidayReturnTo());") == 5


def test_every_holiday_form_carries_return_to() -> None:
    """۴ فرم (افزودن، تیک روز، ورود فایل، حذف) باید return_to بفرستند."""
    assert BOARD.count("csrf_field() . $returnField") == 4
    assert """'<input type="hidden" name="return_to" value="' . e($returnTo) . '">'""" in BOARD


def test_year_board_select_attribute_is_not_broken() -> None:
    """باگ قدیمی: نقل‌قول اضافی باعث می‌شد انتخاب سال با JS پیدا نشود."""
    assert 'data-year-board-year">' not in BOARD
    assert "<select data-year-board-year>" in BOARD
    assert "querySelector('[data-year-board-year]')" in (
        ROOT / "assets/jalali-calendar.js"
    ).read_text(encoding="utf-8")


# ───────────────────────── پنل وب ─────────────────────────


def test_panel_exposes_window_fields() -> None:
    for name in (
        "windowEnabled",
        "windowStart",
        "windowEnd",
        "windowMarginBefore",
        "windowMarginAfter",
        "activePollMs",
        "idlePollSeconds",
        "persistentSourceConnection",
    ):
        assert f'name="{name}"' in PANEL, name


def test_week_days_render_on_one_row_not_stacked() -> None:
    """ریشهٔ باگ: کانتینر روزها کلاس .field داشت و .field{flex-direction:column}
    هیچ‌جا بازنویسی نمی‌شد، پس هر هفت روز زیر هم می‌افتادند."""
    assert '<div class="window-days">' in PANEL
    assert 'class="field full window-days"' not in PANEL, "کانتینر نباید .field باشد"
    rule = _css_rule(PANEL, ".window-days")
    assert "flex-direction:row" in rule, "جهت باید صریحاً row باشد"
    assert "display:flex" in rule
    assert "grid-column:1/-1" in rule, "باید کل عرض فرم را بگیرد"


def test_every_week_day_is_still_rendered() -> None:
    assert PANEL.count("[6,'شنبه']") == 1
    labels = ("شنبه", "یک‌شنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنج‌شنبه", "جمعه")
    row = PANEL[PANEL.index("const WINDOW_DAY_LABELS=") :][:220]
    for label in labels:
        assert f"'{label}'" in row, label
    assert '<span class="window-days-label">روزهای کاری</span>' in PANEL


def test_six_window_fields_sit_in_two_rows_of_three() -> None:
    assert '<div class="window-grid">' in PANEL
    block = PANEL[PANEL.index('<div class="window-grid">') :]
    block = block[: block.index("</div>")]
    assert block.count('<label class="field">') == 6, "دقیقاً شش فیلد"
    rule = _css_rule(PANEL, ".window-grid")
    assert "grid-template-columns:repeat(3,minmax(0,1fr))" in rule, "سه ستون"
    assert "grid-column:1/-1" in rule


def test_time_inputs_are_forced_to_24_hour() -> None:
    """input[type=time] قالبش را از locale می‌گیرد؛ lang=en-GB یعنی ۲۴ساعته."""
    for name in ("windowStart", "windowEnd"):
        field = PANEL[PANEL.index(f'name="{name}"') :][:140]
        assert 'type="time"' in field, name
        assert 'lang="en-GB"' in field, name
        assert 'dir="ltr"' in field, name
        assert 'step="60"' in field, name


def test_time_picker_icon_stays_attached_to_its_field() -> None:
    assert "::-webkit-calendar-picker-indicator" in PANEL
    assert ".window-grid input[type=time]" in PANEL


def test_database_time_value_is_trimmed_for_the_time_input() -> None:
    """ستون TIME مقدار 11:00:00 می‌دهد ولی input با step=60 فقط HH:MM می‌پذیرد."""
    assert "function hhmm(value){" in PANEL
    assert "esc(hhmm(w.start))" in PANEL
    assert "esc(hhmm(w.end))" in PANEL
    assert "normalizeDigits(value)" in PANEL[PANEL.index("function hhmm(value){") :][:260]


def test_panel_version_was_bumped_with_the_panel_markup() -> None:
    """اگر این دو عدد یکی نباشند، بنر «پنل قدیمی» به‌اشتباه روشن می‌شود."""
    web = re.search(r"const PANEL_VERSION='([\d.]+)'", PANEL)
    php = re.search(r"const FOOD_TICKET_PANEL_VERSION = '([\d.]+)'", TICKET)
    assert web and php
    assert web.group(1) == php.group(1), (web.group(1), php.group(1))


def test_help_guide_explains_which_package_to_install() -> None:
    guide = (ROOT / "help-guide.php").read_text(encoding="utf-8")
    assert 'id="update-packages"' in guide
    assert '<a href="#update-packages">' in guide, "باید در فهرست مطالب باشد"
    assert "VERSION" in guide
    for version in ("1.62.77", "1.62.75", "1.62.74"):
        assert version in guide, version
    assert "install.php" in guide and "migrations/" in guide, "هشدار سورس کامل"
    assert "restart-food-worker-SERVICE.bat" in guide


def test_panel_sends_window_payload_on_save() -> None:
    assert "function windowPayload(form,o){" in PANEL
    assert PANEL.count("windowPayload(f,o)") == 2, "هر دو مسیر ذخیره (آنلاین و محلی)"
    for key in WINDOW_COLUMNS:
        assert f"{key}:" in PANEL, key


def test_panel_reads_window_state_from_the_api() -> None:
    assert "if(cfg.window!=null)state.settings.window=cfg.window;" in PANEL
    assert "'window' => function_exists('food_ticket_window_state')" in TICKET


def test_panel_warns_when_the_migration_has_not_run() -> None:
    assert "food_ticket_config_has_column('window_enabled')" in TICKET
    assert "مهاجرت ۰۳۱ را اجرا کنید" in PANEL


def test_window_api_fields_require_the_db_edit_permission() -> None:
    db_fields = TICKET[TICKET.index("$dbFields = [") :][:900]
    for key in ("window_enabled", "window_start", "active_poll_ms", "persistent_source_connection"):
        assert key in db_fields, key
    assert "if ($hasDbFields && !user_can($user, 'food.db_edit')) {" in TICKET
