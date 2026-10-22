<?php
declare(strict_types=1);

function inventory_command(string $command): string
{
    if (!function_exists('shell_exec')) {
        return '';
    }
    $result = @shell_exec($command);
    return is_string($result) ? trim($result) : '';
}

function inventory_json(string $command): array
{
    $raw = inventory_command($command);
    if ($raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [];
    }
    return array_is_list($decoded) ? $decoded : [$decoded];
}

function inventory_last_error(): string
{
    $error = $GLOBALS['itsm_inventory_last_error'] ?? '';
    return is_string($error) ? $error : '';
}

function inventory_set_error(string $message): void
{
    $GLOBALS['itsm_inventory_last_error'] = $message;
    if (function_exists('system_log')) {
        system_log('error', 'inventory_collect', $message);
    }
}

function inventory_server_script_path(): string
{
    return __DIR__ . '/tools/collect_server_inventory.ps1';
}

function inventory_local_hostnames(): array
{
    $host = strtolower(trim((string) (gethostname() ?: php_uname('n'))));
    if ($host === '') {
        return [];
    }
    $short = strtolower(explode('.', $host)[0]);
    return array_values(array_unique(array_filter([$host, $short])));
}

function inventory_server_asset_tag(): string
{
    $host = trim((string) (gethostname() ?: php_uname('n')));
    return 'SERVER-' . strtoupper((string) preg_replace('/[^A-Za-z0-9_-]/', '-', $host !== '' ? $host : 'HOST'));
}

function inventory_asset_is_local(array $asset): bool
{
    $tag = strtoupper(trim((string) ($asset['asset_tag'] ?? '')));
    if ($tag !== '' && $tag === inventory_server_asset_tag()) {
        return true;
    }
    $assetHost = strtolower(trim((string) ($asset['hostname'] ?? '')));
    return $assetHost !== '' && in_array($assetHost, inventory_local_hostnames(), true);
}

function inventory_run_powershell_file(string $scriptPath): array
{
    if (!function_exists('shell_exec')) {
        inventory_set_error('تابع shell_exec در PHP غیرفعال است؛ سرور نمی‌تواند PowerShell را اجرا کند.');
        return [];
    }
    if (!is_file($scriptPath)) {
        inventory_set_error('فایل اسکریپت جمع‌آوری یافت نشد: ' . $scriptPath);
        return [];
    }
    $raw = inventory_command('powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -File ' . escapeshellarg($scriptPath) . ' 2>&1');
    if ($raw === '') {
        inventory_set_error('PowerShell هیچ خروجی نداد. دسترسی حساب سرویس وب به PowerShell و فعال بودن shell_exec را بررسی کنید.');
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        $snippet = function_exists('mb_substr') ? mb_substr($raw, 0, 300) : substr($raw, 0, 300);
        inventory_set_error('خروجی PowerShell قابل خواندن نبود: ' . $snippet);
        return [];
    }
    return $decoded;
}

function inventory_diagnostics(): array
{
    $checks = [];
    $checks[] = ['سیستم‌عامل سرور', PHP_OS_FAMILY . ' ' . php_uname('r')];
    $checks[] = ['shell_exec فعال است؟', function_exists('shell_exec') ? 'بله' : 'خیر'];
    $disabled = array_filter(array_map('trim', explode(',', (string) ini_get('disable_functions'))));
    $checks[] = ['shell_exec در disable_functions', in_array('shell_exec', $disabled, true) ? 'بله — غیرفعال است' : 'خیر'];
    $checks[] = ['نام سرور (hostname)', (string) (gethostname() ?: php_uname('n'))];
    if (PHP_OS_FAMILY !== 'Windows') {
        $checks[] = ['مسیر powershell', 'سیستم‌عامل سرور ویندوز نیست؛ استخراج سخت‌افزار فقط از روی سرور ویندوز انجام می‌شود.'];
        return $checks;
    }
    $powershellPath = inventory_command('where powershell');
    $checks[] = ['مسیر powershell', $powershellPath !== '' ? $powershellPath : 'پیدا نشد'];
    $scriptPath = inventory_server_script_path();
    $checks[] = ['فایل اسکریپت جمع‌آوری', is_file($scriptPath) ? 'موجود است' : 'یافت نشد: ' . $scriptPath];
    $data = inventory_run_powershell_file($scriptPath);
    if ($data === []) {
        $checks[] = ['اجرای جمع‌آوری', 'ناموفق'];
        $checks[] = ['پیام خطا', inventory_last_error() ?: 'خطای ناشناخته'];
        return $checks;
    }
    $checks[] = ['اجرای PowerShell', 'موفق'];
    $normalized = inventory_apply_powershell_data(inventory_base_inventory(), $data);
    $checks[] = ['امتیاز ردیف‌های سخت‌افزاری', (string) inventory_hardware_score($normalized) . ' از ۸'];
    foreach (['computer' => 'مشخصات رایانه', 'bios' => 'بایوس', 'processor' => 'پردازنده', 'os' => 'سیستم‌عامل', 'memory_modules' => 'ماژول‌های RAM', 'physical_disks' => 'هاردها', 'graphics' => 'کارت گرافیک', 'network' => 'کارت شبکه', 'printers' => 'پرینترها', 'software' => 'نرم‌افزارها'] as $key => $label) {
        $checks[] = ['تعداد ' . $label, (string) count(inventory_rows($data[$key] ?? []))];
    }
    return $checks;
}

function inventory_value(array $row, array $keys, mixed $default = ''): mixed
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null) {
            return $row[$key];
        }
    }
    return $default;
}

function inventory_rows(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }
    return array_is_list($value) ? $value : [$value];
}

