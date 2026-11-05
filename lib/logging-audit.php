<?php

declare(strict_types=1);

/**
 * ممیزی سلامت سامانه — جزئی از سیستم لاگ‌گیری مرکزی.
 *
 * چرا این ماژول داخل سیستم لاگ است و نه کنار آن
 * ==============================================
 * سامانه یک بار دچار نقصی شد که ماه‌ها خاموش ماند: فایل‌های اسکیما از
 * مهاجرت‌ها عقب افتادند، پس نصب‌های تازه جدول‌های لاگ‌گیری را نداشتند. چون
 * کد لاگ‌گیری INSERT خود را داخل try/catch بی‌صدا دارد، هیچ خطایی دیده
 * نشد — فقط هیچ لاگی ثبت نمی‌شد.
 *
 * درس: چیزی که «خطا نمی‌دهد» را فقط بررسی فعال پیدا می‌کند. و جای طبیعی
 * این بررسی همان‌جاست که بقیهٔ سلامت سامانه ثبت می‌شود، نه یک سامانهٔ
 * موازی. پس خروجی این ماژول از همان مسیر همیشگی عبور می‌کند:
 *
 *   log_event()            → جدول system_logs (تاریخچهٔ هر اجرا)
 *   logging_issue_upsert() → جدول log_issues  (مشکل باز، با شمارش تکرار)
 *   $alerts                → جدول logging_health_alerts + اعلان به ادمین
 *
 * و در «داشبورد لاگ» کنار بقیهٔ رخدادها دیده می‌شود.
 *
 * برتری نسبت به بررسی ایستا
 * ==========================
 * این ماژول داخل سامانهٔ در حال اجراست، پس می‌تواند **پایگاه دادهٔ واقعی
 * همین نصب** را ببیند — کاری که بررسی ایستای زمان ساخت هرگز نمی‌تواند.
 * مهم‌ترین بررسی‌ها دقیقاً همین‌ها هستند: آیا جدول‌ها و ستون‌هایی که کد
 * لازم دارد، روی *این* سرور واقعاً وجود دارند؟
 *
 * فلسفهٔ خط مبنا
 * ==============
 * وقتی سوئیت تست ۳۵ شکست دائمی داشت، آن‌ها نویز پس‌زمینه شدند و یک باگ
 * واقعی داخلشان گم شد. ابزاری که همیشه قرمز است نادیده گرفته می‌شود. پس
 * بررسی‌ها دو دسته‌اند:
 *
 *  - «ساختاری»: هرگز نباید یافته داشته باشند. هر یافته یعنی این نصب
 *    خراب است → OPS-AUDIT-003 با شدت critical.
 *  - «بدهی فنی»: عدد فعلی پذیرفته شده و در storage نگهداری می‌شود. فقط
 *    **بدتر شدن** هشدار می‌دهد → OPS-AUDIT-002 با شدت warning.
 */

defined('APP_ROOT') || exit;

/** مسیر فایل خط مبنا. در storage است چون مخصوص همین نصب است، نه کد. */
function logging_audit_baseline_path(): string
{
    return APP_ROOT . '/storage/logs/audit-baseline.json';
}

/**
 * بررسی‌هایی که هرگز نباید یافته داشته باشند.
 * یافته در این‌ها یعنی نصب خراب است، نه اینکه بدهی فنی دارد.
 */
function logging_audit_structural_checks(): array
{
    return ['db_schema_tables', 'db_schema_columns', 'db_pending_migrations', 'filesystem_writable'];
}

/**
 * جدول‌هایی که نبودشان یعنی یک زیرسامانه کامل از کار افتاده.
 * عمداً دستی فهرست شده‌اند تا اگر کسی جدولی را از اسکیما حذف کرد، اینجا
 * بماند و فریاد بزند.
 */
