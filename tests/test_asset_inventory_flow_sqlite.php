<?php
declare(strict_types=1);

/**
 * SQLite-backed integration of the production asset upsert/store functions.
 *
 * Run: php tests/test_asset_inventory_flow_sqlite.php
 * This executes real PHP and real SQL against an isolated in-memory database;
 * it does NOT impersonate an Active Directory, Windows host, WMI/DCOM, WinRM,
 * SMB, or the customer's MySQL server.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}
if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "SKIP: pdo_sqlite is required for this integration test.\n");
    exit(77);
}

define('APP_ROOT', dirname(__DIR__));
$GLOBALS['asset_test_db'] = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$GLOBALS['asset_test_db']->sqliteCreateFunction('NOW', static fn (): string => date('Y-m-d H:i:s'));
$GLOBALS['asset_profile_test_writes'] = [];

function db(): PDO { return $GLOBALS['asset_test_db']; }
function setting(string $key, mixed $default = null): mixed { return $default; }
function app_error_log(string $message): void { $GLOBALS['asset_test_errors'][] = $message; }
function system_log(mixed ...$args): void {}
function asset_profile_auto_values(array $inventory): array
{
    $hardware = is_array($inventory['hardware'] ?? null) ? $inventory['hardware'] : [];
    $disk = inventory_rows($hardware['physical_disks'] ?? [])[0] ?? [];
    return [
        'hostname' => (string) ($inventory['reported_hostname'] ?? $inventory['hostname'] ?? ''),
        'machine_serial' => (string) ($inventory['serial_number'] ?? ''),
        'cpu_model' => (string) ($inventory['cpu'] ?? ''),
        'disk1_model' => (string) inventory_value(is_array($disk) ? $disk : [], ['Model', 'model'], ''),
    ];
}
function asset_profile_apply_auto(int $assetId, array $values, int $userId, bool $overwrite = false): int
{
    $GLOBALS['asset_profile_test_writes'][$assetId] = ['values' => $values, 'user_id' => $userId, 'overwrite' => $overwrite];
    return count($values);
}

require_once APP_ROOT . '/inventory.php';
require_once APP_ROOT . '/domain-scan.php';

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
function source_insert_sql(string $path): string
{
    $source = (string) file_get_contents($path);
    $marker = "prepare('INSERT INTO assets ";
    $start = strpos($source, $marker);
    if ($start === false) { return ''; }
    $start += strlen("prepare('");
    $end = strpos($source, "')->execute(inventory_asset_insert_params(\$assetTag, \$values))", $start);
    return $end === false ? '' : substr($source, $start, $end - $start);
}
function make_asset_inventory(
    string $hostname,
    string $serial,
    string $mac,
    string $ip,
    string $cpu,
    int $memoryBytes,
    string $memorySerial,
    string $diskModel,
    string $diskSerial,
    int $diskBytes,
    bool $memoryQueryOk = true
): array {
    $status = [
        'root/cimv2|Win32_ComputerSystem' => ['ok' => true, 'count' => 1],
        'root/cimv2|Win32_BIOS' => ['ok' => true, 'count' => 1],
        'root/cimv2|Win32_Processor' => ['ok' => true, 'count' => 1],
        'root/cimv2|Win32_OperatingSystem' => ['ok' => true, 'count' => 1],
        'root/cimv2|Win32_BaseBoard' => ['ok' => true, 'count' => 1],
        'root/cimv2|Win32_PhysicalMemory' => ['ok' => $memoryQueryOk, 'count' => $memoryQueryOk ? 1 : 0],
        'root/cimv2|Win32_DiskDrive' => ['ok' => true, 'count' => 1],
        'root/cimv2|Win32_VideoController' => ['ok' => true, 'count' => 1],
        'root/cimv2|Win32_NetworkAdapterConfiguration' => ['ok' => true, 'count' => 1],
        'root/cimv2|Win32_Printer' => ['ok' => true, 'count' => 0],
    ];
    $base = inventory_base_inventory();
    // Match the neutral target object assembled by domain_scan_remote_inventory_many().
    $base['hostname'] = $hostname;
    $base['serial_number'] = '';
    $base['manufacturer'] = '';
    $base['model'] = '';
    $base['computer_type'] = 'Windows client';
    $base['operating_system'] = '';
    $base['os_architecture'] = '';
    $base['os_serial'] = '';
    $base['ip_address'] = '';
    $base['mac_address'] = '';
    $base['cpu'] = '';
    $base['memory_mb'] = 0;
    $base['domain_username'] = '';
    $base['antivirus'] = '';
    $base['disks'] = [];
    $base['software'] = [];
    $base['peripherals'] = ['printers' => [], 'scanners' => [], 'monitors' => [], 'keyboards' => [], 'mice' => []];
    $base['hardware'] = [];
    $payload = [
        'collection_status' => $status,
        'computer' => [[
            'Name' => explode('.', $hostname)[0],
            'Manufacturer' => 'Example Systems',
            'Model' => 'Workstation 900',
            'UserName' => 'EXAMPLE\\test.user',
            'TotalPhysicalMemory' => $memoryBytes,
            'Domain' => 'EXAMPLE.LOCAL',
        ]],
        'bios' => [['SerialNumber' => $serial]],
        'processor' => [[
            'Name' => $cpu,
            'NumberOfCores' => 8,
            'NumberOfLogicalProcessors' => 16,
            'ProcessorId' => 'CPU-ID-001',
        ]],
        'os' => [[
            'Caption' => 'Microsoft Windows 11 Pro',
            'Version' => '10.0.22631',
            'OSArchitecture' => '64-bit',
            'SerialNumber' => 'WINDOWS-SERIAL-001',
            'WindowsDirectory' => 'C:\\WINDOWS',
        ]],
        'motherboard' => [['Manufacturer' => 'Example Systems', 'Product' => 'BOARD-1', 'SerialNumber' => 'BOARD-SERIAL-1']],
        'memory_modules' => $memoryQueryOk ? [[
            'Manufacturer' => 'Memory Co',
            'PartNumber' => 'DDR5-16GB',
            'Capacity' => $memoryBytes,
            'Speed' => 4800,
            'SerialNumber' => $memorySerial,
        ]] : [],
        'physical_disks' => [[
            'Index' => 0,
            'Model' => $diskModel,
            'Size' => $diskBytes,
            'SerialNumber' => $diskSerial,
            'MediaType' => 'Fixed hard disk media',
            'InterfaceType' => 'NVMe',
        ]],
        'graphics' => [['Name' => 'Example GPU', 'AdapterRAM' => 2147483648, 'DriverVersion' => '1.0']],
        'sound' => [],
        'network_adapters' => [['Name' => 'Example Ethernet', 'MACAddress' => $mac]],
        'network' => [[
            'Description' => 'Example Ethernet',
            'MACAddress' => $mac,
            'IPAddress' => [$ip],
            'DefaultIPGateway' => ['10.20.0.1'],
        ]],
        'disks' => [['DeviceID' => 'C:', 'VolumeName' => 'Windows', 'Size' => $diskBytes, 'FreeSpace' => (int) ($diskBytes / 2), 'DriveType' => 3]],
        'printers' => [], 'scanners' => [], 'monitors' => [], 'monitors_pnp' => [],
        'keyboards' => [], 'mice' => [], 'antivirus' => [['displayName' => 'Microsoft Defender']],
        'software' => [['name' => 'Example App', 'version' => '1.0']],
        'os_users' => [], 'system_product' => [], 'enclosure' => [], 'memory_arrays' => [],
        'pnp_devices' => [], 'signed_drivers' => [], 'services' => [], 'processes' => [],
        'startup_items' => [], 'shares' => [], 'user_accounts' => [], 'windows_updates' => [],
        'environment_names' => [], 'firewall_products' => [], 'antispyware_products' => [],
        'firewall_profiles' => [], 'storage_devices' => [], 'storage_reliability' => [],
        'smart_status' => [], 'monitor_ids' => [],
    ];
    return inventory_apply_powershell_data($base, $payload);
}

function create_sqlite_schema(): void
{
    $pdo = db();
    $pdo->exec(<<<'SQL'
        CREATE TABLE assets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            asset_tag TEXT NOT NULL UNIQUE,
            record_number TEXT NULL,
            hostname TEXT NULL,
            hostname_key TEXT NULL UNIQUE,
            serial_number TEXT NULL,
            computer_type TEXT NULL,
            manufacturer TEXT NULL,
            model TEXT NULL,
            owner_user_id INTEGER NULL,
            department_id INTEGER NULL,
            plaque_number TEXT NULL,
            lifecycle_status TEXT NOT NULL DEFAULT 'in_stock',
            retired_at TEXT NULL,
            operating_system TEXT NULL,
            os_architecture TEXT NULL,
            os_serial TEXT NULL,
            ip_address TEXT NULL,
            mac_address TEXT NULL,
            cpu TEXT NULL,
            memory_mb INTEGER NULL,
            disks_json TEXT NULL,
            software_json TEXT NULL,
            hardware_json TEXT NULL,
            peripherals_json TEXT NULL,
            domain_username TEXT NULL,
            antivirus TEXT NULL,
            source TEXT NOT NULL DEFAULT 'manual',
            last_inventory_at TEXT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NULL
        )
    SQL);
    $pdo->exec('CREATE TABLE asset_inventory_history (id INTEGER PRIMARY KEY AUTOINCREMENT, asset_id INTEGER NOT NULL, snapshot_json TEXT NOT NULL, collected_by INTEGER NULL, collected_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    $pdo->exec('CREATE TABLE asset_memory_modules (id INTEGER PRIMARY KEY AUTOINCREMENT, asset_id INTEGER NOT NULL, slot_no INTEGER NOT NULL, manufacturer TEXT, model TEXT, capacity_mb INTEGER, speed_mhz INTEGER, serial_number TEXT, collected_at TEXT)');
    $pdo->exec('CREATE TABLE asset_storage_devices (id INTEGER PRIMARY KEY AUTOINCREMENT, asset_id INTEGER NOT NULL, slot_no INTEGER NOT NULL, device_type TEXT, model TEXT, capacity_bytes INTEGER, serial_number TEXT, media_type TEXT, interface_type TEXT, collected_at TEXT)');
    $pdo->exec('CREATE TABLE asset_graphics_adapters (id INTEGER PRIMARY KEY AUTOINCREMENT, asset_id INTEGER NOT NULL, slot_no INTEGER NOT NULL, model TEXT, vram_mb INTEGER, driver_version TEXT, collected_at TEXT)');
    $pdo->exec('CREATE TABLE asset_peripherals (id INTEGER PRIMARY KEY AUTOINCREMENT, asset_id INTEGER NOT NULL, peripheral_type TEXT, slot_no INTEGER, model TEXT, manufacturer TEXT, serial_number TEXT, details_json TEXT, source TEXT, collected_at TEXT)');
}

create_sqlite_schema();

// 1) Real domain-stub -> production PowerShell mapper -> production DB store.
echo "=== جریان domain stub → mapping → store ===\n";
$hostname = 'PC-023.CORP.LOCAL';
$assetId = domain_scan_asset_for($hostname);
check('اسکن AD رکورد اولیه می‌سازد و hostname_key را پر می‌کند', $assetId > 0 && (int) db()->query('SELECT COUNT(*) FROM assets')->fetchColumn() === 1);
db()->prepare('UPDATE assets SET owner_user_id = ?, department_id = ?, plaque_number = ? WHERE id = ?')->execute([7, 9, 'PL-023', $assetId]);

$firstInventory = make_asset_inventory($hostname, 'MACHINE-SERIAL-023', 'AA:BB:CC:00:00:23', '10.20.0.23', 'CPU Model A', 17179869184, 'RAM-SERIAL-A', 'SSD Model A', 'DISK-SERIAL-A', 512000000000);
check('نمونهٔ PowerShell واقعی‌نما به موجودی سخت‌افزاری قابل‌استفاده map می‌شود', inventory_hardware_score($firstInventory) >= 5);
$firstFilled = domain_scan_store_inventory($assetId, $hostname, $firstInventory, 17);
$row = db()->query('SELECT * FROM assets WHERE id = ' . (int) $assetId)->fetch();
check('ذخیرهٔ دامنه نام واقعی PC-023 را می‌نویسد، نه FQDN/IP', ($row['hostname'] ?? '') === 'PC-023' && ($row['ip_address'] ?? '') === '10.20.0.23');
check('سریال/MAC/CPU/RAM/منبع از هدف ذخیره می‌شوند',
    ($row['serial_number'] ?? '') === 'MACHINE-SERIAL-023'
    && ($row['mac_address'] ?? '') === 'AA:BB:CC:00:00:23'
    && ($row['cpu'] ?? '') === 'CPU Model A'
    && (int) ($row['memory_mb'] ?? 0) === 16384
    && ($row['source'] ?? '') === 'domain');
check('قطعات جداگانه و تاریخچهٔ اولین برداشت ثبت می‌شوند',
    (int) db()->query('SELECT COUNT(*) FROM asset_memory_modules WHERE asset_id = ' . (int) $assetId)->fetchColumn() === 1
    && (int) db()->query('SELECT COUNT(*) FROM asset_storage_devices WHERE asset_id = ' . (int) $assetId)->fetchColumn() === 1
    && (int) db()->query('SELECT COUNT(*) FROM asset_inventory_history WHERE asset_id = ' . (int) $assetId)->fetchColumn() === 1);
check('فیلدهای دستی/اداری دست‌نخورده‌اند',
    (int) ($row['owner_user_id'] ?? 0) === 7 && (int) ($row['department_id'] ?? 0) === 9 && ($row['plaque_number'] ?? '') === 'PL-023');
check('مسیر خودکار شناسنامه فیلدهای ماشین را دریافت می‌کند',
    ($GLOBALS['asset_profile_test_writes'][$assetId]['values']['cpu_model'] ?? '') === 'CPU Model A'
    && ($GLOBALS['asset_profile_test_writes'][$assetId]['overwrite'] ?? false) === true
    && $firstFilled > 0);

// 2) Replace RAM + disk and change network address; same machine serial means same row.
echo "=== برداشت دوم: تعویض قطعه و تغییر IP ===\n";
$secondInventory = make_asset_inventory($hostname, 'MACHINE-SERIAL-023', 'AA:BB:CC:00:00:24', '10.20.0.24', 'CPU Model A', 34359738368, 'RAM-SERIAL-B', 'NVMe Model B', 'DISK-SERIAL-B', 1000204886016);
$secondFilled = domain_scan_store_inventory($assetId, $hostname, $secondInventory, 17);
$row2 = db()->query('SELECT * FROM assets WHERE id = ' . (int) $assetId)->fetch();
$memoryRow = db()->query('SELECT * FROM asset_memory_modules WHERE asset_id = ' . (int) $assetId)->fetch();
$diskRow = db()->query('SELECT * FROM asset_storage_devices WHERE asset_id = ' . (int) $assetId)->fetch();
check('برداشت دوم همان id را به‌روز می‌کند؛ ردیف تکراری نمی‌سازد',
    (int) db()->query('SELECT COUNT(*) FROM assets')->fetchColumn() === 1
    && (int) ($row2['id'] ?? 0) === $assetId
    && (int) db()->query('SELECT COUNT(*) FROM asset_inventory_history WHERE asset_id = ' . (int) $assetId)->fetchColumn() === 2);
check('IP/MAC و ظرفیت RAM تغییرکرده روی همان شناسنامه ثبت می‌شود',
    ($row2['ip_address'] ?? '') === '10.20.0.24'
    && ($row2['mac_address'] ?? '') === 'AA:BB:CC:00:00:24'
    && (int) ($row2['memory_mb'] ?? 0) === 32768);
check('فقط مقادیر ماشینِ تازه جای قبلی را می‌گیرند؛ پلاک/مالک/واحد حفظ می‌شوند',
    ($diskRow['model'] ?? '') === 'NVMe Model B'
    && ($diskRow['serial_number'] ?? '') === 'DISK-SERIAL-B'
    && (int) ($memoryRow['capacity_mb'] ?? 0) === 32768
    && ($row2['plaque_number'] ?? '') === 'PL-023'
    && (int) ($row2['owner_user_id'] ?? 0) === 7
    && (int) ($row2['department_id'] ?? 0) === 9
    && $secondFilled > 0);

// Change one piece only: disk details must be carried forward unchanged.
echo "=== برداشت سوم: فقط RAM تغییر می‌کند ===\n";
$ramOnlyInventory = make_asset_inventory($hostname, 'MACHINE-SERIAL-023', 'AA:BB:CC:00:00:24', '10.20.0.24', 'CPU Model A', 68719476736, 'RAM-SERIAL-C', 'NVMe Model B', 'DISK-SERIAL-B', 1000204886016);
domain_scan_store_inventory($assetId, $hostname, $ramOnlyInventory, 17);
$ramOnlyMemory = db()->query('SELECT * FROM asset_memory_modules WHERE asset_id = ' . (int) $assetId)->fetch();
$ramOnlyDisk = db()->query('SELECT * FROM asset_storage_devices WHERE asset_id = ' . (int) $assetId)->fetch();
check('وقتی فقط RAM عوض شود، ظرفیت/سریال RAM تازه و دیسک بدون تغییر می‌ماند',
    (int) ($ramOnlyMemory['capacity_mb'] ?? 0) === 65536
    && ($ramOnlyMemory['serial_number'] ?? '') === 'RAM-SERIAL-C'
    && ($ramOnlyDisk['model'] ?? '') === 'NVMe Model B'
    && ($ramOnlyDisk['serial_number'] ?? '') === 'DISK-SERIAL-B');

// Failed RAM query must preserve its last-known child rows rather than erasing them.
$partialInventory = make_asset_inventory($hostname, 'MACHINE-SERIAL-023', 'AA:BB:CC:00:00:24', '10.20.0.24', 'CPU Model A', 68719476736, '', 'NVMe Model B', 'DISK-SERIAL-B', 1000204886016, false);
domain_scan_store_inventory($assetId, $hostname, $partialInventory, 17);
$memoryAfterFailure = db()->query('SELECT * FROM asset_memory_modules WHERE asset_id = ' . (int) $assetId)->fetch();
check('ردشدن کوئری RAM مقدار قبلی را پاک نمی‌کند',
    ($memoryAfterFailure['serial_number'] ?? '') === 'RAM-SERIAL-C'
    && (int) ($memoryAfterFailure['capacity_mb'] ?? 0) === 65536);

// 3) Machine-identity lookup: serial first, then MAC even when a different valid serial is present.
$bySerial = asset_resolve_by_machine('RENAMED-PC.CORP.LOCAL', 'MACHINE-SERIAL-023', 'FF:FF:FF:FF:FF:01');
$byMacAfterSerialMiss = asset_resolve_by_machine('RENAMED-PC.CORP.LOCAL', 'NEW-BOARD-SERIAL-999', 'AA-BB-CC-00-00-24');
$byName = asset_resolve_by_machine('PC-023.CORP.LOCAL', 'TO BE FILLED BY O.E.M.', '00:00:00:00:00:00');
$byNameAfterSerialMiss = asset_resolve_by_machine('PC-023.CORP.LOCAL', 'NEW-SERIAL-VALID', '01:BB:CC:DD:EE:FF');
check('نام‌عوض‌شده با همان سریال، همان شناسنامه را می‌یابد', $bySerial['id'] === $assetId && $bySerial['matched_by'] === 'serial');
check('سریال تازه/مادربردتعویض‌شده پس از miss با MAC همان دستگاه match می‌شود', $byMacAfterSerialMiss['id'] === $assetId && $byMacAfterSerialMiss['matched_by'] === 'mac');
check('اگر سریال تازه باشد و MAC نامعتبر، نام سیستم fallback می‌شود', $byNameAfterSerialMiss['id'] === $assetId && $byNameAfterSerialMiss['matched_by'] === 'hostname');
check('اگر سریال و MAC بی‌معنی باشند، نام ماشین fallback می‌شود', $byName['id'] === $assetId && $byName['matched_by'] === 'hostname');

// 4) Exercise the exact production SQL and bind list used by the API insertion.
$apiSource = (string) file_get_contents(APP_ROOT . '/api/inventory.php');
$apiSql = source_insert_sql(APP_ROOT . '/api/inventory.php');
$apiValues = [
    'API-PC-001', 'AGENT-SERIAL-001', 'Windows client', 'Example Systems', 'Model API',
    null, null, 'Windows 11 Pro', '64-bit', 'WINDOWS-001', '10.20.0.31', 'AA:BB:CC:00:00:31',
    'CPU API', 8192, '[]', '[]', '{}', '{}', 'test.user', 'Defender',
];
$apiParams = inventory_asset_insert_params('CLIENT-API-001', $apiValues);
$apiInsertOk = false;
try {
    db()->prepare($apiSql)->execute($apiParams);
    $apiInsertOk = true;
} catch (Throwable $exception) {
    $apiInsertError = $exception->getMessage();
}
$apiRow = db()->query("SELECT * FROM assets WHERE asset_tag = 'CLIENT-API-001'")->fetch();
check('API INSERT SQL واقعاً با پارامترهای production اجرا می‌شود',
    $apiInsertOk && count($apiParams) === substr_count($apiSql, '?') && ($apiRow['hostname'] ?? '') === 'API-PC-001', $apiInsertError ?? '');
check('API مسیر helper مشترک را فراخوانی می‌کند', str_contains($apiSource, 'inventory_asset_insert_params($assetTag, $values)'));

// 5) Exercise inventory_upsert_server_asset() itself: first insert and second update.
echo "=== upsert محلی سرور: درج واقعی + به‌روزرسانی ===\n";
$serverInventory1 = [
    'hostname' => 'LAB-SERVER-01', 'serial_number' => 'SERVER-SERIAL-001',
    'manufacturer' => 'Example Systems', 'model' => 'Server Model',
    'operating_system' => 'Windows Server 2016', 'os_architecture' => '64-bit', 'os_serial' => 'OS-SERVER-001',
    'ip_address' => '10.20.0.1', 'mac_address' => 'AA:BB:CC:00:00:01', 'cpu' => 'Server CPU', 'memory_mb' => 8192,
    'disks' => [['DeviceID' => 'C:', 'Size' => 500000000000]], 'software' => [['name' => 'App', 'version' => '1']],
    'hardware' => ['memory_modules' => [['Manufacturer' => 'Memory Co', 'PartNumber' => 'DDR4-8GB', 'Capacity' => 8589934592, 'Speed' => 3200, 'SerialNumber' => 'SERVER-RAM-1']],
        'physical_disks' => [['Model' => 'Server Disk A', 'Size' => 500000000000, 'SerialNumber' => 'SERVER-DISK-A']],
        'graphics' => [], 'collection_status' => ['root/cimv2|Win32_PhysicalMemory' => ['ok' => true, 'count' => 1], 'root/cimv2|Win32_DiskDrive' => ['ok' => true, 'count' => 1]]],
    'peripherals' => ['printers' => [], 'scanners' => [], 'monitors' => [], 'keyboards' => [], 'mice' => []],
    'domain_username' => 'EXAMPLE\\admin', 'antivirus' => 'Defender',
];
$serverId = 0;
$serverInsertError = '';
try {
    $serverId = inventory_upsert_server_asset($serverInventory1, 17);
} catch (Throwable $exception) {
    $serverInsertError = $exception->getMessage();
}
check('upsert سرور واقعاً INSERT را اجرا می‌کند (با تعداد bind درست)', $serverId > 0, $serverInsertError);
db()->prepare('UPDATE assets SET owner_user_id = 27, department_id = 12, plaque_number = ? WHERE id = ?')->execute(['SERVER-PL-1', $serverId]);
$serverInventory2 = $serverInventory1;
$serverInventory2['hostname'] = 'LAB-SERVER-RENAMED';
$serverInventory2['ip_address'] = '10.20.0.2';
$serverInventory2['mac_address'] = 'AA:BB:CC:00:00:02';
$serverInventory2['hardware']['memory_modules'][0]['PartNumber'] = 'DDR4-16GB';
$serverInventory2['hardware']['memory_modules'][0]['Capacity'] = 17179869184;
$serverInventory2['hardware']['memory_modules'][0]['SerialNumber'] = 'SERVER-RAM-2';
$serverInventory2['memory_mb'] = 16384;
$serverUpdateId = 0;
$serverUpdateError = '';
try {
    $serverUpdateId = inventory_upsert_server_asset($serverInventory2, 17);
} catch (Throwable $exception) {
    $serverUpdateError = $exception->getMessage();
}
$serverRow = $serverId > 0 ? db()->query('SELECT * FROM assets WHERE id = ' . (int) $serverId)->fetch() : [];
check('سریال ثابت پس از تغییر نام/IP/MAC همان ردیف را می‌یابد',
    $serverId > 0 && $serverUpdateId === $serverId && (int) db()->query("SELECT COUNT(*) FROM assets WHERE serial_number = 'SERVER-SERIAL-001'")->fetchColumn() === 1, $serverUpdateError);
check('مالک/واحد/پلاک دستی در upsert سرور حفظ می‌شوند',
    (int) ($serverRow['owner_user_id'] ?? 0) === 27
    && (int) ($serverRow['department_id'] ?? 0) === 12
    && ($serverRow['plaque_number'] ?? '') === 'SERVER-PL-1');

// 6) Actual local network probes, not a browser preview or fake ping result.
echo "=== آزمون socket/ICMP محلی ===\n";
$listener = @stream_socket_server('tcp://127.0.0.1:0', $socketErrno, $socketError);
if (is_resource($listener)) {
    $listenerAddress = (string) stream_socket_get_name($listener, false);
    $port = (int) substr(strrchr($listenerAddress, ':'), 1);
    $tcpResult = domain_scan_tcp_probe(['sandbox-loopback' => '127.0.0.1'], [$port], 1000);
    check('TCP probe به listener زندهٔ محلی وصل می‌شود', ($tcpResult['sandbox-loopback'] ?? false) === true, $listenerAddress);
    fclose($listener);
} else {
    check('TCP probe به listener زندهٔ محلی وصل می‌شود', false, $socketError ?? (string) $socketErrno);
}
$pingResult = domain_scan_ping_hosts(['127.0.0.1', '127.0.0.1', 'invalid host'], []);
check('ping واقعی loopback موفق است و ورودی تکراری یک بار برمی‌گردد',
    ($pingResult['127.0.0.1'] ?? false) === true && count($pingResult) === 1, json_encode($pingResult));
$fallbackPing = domain_scan_ping_hosts(
    ['sandbox-fallback.invalid'],
    ['sandbox-fallback.invalid' => '127.0.0.1']
);
check('مسیر واقعی IP fallback پس از DNS ناموفق، loopback را آنلاین می‌بیند',
    ($fallbackPing['sandbox-fallback.invalid'] ?? false) === true, json_encode($fallbackPing));
$fallback = domain_scan_queue_fallback_ips(
    [['hostname' => 'pc-023.corp.local', 'asset_id' => $assetId]],
    [$assetId => '10.20.0.24']
);
check('IP fallback با نام FQDN خود صف کلید می‌خورد، نه hostname کوتاه assets',
    ($fallback['pc-023.corp.local'] ?? '') === '10.20.0.24' && !isset($fallback['pc-023']));

// Explicit environment boundary: never report the Windows remote path as tested here.
if (PHP_OS_FAMILY !== 'Windows') {
    echo '[LIMIT] اتصال واقعی WMI/DCOM، WinRM، SMB/ADMIN$ و اجرای CIM روی رایانهٔ دامنه در این Linux sandbox قابل اجرا نیست.' . PHP_EOL;
    echo '[LIMIT] هیچ MySQL واقعی/دادهٔ مشتری نیز در این اجرا استفاده نشده است؛ این آزمون SQLite درون‌حافظه‌ای بود.' . PHP_EOL;
}

echo "\n──────────────────────────────\n";
echo "نتیجهٔ integration SQLite: $pass موفق، $fail ناموفق\n";
exit($fail === 0 ? 0 : 1);
