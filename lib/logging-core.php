<?php
declare(strict_types=1);

/**
 * هستهٔ مرکزی Logging سامانه.
 * مسئول هم‌بستگی درخواست، پالایش داده، کاتالوگ رخداد و صف جایگزین ثبت است.
 * این فایل به زیرساخت خارجی وابسته نیست و با PHP 8.1+ سازگار است.
 */

if (!defined('APP_ROOT')) {
    exit;
}

require_once __DIR__ . '/log-catalog.php';

// سقف سخت صف اضطراری؛ فقط هنگام اختلال دیتابیس استفاده می‌شود و از پرشدن بی‌حد دیسک جلوگیری می‌کند.
if (!defined('LOGGING_SPOOL_MAX_BYTES')) define('LOGGING_SPOOL_MAX_BYTES', 268435456); // 256 MiB
if (!defined('LOGGING_SPOOL_MAX_FILES')) define('LOGGING_SPOOL_MAX_FILES', 256);
if (!defined('LOGGING_SPOOL_MIN_FREE_BYTES')) define('LOGGING_SPOOL_MIN_FREE_BYTES', 268435456); // 256 MiB

function logging_uuid(): string
{
    try {
        $bytes = random_bytes(16);
    } catch (Throwable) {
        $fallback = substr(hash('sha256', uniqid('', true) . mt_rand()), 0, 32);
        $bytes = hex2bin($fallback);
        if (!is_string($bytes) || strlen($bytes) !== 16) {
            $bytes = str_repeat("\0", 16);
        }
    }
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
}

/** Stable UUIDv8-style identifier for idempotent reconciliation records. */
function logging_deterministic_uuid(string $namespace, string $value): string
{
    $hex = substr(hash('sha256', $namespace . '|' . $value), 0, 32);
    $hex[12] = '8';
    $hex[16] = dechex((hexdec($hex[16]) & 0x3) | 0x8);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
}

function logging_is_uuid_v4(string $value): bool
{
    return (bool) preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $value);
}

function logging_is_uuid(string $value): bool
{
    return (bool) preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $value);
}

function logging_request_id(): string
{
    if (!isset($GLOBALS['__logging_request_id'])) {
        $GLOBALS['__logging_request_id'] = logging_uuid();
    }
    return (string) $GLOBALS['__logging_request_id'];
}

function logging_trace_id(): string
{
    if (!isset($GLOBALS['__logging_trace_id'])) $GLOBALS['__logging_trace_id'] = logging_uuid();
    return (string) $GLOBALS['__logging_trace_id'];
}

function logging_operation_id(): string
{
    if (!isset($GLOBALS['__logging_operation_id'])) $GLOBALS['__logging_operation_id'] = logging_uuid();
    return (string) $GLOBALS['__logging_operation_id'];
}

/** آغاز یک اجرای مستقل؛ برای هر درخواست HTTP یا هر چرخهٔ مستقل Worker. */
function logging_begin_execution(string $name = 'request'): array
{
    $GLOBALS['__logging_request_id'] = logging_uuid();
    $GLOBALS['__logging_trace_id'] = logging_uuid();
    $GLOBALS['__logging_operation_id'] = logging_uuid();
    $GLOBALS['__logging_operation_name'] = logging_sanitize_text($name, 120);
    $GLOBALS['__logging_parent_event_id'] = null;
    $GLOBALS['__logging_started_at'] = microtime(true);
    return logging_correlation_context();
}

/** آغاز یک عملیات فرعی در همان درخواست/Trace؛ شناسهٔ Request و Trace ثابت می‌ماند. */
function logging_begin_operation(string $name = 'request'): string
{
    $GLOBALS['__logging_operation_id'] = logging_uuid();
    $GLOBALS['__logging_operation_name'] = logging_sanitize_text($name, 120);
    $GLOBALS['__logging_parent_event_id'] = null;
    return (string) $GLOBALS['__logging_operation_id'];
}

function logging_operation_name(): string
{
    return (string) ($GLOBALS['__logging_operation_name'] ?? 'request');
}

/** شناسه‌های جاری برای افزودن به پیام خطا یا انتقال امن به Worker داخلی. */
function logging_correlation_context(): array
{
    return [
        'request_id' => logging_request_id(),
        'trace_id' => logging_trace_id(),
        'operation_id' => logging_operation_id(),
        'operation_name' => logging_operation_name(),
        'parent_event_id' => logging_parent_event_id(),
    ];
}

function logging_parent_event_id(): ?string
{
    $value = (string) ($GLOBALS['__logging_parent_event_id'] ?? '');
    return logging_is_uuid_v4($value) ? $value : null;
}

function logging_bootstrap(): void
{
    // ورودی HTTP و هر اجرای CLI شناسه‌های مستقل دارند؛ هدر ورودی کاربر هرگز مبنای ID قرار نمی‌گیرد.
    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? 'request'));
    $executionName = PHP_SAPI === 'cli' ? 'worker:' . $script : 'http:' . $script;
    $context = logging_begin_execution($executionName);
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        header('X-Request-Id: ' . $context['request_id']);
        header('X-Trace-Id: ' . $context['trace_id']);
    }
}

function logging_catalog_aliases(): array
{
    return [
        'PHP_ERROR' => 'SYS-PHP-001', 'PHP_FATAL' => 'SYS-PHP-001', 'DB_ERROR' => 'SYS-DB-001',
        'DATABASE_ERROR' => 'SYS-DB-001', 'LDAP_ERROR' => 'INT-LDAP-001', 'LOGIN_FAILED' => 'SEC-AUTH-001',
        'LOGIN_LOCKED' => 'SEC-AUTH-002', 'ACCESS_DENIED' => 'SEC-ACL-001', 'CSRF_419' => 'SEC-CSRF-001',
        'BACKUP_CREATED' => 'AUD-BKP-001', 'BACKUP_RESTORED' => 'AUD-BKP-002', 'SETTINGS_CHANGED' => 'AUD-SET-001',
    ];
}

function logging_event_catalog(): array
{
    $catalog = logging_catalog_definitions();
    $aliases = [];
    foreach (logging_catalog_aliases() as $alias => $canonical) $aliases[$canonical][] = $alias;
    foreach ($catalog as $code => &$definition) $definition['aliases'] = $aliases[$code] ?? [];
    unset($definition);
    return $catalog;
}

function logging_catalog_resolve(string $code, string $fallbackCategory = 'system', string $fallbackSeverity = 'error'): array
{
    $catalog = logging_event_catalog();
    $requestedCode = strtoupper(trim($code));
    $canonicalCode = logging_catalog_aliases()[$requestedCode] ?? $requestedCode;
    if (isset($catalog[$canonicalCode])) {
        return $catalog[$canonicalCode] + ['code' => $canonicalCode, 'requested_code' => $requestedCode];
    }
    return [
        'code' => preg_match('/^[A-Z0-9][A-Z0-9._-]{2,39}$/', $code) ? $code : 'SYS-LEGACY-001',
        'category' => in_array($fallbackCategory, ['system', 'security', 'audit', 'business', 'operation', 'integration', 'performance'], true) ? $fallbackCategory : 'system',
        'severity' => in_array($fallbackSeverity, ['debug', 'info', 'notice', 'warning', 'error', 'critical'], true) ? $fallbackSeverity : 'error',
        'title' => 'رخداد سامانه',
        'user_message' => 'عملیات انجام نشد. کد پیگیری را به پشتیبانی بدهید.',
        'admin_explanation' => 'کد رخداد در کاتالوگ شناخته‌شده نیست؛ مسیر سازگاری قدیمی استفاده شد.',
        'probable_cause' => 'برای تشخیص علت، اطلاعات فنی پالایش‌شده و رخدادهای هم‌بسته بررسی شوند.',
        'recommended_action' => 'با Request ID و زمان رخداد، جزئیات فنی را بررسی کنید.',
        'allowed_context_keys' => ['module', 'operation', 'target_type', 'target_id', 'request_id'],
        'aliases' => [],
    ];
}

