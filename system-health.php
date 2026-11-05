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
    // هیچ DDL در مسیر درخواست وب اجرا نمی‌شود؛ ساختار این جدول در Migrationها و schema.sql مدیریت می‌شود.
    static $checked = false;
    if ($checked) return;
    if (function_exists('db_table_exists') && !db_table_exists('log_issues')) {
        throw new RuntimeException('log_issues table is missing; apply the database migrations.');
    }
    // فقط پس از بررسی موفق cache شود؛ شکست موقت اتصال نباید بررسی بعدی را دور بزند.
    $checked = true;
}

/** فیلتر مشترک Issueها؛ موارد باز/درحال‌بررسی بدون محدودیت سن در دسترس می‌مانند. */
function system_health_issue_filter(int $days, string $severity, string $category, string $status): array
{
    $days = max(1, min(3650, $days));
    $where = [];
    $params = [];
    if (in_array($status, ['open', 'investigating', 'resolved'], true)) {
        $where[] = 'status = ?';
        $params[] = $status;
        if ($status === 'resolved') $where[] = 'last_seen >= DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)';
    } else {
        $where[] = "(status IN ('open','investigating') OR last_seen >= DATE_SUB(NOW(), INTERVAL " . $days . ' DAY))';
    }
    if (in_array($severity, ['warning','error','critical'], true)) { $where[] = 'severity = ?'; $params[] = $severity; }
    if (in_array($category, ['system','security','audit','business','operation','integration','performance'], true)) { $where[] = 'category = ?'; $params[] = $category; }
    return [$where, $params];
}

/** Issueهای باز بدون محدودیت ۹۰ روز؛ خروجی صفحه‌بندی‌شده برای جلوگیری از پنهان‌شدن موارد قدیمی. */
function system_health_issue_rows(int $days = 30, string $severity = '', string $category = '', string $status = '', int $page = 1, int $perPage = 200): array
{
    [$where, $params] = system_health_issue_filter($days, $severity, $category, $status);
    $page = max(1, $page);
    $perPage = max(1, min(5000, $perPage));
    $offset = ($page - 1) * $perPage;
    try {
        $stmt = db()->prepare('SELECT fingerprint, code, category, context, severity, title, first_seen, last_seen, occurrences, last_event_id, last_request_id, status, resolution_note, assigned_to FROM log_issues WHERE ' . implode(' AND ', $where) . " ORDER BY FIELD(status,'open','investigating','resolved'), FIELD(severity,'critical','error','warning'), occurrences DESC, last_seen DESC LIMIT " . $perPage . ' OFFSET ' . $offset);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable) {
        return [];
    }
}

function system_health_issue_total(int $days = 30, string $severity = '', string $category = '', string $status = ''): int
{
    [$where, $params] = system_health_issue_filter($days, $severity, $category, $status);
    try {
        $stmt = db()->prepare('SELECT COUNT(*) FROM log_issues WHERE ' . implode(' AND ', $where));
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

function system_health_issue_history(string $fingerprint, int $page = 1, int $perPage = 50): array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $fingerprint)) return [];
    $page = max(1, $page); $perPage = max(1, min(100, $perPage)); $offset = ($page - 1) * $perPage;
    try {
        $stmt = db()->prepare('SELECT h.*, COALESCE(u.full_name, u.username, CONCAT("کاربر #", h.actor_id)) AS actor_label
            FROM log_issue_history h LEFT JOIN users u ON u.id = h.actor_id
            WHERE h.fingerprint = ? ORDER BY h.id DESC LIMIT ' . $perPage . ' OFFSET ' . $offset);
        $stmt->execute([$fingerprint]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable) {
        return [];
    }
}

function system_health_issue_history_total(string $fingerprint): int
{
    if (!preg_match('/^[a-f0-9]{64}$/', $fingerprint)) return 0;
    try {
        $stmt = db()->prepare('SELECT COUNT(*) FROM log_issue_history WHERE fingerprint = ?');
        $stmt->execute([$fingerprint]);
        return (int) $stmt->fetchColumn();
    } catch (Throwable) { return 0; }
}

function system_health_fingerprint(string $context, string $message, string $code = 'SYS-LEGACY-001'): string
{
    return function_exists('logging_fingerprint_current') ? logging_fingerprint_current($code, $context, $message) : sha1($context . '|' . $message);
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
        'ticket_events' => 'تاریخچهٔ درخواست‌ها',
        'log_issues' => 'مسئله‌های تجمیع‌شده',
        'log_issue_history' => 'تاریخچهٔ رسیدگی به مسئله‌ها',
        'log_issue_events' => 'شناسه‌های تجمیع رخداد',
        'log_issue_notifications' => 'صف تحویل اعلان‌ها',
        'logging_health_alerts' => 'وضعیت هشدارهای سلامت',
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
        'critical' => 'بحرانی — رسیدگی فوری',
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
    $eventCode = (string) ($item['code'] ?? '');
    if ($eventCode !== '') {
        $definition = logging_catalog_resolve($eventCode, (string) ($item['category'] ?? 'system'), (string) ($item['level'] ?? 'error'));
        if (($definition['title'] ?? '') !== 'رخداد سامانه') {
            return [
                'module' => system_health_human_module($context),
                'title' => (string) ($definition['title'] ?? $rawTitle),
                'cause' => (string) ($definition['probable_cause'] ?? 'برای تشخیص علت بررسی فنی لازم است.'),
                'action' => (string) ($definition['recommended_action'] ?? 'رخدادهای هم‌بسته را بررسی کنید.'),
            ];
        }
    }
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
        'cause' => 'علت دقیق از روی اطلاعات قابل‌نمایش مشخص نیست؛ متن خام در گزارش امن پنهان می‌ماند.',
        'action' => 'مرجع فنی این رخداد را به مسئول سامانه بدهید تا جزئیات همان مورد در لاگ داخلی بررسی شود.',
    ];
}

/** مرجع کم‌خطر برای وصل‌کردن ردیف خروجی به رخداد در لاگ داخلی؛ مسیر کامل و متن خام را برنمی‌گرداند. */
function system_health_event_reference(array $item): string
{
    $parts = [];
    $errorType = trim((string) ($item['error_type'] ?? ''));
    $safeType = str_replace('\\', '', $errorType);
    if ($safeType !== '' && preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,79}$/D', $safeType)) {
        $parts[] = 'نوع ' . $safeType;
    }

    $location = strtr(trim((string) ($item['location'] ?? '')), ['\\' => '/']);
    if ($location !== '' && preg_match('~(?:^|/)([A-Za-z0-9_.-]+\.php):(\d+)$~i', $location, $match)) {
        $parts[] = $match[1] . ':' . $match[2];
    }
    if ($parts !== []) {
        return implode(' · ', $parts);
    }

    $fingerprint = strtolower((string) ($item['fingerprint'] ?? ''));
    if (!preg_match('/^(?:[a-f0-9]{40}|[a-f0-9]{64})$/', $fingerprint)) {
        $fingerprint = system_health_fingerprint((string) ($item['context'] ?? ''), (string) ($item['title'] ?? ''), (string) ($item['code'] ?? 'SYS-LEGACY-001'));
    }
    return 'کد ' . strtoupper(substr($fingerprint, 0, 10));
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
    try {
        system_health_ensure();
        system_logs_ensure();
        $row = db()->query("SELECT
            SUM(level IN ('critical','error') AND created_at >= NOW() - INTERVAL 24 HOUR) AS errors_24h,
            SUM(level = 'warning' AND created_at >= NOW() - INTERVAL 24 HOUR) AS warnings_24h,
            SUM(context = 'خطای قاتل PHP' AND created_at >= NOW() - INTERVAL 1 HOUR) AS fatal_1h,
            SUM(context = 'کندی صفحه' AND created_at >= NOW() - INTERVAL 24 HOUR) AS slow_pages_24h,
            SUM(context = 'کندی کوئری' AND created_at >= NOW() - INTERVAL 24 HOUR) AS slow_queries_24h
            FROM system_logs WHERE created_at >= NOW() - INTERVAL 24 HOUR")->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $exception) {
        logging_emergency_failure('system_health_summary_unavailable', get_class($exception));
        return [
            'errors_24h' => null, 'warnings_24h' => null, 'fatal_1h' => null,
            'slow_pages_24h' => null, 'slow_queries_24h' => null,
            'state' => 'unknown', 'state_label' => 'نامشخص',
            'plain_message' => 'به دلیل در دسترس نبودن پایگاه‌داده، آمار رخدادها قابل محاسبه نیست؛ این وضعیت به معنی صفر بودن خطاها نیست.',
            'next_step' => 'ابتدا اتصال و سرویس پایگاه‌داده را بررسی کنید؛ پس از بازیابی، صف اضطراری Logging را بازپخش کنید.',
        ];
    }
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
    $sql = "SELECT g.context, g.message, g.level, g.cnt, g.first_at, g.last_at, s.code, s.category, s.meta_json, s.request_uri, s.request_id,
            i.fingerprint AS issue_fingerprint, COALESCE(i.status, 'open') AS status, i.resolution_note AS note
        FROM (
            SELECT context, message, level, COUNT(*) AS cnt, MIN(created_at) AS first_at, MAX(created_at) AS last_at, MAX(id) AS last_id
            FROM system_logs
            WHERE created_at >= NOW() - INTERVAL ? DAY AND level = ?
            GROUP BY context, message, level
        ) g
        JOIN system_logs s ON s.id = g.last_id
        LEFT JOIN log_issues i ON i.code = COALESCE(s.code, 'SYS-LEGACY-001') AND i.context = s.context AND i.title = LEFT(s.message, 255)
        ORDER BY g.cnt DESC, g.last_at DESC
        LIMIT 100";
    $items = [];
    try {
    foreach (['critical', 'error', 'warning'] as $level) {
        $stmt = db()->prepare($sql);
        $stmt->execute([$days, $level]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $meta = json_decode((string) ($row['meta_json'] ?? ''), true);
            $meta = is_array($meta) ? $meta : [];
            $items[] = [
                'fingerprint' => (string) ($row['issue_fingerprint'] ?? ''),
                'issue_fingerprint' => (string) ($row['issue_fingerprint'] ?? ''),
                'context' => (string) $row['context'],
                'title' => (string) $row['message'],
                'level' => (string) $row['level'],
                'code' => (string) ($row['code'] ?? 'SYS-LEGACY-001'),
                'category' => (string) ($row['category'] ?? 'system'),
                'count' => (int) $row['cnt'],
                'first_at' => (string) $row['first_at'],
                'last_at' => (string) $row['last_at'],
                'cause' => (string) ($meta['cause'] ?? ''),
                'action' => (string) ($meta['action'] ?? ''),
                'technical' => (string) ($meta['technical'] ?? ''),
                'location' => (string) ($meta['location'] ?? ''),
                'error_type' => (string) ($meta['error_type'] ?? ''),
                'request_uri' => (string) ($row['request_uri'] ?? ''),
                'request_id' => (string) ($row['request_id'] ?? ''),
                'status' => (string) $row['status'],
                'note' => (string) ($row['note'] ?? ''),
            ];
        }
    }
    usort($items, static function (array $a, array $b): int {
        $rankA = in_array(($a['level'] ?? ''), ['critical', 'error'], true) ? 0 : 1;
        $rankB = in_array(($b['level'] ?? ''), ['critical', 'error'], true) ? 0 : 1;
        return ($rankA <=> $rankB)
            ?: ((int) ($b['count'] ?? 0) <=> (int) ($a['count'] ?? 0))
            ?: strcmp((string) ($b['last_at'] ?? ''), (string) ($a['last_at'] ?? ''));
    });
    return $items;
    } catch (Throwable $exception) {
        logging_emergency_failure('system_health_groups_failed', get_class($exception));
        return [];
    }
}

