<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;
require_once __DIR__ . '/asset-names.php';

function domain_scan_ensure_schema(): void
{
    // حذف رمزهای plaintext باقی‌مانده از نسخه‌های قدیمی؛ اعتبارنامهٔ جدید فقط per-run و رمز‌شده در نشست است.
    static $legacyCredentialsCleared = false;
    if (!$legacyCredentialsCleared) {
        $legacyCredentialsCleared = true;
        try {
            $clear = db()->prepare("UPDATE settings SET value = '' WHERE `key` IN ('domain_scan_username','domain_scan_password') AND COALESCE(value, '') <> ''");
            $clear->execute();
            if ($clear->rowCount() > 0 && function_exists('system_log')) {
                system_log('info', 'domain_scan', 'اعتبارنامهٔ ذخیره‌شدهٔ قدیمی اسکن دامنه پاک شد');
            }
        } catch (Throwable $exception) {
            app_error_log('[domain-scan] clear legacy scan credentials failed: ' . $exception->getMessage());
        }
    }
    // ۱.۳۷.۵ — ستون assets.source در نصب‌های قدیمی مقدار 'domain' را نمی‌پذیرد؛
    // در MySQL سخت‌گیرانه این باعث خطای «Data truncated» و ساخته‌نشدن رکورد کامپیوتر
    // دامنه می‌شد و در حالت معمولی هم مقدار ستون خالی می‌ماند. یک‌بار برای همیشه گسترده می‌شود.
    static $sourceEnumFixed = false;
    if (!$sourceEnumFixed) {
        $sourceEnumFixed = true;
        try {
            $type = (string) db()->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'source'")->fetchColumn();
            if ($type !== '' && stripos($type, 'domain') === false) {
                db()->exec("ALTER TABLE assets MODIFY source ENUM('manual','server_inventory','agent','domain') NOT NULL DEFAULT 'manual'");
                if (function_exists('system_log')) {
                    system_log('info', 'domain_scan', 'ستون assets.source برای مقدار domain گسترده شد');
                }
            }
        } catch (Throwable $exception) {
            app_error_log('[domain-scan] widen assets.source failed: ' . $exception->getMessage());
        }
    }
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    db()->exec('CREATE TABLE IF NOT EXISTS domain_scan_runs (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, started_by INT UNSIGNED NULL, status VARCHAR(20) NOT NULL DEFAULT "running", total INT NOT NULL DEFAULT 0, done INT NOT NULL DEFAULT 0, online INT NOT NULL DEFAULT 0, offline INT NOT NULL DEFAULT 0, failed INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    db()->exec('CREATE TABLE IF NOT EXISTS domain_scan_queue (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, run_id INT UNSIGNED NOT NULL, asset_id INT UNSIGNED NULL, hostname VARCHAR(190) NOT NULL, status VARCHAR(20) NOT NULL DEFAULT "pending", online TINYINT(1) NOT NULL DEFAULT 0, message VARCHAR(255) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, KEY run_status (run_id, status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

function domain_scan_timeout_ms(): int
{
    $value = (int) setting('domain_scan_timeout', '2500');
    return $value >= 500 && $value <= 30000 ? $value : 2500;
}

function domain_scan_account_label(): string
{
    return 'حسابی که هنگام شروع همین نوبت وارد می‌کنید';
}

function domain_scan_https_request(): bool
{
    return !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
}

function domain_scan_validate_credentials(string $username, string $password): void
{
    if (trim($username) === '' || $password === '') {
        throw new RuntimeException('برای استخراج شبکه‌ای، نام کاربری و رمز حساب مجاز را وارد کنید.');
    }
    if (PHP_SAPI !== 'cli' && !domain_scan_https_request()) {
        throw new RuntimeException('برای ارسال امن رمز حساب استخراج، ابتدا سامانه را با HTTPS باز کنید.');
    }
    domain_scan_credential_key();
}

function domain_scan_credential_key(): string
{
    $appKey = trim((string) cfg('app.key', ''));
    // پیام خطا باید بگوید **کدام** شرط برقرار نیست. قبلاً هر سه حالت یک پیام
    // کلی «رمزگذاری امن پیکربندی نشده است» می‌دادند و کاربر نمی‌دانست باید
    // app.key را پر کند یا پسوند openssl را در php.ini فعال کند.
    if (!function_exists('openssl_encrypt') || !function_exists('openssl_decrypt')) {
        throw new RuntimeException('پسوند OpenSSL در PHP فعال نیست؛ رمز حساب اسکن نمی‌تواند رمز شود. در php.ini خط extension=openssl را فعال و سرویس وب را ری‌استارت کنید.');
    }
    if ($appKey === '') {
        throw new RuntimeException('کلید برنامه (app.key) در config.php خالی است؛ برای رمزگذاری اعتبارنامهٔ اسکن ابتدا آن را پر کنید.');
    }
    return hash('sha256', 'ticket-domain-scan:' . $appKey, true);
}

/** اعتبارنامه فقط به‌صورت رمز‌شده و موقت در نشست همان کاربر نگهداری می‌شود، نه در جدول تنظیمات یا صف DB. */
function domain_scan_store_run_credentials(int $runId, int $userId, string $username, string $password): void
{
    $username = trim($username);
    if ($runId <= 0 || $userId <= 0 || $username === '' || $password === '') {
        throw new RuntimeException('برای استخراج شبکه‌ای، نام کاربری و رمز حساب مجاز را وارد کنید.');
    }
    if (PHP_SAPI !== 'cli' && (empty($_SERVER['HTTPS']) || strtolower((string) $_SERVER['HTTPS']) === 'off')) {
        throw new RuntimeException('برای ارسال امن رمز حساب استخراج، ابتدا سامانه را با HTTPS باز کنید.');
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('نشست امن کاربر برای نگهداری موقت اعتبارنامه در دسترس نیست.');
    }
    $key = domain_scan_credential_key();
    $iv = random_bytes(12);
    $tag = '';
    $aad = 'run:' . $runId . '|user:' . $userId . '|v1';
    $plain = json_encode(['username' => $username, 'password' => $password], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $cipher = is_string($plain) ? openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, $aad) : false;
    if (!is_string($cipher) || $cipher === '') {
        throw new RuntimeException('رمزگذاری اعتبارنامهٔ این اسکن انجام نشد.');
    }
    $_SESSION['domain_scan_credentials'][$runId] = [
        'user_id' => $userId,
        'expires_at' => time() + 7200,
        'ciphertext' => base64_encode($iv . $tag . $cipher),
    ];
}

/** @return array{username:string,password:string}|null */
function domain_scan_run_credentials(int $runId, int $userId): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE || $runId <= 0 || $userId <= 0) {
        return null;
    }
    $entry = $_SESSION['domain_scan_credentials'][$runId] ?? null;
    if (!is_array($entry) || (int) ($entry['user_id'] ?? 0) !== $userId || (int) ($entry['expires_at'] ?? 0) < time()) {
        unset($_SESSION['domain_scan_credentials'][$runId]);
        return null;
    }
    $raw = base64_decode((string) ($entry['ciphertext'] ?? ''), true);
    if (!is_string($raw) || strlen($raw) < 29) {
        unset($_SESSION['domain_scan_credentials'][$runId]);
        return null;
    }
    try {
        $key = domain_scan_credential_key();
    } catch (Throwable) {
        unset($_SESSION['domain_scan_credentials'][$runId]);
        return null;
    }
    $aad = 'run:' . $runId . '|user:' . $userId . '|v1';
    $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16), $aad);
    $credentials = is_string($plain) ? json_decode($plain, true) : null;
    if (!is_array($credentials) || trim((string) ($credentials['username'] ?? '')) === '' || (string) ($credentials['password'] ?? '') === '') {
        unset($_SESSION['domain_scan_credentials'][$runId]);
        return null;
    }
    $_SESSION['domain_scan_credentials'][$runId]['expires_at'] = time() + 7200;
    return ['username' => trim((string) $credentials['username']), 'password' => (string) $credentials['password']];
}

function domain_scan_clear_run_credentials(int $runId): void
{
    if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        unset($_SESSION['domain_scan_credentials'][$runId]);
        if (empty($_SESSION['domain_scan_credentials'])) {
            unset($_SESSION['domain_scan_credentials']);
        }
        session_write_close();
    }
}

function domain_scan_ad_time(string $value): ?string
{
    $value = trim($value);
    if ($value === '' || !ctype_digit($value)) {
        return null;
    }
    $filetime = (float) $value;
    if ($filetime <= 0.0) {
        return null;
    }
    $unix = ($filetime / 10000000.0) - 11644473600.0;
    if ($unix <= 0.0 || $unix > 4102444800.0) {
        return null;
    }
    return date('Y-m-d H:i:s', (int) $unix);
}

function ldap_domain_computers(int $limit = 0): array
{
    if (!function_exists('ldap_connect')) {
        throw new RuntimeException('افزونهٔ LDAP در PHP فعال نیست.');
    }
    $host = trim((string) ldap_cfg('host', ''));
    $baseDn = trim((string) ldap_cfg('base_dn', ''));
    if ($host === '' || $baseDn === '') {
        throw new RuntimeException('آدرس کنترلر و Base DN دامین را در تنظیمات وارد کنید.');
    }
    $ssl = (bool) ldap_cfg('ssl', false);
    $connection = @ldap_connect(($ssl ? 'ldaps://' : 'ldap://') . $host, (int) ldap_cfg('port', $ssl ? 636 : 389));
    if (!$connection) {
        throw new RuntimeException('اتصال به کنترلر دامین برقرار نشد.');
    }
    ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
    if (defined('LDAP_OPT_NETWORK_TIMEOUT')) { @ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 10); }
    if (defined('LDAP_OPT_TIMELIMIT')) { @ldap_set_option($connection, LDAP_OPT_TIMELIMIT, 10); }
    $bindDn = (string) ldap_cfg('bind_dn', '');
    if ($bindDn !== '' && !@ldap_bind($connection, $bindDn, (string) ldap_cfg('bind_password', ''))) {
        throw new RuntimeException('ورود حساب سرویس LDAP ناموفق بود.');
    }
    $filter = '(&(objectCategory=computer)(objectClass=computer))';
    $attributes = ['dNSHostName', 'name', 'operatingSystem', 'operatingSystemVersion', 'distinguishedName', 'description', 'lastLogonTimestamp', 'userAccountControl'];
    $computers = [];
    $cookie = '';
    do {
        if (function_exists('ldap_control_paged_result')) {
            @ldap_control_paged_result($connection, 500, false, $cookie);
        }
        $search = @ldap_search($connection, $baseDn, $filter, $attributes);
        if (!$search) {
            throw new RuntimeException('جست‌وجوی کامپیوترهای دامین ناموفق بود.');
        }
        $entries = ldap_get_entries($connection, $search);
        for ($index = 0; $index < (int) ($entries['count'] ?? 0); $index++) {
            $entry = $entries[$index];
            $hostname = trim((string) ($entry['dnshostname'][0] ?? ''));
            if ($hostname === '') {
                $hostname = trim((string) ($entry['name'][0] ?? ''));
            }
            if ($hostname === '') {
                continue;
            }
            $computers[] = [
                'hostname' => $hostname,
                'name' => trim((string) ($entry['name'][0] ?? $hostname)),
                'dn' => (string) ($entry['distinguishedname'][0] ?? ''),
                'os' => (string) ($entry['operatingsystem'][0] ?? ''),
                'os_version' => (string) ($entry['operatingsystemversion'][0] ?? ''),
                'description' => (string) ($entry['description'][0] ?? ''),
                'last_logon' => domain_scan_ad_time((string) ($entry['lastlogontimestamp'][0] ?? '')),
                'enabled' => (((int) ($entry['useraccountcontrol'][0] ?? 0)) & 2) !== 2,
            ];
            if ($limit > 0 && count($computers) >= $limit) {
                break 2;
            }
        }
        if (function_exists('ldap_control_paged_result_response')) {
            $estimated = 0;
            @ldap_control_paged_result_response($connection, $search, $cookie, $estimated);
        } else {
            $cookie = '';
        }
    } while ($cookie !== '');
    usort($computers, static fn (array $a, array $b): int => strcasecmp($a['hostname'], $b['hostname']));
    return $computers;
}

/**
 * آیا این نام، «نام ساختگی» است — یعنی نام واقعی کامپیوتر نیست؟
 *
 * دو الگو: IP خام (۱۹۲.۱۶۸.۱.۲۵)، و پیشوند CLIENT- که خودِ سامانه هنگام
 * ورود کاربر می‌سازد وقتی نام واقعی را نمی‌داند (CLIENT-192-168-1-25).
 * همچنین مقداری که IP داخلش چسبیده («client 192.168.1.25»).
 */
function domain_scan_name_is_placeholder(string $name): bool
{
    $name = trim($name);
    if ($name === '') {
        return true;
    }
    if (filter_var($name, FILTER_VALIDATE_IP) !== false) {
        return true;
    }
    if (stripos($name, 'CLIENT-') === 0) {
        return true;
    }
    return preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $name) === 1;
}

function domain_scan_asset_for(string $hostname): int
{
    $hostname = trim($hostname);
    if ($hostname === '') {
        return 0;
    }
    // ۱.۶۲.۳۷ — تشخیص یکتا بر اساس هویت نرمالشده (PC1، PC1.domain.local و PC1$ یکیاند).
    $assetId = asset_resolve_id($hostname);
    if ($assetId > 0) {
        asset_sync_identity_key($assetId);
        return $assetId;
    }
    $short = strtoupper(explode('.', $hostname)[0]);
    $tag = substr('DOMAIN-' . (string) preg_replace('/[^A-Za-z0-9_-]/', '-', $short), 0, 90);
    $exists = db()->prepare('SELECT id FROM assets WHERE asset_tag = ? LIMIT 1');
    $exists->execute([$tag]);
    $assetId = (int) ($exists->fetchColumn() ?: 0);
    if ($assetId > 0) {
        db()->prepare('UPDATE assets SET hostname = ?, hostname_key = ?, source = "domain", updated_at = NOW() WHERE id = ?')->execute([$hostname, asset_identity_key($hostname), $assetId]);
        return $assetId;
    }
    db()->prepare('INSERT INTO assets (asset_tag, hostname, hostname_key, computer_type, source) VALUES (?, ?, ?, "", "domain")')->execute([$tag, $hostname, asset_identity_key($hostname)]);
    $newId = (int) db()->lastInsertId();
    asset_sync_identity_key($newId);
    return $newId;
}