function logging_audit_required_tables(): array
{
    return [
        'system_logs' => 'ثبت رخدادهای فنی',
        'activity_logs' => 'گزارش فعالیت کاربران',
        'log_issues' => 'تجمیع مشکلات باز',
        'log_issue_history' => 'تاریخچهٔ رسیدگی به مشکلات',
        'log_issue_events' => 'حذف تکراری رخدادها',
        'log_issue_notifications' => 'اعلان مشکلات',
        'logging_health_alerts' => 'هشدارهای سلامت',
        'schema_updates' => 'ثبت مهاجرت‌های اجراشده',
        'users' => 'حساب‌های کاربری',
        'tickets' => 'درخواست‌ها',
        'notifications' => 'اعلان‌ها',
        'settings' => 'تنظیمات سامانه',
    ];
}

/**
 * ستون‌هایی که کد بدون شرط به آن‌ها تکیه می‌کند.
 * این‌ها از مهاجرت‌هایی آمده‌اند که روی نصب‌های قدیمی ممکن است اجرا نشده
 * باشند، و نبودشان باعث شکست خاموش می‌شود نه خطای آشکار.
 */
function logging_audit_required_columns(): array
{
    return [
        'system_logs' => ['event_id', 'request_id', 'trace_id', 'operation_id', 'parent_event_id', 'code', 'category', 'fingerprint'],
        'activity_logs' => ['event_id', 'request_id', 'trace_id', 'operation_id', 'parent_event_id', 'result', 'code', 'category'],
        'ticket_events' => ['event_id', 'request_id', 'trace_id', 'operation_id', 'parent_event_id'],
        'log_issues' => ['fingerprint', 'context', 'occurrences', 'status'],
        'notifications' => ['idempotency_key'],
        'assets' => ['hostname_key'],
        'users' => ['must_change_password'],
    ];
}

/** یک یافتهٔ ممیزی. */
function logging_audit_finding(string $where, string $detail): array
{
    return ['where' => $where, 'detail' => $detail];
}

/**
 * آیا جدول در پایگاه دادهٔ همین نصب وجود دارد؟
 * از db_table_exists استفاده می‌کند اگر در دسترس باشد.
 */
function logging_audit_table_exists(string $table): bool
{
    if (function_exists('db_table_exists')) {
        return db_table_exists($table);
    }
    try {
        $stmt = db()->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable) {
        return false;
    }
}

/** ستون‌های موجود یک جدول در پایگاه دادهٔ همین نصب. */
function logging_audit_table_columns(string $table): array
{
    try {
        $stmt = db()->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->execute([$table]);
        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    } catch (Throwable) {
        return [];
    }
}

// ---------------------------------------------------------------------------
// بررسی‌های ساختاری — روی پایگاه دادهٔ زندهٔ همین نصب
// ---------------------------------------------------------------------------

/** هر جدول لازم باید روی این نصب واقعاً وجود داشته باشد. */
function logging_audit_check_db_schema_tables(): array
{
    $findings = [];
    foreach (logging_audit_required_tables() as $table => $purpose) {
        if (!logging_audit_table_exists($table)) {
            $findings[] = logging_audit_finding($table, 'جدول وجود ندارد — ' . $purpose . ' کار نمی‌کند');
        }
    }
    return [
        'key' => 'db_schema_tables',
        'title' => 'جدول‌های لازم در پایگاه داده',
        'findings' => $findings,
        'rationale' => 'نصب تازه‌ای که از اسکیمای ناقص ساخته شده، این جدول‌ها را ندارد و زیرسامانهٔ مربوطه بی‌صدا از کار می‌افتد.',
    ];
}

/** هر ستونی که کد بدون شرط می‌خواند باید وجود داشته باشد. */
function logging_audit_check_db_schema_columns(): array
{
    $findings = [];
    foreach (logging_audit_required_columns() as $table => $columns) {
        if (!logging_audit_table_exists($table)) {
            continue; // نبودِ جدول را بررسی دیگری گزارش می‌کند
        }
        $present = array_map('strtolower', logging_audit_table_columns($table));
        foreach ($columns as $column) {
            if (!in_array(strtolower($column), $present, true)) {
                $findings[] = logging_audit_finding($table . '.' . $column, 'ستون وجود ندارد؛ کوئری‌های مربوطه شکست می‌خورند');
            }
        }
    }
    return [
        'key' => 'db_schema_columns',
        'title' => 'ستون‌های لازم در پایگاه داده',
        'findings' => $findings,
        'rationale' => 'ستون گم‌شده معمولاً یعنی یک مهاجرت اجرا نشده است. خطایش داخل try/catch بلعیده می‌شود و قابلیت بی‌صدا می‌میرد.',
    ];
}