/** شمارش کامل رخدادها و گروه‌ها؛ مستقل از سقف گروه‌های نمایشی در صفحه/Excel. */
function system_health_event_totals(int $days = 7): array
{
    system_logs_ensure();
    $days = max(1, min(90, $days));
    $totalsStmt = db()->prepare("SELECT
        SUM(level IN ('critical','error')) AS errors,
        SUM(level = 'warning') AS warnings
        FROM system_logs
        WHERE created_at >= NOW() - INTERVAL ? DAY AND level IN ('critical','error','warning')");
    $totalsStmt->execute([$days]);
    $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $groupsStmt = db()->prepare("SELECT COUNT(*) FROM (
        SELECT context, message, level
        FROM system_logs
        WHERE created_at >= NOW() - INTERVAL ? DAY AND level IN ('critical','error','warning')
        GROUP BY context, message, level
    ) AS grouped_events");
    $groupsStmt->execute([$days]);
    return [
        'errors' => (int) ($totals['errors'] ?? 0),
        'warnings' => (int) ($totals['warnings'] ?? 0),
        'groups' => (int) $groupsStmt->fetchColumn(),
    ];
}

/** بررسی‌های خودکار سلامت. هر مورد: [عنوان، وضعیت ok|warn|fail، توضیح]. */
function system_health_checks(): array
{
    $checks = [];
    $dbAvailable = true;
    try {
        db()->query('SELECT 1')->fetchColumn();
        $checks[] = ['اتصال به دیتابیس', 'ok', 'اتصال برقرار است.'];
    } catch (Throwable) {
        $dbAvailable = false;
        $checks[] = ['اتصال به دیتابیس', 'fail', 'اتصال برقرار نیست؛ تنظیمات دیتابیس و سرویس MariaDB را بررسی کنید.'];
    }

    if (!$dbAvailable) {
        $free = @disk_free_space(__DIR__);
        $total = @disk_total_space(__DIR__);
        $checks[] = ($free !== false && $total !== false && $total > 0)
            ? ['فضای خالی دیسک', ($free / $total) < 0.10 ? 'fail' : (($free / $total) < 0.20 ? 'warn' : 'ok'), round(($free / $total) * 100, 1) . '٪ فضای دیسک آزاد است.']
            : ['فضای خالی دیسک', 'warn', 'اندازه‌گیری فضای دیسک ممکن نشد.'];
        foreach (['uploads', 'storage'] as $dir) {
            $path = __DIR__ . '/' . $dir;
            $checks[] = is_dir($path) && is_writable($path)
                ? ['پوشهٔ ' . $dir, 'ok', 'قابل نوشتن است.']
                : ['پوشهٔ ' . $dir, 'fail', 'پوشه وجود ندارد یا قابل نوشتن نیست.'];
        }
        $counters = logging_health_counters();
        $checks[] = in_array(($counters['state'] ?? 'unknown'), ['known', 'not_initialized'], true)
            ? ['شمارندهٔ سلامت Logging', ($counters['state'] ?? '') === 'known' ? 'ok' : 'warn', 'وضعیت شمارنده: ' . (string) ($counters['state'] ?? 'unknown') . '؛ ازدست‌رفتهٔ تاریخی: ' . number_format((int) ($counters['dropped_total'] ?? 0))]
            : ['شمارندهٔ سلامت Logging', 'fail', 'فایل شمارنده قابل خواندن یا معتبر نیست؛ مقدار صفر فرض نمی‌شود.'];
        $spool = logging_spool_stats();
        $checks[] = ($spool['state'] ?? 'unknown') === 'unknown'
            ? ['صف اضطراری لاگ', 'fail', 'وضعیت صف نامشخص است؛ فایل‌های JSONL و مجوزهای Worker بررسی شوند.']
            : ['صف اضطراری لاگ', ((int) ($spool['age_seconds'] ?? 0) > 1800) ? 'fail' : (((int) ($spool['age_seconds'] ?? 0) > 600) ? 'warn' : 'ok'), number_format((int) ($spool['files'] ?? 0)) . ' فایل در صف؛ ' . number_format((int) ($spool['dead_letter_files'] ?? 0)) . ' فایل در Dead Letter.'];
        $deadLetterFiles = (int) ($spool['dead_letter_files'] ?? 0);
    $checks[] = $deadLetterFiles > 0
        ? ['رخدادهای منتقل‌شده به Dead Letter', 'fail', number_format($deadLetterFiles) . ' فایل (' . round(((int) ($spool['dead_letter_bytes'] ?? 0)) / 1048576, 2) . ' مگابایت) نیازمند بررسی و بازیابی دستی است.']
        : ['رخدادهای منتقل‌شده به Dead Letter', 'ok', 'فایل Dead Letter وجود ندارد.'];
    $rewriteTemps = (int) ($spool['rewrite_temp_files'] ?? 0);
    $checks[] = $rewriteTemps > 0
        ? ['فایل‌های موقت بازنویسی صف', 'warn', number_format($rewriteTemps) . ' فایل موقت باقی مانده؛ پس از بررسی Worker پاک‌سازی شود.']
        : ['فایل‌های موقت بازنویسی صف', 'ok', 'فایل موقت بازنویسی باقی نمانده است.'];

    $workerPath = APP_ROOT . '/storage/logs/logging-worker-status.json';
        $worker = is_file($workerPath) ? json_decode((string) @file_get_contents($workerPath), true) : [];
        $workerAt = is_array($worker) ? strtotime((string) ($worker['last_attempt_at'] ?? '')) : false;
        $checks[] = $workerAt === false
            ? ['آخرین اجرای Worker', 'warn', 'اطلاعات اجرای Worker موجود نیست.']
            : ['آخرین اجرای Worker', (time() - $workerAt) > 900 && (int) ($spool['files'] ?? 0) > 0 ? 'warn' : 'ok', 'آخرین تلاش: ' . persian_date(date('Y-m-d H:i:s', $workerAt))];
        $checks[] = ['پایگاه‌داده و آمارهای وابسته', 'warn', 'به علت قطع اتصال، بررسی جدول‌ها، Issueها، اعلان‌ها و آمار رخدادها انجام نشد.'];
        $checks[] = ['نسخهٔ سامانه', 'ok', 'نسخهٔ فعلی: ' . trim((string) @file_get_contents(__DIR__ . '/VERSION'))];
        return $checks;
    }

    $required = ['users', 'tickets', 'traffic_visits', 'food_orders', 'food_calendar', 'food_guest_requests', 'food_ticket_events', 'system_logs', 'activity_logs', 'ticket_events', 'log_issues', 'logging_health_alerts', 'log_issue_history', 'log_issue_events', 'log_issue_notifications'];
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
    $loggingColumns = [
        'system_logs' => ['event_id', 'request_id', 'trace_id', 'operation_id', 'parent_event_id', 'code', 'category', 'fingerprint'],
        'activity_logs' => ['event_id', 'request_id', 'trace_id', 'operation_id', 'parent_event_id', 'result', 'code', 'category'],
        'ticket_events' => ['event_id', 'request_id', 'trace_id', 'operation_id', 'parent_event_id'],
    ];
    $existingLoggingColumns = [];
    $columnRows = db()->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('system_logs','activity_logs','ticket_events')")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columnRows as $columnRow) {
        $existingLoggingColumns[(string) $columnRow['TABLE_NAME']][(string) $columnRow['COLUMN_NAME']] = true;
    }
    $missingLoggingColumns = [];
    foreach ($loggingColumns as $table => $columns) {
        foreach ($columns as $column) {
            if (empty($existingLoggingColumns[$table][$column])) {
                $missingLoggingColumns[] = $table . '.' . $column;
            }
        }
    }
    $checks[] = $missingLoggingColumns === []
        ? ['ساختار هم‌بستگی Logging', 'ok', 'ستون‌های Request ID، Trace ID، Operation ID و Parent Event ID در هر سه جدول آماده‌اند.']
        : ['ساختار هم‌بستگی Logging', 'fail', 'مهاجرت 022 اجرا نشده یا ناقص است: ' . implode('، ', $missingLoggingColumns)];
    $issueTablesReady = db_table_exists('log_issues') && db_table_exists('logging_health_alerts')
        && db_table_exists('log_issue_history') && db_table_exists('log_issue_events') && db_table_exists('log_issue_notifications');
    $checks[] = $issueTablesReady
        ? ['تجمیع، تاریخچه و تحویل اعلان Logging', 'ok', 'مرجع Issue، تاریخچهٔ before/after، Deduplication و صف اعلان آماده‌اند.']
        : ['تجمیع، تاریخچه و تحویل اعلان Logging', 'fail', 'مهاجرت‌های 020 و 021 کامل اجرا نشده‌اند؛ جدول‌های log_issues، log_issue_history، log_issue_events و log_issue_notifications لازم‌اند.'];

    $free = @disk_free_space(__DIR__);
    $total = @disk_total_space(__DIR__);
    if ($free === false || $total === false || $total <= 0) {
        $checks[] = ['فضای خالی دیسک', 'warn', 'اندازه‌گیری فضای دیسک ممکن نشد.'];
    } else {
        $percent = ($free / $total) * 100;
        $checks[] = $percent < 10
            ? ['فضای خالی دیسک', 'fail', 'فقط ' . round($percent, 1) . '٪ فضای دیسک آزاد است؛ فوراً فضای ذخیره‌سازی را بررسی کنید.']
            : ($percent < 20 ? ['فضای خالی دیسک', 'warn', round($percent, 1) . '٪ فضای دیسک آزاد است؛ آستانهٔ هشدار کمتر از ۲۰٪ است.'] : ['فضای خالی دیسک', 'ok', round($percent, 1) . '٪ فضای دیسک آزاد است.']);
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

    // ── ممیزی سلامت سامانه ──────────────────────────────────────────────
    // ممیزی در کرون هر دقیقه اجرا می‌شود. اینجا فقط *گزارش* می‌شود تا مدیر
    // بدون ورود به سرور بفهمد واقعاً اجرا شده یا نه. اگر این دو ردیف را
    // نگذاریم، سکوت صفحه به‌اشتباه نشانهٔ سلامت تلقی می‌شود — همان تله‌ای
    // که این ممیزی برای گرفتنش ساخته شده.
    try {
        require_once __DIR__ . '/lib/logging-audit.php';

        $heartbeat = logging_audit_heartbeat_path();
        if (!is_file($heartbeat)) {
            $checks[] = ['ممیزی سلامت سامانه', 'warn',
                'هنوز هیچ اجرایی ثبت نشده است. ممیزی بخشی از کرون «بررسی سلامت لاگ» است؛ '
                . 'اگر تا چند دقیقه پس از به‌روزرسانی همچنان این پیام را می‌بینید، '
                . 'Scheduled Task مربوط به cron\\run_logging_maintenance.bat اجرا نمی‌شود.'];
        } else {
            $hb = json_decode((string) @file_get_contents($heartbeat), true);
            $lastAt = is_array($hb) ? (string) ($hb['last_run_at'] ?? '') : '';
            $lastTs = $lastAt !== '' ? strtotime($lastAt) : false;
            $findings = is_array($hb) ? (int) ($hb['findings'] ?? 0) : 0;
            $structural = is_array($hb) ? (int) ($hb['structural'] ?? 0) : 0;
            $regressions = is_array($hb) ? (int) ($hb['regressions'] ?? 0) : 0;
            if ($lastTs === false) {
                $checks[] = ['ممیزی سلامت سامانه', 'warn',
                    'فایل ضربان ممیزی خوانا نیست؛ زمان آخرین اجرا نامشخص است.'];
            } elseif ($lastTs < time() - 86400) {
                $checks[] = ['ممیزی سلامت سامانه', 'fail',
                    'آخرین اجرا ' . persian_date(date('Y-m-d H:i:s', $lastTs))
                    . ' بوده و بیش از یک شبانه‌روز گذشته است؛ کرون سلامت اجرا نمی‌شود.'];
            } elseif ($structural > 0) {
                $checks[] = ['ممیزی سلامت سامانه', 'fail',
                    $structural . ' ناسازگاری ساختاری یافت شد (جدول یا ستون گم‌شده، مهاجرت اجرانشده یا '
                    . 'پوشهٔ غیرقابل نوشتن). جزئیات در لاگ سامانه با کد OPS-AUDIT-003.'];
            } elseif ($regressions > 0) {
                $checks[] = ['ممیزی سلامت سامانه', 'warn',
                    $regressions . ' مورد نسبت به خط مبنا بدتر شده است؛ کد OPS-AUDIT-002 در لاگ سامانه.'];
            } else {
                $checks[] = ['ممیزی سلامت سامانه', 'ok',
                    'آخرین اجرا: ' . persian_date(date('Y-m-d H:i:s', $lastTs))
                    . ' — بدون ناسازگاری ساختاری و بدون پس‌رفت (' . number_format($findings) . ' یافتهٔ رفتاری).'];
            }
        }

        $baselinePath = logging_audit_baseline_path();
        $checks[] = is_file($baselinePath)
            ? ['خط مبنای ممیزی', 'ok',
                'ساخته شده است؛ از این پس فقط بدتر شدن اعداد هشدار می‌دهد. '
                . 'مسیر: storage/logs/' . basename($baselinePath)]
            : ['خط مبنای ممیزی', 'warn',
                'هنوز ساخته نشده است. اولین اجرای ممیزی آن را خودکار می‌سازد و در لاگ اعلام می‌کند؛ '
                . 'نیازی به اجرای دستی روی سرور نیست.'];
    } catch (Throwable $auditProbe) {
        $checks[] = ['ممیزی سلامت سامانه', 'fail',
            'وضعیت ممیزی خوانده نشد: ' . $auditProbe->getMessage()
            . ' — این خودش یک مشکل است و نباید نادیده گرفته شود.'];
    }

    $loggingCounters = logging_health_counters();
    $droppedRecent = !empty($loggingCounters['dropped_last_at']) && strtotime((string) $loggingCounters['dropped_last_at']) >= time() - 86400;
    if (in_array(($loggingCounters['state'] ?? 'unknown'), ['unreadable', 'corrupt'], true)) {
        $checks[] = ['شمارندهٔ سلامت Logging', 'fail', 'فایل شمارنده قابل خواندن یا معتبر نیست؛ مقدار صفر فرض نشده است. فایل logging-health.json و مجوزهای storage/logs بررسی شوند.'];
    } elseif (($loggingCounters['state'] ?? '') === 'not_initialized') {
        $checks[] = ['شمارندهٔ سلامت Logging', 'warn', 'فایل شمارنده هنوز ساخته نشده است؛ وضعیت شمارنده‌ها نامشخص است و صفر قطعی تلقی نمی‌شود.'];
    }
    $checks[] = $droppedRecent
        ? ['رخدادهای ازدست‌رفتهٔ لاگ', 'fail', 'در ۲۴ ساعت اخیر حداقل یک رخداد پس از شکست دیتابیس و صف اضطراری نیز از دست رفته است. مجوزها، دیسک و فایل‌های خطای PHP را فوراً بررسی کنید.']
        : (($loggingCounters['state'] ?? '') === 'known'
            ? ['رخدادهای ازدست‌رفتهٔ لاگ', 'ok', 'در ۲۴ ساعت اخیر شکست نهایی جدیدی ثبت نشده است؛ مجموع تاریخی: ' . number_format((int) $loggingCounters['dropped_total']) . '.']
            : ['رخدادهای ازدست‌رفتهٔ لاگ', 'warn', 'به دلیل نامشخص بودن شمارنده، نمی‌توان نبود رخداد ازدست‌رفته را تأیید کرد.']);

    $spool = logging_spool_stats();
    if (($spool['state'] ?? 'unknown') === 'unknown') {
        $checks[] = ['صف اضطراری لاگ', 'fail', 'حداقل بخشی از فایل‌های صف قابل خواندن یا تحلیل نیستند؛ شمار فایل‌های پردازش‌شده و سن صف قطعی نیست. فایل‌های JSONL و دسترسی Worker بررسی شوند.'];
    } elseif ($spool['files'] === 0) {
        $checks[] = ['صف اضطراری لاگ', 'ok', 'صف خالی است؛ ثبت مستقیم در دیتابیس انجام می‌شود.'];
    } else {
        $ageMinutes = (int) ceil($spool['age_seconds'] / 60);
        $state = $ageMinutes > 30 ? 'fail' : ($ageMinutes > 10 ? 'warn' : 'ok');
        $checks[] = ['صف اضطراری لاگ', $state, number_format($spool['files']) . ' فایل، ' . round($spool['bytes'] / 1048576, 2) . ' مگابایت؛ قدیمی‌ترین رخداد حدود ' . $ageMinutes . ' دقیقه در صف است. آستانهٔ هشدار ۱۰ و بحرانی ۳۰ دقیقه است.'];
        if ($spool['bytes'] > 100 * 1024 * 1024) $checks[] = ['حجم صف اضطراری', 'fail', 'حجم صف از ۱۰۰ مگابایت بیشتر شده است؛ سلامت دیتابیس و Worker را بررسی کنید.'];
    }

    $deadLetterFiles = (int) ($spool['dead_letter_files'] ?? 0);
    $checks[] = $deadLetterFiles > 0
        ? ['رخدادهای منتقل‌شده به Dead Letter', 'fail', number_format($deadLetterFiles) . ' فایل (' . round(((int) ($spool['dead_letter_bytes'] ?? 0)) / 1048576, 2) . ' مگابایت) نیازمند بررسی و بازیابی دستی است.']
        : ['رخدادهای منتقل‌شده به Dead Letter', 'ok', 'فایل Dead Letter وجود ندارد.'];
    $rewriteTemps = (int) ($spool['rewrite_temp_files'] ?? 0);
    $checks[] = $rewriteTemps > 0
        ? ['فایل‌های موقت بازنویسی صف', 'warn', number_format($rewriteTemps) . ' فایل موقت باقی مانده؛ پس از بررسی Worker پاک‌سازی شود.']
        : ['فایل‌های موقت بازنویسی صف', 'ok', 'فایل موقت بازنویسی باقی نمانده است.'];

    $workerPath = APP_ROOT . '/storage/logs/logging-worker-status.json';
    $worker = is_file($workerPath) ? json_decode((string) @file_get_contents($workerPath), true) : [];
    $lastSuccess = is_array($worker) && !empty($worker['last_success_at']) ? strtotime((string) $worker['last_success_at']) : false;
    if ($spool['files'] > 0) {
        $workerAge = $lastSuccess === false ? null : max(0, time() - $lastSuccess);
        $checks[] = $workerAge === null || $workerAge > 900
            ? ['آخرین بازپخش موفق صف', 'warn', 'در ۱۵ دقیقهٔ اخیر بازپخش موفق ثبت نشده است؛ زمان‌بندی Worker و گزارش اجرای آن را بررسی کنید.']
            : ['آخرین بازپخش موفق صف', 'ok', 'آخرین اجرای موفق ' . (int) ceil($workerAge / 60) . ' دقیقه پیش ثبت شده است.'];
    } else {
        $checks[] = ['آخرین بازپخش موفق صف', 'ok', 'صف خالی است؛ بازپخش معوقی وجود ندارد.'];
    }

    try {
        $dbErrors = (int) db()->query("SELECT COUNT(*) FROM system_logs WHERE code = 'SYS-DB-001' AND created_at >= NOW() - INTERVAL 24 HOUR")->fetchColumn();
        $checks[] = $dbErrors > 0
            ? ['خطاهای پایگاه داده در ۲۴ ساعت', $dbErrors >= 5 ? 'fail' : 'warn', number_format($dbErrors) . ' خطای پایگاه داده ثبت شده است؛ لاگ MariaDB و عملیات ناموفق بررسی شود.']
            : ['خطاهای پایگاه داده در ۲۴ ساعت', 'ok', 'خطای پایگاه داده با کد مشخص در ۲۴ ساعت اخیر ثبت نشده است.'];
    } catch (Throwable) {
        $checks[] = ['خطاهای پایگاه داده در ۲۴ ساعت', 'warn', 'آمار خطاهای دیتابیس قابل خواندن نیست.'];
    }

    try {
        $tableMb = (float) db()->query("SELECT COALESCE((DATA_LENGTH + INDEX_LENGTH) / 1048576, 0) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs'")->fetchColumn();
        $checks[] = $tableMb >= 5120
            ? ['حجم جدول لاگ فنی', 'fail', round($tableMb, 1) . ' مگابایت؛ از آستانهٔ ۵ گیگابایت عبور کرده است.']
            : ($tableMb >= 1024 ? ['حجم جدول لاگ فنی', 'warn', round($tableMb, 1) . ' مگابایت؛ آستانهٔ هشدار ۱ گیگابایت است.'] : ['حجم جدول لاگ فنی', 'ok', round($tableMb, 1) . ' مگابایت.']);
    } catch (Throwable) {
        $checks[] = ['حجم جدول لاگ فنی', 'warn', 'اندازهٔ جدول لاگ فنی قابل خواندن نیست.'];
    }

    try {
        $notificationStats = db()->query("SELECT SUM(state IN ('pending','sending')) AS pending_count, SUM(state = 'failed') AS failed_count, MIN(CASE WHEN state IN ('pending','sending') THEN next_attempt_at END) AS oldest_pending FROM log_issue_notifications")->fetch(PDO::FETCH_ASSOC) ?: [];
        $pendingNotifications = (int) ($notificationStats['pending_count'] ?? 0);
        $failedNotifications = (int) ($notificationStats['failed_count'] ?? 0);
        $oldestPendingAt = !empty($notificationStats['oldest_pending']) ? strtotime((string) $notificationStats['oldest_pending']) : false;
        $pendingAge = $oldestPendingAt === false ? 0 : max(0, time() - $oldestPendingAt);
        $notificationState = $failedNotifications > 0 ? 'fail' : (($pendingNotifications > 0 && $pendingAge > 900) ? 'warn' : 'ok');
        $checks[] = ['صف تحویل اعلان‌های Issue', $notificationState, number_format($pendingNotifications) . ' در انتظار/ارسال، ' . number_format($failedNotifications) . ' ناموفق؛ ' . ($pendingNotifications > 0 ? 'قدیمی‌ترین موعد: ' . (int) ceil($pendingAge / 60) . ' دقیقه قبل.' : 'صف معوق ندارد.')];
    } catch (Throwable) {
        $checks[] = ['صف تحویل اعلان‌های Issue', 'warn', 'آمار صف اعلان‌ها قابل خواندن نیست؛ اجرای Migrationهای Logging و دسترسی دیتابیس بررسی شود.'];
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
    require_permission('system.health_edit');
    require_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if (in_array($action, ['issue_status', 'incident_status'], true) && !user_can($user, 'logs.view')) {
        security_log_access_denied($user, 'logs.view', 'system-health-issue-action');
        http_response_code(403);
        exit('دسترسی به مدیریت رخدادهای فنی مجاز نیست.');
    }
    if ($action === 'issue_status') {
        $fingerprint = strtolower(trim((string) ($_POST['fingerprint'] ?? '')));
        $status = (string) ($_POST['status'] ?? '');
        $note = logging_sanitize_text(trim((string) ($_POST['note'] ?? '')), 1000);
        if (!preg_match('/^[a-f0-9]{64}$/', $fingerprint) || !array_key_exists($status, SYSTEM_HEALTH_STATUSES)) {
            flash('danger', 'اطلاعات وضعیت مسئله معتبر نیست.');
        } else {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $beforeQuery = $pdo->prepare('SELECT status, assigned_to, resolution_note FROM log_issues WHERE fingerprint = ? FOR UPDATE');
                $beforeQuery->execute([$fingerprint]);
                $before = $beforeQuery->fetch(PDO::FETCH_ASSOC);
                if (!$before) {
                    $pdo->rollBack();
                    flash('warning', 'مسئله پیدا نشد.');
                } else {
                    $newNote = $note !== '' ? $note : null;
                    $newAssignee = (int) $user['id'];
                    $changed = (string) $before['status'] !== $status
                        || (int) ($before['assigned_to'] ?? 0) !== $newAssignee
                        || (string) ($before['resolution_note'] ?? '') !== (string) ($newNote ?? '');
                    if ($changed) {
                        $pdo->prepare('UPDATE log_issues SET status = ?, resolution_note = ?, assigned_to = ?, updated_at = NOW() WHERE fingerprint = ?')
                            ->execute([$status, $newNote, $newAssignee, $fingerprint]);
                        $pdo->prepare('INSERT INTO log_issue_history (fingerprint, actor_id, request_id, before_status, after_status, before_assigned_to, after_assigned_to, before_note, after_note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                            ->execute([$fingerprint, (int) $user['id'], logging_request_id(), (string) $before['status'], $status,
                                $before['assigned_to'] !== null ? (int) $before['assigned_to'] : null, $newAssignee,
                                $before['resolution_note'], $newNote]);
                        // ثبت حسابرسی در همان تراکنش تغییر وضعیت؛ شکست Audit باعث rollback تغییر می‌شود.
                        save_audit_required((int) $user['id'], 'system_incident_status', null, ['issue_fingerprint' => $fingerprint, 'status' => $status]);
                    }
                    $pdo->commit();
                    flash('success', $changed ? 'وضعیت و تاریخچهٔ رسیدگی ثبت شد: ' . SYSTEM_HEALTH_STATUSES[$status] . '.' : 'وضعیت مسئله بدون تغییر باقی ماند.');
                }
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $exception;
            }
        }
    }
    if ($action === 'incident_status') {
        http_response_code(410);
        exit('مدیریت رخدادهای قدیمی متوقف شده است؛ از Issue Management استفاده کنید.');
    }
    if ($action === 'cache_purge') {
        if (empty($_POST['confirm_purge'])) {
            flash('danger', 'برای پاک‌سازی فایل‌های موقت باید تأیید را علامت بزنید.');
        } else {
            save_audit_required((int) $user['id'], 'maintenance_cache_purged', null, ['requested' => true], 'unknown');
            $result = sys_maint_cache_purge();
            save_audit_required((int) $user['id'], 'maintenance_cache_purged', null, $result);
            flash('success', 'فایل‌های موقت پاک شدند: ' . $result['deleted'] . ' فایل، ' . round($result['bytes'] / 1048576, 1) . ' مگابایت (ناموفق: ' . $result['failed'] . ').');
        }
    }
    if ($action === 'analyze_tables') {
        save_audit_required((int) $user['id'], 'maintenance_tables_analyzed', null, ['requested' => true], 'unknown');
        $result = sys_maint_analyze_tables();
        save_audit_required((int) $user['id'], 'maintenance_tables_analyzed', null, ['done' => count($result['done']), 'failed' => $result['failed']]);
        flash('success', 'تحلیل آمار جدول‌ها: ' . count($result['done']) . ' جدول انجام شد' . ($result['failed'] ? '، ناموفق: ' . implode('، ', $result['failed']) : '') . '.');
    }
    if ($action === 'prune_system_logs') {
        $days = max(90, min(3650, (int) ($_POST['retention_days'] ?? 90)));
        save_audit_required((int) $user['id'], 'system_logs_pruned', null, ['days' => $days], 'unknown');
        $deleted = system_logs_prune($days);
        save_audit_required((int) $user['id'], 'system_logs_pruned', null, ['days' => $days, 'deleted' => $deleted]);
        flash('success', 'پاک‌سازی طبق سیاست نگهداری انجام شد؛ رخدادهای عادی با بازهٔ ' . max(90, min(3650, $days)) . ' روز برای خطاهای فنی؛ Debug هفت روز، Info/Notice سی روز، کسب‌وکار ۶۰ روز و امنیت/حسابرسی/عملیات حساس حداقل ۱۸۳ روز نگهداری شدند. تعداد حذف‌شده: ' . $deleted . ' مورد.');
    }
    redirect('index.php?page=system-health');
}

