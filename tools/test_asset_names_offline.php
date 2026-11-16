<?php
declare(strict_types=1);

/**
 * آزمون آفلاین «نام کوتاه کامپیوترها» — نسخهٔ ۱.۳۷.۵
 * ============================================================================
 *   php tools/test_asset_names_offline.php
 *
 * چرا این آزمون: کاربر دو نام گیج‌کننده می‌دید (DOMAIN-ADMINIT2 و ADMINIT2.DOMAIN.LOCAL)
 * و خواست فقط «اسم» دیده شود، مثل adminit2. این آزمون دقیقاً همان نمونه‌ها را می‌سنجد
 * و مطمئن می‌شود هیچ داده‌ای در دیتابیس عوض نشده (فقط نمایش).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$pass = 0;
$fail = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "[PASS] $label\n";
        return;
    }
    $fail++;
    echo "[FAIL] $label" . ($detail !== '' ? ' — ' . $detail : '') . "\n";
}

/** بدنهٔ یک تابع را از فایل برمی‌دارد (بدون بارگذاری کل پروژه). */
function grabFunction(string $file, string $name): string
{
    $src = (string) file_get_contents($file);
    $needle = 'function ' . $name . '(';
    $start = strpos($src, $needle);
    if ($start === false) {
        throw new RuntimeException('تابع پیدا نشد: ' . $name);
    }
    $brace = strpos($src, '{', $start);
    $depth = 0;
    for ($i = $brace; $i < strlen($src); $i++) {
        if ($src[$i] === '{') {
            $depth++;
        } elseif ($src[$i] === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($src, $start, $i - $start + 1);
            }
        }
    }
    throw new RuntimeException('بدنهٔ تابع بسته نشد: ' . $name);
}

$root = dirname(__DIR__);
$file = $root . '/asset-names.php';

// محیط حداقلی: setting() و e() و mb_* (همان چیزهایی که تابع‌ها استفاده می‌کنند)
$GLOBALS['FT_SETTINGS'] = [];
function setting(string $key, ?string $default = null): ?string
{
    return $GLOBALS['FT_SETTINGS'][$key] ?? $default;
}
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
function set_style(string $style): void
{
    $GLOBALS['FT_SETTINGS']['asset_hostname_style'] = $style;
}

// ثابت الگوی IPv4 از فایل ماژول (تابع نیست، grabFunction پیدا نمی‌کند)
if (!defined('ASSET_IPV4_PATTERN')) {
    if (preg_match("/const ASSET_IPV4_PATTERN = '([^']+)';/", $file, $mPattern) === 1) {
        define('ASSET_IPV4_PATTERN', str_replace('\\\\', '\\', $mPattern[1]));
    } else {
        define('ASSET_IPV4_PATTERN', '((?:\\d{1,3}\\.){3}\\d{1,3})');
    }
}
foreach (['asset_is_ip', 'asset_is_domain_name', 'asset_strip_ip', 'asset_hostname_style', 'asset_name_core', 'asset_display_name', 'asset_name_or', 'asset_is_auto_domain_tag', 'asset_name_title', 'asset_name_attr', 'asset_tag_label'] as $fn) {
    eval(grabFunction($file, $fn));
}

echo "=== آزمون نام کوتاه کامپیوترها (۱.۳۷.۵) ===\n";

/* ───── ۱) حالت پیش‌فرض: فقط نام، با حروف کوچک (دقیقاً نمونهٔ کاربر) ───── */
set_style('short_lower');
check('۱: ADMINIT2.DOMAIN.LOCAL ⇒ adminit2', asset_display_name('ADMINIT2.DOMAIN.LOCAL') === 'adminit2', asset_display_name('ADMINIT2.DOMAIN.LOCAL'));
check('۲: DOMAIN-ADMINIT2 ⇒ adminit2', asset_display_name('DOMAIN-ADMINIT2') === 'adminit2', asset_display_name('DOMAIN-ADMINIT2'));
check('۳: adminit2 ⇒ adminit2 (بدون تغییر)', asset_display_name('adminit2') === 'adminit2', asset_display_name('adminit2'));
check('۴: ADMINIT2 ⇒ adminit2', asset_display_name('ADMINIT2') === 'adminit2', asset_display_name('ADMINIT2'));