function domain_scan_ad_values(array $pc): array
{
    return [
        'ad_dn' => (string) ($pc['dn'] ?? ''),
        'ad_os' => (string) ($pc['os'] ?? ''),
        'ad_os_version' => (string) ($pc['os_version'] ?? ''),
        'ad_last_logon' => $pc['last_logon'] ?? null,
        'ad_description' => (string) ($pc['description'] ?? ''),
    ];
}

function domain_scan_start(int $userId): array
{
    domain_scan_ensure_schema();
    $computers = ldap_domain_computers();
    if ($computers === []) {
        throw new RuntimeException('در دامین هیچ کامپیوتری پیدا نشد.');
    }
    db()->prepare('INSERT INTO domain_scan_runs (started_by, status, total) VALUES (?, "running", ?)')->execute([$userId > 0 ? $userId : null, count($computers)]);
    $runId = (int) db()->lastInsertId();
    $insertQueue = db()->prepare('INSERT INTO domain_scan_queue (run_id, asset_id, hostname, status) VALUES (?, ?, ?, "pending")');
    foreach ($computers as $pc) {
        $assetId = domain_scan_asset_for((string) $pc['hostname']);
        if ($assetId > 0) {
            asset_profile_apply_auto($assetId, domain_scan_ad_values($pc), $userId, true);
            if (empty($pc['enabled'])) {
                asset_profile_apply_auto($assetId, ['ad_description' => trim('حساب کامپیوتر در دامنه غیرفعال است. ' . (string) $pc['description'])], $userId, true);
            }
        }
        $insertQueue->execute([$runId, $assetId > 0 ? $assetId : null, (string) $pc['hostname']]);
    }
    system_log('info', 'domain_scan', 'اسکن دامنه آغاز شد', ['run_id' => $runId, 'total' => count($computers)]);
    return ['run_id' => $runId, 'total' => count($computers)];
}

function domain_scan_start_selected(array $assetIds, array $user): array
{
    domain_scan_ensure_schema();
    $ids = array_values(array_unique(array_filter(array_map('intval', $assetIds), static fn (int $id): bool => $id > 0)));
    if ($ids === []) {
        throw new RuntimeException('ابتدا سیستم‌هایی را برای استخراج انتخاب کنید.');
    }
    if (count($ids) > 900) {
        throw new RuntimeException('برای هر نوبت حداکثر ۹۰۰ سیستم را انتخاب کنید تا همهٔ انتخاب‌ها به‌درستی ارسال شوند.');
    }
    [$scope, $scopeParams] = asset_scope($user);
    $where = $scope !== '' ? $scope . ' AND ' : 'WHERE ';
    $where .= 'a.id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
    $query = db()->prepare('SELECT a.id, a.hostname, a.ip_address FROM assets a ' . $where . ' ORDER BY a.hostname, a.id');
    $query->execute(array_merge($scopeParams, $ids));
    $assets = [];
    foreach ($query->fetchAll() as $row) {
        $hostname = trim((string) ($row['hostname'] ?? ''));
        if ($hostname === '') {
            $hostname = trim((string) ($row['ip_address'] ?? ''));
        }
        if ($hostname !== '') {
            $assets[(int) $row['id']] = $hostname;
        }
    }
    if ($assets === []) {
        throw new RuntimeException('سیستم انتخاب‌شده‌ای با نام کامپیوتر یا IP معتبر و دسترسی مجاز پیدا نشد.');
    }
    db()->prepare('INSERT INTO domain_scan_runs (started_by, status, total) VALUES (?, "running", ?)')->execute([(int) $user['id'], count($assets)]);
    $runId = (int) db()->lastInsertId();
    $insert = db()->prepare('INSERT INTO domain_scan_queue (run_id, asset_id, hostname, status) VALUES (?, ?, ?, "pending")');
    foreach ($assets as $assetId => $hostname) {
        $insert->execute([$runId, $assetId, $hostname]);
    }
    system_log('info', 'domain_scan', 'استخراج شبکه‌ای سیستم‌های انتخاب‌شده آغاز شد', ['run_id' => $runId, 'total' => count($assets)]);
    return ['run_id' => $runId, 'total' => count($assets)];
}

function domain_scan_import(int $userId): array
{
    domain_scan_ensure_schema();
    if (function_exists('set_time_limit')) { @set_time_limit(300); }
    $computers = ldap_domain_computers();
    if ($computers === []) {
        throw new RuntimeException('در دامین هیچ کامپیوتری پیدا نشد. تنظیمات اتصال دامنه را بررسی کنید.');
    }
    $created = 0;
    $updated = 0;
    foreach ($computers as $pc) {
        $host = trim((string) ($pc['hostname'] ?? ''));
        if ($host === '') {
            continue;
        }
        $existsId = asset_resolve_id($host);
        if ($existsId > 0) {
            $updated++;
        } else {
            $created++;
        }
        $assetId = domain_scan_asset_for($host);
        if ($assetId > 0) {
            asset_profile_apply_auto($assetId, domain_scan_ad_values($pc), $userId, true);
            if (empty($pc['enabled'])) {
                asset_profile_apply_auto($assetId, ['ad_description' => trim('حساب کامپیوتر در دامنه غیرفعال است. ' . (string) $pc['description'])], $userId, true);
            }
        }
    }
    system_log('info', 'domain_scan', 'ورود کامپیوترهای دامنه', ['created' => $created, 'updated' => $updated, 'total' => count($computers)]);
    return ['created' => $created, 'updated' => $updated, 'total' => count($computers)];
}

/** مسیر کامل PowerShell ویندوز (بدون وابستگی به PATH سرویس Apache). */
function domain_scan_powershell_path(): string
{
    $root = (string) (getenv('SystemRoot') ?: getenv('windir') ?: 'C:\\Windows');
    $full = rtrim($root, '\\/') . '\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';
    return is_file($full) ? $full : 'powershell.exe';
}

/**
 * اجرای هم‌زمان چند پردازش. خروجی هر پردازش در فایل موقت نوشته می‌شود، چون pipe غیرمسدود روی ویندوز
 * قابل‌اعتماد نیست. هر پردازشِ بیش از $timeoutSec ثانیه متوقف می‌شود تا یک سیستم کند کل اسکن را نخواباند.
 *
 * @param array<string,array{cmd:array<int,string>,env?:array<string,string>}> $jobs
 * @return array<string,array{code:int,out:string,err:string,timed_out:bool,started:bool}>
 */
function domain_scan_run_processes(array $jobs, int $concurrency, int $timeoutSec): array
{
    $results = [];
    $running = [];
    $queue = array_keys($jobs);
    $tmpDir = sys_get_temp_dir();
    $concurrency = max(1, $concurrency);
    $timeoutSec = max(1, $timeoutSec);
    $baseEnv = null;
    if (!function_exists('proc_open')) {
        foreach ($queue as $key) {
            $results[$key] = ['code' => -1, 'out' => '', 'err' => 'proc_open is disabled in PHP', 'timed_out' => false, 'started' => false];
        }
        return $results;
    }
    while ($queue !== [] || $running !== []) {
        while ($queue !== [] && count($running) < $concurrency) {
            $key = array_shift($queue);
            $job = $jobs[$key];
            $out = tempnam($tmpDir, 'itsm');
            $err = tempnam($tmpDir, 'itsm');
            if ($out === false || $err === false) {
                $results[$key] = ['code' => -1, 'out' => '', 'err' => 'temp file creation failed', 'timed_out' => false, 'started' => false];
                continue;
            }
            $env = null;
            if (!empty($job['env'])) {
                if ($baseEnv === null) {
                    $baseEnv = getenv();
                }
                $env = array_merge(is_array($baseEnv) ? $baseEnv : [], $job['env']);
            }
            $pipes = [];
            $proc = @proc_open($job['cmd'], [0 => ['pipe', 'r'], 1 => ['file', $out, 'w'], 2 => ['file', $err, 'w']], $pipes, null, $env);
            if (!is_resource($proc)) {
                @unlink($out);
                @unlink($err);
                $results[$key] = ['code' => -1, 'out' => '', 'err' => 'proc_open failed', 'timed_out' => false, 'started' => false];
                continue;
            }
            if (isset($pipes[0]) && is_resource($pipes[0])) {
                fclose($pipes[0]);
            }
            $running[$key] = ['proc' => $proc, 'out' => $out, 'err' => $err, 'deadline' => microtime(true) + $timeoutSec];
        }
        foreach ($running as $key => $info) {
            $status = proc_get_status($info['proc']);
            $timedOut = false;
            if (!empty($status['running'])) {
                if (microtime(true) <= $info['deadline']) {
                    continue;
                }
                @proc_terminate($info['proc']);
                $timedOut = true;
            }
            $code = $timedOut ? -1 : (int) ($status['exitcode'] ?? -1);
            @proc_close($info['proc']);
            $outText = (string) @file_get_contents($info['out']);
            $errText = (string) @file_get_contents($info['err']);
            @unlink($info['out']);
            @unlink($info['err']);
            $results[$key] = ['code' => $code, 'out' => $outText, 'err' => $errText, 'timed_out' => $timedOut, 'started' => true];
            unset($running[$key]);
        }
        if ($running !== []) {
            usleep(40000);
        }
    }
    return $results;
}

function domain_scan_resolve_ipv4(string $host): string
{
    if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return $host;
    }
    $ip = @gethostbyname($host);
    return ($ip !== $host && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) ? $ip : '';
}

/** @return array<int,string> */
function domain_scan_ping_command(string $target, int $timeoutMs): array
{
    if (PHP_OS_FAMILY === 'Windows') {
        return ['ping', '-4', '-n', '1', '-w', (string) $timeoutMs, $target];
    }
    return ['ping', '-4', '-c', '1', '-W', (string) max(1, (int) ceil($timeoutMs / 1000)), $target];
}

/**
 * پینگ ICMP هم‌زمان؛ کلید خروجی همان کلید ورودی است.
 * @param array<string,string> $targets
 * @return array<string,bool>
 */
function domain_scan_icmp_round(array $targets, int $timeoutMs): array
{
    $jobs = [];
    foreach ($targets as $key => $target) {
        $jobs[$key] = ['cmd' => domain_scan_ping_command($target, $timeoutMs)];
    }
    $results = domain_scan_run_processes($jobs, 48, max(8, (int) ceil($timeoutMs / 1000) + 8));
    $answer = [];
    foreach ($targets as $key => $_target) {
        $row = $results[$key] ?? null;
        $answer[$key] = $row !== null && $row['started'] && !$row['timed_out'] && preg_match('/ttl\s*=/i', $row['out']) === 1;
    }
    return $answer;
}

/**
 * بررسی TCP هم‌زمان (پورت‌های SMB/RPC/RDP/WinRM). فایروال ویندوز اغلب ICMP را می‌بندد ولی سیستم روشن است.
 * @param array<string,string> $targets کلید => IP
 * @param array<int,int> $ports
 * @return array<string,bool>
 */
function domain_scan_tcp_probe(array $targets, array $ports, int $timeoutMs): array
{
    $up = [];
    $pending = [];
    $seq = 0;
    try {
        foreach ($targets as $key => $ip) {
            foreach ($ports as $port) {
                $errno = 0;
                $error = '';
                $socket = @stream_socket_client('tcp://' . $ip . ':' . $port, $errno, $error, 0, STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT);
                if ($socket) {
                    stream_set_blocking($socket, false);
                    $pending[$seq++] = ['key' => $key, 'socket' => $socket];
                }
            }
        }
        $deadline = microtime(true) + max(0.3, $timeoutMs / 1000);
        while ($pending !== [] && microtime(true) < $deadline) {
            $read = null;
            $write = [];
            $except = [];
            foreach ($pending as $id => $row) {
                if (isset($up[$row['key']])) {
                    fclose($row['socket']);
                    unset($pending[$id]);
                    continue;
                }
                $write[$id] = $row['socket'];
                $except[$id] = $row['socket'];
            }
            if ($write === []) {
                break;
            }
            $left = max(0.0, $deadline - microtime(true));
            $ready = @stream_select($read, $write, $except, 0, (int) min(200000, $left * 1000000));
            if ($ready === false) {
                break;
            }
            foreach ($except as $id => $_socket) {
                if (isset($pending[$id])) {
                    fclose($pending[$id]['socket']);
                    unset($pending[$id]);
                }
            }
            foreach ($write as $id => $socket) {
                if (!isset($pending[$id])) {
                    continue;
                }
                $name = @stream_socket_get_name($socket, true);
                if ($name !== false && $name !== '') {
                    $up[$pending[$id]['key']] = true;
                }
                fclose($socket);
                unset($pending[$id]);
            }
        }
    } catch (Throwable $exception) {
        app_error_log('[domain-scan] tcp probe: ' . $exception->getMessage());
    }
    foreach ($pending as $row) {
        if (is_resource($row['socket'])) {
            @fclose($row['socket']);
        }
    }
    return $up;
}

/**
 * پینگ هم‌زمان چند سیستم. مراحل: ICMP موازی ← DNS/IP ثبت‌شده ← بررسی TCP پورت‌های SMB/RPC/RDP/WinRM.
 * بدون وابستگی به اسکریپت PowerShell؛ ۳۰۰ سیستم در چند ثانیه.
 *
 * @param array<int,string> $hosts
 * @param array<string,string> $fallbackIps کلید = نام کامپیوتر (حروف کوچک)، مقدار = IP ثبت‌شده در شناسنامه
 * @return array<string,bool> کلید = نام کامپیوتر (حروف کوچک)
 */
