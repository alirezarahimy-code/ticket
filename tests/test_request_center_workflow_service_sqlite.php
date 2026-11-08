<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/modules/request-center/WorkflowService.php';

function workflow_check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
    echo "ok - {$message}\n";
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE tickets (
    id INTEGER PRIMARY KEY, status TEXT NOT NULL, requester_id INTEGER NOT NULL,
    assigned_to INTEGER NULL, first_response_at TEXT NULL, resolved_at TEXT NULL,
    cancel_requested_at TEXT NULL, cancel_requested_by INTEGER NULL, cancel_request_reason TEXT NULL,
    priority TEXT NOT NULL, department_id INTEGER NULL, service_group TEXT NULL, due_at TEXT NULL,
    assigned_at TEXT NULL, closed_at TEXT NULL, rejected_at TEXT NULL, supervisor_id INTEGER NULL,
    supervisor_approved_at TEXT NULL, supervisor_note TEXT NULL,
    sla_minutes INTEGER NULL, sla_paused_at TEXT NULL, sla_pause_minutes INTEGER NOT NULL DEFAULT 0,
    ola_policy_id INTEGER NULL, ola_response_due_at TEXT NULL, ola_due_at TEXT NULL,
    ola_escalation_1_at TEXT NULL, ola_escalation_2_at TEXT NULL,
    updated_at TEXT NULL
)');
$pdo->exec('CREATE TABLE ticket_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT, ticket_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL, body TEXT NOT NULL, is_internal INTEGER NOT NULL DEFAULT 0
)');
$insert = $pdo->prepare('INSERT INTO tickets (id,status,requester_id,assigned_to,priority,cancel_requested_at) VALUES (?,?,?,?,?,?)');
$insert->execute([1, 'assigned', 10, 20, 'normal', null]);
$insert->execute([2, 'pending', 11, 21, 'urgent', null]);
$insert->execute([3, 'assigned', 12, 22, 'normal', '2026-11-01 10:00:00']);
$insert->execute([4, 'resolved', 13, 23, 'normal', null]);
$insert->execute([5, 'closed', 14, 24, 'normal', null]);
$insert->execute([6, 'assigned', 15, 25, 'normal', null]);
$insert->execute([7, 'new', 16, null, 'normal', null]);
$insert->execute([8, 'resolved', 17, 27, 'normal', null]);
$insert->execute([9, 'resolved', 18, 28, 'normal', null]);
$insert->execute([10, 'resolved', 19, 29, 'normal', null]);
$insert->execute([11, 'assigned', 20, 30, 'normal', '2026-11-01 12:00:00']);
$insert->execute([12, 'manager_review', 21, null, 'normal', null]);
$insert->execute([13, 'assigned', 22, 32, 'normal', null]);
$insert->execute([14, 'new', 23, null, 'normal', null]);
$insert->execute([15, 'assigned', 24, 34, 'normal', null]);
$insert->execute([16, 'closed', 25, 26, 'normal', null]);
$insert->execute([17, 'cancelled', 27, null, 'normal', null]);
$insert->execute([18, 'rejected', 28, null, 'normal', '2026-11-01 14:00:00']);
$pdo->exec("UPDATE tickets SET service_group = 'it', due_at = '2026-11-10 10:00:00', ola_due_at = '2026-11-12 10:00:00' WHERE id = 6");
$pdo->prepare('UPDATE tickets SET first_response_at = ? WHERE id = 13')->execute(['2026-11-01 13:00:00']);

$service = new RequestCenterWorkflowService($pdo);

$messageOutsideTransaction = false;
try {
    $service->appendMessage(1, 10, 'بدون تراکنش');
} catch (LogicException) {
    $messageOutsideTransaction = true;
}
workflow_check($messageOutsideTransaction, 'conversation writes require the caller transaction');

$pdo->beginTransaction();
$firstMessageId = $service->appendMessage(1, 10, 'پاسخ عمومی');
$internalMessageId = $service->appendMessage(1, 10, 'یادداشت داخلی', true);
workflow_check($firstMessageId > 0 && $internalMessageId > $firstMessageId,
    'conversation writer returns ordered inserted identifiers');
$pdo->rollBack();
workflow_check((int) $pdo->query('SELECT COUNT(*) FROM ticket_messages')->fetchColumn() === 0,
    'conversation entries roll back with the caller transaction');