/* ───── ۲) نام‌های واقعی‌تر و حالت‌های لبه‌ای ───── */
check('۵: PC-099.DOMAIN.LOCAL ⇒ pc-099', asset_display_name('PC-099.DOMAIN.LOCAL') === 'pc-099', asset_display_name('PC-099.DOMAIN.LOCAL'));
check('۶: حساب کامپیوتر با $ ⇒ adminit2', asset_display_name('ADMINIT2$') === 'adminit2', asset_display_name('ADMINIT2$'));
check('۷: DOMAIN-ADMINIT2.DOMAIN.LOCAL (هر دو حالت با هم) ⇒ adminit2',
    asset_display_name('DOMAIN-ADMINIT2.DOMAIN.LOCAL') === 'adminit2', asset_display_name('DOMAIN-ADMINIT2.DOMAIN.LOCAL'));
check('۸: فاصله‌های اضافی پاک می‌شود', asset_display_name('  ADMINIT2.DOMAIN.LOCAL  ') === 'adminit2', asset_display_name('  ADMINIT2.DOMAIN.LOCAL  '));
// ۱.۶۳.۱-r15 — IP دیگر هرگز در ستون «نام» نمی‌نشیند؛ فقط در tooltip.
check('۹: IP خالص به‌عنوان نام نمایش داده نمی‌شود (فقط در tooltip)',
    asset_display_name('192.168.10.42') === '', var_export(asset_display_name('192.168.10.42'), true));
check('۹-ب: IP خالص در tooltip با برچسب «آدرس» می‌آید',
    str_contains(asset_name_title('192.168.10.42'), '192.168.10.42'), asset_name_title('192.168.10.42'));
check('۱۰: نام خالی ⇒ خالی', asset_display_name('') === '');
check('۱۱: نام سرور با نقطهٔ داخلی زیاد خراب نمی‌شود (srv01.corp) ⇒ srv01',
    asset_display_name('srv01.corp') === 'srv01', asset_display_name('srv01.corp'));

/* ───── ۳) حالت‌های قابل انتخاب در تنظیمات ───── */
set_style('short');
check('۱۲: حالت «حروف اصلی»: ADMINIT2.DOMAIN.LOCAL ⇒ ADMINIT2', asset_display_name('ADMINIT2.DOMAIN.LOCAL') === 'ADMINIT2', asset_display_name('ADMINIT2.DOMAIN.LOCAL'));
set_style('full');
check('۱۳: حالت «نام کامل»: همان ADMINIT2.DOMAIN.LOCAL قبلی برمی‌گردد', asset_display_name('ADMINIT2.DOMAIN.LOCAL') === 'ADMINIT2.DOMAIN.LOCAL');
set_style('چیز_نامعتبر');
// ۱.۶۳.۳: پیش‌فرض از short_lower به short عوض شد تا نام دقیقاً مثل سیستم‌عامل باشد.
check('۱۴: مقدار نامعتبر در تنظیمات ⇒ بازگشت به پیش‌فرض ADMINIT2 (حفظ حروف)', asset_display_name('ADMINIT2.DOMAIN.LOCAL') === 'ADMINIT2', asset_display_name('ADMINIT2.DOMAIN.LOCAL'));
set_style('short_lower');

/* ───── ۴) شناسهٔ رکورد (asset_tag) ───── */
check('۱۵: شناسهٔ خودکارِ دامنه تشخیص داده می‌شود', asset_is_auto_domain_tag('DOMAIN-ADMINIT2') === true);
check('۱۶: شناسهٔ دستیِ واقعی تشخیص داده نمی‌شود', asset_is_auto_domain_tag('IT-1402-0042') === false);
check('۱۷: برچسب شناسهٔ خودکار بدون پیشوند DOMAIN- ⇒ ADMINIT2', asset_tag_label('DOMAIN-ADMINIT2', 'ADMINIT2.DOMAIN.LOCAL') === 'ADMINIT2', asset_tag_label('DOMAIN-ADMINIT2', 'ADMINIT2.DOMAIN.LOCAL'));
check('۱۸: برچسب شناسهٔ دستی دست‌نخورده ⇒ IT-1402-0042', asset_tag_label('IT-1402-0042') === 'IT-1402-0042');
check('۱۹: برچسب خالی برای شناسهٔ خالی', asset_tag_label('') === '');

/* ───── ۵) نام کامل گم نمی‌شود (tooltip) ───── */
$title = asset_name_title('ADMINIT2.DOMAIN.LOCAL', 'DOMAIN-ADMINIT2');
check('۲۰: راهنما (tooltip) نام کامل دامنه را نگه می‌دارد', $title === 'ADMINIT2.DOMAIN.LOCAL', $title);
check('۲۱: برای شناسهٔ دستی، شناسه هم در راهنما می‌آید',
    strpos(asset_name_title('ADMINIT2.DOMAIN.LOCAL', 'IT-1402-0042'), 'IT-1402-0042') !== false,
    asset_name_title('ADMINIT2.DOMAIN.LOCAL', 'IT-1402-0042'));
