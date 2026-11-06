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