/** Map persisted inventory paths to the collector query that can authoritatively report an empty result. */
function inventory_collection_status_keys(string $path): array
{
    static $map = [
        'serial_number' => ['root/cimv2|Win32_BIOS'],
        'manufacturer' => ['root/cimv2|Win32_ComputerSystem'],
        'model' => ['root/cimv2|Win32_ComputerSystem'],
        'cpu' => ['root/cimv2|Win32_Processor'],
        'memory_mb' => ['root/cimv2|Win32_ComputerSystem'],
        'domain_username' => ['root/cimv2|Win32_ComputerSystem'],
        'operating_system' => ['root/cimv2|Win32_OperatingSystem'],
        'os_architecture' => ['root/cimv2|Win32_OperatingSystem'],
        'os_serial' => ['root/cimv2|Win32_OperatingSystem'],
        'ip_address' => ['root/cimv2|Win32_NetworkAdapterConfiguration'],
        'mac_address' => ['root/cimv2|Win32_NetworkAdapterConfiguration'],
        'antivirus' => ['root/SecurityCenter2|AntiVirusProduct'],
        'hardware.bios' => ['root/cimv2|Win32_BIOS'],
        'hardware.computer' => ['root/cimv2|Win32_ComputerSystem'],
        'hardware.processor' => ['root/cimv2|Win32_Processor'],
        'hardware.operating_system' => ['root/cimv2|Win32_OperatingSystem'],
        'hardware.motherboard' => ['root/cimv2|Win32_BaseBoard'],
        'hardware.memory_modules' => ['root/cimv2|Win32_PhysicalMemory'],
        'hardware.physical_disks' => ['root/cimv2|Win32_DiskDrive'],
        'hardware.graphics' => ['root/cimv2|Win32_VideoController'],
        'hardware.sound' => ['root/cimv2|Win32_SoundDevice'],
        'hardware.optical_drives' => ['root/cimv2|Win32_CDROMDrive'],
        'hardware.network_adapters' => ['root/cimv2|Win32_NetworkAdapter'],
        'hardware.network_configurations' => ['root/cimv2|Win32_NetworkAdapterConfiguration'],
        'hardware.modems' => ['root/cimv2|Win32_POTSModem'],
        'hardware.logical_disks' => ['root/cimv2|Win32_LogicalDisk'],
        'hardware.extended_inventory.system_product' => ['root/cimv2|Win32_ComputerSystemProduct'],
        'hardware.extended_inventory.enclosure' => ['root/cimv2|Win32_SystemEnclosure'],
        'hardware.extended_inventory.memory_arrays' => ['root/cimv2|Win32_PhysicalMemoryArray'],
        'hardware.extended_inventory.pnp_devices' => ['root/cimv2|Win32_PnPEntity'],
        'hardware.extended_inventory.signed_drivers' => ['root/cimv2|Win32_PnPSignedDriver'],
        'hardware.extended_inventory.services' => ['root/cimv2|Win32_Service'],
        'hardware.extended_inventory.processes' => ['root/cimv2|Win32_Process'],
        'hardware.extended_inventory.startup_items' => ['root/cimv2|Win32_StartupCommand'],
        'hardware.extended_inventory.shares' => ['root/cimv2|Win32_Share'],
        'hardware.extended_inventory.user_accounts' => ['root/cimv2|Win32_UserAccount'],
        'hardware.extended_inventory.windows_updates' => ['root/cimv2|Win32_QuickFixEngineering'],
        'hardware.extended_inventory.environment_names' => ['root/cimv2|Win32_Environment'],
        'hardware.extended_inventory.firewall_profiles' => ['root/StandardCimv2|MSFT_NetFirewallProfile'],
        'hardware.extended_inventory.firewall_products' => ['root/SecurityCenter2|FirewallProduct'],
        'hardware.extended_inventory.antispyware_products' => ['root/SecurityCenter2|AntiSpywareProduct'],
        'hardware.extended_inventory.storage_devices' => ['root/Microsoft/Windows/Storage|MSFT_PhysicalDisk'],
        'hardware.extended_inventory.storage_reliability' => ['root/Microsoft/Windows/Storage|MSFT_StorageReliabilityCounter'],
        'hardware.extended_inventory.smart_status' => ['root/wmi|MSStorageDriver_FailurePredictStatus'],
        'hardware.extended_inventory.monitor_ids' => ['root/wmi|WmiMonitorID'],
        'os_users' => ['root/cimv2|Win32_UserProfile'],
        'disks' => ['root/cimv2|Win32_LogicalDisk'],
        'network_configurations' => ['root/cimv2|Win32_NetworkAdapterConfiguration'],
        'peripherals.printers' => ['root/cimv2|Win32_Printer'],
        'peripherals.scanners' => ['root/cimv2|Win32_PnPEntity'],
        'peripherals.keyboards' => ['root/cimv2|Win32_PnPEntity'],
        'peripherals.mice' => ['root/cimv2|Win32_PnPEntity'],
        'peripherals.monitors' => ['root/cimv2|Win32_DesktopMonitor', 'root/cimv2|Win32_PnPEntity'],
    ];
    return $map[$path] ?? [];
}

function inventory_collection_category_succeeded(array $status, string $path): bool
{
    $keys = inventory_collection_status_keys($path);
    if ($keys === []) {
        return false;
    }
    foreach ($keys as $key) {
        if (!is_array($status[$key] ?? null) || ($status[$key]['ok'] ?? false) !== true) {
            return false;
        }
    }
    return true;
}

