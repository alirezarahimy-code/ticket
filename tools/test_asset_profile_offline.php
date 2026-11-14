<?php
/**
 * تست آفلاین «شناسنامهٔ فنی» — شبیه‌سازی کامل خروجی PowerShell (collect_server_inventory.ps1)
 * و عبور آن از مسیر واقعی PHP: inventory_apply_powershell_data() → asset_profile_auto_values()
 *
 * اجرا: php tools/test_asset_profile_offline.php
 * از هیچ سرور ویندوز/WMI استفاده نمی‌کند؛ فقط تطبیق ساختار داده و پرشدن فیلدها را می‌سنجد.
 */
declare(strict_types=1);

define('APP_ROOT', __DIR__ . '/..');
chdir(APP_ROOT);

$fails = 0;
$passes = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $fails, $passes;
    if ($ok) {
        $passes++;
        echo "[PASS] {$label}\n";
        return;
    }
    $fails++;
    echo "[FAIL] {$label}" . ($detail !== '' ? " :: {$detail}" : '') . "\n";
}

// ── استاب‌های حداقلی تا فایل‌های واقعی بدون DB بارگذاری شوند ──────────────────
if (!defined('APP_ROOT')) { define('APP_ROOT', __DIR__ . '/..'); }
if (!function_exists('inventory_rows')) {
    require_once APP_ROOT . '/inventory.php';
}
if (!function_exists('asset_profile_auto_values')) {
    require_once APP_ROOT . '/asset-profile.php';
}
if (!function_exists('asset_profile_printer_kind')) {
    // asset-profile.php به آن نیاز دارد؛ اگر تعریف نشده باشد یک استاب سبک.
    function asset_profile_printer_kind(array $row): string { return 'local'; }
}

/**
 * payload نمونهٔ واقعی: همان کلیدهایی که collect_server_inventory.ps1 با
 * ConvertTo-Json -Depth 8 -Compress تولید می‌کند (شامل collection_status).
 */
