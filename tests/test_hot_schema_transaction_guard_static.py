"""نگهبان ایستای «مهاجرت خودکار اسکیما هرگز داخل تراکنش اجرا نشود».

### چرا این از یک کندیِ ساده خیلی بدتر است

در MySQL هر دستور DDL یک **COMMIT ضمنی** می‌زند. یعنی اگر در میانهٔ یک تراکنش
یک `ALTER TABLE` اجرا شود، هرچه تا آن لحظه نوشته شده بی‌صدا نهایی می‌شود و
`rollBack()` بعدی دیگر چیزی برای برگرداندن ندارد. نتیجه: نیمهٔ اول یک عملیات
ثبت می‌ماند و نیمهٔ دوم نه، بدون هیچ خطایی.

### این حادثه واقعاً رخ داده است

سرآیند `ticket_number_prefix()` در `lib/tickets-sla.php` همین را مستند کرده:
خطای «There is no active transaction» وقتی `ticket_number_next()` داخل تراکنش
ثبت درخواست یک `CREATE TABLE IF NOT EXISTS` اجرا می‌کرد. درخواست ثبت می‌شد ولی
`db()->commit()` پایانی خطا می‌داد. به همین دلیل `ticket_number_ensure_schema_run()`
گارد تراکنش گرفت و DDL را به پایان درخواست موکول کرد.

### شکافی که باز مانده بود

`ticket_hot_schema_ensure()` همان درس را نگرفته بود: حدود ۳۵ دستور
`ALTER TABLE` روی `tickets` و `users`، سه تغییر مقدار ENUM، و شاخه‌دادن به
`org_ensure_schema()`، `org_ticket_schema_ensure()` و `lac_ensure_schema()` —
همه بدون بررسی اینکه تراکنشی باز است یا نه.

در مسیر وب معمولاً بی‌خطر است، چون `index.php` آن را در خط ۲۸ و پیش از هر
تراکنش صدا می‌زند. ولی هر نقطهٔ ورود دیگری که این بوت‌استرپ را ندارد و تراکنش
باز می‌کند، اولین فراخوانی را داخل تراکنش انجام می‌دهد.

### دام ظریف `static $ready`

این تابع `$ready = true` را **پیش از** انجام کار می‌گذاشت. اگر گارد اضافه شود
ولی این ترتیب اصلاح نشود، فراخوانی داخل تراکنش تابع را «انجام‌شده» علامت می‌زند
و مهاجرت موکول‌شده هم زودهنگام برمی‌گردد — یعنی ستون‌ها **هرگز** ساخته نمی‌شوند.
تابع گارددارِ موجود دقیقاً به همین دلیل هیچ `static` ندارد.
"""

from __future__ import annotations

import io
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def read(relative_path: str) -> str:
    with io.open(os.path.join(ROOT, relative_path), encoding="utf-8") as handle:
        return handle.read()


def strip_php_comments(source: str) -> str:
    source = re.sub(r"/\*.*?\*/", "", source, flags=re.S)
    source = re.sub(r"(?m)^\s*//.*$", "", source)
    source = re.sub(r"(?m)\s//[^\"'\n]*$", "", source)
    return source


def php_function(relative_path: str, signature: str) -> str:
    source = read(relative_path)
    start = source.index(signature)
    end = source.index("\nfunction ", start + 10)
    return source[start:end]


HOT = "function ticket_hot_schema_ensure("


def hot_body() -> str:
    return strip_php_comments(php_function("lib/tickets-sla.php", HOT))


def test_hot_schema_checks_for_an_open_transaction() -> None:
    body = hot_body()
    assert re.search(r"db_in_transaction\(\)|inTransaction\(\)", body), (
        "ticket_hot_schema_ensure() بدون بررسی تراکنش باز، ~۳۵ دستور ALTER TABLE "
        "اجرا می‌کند؛ هر کدام یک COMMIT ضمنی است."
    )


def test_transaction_check_comes_before_any_ddl() -> None:
    """گاردی که بعد از اولین ALTER بنشیند، هیچ چیز را نجات نمی‌دهد."""
    body = hot_body()
    guard = min(
        (body.index(token) for token in ("db_in_transaction()", "inTransaction()") if token in body),
        default=-1,
    )
    assert guard >= 0, "گارد تراکنش پیدا نشد."
    first_ddl = body.index("ALTER TABLE")
    assert guard < first_ddl, (
        "گارد تراکنش بعد از اولین دستور ALTER TABLE قرار گرفته است."
    )