/** Preserve omitted/failed categories; clear an old category only when the collector confirms success. */
function inventory_merge_missing_values(array $fresh, array $previous, ?array $status = null, string $path = ''): array
{
    if ($status === null) {
        $status = is_array($fresh['collection_status'] ?? null)
            ? $fresh['collection_status']
            : (is_array($fresh['hardware']['collection_status'] ?? null) ? $fresh['hardware']['collection_status'] : []);
    }
    foreach ($previous as $key => $oldValue) {
        $childPath = $path === '' ? (string) $key : $path . '.' . $key;
        if (!array_key_exists($key, $fresh)) {
            $fresh[$key] = $oldValue;
            continue;
        }
        // Quality metadata describes this scan; never blend in stale status from an earlier scan.
        if ($key === 'collection_status') {
            continue;
        }
        $newValue = $fresh[$key];
        $emptyScalar = $newValue === null || $newValue === '' || ($key === 'memory_mb' && (int) $newValue <= 0);
        if ($emptyScalar && !is_array($newValue) && $oldValue !== null && $oldValue !== '') {
            // A successful query does not prove an empty scalar property is authoritative; retain the last-known scalar.
            $fresh[$key] = $oldValue;
            continue;
        }
        if (is_array($newValue) && $newValue === [] && is_array($oldValue) && $oldValue !== []) {
            if (!inventory_collection_category_succeeded($status, $childPath)) {
                $fresh[$key] = $oldValue;
            }
            continue;
        }
        if (is_array($newValue) && is_array($oldValue) && !array_is_list($newValue) && !array_is_list($oldValue)) {
            $fresh[$key] = inventory_merge_missing_values($newValue, $oldValue, $status, $childPath);
        }
    }
    return $fresh;
}

function inventory_saved_asset_snapshot(array $asset): array
{
    $decode = static function (mixed $json): array {
        $value = json_decode((string) $json, true);
        return is_array($value) ? $value : [];
    };
    $hardware = $decode($asset['hardware_json'] ?? '');
    return [
        'hostname' => (string) ($asset['hostname'] ?? ''),
        'serial_number' => (string) ($asset['serial_number'] ?? ''),
        'manufacturer' => (string) ($asset['manufacturer'] ?? ''),
        'model' => (string) ($asset['model'] ?? ''),
        'computer_type' => (string) ($asset['computer_type'] ?? ''),
        'operating_system' => (string) ($asset['operating_system'] ?? ''),
        'os_architecture' => (string) ($asset['os_architecture'] ?? ''),
        'os_serial' => (string) ($asset['os_serial'] ?? ''),
        'ip_address' => (string) ($asset['ip_address'] ?? ''),
        'mac_address' => (string) ($asset['mac_address'] ?? ''),
        'cpu' => (string) ($asset['cpu'] ?? ''),
        'memory_mb' => (int) ($asset['memory_mb'] ?? 0),
        'disks' => $decode($asset['disks_json'] ?? ''),
        'software' => $decode($asset['software_json'] ?? ''),
        'hardware' => $hardware,
        'collection_status' => is_array($hardware['collection_status'] ?? null) ? $hardware['collection_status'] : [],
        'peripherals' => $decode($asset['peripherals_json'] ?? ''),
        'domain_username' => (string) ($asset['domain_username'] ?? ''),
        'antivirus' => (string) ($asset['antivirus'] ?? ''),
    ];
}

function inventory_apply_collection_status_metadata(array $inventory, array $previousStatus, string $source): array
{
    $status = is_array($inventory['collection_status'] ?? null)
        ? $inventory['collection_status']
        : (is_array($inventory['hardware']['collection_status'] ?? null) ? $inventory['hardware']['collection_status'] : []);
    $source = in_array($source, ['agent', 'domain', 'server_inventory'], true) ? $source : 'unknown';
    $attemptedAt = date('c');
    foreach ($status as $key => $result) {
        if (!is_array($result)) {
            continue;
        }
        $prior = is_array($previousStatus[$key] ?? null) ? $previousStatus[$key] : [];
        $result['attempted'] = true;
        $result['last_attempt_at'] = $attemptedAt;
        $result['last_attempt_source'] = $source;
        if (($result['ok'] ?? false) === true) {
            $result['last_success_at'] = $attemptedAt;
            $result['last_success_source'] = $source;
        } else {
            if (!empty($prior['last_success_at'])) {
                $result['last_success_at'] = $prior['last_success_at'];
            }
            if (!empty($prior['last_success_source'])) {
                $result['last_success_source'] = $prior['last_success_source'];
            }
        }
        $status[$key] = $result;
    }
    foreach ($previousStatus as $key => $prior) {
        if (isset($status[$key]) || !is_array($prior)) {
            continue;
        }
        // The current method did not query this category (for example, a lighter-weight agent).
        // Keep its last successful provenance without reporting a false failure.
        $prior['ok'] = null;
        $prior['count'] = null;
        $prior['attempted'] = false;
        $status[$key] = $prior;
    }
    $inventory['collection_status'] = $status;
    if (is_array($inventory['hardware'] ?? null)) {
        $inventory['hardware']['collection_status'] = $status;
    }
    return $inventory;
}

function inventory_asset_exists(int $assetId): bool
{
    if ($assetId <= 0) {
        return false;
    }
    $query = db()->prepare('SELECT 1 FROM assets WHERE id = ? LIMIT 1');
    $query->execute([$assetId]);
    return (bool) $query->fetchColumn();
}

