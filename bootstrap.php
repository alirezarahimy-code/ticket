<?php
declare(strict_types=1);

const APP_ROOT = __DIR__;

require_once __DIR__ . '/permissions.php';

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'path' => '/',
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
    ]);
    session_start();
}

$configFile = (string) (getenv('ITSM_CONFIG_PATH') ?: APP_ROOT . '/config.php');
if (!is_file($configFile)) {
    header('Location: install.php');
    exit;
}

$config = require $configFile;
ini_set('display_errors', '0');
ini_set('log_errors', '1');
$cspNonce = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');

function csp_nonce(): string
{
    global $cspNonce;
    return (string) $cspNonce;
}

// Enforce HTTPS in production
if (isset($config['security']['enforce_https']) && $config['security']['enforce_https'] === true) {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
        $httpsUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $httpsUrl, true, 301);
        exit;
    }
}

if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; script-src 'self' 'nonce-{$cspNonce}'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'");
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    // Additional security headers
    header('X-XSS-Protection: 1; mode=block');
    header('X-Permitted-Cross-Domain-Policies: none');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
}

function cfg(string $path, mixed $default = null): mixed
{
    global $config;
    $value = $config;
    foreach (explode('.', $path) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

date_default_timezone_set((string) cfg('app.timezone', 'Asia/Tehran'));

/** PDO با اندازه‌گیری زمان query() مستقیم (برای کوئری‌هایی که با prepare نمی‌آیند). */
class AppPDO extends PDO
{
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $start = microtime(true);
        $result = $fetchMode === null
            ? parent::query($query)
            : parent::query($query, $fetchMode, ...$fetchModeArgs);
        $ms = (microtime(true) - $start) * 1000;
        if ($ms >= 500 && empty($GLOBALS['__slow_query_guard'])) {
            $GLOBALS['__slow_query_guard'] = true;
            try {
                system_log('warning', 'کندی کوئری', 'یک کوئری ' . (int) round($ms) . ' میلی‌ثانیه طول کشید', [
                    'cause' => 'کوئری سنگین یا نبودن ایندکس مناسب روی جدول بزرگ.',
                    'action' => 'اگر تکرار شد، بازهٔ گزارش را کوچک‌تر کنید یا برای توسعه‌دهنده ارسال کنید.',
                    'technical' => mb_substr($query, 0, 300),
                    'ms' => (int) round($ms),
                ]);
            } catch (Throwable) {
            } finally {
                $GLOBALS['__slow_query_guard'] = false;
            }
        }
        return $result;
    }
}

/**
 * اجراکنندهٔ کوئری که کوئری‌های کندتر از ۵۰۰ میلی‌ثانیه را در لاگ فنی ثبت می‌کند.
 * پارامترها ثبت نمی‌شوند؛ فقط متن کوئری کوتاه‌شده. ثبت لاگ خودش را دوباره اندازه نمی‌گیرد.
 */
class AppSlowStatement extends PDOStatement
{
    public function execute(?array $params = null): bool
    {
        $start = microtime(true);
        $result = parent::execute($params);
        $ms = (microtime(true) - $start) * 1000;
        if ($ms >= 500 && empty($GLOBALS['__slow_query_guard'])) {
            $GLOBALS['__slow_query_guard'] = true;
            try {
                system_log('warning', 'کندی کوئری', 'یک کوئری ' . (int) round($ms) . ' میلی‌ثانیه طول کشید', [
                    'cause' => 'کوئری سنگین یا نبودن ایندکس مناسب روی جدول بزرگ.',
                    'action' => 'اگر تکرار شد، بازهٔ گزارش را کوچک‌تر کنید یا برای توسعه‌دهنده ارسال کنید.',
                    'technical' => mb_substr((string) $this->queryString, 0, 300),
                    'ms' => (int) round($ms),
                ]);
            } catch (Throwable) {
            } finally {
                $GLOBALS['__slow_query_guard'] = false;
            }
        }
        return $result;
    }
}

function db(bool $reconnect = false): PDO
{
    static $pdo = null;
    if ($reconnect) {
        $pdo = null;
    }
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    global $config;
    // اگر به هر دلیل config خالی شده، دوباره از فایل بارگذاری شود
    $user = (string) cfg('database.user', '');
    $host = (string) cfg('database.host', '');
    if ($user === '' || $host === '') {
        $configFile = (string) (getenv('ITSM_CONFIG_PATH') ?: (APP_ROOT . '/config.php'));
        if (is_file($configFile)) {
            $reloaded = require $configFile;
            if (is_array($reloaded)) {
                $config = $reloaded;
            }
        }
        $user = (string) cfg('database.user', '');
        $host = (string) cfg('database.host', '');
    }
    if ($user === '' || $host === '') {
        throw new RuntimeException('تنظیمات دیتابیس خالی است. مسیر config.php و ITSM_CONFIG_PATH را بررسی کنید: ' . (string) (getenv('ITSM_CONFIG_PATH') ?: (APP_ROOT . '/config.php')));
    }
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $host,
        (int) cfg('database.port', 3306),
        (string) cfg('database.name', ''),
        (string) cfg('database.charset', 'utf8mb4')
    );
    $pdo = new AppPDO($dsn, $user, (string) cfg('database.password', ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STATEMENT_CLASS => [AppSlowStatement::class],
    ]);
    // هم‌زمان کردن ساعت دیتابیس با ساعت برنامه: CURDATE()/CURTIME() باید با date() در PHP یکی باشد.
    try {
        $offset = (new DateTimeImmutable('now', new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('P');
        $pdo->exec("SET time_zone = '" . $offset . "'");
    } catch (Throwable) {
        // اگر تنظیم نشد، همان ساعت پیش‌فرض سرور دیتابیس استفاده می‌شود.
    }
    return $pdo;
}

function db_table_exists(string $table): bool
{
    static $cache = [];
    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }
    try {
        static $all = null;
        if ($all === null) {
            $all = array_flip(array_map('strval', db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN)));
        }
        if (isset($all[$table])) {
            return $cache[$table] = true;
        }
        // جدولی که تازه ساخته شده را از فهرست قدیمی تشخیص نمی‌دهیم؛ برای نفی، یک بار دیگر دقیق بررسی می‌کنیم.
        $exists = in_array($table, array_map('strval', db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN)), true);
        if ($exists) {
            $all[$table] = true;
        }
        return $cache[$table] = $exists;
    } catch (Throwable) {
        return $cache[$table] = false;
    }
}

function db_table_columns(string $table): array
{
    static $cache = [];
    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }
    if (!db_table_exists($table)) {
        return $cache[$table] = [];
    }
    try {
        $query = db()->prepare('SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ?');
        $query->execute([$table]);
        return $cache[$table] = array_fill_keys(array_map('strval', $query->fetchAll(PDO::FETCH_COLUMN)), true);
    } catch (Throwable) {
        return $cache[$table] = [];
    }
}