$pdo->beginTransaction();
$internalMessageId = $service->appendMessage(1, 10, 'یادداشت داخلی', true);
$pdo->commit();
$internalMessage = $pdo->query('SELECT is_internal, body FROM ticket_messages WHERE id = ' . (int) $internalMessageId)->fetch(PDO::FETCH_ASSOC);
workflow_check(is_array($internalMessage) && (int) $internalMessage['is_internal'] === 1
    && $internalMessage['body'] === 'یادداشت داخلی',
    'conversation writer preserves internal-note visibility metadata');

$pdo->beginTransaction();
workflow_check(!$service->requestCancellation(14, 14, 'closed', 'لغو درخواست بسته‌شده'),
    'cancellation request rejects a closed ticket even when its owner matches');
workflow_check(!$service->requestCancellation(17, 27, 'cancelled', 'لغو درخواست لغوشده'),
    'cancellation request rejects a terminal cancelled ticket');
workflow_check(!$service->rejectCancellation(18, 'rejected', '2026-11-01 14:00:00'),
    'cancellation decision rejects a rejected ticket outside its allowed lifecycle states');
$pdo->rollBack();

$pdo->beginTransaction();
workflow_check($service->requestCancellation(15, 24, 'assigned', 'نیاز به لغو'),
    'requester cancellation request is recorded under a locked status snapshot');
$pdo->rollBack();
$cancelRollback = $pdo->query('SELECT cancel_requested_at FROM tickets WHERE id = 15')->fetchColumn();
workflow_check($cancelRollback === null, 'cancellation metadata rolls back with the caller transaction');

$pdo->beginTransaction();
workflow_check($service->requestCancellation(15, 24, 'assigned', 'نیاز به لغو'),
    'requester cancellation request can be committed after rollback');
$pdo->commit();
$cancelSnapshot = $pdo->query('SELECT status, cancel_requested_by, cancel_request_reason, cancel_requested_at FROM tickets WHERE id = 15')->fetch(PDO::FETCH_ASSOC);
workflow_check($cancelSnapshot['status'] === 'assigned' && (int) $cancelSnapshot['cancel_requested_by'] === 24
    && $cancelSnapshot['cancel_request_reason'] === 'نیاز به لغو' && !empty($cancelSnapshot['cancel_requested_at']),
    'cancellation request preserves lifecycle status and records its reason');

$pdo->beginTransaction();
workflow_check(!$service->requestCancellation(15, 99, 'assigned', 'درخواست نامعتبر'),
    'cancellation request rejects a non-owner or duplicate request');
$pdo->rollBack();

$pdo->beginTransaction();
$pdo->exec('UPDATE tickets SET assigned_to = 999 WHERE id = 15');
workflow_check(!$service->rejectCancellation(15, 'assigned', (string) $cancelSnapshot['cancel_requested_at'], [
    'requester_id' => 24,
    'assigned_to' => 34,
    'priority' => 'normal',
    'department_id' => null,
]), 'cancellation rejection refuses a stale ownership snapshot');
$pdo->rollBack();

$pdo->beginTransaction();
workflow_check(!$service->rejectCancellation(15, 'assigned', (string) $cancelSnapshot['cancel_requested_at'], [
    'requester_id' => 24,
    'assigned_to' => 34,
    'priority' => 'normal',
    'department_id' => null,
    'service_group' => 'support',
]), 'cancellation rejection refuses a stale routing-scope snapshot');
$pdo->rollBack();

$pdo->beginTransaction();
workflow_check($service->rejectCancellation(15, 'assigned', (string) $cancelSnapshot['cancel_requested_at'], [
    'requester_id' => 24,
    'assigned_to' => 34,
    'priority' => 'normal',
    'department_id' => null,
    'service_group' => null,
]), 'cancellation rejection clears only the exact pending request under a matching snapshot');
$pdo->commit();
$cleared = $pdo->query('SELECT cancel_requested_at, cancel_requested_by, cancel_request_reason FROM tickets WHERE id = 15')->fetch(PDO::FETCH_ASSOC);
workflow_check($cleared['cancel_requested_at'] === null && $cleared['cancel_requested_by'] === null
    && $cleared['cancel_request_reason'] === null,
    'rejected cancellation metadata is cleared atomically');
$pdo->beginTransaction();
$scopeGuardRejected = !$service->transition(6, 'assigned', 'in_progress', [], 'standard', [
    'service_group' => 'support',
]);
workflow_check($scopeGuardRejected,
    'lifecycle transition refuses a stale service-group scope snapshot');
$pdo->rollBack();

