<?php
declare(strict_types=1);

/**
 * MariaDB integration contract for RequestCenterWorkflowService.
 *
 * Uses a connection-local TEMPORARY TABLE named tickets. It shadows the
 * permanent table for this connection and disappears when the process exits.
 * No production rows or persistent schema objects are touched.
 */
require_once dirname(__DIR__) . '/modules/request-center/WorkflowService.php';

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
    $user,
    $pass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

function mariadb_workflow_check(bool $condition, string $label): void
{
    if (!$condition) {
        throw new RuntimeException('FAIL ' . $label);
    }
    echo 'PASS ' . $label . PHP_EOL;
}

$version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
if (stripos($version, 'MariaDB') === false) {
    throw new RuntimeException('Expected MariaDB, received: ' . $version);
}
echo 'MariaDB WorkflowService integration: ' . $version . PHP_EOL;

$pdo->exec('CREATE TEMPORARY TABLE tickets (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    status VARCHAR(32) NOT NULL,
    requester_id BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    first_response_at DATETIME NULL,
    resolved_at DATETIME NULL,
    cancel_requested_at DATETIME NULL,
    cancel_requested_by BIGINT UNSIGNED NULL,
    cancel_request_reason VARCHAR(1000) NULL,
    priority VARCHAR(20) NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    service_group VARCHAR(20) NULL,
    due_at DATETIME NULL,
    assigned_at DATETIME NULL,
    closed_at DATETIME NULL,
    supervisor_id BIGINT UNSIGNED NULL,
    supervisor_approved_at DATETIME NULL,
    supervisor_note TEXT NULL,
    sla_minutes INT NULL,
    sla_paused_at DATETIME NULL,
    sla_pause_minutes INT NOT NULL DEFAULT 0,
    ola_policy_id BIGINT UNSIGNED NULL,
    ola_response_due_at DATETIME NULL,
    ola_due_at DATETIME NULL,
    ola_escalation_1_at DATETIME NULL,
    ola_escalation_2_at DATETIME NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB');

$pdo->exec('CREATE TEMPORARY TABLE ticket_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB');

$insert = $pdo->prepare(
    'INSERT INTO tickets (id,status,requester_id,assigned_to,priority,cancel_requested_at) VALUES (?,?,?,?,?,?)'
);
$insert->execute([1, 'assigned', 101, 201, 'normal', null]);
$insert->execute([2, 'in_progress', 102, 202, 'urgent', null]);
$insert->execute([3, 'assigned', 103, 203, 'normal', '2026-11-01 10:00:00']);
$insert->execute([4, 'assigned', 104, 204, 'normal', null]);
$insert->execute([5, 'assigned', 105, 205, 'normal', null]);
$insert->execute([6, 'closed', 106, 206, 'normal', null]);
$pdo->exec("UPDATE tickets SET service_group = 'it', due_at = '2026-11-10 10:00:00', ola_due_at = '2026-11-12 10:00:00' WHERE id = 4");

$service = new RequestCenterWorkflowService($pdo);

$pdo->beginTransaction();
$pdo->exec("UPDATE tickets SET first_response_at = '2026-11-08 09:00:00' WHERE id = 4");
$staleFirstResponse = $service->transition(4, 'assigned', 'in_progress', [], 'standard', [
    'first_response_at' => null,
]);
mariadb_workflow_check(!$staleFirstResponse, 'MariaDB optimistic guard rejects a stale first-response timestamp');
$pdo->rollBack();

// The MySQL driver branch appends FOR UPDATE; successful execution here proves
// that the production locking query is accepted by the target database engine.
$pdo->beginTransaction();
$changed = $service->transition(
    1,
    'assigned',
    'in_progress',
    ['first_response_at' => RequestCenterSqlExpression::coalesceNow()],
    'standard'
);
mariadb_workflow_check($changed, 'MariaDB FOR UPDATE transition executes and changes the locked row');
$pdo->commit();
$row = $pdo->query('SELECT status, first_response_at FROM tickets WHERE id = 1')->fetch();
mariadb_workflow_check(
    $row['status'] === 'in_progress' && $row['first_response_at'] !== null,
    'MariaDB transition persists status and COALESCE(NOW()) timestamp'
);

// A caller-owned rollback must undo both the status and associated metadata.
$pdo->beginTransaction();
$changed = $service->transition(
    2,
    'in_progress',
    'resolved',
    ['resolved_at' => RequestCenterSqlExpression::now()],
    'standard'
);
mariadb_workflow_check($changed, 'MariaDB accepts a second transition before caller rollback');
$pdo->rollBack();
$row = $pdo->query('SELECT status, resolved_at FROM tickets WHERE id = 2')->fetch();
mariadb_workflow_check(
    $row['status'] === 'in_progress' && $row['resolved_at'] === null,
    'caller rollback restores status and related timestamp atomically'
);

// A stale status and a stale optimistic field must both be non-mutating.
$pdo->beginTransaction();
$staleServiceGroup = $service->transition(4, 'assigned', 'in_progress', [], 'standard', [
    'service_group' => 'support',
]);
$pdo->rollBack();
mariadb_workflow_check(!$staleServiceGroup, 'routing-scope guard rejects a stale service group under MariaDB row lock');

$pdo->beginTransaction();
$pdo->exec("UPDATE tickets SET due_at = '2026-11-11 10:00:00' WHERE id = 4");
$staleDeadline = $service->transition(4, 'assigned', 'in_progress', [], 'standard', [
    'due_at' => '2026-11-10 10:00:00',
]);
$pdo->rollBack();
mariadb_workflow_check(!$staleDeadline, 'SLA deadline guard rejects stale computed data under MariaDB row lock');

$pdo->beginTransaction();
$staleStatus = $service->transition(1, 'assigned', 'resolved');
$pdo->rollBack();
mariadb_workflow_check(!$staleStatus, 'stale expected status is rejected');

$pdo->beginTransaction();
$stalePriority = $service->transition(
    4,
    'assigned',
    'in_progress',
    [],
    'standard',
    ['priority' => 'critical']
);
$pdo->rollBack();
mariadb_workflow_check(!$stalePriority, 'optimistic priority guard rejects stale input');

$pdo->beginTransaction();
$staleReopen = $service->transition(
    6,
    'closed',
    'in_progress',
    ['closed_at' => null, 'resolved_at' => null, 'due_at' => '2026-11-09 10:00:00'],
    'supervisor_reopen',
    [
        'requester_id' => 106,
        'assigned_to' => 206,
        'priority' => 'urgent',
        'department_id' => null,
        'cancel_requested_at' => null,
    ]
);
$pdo->rollBack();
mariadb_workflow_check(!$staleReopen, 'supervisor reopen rejects a stale priority snapshot under MariaDB row lock');
$row = $pdo->query('SELECT status FROM tickets WHERE id = 6')->fetch();
mariadb_workflow_check($row['status'] === 'closed', 'stale supervisor reopen leaves the closed ticket unchanged');

// Cancellation-pending blocks ordinary transitions even after the row is locked.
$blockedCancellation = false;
$pdo->beginTransaction();
try {
    $service->transition(3, 'assigned', 'in_progress');
} catch (RuntimeException) {
    $blockedCancellation = true;
}
$pdo->rollBack();
mariadb_workflow_check($blockedCancellation, 'pending cancellation blocks ordinary status transition');

// Column names are never accepted from untrusted caller input.
$blockedColumn = false;
$pdo->beginTransaction();
try {
    $service->transition(4, 'assigned', 'in_progress', ['status = status; DROP TABLE tickets' => 'x']);
} catch (InvalidArgumentException) {
    $blockedColumn = true;
}
$pdo->rollBack();
mariadb_workflow_check($blockedColumn, 'update allowlist rejects arbitrary SQL column input');

// Conversation writes and cancellation metadata share the caller transaction.
$pdo->beginTransaction();
$messageId = $service->appendMessage(1, 101, 'MariaDB reply', false);
mariadb_workflow_check($messageId > 0, 'message insert returns an identifier on MariaDB');
$pdo->rollBack();
$count = (int) $pdo->query('SELECT COUNT(*) FROM ticket_messages')->fetchColumn();
mariadb_workflow_check($count === 0, 'caller rollback removes the conversation insert');

$pdo->beginTransaction();
$requested = $service->requestCancellation(5, 105, 'assigned', 'duplicate request');
mariadb_workflow_check($requested, 'cancellation request locks and updates the expected owner/status row');
$pdo->commit();
$cancel = $pdo->query('SELECT status, cancel_requested_by, cancel_request_reason, cancel_requested_at FROM tickets WHERE id = 5')->fetch();
mariadb_workflow_check($cancel['status'] === 'assigned' && (int) $cancel['cancel_requested_by'] === 105
    && $cancel['cancel_request_reason'] === 'duplicate request' && $cancel['cancel_requested_at'] !== null,
    'cancellation metadata persists without changing lifecycle status');

$pdo->beginTransaction();
$pdo->exec('UPDATE tickets SET assigned_to = 999 WHERE id = 5');
$staleClear = $service->rejectCancellation(5, 'assigned', (string) $cancel['cancel_requested_at'], [
    'requester_id' => 105,
    'assigned_to' => 205,
    'priority' => 'normal',
    'department_id' => null,
    'service_group' => null,
]);
mariadb_workflow_check(!$staleClear, 'cancellation rejection refuses stale ownership snapshot under InnoDB row lock');
$pdo->rollBack();

$pdo->beginTransaction();
$cleared = $service->rejectCancellation(5, 'assigned', (string) $cancel['cancel_requested_at'], [
    'requester_id' => 105,
    'assigned_to' => 205,
    'priority' => 'normal',
    'department_id' => null,
    'service_group' => null,
]);
mariadb_workflow_check($cleared, 'cancellation rejection matches exact timestamp and ownership snapshot');
$pdo->commit();
$cancel = $pdo->query('SELECT cancel_requested_at, cancel_requested_by, cancel_request_reason FROM tickets WHERE id = 5')->fetch();
mariadb_workflow_check($cancel['cancel_requested_at'] === null && $cancel['cancel_requested_by'] === null
    && $cancel['cancel_request_reason'] === null,
    'cancellation rejection clears metadata atomically');

echo 'PASS: MariaDB WorkflowService integration contract' . PHP_EOL;
