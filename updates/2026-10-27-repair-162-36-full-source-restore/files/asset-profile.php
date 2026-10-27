<?php
declare(strict_types=1);

function asset_profile_column_sql(): array
{
    $sql = [];
    $sql['updated_by'] = 'INT UNSIGNED NULL';
    foreach (asset_profile_all_columns() as $key => $type) {
        if ($type === 'bool') {
            $sql[$key] = 'TINYINT(1) NOT NULL DEFAULT 0';
        } elseif ($type === 'textarea') {
            $sql[$key] = 'TEXT NULL';
        } elseif ($type === 'date') {
            $sql[$key] = 'DATETIME NULL';
        } elseif ($type === 'number') {
            $sql[$key] = 'VARCHAR(60) NULL';
        } else {
            $sql[$key] = 'VARCHAR(255) NULL';
        }
    }
    return $sql;
}

function asset_profile_ensure_schema(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    try {
        db()->exec('CREATE TABLE IF NOT EXISTS asset_profiles (asset_id INT UNSIGNED NOT NULL PRIMARY KEY, updated_by INT UNSIGNED NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $columns = db()->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'asset_profiles'")->fetchAll(PDO::FETCH_COLUMN);
        $have = array_flip(array_map('strtolower', $columns));
        foreach (asset_profile_column_sql() as $name => $definition) {
            if (!isset($have[strtolower($name)])) {
                db()->exec('ALTER TABLE asset_profiles ADD COLUMN `' . $name . '` ' . $definition);
            }
        }
        $assetColumns = db()->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assets'")->fetchAll(PDO::FETCH_COLUMN);
        $assetHave = array_flip(array_map('strtolower', $assetColumns));
        $assetAdd = ['record_number' => 'VARCHAR(100) NULL', 'computer_type' => 'VARCHAR(100) NULL', 'plaque_number' => 'VARCHAR(100) NULL', 'person_number' => 'VARCHAR(100) NULL', 'city' => 'VARCHAR(100) NULL'];
        foreach ($assetAdd as $name => $definition) {
            if (!isset($assetHave[strtolower($name)])) {
                db()->exec('ALTER TABLE assets ADD COLUMN `' . $name . '` ' . $definition);
            }
        }
        $done = true;
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            try {
                system_log('error', 'asset_profile_schema', $exception->getMessage());
            } catch (Throwable) {
            }
        }
        throw new RuntimeException(
            'ساختار پایگاه‌دادهٔ شناسنامهٔ سیستم آماده نیست. کاربر پایگاه‌داده باید اجازهٔ CREATE/ALTER روی جدول‌های asset_profiles و assets داشته باشد؛ جزئیات خطا در گزارش سامانه ثبت شد.',
            0,
            $exception
        );
    }
}

