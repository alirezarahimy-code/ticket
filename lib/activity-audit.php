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
    // DDL در مسیر درخواست اجرا نمی‌شود؛ جدول باید از schema.sql یا Migrationها آماده شده باشد.
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        if (!function_exists('db_table_exists') || !db_table_exists('activity_logs')) {
            logging_emergency_failure('activity_logs_table_missing', 'apply database schema/migrations');
        }
    } catch (Throwable $exception) {
        logging_emergency_failure('activity_logs_schema_check_failed', get_class($exception));
    }
}

function activity_client_ip(): string
{
    $remote = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    if (!filter_var($remote, FILTER_VALIDATE_IP)) {
        return '';
    }
    $trusted = cfg('app.trusted_proxies', []);
    if (is_string($trusted)) {
        $trusted = array_filter(array_map('trim', explode(',', $trusted)));
    }
    if (!is_array($trusted) || !in_array($remote, $trusted, true)) {
        return $remote;
    }
    $forwarded = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
    if ($forwarded === '') {
        return $remote;
    }
    $chain = array_map('trim', explode(',', $forwarded));
    $chain[] = $remote;
    for ($i = count($chain) - 1; $i >= 0; $i--) {
        $candidate = $chain[$i];
        if (!filter_var($candidate, FILTER_VALIDATE_IP)) {
            continue;
        }
        if (!in_array($candidate, $trusted, true)) {
            return substr($candidate, 0, 64);
        }
    }
    return $remote;
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
        'login' => 'ورود به سامانه',
        'login_local' => 'ورود با حساب محلی',
        'login_ldap' => 'ورود با حساب دامین',
        'login_failed' => 'تلاش ناموفق ورود',
        'login_locked' => 'قفل‌شدن حساب پس از تلاش‌های ناموفق',
        'logout' => 'خروج از سامانه',
        'page_view' => 'مشاهدهٔ صفحه',
        'profile_updated' => 'به‌روزرسانی پروفایل',
        'change_created' => 'ایجاد درخواست تغییر',
        'change_updated' => 'ویرایش درخواست تغییر',
        'change_submitted' => 'ارسال تغییر برای بررسی CAB',
        'change_cab_approved' => 'تأیید تغییر توسط CAB',
        'change_cab_rejected' => 'رد تغییر توسط CAB',
        'change_transition' => 'تغییر وضعیت اجرای تغییر',
        'change_scheduled' => 'زمان‌بندی اجرای تغییر',
        'change_implemented' => 'ثبت اجرای تغییر',
        'change_rolled_back' => 'بازگشت تغییر',
        'change_closed' => 'بستن تغییر',
        'change_ticket_linked' => 'اتصال درخواست به تغییر',
        'problem_ticket_linked' => 'اتصال درخواست به مشکل',
        'problem_created' => 'ایجاد رکورد مشکل',
        'problem_updated' => 'به‌روزرسانی رکورد مشکل',
        'holiday_created' => 'ثبت تعطیلی',
        'holiday_activated' => 'فعال‌سازی تعطیلی',
        'holiday_deactivated' => 'غیرفعال‌سازی تعطیلی',
        'holiday_deleted' => 'حذف تعطیلی',
        'holidays_imported' => 'ورود گروهی تعطیلات',
        'traffic_visit_created' => 'ثبت ورود مراجعه‌کننده',
        'traffic_visit_updated' => 'ویرایش ورود مراجعه‌کننده',
        'traffic_visit_deleted' => 'حذف رکورد مراجعه‌کننده',
        'traffic_visit_exit_set' => 'ثبت خروج مراجعه‌کننده',
        'traffic_notice_created' => 'ثبت اطلاعیه تردد',
        'food_orders_imported' => 'ورود گروهی سفارش‌های غذا',
        'food_employees_imported' => 'ورود گروهی کارکنان فیش غذا',
        'food_ticket_config_saved' => 'ذخیره تنظیمات چاپ فیش غذا',
        'food_ticket_access_database_uploaded' => 'بارگذاری دیتابیس دسترسی فیش غذا',
        'traffic_records_imported' => 'ورود گروهی تردد',
        'maintenance_cache_purged' => 'پاک‌سازی Cache سامانه',
        'maintenance_tables_analyzed' => 'بهینه‌سازی و تحلیل جداول',
        'system_logs_pruned' => 'پاک‌سازی لاگ‌های سامانه',
        'system_incident_status' => 'تغییر وضعیت رخداد سلامت سامانه',
        'page_layout_saved' => 'ذخیره چیدمان صفحه',
        'page_layout_reset' => 'بازنشانی چیدمان صفحه',
        'chatbot_faq_saved' => 'ذخیره پرسش و پاسخ آریا',
        'chatbot_tts_saved' => 'ذخیره تنظیمات گفتار آریا',
        'chatbot_widget_saved' => 'ذخیره تنظیمات ابزارک آریا',
        'chatbot_tts_ezafe_pairs' => 'به‌روزرسانی واژگان کسره اضافه',
        'org_action_undo_requested' => 'درخواست بازگردانی تغییر ساختار سازمان',
        'org_action_redo_requested' => 'درخواست تکرار تغییر ساختار سازمان',
        'org_action_undone' => 'بازگردانی تغییر ساختار سازمان',
        'org_action_redone' => 'تکرار تغییر ساختار سازمان',
        'backup_restore_started' => 'شروع بازیابی پشتیبان',
        'system_update_started' => 'شروع به‌روزرسانی سامانه',
        'ldap_bulk_sync_started' => 'شروع همگام‌سازی گروهی دامین',
        'ldap_bulk_sync' => 'نتیجه همگام‌سازی گروهی دامین',
        'ldap_diagnosed' => 'آزمون ارتباط LDAP',
        'activity_logs_prune_started' => 'شروع پاک‌سازی گزارش فعالیت',
        'service_created' => 'ایجاد خدمت در کاتالوگ',
        'service_updated' => 'ویرایش خدمت در کاتالوگ',
        'service_deleted' => 'غیرفعال‌سازی خدمت در کاتالوگ',
        'service_category_saved' => 'ذخیره دسته‌بندی خدمت',
        'service_category_deleted' => 'غیرفعال‌سازی دسته‌بندی خدمت',
        'service_field_created' => 'ایجاد فیلد سفارشی خدمت',
        'knowledge_created' => 'ایجاد مقاله دانش‌نامه',
        'department_structure_updated' => 'تغییر مدیر و اعضای معاونت',
        'cd_dvd_inspection_user_changed' => 'تغییر مسئول بازرسی CD/DVD',
        'food_ticket_brand_updated' => 'تغییر هویت بصری پنل فیش غذا',
        'domain_inventory_scan_queued' => 'ثبت نوبت استخراج کل دامنه',
        'domain_inventory_selected' => 'ثبت نوبت استخراج سیستم‌های انتخاب‌شده',
        'domain_inventory_retry' => 'ثبت تلاش دوباره استخراج دامنه',
        'domain_assets_imported' => 'ورود دارایی‌های دامنه',
        'server_inventory_collect_started' => 'شروع استخراج شناسنامه سرور',
        'server_inventory_collected' => 'ثبت شناسنامه سرور',
        'asset_profile_saved' => 'ذخیره شناسنامه دارایی',
        'asset_profile_selftest_applied' => 'اعمال داده‌های آزمون شناسنامه دارایی',
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
        'access_group_created' => 'ایجاد گروه دسترسی',
        'access_group_updated' => 'ویرایش اعضا و دسترسی‌های گروه',
        'access_group_deleted' => 'حذف گروه دسترسی',
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

function activity_default_result(string $actionCode): string
{
    $code = strtolower(trim($actionCode));
    if ($code === '' || $code === 'unknown') return 'unknown';
    if (in_array($code, ['login_failed','post_action_failed','food_ticket_print_failed'], true) || str_ends_with($code, '_failed') || str_ends_with($code, '_error')) return 'failure';
    if (in_array($code, ['login_locked','access_denied','csrf_419'], true) || str_ends_with($code, '_denied') || str_ends_with($code, '_locked')) return 'denied';
    // API قدیمی activity_log پس از عملیات موفق فراخوانی می‌شود؛ نتیجهٔ موفق صریح/قابل‌استنباط است.
    return 'success';
}

function activity_log(array $data): bool
{
    $user = $data['user'] ?? null;
    if (is_array($user)) {
        $userId = isset($user['id']) ? (int) $user['id'] : null;
        $username = (string) ($user['username'] ?? '');
        $fullName = (string) ($user['full_name'] ?? '');
        $role = (string) ($user['role'] ?? '');
    } else {
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : (isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);
        $sessionUser = is_array($_SESSION['audit_user'] ?? null) ? $_SESSION['audit_user'] : (is_array($_SESSION['user'] ?? null) ? $_SESSION['user'] : []);
        $username = (string) ($data['username'] ?? $sessionUser['username'] ?? '');
        $fullName = (string) ($data['full_name'] ?? $sessionUser['full_name'] ?? '');
        $role = (string) ($data['role'] ?? $sessionUser['role'] ?? '');
    }
    $code = substr((string) ($data['action_code'] ?? 'unknown'), 0, 120);
    $eventDefinition = logging_action_event_definition($code);
    $meta = is_array($data['meta'] ?? null) ? logging_sanitize_event_meta($data['meta'], $eventDefinition['code'], $code) : [];
    $ip = activity_client_ip();
    if (!filter_var($ip, FILTER_VALIDATE_IP)) $ip = '';
    $computer = (string) ($data['computer_name'] ?? activity_computer_name($ip));
    $hostname = (string) ($data['hostname'] ?? ($_SERVER['SERVER_NAME'] ?? ''));
    $result = $data['result'] ?? activity_default_result($code);
    $event = [
        'event_id' => (isset($data['event_id']) && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', (string) $data['event_id'])) ? strtolower((string) $data['event_id']) : logging_uuid(), 'user_id' => $userId,
        'username' => substr(logging_sanitize_text($username, 190), 0, 190),
        'full_name' => substr(logging_sanitize_text($fullName, 190), 0, 190),
        'role' => substr(logging_sanitize_text($role, 40), 0, 40),
        'action_code' => $code, 'code' => $eventDefinition['code'], 'category' => $eventDefinition['category'],
        'action_label' => substr(logging_sanitize_text((string) ($data['action_label'] ?? activity_action_label($code)), 190), 0, 190),
        'module' => isset($data['module']) && $data['module'] !== null ? substr((string) $data['module'], 0, 80) : null,
        'target_type' => isset($data['target_type']) && $data['target_type'] !== null ? substr((string) $data['target_type'], 0, 40) : null,
        'target_id' => isset($data['target_id']) && $data['target_id'] !== null ? (int) $data['target_id'] : null,
        'target_label' => isset($data['target_label']) && $data['target_label'] !== null ? substr(logging_sanitize_text((string) $data['target_label'], 255), 0, 255) : null,
        'meta' => $meta, 'ip_address' => $ip !== '' ? $ip : null,
        'computer_name' => $computer !== '' ? substr($computer, 0, 190) : null,
        'hostname' => $hostname !== '' ? substr($hostname, 0, 190) : null,
        'user_agent' => substr(logging_sanitize_text((string) ($data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')), 255), 0, 255),
        'result' => in_array($result, ['success', 'failure', 'denied', 'unknown'], true) ? (string) $result : 'unknown',
    ];
    // هیچ Query هویتی پیش از ساخت payload اجرا نمی‌شود؛ در قطع DB، همین payload کامل به Spool می‌رود.
    return logging_store_activity($event);
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
    $days = max(30, min(3650, $days));
    $auditDays = max(183, $days);
    try {
            $businessDeleted = db_delete_in_batches('activity_logs', "category NOT IN ('security','audit','operation') AND created_at < (NOW() - INTERVAL ? DAY)", [$days]);
        $protectedDeleted = db_delete_in_batches('activity_logs', "category IN ('security','audit','operation') AND created_at < (NOW() - INTERVAL ? DAY)", [$auditDays]);
        return $businessDeleted + $protectedDeleted;
    } catch (Throwable $exception) {
        logging_emergency_failure('activity_logs_prune_failed', get_class($exception));
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
    return max(30, min(3650, $days > 0 ? $days : 60));
}

/** Resolve stable module and target columns for audit events instead of leaving them NULL. */
function activity_audit_context(string $action, ?int $ticketId, array $meta): array
{
    $module = match (true) {
        str_starts_with($action, 'ticket_'), in_array($action, ['ticket_created','ticket_updated','ticket_replied','ticket_rated'], true) => 'tickets',
        str_starts_with($action, 'cd_dvd_') => 'cd-dvd',
        str_starts_with($action, 'holiday_'), $action === 'holidays_imported' => 'holidays',
        str_starts_with($action, 'change_'), str_starts_with($action, 'problem_') => 'governance',
        str_starts_with($action, 'ldap_'), $action === 'sync_ldap_users' => 'ldap',
        in_array($action, ['profile_updated','user_theme_changed','local_password_reset'], true) => 'account',
        str_starts_with($action, 'org_'), str_starts_with($action, 'department_') => 'organization',
        str_contains($action, 'role'), str_contains($action, 'permission') => 'permissions',
        str_contains($action, 'backup'), str_starts_with($action, 'system_update') => 'backup',
        str_starts_with($action, 'service_'), str_starts_with($action, 'knowledge_') => 'services',
        str_starts_with($action, 'traffic_') => 'traffic',
        str_starts_with($action, 'asset_'), str_starts_with($action, 'domain_'), str_starts_with($action, 'server_inventory_') => 'assets',
        str_starts_with($action, 'settings_'), str_starts_with($action, 'save_settings'), str_starts_with($action, 'update_setting') => 'settings',
        default => 'audit',
    };

    $targetType = null;
    $targetId = null;
    $targetLabel = null;
    if (str_starts_with($action, 'cd_dvd_') && isset($meta['record_id'])) {
        $targetType = 'cd_dvd_record'; $targetId = (int) $meta['record_id'];
        $targetLabel = isset($meta['serial']) ? (string) $meta['serial'] : ('#' . $targetId);
    } elseif (str_starts_with($action, 'change_') && isset($meta['change_id'])) {
        $targetType = 'change'; $targetId = (int) $meta['change_id'];
        $targetLabel = isset($meta['change_number']) ? (string) $meta['change_number'] : ('#' . $targetId);
    } elseif (str_starts_with($action, 'problem_') && isset($meta['problem_id'])) {
        $targetType = 'problem'; $targetId = (int) $meta['problem_id'];
        $targetLabel = isset($meta['problem_number']) ? (string) $meta['problem_number'] : ('#' . $targetId);
    } elseif (str_starts_with($action, 'holiday_') && (isset($meta['holiday_id']) || isset($meta['holiday_date']))) {
        $targetType = 'holiday'; $targetId = isset($meta['holiday_id']) ? (int) $meta['holiday_id'] : null;
        $targetLabel = (string) ($meta['holiday_date'] ?? $meta['title'] ?? 'تعطیلی');
    } elseif (isset($meta['unit_id'])) {
        $targetType = 'org_unit'; $targetId = (int) $meta['unit_id'];
        $targetLabel = isset($meta['name']) ? (string) $meta['name'] : ('#' . $targetId);
    } elseif ($ticketId !== null || isset($meta['ticket_id'])) {
        $targetType = 'ticket';
        $targetId = $ticketId ?? (int) $meta['ticket_id'];
        $targetLabel = function_exists('ticket_number') ? (string) ticket_number((int) $targetId) : ('#' . (int) $targetId);
    } elseif (isset($meta['user_id'])) {
        $targetType = 'user'; $targetId = (int) $meta['user_id']; $targetLabel = 'کاربر #' . $targetId;
    }

    return [
        'module' => $module,
        'target_type' => $targetType,
        'target_id' => $targetId,
        'target_label' => $targetLabel,
    ];
}

function save_audit(int $userId, string $action, ?int $ticketId = null, array $meta = []): bool
{
    if ($ticketId !== null && !isset($meta['ticket_id'])) $meta['ticket_id'] = $ticketId;
    $context = activity_audit_context($action, $ticketId, $meta);
    $persisted = activity_log([
        'user_id' => $userId,
        'action_code' => $action,
        'target_id' => $context['target_id'],
        'target_type' => $context['target_type'],
        'target_label' => $context['target_label'],
        'module' => $context['module'],
        'meta' => $meta,
    ]);
    $GLOBALS['activity_audited'] = (bool) $persisted;
    return (bool) $persisted;
}

/** عملیات حساس باید پیش از Commit، ثبت پایدار Audit را الزام کند. */
function save_audit_required(int $userId, string $action, ?int $ticketId = null, array $meta = [], string $result = 'success', ?string $eventId = null): void
{
    if (!in_array($result, ['success', 'failure', 'denied', 'unknown'], true)) $result = 'unknown';
    if ($ticketId !== null && !isset($meta['ticket_id'])) $meta['ticket_id'] = $ticketId;
    $context = activity_audit_context($action, $ticketId, $meta);
    $persisted = activity_log([
        'user_id' => $userId,
        'action_code' => $action,
        'target_type' => $context['target_type'],
        'target_id' => $context['target_id'],
        'target_label' => $context['target_label'],
        'module' => $context['module'],
        'meta' => $meta,
        'result' => $result,
        'event_id' => $eventId,
    ]);
    $GLOBALS['activity_audited'] = (bool) $persisted;
    if (!$persisted) {
        throw new RuntimeException('Required audit persistence failed for action: ' . $action);
    }
}

function log_ticket_event(int $ticketId, ?int $userId, string $eventType, ?string $fromStatus = null, ?string $toStatus = null, array $details = []): void
{
    $eventId = logging_uuid();
    $requestId = logging_request_id();
    $traceId = logging_trace_id();
    $operationId = logging_operation_id();
    $parentEventId = logging_parent_event_id();
    $pdo = null;
    $insideTransaction = false;

    try {
        $pdo = db();
        $insideTransaction = $pdo->inTransaction();
        $query = $pdo->prepare('INSERT INTO ticket_events (event_id, request_id, trace_id, operation_id, parent_event_id, ticket_id, user_id, event_type, from_status, to_status, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $query->execute([
            $eventId, $requestId, $traceId, $operationId, $parentEventId, $ticketId, $userId,
            $eventType, $fromStatus, $toStatus, logging_json($details),
        ]);
        // Circuit فقط پس از موفقیت واقعی INSERT بسته می‌شود.
        if (function_exists('logging_db_circuit_success')) logging_db_circuit_success();
        if ($parentEventId === null) $GLOBALS['__logging_parent_event_id'] = $eventId;
    } catch (Throwable $exception) {
        if ($insideTransaction) {
            // رخداد وابسته به تراکنش rollback‌شده نباید بیرون از آن بازپخش شود.
            throw $exception;
        }
        if (function_exists('logging_db_error_is_transient') && logging_db_error_is_transient($exception)) {
            if (function_exists('logging_db_circuit_failure')) logging_db_circuit_failure($exception);
            $queued = logging_record_failure('ticket_event', [
                'event_id' => $eventId,
                'request_id' => $requestId,
                'trace_id' => $traceId,
                'operation_id' => $operationId,
                'parent_event_id' => $parentEventId,
                'ticket_id' => $ticketId,
                // FK user_id may be locked/deleted while replaying; actor id remains in sanitized details.
                'user_id' => null,
                'event_type' => $eventType,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'details' => $details + ($userId !== null ? ['actor_id' => $userId] : []),
            ]);
            if (!$queued) logging_emergency_failure('ticket_event_spool_unconfirmed', $eventId);
            if ($parentEventId === null) $GLOBALS['__logging_parent_event_id'] = $eventId;
            return;
        }
        // خطای Schema/FK/داده به‌عنوان خطای قطعی پنهان نمی‌شود و در caller قابل مشاهده است.
        throw $exception;
    }
}