/** متنِ قابل‌صدور را برای اشتراک امن با پشتیبانی پالایش می‌کند. */
function system_health_export_safe_text(string $value, array $knownUserLabels = []): string
{
    foreach ($knownUserLabels as $label) {
        $label = trim((string) $label);
        if (logging_utf8_strlen($label) >= 2) {
            $value = str_ireplace($label, '[کاربر]', $value);
        }
    }
    $value = (string) preg_replace('/\b[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}\b/u', '[ایمیل]', $value);
    $value = (string) preg_replace('/(?<![0-9۰-۹٠-٩])[0-9۰-۹٠-٩]{10}(?![0-9۰-۹٠-٩])/u', '[شناسه]', $value);
    $value = (string) preg_replace('/(?<![0-9۰-۹٠-٩])(?:\\+?98|0098|0)?[9۹٩][0-9۰-۹٠-٩]{9}(?![0-9۰-۹٠-٩])/u', '[شماره تماس]', $value);
    $value = (string) preg_replace('/\b(?:\d{1,3}\.){3}\d{1,3}\b/u', '[IP]', $value);
    $value = (string) preg_replace('~https?://[^[:space:]]+~iu', '[نشانی وب]', $value);
    $value = (string) preg_replace('/(?i)(password|pass|pwd|token|secret|authorization|cookie|csrf|api[_-]?key|session|national_code|user_id|employee_id|employee_number|staff_id)\s*"?\s*[:=]\s*"?[^",\s;}]+/u', '$1=[مخفی]', $value);
    return logging_utf8_substr($value, 0, 1200);
}

