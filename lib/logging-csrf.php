<?php
declare(strict_types=1);

/**
 * lib/logging-csrf.php — پیام‌های فلش، لاگ سیستمی و CSRF — جداشده از bootstrap.php در نسخهٔ ۱.۶۲.۲۰ (فاز ۲ بازسازی).
 * محتوا: flash و take_flash / ticket_service_* (اسکیما و دسته‌های خدمات) / system_log* و ثبت خطای فنی با توضیح فارسی / ثبت‌کنندهٔ سراسری خطا / db_delete_in_batches / csrf_field، csrf_token، require_csrf.
 * ترتیب تعریف توابع و کدهای سطح‌بالا عیناً مانند bootstrap قبلی حفظ شده است.
 */

if (!defined('APP_ROOT')) {
    exit;
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
function system_error_record(string $level, string $source, string $rawMessage, string $location = '', string $errorType = ''): void
{
    try {
        $errorType = str_replace('\\', '', trim($errorType));
        if ($errorType !== '' && !preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,79}$/D', $errorType)) {
            $errorType = '';
        }
        $explain = system_error_explain($rawMessage);
        $title = $explain['title'];
        if ($title === 'خطای نامشخص سامانه') {
            // عنوان عمومی همهٔ خطاهای ناشناخته یکی می‌شد؛ متن کوتاه خطا جای آن می‌نشیند تا گروه‌ها جدا بمانند.
            $title = 'خطای ناشناخته: ' . mb_substr(system_error_mask($rawMessage), 0, 140);
        }
        $meta = [
            'cause' => $explain['cause'],
            'action' => $explain['action'],
            'technical' => system_error_mask($rawMessage),
            'location' => $location,
        ];
        if ($errorType !== '') {
            $meta['error_type'] = $errorType;
        }
        system_log($level, $source, $title, $meta);
    } catch (Throwable) {
        // ثبت لاگ نباید خود باعث خطای دیگری شود.
    }
}

// هندلرهای سراسری: استثنای نهایی‌نشده، خطای قاتل PHP و کندی صفحه.
if (PHP_SAPI !== 'cli') {
    $GLOBALS['__request_started_at'] = microtime(true);
    set_exception_handler(static function (Throwable $e): void {
        system_error_record('error', 'خطای نهایی‌نشده', $e->getMessage(), $e->getFile() . ':' . $e->getLine(), get_class($e));
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;direction:rtl;padding:24px"><h2>خطای داخلی سامانه</h2><p>خطا ثبت شد و برای پیگیری در گزارش خطا قابل دیدن است.</p></body>';
    });
    register_shutdown_function(static function (): void {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $errorType = match ((int) $error['type']) {
                E_PARSE => 'E_PARSE',
                E_CORE_ERROR => 'E_CORE_ERROR',
                E_COMPILE_ERROR => 'E_COMPILE_ERROR',
                default => 'E_ERROR',
            };
            system_error_record('error', 'خطای قاتل PHP', (string) $error['message'], (string) $error['file'] . ':' . (string) $error['line'], $errorType);
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

