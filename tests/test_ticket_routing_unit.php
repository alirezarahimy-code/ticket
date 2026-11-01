<?php
declare(strict_types=1);

// Offline behavioural tests: no real configuration, database or server data.
const APP_ROOT = __DIR__ . '/..';
function user_is_primary_admin(array $user): bool {
    return ($user['role'] ?? '') === 'primary_admin'
        || (($user['role'] ?? '') === 'admin' && (int) ($user['is_primary_admin'] ?? 0) === 1);
}
function is_staff_role(?string $role): bool { return in_array($role, ['agent', 'manager', 'support_manager', 'supervisor', 'primary_admin', 'inspector'], true); }
function is_assignable_role(?string $role): bool { return in_array($role, ['agent', 'manager', 'support_manager', 'primary_admin'], true); }
function is_global_ticket_role(?string $role): bool { return in_array($role, ['supervisor', 'primary_admin', 'inspector'], true); }
function is_it_agent(array $user): bool { return (int) ($user['is_it_agent'] ?? 0) === 1; }
function user_can(array $user, string $permission): bool {
    return user_is_primary_admin($user) || in_array($permission, $user['permissions'] ?? [], true);
}
function permission_roles(): array { return array_fill_keys(['user', 'agent', 'manager', 'supervisor', 'support_manager', 'admin'], true); }
function permission_table_ready(): bool { return true; }
function role_permissions_custom(string $role): bool { return true; }
function role_permission_effective_codes(string $role, array $stored, bool $custom): array { return $stored; }
final class FakeTicketStatement {
    public function __construct(public string $sql) {}
    public array $ids = [];
    public function execute(array $ids): void { $this->ids = $ids; }
    public function fetch(): array { return $GLOBALS['fake_event_order'] ?? []; }
    public function fetchAll(?int $mode = null): array {
        if (str_contains($this->sql, 'SELECT permission FROM role_permissions')) {
            return $GLOBALS['fake_permissions'][$this->ids[0]] ?? [];
        }
        return array_values(array_filter($GLOBALS['fake_users'], fn ($u) => in_array($u['id'], $this->ids, true) && $u['is_active']));
    }
}
final class FakeTicketDatabase {
    public function prepare(string $sql): FakeTicketStatement { return new FakeTicketStatement($sql); }
}
function db(): FakeTicketDatabase { static $db; return $db ??= new FakeTicketDatabase(); }
require APP_ROOT . '/lib/auth-scope.php';
require APP_ROOT . '/lib/notify-users.php';
function check_ticket(bool $ok, string $message): void {
    if (!$ok) { throw new RuntimeException($message); }
    echo "ok - $message\n";
}
$view = ['ticket.view_own'];
$unit = ['ticket.view_own', 'ticket.view_unit', 'ticket.edit'];
$managerPerm = ['ticket.view_own', 'ticket.view_unit', 'ticket.assign', 'ticket.edit'];
$users = [
    ['id'=>1, 'role'=>'user', 'permissions'=>$view, 'is_active'=>1],
    ['id'=>2, 'role'=>'user', 'permissions'=>$view, 'is_it_agent'=>1, 'handling_unit_code'=>'support', 'is_active'=>1],
    ['id'=>3, 'role'=>'agent', 'permissions'=>$unit, 'handling_unit_code'=>'support', 'is_active'=>1],
    ['id'=>4, 'role'=>'manager', 'permissions'=>$managerPerm, 'department_id'=>5, 'handling_unit_code'=>'support', 'is_active'=>1],
    ['id'=>5, 'role'=>'manager', 'permissions'=>$managerPerm, 'department_id'=>7, 'handling_unit_code'=>'support', 'is_active'=>1],
    ['id'=>6, 'role'=>'supervisor', 'permissions'=>['ticket.view_all'], 'is_active'=>1],
    ['id'=>7, 'role'=>'agent', 'permissions'=>$unit, 'handling_unit_code'=>'it', 'is_active'=>1],
    ['id'=>8, 'role'=>'support_manager', 'permissions'=>$managerPerm, 'handling_unit_code'=>'support', 'is_active'=>0],
    ['id'=>9, 'role'=>'admin', 'is_primary_admin'=>1, 'is_active'=>1],
    ['id'=>10, 'role'=>'user', 'permissions'=>['ticket.view_own', 'ticket.assign'], 'is_active'=>1],
];
$GLOBALS['fake_users'] = $users;
$GLOBALS['fake_permissions'] = [
    'user'=>$view, 'agent'=>$unit, 'manager'=>$managerPerm,
    'supervisor'=>['ticket.view_all'], 'support_manager'=>$managerPerm,
];
$ticket = ['requester_id'=>1, 'assigned_to'=>3, 'service_group'=>'support', 'department_id'=>5, 'status'=>'assigned', 'cancel_requested_at'=>null];
$recipients = ticket_notification_recipients($ticket, [1,2,3,4,5,6,7,8,9,2], 'user_can');
sort($recipients);
check_ticket($recipients === [1,3,4,6,9], 'notifications exclude stale flags, unrelated units and inactive users');
$readonlyRecipients = ticket_notification_recipients($ticket, [1,2,3,4,5,6,7,8,9,2]);
sort($readonlyRecipients);
check_ticket($readonlyRecipients === $recipients, 'notification permission checks use the effective read-only grants');
$staffRecipients = ticket_notification_recipients($ticket, [1,2,3,4,6,9], 'user_can', true);
sort($staffRecipients);
check_ticket($staffRecipients === [3,4,6,9], 'staff-only alerts do not leak to requester or stale ordinary-user flags');
check_ticket(ticket_assignee_is_eligible($ticket, $users[2], 'user_can'), 'active agent with view and edit permissions is eligible');
$noEdit = $users[2]; $noEdit['permissions'] = ['ticket.view_unit'];
check_ticket(!ticket_assignee_is_eligible($ticket, $noEdit, 'user_can'), 'agent without edit permission cannot be assigned');
$noView = $users[2]; $noView['permissions'] = ['ticket.edit'];
check_ticket(!ticket_assignee_is_eligible($ticket, $noView, 'user_can'), 'agent without view permission cannot be assigned');
check_ticket(!ticket_assignee_is_eligible($ticket, $users[4], 'user_can'), 'manager from other department cannot be assigned');
check_ticket(!ticket_assignee_is_eligible($ticket, $users[1], 'user_can'), 'stale IT flag does not make a normal user assignable');
check_ticket(ticket_handler_level($ticket, $users[2]) === 'assignee', 'assigned agent is handler');
check_ticket(ticket_handler_level($ticket, $users[3]) === 'manager', 'manager in matching unit is handler');
check_ticket(ticket_handler_level($ticket, $users[4]) === '', 'other unit manager cannot handle the request');
check_ticket(ticket_handler_level($ticket, $users[8]) === 'manager', 'legacy primary admin can handle the request');
check_ticket(ticket_reply_starts_work($ticket, $users[2]), 'assigned agent reply starts work');
$noEditReply = $users[2]; $noEditReply['permissions'] = ['ticket.view_unit', 'ticket.reply'];
check_ticket(!ticket_reply_starts_work($ticket, $noEditReply), 'reply-only agent cannot change status without edit permission');
check_ticket(!ticket_reply_starts_work($ticket, $users[0]), 'requester reply cannot start work');
$otherAgent = $users[2]; $otherAgent['id'] = 11;
check_ticket(!ticket_reply_starts_work($ticket, $otherAgent), 'other agent reply cannot start work');
$ownTicket = $ticket; $ownTicket['requester_id'] = 3;
check_ticket(ticket_user_is_requester($ownTicket, $users[2]) && !ticket_reply_starts_work($ownTicket, $users[2]), 'staff member remains requester on own ticket');
$ownTicket['requester_id'] = 10;
check_ticket(ticket_handler_level($ownTicket, $users[9]) === '', 'custom assignment permission does not turn an ordinary user into a handler');
$ownTicket['requester_id'] = 5;
check_ticket(ticket_user_can_view($ownTicket, $users[4]) && ticket_handler_level($ownTicket, $users[4]) === '', 'own ticket does not give manager authority outside department');
[$ownScope, $ownParameters] = ticket_scope($users[4]);
check_ticket(str_contains($ownScope, 'requester_id = ?') && $ownParameters === ['support', 7, 5], 'manager list includes own requests outside their department but within their group');
$ticket['cancel_requested_at'] = '2026-01-01 12:00:00';
check_ticket(!ticket_reply_starts_work($ticket, $users[2]), 'pending cancellation blocks implicit transition');

$ratingTicket = ['id'=>42, 'rating_score'=>4, 'rating_at'=>'2026-01-01 12:00:00', 'resolved_at'=>'2026-01-01 12:00:00'];
$GLOBALS['fake_event_order'] = ['rated_id'=>11, 'resolved_id'=>12];
check_ticket(!ticket_rating_is_current($ratingTicket), 'old rating in same second is invalid after a later resolution event');
$GLOBALS['fake_event_order'] = ['rated_id'=>13, 'resolved_id'=>12];
check_ticket(ticket_rating_is_current($ratingTicket), 'new rating in same second is valid after resolution');
$GLOBALS['fake_event_order'] = [];
check_ticket(ticket_rating_is_current($ratingTicket), 'old records without events keep compatible rating behaviour');