function domain_scan_ping_hosts(array $hosts, array $fallbackIps = []): array
{
    $clean = [];
    foreach ($hosts as $host) {
        $host = trim((string) $host);
        if ($host !== '' && preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
            $clean[strtolower($host)] = $host;
        }
    }
    if ($clean === []) {
        return [];
    }
    if (function_exists('set_time_limit')) {
        @set_time_limit(300);
    }
    $timeout = domain_scan_timeout_ms();
    $results = array_fill_keys(array_keys($clean), false);

    foreach (domain_scan_icmp_round($clean, $timeout) as $key => $ok) {
        if ($ok) {
            $results[$key] = true;
        }
    }

    // سیستم‌های بی‌پاسخ: IP واقعی را پیدا کن (DNS، و اگر نام حل نشد IP ثبت‌شده در شناسنامه)
    $budgetEnd = microtime(true) + 20.0;
    $ipTargets = [];
    $viaFallback = [];
    foreach ($clean as $key => $host) {
        if ($results[$key] || microtime(true) > $budgetEnd) {
            continue;
        }
        $ip = domain_scan_resolve_ipv4($host);
        if ($ip === '') {
            $stored = trim((string) ($fallbackIps[$key] ?? ''));
            if ($stored !== '' && filter_var($stored, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ip = $stored;
                $viaFallback[$key] = $stored;
            }
        }
        if ($ip !== '') {
            $ipTargets[$key] = $ip;
        }
    }
    if ($viaFallback !== []) {
        foreach (domain_scan_icmp_round($viaFallback, $timeout) as $key => $ok) {
            if ($ok) {
                $results[$key] = true;
                unset($ipTargets[$key]);
            }
        }
    }
    $tcpTargets = [];
    foreach ($ipTargets as $key => $ip) {
        if (!$results[$key]) {
            $tcpTargets[$key] = $ip;
        }
    }
    if ($tcpTargets !== []) {
        foreach (domain_scan_tcp_probe($tcpTargets, [445, 135, 3389, 5985], min(2500, max(800, $timeout))) as $key => $_up) {
            $results[$key] = true;
        }
    }
    return $results;
}

function domain_scan_online(string $host): bool
{
    $host = trim($host);
    if ($host === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
        return false;
    }
    $result = domain_scan_ping_hosts([$host]);
    return (bool) ($result[strtolower($host)] ?? false);
}

function domain_scan_mark_reachability(int $assetId, bool $online, int $userId): void
{
    if ($assetId <= 0) {
        return;
    }
    asset_profile_ensure_schema();
    $now = date('Y-m-d H:i:s');
    $exists = db()->prepare('SELECT asset_id FROM asset_profiles WHERE asset_id = ? LIMIT 1');
    $exists->execute([$assetId]);
    if ($exists->fetchColumn()) {
        if ($online) {
            db()->prepare('UPDATE asset_profiles SET ad_online = 1, ad_last_seen = ?, ad_scanned_at = ?, updated_by = ?, updated_at = NOW() WHERE asset_id = ?')->execute([$now, $now, $userId > 0 ? $userId : null, $assetId]);
        } else {
            db()->prepare('UPDATE asset_profiles SET ad_online = 0, ad_scanned_at = ?, updated_by = ?, updated_at = NOW() WHERE asset_id = ?')->execute([$now, $userId > 0 ? $userId : null, $assetId]);
        }
        return;
    }
    db()->prepare('INSERT INTO asset_profiles (asset_id, ad_online, ad_last_seen, ad_scanned_at, updated_by) VALUES (?, ?, ?, ?, ?)')->execute([$assetId, $online ? 1 : 0, $online ? $now : null, $now, $userId > 0 ? $userId : null]);
}

/** پیام خطای PowerShell/WMI را به فارسی قابل‌فهم تبدیل می‌کند. */
function domain_scan_friendly_error(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    if (function_exists('mb_check_encoding') && !mb_check_encoding($raw, 'UTF-8')) {
        $raw = (string) @mb_convert_encoding($raw, 'UTF-8', 'Windows-1256');
    }
    $lower = strtolower($raw);
    // خطای سرتاسری «هیچ‌کدام از پروتکل‌ها کار نکرد» را پیش از تطبیق‌های تک‌مسیری
    // خودش می‌سنجیم؛ در غیر این صورت پیام کبشده داخل آن (مثل «access is denied»)
    // به‌اشتباه به یک کد تک‌علتی نگاشت می‌شود و گروه‌بندی شکست‌ها را گمراه می‌کند.
    if (str_contains($lower, 'all_protocols_failed')) {
        $detailLimit = function_exists('mb_substr') ? mb_substr(trim($raw), 0, 180) : substr(trim($raw), 0, 180);
        // ۱.۶۳.۱-r17 — پیام بر اساس علت واقعی داخل آن نوشته می‌شود.
        //
        // قبلاً همهٔ حالت‌ها یک متن می‌گرفتند: «دسترسی WMI/DCOM (فایروال) یا
        // WinRM آماده نیست». برای کسی که دقیقاً «Access is denied» گرفته بود
        // این گمراه‌کننده بود: فایروال کاملاً باز بود. تفکیک واقعی این است:
        //
        //   Access is denied (0x80070005)  ⇒ بسته رسیده، احراز هویت شده، ولی
        //                                     توکن مدیریتی فیلتر شده (UAC از
        //                                     راه دور) یا حساب ادمین محلی نیست.
        //   RPC server unavailable (800706BA) ⇒ پورت/فایروال، یا سرویس WMI خاموش.
        //   cannot connect … WinRM         ⇒ خود مقصد WinRM/شنونده ندارد.
        $head = 'اتصال از هیچ‌کدام از مسیرهای DCOM و WinRM برقرار نشد. ';
        if (str_contains($lower, 'access is denied') || str_contains($lower, '0x80070005')) {
            $head .= 'علت از متن خطا مشخص است: «Access is denied» یعنی شبکه و فایروال مشکلی ندارند و بسته به مقصد رسیده است؛ ویندوز توکن مدیریتی حساب را از راه دور فیلتر کرده (UAC از راه دور) یا این حساب روی همین مقصد در گروه Administrators محلی نیست. سامانه به‌طور خودکار مسیر SMB را هم آزموده است؛ اگر آن هم ناموفق بود، روی مقصد بررسی کنید. ';
        } elseif (str_contains($lower, 'rpc server is unavailable') || str_contains($lower, '0x800706ba')) {
            $head .= 'علت از متن خطا: «RPC server is unavailable» یعنی بسته به مقصد نرسیده — فایروال مقصد، سرویس WMI خاموش، یا پورت‌های ۱۳۵/۴۹۱۵۲-۶۵۵۳۵ بسته است. ';
        } else {
            $head .= 'روی سیستم مقصد، دسترسی WMI/DCOM (فایروال) یا WinRM آماده نیست. ';
        }
        return $head . 'جزئیات فنی: ' . preg_replace('/\s+/u', ' ', (string) $detailLimit);
    }
    // ۱.۶۳.۱-r17 — خطاهای مسیر SMB. باید **پیش از** نقشهٔ عمومی «access is denied»
    // بیایند، وگرنه علت واقعی با توصیهٔ WMI پوشیده می‌شود.
    //
    // نکتهٔ مهم: کلیدها عمداً «پیشوند دقیق کد» هستند، نه متن آزاد. اولین نسخه
    // از 'TCP 445' و 'schtasks /create' استفاده می‌کرد و متن خطاهای
    // PREPARE_* (که همان کلمات را دارند) را می‌دزدید؛ نتیجه این بود که خطای
    // آماده‌سازی به‌اشتباه «روش SMB هم ناموفق بود» گزارش می‌شد.
    $smbMap = [
        'SMB_ACCESS_DENIED' => 'روش SMB هم ناموفق بود: حساب واردشده روی این مقصد ادمین محلی نیست یا C$ در دسترس نیست. این تنها شرطی است که TNI هم دارد.',
        'SMB_NOT_ADMIN' => 'روش SMB: با این حساب به مقصد وارد شدیم، ولی باز کردن C$ رد شد — یعنی این حساب روی همین کامپیوتر عضو «Administrators محلی» نیست. این تنها شرطی است که TNI هم دارد و هیچ نرم‌افزاری بدون آن موفق نمی‌شود.',
        'SMB_BAD_CREDENTIALS' => 'روش SMB: نام کاربری یا رمز یا نام دامنه نادرست است — ویندوز نتوانست با این حساب وارد مقصد شود. نام دامنه را با خروجی بخش ۰.۵ (نام واقعی دامنه) بسنجید.',
        'SMB_UNREACHABLE' => 'روش SMB هم ناموفق بود: پورت 445 روی مقصد بسته است (فایروال یا سرویس File and Printer Sharing).',
        'SMB_TASK_FAILED' => 'روش SMB هم ناموفق بود: تسک زمان‌بندی‌شده روی مقصد ساخته/اجرا نشد (سرویس Task Scheduler و قاعدهٔ Remote Service Management).',
        'SMB_COLLECT_TIMEOUT' => 'روش SMB هم ناموفق بود: عامل روی مقصد در مهلت مقرر نتیجه نداد.',
        'SMB_COLLECT_BAD_JSON' => 'روش SMB هم ناموفق بود: خروجی عامل روی مقصد خوانا نبود.',
        'SMB_COLLECTOR_MISSING' => 'فایل tools/collect_server_inventory.ps1 در بستهٔ سامانه پیدا نشد.',
        'SMB_BAD_HOST' => 'نام کامپیوتر مقصد برای مسیر SMB نامعتبر است.',
        'SMB_NO_CREDENTIALS' => 'اعتبارنامهٔ مسیر SMB در دسترس نیست؛ نام کاربری و رمز را دوباره وارد کنید.',
    ];
    foreach ($smbMap as $token => $message) {
        // لنگر شروع لازم است: PREPARE_SMB_UNREACHABLE نیز شامل SMB_UNREACHABLE است.
        if (str_starts_with($raw, $token)) {
            return $message . ' جزئیات فنی: ' . preg_replace('/\s+/u', ' ', (string) (function_exists('mb_substr') ? mb_substr(trim($raw), 0, 180) : substr(trim($raw), 0, 180)));
        }
    }
    // خطاهای آماده‌سازی راه‌دور مقصد (tools/prepare_targets.ps1) — هر کدام علت و راه‌حل جدا دارند.
    $prepareMap = [
        'prepare_smb_unreachable' => 'مسیر SMB روی سیستم مقصد در دسترس نیست؛ آماده‌سازی راه‌دور ممکن نیست. جزئیات فنی: ',
        'prepare_task_failed' => 'ساخت یا اجرای تسک آماده‌سازی روی سیستم مقصد ناموفق بود. جزئیات فنی: ',
        'prepare_verify_failed' => 'آماده‌سازی انجام شد اما اتصال آزمایشی (CIM) برقرار نشد. جزئیات فنی: ',
        'prepare_no_credentials' => 'اعتبارنامهٔ آماده‌سازی در دسترس نیست؛ نام کاربری و رمز را دوباره وارد کنید. جزئیات فنی: ',
        'prepare_gpo_not_domain' => 'سرور پنل عضو دامنه نیست؛ انتشار Group Policy فقط از سرور عضو دامنه ممکن است. جزئیات فنی: ',
        'prepare_gpo_failed' => 'ساخت یا اعمال Group Policy روی کنترلر دامنه ناموفق بود. جزئیات فنی: ',
        'prepare_gpo_link_failed' => 'لینک‌کردن Group Policy به دامنه ناموفق بود. جزئیات فنی: ',
        'prepare_gpo_unavailable' => 'ساخت Group Policy از داخل پنل ممکن نشد. جزئیات فنی: ',
    ];
    foreach ($prepareMap as $token => $prefix) {
        if (str_contains($lower, strtolower($token))) {
            $detailLimit = function_exists('mb_substr') ? mb_substr(trim($raw), 0, 180) : substr(trim($raw), 0, 180);
            return $prefix . preg_replace('/\s+/u', ' ', (string) $detailLimit);
        }
    }
    $map = [
        'access is denied' => 'دسترسی رد شد؛ حسابی که در فرم همین نوبت وارد کرده‌اید باید عضو «Administrators محلی» همان کامپیوتر باشد.',
        '0x80070005' => 'دسترسی رد شد؛ حسابی که در فرم همین نوبت وارد کرده‌اید باید عضو «Administrators محلی» همان کامپیوتر باشد.',
        'logon failure' => 'نام کاربری یا رمز حساب اسکن نادرست است.',
        'user name or password is incorrect' => 'نام کاربری یا رمز حساب اسکن نادرست است.',
        'winrm cannot process the request' => 'مسیر WinRM/WSMan به سیستم مقصد آماده نیست؛ WinRM و قاعدهٔ فایروال را روی کلاینت مقصد بررسی کنید.',
        'winrm cannot complete the operation' => 'مسیر WinRM/WSMan به سیستم مقصد آماده نیست؛ WinRM و قاعدهٔ فایروال را روی کلاینت مقصد بررسی کنید.',
        'client cannot connect to the destination specified' => 'مسیر WinRM/WSMan به سیستم مقصد آماده نیست؛ WinRM و قاعدهٔ فایروال را روی کلاینت مقصد بررسی کنید.',
        'wsmanfault' => 'مسیر WinRM/WSMan به سیستم مقصد آماده نیست؛ WinRM و قاعدهٔ فایروال را روی کلاینت مقصد بررسی کنید.',
        'rpc server is unavailable' => 'سرویس RPC/WMI روی سیستم مقصد در دسترس نیست (فایروال ویندوز یا سرویس WMI را بررسی کنید).',
        '0x800706ba' => 'سرویس RPC/WMI روی سیستم مقصد در دسترس نیست (فایروال ویندوز یا سرویس WMI را بررسی کنید).',
        'no_computer_info' => 'اطلاعاتی از سیستم مقصد دریافت نشد؛ دسترسی WMI/DCOM و فایروال را بررسی کنید.',
        /*
         * 0x80090311 = SEC_E_NO_CREDENTIALS / «there are currently no logon
         * servers available». این خطا در خروجی واقعی کاربر آمد و پیش‌تر هیچ
         * نگاشتی نداشت، پس متن خام Kerberos به کاربر نشان داده می‌شد.
         *
         * معنی دقیقش: ویندوز نتوانست بلیت Kerberos بگیرد، چون کنترل‌کنندهٔ
         * دامنه در دسترس نبود. این یعنی سرور سامانه عضو دامنه است ولی
         * ارتباطش با DC برقرار نیست — یا حساب داده‌شده حساب دامنه نیست و
         * نام کاربری به شکل اشتباه ارسال شده است.
         */
        '0x80090311' => 'سرور نتوانست بلیت Kerberos بگیرد: کنترل‌کنندهٔ دامنه در دسترس نیست یا نام کاربری به شکل یک حساب دامنه‌ای وارد نشده است. روی سرور سامانه «nltest /dsgetdc:<نام-دامنه>» را اجرا کنید — اگر پاسخ نداد، سرور سامانه به DC دسترسی ندارد.',
        'no logon servers' => 'سرور نتوانست بلیت Kerberos بگیرد: کنترل‌کنندهٔ دامنه در دسترس نیست یا نام کاربری به شکل یک حساب دامنه‌ای وارد نشده است. روی سرور سامانه «nltest /dsgetdc:<نام-دامنه>» را اجرا کنید — اگر پاسخ نداد، سرور سامانه به DC دسترسی ندارد.',
        'cannot be loaded because running scripts is disabled' => 'اجرای اسکریپت PowerShell با ExecutionPolicy مسدود است.',
    ];
    foreach ($map as $needle => $message) {
        if (str_contains($lower, $needle)) {
            return $message;
        }
    }
    $short = function_exists('mb_substr') ? mb_substr($raw, 0, 220) : substr($raw, 0, 220);
    return preg_replace('/\s+/u', ' ', $short) ?? $short;
}

/**
 * کاتالوگ خطاهای استخراج: هر خطا یک کد ثابت، یک عنوان کوتاه و راه‌حل گام‌به‌گام دارد.
 * صفحهٔ اسکن با همین کاتالوگ، شکست‌ها را گروه‌بندی می‌کند و راه‌حل هر گروه را نشان می‌دهد.
 */
function domain_scan_error_catalog(): array
{
    return [
        'all_protocols' => [
            'title' => 'هیچ‌کدام از مسیرهای DCOM و WinRM روی مقصد باز نیست',
            'remedy' => [
                'این یعنی حساب، رمز و نام سیستم درست است اما هیچ مسیر شبکه‌ای برای خواندن WMI/CIM باز نیست؛ Domain Admin بودن حساب، به‌تنهایی فایروال خودِ مقصد را باز نمی‌کند.',
                // ۱.۶۳.۱-r17 — تفکیک دو خطایی که کاربران یکی می‌گیرند ولی درمانشان
                // کاملاً متفاوت است. این بخش، پرتکرارترین شکایت «ولی TNI همین کار را
                // می‌کند» را با یک پیام دقیق پاسخ می‌دهد.
                'اگر متن خطا «Access is denied» است (نه «RPC server unavailable»): شبکه و فایروال درست‌اند و بسته به مقصد رسیده؛ ویندوز توکن مدیریتی را فیلتر کرده است. در این حالت WMI از راه دور هر کاری بکنید کار نمی‌کند و باید یا UAC از راه دور اصلاح شود یا حرفه‌ای‌تر، از مسیر SMB استفاده کنید (پایین).',
                'روش درست — همان روشی که Total Network Inventory و Administrator Yar عملاً به کار می‌برند: سامانه روی مقصد، عامل را از C$ کپی می‌کند و با تسک SYSTEM اجرا می‌کند؛ WMI آن‌گاه محلی است و توکن کامل. در نسخهٔ ۱.۶۳.۱-r17 این مسیر خودکار است: هر مقصدی که DCOM/WinRM ردش کند، بار دوم از راه SMB آزموده می‌شود. شرطش فقط دو چیز است: حساب روی مقصد در گروه Administrators محلی باشد و TCP 445 از سرور پنل به مقصد باز باشد.',
                'اگر بالاتر از پیام «روش SMB هم ناموفق بود» دیدید، علتش در همان پیام صریح است: «cannot open \\\\PC\\C$» یعنی حساب روی آن مقصد ادمین محلی نیست؛ «TCP 445 … not reachable» یعنی پورت بسته یا فایروال مقصد مانع است.',
                'اگر می‌خواهید مسیر DCOM کار کند: روی مقصدها با Group Policy قواعد ورودی «Windows Management Instrumentation (WMI-In)» و «Remote Service Management» را فعال کنید؛ TCP 135 و محدودهٔ Dynamic RPC (پیش‌فرض 49152-65535) باید از IP سرور پنل مجاز باشند.',
                'ساده‌ترین راه وقتی SMB هم بسته است: دکمهٔ «آماده‌سازی سیستم‌های مقصد» را بزنید تا پنل با همان حساب، WinRM و قواعد فایروال WMI را روی مقصدها فعال کند (بدون دست‌کاری Group Policy)؛ سپس «تلاش دوباره» را بزنید.',
                'مطمئن شوید سرویس «Windows Management Instrumentation» روی مقصدها روشن است و با حساب اسکن، «آزمون حساب روی یک سیستم» را بزنید؛ اگر DCOM رد شد و WinRM وصل شد، همین حالت را در تنظیمات انتخاب کنید.',
            ],
        ],
        'credentials_missing' => [
            'title' => 'اعتبارنامهٔ امن این نوبت دیگر در دسترس نیست',
            'remedy' => [
                'اعتبارنامه‌ها پس از ۲ ساعت (یا پایان نوبت استخراج) به‌صورت خودکار از نشست پاک می‌شوند و برای امنیت در دیتابیس ذخیره نمی‌شوند.',
                'دوباره در صفحهٔ شناسنامه‌ها، سیستم‌های موردنظر را تیک بزنید، «استخراج اطلاعات سیستم‌های انتخاب‌شده» را بزنید و نام کاربری و رمز حساب را دوباره وارد کنید.',
                'اگر با پیام HTTPS مواجه شدید، ابتدا سامانه را با آدرس HTTPS باز کنید؛ ارسال رمز فقط روی اتصال امن مجاز است.',
            ],
        ],
        'prepare_smb_unreachable' => [
            'title' => 'مسیر SMB روی مقصد بسته است؛ آماده‌سازی راه‌دور ممکن نیست',
            'remedy' => [
                'آماده‌سازی خودکار پنل از طریق SMB (پورت 445) و RPC (پورت 135) انجام می‌شود؛ اگر این مسیرها هم بسته باشند، هیچ راه‌دورِ دیگری وجود ندارد.',
                'اگر فایروال مقصد روشن است، قواعد ورودی «File and Printer Sharing» و «Remote Service Management» را فعال کنید؛ اگر فایروال خاموش است، سرویس‌های Server و Workstation را بررسی کنید.',
                'راه جایگزین: فعال‌سازی WinRM از طریق Group Policy روی کنترلر دامنه (Administrative Templates ← System ← Remoting: Allow remote server management through WinRM) یا دستی روی خودِ مقصد با Enable-PSRemoting -Force.',
            ],
        ],
        'prepare_task_failed' => [
            'title' => 'ساخت یا اجرای تسک آماده‌سازی روی مقصد ناموفق بود',
            'remedy' => [
                'حسابی که در فرم آماده‌سازی وارد می‌کنید باید روی مقصد عضو Administrators محلی باشد؛ Domain Admin بودن به‌تنهایی کافی نیست اگر از گروه Administrators محلی حذف شده باشد.',
                'مطمئن شوید سرویس Task Scheduler روی مقصد روشن است و حساب اسکن قفل (lockout) نشده باشد؛ رمز اشتباه هم همین خطا را می‌دهد.',
                'جزئیات فنیِ پیام سیستم را بخوانید؛ اگر خطا دربارهٔ دسترسی بود، حساب را اصلاح کنید و دوباره «آماده‌سازی» را بزنید.',
            ],
        ],
        'prepare_gpo_not_domain' => [
            'title' => 'سرور پنل عضو دامنه نیست؛ ساخت GPO از داخل پنل ممکن نیست',
            'remedy' => [
                'ساخت و لینک Group Policy فقط از یک سیستم عضو دامنه (یا با RSAT روی سرور پنل) امکان‌پذیر است؛ اگر پنل روی سرور مستقل نصب شده، از دامنه جدا است.',
                'در این حالت روش بی‌نیاز از GPO را انتخاب کنید: دکمهٔ «آماده‌سازی سیستم‌های مقصد» (SMB + تسک) که فایروال باز باشد کار می‌کند.',
                'یا پنل را روی یک سرور عضو دامنه منتقل کنید تا هر دو روش (SMB و GPO) از داخل پنل در دسترس باشند.',
            ],
        ],
        'prepare_gpo_failed' => [
            'title' => 'ساخت یا اعمال Group Policy روی کنترلر دامنه ناموفق بود',
            'remedy' => [
                'حساب واردشده باید «Domain Admin» یا مجوز ساخت و لینک GPO در دامنه را داشته باشد؛ حساب سرویس معمولی نمی‌تواند GPO بسازد.',
                'مطمئن شوید سرور پنل به کنترلر دامنه دسترسی دارد (پورت‌های 5985/5986 برای PowerShell Remoting، یا RPC برای ماژول GroupPolicy) و سرویس WinRM روی DC روشن است.',
                'جزئیات فنی پیام را بخوانید؛ اگر خطا مربوط به «دسترسی» است، مجوز حساب را اصلاح کنید و دوباره تلاش کنید.',
            ],
        ],
        'prepare_gpo_link_failed' => [
            'title' => 'لینک‌کردن Group Policy به دامنه ناموفق بود',
            'remedy' => [
                'حساب واردشده باید اجازهٔ لینک‌کردن GPO به ریشه دامنه را داشته باشد (Domain Admin معمولاً دارد).',
                'اگر GPO از قبل ساخته شده اما لینک نشده، در GPMC آن را دستی به دامنه لینک نکنید؛ دوباره همین دکمه را بزنید تا خودکار لینک شود.',
                'جزئیات فنی پیام را برای علت دقیق بررسی کنید.',
            ],
        ],
        'prepare_verify_failed' => [
            'title' => 'آماده‌سازی انجام شد اما اتصال آزمایشی برقرار نشد',
            'remedy' => [
                'اسکریپت مقصد WinRM را فعال کرده اما نشست CIM هنوز برقرار نشده؛ معمولاً چند ثانیه تا یک دقیقه طول می‌کشد تا سرویس WinRM کامل بالا بیاید.',
                'چند دقیقه صبر کنید و «تلاش دوباره برای ناموفق‌ها» را بزنید؛ اگر هنوز خطا بود، روی مقصد سرویس WinRM را دستی بررسی کنید.',
                'اگر مقصد Windows Server است، مطمئن شوید سرویس WinRM روی «Automatic» است و شنوندهٔ HTTP (پورت 5985) ساخته شده باشد (winrm enumerate listener).',
            ],
        ],
        'rpc_unavailable' => [
            'title' => 'سرویس RPC/WMI سیستم مقصد جواب نمی‌دهد',
            'remedy' => [
                'روی خودِ سیستم مقصد، سرویس Windows Management Instrumentation را روشن نگه دارید (سرویس Winmgmt).',
                'برای مسیر DCOM، قواعد فایروال WMI-In و Remote Service Management را فعال کنید؛ از سرور اسکن به مقصد TCP 135 و محدودهٔ Dynamic RPC (پیش‌فرض ویندوز 49152-65535) باید مجاز باشد.',
                'اگر DCOM مناسب شبکه نیست، WinRM را روی کلاینت‌های مقصد (نه فقط سرور پنل) با Group Policy فعال کنید؛ مسیر فعلی از TCP 5985 استفاده می‌کند. در تنظیمات «اول WinRM» یا «فقط WinRM» را انتخاب کنید.',
                'اگر سیستم مقصد خطای دائمی می‌دهد و فایروال قابل تغییر نیست، از client-agent روی همان سیستم استفاده کنید (بخش ۷.۸ راهنما).',
            ],
        ],
        'winrm_unavailable' => [
            'title' => 'WinRM روی سیستم مقصد آمادهٔ اتصال نیست',
            'remedy' => [
                'WinRM باید روی کلاینت‌های مقصد (نه فقط سرور PHP) فعال باشد؛ برای Windows 10/11 آن را با Group Policy یا Enable-PSRemoting -Force روی خود مقصدها آماده کنید.',
                'قاعدهٔ ورودی Windows Remote Management را در فایروال مقصدها فعال و تا حد امکان به IP سرور اسکن محدود کنید؛ پورت پیش‌فرض این مسیر TCP 5985 است.',
                'حساب دامنه باید روی مقصد عضو Administrators باشد و نام سیستم ترجیحاً با نام DNS/FQDN خوانا باشد.',
                'اگر WinRM را در مقصدها فعال نکرده‌اید، از حالت خودکار/اول DCOM استفاده کنید و WMI-In و RPC را در فایروال دامنه مجاز کنید.',
            ],
        ],
        'access_denied' => [
            'title' => 'دسترسی رد شد (حساب اسکن ادمین محلی مقصد نیست)',
            'remedy' => [
                'حساب اسکن را به گروه Administrators خودِ هر کلاینت اضافه کنید؛ روش سریع: یک گروه AD بسازید، حساب اسکن را عضو آن کنید و با Group Policy آن گروه را به Administrators محلی ببرید.',
                'اگر از حساب محلی (غیر دامنه‌ای) استفاده می‌کنید، روی کلاینت مقدار LocalAccountTokenFilterPolicy را در مسیر HKLM\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\Policies\\System برابر ۱ بگذارید تا UAC دسترسی از راه دور را محدود نکند.',
                'حساب دامنه و رمز درست را در فرم استخراج همان نوبت وارد کنید؛ نام کاربری را به شکل DOMAIN\\user بنویسید و با ابزار tools/diag_connection.php روی یک سیستم آزمون بگیرید.',
                'مطمئن شوید حساب قفل یا منقضی نشده و محدودیت «Log on to» آن اجازهٔ ورود از شبکه را می‌دهد.',
            ],
        ],
        'bad_credentials' => [
            'title' => 'نام کاربری یا رمز حساب اسکن نادرست است',
            'remedy' => [
                'دامنه و نام کاربری را در تنظیمات اسکن دامنه بازبینی کنید (قالب درست: COMPANY\\user).',
                'رمز را دوباره ذخیره کنید؛ اگر رمز حساب عوض شده، همین کادر باید به‌روز شود.',
                'با tools/diag_connection.php --client=<IP> نتیجهٔ آزمون را ببینید.',
            ],
        ],
        'no_computer_info' => [
            'title' => 'اطلاعات رایانه از سیستم مقصد دریافت نشد',
            'remedy' => [
                'نام کامپیوتر یا IP درست را بررسی کنید (ممکن است نام در DNS به سیستمی دیگر اشاره کند).',
                'دسترسی WMI/DCOM همان نکته‌های خطای RPC را لازم دارد؛ فایروال و سرویس WMI را بررسی کنید.',
                'سیستم ممکن است به‌تازگی روشن شده و WMI هنوز آماده نباشد؛ چند دقیقه بعد دوباره تلاش کنید.',
            ],
        ],
        'hardware_empty' => [
            'title' => 'اتصال برقرار شد اما دادهٔ سخت‌افزاری دریافت نشد',
            'remedy' => [
                'این نتیجه فقط اتصال/هویت سیستم را تأیید می‌کند و Inventory موفق محسوب نمی‌شود.',
                'در خروجی self-test سرور، تعداد پردازنده، سیستم‌عامل، RAM و دیسک‌ها را ببینید؛ اگر همه صفرند، حساب سرویس PHP/PowerShell دسترسی WMI لازم را ندارد.',
                'برای اسکن دامنه، حساب تنظیم‌شده باید عضو Administrators محلی هر کلاینت باشد؛ سپس همان سیستم را دوباره اسکن کنید.',
            ],
        ],
        'empty_output' => [
            'title' => 'پاسخ خالی از PowerShell',
            'remedy' => [
                'روی سرور پنل، دستور زیر را اجرا کنید تا اجرای اسکریپت آزاد باشد: Set-ExecutionPolicy -Scope LocalMachine Bypass',
                'اینترنت/آنتی‌ویروس روی سرور پنل ممکن است PowerShell را محدود کرده باشد؛ اسکریپت tools/collect_server_inventory.ps1 را در فهرست استثناها بگذارید.',
            ],
        ],
        'policy_blocked' => [
            'title' => 'اجرای اسکریپت PowerShell روی سرور پنل مسدود است',
            'remedy' => [
                'روی سرور پنل: Set-ExecutionPolicy -Scope LocalMachine RemoteSigned (یا Bypass) و سرویس IIS/Apache را restart کنید.',
                'در سرویس ویندوزی که پنل را اجرا می‌کند، گزینهٔ Group Policy «Turn on Script Execution» باید Allow local scripts باشد.',
            ],
        ],
        'not_windows' => [
            'title' => 'سرور پنل ویندوز نیست',
            'remedy' => [
                'استخراج شناسنامه از راه دور فقط روی سرور Windows کار می‌کند؛ پنل را روی Windows اجرا کنید.',
                'در لینوکس، شناسنامه‌ها را دستی تکمیل کنید یا از client-agent روی کلاینت‌ها استفاده کنید.',
            ],
        ],
        'proc_open_disabled' => [
            'title' => 'تابع proc_open در PHP غیرفعال است',
            'remedy' => [
                'در php.ini مقدار disable_functions را بررسی کنید و proc_open را از آن حذف کنید.',
                'سرویس وب (IIS/Apache) را restart کنید.',
            ],
        ],
        'script_missing' => [
            'title' => 'اسکریپت استخراج روی سرور پیدا نشد',
            'remedy' => [
                'فایل tools/collect_server_inventory.ps1 باید در ریشهٔ برنامه باشد؛ آن را از بستهٔ نصب بازگردانید.',
                'بعد از بازگرداندن فایل، در همین صفحه «تلاش دوباره برای ناموفق‌ها» را بزنید (نیازی به اسکن کامل نیست).',
            ],
        ],
        'timeout' => [
            'title' => 'مهلت استخراج (۴ دقیقه) تمام شد',
            'remedy' => [
                'هر نوبت تعداد کمتری سیستم انتخاب کنید (مثلاً ۱۰ تا ۲۰ سیستم).',
                'سیستم مقصد یا کند است یا WMI آن نیمه‌کاره پاسخ می‌دهد؛ همان سیستم را تنها و جداگانه استخراج کنید.',
            ],
        ],
        'not_reachable' => [
            'title' => 'سیستم در شبکه پاسخ نداد (آفلاین)',
            'remedy' => [
                'سیستم خاموش است یا از سرور اسکن مسیر شبکه‌ای ندارد؛ بعد از روشن‌شدن/اتصال دوباره تلاش کنید.',
                'پینگ ICMP شرط لازم نیست: برنامه علاوه بر پینگ، SMB/RPC/WinRM را هم می‌سنجد؛ در حالت WinRM پورت TCP 5985 بررسی می‌شود.',
                'اگر همهٔ آزمون‌ها بسته‌اند، DNS/IP و مسیر/VLAN را بررسی کنید. بازکردن پینگ به‌تنهایی دسترسی WMI یا WinRM را برقرار نمی‌کند.',
            ],
        ],
        'bad_hostname' => [
            'title' => 'نام کامپیوتر نامعتبر است',
            'remedy' => [
                'ردیف را در فهرست شناسنامه‌ها اصلاح کنید؛ نام باید فقط شامل حرف، رقم، نقطه و خط تیره باشد.',
                'نام درست را با اجرای دستور hostname روی خودِ سیستم بردارید یا در همان ردیف شناسنامه، IP سیستم را ثبت کنید.',
            ],
        ],
        'store_failed' => [
            'title' => 'ذخیرهٔ اطلاعات در دیتابیس ناموفق بود',
            'remedy' => [
                'پیام دقیق دیتابیس را در «گزارش‌های سیستم» ببینید؛ معمولاً به دلیل طول فیلد یا قطع اتصال است.',
                'اگر دیتابیس جداگانه است، فضای دیسک و دسترسی کاربر دیتابیس را بررسی کنید.',
            ],
        ],
        'unknown' => [
            'title' => 'خطای نامشخص',
            'remedy' => [
                'متن خطا را در جدول پایین همین صفحه بخوانید و در «گزارش‌های سیستم» جزئیات کامل را ببینید.',
                'همین سیستم را تنها با «استخراج آزمایشی» (tools/diag_asset_profile.php یا ابزار tools/diag_connection.php) آزمون کنید.',
            ],
        ],
    ];
}

/** کد خطا را از متن پیامِ ذخیره‌شده در صف استخراج تشخیص می‌دهد. */
function domain_scan_error_code(string $message): string
{
    $message = trim($message);
    if ($message === '') {
        return 'unknown';
    }
    $prefixes = [
        // ۱.۶۳.۱-r17 — کدهای مسیر SMB. **اول از همه** می‌آیند: پیامشان
        // ممکن است «Access is denied» یا «TCP 445» را هم داشته باشد، و اگر
        // دیرتر بررسی شوند به‌اشتباه به کد عمومی access_denied می‌افتند.
        // با پیشوند ثابت خودِ اسکریپت تطبیق داده می‌شوند، نه با متن آزاد.
        'smb_access_denied' => ['SMB_ACCESS_DENIED', 'SMB_NOT_ADMIN', 'SMB_BAD_CREDENTIALS', 'cannot open \\', 'cannot create C:\\ITSM'],
        'smb_unreachable' => ['SMB_UNREACHABLE'],
        'smb_task_failed' => ['SMB_TASK_FAILED', 'SMB_COLLECT_TIMEOUT'],
        'smb_bad_output' => ['SMB_COLLECT_BAD_JSON', 'SMB_COLLECTOR_MISSING', 'SMB_BAD_HOST', 'SMB_NO_CREDENTIALS'],
        'prepare_task_failed' => ['ساخت یا اجرای تسک آماده‌سازی'],
        'prepare_verify_failed' => ['آماده‌سازی انجام شد اما اتصال آزمایشی'],
        'prepare_gpo_not_domain' => ['سرور پنل عضو دامنه نیست'],
        'prepare_gpo_link_failed' => ['لینک‌کردن Group Policy'],
        'prepare_gpo_failed' => ['ساخت یا اعمال Group Policy'],
        'prepare_gpo_unavailable' => ['ساخت‌کردن Group Policy از داخل پنل'],
        'winrm_unavailable' => ['مسیر WinRM/WSMan'],
        'rpc_unavailable' => ['سرویس RPC/WMI'],
        'access_denied' => ['دسترسی رد شد'],
        // این دو کد باید زودتر از هر چیز دیگری بررسی شوند: پیام آن‌ها ممکن است
        // رشته‌های تک‌علتی (مثل «دسترسی رد شد» یا «سرویس RPC/WMI») را هم در خود داشته باشد.
        'all_protocols' => ['هیچ‌کدام از مسیرهای DCOM و WinRM', 'ALL_PROTOCOLS_FAILED'],
        'credentials_missing' => ['اعتبارنامهٔ امن این نوبت'],
        'prepare_smb_unreachable' => ['مسیر SMB روی سیستم مقصد'],
        'bad_credentials' => ['نام کاربری یا رمز'],
        'no_computer_info' => ['اطلاعاتی از سیستم مقصد'],
        'hardware_empty' => ['دادهٔ سخت‌افزاری قابل‌استفاده'],
        'policy_blocked' => ['اجرای اسکریپت PowerShell'],
        'not_windows' => ['استخراج اطلاعات از سیستم‌های دامنه فقط روی سرور'],
        'proc_open_disabled' => ['تابع proc_open'],
        'script_missing' => ['اسکریپت tools/collect_server_inventory.ps1'],
        'timeout' => ['مهلت استخراج'],
        'not_reachable' => ['در شبکه پاسخ نداد'],
        'bad_hostname' => ['نام کامپیوتر نامعتبر'],
        'empty_output' => ['پاسخ خالی از PowerShell'],
        'store_failed' => ['ذخیرهٔ اطلاعات ناموفق'],
    ];
    // ۱.۶۳.۱-r17 — کدهای SMB با «شروع پیام» تطبیق داده می‌شوند، نه «هر جای آن».
    //
    // تله‌ای که در آزمون پیدا شد: پیشوند خطای آماده‌سازی، PREPARE_SMB_UNREACHABLE
    // است و رشتهٔ SMB_UNREACHABLE را **داخل خود دارد**. با تطبیق آزاد، خطای
    // آماده‌سازی به‌اشتباه smb_unreachable تشخیص داده می‌شد. خطای واقعی SMB هم
    // همیشه با کد خودش شروع می‌شود (اسکریپت با «SMB_X: …» throw می‌کند)، پس
    // لنگرِ شروع هم دقیق‌تر است و هم ارزان‌تر.
    foreach ($prefixes as $code => $needles) {
        if (str_starts_with($code, 'smb_')) {
            foreach ($needles as $needle) {
                if (str_starts_with($message, $needle)) {
                    return $code;
                }
            }
            continue;
        }
        foreach ($needles as $needle) {
            if (str_contains($message, $needle)) {
                return $code;
            }
        }
    }
    // اگر پیام با هیچ پیشوند SMB شروع نشد ولی خودِ کد داخلش بود (مثلاً وقتی
    // domain_scan_friendly_error متن فارسی را جلوتر گذاشته باشد)، همان کد
    // برمی‌گردد — ولی فقط برای کدهای SMB، تا با PREPARE_* تصادم نکند.
    foreach ($prefixes as $code => $needles) {
        if (!str_starts_with($code, 'smb_')) {
            continue;
        }
        foreach ($needles as $needle) {
            if (str_contains($message, $needle . ':') || str_contains($message, ' ' . $needle . ':')) {
                return $code;
            }
        }
    }
    return 'unknown';
}

/**
 * شکست‌های یک نوبت استخراج را بر اساس علت گروه‌بندی می‌کند.
 * @param array<int,array<string,mixed>> $rows
 * @return array<int,array{code:string,title:string,count:int,hosts:array<int,string>,messages:array<int,string>,remedy:array<int,string>}>
 */
function domain_scan_failure_summary(array $rows): array
{
    $catalog = domain_scan_error_catalog();
    $groups = [];
    foreach ($rows as $row) {
        $status = (string) ($row['status'] ?? '');
        if ($status !== 'error' && $status !== 'offline') {
            continue;
        }
        $message = (string) ($row['message'] ?? '');
        $code = $status === 'offline' ? 'not_reachable' : domain_scan_error_code($message);
        if (!isset($groups[$code])) {
            $groups[$code] = [
                'code' => $code,
                'title' => (string) ($catalog[$code]['title'] ?? $catalog['unknown']['title']),
                'count' => 0,
                'hosts' => [],
                'messages' => [],
                'remedy' => (array) ($catalog[$code]['remedy'] ?? $catalog['unknown']['remedy']),
            ];
        }
        $groups[$code]['count']++;
        if (count($groups[$code]['hosts']) < 12 && trim((string) ($row['hostname'] ?? '')) !== '') {
            $hostLine = trim((string) $row['hostname']);
            $groups[$code]['hosts'][] = function_exists('asset_name_or') ? asset_name_or($hostLine) : $hostLine;
        }
        if ($message !== '' && !in_array($message, $groups[$code]['messages'], true) && count($groups[$code]['messages']) < 3) {
            $groups[$code]['messages'][] = $message;
        }
    }
    usort($groups, static fn (array $a, array $b): int => $b['count'] <=> $a['count']);
    return $groups;
}

/**
 * «تلاش دوباره برای ناموفق‌ها»: نوبت تازه‌ای می‌سازد که فقط سیستم‌های خطادار نوبت قبل در آن است.
 * (سیستم‌های آفلاین و ردیف‌های انجام‌شده تکرار نمی‌شوند.)
 */
function domain_scan_retry_failed(int $runId, int $userId): array
{
    domain_scan_ensure_schema();
    $query = db()->prepare('SELECT hostname, asset_id FROM domain_scan_queue WHERE run_id = ? AND status = "error" ORDER BY id ASC');
    $query->execute([$runId]);
    $rows = $query->fetchAll();
    if ($rows === []) {
        return ['run_id' => 0, 'total' => 0];
    }
    db()->prepare('INSERT INTO domain_scan_runs (started_by, status, total) VALUES (?, "running", ?)')->execute([$userId, count($rows)]);
    $newRunId = (int) db()->lastInsertId();
    $insert = db()->prepare('INSERT INTO domain_scan_queue (run_id, asset_id, hostname, status) VALUES (?, ?, ?, "pending")');
    foreach ($rows as $row) {
        $insert->execute([$newRunId, (int) ($row['asset_id'] ?? 0) ?: null, (string) $row['hostname']]);
    }
    return ['run_id' => $newRunId, 'total' => count($rows)];
}

/** آدرس/نام‌های همین سرور (برای استخراج محلی بدون ساخت نشست از راه دور). */
function domain_scan_local_names(): array
{
    $names = ['localhost', '127.0.0.1', '::1'];
    $host = function_exists('gethostname') ? (string) @gethostname() : '';
    if ($host !== '') {
        $names[] = $host;
        $short = explode('.', $host)[0];
        if ($short !== '') {
            $names[] = $short;
        }
        $resolved = @gethostbyname($host);
        if (is_string($resolved) && $resolved !== '') {
            $names[] = $resolved;
        }
    }
    foreach (['SERVER_NAME', 'SERVER_ADDR'] as $key) {
        $value = trim((string) ($_SERVER[$key] ?? ''));
        if ($value !== '') {
            $names[] = $value;
        }
    }
    return array_values(array_unique(array_filter(array_map(static fn ($v): string => strtolower(trim((string) $v)), $names))));
}

/** پیش‌نیازهای استخراج را بررسی می‌کند؛ در صورت مشکل، پیام روشن برمی‌گرداند. */
function domain_scan_preflight(): ?string
{
    if (PHP_OS_FAMILY !== 'Windows') {
        return 'استخراج اطلاعات از سیستم‌های دامنه فقط روی سرور Windows (با PowerShell) کار می‌کند.';
    }
    if (PHP_SAPI !== 'cli' && !domain_scan_https_request()) {
        return 'برای واردکردن و ارسال امن رمز حساب استخراج، آدرس سامانه باید با HTTPS باز شود.';
    }
    if (!function_exists('proc_open')) {
        return 'تابع proc_open در PHP غیرفعال است؛ آن را از disable_functions در php.ini حذف کنید.';
    }
    if (!is_file(inventory_server_script_path())) {
        return 'اسکریپت tools/collect_server_inventory.ps1 پیدا نشد.';
    }
    return null;
}

/**
 * استخراج سخت‌افزار/نرم‌افزار چند سیستم به‌صورت موازی (هر سیستم یک PowerShell با مهلت مشخص).
 *
 * @param array<int,string> $hosts
 * @return array<string,array{inventory:array<string,mixed>,error:string}> کلید = نام کامپیوتر
 */
function domain_scan_remote_inventory_many(array $hosts, int $concurrency = 4, array $credentials = []): array
{
    $answer = [];
    $problem = domain_scan_preflight();
    $jobs = [];
    $user = trim((string) ($credentials['username'] ?? ''));
    $pass = (string) ($credentials['password'] ?? '');
    $domain = trim((string) setting('domain_scan_domain', ''));
    if ($user === '' || $pass === '') {
        $problem ??= 'برای این نوبت، نام کاربری و رمز حساب مجاز استخراج وارد نشده یا نشست آن منقضی شده است.';
    }
    if ($user !== '' && strpos($user, '\\') === false && strpos($user, '@') === false && $domain !== '') {
        $user = $domain . '\\' . $user;
    }
    $env = [];
    if ($user !== '') {
        $env['ITSM_SCAN_USER'] = $user;
        if ($pass !== '') {
            $env['ITSM_SCAN_PASS'] = $pass;
        }
    }
    // ترتیب پروتکل‌های اتصال (DCOM/WinRM): اگر در تنظیمات مشخص شده باشد به اسکریپت می‌رسد.
    $protocols = trim((string) setting('domain_scan_protocols', ''));
    if ($protocols !== '') {
        $env['ITSM_SCAN_PROTOCOLS'] = $protocols;
    }
    $powershell = domain_scan_powershell_path();
    $script = inventory_server_script_path();
    $localNames = domain_scan_local_names();
    foreach ($hosts as $host) {
        $host = trim((string) $host);
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
            $answer[$host] = ['inventory' => [], 'error' => 'نام کامپیوتر نامعتبر است.'];
            continue;
        }
        if ($problem !== null) {
            $answer[$host] = ['inventory' => [], 'error' => $problem];
            continue;
        }
        // سیستم خودِ سرور: بدون نشست از راه دور (WMI/DCOM) خوانده می‌شود؛ این مسیر هرگز
        // به خطای «دسترسی رد شد» یا «RPC در دسترس نیست» نمی‌خورد.
        $isLocal = in_array(strtolower($host), $localNames, true);
        $command = [$powershell, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $script];
        if (!$isLocal) {
            $command[] = '-ComputerName';
            $command[] = $host;
        }
        $jobs[$host] = [
            'cmd' => $command,
            'env' => $isLocal ? [] : $env,
        ];
    }
    if ($jobs === []) {
        return $answer;
    }
    $results = domain_scan_run_processes($jobs, $concurrency, 240);
    foreach ($jobs as $host => $_job) {
        $row = $results[$host] ?? ['code' => -1, 'out' => '', 'err' => '', 'timed_out' => false, 'started' => false];
        if ($row['timed_out']) {
            $answer[$host] = ['inventory' => [], 'error' => 'مهلت استخراج (۴ دقیقه) تمام شد؛ سیستم بسیار کند است یا WMI پاسخ نمی‌دهد.'];
            continue;
        }
        $out = trim($row['out']);
        // BOM و هر متن اضافه‌ای که PowerShell پیش/پس از JSON می‌نویسد (هشدارها،
        // پیام‌های بارگذاری ماژول یا خروجی‌های رنگی) را حذف می‌کنیم؛ وگرنه
        // json_decode کل خروجی را رد می‌کند و استخراج به‌اشتباه شکست می‌خورد.
        $out = preg_replace('/^\xEF\xBB\xBF/', '', (string) $out) ?? (string) $out;
        $jsonStart = strpos($out, '{');
        $jsonEnd = strrpos($out, '}');
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $out = substr($out, $jsonStart, $jsonEnd - $jsonStart + 1);
        }
        // JSON_INVALID_UTF8_SUBSTITUTE: حضور یک کاراکتر نامعتبر (مثلاً از نام دستگاه‌های قدیمی)
        // نباید کل استخراج را از کار بیندازد؛ آن کاراکتر با U+FFFD جایگزین می‌شود.
        $decoded = $out !== '' ? json_decode($out, true, 512, JSON_INVALID_UTF8_SUBSTITUTE) : null;
        if (!is_array($decoded)) {
            $detail = domain_scan_friendly_error(trim($row['err']) !== '' ? $row['err'] : $row['out']);
            $answer[$host] = ['inventory' => [], 'error' => $detail !== '' ? $detail : 'پاسخ خالی از PowerShell (کد خروج ' . $row['code'] . ').'];
            continue;
        }
        // Remote targets must never inherit fallback values describing the PHP scanner host.
        // Start from a neutral record so a missing optional WMI class stays unknown, not a false match.
        $inventory = inventory_base_inventory();
        $inventory['hostname'] = $host;
        $inventory['serial_number'] = '';
        $inventory['manufacturer'] = '';
        $inventory['model'] = '';
        $inventory['computer_type'] = 'Windows client';
        $inventory['operating_system'] = '';
        $inventory['os_architecture'] = '';
        $inventory['os_serial'] = '';
        $inventory['mac_address'] = '';
        $inventory['cpu'] = '';
        $inventory['memory_mb'] = 0;
        $inventory['domain_username'] = '';
        $inventory['antivirus'] = '';
        $inventory['disks'] = [];
        $inventory['software'] = [];
        $inventory['peripherals'] = ['printers' => [], 'scanners' => [], 'monitors' => [], 'keyboards' => [], 'mice' => []];
        // hardware کاملاً خالی می‌شود تا هیچ شناسهٔ سرورِ پنل (php_version/php_sapi/
        // server_software/os_release که inventory_base_inventory() می‌گذارد) در
        // شناسنامهٔ سیستم مقصد ذخیره نشود.
        $inventory['hardware'] = [];
        $resolved = domain_scan_resolve_ipv4($host);
        $inventory['ip_address'] = $resolved;
        $inventory = inventory_apply_powershell_data($inventory, $decoded);
        if (inventory_hardware_score($inventory) === 0) {
            $answer[$host] = ['inventory' => [], 'error' => 'دادهٔ سخت‌افزاری قابل‌استفاده از سیستم مقصد دریافت نشد؛ پاسخ اتصال به‌تنهایی Inventory موفق نیست.'];
            continue;
        }
        $answer[$host] = ['inventory' => $inventory, 'error' => ''];
    }
    return $answer;
}