function asset_profile_sections(): array
{
    static $sections = null;
    if ($sections !== null) {
        return $sections;
    }
    $sections = [
        'g_plaque' => [
            'title' => 'پلاک‌ها و پلمپ',
            'hint' => 'شماره پلاک تجهیزات و پلمپ کیس',
            'icon' => '🏷',
            'fields' => [
                ['key' => 'plaque_number', 'label' => 'شماره پلاک کیس', 'type' => 'text'],
                ['key' => 'plaque_monitor', 'label' => 'شماره پلاک مانیتور', 'type' => 'text'],
                ['key' => 'plaque_printer', 'label' => 'شماره پلاک پرینتر', 'type' => 'text'],
                ['key' => 'plaque_printer2', 'label' => 'شماره پلاک پرینتر ۲ (در صورت لزوم)', 'type' => 'text'],
                ['key' => 'plaque_scanner', 'label' => 'شماره پلاک اسکنر', 'type' => 'text'],
                ['key' => 'case_seal', 'label' => 'شماره پلمپ‌های کیس', 'type' => 'textarea', 'full' => true],
            ],
        ],
        'meta' => [
            'title' => 'مشخصات ثبت',
            'hint' => 'اطلاعات ثبت‌کننده شناسنامه',
            'icon' => '🗂',
            'fields' => [
                ['key' => 'record_number', 'label' => 'شماره شناسنامه', 'type' => 'text'],
                ['key' => 'registrar_name', 'label' => 'نام کاربر (ثبت‌کننده)', 'type' => 'text'],
                ['key' => 'registry_date', 'label' => 'تاریخ ثبت', 'type' => 'date'],
            ],
        ],
        'a_user' => [
            'title' => 'مشخصات محیط کاری و کاربر رایانه',
            'hint' => 'اطلاعات سازمانی، کاربر و سوابق مهارتی',
            'icon' => '👤',
            'fields' => [
                ['key' => 'unit_name', 'label' => 'نام واحد', 'type' => 'text'],
                ['key' => 'computer_type', 'label' => 'نوع رایانه', 'type' => 'text', 'auto' => true],
                ['key' => 'user_login', 'label' => 'نام کاربری', 'type' => 'text'],
                ['key' => 'owner_full_name', 'label' => 'نام و نام خانوادگی', 'type' => 'text'],
                ['key' => 'education', 'label' => 'رشته تحصیلی', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'شماره تماس', 'type' => 'text'],
                ['key' => 'degree', 'label' => 'مدرک تحصیلی', 'type' => 'select', 'options' => ['', 'زیر دیپلم', 'دیپلم', 'کاردانی', 'کارشناسی', 'کارشناسی ارشد', 'دکتری', 'سایر']],
                ['key' => 'skill_level', 'label' => 'تسلط کاربر', 'type' => 'select', 'options' => ['', 'مبتدی', 'متوسط', 'خوب', 'حرفه‌ای', 'نامشخص']],
                ['key' => 'personnel_number', 'label' => 'شماره پرسنلی', 'type' => 'text'],
                ['key' => 'training_skills', 'label' => 'دوره‌ها و مهارت‌های گذرانده‌شده کاربر در زمینه IT (ICDL، …) و دیگر مهارت‌ها', 'type' => 'textarea', 'full' => true],
                ['key' => 'secure_info', 'label' => 'در صورتی که اطلاعات مهم و محرمانه روی رایانه ذخیره می‌شود، توضیح مختصری دهید', 'type' => 'textarea', 'full' => true],
                ['key' => 'unit_software', 'label' => 'نرم‌افزارهای مرتبط با کل واحد (عنوان نرم‌افزار - کاربرد - شرکت سازنده)', 'type' => 'textarea', 'full' => true],
            ],
        ],
        'b_os' => [
            'title' => 'مشخصات سیستم‌عامل',
            'hint' => 'با دکمهٔ استخراج، خودکار پر می‌شود',
            'icon' => '🪟',
            'fields' => [
                ['key' => 'os_name', 'label' => 'سیستم‌عامل', 'type' => 'text', 'auto' => true],
                ['key' => 'hostname', 'label' => 'نام کامپیوتر', 'type' => 'text', 'auto' => true],
                ['key' => 'windows_serial', 'label' => 'شناسه ویندوز (SerialNumber؛ نه کلید محصول)', 'type' => 'text', 'auto' => true],
                ['key' => 'os_version', 'label' => 'نسخه', 'type' => 'text', 'auto' => true],
                ['key' => 'os_install_path', 'label' => 'محل نصب', 'type' => 'text', 'auto' => true],
                ['key' => 'user_count', 'label' => 'تعداد پروفایل‌های کاربری', 'type' => 'text', 'auto' => true],
                ['key' => 'os_install_date', 'label' => 'تاریخ نصب', 'type' => 'date', 'auto' => true],
            ],
        ],
        'c_hw' => [
            'title' => 'سخت‌افزار',
            'hint' => 'هارد، مادربرد، CPU، RAM، گرافیک و صدا',
            'icon' => '⚙',
            'fields' => [
                ['key' => 'disk1_model', 'label' => 'هارد دیسک ۱', 'type' => 'text', 'auto' => true],
                ['key' => 'disk1_capacity', 'label' => 'ظرفیت هارد ۱', 'type' => 'text', 'auto' => true],
                ['key' => 'disk1_serial', 'label' => 'سریال هارد ۱', 'type' => 'text', 'auto' => true],
                ['key' => 'disk2_model', 'label' => 'هارد دیسک ۲', 'type' => 'text', 'auto' => true],
                ['key' => 'disk2_capacity', 'label' => 'ظرفیت هارد ۲', 'type' => 'text', 'auto' => true],
                ['key' => 'disk2_serial', 'label' => 'سریال هارد ۲', 'type' => 'text', 'auto' => true],
                ['key' => 'motherboard_model', 'label' => 'سازنده مادربرد', 'type' => 'text', 'auto' => true],
                ['key' => 'motherboard_product', 'label' => 'مدل مادربرد', 'type' => 'text', 'auto' => true],
                ['key' => 'motherboard_serial', 'label' => 'سریال مادربرد', 'type' => 'text', 'auto' => true],
                ['key' => 'cpu_model', 'label' => 'CPU', 'type' => 'text', 'auto' => true],
                ['key' => 'cpu_serial', 'label' => 'شناسه پردازنده (ProcessorId)', 'type' => 'text', 'auto' => true],
                ['key' => 'cpu_cores', 'label' => 'هسته', 'type' => 'text', 'auto' => true],
                ['key' => 'ram1', 'label' => 'RAM ۱', 'type' => 'text', 'auto' => true],
                ['key' => 'ram2', 'label' => 'RAM ۲', 'type' => 'text', 'auto' => true],
                ['key' => 'ram_total', 'label' => 'ظرفیت کل RAM', 'type' => 'text', 'auto' => true],
                ['key' => 'gpu1_model', 'label' => 'کارت گرافیک ۱', 'type' => 'text', 'auto' => true],
                ['key' => 'gpu1_memory', 'label' => 'حافظه گرافیک ۱', 'type' => 'text', 'auto' => true],
                ['key' => 'gpu2_model', 'label' => 'کارت گرافیک ۲', 'type' => 'text', 'auto' => true],
                ['key' => 'gpu2_memory', 'label' => 'حافظه گرافیک ۲', 'type' => 'text', 'auto' => true],
                ['key' => 'audio', 'label' => 'کارت صدا', 'type' => 'text', 'auto' => true],
            ],
        ],
        'd_net' => [
            'title' => 'شبکه و مکان استقرار',
            'hint' => 'نشانی شبکه و وضعیت اتصال و قفل‌ها',
            'icon' => '🌐',
            'fields' => [
                ['key' => 'ip_address', 'label' => 'IP', 'type' => 'text', 'auto' => true],
                ['key' => 'mac_address', 'label' => 'مک آدرس', 'type' => 'text', 'auto' => true],
                ['key' => 'netcard', 'label' => 'کارت شبکه', 'type' => 'text', 'auto' => true],
                ['key' => 'net_node', 'label' => 'شماره نود شبکه', 'type' => 'text'],
                ['key' => 'connected_internet', 'label' => 'متصل به اینترنت', 'type' => 'bool'],
                ['key' => 'connected_network', 'label' => 'متصل به شبکه', 'type' => 'bool'],
                ['key' => 'lock_usb', 'label' => 'قفل شده USB', 'type' => 'bool'],
                ['key' => 'lock_case', 'label' => 'پلمپ شده کیس', 'type' => 'bool'],
                ['key' => 'lock_cd_dvd', 'label' => 'قفل CD-DVD R/W', 'type' => 'bool'],
            ],
        ],
        'e_peripheral' => [
            'title' => 'تجهیزات جانبی',
            'hint' => 'مانیتور، پرینتر، اسکنر، کیبورد و ماوس',
            'icon' => '🖨',
            'fields' => [
                ['key' => 'monitor_model', 'label' => 'مدل مانیتور', 'type' => 'text', 'auto' => true],
                ['key' => 'printer_model', 'label' => 'مدل پرینتر', 'type' => 'text', 'auto' => true],
                ['key' => 'scanner_model', 'label' => 'مدل اسکنر', 'type' => 'text', 'auto' => true],
                ['key' => 'keyboard_model', 'label' => 'مدل صفحه‌کلید', 'type' => 'text', 'auto' => true],
                ['key' => 'mouse_model', 'label' => 'مدل ماوس', 'type' => 'text', 'auto' => true],
                ['key' => 'barcode_reader', 'label' => 'بارکدخوان', 'type' => 'text', 'auto' => true],
            ],
        ],
        'f_case' => [
            'title' => 'کیس، پاور و آنتی‌ویروس',
            'hint' => 'مشخصات بدنه و محافظت',
            'icon' => '🛡',
            'fields' => [
                ['key' => 'antivirus', 'label' => 'آنتی‌ویروس', 'type' => 'text', 'auto' => true],
                ['key' => 'power_model', 'label' => 'مدل پاور', 'type' => 'text'],
                ['key' => 'case_model', 'label' => 'مدل کیس', 'type' => 'text'],
            ],
        ],
        'h_ad' => [
            'title' => 'اطلاعات دامنه (Active Directory)',
            'hint' => 'با اسکن دامنه به‌صورت خودکار پر می‌شود',
            'icon' => '🏢',
            'fields' => [
                ['key' => 'ad_dn', 'label' => 'مسیر در دامنه (DN / OU)', 'type' => 'text', 'full' => true],
                ['key' => 'ad_os', 'label' => 'سیستم‌عامل طبق دامنه', 'type' => 'text', 'auto' => true],
                ['key' => 'ad_os_version', 'label' => 'نسخه طبق دامنه', 'type' => 'text', 'auto' => true],
                ['key' => 'ad_last_logon', 'label' => 'آخرین ورود در دامنه', 'type' => 'date', 'auto' => true],
                ['key' => 'ad_last_seen', 'label' => 'آخرین مشاهدهٔ آنلاین', 'type' => 'date', 'auto' => true],
                ['key' => 'ad_scanned_at', 'label' => 'آخرین بررسی شبکه', 'type' => 'date', 'auto' => true],
                ['key' => 'ad_online', 'label' => 'در شبکه آنلاین است', 'type' => 'bool'],
                ['key' => 'ad_description', 'label' => 'توضیحات دامنه', 'type' => 'text', 'full' => true, 'auto' => true],
            ],
        ],
    ];
    return $sections;
}

function asset_profile_all_columns(): array
{
    static $columns = null;
    if ($columns !== null) {
        return $columns;
    }
    $columns = [];
    foreach (asset_profile_sections() as $section) {
        foreach ($section['fields'] as $field) {
            $columns[$field['key']] = $field['type'];
        }
    }
    return $columns;
}

function asset_profile_field(string $key): ?array
{
    foreach (asset_profile_sections() as $section) {
        foreach ($section['fields'] as $field) {
            if ($field['key'] === $key) {
                return $field;
            }
        }
    }
    return null;
}

function asset_profile_export_columns(): array
{
    static $columns = null;
    if ($columns !== null) {
        return $columns;
    }
    $columns = [];
    foreach (asset_profile_sections() as $sectionKey => $section) {
        foreach ($section['fields'] as $field) {
            $columns[] = [
                'key' => $field['key'],
                'label' => $field['label'],
                'type' => $field['type'] ?? 'text',
                'section' => (string) $section['title'],
            ];
        }
    }
    return $columns;
}

function asset_profile_export_value(array $field, mixed $value): string
{
    $type = $field['type'];
    if ($value === null || $value === '') {
        return '';
    }
    if ($type === 'bool') {
        return (int) $value === 1 ? 'بله' : 'خیر';
    }
    if ($type === 'date') {
        return jalali_date((string) $value, false);
    }
    return (string) $value;
}

function asset_profile_fetch(int $assetId): array
{
    asset_profile_ensure_schema();
    $defaults = array_fill_keys(array_keys(asset_profile_all_columns()), null);
    $query = db()->prepare('SELECT * FROM asset_profiles WHERE asset_id = ? LIMIT 1');
    $query->execute([$assetId]);
    $row = $query->fetch();
    return $row ? array_merge($defaults, $row) : $defaults;
}