check('۲۲: وقتی نام کوتاه و کامل یکی است، راهنما خالی می‌ماند', asset_name_title('adminit2') === '', asset_name_title('adminit2'));
check('۲۳: صفت title آمادهٔ درج در HTML', strpos(asset_name_attr('ADMINIT2.DOMAIN.LOCAL', 'DOMAIN-ADMINIT2'), 'title="ADMINIT2.DOMAIN.LOCAL"') !== false, asset_name_attr('ADMINIT2.DOMAIN.LOCAL', 'DOMAIN-ADMINIT2'));

/* ───── ۶) اتصال به همهٔ جاهای نمایش (سرور) ───── */
$index = (string) file_get_contents($root . '/index.php') . "\n" . (string) file_get_contents($root . '/actions/asset-actions.php') . "\n" . (string) file_get_contents($root . '/actions/settings-actions.php');
check('۲۴: ماژول نام‌ها در index.php بارگذاری شده است', preg_match('/require(_once)? __DIR__ \. \'\/asset-names\.php\';/', $index) === 1);
// ۱.۶۳.۱-r15 — دسترسی نمایشی از asset_display_name به asset_name_or رفت، چون
// asset_display_name در حالت «نام واقعی نداریم» عمداً رشتهٔ خالی می‌دهد و هر
// سلول رابط کاربری خالی می‌شد. این دو تابع از یک قاعده تغذیه می‌شوند.
check('۲۵: فهرست شناسنامه‌ها از نام نمایشی استفاده می‌کند',
    (substr_count($index, 'asset_name_or(') + substr_count($index, 'asset_display_name(')) >= 6,
    (string) (substr_count($index, 'asset_name_or(') + substr_count($index, 'asset_display_name(')));
check('۲۶: گزینه‌های کمبوی «سیستم مرتبط» هم نام کوتاه را نشان می‌دهند',
    strpos($index, "e(asset_name_or(\$asset['hostname'])") !== false
    || strpos($index, "e(asset_display_name(\$asset['hostname'])") !== false);
check('۲۷: ردیف‌های اسکن دامنه نام کوتاه را نشان می‌دهند',
    strpos($index, "e(asset_name_or((string) \$scanRow['hostname']))") !== false);
check('۲۸: پاسخ JSON اسکن، نام نمایشی را هم می‌فرستد',
    strpos($index, "'name' => asset_display_name(") !== false || strpos($index, "'name' => asset_name_or(") !== false);
check('۲۹: به‌روزرسانی زندهٔ جدول اسکن هم نام نمایشی را می‌نویسد', strpos($index, 'x.name||x.hostname') !== false);
check('۳۰: شناسهٔ خودکارِ دامنه در فهرست‌ها پنهان می‌شود (خروجی «دامنه» به‌جای DOMAIN-…)',
    strpos($index, "asset_is_auto_domain_tag(\$asset['asset_tag']) ? '' : '<b>'") !== false);

$inventoryPage = (string) file_get_contents($root . '/asset-inventory-page.php');
check('۳۱: فهرست سیستم‌ها (asset-inventory-page) هم نام کوتاه دارد',
    strpos($inventoryPage, 'asset_display_name(') !== false || strpos($inventoryPage, 'asset_name_or(') !== false);

$scan = (string) file_get_contents($root . '/domain-scan.php');
check('۳۲: فهرست میزبان‌های خطادار اسکن هم نام کوتاه دارد',
    strpos($scan, 'asset_name_or($hostLine)') !== false || strpos($scan, 'asset_display_name($hostLine)') !== false);
check('۳۳: ستون assets.source برای مقدار domain گسترده می‌شود (رفع ثبت‌نشدن رکورد دامنه)',
    strpos($scan, "MODIFY source ENUM('manual','server_inventory','agent','domain')") !== false);

/* ───── ۷) تنظیمات: انتخاب حالت نمایش ───── */
check('۳۴: تنظیم asset_hostname_style در صفحهٔ تنظیمات هست', strpos($index, 'name="asset_hostname_style"') !== false);
check('۳۵: مقدار تنظیم با whitelist ذخیره می‌شود (بدون مقدار دلخواه)',
    strpos($index, "valid_choice(post_value('asset_hostname_style'), ['short_lower', 'short', 'full'], 'short')") !== false);
