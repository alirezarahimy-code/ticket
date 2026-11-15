<?php
/**
 * آزمون آفلاین ۱.۶۳.۴ — استخراج شناسنامه.
 *
 * سه ایراد واقعی که کاربر پرسیده بود «استخراج درست انجام می‌شود یا خطا دارد؟»:
 *
 *   ۱) اگر نام کامپیوترِ برگشتی از اسکن قبلاً روی رکورد دیگری ثبت شده بود،
 *      UPDATE با «Duplicate entry … uq_assets_hostname_key» شکست می‌خورد و
 *      **کل** استخراج — با همهٔ فیلدهای سالم — از بین می‌رفت.
 *   ۲) همان UPDATE نام کامپیوتر را هم بازنویسی می‌کرد؛ یعنی فهرست/شناسنامه
 *      بی‌صدا هویت عوض می‌کرد.
 *   ۳) سریال و مدل خودِ دستگاه اصلاً فیلدی در شناسنامه نداشتند؛ فقط سریال
 *      مادربرد/هارد/ویندوز بود. هرچه استخراج می‌خواند، در فرم دیده نمی‌شد.
 *
 * این آزمون هر سه را به‌علاوهٔ صحت نگاشت دادهٔ PowerShell می‌سنجد.
 */

$root = dirname(__DIR__);
$scan = file_get_contents($root . '/domain-scan.php');
$profile = file_get_contents($root . '/asset-profile.php');
$inventory = file_get_contents($root . '/inventory.php');
$names = file_get_contents($root . '/asset-names.php');

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

echo "═══ ۱) نام کامپیوتر در استخراج بازنویسی نمی‌شود ═══\n";
check('$writeName ساخته می‌شود', strpos($scan, '$writeName = $currentName') !== false);
check('UPDATE با $writeName انجام می‌شود', (bool) preg_match('/UPDATE assets SET hostname = \?.*?\$writeName/s', $scan));
check('نام مشاهده‌شده در hardware ثبت می‌شود', strpos($scan, "'observed_hostname'") !== false);
check('پرچم ناسازگاری ثبت می‌شود', strpos($scan, "'hostname_mismatch'") !== false);

echo "\n═══ ۲) برخورد کلید یکتا کل استخراج را از بین نمی‌برد ═══\n";
check('UPDATE در try/catch پیچیده شده', (bool) preg_match('/try \{\s*db\(\)->prepare\(\'UPDATE assets SET hostname/s', $scan));
check('کد 23000 تشخیص داده می‌شود', strpos($scan, "=== '23000'") !== false || strpos($scan, "== '23000'") !== false);
check('فیلدها بدون نام دوباره نوشته می‌شوند', strpos($scan, 'UPDATE assets SET serial_number = ?') !== false);
check('رخداد ثبت می‌شود', strpos($scan, 'hostname collision on asset') !== false);
check('asset_sync_identity_key هم مقاوم شده', (bool) preg_match('/function asset_sync_identity_key.*?catch \(PDOException/s', $names));

echo "\n═══ ۳) سریال و مدل دستگاه در شناسنامه هست ═══\n";
check('فیلد machine_serial تعریف شده', strpos($profile, "'key' => 'machine_serial'") !== false);
check('فیلد machine_model تعریف شده', strpos($profile, "'key' => 'machine_model'") !== false);
check('machine_serial از inventory خوانده می‌شود', (bool) preg_match("/'machine_serial' => \(string\) \(\\\$inventory\['serial_number'\]/", $profile));
check('machine_model از manufacturer + model ساخته می‌شود', (bool) preg_match("/'machine_model' => trim\(\(string\) \(\\\$inventory\['manufacturer'\].*?\\\$inventory\['model'\]/s", $profile));
check('fallback از جدول assets هم هست', strpos($profile, "'machine_serial' => trim((string) (\$asset['serial_number']") !== false);

echo "\n═══ ۴) نگاشت دادهٔ واقعی PowerShell ═══\n";
// بارگذاری اختیاری: اگر این آزمون خارج از درخت کامل (مثلاً در یک بستهٔ
// جداشده) اجرا شود، بخش‌های ۴ و ۵ رد می‌شوند نه اینکه خطا بدهند.
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $root);
}
$inventoryLoadable = is_file($root . '/lib/db-core.php') && is_file($root . '/inventory.php') && is_file($root . '/config.php');
if ($inventoryLoadable) {
    try {
        require_once $root . '/lib/db-core.php';
        require_once $root . '/inventory.php';
    } catch (Throwable $loadError) {
        $inventoryLoadable = false;
    }
    $inventoryLoadable = $inventoryLoadable && function_exists('inventory_apply_powershell_data');
}
if (!$inventoryLoadable) {
    echo "\n  ⚠ درخت کامل نصب در دسترس نیست؛ بخش‌های ۴ و ۵ (نگاشت دادهٔ زنده) رد شد.\n";
}