function sample_powershell_payload(): array
{
    $ok = static fn (int $count): array => ['ok' => true, 'count' => $count];
    return [
        'collection_status' => [
            'root/cimv2|Win32_ComputerSystem' => $ok(1),
            'root/cimv2|Win32_BIOS' => $ok(1),
            'root/cimv2|Win32_Processor' => $ok(1),
            'root/cimv2|Win32_OperatingSystem' => $ok(1),
            'root/cimv2|Win32_BaseBoard' => $ok(1),
            'root/cimv2|Win32_PhysicalMemory' => $ok(2),
            'root/cimv2|Win32_DiskDrive' => $ok(2),
            'root/cimv2|Win32_VideoController' => $ok(1),
            'root/cimv2|Win32_SoundDevice' => $ok(1),
            'root/cimv2|Win32_NetworkAdapterConfiguration' => $ok(1),
            'root/cimv2|Win32_Printer' => $ok(1),
            'root/cimv2|Win32_PnPEntity' => $ok(4),
            'root/cimv2|Win32_DesktopMonitor' => $ok(1),
            'root/cimv2|Win32_UserProfile' => $ok(2),
            'root/wmi|WmiMonitorID' => $ok(1),
            'root/SecurityCenter2|AntiVirusProduct' => $ok(1),
            'root/cimv2|Win32_CDROMDrive' => ['ok' => false, 'count' => 0], // عمداً ناموفق
        ],
        'computer' => [[
            'Manufacturer' => 'Dell Inc.', 'Model' => 'OptiPlex 7090',
            'UserName' => 'CORP\\ali.rahimi', 'TotalPhysicalMemory' => 17179869184,
            'Domain' => 'CORP', 'Name' => 'PC-023',
        ]],
        'bios' => [[
            'SerialNumber' => 'SN-BIOS-778899', 'Manufacturer' => 'Dell Inc.',
            'SMBIOSBIOSVersion' => '2.14.0', 'ReleaseDate' => '2023-05-01T00:00:00Z',
        ]],
        'processor' => [[
            'Name' => 'Intel(R) Core(TM) i7-10700 CPU @ 2.90GHz',
            'NumberOfCores' => 8, 'NumberOfLogicalProcessors' => 16,
            'ProcessorId' => 'BFEBFBFF000A0653', 'MaxClockSpeed' => 2900,
        ]],
        'os' => [[
            'Caption' => 'Microsoft Windows 11 Pro', 'Version' => '10.0.22631',
            'BuildNumber' => '22631', 'OSArchitecture' => '64-bit',
            'SerialNumber' => '00330-80000-00000-AA123', 'WindowsDirectory' => 'C:\\WINDOWS',
            'SystemDrive' => 'C:', 'InstallDate' => '2023-02-11T00:00:00Z',
        ]],
        'motherboard' => [[
            'Manufacturer' => 'Dell Inc.', 'Product' => '0WMJ54', 'SerialNumber' => 'MB-SN-4455',
        ]],
        'memory_modules' => [
            ['DeviceLocator' => 'DIMM A1', 'Capacity' => 8589934592, 'Manufacturer' => 'SK Hynix', 'PartNumber' => 'HMA81GU6DJR8N-XN', 'Speed' => 3200, 'SerialNumber' => 'RM1'],
            ['DeviceLocator' => 'DIMM B1', 'Capacity' => 8589934592, 'Manufacturer' => 'SK Hynix', 'PartNumber' => 'HMA81GU6DJR8N-XN', 'Speed' => 3200, 'SerialNumber' => 'RM2'],
        ],
        'physical_disks' => [
            ['Index' => 0, 'Model' => 'Samsung SSD 980 PRO 1TB', 'Size' => 1000204886016, 'SerialNumber' => 'S6B0NL0R123456', 'MediaType' => 'Fixed hard disk media', 'InterfaceType' => 'SCSI'],
            ['Index' => 1, 'Model' => 'ST2000DM008-2FR102', 'Size' => 2000398934016, 'SerialNumber' => 'ZFL1ABCD', 'MediaType' => 'Fixed hard disk media', 'InterfaceType' => 'IDE'],
        ],
        'graphics' => [['Name' => 'Intel(R) UHD Graphics 630', 'AdapterRAM' => 1073741824, 'DriverVersion' => '31.0.101.2111']],
        'sound' => [['Name' => 'Realtek(R) Audio', 'Manufacturer' => 'Realtek']],
        'optical_drives' => [],
        'network_adapters' => [['Name' => 'Intel(R) Ethernet Connection I219-LM', 'MACAddress' => 'AA:BB:CC:11:22:33']],
        'network' => [[
            'Description' => 'Intel(R) Ethernet Connection I219-LM',
            'MACAddress' => 'AA:BB:CC:11:22:33',
            'IPAddress' => ['10.10.25.47', 'fe80::1'],
            'DefaultIPGateway' => ['10.10.25.1'],
            'DNSDomain' => 'corp.local',
        ]],
        'disks' => [['DeviceID' => 'C:', 'VolumeName' => 'OS', 'Size' => 999000000000, 'FreeSpace' => 400000000000, 'DriveType' => 3]],
        'printers' => [['Name' => 'HP LaserJet 400 M401 (HP)', 'DriverName' => 'HP LaserJet', 'Default' => true, 'Network' => true]],
        'scanners' => [['Name' => 'Canon DR-C230', 'Manufacturer' => 'Canon', 'PNPClass' => 'Image']],
        'monitors' => [['Name' => 'Dell P2419H', 'MonitorManufacturer' => 'Dell', 'ScreenWidth' => 1920, 'ScreenHeight' => 1080]],
        'monitors_pnp' => [['Name' => 'Dell P2419H', 'PNPClass' => 'Monitor']],
        'keyboards' => [['Name' => 'Dell KB216 Wired Keyboard', 'PNPClass' => 'Keyboard']],
        'mice' => [['Name' => 'Logitech M90 USB Optical Mouse', 'PNPClass' => 'Mouse']],
        'antivirus' => [['displayName' => 'Microsoft Defender Antivirus', 'productState' => 397568]],
        'os_users' => [
            ['LocalPath' => 'C:\\Users\\ali.rahimi', 'SID' => 'S-1-5-21-1', 'Loaded' => true],
            ['LocalPath' => 'C:\\Users\\reza.karimi', 'SID' => 'S-1-5-21-2', 'Loaded' => false],
            ['LocalPath' => 'C:\\Users\\Default', 'SID' => 'S-1-5-21-3'],
        ],
        'software' => [['name' => 'Microsoft Office LTSC 2021', 'version' => '16.0.14332']],
        'system_product' => [['Vendor' => 'Dell Inc.', 'Name' => 'OptiPlex 7090', 'UUID' => '4C4C4544-0042']],
        'enclosure' => [['Manufacturer' => 'Dell Inc.', 'SerialNumber' => 'SN-ENCL-99']],
        'memory_arrays' => [['MemoryDevices' => 4, 'MaxCapacityEx' => 137438953472]],
        'pnp_devices' => [], 'signed_drivers' => [], 'services' => [], 'processes' => [],
        'startup_items' => [], 'shares' => [], 'user_accounts' => [], 'windows_updates' => [],
        'environment_names' => [], 'firewall_products' => [], 'antispyware_products' => [],
        'firewall_profiles' => [], 'storage_devices' => [], 'storage_reliability' => [],
        'smart_status' => [],
        'monitor_ids' => [['InstanceName' => 'DISPLAY\\DEL41A6\\5&2', 'UserFriendlyName' => [68, 0, 101, 0, 108, 0, 108, 0, 32, 0, 80, 0, 50, 0, 52, 0, 49, 0, 57, 0, 72, 0], 'SerialNumberID' => [49, 0, 50, 0, 51, 0]]],
    ];
}

