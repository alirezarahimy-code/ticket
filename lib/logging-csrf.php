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
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        if (!function_exists('db_table_exists') || !db_table_exists('system_logs')) {
            logging_emergency_failure('system_logs_table_missing', 'apply database schema/migrations');
        }
    } catch (Throwable $exception) {
        logging_emergency_failure('system_logs_schema_check_failed', get_class($exception));
    }
}

function system_log(string $level, string $context, string $message, array $meta = []): void
{
    $level = in_array($level, ['debug', 'info', 'notice', 'warning', 'error', 'critical'], true) ? $level : 'error';
    $meta = logging_sanitize_value($meta);
    $code = (string) ($meta['code'] ?? 'SYS-LEGACY-001');
    $definition = logging_catalog_resolve($code, (string) ($meta['category'] ?? 'system'), $level);
    $meta = logging_sanitize_event_meta($meta, $definition['code'], $context);
    $event = [
        'event_id' => logging_uuid(),
        'code' => $definition['code'],
        'category' => $definition['category'],
        'level' => $level,
        'context' => $context,
        'message' => $message,
        'meta' => $meta,
        'fingerprint' => logging_fingerprint_current($definition['code'], $context, $message),
    ];
    logging_store_system($event);
    $safeMeta = logging_json($meta) ?? '';
    $line = date('c') . ' [' . $level . '] ' . logging_sanitize_single_line($context, 120) . ' — ' . logging_sanitize_single_line($message, 2000) . ($safeMeta !== '' ? ' ' . $safeMeta : '');
    if (!logging_append_legacy_file($line)) {
        logging_emergency_failure('app_log_write_failed', '');
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
    return logging_sanitize_text($text, 2000);
}

/** ثبت یک خطای فنی با توضیح فارسی در system_logs (هیچ‌وقت خودش خطا ایجاد نمی‌کند). */
function system_error_record(string $level, string $source, string $rawMessage, string $location = '', string $errorType = '', ?Throwable $exception = null): void
{
    try {
        $errorType = str_replace('\\', '', trim($errorType));
        if ($errorType !== '' && !preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,79}$/D', $errorType)) {
            $errorType = '';
        }
        $location = str_replace('\\', '/', trim($location));
        $rootPrefix = rtrim(str_replace('\\', '/', APP_ROOT), '/') . '/';
        if ($location !== '' && str_starts_with($location, $rootPrefix)) {
            $location = substr($location, strlen($rootPrefix));
        } elseif ($location !== '' && preg_match('~^[A-Za-z]:/|^/~', $location)) {
            $location = basename(preg_replace('/:\d+$/', '', $location)) . (preg_match('/:(\d+)$/', $location, $lineMatch) ? ':' . $lineMatch[1] : '');
        }
        $location = logging_sanitize_text($location, 240);
        $explain = system_error_explain($rawMessage);
        $title = $explain['title'];
        if ($title === 'خطای نامشخص سامانه') {
            // عنوان عمومی همهٔ خطاهای ناشناخته یکی می‌شد؛ متن کوتاه خطا جای آن می‌نشیند تا گروه‌ها جدا بمانند.
            $safeMessage = system_error_mask($rawMessage);
            $shortMessage = logging_utf8_substr($safeMessage, 0, 140);
            $title = 'خطای ناشناخته: ' . $shortMessage;
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
        if ($exception instanceof Throwable) {
            $meta['stack_trace'] = logging_exception_trace($exception, 12);
        }
        $meta['code'] = $errorType === 'PDOException' || stripos($rawMessage, 'SQLSTATE') !== false ? 'SYS-DB-001' : (stripos($rawMessage, 'LdapErr') !== false ? 'INT-LDAP-001' : (stripos($rawMessage, 'CSRF') !== false ? 'SEC-CSRF-001' : 'SYS-PHP-001'));
        $meta['category'] = $meta['code'] === 'INT-LDAP-001' ? 'integration' : ($meta['code'] === 'SEC-CSRF-001' ? 'security' : 'system');
        $catalogDefinition = logging_catalog_resolve((string) $meta['code'], (string) $meta['category'], $level);
        $GLOBALS['__logging_user_message'] = (string) ($catalogDefinition['user_message'] ?? 'در اجرای عملیات مشکلی پیش آمد. کد پیگیری را به پشتیبانی بدهید.');
        system_log($level, $source, $title, $meta);
    } catch (Throwable $exception) {
        logging_emergency_failure('system_error_record_failed', get_class($exception));
    }
}

// هندلرهای سراسری برای HTTP و CLI؛ خروجی هر محیط مستقل است و خطای خام به کاربر وب نشت نمی‌کند.
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
$GLOBALS['__request_started_at'] = microtime(true);
set_exception_handler(static function (Throwable $e): void {
    system_error_record('error', 'خطای نهایی‌نشده', $e->getMessage(), $e->getFile() . ':' . $e->getLine(), get_class($e), $e);
    $requestId = logging_request_id();
    if (PHP_SAPI === 'cli') {
        if (defined('STDERR')) fwrite(STDERR, '[FATAL] ' . get_class($e) . ': ' . logging_sanitize_text($e->getMessage(), 500) . ' | Request-ID: ' . $requestId . PHP_EOL);
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    $friendlyMessage = (string) ($GLOBALS['__logging_user_message'] ?? 'در اجرای عملیات مشکلی پیش آمد. دوباره تلاش کنید؛ اگر تکرار شد کد پیگیری را به پشتیبانی بدهید.');
    echo '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;direction:rtl;padding:24px"><h2>عملیات انجام نشد</h2><p>' . htmlspecialchars($friendlyMessage, ENT_QUOTES, 'UTF-8') . '</p><p>کد پیگیری: <b dir="ltr">' . htmlspecialchars($requestId, ENT_QUOTES, 'UTF-8') . '</b></p></body>';
});
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    // جلوگیری از recursion و سیل Warning/Notice/Deprecated؛ خطاهای واقعی همچنان ثبت می‌شوند.
    if (!empty($GLOBALS['__logging_php_error_handler_active'])) return true;
    static $seenLowSeverity = [];
    static $lowSeverityCount = 0;
    $lowSeverity = in_array($severity, [E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, E_STRICT], true);
    if ($lowSeverity) {
        $fingerprint = hash('sha256', $severity . "\0" . $file . "\0" . $line . "\0" . $message);
        if (isset($seenLowSeverity[$fingerprint]) || $lowSeverityCount >= 20) {
            $GLOBALS['__logging_php_error_handler_active'] = true;
            try {
                if (function_exists('logging_increment_counter')) logging_increment_counter('throttled_total');
            } finally {
                $GLOBALS['__logging_php_error_handler_active'] = false;
            }
            return true;
        }
        $seenLowSeverity[$fingerprint] = true;
        $lowSeverityCount++;
    }
    $GLOBALS['__logging_php_error_handler_active'] = true;
    try {
    $level = match ($severity) {
        E_USER_ERROR, E_RECOVERABLE_ERROR => 'error',
        E_WARNING, E_USER_WARNING => 'warning',
        E_NOTICE, E_USER_NOTICE => 'notice',
        E_DEPRECATED, E_USER_DEPRECATED, E_STRICT => 'debug',
        default => 'warning',
    };
    $type = match ($severity) {
        E_WARNING => 'E_WARNING', E_NOTICE => 'E_NOTICE', E_DEPRECATED => 'E_DEPRECATED',
        E_USER_WARNING => 'E_USER_WARNING', E_USER_NOTICE => 'E_USER_NOTICE',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED', E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        default => 'PHP_ERROR',
    };
    system_error_record($level, 'PHP ' . $type, $message, basename($file) . ':' . $line, $type);
    } finally {
        $GLOBALS['__logging_php_error_handler_active'] = false;
    }
    return true;
});
register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $errorType = match ((int) $error['type']) {
            E_PARSE => 'E_PARSE', E_CORE_ERROR => 'E_CORE_ERROR', E_COMPILE_ERROR => 'E_COMPILE_ERROR', default => 'E_ERROR',
        };
        system_error_record('error', 'خطای قاتل PHP', (string) $error['message'], (string) $error['file'] . ':' . (string) $error['line'], $errorType);
        if (PHP_SAPI === 'cli') {
            if (defined('STDERR')) fwrite(STDERR, '[FATAL] ' . $errorType . ': ' . logging_sanitize_text((string) $error['message'], 500) . ' | Request-ID: ' . logging_request_id() . PHP_EOL);
        } elseif (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            $friendlyMessage = (string) ($GLOBALS['__logging_user_message'] ?? 'در اجرای عملیات مشکلی پیش آمد. کد پیگیری را به پشتیبانی بدهید.');
            echo '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;direction:rtl;padding:24px"><h2>عملیات انجام نشد</h2><p>' . htmlspecialchars($friendlyMessage, ENT_QUOTES, 'UTF-8') . '</p><p>کد پیگیری: <b dir="ltr">' . htmlspecialchars(logging_request_id(), ENT_QUOTES, 'UTF-8') . '</b></p></body>';
        }
    }
    $elapsed = microtime(true) - (float) ($GLOBALS['__request_started_at'] ?? microtime(true));
    if ($elapsed >= 2.0) {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $page = PHP_SAPI === 'cli' ? basename((string) ($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? 'worker')) : (preg_match('/page=([a-z0-9_\\-]+)/i', $uri, $m) ? $m[1] : 'نامشخص');
        system_log('warning', PHP_SAPI === 'cli' ? 'کندی Worker' : 'کندی صفحه', 'اجرای «' . $page . '» ' . round($elapsed, 1) . ' ثانیه طول کشید', [
            'cause' => 'کندی معمولاً از کوئری سنگین، گزارش بزرگ یا بار بالای سرور است.',
            'action' => 'اگر این پیام تکرار شد، بازهٔ گزارش یا تعداد رکوردها را بررسی کنید.',
            'ms' => (int) round($elapsed * 1000),
        ]);
    }
});

