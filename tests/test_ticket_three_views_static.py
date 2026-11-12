"""نگهبان ایستای «نما تابع نسبت کاربر با همین درخواست است، نه نقش سراسری او».

### مسئله

سامانه سه گروه مخاطب دارد — ثبت‌کننده، کارشناس مسئول، سرپرست — ولی صفحهٔ درخواست
تصمیمش را از ترکیب پراکندهٔ چند بولین می‌گرفت که بعضی‌هایشان **نقش سراسری** کاربر
را می‌دیدند، نه رابطه‌اش با همین درخواست.

نتیجهٔ عملی: کارشناس یا مدیری که خودش درخواستی ثبت می‌کرد، روی درخواست **خودش**
نمای رسیدگی‌کننده می‌گرفت — پنل ارجاع، تغییر وضعیت، و حتی یادداشت‌های داخلی تیم
رسیدگی‌کننده دربارهٔ همان درخواست.

### چرا در خود کد هم رد پایش بود

`ticket_reply_starts_work()` در `lib/auth-scope.php` این قاعده را صریح نوشته بود:
«فقط رسیدگی‌کننده، هرگز ثبت‌کننده» و برای همین دستی `!ticket_user_is_requester()`
را به `ticket_handler_level()` می‌چسباند. همین وصله در دو جای دیگر هم تکرار شده
بود — و در پنج جای دیگر جا افتاده بود. یعنی قاعده درست بود ولی در تابع مرجع
ننشسته بود.
"""

from __future__ import annotations

import io
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def read(relative_path: str) -> str:
    with io.open(os.path.join(ROOT, relative_path), encoding="utf-8") as handle:
        return handle.read()


def php_function(relative_path: str, signature: str) -> str:
    source = read(relative_path)
    start = source.index(signature)
    end = source.index("\nfunction ", start + 10)
    return source[start:end]


def strip_php_comments(source: str) -> str:
    source = re.sub(r"/\*.*?\*/", " ", source, flags=re.S)
    source = re.sub(r"//[^\n]*", " ", source)
    return source


# ───────── ۱) قاعده در تابع مرجع بنشیند، نه وصله‌های پراکنده ─────────

def test_handler_level_denies_supervisor_authority_over_your_own_ticket():
    code = strip_php_comments(
        php_function("lib/auth-scope.php", "function ticket_handler_level(array $ticket, array $user): string")
    )
    assert "ticket_user_is_requester($ticket, $user)" in code, (
        "تابع مرجع باید خودش بداند که کاربر ثبت‌کنندهٔ همین درخواست است"
    )
    requester_at = code.index("ticket_user_is_requester($ticket, $user)")
    manager_at = code.index("return 'manager';")
    assert requester_at < manager_at, (
        "بررسی ثبت‌کننده باید پیش از اعطای اختیار سرپرستی انجام شود"
    )


def test_a_requester_who_is_also_the_assigned_agent_can_still_work():
    """تفکیک وظایف نباید کار را قفل کند: اگر واقعاً مسئول همان درخواست است، کار می‌کند."""
    code = strip_php_comments(
        php_function("lib/auth-scope.php", "function ticket_handler_level(array $ticket, array $user): string")
    )
    # روی بلوکِ «اگر ثبت‌کننده است» لنگر بزن، نه اولینِ هر جای تابع:
    # فراخوان Manager-Tree (isManagerRequester) زودتر از آنجا می‌آید و پنجرهٔ ۶۰۰ نویسه‌ای را منحرف می‌کرد.
    block = code[code.index("if (ticket_user_is_requester($ticket, $user)) {"):]
    assert "'assignee'" in block[:600], (
        "ثبت‌کننده‌ای که کارشناس مسئول همین درخواست است باید نمای کارشناس بگیرد"
    )
    assert "assigned_to" in block[:600]


def test_reply_guard_keeps_its_explicit_requester_check():
    """این گارد باید بماند: کسی که هم ثبت‌کننده و هم مسئول است، با پاسخ دادن
    نباید وضعیت را به‌طور ضمنی جلو ببرد."""
    code = strip_php_comments(
        php_function("lib/auth-scope.php", "function ticket_reply_starts_work(array $ticket, array $user): bool")
    )
    assert "!ticket_user_is_requester($ticket, $user)" in code


