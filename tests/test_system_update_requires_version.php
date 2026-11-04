<?php

declare(strict_types=1);

/**
 * tests/test_system_update_requires_version.php
 *
 * گارد «پیش‌نیاز نسخه» در نصب‌کنندهٔ بسته‌های به‌روزرسانی.
 *
 * چرا لازم است؟ بستهٔ افزایشی فقط فایل‌هایی را دارد که نسبت به requires_version
 * عوض شده‌اند. اگر روی نسخهٔ پایین‌تر نصب شود، فایل‌های نسخه‌های میانی جا می‌مانند و
 * سامانه بدون هیچ پیام خطایی نیمه‌کاره می‌شود. تا پیش از ۱.۶۲.۷۹ این کلید فقط یک
 * یادداشت توضیحی بود و هیچ‌جا بررسی نمی‌شد.
 *
 * اجرا:  php tests/test_system_update_requires_version.php
 */

const APP_ROOT = __DIR__ . '/..';

// system-update.php در سطح فایل فقط تابع تعریف می‌کند؛ این چند تابع برای بارگذاری کافی‌اند.
function db()
{
    throw new RuntimeException('این آزمون به دیتابیس نیاز ندارد.');
}
function save_audit(...$args): void
{
}
function app_error_log(string $message): bool
{
    return true;
}
function cfg(string $key, mixed $default = null): mixed
{
    return $default;
}

require APP_ROOT . '/system-update.php';

$failed = 0;
function expect(string $name, bool $ok): void
{
    global $failed;
    echo ($ok ? 'PASS' : 'FAIL') . "  $name\n";
    if (!$ok) {
        $failed++;
    }
}

/** میان‌بر: آیا نصب مجاز است؟ */
function allowed(string $current, mixed $requires, string $mode = 'upgrade', bool $ignore = false): bool
{
    return system_update_requires_violation($current, $requires, $mode, $ignore) === null;
}

// ── پیش‌نیاز برآورده ────────────────────────────────────────────────────
expect('نسخهٔ دقیقاً برابر پیش‌نیاز مجاز است', allowed('1.62.77', '1.62.77'));
expect('نسخهٔ بالاتر از پیش‌نیاز مجاز است', allowed('1.62.78', '1.62.77'));
expect('نسخهٔ خیلی بالاتر مجاز است', allowed('1.63.0', '1.62.77'));

// ── پیش‌نیاز برآورده نیست ───────────────────────────────────────────────
expect('نسخهٔ پایین‌تر رد می‌شود', !allowed('1.62.75', '1.62.77'));
expect('یک وصله پایین‌تر هم رد می‌شود', !allowed('1.62.76', '1.62.77'));
expect('نسخهٔ خیلی قدیمی رد می‌شود', !allowed('1.60.0', '1.62.77'));

// ── متن خطا باید قابل اقدام باشد ────────────────────────────────────────
$message = (string) system_update_requires_violation('1.62.75', '1.62.77', 'upgrade');
expect('پیام، نسخهٔ پیش‌نیاز را می‌گوید', str_contains($message, '1.62.77'));
expect('پیام، نسخهٔ نصب‌شده را می‌گوید', str_contains($message, '1.62.75'));
expect('پیام، راه‌حل سورس کامل را پیشنهاد می‌دهد', str_contains($message, 'سورس'));
expect('پیام، راه نادیده گرفتن را توضیح می‌دهد', str_contains($message, 'نادیده'));

// ── سازگاری با بسته‌های قدیمی ───────────────────────────────────────────
expect('بسته بدون requires_version مثل قبل نصب می‌شود', allowed('1.60.0', null));
expect('requires_version خالی نادیده گرفته می‌شود', allowed('1.60.0', ''));
expect('requires_version فقط فاصله نادیده گرفته می‌شود', allowed('1.60.0', '   '));
expect('requires_version نامعتبر نادیده گرفته می‌شود', allowed('1.60.0', 'نسخهٔ قبلی'));
expect('requires_version با حروف نادیده گرفته می‌شود', allowed('1.60.0', '1.62.77-beta'));

// ── درهای خروج ──────────────────────────────────────────────────────────
expect('تیک «نادیده بگیر» گارد را خاموش می‌کند', allowed('1.62.75', '1.62.77', 'upgrade', true));
expect('downgrade تأییدشده درگیر گارد نمی‌شود', allowed('1.62.75', '1.62.77', 'downgrade'));
expect('reinstall همان نسخه مجاز است', allowed('1.62.79', '1.62.77', 'reinstall'));

// ── نسخه‌های با تعداد جزء متفاوت ────────────────────────────────────────
expect('1.62 در برابر پیش‌نیاز 1.62.77 رد می‌شود', !allowed('1.62', '1.62.77'));
expect('1.62.77.1 در برابر 1.62.77 مجاز است', allowed('1.62.77.1', '1.62.77'));

// ── بستهٔ واقعی این نسخه ─────────────────────────────────────────────────
$repoVersion = trim((string) file_get_contents(APP_ROOT . '/VERSION'));
expect('نسخهٔ مخزن، پیش‌نیاز بستهٔ خودش را برآورده می‌کند', allowed($repoVersion, '1.62.77'));

echo $failed === 0
    ? "\nهمهٔ آزمون‌های گارد پیش‌نیاز نسخه موفق بودند.\n"
    : "\n$failed آزمون ناموفق.\n";
exit($failed === 0 ? 0 : 1);
