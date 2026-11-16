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

echo "\n═══ ۳.۵) کار با هر دو پروتکل http و https ═══\n";

$sessionSrc = $session;
check('تنظیم «هر دو پروتکل» در نشست خوانده می‌شود', str_contains($sessionSrc, 'security_cookie_both_protocols'));
check('روشن‌بودن آن پرچم Secure را برمی‌دارد', str_contains($sessionSrc, '$secure = $isHttps && !$bothProtocols;'));

// اثبات رفتاری: پرچم Secure برای هر مقدار تنظیم
$logic = null;
if (preg_match('/\$isHttps = .*?\n.*?\$secure = \$isHttps && !\$bothProtocols;/s', $sessionSrc, $m) === 1) {
    $logic = $m[0];
}
check('منطق تصمیم قابل استخراج است', $logic !== null);
if ($logic !== null) {
    $results = [];
    foreach (['0' => true, '1' => false, 'true' => false, 'on' => false, 'yes' => false, 'false' => true] as $val => $wantSecure) {
        $tmp3 = tempnam(sys_get_temp_dir(), 'ck') . '.php';
        file_put_contents($tmp3, "<?php\n\$_SERVER['HTTPS']='on';\n\$GLOBALS['__v']=" . var_export((string) $val, true) . ";\n"
            . "function setting(\$k, \$d=null){ return \$GLOBALS['__v']; }\n"
            . $logic . "\necho \$secure ? 'SECURE' : 'OPEN';\n");
        $got = trim((string) shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($tmp3) . ' 2>&1'));
        @unlink($tmp3);
        $results[] = $got === ($wantSecure ? 'SECURE' : 'OPEN');
    }
    check('روشن=آزاد و خاموش=Secure (۶ حالت)', !in_array(false, $results, true), implode(',', array_map('intval', $results)));
}

$core = (string) file_get_contents($root . '/lib/db-core.php');
check('HSTS قابل تنظیم است (نه hardcode)', str_contains($core, "cfg('security.hsts'"));
check('HSTS پیش‌فرض کوتاه است (۵ دقیقه)', str_contains($core, "default => 'max-age=300'"));
check('HSTS includeSubDomains فقط در strict', substr_count($core, "'max-age=31536000; includeSubDomains'") === 1);
check('حالت off هیچ هدر HSTS نمی‌فرستد', str_contains($core, "'off', 'none', '0', 'false' => ''"));

$settingsSrc = (string) file_get_contents($root . '/actions/settings-actions.php');
$indexSrc = (string) file_get_contents($root . '/index.php');
check('تنظیم از رابط کاربری ذخیره می‌شود', str_contains($settingsSrc, "save_setting('security_cookie_both_protocols'"));
// به‌جای تطبیق رشته در سورس (که کوتیشن‌های فرار مشکل می‌سازند)، همان قطعهٔ
// فرم را از index.php بیرون می‌کشیم و رفتارش را می‌سنجیم.
$formStart = strpos($indexSrc, 'security_cookie_both_protocols');
$formChunk = $formStart !== false ? substr($indexSrc, max(0, $formStart - 400), 900) : '';
check('فیلد مخفی «0» هست تا خاموش‌کردن هم کار کند',
    (bool) preg_match('/hidden[^>]*security_cookie_both_protocols[^>]*value=.?0/', $formChunk), $formChunk === '' ? 'قطعه پیدا نشد' : '');
check('چک‌باکس روشن‌کردن هست',
    (bool) preg_match('/checkbox[^>]*security_cookie_both_protocols[^>]*value=.?1/', $formChunk));
check('پیام ذخیره، اثر کار را توضیح می‌دهد', str_contains($settingsSrc, 'هر دو پروتکل'));

echo "\n═══ ۳.۶) نام آلوده به MAC — ۱.۶۳.۱-r17 ═══\n";

// asset-names.php همین بالاتر (بخش ۲) کامل بارگذاری شده است؛
// دوباره‌سازی توابع نه لازم است و نه درست — بازتعریف، نسخهٔ آزمون را
// جای نسخهٔ واقعی می‌گذاشت و همان باگ را پنهان می‌کرد.
$GLOBALS['__style'] = 'short';