$pdo->beginTransaction();
$pdo->exec("UPDATE tickets SET due_at = '2026-11-11 10:00:00' WHERE id = 6");
$staleDeadlineRejected = !$service->transition(6, 'assigned', 'in_progress', [], 'standard', [
    'due_at' => '2026-11-10 10:00:00',
]);
workflow_check($staleDeadlineRejected,
    'lifecycle transition refuses a stale SLA deadline snapshot');
$pdo->rollBack();

workflow_check(
    RequestCenterWorkflowService::allowedTransitions('pending') === ['in_progress', 'waiting_user', 'resolved', 'assigned', 'manager_review'],
    'legacy pending has exit paths'
);
workflow_check(
    !in_array('pending', RequestCenterWorkflowService::allowedTransitions('in_progress'), true),
    'no new transition enters legacy pending'
);
workflow_check(
    RequestCenterWorkflowService::allowedTransitions('rejected') === [],
    'rejected is a terminal lifecycle state'
);

$pdo->beginTransaction();
workflow_check(
    $service->transition(12, 'manager_review', 'rejected', [
        'rejected_at' => RequestCenterSqlExpression::now(),
        'supervisor_id' => 99,
        'supervisor_note' => 'درخواست خارج از دامنه خدمت است',
    ], 'supervisor_reject', [
        'cancel_requested_at' => null,
        'first_response_at' => null,
        'resolved_at' => null,
    ]),
    'supervisor may reject an invalid request before work starts'
);
$pdo->commit();
workflow_check(
    $pdo->query('SELECT status FROM tickets WHERE id = 12')->fetchColumn() === 'rejected',
    'rejection persists as a state distinct from cancellation'
);
workflow_check(
    $pdo->query('SELECT rejected_at FROM tickets WHERE id = 12')->fetchColumn() !== false,
    'rejection timestamp is persisted'
);

$rejectAfterWorkBlocked = false;
$pdo->beginTransaction();
try {
    $service->transition(13, 'assigned', 'rejected', [
        'rejected_at' => RequestCenterSqlExpression::now(),
    ], 'supervisor_reject', [
        'cancel_requested_at' => null,
        'resolved_at' => null,
    ]);
} catch (RuntimeException) {
    $rejectAfterWorkBlocked = true;
}
$pdo->rollBack();
workflow_check($rejectAfterWorkBlocked, 'supervisor cannot reject after first response has started');

$standardRejectBlocked = false;
$pdo->beginTransaction();
try {
    $service->transition(14, 'new', 'rejected', [], 'standard');
} catch (RuntimeException) {
    $standardRejectBlocked = true;
}
$pdo->rollBack();
workflow_check($standardRejectBlocked, 'standard workflow cannot bypass supervisor rejection authorization');

$pdo->beginTransaction();
workflow_check(
    $service->transition(1, 'assigned', 'in_progress', ['sla_paused_at' => null], 'standard'),
    'standard transition writes status and related columns in caller transaction'
);
$pdo->commit();
workflow_check(
    $pdo->query('SELECT status FROM tickets WHERE id = 1')->fetchColumn() === 'in_progress',
    'status persisted after caller commit'
);

$pdo->beginTransaction();
$stale = $service->transition(1, 'assigned', 'resolved', [], 'standard');
$pdo->rollBack();
workflow_check(!$stale, 'stale expected status is rejected without writing');

$blockedPendingTarget = false;
$pdo->beginTransaction();
try {
    $service->transition(1, 'in_progress', 'pending', [], 'standard');
} catch (RuntimeException) {
    $blockedPendingTarget = true;
}
$pdo->rollBack();
workflow_check($blockedPendingTarget, 'standard policy rejects entering legacy pending');

$blockedCancellation = false;
$pdo->beginTransaction();
try {
    $service->transition(3, 'assigned', 'in_progress', [], 'standard');
} catch (RuntimeException) {
    $blockedCancellation = true;
}
$pdo->rollBack();
workflow_check($blockedCancellation, 'cancellation-pending blocks ordinary transitions');

$pdo->beginTransaction();
workflow_check(
    $service->transition(3, 'assigned', 'cancelled', ['cancel_requested_at' => null], 'cancellation_approved'),
    'approved cancellation uses its explicit exceptional transition'
);
$pdo->commit();

$pdo->beginTransaction();
workflow_check(
    $service->transition(4, 'resolved', 'closed', [
        'closed_at' => RequestCenterSqlExpression::now(),
        'supervisor_note' => 'auto close test',
    ], 'auto_close'),
    'auto-close uses its explicit exceptional transition'
);
$pdo->commit();

