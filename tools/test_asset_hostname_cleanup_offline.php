<?php
declare(strict_types=1);

/**
 * آزمون آفلاین ابزار پاک‌سازی نام کامپیوترها — نسخهٔ ۱.۶۳.۱
 *
 * چرا این آزمون: روی شبکهٔ واقعی، reverse DNS نامی مثل «clint 192.168.1.65»
 * برمی‌گرداند. قاعدهٔ نمایش پیش‌تر آن را به «clint 192» تبدیل می‌کرد — نه نام،
 * نه IP. این آزمون همان سناریو و چند حالت دیگر را ثابت می‌کند و بررسی می‌کند
 * که ابزار تعمیر هم همان قاعده را به کار می‌برد.
 *
 * اجرا:  php tools/test_asset_hostname_cleanup_offline.php
 */

$root = dirname(__DIR__);
$file = $root . '/asset-names.php';
if (!is_file($file)) {
    fwrite(STDERR, "فایل asset-names.php پیدا نشد.\n");
    exit(1);
}

/* ───── محیط حداقلی ───── */
$GLOBALS['FT_SETTINGS'] = [];
function setting(string $key, ?string $default = null): ?string
{
    return $GLOBALS['FT_SETTINGS'][$key] ?? $default;
}
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** استخراج بدنهٔ یک تابع از فایل، با شمارش آکولاد. */
function grabFunction(string $source, string $name): string
{
    if (preg_match('/\bfunction\s+' . preg_quote($name, '/') . '\s*\(/', $source, $m, PREG_OFFSET_CAPTURE) !== 1) {
        throw new RuntimeException('تابع پیدا نشد: ' . $name);
    }
    $start = (int) $m[0][1];
    $brace = strpos($source, '{', $start + strlen($m[0][0]));
    if ($brace === false) {
        throw new RuntimeException('بدنهٔ تابع پیدا نشد: ' . $name);
    }
    $depth = 0;
    $len = strlen($source);
    for ($i = $brace; $i < $len; $i++) {
        if ($source[$i] === '{') {
            $depth++;
        } elseif ($source[$i] === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($source, $start, $i - $start + 1);
            }
        }
    }
    throw new RuntimeException('پایان تابع پیدا نشد: ' . $name);
}

$source = (string) file_get_contents($file);

if (!defined('ASSET_IPV4_PATTERN')) {
    if (preg_match("/const ASSET_IPV4_PATTERN = '([^']+)';/", $source, $mPattern) === 1) {
        define('ASSET_IPV4_PATTERN', str_replace('\\\\', '\\', $mPattern[1]));
    } else {
        define('ASSET_IPV4_PATTERN', '((?:\d{1,3}\.){3}\d{1,3})');
    }
}

foreach (['asset_is_ip', 'asset_is_domain_name', 'asset_strip_ip', 'asset_hostname_style', 'asset_name_core', 'asset_display_name', 'asset_name_or', 'asset_is_auto_domain_tag', 'asset_name_title', 'asset_attr_name'] as $fn) {
    if (function_exists($fn)) {
        continue;
    }
    try {
        eval(grabFunction($source, $fn));
    } catch (Throwable) {
        // تابع اختیاری است
    }
}

$pass = 0;
$fail = 0;
function check(string $label, bool $ok, string $actual = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "[PASS] " . $label . "\n";
    } else {
        $fail++;
        echo "[FAIL] " . $label . ($actual !== '' ? ' — ' . $actual : '') . "\n";
    }
}

function set_style(string $style): void
{
    $GLOBALS['FT_SETTINGS']['asset_hostname_style'] = $style;
}

echo "=== آزمون پاک‌سازی نام کامپیوتر (۱.۶۳.۱) ===\n\n";

set_style('short_lower');