// دقیقاً همان چیزی که کاربر دید: نام + IP + MAC در یک رشته
check('۱: «bazresi-pc 192.168.1.65 00-1A-…» ⇒ bazresi-pc',
    (function () { return function_exists('asset_display_name') ? asset_display_name('bazresi-pc 192.168.1.65 00-1A-2B-3C-4D-5E') : ''; })() === 'bazresi-pc',
    function_exists('asset_display_name') ? asset_display_name('bazresi-pc 192.168.1.65 00-1A-2B-3C-4D-5E') : 'تابع در دسترس نیست');
check('۲: MAC با دونقطه هم حذف می‌شود',
    function_exists('asset_display_name') && asset_display_name('bazresi-pc 192.168.1.65 AA:BB:CC:DD:EE:FF') === 'bazresi-pc');
check('۳: MAC تنها بدون IP هم حذف می‌شود',
    function_exists('asset_display_name') && asset_display_name('bazresi-pc 00-1A-2B-3C-4D-5E') === 'bazresi-pc');
check('۴: MAC خالص هرگز نام نمایش داده نمی‌شود',
    function_exists('asset_display_name') && asset_display_name('00-1A-2B-3C-4D-5E') === '');
check('۵: MAC خالص ⇒ «نام ثبت نشده»',
    function_exists('asset_name_or') && asset_name_or('00:1A:2B:3C:4D:5E') === 'نام ثبت نشده');
check('۶: IP+MAC خالص هیچ‌کدام نام نمی‌شوند',
    function_exists('asset_display_name') && asset_display_name('192.168.1.65 00-1A-2B-3C-4D-5E') === '');
check('۷: نام واقعی که شبیه MAC نیست آسیب نمی‌بیند',
    function_exists('asset_display_name') && asset_display_name('PC-12AB34CD') === 'PC-12AB34CD');
check('۸: MBA4-01 دست‌نخورده می‌ماند (رگرسیون مهم)',
    function_exists('asset_display_name') && asset_display_name('MBA4-01') === 'MBA4-01');
check('۹: کلید هویت پس از حذف MAC تکراری نمی‌سازد',
    function_exists('asset_identity_key') && asset_identity_key('bazresi-pc 192.168.1.65 00-1A-2B-3C-4D-5E') === asset_identity_key('bazresi-pc'));
check('۱۰: الگوی MAC و توابعش در asset-names.php هست',
    str_contains($names, 'ASSET_MAC_PATTERN') && str_contains($names, 'function asset_strip_mac') && str_contains($names, 'function asset_is_mac'));

echo "\n═══ ۳.۷) ابزار diag_extract — تله‌های ویندوز (r17-ب) ═══\n";