function asset_profile_fill_from_asset(array $profile, array $asset): array
{
    $hardware = json_decode((string) ($asset['hardware_json'] ?? ''), true);
    $peripherals = json_decode((string) ($asset['peripherals_json'] ?? ''), true);
    $hardware = is_array($hardware) ? $hardware : [];
    $peripherals = is_array($peripherals) ? $peripherals : [];
    $derived = asset_profile_auto_values([
        'hostname' => $asset['hostname'] ?? '',
        'computer_type' => $asset['computer_type'] ?? '',
        'operating_system' => $asset['operating_system'] ?? '',
        'os_serial' => $asset['os_serial'] ?? '',
        'ip_address' => $asset['ip_address'] ?? '',
        'mac_address' => $asset['mac_address'] ?? '',
        'cpu' => $asset['cpu'] ?? '',
        'memory_mb' => $asset['memory_mb'] ?? 0,
        'domain_username' => $asset['domain_username'] ?? '',
        'antivirus' => $asset['antivirus'] ?? '',
        'hardware' => $hardware,
        'peripherals' => $peripherals,
    ]);
    $fieldProvenance = is_array($derived['_field_provenance'] ?? null) ? $derived['_field_provenance'] : [];
    unset($derived['_field_provenance'], $derived['_clear_fields']);
    $assetFallbacks = [
        'record_number' => trim((string) ($asset['record_number'] ?? '')) !== '' ? $asset['record_number'] : ($asset['asset_tag'] ?? null),
        'registry_date' => $asset['created_at'] ?? null,
        'unit_name' => $asset['department_name'] ?? null,
        'computer_type' => $asset['computer_type'] ?? null,
        'user_login' => $asset['domain_username'] ?? null,
        'owner_full_name' => $asset['owner_name'] ?? null,
        'phone' => $asset['phone'] ?? null,
        'personnel_number' => trim((string) ($asset['person_number'] ?? '')) !== '' ? $asset['person_number'] : ($asset['employee_number'] ?? null),
        'plaque_number' => $asset['plaque_number'] ?? null,
        'os_name' => $asset['operating_system'] ?? null,
        'hostname' => $asset['hostname'] ?? null,
        'windows_serial' => $asset['os_serial'] ?? null,
        'os_install_date' => $asset['os_install_date'] ?? null,
        'user_count' => $asset['registered_users'] ?? null,
        'cpu_model' => $asset['cpu'] ?? null,
        'ram_total' => !empty($asset['memory_mb']) ? ((int) $asset['memory_mb'] . ' MB') : null,
        'ip_address' => $asset['ip_address'] ?? null,
        'mac_address' => $asset['mac_address'] ?? null,
        'netcard' => $asset['network_card'] ?? null,
        'audio' => $asset['sound_card'] ?? null,
        'antivirus' => $asset['antivirus'] ?? null,
    ];
    $fallbacks = $derived;
    foreach ($assetFallbacks as $key => $value) {
        if ($value !== null && $value !== '') {
            $fallbacks[$key] = $value;
        }
    }
    foreach ($fallbacks as $key => $value) {
        if (($profile[$key] ?? null) === null || $profile[$key] === '') {
            $profile[$key] = $value;
        }
    }
    $profile['_field_provenance'] = $fieldProvenance;
    return $profile;
}

/** Summarize current collector status separately from values retained from older scans. */
function asset_profile_collection_status_summary(array $asset): array
{
    $hardware = json_decode((string) ($asset['hardware_json'] ?? ''), true);
    $hardware = is_array($hardware) ? $hardware : [];
    $status = is_array($hardware['collection_status'] ?? null)
        ? $hardware['collection_status']
        : (is_array($asset['collection_status'] ?? null) ? $asset['collection_status'] : []);
    $labels = [
        'root/cimv2|Win32_ComputerSystem' => 'مشخصات پایهٔ رایانه',
        'root/cimv2|Win32_BIOS' => 'BIOS و سریال دستگاه',
        'root/cimv2|Win32_Processor' => 'پردازنده',
        'root/cimv2|Win32_OperatingSystem' => 'سیستم‌عامل',
        'root/cimv2|Win32_PhysicalMemory' => 'ماژول‌های RAM',
        'root/cimv2|Win32_DiskDrive' => 'دیسک‌های فیزیکی',
        'root/cimv2|Win32_VideoController' => 'کارت گرافیک',
        'root/cimv2|Win32_NetworkAdapterConfiguration' => 'پیکربندی شبکه',
        'root/cimv2|Win32_Printer' => 'چاپگرها',
        'root/cimv2|Win32_PnPEntity' => 'تجهیزات Plug and Play',
        'root/wmi|WmiMonitorID' => 'شناسهٔ مانیتورها',
        'root/Microsoft/Windows/Storage|MSFT_PhysicalDisk' => 'اطلاعات ذخیره‌سازهای پیشرفته',
        'root/Microsoft/Windows/Storage|MSFT_StorageReliabilityCounter' => 'شاخص‌های سلامت ذخیره‌ساز',
        'root/wmi|MSStorageDriver_FailurePredictStatus' => 'وضعیت SMART',
    ];
    $issues = [];
    foreach ($status as $key => $result) {
        if (!is_array($result)) {
            continue;
        }
        $ok = ($result['ok'] ?? false) === true;
        $count = max(0, (int) ($result['count'] ?? 0));
        if (($result['attempted'] ?? true) === false || !array_key_exists('ok', $result) || $result['ok'] === null) {
            $issues[] = ['name' => $labels[$key] ?? (string) $key, 'state' => 'not_attempted'];
        } elseif ($ok === false) {
            $issues[] = [
                'name' => $labels[$key] ?? (string) $key,
                'state' => 'failed',
                'last_success_at' => (string) ($result['last_success_at'] ?? ''),
                'last_success_source' => (string) ($result['last_success_source'] ?? ''),
            ];
        } elseif ($ok === true && $count === 0) {
            $issues[] = ['name' => $labels[$key] ?? (string) $key, 'state' => 'empty'];
        }
    }
    return $issues;
}

/**
 * واکشی گروهی شناسنامه‌ها با یک کوئری (جلوگیری از N+1 در لیست/خروجی).
 * @param int[] $assetIds
 * @return array<int, array>
 */
/**
 * Fill legacy/mirrored profile fields from the canonical asset row without overwriting
 * values stored specifically in asset_profiles. This keeps older records visible.
 */
function asset_profile_fill_from_asset(array $profile, array $asset): array
{
    $hardware = json_decode((string) ($asset['hardware_json'] ?? ''), true);
    $peripherals = json_decode((string) ($asset['peripherals_json'] ?? ''), true);
    $hardware = is_array($hardware) ? $hardware : [];
    $peripherals = is_array($peripherals) ? $peripherals : [];
    $derived = asset_profile_auto_values([
        'hostname' => $asset['hostname'] ?? '',
        'computer_type' => $asset['computer_type'] ?? '',
        'operating_system' => $asset['operating_system'] ?? '',
        'os_serial' => $asset['os_serial'] ?? '',
        'ip_address' => $asset['ip_address'] ?? '',
        'mac_address' => $asset['mac_address'] ?? '',
        'cpu' => $asset['cpu'] ?? '',
        'memory_mb' => $asset['memory_mb'] ?? 0,
        'domain_username' => $asset['domain_username'] ?? '',
        'antivirus' => $asset['antivirus'] ?? '',
        'hardware' => $hardware,
        'peripherals' => $peripherals,
    ]);
    $fieldProvenance = is_array($derived['_field_provenance'] ?? null) ? $derived['_field_provenance'] : [];
    unset($derived['_field_provenance'], $derived['_clear_fields']);
    $assetFallbacks = [
        'record_number' => trim((string) ($asset['record_number'] ?? '')) !== '' ? $asset['record_number'] : ($asset['asset_tag'] ?? null),
        'registry_date' => $asset['created_at'] ?? null,
        'unit_name' => $asset['department_name'] ?? null,
        'computer_type' => $asset['computer_type'] ?? null,
        'user_login' => $asset['domain_username'] ?? null,
        'owner_full_name' => $asset['owner_name'] ?? null,
        'phone' => $asset['phone'] ?? null,
        'personnel_number' => trim((string) ($asset['person_number'] ?? '')) !== '' ? $asset['person_number'] : ($asset['employee_number'] ?? null),
        'plaque_number' => $asset['plaque_number'] ?? null,
        'os_name' => $asset['operating_system'] ?? null,
        'hostname' => $asset['hostname'] ?? null,
        'windows_serial' => $asset['os_serial'] ?? null,
        'os_install_date' => $asset['os_install_date'] ?? null,
        'user_count' => $asset['registered_users'] ?? null,
        'cpu_model' => $asset['cpu'] ?? null,
        'ram_total' => !empty($asset['memory_mb']) ? ((int) $asset['memory_mb'] . ' MB') : null,
        'ip_address' => $asset['ip_address'] ?? null,
        'mac_address' => $asset['mac_address'] ?? null,
        'netcard' => $asset['network_card'] ?? null,
        'audio' => $asset['sound_card'] ?? null,
        'antivirus' => $asset['antivirus'] ?? null,
    ];
    $fallbacks = $derived;
    foreach ($assetFallbacks as $key => $value) {
        if ($value !== null && $value !== '') {
            $fallbacks[$key] = $value;
        }
    }
    foreach ($fallbacks as $key => $value) {
        if (($profile[$key] ?? null) === null || $profile[$key] === '') {
            $profile[$key] = $value;
        }
    }
    $profile['_field_provenance'] = $fieldProvenance;
    return $profile;
}

