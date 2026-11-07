<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/modules/request-center/AppointmentRepository.php';

function check_request_center(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
    echo "ok - {$message}\n";
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, full_name TEXT)');
$pdo->exec('CREATE TABLE tickets (id INTEGER PRIMARY KEY, ticket_no TEXT, subject TEXT)');
$pdo->exec("INSERT INTO users VALUES (1, 'Agent One'), (2, 'Agent Two')");
$pdo->exec("INSERT INTO tickets VALUES (101, 'RC-00101', 'Test request')");
$pdo->exec('CREATE TABLE request_center_appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_id INTEGER NOT NULL, assignee_id INTEGER NOT NULL,
    starts_at TEXT NOT NULL, ends_at TEXT NOT NULL,
    kind TEXT NOT NULL DEFAULT \'work\', status TEXT NOT NULL DEFAULT \'planned\',
    note TEXT NULL, created_by INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

$repo = new AppointmentRepository($pdo);
check_request_center($repo->tableExists(), 'appointment table is detected');

$now = date('Y-m-d 10:00:00', strtotime('+1 day'));
$start = date('Y-m-d H:i:s', strtotime('+1 day 10:00'));
$end = date('Y-m-d H:i:s', strtotime('+1 day 11:30'));

$first = $repo->create(101, 1, $start, $end, 'work', 'یادداشت', 1);
check_request_center($first['ok'] === true, 'first appointment is created');
check_request_center(isset($first['id']), 'created appointment exposes id');

$overlap = $repo->create(101, 1, date('Y-m-d H:i:s', strtotime('+1 day 11:00')), date('Y-m-d H:i:s', strtotime('+1 day 12:00')), 'visit', null, 1);
check_request_center($overlap['ok'] === false, 'overlapping interval for the same assignee is rejected');

$adjacent = $repo->create(101, 1, $end, date('Y-m-d H:i:s', strtotime('+1 day 12:30')), 'work', null, 1);
check_request_center($adjacent['ok'] === true, 'adjacent interval (start == previous end) is accepted');

$otherAssignee = $repo->create(101, 2, $start, $end, 'work', null, 1);
check_request_center($otherAssignee['ok'] === true, 'same interval for a different assignee is accepted');

$invalidKind = $repo->create(101, 1, $start, $end, 'party', null, 1);
check_request_center($invalidKind['ok'] === false, 'invalid kind is rejected');

$reversed = $repo->create(101, 1, $end, $start, 'work', null, 1);
check_request_center($reversed['ok'] === false, 'end before start is rejected');

$mine = $repo->listUpcoming(1, date('Y-m-d', strtotime('+1 day')) . ' 00:00:00', 10);
check_request_center(count($mine) === 2, 'listUpcoming returns own two planned appointments');

$notOwner = $repo->cancel((int) $first['id'], 2, false);
check_request_center($notOwner['ok'] === false, 'cancel by another ordinary agent is rejected');

$elevated = $repo->cancel((int) $first['id'], 2, true);
check_request_center($elevated['ok'] === true, 'elevated user can cancel any appointment');

$cancelledTwice = $repo->cancel((int) $first['id'], 1, false);
check_request_center($cancelledTwice['ok'] === false, 'already cancelled appointment cannot be cancelled again');

echo "ALL-APPOINTMENT-TESTS-PASSED\n";