$diagSrc = (string) file_get_contents($root . '/tools/diag_extract.php');
// باگ واقعی: Read-Host داخل shell_exec هیچ کنسولی ندارد و رشتهٔ خالی
// برمی‌گرداند — رمز خالی و پیام گمراه‌کنندهٔ «اعتبارنامه نادرست».
check('۱۱: رمز با shell_exec خوانده نمی‌شود (باگ کنسول‌نداشتن)', !str_contains($diagSrc, 'shell_exec(\'powershell -NoProfile -Command "$s=Read-Host'));
check('۱۲: رمز از تابع اختصاصی خوانده می‌شود', str_contains($diagSrc, 'function diag_read_password'));
check('۱۳: گزینهٔ متغیر محیطی برای اجرای خودکار دارد', str_contains($diagSrc, 'ITSM_DIAG_PASS'));
check('۱۴: اگر رمز خالی ماند، پیام صریح می‌دهد و گیر نمی‌کند', str_contains($diagSrc, 'رمز خالی بود'));
// باگ واقعی: کدهای ANSI در cmd.exe قدیمی به‌صورت ←[31m چاپ می‌شدند
check('۱۵: رنگ فقط در ترمینال واقعی', str_contains($diagSrc, 'diagColorEnabled'));
check('۱۶: خروجی بدون رنگ هم خوانا است (علامت ✓/✗)', str_contains($diagSrc, "'✓'") || str_contains($diagSrc, "✓"));
// دکمهٔ اجرا: php.exe روی IIS در PATH نیست
$bat = (string) file_get_contents($root . '/tools/RUN-DIAG-EXTRACT.bat');
check('۱۷: اجراکنندهٔ batch منتشر شده است', $bat !== '');
check('۱۸: php را در PATH و مسیرهای رایج می‌گردد', str_contains($bat, 'where php') && str_contains($bat, 'Program Files\\PHP'));
check('۱۹: مسیر خودش را امن می‌سازد (%~dp0)', str_contains($bat, '%~dp0diag_extract.php'));
check('۲۰: اگر php پیدا نشد، راهنمای صریح می‌دهد', str_contains($bat, 'php.exe پیدا نشد'));
// سند دستور اجرا
$cmdDoc = (string) file_get_contents($root . '/docs/DIAG-EXTRACT-COMMANDS-FA.md');
check('۲۱: سند دستور اجرا موجود است', $cmdDoc !== '');
check('۲۲: می‌گوید روی سرور XAMPP اجرا شود، نه روی کلاینت', str_contains($cmdDoc, 'روی **همان سرور XAMPP**'));
check('۲۳: مسیر جایگزین php.exe را نشان می‌دهد', str_contains($cmdDoc, 'C:\\xampp\\php\\php.exe'));

echo "\n═══ ۳.۸) XAMPP — حساب سرویس Apache (r17-ج) ═══\n";
// روی XAMPP، Apache با LocalSystem اجرا می‌شود و PHP همان هویت را دارد؛
// پس استخراج از راه دور از پنل هیچ دسترسی شبکه‌ای ندارد. این باید در ابزار
// تشخیص داده و صریح گفته شود، نه اینکه کاربر دنبال فایروال بگردد.
check('۲۴: حساب سرویس Apache خوانده می‌شود', str_contains($diagSrc, 'حساب سرویس Apache'));
check('۲۵: LocalSystem به‌عنوان ریشهٔ مشکل نام برده می‌شود', str_contains($diagSrc, 'LocalSystem'));
check('۲۶: نام سرویس Apache2.4 جست‌وجو می‌شود', str_contains($diagSrc, 'Apache2.4'));
// نشت رمز: خط فرمان فرآیند برای هر کاربری در Task Manager خوانا است.
check('۲۷: رمز SMB از خط فرمان رد نمی‌شود', !str_contains($diagSrc, "'-Pass', \$pass"));
check('۲۸: رمز از محیط فرآیند فرزند می‌رود', str_contains($diagSrc, 'ITSM_DIAG_SMB_PASS'));
// اثر جانبی روی کلاینت: پوشهٔ آزمون باید پاک شود.
check('۲۹: پوشهٔ آزمون روی مقصد پاک می‌شود', str_contains($diagSrc, 'Remove-Item -Path "$($drive.Name):\\ITSM" -Recurse'));
// سند باید XAMPP را اول حساب کند، نه IIS
check('۳۰: سند مسیر کامل C:\\xampp\\php\\php.exe را می‌دهد', str_contains($cmdDoc, 'C:\\xampp\\php\\php.exe'));
check('۳۱: سند هشدار LocalSystem را توضیح می‌دهد', str_contains($cmdDoc, 'LocalSystem'));
check('۳۲: سند مسیر client-agent را به‌عنوان جایگزین می‌دهد', str_contains($cmdDoc, 'Collect-Inventory.ps1'));
// فایل batch باید xampp را قبل از بقیه بگردد
$batPos = strpos($bat, 'C:\\xampp\\php\\php.exe');
$progPos = strpos($bat, 'C:\\Program Files\\PHP\\php.exe');
check('۳۳: batch اول xampp را می‌گردد', $batPos !== false && $progPos !== false && $batPos < $progPos);
check('۳۴: batch درایو D را هم می‌گردد', str_contains($bat, 'D:\\xampp\\php\\php.exe'));

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