/* ── ۱) inventory_apply_powershell_data: تبدیل خام → فیلدهای پایه ─────────── */
$base = inventory_base_inventory();
// در مسیر واقعی دامنه، نام کامپیوتر پیش از فراخوانی ست می‌شود (domain-scan.php).
// در مسیر «همین سرور» از gethostname استفاده می‌شود؛ پس اینجا صریحاً می‌گذاریم.
$base['hostname'] = 'PC-023';
$inventory = inventory_apply_powershell_data($base, sample_powershell_payload());

$baseExpect = [
    'serial_number' => 'SN-BIOS-778899',
    'manufacturer' => 'Dell Inc.',
    'model' => 'OptiPlex 7090',
    'cpu' => 'Intel(R) Core(TM) i7-10700 CPU @ 2.90GHz',
    'operating_system' => 'Microsoft Windows 11 Pro',
    'os_architecture' => '64-bit',
    'os_serial' => '00330-80000-00000-AA123',
    'ip_address' => '10.10.25.47',
    'mac_address' => 'AA:BB:CC:11:22:33',
    'antivirus' => 'Microsoft Defender Antivirus',
];
$baseBad = [];
foreach ($baseExpect as $key => $want) {
    if ((string) ($inventory[$key] ?? '') !== $want) {
        $baseBad[] = $key . '=' . var_export($inventory[$key] ?? null, true) . ' (انتظار: ' . $want . ')';
    }
}
check('۱: فیلدهای پایهٔ assets از خروجی PowerShell درست پر می‌شوند', $baseBad === [], implode(' | ', $baseBad));
check('۲: memory_mb از TotalPhysicalMemory به مگابایت تبدیل شده است', (int) ($inventory['memory_mb'] ?? 0) === 16384, (string) ($inventory['memory_mb'] ?? 0));