function sync_inventory_rows(int $assetId, array $inventory, string $source = 'agent'): void
{
    if ($assetId <= 0) {
        return;
    }
    $source = in_array($source, ['agent', 'server_inventory'], true) ? $source : 'agent';
    $hardware = is_array($inventory['hardware'] ?? null) ? $inventory['hardware'] : [];
    db()->prepare('DELETE FROM asset_memory_modules WHERE asset_id = ?')->execute([$assetId]);
    foreach (inventory_rows($hardware['memory_modules'] ?? []) as $slot => $row) {
        if (!is_array($row)) { continue; }
        $capacity = (int) inventory_value($row, ['Capacity', 'capacity'], 0);
        $capacityMb = $capacity > 100000 ? (int) round($capacity / 1048576) : $capacity;
        db()->prepare('INSERT INTO asset_memory_modules (asset_id, slot_no, manufacturer, model, capacity_mb, speed_mhz, serial_number, collected_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())')->execute([$assetId, $slot + 1, (string) inventory_value($row, ['Manufacturer', 'manufacturer']), (string) inventory_value($row, ['PartNumber', 'Model', 'model']), $capacityMb ?: null, (int) inventory_value($row, ['Speed', 'speed'], 0) ?: null, (string) inventory_value($row, ['SerialNumber', 'serial_number'])]);
    }
    db()->prepare('DELETE FROM asset_storage_devices WHERE asset_id = ?')->execute([$assetId]);
    foreach (inventory_rows($hardware['physical_disks'] ?? $inventory['physical_disks'] ?? []) as $slot => $row) {
        if (!is_array($row)) { continue; }
        db()->prepare('INSERT INTO asset_storage_devices (asset_id, slot_no, device_type, model, capacity_bytes, serial_number, media_type, interface_type, collected_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())')->execute([$assetId, $slot + 1, 'physical', (string) inventory_value($row, ['Model', 'model']), (int) inventory_value($row, ['Size', 'size'], 0) ?: null, (string) inventory_value($row, ['SerialNumber', 'serial_number']), (string) inventory_value($row, ['MediaType', 'media_type']), (string) inventory_value($row, ['InterfaceType', 'interface_type'])]);
    }
    db()->prepare('DELETE FROM asset_graphics_adapters WHERE asset_id = ?')->execute([$assetId]);
    foreach (inventory_rows($hardware['graphics'] ?? $inventory['graphics'] ?? []) as $slot => $row) {
        if (!is_array($row)) { continue; }
        $vram = (int) inventory_value($row, ['AdapterRAM', 'vram_mb'], 0);
        $vramMb = $vram > 100000 ? (int) round($vram / 1048576) : $vram;
        db()->prepare('INSERT INTO asset_graphics_adapters (asset_id, slot_no, model, vram_mb, driver_version, collected_at) VALUES (?, ?, ?, ?, ?, NOW())')->execute([$assetId, $slot + 1, (string) inventory_value($row, ['Name', 'model']), $vramMb ?: null, (string) inventory_value($row, ['DriverVersion', 'driver_version'])]);
    }
    $peripherals = is_array($inventory['peripherals'] ?? null) ? $inventory['peripherals'] : [];
    db()->prepare('DELETE FROM asset_peripherals WHERE asset_id = ?')->execute([$assetId]);
    foreach (['printers' => 'printer', 'scanners' => 'scanner', 'monitors' => 'monitor', 'keyboards' => 'keyboard', 'mice' => 'mouse'] as $key => $type) {
        foreach (inventory_rows($peripherals[$key] ?? []) as $slot => $row) {
            if (!is_array($row)) { continue; }
            $model = (string) inventory_value($row, ['Name', 'FriendlyName', 'model']);
            if ($model === '') { continue; }
            db()->prepare('INSERT INTO asset_peripherals (asset_id, peripheral_type, slot_no, model, manufacturer, serial_number, details_json, source, collected_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())')->execute([$assetId, $type, $slot + 1, $model, (string) inventory_value($row, ['Manufacturer', 'manufacturer']), (string) inventory_value($row, ['SerialNumber', 'serial_number']), json_encode($row, JSON_UNESCAPED_UNICODE), $source]);
        }
    }
}

function inventory_hardware_score(array $inventory): int
{
    $hardware = is_array($inventory['hardware'] ?? null) ? $inventory['hardware'] : [];
    $score = 0;
    foreach (['processor', 'operating_system', 'bios', 'motherboard', 'physical_disks', 'memory_modules', 'graphics', 'network_adapters'] as $key) {
        if (count(inventory_rows($hardware[$key] ?? [])) > 0) {
            $score++;
        }
    }
    return $score;
}

function inventory_collect_server_reliable(): array
{
    if (function_exists('set_time_limit')) {
        @set_time_limit(300);
    }
    $inventory = collect_server_inventory();
    if (PHP_OS_FAMILY === 'Windows' && function_exists('shell_exec') && inventory_hardware_score($inventory) === 0) {
        $GLOBALS['itsm_inventory_last_error'] = '';
        $retry = collect_server_inventory();
        if (inventory_hardware_score($retry) > inventory_hardware_score($inventory)) {
            return $retry;
        }
    }
    return $inventory;
}

function inventory_server_asset_id(): int
{
    $conditions = ['asset_tag = ?'];
    $params = [inventory_server_asset_tag()];
    $host = trim((string) (gethostname() ?: php_uname('n')));
    if ($host !== '') {
        $conditions[] = 'hostname = ?';
        $params[] = $host;
    }
    $query = db()->prepare('SELECT id FROM assets WHERE (' . implode(' OR ', $conditions) . ') ORDER BY id ASC LIMIT 1');
    $query->execute($params);
    return (int) ($query->fetchColumn() ?: 0);
}

