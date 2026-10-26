<?php
declare(strict_types=1);

/*
 * سلامت سامانه (نسخهٔ ۱.۵۱)
 * فرم جدا از لاگ کاربران. خطاها و کندی‌های فنی را گروه‌بندی، با توضیح فارسی نشان می‌دهد،
 * وضعیت پیگیری هر مورد را نگه می‌دارد و چند بررسی خودکار سلامت انجام می‌دهد.
 * دسترسی: system.health
 */

const SYSTEM_HEALTH_STATUSES = ['open' => 'باز', 'investigating' => 'در حال بررسی', 'resolved' => 'حل‌شده'];

function system_health_ensure(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    db()->exec('CREATE TABLE IF NOT EXISTS system_incidents (
        fingerprint CHAR(40) NOT NULL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        status ENUM(\'open\',\'investigating\',\'resolved\') NOT NULL DEFAULT \'open\',
        note TEXT NULL,
        updated_by INT UNSIGNED NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $done = true;
}

function system_health_fingerprint(string $context, string $message): string
{
    return sha1($context . '|' . $message);
}

/** برچسب فارسی برای جدول‌ها؛ نام خام دیتابیس را به کاربر عادی نشان نده. */
function system_health_table_label(string $table): string
{
    return [
        'users' => 'حساب‌های کاربری',
        'tickets' => 'درخواست‌های پشتیبانی',
        'traffic_visits' => 'ثبت تردد',
        'food_orders' => 'سفارش‌های غذا',
        'food_calendar' => 'تقویم غذا',
        'food_guest_requests' => 'درخواست‌های غذای مهمان',
        'food_ticket_events' => 'رویدادهای فیش غذا',
        'system_logs' => 'گزارش فنی سامانه',
        'activity_logs' => 'گزارش فعالیت سامانه',
    ][$table] ?? 'دادهٔ اصلی سامانه';
}

/** تبدیل نام داخلی بخش به عنوانی قابل فهم برای کاربران. */
function system_health_human_module(string $context): string
{
    $context = trim($context);
    $known = [
        'خطای نهایی‌نشده' => 'اجرای صفحه',
        'خطای قاتل PHP' => 'اجرای برنامه',
        'کندی صفحه' => 'سرعت بازشدن صفحه',
        'کندی کوئری' => 'پاسخ‌گویی پایگاه داده',
        'asset_profile_schema' => 'شناسنامه‌های فنی',
        'inventory_collect' => 'شناسنامه‌های فنی',
        'inventory' => 'دارایی‌های سامانه',
        'asset' => 'شناسنامه‌های فنی',
        'domain_scan' => 'اسکن شبکه و دامنه',
        'transaction' => 'ثبت اطلاعات',
        'ticket_service_schema' => 'درخواست‌های پشتیبانی',
        'ticket_number_schema' => 'شماره‌گذاری درخواست‌ها',
        'request' => 'بازکردن صفحه',
        'org_schema' => 'ساختار سازمان',
        'org_ticket_schema' => 'سازمان و درخواست‌ها',
        'traffic-dashboard' => 'گزارش تردد',
        'food_group' => 'فیش‌های غذا',
        'food-order' => 'سفارش‌های غذا',
        'food_order' => 'سفارش‌های غذا',
        'backup' => 'پشتیبان‌گیری',
    ];
    if (isset($known[$context])) {
        return $known[$context];
    }
    if (str_starts_with($context, 'food_') || str_starts_with($context, 'food-')) {
        return 'غذا و فیش‌ها';
    }
    if (str_starts_with($context, 'ticket_')) {
        return 'درخواست‌های پشتیبانی';
    }
    if (str_starts_with($context, 'asset_') || str_starts_with($context, 'inventory_')) {
        return 'شناسنامه‌ها و دارایی‌ها';
    }
    return $context !== '' && preg_match('/^[\p{L}\s‌]+$/u', $context) ? $context : 'بخش فنی سامانه';
}

function system_health_human_level(string $level): string
{
    return match ($level) {
        'error' => 'خطا — نیازمند بررسی',
        'warning' => 'هشدار — پیگیری شود',
        'info' => 'اطلاع — برای آگاهی',
        'debug' => 'جزئیات فنی',
        default => 'ثبت فنی',
    };
}

/** علت و اقدام را از الگوی امن بساز؛ متن فنی خام را هرگز به خروجی ساده برنگردان. */
function system_health_humanize_event(array $item): array
{
    $context = (string) ($item['context'] ?? '');
    $technical = trim((string) ($item['technical'] ?? ''));
    $rawTitle = (string) ($item['title'] ?? '');
    if ($context === 'کندی صفحه') {
        return [
            'module' => system_health_human_module($context),
            'title' => 'یک صفحه دیرتر از حد معمول پاسخ داده است',
            'cause' => 'درخواست صفحه بیش از زمان معمول طول کشیده؛ این مورد به‌تنهایی به معنی قطع سامانه نیست.',
            'action' => 'اگر صفحه برای کاربران واقعاً کند است، این گزارش را برای پشتیبانی بفرستید؛ اگر نه، فقط پایش کنید.',
        ];
    }
    if ($context === 'کندی کوئری') {
        return [
            'module' => system_health_human_module($context),
            'title' => 'دریافت اطلاعات از پایگاه داده کند بوده است',
            'cause' => 'یک درخواست اطلاعاتی دیرتر از معمول پاسخ داده است؛ متن کوئری از گزارش قابل ارسال حذف می‌شود.',
            'action' => 'اگر این کندی تکرار می‌شود، گزارش را برای پشتیبانی بفرستید؛ در غیر این صورت نیاز به اقدام فوری نیست.',
        ];
    }
    $explanation = system_error_explain($technical !== '' ? $technical : $rawTitle);
    if (($explanation['title'] ?? '') !== 'خطای نامشخص سامانه') {
        return [
            'module' => system_health_human_module($context),
            'title' => (string) $explanation['title'],
            'cause' => (string) $explanation['cause'],
            'action' => (string) $explanation['action'],
        ];
    }
    return [
        'module' => system_health_human_module($context),
        'title' => 'یک رخداد فنی نیازمند بررسی ثبت شده است',
        'cause' => 'جزئیات تخصصی برای خوانایی و حفظ حریم خصوصی در این گزارش نمایش داده نمی‌شود.',
        'action' => 'اگر هنگام کار با سامانه مشکلی دیدید، زمان رخداد را یادداشت کنید و همین گزارش را به پشتیبانی بدهید.',
    ];
}

/** URL خام درخواست را به نام صفحه تبدیل می‌کند و همهٔ پارامترها را کنار می‌گذارد. */
function system_health_request_label(string $uri): string
{
    $query = (string) (parse_url($uri, PHP_URL_QUERY) ?: '');
    parse_str($query, $params);
    $page = (string) ($params['page'] ?? '');
    return [
        'system-health' => 'سلامت سامانه',
        'activity-log' => 'لاگ سامانه',
        'asset' => 'شناسنامهٔ فنی',
        'inventory' => 'دارایی‌ها',
        'ticket' => 'درخواست پشتیبانی',
        'new-ticket' => 'ثبت درخواست',
        'food-menu' => 'برنامهٔ غذا',
        'reports' => 'گزارش‌ها',
        'dashboard' => 'داشبورد',
        'settings' => 'تنظیمات',
    ][$page] ?? 'صفحه‌ای از سامانه';
}

/** شمارش ۲۴ ساعت گذشته به تفکیک نوع. */
function system_health_summary(): array
{
    system_health_ensure();
    system_logs_ensure();
    $row = db()->query("SELECT
        SUM(level = 'error' AND created_at >= NOW() - INTERVAL 24 HOUR) AS errors_24h,
        SUM(level = 'warning' AND created_at >= NOW() - INTERVAL 24 HOUR) AS warnings_24h,
        SUM(context = 'خطای قاتل PHP' AND created_at >= NOW() - INTERVAL 1 HOUR) AS fatal_1h,
        SUM(context = 'کندی صفحه' AND created_at >= NOW() - INTERVAL 24 HOUR) AS slow_pages_24h,
        SUM(context = 'کندی کوئری' AND created_at >= NOW() - INTERVAL 24 HOUR) AS slow_queries_24h
        FROM system_logs WHERE created_at >= NOW() - INTERVAL 24 HOUR")->fetch(PDO::FETCH_ASSOC) ?: [];
    $summary = [
        'errors_24h' => (int) ($row['errors_24h'] ?? 0),
        'warnings_24h' => (int) ($row['warnings_24h'] ?? 0),
        'fatal_1h' => (int) ($row['fatal_1h'] ?? 0),
        'slow_pages_24h' => (int) ($row['slow_pages_24h'] ?? 0),
        'slow_queries_24h' => (int) ($row['slow_queries_24h'] ?? 0),
    ];
    if ($summary['fatal_1h'] > 0 || $summary['errors_24h'] >= 10) {
        $summary['state'] = 'critical';
        $summary['state_label'] = 'نیازمند رسیدگی فوری';
        $summary['plain_message'] = 'در یک ساعت گذشته خطای جدی ثبت شده یا خطاها تکرار زیادی داشته‌اند. بررسی پشتیبانی پیشنهاد می‌شود.';
        $summary['next_step'] = 'گزارش خوانا را دانلود کنید و برای پشتیبانی فنی بفرستید.';
    } elseif ($summary['errors_24h'] > 0 || $summary['warnings_24h'] > 0 || $summary['slow_pages_24h'] > 0 || $summary['slow_queries_24h'] > 0) {
        $summary['state'] = 'warning';
        $summary['state_label'] = 'نیازمند بررسی';
        $summary['plain_message'] = 'رخداد فنی یا کندی ثبت شده است. این هشدار به‌تنهایی به معنی ازکارافتادن سامانه نیست.';
        $summary['next_step'] = 'اگر کارکردی مختل شده، گزارش خوانا را برای پشتیبانی بفرستید؛ در غیر این صورت فقط پیگیری کنید.';
    } else {
        $summary['state'] = 'ok';
        $summary['state_label'] = 'سالم';
        $summary['plain_message'] = 'در ۲۴ ساعت گذشته خطا یا کندی قابل‌توجهی ثبت نشده است.';
        $summary['next_step'] = 'اقدامی لازم نیست.';
    }
    return $summary;
}

/** وضعیت نهایی را با بررسی واقعی دیتابیس، دیسک و پوشه‌ها هم‌راستا می‌کند. */
function system_health_apply_check_state(array $summary, array $checks): array
{
    $failures = count(array_filter($checks, static fn (array $check): bool => ($check[1] ?? '') === 'fail'));
    $warnings = count(array_filter($checks, static fn (array $check): bool => ($check[1] ?? '') === 'warn'));
    if ($failures > 0) {
        $summary['state'] = 'critical';
        $summary['state_label'] = 'نیازمند رسیدگی فوری';
        $summary['plain_message'] = 'یکی از بررسی‌های پایهٔ سامانه ناموفق است. مورد قرمز را در فهرست بررسی‌ها ببینید.';
        $summary['next_step'] = 'شرح مورد ناموفق را بخوانید و از مسئول فنی کمک بگیرید.';
    } elseif ($warnings > 0 && ($summary['state'] ?? '') === 'ok') {
        $summary['state'] = 'warning';
        $summary['state_label'] = 'نیازمند بررسی';
        $summary['plain_message'] = 'سامانه در دسترس است، اما یک بررسی احتیاطی نیاز به توجه دارد.';
        $summary['next_step'] = 'توضیح مورد زرد را بخوانید؛ ممکن است فقط یک قابلیت غیرفعال باشد.';
    }
    return $summary;
}

/**
 * موارد گروه‌بندی‌شده در ۷ روز گذشته. برای هر مورد، آخرین نمونه (علت/اقدام/جزئیات) هم برمی‌گردد.
 */
function system_health_groups(int $days = 7): array
{
    system_health_ensure();
    system_logs_ensure();
    $days = max(1, min(90, $days));
    $stmt = db()->prepare("SELECT g.context, g.message, g.level, g.cnt, g.first_at, g.last_at, s.meta_json, s.request_uri,
            COALESCE(i.status, 'open') AS status, i.note
        FROM (
            SELECT context, message, CASE WHEN SUM(level = 'error') > 0 THEN 'error' ELSE 'warning' END AS level, COUNT(*) AS cnt, MIN(created_at) AS first_at, MAX(created_at) AS last_at, MAX(id) AS last_id
            FROM system_logs
            WHERE created_at >= NOW() - INTERVAL ? DAY AND level IN ('error', 'warning')
            GROUP BY context, message
        ) g
        JOIN system_logs s ON s.id = g.last_id
        LEFT JOIN system_incidents i ON i.fingerprint = SHA1(CONCAT(g.context, '|', g.message))
        ORDER BY g.last_at DESC
        LIMIT 200");
    $stmt->execute([$days]);
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $meta = json_decode((string) ($row['meta_json'] ?? ''), true);
        $meta = is_array($meta) ? $meta : [];
        $items[] = [
            'fingerprint' => system_health_fingerprint((string) $row['context'], (string) $row['message']),
            'context' => (string) $row['context'],
            'title' => (string) $row['message'],
            'level' => (string) $row['level'],
            'count' => (int) $row['cnt'],
            'first_at' => (string) $row['first_at'],
            'last_at' => (string) $row['last_at'],
            'cause' => (string) ($meta['cause'] ?? ''),
            'action' => (string) ($meta['action'] ?? ''),
            'technical' => (string) ($meta['technical'] ?? ''),
            'location' => (string) ($meta['location'] ?? ''),
            'request_uri' => (string) ($row['request_uri'] ?? ''),
            'status' => (string) $row['status'],
            'note' => (string) ($row['note'] ?? ''),
        ];
    }
    return $items;
}

/** بررسی‌های خودکار سلامت. هر مورد: [عنوان، وضعیت ok|warn|fail، توضیح]. */
function system_health_checks(): array
{
    $checks = [];
    try {
        db()->query('SELECT 1')->fetchColumn();
        $checks[] = ['اتصال به دیتابیس', 'ok', 'اتصال برقرار است.'];
    } catch (Throwable) {
        $checks[] = ['اتصال به دیتابیس', 'fail', 'اتصال برقرار نیست؛ تنظیمات دیتابیس و سرویس MariaDB را بررسی کنید.'];
    }

    $required = ['users', 'tickets', 'traffic_visits', 'food_orders', 'food_calendar', 'food_guest_requests', 'food_ticket_events', 'system_logs', 'activity_logs'];
    $missing = [];
    foreach ($required as $table) {
        if (!db_table_exists($table)) {
            $missing[] = $table;
        }
    }
    $missingLabels = array_map('system_health_table_label', $missing);
    $checks[] = $missing === []
        ? ['جدول‌های اصلی', 'ok', 'همهٔ بخش‌های اصلی دادهٔ لازم را دارند.']
        : ['جدول‌های اصلی', 'fail', 'دادهٔ لازم برای این بخش‌ها پیدا نشد: ' . implode('، ', $missingLabels) . '. نصب یا به‌روزرسانی پایگاه‌داده باید بررسی شود.'];

    $free = @disk_free_space(__DIR__);
    if ($free === false) {
        $checks[] = ['فضای خالی دیسک', 'warn', 'اندازه‌گیری نشد.'];
    } else {
        $gb = round($free / 1073741824, 1);
        $checks[] = $gb < 1
            ? ['فضای خالی دیسک', 'fail', 'فقط ' . $gb . ' گیگابایت فضا باقی مانده است؛ بکاپ‌های قدیمی را بررسی کنید.']
            : ($gb < 5 ? ['فضای خالی دیسک', 'warn', $gb . ' گیگابایت باقی مانده؛ رو به کمبود است.'] : ['فضای خالی دیسک', 'ok', $gb . ' گیگابایت آزاد.']);
    }

    foreach (['uploads', 'storage'] as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (!is_dir($path)) {
            $checks[] = is_writable(__DIR__)
                ? ['پوشهٔ ' . $dir, 'ok', 'هنوز ساخته نشده؛ در اولین نیاز ساخته می‌شود.']
                : ['پوشهٔ ' . $dir, 'fail', 'پوشه وجود ندارد و کاربر وب اجازهٔ ساختن آن را ندارد.'];
        } elseif (!is_writable($path)) {
            $checks[] = ['پوشهٔ ' . $dir, 'fail', 'قابل نوشتن نیست؛ مجوز کاربر وب را بررسی کنید.'];
        } else {
            $checks[] = ['پوشهٔ ' . $dir, 'ok', 'قابل نوشتن است.'];
        }
    }

    try {
        system_logs_ensure();
        $last = db()->query('SELECT MAX(created_at) FROM system_logs')->fetchColumn();
        $checks[] = ['ثبت لاگ فنی', 'ok', 'آخرین ثبت: ' . ($last ? persian_date((string) $last) : 'هنوز چیزی ثبت نشده است.')];
    } catch (Throwable) {
        $checks[] = ['ثبت لاگ فنی', 'fail', 'جدول لاگ فنی در دسترس نیست.'];
    }

    try {
        if (!ldap_cfg('enabled', false)) {
            $checks[] = ['ورود با حساب شبکه', 'ok', 'این قابلیت در تنظیمات خاموش است و نبودنش مشکل سامانه محسوب نمی‌شود.'];
        } else {
            $ldap = db()->query("SELECT MAX(created_at) FROM users WHERE auth_source = 'ldap'")->fetchColumn();
            $checks[] = ['آخرین ورود/ثبت حساب شبکه', $ldap ? 'ok' : 'warn', $ldap ? persian_date((string) $ldap) : 'هنوز حسابی از شبکه ثبت نشده؛ اگر ورود شبکه باید فعال باشد، تنظیماتش بررسی شود.'];
        }
    } catch (Throwable) {
        $checks[] = ['ورود با حساب شبکه', 'warn', 'وضعیت بررسی نشد؛ اگر از ورود شبکه استفاده نمی‌کنید، این مورد قابل چشم‌پوشی است.'];
    }

    $checks[] = ['نسخهٔ سامانه', 'ok', 'نسخهٔ فعلی: ' . trim((string) @file_get_contents(__DIR__ . '/VERSION'))];
    return $checks;
}

/** جدول‌هایی که دکمهٔ «تحلیل آمار» روی آن‌ها اجرا می‌شود (فهرست ثابت؛ ورودی کاربر نیست). */
const SYS_MAINT_ANALYZE_TABLES = ['activity_logs', 'system_logs', 'food_ticket_events', 'food_ticket_print_log', 'tickets', 'ticket_messages', 'traffic_visits', 'notifications'];
/** پسوندهای فایل‌های موقت برنامه که پاک‌سازی دستی می‌شوند. */
const SYS_MAINT_CACHE_EXT = ['cache', 'tmp'];

/**
 * فایل‌های کش/موقت برنامه در storage/cache که قدیمی‌تر از بازهٔ مشخص‌اند.
 * فقط فایل‌های مستقیم همان پوشه با پسوند مجاز؛ زیرپوشه، لینک نمادین و مسیر خارج از پوشه کنار گذاشته می‌شوند.
 * هیچ فایل داده، پیوست، بکاپ یا تنظیمات در این مسیر نیست.
 */
function sys_maint_cache_candidates(int $olderThanHours = 24): array
{
    $dir = realpath(APP_ROOT . '/storage/cache');
    if ($dir === false || !is_dir($dir)) {
        return ['dir' => '', 'files' => [], 'bytes' => 0];
    }
    $cutoff = time() - max(1, $olderThanHours) * 3600;
    $files = [];
    $bytes = 0;
    foreach (new DirectoryIterator($dir) as $item) {
        if ($item->isDot() || !$item->isFile() || $item->isLink()) {
            continue;
        }
        if (!in_array(strtolower($item->getExtension()), SYS_MAINT_CACHE_EXT, true) || $item->getMTime() > $cutoff) {
            continue;
        }
        $real = $item->getRealPath();
        if ($real === false || !str_starts_with($real, $dir . DIRECTORY_SEPARATOR)) {
            continue;
        }
        $size = (int) $item->getSize();
        $files[] = ['name' => $item->getFilename(), 'path' => $real, 'bytes' => $size, 'mtime' => $item->getMTime()];
        $bytes += $size;
    }
    return ['dir' => $dir, 'files' => $files, 'bytes' => $bytes];
}

/** پاک‌سازی فایل‌های کش: فهرست دوباره و در لحظهٔ اجرا ساخته می‌شود (به فهرست نمایش‌داده‌شده اعتماد نمی‌شود). */
function sys_maint_cache_purge(): array
{
    $found = sys_maint_cache_candidates(24);
    $deleted = 0;
    $bytes = 0;
    $failed = 0;
    foreach ($found['files'] as $file) {
        if (@unlink($file['path'])) {
            $deleted++;
            $bytes += $file['bytes'];
        } else {
            $failed++;
        }
    }
    return ['deleted' => $deleted, 'bytes' => $bytes, 'failed' => $failed];
}

/** ANALYZE TABLE روی جدول‌های فهرست‌شده: فقط آمار ایندکس را به‌روز می‌کند و داده را تغییر نمی‌دهد. */
function sys_maint_analyze_tables(): array
{
    $done = [];
    $failed = [];
    foreach (SYS_MAINT_ANALYZE_TABLES as $table) {
        try {
            if (!db_table_exists($table)) {
                $failed[] = $table;
                continue;
            }
            db()->query('ANALYZE TABLE `' . $table . '`')->fetchAll();
            $done[] = $table;
        } catch (Throwable) {
            $failed[] = $table;
        }
    }
    return ['done' => $done, 'failed' => $failed];
}

/** ده جدول بزرگ دیتابیس (فقط خواندنی). */
function sys_maint_table_overview(): array
{
    try {
        return db()->query('SELECT TABLE_NAME AS name, TABLE_ROWS AS rows_est, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1048576, 1) AS size_mb FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC LIMIT 10')->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

function system_health_handle_post(array $user): void
{
    require_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'incident_status') {
        $fingerprint = preg_replace('/[^a-f0-9]/', '', (string) ($_POST['fingerprint'] ?? '')) ?? '';
        $status = (string) ($_POST['status'] ?? '');
        $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 255);
        $note = mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 1000);
        if (strlen($fingerprint) !== 40 || !array_key_exists($status, SYSTEM_HEALTH_STATUSES)) {
            flash('danger', 'اطلاعات وضعیت معتبر نیست.');
        } else {
            system_health_ensure();
            db()->prepare('INSERT INTO system_incidents (fingerprint, title, status, note, updated_by) VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE title = VALUES(title), status = VALUES(status), note = VALUES(note), updated_by = VALUES(updated_by)')
                ->execute([$fingerprint, $title, $status, $note, (int) $user['id']]);
            save_audit((int) $user['id'], 'system_incident_status', null, ['fingerprint' => $fingerprint, 'status' => $status]);
            flash('success', 'وضعیت پیگیری ثبت شد: ' . SYSTEM_HEALTH_STATUSES[$status] . '.');
        }
    }
    if ($action === 'cache_purge') {
        if (empty($_POST['confirm_purge'])) {
            flash('danger', 'برای پاک‌سازی فایل‌های موقت باید تأیید را علامت بزنید.');
        } else {
            $result = sys_maint_cache_purge();
            save_audit((int) $user['id'], 'maintenance_cache_purged', null, $result);
            flash('success', 'فایل‌های موقت پاک شدند: ' . $result['deleted'] . ' فایل، ' . round($result['bytes'] / 1048576, 1) . ' مگابایت (ناموفق: ' . $result['failed'] . ').');
        }
    }
    if ($action === 'analyze_tables') {
        $result = sys_maint_analyze_tables();
        save_audit((int) $user['id'], 'maintenance_tables_analyzed', null, ['done' => count($result['done']), 'failed' => $result['failed']]);
        flash('success', 'تحلیل آمار جدول‌ها: ' . count($result['done']) . ' جدول انجام شد' . ($result['failed'] ? '، ناموفق: ' . implode('، ', $result['failed']) : '') . '.');
    }
    if ($action === 'prune_system_logs') {
        $days = (int) ($_POST['retention_days'] ?? 30);
        $deleted = system_logs_prune($days);
        save_audit((int) $user['id'], 'system_logs_pruned', null, ['days' => $days, 'deleted' => $deleted]);
        flash('success', 'لاگ‌های فنی قدیمی‌تر از ' . max(1, min(3650, $days)) . ' روز پاک شدند: ' . $deleted . ' مورد.');
    }
    redirect('index.php?page=system-health');
}

/** متنِ قابل‌صدور را برای اشتراک امن با پشتیبانی پالایش می‌کند. */
function system_health_export_safe_text(string $value, array $knownUserLabels = []): string
{
    foreach ($knownUserLabels as $label) {
        $label = trim((string) $label);
        if (mb_strlen($label) >= 2) {
            $value = str_ireplace($label, '[کاربر]', $value);
        }
    }
    $value = (string) preg_replace('/\b[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}\b/u', '[ایمیل]', $value);
    $value = (string) preg_replace('/(?<![0-9۰-۹٠-٩])[0-9۰-۹٠-٩]{10}(?![0-9۰-۹٠-٩])/u', '[شناسه]', $value);
    $value = (string) preg_replace('/(?<![0-9۰-۹٠-٩])(?:\\+?98|0098|0)?[9۹٩][0-9۰-۹٠-٩]{9}(?![0-9۰-۹٠-٩])/u', '[شماره تماس]', $value);
    $value = (string) preg_replace('/\b(?:\d{1,3}\.){3}\d{1,3}\b/u', '[IP]', $value);
    $value = (string) preg_replace('~https?://[^[:space:]]+~iu', '[نشانی وب]', $value);
    $value = (string) preg_replace('/(?i)(password|pass|pwd|token|secret|authorization|cookie|csrf|api[_-]?key|session|national_code|user_id|employee_id|staff_id)\s*"?\s*[:=]\s*"?[^",\s;}]+/u', '$1=[مخفی]', $value);
    return mb_substr($value, 0, 1200);
}

/** خروجی امن Excel برای پشتیبانی: بدون شناسه‌های کاربری، نشانی درخواست، جزئیات خام و یادداشت‌ها. */
function system_health_export_safe_xlsx(array $user): never
{
    if (!user_can($user, 'system.health')) {
        http_response_code(403);
        exit('دسترسی به سلامت سامانه مجاز نیست.');
    }
    $days = max(1, min(90, (int) ($_GET['days'] ?? 7)));
    $rows = system_health_groups($days);
    $events = [];
    foreach ($rows as $row) {
        if (!in_array((string) ($row['level'] ?? ''), ['error', 'warning'], true)) {
            continue;
        }
        $events[] = ['row' => $row, 'friendly' => system_health_humanize_event($row)];
    }
    usort($events, static function (array $a, array $b): int {
        $rankA = ($a['row']['level'] ?? '') === 'error' ? 0 : 1;
        $rankB = ($b['row']['level'] ?? '') === 'error' ? 0 : 1;
        return ($rankA <=> $rankB)
            ?: ((int) ($b['row']['count'] ?? 0) <=> (int) ($a['row']['count'] ?? 0))
            ?: strcmp((string) ($b['row']['last_at'] ?? ''), (string) ($a['row']['last_at'] ?? ''));
    });
    $knownUserLabels = [];
    try {
        $knownUserLabels = db()->query("SELECT full_name FROM users WHERE full_name IS NOT NULL AND full_name <> '' UNION SELECT username FROM users WHERE username IS NOT NULL AND username <> ''")->fetchAll(PDO::FETCH_COLUMN);
        usort($knownUserLabels, static fn ($a, $b): int => mb_strlen((string) $b) <=> mb_strlen((string) $a));
    } catch (Throwable) {
        // گزارش همچنان از شناسه‌ها، URLها و متن خام خالی می‌ماند؛ این فهرست فقط ماسک نام‌های شناخته‌شده را تقویت می‌کند.
    }
    $checks = system_health_checks();
    $summary = system_health_apply_check_state(system_health_summary(), $checks);
    $errorOccurrences = 0;
    $warningOccurrences = 0;
    foreach ($events as $event) {
        if (($event['row']['level'] ?? '') === 'error') {
            $errorOccurrences += (int) ($event['row']['count'] ?? 0);
        } else {
            $warningOccurrences += (int) ($event['row']['count'] ?? 0);
        }
    }
    $periodLabel = '۷ روز اخیر';
    if ($days !== 7) {
        $periodLabel = number_format($days) . ' روز اخیر';
    }
    $safe = static fn (string $text): string => system_health_export_safe_text($text, $knownUserLabels);
    $summaryRows = [
        [['v' => 'گزارش قابل‌فهم سلامت سامانه', 's' => 'title']],
        [['v' => 'موضوع', 's' => 'header'], ['v' => 'نتیجه', 's' => 'header'], ['v' => 'راهنما', 's' => 'header']],
        [['v' => 'بازهٔ رخدادهای فنی', 's' => 'body'], ['v' => $periodLabel, 's' => 'body'], ['v' => 'رخدادها در برگهٔ بعدی به زبان ساده گروه‌بندی شده‌اند.', 's' => 'body']],
        [['v' => 'وضعیت کلی در ۲۴ ساعت گذشته', 's' => 'body'], ['v' => (string) $summary['state_label'], 's' => 'body'], ['v' => (string) $summary['plain_message'], 's' => 'body']],
        [['v' => 'خطاهای ثبت‌شده', 's' => 'body'], ['v' => number_format((int) $summary['errors_24h']), 's' => 'body'], ['v' => 'خطا یعنی یک عملیات با مشکل روبه‌رو شده؛ تکرار زیاد یا خطای جدی نیاز به بررسی دارد.', 's' => 'body']],
        [['v' => 'هشدارها', 's' => 'body'], ['v' => number_format((int) $summary['warnings_24h']), 's' => 'body'], ['v' => 'هشدار معمولاً به معنی نیاز به پیگیری است، نه لزوماً ازکارافتادن سامانه.', 's' => 'body']],
        [['v' => 'صفحه‌های کند', 's' => 'body'], ['v' => number_format((int) $summary['slow_pages_24h']), 's' => 'body'], ['v' => 'اگر کاربران کندی حس نمی‌کنند، این مورد را فقط پایش کنید.', 's' => 'body']],
        [['v' => 'درخواست‌های کند پایگاه داده', 's' => 'body'], ['v' => number_format((int) $summary['slow_queries_24h']), 's' => 'body'], ['v' => 'متن کوئری و جزئیات خام در این فایل قرار نمی‌گیرد.', 's' => 'body']],
        [['v' => 'خطاهای جدی در یک ساعت اخیر', 's' => 'body'], ['v' => number_format((int) $summary['fatal_1h']), 's' => 'body'], ['v' => 'اگر عدد بیشتر از صفر است، گزارش را زودتر برای مسئول فنی بفرستید.', 's' => 'body']],
        [['v' => 'رخدادهای خطا در بازهٔ گزارش', 's' => 'body'], ['v' => number_format($errorOccurrences), 's' => 'body'], ['v' => 'تعداد تکرار هر نوع رخداد در برگهٔ «رخدادها» آمده است.', 's' => 'body']],
        [['v' => 'رخدادهای هشدار در بازهٔ گزارش', 's' => 'body'], ['v' => number_format($warningOccurrences), 's' => 'body'], ['v' => 'رخدادهای اطلاع‌رسانی عادی از خروجی حذف شده‌اند.', 's' => 'body']],
        [['v' => 'اقدام پیشنهادی', 's' => 'body'], ['v' => (string) $summary['next_step'], 's' => 'body'], ['v' => 'برای فهمیدن عنوان‌های اولویت‌دار، برگهٔ «رخدادها» را ببینید.', 's' => 'body']],
        [['v' => 'حریم خصوصی', 's' => 'body'], ['v' => 'اطلاعات کاربران در خروجی نیست', 's' => 'body'], ['v' => 'نام/شناسهٔ کاربر، IP، نشانی درخواست، یادداشت و متن خام خطا یا کوئری صادر نمی‌شود.', 's' => 'body']],
        [['v' => 'زمان تهیه', 's' => 'body'], ['v' => persian_date(date('Y-m-d H:i:s')), 's' => 'body'], ['v' => 'در صورت ارسال، فقط همین فایل را بفرستید؛ فایل خام سرور لازم نیست.', 's' => 'body']],
        [['v' => 'بررسی‌های پایهٔ سامانه', 's' => 'title']],
        [['v' => 'بررسی', 's' => 'header'], ['v' => 'نتیجه', 's' => 'header'], ['v' => 'توضیح ساده', 's' => 'header']],
    ];
    foreach ($checks as [$label, $state, $detail]) {
        $stateLabel = ['ok' => 'مناسب', 'warn' => 'نیازمند توجه', 'fail' => 'ناموفق'][$state] ?? 'نامشخص';
        $summaryRows[] = [
            ['v' => $safe((string) $label), 's' => 'body'],
            ['v' => $stateLabel, 's' => 'body'],
            ['v' => $safe((string) $detail), 's' => 'body'],
        ];
    }

    $eventRows = [
        [['v' => 'رخدادهای قابل‌فهم برای پیگیری', 's' => 'title']],
        [['v' => 'فقط خطاها و هشدارها نمایش داده می‌شوند؛ ردیف‌های پرتکرار بالاتر آمده‌اند. متن خام و اطلاعات کاربران حذف شده است.', 's' => 'body']],
        array_map(static fn (string $header): array => ['v' => $header, 's' => 'header'], ['اولویت', 'بخش سامانه', 'چه اتفاقی افتاده؟', 'چند بار؟', 'اولین مشاهده', 'آخرین مشاهده', 'علت احتمالی به زبان ساده', 'پیشنهاد بعدی', 'وضعیت پیگیری']),
    ];
    foreach ($events as $event) {
        $row = $event['row'];
        $friendly = $event['friendly'];
        $count = (int) ($row['count'] ?? 0);
        $isError = ($row['level'] ?? '') === 'error';
        $priority = $isError ? 'بالا — خطا' : 'معمولی — هشدار';
        if ($count >= 5) {
            $priority .= '، پرتکرار';
        }
        $eventRows[] = array_map(static fn (string $value): array => ['v' => $value, 's' => 'body'], [
            $priority,
            $safe((string) $friendly['module']),
            $safe((string) $friendly['title']),
            number_format($count) . ' بار',
            persian_date((string) $row['first_at']),
            persian_date((string) $row['last_at']),
            $safe((string) $friendly['cause']),
            $safe((string) $friendly['action']),
            SYSTEM_HEALTH_STATUSES[(string) ($row['status'] ?? 'open')] ?? 'باز',
        ]);
    }
    if ($events === []) {
        $eventRows[] = [['v' => 'در این بازه خطا یا هشداری ثبت نشده است.', 's' => 'body']];
    }
    $bytes = xlsx_build([
        [
            'name' => 'خلاصهٔ قابل‌فهم',
            'rows' => $summaryRows,
            'widths' => [34, 32, 80],
            'merges' => ['A1:C1', 'A15:C15'],
        ],
        [
            'name' => 'رخدادها',
            'rows' => $eventRows,
            'widths' => [22, 24, 42, 14, 22, 22, 56, 56, 20],
            'merges' => ['A1:I1', 'A2:I2'],
        ],
    ]);
    xlsx_send('system-health-readable-' . date('Y-m-d') . '.xlsx', $bytes);
}

function system_health_render(array $user): never
{
    if (!user_can($user, 'system.health')) {
        http_response_code(403);
        exit('دسترسی به سلامت سامانه مجاز نیست.');
    }
    if (in_array((string) ($_GET['export'] ?? ''), ['safe', 'csv'], true)) {
        system_health_export_safe_xlsx($user);
    }
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        system_health_handle_post($user);
    }
    $summary = system_health_summary();
    $checks = system_health_checks();
    $summary = system_health_apply_check_state($summary, $checks);
    $groups = system_health_groups(7);
    $canManage = user_can($user, 'system.health');
    $stateClass = ['ok' => 'good', 'warning' => 'warn', 'critical' => 'danger'][$summary['state']] ?? 'warn';
    $checkClass = ['ok' => 'good', 'warn' => 'warn', 'fail' => 'danger'];
    $checkLabel = ['ok' => 'مناسب', 'warn' => 'نیازمند توجه', 'fail' => 'ناموفق'];

    render_header('سلامت سامانه', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">گزارش سادهٔ وضعیت فنی</span><h1>سلامت سامانه</h1><p>این صفحه می‌گوید آیا در بررسی‌های پایه یا ثبت رخدادهای اخیر موردی برای پیگیری دیده شده است.</p></div><div class="actions"><a class="button" href="index.php?page=system-health&amp;export=safe">دانلود گزارش خوانا و امن (Excel)</a></div></section>';

    echo '<section class="card form-card"><h2>نتیجه: <span class="status ' . $stateClass . '">' . e($summary['state_label']) . '</span></h2>';
    echo '<p>' . e($summary['plain_message']) . '</p><p><b>پیشنهاد بعدی:</b> ' . e($summary['next_step']) . '</p>';
    echo '<p class="muted">در ۲۴ ساعت گذشته: ' . number_format($summary['errors_24h']) . ' خطا، ' . number_format($summary['warnings_24h']) . ' هشدار، ' . number_format($summary['slow_pages_24h']) . ' صفحهٔ کند، ' . number_format($summary['slow_queries_24h']) . ' پاسخ کند پایگاه‌داده؛ ' . number_format($summary['fatal_1h']) . ' خطای جدی در یک ساعت گذشته.</p>';
    echo '<p class="muted">«هشدار» به‌تنهایی به معنی قطع یا خراب‌شدن سامانه نیست. فهرست زیر نتیجهٔ بررسی‌های پایه را جداگانه نشان می‌دهد؛ رخدادهای فنی نیز برای ۷ روز گذشته گروه‌بندی شده‌اند.</p></section>';

    echo '<section class="card ticket-table"><div class="table-head"><span>بررسی</span><span>وضعیت</span><span>توضیح</span></div>';
    foreach ($checks as [$label, $state, $detail]) {
        echo '<div class="table-row"><span>' . e($label) . '</span><span><em class="status ' . ($checkClass[$state] ?? 'warn') . '">' . e($checkLabel[$state] ?? $state) . '</em></span><span class="muted">' . e($detail) . '</span></div>';
    }
    echo '</section>';

    echo '<section class="card ticket-table"><div class="section-title"><div><h2>خطاها و هشدارهای ۷ روز گذشته</h2><p class="muted">رخدادهای تکراری گروه‌بندی و به زبان ساده توضیح داده شده‌اند؛ اطلاع‌رسانی‌های عادی در این فهرست نمی‌آیند.</p></div></div>';
    if ($groups === []) {
        echo '<div class="table-row"><span class="muted">در ۷ روز گذشته خطا یا هشداری ثبت نشده است.</span></div>';
    }
    foreach ($groups as $item) {
        $friendly = system_health_humanize_event($item);
        $levelLabel = system_health_human_level((string) $item['level']);
        $repeatLabel = number_format((int) $item['count']) . ' بار در ۷ روز';
        $statusLabel = SYSTEM_HEALTH_STATUSES[$item['status']] ?? $item['status'];
        echo '<div class="table-row"><span><strong>' . e($friendly['title']) . '</strong><br><small class="muted">بخش: ' . e($friendly['module']) . ' • ' . e($levelLabel) . ' • تکرار: ' . e($repeatLabel) . '</small></span>';
        echo '<span class="muted">' . e($statusLabel) . '</span>';
        echo '<span class="muted"><small>آخرین مشاهده: ' . e(persian_date($item['last_at'])) . '</small>';
        if ($friendly['cause'] !== '') {
            echo '<br><small><b>معنی ساده:</b> ' . e($friendly['cause']) . '</small>';
        }
        if ($friendly['action'] !== '') {
            echo '<br><small><b>پیشنهاد:</b> ' . e($friendly['action']) . '</small>';
        }
        if ($item['technical'] !== '' || $item['location'] !== '') {
            echo '<details><summary><small>جزئیات تخصصی (برای مسئول فنی؛ ممکن است حاوی دادهٔ حساس باشد)</small></summary><small dir="ltr">' . e(trim($item['location'] . "\n" . $item['technical'])) . '</small></details>';
        }
        if ($canManage) {
            echo '<form method="post" class="role-form">' . csrf_field()
                . '<input type="hidden" name="action" value="incident_status">'
                . '<input type="hidden" name="fingerprint" value="' . e($item['fingerprint']) . '">'
                . '<input type="hidden" name="title" value="' . e($friendly['title']) . '">'
                . '<label>وضعیت پیگیری<select name="status">';
            foreach (SYSTEM_HEALTH_STATUSES as $key => $label) {
                echo '<option value="' . e($key) . '"' . ($item['status'] === $key ? ' selected' : '') . '>' . e($label) . '</option>';
            }
            echo '</select></label><label>یادداشت پیگیری<input name="note" maxlength="1000" value="' . e($item['note']) . '"></label>'
                . '<button class="button secondary" type="submit">ذخیرهٔ وضعیت</button></form>';
        }
        echo '</span></div>';
    }
    echo '</section>';

    if ($canManage) {
        echo '<section class="card form-card"><h2>نگهداری لاگ فنی</h2><p class="muted">لاگ‌های فنی قدیمی‌تر از بازهٔ نگهداری پاک می‌شوند. پیش‌فرض ۳۰ روز است.</p>'
            . '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="prune_system_logs">'
            . '<label>بازهٔ نگهداری (روز)<input type="number" name="retention_days" min="1" max="3650" value="30"></label>'
            . '<button class="button secondary" type="submit">پاک‌سازی لاگ‌های قدیمی</button></form></section>';
    }
    if ($canManage) {
        $cache = sys_maint_cache_candidates(24);
        $tables = sys_maint_table_overview();
        echo '<section class="card form-card" id="maintenance"><h2>نگهداری امن سامانه</h2>'
            . '<p class="muted">این بخش فقط فایل‌های موقت خود برنامه و آمار جدول‌ها را هدف می‌گیرد. داده‌های کاری، پیوست‌ها، بکاپ‌ها، تنظیمات و داده‌های حسابرسی فیش غذا هرگز حذف نمی‌شوند.</p>'
            . '<h3>فایل‌های موقت (بیش از ۲۴ ساعت)</h3>'
            . '<p>' . count($cache['files']) . ' فایل، حدود ' . round($cache['bytes'] / 1048576, 1) . ' مگابایت.</p>'
            . ($cache['dir'] === '' ? '<p class="muted">پوشهٔ storage/cache وجود ندارد.</p>' : '')
            . '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="cache_purge">'
            . '<label><input type="checkbox" name="confirm_purge" value="1"> فهرست بالا را بررسی کردم و پاک‌سازی را تأیید می‌کنم</label> '
            . '<button class="button secondary" type="submit"' . (count($cache['files']) === 0 ? ' disabled' : '') . '>پاک‌سازی فایل‌های موقت</button></form>'
            . '<h3>آمار جدول‌ها</h3>'
            . '<p class="muted">تحلیل آمار ایندکس‌ها را به‌روز می‌کند و داده را تغییر نمی‌دهد.</p>'
            . '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="analyze_tables">'
            . '<button class="button secondary" type="submit">تحلیل آمار جدول‌های اصلی</button></form>';
        if ($tables) {
            echo '<table class="table"><thead><tr><th>جدول</th><th>ردیف (تقریبی)</th><th>حجم (MB)</th></tr></thead><tbody>';
            foreach ($tables as $t) {
                echo '<tr><td>' . e((string) $t['name']) . '</td><td>' . e((string) $t['rows_est']) . '</td><td>' . e((string) $t['size_mb']) . '</td></tr>';
            }
            echo '</tbody></table>';
        }
        echo '</section>';
    }
    render_footer();
    exit;
}
