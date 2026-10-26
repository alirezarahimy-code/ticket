<?php
declare(strict_types=1);

/**
 * lib/activity-audit.php — ثبت فعالیت، حسابرسی و رویداد درخواست‌ها — جداشده از bootstrap.php در نسخهٔ ۱.۶۲.۲۰ (فاز ۲ بازسازی).
 * محتوا: activity_log* (اسکیما، IP، نام رایانه، برچسب فارسی اکشن‌ها، ثبت و هرس) / ops_low_value_tables_prune / save_audit / log_ticket_event.
 * ترتیب تعریف توابع و کدهای سطح‌بالا عیناً مانند bootstrap قبلی حفظ شده است.
 */

if (!defined('APP_ROOT')) {
    exit;
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
        'food_day_visit_set' => 'خوداظهاری محل تحویل غذا برای امروز',
        'food_day_visit_cleared' => 'لغو خوداظهاری محل تحویل غذا',
        'order_cancel_request' => 'درخواست لغو سفارش غذا در روز بسته',
        'order_cancel_request_approved' => 'تأیید لغو سفارش غذا توسط پشتیبانی',
        'order_cancel_request_rejected' => 'رد درخواست لغو سفارش غذا',
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
        'food_order_cancel_decide' => 'رسیدگی به درخواست لغو غذا',
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
