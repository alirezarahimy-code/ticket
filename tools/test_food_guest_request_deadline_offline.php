<?php
declare(strict_types=1);

// آزمون آفلاین مهلت ثبت سفارش مهمان در همان روز؛ بدون اتصال به پایگاه‌داده.

$root = dirname(__DIR__);
function extract_php_function(string $file, string $name): string
{
    $src = (string) file_get_contents($file);
    $start = strpos($src, 'function ' . $name . '(');
    if ($start === false) {
        throw new RuntimeException("تابع {$name} در {$file} پیدا نشد");
    }
    $open = strpos($src, '{', $start);
    $depth = 0;
    for ($i = $open, $len = strlen($src); $i < $len; $i++) {
        if ($src[$i] === '{') {
            $depth++;
        } elseif ($src[$i] === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($src, $start, $i - $start + 1);
            }
        }
    }
    throw new RuntimeException("پایان تابع {$name} پیدا نشد");
}

$GLOBALS['test_settings'] = [];
function setting(string $key, ?string $default = null): ?string
{
    return $GLOBALS['test_settings'][$key] ?? $default;
}

$source = $root . '/food-order.php';
$code = '';
foreach ([
    'food_order_normalize_digits',
    'food_guest_request_same_day_cutoff',
    'food_guest_request_same_day_is_open',
    'food_guest_request_date_error',
] as $function) {
    $code .= extract_php_function($source, $function) . "\n";
}
eval($code);

$passed = 0;
$failed = 0;
function check(string $label, bool $ok): void
{
    global $passed, $failed;
    $ok ? $passed++ : $failed++;
    echo ($ok ? '[ok] ' : '[FAIL] ') . $label . "\n";
}
function moment(string $value): DateTimeImmutable
{
    return new DateTimeImmutable($value, new DateTimeZone('Asia/Tehran'));
}

$GLOBALS['test_settings'] = [];
check('پیش‌فرض مهلت درخواست همان‌روز ۰۹:۳۰ است', food_guest_request_same_day_cutoff() === '09:30');
check('تا قبل از دقیقهٔ مهلت پذیرفته است', food_guest_request_same_day_is_open(moment('2026-10-17 09:29:59')));
check('آغاز دقیقهٔ ۰۹:۳۰ پذیرفته است', food_guest_request_same_day_is_open(moment('2026-10-17 09:30:00')));
check('پایان دقیقهٔ ۰۹:۳۰ نیز پذیرفته است', food_guest_request_same_day_is_open(moment('2026-10-17 09:30:59')));
check('از ساعت ۰۹:۳۱ درخواست همان روز بسته است', !food_guest_request_same_day_is_open(moment('2026-10-17 09:31:00')));
check('امروز تا پایان دقیقهٔ ۰۹:۳۰ خطا ندارد', food_guest_request_date_error('2026-10-17', moment('2026-10-17 09:30:59')) === null);
check('امروز پس از مهلت با پیام روشن رد می‌شود', str_contains((string) food_guest_request_date_error('2026-10-17', moment('2026-10-17 09:31:00')), 'مهلت'));
check('روز گذشته همیشه رد می‌شود', food_guest_request_date_error('2026-10-16', moment('2026-10-17 08:00:00')) !== null);
check('روز آینده حتی بعد از مهلت امروز مجاز می‌ماند', food_guest_request_date_error('2026-10-18', moment('2026-10-17 10:00:00')) === null);

$GLOBALS['test_settings'] = ['food_guest_request_same_day_cutoff' => '۱۷:۰۵'];
check('تنظیم ارقام فارسی خوانده می‌شود', food_guest_request_same_day_cutoff() === '17:05');
check('مهلت قابل تنظیم تا پایان دقیقهٔ ۱۷:۰۵ باز است', food_guest_request_same_day_is_open(moment('2026-10-17 17:05:59')));
check('پس از مهلت تنظیمی بسته می‌شود', !food_guest_request_same_day_is_open(moment('2026-10-17 17:06:00')));
$GLOBALS['test_settings'] = ['food_guest_request_same_day_cutoff' => '27:99'];
check('مقدار تنظیمی نامعتبر به پیش‌فرض امن برمی‌گردد', food_guest_request_same_day_cutoff() === '09:30');

printf("\nنتیجه: %d موفق، %d ناموفق\n", $passed, $failed);
exit($failed === 0 ? 0 : 1);
