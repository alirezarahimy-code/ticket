"""نگهبان ایستای بستهٔ ۶: «تفکیک وظایف در لحظهٔ ارجاع» و «تقویم کاری قابل تنظیم».

### مسئلهٔ یکم — ارجاع به ثبت‌کننده

بستهٔ ۵ نمای پس از ارجاع را بست: کسی که خودش درخواست را ثبت کرده، دیگر روی آن
درخواست نمای سرپرست نمی‌گیرد. ولی خودِ **لحظهٔ ارجاع** باز ماند.

`ticket_assignee_is_eligible()` تنها دروازهٔ «این کاربر می‌تواند مسئول این درخواست
شود؟» است و هر پنج مصرف‌کننده از آن رد می‌شوند:

  * `choose_handling_agent()` (ارجاع خودکار در لحظهٔ ثبت، دو مسیر مالک و جایگزین)
  * `claim_ticket` (برداشتن درخواست توسط خود کارشناس)
  * ارجاع دستی در `actions/ticket-actions.php`
  * دکمهٔ «برداشتن درخواست» در `index.php`
  * فهرست کشویی کارشناسان در پنل ارجاع

ولی این تابع هیچ‌وقت `requester_id` را نگاه نمی‌کرد — با آنکه `choose_handling_agent()`
همان ابتدا `requester_id` را داخل آرایهٔ درخواست می‌گذارد و به این تابع می‌دهد. یعنی
داده از قبل آنجا بود و فقط سنجیده نمی‌شد.

### دریچهٔ اضطراری

بستن کامل این مسیر یک خطر عملیاتی دارد: اگر یک واحد فقط **یک** رسیدگی‌کننده داشته
باشد و خودِ او درخواست ثبت کند، هیچ‌کس برای ارجاع نمی‌ماند و درخواست در
`manager_review` می‌ماند. برای همین کلید `security.allow_self_assignment` اضافه شده
که پیش‌فرضش «بسته» است و فقط در چنین واحدهایی باز می‌شود.

### مسئلهٔ دوم — ساعت کاری هاردکد

`next_business_start()`، `business_due_at()` و `business_elapsed_minutes()` هر سه
ساعت ۸ تا ۱۶ و تعطیلی پنج‌شنبه/جمعه را **در متن کد** داشتند؛ سه نسخهٔ جدا از یک
قاعده. سازمانی با شیفت دیگر یا تعطیلی فقط جمعه راهی برای تغییرش نداشت.

### چرا اعتبارسنجی اینجا حیاتی است

`business_due_at()` حلقه‌ای دارد که تا تمام‌شدن دقیقه‌های مهلت جلو می‌رود. اگر ساعت
پایان کوچک‌تر یا مساوی ساعت شروع شود، ظرفیت هر روز صفر یا منفی می‌شود و حلقه
**هرگز تمام نمی‌شود**. همین‌طور اگر هر هفت روز هفته تعطیل اعلام شود،
`next_business_start()` بی‌نهایت جلو می‌رود. پس قابل‌تنظیم‌کردن این مقادیر بدون
مهار کردنشان، یک حلقهٔ بی‌نهایت در مسیر درخواست می‌سازد. نگهبان‌های زیر دقیقاً
همین مهار را تضمین می‌کنند.
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
    """کامنت‌ها را برمی‌دارد تا assertها متن توضیحی را به‌جای کد نشمارند."""
    source = re.sub(r"/\*.*?\*/", "", source, flags=re.S)
    source = re.sub(r"(?m)^\s*//.*$", "", source)
    source = re.sub(r"(?m)\s//[^\"'\n]*$", "", source)
    return source


def php_function(relative_path: str, signature: str) -> str:
    source = read(relative_path)
    start = source.index(signature)
    end = source.index("\nfunction ", start + 10)
    return source[start:end]


# ───────────────────────── محور ۱: ارجاع به ثبت‌کننده ─────────────────────────


def test_requester_guard_runs_before_primary_admin_shortcut() -> None:
    """ترتیب مهم است: میان‌بُر «ادمین اصلی» بدون قید، گارد را کامل دور می‌زند.

    `ticket_assignee_is_eligible()` برای ادمین اصلی زودهنگام `return $group === 'it'`
    می‌کند. اگر گارد ثبت‌کننده بعد از آن بنشیند، هیچ‌وقت اجرا نمی‌شود و ادمین اصلی
    همچنان مسئول درخواست خودش می‌شود — یعنی دقیقاً پرخطرترین حالت باز می‌ماند.
    """
    body = strip_php_comments(
        php_function("lib/auth-scope.php", "function ticket_assignee_is_eligible(")
    )
    guard = body.index("ticket_user_is_requester")
    shortcut = body.index("user_is_primary_admin")
    assert guard < shortcut, (
        "گارد ثبت‌کننده بعد از میان‌بُر user_is_primary_admin() قرار گرفته است؛ "
        "ادمین اصلی می‌تواند مسئول درخواست خودش شود."
    )


def test_eligibility_gate_compares_candidate_against_requester() -> None:
    """باید شناسهٔ کاندیدا با شناسهٔ ثبت‌کننده مقایسه شود، نه صرفاً کلید خوانده شود."""
    body = strip_php_comments(
        php_function("lib/auth-scope.php", "function ticket_assignee_is_eligible(")
    )
    assert re.search(r"ticket_user_is_requester\s*\(", body), (
        "مقایسهٔ ثبت‌کننده باید از تابع مرجع ticket_user_is_requester() بیاید "
        "تا منطق موازی ساخته نشود."
    )


def test_self_assignment_has_configurable_escape_hatch() -> None:
    """واحد تک‌نفره باید بتواند قاعده را آگاهانه باز کند."""
    body = strip_php_comments(
        php_function("lib/auth-scope.php", "function ticket_assignee_is_eligible(")
    )
    assert "security.allow_self_assignment" in body, (
        "دریچهٔ اضطراری security.allow_self_assignment وجود ندارد؛ "
        "واحدی که تنها یک رسیدگی‌کننده دارد قفل می‌شود."
    )


def test_escape_hatch_defaults_to_closed() -> None:
    """پیش‌فرض باید «بسته» باشد وگرنه قاعده عملاً اجرا نمی‌شود."""
    body = strip_php_comments(
        php_function("lib/auth-scope.php", "function ticket_assignee_is_eligible(")
    )
    match = re.search(
        r"security\.allow_self_assignment'\s*,\s*(\w+)", body
    )
    assert match is not None, "فراخوانی کلید تنظیمات با مقدار پیش‌فرض پیدا نشد."
    assert match.group(1) == "false", (
        "پیش‌فرض security.allow_self_assignment باید false باشد "
        f"ولی {match.group(1)} است."
    )


def test_escape_hatch_tolerates_missing_config_layer() -> None:
    """این تابع در بافت‌هایی بدون bootstrap هم فراخوانی می‌شود."""
    body = strip_php_comments(
        php_function("lib/auth-scope.php", "function ticket_assignee_is_eligible(")
    )
    assert "function_exists('cfg')" in body, (
        "خواندن cfg() باید با function_exists('cfg') محافظت شود؛ "
        "وگرنه در بافت بدون لایهٔ تنظیمات خطای کشنده می‌دهد."
    )


# ───────────────────────── محور ۲: تقویم کاری قابل تنظیم ─────────────────────────

BUSINESS_FUNCTIONS = (
    "function next_business_start(",
    "function business_due_at(",
    "function business_elapsed_minutes(",
)


def test_business_hours_have_single_source() -> None:
    """هر سه تابع باید از یک منبع واحد ساعت کاری بخوانند."""
    source = read("lib/tickets-sla.php")
    assert "function sla_business_hours(" in source, (
        "تابع مرجع sla_business_hours() وجود ندارد؛ ساعت کاری همچنان "
        "در سه تابع جداگانه تکرار شده است."
    )


def test_no_hardcoded_clock_hours_remain() -> None:
    """ساعت ۸ و ۱۶ نباید در متن این سه تابع بماند."""
    offenders = []
    for signature in BUSINESS_FUNCTIONS:
        body = strip_php_comments(php_function("lib/tickets-sla.php", signature))
        for literal in re.findall(r"setTime\(\s*(\d+)\s*,", body):
            offenders.append(f"{signature.strip('function (')} → setTime({literal}")
    assert not offenders, (
        "ساعت کاری هنوز در متن کد هاردکد است: " + ", ".join(offenders)
    )


def test_no_hardcoded_weekend_days_remain() -> None:
    """آرایهٔ [4, 5] نباید در متن این سه تابع بماند."""
    offenders = []
    for signature in BUSINESS_FUNCTIONS:
        body = strip_php_comments(php_function("lib/tickets-sla.php", signature))
        if re.search(r"\[\s*4\s*,\s*5\s*\]", body):
            offenders.append(signature)
    assert not offenders, (
        "روزهای تعطیل هفتگی هنوز هاردکد است در: " + ", ".join(offenders)
    )


def test_work_hours_guard_against_inverted_range() -> None:
    """ساعت پایان ≤ شروع یعنی حلقهٔ بی‌نهایت در business_due_at."""
    body = strip_php_comments(
        php_function("lib/tickets-sla.php", "function sla_business_hours(")
    )
    assert re.search(r"<=\s*\$start|\$end\s*<=|\$start\s*>=\s*\$end", body), (
        "sla_business_hours() بازهٔ وارونه (پایان ≤ شروع) را مهار نمی‌کند؛ "
        "این یعنی حلقهٔ بی‌نهایت در business_due_at()."
    )


def test_work_hours_guard_against_full_week_closure() -> None:
    """اگر هر هفت روز تعطیل شود، next_business_start بی‌نهایت جلو می‌رود."""
    body = strip_php_comments(
        php_function("lib/tickets-sla.php", "function sla_business_hours(")
    )
    assert re.search(r"count\(\s*\$weekend\s*\)\s*>=?\s*[67]", body), (
        "sla_business_hours() حالت «همهٔ هفته تعطیل» را مهار نمی‌کند؛ "
        "این یعنی حلقهٔ بی‌نهایت در next_business_start()."
    )


def test_work_hours_are_editable_from_calendar_page() -> None:
    """تنظیمی که رابط کاربری ندارد، عملاً قابل تنظیم نیست.

    اینجا عمداً روی «فراخوانی» assert می‌شود نه «وجود تعریف تابع»: صرفِ تعریف‌شدن
    `holiday_board_work_hours_form()` ثابت نمی‌کند که فرم واقعاً رندر می‌شود. نسخهٔ
    قبلی همین تست با حذف نقطهٔ فراخوانی هم سبز می‌ماند.
    """
    board = strip_php_comments(read("holiday-board.php"))
    assert "function holiday_board_work_hours_form(" in board, (
        "سازندهٔ فرم تقویم کاری تعریف نشده است."
    )
    render = board[board.index("function holiday_board_render(") :]
    assert "holiday_board_work_hours_form(" in render, (
        "فرم تقویم کاری ساخته شده ولی داخل holiday_board_render() رندر نمی‌شود؛ "
        "صفحه عملاً راهی برای ویرایش ساعت کاری ندارد."
    )
    assert "save_work_hours" in board, (
        "فرم تقویم کاری اکشن save_work_hours را ارسال نمی‌کند."
    )


def test_work_hours_action_is_permission_guarded() -> None:
    """ذخیرهٔ ساعت کاری باید همان مجوز تقویم را بخواهد."""
    actions = strip_php_comments(read("actions/holiday-actions.php"))
    assert "save_work_hours" in actions, (
        "اکشن save_work_hours در actions/holiday-actions.php پیاده نشده است."
    )
    start = actions.index("'save_work_hours'")
    block = actions[start : start + 1800]
    assert "require_permission('holidays.manage')" in block, (
        "اکشن save_work_hours بدون بررسی مجوز holidays.manage است."
    )


def test_calendar_page_text_is_not_stale() -> None:
    """متن صفحه نباید تعطیلی هفتگی را ثابت اعلام کند وقتی قابل تنظیم شده."""
    page = read("index.php")
    assert "پنج‌شنبه و جمعه تعطیل ثابت" not in page, (
        "متن صفحهٔ تقویم هنوز پنج‌شنبه و جمعه را «تعطیل ثابت» معرفی می‌کند، "
        "در حالی که این روزها اکنون قابل تنظیم‌اند."
    )