function logging_action_event_definition(string $action): array
{
    $map = [
        'local_password_reset' => ['SEC-AUTH-005', 'security'],
        'login_failed' => ['SEC-AUTH-001', 'security'],
        'login_locked' => ['SEC-AUTH-002', 'security'],
        'login' => ['SEC-AUTH-003', 'security'],
        'login_local' => ['SEC-AUTH-003', 'security'],
        'login_ldap' => ['SEC-AUTH-003', 'security'],
        'logout' => ['SEC-AUTH-004', 'security'],
        'access_denied' => ['SEC-ACL-001', 'security'],
        'permission_denied' => ['SEC-ACL-001', 'security'],
        'update_user_role' => ['AUD-ACL-001', 'audit'],
        'save_role_permissions' => ['AUD-ACL-001', 'audit'],
        'save_key_roles' => ['AUD-ACL-001', 'audit'],
        'reset_role_permissions' => ['AUD-ACL-001', 'audit'],
        'reset_role_defaults' => ['AUD-ACL-001', 'audit'],
        'update_setting' => ['AUD-SET-001', 'audit'],
        'save_settings' => ['AUD-SET-001', 'audit'],
        'update_system_settings' => ['AUD-SET-001', 'audit'],
        'config_changed' => ['AUD-SET-001', 'audit'],
        'http_403' => ['SEC-ACL-001', 'security'],
        'csrf_419' => ['SEC-CSRF-001', 'security'],
        'create_backup' => ['AUD-BKP-001', 'audit'],
        'backup_created' => ['AUD-BKP-001', 'audit'],
        'backup_restored' => ['AUD-BKP-002', 'audit'],
        'backup_restore_started' => ['AUD-BKP-002', 'audit'],
        'system_update_started' => ['AUD-BKP-003', 'audit'],
        'ldap_bulk_sync_started' => ['AUD-ACL-002', 'audit'],
        'ldap_bulk_sync' => ['AUD-ACL-002', 'audit'],
        'ldap_diagnosed' => ['INT-LDAP-001', 'integration'],
        'profile_updated' => ['AUD-USER-001', 'audit'],
        'user_theme_changed' => ['AUD-USER-001', 'audit'],
        'activity_logs_prune_started' => ['AUD-SET-001', 'audit'],
        'service_created' => ['AUD-SVC-001', 'audit'],
        'service_updated' => ['AUD-SVC-001', 'audit'],
        'service_deleted' => ['AUD-SVC-001', 'audit'],
        'service_category_saved' => ['AUD-SVC-001', 'audit'],
        'service_category_deleted' => ['AUD-SVC-001', 'audit'],
        'service_field_created' => ['AUD-SVC-001', 'audit'],
        'knowledge_created' => ['AUD-KNW-001', 'audit'],
        'change_created' => ['AUD-GOV-001', 'audit'],
        'change_updated' => ['AUD-GOV-001', 'audit'],
        'change_submitted' => ['AUD-GOV-001', 'audit'],
        'change_cab_approved' => ['AUD-GOV-001', 'audit'],
        'change_cab_rejected' => ['AUD-GOV-001', 'audit'],
        'change_transition' => ['AUD-GOV-001', 'audit'],
        'change_scheduled' => ['AUD-GOV-001', 'audit'],
        'change_implemented' => ['AUD-GOV-001', 'audit'],
        'change_rolled_back' => ['AUD-GOV-001', 'audit'],
        'change_closed' => ['AUD-GOV-001', 'audit'],
        'change_ticket_linked' => ['AUD-GOV-001', 'audit'],
        'problem_ticket_linked' => ['AUD-GOV-002', 'audit'],
        'problem_created' => ['AUD-GOV-002', 'audit'],
        'problem_updated' => ['AUD-GOV-002', 'audit'],
        'cd_dvd_in' => ['AUD-MEDIA-001', 'audit'],
        'cd_dvd_out' => ['AUD-MEDIA-001', 'audit'],
        'cd_dvd_updated' => ['AUD-MEDIA-001', 'audit'],
        'cd_dvd_deleted' => ['AUD-MEDIA-001', 'audit'],
        'holiday_created' => ['AUD-CAL-001', 'audit'],
        'holiday_activated' => ['AUD-CAL-001', 'audit'],
        'holiday_deactivated' => ['AUD-CAL-001', 'audit'],
        'holiday_deleted' => ['AUD-CAL-001', 'audit'],
        'holidays_imported' => ['AUD-CAL-001', 'audit'],
        'change_cab_decision' => ['AUD-GOV-001', 'audit'],
        'traffic_visit_created' => ['AUD-TRF-001', 'audit'],
        'traffic_visit_updated' => ['AUD-TRF-001', 'audit'],
        'traffic_visit_deleted' => ['AUD-TRF-001', 'audit'],
        'traffic_visit_exit_set' => ['AUD-TRF-001', 'audit'],
        'traffic_notice_created' => ['AUD-TRF-001', 'audit'],
        'food_orders_imported' => ['AUD-FOOD-001', 'audit'],
        'food_employees_imported' => ['AUD-FOOD-001', 'audit'],
        'food_ticket_config_saved' => ['AUD-FOOD-002', 'audit'],
        'food_ticket_access_database_uploaded' => ['AUD-FOOD-002', 'audit'],
        'traffic_records_imported' => ['AUD-TRF-002', 'audit'],
        'maintenance_cache_purged' => ['AUD-MNT-001', 'audit'],
        'maintenance_tables_analyzed' => ['AUD-MNT-001', 'audit'],
        'system_logs_pruned' => ['AUD-MNT-001', 'audit'],
        'page_layout_saved' => ['AUD-SET-001', 'audit'],
        'page_layout_reset' => ['AUD-SET-001', 'audit'],
        'chatbot_faq_saved' => ['AUD-SET-002', 'audit'],
        'chatbot_tts_saved' => ['AUD-SET-002', 'audit'],
        'chatbot_widget_saved' => ['AUD-SET-002', 'audit'],
        'chatbot_tts_ezafe_pairs' => ['AUD-SET-002', 'audit'],
        'department_structure_updated' => ['AUD-ORG-002', 'audit'],
        'cd_dvd_inspection_user_changed' => ['AUD-SET-001', 'audit'],
        'food_ticket_brand_updated' => ['AUD-SET-001', 'audit'],
        'domain_inventory_scan_queued' => ['AUD-INV-001', 'audit'],
        'domain_inventory_retry' => ['AUD-INV-001', 'audit'],
        'domain_inventory_selected' => ['AUD-INV-001', 'audit'],
        'domain_assets_imported' => ['AUD-INV-001', 'audit'],
        'server_inventory_collect_started' => ['AUD-INV-001', 'audit'],
        'server_inventory_collected' => ['AUD-INV-001', 'audit'],
        'assets_pinged' => ['AUD-INV-001', 'operation'],
        'asset_profile_saved' => ['AUD-INV-001', 'audit'],
        'asset_profile_selftest_applied' => ['AUD-INV-001', 'audit'],
        'restore_backup' => ['AUD-BKP-002', 'audit'],
        'org_backup_restored' => ['AUD-BKP-002', 'audit'],
        'org_company_display_names_updated' => ['AUD-SET-001', 'audit'],
        'triangle_effect_saved' => ['AUD-SET-001', 'audit'],
        'inspection_food_absence_alarm_updated' => ['AUD-SET-001', 'audit'],
        'system_updated' => ['AUD-BKP-003', 'audit'],
        'org_ceo_saved' => ['AUD-ORG-001', 'audit'],
        'user_org_updated' => ['AUD-ORG-001', 'audit'],
        'org_unit_saved' => ['AUD-ORG-001', 'audit'],
        'org_unit_deleted' => ['AUD-ORG-001', 'audit'],
        'org_users_assigned' => ['AUD-ORG-001', 'audit'],
        'org_manager_assigned' => ['AUD-ORG-001', 'audit'],
        'org_manager_removed' => ['AUD-ORG-001', 'audit'],
        'org_experts_removed' => ['AUD-ORG-001', 'audit'],
        'org_structure_reset' => ['AUD-ORG-001', 'audit'],
        'org_action_undone' => ['AUD-ORG-001', 'audit'],
        'org_action_redone' => ['AUD-ORG-001', 'audit'],
        'org_action_undo_requested' => ['AUD-ORG-001', 'audit'],
        'org_action_redo_requested' => ['AUD-ORG-001', 'audit'],
        'org_deputies_sorted' => ['AUD-ORG-001', 'audit'],
        'org_manager_primary_admin_repaired' => ['AUD-ORG-001', 'audit'],
        'org_manager_support_role_repaired' => ['AUD-ORG-001', 'audit'],
        'activity_logs_exported' => ['AUD-LOG-001', 'audit'],
        'activity_logs_pruned' => ['AUD-LOG-002', 'audit'],
        'system_incident_status' => ['AUD-LOG-003', 'audit'],
        'food_ticket_print_failed' => ['FOOD-PRT-003', 'operation'],
        'ldap_sync' => ['INT-LDAP-001', 'integration'],
        'sync_ldap_users' => ['INT-LDAP-001', 'integration'],
    ];
    if (isset($map[$action])) {
        return ['code' => $map[$action][0], 'category' => $map[$action][1]];
    }
    if (str_starts_with($action, 'food_')) {
        return ['code' => 'FOOD-LEGACY-001', 'category' => 'operation'];
    }
    if (str_starts_with($action, 'traffic_')) {
        return ['code' => 'BIZ-TRF-001', 'category' => 'business'];
    }
    if (str_starts_with($action, 'ticket_') || in_array($action, ['create_ticket', 'update_ticket', 'reply', 'rate_ticket'], true)) {
        return ['code' => 'BIZ-TKT-001', 'category' => 'business'];
    }
    if (str_contains($action, 'role') || str_contains($action, 'permission') || str_contains($action, 'backup')
        || str_contains($action, 'setting') || str_contains($action, 'org_') || str_starts_with($action, 'delete_')
        || str_starts_with($action, 'reset_') || str_starts_with($action, 'assign_') || str_starts_with($action, 'remove_')
        || str_starts_with($action, 'system_') || str_starts_with($action, 'maintenance_')) {
        return ['code' => 'AUD-LEGACY-001', 'category' => 'audit'];
    }
    return ['code' => 'BIZ-LEGACY-001', 'category' => 'business'];
}

/** نرمال‌سازی رقم‌های فارسی/عربی به ASCII بدون تغییر سایر نویسه‌ها. */
function logging_normalize_digits(string $value): string
{
    return strtr($value, [
        '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
        '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
    ]);
}

/** اعتبارسنجی checksum کد ملی ایران؛ فراخوانی‌کننده فقط با opt-in تنظیماتی از آن برای رد داده استفاده کند. */
function logging_is_valid_iranian_national_code(string $value): bool
{
    $code = logging_normalize_digits(trim($value));
    if (!preg_match('/^\d{10}$/D', $code) || preg_match('/^(\d)\1{9}$/', $code)) return false;
    $sum = 0;
    for ($i = 0; $i < 9; $i++) $sum += ((int) $code[$i]) * (10 - $i);
    $remainder = $sum % 11;
    $check = $remainder < 2 ? $remainder : 11 - $remainder;
    return $check === (int) $code[9];
}

function logging_strict_national_code_enabled(): bool
{
    return function_exists('cfg') && cfg('logging.strict_national_code', false) === true;
}

function logging_sensitive_key(string $key): bool
{
    // کلیدها پیش از تطبیق، از نظر حروف فارسی/عربی و فاصله‌های نامرئی یکسان‌سازی می‌شوند.
    $normalized = str_replace(["ي", "ك", "ۀ", "ة", "ـ", "\u{200c}", "\u{200f}", "\u{200e}"], ["ی", "ک", "ه", "ه", "", " ", "", ""], trim($key));
    $flat = function_exists('mb_strtolower') ? mb_strtolower($normalized, 'UTF-8') : strtolower($normalized);
    $flat = preg_replace('/[\s_\-]+/u', '', $flat) ?? $flat;
    $english = strtolower(preg_replace('/[^a-z0-9]+/i', '', $key) ?? $key);
    $sensitiveEnglish = [
        'password','passwd','pass','pwd','secret','token','authorization','cookie','setcookie',
        'csrf','session','sessionid','apikey','apitoken','accesstoken','refreshtoken','privatekey','bindpassword','ldapbindpassword','nationalcode','nationalid',
        'ssn','email','phone','mobile','employeeid','employeenumber','personnelcode','birthdate','birthplace','dob',
    ];
    if (in_array($english, $sensitiveEnglish, true)) return true;
    foreach ([
        'رمز','رمزعبور','گذرواژه','کلمهعبور','توکن','نشست','کوکی','کلیدخصوصی','کلیدمحرمانه',
        'مجوزدسترسی','اعتبارنامه','کدملی','شمارهملی','ایمیل','پستالکترونیک','تلفن','شمارهتلفن',
        'همراه','شمارههمراه','شمارهپرسنلی','کدپرسنلی','تاریختولد','محلتولد','شناسهکارمند',
    ] as $needle) {
        if ($flat !== '' && str_contains($flat, $needle)) return true;
    }
    return false;
}

