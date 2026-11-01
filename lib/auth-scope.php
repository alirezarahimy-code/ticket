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

function ticket_user_can_view(array $ticket, array $user, ?callable $permissionCheck = null): bool
{
    $permissionCheck = $permissionCheck ?? 'user_can';
    // جداسازی حوزه پیش از درخواست‌های خود و مجوزهای سفارشی؛ کارمند پشتیبانی فقط پشتیبانی.
    // این کنترل روی جزئیات، پیوست، پاسخ، ارجاع و عملیات گروهی هم اعمال می‌شود.
    $role = (string) ($user['role'] ?? '');
    if (user_is_primary_admin($user)) {
        return true;
    }
    $ticketGroup = (string) ($ticket['service_group'] ?? '');
    if ($role === 'support_manager' && $ticketGroup !== 'support') {
        return false;
    }
    if (in_array($role, ['agent', 'manager'], true) && $ticketGroup !== user_service_group($user)) {
        return false;
    }
    // ۱.۳۷.۸ — دسترسی‌های مشاهدهٔ درخواست از سطوح دسترسی نقش‌ها خوانده می‌شود:
    // ticket.view_own (درخواست‌های خودم)، ticket.view_unit (واحد/زیرمجموعه)، ticket.view_all (همهٔ درخواست‌ها).
    if ((int) $ticket['requester_id'] === (int) $user['id'] && $permissionCheck($user, 'ticket.view_own')) {
        return true;
    }
    if (is_global_ticket_role($user['role']) && $permissionCheck($user, 'ticket.view_all')) {
        return true;
    }
    if (!$permissionCheck($user, 'ticket.view_unit')) {
        return false;
    }
    if (($user['role'] ?? '') === 'support_manager') {
        // مدیر پشتیبانی فقط حوزه پشتیبانی؛ بدون دسترسی به درخواست‌های IT و بدون فیلتر معاونت.
        return (string) ($ticket['service_group'] ?? '') === 'support';
    }
    if ($user['role'] === 'agent') {
        return (string) ($ticket['service_group'] ?? '') === user_service_group($user);
    }
    if ($user['role'] === 'manager') {
        // هم‌تراز با ticket_scope: مدیر بدون معاونت، کل حوزه را می‌بیند؛ وگرنه فقط معاونت خودش.
        if ((string) ($ticket['service_group'] ?? '') !== user_service_group($user)) {
            return false;
        }
        $managerDept = (int) ($user['department_id'] ?? 0);
        return $managerDept <= 0 || (int) ($ticket['department_id'] ?? 0) === $managerDept;
    }
    return false;
}


/**
 * سطح اختیار کاربر روی درخواست: manager (ارجاع/تغییر کامل)، assignee (کارشناس مسئول)، یا رشتهٔ خالی.
 */
function ticket_handler_level(array $ticket, array $user): string
{
    if (!ticket_user_can_view($ticket, $user)) {
        return '';
    }
    // جداسازی حوزه حتی اگر مجوز ارجاع به‌اشتباه داده شده باشد: مدیر پشتیبانی فقط پشتیبانی.
    $ticketGroup = (string) ($ticket['service_group'] ?? '');
    if (($user['role'] ?? '') === 'support_manager' && $ticketGroup !== 'support') {
        return '';
    }
    if (in_array(($user['role'] ?? ''), ['agent', 'manager'], true) && $ticketGroup !== user_service_group($user)) {
        return '';
    }
    if (($user['role'] ?? '') === 'manager' && (int) ($user['department_id'] ?? 0) > 0
        && (int) ($user['department_id'] ?? 0) !== (int) ($ticket['department_id'] ?? 0)) {
        return '';
    }
    if ((is_assignable_role((string) ($user['role'] ?? '')) || user_is_primary_admin($user))
        && user_can($user, 'ticket.assign')) {
        return 'manager';
    }
    if ((int) ($ticket['assigned_to'] ?? 0) === (int) $user['id'] && is_assignable_role((string) ($user['role'] ?? ''))) {
        return 'assignee';
    }
    return '';
}