function inventory_server_profile_is_empty(): bool
{
    $assetId = inventory_server_asset_id();
    if ($assetId <= 0) {
        return true;
    }
    if (!function_exists('asset_profile_fetch')) {
        return false;
    }
    $profile = asset_profile_fetch($assetId);
    $keys = ['os_name', 'os_version', 'cpu_model', 'ram_total', 'disk1_model', 'motherboard_model', 'motherboard_serial', 'ip_address', 'mac_address'];
    $filled = 0;
    foreach ($keys as $key) {
        if (trim((string) ($profile[$key] ?? '')) !== '') {
            $filled++;
        }
    }
    return $filled < 3;
}

function inventory_auto_extract_server(int $userId): ?int
{
    if (PHP_OS_FAMILY !== 'Windows' || !function_exists('shell_exec') || !function_exists('asset_profile_apply_auto')) {
        return null;
    }
    if (!inventory_server_profile_is_empty()) {
        return null;
    }
    $inventory = inventory_collect_server_reliable();
    if (inventory_hardware_score($inventory) === 0) {
        return null;
    }
    $assetId = inventory_upsert_server_asset($inventory, $userId);
    if ($assetId <= 0) {
        return null;
    }
    $filled = asset_profile_apply_auto($assetId, asset_profile_auto_values($inventory), $userId, false);
    system_log('info', 'inventory', 'استخراج خودکار شناسنامه سرور در اولین ورود', ['asset_id' => $assetId, 'filled' => $filled]);
    return $assetId;
}

function inventory_base_inventory(): array
{
    $hostname = gethostname() ?: php_uname('n');
    return [
        'hostname' => $hostname,
        'serial_number' => '',
        'manufacturer' => '',
        'model' => '',
        'computer_type' => PHP_OS_FAMILY === 'Windows' ? 'Windows Server' : (PHP_OS_FAMILY . ' Server'),
        'operating_system' => PHP_OS_FAMILY . ' ' . php_uname('r'),
        'os_architecture' => php_uname('m'),
        'os_serial' => '',
        'ip_address' => $_SERVER['SERVER_ADDR'] ?? gethostbyname($hostname),
        'mac_address' => '',
        'cpu' => php_uname('m'),
        'memory_mb' => 0,
        'domain_username' => $_SERVER['AUTH_USER'] ?? ($_SERVER['REMOTE_USER'] ?? ''),
        'antivirus' => '',
        'disks' => [],
        'software' => [],
        'peripherals' => [
            'printers' => [],
            'scanners' => [],
            'monitors' => [],
            'keyboards' => [],
            'mice' => [],
        ],
        'hardware' => [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? '',
            'os_release' => php_uname('a'),
        ],
        'collected_at' => date('c'),
    ];
}