function logging_utf8_substr(string $value, int $start, int $length): string
{
    if (function_exists('mb_substr')) return mb_substr($value, $start, $length, 'UTF-8');
    if (function_exists('iconv_substr')) {
        $result = iconv_substr($value, $start, $length, 'UTF-8');
        if (is_string($result)) return $result;
    }
    return substr($value, $start, $length);
}

function logging_utf8_strlen(string $value): int
{
    if (function_exists('mb_strlen')) return mb_strlen($value, 'UTF-8');
    if (function_exists('iconv_strlen')) {
        $result = iconv_strlen($value, 'UTF-8');
        if (is_int($result)) return $result;
    }
    return strlen($value);
}

function logging_sanitize_text(string $value, int $limit = 2000): string
{
    // C0/C1 control characters are removed; LF and TAB remain valid for multiline diagnostic text.
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0D\x0E-\x1F\x7F]/', '', $value) ?? $value;
    $value = preg_replace('/Bearer\s+[A-Za-z0-9._~+\/-]+=*/i', 'Bearer [REDACTED]', $value) ?? $value;
    $value = preg_replace('/((?:رمز(?:\s*عبور)?|گذرواژه|کلمه\s*عبور|توکن|کلید\s*(?:خصوصی|محرمانه)|کد\s*ملی|شماره\s*(?:ملی|همراه|تلفن|پرسنلی)|ایمیل|نشانی\s*ایمیل)\s*[=:：]\s*)[^\s,;،]+/iu', '$1[REDACTED]', $value) ?? $value;
    $value = preg_replace('/((?:password|passwd|pwd|secret|token|api[_-]?key|authorization|cookie|csrf)\s*[=:]\s*)[^\s,;]+/iu', '$1[REDACTED]', $value) ?? $value;
    $value = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '[EMAIL_REDACTED]', $value) ?? $value;
    if (logging_strict_national_code_enabled()) {
        $value = preg_replace_callback('/(?<![0-9۰-۹٠-٩])[0-9۰-۹٠-٩]{10}(?![0-9۰-۹٠-٩])/u', static function (array $m): string {
            return logging_is_valid_iranian_national_code($m[0]) ? '[NATIONAL_ID_REDACTED]' : $m[0];
        }, $value) ?? $value;
    } else {
        // رفتار سازگار قدیمی: تمام شناسه‌های ده‌رقمی بدون وابستگی به تنظیمات پالایش می‌شوند.
        $value = preg_replace('/(?<![0-9۰-۹٠-٩])[0-9۰-۹٠-٩]{10}(?![0-9۰-۹٠-٩])/u', '[NATIONAL_ID_REDACTED]', $value) ?? $value;
    }
    $value = preg_replace('~(mysql:|pgsql:)[^\s]+~i', '[DATABASE_DSN_REDACTED]', $value) ?? $value;
    return logging_utf8_substr($value, 0, $limit);
}

/** Sanitizer برای sinkهای تک‌خطی؛ CR/LF/TAB را به فاصله تبدیل می‌کند تا log injection رخ ندهد. */
function logging_sanitize_single_line(string $value, int $limit = 2000): string
{
    $value = logging_sanitize_text($value, $limit);
    $value = preg_replace('/[\r\n\t]+/u', ' ', $value) ?? $value;
    return trim($value);
}

/** پالایش متادیتا با allowlist وابسته به کد رخداد و زمینه؛ کلید ناشناخته به‌صورت پیش‌فرض حذف می‌شود. */
/** Fingerprint پایدار و نسخه‌دار برای همسان‌سازی ثبت، بازپخش و تجمیع Issue. */
function logging_fingerprint_v2(string $code, string $context, string $message): string
{
    $normalized = logging_sanitize_text(trim($message), 2000);
    $normalized = preg_replace('/\b[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}\b/i', '{uuid}', $normalized) ?? $normalized;
    $normalized = preg_replace('/\b\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?\b/i', '{timestamp}', $normalized) ?? $normalized;
    $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
    $parts = [strtolower(trim($code)), strtolower(trim($context)), function_exists('mb_strtolower') ? mb_strtolower($normalized, 'UTF-8') : strtolower($normalized)];
    return hash('sha256', implode('|', $parts));
}

/** نسخهٔ آزمایشی v3؛ بدون مهاجرت خودکار Issueها و فقط با opt-in صریح فعال می‌شود. */
function logging_fingerprint_v3(string $code, string $context, string $message): string
{
    $base = logging_fingerprint_v2($code, $context, $message);
    return hash('sha256', 'logging-fingerprint-v3|' . $base);
}

/** تنها نقطهٔ انتخاب نسخهٔ fingerprint برای store/replay/issue/rebuild. */
function logging_fingerprint_current(string $code, string $context, string $message): string
{
    $version = function_exists('cfg') ? (int) cfg('logging.fingerprint_version', 2) : 2;
    return $version === 3 ? logging_fingerprint_v3($code, $context, $message) : logging_fingerprint_v2($code, $context, $message);
}

/** Trace پالایش‌شده؛ هرگز آرگومان تابع، متغیر محلی یا مقدار حساس ثبت نمی‌شود. */
function logging_exception_trace(Throwable $exception, int $maxFrames = 12): array
{
    $root = rtrim(str_replace('\\', '/', APP_ROOT), '/') . '/';
    $chain = [];
    $current = $exception;
    for ($depth = 0; $current instanceof Throwable && $depth < 3; $depth++, $current = $current->getPrevious()) {
        $frames = [];
        $rawFrames = array_slice($current->getTrace(), 0, max(1, min(12, $maxFrames)));
        array_unshift($rawFrames, ['file' => $current->getFile(), 'line' => $current->getLine(), 'class' => get_class($current), 'function' => '{throw}']);
        foreach ($rawFrames as $frame) {
            $file = str_replace('\\', '/', (string) ($frame['file'] ?? ''));
            if ($file !== '' && str_starts_with($file, $root)) $file = substr($file, strlen($root));
            elseif ($file !== '') $file = basename($file);
            $frames[] = [
                'file' => logging_sanitize_text($file, 180),
                'line' => max(0, (int) ($frame['line'] ?? 0)),
                'class' => logging_sanitize_text((string) ($frame['class'] ?? ''), 100),
                'function' => logging_sanitize_text((string) ($frame['function'] ?? ''), 100),
            ];
        }
        $chain[] = ['type' => get_class($current), 'message' => logging_sanitize_text($current->getMessage(), 500), 'frames' => $frames];
    }
    return $chain;
}