/** هر فایل مهاجرت باید در schema_updates ثبت شده باشد. */
function logging_audit_check_db_pending_migrations(): array
{
    $findings = [];
    if (!logging_audit_table_exists('schema_updates')) {
        return [
            'key' => 'db_pending_migrations',
            'title' => 'مهاجرت‌های اجرانشده',
            'findings' => [logging_audit_finding('schema_updates', 'جدول ثبت مهاجرت‌ها وجود ندارد')],
            'rationale' => 'بدون این جدول معلوم نیست کدام مهاجرت اجرا شده است.',
        ];
    }
    $applied = [];
    try {
        $rows = db()->query('SELECT filename FROM schema_updates')->fetchAll(PDO::FETCH_COLUMN) ?: [];
        foreach ($rows as $row) {
            $applied[strtolower(basename((string) $row))] = true;
        }
    } catch (Throwable $exception) {
        return [
            'key' => 'db_pending_migrations',
            'title' => 'مهاجرت‌های اجرانشده',
            'findings' => [logging_audit_finding('schema_updates', 'خواندن جدول ممکن نشد: ' . get_class($exception))],
            'rationale' => 'وضعیت مهاجرت‌ها نامعلوم است.',
        ];
    }
    foreach (glob(APP_ROOT . '/migrations/*.sql') ?: [] as $path) {
        $name = strtolower(basename($path));
        if (str_contains($name, 'rollback')) {
            continue;
        }
        if (!isset($applied[$name])) {
            $findings[] = logging_audit_finding('migrations/' . basename($path), 'در این نصب اجرا نشده است');
        }
    }
    return [
        'key' => 'db_pending_migrations',
        'title' => 'مهاجرت‌های اجرانشده',
        'findings' => $findings,
        'rationale' => 'مهاجرت اجرانشده یعنی ساختار پایگاه داده از نسخهٔ کد عقب است.',
    ];
}

/** پوشه‌هایی که سامانه در آن‌ها می‌نویسد باید قابل نوشتن باشند. */
function logging_audit_check_filesystem_writable(): array
{
    $findings = [];
    $required = ['storage', 'storage/logs', 'storage/sessions', 'storage/backups'];
    foreach ($required as $relative) {
        $path = APP_ROOT . '/' . $relative;
        if (!is_dir($path)) {
            if (!@mkdir($path, 0775, true) && !is_dir($path)) {
                $findings[] = logging_audit_finding($relative, 'پوشه وجود ندارد و ساخته نشد');
                continue;
            }
        }
        if (!is_writable($path)) {
            $findings[] = logging_audit_finding($relative, 'پوشه قابل نوشتن نیست');
        }
    }
    return [
        'key' => 'filesystem_writable',
        'title' => 'قابل نوشتن بودن پوشه‌های سامانه',
        'findings' => $findings,
        'rationale' => 'اگر storage قابل نوشتن نباشد، صف اضطراری لاگ هم کار نمی‌کند و رخدادها برای همیشه از بین می‌روند.',
    ];
}

// ---------------------------------------------------------------------------
// بررسی‌های رفتاری — سلامت کد در حال اجرا
// ---------------------------------------------------------------------------

/**
 * صف اضطراری لاگ نباید انباشته شود.
 * انباشت یعنی نوشتن در پایگاه داده شکست می‌خورد و رخدادها روی دیسک مانده‌اند.
 */