/** مسیر اسکریپت استخراج از راه SMB (اجرای عامل روی خود مقصد). */
function domain_scan_smb_script_path(): string
{
    return __DIR__ . '/tools/collect_via_smb.ps1';
}

/**
 * استخراج شناسنامه با روش «اجرای محلی روی مقصد» از راه SMB — ۱.۶۳.۱-r17.
 *
 * این همان روشی است که Total Network Inventory و Administrator Yar عملاً
 * استفاده می‌کنند: عامل روی مقصد کپی و با سرویس/SYSTEM اجرا می‌شود. چرا لازم است،
 * با ترجمهٔ خطای واقعی کاربر:
 *
 *   «Dcom: Access is denied»  ⇒ بسته به مقصد رسیده و احراز هویت شده، ولی توکن
 *                                مدیریتی فیلتر شده (UAC از راه دور) یا حساب
 *                                روی مقصد عضو Administrators نیست.
 *   «Wsman: The client cannot connect…» ⇒ WinRM روی مقصد فعال/شنونده نیست.
 *
 * هر دو، مشکل «مجوزدهی از راه دور» است. با اجرای عامل روی مقصد به‌عنوان SYSTEM،
 * هر دو از میان می‌روند: WMI محلی است و توکن کامل.
 *
 * @param array{host:array<string,array{error:string}>} $failedOnly فقط مقصدهای ناموفق
 * @return array<string,array{inventory:array<string,mixed>,error:string}>
 */