/** Summarize current collector status separately from values retained from older scans. */
function asset_profile_collection_status_summary(array $asset): array
{
    $hardware = json_decode((string) ($asset['hardware_json'] ?? ''), true);
    $hardware = is_array($hardware) ? $hardware : [];
    $status = is_array($hardware['collection_status'] ?? null)
        ? $hardware['collection_status']
        : (is_array($asset['collection_status'] ?? null) ? $asset['collection_status'] : []);
    $labels = [
        'root/cimv2|Win32_ComputerSystem' => 'مشخصات پایهٔ رایانه',
        'root/cimv2|Win32_BIOS' => 'BIOS و سریال دستگاه',
        'root/cimv2|Win32_Processor' => 'پردازنده',
        'root/cimv2|Win32_OperatingSystem' => 'سیستم‌عامل',
        'root/cimv2|Win32_PhysicalMemory' => 'ماژول‌های RAM',
        'root/cimv2|Win32_DiskDrive' => 'دیسک‌های فیزیکی',
        'root/cimv2|Win32_VideoController' => 'کارت گرافیک',
        'root/cimv2|Win32_NetworkAdapterConfiguration' => 'پیکربندی شبکه',
        'root/cimv2|Win32_Printer' => 'چاپگرها',
        'root/cimv2|Win32_PnPEntity' => 'تجهیزات Plug and Play',
        'root/wmi|WmiMonitorID' => 'شناسهٔ مانیتورها',
        'root/Microsoft/Windows/Storage|MSFT_PhysicalDisk' => 'اطلاعات ذخیره‌سازهای پیشرفته',
        'root/Microsoft/Windows/Storage|MSFT_StorageReliabilityCounter' => 'شاخص‌های سلامت ذخیره‌ساز',
        'root/wmi|MSStorageDriver_FailurePredictStatus' => 'وضعیت SMART',
    ];
    $issues = [];
    foreach ($status as $key => $result) {
        if (!is_array($result)) {
            continue;
        }
        $ok = ($result['ok'] ?? false) === true;
        $count = max(0, (int) ($result['count'] ?? 0));
        if (($result['attempted'] ?? true) === false || !array_key_exists('ok', $result) || $result['ok'] === null) {
            $issues[] = ['name' => $labels[$key] ?? (string) $key, 'state' => 'not_attempted'];
        } elseif ($ok === false) {
            $issues[] = [
                'name' => $labels[$key] ?? (string) $key,
                'state' => 'failed',
                'last_success_at' => (string) ($result['last_success_at'] ?? ''),
                'last_success_source' => (string) ($result['last_success_source'] ?? ''),
            ];
        } elseif ($ok === true && $count === 0) {
            $issues[] = ['name' => $labels[$key] ?? (string) $key, 'state' => 'empty'];
        }
    }
    return $issues;
}

/**
 * واکشی گروهی شناسنامه‌ها با یک کوئری (جلوگیری از N+1 در لیست/خروجی).
 * @param int[] $assetIds
 * @return array<int, array>
 */
function asset_profile_map(array $assetIds): array
{
    $defaults = array_fill_keys(array_keys(asset_profile_all_columns()), null);
    $result = [];
    $ids = array_values(array_unique(array_filter(array_map('intval', $assetIds))));
    if (!$ids) {
        return $result;
    }
    asset_profile_ensure_schema();
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $query = db()->prepare('SELECT * FROM asset_profiles WHERE asset_id IN (' . $marks . ')');
    $query->execute($ids);
    foreach ($query->fetchAll() as $row) {
        $result[(int) $row['asset_id']] = array_merge($defaults, $row);
    }
    foreach ($ids as $id) {
        if (!isset($result[$id])) {
            $result[$id] = $defaults;
        }
    }
    return $result;
}

function asset_profile_input_value(array $field, mixed $value): string
{
    if ($value === null) {
        return '';
    }
    if (($field['type'] ?? '') === 'date') {
        return jalali_date((string) $value, false);
    }
    return (string) $value;
}

function asset_profile_render_fields(array $keys, array $profile): string
{
    $html = '';
    foreach ($keys as $key) {
        $field = asset_profile_field($key);
        if ($field === null) {
            continue;
        }
        $type = $field['type'] ?? 'text';
        $value = asset_profile_input_value($field, $profile[$key] ?? null);
        $full = !empty($field['full']) ? ' full' : '';
        $html .= '<label class="profile-field' . $full . '">' . e($field['label']);
        if (!empty($field['auto'])) {
            $provenance = is_array($profile['_field_provenance'][$key] ?? null) ? $profile['_field_provenance'][$key] : [];
            foreach ($provenance as $sourceInfo) {
                if (!is_array($sourceInfo)) { continue; }
                $collector = (string) ($sourceInfo['collector'] ?? '');
                $collectorLabel = str_contains($collector, '|') ? substr($collector, strpos($collector, '|') + 1) : $collector;
                $stateLabels = ['success' => 'پرس‌وجوی اخیر پاسخ داد', 'empty' => 'پرس‌وجوی موفق؛ بدون ردیف', 'failed' => 'پرس‌وجوی اخیر ناموفق', 'not_attempted' => 'در روش اخیر پرس‌وجو نشد'];
                $stateLabel = $stateLabels[(string) ($sourceInfo['state'] ?? '')] ?? 'وضعیت نامشخص';
                $attemptedAt = trim((string) ($sourceInfo['last_attempt_at'] ?? ''));
                $successAt = trim((string) ($sourceInfo['last_success_at'] ?? ''));
                $timeLabel = $attemptedAt !== '' ? ' — تلاش: ' . persian_date($attemptedAt) . ' ' . date('H:i', strtotime($attemptedAt)) : '';
                if (($sourceInfo['state'] ?? '') === 'failed' && $successAt !== '') {
                    $timeLabel .= '؛ آخرین پاسخ موفق: ' . persian_date($successAt) . ' ' . date('H:i', strtotime($successAt));
                }
                $html .= '<small class="muted profile-field-provenance" title="وضعیت پرس‌وجوی منبع؛ تضمین نمی‌کند مقدار فعلی در همان زمان تغییر کرده باشد">منبع ' . e($collectorLabel) . ': ' . e($stateLabel . $timeLabel) . '</small>';
            }
        }
        if ($type === 'textarea') {
            $html .= '<textarea name="profile[' . $key . ']" rows="2">' . e($value) . '</textarea>';
        } elseif ($type === 'bool') {
            $checked = (int) ($profile[$key] ?? 0) === 1 ? ' checked' : '';
            $html .= '<span class="switch-row"><input type="checkbox" name="profile[' . $key . ']" value="1"' . $checked . '> فعال</span>';
        } elseif ($type === 'select') {
            $html .= '<select name="profile[' . $key . ']">';
            foreach (($field['options'] ?? []) as $option) {
                $selected = (string) $profile[$key] === (string) $option ? ' selected' : '';
                $html .= '<option value="' . e((string) $option) . '"' . $selected . '>' . e($option === '' ? 'انتخاب کنید' : (string) $option) . '</option>';
            }
            $html .= '</select>';
        } else {
            $inputType = $type === 'number' ? 'number' : 'text';
            $placeholder = $type === 'date' ? ' placeholder="۱۴۰۵/۰۱/۰۱"' : '';
            $html .= '<input type="' . $inputType . '" name="profile[' . $key . ']" value="' . e($value) . '"' . $placeholder . '>';
        }
        $html .= '</label>';
    }
    return $html;
}

