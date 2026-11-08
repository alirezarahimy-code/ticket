<?php
declare(strict_types=1);

function ticket_scope(array $user, string $alias = 't'): array
{
    return ["WHERE {$alias}.requester_id = ?", [(int) $user['id']]];
}

function ticket_user_can_view(array $ticket, array $user): bool
{
    return (int) ($ticket['requester_id'] ?? 0) === (int) ($user['id'] ?? 0);
}

function check_request_center(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
    echo "ok - {$message}\n";
}

require_once dirname(__DIR__) . '/modules/request-center/RequestCenterRepository.php';
require_once dirname(__DIR__) . '/modules/request-center/WorkflowService.php';

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');
$dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
// Mirror the organization provider's install-time schema setup in this disposable CI database.
$pdo->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS org_unit_id INT UNSIGNED NULL');
$pdo->exec('CREATE TABLE IF NOT EXISTS org_unit_managers (
    unit_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (unit_id, user_id),
    INDEX idx_org_mgr_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$repository = new RequestCenterRepository($pdo);
$problems = $repository->schemaProblems();
if ($problems !== []) {
    fwrite(STDERR, "FAIL: Request Center schema preflight\n" . implode("\n", $problems) . "\n");
    exit(1);
}
$department = $pdo->prepare('INSERT INTO departments (name) VALUES (?)');
$department->execute(['RC Contract Department']);
$departmentId = (int) $pdo->lastInsertId();

$userInsert = $pdo->prepare('INSERT INTO users (username, full_name, role, department_id) VALUES (?, ?, ?, ?)');
$userInsert->execute(['rc-requester-' . bin2hex(random_bytes(4)), 'RC Requester', 'user', $departmentId]);
$requesterId = (int) $pdo->lastInsertId();
$userInsert->execute(['rc-agent-' . bin2hex(random_bytes(4)), 'RC Agent', 'agent', $departmentId]);
$agentId = (int) $pdo->lastInsertId();

$category = $pdo->prepare('INSERT INTO categories (name, service_group) VALUES (?, ?)');
$category->execute(['RC Hardware', 'it']);
$categoryId = (int) $pdo->lastInsertId();

$service = $pdo->prepare('INSERT INTO service_catalog (name, code, category_id, service_group) VALUES (?, ?, ?, ?)');
$service->execute(['RC Support', 'RC-' . strtoupper(bin2hex(random_bytes(3))), $categoryId, 'it']);
$serviceId = (int) $pdo->lastInsertId();

$asset = $pdo->prepare('INSERT INTO assets (asset_tag, hostname, department_id) VALUES (?, ?, ?)');
$asset->execute(['RC-' . strtoupper(bin2hex(random_bytes(4))), 'RC-PC', $departmentId]);
$assetId = (int) $pdo->lastInsertId();

$unit = $pdo->prepare('INSERT INTO org_units (company_id, name, unit_type) VALUES (1, ?, ?)');
$unit->execute(['RC Unit', 'department']);
$unitId = (int) $pdo->lastInsertId();

$ticket = $pdo->prepare(
    'INSERT INTO tickets (subject, description, requester_id, assigned_to, department_id,
                          category_id, service_id, asset_id, requesting_unit_id, service_group, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$ticket->execute([
    'RC Visible Request', 'Request Center MariaDB integration fixture', $requesterId, $agentId,
    $departmentId, $categoryId, $serviceId, $assetId, $unitId, 'it', 'assigned',
]);
$ticketId = (int) $pdo->lastInsertId();

$userInsert->execute(['rc-rejection-requester-' . bin2hex(random_bytes(4)), 'RC Rejection Requester', 'user', $departmentId]);
$rejectionRequesterId = (int) $pdo->lastInsertId();

$rejectionTicket = $pdo->prepare(
    'INSERT INTO tickets (subject, description, requester_id, assigned_to, department_id,
                          category_id, service_id, asset_id, requesting_unit_id, service_group, status)
     VALUES (?, ?, ?, NULL, ?, ?, ?, NULL, NULL, ?, ?)'
);
$rejectionTicket->execute([
    'RC Invalid Request', 'Invalid service scope fixture', $rejectionRequesterId,
    $departmentId, $categoryId, $serviceId, 'it', 'manager_review',
]);
$rejectionTicketId = (int) $pdo->lastInsertId();

$workflow = new RequestCenterWorkflowService($pdo);
$pdo->beginTransaction();
$transitioned = $workflow->transition(
    $ticketId,
    'assigned',
    'in_progress',
    ['first_response_at' => RequestCenterSqlExpression::coalesceNow()],
    'standard',
    ['assigned_to' => $agentId, 'cancel_requested_at' => null]
);
if (!$transitioned) {
    $pdo->rollBack();
    throw new RuntimeException('MariaDB WorkflowService did not apply a valid transition');
}
$pdo->commit();
$persisted = $pdo->prepare('SELECT status, first_response_at FROM tickets WHERE id = ?');
$persisted->execute([$ticketId]);
$persistedState = $persisted->fetch();
check_request_center(
    $persistedState['status'] === 'in_progress' && !empty($persistedState['first_response_at']),
    'MariaDB WorkflowService locks, validates and persists status with first-response metadata'
);

$pdo->beginTransaction();
$rejected = $workflow->transition(
    $rejectionTicketId,
    'manager_review',
    'rejected',
    [
        'rejected_at' => RequestCenterSqlExpression::now(),
        'supervisor_id' => $agentId,
        'supervisor_note' => 'درخواست خارج از دامنه خدمت است',
    ],
    'supervisor_reject',
    ['cancel_requested_at' => null, 'first_response_at' => null, 'resolved_at' => null]
);
if (!$rejected) {
    $pdo->rollBack();
    throw new RuntimeException('MariaDB WorkflowService did not apply a valid supervisor rejection');
}
$pdo->commit();
$rejectedState = $pdo->prepare('SELECT status, rejected_at FROM tickets WHERE id = ?');
$rejectedState->execute([$rejectionTicketId]);
$rejectedRow = $rejectedState->fetch();
check_request_center(
    $rejectedRow['status'] === 'rejected' && !empty($rejectedRow['rejected_at']),
    'MariaDB persists rejected as a distinct terminal state with timestamp'
);

$pdo->beginTransaction();
$staleTransition = $workflow->transition($ticketId, 'assigned', 'resolved', [], 'standard');
$pdo->rollBack();
check_request_center(!$staleTransition, 'MariaDB WorkflowService rejects a stale expected status');


$message = $pdo->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, ?)');
$message->execute([$ticketId, $requesterId, 'Public message', 0]);
$publicMessageId = (int) $pdo->lastInsertId();
$message->execute([$ticketId, $agentId, 'Internal note', 1]);
$internalMessageId = (int) $pdo->lastInsertId();