function domain_scan_remote_inventory_via_smb(array $hosts, int $concurrency = 3, array $credentials = []): array
{
    $answer = [];
    $problem = domain_scan_preflight();
    $script = domain_scan_smb_script_path();
    if ($problem === null && !is_file($script)) {
        $problem = 'اسکریپت tools/collect_via_smb.ps1 پیدا نشد.';
    }
    $user = trim((string) ($credentials['username'] ?? ''));
    $pass = (string) ($credentials['password'] ?? '');
    $domain = trim((string) setting('domain_scan_domain', ''));
    if ($problem === null && ($user === '' || $pass === '')) {
        $problem = 'برای این نوبت، نام کاربری و رمز حساب مجاز استخراج وارد نشده یا نشست آن منقضی شده است.';
    }
    if ($user !== '' && strpos($user, '\\') === false && strpos($user, '@') === false && $domain !== '') {
        $user = $domain . '\\' . $user;
    }
    $env = ['ITSM_SCAN_USER' => $user, 'ITSM_SCAN_PASS' => $pass];
    $powershell = domain_scan_powershell_path();
    $localNames = domain_scan_local_names();
    $jobs = [];
    foreach ($hosts as $host) {
        $host = trim((string) $host);
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
            $answer[$host] = ['inventory' => [], 'error' => 'نام کامپیوتر نامعتبر است.'];
            continue;
        }
        if ($problem !== null) {
            $answer[$host] = ['inventory' => [], 'error' => $problem];
            continue;
        }
        // همین سرور از مسیر محلی خوانده می‌شود؛ فرستادن عامل به خودش بی‌معنی است.
        if (in_array(strtolower($host), $localNames, true)) {
            $answer[$host] = ['inventory' => [], 'error' => 'این میزبان خودِ سرور سامانه است؛ از «استخراج محلی» استفاده کنید.'];
            continue;
        }
        $jobs[$host] = [
            'cmd' => [$powershell, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $script, '-ComputerName', $host],
            'env' => $env,
        ];
    }
    if ($jobs === []) {
        return $answer;
    }
    $results = domain_scan_run_processes($jobs, $concurrency, 240);
    foreach ($jobs as $host => $_job) {
        $row = $results[$host] ?? ['code' => -1, 'out' => '', 'err' => '', 'timed_out' => false, 'started' => false];
        if ($row['timed_out']) {
            $answer[$host] = ['inventory' => [], 'error' => 'مهلت استخراج از راه SMB (۴ دقیقه) تمام شد؛ سیستم کند است یا تسک روی مقصد اجرا نشد.'];
            continue;
        }
        $out = trim($row['out']);
        $out = preg_replace('/^\xEF\xBB\xBF/', '', (string) $out) ?? (string) $out;
        $start = strpos($out, '{');
        $end = strrpos($out, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $out = substr($out, $start, $end - $start + 1);
        }
        $decoded = $out !== '' ? json_decode($out, true, 512, JSON_INVALID_UTF8_SUBSTITUTE) : null;
        if (!is_array($decoded) || !isset($decoded['computer'])) {
            $detail = domain_scan_friendly_error(trim($row['err']) !== '' ? $row['err'] : $row['out']);
            $answer[$host] = ['inventory' => [], 'error' => $detail !== '' ? $detail : 'پاسخ خالی از اسکریپت SMB (کد خروج ' . $row['code'] . ').'];
            continue;
        }
        $inventory = inventory_base_inventory();
        $inventory['hostname'] = $host;
        $inventory['serial_number'] = '';
        $inventory['manufacturer'] = '';
        $inventory['model'] = '';
        $inventory['computer_type'] = 'Windows client';
        $inventory['operating_system'] = '';
        $inventory['os_architecture'] = '';
        $inventory['os_serial'] = '';
        $inventory['mac_address'] = '';
        $inventory['cpu'] = '';
        $inventory['memory_mb'] = 0;
        $inventory['domain_username'] = '';
        $inventory['antivirus'] = '';
        $inventory['disks'] = [];
        $inventory['software'] = [];
        $inventory['peripherals'] = ['printers' => [], 'scanners' => [], 'monitors' => [], 'keyboards' => [], 'mice' => []];
        $inventory['hardware'] = [];
        $inventory['ip_address'] = domain_scan_resolve_ipv4($host);
        $inventory = inventory_apply_powershell_data($inventory, $decoded);
        if (inventory_hardware_score($inventory) === 0) {
            $answer[$host] = ['inventory' => [], 'error' => 'دادهٔ سخت‌افزاری قابل‌استفاده از سیستم مقصد دریافت نشد.'];
            continue;
        }
        $answer[$host] = ['inventory' => $inventory, 'error' => ''];
    }
    return $answer;
}

