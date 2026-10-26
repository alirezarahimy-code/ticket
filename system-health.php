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
        $summary['state_label'] = 'مشکل جدی';
    } elseif ($summary['errors_24h'] > 0 || $summary['slow_pages_24h'] > 0 || $summary['slow_queries_24h'] > 0) {
        $summary['state'] = 'warning';
        $summary['state_label'] = 'هشدار';
    } else {
        $summary['state'] = 'ok';
        $summary['state_label'] = 'سالم';
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
            SELECT context, message, MAX(level) AS level, COUNT(*) AS cnt, MIN(created_at) AS first_at, MAX(created_at) AS last_at, MAX(id) AS last_id
            FROM system_logs
            WHERE created_at >= NOW() - INTERVAL ? DAY
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

    $required = ['users', 'tickets', 'traffic_visits', 'food_orders', 'food_calendar', 'food_guest_requests', 'system_logs', 'activity_logs'];
    $missing = [];
    foreach ($required as $table) {
        if (!db_table_exists($table)) {
            $missing[] = $table;
        }
    }
    $checks[] = $missing === []
        ? ['جدول‌های اصلی', 'ok', 'همهٔ جدول‌های اصلی موجودند.']
        : ['جدول‌های اصلی', 'fail', 'جدول‌های زیر پیدا نشد: ' . implode('، ', $missing) . '. مهاجرت یا فایل schema را اجرا کنید.'];

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
        $ldap = db()->query("SELECT MAX(created_at) FROM users WHERE auth_source = 'ldap'")->fetchColumn();
        $checks[] = ['آخرین کاربر دامین ثبت‌شده', $ldap ? 'ok' : 'warn', $ldap ? persian_date((string) $ldap) : 'کاربر دامین ثبت نشده است.'];
    } catch (Throwable) {
        $checks[] = ['آخرین کاربر دامین ثبت‌شده', 'warn', 'اندازه‌گیری نشد.'];
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
    $knownUserLabels = [];
    try {
        $knownUserLabels = db()->query("SELECT full_name FROM users WHERE full_name IS NOT NULL AND full_name <> '' UNION SELECT username FROM users WHERE username IS NOT NULL AND username <> ''")->fetchAll(PDO::FETCH_COLUMN);
        usort($knownUserLabels, static fn ($a, $b): int => mb_strlen((string) $b) <=> mb_strlen((string) $a));
    } catch (Throwable) {
        // اگر فهرست کاربران در دسترس نبود، بقیهٔ الگوهای حساس همچنان ماسک می‌شوند.
    }
    $headers = ['آخرین زمان', 'اولین زمان', 'تعداد', 'سطح', 'بخش', 'دستهٔ فنی (بدون متن خام)', 'فایل و خط کد', 'وضعیت پیگیری'];
    $sheetRows = [array_map(static fn (string $h): array => ['v' => $h, 's' => 'header'], $headers)];
    foreach ($rows as $r) {
        if (!in_array((string) ($r['level'] ?? ''), ['error', 'warning'], true)) {
            continue;
        }
        $location = str_replace('\\', '/', (string) ($r['location'] ?? ''));
        $location = preg_replace('~^.*/~', '', $location) ?? $location;
        $rawTitle = (string) ($r['title'] ?? '');
        $context = (string) ($r['context'] ?? '');
        if ($context === 'کندی صفحه') {
            $title = 'کندی صفحه (جزئیات درخواست حذف شد)';
        } elseif ($context === 'کندی کوئری') {
            $title = 'کندی کوئری (متن کوئری حذف شد)';
        } else {
            $classification = system_error_explain($rawTitle);
            $title = $classification['title'] === 'خطای نامشخص سامانه'
                ? 'رخداد فنی؛ متن خام برای حفظ حریم خصوصی حذف شد'
                : (string) $classification['title'];
        }
        $values = [
            (string) ($r['last_at'] ?? ''),
            (string) ($r['first_at'] ?? ''),
            (string) ($r['count'] ?? ''),
            (string) ($r['level'] ?? ''),
            system_health_export_safe_text($context, $knownUserLabels),
            system_health_export_safe_text($title, $knownUserLabels),
            system_health_export_safe_text($location, $knownUserLabels),
            (string) ($r['status'] ?? ''),
        ];
        $sheetRows[] = array_map(static fn (string $value): array => ['v' => $value, 's' => 'body'], $values);
    }
    $bytes = xlsx_build([[
        'name' => 'سلامت سامانه',
        'rows' => $sheetRows,
        'widths' => [18, 18, 10, 12, 20, 42, 24, 14],
    ]]);
    xlsx_send('system-health-safe-' . date('Y-m-d') . '.xlsx', $bytes);
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
    $groups = system_health_groups(7);
    $canManage = user_can($user, 'system.health');
    $stateClass = ['ok' => 'good', 'warning' => 'warn', 'critical' => 'danger'][$summary['state']] ?? 'warn';
    $checkClass = ['ok' => 'good', 'warn' => 'warn', 'fail' => 'danger'];
    $checkLabel = ['ok' => 'سالم', 'warn' => 'هشدار', 'fail' => 'خطا'];

    render_header('سلامت سامانه', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">System Health</span><h1>سلامت سامانه</h1><p><a class="btn secondary" href="index.php?page=system-health&amp;export=safe">دانلود گزارش فنی پالایش‌شده برای پشتیبانی</a></p><p>گزارش فقط خطاها و هشدارها را می‌دهد؛ شناسهٔ کاربر، نشانی درخواست، یادداشت، متن خام خطا/کوئری، علت/اقدامِ آزاد و رویدادهای کاربران داخل فایل نیست. عنوان‌ها به دستهٔ فنی تبدیل می‌شوند و نام‌های ثبت‌شده، کد ملی، تلفن، ایمیل، IP و مقادیر محرمانهٔ متداول هم ماسک می‌شوند.</p><p>هشدار یعنی در ۲۴ ساعت اخیر دست‌کم یک خطا یا کندی ثبت شده؛ به‌تنهایی به معنی ازکارافتادن یا خراب‌شدن سامانه نیست. جزئیات شمارش و بررسی‌ها پایین صفحه است.</p></div></section>';

    echo '<section class="card form-card"><h2>وضعیت کلی: <span class="status ' . $stateClass . '">' . e($summary['state_label']) . '</span></h2>';
    echo '<p class="muted">۲۴ ساعت گذشته: ' . $summary['errors_24h'] . ' خطا، ' . $summary['warnings_24h'] . ' هشدار، ' . $summary['slow_pages_24h'] . ' صفحهٔ کند، ' . $summary['slow_queries_24h'] . ' کوئری کند، ' . $summary['fatal_1h'] . ' خطای قاتل در یک ساعت گذشته.</p>';
    echo '<p class="muted">قاعدهٔ وضعیت: «مشکل جدی» یعنی خطای قاتل در یک ساعت گذشته یا حداقل ۱۰ خطا در ۲۴ ساعت. «هشدار» یعنی حداقل یک خطا یا کندی.</p></section>';

    echo '<section class="card ticket-table"><div class="table-head"><span>بررسی</span><span>وضعیت</span><span>توضیح</span></div>';
    foreach ($checks as [$label, $state, $detail]) {
        echo '<div class="table-row"><span>' . e($label) . '</span><span><em class="status ' . ($checkClass[$state] ?? 'warn') . '">' . e($checkLabel[$state] ?? $state) . '</em></span><span class="muted">' . e($detail) . '</span></div>';
    }
    echo '</section>';

    echo '<section class="card ticket-table"><div class="section-title"><div><h2>رخدادهای ۷ روز گذشته</h2><p class="muted">هر نوع رخداد یک بار نشان داده می‌شود و تعداد تکرارش کنار آن است.</p></div></div>';
    if ($groups === []) {
        echo '<div class="table-row"><span class="muted">در ۷ روز گذشته رخدادی ثبت نشده است.</span></div>';
    }
    foreach ($groups as $item) {
        $levelLabel = $item['level'] === 'error' ? 'خطا' : ($item['level'] === 'warning' ? 'هشدار' : 'اطلاع');
        echo '<div class="table-row"><span><strong>' . e($item['title']) . '</strong><br><small class="muted">' . e($item['context']) . ' • ' . e($levelLabel) . ' • تعداد: ' . (int) $item['count'] . '</small></span>';
        echo '<span class="muted">' . e(SYSTEM_HEALTH_STATUSES[$item['status']] ?? $item['status']) . '</span>';
        echo '<span class="muted"><small>آخرین بار: ' . e(persian_date($item['last_at'])) . '</small>';
        if ($item['cause'] !== '') {
            echo '<br><small><b>علت احتمالی:</b> ' . e($item['cause']) . '</small>';
        }
        if ($item['action'] !== '') {
            echo '<br><small><b>اقدام پیشنهادی:</b> ' . e($item['action']) . '</small>';
        }
        if ($item['request_uri'] !== '') {
            echo '<br><small dir="ltr">' . e(mb_substr($item['request_uri'], 0, 160)) . '</small>';
        }
        if ($item['technical'] !== '' || $item['location'] !== '') {
            echo '<details><summary><small>جزئیات فنی</small></summary><small dir="ltr">' . e(trim($item['location'] . "\n" . $item['technical'])) . '</small></details>';
        }
        if ($canManage) {
            echo '<form method="post" class="role-form">' . csrf_field()
                . '<input type="hidden" name="action" value="incident_status">'
                . '<input type="hidden" name="fingerprint" value="' . e($item['fingerprint']) . '">'
                . '<input type="hidden" name="title" value="' . e($item['title']) . '">'
                . '<label>وضعیت پیگیری<select name="status">';
            foreach (SYSTEM_HEALTH_STATUSES as $key => $label) {
                echo '<option value="' . e($key) . '"' . ($item['status'] === $key ? ' selected' : '') . '>' . e($label) . '</option>';
            }
            echo '</select></label><label>یادداشت<input name="note" maxlength="1000" value="' . e($item['note']) . '"></label>'
                . '<button class="button secondary" type="submit">ذخیره</button></form>';
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
