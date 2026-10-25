<?php
declare(strict_types=1);

/**
 * lib/db-core.php — هسته، پیکربندی، HTTP امن و پایگاه داده — جداشده از bootstrap.php در نسخهٔ ۱.۶۲.۲۰ (فاز ۲ بازسازی).
 * محتوا: csp_nonce / cfg / کلاس‌های AppPDO و AppSlowStatement / db_* (اتصال، ستون‌ها، ترمیم اسکیما، تراکنش) / محدودسازی نرخ API و ورود / app_version / e / حالت روز-شب / redirect / وضعیت نصب دیتابیس.
 * ترتیب تعریف توابع و کدهای سطح‌بالا عیناً مانند bootstrap قبلی حفظ شده است.
 */

if (!defined('APP_ROOT')) {
    exit;
}

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

function db_table_columns(string $table, bool $refresh = false): array
{
    static $cache = [];
    if ($refresh) {
        unset($cache[$table]);
    }
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

function db_column_present(string $table, string $column): bool
{
    foreach (array_keys(db_table_columns($table)) as $name) {
        if (strcasecmp((string) $name, $column) === 0) {
            return true;
        }
    }
    return false;
}

/** ستون جاافتاده را اضافه می‌کند؛ خطا صفحه را نمی‌خواباند. */
function db_add_missing_column(string $table, string $column, string $ddl): bool
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
        return false;
    }
    try {
        if (db_column_present($table, $column)) {
            return true;
        }
        db()->exec($ddl);
        db_table_columns($table, true);
        return true;
    } catch (Throwable) {
        return false;
    }
}

/** مقدارهای جاافتادهٔ ENUM را بدون حذف مقدارهای فعلی اضافه می‌کند. */
function db_ensure_enum_values(string $table, string $column, array $needed, string $notNullDefault = ''): void
{
    if ($needed === [] || !preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
        return;
    }
    try {
        $stmt = db()->prepare("SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }
        $type = (string) ($row['COLUMN_TYPE'] ?? '');
        if (!preg_match('/^enum\\((.*)\\)$/i', $type, $match)) {
            return;
        }
        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $match[1], $parts);
        $values = $parts[1] ?? [];
        $missing = [];
        foreach ($needed as $need) {
            $need = (string) $need;
            if ($need !== '' && !in_array($need, $values, true)) {
                $values[] = $need;
                $missing[] = $need;
            }
        }
        if ($missing === [] || $values === []) {
            return;
        }
        $list = implode(',', array_map(static fn (string $value): string => "'" . str_replace("'", "\\'", $value) . "'", $values));
        $nullSql = strtoupper((string) ($row['IS_NULLABLE'] ?? 'YES')) === 'NO' ? ' NOT NULL' : ' NULL';
        $defaultSql = '';
        if ($notNullDefault !== '') {
            $defaultSql = " DEFAULT '" . str_replace("'", "\\'", $notNullDefault) . "'";
        } elseif (($row['COLUMN_DEFAULT'] ?? null) !== null) {
            $defaultSql = " DEFAULT '" . str_replace("'", "\\'", (string) $row['COLUMN_DEFAULT']) . "'";
        }
        db()->exec('ALTER TABLE `' . $table . '` MODIFY COLUMN `' . $column . '` ENUM(' . $list . ')' . $nullSql . $defaultSql);
        db_table_columns($table, true);
    } catch (Throwable) {
    }
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

/** حالت روز/شب سراسری رابط؛ مقدار cookie بین همهٔ مسیرهای سامانه مشترک است. */
function app_theme_mode(): string
{
    $mode = (string) ($_COOKIE['ticket_theme_mode'] ?? 'light');
    return in_array($mode, ['light', 'dark'], true) ? $mode : 'light';
}

function app_theme_toggle_markup(): string
{
    $dark = app_theme_mode() === 'dark';
    $label = $dark ? 'حالت روز' : 'حالت شب';
    $icon = $dark ? '☀️' : '🌙';
    return '<button type="button" class="theme-mode-toggle" data-theme-toggle aria-pressed="' . ($dark ? 'true' : 'false') . '" aria-label="تغییر به ' . $label . '"><span class="theme-mode-icon" data-theme-icon aria-hidden="true">' . $icon . '</span><span data-theme-label>' . $label . '</span></button>';
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