function logging_sanitize_event_meta(array $meta, string $code, string $context = ''): array
{
    $definition = logging_catalog_resolve($code);
    $allowed = [
        'code','category','cause','action','technical','location','error_type','file_basename','line','stack_trace','ms','reason','attempt','bytes','failed','success',
        'route','sql_state','db_operation','table','duration_ms','query_count','in_transaction',
        'module','operation','target_type','target_id','target_label','request_id','result','status',
    ];
    $activityActionAllowlist = [
        'login_failed' => ['attempts','auth_source','attempt_window'],
        'login_locked' => ['attempts','lock_duration'],
        'access_denied' => ['permission','route','reason','permission_set'],
        'role_permissions_updated' => ['role','permissions'],
        'role_permissions_reset' => ['roles'],
        'reset_role_defaults' => ['role'],
        'key_roles_updated' => ['supervisors','inspectors','it_experts'],
        'ticket_created' => ['ticket_id','operation','from_status','to_status','service_id','priority','ticket_type'],
        'ticket_updated' => ['ticket_id','operation','from_status','to_status','changed_fields','status','priority','assigned_to'],
        'ticket_cancellation_requested' => ['reason','status'],
        'ticket_cancellation_approved' => ['reason','note','requested_at'],
        'ticket_cancellation_rejected' => ['reason','note','requested_at'],
        'ticket_cancelled_by_requester' => ['reason','mode'],
        'ticket_claimed' => ['assigned_to'],
        'ticket_reopened_by_user' => ['reason'],
        'ticket_replied' => ['ticket_id','operation'],
        'ticket_rated' => ['ticket_id','score'],
        'backup_created' => ['backup_id','backup_size','result','name','uploads'],
        'backup_restored' => ['backup_id','result','safety_backup','uploads'],
        'backup_restore_started' => ['safety_backup'],
        'system_update_started' => ['requested','version'],
        'ldap_bulk_sync_started' => ['requested'],
        'activity_logs_prune_started' => ['days'],
        'service_created' => ['service_id','name','code','service_group'],
        'service_updated' => ['service_id','name'],
        'service_deleted' => ['service_id'],
        'service_category_saved' => ['category_id','name'],
        'service_category_deleted' => ['category_id'],
        'service_field_created' => ['service_id','field_key','field_type'],
        'knowledge_created' => ['article_id','title'],
        'change_created' => ['change_id','change_number','title','risk_level','ticket_id'],
        'change_updated' => ['change_id','change_number','title','risk_level','status','changed_fields'],
        'change_submitted' => ['change_id','change_number','status'],
        'change_cab_approved' => ['change_id','change_number','status','decision'],
        'change_cab_rejected' => ['change_id','change_number','status','decision','note'],
        'change_transition' => ['change_id','change_number','status','transition','implementation_note','ticket_id'],
        'change_scheduled' => ['change_id','change_number','status','ticket_id'],
        'change_implemented' => ['change_id','change_number','status','ticket_id'],
        'change_rolled_back' => ['change_id','change_number','status','ticket_id'],
        'change_closed' => ['change_id','change_number','status','ticket_id'],
        'change_ticket_linked' => ['change_id','ticket_id'],
        'problem_ticket_linked' => ['problem_id','ticket_id'],
        'problem_created' => ['problem_id','problem_number','title','priority','ticket_id'],
        'problem_updated' => ['problem_id','problem_number','status','priority','changed_fields'],
        'cd_dvd_in' => ['record_id','serial','media','before','after'],
        'cd_dvd_out' => ['record_id','serial','media','before','after'],
        'cd_dvd_updated' => ['record_id','serial','media','before','after'],
        'cd_dvd_deleted' => ['record_id','serial','media','before','after'],
        'holiday_created' => ['holiday_id','holiday_date','title','is_active'],
        'holiday_activated' => ['holiday_id','holiday_date','title','is_active'],
        'holiday_deactivated' => ['holiday_id','holiday_date','title','is_active'],
        'holiday_deleted' => ['holiday_id','holiday_date','title'],
        'holidays_imported' => ['count','skipped_weekends','source'],
        'traffic_visit_created' => ['visit_id','serial'],
        'traffic_visit_updated' => ['visit_id'],
        'traffic_visit_deleted' => ['visit_id'],
        'traffic_visit_exit_set' => ['visit_id'],
        'traffic_notice_created' => ['visit_id','serial'],
        'food_orders_imported' => ['file','created','restored','rows'],
        'food_employees_imported' => ['file','rows','created','updated','failed'],
        'food_ticket_config_saved' => ['printer_mode','printer_host'],
        'food_ticket_access_database_uploaded' => ['kind','filename','size'],
        'traffic_records_imported' => ['created','updated','skipped','failed','mode'],
        'maintenance_cache_purged' => ['requested','deleted','files'],
        'maintenance_tables_analyzed' => ['requested','done','failed'],
        'system_logs_pruned' => ['days','deleted'],
        'system_incident_status' => ['incident_id','status'],
        'page_layout_saved' => ['page','rules'],
        'page_layout_reset' => ['page','rules'],
        'chatbot_faq_saved' => ['id','question'],
        'chatbot_tts_saved' => ['mode','speed','voice','kind'],
        'chatbot_widget_saved' => ['name'],
        'chatbot_tts_ezafe_pairs' => ['added'],
        'department_structure_updated' => ['department_id','manager_id','member_ids'],
        'cd_dvd_inspection_user_changed' => ['user_id'],
        'food_ticket_brand_updated' => ['logo_changed'],
        'domain_inventory_scan_queued' => ['run_id','total'],
        'domain_inventory_retry' => ['run_id','total','from_run'],
        'domain_inventory_selected' => ['run_id','total'],
        'domain_assets_imported' => ['created','updated','total'],
        'server_inventory_collect_started' => ['overwrite'],
        'server_inventory_collected' => ['asset_id','overwrite','hardware_score','operation','result'],
        'assets_pinged' => ['checked','online','result'],
        'asset_profile_saved' => ['asset_id','field_count'],
        'asset_profile_selftest_applied' => ['asset_id','applied','overwrite'],
        'activity_logs_exported' => ['format','filter_period'],
        'activity_logs_pruned' => ['days','deleted','retention_days','deleted_count'],
        'triangle_effect_saved' => ['pages','effect'],
        'inspection_food_absence_alarm_updated' => ['enabled'],
        'org_company_display_names_updated' => ['companies'],
        'org_ceo_saved' => ['user_id'],
        'user_org_updated' => ['user_id','unit_id','manager_user_id'],
        'org_action_undone' => ['label'],
        'org_action_redone' => ['label'],
        'org_action_undo_requested' => ['label'],
        'org_action_redo_requested' => ['label'],
        'org_deputies_sorted' => ['unit_ids'],
        'org_experts_removed' => ['unit_id','user_ids'],
        'org_backup_restored' => ['units','users'],
        'org_manager_primary_admin_repaired' => ['user_ids'],
        'org_manager_support_role_repaired' => ['user_ids'],
        'local_password_reset' => ['user_id'],
        'local_guard_roles_saved' => ['changed'],
        'ldap_bulk_sync' => ['created','updated','disabled','skipped','failed'],
        'ldap_diagnosed' => ['ok'],
        'user_theme_changed' => ['theme'],
        'profile_updated' => ['changed_fields'],
        'food_ticket_print_failed' => ['printer_alias','print_job_id','attempt','result'],
        'food_ticket_delivered' => ['order_id','ticket_id','result'],
        'food_ticket_delivery_revoked' => ['order_id','ticket_id','reason'],
        'food_group_tickets_issued' => ['order_id','count','result'],
        'org_unit_saved' => ['unit_id','operation','changed_fields','name','type','chart_side','manager','managers'],
        'org_unit_deleted' => ['unit_id','operation'],
        'org_users_assigned' => ['unit_id','user_id','user_ids','operation'],
        'org_manager_assigned' => ['unit_id','manager_user_id','user_id','target_type'],
        'org_manager_removed' => ['unit_id','manager_user_id','user_id','target_type'],
        'settings_updated' => ['setting_key','changed_fields'],
        'profile_updated' => ['changed_fields'],
        'post_action_failed' => ['action','page','error_type'],
        'system_updated' => ['version','mode','from','migrations','files','backup'],
        'maintenance_cache_purged' => ['requested','deleted','files','bytes','failed'],
        'maintenance_tables_analyzed' => ['requested','done','failed'],
    ];
    $contextAllowlist = [
        'traffic-dashboard' => ['file','line'],
        'food_order' => ['order_id','operation','result'],
        'inventory' => ['asset_id','filled','score','checked','online'],
        'inventory_collect' => ['asset_id','filled','score'],
        'domain_scan' => ['run_id','total','created','updated'],
        'food_group' => ['date','operation'],
        'food-employees' => ['count','created','updated','total'],
        'food-orders' => ['created','updated','total','imported','skipped'],
        'food-printer' => ['printer_alias','job_id','paper_width','paper_height','applied_width','applied_height','alignment'],
        'food-ticket-api' => ['route','location'],
        'food_print' => ['printer_alias','job_id','duration_ms','result'],
        'food_print_align' => ['printer_alias','job_id','alignment'],
        'ticket_number_schema' => ['in_transaction','table','operation'],
        'ticket_number_autofill' => ['operation','table'],
        'transaction' => ['context','in_transaction'],
        'کندی کوئری' => ['duration_ms','query_count','table','db_operation'],
        'کندی صفحه' => ['duration_ms','route','query_count'],
        'request' => ['route','file','line','error_type','location','technical','cause','action'],
    ];
    $allowed = array_merge($allowed, (array) ($definition['allowed_context_keys'] ?? []), $contextAllowlist[$context] ?? [], $activityActionAllowlist[$context] ?? []);
    $allowed = array_fill_keys(array_map(static fn ($key): string => strtolower((string) $key), $allowed), true);
    $filtered = [];
    foreach ($meta as $key => $value) {
        $normalizedKey = strtolower((string) $key);
        if (!isset($allowed[$normalizedKey]) || logging_sensitive_key((string) $key)) continue;
        $filtered[$key] = logging_sanitize_value($value, (string) $key);
    }
    return $filtered;
}

function logging_sanitize_value($value, string $key = '', int $depth = 0)
{
    if (logging_sensitive_key($key)) {
        return '[REDACTED]';
    }
    if ($depth > 5) {
        return '[MAX_DEPTH]';
    }
    if (is_array($value)) {
        $clean = [];
        $count = 0;
        foreach ($value as $childKey => $childValue) {
            if (++$count > 100) {
                $clean['_truncated'] = true;
                break;
            }
            $clean[(string) $childKey] = logging_sanitize_value($childValue, (string) $childKey, $depth + 1);
        }
        return $clean;
    }
    if (is_object($value)) {
        return '[OBJECT:' . get_class($value) . ']';
    }
    if (is_string($value)) {
        // شناسه‌های ساختاری UUID هستند، نه متن آزاد؛ پالایش عمومی ارقام نباید آن‌ها را بشکند.
        if (in_array($key, ['event_id', 'request_id', 'trace_id', 'operation_id', 'parent_event_id'], true)) {
            $identifier = trim($value);
            return logging_is_uuid_v4($identifier) ? strtolower($identifier) : '[INVALID_UUID]';
        }
        return logging_sanitize_text($value, 2000);
    }
    if (is_float($value) && (!is_finite($value))) {
        return null;
    }
    if (is_resource($value)) {
        return '[RESOURCE]';
    }
    return is_scalar($value) || $value === null ? $value : '[UNSUPPORTED]';
}

function logging_safe_request_uri(): ?string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    if ($uri === '') {
        return null;
    }
    $path = (string) (parse_url($uri, PHP_URL_PATH) ?: '/');
    return substr(logging_sanitize_text($path, 255), 0, 255);
}

function logging_spool_write(string $stream, array $payload): bool
{
    // نام فایل صف فقط از جریان‌های شناخته‌شده ساخته می‌شود؛ ورودی دلخواه نباید مسیر فایل را کنترل کند.
    if (!in_array($stream, ['system', 'activity', 'ticket_event'], true)) {
        logging_emergency_failure('spool_stream_rejected');
        return false;
    }

    $dir = APP_ROOT . '/storage/logs/spool';
    try {
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('spool directory unavailable');
        }
        $day = date('Ymd');
        $path = $dir . DIRECTORY_SEPARATOR . $stream . '-' . $day . '.jsonl';
        $record = [
            'version' => 1,
            'stream' => $stream,
            'queued_at' => date('c'),
            'attempts' => 0,
            'payload' => logging_sanitize_value($payload),
        ];
        $json = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if (!is_string($json)) {
            throw new RuntimeException('spool JSON encoding failed');
        }
        // قفل مشترک نویسنده/Worker برای امکان بازنویسی اتمیک فایل در Windows و Linux.
        $writerLock = @fopen($dir . DIRECTORY_SEPARATOR . '.writer.lock', 'c');
        if ($writerLock === false || !flock($writerLock, LOCK_EX)) {
            if (is_resource($writerLock)) fclose($writerLock);
            throw new RuntimeException('spool writer lock unavailable');
        }
        $queueFiles = array_merge(glob($dir . DIRECTORY_SEPARATOR . '*.jsonl') ?: [], glob($dir . DIRECTORY_SEPARATOR . '*.jsonl.processing') ?: []);
        $queueBytes = 0;
        foreach ($queueFiles as $queuedFile) {
            $queuedSize = @filesize($queuedFile);
            if ($queuedSize === false) {
                flock($writerLock, LOCK_UN);
                fclose($writerLock);
                throw new RuntimeException('spool_capacity_unknown');
            }
            $queueBytes += (int) $queuedSize;
        }
        $freeBytes = @disk_free_space($dir);
        if (count($queueFiles) >= LOGGING_SPOOL_MAX_FILES
            || $queueBytes + strlen($json) + 1 > LOGGING_SPOOL_MAX_BYTES
            || ($freeBytes !== false && $freeBytes < LOGGING_SPOOL_MIN_FREE_BYTES)) {
            flock($writerLock, LOCK_UN);
            fclose($writerLock);
            throw new RuntimeException('spool_capacity_exceeded');
        }
        $handle = @fopen($path, 'ab');
        if ($handle === false) {
            flock($writerLock, LOCK_UN);
            fclose($writerLock);
            throw new RuntimeException('spool file unavailable');
        }
        @chmod($path, 0640);
        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('spool lock unavailable');
            }
            $offset = ftell($handle);
            if (!is_int($offset) || $offset < 0) {
                throw new RuntimeException('spool offset unavailable');
            }
            $data = $json . PHP_EOL;
            $length = strlen($data);
            $writtenTotal = 0;
            while ($writtenTotal < $length) {
                $written = fwrite($handle, substr($data, $writtenTotal));
                if ($written === false || $written === 0) {
                    break;
                }
                $writtenTotal += $written;
            }
            if ($writtenTotal !== $length || !fflush($handle)) {
                // خط ناقص را تا offset قبلی برمی‌گردانیم تا Worker JSON نیمه‌کاره نخواند.
                if (ftruncate($handle, $offset)) {
                    fflush($handle);
                }
                throw new RuntimeException('spool short write');
            }
            if (function_exists('fsync')) { @fsync($handle); }
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
            flock($writerLock, LOCK_UN);
            fclose($writerLock);
        }
        logging_spool_stats_cache_invalidate();
        return true;
    } catch (Throwable $exception) {
        logging_emergency_failure('spool_write_failed', $exception->getMessage());
        return false;
    }
}

