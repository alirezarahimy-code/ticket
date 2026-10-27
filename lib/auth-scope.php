<?php
declare(strict_types=1);

/**
 * lib/auth-scope.php — نشست کاربر، سطوح دسترسی و دامنهٔ دید — جداشده از bootstrap.php در نسخهٔ ۱.۶۲.۲۰ (فاز ۲ بازسازی).
 * محتوا: current_user / login_user / require_login، require_admin، require_primary_admin، require_staff، require_supervisor / ticket_scope و asset_scope (دامنهٔ دید نقش‌ها) / برچسب و گذار چرخهٔ عمر دارایی.
 * ترتیب تعریف توابع و کدهای سطح‌بالا عیناً مانند bootstrap قبلی حفظ شده است.
 */

if (!defined('APP_ROOT')) {
    exit;
}

function current_user(): ?array
{
    static $user;
    if ($user !== null) {
        return $user ?: null;
    }
    if (empty($_SESSION['user_id'])) {
        $user = false;
        return null;
    }
    $query = db()->prepare('SELECT u.*, d.name AS department_name, hu.code AS handling_unit_code, hu.name AS handling_unit_name FROM users u LEFT JOIN departments d ON d.id = u.department_id LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.id = ? AND u.is_active = 1 LIMIT 1');
    $query->execute([(int) $_SESSION['user_id']]);
    $user = $query->fetch() ?: false;
    return $user ?: null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    if (function_exists('activity_log')) {
        activity_log([
            'action_code' => !empty($user['auth_source']) && $user['auth_source'] === 'ldap' ? 'login_ldap' : 'login_local',
            'user_id' => (int) $user['id'],
            'username' => (string) ($user['username'] ?? ''),
            'full_name' => (string) ($user['full_name'] ?? ''),
            'role' => (string) ($user['role'] ?? ''),
            'module' => 'auth',
        ]);
    }
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('index.php?page=login');
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if (!(function_exists('is_admin_role') ? is_admin_role($user['role']) : in_array($user['role'], ['primary_admin'], true))) {
        http_response_code(403);
        exit('دسترسی به این بخش مجاز نیست.');
    }
    return $user;
}

function require_primary_admin(): array
{
    $user = require_login();
    $isPrimary = function_exists('user_is_primary_admin')
        ? user_is_primary_admin($user)
        : ($user['role'] === 'primary_admin' || ($user['role'] === 'admin' && (int) ($user['is_primary_admin'] ?? 0) === 1));
    if (!$isPrimary) {
        http_response_code(403);
        exit('فقط ادمین اصلی اجازه مدیریت نقش‌ها و معاونت‌ها را دارد.');
    }
    return $user;
}

function require_staff(): array
{
    $user = require_login();
    if (!(function_exists('is_staff_role') ? is_staff_role($user['role']) : in_array($user['role'], ['agent', 'manager', 'supervisor'], true))) {
        http_response_code(403);
        exit('دسترسی به این بخش مجاز نیست.');
    }
    return $user;
}

function require_supervisor(): array
{
    $user = require_login();
    if (!in_array($user['role'], ['supervisor', 'primary_admin'], true)) {
        http_response_code(403);
        exit('دسترسی به پنل سوپروایزر مجاز نیست.');
    }
    return $user;
}

function ticket_scope(array $user, string $alias = 't'): array
{
    // این قید پیش از هر مجوز «مشاهده همه» اجرا می‌شود تا مدیر پشتیبانی هرگز
    // با مجوز سفارشی/قدیمی ticket.view_all به حوزهٔ IT دسترسی نگیرد.
    if (($user['role'] ?? '') === 'support_manager') {
        return ["WHERE {$alias}.service_group = ?", ['support']];
    }
    if (function_exists('is_global_ticket_role') ? is_global_ticket_role($user['role']) : in_array($user['role'], ['supervisor'], true)) {
        return ['', []];
    }
    if (in_array($user['role'], ['agent', 'manager'], true)) {
        $group = user_service_group($user);
        $departmentId = (int) ($user['department_id'] ?? 0);
        if ($user['role'] === 'agent') {
            return ["WHERE {$alias}.service_group = ?", [$group]];
        }
        return $departmentId > 0 ? ["WHERE {$alias}.service_group = ? AND {$alias}.department_id = ?", [$group, $departmentId]] : ["WHERE {$alias}.service_group = ?", [$group]];
    }
    return ["WHERE {$alias}.requester_id = ?", [(int) $user['id']]];
}

function ticket_is_waiting_status(string $status): bool
{
    // مقدار قدیمی pending هم از نظر مکث SLA/OLA و پاسخ کاربر مثل waiting_user رفتار می‌کند.
    return $status === 'waiting_user' || $status === 'pending';
}

function user_service_group(array $user): string
{
    return in_array((string) ($user['handling_unit_code'] ?? ''), ['it', 'support'], true)
        ? (string) $user['handling_unit_code']
        : (is_it_agent($user) ? 'it' : 'support');
}

function asset_scope(array $user, string $alias = 'a'): array
{
    if ((function_exists('is_global_ticket_role') ? is_global_ticket_role($user['role']) : in_array($user['role'], ['supervisor'], true)) || (is_it_agent($user) && user_service_group($user) === 'it')) {
        return ['', []];
    }
    if (user_service_group($user) !== 'it') {
        return ['WHERE 1 = 0', []];
    }
    $departmentId = (int) ($user['department_id'] ?? 0);
    return $departmentId > 0 ? ["WHERE {$alias}.department_id = ?", [$departmentId]] : ['WHERE 1 = 0', []];
}