function inventory_apply_powershell_data(array $inventory, array $data): array
{
    $rows = static fn (string $key): array => inventory_rows($data[$key] ?? []);
    $collectionStatus = is_array($data['collection_status'] ?? null) ? $data['collection_status'] : [];
    $inventory['collection_status'] = $collectionStatus;
    $computer = $rows('computer')[0] ?? [];
    $bios = $rows('bios')[0] ?? [];
    $processor = $rows('processor')[0] ?? [];
    $os = $rows('os')[0] ?? [];
    $motherboard = $rows('motherboard')[0] ?? [];
    $memoryModules = $rows('memory_modules');
    $physicalDisks = $rows('physical_disks');
    $gpu = $rows('graphics');
    $sound = $rows('sound');
    $opticalDrives = $rows('optical_drives');
    $networkAdapters = $rows('network_adapters');
    $modems = $rows('modems');
    $localUsers = $rows('os_users');
    $disks = $rows('disks');
    $network = $rows('network');
    $printers = $rows('printers');
    $scanners = $rows('scanners');
    $monitors = $rows('monitors') ?: $rows('monitors_pnp');
    $keyboards = $rows('keyboards');
    $mice = $rows('mice');
    $antivirus = $rows('antivirus');
    $software = $rows('software');
    $inventory['serial_number'] = (string) ($bios['SerialNumber'] ?? '');
    $inventory['manufacturer'] = (string) ($computer['Manufacturer'] ?? '');
    $inventory['model'] = (string) ($computer['Model'] ?? '');
    $inventory['cpu'] = (string) ($processor['Name'] ?? '') !== '' ? (string) $processor['Name'] : $inventory['cpu'];
    $inventory['memory_mb'] = (int) round(((int) ($computer['TotalPhysicalMemory'] ?? 0)) / 1048576);
    $inventory['domain_username'] = (string) ($computer['UserName'] ?? $inventory['domain_username']);
    $inventory['operating_system'] = (string) ($os['Caption'] ?? $inventory['operating_system']);
    $inventory['os_architecture'] = (string) ($os['OSArchitecture'] ?? $inventory['os_architecture']);
    $inventory['os_serial'] = (string) ($os['SerialNumber'] ?? '');
    $antivirusNames = [];
    foreach ($antivirus as $item) {
        if (!is_array($item)) { continue; }
        $antivirusName = trim((string) ($item['displayName'] ?? ''));
        if ($antivirusName !== '') { $antivirusNames[] = $antivirusName; }
    }
    $inventory['antivirus'] = implode(', ', array_unique($antivirusNames));
    $extendedInventory = [];
    foreach ([
        'system_product', 'enclosure', 'memory_arrays', 'pnp_devices', 'signed_drivers',
        'services', 'processes', 'startup_items', 'shares', 'user_accounts', 'windows_updates',
        'environment_names', 'firewall_products', 'antispyware_products', 'firewall_profiles',
        'storage_devices', 'storage_reliability', 'smart_status', 'monitor_ids',
    ] as $key) {
        $extendedInventory[$key] = $rows($key);
    }
    $inventory['hardware'] = array_merge($inventory['hardware'], [
        'manufacturer' => $computer['Manufacturer'] ?? '',
        'model' => $computer['Model'] ?? '',
        'user' => $computer['UserName'] ?? '',
        'bios' => $bios,
        'processor' => $processor,
        'operating_system' => $os,
        'motherboard' => $motherboard,
        'memory_modules' => $memoryModules,
        'physical_disks' => $physicalDisks,
        'graphics' => $gpu,
        'sound' => $sound,
        'optical_drives' => $opticalDrives,
        'network_adapters' => $networkAdapters,
        // Win32_NetworkAdapterConfiguration carries IPs/gateways; keep it separately instead of
        // dropping it after using only the first row for a MAC address.
        'network_configurations' => $network,
        'modems' => $modems,
        'logical_disks' => $disks,
        'extended_inventory' => $extendedInventory,
        'collection_status' => $collectionStatus,
    ]);
    $inventory['os_users'] = $localUsers;
    $inventory['disks'] = $disks;
    $inventory['software'] = $software;
    $inventory['peripherals'] = ['printers' => $printers, 'scanners' => $scanners, 'monitors' => $monitors, 'keyboards' => $keyboards, 'mice' => $mice];
    $inventory['network_configurations'] = $network;
    $preferredIp = trim((string) ($inventory['ip_address'] ?? ''));
    $primaryNetwork = null;
    foreach ($network as $networkRow) {
        if (!is_array($networkRow)) { continue; }
        $addresses = array_map('strval', (array) inventory_value($networkRow, ['IPAddress', 'ip_address'], []));
        if ($preferredIp !== '' && in_array($preferredIp, $addresses, true)) {
            $primaryNetwork = $networkRow;
            break;
        }
    }
    if (!is_array($primaryNetwork)) {
        foreach ($network as $networkRow) {
            if (!is_array($networkRow)) { continue; }
            $addresses = (array) inventory_value($networkRow, ['IPAddress', 'ip_address'], []);
            $gateways = (array) inventory_value($networkRow, ['DefaultIPGateway', 'default_gateway'], []);
            if ($addresses !== [] && $gateways !== []) {
                $primaryNetwork = $networkRow;
                break;
            }
        }
    }
    if (!is_array($primaryNetwork)) {
        $primaryNetwork = is_array($network[0] ?? null) ? $network[0] : [];
    }
    $primaryIps = array_values(array_filter(array_map('trim', array_map('strval', (array) inventory_value($primaryNetwork, ['IPAddress', 'ip_address'], []))), static fn (string $ip): bool => filter_var($ip, FILTER_VALIDATE_IP) !== false && !in_array(strtolower($ip), ['0.0.0.0', '::', '127.0.0.1', '::1'], true) && strncmp($ip, '169.254.', 8) !== 0 && stripos($ip, 'fe80:') !== 0));
    if ($preferredIp !== '' && in_array($preferredIp, $primaryIps, true)) {
        $inventory['ip_address'] = $preferredIp;
    } elseif ($primaryIps !== []) {
        $ipv4 = array_values(array_filter($primaryIps, static fn (string $ip): bool => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false));
        $inventory['ip_address'] = $ipv4[0] ?? $primaryIps[0];
    }
    $inventory['mac_address'] = (string) inventory_value($primaryNetwork, ['MACAddress', 'mac_address'], '');
    return $inventory;
}

function collect_server_inventory(): array
{
    $inventory = inventory_base_inventory();

    if (PHP_OS_FAMILY === 'Windows') {
        $inventory = inventory_apply_powershell_data($inventory, inventory_run_powershell_file(inventory_server_script_path()));
    } else {
        $memory = inventory_command("grep MemTotal /proc/meminfo 2>/dev/null | awk '{print \$2}'");
        $inventory['memory_mb'] = (int) round(((int) $memory) / 1024);
        $inventory['cpu'] = inventory_command("awk -F: '/model name/ {print \$2; exit}' /proc/cpuinfo 2>/dev/null") ?: php_uname('p');
        $inventory['disks'] = [['filesystem' => '/', 'usage' => inventory_command("df -h / 2>/dev/null | awk 'NR==2 {print \$3 \" / \" \$2 \" (\" \$5 \")\"}'")]];
        $inventory['software'] = [['name' => 'PHP', 'version' => PHP_VERSION], ['name' => 'Web server', 'version' => $_SERVER['SERVER_SOFTWARE'] ?? '']];
        $inventory['domain_username'] = get_current_user();
        $inventory['mac_address'] = inventory_command("ip link 2>/dev/null | awk '/link\\/ether/ {print \$2; exit}'");
    }
    return $inventory;
}