function logging_spool_stats_cache_invalidate(): void
{
    $path = APP_ROOT . '/storage/logs/spool/.stats-cache.json';
    if (is_file($path)) @unlink($path);
}

function logging_spool_stats(): array
{
    $dir = APP_ROOT . '/storage/logs/spool';
    $cachePath = $dir . DIRECTORY_SEPARATOR . '.stats-cache.json';
    if (is_file($cachePath)) {
        $cached = json_decode((string) @file_get_contents($cachePath), true);
        if (is_array($cached) && (int) ($cached['_cached_at'] ?? 0) >= time() - 5) {
            unset($cached['_cached_at']);
            return $cached;
        }
    }
    $stats = logging_spool_stats_uncached();
    if (!is_dir($dir)) @mkdir($dir, 0750, true);
    if (is_dir($dir)) {
        $payload = $stats + ['_cached_at' => time()];
        @file_put_contents($cachePath, json_encode($payload, JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
    return $stats;
}

function logging_spool_stats_uncached(): array
{
    $dir = APP_ROOT . '/storage/logs/spool';
    $files = [];
    if (is_dir($dir)) {
        $files = array_merge(glob($dir . DIRECTORY_SEPARATOR . '*.jsonl') ?: [], glob($dir . DIRECTORY_SEPARATOR . '*.jsonl.processing') ?: []);
    }
    $bytes = 0;
    $oldest = null;
    $uncertain = false;
    $processing = 0;
    foreach ($files as $file) {
        if (str_ends_with($file, '.jsonl.processing')) $processing++;
        clearstatcache(true, $file);
        $size = @filesize($file);
        if ($size === false) $uncertain = true; else $bytes += (int) $size;
        $mtime = @filemtime($file);
        $fileOldest = false;
        $handle = @fopen($file, 'rb');
        if ($handle === false) {
            $uncertain = true;
        } else {
            while (($line = fgets($handle)) !== false) {
                if (trim($line) === '') continue;
                $record = json_decode($line, true);
                if (!is_array($record) || empty($record['queued_at'])) {
                    $uncertain = true;
                    continue;
                }
                $parsed = strtotime((string) $record['queued_at']);
                if ($parsed === false) {
                    $uncertain = true;
                    continue;
                }
                if ($fileOldest === false || $parsed < $fileOldest) $fileOldest = $parsed;
            }
            if (!feof($handle)) $uncertain = true;
            fclose($handle);
        }
        $candidate = $fileOldest !== false ? $fileOldest : $mtime;
        if ($candidate !== false && ($oldest === null || $candidate < $oldest)) $oldest = $candidate;
    }
    $rewriteTemps = is_dir($dir) ? (glob($dir . DIRECTORY_SEPARATOR . '*.rewrite.tmp') ?: []) : [];
    $deadFiles = is_dir($dir . DIRECTORY_SEPARATOR . 'dead-letter')
        ? (glob($dir . DIRECTORY_SEPARATOR . 'dead-letter' . DIRECTORY_SEPARATOR . '*.jsonl') ?: []) : [];
    $deadBytes = 0;
    foreach ($deadFiles as $deadFile) {
        $deadSize = @filesize($deadFile);
        if ($deadSize === false) $uncertain = true; else $deadBytes += (int) $deadSize;
    }
    return [
        'files' => count($files),
        'processing_files' => $processing,
        'rewrite_temp_files' => count($rewriteTemps),
        'bytes' => $bytes,
        'dead_letter_files' => count($deadFiles),
        'dead_letter_bytes' => $deadBytes,
        'oldest_at' => $oldest,
        'age_seconds' => $oldest !== null ? max(0, time() - $oldest) : 0,
        'state' => $uncertain ? 'unknown' : 'known',
    ];
}

function logging_increment_counter(string $name): void
{
    if (!in_array($name, ['fallback_total', 'dropped_total', 'throttled_total', 'schema_replay_error_total', 'unknown_replay_error_total'], true)) {
        return;
    }
    $path = APP_ROOT . '/storage/logs/logging-health.json';
    try {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('health directory unavailable');
        }
        $handle = @fopen($path, 'c+');
        if ($handle === false) {
            throw new RuntimeException('health counter unavailable');
        }
        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('health counter lock unavailable');
            }
            rewind($handle);
            $raw = stream_get_contents($handle);
            $counts = json_decode((string) $raw, true);
            $counterValid = is_array($counts) && json_last_error() === JSON_ERROR_NONE
                && (array_key_exists('fallback_total', $counts) || array_key_exists('dropped_total', $counts) || array_key_exists('throttled_total', $counts) || array_key_exists('schema_replay_error_total', $counts) || array_key_exists('unknown_replay_error_total', $counts))
                && (!isset($counts['fallback_total']) || is_numeric($counts['fallback_total']))
                && (!isset($counts['dropped_total']) || is_numeric($counts['dropped_total']))
                && (!isset($counts['throttled_total']) || is_numeric($counts['throttled_total']))
                && (!isset($counts['schema_replay_error_total']) || is_numeric($counts['schema_replay_error_total']))
                && (!isset($counts['unknown_replay_error_total']) || is_numeric($counts['unknown_replay_error_total']));
            if (trim((string) $raw) !== '' && !$counterValid) {
                flock($handle, LOCK_UN);
                logging_emergency_failure('health_counter_corrupt');
                return; // دادهٔ خراب را overwrite نمی‌کنیم؛ صفر ساختگی تولید نمی‌شود.
            }
            if (!is_array($counts)) $counts = [];
            $counts[$name] = (int) ($counts[$name] ?? 0) + 1;
            $counts['updated_at'] = date('c');
            if ($name === 'dropped_total') $counts['dropped_last_at'] = date('c');
            if ($name === 'throttled_total') $counts['throttled_last_at'] = date('c');
            if ($name === 'schema_replay_error_total') $counts['schema_replay_last_at'] = date('c');
            if ($name === 'unknown_replay_error_total') $counts['unknown_replay_last_at'] = date('c');
            $json = json_encode($counts, JSON_UNESCAPED_SLASHES);
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, is_string($json) ? $json : '{}');
            fflush($handle);
            if (function_exists('fsync')) { @fsync($handle); }
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    } catch (Throwable) {
        error_log('[LOGGING_FAILURE] counter_write_failed ' . date('c'));
    }
}

function logging_health_counters(): array
{
    $path = APP_ROOT . '/storage/logs/logging-health.json';
    if (!is_file($path)) {
        return ['fallback_total' => 0, 'dropped_total' => 0, 'throttled_total' => 0, 'schema_replay_error_total' => 0, 'unknown_replay_error_total' => 0, 'schema_replay_last_at' => null, 'unknown_replay_last_at' => null, 'dropped_last_at' => null, 'throttled_last_at' => null, 'state' => 'not_initialized'];
    }
    $raw = @file_get_contents($path);
    if (!is_string($raw)) {
        return ['fallback_total' => null, 'dropped_total' => null, 'throttled_total' => null, 'schema_replay_error_total' => null, 'unknown_replay_error_total' => null, 'schema_replay_last_at' => null, 'unknown_replay_last_at' => null, 'dropped_last_at' => null, 'throttled_last_at' => null, 'state' => 'unreadable'];
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE
        || (!array_key_exists('fallback_total', $data) && !array_key_exists('dropped_total', $data) && !array_key_exists('throttled_total', $data) && !array_key_exists('schema_replay_error_total', $data) && !array_key_exists('unknown_replay_error_total', $data))
        || (isset($data['fallback_total']) && !is_numeric($data['fallback_total']))
        || (isset($data['dropped_total']) && !is_numeric($data['dropped_total']))
        || (isset($data['throttled_total']) && !is_numeric($data['throttled_total']))
        || (isset($data['schema_replay_error_total']) && !is_numeric($data['schema_replay_error_total']))
        || (isset($data['unknown_replay_error_total']) && !is_numeric($data['unknown_replay_error_total']))) {
        return ['fallback_total' => null, 'dropped_total' => null, 'throttled_total' => null, 'schema_replay_error_total' => null, 'unknown_replay_error_total' => null, 'schema_replay_last_at' => null, 'unknown_replay_last_at' => null, 'dropped_last_at' => null, 'throttled_last_at' => null, 'state' => 'corrupt'];
    }
    return [
        'fallback_total' => max(0, (int) ($data['fallback_total'] ?? 0)),
        'dropped_total' => max(0, (int) ($data['dropped_total'] ?? 0)),
        'throttled_total' => max(0, (int) ($data['throttled_total'] ?? 0)),
        'schema_replay_error_total' => max(0, (int) ($data['schema_replay_error_total'] ?? 0)),
        'unknown_replay_error_total' => max(0, (int) ($data['unknown_replay_error_total'] ?? 0)),
        'dropped_last_at' => isset($data['dropped_last_at']) ? (string) $data['dropped_last_at'] : null,
        'throttled_last_at' => isset($data['throttled_last_at']) ? (string) $data['throttled_last_at'] : null,
        'schema_replay_last_at' => isset($data['schema_replay_last_at']) ? (string) $data['schema_replay_last_at'] : null,
        'unknown_replay_last_at' => isset($data['unknown_replay_last_at']) ? (string) $data['unknown_replay_last_at'] : null,
        'state' => 'known',
    ];
}