/** A ticket assignee must be active, in the right scope and able to handle it. */
function ticket_assignee_is_eligible(array $ticket, array $user, ?callable $permissionCheck = null): bool
{
    $permissionCheck = $permissionCheck ?? 'user_can';
    $role = (string) ($user['role'] ?? '');
    $group = (string) ($ticket['service_group'] ?? '');
    if ((int) ($user['is_active'] ?? 0) !== 1) {
        return false;
    }
    if (user_is_primary_admin($user)) {
        return $group === 'it';
    }
    if ($role === 'support_manager') {
        $inGroup = $group === 'support';
    } elseif (in_array($role, ['agent', 'manager'], true)) {
        $inGroup = (string) ($user['handling_unit_code'] ?? '') === $group;
    } else {
        return false;
    }
    if (!$inGroup || ($role === 'manager'
        && ((int) ($ticket['department_id'] ?? 0) <= 0
            || (int) ($ticket['department_id'] ?? 0) !== (int) ($user['department_id'] ?? 0)))) {
        return false;
    }
    return ticket_user_can_view($ticket, $user, $permissionCheck) && $permissionCheck($user, 'ticket.edit');
}

/** Current rating belongs to the latest resolution, including same-second events. */
function ticket_rating_is_current(array $ticket): bool
{
    if (empty($ticket['rating_score'])) {
        return false;
    }
    if (empty($ticket['resolved_at']) || empty($ticket['rating_at'])) {
        return true; // Compatibility with older records lacking timestamps.
    }
    $ratingTime = strtotime((string) $ticket['rating_at']);
    $resolvedTime = strtotime((string) $ticket['resolved_at']);
    if ($ratingTime !== $resolvedTime) {
        return $ratingTime > $resolvedTime;
    }
    // DATETIME has second precision. Use the immutable event order when a rating
    // and a new resolution share a second; legacy records may have no events.
    if (!empty($ticket['id'])) {
        try {
            $query = db()->prepare('SELECT MAX(CASE WHEN event_type = "rated" THEN id ELSE NULL END) AS rated_id, MAX(CASE WHEN to_status = "resolved" AND (from_status IS NULL OR from_status <> "resolved") THEN id ELSE NULL END) AS resolved_id FROM ticket_events WHERE ticket_id = ?');
            $query->execute([(int) $ticket['id']]);
            $order = $query->fetch();
            if (!empty($order['rated_id']) && !empty($order['resolved_id'])) {
                return (int) $order['rated_id'] > (int) $order['resolved_id'];
            }
        } catch (Throwable) {
            // Preserve legacy behaviour when event history is unavailable.
        }
    }
    return true;
}

function ticket_user_is_requester(array $ticket, array $user): bool
{
    return (int) ($ticket['requester_id'] ?? 0) > 0
        && (int) $ticket['requester_id'] === (int) ($user['id'] ?? 0);
}

/** Only the handler, never the requester, may start work implicitly by replying. */
function ticket_reply_starts_work(array $ticket, array $user): bool
{
    return !ticket_user_is_requester($ticket, $user)
        && (string) ($ticket['status'] ?? '') === 'assigned'
        && empty($ticket['cancel_requested_at'])
        && user_can($user, 'ticket.edit')
        && ticket_handler_level($ticket, $user) !== '';
}

function ticket_scope(array $user, string $alias = 't'): array
{
    if (user_is_primary_admin($user)) {
        return ['', []];
    }
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
        // A manager may view their own request in another department, but remains
        // unable to handle it there (ticket_handler_level enforces that scope).
        return $departmentId > 0 ? ["WHERE {$alias}.service_group = ? AND ({$alias}.department_id = ? OR {$alias}.requester_id = ?)", [$group, $departmentId, (int) $user['id']]] : ["WHERE {$alias}.service_group = ?", [$group]];
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