/** خروجی تخصصی محدود به logs.view؛ اطلاعات راز و شناسه‌های شخصی پیش از ساخت Excel ماسک می‌شوند. */
function system_health_export_technical_xlsx(array $user): never
{
    if (!user_can($user, 'logs.view')) { security_log_access_denied($user, 'logs.view', 'system-health-technical-export'); http_response_code(403); exit('دسترسی به خروجی فنی مجاز نیست.'); }
    $knownUserLabels = [];
    try {
        $knownUserLabels = db()->query("SELECT full_name FROM users WHERE full_name IS NOT NULL AND full_name <> '' UNION SELECT username FROM users WHERE username IS NOT NULL AND username <> ''")->fetchAll(PDO::FETCH_COLUMN);
        usort($knownUserLabels, static fn ($a, $b): int => logging_utf8_strlen((string) $b) <=> logging_utf8_strlen((string) $a));
    } catch (Throwable) {}
    $safe = static fn (string $value): string => system_health_export_safe_text($value, $knownUserLabels);
    $stmt = db()->prepare("SELECT event_id, request_id, code, category, level, context, message, meta_json, created_at FROM system_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY id DESC LIMIT 5000");
    $stmt->execute();
    $eventRows = [
        [['v'=>'گزارش فنی Logging — ۳۰ روز اخیر','s'=>'title']],
        array_map(static fn (string $label): array => ['v'=>$label,'s'=>'header'], ['Event ID','Request ID','کد رخداد','دسته','شدت','بخش','پیام پالایش‌شده','نوع خطا','محل امن','علت','اقدام','جزئیات فنی پالایش‌شده','زمان']),
    ];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $meta = json_decode((string) ($row['meta_json'] ?? ''), true);
        $meta = is_array($meta) ? $meta : [];
        $eventRows[] = [
            ['v'=>(string) ($row['event_id'] ?? ''),'s'=>'body'],
            ['v'=>(string) ($row['request_id'] ?? ''),'s'=>'body'],
            ['v'=>(string) ($row['code'] ?? ''),'s'=>'body'],
            ['v'=>(string) ($row['category'] ?? ''),'s'=>'body'],
            ['v'=>(string) ($row['level'] ?? ''),'s'=>'body'],
            ['v'=>$safe((string) ($row['context'] ?? '')),'s'=>'body'],
            ['v'=>$safe((string) ($row['message'] ?? '')),'s'=>'body'],
            ['v'=>$safe((string) ($meta['error_type'] ?? '')),'s'=>'body'],
            ['v'=>$safe((string) ($meta['location'] ?? '')),'s'=>'body'],
            ['v'=>$safe((string) ($meta['cause'] ?? '')),'s'=>'body'],
            ['v'=>$safe((string) ($meta['action'] ?? '')),'s'=>'body'],
            ['v'=>$safe((string) ($meta['technical'] ?? '')),'s'=>'body'],
            ['v'=>(string) ($row['created_at'] ?? ''),'s'=>'body'],
        ];
    }
    if (count($eventRows) === 2) $eventRows[] = [['v'=>'در ۳۰ روز اخیر رخداد فنی ثبت نشده است.','s'=>'body']];

    $issueRows = [
        [['v'=>'وضعیت Issueهای تجمیع‌شده','s'=>'title']],
        array_map(static fn (string $label): array => ['v'=>$label,'s'=>'header'], ['Fingerprint','کد','دسته','شدت','عنوان','تعداد','اولین مشاهده','آخرین مشاهده','وضعیت','آخرین Request ID','یادداشت رسیدگی']),
    ];
    foreach (system_health_issue_rows(30) as $issue) {
        $issueRows[] = [
            ['v'=>(string) $issue['fingerprint'],'s'=>'body'], ['v'=>(string) $issue['code'],'s'=>'body'],
            ['v'=>(string) $issue['category'],'s'=>'body'], ['v'=>(string) $issue['severity'],'s'=>'body'],
            ['v'=>$safe((string) $issue['title']),'s'=>'body'], ['v'=>(int) $issue['occurrences'],'s'=>'body'],
            ['v'=>(string) $issue['first_seen'],'s'=>'body'], ['v'=>(string) $issue['last_seen'],'s'=>'body'],
            ['v'=>SYSTEM_HEALTH_STATUSES[(string) $issue['status']] ?? 'باز','s'=>'body'],
            ['v'=>(string) ($issue['last_request_id'] ?? ''),'s'=>'body'],
            ['v'=>$safe((string) ($issue['resolution_note'] ?? '')),'s'=>'body'],
        ];
    }
    if (count($issueRows) === 2) $issueRows[] = [['v'=>'Issue ثبت‌شده‌ای در این بازه وجود ندارد.','s'=>'body']];
    $bytes = xlsx_build([
        ['name'=>'رخدادهای فنی','rows'=>$eventRows,'widths'=>[38,38,18,18,16,24,60,24,32,60,60,70,22],'merges'=>['A1:M1']],
        ['name'=>'Issueها','rows'=>$issueRows,'widths'=>[66,18,18,16,52,14,22,22,20,38,60],'merges'=>['A1:K1']],
    ]);
    xlsx_send('system-logging-technical-' . date('Y-m-d') . '.xlsx', $bytes);
}