/** جایگزینی فایل وضعیت با بازیابی محافظه‌کارانه برای تفاوت rename در Windows/POSIX. */
function logging_atomic_replace_file(string $temporaryPath, string $destinationPath): bool
{
    if (!is_file($temporaryPath) || dirname($temporaryPath) !== dirname($destinationPath)) return false;
    if (PHP_OS_FAMILY !== 'Windows') return @rename($temporaryPath, $destinationPath);

    // بازیابی جایگزینی که پردازهٔ قبلی بعد از انتقال مقصد به backup متوقف کرده است.
    if (!is_file($destinationPath)) {
        $staleBackups = glob($destinationPath . '.replace-*.bak') ?: [];
        rsort($staleBackups, SORT_STRING);
        if ($staleBackups !== []) @rename($staleBackups[0], $destinationPath);
    }
    $backupPath = $destinationPath . '.replace-' . bin2hex(random_bytes(4)) . '.bak';
    $hadDestination = is_file($destinationPath);
    if ($hadDestination && !@rename($destinationPath, $backupPath)) return false;
    if (!@rename($temporaryPath, $destinationPath)) {
        if ($hadDestination && is_file($backupPath)) @rename($backupPath, $destinationPath);
        return false;
    }
    if ($hadDestination) @unlink($backupPath);
    return true;
}

/**
 * Persist worker lifecycle outside the database so a DB outage cannot erase heartbeat evidence.
 * State is one of running/success/partial/failure; details must contain counters, not payloads.
 */
function logging_worker_heartbeat(string $worker, string $state, array $details = []): bool
{
    $worker = strtolower(trim($worker));
    $worker = preg_replace('/[^a-z0-9_.-]/', '-', $worker) ?: '';
    if ($worker === '' || !in_array($state, ['running', 'success', 'partial', 'failure'], true)) return false;
    $directory = APP_ROOT . '/storage/logs/worker-heartbeats';
    if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) return false;
    $path = $directory . '/' . $worker . '.json';
    $current = [];
    if (is_file($path)) {
        $decoded = json_decode((string) @file_get_contents($path), true);
        if (is_array($decoded)) $current = $decoded;
    }
    $now = date('c');
    $current['worker'] = $worker;
    $current['state'] = $state;
    $current['updated_at'] = $now;
    $current['pid'] = getmypid() ?: 0;
    $current['host'] = substr((string) gethostname(), 0, 120);
    if ($state === 'running') $current['last_started_at'] = $now;
    if ($state !== 'running') $current['last_finished_at'] = $now;
    if ($state === 'success') $current['last_success_at'] = $now;
    $safeDetails = [];
    foreach ($details as $key => $value) {
        if (!preg_match('/^[a-z][a-z0-9_]{0,39}$/', (string) $key)) continue;
        if (is_int($value) || is_float($value) || is_bool($value) || $value === null) $safeDetails[$key] = $value;
        elseif (is_string($value)) $safeDetails[$key] = substr(logging_sanitize_text($value, 120), 0, 120);
    }
    $current['details'] = $safeDetails;
    $json = json_encode($current, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if (!is_string($json)) return false;
    $tmp = $path . '.' . bin2hex(random_bytes(5)) . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    @chmod($tmp, 0640);
    if (!logging_atomic_replace_file($tmp, $path)) { @unlink($tmp); return false; }
    return true;
}

function logging_emergency_failure(string $code, string $detail = ''): void
{
    // مسیر مستقل از DB؛ فقط کد ثابت و زمان نوشته می‌شود تا دادهٔ حساس وارد گزارش اضطراری نشود.
    $safeCode = preg_replace('/[^A-Za-z0-9_.-]/', '_', $code) ?: 'unknown';
    $line = '[LOGGING_FAILURE] ' . $safeCode . ' ' . date('c') . PHP_EOL;
    error_log(rtrim($line));
    $GLOBALS['__logging_last_failure'] = $safeCode;
    if (!defined('APP_ROOT')) return;
    $dir = APP_ROOT . '/storage/logs';
    $lock = null;
    try {
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) return;
        $lock = @fopen($dir . DIRECTORY_SEPARATOR . '.emergency.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) return;
        $path = $dir . DIRECTORY_SEPARATOR . 'emergency.log';
        $size = is_file($path) ? (int) @filesize($path) : 0;
        if ($size >= 5 * 1024 * 1024) {
            // چهار فایل چرخشی نگه می‌داریم تا مسیر مستقل هم باعث پرشدن نامحدود دیسک نشود.
            @unlink($path . '.4');
            for ($i = 3; $i >= 1; $i--) {
                if (is_file($path . '.' . $i)) @rename($path . '.' . $i, $path . '.' . ($i + 1));
            }
            if (is_file($path) && !@rename($path, $path . '.1')) {
                logging_emergency_failure_rotation_warning();
            }
        }
        $handle = @fopen($path, 'ab');
        if ($handle !== false) {
            try {
                @chmod($path, 0640);
                $written = fwrite($handle, $line);
                if ($written === strlen($line)) {
                    fflush($handle);
                    if (function_exists('fsync')) @fsync($handle);
                }
            } finally {
                fclose($handle);
            }
        }
    } catch (Throwable) {
        // error_log remains the final fallback when local emergency storage is unavailable.
    } finally {
        if (is_resource($lock)) {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }
    }
}

function logging_emergency_failure_rotation_warning(): void
{
    error_log('[LOGGING_FAILURE] emergency_log_rotation_failed ' . date('c'));
}

/** وضعیت آخرین تلاش پایدارسازی در همین درخواست: durable_db / durable_spool / lost_unconfirmed. */
function logging_persistence_status(): string
{
    return (string) ($GLOBALS['__logging_persistence_status'] ?? 'unknown');
}

function logging_record_failure(string $stream, array $payload): bool
{
    $payload['event_id'] = logging_is_uuid((string) ($payload['event_id'] ?? '')) ? (string) $payload['event_id'] : logging_uuid();
    $payload['request_id'] = logging_is_uuid_v4((string) ($payload['request_id'] ?? '')) ? (string) $payload['request_id'] : logging_request_id();
    $payload['trace_id'] = logging_is_uuid_v4((string) ($payload['trace_id'] ?? '')) ? (string) $payload['trace_id'] : logging_trace_id();
    $payload['operation_id'] = logging_is_uuid_v4((string) ($payload['operation_id'] ?? '')) ? (string) $payload['operation_id'] : logging_operation_id();
    $payload['parent_event_id'] = logging_is_uuid_v4((string) ($payload['parent_event_id'] ?? '')) ? (string) $payload['parent_event_id'] : logging_parent_event_id();
    $payload['created_at'] = (string) ($payload['created_at'] ?? date('Y-m-d H:i:s'));
    logging_increment_counter('fallback_total');
    if (!logging_spool_write($stream, $payload)) {
        $GLOBALS['__logging_persistence_status'] = 'lost_unconfirmed';
        logging_increment_counter('dropped_total');
        logging_emergency_failure('event_dropped', $stream);
        return false;
    }
    $GLOBALS['__logging_persistence_status'] = 'durable_spool';
    return true;
}

function logging_json(array $value): ?string
{
    $json = json_encode(logging_sanitize_value($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    return is_string($json) ? substr($json, 0, 60000) : null;
}


function logging_append_legacy_file(string $line): bool
{
    $dir = APP_ROOT . '/storage/logs';
    try {
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            return false;
        }
        $path = $dir . DIRECTORY_SEPARATOR . 'app.log';
        clearstatcache(true, $path);
        if (is_file($path)) {
            $mtime = (int) @filemtime($path);
            $dailyRotationDue = $mtime > 0 && date('Ymd', $mtime) < date('Ymd');
            $sizeRotationDue = (int) @filesize($path) >= 10 * 1024 * 1024;
            if ($dailyRotationDue || $sizeRotationDue) {
                $rotated = $dir . DIRECTORY_SEPARATOR . 'app-' . ($dailyRotationDue ? date('Ymd', $mtime) : date('Ymd')) . '-' . date('His') . '.log';
                if (@rename($path, $rotated)) {
                    $archives = glob($dir . DIRECTORY_SEPARATOR . 'app-*.log') ?: [];
                    rsort($archives, SORT_STRING);
                    foreach (array_slice($archives, 14) as $old) @unlink($old);
                }
            }
        }
        $handle = @fopen($path, 'ab');
        if ($handle === false) {
            return false;
        }
        try {
            if (!flock($handle, LOCK_EX)) {
                return false;
            }
            $data = $line . PHP_EOL;
            $written = fwrite($handle, $data);
            fflush($handle);
            if (function_exists('fsync')) { @fsync($handle); }
            flock($handle, LOCK_UN);
            return $written === strlen($data);
        } finally {
            fclose($handle);
        }
    } catch (Throwable) {
        return false;
    }
}

/** درج idempotent رخداد بازپخش‌شده؛ فقط جدول‌های ثابت مجازند. */
function logging_replay_insert(string $table, string $sql, array $params, string $eventId): bool
{
    if (!in_array($table, ['system_logs', 'activity_logs', 'ticket_events'], true)
        || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $eventId)
        || !preg_match('/^\s*INSERT\s+INTO\s+' . preg_quote($table, '/') . '\s*\(/i', $sql)) {
        throw new InvalidArgumentException('Invalid replay target or event identifier.');
    }
    try {
        db()->prepare($sql)->execute($params);
        return true;
    } catch (PDOException $exception) {
        if ((string) $exception->getCode() !== '23000') throw $exception;
        $check = db()->prepare('SELECT 1 FROM `' . $table . '` WHERE event_id = ? LIMIT 1');
        $check->execute([$eventId]);
        if (!$check->fetchColumn()) throw $exception;
        return false;
    }
}