// خروجی نمونه با همان ساختاری که tools/collect_server_inventory.ps1 می‌سازد.
$payload = [
    'computer' => [['Manufacturer' => 'HP', 'Model' => 'EliteDesk 800 G6', 'TotalPhysicalMemory' => 17179869184, 'UserName' => 'MBA4\\m.ahmadi']],
    'bios' => [['SerialNumber' => 'CZC1234ABC']],
    'processor' => [['Name' => 'Intel(R) Core(TM) i7-10700 CPU @ 2.90GHz']],
    'os' => [['Caption' => 'Microsoft Windows 11 Pro', 'OSArchitecture' => '64-bit', 'SerialNumber' => '00330-80000']],
    'network' => [
        ['Description' => 'Hyper-V Virtual', 'MACAddress' => '00:15:5D:00:00:01', 'IPAddress' => ['192.168.99.1']],
        ['Description' => 'Intel I219-V', 'MACAddress' => 'A4:BB:6D:12:34:56', 'IPAddress' => ['192.168.1.123'], 'DefaultIPGateway' => ['192.168.1.1']],
    ],
    'antivirus' => [['displayName' => 'Windows Defender']],
    'disks' => [['DriveLetter' => 'C:', 'Model' => 'Samsung SSD', 'SizeGB' => 512]],
    'software' => [['Name' => 'Microsoft Office', 'Version' => '2021']],
    'printers' => [['Name' => 'HP LaserJet M404']],
    'scanners' => [],
    'monitors' => [['Name' => 'Dell P2422H']],
    'keyboards' => [],
    'mice' => [],
];

if (function_exists('inventory_apply_powershell_data') && function_exists('inventory_base_inventory')) {
    $inv = inventory_base_inventory();
    foreach (['serial_number', 'manufacturer', 'model', 'operating_system', 'os_architecture', 'os_serial', 'mac_address', 'cpu', 'domain_username', 'antivirus'] as $k) {
        $inv[$k] = '';
    }
    $inv['memory_mb'] = 0;
    $inv['disks'] = [];
    $inv['software'] = [];
    $inv['hardware'] = [];
    $inv['peripherals'] = ['printers' => [], 'scanners' => [], 'monitors' => [], 'keyboards' => [], 'mice' => []];
    $inv['hostname'] = 'MBA4-23';
    $inv = inventory_apply_powershell_data($inv, $payload);

    check('سریال BIOS', ($inv['serial_number'] ?? '') === 'CZC1234ABC', var_export($inv['serial_number'] ?? null, true));
    check('سازنده', ($inv['manufacturer'] ?? '') === 'HP');
    check('مدل', ($inv['model'] ?? '') === 'EliteDesk 800 G6');
    check('سیستم‌عامل', ($inv['operating_system'] ?? '') === 'Microsoft Windows 11 Pro');
    check('معماری', ($inv['os_architecture'] ?? '') === '64-bit');
    check('شناسه ویندوز', ($inv['os_serial'] ?? '') === '00330-80000');
    check('پردازنده', strpos((string) ($inv['cpu'] ?? ''), 'i7-10700') !== false);
    check('حافظه ۱۶ گیگ', (int) ($inv['memory_mb'] ?? 0) === 16384, (string) ($inv['memory_mb'] ?? 0));
    check('MAC کارت فیزیکی، نه Hyper-V', ($inv['mac_address'] ?? '') === 'A4:BB:6D:12:34:56', (string) ($inv['mac_address'] ?? ''));
    check('IP کارت فیزیکی', ($inv['ip_address'] ?? '') === '192.168.1.123', (string) ($inv['ip_address'] ?? ''));
    check('آنتی‌ویروس', ($inv['antivirus'] ?? '') === 'Windows Defender');
    check('هارد', count($inv['disks'] ?? []) === 1);
    check('نرم‌افزار', count($inv['software'] ?? []) === 1);
    check('پرینتر', count($inv['peripherals']['printers'] ?? []) === 1);
    check('مانیتور', count($inv['peripherals']['monitors'] ?? []) === 1);
    check('امتیاز سخت‌افزاری > ۰ (پذیرش استخراج)', inventory_hardware_score($inv) > 0, (string) inventory_hardware_score($inv));
} else {
    check('توابع inventory قابل بارگذاری‌اند', false, 'inventory.php بارگذاری نشد');
}

echo "\n═══ ۵) پاسخ خالی به‌درستی رد می‌شود ═══\n";
if (function_exists('inventory_hardware_score') && function_exists('inventory_base_inventory')) {
    check('امتیاز پاسخ خالی صفر است', inventory_hardware_score(inventory_base_inventory()) === 0);
}

echo "\n" . str_repeat('─', 60) . "\n";
echo "نتیجه: {$ok} موفق، {$fail} ناموفق\n";
exit($fail === 0 ? 0 : 1);