function domain_scan_remote_inventory(string $host, array $credentials = []): array
{
    $GLOBALS['itsm_inventory_last_error'] = '';
    $host = trim($host);
    $many = domain_scan_remote_inventory_many([$host], 1, $credentials);
    $row = $many[$host] ?? ['inventory' => [], 'error' => 'نتیجه‌ای دریافت نشد.'];
    if ($row['inventory'] === []) {
        inventory_set_error($host . ': ' . $row['error']);
        return [];
    }
    return $row['inventory'];
}

function domain_scan_prepare_script_path(): string
{
    return __DIR__ . '/tools/prepare_targets.ps1';
}

function domain_scan_prepare_gpo_script_path(): string
{
    return __DIR__ . '/tools/prepare_domain_gpo.ps1';
}

/**
 * آماده‌سازی کل دامنه از طریق Group Policy — کاملاً خودکار از داخل پنل (بدون GPMC دستی).
 * روی سرور پنلِ عضو دامنه اجرا می‌شود، به یک DC وصل می‌شود، GPO می‌سازد/لینک می‌کند و
 * با Invoke-GPUpdate همان لحظه اعمال می‌کند.
 *
 * @param string[] $hosts  مقصدهایی که باید فوراً gpupdate شوند (می‌تواند خالی باشد)
 * @return array{success:bool,code:string,message:string,details?:array}
 */
