#!/usr/bin/env php
<?php
/**
 * آزمون آفلاین: نام کامپیوتر در شناسنامه **دقیقاً** همان چیزی است که در
 * سیستم‌عامل/Active Directory ثبت شده — بدون حرف اضافه، بدون حرف کم.
 *
 * ── شکایت کاربر (۱.۶۳.۳) ───────────────────────────────────────────────
 * «برای سیستم‌ها یک الگو استفاده کردم: mba4-01 الی mba4-150. تو شناسنامه
 *  دقیقاً همین‌هایی که به‌عنوان computer name تو سیستم‌عامل مشخص شده نمایش
 *  داده می‌شود بدون اضافه و کم؟ تو فرم ثبت تیکت چی؟»
 *
 * دو ایراد واقعی پیدا و رفع شد:
 *   ۱. لایهٔ فارسی‌سازی ارقام (lib/fa-digits.php) هر رقمی در متن را فارسی
 *      می‌کرد ⇒ «MBA4-01» به «MBA4-۰۱» تبدیل می‌شد. کاربر باید شناسنامه را
 *      با برچسب روی کیس تطبیق دهد؛ «۰۱» فارسی با هیچ برچسبی جور درنمی‌آید
 *      و کپی/جست‌وجو هم می‌شکند.
 *   ۲. پیش‌فرض نمایش short_lower بود ⇒ «MBA4-01» به «mba4-01» تبدیل می‌شد.
 *      حالا پیش‌فرض short (حفظ حروف) است؛ کوچک‌کردن انتخابی مانده است.
 *
 * اجرا: php tools/test_asset_name_exact_offline.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/lib/fa-digits.php';

$pass = 0;
$fail = 0;
function chk(bool $ok, string $label, string $actual = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "  ✅ {$label}\n";
    } else {
        $fail++;
        echo "  ❌ {$label}" . ($actual !== '' ? " — {$actual}" : '') . "\n";
    }
}

echo "── آزمون: نام کامپیوتر «بدون اضافه و کم» ──\n\n";

/* ═══ ۱) ارقام نام کامپیوتر فارسی نمی‌شوند ═══════════════════════════ */
echo "  ۱) لایهٔ فارسی‌سازی ارقام باید نام کامپیوتر را دست نزند\n";

// نام‌های الگوی کارفرما: mba4-01 تا mba4-150
foreach (['MBA4-01', 'MBA4-02', 'MBA4-23', 'MBA4-99', 'MBA4-100', 'MBA4-150'] as $name) {
    chk(
        fa_digits_text($name) === $name,
        "نام «{$name}» در نمایش بدون تغییر می‌ماند",
        fa_digits_text($name)
    );
}

// نام‌های واقعی دیگر
foreach (['PC-023', 'SRV-DC-01', 'ADMINIT2', 'reza-pc', 'WORKSTATION7', 'pc1'] as $name) {
    chk(
        fa_digits_text($name) === $name,
        "نام «{$name}» دست‌نخورده می‌ماند",
        fa_digits_text($name)
    );
}

chk(
    fa_digits_text('mba4-150') === 'mba4-150',
    'نام با حروف کوچک هم دست‌نخورده می‌ماند'
);

/* ═══ ۲) ولی ارقام واقعی متن همچنان فارسی می‌شوند ═════════════════════ */
echo "\n  ۲) فارسی‌سازی ارقام برای بقیهٔ متن باید کار کند (رگرسیون)\n";

chk(fa_digits_text('1403/08/14') === '۱۴۰۳/۰۸/۱۴', 'تاریخ شمسی فارسی می‌شود', fa_digits_text('1403/08/14'));
chk(fa_digits_text('5 پرس') === '۵ پرس', 'عدد تنها فارسی می‌شود', fa_digits_text('5 پرس'));
chk(
    fa_digits_text('سیستم MBA4-01 در تاریخ 1403/08/14') === 'سیستم MBA4-01 در تاریخ ۱۴۰۳/۰۸/۱۴',
    'متن ترکیبی: نام لاتین، تاریخ فارسی',
    fa_digits_text('سیستم MBA4-01 در تاریخ 1403/08/14')
);
chk(
    fa_digits_text('تعداد 5 پرس برای MBA4-150') === 'تعداد ۵ پرس برای MBA4-150',
    'متن ترکیبی: تعداد فارسی، نام لاتین',
    fa_digits_text('تعداد 5 پرس برای MBA4-150')
);
chk(fa_digits_text('192.168.10.11') === '192.168.10.11', 'IP دست‌نخورده می‌ماند (رگرسیون)');
chk(fa_digits_text('user@corp.local') === 'user@corp.local', 'ایمیل دست‌نخورده می‌ماند (رگرسیون)');