// ۱.۶۳.۳: پیش‌فرض «حفظ حروف اصلی» است تا نام با برچسب کیس یکی باشد.
// برای سنجش «پیش‌فرض»، باید تنظیم ذخیره‌شده برداشته شود؛ وگرنه آزمون
// مقدار قبلی (short_lower) را می‌سنجد نه رفتار پیش‌فرض را.
unset($GLOBALS['FT_SETTINGS']['asset_hostname_style']);
check('۳۶: پیش‌فرض، حالت «فقط نام با حروف اصلی» است', asset_display_name('DC01.CORP.LOCAL') === 'DC01', asset_display_name('DC01.CORP.LOCAL'));

/* ───── ۸) IP چسبیده به نام (۱.۶۳.۱) ───── */
check('۳۷: «clint 192.168.1.65» ⇒ clint (IP حذف می‌شود، نه اینکه نام را ببرد)',
    asset_display_name('clint 192.168.1.65') === 'clint', asset_display_name('clint 192.168.1.65'));
check('۳۸: «clint-192.168.1.65» ⇒ clint',
    asset_display_name('clint-192.168.1.65') === 'clint', asset_display_name('clint-192.168.1.65'));
check('۳۹: «pc1 10.1.2.3» ⇒ pc1 (پیش‌تر «pc1 10» می‌شد)',
    asset_display_name('pc1 10.1.2.3') === 'pc1', asset_display_name('pc1 10.1.2.3'));
check('۴۰: «clint 192.168.1.65.corp.local» ⇒ clint',
    asset_display_name('clint 192.168.1.65.corp.local') === 'clint', asset_display_name('clint 192.168.1.65.corp.local'));
// پیش‌فرض ۱.۶۳.۳ = حفظ حروف؛ پس «CLIENT» با همان حروف می‌ماند.
check('۴۱: «CLIENT 192.168.1.65» ⇒ CLIENT (IP حذف، حروف حفظ)',
    asset_display_name('CLIENT 192.168.1.65') === 'CLIENT', asset_display_name('CLIENT 192.168.1.65'));
check('۴۲: «CLIENT-192.168.1.65» ⇒ CLIENT (پیشوند خودکار حذف)',
    asset_display_name('CLIENT-192.168.1.65') === 'CLIENT', asset_display_name('CLIENT-192.168.1.65'));
check('۴۳: IP خالص به‌عنوان نام نمایش داده نمی‌شود (تنها شناسه)',
    asset_display_name('192.168.1.65') === '', var_export(asset_display_name('192.168.1.65'), true));
// ۱.۶۳.۱-r15 — رفتار عوض شد و **آگاهانه**: قبلاً این ورودی به «192.168.1.65»
// تبدیل می‌شد و همان IP در ستون «نام» می‌نشست. کاربر گزارش داد که رکوردها را
// به شکل «client 192.168.1.25» می‌بیند. اسم واقعی ماشین این نیست؛ این رشته را
// خودِ سامانه هنگام ورود کاربر ساخته. حالا خالی برمی‌گردد تا نمایش بگوید
// «نام ثبت نشده» و از Win32_ComputerSystem.Name پر شود.
check('۴۴: «CLIENT-192-168-1-65» هیچ IP‌ای به‌عنوان نام نمی‌دهد',
    asset_display_name('CLIENT-192-168-1-65') === '', var_export(asset_display_name('CLIENT-192-168-1-65'), true));
check('۴۴-ب: جایگزین صادقانه «نام ثبت نشده» است',
    asset_name_or('CLIENT-192-168-1-65') === 'نام ثبت نشده', asset_name_or('CLIENT-192-168-1-65'));
check('۴۵: نام عادی با IP داخلش مختل نمی‌شود (reza-pc)',
    asset_display_name('reza-pc') === 'reza-pc', asset_display_name('reza-pc'));
check('۴۶: نام دامنه‌ای همچنان درست کوتاه می‌شود (حالت رگرسیون)',
    asset_display_name('ADMINIT2.DOMAIN.LOCAL') === 'ADMINIT2', asset_display_name('ADMINIT2.DOMAIN.LOCAL'));
check('۴۷: حذف IP، بخش دامنه را هم پاک می‌کند (بدون باقی‌ماندهٔ «corp»)', asset_display_name('pc1 192.168.1.5 CORP') === 'pc1', asset_display_name('pc1 192.168.1.5 CORP'));
check('۴۸: نام خالی همچنان خالی است (رگرسیون)', asset_display_name('') === '', asset_display_name(''));

echo "\n" . ($fail === 0
    ? "همهٔ $pass بررسی نام کوتاه کامپیوترها (۱.۳۷.۵) موفق بود.\n"
    : "$fail بررسی ناموفق از " . ($pass + $fail) . " مورد.\n");
exit($fail === 0 ? 0 : 1);
