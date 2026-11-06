<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/modules/request-center/RequestCenterRepository.php';

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

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, full_name TEXT, role TEXT)');
$pdo->exec('CREATE TABLE departments (id INTEGER PRIMARY KEY, name TEXT)');
$pdo->exec('CREATE TABLE categories (id INTEGER PRIMARY KEY, name TEXT)');
$pdo->exec('CREATE TABLE service_catalog (id INTEGER PRIMARY KEY, name TEXT)');
$pdo->exec('CREATE TABLE assets (id INTEGER PRIMARY KEY, hostname TEXT, asset_tag TEXT)');
$pdo->exec('CREATE TABLE org_units (id INTEGER PRIMARY KEY, name TEXT)');
$pdo->exec('CREATE TABLE tickets (
    id INTEGER PRIMARY KEY, ticket_no TEXT, subject TEXT, status TEXT, priority TEXT,
    service_group TEXT, ticket_type TEXT, created_at TEXT, updated_at TEXT, due_at TEXT,
    requester_id INTEGER, assigned_to INTEGER, department_id INTEGER, category_id INTEGER,
    service_id INTEGER, asset_id INTEGER, requesting_unit_id INTEGER
)');
$pdo->exec('CREATE TABLE ticket_messages (
    id INTEGER PRIMARY KEY, ticket_id INTEGER, user_id INTEGER, body TEXT,
    is_internal INTEGER, created_at TEXT
)');
$pdo->exec('CREATE TABLE ticket_attachments (
    id INTEGER PRIMARY KEY, message_id INTEGER, original_name TEXT, stored_name TEXT,
    mime TEXT, size_bytes INTEGER
)');

$pdo->exec("INSERT INTO users VALUES (1, 'Requester One', 'user'), (2, 'Requester Two', 'user'), (3, 'Agent Three', 'agent')");
$pdo->exec("INSERT INTO departments VALUES (10, 'IT')");
$pdo->exec("INSERT INTO categories VALUES (20, 'Hardware')");
$pdo->exec("INSERT INTO service_catalog VALUES (30, 'Computer support')");
$pdo->exec("INSERT INTO assets VALUES (40, 'PC-01', 'ASSET-01')");
$pdo->exec("INSERT INTO org_units VALUES (50, 'Deputy')");
$pdo->exec("INSERT INTO tickets VALUES
    (101, 'IT-00101', 'Visible request', 'assigned', 'normal', 'it', 'incident', '2026-11-01', '2026-11-02', '2026-11-05', 1, 3, 10, 20, 30, 40, 50),
    (202, 'IT-00202', 'Private request', 'assigned', 'urgent', 'it', 'incident', '2026-11-01', '2026-11-02', '2026-11-04', 2, 3, 10, 20, 30, 40, 50)");
$pdo->exec("INSERT INTO ticket_messages VALUES
    (1, 101, 1, 'Public message', 0, '2026-11-01 10:00:00'),
    (2, 101, 3, 'Internal note', 1, '2026-11-01 11:00:00')");
$pdo->exec("INSERT INTO ticket_attachments VALUES
    (900, 2, 'internal.pdf', 'secret-name.pdf', 'application/pdf', 1234)");

$repository = new RequestCenterRepository($pdo);
$user = ['id' => 1, 'role' => 'user'];
$list = $repository->listVisible($user, 1, 50);
check_request_center($list['total'] === 1, 'list count is constrained by the shared scope predicate');
check_request_center(count($list['rows']) === 1 && (int) $list['rows'][0]['id'] === 101, 'list does not return another requester ticket');
$searched = $repository->listVisible($user, 1, 50, ['q' => 'Visible']);
check_request_center($searched['total'] === 1, 'search returns a matching in-scope request');
$privateSearch = $repository->listVisible($user, 1, 50, ['q' => 'Private']);
check_request_center($privateSearch['total'] === 0, 'search cannot reveal another requester ticket');
$filtered = $repository->listVisible($user, 1, 50, ['status' => 'assigned', 'priority' => 'normal', 'service_group' => 'it']);
check_request_center($filtered['total'] === 1, 'status, priority and service-group filters compose with the access scope');
$crossRequesterFilter = $repository->listVisible($user, 1, 50, ['requester_id' => 2]);
check_request_center($crossRequesterFilter['total'] === 0, 'requester workspace filter cannot override the shared access scope');
$ownRequesterFilter = $repository->listVisible($user, 1, 50, ['requester_id' => 1]);
check_request_center($ownRequesterFilter['total'] === 1, 'requester workspace can explicitly constrain the list to the signed-in requester');
$injection = $repository->listVisible($user, 1, 50, ['q' => "' OR 1=1 --"]);
check_request_center($injection['total'] === 0, 'search input remains parameterized and cannot broaden visibility');

$visible = $repository->findVisible(101, $user);
check_request_center(is_array($visible) && $visible['subject'] === 'Visible request', 'visible detail loads joined display data');
check_request_center($repository->findVisible(202, $user) === null, 'out-of-scope detail is indistinguishable from missing detail');

$publicMessages = $repository->messages(101, false);
check_request_center(count($publicMessages) === 1 && $publicMessages[0]['body'] === 'Public message', 'internal messages are excluded in SQL');
$publicAttachments = $repository->attachmentsForMessages($publicMessages);
check_request_center($publicAttachments === [], 'attachments belonging only to hidden messages are not exposed');

$staffMessages = $repository->messages(101, true);
check_request_center(count($staffMessages) === 2, 'authorized internal view can retrieve both message classes');
$staffAttachments = $repository->attachmentsForMessages($staffMessages);
check_request_center(
    count($staffAttachments[2] ?? []) === 1
        && $staffAttachments[2][0]['original_name'] === 'internal.pdf'
        && !array_key_exists('stored_name', $staffAttachments[2][0]),
    'attachment projection exposes metadata but never storage paths'
);

echo "PASS: Request Center repository SQLite contract tests\n";