function db_missing_columns(string $table, array $required): array
{
    $columns = db_table_columns($table);
    return array_values(array_filter($required, static fn (string $column): bool => !isset($columns[$column])));
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

// Rate limiting for API endpoints
// بازمی‌گرداند true اگر مجاز باشد. اگر $retryAfter داده شود، در صورت رد شدن ثانیه‌های باقی‌مانده تا پایان پنجره را می‌گیرد.
function api_rate_limit(string $key, int $maxRequests = 60, int $windowSeconds = 60, ?int &$retryAfter = null): bool
{
    $retryAfter = 0;
    $ip = client_ip();
    $rateKey = hash_hmac('sha256', $key . '|' . $ip, (string) cfg('app.key', 'itsm-rate-key'));

    try {
        $query = db()->prepare('SELECT attempts, UNIX_TIMESTAMP(window_started) AS started_ts FROM api_rate_limits WHERE rate_key = ? LIMIT 1');
        $query->execute([$rateKey]);
        $row = $query->fetch();

        if (!$row || (int) $row['started_ts'] < time() - $windowSeconds) {
            db()->prepare('INSERT INTO api_rate_limits (rate_key, attempts, window_started) VALUES (?, 1, NOW()) ON DUPLICATE KEY UPDATE attempts = 1, window_started = NOW()')->execute([$rateKey]);
            return true;
        }

        $attempts = (int) $row['attempts'] + 1;
        if ($attempts > $maxRequests) {
            $retryAfter = max(1, (int) $row['started_ts'] + $windowSeconds - time());
            return false;
        }

        db()->prepare('UPDATE api_rate_limits SET attempts = ? WHERE rate_key = ?')->execute([$attempts, $rateKey]);
        return true;
    } catch (Throwable) {
        return true; // Fail open - don't block requests if rate limiting fails
    }
}

function check_api_rate_limit(string $key, int $maxRequests = 60, int $windowSeconds = 60): void
{
    $retryAfter = 0;
    if (!api_rate_limit($key, $maxRequests, $windowSeconds, $retryAfter)) {
        $retryAfter = max(1, $retryAfter);
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header('Retry-After: ' . $retryAfter);
        // فیلدهای error/message هر دو پر می‌شوند تا پنل‌های مختلف پیام فارسی را نشان دهند.
        $message = 'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً ' . $retryAfter . ' ثانیه صبر کنید و دوباره تلاش کنید.';
        echo json_encode(['ok' => false, 'error' => $message, 'message' => $message, 'retry_after' => $retryAfter, 'code' => 'rate_limited'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function login_rate_key(string $username): string
{
    return hash_hmac('sha256', strtolower(trim($username)) . '|' . client_ip(), (string) cfg('app.key', 'itsm-rate-key'));
}

function login_rate_allowed(string $username): bool
{
    try {
        $query = db()->prepare('SELECT attempts, window_started, locked_until FROM login_attempts WHERE attempt_key = ? LIMIT 1');
        $query->execute([login_rate_key($username)]);
        $row = $query->fetch();
        if (!$row) {
            return true;
        }
        if (!empty($row['locked_until']) && strtotime((string) $row['locked_until']) > time()) {
            return false;
        }
        if (strtotime((string) $row['window_started']) < time() - 900) {
            db()->prepare('DELETE FROM login_attempts WHERE attempt_key = ?')->execute([login_rate_key($username)]);
        }
        return true;
    } catch (Throwable) {
        return true;
    }
}

function record_login_failure(string $username): void
{
    try {
        $key = login_rate_key($username);
        $query = db()->prepare('SELECT attempts, window_started FROM login_attempts WHERE attempt_key = ? LIMIT 1');
        $query->execute([$key]);
        $row = $query->fetch();
        if (!$row || strtotime((string) $row['window_started']) < time() - 900) {
            db()->prepare('INSERT INTO login_attempts (attempt_key, attempts, window_started, locked_until) VALUES (?, 1, NOW(), NULL) ON DUPLICATE KEY UPDATE attempts = 1, window_started = NOW(), locked_until = NULL')->execute([$key]);
            if (function_exists('activity_log')) {
                activity_log(['action_code' => 'login_failed', 'username' => $username, 'module' => 'auth', 'meta' => ['attempts' => 1]]);
            }
            return;
        }
        $attempts = (int) $row['attempts'] + 1;
        $lockedUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
        db()->prepare('UPDATE login_attempts SET attempts = ?, locked_until = ? WHERE attempt_key = ?')->execute([$attempts, $lockedUntil, $key]);
        if (function_exists('activity_log')) {
            activity_log([
                'action_code' => $lockedUntil ? 'login_locked' : 'login_failed',
                'username' => $username,
                'module' => 'auth',
                'meta' => ['attempts' => $attempts],
            ]);
        }
    } catch (Throwable) {
        app_error_log('ITSM login rate-limit storage failed');
    }
}

function clear_login_failures(string $username): void
{
    try {
        db()->prepare('DELETE FROM login_attempts WHERE attempt_key = ?')->execute([login_rate_key($username)]);
    } catch (Throwable) {
        app_error_log('ITSM login rate-limit cleanup failed');
    }
}

/** نسخهٔ فعلی سامانه از فایل VERSION (نمایش در ورود و پایین صفحات). */
function app_version(): string
{
    static $version = null;
    if ($version === null) {
        $raw = @file_get_contents(APP_ROOT . '/VERSION');
        $version = is_string($raw) && trim($raw) !== '' ? trim($raw) : '—';
    }
    return $version;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * ── هویت نصب و تشخیص «دیتابیس خام» ────────────────────────────────────────────
 * چرا لازم است؟ داده‌های پنل غذا در مرورگر (localStorage) کش می‌شوند. اگر سامانه روی
 * دیتابیس تازه/خالی نصب شود، آن داده‌های قدیمی در مرورگر کاربران باقی می‌مانند و
 * چیزی نمایش داده می‌شود که در دیتابیس وجود ندارد. با این «توکن نصب» هر مرورگر
 * می‌فهمد کشش به دیتابیس دیگری تعلق داشته و آن را دور می‌ریزد.
 */
function db_install_token(bool $create = false): string
{
    if (array_key_exists('__db_install_token', $GLOBALS)) {
        return (string) $GLOBALS['__db_install_token'];
    }
    $token = '';
    try {
        $token = trim((string) (setting('db_install_token') ?? ''));
    } catch (Throwable) {
        $token = '';
    }
    if ($token === '' && $create) {
        $token = bin2hex(random_bytes(12));
        try {
            save_setting('db_install_token', $token);
        } catch (Throwable) {
            // ذخیره نشد؛ توکن فقط برای همین درخواست معتبر است
        }
    }
    $GLOBALS['__db_install_token'] = $token;
    return $token;
}

/** پاک‌کردن کش توکن (برای ابزار تشخیص و آزمون‌ها). */
function db_install_token_forget(): void
{
    unset($GLOBALS['__db_install_token']);
}

/** جدول‌هایی که «خالی بودن» دیتابیس را تعیین می‌کنند. */
function db_install_fresh_tables(): array
{
    // جدول food_ticket_config عمداً نیست: یک ردیف پیش‌فرض ساختاری دارد و همیشه ۱ است.
    return ['users', 'tickets', 'ticket_messages', 'assets', 'food_ticket_events', 'food_ticket_group_runs', 'food_ticket_groups', 'food_ticket_guest_cards'];
}

/**
 * وضعیت نصب برای مرورگر: توکن، خالی‌بودن دیتابیس و شمارندهٔ رکوردها.
 *
 * @return array{token:string, fresh:bool, existing:bool, counts:array<string,int>, checked_at:string}
 */
function db_install_state(bool $refresh = false): array
{
    static $state = null;
    if ($state !== null && !$refresh) {
        return $state;
    }
    if ($refresh) {
        db_install_token_forget();
    }
    $counts = [];
    $anyTable = false;
    foreach (db_install_fresh_tables() as $table) {
        if (!db_table_exists($table)) {
            continue;
        }
        $anyTable = true;
        try {
            // فقط می‌دانیم جدول خالی است یا نه؛ COUNT(*) روی جدول‌های بزرگ (مثلاً رویدادهای کارت) هر درخواست را کند می‌کرد.
            $counts[$table] = db()->query('SELECT 1 FROM `' . $table . '` LIMIT 1')->fetchColumn() ? 1 : 0;
        } catch (Throwable) {
            $counts[$table] = 0;
        }
    }
    $token = db_install_token(true);
    $total = array_sum($counts);
    return $state = [
        'token' => $token,
        'fresh' => $token === '' || $total === 0,
        'existing' => $anyTable,
        'counts' => $counts,
        'checked_at' => date('c'),
    ];
}

/**
 * ── تراکنش‌های محافظت‌شده ─────────────────────────────────────────────────────
 * چرا لازم است؟ هر DDL یا برخی خطاها می‌توانند تراکنش فعال را ببندند؛ در آن حالت
 * db()->commit() خطای «There is no active transaction» می‌دهد و کاربر پیامی می‌بیند
 * که کار انجام نشده، در حالی که انجام شده است.
 * این دو تابع: اگر تراکنشی باز بود commit/rollback می‌کنند، وگرنه وضعیت را در لاگ
 * ثبت می‌کنند و false برمی‌گردانند (بدون پیام کاربر).
 */
function db_in_transaction(): bool
{
    try {
        return db()->inTransaction();
    } catch (Throwable) {
        return false;
    }
}

function db_commit(string $context = ''): bool
{
    try {
        if (db()->inTransaction()) {
            return db()->commit();
        }
        if (function_exists('system_log')) {
            system_log('warning', 'transaction', 'commit روی تراکنشِ بسته‌شده (COMMIT ضمنی) — کار انجام شده است.', ['context' => $context]);
        }
        return false;
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'transaction', 'commit ناموفق: ' . $exception->getMessage(), ['context' => $context]);
        }
        return false;
    }
}

function db_rollback(string $context = ''): bool
{
    try {
        if (db()->inTransaction()) {
            return db()->rollBack();
        }
        return false;
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'transaction', 'rollback ناموفق: ' . $exception->getMessage(), ['context' => $context]);
        }
        return false;
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function ticket_service_ensure_schema(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;
    try {
        $categoryColumns = db()->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('code', array_map('strtolower', $categoryColumns), true)) {
            db()->exec('ALTER TABLE categories ADD COLUMN code VARCHAR(60) NULL UNIQUE');
        }
        $serviceColumns = db()->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_catalog'")->fetchAll(PDO::FETCH_COLUMN);
        $serviceHave = array_map('strtolower', $serviceColumns);
        if (!in_array('category_id', $serviceHave, true)) {
            db()->exec('ALTER TABLE service_catalog ADD COLUMN category_id INT UNSIGNED NULL AFTER department_id');
        }
        $categorySeed = db()->prepare('INSERT INTO categories (name, code, service_group) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), service_group = VALUES(service_group), is_active = 1');
        foreach (ticket_service_categories() as $code => $meta) {
            $categorySeed->execute([$meta['name'], $code, $meta['group']]);
        }
        $categoryIds = db()->query('SELECT code, id FROM categories WHERE code IS NOT NULL')->fetchAll(PDO::FETCH_KEY_PAIR);
        $map = ticket_service_category_map();
        $update = db()->prepare('UPDATE service_catalog SET category_id = ? WHERE code = ? AND (category_id IS NULL OR category_id = 0)');
        foreach ($map as $serviceCode => $categoryCode) {
            if (!isset($categoryIds[$categoryCode])) {
                continue;
            }
            $update->execute([(int) $categoryIds[$categoryCode], $serviceCode]);
        }
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'ticket_service_schema', $exception->getMessage());
        }
    }
}

function ticket_service_categories(): array
{
    return [
        'IT-CAT-HW' => ['name' => 'سخت‌افزار', 'group' => 'it'],
        'IT-CAT-SW' => ['name' => 'نرم‌افزار', 'group' => 'it'],
        'IT-CAT-NET' => ['name' => 'شبکه و اینترنت', 'group' => 'it'],
        'IT-CAT-ACC' => ['name' => 'حساب کاربری و دسترسی', 'group' => 'it'],
        'IT-CAT-PRINT' => ['name' => 'چاپ و اسکن', 'group' => 'it'],
        'IT-CAT-PERIPH' => ['name' => 'تجهیزات جانبی', 'group' => 'it'],
        'IT-CAT-BACKUP' => ['name' => 'پشتیبان‌گیری و بازیابی', 'group' => 'it'],
        'IT-CAT-SETUP' => ['name' => 'نصب و راه‌اندازی', 'group' => 'it'],
        'IT-CAT-MISC' => ['name' => 'سایر خدمات فناوری اطلاعات', 'group' => 'it'],
        'SUP-CAT-OFFICE' => ['name' => 'تجهیزات و فضای اداری', 'group' => 'support'],
        'SUP-CAT-UTIL' => ['name' => 'تأسیسات و انرژی', 'group' => 'support'],
        'SUP-CAT-FURN' => ['name' => 'اثاثیه', 'group' => 'support'],
        'SUP-CAT-CLEAN' => ['name' => 'خدمات و نظافت', 'group' => 'support'],
        'SUP-CAT-STAT' => ['name' => 'ملزومات اداری', 'group' => 'support'],
        'SUP-CAT-GEN' => ['name' => 'خدمات عمومی', 'group' => 'support'],
        'SUP-CAT-MISC' => ['name' => 'سایر خدمات پشتیبانی', 'group' => 'support'],
    ];
}

function ticket_service_category_map(): array
{
    return [
        'IT-COMPUTER' => 'IT-CAT-HW', 'IT-BOOT' => 'IT-CAT-HW', 'IT-BLUE' => 'IT-CAT-HW', 'IT-HARDWARE' => 'IT-CAT-HW',
        'IT-PROFILE' => 'IT-CAT-SETUP', 'IT-DRIVER' => 'IT-CAT-SETUP', 'IT-INSTALL-PC' => 'IT-CAT-SETUP', 'IT-INVENTORY' => 'IT-CAT-SETUP',
        'IT-SOFTWARE' => 'IT-CAT-SW', 'IT-SOFT-ERROR' => 'IT-CAT-SW',
        'IT-PRINTER' => 'IT-CAT-PRINT',
        'IT-PERIPHERAL' => 'IT-CAT-PERIPH', 'IT-MOVE' => 'IT-CAT-PERIPH',
        'IT-NET' => 'IT-CAT-NET', 'IT-SHARE' => 'IT-CAT-NET',
        'IT-ACCOUNT' => 'IT-CAT-ACC', 'IT-ACCESS' => 'IT-CAT-ACC', 'IT-EMAIL' => 'IT-CAT-ACC',
        'IT-BACKUP' => 'IT-CAT-BACKUP',
        'IT-OTHER' => 'IT-CAT-MISC',
        'SUP-OFFICE' => 'SUP-CAT-OFFICE',
        'SUP-ELEC' => 'SUP-CAT-UTIL', 'SUP-COOLING' => 'SUP-CAT-UTIL',
        'SUP-FURN' => 'SUP-CAT-FURN',
        'SUP-CLEAN' => 'SUP-CAT-CLEAN',
        'SUP-STATIONERY' => 'SUP-CAT-STAT',
        'SUP-CDDVD' => 'SUP-CAT-GEN', 'SUP-GENERAL' => 'SUP-CAT-GEN',
        'SUP-OTHER' => 'SUP-CAT-MISC',
    ];
}

function system_logs_ensure(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;
    try {
        db()->exec('CREATE TABLE IF NOT EXISTS system_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            level VARCHAR(20) NOT NULL DEFAULT "error",
            context VARCHAR(120) NULL,
            message TEXT NULL,
            meta_json JSON NULL,
            user_id INT UNSIGNED NULL,
            request_uri VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_system_logs_created (created_at),
            INDEX idx_system_logs_level (level)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    } catch (Throwable) {
    }
}

function system_log(string $level, string $context, string $message, array $meta = []): void
{
    $level = in_array($level, ['debug', 'info', 'warning', 'error'], true) ? $level : 'error';
    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    try {
        system_logs_ensure();
        db()->prepare('INSERT INTO system_logs (level, context, message, meta_json, user_id, request_uri) VALUES (?, ?, ?, ?, ?, ?)')->execute([$level, substr($context, 0, 120), $message, $meta !== [] ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null, $userId, substr($uri, 0, 255)]);
    } catch (Throwable) {
    }
    try {
        $dir = APP_ROOT . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $line = date('c') . ' [' . $level . '] ' . $context . ' — ' . $message . ($meta !== [] ? ' ' . json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '') . PHP_EOL;
        @file_put_contents($dir . '/app.log', $line, FILE_APPEND);
    } catch (Throwable) {
    }
}

/**
 * ترجمهٔ فارسی خطاهای فنی: [عنوان، علت احتمالی، اقدام پیشنهادی].
 * پیام خام فنی فقط در جزئیات نگه داشته می‌شود.
 */
function system_error_explain(string $message): array
{
    $rules = [
        ['A connection attempt failed', 'اتصال به دستگاه یا سرویس شبکه برقرار نشد', 'دستگاه (مثلاً چاپگر) روشن و در شبکه باشد؛ آدرس IP و پورت را در تنظیمات بررسی کنید.'],
        ['timed out', 'مهلت پاسخ دستگاه یا سرویس تمام شد', 'دستگاه مقصد را بررسی کنید؛ اگر شبکه کند است، مهلت اتصال را بالا ببرید.'],
        ['آدرس چاپگر تنظیم نشده', 'آدرس چاپگر در تنظیمات ثبت نشده است', 'در پنل فیش غذا، آدرس IP و پورت چاپگر را وارد کنید.'],
        ['فونت فارسی', 'فونت فارسی برای رسم فیش پیدا نشد', 'فایل tahoma.ttf را در مسیر تنظیم‌شده قرار دهید یا مسیر فونت را اصلاح کنید.'],
        ['imagecreate', 'کتابخانهٔ GD برای ساخت تصویر فیش فعال نیست', 'در php.ini افزونهٔ gd را فعال کنید و سرویس وب را ریستارت کنید.'],
        ['imagepng', 'کتابخانهٔ GD برای ساخت تصویر فیش فعال نیست', 'در php.ini افزونهٔ gd را فعال کنید و سرویس وب را ریستارت کنید.'],
        ['imagettftext', 'کتابخانهٔ GD (FreeType) برای نوشتن متن فارسی فعال نیست', 'در php.ini افزونهٔ gd و پشتیبانی FreeType را فعال کنید.'],
        ['Too many connections', 'تعداد اتصال‌های دیتابیس پر شده است', 'بار همزمان سامانه را بررسی کنید؛ اگر تکرار شد، max_connections دیتابیس را افزایش دهید.'],
        ['Lock wait timeout', 'قفل دیتابیس بیش از حد طول کشید', 'عملیات همزمان روی همان جدول را بررسی کنید؛ معمولاً با تکرار کم می‌شود.'],
        ['Deadlock', 'دو عملیات همزمان روی دیتابیس یکدیگر را قفل کردند', 'اگر تکرار شد، اجرای همزمان گزارش‌ها و ایمپورت‌ها را جدا کنید.'],
        ['Access denied for user', 'کاربر دیتابیس اجازهٔ دسترسی ندارد', 'نام کاربری و رمز دیتابیس در config.php و دسترسی کاربر در MySQL را بررسی کنید.'],
        ['Unknown database', 'پایگاه‌داده پیدا نشد', 'نام دیتابیس در config.php را بررسی کنید.'],
        ['Base table or view not found', 'جدول موردنیاز در پایگاه‌داده نیست', 'مهاجرت یا فایل SQL مربوط به آپدیت را اجرا کنید.'],
        ['Unknown column', 'ستون موردنیاز در جدول نیست', 'مهاجرت به‌روزرسانی را اجرا کنید؛ احتمالاً نسخهٔ فایل‌ها با دیتابیس هم‌خوان نیست.'],
        ['No space left on device', 'فضای دیسک سرور پر شده است', 'فضای دیسک را آزاد کنید و لاگ‌ها و فایل‌های موقت قدیمی را پاک کنید.'],
        ['LdapErr', 'ارتباط با سرور دامین (LDAP) مشکل دارد', 'اگر فقط یک کاربر است، نام کاربری و رمز را بررسی کنید؛ اگر چند کاربر است، سرور دامین و تنظیمات LDAP را بررسی کنید.'],
        ['Undefined variable', 'متغیر تعریف‌نشده در کد برنامه (باگ)', 'این خطای برنامه است؛ متن فنی و مکان خطا را برای توسعه‌دهنده بفرستید.'],
        ['Undefined array key', 'داده ناقص یا کلید نامعتبر در برنامه (باگ)', 'این خطای برنامه است؛ متن فنی و مکان خطا را برای توسعه‌دهنده بفرستید.'],
        ['Undefined index', 'داده ناقص یا کلید نامعتبر در برنامه (باگ)', 'این خطای برنامه است؛ متن فنی و مکان خطا را برای توسعه‌دهنده بفرستید.'],
        ['Uncaught TypeError', 'نوع داده نادرست در برنامه (باگ)', 'متن فنی و مکان خطا را برای توسعه‌دهنده بفرستید.'],
        ['Failed to open stream', 'فایل یا مسیر موردنظر باز نشد', 'وجود فایل، مسیر و دسترسی خواندن/نوشتن را بررسی کنید.'],
        ['SQLSTATE[HY000] [2002]', 'اتصال به پایگاه‌داده برقرار نشد', 'سرویس MariaDB/MySQL را بررسی کنید و تنظیمات دیتابیس در config.php را کنترل کنید.'],
        ['Connection refused', 'اتصال به یک سرویس رد شد', 'سرویس مقصد (دیتابیس یا سرویس جانبی) را بررسی کنید که روشن باشد.'],
        ['SQLSTATE[HY000]: General error: 2006', 'اتصال دیتابیس قطع شد', 'سرور دیتابیس را بررسی کنید؛ اگر تکرار شد، تنظیم timeout را بالا ببرید.'],
        ['SQLSTATE[42S02]', 'جدول موردنیاز در پایگاه‌داده وجود ندارد', 'مهاجرت یا فایل schema مربوط را اجرا کنید.'],
        ['SQLSTATE[42S22]', 'ستون موردنیاز در جدول وجود ندارد', 'مهاجرت به‌روزرسانی را اجرا کنید؛ احتمالاً نسخهٔ فایل‌ها با دیتابیس هم‌خوان نیست.'],
        ['SQLSTATE[23000]', 'داده تکراری یا وابستگی ناسازگار', 'داده تکراری را بررسی کنید یا ابتدا رکوردهای وابسته را جابه‌جا کنید.'],
        ['Maximum execution time', 'اجرای درخواست بیش از حد طول کشید', 'بار سرور یا گزارش‌های سنگین را بررسی کنید؛ بازهٔ گزارش را کوچک‌تر کنید.'],
        ['Allowed memory size', 'حافظهٔ PHP تمام شد', 'memory_limit را افزایش دهید یا گزارش را در بازه‌های کوچک‌تر اجرا کنید.'],
        ['Call to undefined function', 'یک تابع پیدا نشد؛ احتمالاً فایل‌های سامانه ناهماهنگ‌اند', 'سامانه را از یک بستهٔ کامل نصب کنید و نسخهٔ فایل‌ها را یکسان کنید.'],
        ['Class ', 'یک کلاس پیدا نشد؛ احتمالاً فایل‌های سامانه ناهماهنگ‌اند', 'سامانه را از یک بستهٔ کامل نصب کنید.'],
        ['Permission denied', 'سامانه به یک فایل یا پوشه دسترسی ندارد', 'مجوز نوشتن پوشه‌های uploads و storage را برای کاربر وب بررسی کنید.'],
        ['CSRF', 'توکن امنیتی فرم منقضی شده است', 'صفحه را تازه‌سازی کنید و دوباره تلاش کنید.'],
    ];
    foreach ($rules as [$needle, $title, $action]) {
        if (str_contains($message, $needle)) {
            return ['title' => $title, 'cause' => 'بر اساس متن خطا؛ برای تأیید جزئیات فنی را ببینید.', 'action' => $action];
        }
    }
    return ['title' => 'خطای نامشخص سامانه', 'cause' => 'علت دقیق از روی متن خطا مشخص نیست.', 'action' => 'جزئیات فنی را بخوانید؛ اگر تکرار شد، آن را برای پشتیبانی فنی بفرستید.'];
}

/** حذف اطلاعات حساس پیش از ثبت: کد ملی (۱۰ رقم)، رمز و کلیدها. */
function system_error_mask(string $text): string
{
    $text = (string) preg_replace('/\b\d{10}\b/u', '[کد ملی مخفی]', $text);
    $text = (string) preg_replace('/(password|pass|pwd|رمز)\s*[=:]\s*\S+/iu', '$1=[مخفی]', $text);
    return mb_substr($text, 0, 2000);
}

/** ثبت یک خطای فنی با توضیح فارسی در system_logs (هیچ‌وقت خودش خطا ایجاد نمی‌کند). */
function system_error_record(string $level, string $source, string $rawMessage, string $location = ''): void
{
    try {
        $explain = system_error_explain($rawMessage);
        $title = $explain['title'];
        if ($title === 'خطای نامشخص سامانه') {
            // عنوان عمومی همهٔ خطاهای ناشناخته یکی می‌شد؛ متن کوتاه خطا جای آن می‌نشیند تا گروه‌ها جدا بمانند.
            $title = 'خطای ناشناخته: ' . mb_substr(system_error_mask($rawMessage), 0, 140);
        }
        system_log($level, $source, $title, [
            'cause' => $explain['cause'],
            'action' => $explain['action'],
            'technical' => system_error_mask($rawMessage),
            'location' => $location,
        ]);
    } catch (Throwable) {
        // ثبت لاگ نباید خود باعث خطای دیگری شود.
    }
}

// هندلرهای سراسری: استثنای نهایی‌نشده، خطای قاتل PHP و کندی صفحه.
if (PHP_SAPI !== 'cli') {
    $GLOBALS['__request_started_at'] = microtime(true);
    set_exception_handler(static function (Throwable $e): void {
        system_error_record('error', 'خطای نهایی‌نشده', $e->getMessage(), $e->getFile() . ':' . $e->getLine());
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;direction:rtl;padding:24px"><h2>خطای داخلی سامانه</h2><p>خطا ثبت شد و برای پیگیری در گزارش خطا قابل دیدن است.</p></body>';
    });
    register_shutdown_function(static function (): void {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            system_error_record('error', 'خطای قاتل PHP', (string) $error['message'], (string) $error['file'] . ':' . (string) $error['line']);
        }
        $elapsed = microtime(true) - (float) ($GLOBALS['__request_started_at'] ?? microtime(true));
        if ($elapsed >= 2.0) {
            $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
            $page = preg_match('/page=([a-z0-9_\-]+)/i', $uri, $m) ? $m[1] : 'نامشخص';
            system_log('warning', 'کندی صفحه', 'صفحهٔ «' . $page . '» ' . round($elapsed, 1) . ' ثانیه طول کشید', [
                'cause' => 'کندی معمولاً از کوئری سنگین، گزارش بزرگ یا بار بالای سرور است.',
                'action' => 'اگر این پیام تکرار شد، بازهٔ گزارش یا تعداد رکوردها را بررسی کنید.',
                'ms' => (int) round($elapsed * 1000),
            ]);
        }
    });
}

/** نمایش فارسی جزئیات یک لاگ: علت احتمالی، اقدام پیشنهادی و جزئیات فنی زیر «جزئیات». */
/**
 * ثبت خطای برنامه هم در گزارش سرور و هم در «سلامت سامانه» (system_logs).
 * یک پیام تکراری در هر ۶۰ ثانیه فقط یک‌بار در جدول ثبت می‌شود تا حلقه‌های خطا جدول را پر نکنند.
 */
function app_error_log(string $message): bool
{
    error_log($message);
    static $recent = [];
    $key = sha1($message);
    $now = time();
    if (isset($recent[$key]) && $now - $recent[$key] < 60) {
        return true;
    }
    $recent[$key] = $now;
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $location = isset($trace[0]['file']) ? basename((string) $trace[0]['file']) . ':' . (int) ($trace[0]['line'] ?? 0) : '';
    system_error_record('error', 'خطای برنامه', $message, $location);
    return true;
}

function system_log_meta_html(string $json): string
{
    if ($json === '') {
        return '';
    }
    $meta = json_decode($json, true);
    if (!is_array($meta)) {
        return '<br><small>' . e($json) . '</small>';
    }
    $html = '';
    if (!empty($meta['cause'])) {
        $html .= '<br><small><b>علت احتمالی:</b> ' . e((string) $meta['cause']) . '</small>';
    }
    if (!empty($meta['action'])) {
        $html .= '<br><small><b>اقدام پیشنهادی:</b> ' . e((string) $meta['action']) . '</small>';
    }
    $technical = [];
    foreach (['technical', 'location', 'ms'] as $key) {
        if (isset($meta[$key]) && $meta[$key] !== '') {
            $technical[] = $key . ': ' . (string) $meta[$key];
        }
    }
    if ($technical) {
        $html .= '<details><summary><small>جزئیات فنی</small></summary><small dir="ltr">' . e(implode("\n", $technical)) . '</small></details>';
    }
    return $html !== '' ? $html : '<br><small>' . e($json) . '</small>';
}

/**
 * حذف دسته‌ای ردیف‌ها برای جلوگیری از قفل طولانی جدول و رشد ناگهانی undo-log.
 * $table و $where فقط از کد خودِ برنامه می‌آیند (نه ورودی کاربر)؛ مقادیر با پارامتر می‌روند.
 * سقف ۲۰۰ دسته × ۵۰۰۰ ردیف در هر اجرا؛ بقیه در اجرای بعدی حذف می‌شوند.
 */
function db_delete_in_batches(string $table, string $where, array $params, int $batch = 5000, int $maxBatches = 200): int
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return 0;
    }
    $batch = max(100, min(20000, $batch));
    $total = 0;
    $sql = 'DELETE FROM `' . $table . '` WHERE ' . $where . ' LIMIT ' . $batch;
    for ($i = 0; $i < $maxBatches; $i++) {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $done = $stmt->rowCount();
        $total += $done;
        if ($done < $batch) {
            break;
        }
    }
    return $total;
}