function asset_profile_render_sections(array $profile): string
{
    $html = '';
    foreach (asset_profile_sections() as $key => $section) {
        $fieldKeys = array_map(static fn (array $field): string => $field['key'], $section['fields']);
        $html .= '<section class="card form-card profile-card" data-section="' . $key . '"><div class="form-section-head"><h2>' . $section['icon'] . ' ' . e($section['title']) . '</h2><span class="muted">' . e($section['hint']) . '</span></div><div class="form-grid profile-grid">' . asset_profile_render_fields($fieldKeys, $profile) . '</div></section>';
    }
    return $html;
}

function asset_profile_save(int $assetId, array $input, int $userId): void
{
    if ($assetId <= 0) {
        return;
    }
    asset_profile_ensure_schema();
    $input = is_array($input['profile'] ?? null) ? $input['profile'] : [];
    $columns = asset_profile_all_columns();
    $values = [];
    foreach ($columns as $key => $type) {
        $raw = $input[$key] ?? null;
        if ($type === 'bool') {
            $values[$key] = !empty($raw) ? 1 : 0;
            continue;
        }
        $raw = is_string($raw) ? trim($raw) : '';
        if ($type === 'date') {
            $values[$key] = $raw !== '' ? jalali_input_to_gregorian($raw) : null;
            continue;
        }
        $values[$key] = $raw !== '' ? $raw : null;
    }
    $values['updated_by'] = $userId > 0 ? $userId : null;
    $columnNames = array_keys($values);
    $set = implode(', ', array_map(static fn (string $column): string => $column . ' = ?', $columnNames));
    $existing = db()->prepare('SELECT asset_id FROM asset_profiles WHERE asset_id = ? LIMIT 1');
    $existing->execute([$assetId]);
    if ($existing->fetchColumn()) {
        db()->prepare('UPDATE asset_profiles SET ' . $set . ', updated_at = NOW() WHERE asset_id = ?')->execute([...array_values($values), $assetId]);
    } else {
        $insertColumns = array_merge(['asset_id'], $columnNames);
        db()->prepare('INSERT INTO asset_profiles (' . implode(', ', $insertColumns) . ') VALUES (' . implode(', ', array_fill(0, count($insertColumns), '?')) . ')')->execute([$assetId, ...array_values($values)]);
    }
    $sync = [
        'record_number' => $values['record_number'] ?? null,
        'computer_type' => $values['computer_type'] ?? null,
        'plaque_number' => $values['plaque_number'] ?? null,
        'person_number' => $values['personnel_number'] ?? null,
        'operating_system' => $values['os_name'] ?? null,
        'ip_address' => $values['ip_address'] ?? null,
        'mac_address' => $values['mac_address'] ?? null,
        'antivirus' => $values['antivirus'] ?? null,
        'os_serial' => $values['windows_serial'] ?? null,
    ];
    $syncSet = [];
    $syncParams = [];
    foreach ($sync as $column => $value) {
        $syncSet[] = $column . ' = ?';
        $syncParams[] = $value;
    }
    $syncParams[] = $assetId;
    db()->prepare('UPDATE assets SET ' . implode(', ', $syncSet) . ' WHERE id = ?')->execute($syncParams);
}

function asset_profile_printer_port_is_physical(string $port): bool
{
    if ($port === '') {
        return false;
    }
    foreach (['lpt', 'com', 'usb', 'dot4', 'wsd', 'ip_', 'tcp', 'npi', '\\\\'] as $prefix) {
        if (str_starts_with($port, $prefix)) {
            return true;
        }
    }
    return (bool) preg_match('/^\d{1,3}(\.\d{1,3}){3}/', $port);
}

function asset_profile_is_virtual_printer(array $row): bool
{
    $name = strtolower(trim((string) inventory_value($row, ['Name', 'FriendlyName', 'model'], '')));
    $driver = strtolower(trim((string) inventory_value($row, ['DriverName', 'driver'], '')));
    $port = strtolower(trim((string) inventory_value($row, ['PortName', 'port'], '')));

    foreach (['portprompt:', 'nul:', 'shrfa', 'file:', 'microsoft', 'pdf', 'xps', 'fax', 'virtual', 'redirected'] as $virtualPort) {
        if ($port !== '' && str_contains($port, $virtualPort)) {
            return true;
        }
    }
    if (asset_profile_printer_port_is_physical($port)) {
        return false;
    }
    $haystack = trim($name . ' ' . $driver);
    foreach ([
        'onenote', 'evernote', 'print to', 'print-to', 'microsoft print', 'print to pdf',
        'pdf', 'xps', 'fax', 'document writer', 'universal document', 'paperless',
        'pdfcreator', 'cutepdf', 'adobe pdf', 'acrobat', 'foxit', 'nitro', 'pdf24',
        'bullzip', 'dopdf', 'primopdf', 'novapdf', 'pdf-xchange', 'pdf995', 'pdfactory',
        'snagit', 'amyuni', 'webex', 'skype', 'virtual', 'redirected', 'image writer',
        'microsoft xps', 'microsoft shared fax', 'root document printer', 'notes writer',
    ] as $needle) {
        if ($haystack !== '' && str_contains($haystack, $needle)) {
            return true;
        }
    }
    if (str_contains($driver, 'microsoft') || str_contains($name, 'microsoft')) {
        return true;
    }
    return false;
}

function asset_profile_printer_kind(array $row): string
{
    $port = strtolower(trim((string) inventory_value($row, ['PortName', 'port'], '')));
    $network = (int) inventory_value($row, ['Network'], 0) === 1;
    $shared = (int) inventory_value($row, ['Shared'], 0) === 1;
    $shareName = trim((string) inventory_value($row, ['ShareName'], ''));
    if ($network || $shared || $shareName !== '' || str_starts_with($port, '\\\\') || str_starts_with($port, 'ip_') || str_starts_with($port, 'tcp') || str_starts_with($port, 'wsd') || str_starts_with($port, 'npi') || preg_match('/^\d{1,3}(\.\d{1,3}){3}/', $port)) {
        return 'network';
    }
    return 'local';
}

function asset_profile_real_printers(mixed $printers): array
{
    $rows = [];
    foreach (inventory_rows($printers) as $row) {
        if (!is_array($row) || asset_profile_is_virtual_printer($row)) {
            continue;
        }
        $model = trim((string) inventory_value($row, ['Name', 'FriendlyName', 'model'], ''));
        if ($model === '') {
            continue;
        }
        $rows[] = $row;
    }
    $order = ['local' => 0, 'network' => 1];
    usort($rows, static function (array $a, array $b) use ($order): int {
        return ($order[asset_profile_printer_kind($a)] ?? 2) <=> ($order[asset_profile_printer_kind($b)] ?? 2);
    });
    return $rows;
}

