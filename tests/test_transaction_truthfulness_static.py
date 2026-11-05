"""نگهبان ایستای «سامانه دربارهٔ کاری که کرده دروغ نگوید».

دو نقص داده‌ای که اینجا قفل می‌شود:

۱. **نتیجهٔ commit نادیده گرفته می‌شد.** هر ده مسیر نوشتن درخواست، `db_commit()`
   را صدا می‌زدند و مقدار بازگشتی‌اش را دور می‌ریختند؛ بلافاصله بعدش پیام موفقیت
   به کاربر نشان داده می‌شد. اگر commit شکست می‌خورد، کاربر «ثبت شد» می‌دید در
   حالی که هیچ داده‌ای ذخیره نشده بود.

   ریشهٔ عمیق‌تر: خودِ `db_commit()` در سه وضعیت متفاوت `false` برمی‌گرداند و یکی
   از آن‌ها («تراکنش از قبل بسته شده، کار انجام شده است») اصلاً خطا نبود. یعنی
   مقدار بازگشتی قابل اعتماد نبود و بررسی‌اش هشدار کاذب می‌داد — به همین دلیل
   هیچ‌کس بررسی‌اش نمی‌کرد.

۲. **تاریخچهٔ جعلی `auto_assigned`.** درخواستی که مستقیم به وضعیت `assigned`
   ساخته می‌شد، در تاریخچه گذاری از `manager_review` ثبت می‌کرد — وضعیتی که
   هرگز در آن نبوده است.
"""

from __future__ import annotations

import io
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

TICKET_WRITE_PATHS = ("actions/ticket-actions.php", "actions/bulk-actions.php")


def read(relative_path: str) -> str:
    with io.open(os.path.join(ROOT, relative_path), encoding="utf-8") as handle:
        return handle.read()


def strip_php_comments(source: str) -> str:
    """کامنت‌ها را حذف می‌کند تا assert روی کد اجرایی بیفتد.

    بدون این کار، توضیحی که می‌گوید «قبلاً چه بود» خودش الگوی جست‌وجو را
    می‌رباید و تست دربارهٔ کامنت قضاوت می‌کند، نه دربارهٔ رفتار.
    """
    source = re.sub(r"/\*.*?\*/", " ", source, flags=re.S)
    source = re.sub(r"//[^\n]*", " ", source)
    return source


def function_body(relative_path: str, signature: str) -> str:
    source = read(relative_path)
    start = source.index(signature)
    end = source.index("\nfunction ", start + 10)
    return source[start:end]


def function_code(relative_path: str, signature: str) -> str:
    return strip_php_comments(function_body(relative_path, signature))


# ───────────────── ۱) معنای روشن برای نتیجهٔ commit ─────────────────

def test_commit_returns_true_when_the_work_is_already_persisted():
    """«تراکنش باز نبود» خطا نیست — کار با COMMIT ضمنی انجام شده است.

    تا وقتی این حالت false برمی‌گرداند، هیچ‌کس نمی‌تواند نتیجهٔ commit را بررسی
    کند بدون اینکه هشدار کاذب بگیرد.
    """
    code = function_code("lib/db-core.php", "function db_commit(string $context = ''): bool")
    # مسیر «تراکنش باز نبود»: بعد از هشدار COMMIT ضمنی و پیش از بلوک catch.
    warning_at = code.index("COMMIT ضمنی")
    catch_at = code.index("catch (Throwable $exception)")
    implicit_branch = code[warning_at:catch_at]
    assert "return true;" in implicit_branch, (
        "حالت «تراکنش از قبل بسته شده» باید true برگرداند، نه false"
    )
    assert "return false;" not in implicit_branch, (
        "این مسیر شکست نیست و نباید false برگرداند"
    )
    # هشدار باید بماند: نشانهٔ DDL در مسیر درخواست است.
    assert "system_log('warning'" in code[:warning_at + 10]


def test_commit_still_reports_a_genuine_failure_as_false():
    code = function_code("lib/db-core.php", "function db_commit(string $context = ''): bool")
    tail = code[code.index("catch (Throwable $exception)"):]
    assert "return false;" in tail, "شکست واقعی commit باید false بماند"
    assert "system_log('error'" in tail, "شکست واقعی باید در لاگ سامانه ثبت شود"


