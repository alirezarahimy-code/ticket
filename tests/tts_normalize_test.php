<?php
declare(strict_types=1);

/**
 * آزمون قواعد متنی TTS آریا — بدون وب‌سرور.
 * اجرا: php tests/tts_normalize_test.php
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return $GLOBALS['TEST_SETTINGS'][$key] ?? $default;
    }
}
if (!function_exists('valid_choice')) {
    function valid_choice($value, array $allowed, $default)
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
if (!function_exists('app_error_log')) {
    function app_error_log($message): void
    {
    }
}
if (!function_exists('e')) {
    function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '';
    }
}

$GLOBALS['TEST_SETTINGS'] = [
    'chatbot_tts_colloquial' => '0',
    'chatbot_tts_zwnj' => 'keep',
];

require APP_ROOT . '/chatbot.php';

$failed = 0;
$passed = 0;

function tts_bare(string $s): string
{
    $s = chatbot_tts_prepare($s);
    $s = preg_replace('/[.]+$/u', '', $s) ?? $s;
    return trim($s);
}

function expect_true(bool $ok, string $msg): void
{
    global $failed, $passed;
    if ($ok) {
        $passed++;
        echo "ok - $msg\n";
        return;
    }
    $failed++;
    echo "FAIL - $msg\n";
}

function expect_eq(string $got, string $want, string $msg): void
{
    expect_true($got === $want, $msg . " | got=" . $got . " want=" . $want);
}

function expect_contains(string $hay, string $needle, string $msg): void
{
    expect_true(str_contains($hay, $needle), $msg . " | hay=" . $hay);
}

function expect_not_contains(string $hay, string $needle, string $msg): void
{
    expect_true(!str_contains($hay, $needle), $msg . " | hay=" . $hay);
}

$p = tts_bare('سفارش را ثبت کنید');
expect_not_contains($p, 'سفارشِ', '۱. سفارش را ثبت کنید ← بدون کسره روی سفارش');

foreach (['وارد شوید', 'سفارش دهید', 'تایید کرد'] as $s) {
    $got = tts_bare($s);
    $first = explode(' ', $got)[0] ?? '';
    expect_true(!str_contains($first, 'ِ'), '۲. بدون کسره روی واژهٔ اول: ' . $s . ' => ' . $got);
}

expect_eq(tts_bare('ثبت سفارش غذا'), 'ثبتِ سفارشِ غذا', '۳. ثبت سفارش غذا ← ثبتِ سفارشِ غذا');
expect_eq(tts_bare('راهنمای سامانه'), 'راهنمایِ سامانه', '۴. راهنمای سامانه ← راهنمایِ سامانه');

$he = tts_bare('شمارهٔ پرسنلی');
expect_contains($he, 'یِ', '۵. شمارهٔ پرسنلی تبدیل هٔ به ه‌یِ');
expect_not_contains($he, 'شماره پرسنلی', '۵. هٔ حذف صرف نشده باشد');

$z1 = tts_bare("می‌خواهم");
expect_contains($z1, "می", '۶. می‌خواهم حفظ ساختار');
$z2 = tts_bare("کتاب‌ها");
expect_contains($z2, 'کتاب', '۶. کتاب‌ها حفظ');
$z3 = tts_bare("خانه‌ام");
expect_contains($z3, 'خانه', '۶. خانه‌ام حفظ');

foreach (['سیستم', 'سرور', 'ستون', 'سه', 'سی'] as $w) {
    $got = tts_bare($w);
    expect_not_contains($got, 'سَ', '۷. بدون سَ نابجا روی ' . $w . ' => ' . $got);
}
$salam = tts_bare('سلام');
expect_eq($salam, 'سَلام', '۷. سلام از واژه‌نامه سَلام');
$sepas = tts_bare('سپس');
expect_eq($sepas, 'سِپس', '۷. سپس از واژه‌نامه');

expect_eq(tts_bare('۱۴۰۵/۰۷/۲۵'), 'بیست‌وپنجم مهر هزار و چهارصد و پنج', '۸. تاریخ جلالی');
expect_contains(tts_bare('ساعت ۸:۳۰'), 'هشت و سی دقیقه', '۹. ساعت ۸:۳۰');
expect_eq(tts_bare('۸:۰۰'), 'هشت', '۹. ساعت تمام');

expect_eq(tts_bare('۱۲۰۰'), 'هزار و دویست', '۱۰. ۱۲۰۰');
expect_eq(tts_bare('۱٫۵'), 'یک ممیز پنج', '۱۰. ۱٫۵');
expect_eq(tts_bare('۲۵٪'), 'بیست و پنج درصد', '۱۰. ۲۵٪');
$phone = tts_bare('۰۹۱۲۳۴۵۶۷۸۹');
expect_contains($phone, 'صفر', '۱۰. تلفن رقم‌به‌رقم');
expect_contains($phone, 'نه', '۱۰. تلفن شامل نه');

$tkt = tts_bare('TKT-12-345');
expect_contains($tkt, 'شمارهٔ پیگیری', '۱۱. TKT');
expect_contains($tkt, 'دوازده', '۱۱. TKT دوازده');
expect_contains($tts = tts_bare('USB'), 'یو اس بی', '۱۱. USB');
expect_contains(tts_bare('DVD'), 'دی وی دی', '۱۱. DVD');
expect_contains(tts_bare('IP'), 'آی پی', '۱۱. IP');
expect_contains(tts_bare('VPN'), 'وی پی ان', '۱۱. VPN');
expect_contains(tts_bare('Wi-Fi'), 'وای فای', '۱۱. Wi-Fi');

expect_eq(tts_bare('https://x.com/a.php'), 'پیوند', '۱۲. پیوند');

$md = tts_bare('سلام **دنیا** 😀');
expect_not_contains($md, '**', '۱۳. مارک‌داون پاک');
expect_not_contains($md, '😀', '۱۳. ایموجی پاک');

$long = str_repeat('این یک جملهٔ کامل است. ', 40);
$cut = chatbot_tts_prepare($long);
expect_true(mb_strlen($cut, 'UTF-8') <= 800, '۱۴. سقف طول');
expect_true((bool) preg_match('/[.؟!]$/u', $cut), '۱۴. برش در مرز جمله');

$range = tts_bare('۵-۱۰');
expect_contains($range, 'تا', 'بازه ۵-۱۰');

echo "\n$passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);