/* ───── ۱) همان سناریوی کاربر ───── */
check(
    '۱: «clint 192.168.1.65» ⇒ clint (پیش‌تر «clint 192» می‌شد)',
    asset_name_core('clint 192.168.1.65') === 'clint',
    asset_name_core('clint 192.168.1.65')
);
check(
    '۲: نمایش همان مورد هم clint است',
    asset_display_name('clint 192.168.1.65') === 'clint',
    asset_display_name('clint 192.168.1.65')
);

/* ───── ۲) شکل‌های دیگر IP چسبیده ───── */
foreach ([
    'clint-192.168.1.65' => 'clint',
    'clint_192.168.1.65' => 'clint',
    'pc1 10.1.2.3' => 'pc1',
    'PC1 10.1.2.3' => 'pc1',
    'clint 192.168.1.65.corp.local' => 'clint',
    'CLIENT 192.168.1.65' => 'client',
    'clint   192.168.1.65   ' => 'clint',
] as $input => $expected) {
    check(
        '3: «' . $input . '» ⇒ ' . $expected,
        asset_display_name($input) === $expected,
        asset_display_name($input)
    );
}

/* ───── ۳) نام واقعی نباید آسیب ببیند ───── */
foreach ([
    'reza-pc' => 'reza-pc',
    'adminit2' => 'adminit2',
    'ADMINIT2.DOMAIN.LOCAL' => 'adminit2',
    'DOMAIN-ADMINIT2' => 'adminit2',
    'ADMINIT2$' => 'adminit2',
    'pc-099' => 'pc-099',
    'server01' => 'server01',
] as $input => $expected) {
    check(
        '4: رگرسیون «' . $input . '» ⇒ ' . $expected,
        asset_display_name($input) === $expected,
        asset_display_name($input)
    );
}

/* ───── ۴) فقط IP: دست‌نخورده ───── */
foreach (['192.168.1.65', '10.0.0.5', '127.0.0.1'] as $ip) {
    check(
        '5: IP خالص «' . $ip . '» به‌عنوان نام نمایش داده نمی‌شود',
        asset_display_name($ip) === '',
        var_export(asset_display_name($ip), true)
    );
}

/* ───── ۵) حالت‌های نمایش ───── */
set_style('short');
check('6: حالت short حروف اصلی را نگه می‌دارد', asset_name_core('CLINT 192.168.1.65') === 'CLINT', asset_name_core('CLINT 192.168.1.65'));
check('7: نمایش short = CLINT', asset_display_name('CLINT 192.168.1.65') === 'CLINT', asset_display_name('CLINT 192.168.1.65'));
set_style('full');
check('8: حالت full نام کامل می‌دهد', asset_display_name('CLINT.CORP.LOCAL') === 'CLINT.CORP.LOCAL', asset_display_name('CLINT.CORP.LOCAL'));
set_style('short_lower');

/* ───── ۶) خالی و فاصله ───── */
check('9: نام خالی ⇒ خالی', asset_display_name('') === '', asset_display_name(''));
check('10: فقط فاصله ⇒ خالی', asset_display_name('   ') === '', asset_display_name('   '));

/* ───── ۷) IP خط‌تیره‌ای (شکل نام خودکار کلاینت) ───── */
// ۱.۶۳.۱-r15 — رفتار عوض شد و آگاهانه. قبلاً این ورودی «192.168.1.65»
// برمی‌گرداند و همان IP در ستون «نام» می‌نشست — همان چیزی که کاربر گزارش
// داد («client 192.168.1.25»). اسم واقعی ماشین این نیست؛ این رشته را خودِ
// سامانه هنگام ورود کاربر ساخته. حالا خالی برمی‌گردد تا نمایش از
// Win32_ComputerSystem.Name پر شود و تا آن موقع «نام ثبت نشده» بگوید.
check(
    '11: «CLIENT-192-168-1-65» هیچ IP‌ای به‌عنوان نام نمی‌دهد',
    asset_display_name('CLIENT-192-168-1-65') === '',
    var_export(asset_display_name('CLIENT-192-168-1-65'), true)
);
check(
    '11-ب: جایگزین نمایشی «نام ثبت نشده» است',
    asset_name_or('CLIENT-192-168-1-65') === 'نام ثبت نشده',
    asset_name_or('CLIENT-192-168-1-65')
);