/** ثبت/به‌روزرسانی Issueهای تکرارشونده؛ شکست Aggregator نباید ثبت رخداد اصلی را مختل کند. */
function logging_issue_upsert(array $event, bool $sendNotification = true): bool
{
    $severity = (string) ($event['level'] ?? 'error');
    if (!in_array($severity, ['warning', 'error', 'critical'], true)) return true;
    $category = (string) ($event['category'] ?? 'system');
    $code = (string) ($event['code'] ?? 'SYS-LEGACY-001');
    $context = logging_sanitize_text((string) ($event['context'] ?? 'system'), 120);
    $rawMessage = logging_sanitize_text((string) ($event['message'] ?? 'رخداد فنی'), 2000);
    $title = logging_utf8_substr($rawMessage, 0, 255);
    $fingerprint = logging_fingerprint_current($code, $context, $rawMessage);
    $eventId = (string) ($event['event_id'] ?? '');
    if (!logging_is_uuid($eventId)) return false;
    $eventTime = (string) ($event['created_at'] ?? date('Y-m-d H:i:s'));
    $pdo = null;
    $ownsTransaction = false;
    $savepoint = null;
    try {
        if (logging_db_circuit_should_skip()) return false;
        $pdo = db();
        $ownsTransaction = !$pdo->inTransaction();
        if ($ownsTransaction) {
            $pdo->beginTransaction();
        } else {
            $savepoint = 'logging_issue_' . substr($fingerprint, 0, 16);
            if ($pdo->exec('SAVEPOINT ' . $savepoint) === false) throw new RuntimeException('issue aggregation savepoint failed');
        }
        $dedupe = $pdo->prepare('INSERT IGNORE INTO log_issue_events (event_id, fingerprint, created_at) VALUES (?, ?, ?)');
        $dedupe->execute([$eventId, $fingerprint, $eventTime]);
        if ($dedupe->rowCount() === 0) {
            if ($ownsTransaction && $pdo->inTransaction()) $pdo->commit();
            elseif ($savepoint !== null) $pdo->exec('RELEASE SAVEPOINT ' . $savepoint);
            return true;
        }
        $sql = "INSERT INTO log_issues (fingerprint, code, category, context, severity, title, first_seen, last_seen, occurrences, last_event_id, last_request_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, 'open')
                ON DUPLICATE KEY UPDATE occurrences = occurrences + 1,
                    first_seen = LEAST(first_seen, VALUES(first_seen)), last_seen = GREATEST(last_seen, VALUES(last_seen)),
                    last_event_id = VALUES(last_event_id), last_request_id = VALUES(last_request_id),
                    context = VALUES(context),
                    severity = CASE WHEN FIELD(VALUES(severity),'debug','info','notice','warning','error','critical') > FIELD(severity,'debug','info','notice','warning','error','critical') THEN VALUES(severity) ELSE severity END,
                    resolution_note = IF(status = 'resolved', NULL, resolution_note),
                    status = IF(status = 'resolved', 'open', status)";
        $pdo->prepare($sql)->execute([$fingerprint, $code, $category, $context, $severity, $title, $eventTime, $eventTime,
            $eventId, (string) ($event['request_id'] ?? logging_request_id())]);
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->commit();
        elseif ($savepoint !== null) $pdo->exec('RELEASE SAVEPOINT ' . $savepoint);
        logging_db_circuit_success();

        // تحویل اعلان‌ها مستقل از ثبت رخداد و توسط Worker صف پایدار انجام می‌شود.
        return true;
    } catch (Throwable $exception) {
        if (logging_db_error_is_transient($exception)) logging_db_circuit_failure($exception);
        if ($ownsTransaction && $pdo instanceof PDO && $pdo->inTransaction()) {
            try { $pdo->rollBack(); } catch (Throwable) {}
        } elseif ($savepoint !== null && $pdo instanceof PDO && $pdo->inTransaction()) {
            try { $pdo->exec('ROLLBACK TO SAVEPOINT ' . $savepoint); $pdo->exec('RELEASE SAVEPOINT ' . $savepoint); } catch (Throwable) {}
        }
        logging_emergency_failure('issue_aggregation_failed', get_class($exception));
        return false;
    }
}

/** فقط خطاهای اتصال/قفل گذرا Circuit Breaker را باز می‌کنند؛ خطای SQL یا داده نباید DB سالم را Open کند. */
function logging_db_error_is_transient(Throwable $exception): bool
{
    $state = strtoupper((string) $exception->getCode());
    if (str_starts_with($state, '08') || $state === '40001') return true;
    $native = null;
    if ($exception instanceof PDOException && is_array($exception->errorInfo ?? null)) $native = (int) ($exception->errorInfo[1] ?? 0);
    if (in_array($native, [1040, 1205, 1213, 2002, 2003, 2006, 2013, 2055], true)) return true;
    $message = strtolower($exception->getMessage());
    foreach (['connection refused','connection timed out','connect timeout','server has gone away','lost connection','too many connections','deadlock found','lock wait timeout','simulated database outage','database outage','database unavailable','could not find driver'] as $needle) {
        if (str_contains($message, $needle)) return true;
    }
    return false;
}

/** طبقه‌بندی خطاهای بازپخش؛ فقط خطای دادهٔ قطعی attempt مصرف می‌کند. */
function logging_replay_error_classify(Throwable $exception): string
{
    if ($exception instanceof JsonException || $exception instanceof UnexpectedValueException) return 'malformed';
    if (logging_db_error_is_transient($exception)) return 'transient';

    $native = null;
    $state = strtoupper((string) $exception->getCode());
    if ($exception instanceof PDOException && is_array($exception->errorInfo ?? null)) {
        $native = (int) ($exception->errorInfo[1] ?? 0);
        $state = strtoupper((string) ($exception->errorInfo[0] ?? $state));
    }
    if (in_array($native, [1054, 1146, 1451, 1452], true) || in_array($state, ['42S22', '42S02'], true)) return 'schema';
    if (in_array($native, [1048, 1062, 1264, 1265, 1292, 1366, 1406, 3819], true)
        || in_array($state, ['22001', '22003', '22007', '22008', '22018'], true)) return 'permanent_data';
    return 'unknown';
}

/** Circuit breaker محلی و مستقل از دیتابیس؛ از اتصال‌های متوالی هنگام outage جلوگیری می‌کند. */
function logging_db_circuit_path(): string
{
    return APP_ROOT . '/storage/logs/db-circuit.json';
}

function logging_db_circuit_release_probe(): void
{
    $lock = $GLOBALS['__logging_db_probe_lock'] ?? null;
    if (is_resource($lock)) { @flock($lock, LOCK_UN); @fclose($lock); }
    unset($GLOBALS['__logging_db_probe_lock']);
}

function logging_db_circuit_with_lock(callable $callback): mixed
{
    $path = logging_db_circuit_path();
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) return null;
    $lock = @fopen($path . '.lock', 'c');
    if ($lock === false || !@flock($lock, LOCK_EX)) {
        if (is_resource($lock)) fclose($lock);
        return null;
    }
    try { return $callback($path); }
    finally { @flock($lock, LOCK_UN); fclose($lock); }
}

function logging_db_circuit_should_skip(): bool
{
    if (is_resource($GLOBALS['__logging_db_probe_lock'] ?? null)) return false;
    $state = logging_db_circuit_with_lock(static function (string $path): array {
        if (!is_file($path)) return [];
        $raw = @file_get_contents($path);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        return is_array($decoded) ? $decoded : ['state' => 'open', 'open_until' => time() + 5];
    });
    // در صورت ناتوانی از خواندن state، برای جلوگیری از طوفان اتصال DB موقتاً fail-closed عمل می‌کنیم.
    if (!is_array($state)) return true;
    if ((string) ($state['state'] ?? '') !== 'open') return false;
    if ((int) ($state['open_until'] ?? 0) > time()) return true;
    // پس از پایان زمان انتظار، فقط یک پردازه اجازهٔ Probe دارد.
    $lock = @fopen(logging_db_circuit_path() . '.probe.lock', 'c');
    if ($lock === false || !@flock($lock, LOCK_EX | LOCK_NB)) {
        if (is_resource($lock)) fclose($lock);
        return true;
    }
    $GLOBALS['__logging_db_probe_lock'] = $lock;
    return false;
}

