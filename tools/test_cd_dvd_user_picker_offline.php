<?php
/**
 * آزمون آفلاین ۱.۶۳.۴ — انتخاب‌گر «شخص واردکننده» در کنترل CD/DVD.
 *
 * باگ کاربر: در فیلد «شخص واردکننده» فقط یک حرف می‌شد تایپ کرد، و جست‌وجوی «ر»
 * برای «علی رضایی» هیچ نتیجه‌ای نمی‌داد. ریشه دو چیز بود:
 *   ۱) فیلد پس از انتخاب کاربر `readOnly` می‌شد و مرورگر **هیچ** کاراکتری
 *      نمی‌پذیرفت؛ اولین کلیک همهٔ متن را پاک می‌کرد.
 *   ۲) فیلتر فقط «شامل‌بودن عبارت» را روی رشتهٔ «نام + نام کاربری» می‌سنجید،
 *      پس حرفی از میانهٔ نام خانوادگی هرگز به نتیجه نمی‌رسید.
 *
 * این آزمون هر دو را روی کد واقعی می‌سنجد، نه روی یک بازنویسیِ موازی.
 */

$root = dirname(__DIR__);
$js = file_get_contents($root . '/assets/cd-dvd.js');
$php = file_get_contents($root . '/cd-dvd.php');
$css = file_get_contents($root . '/assets/style.css');

$ok = 0;
$fail = 0;
function check(string $label, bool $pass, string $detail = ''): void
{
    global $ok, $fail;
    if ($pass) {
        $ok++;
        echo "  ✓ {$label}\n";
        return;
    }
    $fail++;
    echo "  ✗ {$label}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
}

echo "═══ ۱) هیچ readOnly روی فیلد شخص واردکننده ═══\n";
check('الگوی «broughtBy.readOnly = true» حذف شده است', !preg_match('/broughtBy\.readOnly\s*=\s*true/', $js));
check('فیلد به‌صراحت readOnly=false می‌شود', (bool) preg_match('/broughtBy\.readOnly\s*=\s*false/', $js));
check('توضیح چرایی باگ در کد هست (برای جلوگیری از بازگشت)', strpos($js, 'readOnly') !== false && strpos($js, 'هیچ کاراکتری') !== false);

echo "\n═══ ۲) جست‌وجو بر اساس هر کلمه، نه فقط ابتدای رشته ═══\n";
check('تابع matchesSearch وجود دارد', strpos($js, 'function matchesSearch(') !== false);
check('هر کلمهٔ جست‌وجو جدا سنجیده می‌شود (every)', (bool) preg_match('/needle\.split\(\' \'\)\.every/', $js));
check('indexOf در کل متن، نه startsWith', (bool) preg_match('/text\.indexOf\(word\)\s*!==\s*-1/', $js));
check('نرمال‌سازی ی/ک عربی', strpos($js, '\\u064A') !== false && strpos($js, '\\u06A9') !== false);
check('حذف اعراب و کشیده', strpos($js, '\\u064B') !== false && strpos($js, '\\u0640') !== false);

echo "\n═══ ۳) انتخاب قبلی با تایپ پاک می‌شود، نه با readOnly ═══\n";
check('وضعیت انتخاب در pickedValue نگه داشته می‌شود', strpos($js, 'let pickedValue') !== false);
check('وضعیت انتخاب در pickedId نگه داشته می‌شود', strpos($js, 'let pickedId') !== false);
check('searchUnchanged() وجود دارد', strpos($js, 'function searchUnchanged(') !== false);
check('با تایپ تازه شناسهٔ مخفی صفر می‌شود', (bool) preg_match('/broughtById\.value\s*=\s*\'0\'/', $js));

echo "\n═══ ۴) فرم واقعی (نه تابع مردهٔ قدیمی) انتخاب‌گر متنی دارد ═══\n";
check('در فرم جاری input متنی با data-user-picker هست', (bool) preg_match('/data-user-picker.*?id="cd-dvd-brought-by"/s', $php));
check('گزینه‌ها با data-search ساخته می‌شوند', strpos($php, 'data-search=') !== false);
check('پیام «پیدا نشد» وجود دارد', strpos($php, 'cd-dvd-user-empty') !== false);
check('دیگر <select id="cd-dvd-brought-by"> نداریم', !preg_match('/<select[^>]*id="cd-dvd-brought-by"/', $php));

echo "\n═══ ۵) CSS پیام خالی ═══\n";
check('.cd-dvd-user-empty در CSS تعریف شده', strpos($css, '.cd-dvd-user-empty') !== false);
check('حالت hidden آن پنهان می‌شود', (bool) preg_match('/\.cd-dvd-user-empty\[hidden\][^{]*\{[^}]*display:\s*none/', $css));

echo "\n═══ ۶) رفتار تاب‌آور در نبود عناصر ═══\n";
check('initializeUserPicker در نبود picker برمی‌گردد', (bool) preg_match('/if \(!userPicker \|\| !userOptions \|\| !broughtBy \|\| !broughtById\)\s*\{\s*return;/s', $js));
check('اگر broughtBy یک SELECT بود، فیلتر متنی رد می‌شود', (bool) preg_match("/if \\(broughtBy\\.tagName === 'SELECT'\\) \\{\\s*return;/s", $js));

echo "\n" . str_repeat('─', 60) . "\n";
echo "نتیجه: {$ok} موفق، {$fail} ناموفق\n";
exit($fail === 0 ? 0 : 1);
