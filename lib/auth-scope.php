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
        exit('فقط ادمین اصلی اجازهٔ مدیریت نقش‌ها، معاونت‌ها و گروه‌های دسترسی را دارد.');
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

/** Active descendants of units actually managed by this user, restricted to each company. */
function ticket_manager_scope_unit_ids(array $user): array
{
    $id = (int) ($user['id'] ?? 0);
    if ($id <= 0) return [];
    try {
        $roots = db()->prepare('SELECT ou.id FROM org_units ou JOIN org_companies company ON company.id = ou.company_id AND company.is_active = 1 WHERE ou.manager_user_id = ? AND ou.is_active = 1 UNION SELECT ou.id FROM org_unit_managers m JOIN org_units ou ON ou.id = m.unit_id JOIN org_companies company ON company.id = ou.company_id AND company.is_active = 1 WHERE m.user_id = ? AND ou.is_active = 1');
        $roots->execute([$id, $id]);
        $rootIds = array_map('intval', $roots->fetchAll(PDO::FETCH_COLUMN));
        if (!$rootIds) return [];
        $units = db()->query('SELECT id, parent_id, company_id FROM org_units WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);
        $byId = []; $children = [];
        foreach ($units as $unit) {
            $uid = (int) $unit['id'];
            $byId[$uid] = $unit;
            $children[(int) ($unit['parent_id'] ?? 0)][] = $uid;
        }
        $result = [];
        foreach ($rootIds as $root) {
            if (!isset($byId[$root]) || (int) ($byId[$root]['company_id'] ?? 0) <= 0) continue;
            $company = (int) $byId[$root]['company_id']; $stack = [$root]; $seen = [];
            while ($stack) {
                $unitId = array_pop($stack);
                if (isset($seen[$unitId]) || !isset($byId[$unitId]) || (int) ($byId[$unitId]['company_id'] ?? 0) !== $company) continue;
                $seen[$unitId] = true; $result[$unitId] = $unitId;
                foreach ($children[$unitId] ?? [] as $child) $stack[] = $child;
            }
        }
        return array_values($result);
    } catch (Throwable $error) {
        // Missing schema or incomplete chart must not grant the entire support pool.
        if (function_exists('app_error_log')) app_error_log('[ticket-scope] ' . $error->getMessage());
        return [];
    }
}

/**
 * آیا کاربر «مدیر واحدِ صرف» است؟
 * رئیسِ فعالِ دست‌کم یک واحدِ چارت سازمانی است و هیچ نشانۀ کارشناسی/پشتیبانی/سراسری ندارد:
 * نه نقش agent/support_manager، نه is_it_agent، نه handling_unit_code، نه نقش سراسری و نه مدیر اصلی.
 * مالکِ این تعریف همین تابع است؛ هر مرزِ دسترسیِ «مدیر واحد» باید از آن تغذیه شود.
 */
function ticket_sole_unit_head(array $user): bool
{
    if (function_exists('user_is_primary_admin') && user_is_primary_admin($user)) {
        return false;
    }
    // اولویت با خودِ نقش است؛ دسترسی‌های موردیِ گرنت‌شده از گروه‌ها، نقش را تغییر نمی‌دهند.
    if (is_global_ticket_role((string) ($user['role'] ?? ''))) {
        return false;
    }
    $role = (string) ($user['role'] ?? '');
    if (in_array($role, ['agent', 'support_manager'], true)) {
        return false;
    }
    if (function_exists('is_it_agent') && is_it_agent($user)) {
        return false;
    }
    if (trim((string) ($user['handling_unit_code'] ?? '')) !== '') {
        return false;
    }
    return ticket_manager_scope_unit_ids($user) !== [];
}

/** SQL predicate shared by list and single-ticket authorization. */
function ticket_requester_tree_predicate(string $requesterExpr, array $unitIds): array
{
    if (!$unitIds) return ['1 = 0', []];
    $marks = implode(',', array_fill(0, count($unitIds), '?'));
    return ['EXISTS (SELECT 1 FROM users scope_user WHERE scope_user.id = ' . $requesterExpr
        . ' AND (scope_user.org_unit_id IN (' . $marks . ')'
        . ' OR EXISTS (SELECT 1 FROM org_units scope_managed WHERE scope_managed.is_active = 1 AND scope_managed.manager_user_id = scope_user.id AND scope_managed.id IN (' . $marks . '))'
        . ' OR EXISTS (SELECT 1 FROM org_unit_managers scope_members JOIN org_units scope_unit ON scope_unit.id = scope_members.unit_id AND scope_unit.is_active = 1 WHERE scope_members.user_id = scope_user.id AND scope_unit.id IN (' . $marks . '))))', array_merge($unitIds, $unitIds, $unitIds)];
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
    if ($role === 'agent' && $ticketGroup !== user_service_group($user)) {
        return false;
    }
    if ($role === 'manager' && empty(ticket_manager_scope_unit_ids($user))
        && $ticketGroup !== user_service_group($user)) {
        // مدیر بدون درخت سازمانی، همان رفتار قدیمی (فقط حوزهٔ خودش) را دارد.
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
        // فضای مدیر واحد: مرزِ اختیار او = چارت سازمانی (خودش + زیرمجموعه‌اش، هر دو حوزهٔ IT و پشتیبانی).
        // مدیر واحدِ صرف حتی درخواستِ ارجاعی به خودش را از این مسیر نمی‌بیند؛ مرز او فقط چارت است.
        if (!ticket_sole_unit_head($user)
            && (int) ($ticket['assigned_to'] ?? 0) === (int) $user['id'] && $permissionCheck($user, 'ticket.view_unit')) {
            // هر درخواستی که به خودِ مدیر ارجاع شده برای خودش قابل مشاهده است.
            return true;
        }
        $ids = ticket_manager_scope_unit_ids($user);
        if (!$ids) {
            // بدون درخت مشخص، همان رفتار داخلیِ حوزهٔ خودش باقی می‌ماند.
            return $permissionCheck($user, 'ticket.view_unit')
                && (string) ($ticket['service_group'] ?? '') === user_service_group($user);
        }
        [$predicate, $params] = ticket_requester_tree_predicate('?', $ids);
        try {
            $query = db()->prepare('SELECT ' . $predicate);
            $query->execute(array_merge([(int) ($ticket['requester_id'] ?? 0)], $params));
            return (bool) $query->fetchColumn();
        } catch (Throwable $error) {
            if (function_exists('app_error_log')) app_error_log('[ticket-scope] ' . $error->getMessage());
            return false;
        }
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
    if (($user['role'] ?? '') === 'agent' && $ticketGroup !== user_service_group($user)) {
        return '';
    }
    if (($user['role'] ?? '') === 'manager' && empty(ticket_manager_scope_unit_ids($user))
        && $ticketGroup !== user_service_group($user)) {
        // مدیر بدون درخت سازمانی: خارج از حوزهٔ خودش اختیاری ندارد.
        return '';
    }
    if (($user['role'] ?? '') === 'manager') {
        $managerScopeIds = ticket_manager_scope_unit_ids($user);
        $isManagerRequester = ticket_user_is_requester($ticket, $user);
        if ($managerScopeIds) {
            // سرپرستی درخواست‌های دیگران داخل درخت واحدهای مدیر برقرار است (هر دو حوزه)؛
            // درخواستِ خودِ مدیر هرگز از درخت اختیارِ سرپرستی نمی‌گیرد (تفکیک وظایف).
            if (!$isManagerRequester) {
                [$treePredicate, $treeParams] = ticket_requester_tree_predicate('?', $managerScopeIds);
                $inSubtree = false;
                try {
                    $treeQuery = db()->prepare('SELECT ' . $treePredicate);
                    $treeQuery->execute(array_merge([(int) ($ticket['requester_id'] ?? 0)], $treeParams));
                    $inSubtree = (bool) $treeQuery->fetchColumn();
                } catch (Throwable $error) {
                    if (function_exists('app_error_log')) {
                        app_error_log('[ticket-scope] ' . $error->getMessage());
                    }
                }
                if (!$inSubtree) {
                    return '';
                }
            }
        } elseif ((int) ($user['department_id'] ?? 0) > 0
            && (int) ($user['department_id'] ?? 0) !== (int) ($ticket['department_id'] ?? 0)) {
            return '';
        }
    }
    // تفکیک وظایف: نما و اختیار تابع «نسبت این کاربر با همین درخواست» است، نه نقش
    // سراسری او. کارشناس یا مدیری که خودش درخواستی ثبت کرده، روی آن درخواست
    // ثبت‌کننده است — نه سرپرست. وگرنه می‌توانست درخواست خودش را به خودش ارجاع
    // دهد، وضعیتش را تغییر دهد و کار خودش را تأیید کند.
    //
    // استثنا: اگر واقعاً کارشناس مسئول همان درخواست باشد باید بتواند رویش کار کند،
    // ولی فقط در حد کارشناس. (ticket_reply_starts_work گارد جداگانهٔ خودش را دارد
    // تا پاسخِ چنین کاربری وضعیت را به‌طور ضمنی جلو نبرد.)
    if (ticket_user_is_requester($ticket, $user)) {
        if (ticket_sole_unit_head($user)) {
            // مدیر واحدِ صرف روی درخواست خودش هم اختیار رسیدگی ندارد.
            return '';
        }
        return ((int) ($ticket['assigned_to'] ?? 0) === (int) ($user['id'] ?? 0)
            && is_assignable_role((string) ($user['role'] ?? '')))
            ? 'assignee'
            : '';
    }
    if ((is_assignable_role((string) ($user['role'] ?? '')) || user_is_primary_admin($user))
        && user_can($user, 'ticket.assign')) {
        if (ticket_sole_unit_head($user)) {
            // اختیار «ارجاع پیش از تخصیص» + اولویت/اصلاح — نه اختیار کامل کارشناسی.
            return 'assigner';
        }
        return 'manager';
    }
    if ((int) ($ticket['assigned_to'] ?? 0) === (int) $user['id'] && is_assignable_role((string) ($user['role'] ?? ''))) {
        return 'assignee';
    }
    return '';
}

/**
 * کدام یک از سه نما باید برای این کاربر روی همین درخواست رندر شود.
 *
 *   'requester'  ثبت‌کننده یا بینندهٔ ساده — وضعیت، گفت‌وگو، امتیاز، لغو
 *   'assignee'   کارشناس مسئول همین درخواست — کار روی درخواست، یادداشت داخلی
 *   'supervisor' مدیر حوزه — به‌علاوهٔ ارجاع، اولویت و تصمیم‌های سرپرستی
 *
 * عمداً از ticket_handler_level() مشتق می‌شود تا نما و اختیار هیچ‌وقت از هم جدا
 * نیفتند؛ دو منبع حقیقت موازی همان چیزی بود که این نقص را ساخت.
 */
function ticket_view_mode(array $ticket, array $user): string
{
    return match (ticket_handler_level($ticket, $user)) {
        'manager' => 'supervisor',
        'assignee' => 'assignee',
        default => 'requester',
    };
}

/**
 * آیا این کاربر یادداشت‌ها و پیوست‌های داخلی همین درخواست را می‌بیند؟
 *
 * پیش از این تصمیم فقط با نقش سراسری گرفته می‌شد، پس کارشناس یا مدیری که خودش
 * درخواست داده بود، یادداشت‌های داخلیِ تیم رسیدگی‌کننده دربارهٔ درخواست خودش را
 * می‌دید. یادداشت داخلی برای هماهنگی تیم است، نه برای ثبت‌کننده.
 *
 * دامنه عمداً فقط برای همین حالت تنگ شده است: بقیهٔ کارکنانی که به درخواست
 * دسترسی دید دارند همچنان می‌بینند تا همکاری و جانشینی مختل نشود.
 */
function ticket_can_see_internal(array $ticket, array $user): bool
{
    if (ticket_sole_unit_head($user)) {
        // مدیر واحدِ صرف: فقط گفت‌وگوی عمومی را می‌بیند؛ یادداشت‌های تیم رسیدگی نه.
        return false;
    }
    if (ticket_user_is_requester($ticket, $user)
        && (int) ($ticket['assigned_to'] ?? 0) !== (int) ($user['id'] ?? 0)) {
        return false;
    }
    return is_staff_role((string) ($user['role'] ?? '')) || user_is_primary_admin($user);
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
    // تفکیک وظایف در لحظهٔ ارجاع: ثبت‌کنندهٔ یک درخواست، مسئول رسیدگی به درخواست
    // خودش نمی‌شود — نه با ارجاع خودکار هنگام ثبت، نه با «برداشتن درخواست»، نه از
    // فهرست کشویی ارجاع دستی. این تابع تنها نقطهٔ مشترک هر پنج مسیر ارجاع است، پس
    // قاعده همین‌جا می‌نشیند تا مثل قبل به‌صورت وصله در چند جا تکرار (و در بقیه جا
    // انداخته) نشود. دادهٔ لازم از قبل اینجا بود: choose_handling_agent() همان ابتدا
    // requester_id را داخل آرایهٔ درخواست می‌گذارد.
    //
    // دریچهٔ اضطراری: واحدی که فقط یک رسیدگی‌کننده دارد با این قاعده قفل می‌شود،
    // چون کسی برای ارجاع باقی نمی‌ماند و درخواست در manager_review بی‌صاحب می‌ماند.
    // چنین واحدی می‌تواند security.allow_self_assignment را آگاهانه باز کند.
    // function_exists لازم است چون این تابع در بافت‌های بدون لایهٔ تنظیمات هم
    // (از جمله تست واحد مسیر ارجاع) فراخوانی می‌شود.
    $allowSelfAssignment = function_exists('cfg')
        ? (bool) cfg('security.allow_self_assignment', false)
        : false;
    if (!$allowSelfAssignment && ticket_user_is_requester($ticket, $user)) {
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
        && !ticket_sole_unit_head($user)
        && ticket_handler_level($ticket, $user) !== '';
}

function ticket_scope(array $user, string $alias = 't'): array
{
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $alias)) throw new InvalidArgumentException('Invalid ticket alias');
    if (user_is_primary_admin($user)) return ['', []];
    $role = (string) ($user['role'] ?? '');
    $own = user_can($user, 'ticket.view_own');
    $unit = user_can($user, 'ticket.view_unit');
    if (is_global_ticket_role($role) && user_can($user, 'ticket.view_all')) return ['', []];
    if (in_array($role, ['support_manager', 'agent', 'manager'], true)) {
        $group = $role === 'support_manager' ? 'support' : user_service_group($user);
        if ($unit && $role !== 'manager') return ["WHERE {$alias}.service_group = ?", [$group]];
        if ($role === 'manager' && ticket_sole_unit_head($user)) {
            // مدیر واحدِ صرف: همان مرزِ «فضای مدیر واحد» — خودش + زیردرخت چارت؛ بدون فیلتر حوزهٔ خدمت.
            [$tree, $treeParams] = ticket_requester_tree_predicate("{$alias}.requester_id", ticket_manager_scope_unit_ids($user));
            $parts = []; $params = [];
            if ($own) { $parts[] = "{$alias}.requester_id = ?"; $params[] = (int) ($user['id'] ?? 0); }
            $parts[] = $tree; $params = array_merge($params, $treeParams);
            return ["WHERE (" . implode(' OR ', $parts) . ')', $params];
        }
        $parts = []; $params = [$group];
        if ($own) { $parts[] = "{$alias}.requester_id = ?"; $params[] = (int) ($user['id'] ?? 0); }
        if ($unit && $role === 'manager') {
            [$tree, $treeParams] = ticket_requester_tree_predicate("{$alias}.requester_id", ticket_manager_scope_unit_ids($user));
            $parts[] = $tree; $params = array_merge($params, $treeParams);
        }
        return ["WHERE {$alias}.service_group = ? AND (" . ($parts ? implode(' OR ', $parts) : '1 = 0') . ')', $params];
    }
    return $own ? ["WHERE {$alias}.requester_id = ?", [(int) ($user['id'] ?? 0)]] : ['WHERE 1 = 0', []];
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
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $alias)) {
        throw new InvalidArgumentException('Invalid asset alias');
    }

    $role = (string) ($user['role'] ?? '');
    $isPrimaryAdmin = function_exists('user_is_primary_admin')
        ? user_is_primary_admin($user)
        : $role === 'primary_admin';
    $isGlobal = function_exists('is_global_ticket_role')
        ? is_global_ticket_role($role)
        : in_array($role, ['supervisor', 'inspector', 'primary_admin'], true);
    if ($isPrimaryAdmin || $isGlobal) {
        return ['', []];
    }

    // Support-side users never receive access to the IT asset inventory.
    if (user_service_group($user) !== 'it') {
        return ['WHERE 1 = 0', []];
    }

    // assets.own_unit is a scope limiter: when granted, restrict the inventory
    // to the user's department. Without it, an authorized IT user sees all IT assets.
    // If the permission system is unavailable, fail closed to the user's own unit.
    $ownUnitOnly = function_exists('user_can')
        ? user_can($user, 'assets.own_unit')
        : true;
    if (!$ownUnitOnly) {
        return ['', []];
    }

    $departmentId = (int) ($user['department_id'] ?? 0);
    return $departmentId > 0
        ? ["WHERE {$alias}.department_id = ?", [$departmentId]]
        : ['WHERE 1 = 0', []];
}