function asset_profile_auto_values(array $inventory): array
{
    $hardware = is_array($inventory['hardware'] ?? null) ? $inventory['hardware'] : [];
    $os = is_array($hardware['operating_system'] ?? null) ? $hardware['operating_system'] : [];
    $processor = is_array($hardware['processor'] ?? null) ? $hardware['processor'] : [];
    $motherboard = is_array($hardware['motherboard'] ?? null) ? $hardware['motherboard'] : [];
    $peripherals = is_array($inventory['peripherals'] ?? null) ? $inventory['peripherals'] : [];
    $disks = array_values(array_filter(inventory_rows($hardware['physical_disks'] ?? []), 'is_array'));
    usort($disks, static function (array $a, array $b): int {
        $aIndex = (int) inventory_value($a, ['Index', 'index'], PHP_INT_MAX);
        $bIndex = (int) inventory_value($b, ['Index', 'index'], PHP_INT_MAX);
        return ($aIndex <=> $bIndex) ?: strcasecmp((string) inventory_value($a, ['Model', 'model'], ''), (string) inventory_value($b, ['Model', 'model'], ''));
    });
    $memory = array_values(array_filter(inventory_rows($hardware['memory_modules'] ?? []), 'is_array'));
    usort($memory, static function (array $a, array $b): int {
        return strcasecmp((string) inventory_value($a, ['DeviceLocator', 'BankLabel', 'PartNumber'], ''), (string) inventory_value($b, ['DeviceLocator', 'BankLabel', 'PartNumber'], ''));
    });
    $graphics = array_values(array_filter(inventory_rows($hardware['graphics'] ?? []), 'is_array'));
    usort($graphics, static fn (array $a, array $b): int => strcasecmp((string) inventory_value($a, ['Name', 'model'], ''), (string) inventory_value($b, ['Name', 'model'], '')));
    $sound = inventory_rows($hardware['sound'] ?? []);
    $network = inventory_rows($hardware['network_adapters'] ?? []);
    $printers = asset_profile_real_printers($peripherals['printers'] ?? []);
    $first = static function (array $rows, array $keys): string {
        foreach ($rows as $row) {
            if (!is_array($row)) { continue; }
            $value = trim((string) inventory_value($row, $keys, ''));
            if ($value !== '') { return $value; }
        }
        return '';
    };
    $diskAt = static function (int $index, array $keys) use ($disks): string {
        if (!isset($disks[$index]) || !is_array($disks[$index])) { return ''; }
        return trim((string) inventory_value($disks[$index], $keys, ''));
    };
    $byteLabel = static function (mixed $value): string {
        $bytes = (int) $value;
        if ($bytes <= 0) { return ''; }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        return round($bytes / (1024 ** $power), 1) . ' ' . $units[$power];
    };
    $diskCapacity = static function (int $index) use ($disks, $byteLabel): string {
        if (!isset($disks[$index]) || !is_array($disks[$index])) { return ''; }
        return $byteLabel(inventory_value($disks[$index], ['Size', 'size'], 0));
    };
    $memAt = static function (int $index) use ($memory, $byteLabel): string {
        if (!isset($memory[$index]) || !is_array($memory[$index])) { return ''; }
        $row = $memory[$index];
        $name = trim((string) inventory_value($row, ['Manufacturer', 'manufacturer'], '') . ' ' . (string) inventory_value($row, ['PartNumber', 'model'], ''));
        $capacity = $byteLabel(inventory_value($row, ['Capacity', 'capacity'], 0));
        return trim($name . ($capacity !== '' ? ' ' . $capacity : ''));
    };
    $gpuAt = static function (int $index, array $keys) use ($graphics): string {
        if (!isset($graphics[$index]) || !is_array($graphics[$index])) { return ''; }
        return trim((string) inventory_value($graphics[$index], $keys, ''));
    };
    $osInstallDate = null;
    $rawInstall = (string) inventory_value($os, ['InstallDate', 'install_date'], '');
    if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $rawInstall, $matches)) {
        $osInstallDate = $matches[1] . '-' . $matches[2] . '-' . $matches[3] . ' 00:00:00';
    }
    $localUsers = inventory_rows($inventory['os_users'] ?? []);
    $userProfiles = [];
    foreach ($localUsers as $localUser) {
        if (!is_array($localUser)) { continue; }
        $path = trim((string) inventory_value($localUser, ['LocalPath', 'local_path'], ''));
        $name = trim((string) inventory_value($localUser, ['Name', 'name'], ''));
        if ($name === '' && $path !== '') {
            $name = basename(str_replace('\\', '/', $path));
        }
        if ($name === '' || preg_match('/\$$/', $name) || in_array(strtolower($name), ['public', 'default', 'default user', 'all users', 'defaultuser0', 'systemprofile', 'localservice', 'networkservice'], true)) {
            continue;
        }
        $sid = trim((string) inventory_value($localUser, ['SID', 'sid'], ''));
        $userProfiles[$sid !== '' ? $sid : strtolower($name)] = true;
    }
    $userCount = count($userProfiles);
    $networkConfigurations = inventory_rows($hardware['network_configurations'] ?? $inventory['network_configurations'] ?? $inventory['network'] ?? []);
    if ($networkConfigurations === []) {
        $networkConfigurations = $network;
    }
    $usableIp = static function (mixed $candidate): string {
        $candidate = trim((string) $candidate);
        if ($candidate === '' || !filter_var($candidate, FILTER_VALIDATE_IP)) {
            return '';
        }
        if (in_array(strtolower($candidate), ['0.0.0.0', '::', '127.0.0.1', '::1'], true) || strncmp($candidate, '169.254.', 8) === 0 || stripos($candidate, 'fe80:') === 0) {
            return '';
        }
        return $candidate;
    };
    // Use the resolved target address only when it is present on a live adapter; otherwise
    // prefer a NIC with a default gateway. Never borrow the PHP server's SERVER_ADDR.
    $preferredIp = $usableIp($inventory['ip_address'] ?? '');
    $ip = '';
    $selectedNetwork = null;
    foreach ($networkConfigurations as $adapter) {
        if (!is_array($adapter)) { continue; }
        $adapterIps = array_map($usableIp, (array) inventory_value($adapter, ['IPAddress', 'ip_address'], []));
        if ($preferredIp !== '' && in_array($preferredIp, $adapterIps, true)) {
            $ip = $preferredIp;
            $selectedNetwork = $adapter;
            break;
        }
    }
    if (!is_array($selectedNetwork)) {
        $candidates = [];
        foreach ($networkConfigurations as $adapter) {
            if (!is_array($adapter)) { continue; }
            $adapterIps = array_values(array_filter(array_map($usableIp, (array) inventory_value($adapter, ['IPAddress', 'ip_address'], []))));
            if ($adapterIps === []) { continue; }
            $gateways = array_values(array_filter(array_map($usableIp, (array) inventory_value($adapter, ['DefaultIPGateway', 'default_gateway'], []))));
            foreach ($adapterIps as $candidate) {
                $candidates[] = ['ip' => $candidate, 'adapter' => $adapter, 'gateway' => $gateways !== []];
            }
        }
        usort($candidates, static fn (array $a, array $b): int => (int) $b['gateway'] <=> (int) $a['gateway']);
        if ($candidates !== []) {
            $ip = $candidates[0]['ip'];
            $selectedNetwork = $candidates[0]['adapter'];
        }
    }
    if ($ip === '') {
        $ip = $preferredIp;
    }
    if (!is_array($selectedNetwork)) {
        foreach ($networkConfigurations as $adapter) {
            if (is_array($adapter) && trim((string) inventory_value($adapter, ['MACAddress', 'mac_address'], '')) !== '') {
                $selectedNetwork = $adapter;
                break;
            }
        }
    }
    $mac = trim((string) inventory_value(is_array($selectedNetwork) ? $selectedNetwork : [], ['MACAddress', 'mac_address'], ''));
    if ($mac === '') {
        $mac = (string) ($inventory['mac_address'] ?? (is_array($network[0] ?? null) ? inventory_value($network[0], ['MACAddress'], '') : ''));
    }
    $primaryAdapter = null;
    foreach ($network as $adapter) {
        if (!is_array($adapter)) { continue; }
        if ($mac !== '' && strcasecmp(trim((string) inventory_value($adapter, ['MACAddress', 'mac_address'], '')), $mac) === 0) {
            $primaryAdapter = $adapter;
            break;
        }
    }
    if (!is_array($primaryAdapter)) {
        $primaryAdapter = is_array($network[0] ?? null) ? $network[0] : [];
    }
    $monitors = inventory_rows($peripherals['monitors'] ?? []);
    $extendedInventory = is_array($hardware['extended_inventory'] ?? null) ? $hardware['extended_inventory'] : [];
    $monitorIds = inventory_rows($extendedInventory['monitor_ids'] ?? []);
    $monitorIdName = '';
    foreach ($monitorIds as $monitorId) {
        if (!is_array($monitorId)) { continue; }
        $rawName = inventory_value($monitorId, ['UserFriendlyName', 'user_friendly_name'], '');
        if (is_array($rawName)) {
            $codes = array_values(array_filter(array_map('intval', $rawName), static fn (int $code): bool => $code > 0 && $code <= 65535));
            if ($codes !== []) {
                $packed = pack('v*', ...$codes);
                if (function_exists('mb_convert_encoding')) {
                    $rawName = @mb_convert_encoding($packed, 'UTF-8', 'UTF-16LE');
                } elseif (function_exists('iconv')) {
                    $rawName = @iconv('UTF-16LE', 'UTF-8//IGNORE', $packed);
                } else {
                    $rawName = implode('', array_map(static fn (int $code): string => $code <= 127 ? chr($code) : '', $codes));
                }
            } else {
                $rawName = '';
            }
        }
        $monitorIdName = trim(str_replace("\0", '', (string) $rawName));
        if ($monitorIdName !== '') { break; }
    }
    $scanners = inventory_rows($peripherals['scanners'] ?? []);
    $scanners = array_values(array_filter($scanners, static function ($device): bool {
        if (!is_array($device)) { return false; }
        $name = (string) inventory_value($device, ['FriendlyName', 'Name', 'Description'], '');
        return !preg_match('/camera|webcam|دوربین/i', $name);
    }));
    $barcodeReader = '';
    foreach (array_merge($scanners, inventory_rows($peripherals['keyboards'] ?? []), inventory_rows($peripherals['mice'] ?? [])) as $device) {
        if (!is_array($device)) { continue; }
        $deviceName = (string) inventory_value($device, ['FriendlyName', 'Name', 'Description'], '');
        if ($deviceName !== '' && preg_match('/barcode|بارکد/i', $deviceName)) {
            $barcodeReader = $deviceName;
            break;
        }
    }
    $values = [
        'computer_type' => (string) ($inventory['computer_type'] ?? ''),
        'hostname' => (string) ($inventory['hostname'] ?? ''),
        'os_name' => (string) ($inventory['operating_system'] ?? ''),
        'windows_serial' => (string) inventory_value($os, ['SerialNumber', 'serial_number'], ($inventory['os_serial'] ?? '')),
        // نام فعلی از Win32_ComputerSystem.UserName می‌آید؛ RegisteredUser و فهرست پروفایل‌ها هویت فعلی نیستند.
        'user_login' => (function () use ($inventory, $hardware): string {
            $candidates = [
                (string) inventory_value($hardware, ['user', 'domain_username'], ''),
                (string) ($inventory['domain_username'] ?? ''),
            ];
            foreach ($candidates as $candidate) {
                $candidate = trim((string) preg_replace('/^.*\\\\/', '', $candidate)); // DOMAIN\user → user
                if ($candidate !== '') {
                    return $candidate;
                }
            }
            return '';
        })(),
        'os_version' => (string) inventory_value($os, ['Version', 'version'], ''),
        'os_install_path' => (string) inventory_value($os, ['WindowsDirectory', 'SystemDrive'], ''),
        'user_count' => $userCount > 0 ? (string) $userCount : '',
        'os_install_date' => $osInstallDate,
        'disk1_model' => $diskAt(0, ['Model', 'model']),
        'disk1_capacity' => $diskCapacity(0),
        'disk1_serial' => $diskAt(0, ['SerialNumber', 'serial_number']),
        'disk2_model' => $diskAt(1, ['Model', 'model']),
        'disk2_capacity' => $diskCapacity(1),
        'disk2_serial' => $diskAt(1, ['SerialNumber', 'serial_number']),
        'motherboard_model' => (string) inventory_value($motherboard, ['Manufacturer', 'manufacturer'], ''),
        'motherboard_product' => (string) inventory_value($motherboard, ['Product', 'model'], ''),
        'motherboard_serial' => (string) inventory_value($motherboard, ['SerialNumber', 'serial_number'], ''),
        'cpu_model' => (string) ($inventory['cpu'] ?? ''),
        'cpu_serial' => (string) inventory_value($processor, ['ProcessorId', 'processor_id'], ''),
        'cpu_cores' => (string) inventory_value($processor, ['NumberOfCores', 'cores'], ''),
        'ram1' => $memAt(0),
        'ram2' => $memAt(1),
        'ram_total' => (int) ($inventory['memory_mb'] ?? 0) > 0 ? (int) $inventory['memory_mb'] . ' MB' : '',
        'gpu1_model' => $gpuAt(0, ['Name', 'model']),
        'gpu1_memory' => $byteLabel(inventory_value($graphics[0] ?? [], ['AdapterRAM', 'vram_mb'], 0)),
        'gpu2_model' => $gpuAt(1, ['Name', 'model']),
        'gpu2_memory' => $byteLabel(inventory_value($graphics[1] ?? [], ['AdapterRAM', 'vram_mb'], 0)),
        'audio' => $first($sound, ['Name', 'name']),
        'ip_address' => $ip,
        'mac_address' => $mac,
        'netcard' => $first(is_array($primaryAdapter) ? [$primaryAdapter] : [], ['Name', 'name', 'Description']),
        'connected_network' => $ip !== '' ? 1 : 0,
        'monitor_model' => $monitorIdName !== '' ? $monitorIdName : $first($monitors, ['Name', 'FriendlyName', 'model']),
        'printer_model' => isset($printers[0]) && is_array($printers[0]) ? trim((string) inventory_value($printers[0], ['Name', 'FriendlyName', 'model']) . (asset_profile_printer_kind($printers[0]) === 'network' ? ' (پرینتر شبکه/اشتراکی)' : ' (پرینتر فیزیکی)')) : '',
        'scanner_model' => $first($scanners, ['FriendlyName', 'Name', 'model']),
        'keyboard_model' => $first(inventory_rows($peripherals['keyboards'] ?? []), ['Name', 'FriendlyName', 'model']),
        'mouse_model' => $first(inventory_rows($peripherals['mice'] ?? []), ['Name', 'FriendlyName', 'model']),
        'barcode_reader' => $barcodeReader,
        'antivirus' => (string) ($inventory['antivirus'] ?? ''),
    ];
    $collectionStatus = is_array($inventory['collection_status'] ?? null)
        ? $inventory['collection_status']
        : (is_array($hardware['collection_status'] ?? null) ? $hardware['collection_status'] : []);
    $fieldQueries = [
        'root/cimv2|Win32_ComputerSystem' => ['ram_total', 'user_login'],
        'root/cimv2|Win32_BIOS' => ['windows_serial'],
        'root/cimv2|Win32_Processor' => ['cpu_model', 'cpu_serial', 'cpu_cores'],
        'root/cimv2|Win32_OperatingSystem' => ['os_name', 'os_version', 'os_install_path', 'windows_serial', 'os_install_date'],
        'root/cimv2|Win32_UserProfile' => ['user_count'],
        'root/cimv2|Win32_PhysicalMemory' => ['ram1', 'ram2'],
        'root/cimv2|Win32_DiskDrive' => ['disk1_model', 'disk1_capacity', 'disk1_serial', 'disk2_model', 'disk2_capacity', 'disk2_serial'],
        'root/cimv2|Win32_BaseBoard' => ['motherboard_model', 'motherboard_product', 'motherboard_serial'],
        'root/cimv2|Win32_VideoController' => ['gpu1_model', 'gpu1_memory', 'gpu2_model', 'gpu2_memory'],
        'root/cimv2|Win32_NetworkAdapterConfiguration' => ['ip_address', 'mac_address', 'netcard', 'connected_network'],
        'root/cimv2|Win32_Printer' => ['printer_model'],
        'root/cimv2|Win32_PnPEntity' => ['monitor_model', 'scanner_model', 'keyboard_model', 'mouse_model', 'barcode_reader'],
        'root/cimv2|Win32_DesktopMonitor' => ['monitor_model'],
        'root/wmi|WmiMonitorID' => ['monitor_model'],
        'root/SecurityCenter2|AntiVirusProduct' => ['antivirus'],
    ];
    foreach ($fieldQueries as $queryKey => $fieldKeys) {
        $queryStatus = $collectionStatus[$queryKey] ?? null;
        if (is_array($queryStatus) && ($queryStatus['ok'] ?? false) === true) {
            continue;
        }
        foreach ($fieldKeys as $fieldKey) {
            if (array_key_exists($fieldKey, $values) && ($values[$fieldKey] === '' || $values[$fieldKey] === 0 || $values[$fieldKey] === null)) {
                // A failed or legacy/unreported optional query must not erase a hand-entered profile value.
                $values[$fieldKey] = null;
            }
        }
    }
    $querySucceeded = static fn (string $key): bool => is_array($collectionStatus[$key] ?? null) && ($collectionStatus[$key]['ok'] ?? false) === true;
    $clearFields = [];
    if ($memory === [] && $querySucceeded('root/cimv2|Win32_PhysicalMemory')) {
        array_push($clearFields, 'ram1', 'ram2');
    }
    if ($disks === [] && $querySucceeded('root/cimv2|Win32_DiskDrive')) {
        array_push($clearFields, 'disk1_model', 'disk1_capacity', 'disk1_serial', 'disk2_model', 'disk2_capacity', 'disk2_serial');
    }
    if ($graphics === [] && $querySucceeded('root/cimv2|Win32_VideoController')) {
        array_push($clearFields, 'gpu1_model', 'gpu1_memory', 'gpu2_model', 'gpu2_memory');
    }
    if ($sound === [] && $querySucceeded('root/cimv2|Win32_SoundDevice')) {
        $clearFields[] = 'audio';
    }
    if ($networkConfigurations === [] && $querySucceeded('root/cimv2|Win32_NetworkAdapterConfiguration')) {
        array_push($clearFields, 'ip_address', 'mac_address', 'netcard', 'connected_network');
    }
    if (inventory_rows($peripherals['printers'] ?? []) === [] && $querySucceeded('root/cimv2|Win32_Printer')) {
        $clearFields[] = 'printer_model';
    }
    if ($scanners === [] && $querySucceeded('root/cimv2|Win32_PnPEntity')) {
        $clearFields[] = 'scanner_model';
    }
    if (inventory_rows($peripherals['keyboards'] ?? []) === [] && $querySucceeded('root/cimv2|Win32_PnPEntity')) {
        $clearFields[] = 'keyboard_model';
    }
    if (inventory_rows($peripherals['mice'] ?? []) === [] && $querySucceeded('root/cimv2|Win32_PnPEntity')) {
        $clearFields[] = 'mouse_model';
    }
    if ($userProfiles === [] && $querySucceeded('root/cimv2|Win32_UserProfile')) {
        $clearFields[] = 'user_count';
    }
    if ($monitorIds === [] && $monitors === [] && $querySucceeded('root/wmi|WmiMonitorID') && $querySucceeded('root/cimv2|Win32_DesktopMonitor')) {
        $clearFields[] = 'monitor_model';
    }
    if ($barcodeReader === '' && $querySucceeded('root/cimv2|Win32_PnPEntity')) {
        $clearFields[] = 'barcode_reader';
    }
    $values['_clear_fields'] = array_values(array_unique($clearFields));
    $fieldProvenance = [];
    foreach ($fieldQueries as $queryKey => $fieldKeys) {
        $queryStatus = is_array($collectionStatus[$queryKey] ?? null) ? $collectionStatus[$queryKey] : null;
        $state = $queryStatus === null || ($queryStatus['attempted'] ?? true) === false || !array_key_exists('ok', $queryStatus) || $queryStatus['ok'] === null
            ? 'not_attempted'
            : (($queryStatus['ok'] ?? false) === true ? ((int) ($queryStatus['count'] ?? 0) === 0 ? 'empty' : 'success') : 'failed');
        $entry = [
            'collector' => $queryKey,
            'state' => $state,
            'count' => isset($queryStatus['count']) ? (int) $queryStatus['count'] : null,
            'last_attempt_at' => (string) ($queryStatus['last_attempt_at'] ?? ''),
            'last_attempt_source' => (string) ($queryStatus['last_attempt_source'] ?? ''),
            'last_success_at' => (string) ($queryStatus['last_success_at'] ?? ''),
            'last_success_source' => (string) ($queryStatus['last_success_source'] ?? ''),
        ];
        foreach ($fieldKeys as $fieldKey) {
            $fieldProvenance[$fieldKey][] = $entry;
        }
    }
    $values['_field_provenance'] = $fieldProvenance;
    return $values;
}