# ───────── ۲) سه نما یک منبع حقیقت داشته باشد ─────────

def test_a_single_function_answers_which_view_to_render():
    source = read("lib/auth-scope.php")
    assert "function ticket_view_mode(array $ticket, array $user): string" in source, (
        "باید یک تابع مرجع برای «کدام نما» وجود داشته باشد"
    )
    code = strip_php_comments(php_function("lib/auth-scope.php", "function ticket_view_mode("))
    for mode in ("'supervisor'", "'assignee'", "'requester'"):
        assert mode in code, f"نمای {mode} برگردانده نمی‌شود"
    assert "ticket_handler_level($ticket, $user)" in code, (
        "نما باید از همان تابع مرجع اختیار مشتق شود، نه منطق موازی"
    )


def test_the_ticket_page_uses_the_single_view_function():
    source = read("modules/request-center/Pages/detail.php")
    assert "ticket_view_mode($ticket, $user)" in source, (
        "صفحهٔ درخواست باید نما را از تابع مرجع بگیرد"
    )
    assert "data-view-mode=" in source, (
        "نمای فعال باید در خروجی قابل تشخیص باشد تا رفتار سه نما آزمون‌پذیر بماند"
    )


# ───────── ۳) یادداشت داخلی: رابطه، نه نقش سراسری ─────────

def test_internal_notes_are_hidden_from_the_requester_even_if_they_are_staff():
    source = read("lib/auth-scope.php")
    assert "function ticket_can_see_internal(array $ticket, array $user): bool" in source, (
        "باید تابعی باشد که دیدن یادداشت داخلی را بر پایهٔ رابطه تصمیم بگیرد"
    )
    code = strip_php_comments(php_function("lib/auth-scope.php", "function ticket_can_see_internal("))
    assert "ticket_user_is_requester($ticket, $user)" in code
    assert "return false;" in code, "ثبت‌کننده نباید یادداشت داخلی درخواست خودش را ببیند"
    assert "assigned_to" in code, (
        "کارشناسی که مسئول همان درخواست است استثناست و باید ببیند"
    )


def test_message_list_no_longer_decides_by_global_role():
    source = read("index.php")
    combined = source + "\n" + read("modules/request-center/Pages/detail.php") + "\n" + read("modules/request-center/lib/endpoints.php")
    assert "if ((int) $message['is_internal'] === 1 && !$staff) {" not in source, (
        "فیلتر یادداشت داخلی هنوز از نقش سراسری تصمیم می‌گیرد"
    )
    assert "ticket_can_see_internal(" in combined


def test_internal_attachment_download_uses_the_same_rule():
    source = read("index.php")
    assert "$canDownloadInternal = is_staff_role($downloadUser['role']) || user_is_primary_admin($downloadUser);" not in source, (
        "دانلود پیوست داخلی هنوز از نقش سراسری تصمیم می‌گیرد"
    )
    endpoints = read("modules/request-center/lib/endpoints.php")
    download_block = endpoints[endpoints.index("request_center_download_attachment"):]
    assert "ticket_can_see_internal($attachmentTicket, $downloadUser)" in download_block[:1400], (
        "دانلود پیوست داخلی باید همان قاعدهٔ رابطه‌محور را به کار ببرد"
    )


# ───────── ۴) محافظت‌هایی که نباید خراب شوند ─────────

def test_scope_isolation_rules_are_untouched():
    code = strip_php_comments(
        php_function("lib/auth-scope.php", "function ticket_handler_level(array $ticket, array $user): string")
    )
    assert "ticket_user_can_view($ticket, $user)" in code, "بررسی دسترسی دید باید اول بماند"
    assert "support_manager" in code, "جداسازی حوزهٔ مدیر پشتیبانی باید بماند"
    assert "user_service_group($user)" in code, "جداسازی گروه خدمت باید بماند"
    assert "ticket.assign" in code, "اختیار سرپرستی همچنان به مجوز ارجاع گره خورده است"