/** خروجی امن Excel برای پشتیبانی: بدون شناسه‌های کاربری، نشانی درخواست، جزئیات خام و یادداشت‌ها. */
function system_health_export_safe_xlsx(array $user): never
{
    if (!user_can($user, 'system.health')) {
        security_log_access_denied($user, 'system.health', 'system-health-safe-export');
        http_response_code(403);
        exit('دسترسی به سلامت سامانه مجاز نیست.');
    }
    $days = max(1, min(90, (int) ($_GET['days'] ?? 7)));
    $rows = system_health_groups($days);
    $events = [];
    foreach ($rows as $row) {
        if (!in_array((string) ($row['level'] ?? ''), ['critical', 'error', 'warning'], true)) {
            continue;
        }
        $events[] = ['row' => $row, 'friendly' => system_health_humanize_event($row)];
    }
    usort($events, static function (array $a, array $b): int {
        $rankA = in_array(($a['row']['level'] ?? ''), ['critical', 'error'], true) ? 0 : 1;
        $rankB = in_array(($b['row']['level'] ?? ''), ['critical', 'error'], true) ? 0 : 1;
        return ($rankA <=> $rankB)
            ?: ((int) ($b['row']['count'] ?? 0) <=> (int) ($a['row']['count'] ?? 0))
            ?: strcmp((string) ($b['row']['last_at'] ?? ''), (string) ($a['row']['last_at'] ?? ''));
    });
    $knownUserLabels = [];
    try {
        $knownUserLabels = db()->query("SELECT full_name FROM users WHERE full_name IS NOT NULL AND full_name <> '' UNION SELECT username FROM users WHERE username IS NOT NULL AND username <> ''")->fetchAll(PDO::FETCH_COLUMN);
        usort($knownUserLabels, static fn ($a, $b): int => logging_utf8_strlen((string) $b) <=> logging_utf8_strlen((string) $a));
    } catch (Throwable) {
        // گزارش همچنان از شناسه‌ها، URLها و متن خام خالی می‌ماند؛ این فهرست فقط ماسک نام‌های شناخته‌شده را تقویت می‌کند.
    }
    $checks = system_health_checks();
    $summary = system_health_apply_check_state(system_health_summary(), $checks);
    $eventTotals = system_health_event_totals($days);
    $errorOccurrences = $eventTotals['errors'];
    $warningOccurrences = $eventTotals['warnings'];
    $shownGroupCount = count($events);
    $omittedGroupCount = max(0, $eventTotals['groups'] - $shownGroupCount);
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
        [['v' => 'رخدادهای خطا در بازهٔ گزارش', 's' => 'body'], ['v' => number_format($errorOccurrences), 's' => 'body'], ['v' => 'جمع کل خطاها در بازه است؛ فهرست بعدی فقط گروه‌های پرتکرار را نشان می‌دهد.', 's' => 'body']],
        [['v' => 'رخدادهای هشدار در بازهٔ گزارش', 's' => 'body'], ['v' => number_format($warningOccurrences), 's' => 'body'], ['v' => 'جمع کل هشدارها در بازه است؛ اطلاع‌رسانی‌های عادی حذف شده‌اند.', 's' => 'body']],
        [['v' => 'اقدام پیشنهادی', 's' => 'body'], ['v' => (string) $summary['next_step'], 's' => 'body'], ['v' => 'برای فهمیدن عنوان‌های اولویت‌دار، برگهٔ «رخدادها» را ببینید.', 's' => 'body']],
        [['v' => 'حریم خصوصی', 's' => 'body'], ['v' => 'اطلاعات کاربران در خروجی نیست', 's' => 'body'], ['v' => 'نام/شناسهٔ کاربر، IP، نشانی درخواست، یادداشت و متن خام خطا یا کوئری صادر نمی‌شود.', 's' => 'body']],
        [['v' => 'زمان تهیه', 's' => 'body'], ['v' => persian_date(date('Y-m-d H:i:s')), 's' => 'body'], ['v' => 'در صورت ارسال، فقط همین فایل را بفرستید؛ فایل خام سرور لازم نیست.', 's' => 'body']],
        [['v' => 'گروه‌های رخداد در برگهٔ بعدی', 's' => 'body'], ['v' => number_format($shownGroupCount) . ' از ' . number_format($eventTotals['groups']), 's' => 'body'], ['v' => 'حداکثر ۱۰۰ گروه پرتکرار از هر سطح نمایش داده می‌شود؛ جمع خطا و هشدار بالا از کل بازه محاسبه شده است.', 's' => 'body']],
        [['v' => 'گروه‌های باقی‌ماندهٔ نمایش‌داده‌نشده', 's' => 'body'], ['v' => number_format($omittedGroupCount), 's' => 'body'], ['v' => 'برای جزئیات بیشتر، بازه را کوتاه‌تر کنید یا لاگ داخلی را بررسی کنید.', 's' => 'body']],
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
        [['v' => 'فقط خطاها و هشدارها نمایش داده می‌شوند؛ حداکثر ۱۰۰ گروه پرتکرار از هر سطح. جمع کل بازه کامل است؛ متن خام، مسیر کامل و اطلاعات کاربران صادر نمی‌شود.', 's' => 'body']],
        array_map(static fn (string $header): array => ['v' => $header, 's' => 'header'], ['اولویت', 'بخش سامانه', 'چه اتفاقی افتاده؟', 'چند بار؟', 'اولین مشاهده', 'آخرین مشاهده', 'علت احتمالی به زبان ساده', 'پیشنهاد بعدی', 'وضعیت پیگیری', 'مرجع فنی امن']),
    ];
    foreach ($events as $event) {
        $row = $event['row'];
        $friendly = $event['friendly'];
        $count = (int) ($row['count'] ?? 0);
        $isError = in_array(($row['level'] ?? ''), ['critical', 'error'], true);
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
            system_health_event_reference($row),
        ]);
    }
    if ($events === []) {
        $eventRows[] = [['v' => 'در این بازه خطا یا هشداری ثبت نشده است.', 's' => 'body']];
    }
    $issueRows = [
        [['v' => 'مسئله‌های تجمیع‌شده', 's' => 'title']],
        [['v' => 'کد رخداد', 's' => 'header'], ['v' => 'عنوان', 's' => 'header'], ['v' => 'شدت', 's' => 'header'], ['v' => 'دسته', 's' => 'header'], ['v' => 'تعداد رخداد', 's' => 'header'], ['v' => 'اولین مشاهده', 's' => 'header'], ['v' => 'آخرین مشاهده', 's' => 'header'], ['v' => 'وضعیت', 's' => 'header'], ['v' => 'علت احتمالی', 's' => 'header'], ['v' => 'اقدام پیشنهادی', 's' => 'header']],
    ];
    foreach (system_health_issue_rows($days) as $issue) {
        $definition = logging_catalog_resolve((string) $issue['code'], (string) $issue['category'], (string) $issue['severity']);
        $issueRows[] = [
            ['v' => (string) $issue['code'], 's' => 'body'],
            ['v' => (string) ($definition['title'] ?? $issue['title']), 's' => 'body'],
            ['v' => system_health_human_level((string) $issue['severity']), 's' => 'body'],
            ['v' => (string) $issue['category'], 's' => 'body'],
            ['v' => (int) $issue['occurrences'], 's' => 'body'],
            ['v' => (string) $issue['first_seen'], 's' => 'body'],
            ['v' => (string) $issue['last_seen'], 's' => 'body'],
            ['v' => SYSTEM_HEALTH_STATUSES[(string) $issue['status']] ?? 'باز', 's' => 'body'],
            ['v' => $safe((string) ($definition['probable_cause'] ?? '')), 's' => 'body'],
            ['v' => $safe((string) ($definition['recommended_action'] ?? '')), 's' => 'body'],
        ];
    }
    if (count($issueRows) === 2) $issueRows[] = [['v' => 'در این بازه مسئله‌ای ثبت نشده است.', 's' => 'body']];

    $bytes = xlsx_build([
        [
            'name' => 'خلاصهٔ قابل‌فهم',
            'rows' => $summaryRows,
            'widths' => [34, 32, 80],
            'merges' => ['A1:C1', 'A17:C17'],
        ],
        [
            'name' => 'رخدادها',
            'rows' => $eventRows,
            'widths' => [22, 24, 42, 14, 22, 22, 56, 56, 20, 28],
            'merges' => ['A1:J1', 'A2:J2'],
        ],
        [
            'name' => 'مسئله‌ها',
            'rows' => $issueRows,
            'widths' => [20, 38, 22, 18, 16, 22, 22, 18, 60, 60],
            'merges' => ['A1:J1'],
        ],
    ]);
    xlsx_send('system-health-readable-' . date('Y-m-d') . '.xlsx', $bytes);
}