$pdo->beginTransaction();
$staleSupervisorReopen = $service->transition(16, 'closed', 'manager_review', [
    'closed_at' => null,
    'resolved_at' => null,
    'due_at' => '2026-11-09 10:00:00',
], 'supervisor_reopen', [
    'requester_id' => 25,
    'assigned_to' => 26,
    'priority' => 'urgent',
    'department_id' => null,
    'cancel_requested_at' => null,
]);
$pdo->rollBack();
workflow_check(!$staleSupervisorReopen, 'supervisor reopen refuses stale priority snapshot before recalculating SLA');
workflow_check(
    $pdo->query('SELECT status FROM tickets WHERE id = 16')->fetchColumn() === 'closed',
    'stale supervisor reopen leaves the closed ticket unchanged'
);

$pdo->beginTransaction();
workflow_check(
    $service->transition(5, 'closed', 'in_progress', [
        'closed_at' => null,
        'supervisor_approved_at' => null,
        'supervisor_note' => 'reopened',
    ], 'supervisor_reopen', [
        'requester_id' => 14,
        'assigned_to' => 24,
        'priority' => 'normal',
        'department_id' => null,
        'cancel_requested_at' => null,
    ]),
    'supervisor reopen clears closure metadata under a matching snapshot'
);
$pdo->commit();

$pdo->beginTransaction();
$guarded = $service->transition(6, 'assigned', 'in_progress', [], 'standard', ['priority' => 'urgent']);
$pdo->rollBack();
workflow_check(!$guarded, 'optimistic field guards reject changed priority');

$pdo->beginTransaction();
$staleFirstResponse = $service->transition(13, 'assigned', 'in_progress', [], 'standard', [
    'first_response_at' => null,
]);
$pdo->rollBack();
workflow_check(!$staleFirstResponse, 'optimistic field guards reject a changed first-response timestamp');

$pdo->beginTransaction();
workflow_check(
    $service->transition(7, 'new', 'cancelled', ['due_at' => null], 'requester_cancel', ['requester_id' => 16]),
    'requester can cancel an unassigned request before first response'
);
$pdo->commit();

$pdo->beginTransaction();
workflow_check(
    $service->transition(8, 'resolved', 'in_progress', ['resolved_at' => null], 'requester_reopen'),
    'requester reopen follows the explicit resolved-to-in-progress exception'
);
$pdo->commit();

$pdo->beginTransaction();
workflow_check(
    $service->transition(9, 'resolved', 'in_progress', ['resolved_at' => null, 'supervisor_note' => 'rework'], 'supervisor_rework'),
    'supervisor rework follows its explicit exception'
);
$pdo->commit();

$pdo->beginTransaction();
workflow_check(
    $service->transition(10, 'resolved', 'closed', ['closed_at' => RequestCenterSqlExpression::coalesceNow()], 'supervisor_close'),
    'supervisor close follows its explicit exception'
);
$pdo->commit();

$pdo->beginTransaction();
workflow_check(
    $service->transition(11, 'assigned', 'assigned', ['first_response_at' => RequestCenterSqlExpression::coalesceNow()], 'same_state', [
        'cancel_requested_at' => '2026-11-01 12:00:00',
    ]),
    'same-state conversation metadata can be persisted while cancellation decision is pending'
);
$pdo->commit();

$blockedPendingAssignment = false;
$pdo->beginTransaction();
try {
    $service->transition(11, 'assigned', 'assigned', ['assigned_to' => 31], 'same_state', [
        'cancel_requested_at' => '2026-11-01 12:00:00',
    ]);
} catch (RuntimeException) {
    $blockedPendingAssignment = true;
}
$pdo->rollBack();
workflow_check($blockedPendingAssignment, 'same-state pending-cancellation path cannot change assignment');

$pdo->beginTransaction();
$guardedDepartment = $service->transition(8, 'resolved', 'in_progress', [], 'requester_reopen', ['department_id' => 999]);
$pdo->rollBack();
workflow_check(!$guardedDepartment, 'optimistic field guards reject changed department');

$noTransaction = false;
try {
    $service->transition(6, 'assigned', 'in_progress');
} catch (LogicException) {
    $noTransaction = true;
}
workflow_check($noTransaction, 'service refuses to run outside caller transaction');

$badColumn = false;
$pdo->beginTransaction();
try {
    $service->transition(6, 'assigned', 'in_progress', ['untrusted_sql' => 'NOW()']);
} catch (InvalidArgumentException) {
    $badColumn = true;
}
$pdo->rollBack();
workflow_check($badColumn, 'update column allowlist blocks arbitrary SQL');

echo "PASS: Request Center WorkflowService SQLite contract tests\n";