function logging_audit_check_spool_backlog(): array
{
    $findings = [];
    $stats = function_exists('logging_spool_stats') ? logging_spool_stats() : [];
    $files = (int) ($stats['files'] ?? 0);
    $age = (int) ($stats['age_seconds'] ?? 0);
    if ($files > 0 && $age > 600) {
        $findings[] = logging_audit_finding('storage/logs/spool', $files . ' رخداد بیش از ' . intdiv($age, 60) . ' دقیقه در صف مانده‌اند');
    }
    if ((int) ($stats['dead_letter_files'] ?? 0) > 0) {
        $findings[] = logging_audit_finding('storage/logs/dead-letter', $stats['dead_letter_files'] . ' رخداد پس از تلاش‌های ناموفق کنار گذاشته شده‌اند');
    }
    return [
        'key' => 'spool_backlog',
        'title' => 'انباشت صف اضطراری لاگ',
        'findings' => $findings,
        'rationale' => 'صف پر یعنی رخدادها به پایگاه داده نمی‌رسند؛ سامانه ظاهراً سالم است ولی حافظهٔ رخدادش کار نمی‌کند.',
    ];
}

/**
 * مشکلات باز در log_issues که مدت زیادی رسیدگی نشده‌اند.
 * این دقیقاً «سلامت رفتاری کد در حال اجرا» است: خطاهایی که واقعاً رخ داده‌اند.
 */
function logging_audit_check_unresolved_issues(): array
{
    $findings = [];
    if (!logging_audit_table_exists('log_issues')) {
        return [
            'key' => 'unresolved_issues',
            'title' => 'مشکلات باز رسیدگی‌نشده',
            'findings' => [],
            'rationale' => 'جدول مشکلات در دسترس نیست.',
        ];
    }
    try {
        $stmt = db()->query(
            "SELECT code, context, severity, occurrences, title
               FROM log_issues
              WHERE status = 'open' AND severity IN ('error','critical')
                AND last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)
           ORDER BY occurrences DESC
              LIMIT 20"
        );
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $findings[] = logging_audit_finding(
                (string) $row['code'] . ' / ' . (string) $row['context'],
                (int) $row['occurrences'] . ' بار — ' . mb_substr((string) $row['title'], 0, 120)
            );
        }
    } catch (Throwable $exception) {
        $findings[] = logging_audit_finding('log_issues', 'خواندن ممکن نشد: ' . get_class($exception));
    }
    return [
        'key' => 'unresolved_issues',
        'title' => 'مشکلات باز رسیدگی‌نشده',
        'findings' => $findings,
        'rationale' => 'این‌ها خطاهایی هستند که در هفتهٔ گذشته واقعاً روی این نصب رخ داده‌اند و هنوز بسته نشده‌اند.',
    ];
}

/** Workerهایی که ضربان‌شان قطع شده. */
function logging_audit_check_worker_heartbeats(): array
{
    $findings = [];
    $dir = APP_ROOT . '/storage/logs/worker-heartbeats';
    $expected = ['issue-notifications' => 3600, 'issue-rebuild' => 86400, 'spool-replay' => 1800];
    foreach ($expected as $name => $maxAge) {
        $path = $dir . DIRECTORY_SEPARATOR . $name . '.json';
        if (!is_file($path)) {
            continue; // Worker شاید هنوز هرگز اجرا نشده؛ هشدار کاذب نمی‌دهیم
        }
        $data = json_decode((string) @file_get_contents($path), true);
        $last = is_array($data) && !empty($data['last_finished_at']) ? strtotime((string) $data['last_finished_at']) : false;
        if ($last === false || $last < time() - $maxAge) {
            $findings[] = logging_audit_finding($name, 'ضربان قدیمی‌تر از ' . intdiv($maxAge, 60) . ' دقیقه');
        }
    }
    return [
        'key' => 'worker_heartbeats',
        'title' => 'ضربان Workerهای لاگ',
        'findings' => $findings,
        'rationale' => 'Worker خوابیده یعنی صف پردازش نمی‌شود و اعلان مشکلات به دست ادمین نمی‌رسد.',
    ];
}

// ---------------------------------------------------------------------------
// اجرا
// ---------------------------------------------------------------------------

/** مسیر ضربان ممیزی — کنار ضربان بقیهٔ Workerها. */
function logging_audit_heartbeat_path(): string
{
    return APP_ROOT . '/storage/logs/worker-heartbeats/system-audit.json';
}