function system_health_request_timeline(string $requestId): array
{
    if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $requestId)) {
        return [];
    }
    $sql = "SELECT created_at, 'system' AS source, level AS result, context AS title, message AS detail, event_id, trace_id, operation_id, parent_event_id
            FROM system_logs WHERE request_id = ?
            UNION ALL
            SELECT created_at, 'activity' AS source, result, action_code AS title, action_label AS detail, event_id, trace_id, operation_id, parent_event_id
            FROM activity_logs WHERE request_id = ?
            UNION ALL
            SELECT created_at, 'ticket' AS source, event_type AS result, CONCAT('درخواست #', ticket_id) AS title,
                   CONCAT(COALESCE(from_status, ''), ' → ', COALESCE(to_status, ''), ' ', COALESCE(CAST(details AS CHAR), '')) AS detail, event_id, trace_id, operation_id, parent_event_id
            FROM ticket_events WHERE request_id = ?
            ORDER BY created_at ASC LIMIT 300";
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute([$requestId, $requestId, $requestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable) {
        return [];
    }
}

function system_health_render(array $user): never
{
    if (!user_can($user, 'system.health')) {
        security_log_access_denied($user, 'system.health', 'system-health');
        http_response_code(403);
        exit('دسترسی به سلامت سامانه مجاز نیست.');
    }
    if (in_array((string) ($_GET['export'] ?? ''), ['safe', 'csv'], true)) {
        system_health_export_safe_xlsx($user);
    }
    if ((string) ($_GET['export'] ?? '') === 'technical') {
        system_health_export_technical_xlsx($user);
    }
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        system_health_handle_post($user);
    }
    $summary = system_health_summary();
    $checks = system_health_checks();
    $summary = system_health_apply_check_state($summary, $checks);
    $canViewTechnical = user_can($user, 'logs.view');
    $groups = $canViewTechnical ? system_health_groups(7) : [];
    $eventTotals = system_health_event_totals(7);
    $requestId = trim((string) ($_GET['request_id'] ?? ''));
    $requestId = preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $requestId) ? $requestId : '';
    $requestTimeline = ($canViewTechnical && $requestId !== '') ? system_health_request_timeline($requestId) : [];
    $issueDays = max(1, min(90, (int) ($_GET['issue_days'] ?? 30)));
    $issueSeverity = (string) ($_GET['issue_severity'] ?? '');
    $issueCategory = (string) ($_GET['issue_category'] ?? '');
    $issueStatus = (string) ($_GET['issue_status'] ?? '');
    $issuePage = max(1, (int) ($_GET['issue_page'] ?? 1));
    $issuePageSize = 50;
    $issueTotal = $canViewTechnical ? system_health_issue_total($issueDays, $issueSeverity, $issueCategory, $issueStatus) : 0;
    $issuePages = max(1, (int) ceil($issueTotal / $issuePageSize));
    $issuePage = min($issuePage, $issuePages);
    $issues = $canViewTechnical ? system_health_issue_rows($issueDays, $issueSeverity, $issueCategory, $issueStatus, $issuePage, $issuePageSize) : [];
    $historyFingerprint = strtolower(trim((string) ($_GET['issue_history'] ?? '')));
    $historyPage = max(1, (int) ($_GET['history_page'] ?? 1));
    $historyTotal = ($canViewTechnical && preg_match('/^[a-f0-9]{64}$/', $historyFingerprint)) ? system_health_issue_history_total($historyFingerprint) : 0;
    $historyPages = max(1, (int) ceil($historyTotal / 50));
    $historyPage = min($historyPage, $historyPages);
    $selectedIssueHistory = ($canViewTechnical && preg_match('/^[a-f0-9]{64}$/', $historyFingerprint)) ? system_health_issue_history($historyFingerprint, $historyPage, 50) : [];
    $canManage = user_can($user, 'system.health_edit') && $canViewTechnical;
    $stateClass = ['ok' => 'good', 'warning' => 'warn', 'critical' => 'danger'][$summary['state']] ?? 'warn';
    $checkClass = ['ok' => 'good', 'warn' => 'warn', 'fail' => 'danger'];
    $checkLabel = ['ok' => 'مناسب', 'warn' => 'نیازمند توجه', 'fail' => 'ناموفق'];

    render_header('سلامت سامانه', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">گزارش سادهٔ وضعیت فنی</span><h1>سلامت سامانه</h1><p>این صفحه می‌گوید آیا در بررسی‌های پایه یا ثبت رخدادهای اخیر موردی برای پیگیری دیده شده است.</p></div><div class="actions"><a class="button" href="index.php?page=system-health&amp;export=safe">دانلود گزارش خوانا و امن (Excel)</a>' . (user_can($user, 'logs.view') ? '<a class="button secondary" href="index.php?page=system-health&amp;export=technical">گزارش فنی پالایش‌شده (Excel)</a>' : '') . '</div></section>';
    if ($canViewTechnical) {
    echo '<section class="card form-card"><h2>ردیابی یک درخواست</h2><p class="muted">کد پیگیری نمایش‌داده‌شده در خطای ۵۰۰ را وارد کنید تا رخدادهای فنی، فعالیت کاربر و تاریخچهٔ درخواست کنار هم دیده شوند.</p><form method="get"><input type="hidden" name="page" value="system-health"><label>کد پیگیری درخواست<input name="request_id" dir="ltr" maxlength="36" value="' . e($requestId) . '" placeholder="xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx"></label><button class="button secondary" type="submit">جست‌وجوی درخواست</button></form>';
    if ($requestId !== '') {
        echo '<p class="muted" dir="ltr">' . e($requestId) . ' · ' . count($requestTimeline) . ' رخداد</p>';
        if ($requestTimeline === []) {
            echo '<p>برای این کد پیگیری رخدادی پیدا نشد؛ ممکن است مهاجرت دیتابیس هنوز اجرا نشده باشد یا رخداد قدیمی باشد.</p>';
        } else {
            echo '<div class="ticket-table"><div class="table-head"><span>زمان</span><span>منبع</span><span>رخداد</span></div>';
            foreach ($requestTimeline as $event) {
                $sourceLabel = ['system' => 'فنی', 'activity' => 'فعالیت کاربر', 'ticket' => 'تاریخچه درخواست'][$event['source']] ?? $event['source'];
                echo '<div class="table-row"><span class="date-cell">' . e(persian_date((string) $event['created_at'])) . '</span><span>' . e($sourceLabel) . '</span><span><b>' . e((string) $event['title']) . '</b><br><small class="muted">' . e(logging_sanitize_text((string) ($event['detail'] ?? ''), 1200)) . '</small><br><small dir="ltr" class="muted">Trace: ' . e((string) ($event['trace_id'] ?? '')) . ' · Operation: ' . e((string) ($event['operation_id'] ?? '')) . '</small></span></div>';
            }
            echo '</div>';
        }
    }
    echo '</section>';
    }

    echo '<section class="card form-card"><h2>نتیجه: <span class="status ' . $stateClass . '">' . e($summary['state_label']) . '</span></h2>';
    echo '<p>' . e($summary['plain_message']) . '</p><p><b>پیشنهاد بعدی:</b> ' . e($summary['next_step']) . '</p>';
    if (($summary['state'] ?? '') === 'unknown') {
        echo '<p class="muted">آمار ۲۴ ساعت گذشته فعلاً نامشخص است؛ اعداد صفر نمایش داده نمی‌شوند.</p>';
    } else {
        echo '<p class="muted">در ۲۴ ساعت گذشته: ' . number_format((int) $summary['errors_24h']) . ' خطا، ' . number_format((int) $summary['warnings_24h']) . ' هشدار، ' . number_format((int) $summary['slow_pages_24h']) . ' صفحهٔ کند، ' . number_format((int) $summary['slow_queries_24h']) . ' پاسخ کند پایگاه‌داده؛ ' . number_format((int) $summary['fatal_1h']) . ' خطای جدی در یک ساعت گذشته.</p>';
    }
    echo '<p class="muted">«هشدار» به‌تنهایی به معنی قطع یا خراب‌شدن سامانه نیست. فهرست زیر نتیجهٔ بررسی‌های پایه را جداگانه نشان می‌دهد؛ رخدادهای فنی نیز برای ۷ روز گذشته گروه‌بندی شده‌اند.</p></section>';

    echo '<section class="card ticket-table"><div class="table-head"><span>بررسی</span><span>وضعیت</span><span>توضیح</span></div>';
    foreach ($checks as [$label, $state, $detail]) {
        echo '<div class="table-row"><span>' . e($label) . '</span><span><em class="status ' . ($checkClass[$state] ?? 'warn') . '">' . e($checkLabel[$state] ?? $state) . '</em></span><span class="muted">' . e($detail) . '</span></div>';
    }
    echo '</section>';

    if ($canViewTechnical) {
    echo '<section class="card form-card"><div class="section-title"><div><h2>مسئله‌های تجمیع‌شده (Issue Management)</h2><p class="muted">خطاهای هم‌نوع با اثرانگشت پایدار در یک مسئله جمع می‌شوند؛ شمارنده با هر رخداد جدید افزایش می‌یابد. دادهٔ امنیتی و حسابرسی در لاگ خام حذف یا محدودسازی نمی‌شود.</p></div></div>';
    echo '<form method="get" class="role-form"><input type="hidden" name="page" value="system-health">'
        . '<label>بازه<select name="issue_days">';
    foreach ([1=>'۱ روز',7=>'۷ روز',30=>'۳۰ روز',90=>'۹۰ روز'] as $daysValue=>$daysLabel) echo '<option value="' . $daysValue . '"' . ($issueDays===$daysValue?' selected':'') . '>' . $daysLabel . '</option>';
    echo '</select></label><label>شدت<select name="issue_severity"><option value="">همه</option>';
    foreach (['critical'=>'بحرانی','error'=>'خطا','warning'=>'هشدار'] as $v=>$l) echo '<option value="' . $v . '"' . ($issueSeverity===$v?' selected':'') . '>' . $l . '</option>';
    echo '</select></label><label>دسته<select name="issue_category"><option value="">همه</option>';
    foreach (['system'=>'سیستم','security'=>'امنیت','audit'=>'حسابرسی','business'=>'کسب‌وکار','operation'=>'عملیات','integration'=>'یکپارچه‌سازی','performance'=>'عملکرد'] as $v=>$l) echo '<option value="' . $v . '"' . ($issueCategory===$v?' selected':'') . '>' . $l . '</option>';
    echo '</select></label><label>وضعیت<select name="issue_status"><option value="">همه</option>';
    foreach (SYSTEM_HEALTH_STATUSES as $v=>$l) echo '<option value="' . $v . '"' . ($issueStatus===$v?' selected':'') . '>' . e($l) . '</option>';
    echo '</select></label><button class="button secondary" type="submit">اعمال فیلتر</button></form>';
    if ($issues === []) {
        echo '<p class="muted">برای فیلتر انتخاب‌شده مسئله‌ای ثبت نشده است یا Migration شمارهٔ ۰۲۰ هنوز اجرا نشده است.</p>';
    }
    foreach ($issues as $issue) {
        $definition = logging_catalog_resolve((string) $issue['code'], (string) $issue['category'], (string) $issue['severity']);
        $diagnosis = logging_diagnostic_analyze($issue);
        $similarIssues = logging_diagnostic_similarity($issue, $issues, 5);
        $statusLabel = SYSTEM_HEALTH_STATUSES[(string) $issue['status']] ?? (string) $issue['status'];
        echo '<div class="table-row"><span><strong>' . e((string) ($definition['title'] ?? $issue['title'])) . '</strong><br><small class="muted">' . e((string) $issue['code']) . ' · ' . e((string) $issue['category']) . ' · ' . e(system_health_human_level((string) $issue['severity'])) . ' · تکرار: ' . number_format((int) $issue['occurrences']) . '</small>';
        echo '<br><small><a href="index.php?page=system-health&amp;issue_history=' . rawurlencode((string) $issue['fingerprint']) . '">مشاهدهٔ تاریخچهٔ کامل رسیدگی</a></small>';
        if (!empty($issue['last_request_id'])) echo '<br><small>آخرین پیگیری: <a href="index.php?page=system-health&amp;request_id=' . rawurlencode((string) $issue['last_request_id']) . '"><code dir="ltr">' . e((string) $issue['last_request_id']) . '</code></a></small>';
        echo '<br><small>علت احتمالی: ' . e((string) $diagnosis['probable_cause']) . '</small><br><small>اطمینان موتور قواعد: ' . e((string) $diagnosis['confidence_label']) . ($diagnosis['known_rule'] ? ' · قاعده شناخته‌شده' : ' · قاعده اختصاصی موجود نیست') . '</small><br><small>اقدام پیشنهادی: ' . e((string) $diagnosis['recommended_fix']) . '</small>';
        if ($diagnosis['checks'] !== []) echo '<details><summary>شواهد و مراحل بررسی تشخیصی</summary><ul>' . implode('', array_map(static fn($item) => '<li>' . e((string)$item) . '</li>', $diagnosis['checks'])) . '</ul></details>';
        if ($similarIssues !== []) echo '<br><small>مسئله‌های مشابه قابل بررسی در همین صفحه: ' . number_format(count($similarIssues)) . '</small>';
        echo '</span>';
        echo '<span class="muted">' . e($statusLabel) . '<br><small>اولین: ' . e(persian_date((string) $issue['first_seen'])) . '<br>آخرین: ' . e(persian_date((string) $issue['last_seen'])) . '</small></span>';
        if ($canManage) {
            echo '<span><form method="post" class="role-form">' . csrf_field() . '<input type="hidden" name="action" value="issue_status"><input type="hidden" name="fingerprint" value="' . e((string) $issue['fingerprint']) . '"><label>وضعیت<select name="status">';
            foreach (SYSTEM_HEALTH_STATUSES as $v=>$l) echo '<option value="' . $v . '"' . ((string)$issue['status']===$v?' selected':'') . '>' . e($l) . '</option>';
            echo '</select></label><label>یادداشت<input name="note" maxlength="1000" value="' . e((string) ($issue['resolution_note'] ?? '')) . '"></label><button class="button secondary" type="submit">ذخیره</button></form></span>';
        }
        echo '</div>';
    }
    if ($historyFingerprint !== '') {
        echo '<section class="card form-card"><h3>تاریخچهٔ رسیدگی به مسئله</h3><code dir="ltr">' . e($historyFingerprint) . '</code>';
        if ($selectedIssueHistory === []) {
            echo '<p class="muted">برای این مسئله سابقهٔ تغییری ثبت نشده است یا جدول تاریخچه در Migration 021 آماده نیست.</p>';
        } else {
            echo '<div class="ticket-table"><div class="table-head"><span>زمان / مسئول</span><span>تغییر وضعیت</span><span>یادداشت قبل و بعد</span></div>';
            foreach ($selectedIssueHistory as $history) {
                $beforeStatus = SYSTEM_HEALTH_STATUSES[(string) ($history['before_status'] ?? '')] ?? '—';
                $afterStatus = SYSTEM_HEALTH_STATUSES[(string) ($history['after_status'] ?? '')] ?? (string) ($history['after_status'] ?? '');
                echo '<div class="table-row"><span>' . e(persian_date((string) $history['created_at'])) . '<br><small>' . e((string) ($history['actor_label'] ?? 'کاربر')) . '</small></span>';
                echo '<span>' . e($beforeStatus) . ' ← ' . e($afterStatus) . '</span>';
                echo '<span><small>قبل: ' . e((string) ($history['before_note'] ?? '—')) . '<br>بعد: ' . e((string) ($history['after_note'] ?? '—')) . '</small></span></div>';
            }
            echo '</div>';
            if ($historyPages > 1) {
                echo '<nav class="actions" aria-label="صفحه‌بندی تاریخچه"><span class="muted">صفحه ' . number_format($historyPage) . ' از ' . number_format($historyPages) . ' · مجموع ' . number_format($historyTotal) . ' تغییر</span>';
                if ($historyPage > 1) echo '<a class="button secondary" href="index.php?page=system-health&amp;issue_history=' . rawurlencode($historyFingerprint) . '&amp;history_page=' . ($historyPage - 1) . '">قبلی</a>';
                if ($historyPage < $historyPages) echo '<a class="button secondary" href="index.php?page=system-health&amp;issue_history=' . rawurlencode($historyFingerprint) . '&amp;history_page=' . ($historyPage + 1) . '">بعدی</a>';
                echo '</nav>';
            }
        }
        echo '</section>';
    }
    if ($issuePages > 1) {
        $base = 'index.php?page=system-health&amp;issue_days=' . $issueDays . '&amp;issue_severity=' . rawurlencode($issueSeverity) . '&amp;issue_category=' . rawurlencode($issueCategory) . '&amp;issue_status=' . rawurlencode($issueStatus) . '&amp;issue_page=';
        echo '<nav class="actions" aria-label="صفحه‌بندی مسئله‌ها"><span class="muted">صفحه ' . number_format($issuePage) . ' از ' . number_format($issuePages) . ' · مجموع ' . number_format($issueTotal) . ' مسئله</span>';
        if ($issuePage > 1) echo '<a class="button secondary" href="' . $base . ($issuePage - 1) . '">قبلی</a>';
        if ($issuePage < $issuePages) echo '<a class="button secondary" href="' . $base . ($issuePage + 1) . '">بعدی</a>';
        echo '</nav>';
    }
    echo '</section>';
    }

    if ($canViewTechnical) {
    echo '<section class="card ticket-table"><div class="section-title"><div><h2>خطاها و هشدارهای ۷ روز گذشته</h2><p class="muted">رخدادها گروه‌بندی شده‌اند؛ این فهرست حداکثر ۱۰۰ گروه پرتکرار از هر سطح را نشان می‌دهد (' . count($groups) . ' از ' . number_format($eventTotals['groups']) . ' گروه). شمارنده‌های Excel از کل بازه محاسبه می‌شوند.</p></div></div>';
    if ($groups === []) {
        echo '<div class="table-row"><span class="muted">در ۷ روز گذشته خطا یا هشداری ثبت نشده است.</span></div>';
    }
    foreach ($groups as $item) {
        $friendly = system_health_humanize_event($item);
        $levelLabel = system_health_human_level((string) $item['level']);
        $repeatLabel = number_format((int) $item['count']) . ' بار در ۷ روز';
        $statusLabel = SYSTEM_HEALTH_STATUSES[$item['status']] ?? $item['status'];
        $safeReference = system_health_event_reference($item);
        echo '<div class="table-row"><span><strong>' . e($friendly['title']) . '</strong><br><small class="muted">بخش: ' . e($friendly['module']) . ' • ' . e($levelLabel) . ' • تکرار: ' . e($repeatLabel) . ' • مرجع فنی: <code dir="ltr">' . e($safeReference) . '</code>' . (!empty($item['request_id']) ? ' • پیگیری: <a href="index.php?page=system-health&amp;request_id=' . rawurlencode($item['request_id']) . '"><code dir="ltr">' . e($item['request_id']) . '</code></a>' : '') . '</small></span>';
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
        if ($canManage && preg_match('/^[a-f0-9]{64}$/', (string) ($item['issue_fingerprint'] ?? ''))) {
            echo '<form method="post" class="role-form">' . csrf_field()
                . '<input type="hidden" name="action" value="issue_status">'
                . '<input type="hidden" name="fingerprint" value="' . e($item['issue_fingerprint']) . '">'
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
    }

    if ($canManage) {
        echo '<section class="card form-card"><h2>نگهداری لاگ فنی</h2><p class="muted">نگهداری طبقه‌بندی‌شده است: Debug هفت روز، Info/Notice سی روز، کسب‌وکار ۶۰ روز، خطاهای فنی حداقل ۹۰ روز و امنیت/حسابرسی/عملیات حساس حداقل ۱۸۳ روز. مقدار زیر فقط دورهٔ خطاهای فنی را افزایش می‌دهد.</p>'
            . '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="prune_system_logs">'
            . '<label>بازهٔ نگهداری (روز)<input type="number" name="retention_days" min="90" max="3650" value="90"></label>'
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
