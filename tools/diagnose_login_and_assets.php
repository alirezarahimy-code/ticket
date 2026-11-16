<?php
declare(strict_types=1);

/**
 * تشخیص گام‌به‌گام سه ایراد گزارش‌شده — نسخهٔ ۱.۶۳.۱-r15
 *
 * چرا این ابزار: هر سه ایراد «از بیرون» شبیه هم دیده می‌شوند (پیام کلی،
 * بدون جزئیات) ولی سه ریشهٔ کاملاً جدا دارند. این ابزار همان چیزهایی را
 * می‌سنجد که خودِ کد در زمان اجرا می‌بیند و می‌گوید کدام شرط برقرار نیست.
 *
 * اجرا (از ریشهٔ نصب، روی همان سرور):
 *   php tools/diagnose_login_and_assets.php
 *
 * هیچ چیزی را تغییر نمی‌دهد — فقط می‌خواند و گزارش می‌دهد.
 * خروجی: کد ۰ اگر همه‌چیز سالم، ۱ اگر ایرادی پیدا شود.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$root = dirname(__DIR__);
$configPath = (string) (getenv('ITSM_CONFIG_PATH') ?: $root . '/config.php');
if (!is_file($configPath)) {
    fwrite(STDERR, "❌ config.php پیدا نشد: {$configPath}\n");
    exit(1);
}
require $root . '/bootstrap.php';
if (!function_exists('asset_display_name')) {
    require $root . '/asset-names.php';
}

$problems = [];

function dline(string $mark, string $label, string $detail = ''): void
{
    $icon = match ($mark) {
        'ok' => '✓',
        'bad' => '✗',
        'warn' => '!',
        default => '·',
    };
    printf("  %s %-46s %s\n", $icon, $label, $detail);
}

echo "\n══════════════════════════════════════════════════════════════\n";
echo " ۱) احراز هویت دامینی (LDAP)\n";
echo "══════════════════════════════════════════════════════════════\n";

$ldapExt = function_exists('ldap_connect');
dline($ldapExt ? 'ok' : 'bad', 'پسوند LDAP در PHP', $ldapExt ? 'فعال' : 'غیرفعال — در php.ini خط extension=ldap');
if (!$ldapExt) {
    $problems[] = 'پسوند LDAP در PHP غیرفعال است؛ هیچ ورود دامینی ممکن نیست.';
}

$ldapEnabled = (bool) ldap_cfg('enabled', false);
dline($ldapEnabled ? 'ok' : 'bad', 'ورود دامینی در تنظیمات', $ldapEnabled ? 'فعال' : 'غیرفعال — تنظیمات ← دامنه (LDAP)');
if (!$ldapEnabled) {
    $problems[] = 'ورود دامینی در تنظیمات فعال نیست؛ کاربران دامنه رد می‌شوند.';
}

$host = trim((string) ldap_cfg('host', ''));
$port = (int) ldap_cfg('port', 389);
$port = $port > 0 ? $port : ((bool) ldap_cfg('ssl', false) ? 636 : 389);
dline($host !== '' ? 'ok' : 'bad', 'آدرس سرور دامنه', $host !== '' ? $host . ':' . $port : 'خالی');
if ($host === '') {
    $problems[] = 'آدرس سرور LDAP خالی است.';
}

$baseDn = trim((string) ldap_cfg('base_dn', ''));
dline($baseDn !== '' ? 'ok' : 'warn', 'Base DN', $baseDn !== '' ? $baseDn : 'خالی — از RootDSE خوانده می‌شود');

$bindDn = trim((string) ldap_cfg('bind_dn', ''));
dline($bindDn !== '' ? 'ok' : 'warn', 'حساب سرویس (Bind DN)', $bindDn !== '' ? $bindDn : 'خالی — اتصال ناشناس');

if ($host !== '' && $ldapExt) {
    $scheme = (bool) ldap_cfg('ssl', false) ? 'ldaps://' : 'ldap://';
    $started = microtime(true);
    $conn = @ldap_connect($scheme . $host, $port);
    $reachable = false;
    if ($conn) {
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        if (defined('LDAP_OPT_NETWORK_TIMEOUT')) {
            @ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 5);
        }
        $bound = $bindDn !== '' ? @ldap_bind($conn, $bindDn, (string) ldap_cfg('bind_password', '')) : @ldap_bind($conn);
        if ($bound) {
            $reachable = true;
            dline('ok', 'اتصال و Bind به سرور دامنه', 'موفق (' . (int) round((microtime(true) - $started) * 1000) . 'ms)');
            $rootDse = @ldap_read($conn, '', '(objectClass=*)', ['defaultNamingContext', 'dnsHostName']);
            if ($rootDse) {
                $entry = ldap_get_entries($conn, $rootDse);
                $dnc = (string) ($entry[0]['defaultnamingcontext'][0] ?? '');
                dline($dnc !== '' ? 'ok' : 'warn', 'defaultNamingContext', $dnc !== '' ? $dnc : 'خوانده نشد');
            }
        } else {
            $err = @ldap_error($conn);
            $diag = '';
            if (defined('LDAP_OPT_DIAGNOSTIC_MESSAGE')) {
                @ldap_get_option($conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $diag);
            }
            dline('bad', 'اتصال و Bind به سرور دامنه', 'شکست: ' . $err . ($diag !== '' ? ' | ' . $diag : ''));
            $problems[] = 'اتصال به سرور LDAP برقرار نشد (' . $err . '). حساب سرویس یا شبکه را بررسی کنید.';
        }
        @ldap_unbind($conn);
    } else {
        dline('bad', 'اتصال به ' . $scheme . $host . ':' . $port, 'ldap_connect شکست خورد');
        $problems[] = 'ldap_connect به ' . $host . ':' . $port . ' شکست خورد.';
    }
    // آزمون TCP خام — مستقل از LDAP، برای جدا کردن مشکل شبکه از مشکل اعتبار
    $sock = @fsockopen($host, $port, $errno, $errstr, 5);
    dline($sock ? 'ok' : 'bad', 'دسترسی TCP به ' . $host . ':' . $port, $sock ? 'باز است' : 'بسته: ' . $errstr . ' (' . $errno . ')');
    if ($sock) {
        fclose($sock);
    } elseif ($host !== '') {
        $problems[] = 'پورت ' . $port . ' روی ' . $host . ' باز نیست؛ از این سرور به کنترلر دامنه دسترسی نیست.';
    }
}

// حساب‌هایی که نام کاربری‌شان «محلی» ثبت شده — ورود دامینی برایشان ممکن نیست
try {
    $localCount = (int) db()->query("SELECT COUNT(*) FROM users WHERE auth_source = 'local'")->fetchColumn();
    $ldapCount = (int) db()->query("SELECT COUNT(*) FROM users WHERE auth_source = 'ldap'")->fetchColumn();
    $foodCount = (int) db()->query("SELECT COUNT(*) FROM users WHERE auth_source = 'food'")->fetchColumn();
    dline('info', 'حساب‌ها', 'محلی ' . $localCount . ' · دامینی ' . $ldapCount . ' · غذا ' . $foodCount);
} catch (Throwable $e) {
    dline('bad', 'خواندن جدول کاربران', $e->getMessage());
}

echo "\n══════════════════════════════════════════════════════════════\n";
echo " ۲) اتصال امن و نشست (خطای «درخواست نامعتبر است»)\n";
echo "══════════════════════════════════════════════════════════════\n";

dline('info', 'این اجرا از خط فرمان است', 'کوکی مرورگر اینجا دیده نمی‌شود');
echo "\n  بررسی‌های زیر را از مرورگر انجام دهید:\n";
echo "   • اگر یک بار با https:// وارد شده‌اید، همان آدرس را ادامه دهید.\n";
echo "     کوکی نشست با پرچم Secure ذخیره می‌شود و با http:// فرستاده نمی‌شود.\n";
echo "   • برای برگشت به http:// یک بار کوکی‌های سایت را در مرورگر پاک کنید\n";
echo "     (یا در حالت ناشناس باز کنید).\n";

$enforce = (bool) cfg('security.enforce_https', false);
dline($enforce ? 'ok' : 'info', 'enforce_https در config', $enforce ? 'فعال — HTTP به HTTPS منتقل می‌شود' : 'غیرفعال');
dline('info', 'عمر کوکی نشست', (string) ini_get('session.cookie_lifetime') . ' ثانیه');

echo "\n══════════════════════════════════════════════════════════════\n";
echo " ۳) نام‌های آلوده در شناسنامه («client 192.168.x.x»)\n";
echo "══════════════════════════════════════════════════════════════\n";

$style = asset_hostname_style();
dline($style === 'full' ? 'warn' : 'ok', 'حالت نمایش نام کامپیوتر', $style . ($style === 'full' ? ' — قبلاً رشتهٔ خام را دست‌نخورده نشان می‌داد؛ در r15 اصلاح شد' : ''));

try {
    $rows = db()->query("SELECT id, hostname, source FROM assets WHERE hostname REGEXP '[0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    $dirty = 0;
    foreach ($rows as $row) {
        $raw = (string) $row['hostname'];
        // اگر نام «خالص IP» است، نام واقعی ندارد و پاک‌سازی سودی ندارد.
        if (asset_is_ip(trim($raw))) {
            continue;
        }
        $dirty++;
        if ($dirty <= 25) {
            printf("     #%-5s %-34s ⇒ نمایش: %s\n", $row['id'], $raw, asset_display_name($raw));
        }
    }
    dline($dirty === 0 ? 'ok' : 'warn', 'رکوردهای دارای IP داخل نام', (string) $dirty . ' مورد');
    if ($dirty > 0) {
        echo "\n  این رکوردها نام واقعی‌شان در AD/Windows چیز دیگری است و باید اصلاح شوند:\n";
        echo "    php tools/normalize_asset_hostnames.php --from-inventory\n";
        echo "    php tools/normalize_asset_hostnames.php --from-inventory --apply\n";
        echo "  (ابزار فقط IP/دامنه را از نام می‌برد؛ نام واقعی را از خود ویندوز می‌خواند.)\n";
        $problems[] = $dirty . ' رکورد نام آلوده به IP دارد.';
    }
} catch (Throwable $e) {
    dline('bad', 'خواندن جدول دارایی‌ها', $e->getMessage());
}

echo "\n══════════════════════════════════════════════════════════════\n";
echo " ۴) استخراج شناسنامه\n";
echo "══════════════════════════════════════════════════════════════\n";

$openssl = function_exists('openssl_encrypt') && function_exists('openssl_decrypt');
dline($openssl ? 'ok' : 'bad', 'پسوند OpenSSL', $openssl ? 'فعال' : 'غیرفعال — رمز حساب اسکن رمز نمی‌شود');
if (!$openssl) {
    $problems[] = 'پسوند OpenSSL در PHP غیرفعال است؛ استخراج شبکه‌ای کار نمی‌کند.';
}
$appKey = trim((string) cfg('app.key', ''));
dline($appKey !== '' ? 'ok' : 'bad', 'app.key در config', $appKey !== '' ? 'موجود (' . strlen($appKey) . ' کاراکتر)' : 'خالی');
if ($appKey === '') {
    $problems[] = 'app.key در config.php خالی است.';
}
$isHttpsNow = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
dline('info', 'وضعیت HTTPS این اجرا', $isHttpsNow ? 'HTTPS' : 'HTTP — استخراج از مرورگر فقط با HTTPS مجاز است');
dline('info', 'اضافه‌ها', 'pdo_mysql ' . (extension_loaded('pdo_mysql') ? '✓' : '✗') . ' · curl ' . (extension_loaded('curl') ? '✓' : '✗') . ' · gd ' . (extension_loaded('gd') ? '✓' : '✗'));

echo "\n══════════════════════════════════════════════════════════════\n";
if ($problems === []) {
    echo " نتیجه: هیچ ایرادی در این بررسی‌ها پیدا نشد.\n";
    echo " اگر ورود دامینی هنوز رد می‌شود، نام کاربری و رمز را با یک حساب\n";
    echo " شناخته‌شده امتحان کنید و لاگ را ببینید: storage/logs/app.log\n";
    exit(0);
}
echo " نتیجه: " . count($problems) . " ایراد پیدا شد:\n";
foreach ($problems as $i => $p) {
    echo '  ' . ($i + 1) . ') ' . $p . "\n";
}
exit(1);