/* ── ۲) ساختار hardware: باید همین کلیدها باشند که profile می‌خواند ───────── */
$hardware = (array) ($inventory['hardware'] ?? []);
$requiredHardware = ['bios', 'processor', 'operating_system', 'motherboard', 'memory_modules', 'physical_disks', 'graphics', 'sound', 'network_adapters', 'network_configurations', 'logical_disks', 'extended_inventory', 'collection_status'];
$missingHardware = array_values(array_diff($requiredHardware, array_keys($hardware)));
check('۳: ساختار hardware همهٔ کلیدهای مصرفی شناسنامه را دارد', $missingHardware === [], implode(', ', $missingHardware));
check('۴: extended_inventory همهٔ ۱۹ بخش تکمیلی را دارد',
    count((array) ($hardware['extended_inventory'] ?? [])) >= 19,
    (string) count((array) ($hardware['extended_inventory'] ?? [])));

/* ── ۳) asset_profile_auto_values: پر شدن همهٔ فیلدهای شناسنامه ───────────── */
$values = asset_profile_auto_values($inventory);
$expect = [
    'hostname' => 'PC-023',
    'os_name' => 'Microsoft Windows 11 Pro',
    'os_version' => '10.0.22631',
    'os_install_path' => 'C:\\WINDOWS',
    'user_count' => '2',
    'disk1_model' => 'Samsung SSD 980 PRO 1TB',
    'disk1_capacity' => '931.5 GB',
    'disk1_serial' => 'S6B0NL0R123456',
    'disk2_model' => 'ST2000DM008-2FR102',
    'motherboard_model' => 'Dell Inc.',
    'motherboard_product' => '0WMJ54',
    'motherboard_serial' => 'MB-SN-4455',
    'cpu_model' => 'Intel(R) Core(TM) i7-10700 CPU @ 2.90GHz',
    'cpu_serial' => 'BFEBFBFF000A0653',
    'cpu_cores' => '8',
    'ram_total' => '16384 MB',
    'gpu1_model' => 'Intel(R) UHD Graphics 630',
    'audio' => 'Realtek(R) Audio',
    'ip_address' => '10.10.25.47',
    'mac_address' => 'AA:BB:CC:11:22:33',
    'netcard' => 'Intel(R) Ethernet Connection I219-LM',
    'monitor_model' => 'Dell P2419H',
    'scanner_model' => 'Canon DR-C230',
    'keyboard_model' => 'Dell KB216 Wired Keyboard',
    'mouse_model' => 'Logitech M90 USB Optical Mouse',
    'antivirus' => 'Microsoft Defender Antivirus',
];
$bad = [];
foreach ($expect as $key => $want) {
    $got = (string) ($values[$key] ?? '');
    if ($got !== $want) {
        $bad[] = $key . '=' . var_export($got, true) . ' (انتظار: ' . var_export($want, true) . ')';
    }
}
check('۵: فیلدهای شناسنامهٔ فنی از دادهٔ WMI پر می‌شوند', $bad === [], implode("\n         ", $bad));

$ram1 = (string) ($values['ram1'] ?? '');
check('۶: ram1 و ram2 از ماژول‌های حافظه ساخته می‌شوند', str_contains($ram1, 'SK Hynix') && str_contains($ram1, '8 GB') && ($values['ram2'] ?? '') !== '', $ram1 . ' / ' . (string) ($values['ram2'] ?? ''));

$printer = (string) ($values['printer_model'] ?? '');
check('۷: پرینتر با نوع (شبکه/فیزیکی) مشخص می‌شود', str_contains($printer, 'HP LaserJet 400 M401'), $printer);

/* ── ۴) پایداری: کوئری ناموفق نباید مقدار دستی را پاک کند ─────────────────── */
$partial = sample_powershell_payload();
$partial['collection_status']['root/cimv2|Win32_VideoController'] = ['ok' => false, 'count' => 0];
$partial['graphics'] = [];
$inventoryPartial = inventory_apply_powershell_data(inventory_base_inventory(), $partial);
$valuesPartial = asset_profile_auto_values($inventoryPartial);
check('۸: کوئری ناموفق (GPU) فیلد را null می‌کند نه پاک‌کردن اجباری',
    array_key_exists('gpu1_model', $valuesPartial) && ($valuesPartial['gpu1_model'] === null || $valuesPartial['gpu1_model'] === ''),
    var_export($valuesPartial['gpu1_model'] ?? 'MISSING', true));