function logging_db_circuit_failure(Throwable $exception): void
{
    try { logging_db_circuit_with_lock(static function (string $path) use ($exception): void {
        $state = is_file($path) ? json_decode((string) @file_get_contents($path), true) : [];
        if (!is_array($state)) $state = [];
        $failures = min(10, (int) ($state['failures'] ?? 0) + 1);
        $delay = min(60, 5 * (2 ** min(4, $failures - 1)));
        $next = ['state'=>'open','failures'=>$failures,'open_until'=>time()+$delay,'last_failure_at'=>date('c'),'last_error_type'=>get_class($exception)];
        $json = json_encode($next, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        // تمام خواندن/نوشتن/حذف زیر همان قفل انجام می‌شود؛ خواننده JSON نیمه‌نوشته نمی‌بیند.
        $written = @file_put_contents($path, $json, LOCK_EX);
        if ($written !== strlen($json)) throw new RuntimeException('circuit state write failed');
        @chmod($path, 0640);
    }); } catch (Throwable) { /* emergency path must not mask the original DB exception */ }
    logging_db_circuit_release_probe();
}

function logging_db_circuit_success(): void
{
    try { logging_db_circuit_with_lock(static function (string $path): void {
        if (is_file($path) && !@unlink($path)) throw new RuntimeException('circuit state reset failed');
    }); } catch (Throwable) { /* do not mask a successful DB write */ }
    logging_db_circuit_release_probe();
}

/** A unique Event ID already persisted is an idempotent duplicate, not a spool failure. */
function logging_duplicate_event_already_stored(PDO $pdo, string $table, string $eventId, Throwable $exception): bool
{
    if (!in_array($table, ['system_logs', 'activity_logs'], true)) return false;
    $isDuplicate = (string) $exception->getCode() === '23000';
    if ($exception instanceof PDOException && is_array($exception->errorInfo ?? null)) {
        $isDuplicate = $isDuplicate || (int) ($exception->errorInfo[1] ?? 0) === 1062;
    }
    if (!$isDuplicate || !logging_is_uuid($eventId)) return false;
    try {
        $check = $pdo->prepare('SELECT 1 FROM ' . $table . ' WHERE event_id = ? LIMIT 1');
        $check->execute([$eventId]);
        return (bool) $check->fetchColumn();
    } catch (Throwable) {
        return false;
    }
}

function logging_store_system(array $event): bool
{
    $event['event_id'] = logging_is_uuid((string) ($event['event_id'] ?? '')) ? (string) $event['event_id'] : logging_uuid();
    $event['request_id'] = logging_is_uuid_v4((string) ($event['request_id'] ?? '')) ? (string) $event['request_id'] : logging_request_id();
    $event['trace_id'] = logging_is_uuid_v4((string) ($event['trace_id'] ?? '')) ? (string) $event['trace_id'] : logging_trace_id();
    $event['operation_id'] = logging_is_uuid_v4((string) ($event['operation_id'] ?? '')) ? (string) $event['operation_id'] : logging_operation_id();
    $event['parent_event_id'] = logging_is_uuid_v4((string) ($event['parent_event_id'] ?? '')) ? (string) $event['parent_event_id'] : logging_parent_event_id();
    $event['created_at'] = (string) ($event['created_at'] ?? date('Y-m-d H:i:s'));
    $event['message'] = logging_sanitize_text((string) ($event['message'] ?? ''), 2000);
    $event['context'] = logging_sanitize_text((string) ($event['context'] ?? 'system'), 120);
    $event['request_uri'] = logging_safe_request_uri();
    $event['meta_json'] = logging_json((array) ($event['meta'] ?? []));
    $event['fingerprint'] = (string) ($event['fingerprint'] ?? logging_fingerprint_current((string) ($event['code'] ?? 'SYS-LEGACY-001'), $event['context'], $event['message']));
    try {
        if (logging_db_circuit_should_skip()) {
            logging_record_failure('system', $event);
            if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
            return false;
        }
        $pdo = db();
        if ($pdo->inTransaction()) {
            logging_record_failure('system', $event);
            if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
            return false;
        }
        $pdo->prepare('INSERT INTO system_logs (event_id, request_id, trace_id, operation_id, parent_event_id, code, category, fingerprint, level, context, message, meta_json, user_id, request_uri, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([
            $event['event_id'], $event['request_id'], $event['trace_id'], $event['operation_id'], $event['parent_event_id'], $event['code'] ?? null, $event['category'] ?? 'system', $event['fingerprint'],
            $event['level'] ?? 'error', $event['context'], $event['message'], $event['meta_json'],
            isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null, $event['request_uri'], $event['created_at'],
        ]);
        logging_db_circuit_success();
        $GLOBALS['__logging_persistence_status'] = 'durable_db';
        logging_issue_upsert($event);
        if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
        return true;
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && logging_duplicate_event_already_stored($pdo, 'system_logs', (string) $event['event_id'], $exception)) {
            $GLOBALS['__logging_persistence_status'] = 'durable_db';
            if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
            return false;
        }
        if (logging_db_error_is_transient($exception)) logging_db_circuit_failure($exception);
        logging_record_failure('system', $event);
        if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
        return false;
    }
}

/** پاک‌سازی نهایی فیلدهای فعالیت در مرز ذخیره‌سازی؛ مستقل از پاک‌سازی فراخواننده. */
function logging_normalize_activity_event(array $event): array
{
    $limits = [
        'username' => 190,
        'full_name' => 190,
        'role' => 40,
        'action_code' => 120,
        'action_label' => 190,
        'module' => 80,
        'target_type' => 40,
        'target_label' => 255,
        'computer_name' => 190,
        'hostname' => 190,
        'user_agent' => 255,
    ];
    foreach ($limits as $field => $limit) {
        if (array_key_exists($field, $event) && $event[$field] !== null) {
            $event[$field] = logging_sanitize_text((string) $event[$field], $limit);
        }
    }

    $result = (string) ($event['result'] ?? 'unknown');
    $event['result'] = in_array($result, ['success', 'failure', 'denied', 'unknown'], true) ? $result : 'unknown';

    $ip = trim((string) ($event['ip_address'] ?? ''));
    $event['ip_address'] = $ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    $event['user_id'] = isset($event['user_id']) && (int) $event['user_id'] > 0 ? (int) $event['user_id'] : null;
    $event['target_id'] = isset($event['target_id']) ? (int) $event['target_id'] : null;
    $event['meta_json'] = logging_json(is_array($event['meta'] ?? null) ? $event['meta'] : []);
    return $event;
}

function logging_store_activity(array $event): bool
{
    $event['event_id'] = logging_is_uuid((string) ($event['event_id'] ?? '')) ? (string) $event['event_id'] : logging_uuid();
    $event['request_id'] = logging_is_uuid_v4((string) ($event['request_id'] ?? '')) ? (string) $event['request_id'] : logging_request_id();
    $event['trace_id'] = logging_is_uuid_v4((string) ($event['trace_id'] ?? '')) ? (string) $event['trace_id'] : logging_trace_id();
    $event['operation_id'] = logging_is_uuid_v4((string) ($event['operation_id'] ?? '')) ? (string) $event['operation_id'] : logging_operation_id();
    $event['parent_event_id'] = logging_is_uuid_v4((string) ($event['parent_event_id'] ?? '')) ? (string) $event['parent_event_id'] : logging_parent_event_id();
    $event['created_at'] = (string) ($event['created_at'] ?? date('Y-m-d H:i:s'));
    $transactional = false;
    try {
        // اگر DB قبلاً در همین درخواست باز شده، وضعیت تراکنش را بدون ایجاد اتصال جدید بخوان.
        $knownPdo = $GLOBALS['__app_pdo_instance'] ?? null;
        $transactional = $knownPdo instanceof PDO && $knownPdo->inTransaction();
        if (logging_db_circuit_should_skip()) {
            $event = logging_normalize_activity_event($event);
            if ($transactional && (string) ($event['result'] ?? 'unknown') === 'success') {
                // A success event must never escape its business transaction through an independent spool.
                // Fail closed so save_audit_required() aborts the caller's transaction instead of creating false success.
                throw new RuntimeException('Successful audit cannot be spooled independently while a business transaction is active.');
            }
            if ($transactional && in_array((string) ($event['result'] ?? 'unknown'), ['failure', 'denied'], true)) {
                $meta = is_array($event['meta'] ?? null) ? $event['meta'] : [];
                if (!empty($event['user_id'])) $meta['actor_id'] = (int) $event['user_id'];
                $event['meta'] = $meta;
                $event['user_id'] = null;
            }
            $durable = logging_record_failure('activity', $event);
            if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
            return $durable;
        }
        $pdo = db();
        $transactional = $pdo->inTransaction();
        $event = logging_normalize_activity_event($event);
        // failure/denied درون تراکنش نباید با rollback کسب‌وکار حذف شود؛ فقط یک‌بار به Spool مستقل می‌رود.
        if ($transactional && in_array((string) ($event['result'] ?? 'unknown'), ['failure', 'denied'], true)) {
            $meta = is_array($event['meta'] ?? null) ? $event['meta'] : [];
            if (!empty($event['user_id'])) $meta['actor_id'] = (int) $event['user_id'];
            $event['meta'] = $meta;
            $event['user_id'] = null;
            $durable = logging_record_failure('activity', $event);
            if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
            return $durable;
        }
        $pdo->prepare('INSERT INTO activity_logs (event_id, request_id, trace_id, operation_id, parent_event_id, result, code, category, user_id, username, full_name, role, action_code, action_label, module, target_type, target_id, target_label, meta_json, ip_address, computer_name, hostname, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([
            $event['event_id'], $event['request_id'], $event['trace_id'], $event['operation_id'], $event['parent_event_id'], $event['result'] ?? 'unknown', $event['code'] ?? 'AUD-LEGACY-001', $event['category'] ?? 'audit', $event['user_id'] ?? null,
            $event['username'] ?? null, $event['full_name'] ?? null, $event['role'] ?? null, $event['action_code'] ?? 'unknown',
            $event['action_label'] ?? null, $event['module'] ?? null, $event['target_type'] ?? null, $event['target_id'] ?? null,
            $event['target_label'] ?? null, $event['meta_json'], $event['ip_address'] ?? null, $event['computer_name'] ?? null,
            $event['hostname'] ?? null, $event['user_agent'] ?? null, $event['created_at'],
        ]);
        logging_db_circuit_success();
        $GLOBALS['__logging_persistence_status'] = 'durable_db';
        if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
        return true;
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && logging_duplicate_event_already_stored($pdo, 'activity_logs', (string) $event['event_id'], $exception)) {
            $GLOBALS['__logging_persistence_status'] = 'durable_db';
            if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
            return false;
        }
        if (logging_db_error_is_transient($exception)) logging_db_circuit_failure($exception);
        if ($transactional) {
            throw $exception;
        }
        logging_record_failure('activity', $event);
        if (logging_parent_event_id() === null) $GLOBALS['__logging_parent_event_id'] = $event['event_id'];
        return false;
    }
}

function log_event(string $code, string $message, array $context = []): string
{
    $definition = logging_catalog_resolve($code, (string) ($context['category'] ?? 'system'), (string) ($context['severity'] ?? 'error'));
    $eventId = logging_uuid();
    logging_store_system([
        'event_id' => $eventId,
        'code' => $definition['code'],
        'category' => $definition['category'],
        'level' => $definition['severity'],
        'context' => (string) ($context['context'] ?? $definition['category']),
        'message' => $message,
        'meta' => logging_sanitize_event_meta((array) ($context['meta'] ?? []), $definition['code'], (string) ($context['context'] ?? $definition['category'])),
    ]);
    return $eventId;
}

logging_bootstrap();

/**
 * ثبت تلاش دسترسی غیرمجاز در گزارش فعالیت کاربران.
 * index.php این تابع را صدا می‌زند؛ در بستهٔ ورودی تعریف نشده بود و باعث خطای مرگبار می‌شد.
 * ثبت لاگ هرگز نباید درخواست کاربر را بشکند، پس همهٔ خطاها بلعیده می‌شوند.
 */
function security_log_access_denied(array $user, string $permission, string $page): void
{
    if (!function_exists('activity_log')) {
        return;
    }
    try {
        activity_log([
            'user' => $user,
            'action_code' => 'access_denied',
            'action_label' => 'تلاش برای دسترسی غیرمجاز',
            'module' => 'permission',
            'result' => 'denied',
            'target_type' => 'page',
            'target_label' => substr($page, 0, 255),
            'meta' => ['permission' => substr($permission, 0, 190), 'page' => substr($page, 0, 190)],
        ]);
    } catch (Throwable) {
        // سکوت عمدی: شکست ثبت امنیتی نباید به کاربر خطا بدهد.
    }
}