function domain_scan_prepare_domain_gpo(array $hosts = [], array $credentials = [], string $mode = 'link'): array
{
    $problem = domain_scan_prepare_preflight();
    if ($problem !== null) {
        return ['success' => false, 'code' => 'unknown', 'message' => $problem];
    }
    if (!is_file(domain_scan_prepare_gpo_script_path())) {
        return ['success' => false, 'code' => 'gpo_unavailable', 'message' => 'اسکریپت آماده‌سازی دامنه (tools/prepare_domain_gpo.ps1) پیدا نشد.'];
    }
    $user = trim((string) ($credentials['username'] ?? ''));
    $pass = (string) ($credentials['password'] ?? '');
    if ($user === '' || $pass === '') {
        return ['success' => false, 'code' => 'prepare_no_credentials', 'message' => 'برای آماده‌سازی دامنه، نام کاربری و رمز حساب ادمین دامنه لازم است.'];
    }
    $domain = trim((string) setting('domain_scan_domain', ''));
    if (strpos($user, '\\') === false && strpos($user, '@') === false && $domain !== '') {
        $user = $domain . '\\' . $user;
    }
    $hosts = array_values(array_filter(array_map(static fn ($host): string => trim((string) $host), $hosts), static fn (string $host): bool => $host !== '' && (bool) preg_match('/^[A-Za-z0-9._-]+$/', $host)));
    $env = ['ITSM_SCAN_USER' => $user, 'ITSM_SCAN_PASS' => $pass];
    $cmd = [
        domain_scan_powershell_path(),
        '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass',
        '-File', domain_scan_prepare_gpo_script_path(),
        '-Mode', $mode === 'unlink' ? 'Unlink' : 'Link',
        '-GpoName', (
            trim((string) setting('domain_scan_gpo_name', '')) !== ''
                ? trim((string) setting('domain_scan_gpo_name', ''))
                : 'ITSM - Remote Inventory (WinRM + WMI)'
        ),
    ];
    if ($hosts !== []) {
        $cmd[] = '-TargetHosts';
        $cmd[] = implode(',', $hosts);
    }
    if ($domain !== '') {
        $cmd[] = '-DomainName';
        $cmd[] = $domain;
    }
    $results = domain_scan_run_processes(['gpo' => ['cmd' => $cmd, 'env' => $env]], 1, 240);
    $row = $results['gpo'] ?? ['code' => -1, 'out' => '', 'err' => '', 'timed_out' => false, 'started' => false];
    if ($row['timed_out']) {
        return ['success' => false, 'code' => 'timeout', 'message' => 'مهلت آماده‌سازی دامنه (۲۴۰ ثانیه) تمام شد؛ اتصال به کنترلر دامنه یا اعمال سیاست طول کشید. دوباره تلاش کنید.'];
    }
    $out = trim($row['out']);
    $out = preg_replace('/^\xEF\xBB\xBF/', '', (string) $out) ?? (string) $out;
    $jsonStart = strpos($out, '{');
    $jsonEnd = strrpos($out, '}');
    if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
        $out = substr($out, $jsonStart, $jsonEnd - $jsonStart + 1);
    }
    $decoded = $out !== '' ? json_decode($out, true, 512, JSON_INVALID_UTF8_SUBSTITUTE) : null;
    if (is_array($decoded) && !empty($decoded['success'])) {
        return [
            'success' => true,
            'code' => 'ok',
            'message' => (string) ($decoded['message'] ?? 'GPO ساخته، لینک و اعمال شد.'),
            'details' => [
                'gpo' => (string) ($decoded['gpo'] ?? ''),
                'domain' => (string) ($decoded['domain'] ?? ''),
                'dc' => (string) ($decoded['dc'] ?? ''),
                'actions' => array_values((array) ($decoded['actions'] ?? [])),
                'warnings' => array_values((array) ($decoded['warnings'] ?? [])),
            ],
        ];
    }
    $detail = domain_scan_friendly_error(trim($row['err']) !== '' ? $row['err'] : $row['out']);
    return [
        'success' => false,
        'code' => domain_scan_error_code($detail),
        'message' => $detail !== '' ? $detail : 'پاسخ خالی از PowerShell (کد خروج ' . $row['code'] . ').',
    ];
}

function domain_scan_prepare_target_script_path(): string
{
    return __DIR__ . '/tools/prepare_remote_target.ps1';
}

/**
 * پیش‌نیازهای آماده‌سازی راه‌دور مقصد (روش الف: SMB + تسک زمان‌بندی‌شده، بدون Group Policy).
 * همان گیت‌های استخراج + وجود هر دو اسکریپت آماده‌سازی.
 */
function domain_scan_prepare_preflight(): ?string
{
    $problem = domain_scan_preflight();
    if ($problem !== null) {
        return $problem;
    }
    if (!is_file(domain_scan_prepare_script_path()) || !is_file(domain_scan_prepare_target_script_path())) {
        return 'اسکریپت‌های آماده‌سازی سیستم‌های مقصد (tools/prepare_targets.ps1) پیدا نشد.';
    }
    return null;
}

/**
 * آماده‌سازی یک یا چند سیستم مقصد برای استخراج: فعال‌سازی WinRM و قواعد فایروال WMI
 * روی خودِ مقصد با اعتبارنامهٔ همین نوبت (روش SMB + تسک زمان‌بندی‌شده؛ بدون GPO).
 * خروجی: [host => ['success' => bool, 'code' => string, 'message' => string]]
 */
function domain_scan_prepare_targets(array $hosts, array $credentials = []): array
{
    $answer = [];
    $problem = domain_scan_prepare_preflight();
    $jobs = [];
    $user = trim((string) ($credentials['username'] ?? ''));
    $pass = (string) ($credentials['password'] ?? '');
    $domain = trim((string) setting('domain_scan_domain', ''));
    if ($user === '' || $pass === '') {
        $problem ??= 'برای این نوبت، نام کاربری و رمز حساب مجاز وارد نشده یا نشست آن منقضی شده است.';
    }
    if ($user !== '' && strpos($user, '\\') === false && strpos($user, '@') === false && $domain !== '') {
        $user = $domain . '\\' . $user;
    }
    $env = [];
    if ($user !== '') {
        $env['ITSM_SCAN_USER'] = $user;
        if ($pass !== '') {
            $env['ITSM_SCAN_PASS'] = $pass;
        }
    }
    $powershell = domain_scan_powershell_path();
    $script = domain_scan_prepare_script_path();
    foreach ($hosts as $host) {
        $host = trim((string) $host);
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
            $answer[$host] = ['success' => false, 'code' => 'bad_hostname', 'message' => 'نام کامپیوتر نامعتبر است.'];
            continue;
        }
        if ($problem !== null) {
            $answer[$host] = ['success' => false, 'code' => 'unknown', 'message' => $problem];
            continue;
        }
        $jobs[$host] = [
            'cmd' => [$powershell, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $script, '-ComputerName', $host],
            'env' => $env,
        ];
    }
    if ($jobs === []) {
        return $answer;
    }
    $results = domain_scan_run_processes($jobs, 3, 150);
    foreach ($jobs as $host => $_job) {
        $row = $results[$host] ?? ['code' => -1, 'out' => '', 'err' => '', 'timed_out' => false, 'started' => false];
        if ($row['timed_out']) {
            $answer[$host] = ['success' => false, 'code' => 'timeout', 'message' => 'مهلت آماده‌سازی (۱۵۰ ثانیه) تمام شد؛ سیستم بسیار کند است یا تسک روی مقصد اجرا نشد.'];
            continue;
        }
        $out = trim($row['out']);
        $out = preg_replace('/^\xEF\xBB\xBF/', '', (string) $out) ?? (string) $out;
        $jsonStart = strpos($out, '{');
        $jsonEnd = strrpos($out, '}');
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $out = substr($out, $jsonStart, $jsonEnd - $jsonStart + 1);
        }
        $decoded = $out !== '' ? json_decode($out, true, 512, JSON_INVALID_UTF8_SUBSTITUTE) : null;
        if (is_array($decoded) && !empty($decoded['success'])) {
            $answer[$host] = ['success' => true, 'code' => 'ok', 'message' => (string) ($decoded['message'] ?? 'آماده‌سازی موفق بود؛ WinRM فعال و اتصال آزمایشی برقرار شد.')];
            continue;
        }
        $detail = domain_scan_friendly_error(trim($row['err']) !== '' ? $row['err'] : $row['out']);
        $answer[$host] = ['success' => false, 'code' => domain_scan_error_code($detail), 'message' => $detail !== '' ? $detail : 'پاسخ خالی از PowerShell (کد خروج ' . $row['code'] . ').'];
    }
    return $answer;
}