function inventory_upsert_server_asset(array $inventory, int $userId): int
{
    $host = trim((string) ($inventory['hostname'] ?? ''));
    $serial = trim((string) ($inventory['serial_number'] ?? ''));
    $assetTag = inventory_server_asset_tag();
    $conditions = ['asset_tag = ?'];
    $params = [$assetTag];
    if ($host !== '') {
        $conditions[] = 'hostname = ?';
        $params[] = $host;
    }
    if ($serial !== '') {
        $conditions[] = 'serial_number = ?';
        $params[] = $serial;
    }
    $assetQuery = db()->prepare('SELECT * FROM assets WHERE (' . implode(' OR ', $conditions) . ') ORDER BY id ASC LIMIT 1');
    $assetQuery->execute($params);
    $existingAsset = $assetQuery->fetch();
    $assetId = (int) ($existingAsset['id'] ?? 0);
    $receivedInventory = $inventory;
    $previousStatus = [];
    if ($existingAsset) {
        $previous = inventory_saved_asset_snapshot($existingAsset);
        $previousStatus = $previous['collection_status'] ?? [];
        $inventory = inventory_merge_missing_values($inventory, $previous);
        if ((int) ($inventory['memory_mb'] ?? 0) <= 0 && (int) ($previous['memory_mb'] ?? 0) > 0) {
            $inventory['memory_mb'] = (int) $previous['memory_mb'];
        }
    }
    $inventory = inventory_apply_collection_status_metadata($inventory, $previousStatus, 'server_inventory');
    $payload = json_encode($receivedInventory, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $values = [$inventory['hostname'], $inventory['serial_number'], $inventory['manufacturer'], $inventory['model'], $inventory['operating_system'], $inventory['os_architecture'], $inventory['os_serial'], $inventory['ip_address'], $inventory['mac_address'], $inventory['cpu'], $inventory['memory_mb'], json_encode($inventory['disks'], JSON_UNESCAPED_UNICODE), json_encode($inventory['software'], JSON_UNESCAPED_UNICODE), json_encode($inventory['hardware'], JSON_UNESCAPED_UNICODE), json_encode($inventory['peripherals'], JSON_UNESCAPED_UNICODE), $inventory['domain_username'], $inventory['antivirus']];
    if ($assetId > 0) {
        db()->prepare('UPDATE assets SET hostname = ?, serial_number = ?, manufacturer = ?, model = ?, operating_system = ?, os_architecture = ?, os_serial = ?, ip_address = ?, mac_address = ?, cpu = ?, memory_mb = ?, disks_json = ?, software_json = ?, hardware_json = ?, peripherals_json = ?, domain_username = ?, antivirus = ?, source = "server_inventory", last_inventory_at = NOW() WHERE id = ?')->execute([...$values, $assetId]);
    } else {
        db()->prepare('INSERT INTO assets (asset_tag, hostname, serial_number, manufacturer, model, operating_system, os_architecture, os_serial, ip_address, mac_address, cpu, memory_mb, disks_json, software_json, hardware_json, peripherals_json, domain_username, antivirus, source, last_inventory_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "server_inventory", NOW())')->execute([$assetTag, ...$values]);
        $assetId = (int) db()->lastInsertId();
    }
    sync_inventory_rows($assetId, $inventory, 'server_inventory');
    if (inventory_asset_exists($assetId)) {
        db()->prepare('INSERT INTO asset_inventory_history (asset_id, snapshot_json, collected_by) VALUES (?, ?, ?)')->execute([$assetId, $payload, $userId > 0 ? $userId : null]);
    }
    return $assetId;
}

function inventory_collect_initial_asset(int $userId): ?int
{
    try {
        $inventory = collect_server_inventory();
        $assetTag = 'SERVER-' . strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '-', (string) ($inventory['hostname'] ?? 'HOST')));
        $query = db()->prepare('SELECT id FROM assets WHERE asset_tag = ? LIMIT 1');
        $query->execute([$assetTag]);
        if ($query->fetchColumn()) {
            return null;
        }
        return inventory_upsert_server_asset($inventory, $userId);
    } catch (Throwable) {
        return null;
    }
}

/**
 * نام سیستم کلاینت از روی IP — با کش یک‌هفته‌ای.
 *
 * ⚠️ چرا: gethostbyaddr روی هر درخواست ورود، تا چند ثانیه (timeout دی‌ان‌اس) بلاک
 * می‌شد و بارگذاری صفحهٔ ورود را کند می‌کرد. حالا نتیجه در storage ذخیره می‌شود و
 * برای هر IP حداکثر هفته‌ای یک‌بار پرس‌وجو انجام می‌شود. با تنظیم
 * client_hostname_lookup=0 می‌توان این پرس‌وجو را کامل خاموش کرد.
 */
function inventory_reverse_name(string $ip, string $fallback): string
{
    static $cache = null;
    $enabled = true;
    try {
        $enabled = setting('client_hostname_lookup', '1') !== '0';
    } catch (Throwable) {
    }
    if (!$enabled) {
        return $fallback;
    }
    if ($cache === null) {
        $cache = [];
        $root = defined('APP_ROOT') ? (string) APP_ROOT : dirname(__DIR__);
        $file = $root . '/storage/hostname_cache.json';
        if (is_file($file)) {
            $decoded = json_decode((string) @file_get_contents($file), true);
            if (is_array($decoded)) {
                $cache = $decoded;
            }
        }
    }
    $now = time();
    $ttl = 7 * 86400;
    $entry = $cache[$ip] ?? null;
    if (is_array($entry) && ($now - (int) ($entry['ts'] ?? 0)) < $ttl) {
        $name = trim((string) ($entry['name'] ?? ''));
        return $name !== '' ? $name : $fallback;
    }
    $resolved = @gethostbyaddr($ip);
    $name = (is_string($resolved) && $resolved !== '' && $resolved !== $ip) ? $resolved : '';
    $cache[$ip] = ['name' => $name, 'ts' => $now];
    // فقط همین یک کلید جدید و آیتم‌های تازه نگه داشته می‌شوند (فایل کوچک می‌ماند)
    if (count($cache) > 800) {
        uasort($cache, static fn (array $a, array $b): int => (int) ($b['ts'] ?? 0) <=> (int) ($a['ts'] ?? 0));
        $cache = array_slice($cache, 0, 800, true);
    }
    $root = defined('APP_ROOT') ? (string) APP_ROOT : dirname(__DIR__);
    $dir = $root . '/storage';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    @file_put_contents($dir . '/hostname_cache.json', (string) json_encode($cache, JSON_UNESCAPED_UNICODE), LOCK_EX);
    return $name !== '' ? $name : $fallback;
}

