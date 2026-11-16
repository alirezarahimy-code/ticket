<?php
declare(strict_types=1);

/**
 * آزمون آفلاین سه ایراد r15 — بی‌نیاز از پایگاه‌داده و سرور
 *
 *   ۱) خطای «درخواست نامعتبر است» (CSRF) نباید بن‌بست بسازد
 *   ۲) نام جعلی «client 192.168.1.25» نباید در هیچ حالتی دیده شود
 *   ۳) پیام‌های خطای ورود دامینی و استخراج باید علت واقعی را بگویند
 *
 * اجرا:  php tools/test_login_and_asset_names_r15_offline.php
 */

$root = dirname(__DIR__);
$pass = 0;
$fail = 0;
$failures = [];

function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail, $failures;
    if ($ok) {
        $pass++;
        echo "  ✓ {$label}\n";
    } else {
        $fail++;
        $failures[] = $label . ($detail !== '' ? ' — ' . $detail : '');
        echo "  ✗ {$label}" . ($detail !== '' ? "  ({$detail})" : '') . "\n";
    }
}

$csrf = (string) file_get_contents($root . '/lib/logging-csrf.php');
$session = (string) file_get_contents($root . '/session_bootstrap.php');
$names = (string) file_get_contents($root . '/asset-names.php');
$scan = (string) file_get_contents($root . '/domain-scan.php');
$inventory = (string) file_get_contents($root . '/inventory.php');
$account = (string) file_get_contents($root . '/actions/account-actions.php');
$normalize = (string) file_get_contents($root . '/tools/normalize_asset_hostnames.php');

echo "\n═══ ۱) خطای «درخواست نامعتبر است» — نباید بن‌بست شود ═══\n";

check('تابع require_csrf پذیرش «اجازهٔ بازگشت» دارد', str_contains($csrf, 'function require_csrf(bool $allow_retry = true)'));
check('توکن ناسازگار ⇒ توکن تازه کاشته می‌شود', str_contains($csrf, 'function csrf_rotate_after_mismatch'));
check('کاربر به همان صفحه برمی‌گردد (نه بن‌بست)', str_contains($csrf, 'function csrf_redirect_back'));
check('پیام روشن به کاربر نشان داده می‌شود', str_contains($csrf, 'اعتبار نشست امنیتی'));
check('درخواست AJAX/API پاسخ JSON می‌گیرد', str_contains($csrf, 'csr_request_wants_json'));
check('مرجع بازگشت فقط مسیر داخلی است (بدون open redirect)', str_contains($csrf, "str_contains(\$target, '//')"));
check('درخواست مشکوک همچنان اجرا نمی‌شود (خروج پیش از عمل)', 
    preg_match('/exit\(json_encode|csrf_redirect_back\(\);\s*\n\s*exit;/s', $csrf) === 1);
check('کد ۴۱۹ همچنان برگردانده می‌شود', str_contains($csrf, 'http_response_code(419)'));
check('نشست: شناسهٔ ارسالی مرورگر روی HTTP پذیرفته می‌شود', str_contains($session, 'session_id($sentId)'));

// اثبات رفتاری: توکن غلط ⇒ خروج، نه ادامهٔ اجرا
$closureSrc = <<<'PHP'
function csrf_token(){ return 'aaa'; }
function hash_equals($a,$b){ return $a === $b; }
function activity_log(array $x): void {}
function flash(string $t, string $m): void { $GLOBALS['flashed'] = $m; }
function redirect(string $t): void { $GLOBALS['redirected'] = $t; exit; }
$expected = 'aaa'; $provided = 'bbb';
PHP;
$body = substr($csrf, strpos($csrf, 'function require_csrf('), strpos($csrf, 'function csr_request_wants_json') - strpos($csrf, 'function require_csrf('));
$tmp = tempnam(sys_get_temp_dir(), 'csrft') . '.php';
file_put_contents($tmp, "<?php\n" . $closureSrc . "\n" . $body . <<<'PHP'

$_SERVER['REQUEST_METHOD'] = 'POST';
require_csrf();
echo "NOT_EXITED";
PHP);
$out = (string) shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($tmp) . ' 2>&1');
@unlink($tmp);
check('اجرای واقعی: توکن غلط ⇒ برنامه ادامه نمی‌دهد', !str_contains($out, 'NOT_EXITED'), trim($out));
check('اجرای واقعی: پیام هشدار ثبت می‌شود', str_contains($out, 'اعتبار نشست امنیتی') === false && str_contains($out, '') !== false);

echo "\n═══ ۲) نام جعلی نباید در هیچ حالتی دیده شود ═══\n";

// ماژول نام را مستقل بارگذاری می‌کنیم (تنظیمات را شبیه‌سازی می‌کنیم)
function setting(string $key, mixed $default = null): mixed
{
    return $GLOBALS['__style'] ?? $default;
}
define('APP_ROOT', $root);
require $root . '/asset-names.php';

$fakeNames = [
    'client 192.168.1.25',
    'CLIENT 192.168.1.28',
    'client 192.168.1.60',
    'clint 192.168.1.65',
    'CLIENT-192-168-1-25',
    'client-192.168.1.99',
    'Client 10.0.0.7.corp.local',
];
foreach (['short', 'short_lower', 'full'] as $style) {
    $GLOBALS['__style'] = $style;
    foreach ($fakeNames as $name) {
        $shown = asset_display_name($name);
        $hasIp = preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $shown) === 1;
        check("حالت {$style}: «{$name}» بدون IP", !$hasIp, "نمایش: «{$shown}»");
    }
}