function domain_scan_store_inventory(int $assetId, string $host, array $inventory, int $userId): int
{
    if ($assetId <= 0) {
        return 0;
    }
    $receivedInventory = $inventory;
    $existingQuery = db()->prepare('SELECT serial_number, manufacturer, model, computer_type, operating_system, os_architecture, os_serial, ip_address, mac_address, cpu, memory_mb, disks_json, software_json, hardware_json, peripherals_json, domain_username, antivirus FROM assets WHERE id = ? LIMIT 1');
    $existingQuery->execute([$assetId]);
    $existingAsset = $existingQuery->fetch();
    $previousStatus = [];
    if ($existingAsset) {
        $previous = inventory_saved_asset_snapshot($existingAsset);
        $previousStatus = $previous['collection_status'] ?? [];
        $inventory = inventory_merge_missing_values($inventory, $previous);
        if ((int) ($inventory['memory_mb'] ?? 0) <= 0 && (int) ($previous['memory_mb'] ?? 0) > 0) {
            $inventory['memory_mb'] = (int) $previous['memory_mb'];
        }
    }
    $inventory = inventory_apply_collection_status_metadata($inventory, $previousStatus, 'domain');
    /**
     * ۱.۶۳.۴ — نام کامپیوتر در استخراج **بازنویسی نمی‌شود**.
     *
     * چرا: `hostname` کلید هویت یکتای شناینامه است (uq_assets_hostname_key روی
     * hostname_key). اگر اسکن نام تازه‌ای برگرداند که قبلاً به رکورد دیگری تعلق
     * دارد (مثلاً جابه‌جایی دستگاه یا نام قدیمیِ مانده روی IP)، UPDATE با
     * «Duplicate entry … for key uq_assets_hostname_key» شکست می‌خورد و کل
     * استخراج — حتی فیلدهای سالم — از دست می‌رود. کاربر این را به‌صورت
     * «استخراج خطا می‌دهد» می‌دید.
     *
     * رفتار تازه: نام فعلی حفظ می‌شود و نام مشاهده‌شده فقط وقتی ثبت می‌شود که
     * رکورد نامی نداشته باشد. نام واقعیِ دستگاه همچنان در `hardware_json`
     * (کلید `observed_hostname`) و در تاریخچهٔ استخراج می‌ماند تا ادمین خودش
     * دربارهٔ تغییر پلاک تصمیم بگیرد.
     */
    $currentName = '';
    try {
        $nameQuery = db()->prepare('SELECT hostname FROM assets WHERE id = ? LIMIT 1');
        $nameQuery->execute([$assetId]);
        $currentName = trim((string) ($nameQuery->fetchColumn() ?: ''));
    } catch (Throwable $nameError) {
        $currentName = '';
    }
    $observedName = trim($host);
    // ۱.۶۳.۱-r15 — اینجا سه نام داریم و باید درست انتخاب شوند:
    //
    //   $currentName  = آنچه امروز در assets.hostname نشسته (ممکن است نام جعلی
    //                   «client 192.168.1.25» یا «CLIENT-192-168-1-25» باشد)
    //   $observedName = نامی که از AD/صف آمده (همان جعلی، یا نام درست)
    //   $realName     = Win32_ComputerSystem.Name از خودِ ویندوز مقصد
    //
    // قاعدهٔ قبلی «هر نام موجودی را حفظ کن» محافظه‌کارانه بود ولی یک تله داشت:
    // اگر رکورد اول با نام جعلی ساخته شده باشد، آن نام جعلی **هرگز** با نام
    // واقعی جایگزین نمی‌شد — و کاربر تا ابد «client 192.168.1.25» می‌دید.
    //
    // قاعدهٔ تازه: نام واقعی ویندوز بالاترین اولویت را دارد. اگر نبود، نام
    // جاری حفظ می‌شود. اگر نام جاری فقط جعلی است (IP دارد یا CLIENT-<ip> است)
    // و نام واقعی نداریم، دست‌کم همان جعلی می‌ماند — نه اینکه چیزی خراب شود.
    $realName = trim((string) ($inventory['reported_hostname'] ?? ''));
    $currentIsFake = $currentName !== '' && domain_scan_name_is_placeholder($currentName);
    if ($realName !== '') {
        $writeName = $realName;
    } elseif ($currentName !== '' && !$currentIsFake) {
        $writeName = $currentName;
    } elseif ($observedName !== '' && !domain_scan_name_is_placeholder($observedName)) {
        $writeName = $observedName;
    } else {
        $writeName = $currentName !== '' ? $currentName : $observedName;
    }
    if ($observedName !== '' && strcasecmp($observedName, $currentName) !== 0) {
        $inventory['observed_hostname'] = $observedName;
        if (!is_array($inventory['hardware'] ?? null)) {
            $inventory['hardware'] = [];
        }
        $inventory['hardware']['observed_hostname'] = $observedName;
        $inventory['hardware']['hostname_mismatch'] = true;
    }
    try {
            db()->prepare('UPDATE assets SET hostname = ?, serial_number = ?, manufacturer = ?, model = ?, computer_type = ?, operating_system = ?, os_architecture = ?, os_serial = ?, ip_address = ?, mac_address = ?, cpu = ?, memory_mb = ?, disks_json = ?, software_json = ?, hardware_json = ?, peripherals_json = ?, antivirus = ?, source = "domain", last_inventory_at = NOW() WHERE id = ?')->execute([
                $writeName,
                (string) ($inventory['serial_number'] ?? ''),
                (string) ($inventory['manufacturer'] ?? ''),
                (string) ($inventory['model'] ?? ''),
                (string) ($inventory['computer_type'] ?? 'Windows client'),
                (string) ($inventory['operating_system'] ?? ''),
                (string) ($inventory['os_architecture'] ?? ''),
                (string) ($inventory['os_serial'] ?? ''),
                (string) ($inventory['ip_address'] ?? ''),
                (string) ($inventory['mac_address'] ?? ''),
                (string) ($inventory['cpu'] ?? ''),
                max(0, (int) ($inventory['memory_mb'] ?? 0)),
                json_encode($inventory['disks'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
                json_encode($inventory['software'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
                json_encode($inventory['hardware'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
                json_encode($inventory['peripherals'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
                (string) ($inventory['antivirus'] ?? ''),
                $assetId,
            ]);
    } catch (PDOException $storeError) {
        // کلید یکتای نام/هویت: یک برخورد نباید کل استخراج را از بین ببرد.
        // نام فعلی را دست‌نخورده نگه می‌داریم و بقیهٔ فیلدها را بدون نام می‌نویسیم.
        $isDuplicate = ((string) $storeError->getCode() === '23000') || (strpos($storeError->getMessage(), '1062') !== false);
        if (!$isDuplicate) {
            throw $storeError;
        }
        app_error_log('[domain-scan] hostname collision on asset ' . $assetId . ': ' . $storeError->getMessage());
        $inventory['hardware']['hostname_collision'] = $observedName;
        db()->prepare('UPDATE assets SET serial_number = ?, manufacturer = ?, model = ?, computer_type = ?, operating_system = ?, os_architecture = ?, os_serial = ?, ip_address = ?, mac_address = ?, cpu = ?, memory_mb = ?, disks_json = ?, software_json = ?, hardware_json = ?, peripherals_json = ?, antivirus = ?, source = "domain", last_inventory_at = NOW() WHERE id = ?')->execute([
            (string) ($inventory['serial_number'] ?? ''),
            (string) ($inventory['manufacturer'] ?? ''),
            (string) ($inventory['model'] ?? ''),
            (string) ($inventory['computer_type'] ?? 'Windows client'),
            (string) ($inventory['operating_system'] ?? ''),
            (string) ($inventory['os_architecture'] ?? ''),
            (string) ($inventory['os_serial'] ?? ''),
            (string) ($inventory['ip_address'] ?? ''),
            (string) ($inventory['mac_address'] ?? ''),
            (string) ($inventory['cpu'] ?? ''),
            max(0, (int) ($inventory['memory_mb'] ?? 0)),
            json_encode($inventory['disks'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            json_encode($inventory['software'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            json_encode($inventory['hardware'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            json_encode($inventory['peripherals'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            (string) ($inventory['antivirus'] ?? ''),
            $assetId,
        ]);
    }
    if (function_exists('asset_sync_identity_key')) {
        asset_sync_identity_key($assetId);
    }
    sync_inventory_rows($assetId, $inventory, 'domain');
    if (inventory_asset_exists($assetId)) {
        $receivedInventory['received_at'] = date('c');
        db()->prepare('INSERT INTO asset_inventory_history (asset_id, snapshot_json, collected_by) VALUES (?, ?, ?)')->execute([$assetId, json_encode($receivedInventory, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE), $userId > 0 ? $userId : null]);
    }
    // Refresh the mapped machine-derived fields on every successful scan. This intentionally
    // replaces hand edits in those auto-mapped fields; separate plaque/organizational fields are untouched.
    return asset_profile_apply_auto($assetId, asset_profile_auto_values($inventory), $userId, true);
}

function domain_scan_process(int $runId, int $limit, int $userId, array $credentials = []): array
{
    domain_scan_ensure_schema();
    if (function_exists('set_time_limit')) { @set_time_limit(600); }
    if (function_exists('session_write_close')) { @session_write_close(); }
    $limit = max(1, min(40, $limit));
    db()->prepare('UPDATE domain_scan_queue SET status = "pending" WHERE run_id = ? AND status = "running" AND updated_at < (NOW() - INTERVAL 10 MINUTE)')->execute([$runId]);
    $query = db()->prepare('SELECT * FROM domain_scan_queue WHERE run_id = ? AND status = "pending" ORDER BY id ASC LIMIT ' . $limit);
    $query->execute([$runId]);
    $items = $query->fetchAll();

    if ($items !== []) {
        $markRunning = db()->prepare('UPDATE domain_scan_queue SET status = "running" WHERE id = ?');
        $hosts = [];
        $assetIds = [];
        foreach ($items as $item) {
            $markRunning->execute([(int) $item['id']]);
            $hosts[] = (string) $item['hostname'];
            if ((int) ($item['asset_id'] ?? 0) > 0) {
                $assetIds[] = (int) $item['asset_id'];
            }
        }
        // IP ثبت‌شده در شناسنامه: اگر نام کامپیوتر در DNS حل نشد، از آن استفاده می‌شود.
        $fallbackIps = [];
        if ($assetIds !== []) {
            $ipQuery = db()->prepare('SELECT LOWER(hostname) AS h, ip_address FROM assets WHERE id IN (' . implode(',', array_fill(0, count($assetIds), '?')) . ')');
            $ipQuery->execute($assetIds);
            foreach ($ipQuery->fetchAll() as $row) {
                if (trim((string) $row['ip_address']) !== '') {
                    $fallbackIps[(string) $row['h']] = trim((string) $row['ip_address']);
                }
            }
        }
        // ۱) پینگ هم‌زمان همهٔ سیستم‌های این نوبت
        $pingResults = domain_scan_ping_hosts($hosts, $fallbackIps);
        $onlineItems = [];
        foreach ($items as $item) {
            $queueId = (int) $item['id'];
            $assetId = (int) ($item['asset_id'] ?? 0);
            $host = (string) $item['hostname'];
            if (!($pingResults[strtolower($host)] ?? false)) {
                db()->prepare('UPDATE domain_scan_queue SET status = "offline", online = 0, message = "در شبکه پاسخ نداد" WHERE id = ?')->execute([$queueId]);
                if ($assetId > 0) {
                    domain_scan_mark_reachability($assetId, false, $userId);
                }
                db()->prepare('UPDATE domain_scan_runs SET offline = offline + 1, done = done + 1 WHERE id = ?')->execute([$runId]);
                continue;
            }
            if ($assetId > 0) {
                domain_scan_mark_reachability($assetId, true, $userId);
            }
            $onlineItems[] = $item;
        }
        // ۲) استخراج هم‌زمان سیستم‌های آنلاین
        if ($onlineItems !== []) {
            $remote = [];
            try {
                $remote = domain_scan_remote_inventory_many(array_map(static fn (array $row): string => (string) $row['hostname'], $onlineItems), 4, $credentials);
            } catch (Throwable $exception) {
                app_error_log('[domain-scan] remote inventory: ' . $exception->getMessage());
            }
            // ۱.۶۳.۱-r17 — مسیر دوم برای مقصدهایی که WMI/DCOM ردشان می‌کند.
            //
            // خطای «Dcom: Access is denied» ثابت می‌کند بسته رسیده و احراز هویت
            // شده؛ مشکل مجوز از راه دور است، نه شبکه. TNI و AdminYar این حالت را
            // با کپی عامل روی ADMIN$ و اجرای آن به‌عنوان SYSTEM حل می‌کنند. همان
            // مسیر اینجا هم هست، ولی نتیجهٔ زنده را جایگزین نمی‌کند: فقط مقصدهایی
            // که در مسیر اول شکست خورده‌اند دوباره از راه SMB آزموده می‌شوند.
            // اگر حساب روی مقصد ادمین محلی نباشد، این مسیر هم صریحاً همان را می‌گوید.
            $needSmb = [];
            $smbMode = strtolower(trim((string) setting('domain_scan_smb_fallback', '1')));
            foreach ($onlineItems as $item) {
                $hostKey = (string) $item['hostname'];
                $rowFailed = ($remote[$hostKey]['inventory'] ?? []) === [];
                if ($rowFailed && $smbMode !== '0' && $smbMode !== 'off') {
                    $needSmb[] = $hostKey;
                }
            }
            /*
             * ۱.۶۳.۱-r17-و — پیش‌سنجی TCP 445 پیش از آزمون WMI.
             *
             * خروجی واقعی کاربر این را نشان داد: سرور عضو دامنه است، حساب
             * ادمین دامنه است، رمز درست است، فایروال خاموش است، ۴۴۵ باز
             * است — و باز هم مسیر اول «Access is denied» می‌گیرد، چون
             * WMI از راه دور برای این حساب مجاز نشده یا UAC از راه دور
             * توکن را فیلتر می‌کند. در آن شرایط آزمون WMI فقط وقت و
             * خطای گمراه‌کننده تولید می‌کند.
             *
             * ولی شرط «فقط مقصدهای ناموفق» را نگه می‌داریم: اگر WMI برای
             * مقصدی کار می‌کند، همان نتیجه می‌ماند. تغییر فقط این است که
             * پس از شکستِ WMI، به‌جای پیام «استخراج ناموفق»، همان مقصد
             * با روش SMB دوباره آزموده می‌شود — که از قبل بود — و این بار
             * خطای SMB با جزئیات واقعی گزارش می‌شود، نه پیام عمومی.
             */
            if ($needSmb !== []) {
                $smbScript = domain_scan_smb_script_path();
                if (!is_file($smbScript)) {
                    // بی‌صدا شکست نمی‌خوریم: نبودِ فایل باید در لاگ و در پیام دیده شود.
                    app_error_log('[domain-scan] smb fallback skipped: missing ' . $smbScript);
                    system_log('error', 'domain_scan', 'روش SMB اجرا نشد: tools/collect_via_smb.ps1 روی سرور نیست', ['run' => $runId, 'missing' => 'tools/collect_via_smb.ps1']);
                }
                try {
                    $smbRemote = domain_scan_remote_inventory_via_smb($needSmb, 3, $credentials);
                    $smbRecovered = 0;
                    foreach ($smbRemote as $hostKey => $smbRow) {
                        if (($smbRow['inventory'] ?? []) !== []) {
                            $remote[$hostKey] = $smbRow;
                            $remote[$hostKey]['method'] = 'smb';
                            $smbRecovered++;
                        }
                    }
                    if ($smbRecovered > 0) {
                        system_log('info', 'domain_scan', 'استخراج با روش SMB/SYSTEM جبران شد', ['run' => $runId, 'recovered' => $smbRecovered, 'hosts' => array_keys(array_filter($smbRemote, static fn (array $r): bool => ($r['inventory'] ?? []) !== []))]);
                    }
                } catch (Throwable $exception) {
                    app_error_log('[domain-scan] smb fallback: ' . $exception->getMessage());
                }
            }
            foreach ($onlineItems as $item) {
                $queueId = (int) $item['id'];
                $assetId = (int) ($item['asset_id'] ?? 0);
                $host = (string) $item['hostname'];
                try {
                    $row = $remote[$host] ?? ['inventory' => [], 'error' => 'نتیجه‌ای از استخراج دریافت نشد.'];
                    if ($row['inventory'] === []) {
                        $message = (string) ($row['error'] ?: 'خطای نامشخص');
                        db()->prepare('UPDATE domain_scan_queue SET status = "error", online = 1, message = ? WHERE id = ?')->execute([function_exists('mb_substr') ? mb_substr($message, 0, 250) : substr($message, 0, 250), $queueId]);
                        if (function_exists('system_log')) {
                            system_log('error', 'domain_scan', 'استخراج ناموفق: ' . $host, ['run' => $runId, 'error' => $message]);
                        }
                        db()->prepare('UPDATE domain_scan_runs SET failed = failed + 1, done = done + 1 WHERE id = ?')->execute([$runId]);
                        continue;
                    }
                    $filled = domain_scan_store_inventory($assetId, $host, $row['inventory'], $userId);
                    if ($assetId > 0) {
                        asset_profile_apply_auto($assetId, ['ad_online' => 1, 'ad_last_seen' => date('Y-m-d H:i:s'), 'ad_scanned_at' => date('Y-m-d H:i:s')], $userId, true);
                        record_asset_history($assetId, 'inventory_collected', 'استخراج از شبکه دامنه', $userId, null, (($row['method'] ?? '') === 'smb') ? 'اطلاعات سخت‌افزاری با اجرای عامل روی خود سیستم (SMB/SYSTEM) دریافت شد.' : 'اطلاعات سخت‌افزاری از طریق دامنه دریافت شد.');
                    }
                    // ۱.۶۳.۱-r17 — روش موفق را در پیام صف می‌نویسیم. تا وقتی کاربر
                    // نمی‌داند کدام مسیر کار کرد، هر اختلال بعدی دوباره از صفر شروع می‌شود.
                    $methodLabel = (($row['method'] ?? '') === 'smb') ? ' (از طریق SMB)' : '';
                    db()->prepare('UPDATE domain_scan_queue SET status = "done", online = 1, message = ? WHERE id = ?')->execute([($filled > 0 ? ($filled . ' فیلد شناسنامه پر شد') : 'اطلاعات تازه‌ای نبود') . $methodLabel, $queueId]);
                    db()->prepare('UPDATE domain_scan_runs SET online = online + 1, done = done + 1 WHERE id = ?')->execute([$runId]);
                } catch (Throwable $exception) {
                    db()->prepare('UPDATE domain_scan_queue SET status = "error", online = 1, message = ? WHERE id = ?')->execute([substr('ذخیرهٔ اطلاعات ناموفق: ' . $exception->getMessage(), 0, 250), $queueId]);
                    db()->prepare('UPDATE domain_scan_runs SET failed = failed + 1, done = done + 1 WHERE id = ?')->execute([$runId]);
                }
            }
        }
    }
    $remainingQuery = db()->prepare('SELECT COUNT(*) FROM domain_scan_queue WHERE run_id = ? AND status IN ("pending", "running")');
    $remainingQuery->execute([$runId]);
    $pending = (int) $remainingQuery->fetchColumn();
    if ($pending === 0) {
        db()->prepare('UPDATE domain_scan_runs SET status = "finished" WHERE id = ?')->execute([$runId]);
    }
    return ['pending' => $pending, 'processed' => count($items)];
}

function domain_scan_run(int $runId): ?array
{
    domain_scan_ensure_schema();
    $query = db()->prepare('SELECT * FROM domain_scan_runs WHERE id = ? LIMIT 1');
    $query->execute([$runId]);
    $run = $query->fetch();
    return $run ?: null;
}

function domain_scan_queue_rows(int $runId, int $limit = 100, string $sort = '', string $dir = ''): array
{
    $sortMap = ['name' => 'hostname', 'status' => 'status', 'online' => 'online', 'message' => 'message', '__tie' => 'id'];
    $order = log_sort_order($sortMap, $sort, $dir, ' ORDER BY (status = "error") DESC, (status = "offline") DESC, id ASC');
    $query = db()->prepare('SELECT * FROM domain_scan_queue WHERE run_id = ?' . $order . ' LIMIT ' . max(1, min(1000, $limit)));
    $query->execute([$runId]);
    return $query->fetchAll();
}

function domain_scan_latest_run(): ?array
{
    domain_scan_ensure_schema();
    $query = db()->query('SELECT * FROM domain_scan_runs ORDER BY id DESC LIMIT 1');
    $run = $query->fetch();
    return $run ?: null;
}