check('۹: کوئری ناموفق در فهرست clear_fields قرار نمی‌گیرد (مقدار قبلی حفظ می‌شود)',
    !in_array('gpu1_model', (array) ($valuesPartial['_clear_fields'] ?? []), true),
    implode(',', (array) ($valuesPartial['_clear_fields'] ?? [])));

/* ── ۵) کوئری موفقِ خالی باید clear_fields را پر کند ─────────────────────── */
$empty = sample_powershell_payload();
$empty['collection_status']['root/cimv2|Win32_PhysicalMemory'] = ['ok' => true, 'count' => 0];
$empty['memory_modules'] = [];
$valuesEmpty = asset_profile_auto_values(inventory_apply_powershell_data(inventory_base_inventory(), $empty));
check('۱۰: کوئری موفقِ خالی، فیلدهای RAM را در clear_fields می‌گذارد',
    in_array('ram1', (array) ($valuesEmpty['_clear_fields'] ?? []), true), implode(',', (array) ($valuesEmpty['_clear_fields'] ?? [])));

/* ── ۶) هر فیلد شناسنامه باید provenance داشته باشد ──────────────────────── */
$provenance = (array) ($values['_field_provenance'] ?? []);
$covered = [];
foreach ($provenance as $field => $entries) {
    if (in_array($field, (array) ($values['_clear_fields'] ?? []), true) || ($values[$field] ?? '') !== '') {
        $covered[$field] = true;
    }
}
$mappedFields = 0;
foreach ($provenance as $field => $entries) { foreach ($entries as $e) { if (($e['state'] ?? '') === 'success') { $mappedFields++; break; } } }
check('۱۱: provenance برای فیلدهای کلیدی، وضعیت success دارد',
    $mappedFields >= 20 && isset($provenance['cpu_model'], $provenance['os_name'], $provenance['disk1_model']),
    'mapped=' . $mappedFields);

/* ── ۷) خروجی PowerShell واقعی: کلیدها با ConvertTo-Json هم‌خوان باشند ────── */
$psSource = (string) file_get_contents(APP_ROOT . '/tools/collect_server_inventory.ps1');
preg_match_all('/^\s{8}([a-z_]+) = @\(/m', $psSource, $emitted);
$emittedKeys = array_values(array_unique($emitted[1] ?? []));
$consumedByPhp = ['computer', 'bios', 'processor', 'os', 'motherboard', 'memory_modules', 'physical_disks', 'graphics', 'sound', 'optical_drives', 'network_adapters', 'modems', 'os_users', 'disks', 'network', 'printers', 'scanners', 'monitors', 'monitors_pnp', 'keyboards', 'mice', 'antivirus', 'software', 'system_product', 'enclosure', 'memory_arrays', 'pnp_devices', 'signed_drivers', 'services', 'processes', 'startup_items', 'shares', 'user_accounts', 'windows_updates', 'environment_names', 'firewall_products', 'antispyware_products', 'firewall_profiles', 'storage_devices', 'storage_reliability', 'smart_status', 'monitor_ids'];
$notConsumed = array_values(array_diff($emittedKeys, $consumedByPhp));
check('۱۲: همهٔ کلیدهای خروجی PowerShell در PHP مصرف می‌شوند (بدون دادهٔ دورریز)',
    $notConsumed === [], implode(', ', $notConsumed));
check('۱۳: هر کوئری‌ای که شناسنامه از آن فیلد می‌سازد، در collection_status ثبت می‌شود',
    count(array_filter(array_keys((array) sample_powershell_payload()['collection_status']))) >= 15);