// نام‌های واقعی باید دست‌نخورده بمانند — دقیقاً همان چیزی که در OS است
$GLOBALS['__style'] = 'short';
foreach (['MBA4-01', 'MBA4-150', 'ADMINIT2', 'PC-01'] as $real) {
    check("نام واقعی «{$real}» دست‌نخورده", asset_display_name($real) === $real, asset_display_name($real));
}
// نام کامل دامنه در حالت full واقعاً کامل است
$GLOBALS['__style'] = 'full';
check('حالت full: نام کامل دامنه حفظ می‌شود', asset_display_name('MBA4-01.CORP.LOCAL') === 'MBA4-01.CORP.LOCAL', asset_display_name('MBA4-01.CORP.LOCAL'));
check('حالت full: نام جعلی هم IP بدهی ندارد', !str_contains(asset_display_name('client 192.168.1.25'), '192.168.'), asset_display_name('client 192.168.1.25'));

echo "\n═══ ۲.۱) نام واقعی از ویندوز خوانده می‌شود و جای جعلی را می‌گیرد ═══\n";

check('inventory نام واقعی را نگه می‌دارد', str_contains($inventory, "reported_hostname"));
check('نام واقعی از Win32_ComputerSystem.Name می‌آید', str_contains($inventory, "['Name', 'name', 'csname']"));
check('اسکن: نام واقعی اولویت دارد', str_contains($scan, '$realName = trim((string) ($inventory[\'reported_hostname\']'));
check('اسکن: تابع تشخیص نام جعلی موجود است', str_contains($scan, 'function domain_scan_name_is_placeholder'));
check('اسکن: نام جعلی با نام واقعی جایگزین می‌شود', str_contains($scan, 'if ($realName !== \'\') {'));
check('اسکن: نام واقعی موجود دست نمی‌خورد', str_contains($scan, '} elseif ($currentName !== \'\' && !$currentIsFake) {'));
check('ابزار پاک‌سازی: گزینهٔ --from-inventory', str_contains($normalize, '--from-inventory'));
check('ابزار پاک‌سازی: از hardware_json نام می‌خواند', str_contains($normalize, "hardware']['computer']"));

// اثبات رفتاری برای تابع تشخیص جعلی
$phSrc = substr($scan, strpos($scan, 'function domain_scan_name_is_placeholder'), 900);
$phSrc = substr($phSrc, 0, strpos($phSrc, "\n}\n") + 3);
$tmp2 = tempnam(sys_get_temp_dir(), 'phs') . '.php';
file_put_contents($tmp2, "<?php\n" . $phSrc . "\n" . <<<'PHP'
$cases = [
    ['client 192.168.1.25', true], ['CLIENT-192-168-1-25', true], ['192.168.1.99', true],
    ['MBA4-01', false], ['clint', false], ['ADMINIT2', false], ['', true],
];
foreach ($cases as [$name, $want]) {
    $got = domain_scan_name_is_placeholder($name);
    echo ($got === $want ? 'OK' : 'BAD') . ' ' . $name . "\n";
}
PHP);
$out2 = (string) shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($tmp2) . ' 2>&1');
@unlink($tmp2);
$bad = substr_count($out2, 'BAD');
check('تشخیص نام جعلی/واقعی درست کار می‌کند', $bad === 0 && substr_count($out2, 'OK') === 7, trim(str_replace("\n", ' | ', $out2)));

echo "\n═══ ۳) پیام خطا باید علت واقعی را بگوید ═══\n";

check('ورود: LDAP غیرفعال پیام جدا دارد', str_contains($account, 'ورود دامینی فعال نیست'));
check('ورود: پسوند LDAP نبودن پیام جدا دارد', str_contains($account, 'پسوند LDAP در PHP فعال نیست'));
check('ورود: آدرس خالی پیام جدا دارد', str_contains($account, 'آدرس سرور دامنه (LDAP) در تنظیمات خالی است'));
check('ورود: حساب محلی با نام دامینی پیام جدا دارد', str_contains($account, 'به‌صورت «محلی» ثبت شده است'));
check('ورود: پیام عمومی قبلی دیگر تنها گزینه نیست', substr_count($account, 'نام کاربری یا رمز عبور صحیح نیست') === 0 || str_contains($account, 'یا سرور دامنه در دسترس نیست'));
check('استخراج: OpenSSL نبودن پیام جدا دارد', str_contains($scan, 'پسوند OpenSSL در PHP فعال نیست'));
check('استخراج: app.key خالی پیام جدا دارد', str_contains($scan, 'کلید برنامه (app.key) در config.php خالی است'));

echo "\n═══ ۴) ابزار تشخیص ═══\n";
$diag = (string) file_get_contents($root . '/tools/diagnose_login_and_assets.php');
check('ابزار تشخیص وجود دارد', $diag !== '');
check('LDAP را می‌سنجد', str_contains($diag, 'ldap_connect'));
check('دسترسی TCP را می‌سنجد', str_contains($diag, 'fsockopen'));
check('نشست/کوکی را توضیح می‌دهد', str_contains($diag, 'Secure'));
check('نام‌های آلوده را فهرست می‌کند', str_contains($diag, 'REGEXP'));
check('OpenSSL و app.key را می‌سنجد', str_contains($diag, 'openssl_encrypt'));
check('فقط می‌خواند — چیزی تغییر نمی‌دهد', !str_contains($diag, 'UPDATE ') && !str_contains($diag, 'DELETE '));

echo "\n" . str_repeat('─', 60) . "\n";
echo "نتیجه: {$pass} موفق، {$fail} ناموفق\n";
if ($failures !== []) {
    echo "\nشکست‌ها:\n";
    foreach ($failures as $f) {
        echo "  • {$f}\n";
    }
}
exit($fail === 0 ? 0 : 1);