/**
 * فاصلهٔ بیش از حد از اجرای قبلی یعنی کرون سلامت مدتی نخوابیده بوده.
 *
 * محدودیت صادقانه: ممیزی نمی‌تواند نبودِ خودش را گزارش کند، چون وقتی اجرا
 * نمی‌شود کدی هم اجرا نمی‌شود. ولی وقتی **دوباره** اجرا شد می‌تواند بگوید
 * «من مدت زیادی اجرا نشده بودم» — و همین برای تشخیص قطعی سرویس کافی است.
 */
function logging_audit_check_audit_continuity(): array
{
    $findings = [];
    $path = logging_audit_heartbeat_path();
    $maxGap = 172800; // دو شبانه‌روز
    if (is_file($path)) {
        $data = json_decode((string) @file_get_contents($path), true);
        $last = is_array($data) && !empty($data['last_run_at']) ? strtotime((string) $data['last_run_at']) : false;
        if ($last !== false && $last < time() - $maxGap) {
            $findings[] = logging_audit_finding(
                'system-audit',
                'آخرین ممیزی ' . intdiv(time() - $last, 86400) . ' روز پیش بوده؛ کرون سلامت در این بازه اجرا نشده است'
            );
        }
    }
    return [
        'key' => 'audit_continuity',
        'title' => 'پیوستگی اجرای ممیزی',
        'findings' => $findings,
        'rationale' => 'اگر کرون سلامت بخوابد، هیچ بررسی‌ای انجام نمی‌شود و سکوت داشبورد به‌اشتباه نشانهٔ سلامت تلقی می‌شود.',
    ];
}

/** ثبت ضربان ممیزی تا قطعی سرویس در اجرای بعدی دیده شود. */
function logging_audit_write_heartbeat(array $summary): bool
{
    $path = logging_audit_heartbeat_path();
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }
    return @file_put_contents($path, json_encode([
        'last_run_at' => date('Y-m-d H:i:s'),
        'findings' => (int) ($summary['findings'] ?? 0),
        'regressions' => (int) ($summary['regressions'] ?? 0),
        'structural' => (int) ($summary['structural'] ?? 0),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n") !== false;
}

/**
 * تعیین اینکه کدام یافته‌ها «ناسازگاری ساختاری» و کدام «پس‌رفت» هستند.
 *
 * عمداً جداگانه نوشته شده تا هم مسیر کرون و هم مسیر خط فرمان از یک منطق
 * استفاده کنند. نسخهٔ قبلی این محاسبه را فقط در مسیر کرون داشت، پس
 * «php tools/system_audit.php --no-db --check» هرگز نمی‌توانست شکست بدهد
 * و مرحلهٔ CI بی‌اثر بود.
 */
function logging_audit_summarize(array $checks, array $baseline): array
{
    $structural = [];
    $regressions = [];
    $failed = [];
    foreach ($checks as $check) {
        $key = (string) $check['key'];
        $metric = (int) ($check['metric'] ?? 0);
        if (!empty($check['failed'])) {
            $failed[] = $key;
            continue;
        }
        if (!empty($check['structural'])) {
            if ($metric > 0) {
                $structural[] = $key;
            }
            continue;
        }
        $base = $baseline[$key] ?? null;
        if ($base !== null && $metric > $base) {
            $regressions[$key] = ['metric' => $metric, 'baseline' => $base];
        }
    }
    return ['structural_problems' => $structural, 'regressions' => $regressions, 'failed_checks' => $failed];
}

/** فهرست همهٔ بررسی‌ها. */
function logging_audit_checks(): array
{
    return [
        'logging_audit_check_db_schema_tables',
        'logging_audit_check_db_schema_columns',
        'logging_audit_check_db_pending_migrations',
        'logging_audit_check_filesystem_writable',
        'logging_audit_check_spool_backlog',
        'logging_audit_check_unresolved_issues',
        'logging_audit_check_worker_heartbeats',
        'logging_audit_check_audit_continuity',
    ];
}

function logging_audit_load_baseline(): array
{
    $path = logging_audit_baseline_path();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) @file_get_contents($path), true);
    return is_array($data['metrics'] ?? null) ? array_map('intval', $data['metrics']) : [];
}