/* ── ۸) رگرسیون: استخراج دامنه هم همین مسیر را طی کند ────────────────────── */
$domainSource = (string) file_get_contents(APP_ROOT . '/domain-scan.php');
check('۱۴: مسیر استخراج دامنه از همان inventory_apply_powershell_data استفاده می‌کند',
    str_contains($domainSource, 'inventory_apply_powershell_data')
    && str_contains($domainSource, 'asset_profile_apply_auto'));
check('۱۵: استخراج دامنه collection_status را از اسکریپت PowerShell نگه می‌دارد',
    str_contains($psSource, 'collection_status = $script:InventoryCollectorStatus'));

/* ── ۹) نشت شناسه‌های سرور پنل به شناسنامهٔ سیستم مقصد ────────────────────── */
$leakInventory = inventory_apply_powershell_data(inventory_base_inventory(), sample_powershell_payload());
$panelLeaks = array_intersect(['php_version', 'php_sapi', 'server_software', 'os_release'], array_keys((array) ($leakInventory['hardware'] ?? [])));
check('۱۶: شناسه‌های سرور پنل به hardware سیستم مقصد نشت نمی‌کنند', $panelLeaks === [], implode(', ', $panelLeaks));

/* ── ۱۰) پایداری کامل: payloadهای غیرمنتظره نباید خطا بدهند ───────────────── */
$weird = [
    'computer' => 'not-an-array',
    'bios' => ['SerialNumber' => 'X'],
    'collection_status' => 'garbage',
    'os' => [null, 'string', ['Caption' => 'Win10']],
];
$weirdOk = true;
try {
    asset_profile_auto_values(inventory_apply_powershell_data(inventory_base_inventory(), $weird));
} catch (Throwable $e) {
    $weirdOk = false;
    $weirdDetail = $e->getMessage();
}
check('۱۷: داده‌های ناقص/غیرمنتظره در شناسنامه باعث خطا نمی‌شوند', $weirdOk, $weirdDetail ?? '');

/* ── ۱۱) همهٔ فیلدهای «خودکار» واقعاً از یک منبع WMI پر می‌شوند ───────────── */
$autoFields = [];
$profileSource = (string) file_get_contents(APP_ROOT . '/asset-profile.php');
$autoStart = strpos($profileSource, 'function asset_profile_auto_values');
$autoBody = substr($profileSource, $autoStart, strpos($profileSource, "\nfunction ", $autoStart + 10) - $autoStart);
preg_match_all("/^\s{8}'([a-z0-9_]+)' =>/m", $autoBody, $autoMatches);
$autoFields = array_values(array_unique($autoMatches[1] ?? []));
$emptyAuto = [];
foreach ($autoFields as $field) {
    if (($values[$field] ?? null) === '' || ($values[$field] === null && !in_array($field, (array) ($values['_clear_fields'] ?? []), true))) {
        $emptyAuto[] = $field;
    }
}
// فیلدهایی که با این payload نمونه طبعاً خالی می‌مانند (سخت‌افزار موجود نیست)
$expectedEmpty = ['barcode_reader', 'gpu2_model', 'gpu2_memory', 'ad_os', 'ad_os_version', 'ad_last_logon', 'ad_last_seen', 'ad_scanned_at', 'ad_online', 'ad_description', 'ad_dn'];
$unexpectedEmpty = array_values(array_diff($emptyAuto, $expectedEmpty));
check('۱۸: همهٔ فیلدهای خودکارِ قابل‌استخراج با payload نمونه پر می‌شوند',
    $unexpectedEmpty === [], 'خالی ماند: ' . implode(', ', $unexpectedEmpty) . ' | خالی‌های موردانتظار: ' . implode(', ', array_values(array_intersect($emptyAuto, $expectedEmpty))));

echo "\n" . ($fails === 0
    ? "همهٔ " . ($passes + $fails) . " بررسی شناسنامهٔ فنی موفق بود."
    : ($fails . ' بررسی ناموفق از ' . ($passes + $fails))) . "\n";
exit($fails === 0 ? 0 : 1);
