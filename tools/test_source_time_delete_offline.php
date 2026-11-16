<?php
declare(strict_types=1);
/**
 * آزمون آفلاین حذف ردیف جدول تردد (SOURCE_TABLE) — ۱.۶۳.۱-r18
 *
 * باگ واقعی که کاربر گزارش داد: «ترددها از جدول tenter بعد از پایش حذف
 * نمی‌شود». ریشه: شرط حذف برای ستون زمان برابری دقیق می‌ساخت
 * (`C_Time = #12:34:56#`) ولی Access کسری از ثانیه ذخیره می‌کند، پس هیچ
 * ردیفی تطبیق نمی‌خورد و ردیف برای همیشه می‌ماند.
 *
 * این آزمون بدون دیتابیس و بدون Access اجرا می‌شود: فقط منطق ساخت شرط را
 * می‌سنجد.
 */
$root = dirname(__DIR__);
if (!defined('APP_ROOT')) { define('APP_ROOT', $root); }

$src = (string) file_get_contents($root . '/food-ticket.php');

// استخراج تابع‌های خالصِ ساخت شرط، بدون بارگذاری کل سامانه (که دیتابیس می‌خواهد)
foreach ([
    'food_ticket_source_column_kind',
    'food_ticket_source_seconds_of_day',
    'food_ticket_source_time_literal_sod',
    'food_ticket_source_time_between',
    'food_ticket_source_time_where',
] as $fn) {
    $p = strpos($src, 'function ' . $fn . '(');
    if ($p === false) {
        fwrite(STDERR, "تابع $fn پیدا نشد\n");
        exit(1);
    }
    $i = strpos($src, '{', $p);
    $d = 0;
    $e = $i;
    $len = strlen($src);
    for (; $e < $len; $e++) {
        if ($src[$e] === '{') { $d++; }
        elseif ($src[$e] === '}') { $d--; if ($d === 0) { break; } }
    }
    eval(substr($src, $p, $e - $p + 1));
}
if (!function_exists('food_ticket_normalize_digits')) {
    function food_ticket_normalize_digits(string $v): string { return $v; }
}

$pass = 0;
$fail = 0;
$check = static function (string $label, bool $ok, string $detail = '') use (&$pass, &$fail): void {
    if ($ok) { $pass++; echo "  [PASS] $label\n"; }
    else { $fail++; echo "  [FAIL] $label" . ($detail !== '' ? " — $detail" : '') . "\n"; }
};

echo "۱) نوع ستون\n";
$check('C_Time → time', food_ticket_source_column_kind('C_Time') === 'time');
$check('c_time → time', food_ticket_source_column_kind('c_time') === 'time');
$check('PunchTime → time', food_ticket_source_column_kind('PunchTime') === 'time');
$check('C_Date → date', food_ticket_source_column_kind('C_Date') === 'date');
$check('PunchDateTime → datetime', food_ticket_source_column_kind('PunchDateTime') === 'datetime');
$check('created_at → datetime', food_ticket_source_column_kind('created_at') === 'datetime');
$check('L_UID → null (نه تاریخ)', food_ticket_source_column_kind('L_UID') === null);
$check('C_Card → null', food_ticket_source_column_kind('C_Card') === null);

echo "\n۲) ثانیهٔ روز\n";
$check('2026-11-16 12:34:56 → 45296', food_ticket_source_seconds_of_day('2026-11-16 12:34:56') === 45296);
$check('00:00:00 → 0', food_ticket_source_seconds_of_day('00:00:00') === 0);
$check('23:59:59 → 86399', food_ticket_source_seconds_of_day('23:59:59') === 86399);
$check('08:15 → 29700', food_ticket_source_seconds_of_day('08:15') === 29700);
$check('متن نامعتبر → null', food_ticket_source_seconds_of_day('نامعتبر') === null);
$check('ساعت 25:00:00 → null', food_ticket_source_seconds_of_day('25:00:00') === null);

echo "\n۳) شرط حذف — بازه، نه برابری\n";
$w = food_ticket_source_time_where('C_Time', 45296);
$check('ستون زمان بازهٔ نیم‌باز می‌سازد', str_contains($w, '>=') && str_contains($w, '<'), $w);
$check('کران پایین 12:34:56', str_contains($w, '#12:34:56#'), $w);
$check('کران بالا 12:34:57', str_contains($w, '#12:34:57#'), $w);
$check('برابری دقیق ندارد', !preg_match('/C_Time\s*=\s*#/', $w), $w);

$w2 = food_ticket_source_time_where('C_Time', 0);
$check('نیمه‌شب: 00:00:00 تا 00:00:01', str_contains($w2, '#00:00:00#') && str_contains($w2, '#00:00:01#'), $w2);

$w3 = food_ticket_source_time_where('C_Time', 86399);
$check('۲۳:۵۹:۵۹ در ستونِ صرفاً زمان کران پایین معتبر دارد', $w3 !== '' && str_contains($w3, '>= #23:59:59#'), $w3);
$check('۲۳:۵۹:۵۹ مقدار ۲۴:۰۰:۰۰ ناممکن تولید نمی‌کند', !str_contains($w3, '24:00:00'), $w3);

$w4 = food_ticket_source_time_where('[Punch]', 29700, '2026-11-16');
$check('datetime تاریخ هم دارد', str_contains($w4, '#2026-11-16 08:15:00#'), $w4);
$check('datetime کران بالا با تاریخ', str_contains($w4, '#2026-11-16 08:15:01#'), $w4);
$w5 = food_ticket_source_time_where('[Punch]', 86399, '2026-11-16');
$check('datetime در ثانیهٔ آخر تا نیمه‌شب روز بعد محدود می‌شود', str_contains($w5, '< #2026-11-17 00:00:00#'), $w5);
$w6 = food_ticket_source_time_where('[Punch]', 86399, '2026-02-30');
$check('تاریخ نامعتبر به شرطِ حذفِ باز (فقط ساعت) تبدیل نمی‌شود', $w6 === '(1 = 0)', $w6);

echo "\n۴) تاریخ خالص دست‌نخورده می‌ماند\n";
$check('ستون date بازهٔ زمانی نمی‌گیرد', food_ticket_source_column_kind('C_Date') !== 'time');

echo "\n──────────\nنتیجه: $pass موفق، $fail ناموفق\n";
exit($fail === 0 ? 0 : 1);