function logging_audit_save_baseline(array $metrics): bool
{
    $path = logging_audit_baseline_path();
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }
    $payload = [
        'توضیح' => 'وضعیت پذیرفته‌شدهٔ همین نصب. ممیزی فقط وقتی هشدار می‌دهد که عددی از این مقادیر بیشتر شود.',
        'updated_at' => date('Y-m-d H:i:s'),
        'metrics' => $metrics,
    ];
    return @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n") !== false;
}

/**
 * اجرای همهٔ بررسی‌ها.
 *
 * هر بررسی داخل try/catch جداست تا خرابی یکی بقیه را از کار نیندازد — ولی
 * برخلاف الگوی مشکل‌ساز قبلی، خطا **بلعیده نمی‌شود**: به‌عنوان یافتهٔ
 * بررسی ثبت و در لاگ گزارش می‌شود.
 */
function logging_audit_run(): array
{
    $started = microtime(true);
    $results = [];
    foreach (logging_audit_checks() as $callable) {
        try {
            $result = $callable();
        } catch (Throwable $exception) {
            $result = [
                'key' => 'audit_internal_error',
                'title' => 'خطای داخلی ممیزی',
                'findings' => [logging_audit_finding($callable, get_class($exception) . ': ' . $exception->getMessage())],
                'rationale' => 'خودِ ممیزی شکست خورد، پس وضعیت این بخش نامعلوم است. سکوت به‌معنای سلامت نیست.',
                'failed' => true,
            ];
        }
        $result['metric'] = count($result['findings']);
        $result['structural'] = in_array($result['key'], logging_audit_structural_checks(), true);
        $results[] = $result;
    }
    return [
        'checks' => $results,
        'duration_ms' => (int) round((microtime(true) - $started) * 1000),
        'metrics' => array_column($results, 'metric', 'key'),
    ];
}

/**
 * اجرای ممیزی و ثبت کامل آن در سیستم لاگ.
 *
 * @param bool  $updateBaseline خط مبنا را با وضعیت فعلی به‌روز کن
 * @param array $alerts         هشدارها به این آرایه اضافه می‌شوند تا
 *                              cron/check_logging_health.php از همان مسیر
 *                              همیشگی به ادمین اعلان بدهد
 */