/* ═══ ۳) حالت نمایش پیش‌فرض: حفظ حروف ════════════════════════════════ */
echo "\n  ۳) پیش‌فرض نمایش باید حروف اصلی را حفظ کند\n";

$GLOBALS['FT_SETTINGS'] = [];
if (!function_exists('setting')) {
    function setting(string $key, ?string $default = null): ?string
    {
        return $GLOBALS['FT_SETTINGS'][$key] ?? $default;
    }
}
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
require_once $root . '/asset-names.php';

// پیش‌فرض (تنظیم ذخیره نشده)
unset($GLOBALS['FT_SETTINGS']['asset_hostname_style']);
chk(asset_hostname_style() === 'short', 'پیش‌فرضِ نمایش = short (حفظ حروف)', asset_hostname_style());
chk(asset_display_name('MBA4-01') === 'MBA4-01', 'پیش‌فرض: MBA4-01 ⇒ MBA4-01', asset_display_name('MBA4-01'));
chk(asset_display_name('MBA4-150') === 'MBA4-150', 'پیش‌فرض: MBA4-150 ⇒ MBA4-150', asset_display_name('MBA4-150'));
chk(asset_display_name('mba4-01') === 'mba4-01', 'پیش‌فرض: mba4-01 ⇒ mba4-01 (کوچک بود، می‌ماند)', asset_display_name('mba4-01'));

/* ═══ ۴) کوچک‌کردن همچنان انتخابی است ═══════════════════════════════ */
echo "\n  ۴) حالت «حروف کوچک» همچنان در دسترس است\n";

$GLOBALS['FT_SETTINGS']['asset_hostname_style'] = 'short_lower';
chk(asset_display_name('MBA4-01') === 'mba4-01', 'انتخاب short_lower: MBA4-01 ⇒ mba4-01', asset_display_name('MBA4-01'));

$GLOBALS['FT_SETTINGS']['asset_hostname_style'] = 'full';
chk(
    asset_display_name('MBA4-01.CORP.LOCAL') === 'MBA4-01.CORP.LOCAL',
    'انتخاب full: نام کامل دامنه',
    asset_display_name('MBA4-01.CORP.LOCAL')
);

unset($GLOBALS['FT_SETTINGS']['asset_hostname_style']);

/* ═══ ۵) پردازش نام: هیچ حرفی کم یا اضافه نمی‌شود ═══════════════════ */
echo "\n  ۵) پردازش نام نباید حرفی کم یا اضافه کند\n";

foreach (['MBA4-01', 'MBA4-150', 'MBA4-23', 'MB4-01', 'MBA4-001'] as $name) {
    chk(
        asset_name_core($name) === $name,
        "هستهٔ نام «{$name}» خودش است",
        asset_name_core($name)
    );
}

// الگوی کارفرما: هر ۱۵۰ نام باید دقیقاً خودش برگردد
$allGood = true;
$broken = '';
for ($i = 1; $i <= 150; $i++) {
    $name = 'MBA4-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    if (asset_display_name($name) !== $name) {
        $allGood = false;
        $broken = $name . ' → ' . asset_display_name($name);
        break;
    }
}
chk($allGood, 'کل الگوی MBA4-01 تا MBA4-150 بدون تغییر می‌ماند' . ($broken !== '' ? " ({$broken})" : ''));

/* ═══ ۶) دامنه حذف می‌شود ولی نام دست‌نخورده ════════════════════════ */
echo "\n  ۶) نام کامل دامنه به «فقط نام» کوتاه می‌شود، با حفظ حروف\n";

chk(
    asset_display_name('MBA4-01.CORP.LOCAL') === 'MBA4-01',
    'MBA4-01.CORP.LOCAL ⇒ MBA4-01',
    asset_display_name('MBA4-01.CORP.LOCAL')
);
chk(
    asset_display_name('MBA4-150.corp.local') === 'MBA4-150',
    'MBA4-150.corp.local ⇒ MBA4-150',
    asset_display_name('MBA4-150.corp.local')
);
chk(
    asset_display_name('MBA4-01$') === 'MBA4-01',
    'MBA4-01$ (حساب کامپیوتر AD) ⇒ MBA4-01',
    asset_display_name('MBA4-01$')
);