function inventory_collect_client_asset(array $user): ?int
{
    try {
        $clientIp = inventory_client_ip();
        if ($clientIp === '' || !filter_var($clientIp, FILTER_VALIDATE_IP)) {
            return null;
        }
        $serverAddr = (string) ($_SERVER['SERVER_ADDR'] ?? '');
        if (in_array($clientIp, ['127.0.0.1', '::1'], true) || ($serverAddr !== '' && $clientIp === $serverAddr)) {
            return null;
        }
        // ورود فقط هویت IP را ثبت می‌کند؛ reverse DNS در مسیر ورود synchronous نباشد چون
        // DNS/PTR معیوب می‌تواند هر ورود را ده‌ها ثانیه معطل کند. نام سخت‌افزاری با اسکن صریح پر می‌شود.
        $hostname = 'CLIENT-' . str_replace(['.', ':'], '-', $clientIp);
        $hostname = substr(trim($hostname), 0, 190);
        $assetTag = 'CLIENT-' . strtoupper(substr(hash('sha256', $clientIp), 0, 16));
        $username = trim((string) ($user['username'] ?? ''));
        $departmentId = (int) ($user['department_id'] ?? 0) ?: null;
        $hardware = json_encode([
            'collection_mode' => 'client_request_identity',
            'remote_addr' => $clientIp,
            'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'collected_at' => date('c'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $query = db()->prepare('SELECT id FROM assets WHERE asset_tag = ? LIMIT 1');
        $query->execute([$assetTag]);
        $assetId = (int) ($query->fetchColumn() ?: 0);
        $hasFullHardware = false;
        if ($assetId > 0) {
            $existing = db()->prepare('SELECT hostname, hardware_json FROM assets WHERE id = ? LIMIT 1');
            $existing->execute([$assetId]);
            $existingAsset = $existing->fetch(PDO::FETCH_ASSOC) ?: [];
            $existingHardware = json_decode((string) ($existingAsset['hardware_json'] ?? ''), true);
            $isLoginIdentity = is_array($existingHardware) && ($existingHardware['collection_mode'] ?? '') === 'client_request_identity';
            $hasFullHardware = is_array($existingHardware) && !$isLoginIdentity && !empty($existingHardware);
            if ($hasFullHardware) {
                // ورود کاربر فقط هویت/نشانی شبکه را تازه می‌کند؛ نام و زمان جمع‌آوری سخت‌افزار حفظ شود.
                $storedHostname = trim((string) ($existingAsset['hostname'] ?? ''));
                if ($storedHostname !== '') {
                    $hostname = substr($storedHostname, 0, 190);
                }
                db()->prepare('UPDATE assets SET hostname = ?, owner_user_id = ?, department_id = COALESCE(?, department_id), ip_address = ?, domain_username = ? WHERE id = ?')->execute([$hostname, (int) ($user['id'] ?? 0), $departmentId, $clientIp, $username, $assetId]);
            } elseif ($isLoginIdentity) {
                // اصلاح رکوردهای قدیمی که ورود کاربر را اشتباهاً Inventory موفق علامت زده بودند.
                db()->prepare('UPDATE assets SET hostname = ?, owner_user_id = ?, department_id = COALESCE(?, department_id), ip_address = ?, domain_username = ?, hardware_json = ?, last_inventory_at = NULL WHERE id = ?')->execute([$hostname, (int) ($user['id'] ?? 0), $departmentId, $clientIp, $username, $hardware, $assetId]);
            } else {
                db()->prepare('UPDATE assets SET hostname = ?, owner_user_id = ?, department_id = COALESCE(?, department_id), ip_address = ?, domain_username = ?, hardware_json = ? WHERE id = ?')->execute([$hostname, (int) ($user['id'] ?? 0), $departmentId, $clientIp, $username, $hardware, $assetId]);
            }
        } else {
            // ثبت هویت ورود، Inventory سخت‌افزاری نیست؛ تاریخ جمع‌آوری عمداً خالی می‌ماند.
            db()->prepare('INSERT INTO assets (asset_tag, hostname, computer_type, owner_user_id, department_id, ip_address, domain_username, hardware_json, source) VALUES (?, ?, "Windows client", ?, ?, ?, ?, ?, "agent")')->execute([$assetTag, $hostname, (int) ($user['id'] ?? 0), $departmentId, $clientIp, $username, $hardware]);
            $assetId = (int) db()->lastInsertId();
        }
        // شناسنامه هم پر شود: قبلاً فقط جدول assets به‌روز می‌شد و فرم «شناسنامه سیستم»
        // برای سیستم کاربران خالی می‌ماند (شکایت «هیچ اطلاعاتی استخراج نمی‌شود»).
        if (function_exists('asset_profile_apply_auto')) {
            asset_profile_apply_auto($assetId, array_filter([
                'hostname' => $hostname,
                'ip_address' => $clientIp,
                'user_login' => $username,
                'computer_type' => 'Windows client',
                'connected_network' => 1,
            ], static fn ($v): bool => trim((string) $v) !== ''), (int) ($user['id'] ?? 0), false);
        }
        // لاگ هویت ورود در جدول تاریخچهٔ Inventory سخت‌افزاری ثبت نمی‌شود.
        return $assetId > 0 ? $assetId : null;
    } catch (Throwable $exception) {
        app_error_log('ITSM client identity inventory failed: ' . $exception->getMessage());
        return null;
    }
}

function inventory_client_ip(): string
{
    $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}