def test_a_strict_commit_helper_exists_and_raises_a_persian_error():
    source = read("lib/db-core.php")
    assert "function db_commit_or_fail(" in source, (
        "باید نسخه‌ای از commit وجود داشته باشد که در صورت شکست استثنا بیندازد"
    )
    body = function_code("lib/db-core.php", "function db_commit_or_fail(")
    # صرفِ «وجود» throw کافی نیست: یک return زودهنگام پیش از آن، تابع را بی‌اثر
    # می‌کند در حالی که رشتهٔ throw هنوز در فایل دیده می‌شود. پس جریان کنترل را
    # دقیق می‌سنجیم: موفق ⇒ return، و در غیر این صورت throw بدون مانع.
    compact = re.sub(r"\s+", " ", body).strip()
    assert re.search(
        r"if \(db_commit\(\$context\)\) \{ return; \} throw new RuntimeException\('",
        compact,
    ), (
        "db_commit_or_fail باید فقط در حالت موفق return کند و در بقیهٔ حالات "
        "بی‌واسطه RuntimeException بیندازد"
    )
    message = re.search(r"RuntimeException\('([^']+)'", body)
    assert message is not None, "پیام خطا باید رشتهٔ ثابت فارسی باشد"
    assert re.search(r"[\u0600-\u06FF]", message.group(1)), "پیام باید فارسی باشد"


def test_every_ticket_write_path_uses_the_strict_commit():
    for path in TICKET_WRITE_PATHS:
        source = read(path)
        assert "db_commit('index.php');" not in source, (
            f"{path} هنوز نتیجهٔ commit را دور می‌ریزد"
        )
        assert "db_commit_or_fail(" in source, f"{path} باید از commit سخت‌گیرانه استفاده کند"


def test_strict_commit_calls_name_the_action_for_diagnosis():
    """همهٔ فراخوانی‌ها context یکسان 'index.php' داشتند که برای عیب‌یابی بی‌فایده است."""
    for path in TICKET_WRITE_PATHS:
        contexts = re.findall(r"db_commit_or_fail\('([^']*)'\)", read(path))
        assert contexts, f"{path} هیچ فراخوانی db_commit_or_fail ندارد"
        assert "index.php" not in contexts, (
            f"{path} هنوز context بی‌معنی 'index.php' را می‌فرستد"
        )
        assert len(set(contexts)) == len(contexts), (
            f"{path}: هر مسیر باید context یکتا داشته باشد وگرنه لاگ قابل ردیابی نیست"
        )


def test_other_modules_keep_their_existing_commit_contract():
    """تغییر معنا نباید ماژول‌های دیگر را بی‌سر و صدا عوض کند."""
    for path in ("actions/holiday-actions.php", "actions/access-group-actions.php"):
        assert "db_commit('index.php')" in read(path), (
            f"{path} خارج از دامنهٔ این تغییر است و باید دست‌نخورده بماند"
        )


# ───────────────── ۲) تاریخچهٔ راست ─────────────────

def test_auto_assignment_does_not_invent_a_state_the_ticket_never_had():
    source = read("actions/ticket-actions.php")
    assert "'auto_assigned', 'manager_review', 'assigned'" not in source, (
        "درخواستی که مستقیم assigned ساخته شده هرگز در manager_review نبوده است"
    )
    match = re.search(r"log_ticket_event\([^;]*?'auto_assigned'[^;]*?\);", source, re.S)
    assert match is not None, "رویداد auto_assigned باید همچنان ثبت شود"
    call = match.group(0)
    assert "'auto_assigned', null," in call or "'auto_assigned', $initialStatus," in call, (
        "مبدأ باید null باشد (گذار از جایی رخ نداده) نه وضعیتی ساختگی"
    )
    assert "'assigned'" in call, "مقصد همچنان assigned است"
    assert "'assigned_to' => $agentId" in call, "شناسهٔ کارشناس باید در جزئیات بماند"


def test_creation_event_records_the_real_initial_status():
    source = read("actions/ticket-actions.php")
    assert "log_ticket_event($ticketId, (int) $user['id'], 'created', null, $initialStatus" in source, (
        "رویداد ثبت باید همان وضعیت واقعی اولیه را بنویسد"
    )