function logging_audit_run_and_record(bool $updateBaseline = false, array &$alerts = []): array
{
    $run = logging_audit_run();
    $baseline = logging_audit_load_baseline();

    // خط مبنای خالی یعنی هیچ پس‌رفتی هرگز تشخیص داده نمی‌شود — یک بی‌عملیِ
    // خاموش، دقیقاً همان چیزی که این ممیزی برای پیدا کردنش ساخته شده. پس
    // در اولین اجرا خودکار ساخته و صریح اعلام می‌شود.
    $bootstrappedBaseline = false;
    if ($baseline === [] && !$updateBaseline) {
        $bootstrappedBaseline = logging_audit_save_baseline($run['metrics']);
        $baseline = $run['metrics'];
        $alerts['audit_baseline_created'] = 'خط مبنای ممیزی برای این نصب ساخته شد: '
            . implode('، ', array_map(
                static fn($k, $v): string => $k . '=' . $v,
                array_keys($run['metrics']),
                $run['metrics']
            ))
            . '. از این پس فقط بدتر شدن این اعداد هشدار می‌دهد؛ اگر عددی از همین حالا بالاست، آن را بررسی کنید.';
    }

    $regressions = [];
    $structuralProblems = [];

    foreach ($run['checks'] as $check) {
        $key = (string) $check['key'];
        $metric = (int) $check['metric'];
        $samples = array_slice(array_map(
            static fn(array $f): string => $f['where'] . ' — ' . $f['detail'],
            $check['findings']
        ), 0, 5);

        if (!empty($check['structural']) && $metric > 0) {
            // ناسازگاری ساختاری: هرگز بودجه ندارد.
            $structuralProblems[$key] = $check;
            $eventId = log_event(
                'OPS-AUDIT-003',
                $check['title'] . ': ' . $metric . ' مورد — ' . implode(' | ', $samples),
                ['context' => 'audit', 'meta' => ['check' => $key, 'metric' => $metric, 'samples' => $samples]]
            );
            logging_issue_upsert([
                'event_id' => $eventId,
                'code' => 'OPS-AUDIT-003',
                'category' => 'operation',
                'level' => 'critical',
                'context' => 'audit',
                'message' => $check['title'] . ': ' . implode(' | ', $samples),
            ]);
            $alerts['audit_structural_' . $key] = $check['title'] . ' — ' . $metric . ' مورد. ' . $check['rationale'];
            continue;
        }

        if (!empty($check['failed'])) {
            $eventId = log_event(
                'OPS-AUDIT-004',
                $check['title'] . ': ' . implode(' | ', $samples),
                ['context' => 'audit', 'meta' => ['check' => $key, 'samples' => $samples]]
            );
            logging_issue_upsert([
                'event_id' => $eventId,
                'code' => 'OPS-AUDIT-004',
                'category' => 'operation',
                'level' => 'error',
                'context' => 'audit',
                'message' => $check['title'] . ': ' . implode(' | ', $samples),
            ]);
            $alerts['audit_failed_' . $key] = 'ممیزی سلامت در بخش «' . $check['title'] . '» اجرا نشد؛ وضعیت این بخش نامعلوم است.';
            continue;
        }

        $base = $baseline[$key] ?? null;
        if ($base !== null && $metric > $base) {
            $regressions[$key] = ['metric' => $metric, 'baseline' => $base];
            $eventId = log_event(
                'OPS-AUDIT-002',
                $check['title'] . ': از ' . $base . ' به ' . $metric . ' بدتر شد — ' . implode(' | ', $samples),
                ['context' => 'audit', 'meta' => [
                    'check' => $key, 'metric' => $metric, 'baseline' => $base,
                    'delta' => $metric - $base, 'samples' => $samples,
                ]]
            );
            logging_issue_upsert([
                'event_id' => $eventId,
                'code' => 'OPS-AUDIT-002',
                'category' => 'operation',
                'level' => 'warning',
                'context' => 'audit',
                'message' => $check['title'] . ': از ' . $base . ' به ' . $metric . ' بدتر شد',
            ]);
            $alerts['audit_regression_' . $key] = $check['title'] . ' از ' . $base . ' به ' . $metric . ' بدتر شد. ' . $check['rationale'];
        }
    }

    // رخداد پایانی: همیشه ثبت می‌شود، حتی وقتی همه‌چیز سالم است. نبودِ این
    // رخداد در داشبورد یعنی ممیزی اصلاً اجرا نشده — که خودش یک سیگنال است.
    log_event(
        'OPS-AUDIT-001',
        'ممیزی سلامت سامانه اجرا شد: ' . count($run['checks']) . ' بررسی، '
            . array_sum($run['metrics']) . ' یافته، ' . count($regressions) . ' پس‌رفت، '
            . count($structuralProblems) . ' ناسازگاری ساختاری',
        ['context' => 'audit', 'meta' => [
            'checks' => count($run['checks']),
            'findings' => array_sum($run['metrics']),
            'regressions' => count($regressions),
            'duration_ms' => $run['duration_ms'],
            'mode' => $updateBaseline ? 'baseline-update' : 'check',
        ]]
    );

    if ($updateBaseline) {
        logging_audit_save_baseline($run['metrics']);
    }

    $run['regressions'] = $regressions;
    $run['structural_problems'] = array_keys($structuralProblems);
    $run['baseline'] = $baseline;
    $run['baseline_bootstrapped'] = $bootstrappedBaseline;

    // ضربان بعد از همه‌چیز نوشته می‌شود تا اجرای ناقص، قطعی سرویس را پنهان نکند.
    logging_audit_write_heartbeat([
        'findings' => array_sum($run['metrics']),
        'regressions' => count($regressions),
        'structural' => count($structuralProblems),
    ]);

    return $run;
}