/** نمایش فارسی جزئیات یک لاگ: علت احتمالی، اقدام پیشنهادی و جزئیات فنی زیر «جزئیات». */
/**
 * ثبت خطای برنامه هم در گزارش سرور و هم در «سلامت سامانه» (system_logs).
 * پیام یکسان در همان اجرای PHP فقط یک‌بار ثبت می‌شود؛ تجمیع بین درخواست‌ها در صفحهٔ سلامت بر اساس fingerprint انجام می‌شود.
 */
function app_error_log(string $message): bool
{
    $message = system_error_mask($message);
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

/**
 * پاک‌سازی بر اساس کلاس نگهداری:
 * امنیت/حسابرسی/عملیات حساس ۱۸۳ روز، کسب‌وکار ۶۰ روز، خطاهای فنی حداقل ۹۰ روز، info/notice سی روز و debug هفت روز.
 * پارامتر days فقط می‌تواند دورهٔ خطاهای فنی را طولانی‌تر کند؛ کلاس‌های محافظت‌شده کوتاه نمی‌شوند.
 */
function system_logs_prune(int $days = 90): int
{
    $technicalDays = max(90, min(3650, $days));
    try {
        system_logs_ensure();
        $total = 0;
        $total += db_delete_in_batches('system_logs', "category IN ('security','audit','operation') AND created_at < DATE_SUB(NOW(), INTERVAL 183 DAY)", []);
        $total += db_delete_in_batches('system_logs', "category = 'business' AND created_at < DATE_SUB(NOW(), INTERVAL 60 DAY)", []);
        $total += db_delete_in_batches('system_logs', "level = 'debug' AND category NOT IN ('security','audit','operation','business') AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)", []);
        $total += db_delete_in_batches('system_logs', "level IN ('info','notice') AND category NOT IN ('security','audit','operation','business') AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)", []);
        $total += db_delete_in_batches('system_logs', "level IN ('warning','error','critical') AND category NOT IN ('security','audit','operation','business') AND created_at < DATE_SUB(NOW(), INTERVAL " . $technicalDays . " DAY)", []);
        if (function_exists('db_table_exists') && db_table_exists('log_issues')) {
            $total += db_delete_in_batches('log_issues', "status = 'resolved' AND last_seen < DATE_SUB(NOW(), INTERVAL 365 DAY)", []);
        }
        return $total;
    } catch (Throwable $exception) {
        logging_emergency_failure('system_logs_prune_failed', get_class($exception));
        return 0;
    }
}

function system_logs_recent(int $limit = 200, string $level = '', string $requestId = ''): array
{
    try {
        system_logs_ensure();
        $conditions = [];
        $params = [];
        if ($level !== '') {
            $conditions[] = 'level = ?';
            $params[] = $level;
        }
        if ($requestId !== '' && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $requestId)) {
            $conditions[] = 'request_id = ?';
            $params[] = $requestId;
        }
        $where = $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $query = db()->prepare('SELECT * FROM system_logs' . $where . ' ORDER BY id DESC LIMIT ' . max(1, min(500, $limit)));
        $query->execute($params);
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
        if (function_exists('activity_log')) {
            try { activity_log(['action_code' => 'csrf_419', 'action_label' => 'درخواست امنیتی نامعتبر', 'module' => (string) ($_GET['page'] ?? 'request'), 'result' => 'denied', 'meta' => ['method' => (string) ($_SERVER['REQUEST_METHOD'] ?? 'POST')]]); } catch (Throwable) {}
        }
        http_response_code(419);
        exit('درخواست نامعتبر است. صفحه را تازه‌سازی کنید.');
    }
}