$attachment = $pdo->prepare(
    'INSERT INTO ticket_attachments (message_id, original_name, stored_name, mime, size_bytes) VALUES (?, ?, ?, ?, ?)'
);
$attachment->execute([$internalMessageId, 'internal.pdf', 'private-' . bin2hex(random_bytes(6)) . '.pdf', 'application/pdf', 1234]);

$repository = new RequestCenterRepository($pdo);
$visibleUser = ['id' => $requesterId, 'role' => 'user'];
$list = $repository->listVisible($visibleUser, 1, 20);
check_request_center($list['total'] === 1, 'MariaDB list count uses the shared scope predicate');
check_request_center(count($list['rows']) === 1 && (int) $list['rows'][0]['id'] === $ticketId, 'MariaDB list returns the scoped request');
$personalQueue = $repository->listVisible($visibleUser, 1, 20, ['assigned_to' => $agentId]);
check_request_center($personalQueue['total'] === 1, 'MariaDB personal queue applies the assigned-agent filter');
$otherQueue = $repository->listVisible($visibleUser, 1, 20, ['assigned_to' => $requesterId]);
check_request_center($otherQueue['total'] === 0, 'MariaDB personal queue does not return tickets assigned to another account');
$requesterOnly = $repository->listVisible($visibleUser, 1, 20, ['requester_id' => $requesterId]);
check_request_center($requesterOnly['total'] === 1, 'MariaDB requester workspace remains constrained to the signed-in requester');
$detail = $repository->findVisible($ticketId, $visibleUser);
check_request_center(is_array($detail) && $detail['service_name'] === 'RC Support', 'MariaDB detail joins service and reference data');

$publicMessages = $repository->messages($ticketId, false);
check_request_center(count($publicMessages) === 1 && $publicMessages[0]['body'] === 'Public message', 'MariaDB query excludes internal notes');
check_request_center($repository->attachmentsForMessages($publicMessages) === [], 'MariaDB query does not expose hidden-note attachments');

$staffMessages = $repository->messages($ticketId, true);
$staffAttachments = $repository->attachmentsForMessages($staffMessages);
check_request_center(
    count($staffAttachments[$internalMessageId] ?? []) === 1
        && !array_key_exists('stored_name', $staffAttachments[$internalMessageId][0]),
    'MariaDB attachment projection omits storage names'
);

echo "PASS: Request Center schema and repository integration on MariaDB " .
    $pdo->query('SELECT VERSION()')->fetchColumn() . "\n";
