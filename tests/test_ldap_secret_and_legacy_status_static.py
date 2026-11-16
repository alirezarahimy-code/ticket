"""نگهبان ایستای بستهٔ ۷: «رمز حساب سرویس AD در حالت سکون» و «وضعیت میراثی pending».

### مسئلهٔ یکم — رمز AD به‌صورت متن ساده در جدول تنظیمات

رمز حساب سرویس Active Directory با `save_setting('ldap_bind_password', ...)`
مستقیم و بدون هیچ پوششی در جدول `settings` می‌نشست. این رمز معمولاً متعلق به یک
حساب سرویس دامین است، یعنی ارزشش بسیار بیشتر از یک رمز کاربر معمولی است.

مدل تهدید دقیقاً همین است: هرکسی که فقط **خواندن دیتابیس** را به دست بیاورد —
یک پشتیبان‌گیری جابه‌جاشده، یک replica، یا یک تزریق SQL فقط‌خواندنی — رمز دامین
را برداشته است، بی‌آنکه لازم باشد به فایل‌های سرور دسترسی پیدا کند.

### چرا الگوی تازه‌ای ساخته نشد

این مخزن همین مسئله را دو بار حل کرده است: `domain_scan_store_run_credentials()`
در `domain-scan.php` و `food_ticket_encrypt()` در `food-ticket.php`، هر دو با
`aes-256-gcm` و کلید مشتق‌شده از `cfg('app.key')`. سومین نسخهٔ خانگی ساختن یعنی
سومین جای اشتباه‌کردن؛ پس همان الگو تکرار شد.

### چرا سازگاری رو به عقب اجباری است

اگر این تغییر رمزهای موجود را نامعتبر کند، ورود همهٔ کاربران دامینی از کار
می‌افتد — یعنی قفل‌شدن کامل سامانه. پس:

  * نبود `app.key` یعنی رفتار امروز بدون تغییر (ذخیرهٔ متن ساده)، نه خطا.
  * مقدار قدیمیِ متن ساده همچنان خوانده می‌شود.
  * شکست رمزگشایی باید **بسته** شکست بخورد (رشتهٔ خالی)، نه اینکه خودِ پاکت
    رمزشده به‌عنوان رمز به سرور دامین فرستاده شود.

### مسئلهٔ دوم — وضعیت `pending`

`pending` در `ticket_allowed_transitions()` مبدأ دارد ولی **هیچ وضعیتی آن را
به‌عنوان مقصد ندارد**؛ یعنی هیچ درخواست تازه‌ای نمی‌تواند به آن برسد. این یک
وضعیت میراثی از نسخه‌های قدیمی است.

وسوسهٔ پاک‌کردنش باید مهار شود: اگر ردیف‌های قدیمی در دیتابیس واقعی هنوز
`status = 'pending'` باشند و مسیر خروج از آن را برداریم، آن درخواست‌ها برای
همیشه گیر می‌کنند. پس قاعدهٔ درست «حذف» نیست، «قفل‌کردن هر دو سر» است:
ورود ممنوع، خروج همیشه باز.
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


# ───────────────────────── محور ۱: رمز AD در حالت سکون ─────────────────────────


def test_ldap_secret_helpers_exist() -> None:
    source = read("lib/ldap.php")
    for needed in ("function ldap_secret_encrypt(", "function ldap_secret_decrypt("):
        assert needed in source, f"{needed} در lib/ldap.php نیست."


def test_settings_action_never_saves_the_raw_password() -> None:
    """نقطهٔ نوشتن باید از مسیر رمزگذاری رد شود."""
    actions = strip_php_comments(read("actions/settings-actions.php"))
    assert "save_setting('ldap_bind_password', post_value('ldap_bind_password'))" not in actions, (
        "رمز حساب سرویس هنوز بدون رمزگذاری در جدول تنظیمات ذخیره می‌شود."
    )
    assert "ldap_secret_encrypt(" in actions, (
        "اکشن ذخیرهٔ تنظیمات از ldap_secret_encrypt() استفاده نمی‌کند."
    )


def test_single_read_point_decrypts() -> None:
    """`ldap_cfg()` تنها نقطهٔ خواندن است؛ رمزگشایی باید همان‌جا بنشیند."""
    body = strip_php_comments(php_function("lib/ldap.php", "function ldap_cfg("))
    assert "ldap_secret_decrypt(" in body, (
        "ldap_cfg() مقدار ذخیره‌شده را رمزگشایی نمی‌کند؛ رمزِ رمزشده "
        "به‌صورت خام به سرور دامین فرستاده می‌شود."
    )


def test_encryption_reuses_the_repository_pattern() -> None:
    """الگوی موجود مخزن: aes-256-gcm با کلید مشتق‌شده از app.key."""
    body = strip_php_comments(php_function("lib/ldap.php", "function ldap_secret_encrypt("))
    assert "aes-256-gcm" in body, "رمزگذاری باید احرازشده (GCM) باشد، مثل بقیهٔ مخزن."
    key_body = strip_php_comments(php_function("lib/ldap.php", "function ldap_secret_key("))
    assert "app.key" in key_body, "کلید باید از cfg('app.key') مشتق شود."
    assert re.search(r"hash\(\s*'sha256'\s*,\s*'[^']+:", key_body), (
        "کلید باید با یک فضای‌نام اختصاصی مشتق شود تا با کلید اسکن دامنه یکی نشود."
    )


def test_missing_app_key_keeps_todays_behaviour() -> None:
    """نبود app.key نباید ذخیرهٔ تنظیمات را بشکند."""
    body = strip_php_comments(php_function("lib/ldap.php", "function ldap_secret_key("))
    assert "return null" in body, (
        "ldap_secret_key() در نبود app.key باید null برگرداند تا مسیر "
        "ذخیرهٔ متن ساده (رفتار امروز) حفظ شود، نه اینکه استثنا بیندازد."
    )
    encrypt = strip_php_comments(php_function("lib/ldap.php", "function ldap_secret_encrypt("))
    assert "return $plain" in encrypt, (
        "ldap_secret_encrypt() در نبود کلید باید همان متن ساده را برگرداند."
    )


def test_legacy_plaintext_values_still_readable() -> None:
    """رمزهای ذخیره‌شدهٔ قبلی نباید از کار بیفتند."""
    body = strip_php_comments(php_function("lib/ldap.php", "function ldap_secret_decrypt("))
    assert re.search(r"str_starts_with\(", body), (
        "ldap_secret_decrypt() باید پاکت رمزشده را از متن سادهٔ قدیمی تشخیص دهد."
    )


def test_failed_decryption_fails_closed() -> None:
    """پاکت خراب نباید به‌عنوان رمز به سرور دامین فرستاده شود."""
    body = strip_php_comments(php_function("lib/ldap.php", "function ldap_secret_decrypt("))
    assert "return ''" in body, (
        "شکست رمزگشایی باید رشتهٔ خالی برگرداند (شکست بسته)؛ برگرداندن خودِ "
        "پاکت یعنی ارسال متن رمزشده به‌عنوان رمز."
    )


def test_envelope_layout_offsets_are_consistent() -> None:
    """چیدمان پاکت: iv(۱۲) + tag(۱۶) + متن رمز.

    این تنها بخشی است که لایهٔ رفتاری پوششش نمی‌دهد، چون افزونهٔ openssl در محیط
    تست در دسترس نیست. آفست‌ها محتمل‌ترین جای اشتباه‌اند (۲۸ = ۱۲ + ۱۶)، پس
    همین‌جا قفل می‌شوند. درستی خودِ طرح مستقل با پیاده‌سازی مرجع AES-GCM بررسی
    و تأیید شده است.
    """
    body = strip_php_comments(php_function("lib/ldap.php", "function ldap_secret_decrypt("))
    assert "substr($raw, 0, 12)" in body, "بردار آغازین باید ۱۲ بایت نخست باشد."
    assert "substr($raw, 12, 16)" in body, "برچسب احراز باید ۱۶ بایت پس از iv باشد."
    assert "substr($raw, 28)" in body, "متن رمز باید از بایت ۲۸ (۱۲+۱۶) شروع شود."
    assert "strlen($raw) > 28" in body, (
        "پاکت کوتاه‌تر از ۲۹ بایت حتی یک بایت متن رمز ندارد و باید رد شود."
    )
    encrypt = strip_php_comments(php_function("lib/ldap.php", "function ldap_secret_encrypt("))
    assert "random_bytes(12)" in encrypt, (
        "بردار آغازین باید تصادفی و ۱۲ بایتی باشد؛ iv ثابت در GCM فاجعه‌بار است."
    )


def test_failed_decryption_is_logged() -> None:
    """خطای خاموش یعنی مدیر هیچ‌وقت نمی‌فهمد چرا ورود دامینی قطع شده."""
    body = strip_php_comments(php_function("lib/ldap.php", "function ldap_secret_decrypt("))
    assert "system_log(" in body, (
        "شکست رمزگشایی باید لاگ شود تا دلیل قطع‌شدن ورود دامینی قابل ردیابی باشد."
    )


# ───────────────────────── محور ۲: وضعیت میراثی pending ─────────────────────────


def transition_map() -> dict[str, list[str]]:
    source = read("modules/request-center/WorkflowService.php")
    start = source.index("public static function allowedTransitions(")
    end = source.index("\n    /**", start)
    body = source[start:end]
    found: dict[str, list[str]] = {}
    for source_status, targets in re.findall(r"'(\w+)'\s*=>\s*\[([^\]]*)\]", body):
        found[source_status] = re.findall(r"'(\w+)'", targets)
    return found


def test_nothing_transitions_into_pending() -> None:
    """ورود ممنوع: هیچ وضعیتی نباید pending را دوباره زنده کند."""
    offenders = [
        source for source, targets in transition_map().items() if "pending" in targets
    ]
    assert not offenders, (
        "وضعیت میراثی pending دوباره به‌عنوان مقصد اضافه شده است در: "
        + ", ".join(offenders)
    )


def test_pending_always_has_a_way_out() -> None:
    """خروج همیشه باز: ردیف‌های قدیمی نباید برای همیشه گیر کنند."""
    targets = transition_map().get("pending")
    assert targets, (
        "مسیر خروج از pending حذف شده است؛ اگر در دیتابیس واقعی ردیفی با این "
        "وضعیت مانده باشد، برای همیشه قفل می‌شود."
    )
    assert "in_progress" in targets and "resolved" in targets, (
        "خروج از pending باید دست‌کم به in_progress و resolved ممکن باشد."
    )


def test_pending_is_documented_as_legacy() -> None:
    """بدون توضیح، نفر بعدی یا احیایش می‌کند یا پاکش می‌کند — هر دو اشتباه."""
    body = read("modules/request-center/WorkflowService.php")
    assert "میراثی" in body, (
        "نقشهٔ گذار توضیح نمی‌دهد که pending یک وضعیت میراثی است و چرا "
        "نه حذف می‌شود نه مقصد می‌گیرد."
    )