function asset_profile_apply_auto(int $assetId, array $values, int $userId, bool $overwrite = false): int
{
    if ($assetId <= 0) {
        return 0;
    }
    asset_profile_ensure_schema();
    $columns = asset_profile_all_columns();
    $clearFields = is_array($values['_clear_fields'] ?? null) ? array_fill_keys(array_map('strval', $values['_clear_fields']), true) : [];
    unset($values['_clear_fields'], $values['_field_provenance']);
    $current = $overwrite ? [] : asset_profile_fetch($assetId);
    $filtered = [];
    foreach ($values as $key => $value) {
        if (!array_key_exists($key, $columns)) {
            continue;
        }
        if (!$overwrite && ($value === null || $value === '' || $value === 0)) {
            continue;
        }
        if ($overwrite && ($value === null || $value === '' || $value === 0) && !isset($clearFields[$key])) {
            continue;
        }
        if ($overwrite && isset($clearFields[$key]) && $value === '' && ($columns[$key] ?? '') === 'date') {
            $value = null;
        }
        if (!$overwrite) {
            $existingValue = $current[$key] ?? null;
            if ($existingValue !== null && $existingValue !== '' && (int) $existingValue !== 0) {
                continue;
            }
        }
        $filtered[$key] = $value;
    }
    if ($filtered === []) {
        return 0;
    }
    $existing = db()->prepare('SELECT asset_id FROM asset_profiles WHERE asset_id = ? LIMIT 1');
    $existing->execute([$assetId]);
    if ($existing->fetchColumn()) {
        $set = implode(', ', array_map(static fn (string $column): string => $column . ' = ?', array_keys($filtered)));
        db()->prepare('UPDATE asset_profiles SET ' . $set . ', updated_by = ?, updated_at = NOW() WHERE asset_id = ?')->execute([...array_values($filtered), $userId > 0 ? $userId : null, $assetId]);
    } else {
        $columns = array_merge(['asset_id', 'updated_by'], array_keys($filtered));
        $params = array_merge([$assetId, $userId > 0 ? $userId : null], array_values($filtered));
        db()->prepare('INSERT INTO asset_profiles (' . implode(', ', $columns) . ') VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')')->execute($params);
    }
    if (!empty($filtered['hostname'])) {
        db()->prepare('UPDATE assets SET hostname = ? WHERE id = ?')->execute([$filtered['hostname'], $assetId]);
    }
    if (!empty($filtered['computer_type'])) {
        db()->prepare('UPDATE assets SET computer_type = ? WHERE id = ?')->execute([$filtered['computer_type'], $assetId]);
    }
    $assetSync = [
        'operating_system' => $filtered['os_name'] ?? null,
        'ip_address' => $filtered['ip_address'] ?? null,
        'mac_address' => $filtered['mac_address'] ?? null,
        'antivirus' => $filtered['antivirus'] ?? null,
    ];
    $assetSet = [];
    $assetParams = [];
    foreach ($assetSync as $column => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $assetSet[] = $column . ' = ?';
        $assetParams[] = $value;
    }
    if ($assetSet !== []) {
        $assetParams[] = $assetId;
        db()->prepare('UPDATE assets SET ' . implode(', ', $assetSet) . ' WHERE id = ?')->execute($assetParams);
    }
    return count($filtered);
}