/* ───── ۸) باقی‌ماندهٔ دامنه پس از IP ───── */
check(
    '12: «pc1 192.168.1.5 CORP» ⇒ pc1 (دامنهٔ بدون نقطه هم می‌رود)',
    asset_display_name('pc1 192.168.1.5 CORP') === 'pc1',
    asset_display_name('pc1 192.168.1.5 CORP')
);

/* ───── ۹) ابزار تعمیر: همان قاعده را به کار می‌برد ───── */
$tool = $root . '/tools/normalize_asset_hostnames.php';
check('13: ابزار normalize_asset_hostnames.php منتشر شده است', is_file($tool));
if (is_file($tool)) {
    $toolSrc = (string) file_get_contents($tool);
    check(
        '14: ابزار از asset_name_core استفاده می‌کند (قاعدهٔ واحد، نه کد تکراری)',
        str_contains($toolSrc, 'asset_name_core(')
    );
    check(
        '15: ابزار کلید هویت را پس از تغییر نام به‌روز می‌کند',
        str_contains($toolSrc, 'asset_sync_identity_key(') || str_contains($toolSrc, 'asset_identity_key(')
    );
    check(
        '16: ابزار یک رکوردِ «فقط IP» را دست نمی‌زند',
        str_contains($toolSrc, "FILTER_VALIDATE_IP") && str_contains($toolSrc, 'untouchedIpOnly')
    );
    check(
        '17: پیش‌فرض ابزار گزارش است و تا --apply تغییری نمی‌دهد',
        str_contains($toolSrc, "\$apply = in_array('--apply', \$args, true)")
    );
    check(
        '18: ابزار فقط در CLI اجرا می‌شود',
        str_contains($toolSrc, "PHP_SAPI !== 'cli'")
    );
}

/* ───── ۱۰) نوشتن نام، کلید هویت را عوض می‌کند (همهٔ مسیرها) ───── */
$inventorySrc = (string) file_get_contents($root . '/inventory.php');
$domainSrc = (string) file_get_contents($root . '/domain-scan.php');
$assetActionsSrc = (string) file_get_contents($root . '/actions/asset-actions.php');

check('19: inventory.php پس از تغییر نام، کلید هویت را به‌روز می‌کند', substr_count($inventorySrc, 'asset_sync_identity_key') >= 4, (string) substr_count($inventorySrc, 'asset_sync_identity_key'));
check('20: domain-scan.php کلید هویت را هنگام ثبت/به‌روزرسانی نام می‌نویسد', substr_count($domainSrc, 'asset_identity_key(') >= 1 || substr_count($domainSrc, 'asset_sync_identity_key') >= 1);
check('21: فرم شناسنامه (asset-actions) کلید هویت را پس از ویرایش نام به‌روز می‌کند', str_contains($assetActionsSrc, 'asset_sync_identity_key($assetId)'));

/* ───── ۱۱) کمبوی سیستم مرتبط از همان تابع استفاده می‌کند ───── */
$createSrc = (string) file_get_contents($root . '/modules/request-center/Pages/create.php');
check('22: کمبوی «سیستم مرتبط» از تابع نمایشی نام استفاده می‌کند',
    str_contains($createSrc, 'asset_display_name(') || str_contains($createSrc, 'asset_name_or('));

echo "\n" . ($fail === 0
    ? "همهٔ {$pass} بررسی پاک‌سازی نام کامپیوتر موفق بود.\n"
    : "{$fail} بررسی ناموفق از " . ($pass + $fail) . " مورد.\n");

exit($fail === 0 ? 0 : 1);