def test_ready_flag_is_not_set_while_deferring() -> None:
    """دام اصلی: علامت‌زدن «انجام شد» وقتی هنوز کاری نشده.

    اگر `$ready = true` پیش از گارد بماند، فراخوانی داخل تراکنش تابع را سوخته
    می‌کند و مهاجرت موکول‌شده هم بی‌اثر برمی‌گردد؛ یعنی ستون‌ها هرگز ساخته
    نمی‌شوند و این بدتر از مشکل اولیه است.
    """
    body = hot_body()
    ready = body.index("$ready = true;")
    guard = min(
        body.index(token) for token in ("db_in_transaction()", "inTransaction()") if token in body
    )
    assert guard < ready, (
        "$ready = true پیش از گارد تراکنش اجرا می‌شود؛ مهاجرتِ موکول‌شده "
        "زودهنگام برمی‌گردد و ستون‌ها هرگز ساخته نمی‌شوند."
    )


def test_deferred_migration_is_scheduled() -> None:
    """موکول‌کردن یعنی بعداً انجام شود، نه اینکه رها شود."""
    body = hot_body()
    assert "register_shutdown_function" in body, (
        "مهاجرت داخل تراکنش فقط رد می‌شود و هیچ‌وقت دوباره تلاش نمی‌شود."
    )


def test_shutdown_handler_is_registered_only_once() -> None:
    """این تابع ممکن است چند بار در یک درخواست صدا زده شود."""
    body = hot_body()
    assert re.search(r"static \$deferred|\$deferred = true", body), (
        "هر فراخوانی داخل تراکنش یک shutdown handler تازه ثبت می‌کند؛ "
        "باید فقط یک بار ثبت شود."
    )


def test_deferral_is_logged() -> None:
    body = hot_body()
    assert "system_log(" in body, (
        "موکول‌شدن مهاجرت باید لاگ شود، وگرنه هیچ ردی از آن نمی‌ماند."
    )


def test_existing_guard_is_not_regressed() -> None:
    """قفل بازگشتی روی تابعی که این درس را قبلاً گرفته بود."""
    body = strip_php_comments(
        php_function("lib/tickets-sla.php", "function ticket_number_ensure_schema_run(")
    )
    assert "db_in_transaction()" in body, (
        "گارد تراکنش ticket_number_ensure_schema_run() حذف شده است."
    )
    assert "register_shutdown_function" in body, (
        "موکول‌کردن DDL در ticket_number_ensure_schema_run() حذف شده است."
    )


def test_guarded_helper_stays_free_of_static_state() -> None:
    """بدون static بودن، همان چیزی است که موکول‌کردن را ممکن می‌کند."""
    body = strip_php_comments(
        php_function("lib/tickets-sla.php", "function ticket_number_ensure_schema_run(")
    )
    assert "static $" not in body, (
        "ticket_number_ensure_schema_run() حافظهٔ static گرفته است؛ با این کار "
        "تلاش دوبارهٔ پس از پایان تراکنش بی‌اثر می‌شود."
    )


def test_bootstrap_still_runs_migration_outside_any_transaction() -> None:
    """مسیر وب باید همچنان مهاجرت را زود و بیرون از تراکنش انجام دهد."""
    index = read("index.php")
    call = index.index("ticket_number_ensure_schema();")
    # تراکنش‌ها در فایل‌های actions/ باز می‌شوند و index.php آن‌ها را require می‌کند؛
    # پس «بیرون از تراکنش بودن» یعنی این فراخوانی پیش از آن requireها انجام شود.
    first_action_require = min(
        index.index(f"require __DIR__ . '/actions/{name}'")
        for name in ("ticket-actions.php", "holiday-actions.php")
    )
    assert call < first_action_require, (
        "فراخوانی بوت‌استرپ مهاجرت بعد از بارگذاری فایل‌های اکشن افتاده است؛ "
        "آن‌ها تراکنش باز می‌کنند و DDL دیگر بی‌خطر نیست."
    )