/* ═══ ۷) IP همچنان نمایش داده نمی‌شود ═══════════════════════════════ */
echo "\n  ۷) IP از نام حذف می‌شود (رفتار ۱.۶۳.۱ حفظ شود)\n";

chk(
    asset_display_name('MBA4-01 192.168.10.11') === 'MBA4-01',
    'MBA4-01 192.168.10.11 ⇒ MBA4-01',
    asset_display_name('MBA4-01 192.168.10.11')
);
// ۱.۶۳.۱-r15 — IP خالص به‌عنوان نام نمایش داده نمی‌شود؛ فقط در tooltip.
chk(
    asset_display_name('192.168.10.11') === '',
    'IP خالص به‌عنوان نام نمایش داده نمی‌شود (تنها شناسه)',
    asset_display_name('192.168.10.11')
);

/* ═══ ۸) رابط: تنظیمات و صفحه‌ها با پیش‌فرض جدید هماهنگ‌اند ═════════ */
echo "\n  ۸) تنظیمات و صفحه‌ها با پیش‌فرض جدید هماهنگ‌اند\n";

$index = (string) file_get_contents($root . '/index.php');
$settingsActions = (string) file_get_contents($root . '/actions/settings-actions.php');

chk(
    strpos($settingsActions, "['short_lower', 'short', 'full'], 'short')") !== false,
    'ذخیرهٔ تنظیمات پیش‌فرض short دارد'
);
chk(
    strpos($index, 'دقیقاً با همان حروف ثبت‌شده') !== false,
    'گزینهٔ پیش‌فرض در صفحهٔ تنظیمات روشن توصیف شده است'
);
chk(
    strpos($index, 'ارقام نام هم فارسی نمی‌شوند') !== false,
    'راهنمای تنظیمات، رفتار ارقام نام را توضیح می‌دهد'
);
chk(
    strpos($index, "setting('asset_hostname_style', 'short')") !== false,
    'صفحهٔ تنظیمات پیش‌فرض short را نشان می‌دهد'
);

/* ═══ ۹) همهٔ مسیرهای نمایش از تابع مشترک استفاده می‌کنند ════════════ */
echo "\n  ۹) هیچ مسیر نمایشی نام خام را چاپ نمی‌کند\n";

$pages = [
    'asset-inventory-page.php' => 'فهرست شناسنامه‌ها',
    'index.php' => 'صفحه‌های اصلی',
    'modules/request-center/Pages/create.php' => 'فرم ثبت تیکت',
    'modules/request-center/Pages/detail.php' => 'جزئیات تیکت',
];
// ۱.۶۳.۱-r15 — دسترسی نمایشی از asset_display_name به asset_name_or رفت.
// asset_display_name عمداً در حالت «نام واقعی نداریم» رشتهٔ خالی می‌دهد،
// و asset_name_or همان را به برچسب صادقانه «نام ثبت نشده» تبدیل می‌کند.
// هر دو از یک قاعده تغذیه می‌شوند، پس وجود هرکدام کافی است.
foreach ($pages as $file => $label) {
    $src = (string) file_get_contents($root . '/' . $file);
    $hasDisplay = strpos($src, 'asset_display_name') !== false || strpos($src, 'asset_name_or') !== false;
    chk($hasDisplay, "{$label} از تابع نمایشی نام استفاده می‌کند");
}

// فرم ثبت تیکت: گزینه‌ها با asset_display_name ساخته می‌شوند
$createPage = (string) file_get_contents($root . '/modules/request-center/Pages/create.php');
// کد واقعی: echo '<option value="' . (int) $asset['id'] . '" ...>' . e(asset_display_name($asset['hostname']) . ...
chk(
    strpos($createPage, "e(asset_display_name(\$asset['hostname'])") !== false
    || strpos($createPage, "e(asset_name_or(\$asset['hostname'])") !== false,
    'گزینه‌های کمبوی «سیستم مرتبط» با نام نمایشی ساخته می‌شوند'
);

// جزئیات شناسنامه هم نام نمایشی دارد (پیش‌تر خام بود)
chk(
    (bool) preg_match('/<dt>نام کامپیوتر<\/dt><dd[^>]*>\' \. e\(asset_(display_name|name_or)\(/', $index),
    'ردیف «نام کامپیوتر» در جزئیات شناسنامه از نام نمایشی استفاده می‌کند'
);

echo "\nنتیجه: {$pass} موفق، {$fail} ناموفق\n";
exit($fail === 0 ? 0 : 1);