/** حذف لاگ‌های فنی قدیمی‌تر از بازهٔ نگهداری (پیش‌فرض ۳۰ روز). */
function system_logs_prune(int $days = 30): int
{
    $days = max(1, min(3650, $days));
    try {
        system_logs_ensure();
        return db_delete_in_batches('system_logs', 'created_at < (NOW() - INTERVAL ? DAY)', [$days]);
    } catch (Throwable) {
        return 0;
    }
}

function system_logs_recent(int $limit = 200, string $level = ''): array
{
    try {
        system_logs_ensure();
        if ($level !== '') {
            $query = db()->prepare('SELECT * FROM system_logs WHERE level = ? ORDER BY id DESC LIMIT ' . max(1, min(500, $limit)));
            $query->execute([$level]);
        } else {
            $query = db()->query('SELECT * FROM system_logs ORDER BY id DESC LIMIT ' . max(1, min(500, $limit)));
        }
        return $query->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

function take_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/**
 * توکن CSRF.
 * توکن در session نگه داشته می‌شود، اما در یک کوکی نیز نگه‌داری می‌شود تا اگر session زودتر
 * بسته شد (برای آزادسازی قفل فایل)، فرم‌های صفحه همچنان توکن معتبر داشته باشند.
 */
function csrf_token(): string
{
    static $token = null;
    if ($token !== null) {
        return $token;
    }
    if (!empty($_SESSION['csrf'])) {
        return $token = (string) $_SESSION['csrf'];
    }
    $cookieName = 'itsm_csrf';
    $fromCookie = (string) ($_COOKIE[$cookieName] ?? '');
    if (preg_match('/^[a-f0-9]{64}$/', $fromCookie) === 1) {
        $_SESSION['csrf'] = $fromCookie;
        return $token = $fromCookie;
    }
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf'] = $token;
    if (!headers_sent() && session_status() !== PHP_SESSION_DISABLED) {
        setcookie($cookieName, $token, [
            'expires' => time() + 86400 * 7,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => (($_SERVER['HTTPS'] ?? '') === 'on'),
        ]);
        $_COOKIE[$cookieName] = $token;
    }
    return $token;
}

function require_csrf(): void
{
    $expected = csrf_token();
    $provided = (string) ($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
        http_response_code(419);
        exit('درخواست نامعتبر است. صفحه را تازه‌سازی کنید.');
    }
}

function current_user(): ?array
{
    static $user;
    if ($user !== null) {
        return $user ?: null;
    }
    if (empty($_SESSION['user_id'])) {
        $user = false;
        return null;
    }
    $query = db()->prepare('SELECT u.*, d.name AS department_name, hu.code AS handling_unit_code, hu.name AS handling_unit_name FROM users u LEFT JOIN departments d ON d.id = u.department_id LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.id = ? AND u.is_active = 1 LIMIT 1');
    $query->execute([(int) $_SESSION['user_id']]);
    $user = $query->fetch() ?: false;
    return $user ?: null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    if (function_exists('activity_log')) {
        activity_log([
            'action_code' => !empty($user['auth_source']) && $user['auth_source'] === 'ldap' ? 'login_ldap' : 'login_local',
            'user_id' => (int) $user['id'],
            'username' => (string) ($user['username'] ?? ''),
            'full_name' => (string) ($user['full_name'] ?? ''),
            'role' => (string) ($user['role'] ?? ''),
            'module' => 'auth',
        ]);
    }
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('index.php?page=login');
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if (!(function_exists('is_admin_role') ? is_admin_role($user['role']) : in_array($user['role'], ['primary_admin'], true))) {
        http_response_code(403);
        exit('دسترسی به این بخش مجاز نیست.');
    }
    return $user;
}

function require_primary_admin(): array
{
    $user = require_login();
    $isPrimary = function_exists('user_is_primary_admin')
        ? user_is_primary_admin($user)
        : ($user['role'] === 'primary_admin' || ($user['role'] === 'admin' && (int) ($user['is_primary_admin'] ?? 0) === 1));
    if (!$isPrimary) {
        http_response_code(403);
        exit('فقط ادمین اصلی اجازه مدیریت نقش‌ها و معاونت‌ها را دارد.');
    }
    return $user;
}

function require_staff(): array
{
    $user = require_login();
    if (!(function_exists('is_staff_role') ? is_staff_role($user['role']) : in_array($user['role'], ['agent', 'manager', 'supervisor'], true))) {
        http_response_code(403);
        exit('دسترسی به این بخش مجاز نیست.');
    }
    return $user;
}

function require_supervisor(): array
{
    $user = require_login();
    if (!in_array($user['role'], ['supervisor', 'primary_admin'], true)) {
        http_response_code(403);
        exit('دسترسی به پنل سوپروایزر مجاز نیست.');
    }
    return $user;
}

function ticket_scope(array $user, string $alias = 't'): array
{
    if (function_exists('is_global_ticket_role') ? is_global_ticket_role($user['role']) : in_array($user['role'], ['supervisor'], true)) {
        return ['', []];
    }
    if (($user['role'] ?? '') === 'support_manager') {
        // مدیر پشتیبانی فقط حوزه پشتیبانی را می‌بیند؛ بدون فیلتر معاونت و بدون دسترسی به IT.
        return ["WHERE {$alias}.service_group = ?", ['support']];
    }
    if (in_array($user['role'], ['agent', 'manager'], true)) {
        $group = user_service_group($user);
        $departmentId = (int) ($user['department_id'] ?? 0);
        if ($user['role'] === 'agent') {
            return ["WHERE {$alias}.service_group = ?", [$group]];
        }
        return $departmentId > 0 ? ["WHERE {$alias}.service_group = ? AND {$alias}.department_id = ?", [$group, $departmentId]] : ["WHERE {$alias}.service_group = ?", [$group]];
    }
    return ["WHERE {$alias}.requester_id = ?", [(int) $user['id']]];
}

function ticket_is_waiting_status(string $status): bool
{
    // مقدار قدیمی pending هم از نظر مکث SLA/OLA و پاسخ کاربر مثل waiting_user رفتار می‌کند.
    return $status === 'waiting_user' || $status === 'pending';
}

function user_service_group(array $user): string
{
    return in_array((string) ($user['handling_unit_code'] ?? ''), ['it', 'support'], true)
        ? (string) $user['handling_unit_code']
        : (is_it_agent($user) ? 'it' : 'support');
}

function asset_scope(array $user, string $alias = 'a'): array
{
    if ((function_exists('is_global_ticket_role') ? is_global_ticket_role($user['role']) : in_array($user['role'], ['supervisor'], true)) || (is_it_agent($user) && user_service_group($user) === 'it')) {
        return ['', []];
    }
    if (user_service_group($user) !== 'it') {
        return ['WHERE 1 = 0', []];
    }
    $departmentId = (int) ($user['department_id'] ?? 0);
    return $departmentId > 0 ? ["WHERE {$alias}.department_id = ?", [$departmentId]] : ['WHERE 1 = 0', []];
}

function asset_lifecycle_status_label(string $status): string
{
    return ['planned' => 'برنامه‌ریزی‌شده', 'in_stock' => 'در انبار', 'assigned' => 'تحویل‌شده', 'in_repair' => 'در تعمیر', 'retired' => 'بازنشسته', 'disposed' => 'اسقاط‌شده'][$status] ?? $status;
}

function asset_lifecycle_transition_allowed(string $from, string $to): bool
{
    $allowed = [
        'planned' => ['planned', 'in_stock', 'retired'],
        'in_stock' => ['in_stock', 'assigned', 'in_repair', 'retired'],
        'assigned' => ['assigned', 'in_stock', 'in_repair', 'retired'],
        'in_repair' => ['in_repair', 'in_stock', 'assigned', 'retired'],
        'retired' => ['retired', 'disposed'],
        'disposed' => ['disposed'],
    ];
    return in_array($to, $allowed[$from] ?? [], true);
}

function setting(string $key, ?string $default = null): ?string
{
    if (!isset($GLOBALS['__settings_cache'])) {
        // همهٔ تنظیمات را یک‌بار می‌خوانیم تا هر setting() یک کوئری جدا نزند
        // (قبلاً هر صفحه ۲۵+ کوئری تک‌کلیدی داشت).
        $GLOBALS['__settings_cache'] = [];
        try {
            foreach (db()->query('SELECT `key`, value FROM settings') as $row) {
                $GLOBALS['__settings_cache'][(string) $row['key']] = $row['value'] === null ? null : (string) $row['value'];
            }
        } catch (Throwable) {
            $GLOBALS['__settings_cache'] = [];
        }
    }
    $cache = &$GLOBALS['__settings_cache'];
    if (!array_key_exists($key, $cache)) {
        $query = db()->prepare('SELECT value FROM settings WHERE `key` = ? LIMIT 1');
        $query->execute([$key]);
        $value = $query->fetchColumn();
        $cache[$key] = $value === false ? null : (string) $value;
    }
    return $cache[$key] ?? $default;
}

/**
 * بعد از ذخیرهٔ تنظیمات، کش درون‌درخواستی را بی‌اعتبار می‌کند.
 */
function setting_forget_cache(): void
{
    $GLOBALS['__settings_cache'] = null;
}

function save_setting(string $key, string $value): void
{
    $query = db()->prepare(
        'INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)'
    );
    $query->execute([$key, $value]);
    if (isset($GLOBALS['__settings_cache']) && is_array($GLOBALS['__settings_cache'])) {
        $GLOBALS['__settings_cache'][$key] = (string) $value;
    }
}

/**
 * فهرست صفحه‌های قابل انتخاب برای افکت نمایشی.
 * کلیدهای صفحه باید با ?page= در index.php و نام فایل برای صفحات مستقل یکی باشند.
 * صفحات چاپ مستقل عمداً کنار گذاشته شده‌اند تا پس‌زمینه روی خروجی چاپ اثر نگذارد.
 */
function triangle_effect_page_catalog(): array
{
    return [
        'عمومی' => [
            'login' => 'ورود به سامانه',
            'dashboard' => 'داشبورد',
            'help' => 'راهنما',
            'notifications' => 'اعلان‌ها',
            'search' => 'جست‌وجو',
            'profile' => 'پروفایل کاربر',
            'change-password' => 'تغییر رمز عبور',
        ],
        'پشتیبانی و دارایی' => [
            'new-ticket' => 'ثبت درخواست جدید',
            'ticket' => 'جزئیات درخواست',
            'queue' => 'صف کاری',
            'services' => 'خدمات',
            'knowledge' => 'دانش‌نامه',
            'assets' => 'شناسنامه‌های فنی',
            'asset' => 'جزئیات شناسنامه فنی',
            'asset-history' => 'تاریخچه دارایی',
            'inventory' => 'موجودی (Inventory)',
            'inventory-diagnostics' => 'عیب‌یابی موجودی',
            'domain-scan' => 'اسکن دامنه',
            'supervisor' => 'پنل سوپروایزر',
            'governance' => 'تغییر و مشکل',
            'ola' => 'OLA',
            'holidays' => 'تعطیلات',
        ],
        'کنترل و غذا' => [
            'cd-dvd' => 'کنترل CD/DVD',
            'cd-dvd-history' => 'تاریخچه CD/DVD',
            'traffic-control' => 'کنترل تردد',
            'food-order' => 'سفارش غذا',
        ],
        'گزارش‌ها' => [
            'reports' => 'گزارش درخواست‌ها',
            'analytics' => 'چارت‌ها',
            'activity-log' => 'لاگ سامانه',
            'logs' => 'گزارش خطا',
            'audit' => 'ممیزی',
            'system-health' => 'سلامت سامانه',
        ],
        'مدیریت' => [
            'organization' => 'سازمان',
            'settings' => 'تنظیمات',
            'backup' => 'به‌روزرسانی و پشتیبان‌گیری',
            'chatbot-admin' => 'بات پاسخ‌گو',
            'local-accounts' => 'کاربران محلی',
        ],
    ];
}

function triangle_effect_valid_keys(): array
{
    $keys = [];
    foreach (triangle_effect_page_catalog() as $group) {
        foreach ($group as $key => $_label) {
            $keys[] = (string) $key;
        }
    }
    return $keys;
}

/** [حداقل، حداکثر، پیش‌فرض، تعداد رقم اعشار] */
function triangle_effect_config_spec(): array
{
    return [
        'cellSize' => [28, 70, 46, 0],
        'mouseRadiusMult' => [1, 12, 5.0, 1],
        'repulsionMult' => [0, 4, 1.35, 2],
        'springK' => [0.01, 0.3, 0.085, 3],
        'damping' => [0.5, 0.98, 0.80, 2],
        'waveSpeed' => [100, 1500, 620, 0],
        'waveDuration' => [0.3, 4, 1.6, 1],
        'waveWidth' => [20, 200, 70, 0],
        'waveAmpMult' => [0, 1.5, 0.32, 2],
        'idleAmp' => [0, 6, 1.6, 1],
        'opacity' => [0.1, 1, 1, 2],
    ];
}

function triangle_effect_sanitize_config(array $input): array
{
    $out = [];
    foreach (triangle_effect_config_spec() as $key => [$min, $max, $default, $decimals]) {
        $raw = $input[$key] ?? $default;
        $value = is_numeric($raw) ? (float) $raw : (float) $default;
        if (!is_finite($value)) {
            $value = (float) $default;
        }
        $value = max((float) $min, min((float) $max, $value));
        $out[$key] = $decimals > 0 ? round($value, $decimals) : (int) round($value);
    }
    foreach (['meshColor' => '#475569', 'dotColor' => '#334155'] as $key => $default) {
        $raw = $input[$key] ?? $default;
        $raw = is_string($raw) ? $raw : $default;
        $out[$key] = preg_match('/^#[0-9a-fA-F]{6}$/', $raw) === 1 ? strtolower($raw) : $default;
    }
    foreach (['showDots', 'maskVignette'] as $key) {
        $raw = $input[$key] ?? true;
        if (is_bool($raw)) {
            $out[$key] = $raw;
        } elseif (is_int($raw) || is_float($raw)) {
            $out[$key] = (float) $raw !== 0.0;
        } elseif (is_string($raw)) {
            $parsed = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $out[$key] = is_bool($parsed) ? $parsed : true;
        } else {
            $out[$key] = true;
        }
    }
    return $out;
}

function display_effect_type(): string
{
    $effect = setting('display_effect_type', 'triangle');
    return in_array($effect, ['triangle', 'particles'], true) ? $effect : 'triangle';
}

/** [حداقل، حداکثر، پیش‌فرض، تعداد رقم اعشار] */
function particle_effect_config_spec(): array
{
    return [
        'density' => [1800, 9000, 4500, 0],
        'maxParticles' => [40, 450, 320, 0],
        'speed' => [0, 1, 0.25, 2],
        'linkDistance' => [50, 240, 130, 0],
        'mouseRadius' => [60, 320, 180, 0],
        'mouseStrength' => [0, 2, 1, 1],
        'opacity' => [0.1, 1, 1, 2],
        'dotScale' => [0.5, 2.5, 1, 1],
    ];
}

function particle_effect_sanitize_config(array $input): array
{
    $out = [];
    foreach (particle_effect_config_spec() as $key => [$min, $max, $default, $decimals]) {
        $raw = $input[$key] ?? $default;
        $value = is_numeric($raw) ? (float) $raw : (float) $default;
        if (!is_finite($value)) {
            $value = (float) $default;
        }
        $value = max((float) $min, min((float) $max, $value));
        $out[$key] = $decimals > 0 ? round($value, $decimals) : (int) round($value);
    }
    $rawColor = $input['color'] ?? '#172b36';
    $rawColor = is_string($rawColor) ? $rawColor : '#172b36';
    $out['color'] = preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor) === 1 ? strtolower($rawColor) : '#172b36';
    foreach (['showDots', 'showLinks', 'connectCursor', 'autoMove'] as $key) {
        $raw = $input[$key] ?? true;
        if (is_bool($raw)) {
            $out[$key] = $raw;
        } elseif (is_int($raw) || is_float($raw)) {
            $out[$key] = (float) $raw !== 0.0;
        } elseif (is_string($raw)) {
            $parsed = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $out[$key] = is_bool($parsed) ? $parsed : true;
        } else {
            $out[$key] = true;
        }
    }
    return $out;
}

function particle_effect_default_config(): array
{
    return particle_effect_sanitize_config([]);
}

function particle_effect_config(): array
{
    $raw = (string) setting('particle_effect_config', '');
    $decoded = $raw !== '' ? json_decode($raw, true) : null;
    return particle_effect_sanitize_config(is_array($decoded) ? $decoded : []);
}

function particle_effect_enabled_for_current_page(): bool
{
    try {
        return display_effect_type() === 'particles'
            && in_array(triangle_effect_current_page_key(), triangle_effect_enabled_pages(), true);
    } catch (Throwable) {
        return false;
    }
}

function triangle_effect_default_config(): array
{
    return triangle_effect_sanitize_config([]);
}

function triangle_effect_config(): array
{
    $raw = (string) setting('triangle_effect_config', '');
    $decoded = $raw !== '' ? json_decode($raw, true) : null;
    return triangle_effect_sanitize_config(is_array($decoded) ? $decoded : []);
}

function triangle_effect_enabled_pages(): array
{
    $raw = (string) setting('triangle_effect_pages', '');
    $decoded = $raw !== '' ? json_decode($raw, true) : null;
    if (!is_array($decoded)) {
        return [];
    }
    $allowed = array_fill_keys(triangle_effect_valid_keys(), true);
    $selected = [];
    foreach ($decoded as $pageKey) {
        if (!is_string($pageKey)) {
            continue;
        }
        $pageKey = trim($pageKey);
        if ($pageKey !== '' && isset($allowed[$pageKey])) {
            $selected[$pageKey] = true;
        }
    }
    return array_values(array_filter(
        triangle_effect_valid_keys(),
        static fn(string $pageKey): bool => isset($selected[$pageKey])
    ));
}

function triangle_effect_current_page_key(): string
{
    $script = strtolower(pathinfo((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'), PATHINFO_FILENAME));
    if ($script !== 'index') {
        return $script;
    }
    if (!current_user()) {
        return 'login';
    }
    $page = $_GET['page'] ?? '';
    $page = is_string($page) || is_int($page) ? strtolower(trim((string) $page)) : '';
    return $page !== '' ? $page : 'dashboard';
}

function triangle_effect_enabled_for_current_page(): bool
{
    try {
        return display_effect_type() === 'triangle'
            && in_array(triangle_effect_current_page_key(), triangle_effect_enabled_pages(), true);
    } catch (Throwable) {
        return false;
    }
}

/** اسکریپت موتور، و در صفحهٔ تنظیمات پیش‌نمایش زنده را فقط یک‌بار بارگذاری می‌کند. */
function triangle_effect_script_tag(): string
{
    try {
        $pageKey = triangle_effect_current_page_key();
        $enabled = display_effect_type() === 'triangle' && in_array($pageKey, triangle_effect_enabled_pages(), true);
        $user = current_user();
        $canPreview = $pageKey === 'settings' && $user !== null && user_can($user, 'settings.general');
        if (!$enabled && !$canPreview) {
            return '';
        }
        $tag = '<script src="assets/triangle-mesh.js?v=1.56" defer';
        if ($enabled) {
            $json = json_encode(
                triangle_effect_config(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            );
            if (!is_string($json)) {
                return '';
            }
            $tag .= ' data-config="' . htmlspecialchars($json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
        }
        $tag .= '></script>';
        if ($canPreview) {
            $tag .= '<script src="assets/triangle-settings.js?v=1.56.1" defer></script>';
        }
        return $tag;
    } catch (Throwable) {
        return '';
    }
}

/** موتور ذرات فقط برای نوع افکت انتخاب‌شده و صفحه‌های ذخیره‌شده بارگذاری می‌شود. */
function particle_effect_script_tag(): string
{
    try {
        $pageKey = triangle_effect_current_page_key();
        $enabled = particle_effect_enabled_for_current_page();
        $user = current_user();
        $canPreview = $pageKey === 'settings' && $user !== null && user_can($user, 'settings.general');
        if (!$enabled && !$canPreview) {
            return '';
        }
        $tag = '<script src="assets/particle-network.js?v=1.56.1" defer';
        if ($enabled) {
            $json = json_encode(
                particle_effect_config(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            );
            if (!is_string($json)) {
                return '';
            }
            $tag .= ' data-config="' . htmlspecialchars($json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
        }
        $tag .= '></script>';
        if ($canPreview) {
            $tag .= '<script src="assets/particle-settings.js?v=1.56.1" defer></script>';
        }
        return $tag;
    } catch (Throwable) {
        return '';
    }
}

function key_role_setting_map(): array
{
    return [
        'supervisor' => 'role_supervisor_ids',
        'inspection' => 'role_inspection_ids',
        'it_expert' => 'role_it_expert_ids',
    ];
}

function key_role_user_ids(string $role): array
{
    $map = key_role_setting_map();
    if (!isset($map[$role])) {
        return [];
    }
    $raw = (string) (setting($map[$role], '') ?? '');
    $ids = [];
    foreach (explode(',', $raw) as $part) {
        $id = (int) trim($part);
        if ($id > 0 && !in_array($id, $ids, true)) {
            $ids[] = $id;
        }
    }
    return array_slice($ids, 0, 2);
}

function user_is_key_role(array $user, string $role): bool
{
    return in_array((int) ($user['id'] ?? 0), key_role_user_ids($role), true);
}

function ldap_cfg(string $key, mixed $default = null): mixed
{
    $map = [
        'enabled' => 'ldap_enabled',
        'host' => 'ldap_host',
        'port' => 'ldap_port',
        'ssl' => 'ldap_ssl',
        'base_dn' => 'ldap_base_dn',
        'bind_dn' => 'ldap_bind_dn',
        'bind_password' => 'ldap_bind_password',
        'domain_suffix' => 'ldap_domain_suffix',
    ];
    $value = isset($map[$key]) ? setting($map[$key]) : null;
    if ($value === null) {
        return cfg('ldap.' . $key, $default);
    }
    if (in_array($key, ['enabled', 'ssl'], true)) {
        return $value === '1';
    }
    return $value;
}

function ldap_role_for_groups(array $groups): string
{
    $mapping = json_decode((string) setting('ldap_group_map', json_encode(cfg('ldap.group_map', []), JSON_UNESCAPED_UNICODE)), true);
    if (!is_array($mapping)) {
        return 'user';
    }
    $groups = array_map(static fn (mixed $group): string => strtolower(trim((string) $group)), $groups);
    foreach (['supervisor', 'agent', 'manager'] as $role) {
        foreach ((array) ($mapping[$role] ?? []) as $mappedGroup) {
            if (in_array(strtolower(trim((string) $mappedGroup)), $groups, true)) {
                return $role;
            }
        }
    }
    return 'user';
}

function ldap_person_filter(string $filter): string
{
    $filter = trim($filter);
    if ($filter === '') {
        return '(&(objectCategory=person)(objectClass=user))';
    }
    if (stripos($filter, 'objectcategory=person') !== false) {
        return $filter;
    }
    return '(&' . $filter . '(objectCategory=person))';
}

function ldap_auth_log(string $stage, string $username, $connection = null): void
{
    $details = [];
    if ($connection !== null) {
        $details[] = ldap_error($connection);
        if (defined('LDAP_OPT_DIAGNOSTIC_MESSAGE')) {
            $diagnostic = '';
            @ldap_get_option($connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $diagnostic);
            if (trim((string) $diagnostic) !== '') {
                $details[] = preg_replace('/\s+/', ' ', trim(substr((string) $diagnostic, 0, 500)));
            }
        }
    }
    $suffix = $details ? ' | ' . implode(' | ', array_filter($details)) : '';
    error_log('ITSM LDAP authentication ' . $stage . ' for ' . substr($username, 0, 120) . $suffix);
}

/**
 * تشخیص گام‌به‌گام اتصال دامنه؛ برای نمایش پیام دقیق خطا در پنل.
 * @return array{ok:bool,summary:string,steps:array<int,array{label:string,ok:bool,detail:string}>}
 */
function ldap_diagnose(): array
{
    $steps = [];
    $add = static function (string $label, bool $ok, string $detail = '') use (&$steps): void {
        $steps[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail];
    };

    if (!function_exists('ldap_connect')) {
        $add('افزونه LDAP پیاچ‌پی', false, 'نصب نیست. در php.ini خط extension=ldap را فعال و وب‌سرور را ری‌استارت کنید.');
        return ['ok' => false, 'summary' => 'افزونه LDAP پیاچ‌پی نصب نیست.', 'steps' => $steps];
    }
    $add('افزونه LDAP پیاچ‌پی', true);

    $enabled = (bool) ldap_cfg('enabled', false);
    $host = trim((string) ldap_cfg('host', ''));
    $port = (int) ldap_cfg('port', 389);
    $ssl = (bool) ldap_cfg('ssl', false);
    $baseDn = trim((string) ldap_cfg('base_dn', ''));
    $bindDn = trim((string) ldap_cfg('bind_dn', ''));
    $bindPassword = (string) ldap_cfg('bind_password', '');

    $add('فعال بودن LDAP', $enabled, $enabled ? '' : 'در تنظیمات، «ورود کاربران شبکه فعال باشد» را تیک بزنید.');
    $add('آدرس کنترلر', $host !== '', $host !== '' ? $host : 'خالی است.');
    $add('Base DN', $baseDn !== '', $baseDn !== '' ? $baseDn : 'خالی است.');
    if (!$enabled || $host === '' || $baseDn === '') {
        return ['ok' => false, 'summary' => 'تنظیمات دامنه کامل نیست.', 'steps' => $steps];
    }

    $connection = @ldap_connect(($ssl ? 'ldaps://' : 'ldap://') . $host, $port);
    if (!$connection) {
        $add('اتصال به کنترلر', false, 'برقرار نشد؛ آدرس/پورت/فایروال را بررسی کنید.');
        return ['ok' => false, 'summary' => 'اتصال به کنترلر دامین برقرار نشد.', 'steps' => $steps];
    }
    @ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    @ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
    if (defined('LDAP_OPT_NETWORK_TIMEOUT')) { @ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 8); }
    $add('اتصال به کنترلر', true, $host . ':' . $port);

    if ($bindDn !== '') {
        $bound = @ldap_bind($connection, $bindDn, $bindPassword);
        $add('ورود حساب سرویس', (bool) $bound, $bound ? '' : (function_exists('ldap_error') ? ldap_error($connection) : 'ناموفق'));
        if (!$bound) {
            return ['ok' => false, 'summary' => 'رمز یا DN حساب سرویس اشتباه است.', 'steps' => $steps];
        }
    } else {
        $bound = @ldap_bind($connection);
        $add('ورود ناشناس', (bool) $bound, $bound ? '' : 'کنترلر bind ناشناس را رد می‌کند؛ حساب سرویس وارد کنید.');
        if (!$bound) {
            return ['ok' => false, 'summary' => 'کنترلر bind ناشناس را رد می‌کند؛ حساب سرویس لازم است.', 'steps' => $steps];
        }
    }

    $configuredFilter = (string) cfg('ldap.user_filter', '(&(objectCategory=person)(objectClass=user)(sAMAccountName=%s))');
    $filter = ldap_person_filter(str_contains($configuredFilter, '%s')
        ? str_replace('%s', '*', $configuredFilter)
        : '(&(objectClass=user)(sAMAccountName=*))');
    $search = @ldap_search($connection, $baseDn, $filter, ['sAMAccountName'], 0, 5);
    if (!$search) {
        $add('جست‌وجوی کاربران', false, function_exists('ldap_error') ? ldap_error($connection) : 'ناموفق');
        if (function_exists('ldap_unbind')) { @ldap_unbind($connection); }
        return ['ok' => false, 'summary' => 'جست‌وجوی کاربران دامین ناموفق بود.', 'steps' => $steps];
    }
    $entries = @ldap_get_entries($connection, $search);
    $count = (int) ($entries['count'] ?? 0);
    $add('جست‌وجوی کاربران', true, $count . ' کاربر نمونه پیدا شد');
    if (function_exists('ldap_unbind')) { @ldap_unbind($connection); }

    return [
        'ok' => $count > 0,
        'summary' => $count > 0 ? ('اتصال سالم است و ' . $count . ' کاربر نمونه پیدا شد.') : 'اتصال سالم است اما هیچ کاربری پیدا نشد؛ Base DN یا فیلتر را بررسی کنید.',
        'steps' => $steps,
    ];
}

function ldap_sync_all_users(): array
{
    if (!ldap_cfg('enabled', false) || !function_exists('ldap_connect')) {
        throw new RuntimeException('LDAP فعال نیست یا افزونه LDAP PHP نصب نشده است.');
    }
    $host = trim((string) ldap_cfg('host', ''));
    $baseDn = trim((string) ldap_cfg('base_dn', ''));
    if ($host === '') {
        throw new RuntimeException('آدرس کنترلر دامین را تنظیم کنید.');
    }

    $ssl = (bool) ldap_cfg('ssl', false);
    $scheme = $ssl ? 'ldaps://' : 'ldap://';
    $connection = @ldap_connect($scheme . $host, (int) ldap_cfg('port', $ssl ? 636 : 389));
    if (!$connection) {
        throw new RuntimeException('اتصال به کنترلر دامین برقرار نشد.');
    }
    ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
    if (defined('LDAP_OPT_NETWORK_TIMEOUT')) { @ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 8); }
    if (defined('LDAP_OPT_TIMELIMIT')) { @ldap_set_option($connection, LDAP_OPT_TIMELIMIT, 8); }
    $bindDn = (string) ldap_cfg('bind_dn', '');
    if ($bindDn !== '' && !@ldap_bind($connection, $bindDn, (string) ldap_cfg('bind_password', ''))) {
        throw new RuntimeException('ورود حساب سرویس LDAP ناموفق بود. DN و رمز حساب سرویس را بررسی کنید.');
    }
    if ($bindDn === '' && !@ldap_bind($connection)) {
        throw new RuntimeException('کنترلر دامین اجازهٔ اتصال ناشناس نمی‌دهد؛ یک حساب سرویس با رمز در تنظیمات ثبت کنید.');
    }

    // اگر Base DN خالی باشد، از defaultNamingContext دامین استفاده کن.
    if ($baseDn === '') {
        $rootDse = @ldap_read($connection, '', '(objectClass=*)', ['defaultNamingContext']);
        if ($rootDse) {
            $rootEntries = ldap_get_entries($connection, $rootDse);
            $discovered = trim((string) ($rootEntries[0]['defaultnamingcontext'][0] ?? ''));
            if ($discovered !== '') {
                $baseDn = $discovered;
            }
        }
        if ($baseDn === '') {
            throw new RuntimeException('Base DN خالی است و از دامین هم کشف نشد؛ آن را دستی وارد کنید (مثلاً DC=example,DC=local).');
        }
    }

    $configuredFilter = (string) cfg('ldap.user_filter', '(&(objectCategory=person)(objectClass=user)(sAMAccountName=%s))');
    $filter = ldap_person_filter(str_contains($configuredFilter, '%s')
        ? str_replace('%s', '*', $configuredFilter)
        : '(&(objectClass=user)(sAMAccountName=*))');
    $attributes = ['distinguishedName', 'displayName', 'mail', 'department', 'sAMAccountName', 'employeeID', 'telephoneNumber', 'userAccountControl', 'memberOf'];
    $seen = [];
    $result = ['created' => 0, 'updated' => 0, 'disabled' => 0, 'skipped' => 0, 'code_conflict' => 0, 'skipped_disabled' => 0, 'errors' => []];
    $cookie = '';
    $processed = 0;

    do {
        // صفحه‌بندی: در PHP < 8.4 با ldap_control_paged_result و در PHP >= 8.4 با LDAP controls.
        if (function_exists('ldap_control_paged_result')) {
            ldap_control_paged_result($connection, 500, false, $cookie);
            $search = @ldap_search($connection, $baseDn, $filter, $attributes);
        } else {
            $pageControl = [
                'oid' => '1.2.840.113556.1.4.319',
                'iscritical' => false,
                'value' => ['size' => 500, 'cookie' => $cookie],
            ];
            $search = @ldap_search($connection, $baseDn, $filter, $attributes, 0, 0, 8, LDAP_DEREF_NEVER, [$pageControl]);
        }
        if (!$search) {
            throw new RuntimeException('جست‌وجوی کاربران دامین ناموفق بود.');
        }
        $entries = ldap_get_entries($connection, $search);
        for ($index = 0; $index < (int) ($entries['count'] ?? 0); $index++) {
            $entry = $entries[$index];
            $username = trim((string) ($entry['samaccountname'][0] ?? ''));
            if ($username === '') {
                $result['skipped']++;
                continue;
            }
            $usernameKey = strtolower($username);
            $seen[$usernameKey] = true;
            $groups = [];
            for ($groupIndex = 0; $groupIndex < (int) ($entry['memberof']['count'] ?? 0); $groupIndex++) {
                $groups[] = (string) ($entry['memberof'][$groupIndex] ?? '');
            }
            $identity = [
                'username' => $username,
                'full_name' => (string) ($entry['displayname'][0] ?? $username),
                'email' => (string) ($entry['mail'][0] ?? ''),
                'department' => (string) ($entry['department'][0] ?? ''),
                'employee_number' => (string) ($entry['employeeid'][0] ?? ''),
                'phone' => (string) ($entry['telephonenumber'][0] ?? ''),
                'groups' => $groups,
            ];
            $existingQuery = db()->prepare('SELECT id, is_active FROM users WHERE username = ? AND auth_source = "ldap" LIMIT 1');
            $existingQuery->execute([$username]);
            $existingUser = $existingQuery->fetch() ?: null;
            $wasExisting = $existingUser !== null;
            // حساب غیرفعال در دامین: اگر در سامانه نیست، اصلاً ایمپورت نمی‌شود.
            $adDisabled = (((int) ($entry['useraccountcontrol'][0] ?? 0)) & 2) === 2;
            if ($adDisabled && !$wasExisting) {
                $result['skipped_disabled']++;
                $processed++;
                continue;
            }
            try {
                $domainUser = upsert_domain_user($identity);
            } catch (RuntimeException $userError) {
                // یک کاربر مشکل‌دار (مثلاً نام کاربری با حساب محلی برخورد دارد) نباید کل همگام‌سازی را متوقف کند.
                $result['skipped']++;
                $processed++;
                if (count($result['errors']) < 50) {
                    $result['errors'][] = $username . ': ' . $userError->getMessage();
                }
                continue;
            }
            if (!empty($domainUser['code_conflict'])) {
                $result['code_conflict']++;
            }
            $disabled = (((int) ($entry['useraccountcontrol'][0] ?? 0)) & 2) === 2;
            $shouldBeActive = !$disabled && (!$wasExisting || (int) ($existingUser['is_active'] ?? 0) === 1);
            db()->prepare('UPDATE users SET is_active = ? WHERE id = ?')->execute([$shouldBeActive ? 1 : 0, (int) $domainUser['id']]);
            $result[$wasExisting ? 'updated' : 'created']++;
            if ($disabled) {
                $result['disabled']++;
            }
            $processed++;
        }
        if (function_exists('ldap_control_paged_result_response')) {
            $estimated = 0;
            ldap_control_paged_result_response($connection, $search, $cookie, $estimated);
        } elseif (function_exists('ldap_parse_result')) {
            // PHP >= 8.4: خواندن cookie صفحه‌بندی از کنترل‌های نتیجه
            $cookie = '';
            $matchedDn = null;
            $errorCode = 0;
            $errorMessage = '';
            $referrals = [];
            $controls = [];
            if (@ldap_parse_result($connection, $search, $errorCode, $matchedDn, $errorMessage, $referrals, $controls) && is_array($controls)) {
                foreach ($controls as $control) {
                    if (is_array($control) && ($control['oid'] ?? '') === '1.2.840.113556.1.4.319' && is_array($control['value'] ?? null)) {
                        $cookie = (string) ($control['value']['cookie'] ?? '');
                        break;
                    }
                }
            }
        } else {
            $cookie = '';
        }
    } while ($cookie !== '');

    if ($processed === 0) {
        throw new RuntimeException('جست‌وجوی دامین هیچ کاربری برنگرداند؛ برای جلوگیری از غیرفعال‌سازی اشتباه، عملیات متوقف شد.');
    }

    // فقط وقتی صفحه‌بندی کامل محتمل است کاربران غایب را غیرفعال کن
    $pagingOk = function_exists('ldap_control_paged_result') || function_exists('ldap_parse_result') || $processed >= 500;
    if ($pagingOk && $processed > 0) {
        $existingUsers = db()->query('SELECT id, username FROM users WHERE auth_source = "ldap"')->fetchAll();
        foreach ($existingUsers as $existingUser) {
            if (!isset($seen[strtolower((string) $existingUser['username'])])) {
                db()->prepare('UPDATE users SET is_active = 0 WHERE id = ?')->execute([(int) $existingUser['id']]);
                $result['disabled']++;
            }
        }
    }
    return $result;
}

function &ticket_number_cache_store(): array
{
    static $cache = [];
    return $cache;
}

function ticket_number_cache_set(int $id, string $value): void
{
    $cache = &ticket_number_cache_store();
    $cache[$id] = $value;
}

function ticket_number(int $id): string
{
    $cache = &ticket_number_cache_store();
    if ($id <= 0) {
        return '';
    }
    if (!array_key_exists($id, $cache)) {
        $cache[$id] = '';
        try {
            $query = db()->prepare('SELECT ticket_no FROM tickets WHERE id = ? LIMIT 1');
            $query->execute([$id]);
            $cache[$id] = trim((string) ($query->fetchColumn() ?: ''));
        } catch (Throwable $exception) {
            $cache[$id] = '';
        }
    }
    if ($cache[$id] !== '') {
        return $cache[$id];
    }
    return 'SBU1-86-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
}

function ticket_number_prefix(string $serviceGroup): string
{
    if ($serviceGroup === 'it') {
        return 'IT';
    }
    if ($serviceGroup === 'support') {
        return 'SUP';
    }
    return 'TK';
}

/**
 * آماده‌سازی جدول شماره‌گذاری درخواست — بدون DDL غیرضروری.
 *
 * ⚠️ درس گرفته‌شده (خطای «There is no active transaction»):
 * هر دستور DDL در MySQL یک COMMIT ضمنی می‌زند. قبلاً این تابع در هر بار «شماره‌گذاری»
 * یک CREATE TABLE IF NOT EXISTS اجرا می‌کرد و چون ticket_number_next() داخل تراکنش
 * ثبت درخواست صدا زده می‌شود، تراکنش همان‌جا بسته می‌شد؛ در پایان، db()->commit()
 * با خطای «There is no active transaction» سر می‌خورد — هرچند درخواست ثبت شده بود.
 *
 * حالا: اول با SELECT وجود جدول/ستون بررسی می‌شود (بدون COMMIT ضمنی) و DDL فقط اگر
 * واقعاً لازم باشد و «تراکنشی باز نباشد» اجرا می‌شود.
 */
function ticket_number_ensure_schema(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    ticket_number_ensure_schema_run();
    $ready = true;
}

function ticket_number_ensure_schema_run(): void
{
    $inTransaction = function_exists('db_in_transaction') ? db_in_transaction() : false;

    // ۱) جدول دنباله — با SELECT بررسی می‌شود (نه DDL)
    $tableExists = false;
    try {
        db()->query('SELECT 1 FROM ticket_number_seq LIMIT 1');
        $tableExists = true;
    } catch (Throwable) {
        $tableExists = false;
    }

    // ۲) ستون ticket_no در tickets
    $columnExists = true;
    try {
        $columns = db()->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets'")->fetchAll(PDO::FETCH_COLUMN);
        $columnExists = in_array('ticket_no', array_map('strtolower', $columns), true);
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'ticket_number_schema', $exception->getMessage());
        }
        $columnExists = true; // اگر نتوانستیم بررسی کنیم، DDL هم نمی‌زنیم
    }

    if ($tableExists && $columnExists) {
        return;
    }
    if ($inTransaction) {
        // DDL داخل تراکنش = بسته‌شدن بی‌صدا. به‌جای آن، بعد از پایان تراکنش تلاش می‌کنیم.
        if (function_exists('system_log')) {
            system_log('warning', 'ticket_number_schema', 'نیاز به DDL در میانهٔ تراکنش بود؛ به بعد موکول شد.', ['in_transaction' => true]);
        }
        register_shutdown_function(static function (): void {
            try {
                ticket_number_ensure_schema_run();
            } catch (Throwable) {
            }
        });
        return;
    }

    try {
        if (!$tableExists) {
            db()->exec('CREATE TABLE IF NOT EXISTS ticket_number_seq (prefix VARCHAR(20) NOT NULL PRIMARY KEY, seq INT UNSIGNED NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }
        if (!$columnExists) {
            db()->exec('ALTER TABLE tickets ADD COLUMN ticket_no VARCHAR(40) NULL AFTER id');
        }
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'ticket_number_schema', $exception->getMessage());
        }
    }
}

function ticket_number_next(string $prefix): string
{
    ticket_number_ensure_schema();
    $prefix = strtoupper(substr(trim($prefix) !== '' ? trim($prefix) : 'TK', 0, 12));
    $statement = db()->prepare('INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE seq = LAST_INSERT_ID(seq + 1)');
    $statement->execute([$prefix]);
    $sequence = (int) db()->query('SELECT LAST_INSERT_ID()')->fetchColumn();
    if ($sequence <= 0) {
        $sequence = 1;
    }
    return $prefix . '-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
}

function ticket_number_peek(string $prefix): string
{
    ticket_number_ensure_schema();
    $prefix = strtoupper(substr(trim($prefix) !== '' ? trim($prefix) : 'TK', 0, 12));
    try {
        $query = db()->prepare('SELECT seq FROM ticket_number_seq WHERE prefix = ? LIMIT 1');
        $query->execute([$prefix]);
        $sequence = (int) ($query->fetchColumn() ?: 0) + 1;
    } catch (Throwable $exception) {
        $sequence = 1;
    }
    return $prefix . '-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
}

/**
 * پرکردن شمارهٔ درخواست‌های قدیمی که ticket_no ندارند.
 *
 * ⚠️ این تابع در ابتدای هر درخواست صدا زده می‌شد و هر بار یک SELECT روی کل جدول
 * tickets + یک DDL (CREATE TABLE IF NOT EXISTS) اجرا می‌کرد؛ روی سرور پرتردد
 * یکی از دلایل کندی بارگذاری صفحات بود. حالا نتیجهٔ «چیزی برای اصلاح نیست» در
 * تنظیمات ثبت می‌شود و تا وقتی درخواستی بدون شماره ساخته نشود، دوباره اسکن نمی‌شود.
 *
 * @param bool $force true = اسکن دوباره حتی اگر نشانگر ثبت شده باشد (برای ابزار تعمیر)
 */
function ticket_number_autofill(bool $force = false): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    if (!$force) {
        try {
            if (setting('ticket_autofill_done') === '1') {
                return;
            }
        } catch (Throwable) {
        }
    }
    try {
        ticket_number_ensure_schema();
        $marker = 'ticket_autofill_done';
        $rows = db()->query("SELECT id, service_group FROM tickets WHERE ticket_no IS NULL OR ticket_no = '' ORDER BY id ASC")->fetchAll();
        if (!$rows) {
            // دفعهٔ بعد دوباره کل جدول اسکن نشود (این خط، هزینهٔ هر بارگذاری صفحه را کم می‌کند)
            try {
                save_setting('ticket_autofill_done', '1');
            } catch (Throwable) {
            }
            return;
        }
        $sequence = [];
        $update = db()->prepare('UPDATE tickets SET ticket_no = ? WHERE id = ?');
        foreach ($rows as $row) {
            $prefix = ticket_number_prefix((string) $row['service_group']);
            $sequence[$prefix] = ($sequence[$prefix] ?? 0) + 1;
            $update->execute([$prefix . '-' . str_pad((string) $sequence[$prefix], 5, '0', STR_PAD_LEFT), (int) $row['id']]);
        }
    } catch (Throwable $exception) {
        if (function_exists('system_log')) {
            system_log('error', 'ticket_number_autofill', $exception->getMessage());
        }
    }
}

function is_it_agent(array $user): bool
{
    return (int) ($user['is_active'] ?? 0) === 1
        && ($user['role'] === 'agent' || (int) ($user['is_it_agent'] ?? 0) === 1);
}

function jalali_to_gregorian(int $jy, int $jm, int $jd): array
{
    $jy += 1595;
    $days = -355668 + (365 * $jy) + ((int) ($jy / 33) * 8) + (int) ((($jy % 33) + 3) / 4) + $jd;
    $days += $jm < 7 ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186;
    $gy = 400 * (int) ($days / 146097);
    $days %= 146097;
    if ($days > 36524) {
        $gy += 100 * (int) (--$days / 36524);
        $days %= 36524;
        if ($days >= 365) {
            $days++;
        }
    }
    $gy += 4 * (int) ($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $gy += (int) (($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $gd = $days + 1;
    $leap = (($gy % 4 === 0 && $gy % 100 !== 0) || $gy % 400 === 0);
    $monthDays = [0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $gm = 1;
    while ($gm <= 12 && $gd > $monthDays[$gm]) {
        $gd -= $monthDays[$gm++];
    }
    return [$gy, $gm, $gd];
}

function gregorian_to_jalali(int $gy, int $gm, int $gd): array
{
    $gDays = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $jDays = [0, 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    $gy -= 1600;
    $gm -= 1;
    $gd -= 1;
    $days = 365 * $gy + (int) (($gy + 3) / 4) - (int) (($gy + 99) / 100) + (int) (($gy + 399) / 400);
    for ($i = 0; $i < $gm; $i++) {
        $days += $gDays[$i + 1];
    }
    if ($gm > 1 && (($gy + 1600) % 4 === 0 && (($gy + 1600) % 100 !== 0 || ($gy + 1600) % 400 === 0))) {
        $days++;
    }
    $days += $gd - 79;
    $jy = 979 + (33 * (int) ($days / 12053));
    $days %= 12053;
    $jy += 4 * (int) ($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += (int) (($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $jm = $days < 186 ? 1 + (int) ($days / 31) : 7 + (int) (($days - 186) / 30);
    $jd = 1 + ($days < 186 ? $days % 31 : ($days - 186) % 30);
    return [$jy, $jm, $jd];
}

function jalali_date(string $date, bool $withTime = true): string
{
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '';
    }
    [$jy, $jm, $jd] = gregorian_to_jalali((int) date('Y', $timestamp), (int) date('n', $timestamp), (int) date('j', $timestamp));
    $result = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    return $withTime ? $result . ' ' . date('H:i', $timestamp) : $result;
}

function jalali_input_to_gregorian(string $value, bool $endOfDay = false): ?string
{
    $value = trim(str_replace('-', '/', strtr($value, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'])));
    if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $value, $matches)) {
        return null;
    }
    $jy = (int) $matches[1];
    $jm = (int) $matches[2];
    $jd = (int) $matches[3];
    $maxDay = $jm <= 6 ? 31 : ($jm <= 11 ? 30 : 30);
    if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > $maxDay) {
        return null;
    }
    [$gy, $gm, $gd] = jalali_to_gregorian($jy, $jm, $jd);
    [$roundTripJy, $roundTripJm, $roundTripJd] = gregorian_to_jalali($gy, $gm, $gd);
    if ($roundTripJy !== $jy || $roundTripJm !== $jm || $roundTripJd !== $jd) {
        return null;
    }
    return sprintf('%04d-%02d-%02d %s', $gy, $gm, $gd, $endOfDay ? '23:59:59' : '00:00:00');
}

function find_or_create_department(?string $name): ?int
{
    $name = trim((string) $name);
    if ($name === '') {
        return null;
    }
    $query = db()->prepare('SELECT id FROM departments WHERE name = ? LIMIT 1');
    $query->execute([$name]);
    $id = $query->fetchColumn();
    if ($id !== false) {
        return (int) $id;
    }
    $insert = db()->prepare('INSERT INTO departments (name) VALUES (?)');
    $insert->execute([$name]);
    return (int) db()->lastInsertId();
}

function persian_date(string $date): string
{
    return jalali_date($date);
}

function priority_sla_minutes(string $priority): int
{
    return ['critical' => 240, 'urgent' => 2880, 'normal' => 4320][$priority] ?? 4320;
}

function ensure_default_ola_policies(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    try {
        if (!(bool) db()->query("SHOW TABLES LIKE 'ola_policies'")->fetchColumn() || (int) db()->query('SELECT COUNT(*) FROM ola_policies')->fetchColumn() > 0) {
            return;
        }
        $insert = db()->prepare('INSERT INTO ola_policies (name, department_id, priority, response_minutes, resolution_minutes, escalation_1_minutes, escalation_2_minutes) VALUES (?, NULL, ?, ?, ?, ?, ?)');
        $defaults = [
            ['OLA حیاتی سراسری', 'critical', 30, 240, 240, 480],
            ['OLA فوری سراسری', 'urgent', 120, 2880, 2880, 4320],
            ['OLA عادی سراسری', 'normal', 240, 4320, 4320, 5760],
        ];
        foreach ($defaults as $default) {
            $insert->execute($default);
        }
    } catch (Throwable) {
        // OLA is optional until the 1.8 migration is applied.
    }
}

function ola_policy_for(?int $departmentId, string $priority): ?array
{
    ensure_default_ola_policies();
    try {
        $query = db()->prepare('SELECT * FROM ola_policies WHERE is_active = 1 AND priority = ? AND (department_id = ? OR department_id IS NULL) ORDER BY department_id IS NULL, id LIMIT 1');
        $query->execute([$priority, $departmentId ?: 0]);
        return $query->fetch() ?: null;
    } catch (Throwable) {
        return null;
    }
}

function ola_deadlines(?int $departmentId, string $priority, string $createdAt): array
{
    $policy = ola_policy_for($departmentId, $priority);
    if (!$policy) {
        return ['policy_id' => null, 'response_due_at' => null, 'due_at' => null, 'escalation_1_at' => null, 'escalation_2_at' => null];
    }
    $zone = new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'));
    $created = new DateTimeImmutable($createdAt, $zone);
    $deadline = static fn (string $minutes): string => business_due_at((int) $minutes, $created)->format('Y-m-d H:i:s');
    return [
        'policy_id' => (int) $policy['id'],
        'response_due_at' => $deadline((string) $policy['response_minutes']),
        'due_at' => $deadline((string) $policy['resolution_minutes']),
        'escalation_1_at' => $deadline((string) $policy['escalation_1_minutes']),
        'escalation_2_at' => $deadline((string) $policy['escalation_2_minutes']),
    ];
}

function next_business_start(DateTimeImmutable $date): DateTimeImmutable
{
    $date = $date->setTimezone(new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran')));
    while (in_array((int) $date->format('N'), [4, 5], true) || is_sla_holiday($date)) {
        $date = $date->modify('+1 day')->setTime(8, 0);
    }
    if ((int) $date->format('H') < 8) {
        return $date->setTime(8, 0);
    }
    if ((int) $date->format('H') >= 16) {
        return next_business_start($date->modify('+1 day')->setTime(8, 0));
    }
    return $date;
}

function business_due_at(int $minutes, ?DateTimeImmutable $start = null): DateTimeImmutable
{
    $cursor = next_business_start($start ?: new DateTimeImmutable('now', new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))));
    $remaining = max(0, $minutes);
    while ($remaining > 0) {
        $end = $cursor->setTime(16, 0);
        $available = (int) (($end->getTimestamp() - $cursor->getTimestamp()) / 60);
        if ($remaining <= $available) {
            return $cursor->modify('+' . $remaining . ' minutes');
        }
        $remaining -= $available;
        $cursor = next_business_start($cursor->modify('+1 day')->setTime(8, 0));
    }
    return $cursor;
}

function business_elapsed_minutes(DateTimeImmutable $start, DateTimeImmutable $end): int
{
    $zone = new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'));
    $cursor = $start->setTimezone($zone);
    $finish = $end->setTimezone($zone);
    if ($finish <= $cursor) {
        return 0;
    }
    $minutes = 0;
    while ($cursor->format('Y-m-d') <= $finish->format('Y-m-d')) {
        if (!in_array((int) $cursor->format('N'), [4, 5], true) && !is_sla_holiday($cursor)) {
            $dayStart = $cursor->setTime(8, 0);
            $dayEnd = $cursor->setTime(16, 0);
            $from = $cursor > $dayStart ? $cursor : $dayStart;
            $to = $finish < $dayEnd ? $finish : $dayEnd;
            if ($to > $from) {
                $minutes += (int) floor(($to->getTimestamp() - $from->getTimestamp()) / 60);
            }
        }
        $cursor = $cursor->modify('+1 day')->setTime(8, 0);
    }
    return $minutes;
}

function ticket_sla_transition(array $ticket, string $newStatus, string $priority): array
{
    $zone = new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'));
    $pauseMinutes = (int) ($ticket['sla_pause_minutes'] ?? 0);
    $pauseAt = !empty($ticket['sla_paused_at']) ? new DateTimeImmutable((string) $ticket['sla_paused_at'], $zone) : null;
    $oldStatus = (string) ($ticket['status'] ?? 'new');
    $wasWaiting = ticket_is_waiting_status($oldStatus);
    $isWaiting = ticket_is_waiting_status($newStatus);
    if ($wasWaiting && !$isWaiting && $pauseAt) {
        $pauseMinutes += business_elapsed_minutes($pauseAt, new DateTimeImmutable('now', $zone));
        $pauseAt = null;
    } elseif (!$wasWaiting && $isWaiting) {
        $pauseAt = new DateTimeImmutable('now', $zone);
    }
    $createdAt = new DateTimeImmutable((string) $ticket['created_at'], $zone);
    $baseDue = business_due_at(priority_sla_minutes($priority), $createdAt);
    $dueAt = $pauseMinutes > 0 ? business_due_at($pauseMinutes, $baseDue) : $baseDue;
    return [$dueAt->format('Y-m-d H:i:s'), $pauseAt?->format('Y-m-d H:i:s'), $pauseMinutes];
}

/**
 * بازتنظیم/تمدید OLA هنگام تغییر اولویت یا خروج از waiting_user.
 * مهلت‌ها هنگام انتظار کاربر در cron متوقف می‌شوند و پس از پاسخ، زمان مکث به موعدها افزوده می‌شود.
 */
function ticket_ola_transition(array $ticket, string $newStatus, string $priority): array
{
    $zone = new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'));
    $oldStatus = (string) ($ticket['status'] ?? 'new');
    $departmentId = (int) ($ticket['department_id'] ?? 0) ?: null;
    $oldPriority = (string) ($ticket['priority'] ?? 'normal');
    $recalculate = $priority !== $oldPriority
        || empty($ticket['ola_policy_id'])
        || (!array_key_exists('ola_due_at', $ticket));

    if ($recalculate) {
        $deadlines = ola_deadlines($departmentId, $priority, (string) $ticket['created_at']);
    } else {
        $deadlines = [
            'policy_id' => (int) $ticket['ola_policy_id'],
            'response_due_at' => $ticket['ola_response_due_at'] ?? null,
            'due_at' => $ticket['ola_due_at'] ?? null,
            'escalation_1_at' => $ticket['ola_escalation_1_at'] ?? null,
            'escalation_2_at' => $ticket['ola_escalation_2_at'] ?? null,
        ];
    }

    $pauseMinutes = 0;
    if (ticket_is_waiting_status($oldStatus) && !ticket_is_waiting_status($newStatus) && !empty($ticket['sla_paused_at'])) {
        $pauseAt = new DateTimeImmutable((string) $ticket['sla_paused_at'], $zone);
        $pauseMinutes = business_elapsed_minutes($pauseAt, new DateTimeImmutable('now', $zone));
    }
    if ($recalculate) {
        $pauseMinutes += (int) ($ticket['sla_pause_minutes'] ?? 0);
    }
    if ($pauseMinutes > 0) {
        foreach (['response_due_at', 'due_at', 'escalation_1_at', 'escalation_2_at'] as $key) {
            if (empty($deadlines[$key])) {
                continue;
            }
            $deadline = new DateTimeImmutable((string) $deadlines[$key], $zone);
            $deadlines[$key] = business_due_at($pauseMinutes, $deadline)->format('Y-m-d H:i:s');
        }
    }
    return $deadlines;
}

function record_asset_history(int $assetId, string $eventType, string $title, ?int $userId = null, ?int $ticketId = null, string $details = '', array $before = [], array $after = []): void
{
    if ($assetId <= 0) {
        return;
    }
    $query = db()->prepare('INSERT INTO asset_history_events (asset_id, ticket_id, user_id, event_type, title, details, before_json, after_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $query->execute([$assetId, $ticketId, $userId, $eventType, $title, $details, json_encode($before, JSON_UNESCAPED_UNICODE), json_encode($after, JSON_UNESCAPED_UNICODE)]);
}

function status_label(string $status): string
{
    return ['new' => 'جدید', 'manager_review' => 'در انتظار بررسی مدیر واحد', 'assigned' => 'ارجاع‌شده به کارشناس', 'in_progress' => 'در حال انجام', 'waiting_user' => 'در انتظار پاسخ کاربر', 'open' => 'در حال بررسی', 'pending' => 'در انتظار پاسخ', 'resolved' => 'حل‌شده', 'closed' => 'بسته‌شده', 'cancelled' => 'لغوشده به درخواست ثبت‌کننده'][$status] ?? $status;
}

function priority_label(string $priority): string
{
    return ['normal' => 'عادی', 'urgent' => 'فوری', 'critical' => 'حیاتی'][$priority] ?? $priority;
}

function ticket_type_label(string $type): string
{
    return ['incident' => 'رخداد', 'request' => 'درخواست خدمت', 'problem' => 'مشکل ریشه‌ای', 'change' => 'تغییر'][$type] ?? $type;
}

function notify_user(int $userId, string $type, string $title, string $body, ?int $ticketId = null): void
{
    if ($userId <= 0) {
        return;
    }
    db()->prepare('INSERT INTO notifications (user_id, ticket_id, notification_type, title, body) VALUES (?, ?, ?, ?, ?)')->execute([$userId, $ticketId, $type, $title, $body]);
}

function notify_ticket_parties(int $ticketId, string $type, string $title, string $body, ?int $excludeUserId = null): void
{
    $query = db()->prepare('SELECT t.requester_id, t.assigned_to, t.department_id, t.service_group, d.manager_user_id, hu.manager_user_id AS handling_manager_id FROM tickets t LEFT JOIN departments d ON d.id = t.department_id LEFT JOIN handling_units hu ON hu.code = t.service_group WHERE t.id = ? LIMIT 1');
    $query->execute([$ticketId]);
    $ticket = $query->fetch();
    if (!$ticket) {
        return;
    }
    $supervisorQuery = db()->query('SELECT id FROM users WHERE role = "supervisor" AND is_active = 1');
    $supervisorIds = array_map('intval', $supervisorQuery->fetchAll(PDO::FETCH_COLUMN));
    $unassignedAgentIds = [];
    if ($type === 'ticket_created' && empty($ticket['assigned_to'])) {
        $agentQuery = db()->prepare('SELECT u.id FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.is_active = 1 AND (u.is_it_agent = 1 OR u.role IN ("agent", "admin")) AND (u.role = "admin" OR hu.code = ?)');
        $agentQuery->execute([(string) ($ticket['service_group'] ?? 'it')]);
        $unassignedAgentIds = array_map('intval', $agentQuery->fetchAll(PDO::FETCH_COLUMN));
    }
    $recipients = array_unique(array_filter(array_merge([(int) $ticket['requester_id'], (int) $ticket['assigned_to'], (int) $ticket['manager_user_id'], (int) $ticket['handling_manager_id']], $supervisorIds, $unassignedAgentIds)));
    foreach ($recipients as $recipientId) {
        if ($excludeUserId === null || $recipientId !== $excludeUserId) {
            notify_user($recipientId, $type, $title, $body, $ticketId);
        }
    }
}

function notify_ticket_staff(int $ticketId, string $type, string $title, string $body, ?int $excludeUserId = null): void
{
    $query = db()->prepare('SELECT t.assigned_to, t.service_group, d.manager_user_id, hu.manager_user_id AS handling_manager_id FROM tickets t LEFT JOIN departments d ON d.id = t.department_id LEFT JOIN handling_units hu ON hu.code = t.service_group WHERE t.id = ? LIMIT 1');
    $query->execute([$ticketId]);
    $ticket = $query->fetch();
    if (!$ticket) {
        return;
    }
    $supervisorQuery = db()->query('SELECT id FROM users WHERE role = "supervisor" AND is_active = 1');
    $supervisorIds = array_map('intval', $supervisorQuery->fetchAll(PDO::FETCH_COLUMN));
    $unassignedAgentIds = [];
    if (empty($ticket['assigned_to'])) {
        $agentQuery = db()->prepare('SELECT u.id FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.is_active = 1 AND (u.is_it_agent = 1 OR u.role IN ("agent", "admin")) AND (u.role = "admin" OR hu.code = ?)');
        $agentQuery->execute([(string) ($ticket['service_group'] ?? 'it')]);
        $unassignedAgentIds = array_map('intval', $agentQuery->fetchAll(PDO::FETCH_COLUMN));
    }
    foreach (array_unique(array_filter(array_merge([(int) $ticket['assigned_to'], (int) $ticket['manager_user_id'], (int) $ticket['handling_manager_id']], $supervisorIds, $unassignedAgentIds))) as $recipientId) {
        if ($excludeUserId === null || $recipientId !== $excludeUserId) {
            notify_user($recipientId, $type, $title, $body, $ticketId);
        }
    }
}

function unread_notification_count(int $userId): int
{
    $query = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $query->execute([$userId]);
    return (int) $query->fetchColumn();
}

/**
 * شمارش اعلان‌های نخوانده + آخرین شناسه با یک کوئری (برای کارایی؛ در هدر استفاده می‌شود).
 */
function notification_header_summary(int $userId): array
{
    static $cache = [];
    if (array_key_exists($userId, $cache)) {
        return $cache[$userId];
    }
    try {
        $query = db()->prepare('SELECT COALESCE(SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END), 0) AS unread, COALESCE(MAX(id), 0) AS latest FROM notifications WHERE user_id = ?');
        $query->execute([$userId]);
        $row = $query->fetch() ?: [];
        return $cache[$userId] = [
            'unread' => (int) ($row['unread'] ?? 0),
            'latest' => (int) ($row['latest'] ?? 0),
        ];
    } catch (Throwable) {
        return $cache[$userId] = ['unread' => 0, 'latest' => 0];
    }
}

function latest_notification_id(int $userId): int
{
    $query = db()->prepare('SELECT COALESCE(MAX(id), 0) FROM notifications WHERE user_id = ?');
    $query->execute([$userId]);
    return (int) $query->fetchColumn();
}

function is_sla_holiday(DateTimeImmutable $date): bool
{
    $query = db()->prepare('SELECT 1 FROM holidays WHERE holiday_date = ? AND is_active = 1 LIMIT 1');
    $query->execute([$date->format('Y-m-d')]);
    return (bool) $query->fetchColumn();
}

function ldap_authenticate(string $username, string $password): ?array
{
    if (!ldap_cfg('enabled', false) || !function_exists('ldap_connect') || $password === '') {
        return null;
    }
    $host = (string) ldap_cfg('host', '');
    if ($host === '') {
        return null;
    }
    $protocol = ldap_cfg('ssl', false) ? 'ldaps://' : 'ldap://';
    $connection = ldap_connect($protocol . $host, (int) ldap_cfg('port', ldap_cfg('ssl', false) ? 636 : 389));
    if (!$connection) {
        return null;
    }
    ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
    if (defined('LDAP_OPT_NETWORK_TIMEOUT')) { @ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 8); }
    if (defined('LDAP_OPT_TIMELIMIT')) { @ldap_set_option($connection, LDAP_OPT_TIMELIMIT, 8); }
    $bindDn = (string) ldap_cfg('bind_dn', '');
    $bindPassword = (string) ldap_cfg('bind_password', '');
    $serviceBindFailed = false;
    if ($bindDn !== '' && !@ldap_bind($connection, $bindDn, $bindPassword)) {
        ldap_auth_log('service-bind-failed', $username, $connection);
        $serviceBindFailed = true;
    }
    $lookup = preg_replace('/^.*\\\\/', '', $username) ?: $username;
    $lookup = explode('@', $lookup, 2)[0];
    $escapedLookup = ldap_escape($lookup, '', LDAP_ESCAPE_FILTER);
    $escapedUsername = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
    $configuredFilter = (string) cfg('ldap.user_filter', '(&(objectCategory=person)(objectClass=user)(sAMAccountName=%s))');
    $filter = ldap_person_filter(sprintf($configuredFilter, $escapedLookup));
    $fallbackFilter = '(&(objectCategory=person)(objectClass=user)(|(sAMAccountName=' . $escapedLookup . ')(userPrincipalName=' . $escapedUsername . ')))';
    $filters = array_values(array_unique([$filter, $fallbackFilter]));
    $attributes = ['distinguishedName', 'userPrincipalName', 'displayName', 'mail', 'department', 'sAMAccountName', 'employeeID', 'telephoneNumber', 'memberOf'];
    $searchBases = [(string) ldap_cfg('base_dn', '')];
    $domainSuffix = trim((string) ldap_cfg('domain_suffix', ''));
    $addDefaultNamingContext = static function () use (&$searchBases, &$domainSuffix, $connection): void {
        $rootDse = @ldap_read($connection, '', '(objectClass=*)', ['defaultNamingContext']);
        if (!$rootDse) {
            return;
        }
        $rootEntries = ldap_get_entries($connection, $rootDse);
        $defaultNamingContext = trim((string) ($rootEntries[0]['defaultnamingcontext'][0] ?? ''));
        if ($defaultNamingContext !== '' && !in_array($defaultNamingContext, $searchBases, true)) {
            $searchBases[] = $defaultNamingContext;
        }
        if ($domainSuffix === '' && $defaultNamingContext !== '') {
            $labels = [];
            foreach (explode(',', $defaultNamingContext) as $part) {
                if (preg_match('/^DC=(.+)$/i', trim($part), $match)) {
                    $labels[] = $match[1];
                }
            }
            $domainSuffix = implode('.', $labels);
        }
    };
    $addDefaultNamingContext();
    $searchUser = static function ($ldapConnection, array $bases, array $searchFilters, array $searchAttributes): array {
        foreach ($bases as $baseDn) {
            foreach ($searchFilters as $candidate) {
                $handle = @ldap_search($ldapConnection, $baseDn, $candidate, $searchAttributes);
                if (!$handle) {
                    continue;
                }
                $result = ldap_get_entries($ldapConnection, $handle);
                if (($result['count'] ?? 0) > 0) {
                    return [$handle, $result];
                }
            }
        }
        return [false, ['count' => 0]];
    };
    $tryDirectBind = static function ($ldapConnection, array $extraCandidates = []) use ($username, $lookup, $password, &$domainSuffix): bool {
        $candidates = [];
        foreach ($extraCandidates as $candidate) {
            if (trim((string) $candidate) !== '') {
                $candidates[] = trim((string) $candidate);
            }
        }
        if ($domainSuffix !== '') {
            $candidates[] = $lookup . '@' . ltrim($domainSuffix, '@');
        }
        if (str_contains($username, '@') || str_contains($username, '\\')) {
            $candidates[] = $username;
        }
        $candidates[] = $lookup;
        foreach (array_values(array_unique($candidates)) as $candidate) {
            if (@ldap_bind($ldapConnection, $candidate, $password)) {
                return true;
            }
        }
        return false;
    };
    $directBind = false;
    if ($serviceBindFailed) {
        $addDefaultNamingContext();
        $directBind = $tryDirectBind($connection);
        if (!$directBind) {
            ldap_auth_log('direct-bind-failed', $username, $connection);
            return null;
        }
    }
    [$search, $entries] = $searchUser($connection, $searchBases, $filters, $attributes);
    if (!$search) {
        if (!$tryDirectBind($connection)) {
            ldap_auth_log('direct-bind-failed', $username, $connection);
            return null;
        }
        $directBind = true;
        $addDefaultNamingContext();
        [$search, $entries] = $searchUser($connection, $searchBases, $filters, $attributes);
    }
    if (!$search) {
        ldap_auth_log('user-search-failed', $username, $connection);
        return null;
    }
    if (($entries['count'] ?? 0) < 1) {
        if (!$directBind) {
            if ($tryDirectBind($connection)) {
                $directBind = true;
                $addDefaultNamingContext();
                [$search, $entries] = $searchUser($connection, $searchBases, $filters, $attributes);
            } else {
                ldap_auth_log('direct-bind-failed', $username, $connection);
            }
        }
    }
    if (($entries['count'] ?? 0) < 1) {
        if ($directBind) {
            return [
                'username' => $lookup,
                'full_name' => $lookup,
                'email' => '',
                'department' => '',
                'employee_number' => '',
                'phone' => '',
                'groups' => [],
            ];
        }
        ldap_auth_log('user-not-found', $username, $connection);
        return null;
    }
    $entry = $entries[0];
    $userDn = $entry['distinguishedname'][0] ?? ($entry['dn'] ?? '');
    $userUpn = $entry['userprincipalname'][0] ?? '';
    if (!$directBind) {
        $userBindOk = $userUpn !== '' && @ldap_bind($connection, $userUpn, $password);
        if (!$userBindOk && $userDn !== '') {
            $userBindOk = @ldap_bind($connection, $userDn, $password);
        }
        if (!$userBindOk && !$tryDirectBind($connection, [$userUpn, $entry['samaccountname'][0] ?? ''])) {
            ldap_auth_log('user-bind-failed', $username, $connection);
            return null;
        }
    }
    $groups = [];
    for ($groupIndex = 0; $groupIndex < (int) ($entry['memberof']['count'] ?? 0); $groupIndex++) {
        $groups[] = (string) ($entry['memberof'][$groupIndex] ?? '');
    }
    return [
        'username' => $entry['samaccountname'][0] ?? $lookup,
        'full_name' => $entry['displayname'][0] ?? $username,
        'email' => $entry['mail'][0] ?? '',
        'department' => $entry['department'][0] ?? '',
        'employee_number' => $entry['employeeid'][0] ?? '',
        'phone' => $entry['telephonenumber'][0] ?? '',
        'groups' => $groups,
    ];
}

function normalize_employee_number(mixed $value): ?string
{
    $digits = ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'];
    $code = trim(strtr(trim((string) $value), $digits));
    return $code === '' ? null : $code;
}

// شناسه کاربری که این کد پرسنلی را دارد (۰ یعنی آزاد است). $exceptUserId برای ردیف خود کاربر.
function employee_number_owner(?string $code, int $exceptUserId = 0): int
{
    if ($code === null) {
        return 0;
    }
    $query = db()->prepare('SELECT id FROM users WHERE employee_number = ? AND id <> ? LIMIT 1');
    $query->execute([$code, $exceptUserId]);
    return (int) ($query->fetchColumn() ?: 0);
}

function upsert_domain_user(array $identity): array
{
    $username = trim((string) ($identity['username'] ?? ''));
    if ($username === '') {
        throw new RuntimeException('حساب Active Directory نام کاربری معتبر ندارد.');
    }
    $query = db()->prepare('SELECT * FROM users WHERE username = ? AND auth_source = "ldap" LIMIT 1');
    $query->execute([$username]);
    $user = $query->fetch();
    if (!$user) {
        $localQuery = db()->prepare('SELECT id FROM users WHERE username = ? AND auth_source = "local" LIMIT 1');
        $localQuery->execute([$username]);
        if ($localQuery->fetchColumn() !== false) {
            throw new RuntimeException('این نام کاربری قبلاً به یک حساب محلی اختصاص دارد؛ برای جلوگیری از انتقال نقش، حساب LDAP ساخته نشد.');
        }
    }
    $departmentId = find_or_create_department($identity['department'] ?? null);
    if ($user) {
        $effectiveDepartmentId = in_array($user['role'], ['agent', 'manager'], true) && (int) $user['department_id'] > 0
            ? (int) $user['department_id']
            : $departmentId;
        $empNo = normalize_employee_number($identity['employee_number'] ?? '');
        if ($empNo === null) {
            $empNo = normalize_employee_number($user['employee_number'] ?? ''); // اگر AD خالی است کد پرسنلی سامانه حفظ شود
        }
        $codeConflict = false;
        if ($empNo !== null && employee_number_owner($empNo, (int) $user['id']) > 0) {
            // کد AD قبلاً به کاربر دیگری داده شده: کد این کاربر تغییر نمی‌کند و تداخل گزارش می‌شود.
            $codeConflict = true;
            $empNo = normalize_employee_number($user['employee_number'] ?? '');
        }
        // ۱.۵۰: کاربری که از قبل کد پرسنلی دارد، نامش از فهرست رسمی پرسنل غذا می‌آید و از AD بازنویسی نمی‌شود.
        $hasCode = trim((string) ($user['employee_number'] ?? '')) !== '';
        $fullNameToSave = $hasCode ? (string) $user['full_name'] : $identity['full_name'];
        $update = db()->prepare('UPDATE users SET full_name = ?, email = ?, employee_number = ?, phone = ?, department = ?, department_id = ?, auth_source = "ldap" WHERE id = ?');
        $update->execute([$fullNameToSave, $identity['email'], $empNo, $identity['phone'] ?? '', $identity['department'], $effectiveDepartmentId, $user['id']]);
        $user['department_id'] = $effectiveDepartmentId;
        $user['employee_number'] = $empNo;
        link_agent_assets_to_user((int) $user['id'], (string) $identity['username'], $effectiveDepartmentId);
        return array_merge($user, $identity, ['username' => $username, 'employee_number' => $empNo, 'code_conflict' => $codeConflict]);
    }
    $role = ldap_role_for_groups((array) ($identity['groups'] ?? []));
    $handlingUnitId = null;
    if ($role === 'agent') {
        $handlingUnitQuery = db()->prepare('SELECT id FROM handling_units WHERE code = "it" AND is_active = 1 LIMIT 1');
        $handlingUnitQuery->execute();
        $handlingUnitId = (int) ($handlingUnitQuery->fetchColumn() ?: 0) ?: null;
    }
    $newCode = normalize_employee_number($identity['employee_number'] ?? '');
    $codeConflict = $newCode !== null && employee_number_owner($newCode) > 0;
    if ($codeConflict) {
        $newCode = null; // کد تکراری ثبت نمی‌شود؛ ردیف بدون کد ساخته می‌شود
    }
    $insert = db()->prepare('INSERT INTO users (username, full_name, email, employee_number, phone, department, department_id, handling_unit_id, role, is_it_agent, auth_source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "ldap")');
    $insert->execute([$username, $identity['full_name'], $identity['email'], $newCode, $identity['phone'] ?? '', $identity['department'], $departmentId, $handlingUnitId, $role, $role === 'agent' ? 1 : 0]);
    $identity['id'] = (int) db()->lastInsertId();
    $identity['employee_number'] = $newCode;
    $identity['code_conflict'] = $codeConflict;
    $identity['role'] = $role;
    $identity['is_it_agent'] = $role === 'agent' ? 1 : 0;
    $identity['is_active'] = 1;
    $identity['department_id'] = $departmentId;
    $identity['handling_unit_id'] = $handlingUnitId;
    link_agent_assets_to_user((int) $identity['id'], $username, $departmentId);
    $identity['username'] = $username;
    return $identity;
}

function link_agent_assets_to_user(int $userId, string $username, ?int $departmentId): void
{
    if ($username === '') {
        return;
    }
    $userQuery = db()->prepare('SELECT employee_number, phone FROM users WHERE id = ? LIMIT 1');
    $userQuery->execute([$userId]);
    $identity = $userQuery->fetch() ?: [];
    db()->prepare('UPDATE assets SET owner_user_id = ?, department_id = COALESCE(department_id, ?), employee_number = COALESCE(employee_number, ?), phone = COALESCE(phone, ?) WHERE source = "agent" AND domain_username = ? AND owner_user_id IS NULL')->execute([$userId, $departmentId, $identity['employee_number'] ?? null, $identity['phone'] ?? null, $username]);
}

function activity_logs_ensure(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;
    try {
        // هر درخواست DDL نمی‌فرستد؛ DDL روی جدول موجود می‌تواند با قفل‌های metadata، درخواست‌های دیگر را معطل کند.
        if (db_table_exists('activity_logs')) {
            return;
        }
        db()->exec('CREATE TABLE IF NOT EXISTS activity_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            username VARCHAR(190) NULL,
            full_name VARCHAR(190) NULL,
            role VARCHAR(40) NULL,
            action_code VARCHAR(120) NOT NULL,
            action_label VARCHAR(190) NULL,
            module VARCHAR(80) NULL,
            target_type VARCHAR(40) NULL,
            target_id BIGINT UNSIGNED NULL,
            target_label VARCHAR(255) NULL,
            meta_json JSON NULL,
            ip_address VARCHAR(64) NULL,
            computer_name VARCHAR(190) NULL,
            hostname VARCHAR(190) NULL,
            user_agent VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_activity_created (created_at),
            INDEX idx_activity_user (user_id),
            INDEX idx_activity_action (action_code),
            INDEX idx_activity_module (module),
            INDEX idx_activity_ip (ip_address)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    } catch (Throwable) {
    }
}

function activity_client_ip(): string
{
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $header) {
        $value = trim((string) ($_SERVER[$header] ?? ''));
        if ($value === '') {
            continue;
        }
        if ($header === 'HTTP_X_FORWARDED_FOR') {
            $value = trim(explode(',', $value)[0]);
        }
        return substr($value, 0, 64);
    }
    return '';
}

function activity_computer_name(?string $ip = null): string
{
    // نکته: reverse-DNS (gethostbyaddr) روی شبکه می‌تواند چند ثانیه معطل کند و
    // سایت را کند می‌کند؛ بنابراین فقط از نام میزبان سرور پنل استفاده می‌شود.
    if (!defined('ACTIVITY_RESOLVE_HOST')) {
        return '';
    }
    static $cache = [];
    $ip = $ip !== null && $ip !== '' ? $ip : activity_client_ip();
    if ($ip === '') {
        return '';
    }
    if (array_key_exists($ip, $cache)) {
        return $cache[$ip];
    }
    $name = '';
    try {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !in_array($ip, ['127.0.0.1', '::1'], true)) {
            $resolved = @gethostbyaddr($ip);
            if (is_string($resolved) && $resolved !== '' && $resolved !== $ip) {
                $name = $resolved;
            }
        }
    } catch (Throwable) {
        $name = '';
    }
    $cache[$ip] = substr($name, 0, 190);
    return $cache[$ip];
}

function activity_action_label(string $code): string
{
    static $map = [
        'login_local' => 'ورود با حساب محلی',
        'login_ldap' => 'ورود با حساب دامین',
        'login_failed' => 'تلاش ناموفق ورود',
        'login_locked' => 'قفل‌شدن حساب پس از تلاش‌های ناموفق',
        'logout' => 'خروج از سامانه',
        'page_view' => 'مشاهدهٔ صفحه',
        'profile_updated' => 'به‌روزرسانی پروفایل',
        'user_theme_changed' => 'تغییر تم شخصی',
        'ticket_created' => 'ثبت درخواست جدید',
        'ticket_replied' => 'پاسخ در درخواست',
        'ticket_updated' => 'به‌روزرسانی درخواست',
        'ticket_rated' => 'امتیازدهی به درخواست',
        'ticket_reopened' => 'بازگشایی درخواست',
        'ticket_reopened_by_user' => 'بازگشایی درخواست توسط کاربر (مشکل حل نشده)',
        'ticket_cancelled_by_requester' => 'لغو درخواست توسط ثبت‌کننده',
        'ticket_cancellation_requested' => 'درخواست لغو درخواست',
        'ticket_cancellation_approved' => 'تأیید درخواست لغو درخواست',
        'ticket_cancellation_rejected' => 'رد درخواست لغو درخواست',
        'ticket_claimed' => 'برداشتن درخواست توسط کارشناس',
        'supervisor_approved' => 'تأیید سوپروایزر',
        'supervisor_rework' => 'برگشت درخواست برای اصلاح',
        'supervisor_reopened' => 'بازگشایی درخواست توسط سوپروایزر',
        'cd_dvd_in' => 'ثبت ورود رسانه (CD/DVD)',
        'cd_dvd_out' => 'ثبت خروج رسانه (CD/DVD)',
        'cd_dvd_updated' => 'ویرایش ثبت CD/DVD',
        'cd_dvd_deleted' => 'حذف ثبت CD/DVD',
        'backup_created' => 'ایجاد پشتیبان',
        'backup_restored' => 'بازگردانی پشتیبان',
        'server_inventory_collected' => 'استخراج شناسنامهٔ سرور',
        'agent_inventory_collected' => 'استخراج شناسنامهٔ کلاینت (Agent)',
        'domain_assets_imported' => 'ورود کامپیوترهای دامنه',
        'assets_pinged' => 'پینگ سیستم‌های انتخابی',
        'change_created' => 'ثبت تغییر',
        'change_updated' => 'ویرایش تغییر',
        'change_submitted' => 'ارسال تغییر',
        'problem_created' => 'ثبت مشکل',
        'problem_updated' => 'ویرایش مشکل',
        'food_ticket_config_saved' => 'ذخیرهٔ تنظیمات فیش غذا',
        'food_group_created' => 'ایجاد گروه غذا',
        'food_group_updated' => 'ویرایش گروه غذا',
        'food_group_deleted' => 'حذف گروه غذا',
        'food_group_uid_changed' => 'تغییر L_UID نمایندهٔ گروه',
        'food_group_member_added' => 'افزودن عضو به گروه غذا',
        'food_group_member_removed' => 'حذف عضو از گروه غذا',
        'food_absence_set' => 'ثبت غیبت روزانه',
        'food_absence_cleared' => 'حذف غیبت روزانه',
        'food_absence_override' => 'اصلاح غیبت بعد از قفل',
        'food_group_absence_frozen' => 'قفل شدن لیست غیبت (تردد نماینده)',
        'food_group_unknown_uid' => 'تردد با L_UID ناشناخته',
        'food_group_tickets_issued' => 'صدور فیش گروهی',
        'food_ticket_print_success' => 'چاپ موفق فیش',
        'food_ticket_print_failed' => 'شکست چاپ فیش',
        'food_ticket_retry' => 'چاپ مجدد فیش‌های خطادار',
        'food_ticket_ticket_held' => 'توقف چاپ فیش (اعلام غیبت)',
        'food_ticket_ticket_released' => 'بازگشت فیش به صف چاپ (حاضر شد)',
        'food_ticket_delivered' => 'ثبت تحویل غذا',
        'food_ticket_delivery_revoked' => 'لغو تحویل غذا',
        'settings_updated' => 'به‌روزرسانی تنظیمات',
        'activity_logs_pruned' => 'پاک‌سازی لاگ‌های قدیمی',
        'import_domain_users' => 'ورود کاربران دامین',
        'ldap_sync' => 'همگام‌سازی کاربران دامین',
        'activity_logs_exported' => 'خروجی Excel فعالیت کاربران',
        'save_profile' => 'به‌روزرسانی پروفایل',
        'add_category' => 'افزودن دسته‌بندی',
        'add_department' => 'افزودن واحد',
        'add_holiday' => 'افزودن تعطیلات',
        'add_knowledge' => 'افزودن مقالهٔ دانش‌نامه',
        'add_service' => 'افزودن خدمت',
        'add_service_field' => 'افزودن فیلد خدمت',
        'add_asset_relation' => 'افزودن ارتباط دارایی',
        'assign_org_manager' => 'انتصاب مدیر واحد',
        'remove_org_manager' => 'حذف مدیر واحد',
        'remove_org_experts' => 'حذف کارشناسان واحد',
        'save_org_assign_users' => 'ذخیرهٔ تخصیص کاربران واحد',
        'save_org_unit' => 'ذخیرهٔ واحد سازمانی',
        'save_org_node' => 'ذخیرهٔ گره سازمانی',
        'save_org_ceo' => 'ذخیرهٔ مدیرعامل',
        'save_user_org' => 'ذخیرهٔ سازمان کاربر',
        'reset_org_structure' => 'بازنشانی ساختار سازمان',
        'delete_org_unit' => 'حذف واحد سازمانی',
        'undo_org_action' => 'بازگردانی آخرین تغییر سازمان',
        'bulk_update_tickets' => 'عملیات گروهی روی درخواست‌ها',
        'create_ticket' => 'ثبت درخواست جدید',
        'update_ticket' => 'به‌روزرسانی درخواست',
        'reply' => 'پاسخ در درخواست',
        'rate_ticket' => 'امتیازدهی به درخواست',
        'mark_notification' => 'خواندن اعلان',
        'mark_all_notifications' => 'خواندن همهٔ اعلان‌ها',
        'cd_dvd_create' => 'ثبت رکورد CD/DVD',
        'cd_dvd_update' => 'ویرایش رکورد CD/DVD',
        'cd_dvd_delete' => 'حذف رکورد CD/DVD',
        'save_cd_dvd_settings' => 'ذخیرهٔ تنظیمات CD/DVD',
        'traffic_create' => 'ثبت تردد مراجع',
        'traffic_update' => 'ویرایش تردد مراجع',
        'traffic_delete' => 'حذف تردد مراجع',
        'traffic_set_exit' => 'ثبت ساعت خروج مراجع',
        'traffic_add_destination' => 'افزودن مقصد ملاقات',
        'traffic_delete_destination' => 'حذف مقصد ملاقات',
        'traffic_visit_created' => 'ثبت تردد مراجع',
        'traffic_visit_updated' => 'ویرایش تردد مراجع',
        'traffic_visit_deleted' => 'حذف تردد مراجع',
        'traffic_visit_exit_set' => 'ثبت ساعت خروج مراجع',
        'traffic_records_imported' => 'ورود گروهی سوابق تردد از Excel',
        'save_inventory_form' => 'ذخیرهٔ شناسنامهٔ فنی',
        'collect_inventory' => 'استخراج اطلاعات سیستم',
        'create_backup' => 'ایجاد پشتیبان',
        'restore_backup' => 'بازگردانی پشتیبان',
        'import_domain_assets' => 'ورود کامپیوترهای دامنه',
        'ping_assets' => 'پینگ سیستم‌های انتخابی',
        'domain_scan_start' => 'شروع اسکن دامنه',
        'domain_scan_batch' => 'ادامهٔ اسکن دامنه',
        'save_settings' => 'ذخیرهٔ تنظیمات سامانه',
        'save_role_permissions' => 'ذخیرهٔ دسترسی‌های نقش',
        'reset_role_permissions' => 'بازگرداندن همهٔ نقش‌ها به پیش‌فرض',
        'reset_role_defaults' => 'بازگرداندن دسترسی یک نقش به پیش‌فرض کد',
        'save_key_roles' => 'ذخیرهٔ نقش‌های کلیدی',
        'save_department_members' => 'ذخیرهٔ اعضای واحد',
        'update_user_role' => 'تغییر نقش کاربر',
        'sync_ldap_users' => 'همگام‌سازی کاربران دامین',
        'save_service_category' => 'ذخیرهٔ دسته‌بندی خدمات',
        'delete_service' => 'حذف خدمت',
        'delete_service_category' => 'حذف دسته‌بندی خدمات',
        'edit_service' => 'ویرایش خدمت',
        'delete_holiday' => 'حذف تعطیلات',
        'toggle_holiday' => 'فعال/غیرفعال‌کردن تعطیلات',
        'import_holidays' => 'ورود تقویم تعطیلات',
        'prune_activity_logs' => 'پاک‌سازی لاگ‌های فعالیت',
    ];
    return $map[$code] ?? $code;
}

function activity_log(array $data): void
{
    try {
        activity_logs_ensure();
        $user = $data['user'] ?? null;
        if (is_array($user)) {
            $userId = isset($user['id']) ? (int) $user['id'] : null;
            $username = (string) ($user['username'] ?? '');
            $fullName = (string) ($user['full_name'] ?? '');
            $role = (string) ($user['role'] ?? '');
        } else {
            $userId = isset($data['user_id']) ? (int) $data['user_id'] : (isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);
            $username = (string) ($data['username'] ?? '');
            $fullName = (string) ($data['full_name'] ?? '');
            $role = (string) ($data['role'] ?? '');
        }
        if ($userId && ($username === '' || $fullName === '' || $role === '')) {
            $lookup = db()->prepare('SELECT username, full_name, role FROM users WHERE id = ? LIMIT 1');
            $lookup->execute([$userId]);
            $identity = $lookup->fetch() ?: [];
            $username = $username !== '' ? $username : (string) ($identity['username'] ?? '');
            $fullName = $fullName !== '' ? $fullName : (string) ($identity['full_name'] ?? '');
            $role = $role !== '' ? $role : (string) ($identity['role'] ?? '');
        }
        $code = substr((string) ($data['action_code'] ?? 'unknown'), 0, 120);
        $meta = $data['meta'] ?? [];
        $ip = (string) ($data['ip_address'] ?? activity_client_ip());
        $computer = (string) ($data['computer_name'] ?? activity_computer_name($ip));
        $hostname = (string) ($data['hostname'] ?? ($_SERVER['SERVER_NAME'] ?? ''));
        db()->prepare('INSERT INTO activity_logs (user_id, username, full_name, role, action_code, action_label, module, target_type, target_id, target_label, meta_json, ip_address, computer_name, hostname, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([
            $userId,
            substr($username, 0, 190),
            substr($fullName, 0, 190),
            substr($role, 0, 40),
            $code,
            substr((string) ($data['action_label'] ?? activity_action_label($code)), 0, 190),
            isset($data['module']) && $data['module'] !== null ? substr((string) $data['module'], 0, 80) : null,
            isset($data['target_type']) && $data['target_type'] !== null ? substr((string) $data['target_type'], 0, 40) : null,
            isset($data['target_id']) && $data['target_id'] !== null ? (int) $data['target_id'] : null,
            isset($data['target_label']) && $data['target_label'] !== null ? substr((string) $data['target_label'], 0, 255) : null,
            $meta !== [] ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            $ip !== '' ? $ip : null,
            $computer !== '' ? $computer : null,
            $hostname !== '' ? $hostname : null,
            substr((string) ($data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 255),
        ]);
    } catch (Throwable) {
    }
}

/**
 * بازدید صفحه (page_view) کم‌اهمیت و پرحجم است و جدول گزارش فعالیت را بی‌دلیل بزرگ و کند می‌کند.
 * تصمیم ۱.۵۵: خاموش. ثبت عملیات واقعی (ایجاد/ویرایش/ورود/خروج...) ادامه دارد.
 */
const ACTIVITY_LOG_PAGE_VIEWS = false;

function activity_log_view(string $page): void
{
    if (!ACTIVITY_LOG_PAGE_VIEWS) {
        return;
    }
    if (PHP_SAPI === 'cli' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest' || isset($_GET['ajax']);
    if ($isAjax || str_starts_with($page, 'api') || $page === 'login' || $page === 'logout') {
        return;
    }
    $action = (string) ($_GET['action'] ?? '');
    // اپیندپوینت‌های فنی (feed/export/print/photo/...) بازدید صفحه نیستند و لاگ نمی‌شوند.
    static $skip = ['export_assets', 'export_report', 'export_cd_dvd', 'export_activity_logs', 'notification_feed', 'download_backup', 'download_attachment', 'profile_photo'];
    if ($action !== '' || in_array($action, $skip, true)) {
        return;
    }
    if (!empty($_GET['traffic_api']) || !empty($_GET['food_api'])) {
        return;
    }
    $lastKey = 'activity_view_' . md5($page);
    $now = time();
    if (isset($_SESSION[$lastKey]) && ($now - (int) $_SESSION[$lastKey]) < 300) {
        return;
    }
    $_SESSION[$lastKey] = $now;
    activity_log([
        'action_code' => 'page_view',
        'action_label' => 'مشاهدهٔ صفحه: ' . $page,
        'module' => $page,
        'computer_name' => '',
        'meta' => [],
    ]);
}

function activity_logs_prune(int $days = 60): int
{
    $days = max(1, $days);
    try {
        activity_logs_ensure();
        return db_delete_in_batches('activity_logs', 'created_at < (NOW() - INTERVAL ? DAY)', [$days]);
    } catch (Throwable) {
        return 0;
    }
}

/**
 * حداقل نگهداری برای داده‌های حسابرسی فیش غذا (چاپ و تحویل) — ۶ ماه.
 * هیچ کد پاک‌سازی نباید جدول‌های food_ticket_events و food_ticket_print_log را کمتر از این مدت حذف کند.
 */
const FOOD_AUDIT_RETENTION_DAYS = 183;

/**
 * پاک‌سازی جدول‌های کم‌اهمیت و پرحجم که داده‌ای برای حسابرسی ندارند:
 *  - chatbot_logs: بیش از ۹۰ روز
 *  - api_rate_limits: پنجرهٔ شمارش بیش از ۱ روز
 *  - login_attempts: پنجرهٔ قدیمی و بدون قفل فعال
 * فیش‌های غذا و لاگ چاپ هرگز اینجا پاک نمی‌شوند.
 * @return array<string,int> تعداد ردیف‌های حذف‌شده به تفکیک جدول
 */
function ops_low_value_tables_prune(): array
{
    $out = [];
    try {
        $out['chatbot_logs'] = db_delete_in_batches('chatbot_logs', 'created_at < (NOW() - INTERVAL ? DAY)', [90]);
    } catch (Throwable) {
        $out['chatbot_logs'] = 0;
    }
    try {
        $out['api_rate_limits'] = db_delete_in_batches('api_rate_limits', 'window_started < (NOW() - INTERVAL ? DAY)', [1]);
    } catch (Throwable) {
        $out['api_rate_limits'] = 0;
    }
    try {
        $out['login_attempts'] = db_delete_in_batches(
            'login_attempts',
            '(locked_until IS NULL OR locked_until < NOW()) AND window_started < (NOW() - INTERVAL ? DAY)',
            [1]
        );
    } catch (Throwable) {
        $out['login_attempts'] = 0;
    }
    return $out;
}

function activity_log_retention_days(): int
{
    $days = (int) (setting('activity_log_retention_days', '60') ?? '60');
    return $days > 0 ? $days : 60;
}

function save_audit(int $userId, string $action, ?int $ticketId = null, array $meta = []): void
{
    $GLOBALS['activity_audited'] = true;
    activity_log([
        'user_id' => $userId,
        'action_code' => $action,
        'target_type' => $ticketId !== null ? 'ticket' : null,
        'target_id' => $ticketId,
        'meta' => $meta,
    ]);
}

function log_ticket_event(int $ticketId, ?int $userId, string $eventType, ?string $fromStatus = null, ?string $toStatus = null, array $details = []): void
{
    $query = db()->prepare('INSERT INTO ticket_events (ticket_id, user_id, event_type, from_status, to_status, details) VALUES (?, ?, ?, ?, ?, ?)');
    $query->execute([$ticketId, $userId, $eventType, $fromStatus, $toStatus, json_encode($details, JSON_UNESCAPED_UNICODE)]);
}
