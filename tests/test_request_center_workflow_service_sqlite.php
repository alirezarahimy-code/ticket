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
    cancel_requested_at TEXT NULL, priority TEXT NOT NULL, due_at TEXT NULL,
    assigned_at TEXT NULL, closed_at TEXT NULL, supervisor_id INTEGER NULL,
    supervisor_approved_at TEXT NULL, supervisor_note TEXT NULL,
    sla_paused_at TEXT NULL, sla_pause_minutes INTEGER NOT NULL DEFAULT 0,
    updated_at TEXT NULL
)');
$insert = $pdo->prepare('INSERT INTO tickets (id,status,requester_id,assigned_to,priority,cancel_requested_at) VALUES (?,?,?,?,?,?)');
$insert->execute([1, 'assigned', 10, 20, 'normal', null]);
$insert->execute([2, 'pending', 11, 21, 'urgent', null]);
$insert->execute([3, 'assigned', 12, 22, 'normal', '2026-11-01 10:00:00']);
$insert->execute([4, 'resolved', 13, 23, 'normal', null]);
$insert->execute([5, 'closed', 14, 24, 'normal', null]);
$insert->execute([6, 'assigned', 15, 25, 'normal', null]);

$service = new RequestCenterWorkflowService($pdo);
workflow_check(
    RequestCenterWorkflowService::allowedTransitions('pending') === ['in_progress', 'waiting_user', 'resolved', 'assigned', 'manager_review'],
    'legacy pending has exit paths'
);
workflow_check(
    !in_array('pending', RequestCenterWorkflowService::allowedTransitions('in_progress'), true),
    'no new transition enters legacy pending'
);

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
workflow_check(
    $service->transition(5, 'closed', 'in_progress', [
        'closed_at' => null,
        'supervisor_approved_at' => null,
        'supervisor_note' => 'reopened',
    ], 'supervisor_reopen'),
    'supervisor reopen clears closure metadata'
);
$pdo->commit();

$pdo->beginTransaction();
$guarded = $service->transition(6, 'assigned', 'in_progress', [], 'standard', ['priority' => 'urgent']);
$pdo->rollBack();
workflow_check(!$guarded, 'optimistic field guards reject changed priority');

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
