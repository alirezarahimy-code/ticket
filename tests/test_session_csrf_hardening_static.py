"""نگهبان ایستای سخت‌سازی نشست و CSRF.

سه موضوع اینجا قفل می‌شود:

۱. **پذیرش کوکی به‌عنوان توکن CSRF** — رفتار قبلی: اگر نشست توکن نداشت، مقدار
   کوکی `itsm_csrf` به‌عنوان توکن معتبر پذیرفته می‌شد. هرکس بتواند روی مرورگر
   قربانی کوکی بنشاند (زیردامنه، یا شبکهٔ HTTP داخلی بدون TLS) می‌توانست توکن را
   خودش تعیین کند و بعد فرم جعلی با همان مقدار بفرستد — یعنی محافظت CSRF کاملاً
   دور زده می‌شد.

۲. **عمر نشست** — ۳۶۵ روز ثابت و غیرقابل تنظیم بود.

۳. **پوشهٔ `app/` مرده** — نسخهٔ دوم و بی‌استفادهٔ منطق احراز هویت که هیچ‌وقت
   بارگذاری نمی‌شد ولی شبیه کد زنده بود.
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
    """کامنت‌ها را حذف می‌کند تا assert روی کد اجرایی باشد، نه روی توضیحات.

    بدون این کار، توضیحی که می‌گوید «قبلاً کوکی خوانده می‌شد» خودش باعث قرمز شدن
    تست می‌شد — یعنی تست نوشتن توضیح را جریمه می‌کرد، نه رفتار بد را.
    """
    source = re.sub(r"/\*.*?\*/", " ", source, flags=re.S)
    source = re.sub(r"//[^\n]*", " ", source)
    source = re.sub(r"(?m)^\s*#[^\n]*", " ", source)
    return source


def csrf_token_code() -> str:
    source = read("lib/logging-csrf.php")
    start = source.index("function csrf_token(): string")
    end = source.index("\nfunction ", start + 10)
    return strip_php_comments(source[start:end])


# ─────────────────────────── ۱) توکن CSRF ───────────────────────────

def test_csrf_token_never_adopts_a_value_supplied_by_the_browser():
    code = csrf_token_code()
    assert "$_COOKIE" not in code, (
        "csrf_token() نباید هیچ مقداری را از کوکی بخواند؛ مهاجم می‌تواند کوکی بنشاند"
    )
    assert "itsm_csrf" not in code, "ارجاع به کوکی توکن باید از کد حذف شده باشد"
    # تنها منبع مجاز، خودِ نشست است.
    assert "$_SESSION['csrf']" in code
    for forbidden in ("$_GET", "$_POST", "$_REQUEST", "$_SERVER"):
        assert forbidden not in code, f"توکن نباید از {forbidden} خوانده شود"


def test_csrf_token_is_generated_with_a_cryptographic_source():
    code = csrf_token_code()
    assert "random_bytes(32)" in code, "توکن باید از منبع تصادفی امن ساخته شود"
    assert "preg_match('/^[a-f0-9]{64}$/'" in code, (
        "مقدار نشست هم باید قالب‌سنجی شود تا مقدار معیوب به‌عنوان توکن جا نیفتد"
    )


def test_csrf_token_is_seeded_while_the_session_is_still_writable():
    """دلیل وجود کوکی این بود که نشست گاهی زودتر بسته می‌شود؛ حالا توکن پیش از
    هر بسته‌شدنی در خودِ نشست کاشته می‌شود."""
    source = read("session_bootstrap.php")
    # روی خودِ انتساب assert می‌کنیم، نه صرفِ دیده شدن نام متغیر: شرط isset بالاتر
    # هم همان نام را دارد و یک بررسی سهل‌گیرانه با حذف انتساب هم سبز می‌ماند.
    seeding = "$_SESSION['csrf'] = bin2hex(random_bytes(32));"
    assert seeding in source, (
        "توکن باید در app_session_start() واقعاً ساخته و ذخیره شود "
        "تا session_write_close آن را نبلعد"
    )
    seed_at = source.index(seeding)
    start_at = source.index("session_start();")
    assert start_at < seed_at, "کاشت توکن باید بعد از session_start() باشد"
    # و باید درون همان تابع باشد، نه جایی که هرگز اجرا نشود.
    func_at = source.index("function app_session_start(): void")
    next_func_at = source.index("function app_session_enforce_idle(")
    assert func_at < seed_at < next_func_at, "کاشت توکن باید داخل app_session_start() باشد"


def test_legacy_csrf_cookie_is_actively_cleared():
    source = read("lib/logging-csrf.php")
    assert "csrf_discard_legacy_cookie" in source, (
        "کوکی قدیمی باید پاک شود تا مقدار حساس در مرورگر نماند"
    )


def test_require_csrf_still_compares_in_constant_time():
    source = read("lib/logging-csrf.php")
    start = source.index("function require_csrf(): void")
    body = source[start:start + 1200]
    assert "hash_equals(" in body, "مقایسهٔ توکن باید با hash_equals باشد"
    assert "419" in body, "پاسخ درخواست نامعتبر باید ۴۱۹ بماند"


# ─────────────────────────── ۲) عمر نشست ───────────────────────────

def test_session_idle_timeout_exists_and_is_configurable():
    source = read("session_bootstrap.php")
    assert "function app_session_enforce_idle(int $idleDays): void" in source, (
        "باید سازوکار انقضای بی‌استفادگی وجود داشته باشد و مقدارش پارامتر باشد"
    )
    # مقدار از پیکربندی می‌آید، نه عدد ثابت داخل کد.
    assert "cfg('security.session_idle_days'" in read("bootstrap.php"), (
        "روزهای بی‌فعالیت باید از config خوانده شود"
    )
    assert "'session_idle_days'" in read("config.sample.php"), (
        "کلید باید در نمونهٔ پیکربندی مستند باشد"
    )


def test_idle_timeout_is_wired_into_bootstrap_after_config_is_available():
    source = read("bootstrap.php")
    assert "app_session_enforce_idle(" in source, "بررسی بی‌استفادگی باید واقعاً صدا زده شود"
    call_at = source.index("app_session_enforce_idle(")
    db_at = source.index("require __DIR__ . '/lib/db-core.php';")
    assert db_at < call_at, (
        "cfg() در lib/db-core.php تعریف می‌شود، پس فراخوانی باید بعد از آن باشد"
    )
    auth_at = source.index("require __DIR__ . '/lib/auth-scope.php';")
    assert call_at < auth_at, (
        "بررسی باید پیش از بارگذاری لایهٔ احراز هویت انجام شود"
    )


def test_idle_timeout_only_affects_logged_in_sessions():
    source = read("session_bootstrap.php")
    start = source.index("function app_session_enforce_idle(")
    body = source[start:]
    assert "user_id" in body, "نشست بدون کاربر چیزی برای منقضی کردن ندارد"
    assert "session_regenerate_id(true)" in body, (
        "پس از انقضا باید شناسهٔ نشست عوض شود تا شناسهٔ قدیمی قابل استفادهٔ دوباره نباشد"
    )


def test_idle_timeout_can_be_switched_off_without_editing_code():
    source = read("session_bootstrap.php")
    start = source.index("function app_session_enforce_idle(")
    body = source[start:]
    assert re.search(r"\$idleDays\s*<=\s*0", body), (
        "مقدار صفر یا منفی باید بررسی را غیرفعال کند (بازگشت به رفتار قبلی)"
    )


def test_session_cookie_keeps_its_hardening_flags():
    """سخت‌سازی نباید محافظت‌های موجود را خراب کند."""
    source = read("session_bootstrap.php")
    assert "'httponly' => true" in source
    assert "'samesite' => 'Lax'" in source
    assert "ini_set('session.use_strict_mode', '1');" in source, (
        "use_strict_mode جلوی پذیرش شناسهٔ نشستِ ساختهٔ مهاجم را می‌گیرد"
    )


# ─────────────────────────── ۳) کد مرده ───────────────────────────

def test_dead_duplicate_auth_layer_is_gone():
    assert not os.path.exists(os.path.join(ROOT, "app", "Services", "AuthService.php")), (
        "نسخهٔ دوم و بارگذاری‌نشدهٔ منطق ورود باید حذف شده باشد"
    )


def test_no_php_file_references_the_removed_app_namespace():
    offenders = []
    for base, directories, files in os.walk(ROOT):
        directories[:] = [
            d for d in directories
            if d not in {".git", "updates", "downloads", "vendor", "node_modules", "storage"}
        ]
        for name in files:
            if not name.endswith(".php"):
                continue
            path = os.path.join(base, name)
            text = io.open(path, encoding="utf-8", errors="replace").read()
            if "App\\Services\\AuthService" in text or "app/Services/AuthService" in text:
                offenders.append(os.path.relpath(path, ROOT))
    assert offenders == [], "ارجاع به لایهٔ حذف‌شده باقی مانده: " + ", ".join(sorted(offenders))
