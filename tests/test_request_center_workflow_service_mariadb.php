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
    priority VARCHAR(20) NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    due_at DATETIME NULL,
    assigned_at DATETIME NULL,
    closed_at DATETIME NULL,
    rejected_at DATETIME NULL,
    supervisor_id BIGINT UNSIGNED NULL,
    supervisor_approved_at DATETIME NULL,
    supervisor_note TEXT NULL,
    sla_paused_at DATETIME NULL,
    sla_pause_minutes INT NOT NULL DEFAULT 0,
    updated_at DATETIME NULL
) ENGINE=InnoDB');

$insert = $pdo->prepare(
    'INSERT INTO tickets (id,status,requester_id,assigned_to,priority,cancel_requested_at) VALUES (?,?,?,?,?,?)'
);
$insert->execute([1, 'assigned', 101, 201, 'normal', null]);
$insert->execute([2, 'in_progress', 102, 202, 'urgent', null]);
$insert->execute([3, 'assigned', 103, 203, 'normal', '2026-11-01 10:00:00']);
$insert->execute([4, 'assigned', 104, 204, 'normal', null]);
$insert->execute([5, 'new', 105, null, 'normal', null]);
$insert->execute([6, 'assigned', 106, 206, 'normal', null]);
$pdo->exec("UPDATE tickets SET first_response_at = '2026-11-08 09:00:00' WHERE id = 6");

$service = new RequestCenterWorkflowService($pdo);

$pdo->beginTransaction();
$rejected = $service->transition(5, 'new', 'rejected', [
    'rejected_at' => RequestCenterSqlExpression::now(),
    'supervisor_note' => 'invalid request',
], 'supervisor_reject', [
    'cancel_requested_at' => null,
    'first_response_at' => null,
    'resolved_at' => null,
]);
mariadb_workflow_check($rejected, 'MariaDB locks and permits explicit pre-service rejection');
$pdo->commit();
$row = $pdo->query('SELECT status, rejected_at FROM tickets WHERE id = 5')->fetch();
mariadb_workflow_check($row['status'] === 'rejected' && $row['rejected_at'] !== null, 'MariaDB persists terminal rejection and timestamp');

$pdo->beginTransaction();
$lateRejection = $service->transition(6, 'assigned', 'rejected', [
    'rejected_at' => RequestCenterSqlExpression::now(),
], 'supervisor_reject', [
    'cancel_requested_at' => null,
    'first_response_at' => null,
    'resolved_at' => null,
]);
$pdo->rollBack();
mariadb_workflow_check(!$lateRejection, 'MariaDB rejects pre-service rejection after first response');

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

echo 'PASS: MariaDB WorkflowService integration contract' . PHP_EOL;
