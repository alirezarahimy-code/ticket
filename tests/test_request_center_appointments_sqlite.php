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
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, full_name TEXT, role TEXT, is_active INTEGER DEFAULT 1, department_id INTEGER NULL)');
$pdo->exec('CREATE TABLE tickets (id INTEGER PRIMARY KEY, ticket_no TEXT, subject TEXT, status TEXT, due_at TEXT, sla_paused_at TEXT, ola_due_at TEXT)');
$pdo->exec('CREATE TABLE settings (`key` TEXT PRIMARY KEY, `value` TEXT NOT NULL)');
$pdo->exec("INSERT INTO users (id, full_name, role, is_active) VALUES (1, 'Agent One', 'agent', 1), (2, 'Agent Two', 'agent', 1)");
$pdo->exec("INSERT INTO tickets VALUES (101, 'RC-00101', 'Test request', 'in_progress', '2026-11-20 12:00:00', NULL, '2026-11-21 12:00:00')");
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
$ticketClocks = $pdo->query('SELECT status, due_at, sla_paused_at, ola_due_at FROM tickets WHERE id = 101')->fetch(PDO::FETCH_ASSOC);
check_request_center(
    $ticketClocks === ['status' => 'in_progress', 'due_at' => '2026-11-20 12:00:00', 'sla_paused_at' => null, 'ola_due_at' => '2026-11-21 12:00:00'],
    'creating an appointment never changes ticket status or SLA/OLA clocks'
);

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
$ticketClocksAfterCancel = $pdo->query('SELECT status, due_at, sla_paused_at, ola_due_at FROM tickets WHERE id = 101')->fetch(PDO::FETCH_ASSOC);
check_request_center(
    $ticketClocksAfterCancel === $ticketClocks,
    'cancelling an appointment never changes ticket status or SLA/OLA clocks'
);

$cancelledTwice = $repo->cancel((int) $first['id'], 1, false);
check_request_center($cancelledTwice['ok'] === false, 'already cancelled appointment cannot be cancelled again');


// ——— فاز ۴: اتمام، محدوده، ظرفیت و شمارشگرها ———
$pastStart = date('Y-m-d H:i:s', strtotime('-50 minutes'));
$pastEnd = date('Y-m-d H:i:s', strtotime('-5 minutes'));
$pastPlanned = $repo->create(101, 1, $pastStart, $pastEnd, 'work', 'نوبتِ معوقِ گذشته', 1, true);
check_request_center($pastPlanned['ok'] === true, 'past planned appointment is creatable with the elevated outside-window override (retro-record)');

$completeByStranger = $repo->complete((int) $otherAssignee['id'], 1, false);
check_request_center($completeByStranger['ok'] === false, 'complete by another ordinary agent is rejected');

$completeByOwner = $repo->complete((int) $otherAssignee['id'], 2, false); // owner is assignee 2
check_request_center($completeByOwner['ok'] === true, 'owner can complete their own appointment');
$ticketClocksAfterComplete = $pdo->query('SELECT status, due_at, sla_paused_at, ola_due_at FROM tickets WHERE id = 101')->fetch(PDO::FETCH_ASSOC);
check_request_center(
    $ticketClocksAfterComplete === $ticketClocks,
    'completing an appointment never changes ticket status or SLA/OLA clocks'
);

$completeTwice = $repo->complete((int) $otherAssignee['id'], 2, false);
check_request_center($completeTwice['ok'] === false, 'already completed appointment cannot be completed again');

$windowFrom = date('Y-m-d 00:00:00', strtotime('-3 days'));
$windowTo = date('Y-m-d 23:59:59', strtotime('+3 days'));
$rangeMine = $repo->listRange(1, $windowFrom, $windowTo);
check_request_center(count($rangeMine) >= 2, 'listRange returns own appointments in window');
$rangeTeam = $repo->listRange(null, $windowFrom, $windowTo);
check_request_center(count($rangeTeam) >= 3, 'listRange(null) returns the whole team window');
$rangeKind = $repo->listRange(1, $windowFrom, $windowTo, ['kind' => 'visit']);
check_request_center(count($rangeKind) === 0, 'listRange kind filter excludes other kinds');
$rangeDone = $repo->listRange(1, $windowFrom, $windowTo, ['status' => 'done']);
foreach ($rangeDone as $doneRow) {
    check_request_center((string) $doneRow['status'] === 'done', 'listRange status filter keeps only done rows');
}

$capacity = $repo->capacitySummary($windowFrom, $windowTo, date('Y-m-d H:i:s'));
$byAssignee = [];
foreach ($capacity as $capRow) {
    $byAssignee[(int) $capRow['assignee_id']] = (int) $capRow['total'];
}
check_request_center(($byAssignee[1] ?? 0) >= 2 && ($byAssignee[2] ?? 0) >= 1, 'capacitySummary counts per-assignee planned LOAD');

$counts = $repo->countsForRange(1, $windowFrom, $windowTo, date('Y-m-d H:i:s'));
check_request_center($counts['overdue'] >= 1, 'countsForRange reports overdue planned own appointments');
check_request_center($counts['done'] >= 0 && $counts['active'] >= 1, 'countsForRange returns active/done counters');

$cancelledQuality = $repo->cancel((int) $adjacent['id'], 1, false);
check_request_center($cancelledQuality['ok'] === true, 'owner can cancel their own planned appointment');
$capacityAfterCancel = $repo->capacitySummary($windowFrom, $windowTo, date('Y-m-d H:i:s'));
$byAssigneeAfter = [];
foreach ($capacityAfterCancel as $capRow) {
    $byAssigneeAfter[(int) $capRow['assignee_id']] = (int) $capRow['total'];
}
check_request_center(($byAssigneeAfter[1] ?? 0) === (($byAssignee[1] ?? 0) - 1), 'cancelled appointments leave the capacity window');

// ——— تاریخ/ساعت نامعتبر باید reject شود (رفع شکاف createFromFormat بدون getLastErrors) ———
check_request_center(AppointmentRepository::normalizeDateTime('2026-02-31T10:00') === null, 'invalid calendar date (2026-02-31) is rejected, not coerced');
check_request_center(AppointmentRepository::normalizeDateTime('2026-13-10T10:00') === null, 'invalid month (13) is rejected');
check_request_center(AppointmentRepository::normalizeDateTime('2026-00-10T10:00') === null, 'month 00 is rejected');
check_request_center(AppointmentRepository::normalizeDateTime('2026-04-31T10:00') === null, 'April 31st is rejected');
check_request_center(AppointmentRepository::normalizeDateTime('2026-02-29T10:00') === null, 'non-leap 2026-02-29 is rejected');
check_request_center(AppointmentRepository::normalizeDateTime('2028-02-29T10:00') !== null, 'leap 2028-02-29 is accepted');
check_request_center(AppointmentRepository::normalizeDateTime('2026-11-07T24:00') === null, 'hour 24 is rejected');
check_request_center(AppointmentRepository::normalizeDateTime('2026-11-07') === null, 'missing time component is rejected');
check_request_center(AppointmentRepository::normalizeDateTime('2026-11-07T10:60') === null, 'minute 60 is rejected');

echo "ALL-PHASE4-APPOINTMENT-TESTS-PASSED\n";
// ——— اولویت ۳: ساعت کاری روز + سقف ظرفیت روزانه (کارشناس/تیم) — settings key-based، بدون Migration ———
$d2 = date('Y-m-d', strtotime('+2 days'));
$rejectS = $d2 . ' 18:00:00'; $rejectE = $d2 . ' 19:00:00';
$r = $repo->create(101, 1, $rejectS, $rejectE, 'work', null, 1);
check_request_center($r['ok'] === false, 'appointment outside working window (18:00) is rejected');
check_request_center(mb_strpos((string) ($r['error'] ?? ''), 'خارج از ساعات کاری') !== false, 'outside-window error is explicit');

$rOutsideOwn = $repo->create(101, 1, $d2 . ' 07:30:00', $d2 . ' 08:00:00', 'work', null, 1);
check_request_center($rOutsideOwn['ok'] === false, 'appointment starting before the window (07:30) is rejected');

$rOverride = $repo->create(101, 1, $rejectS, $rejectE, 'work', null, 1, true);
check_request_center($rOverride['ok'] === true, 'elevated allowOutsideBusinessHours permits 18:00 booking');

$pdo->exec("INSERT INTO settings (`key`,`value`) VALUES ('rc.work_hours_user_1','17:00-20:00')");
$rUserOverride = $repo->create(101, 1, $d2 . ' 19:00:00', $d2 . ' 19:45:00', 'work', null, 1);
check_request_center($rUserOverride['ok'] === true, 'per-user work-hours override (rc.work_hours_user_1) allows 18:00 for user 1');
$rOtherReject = $repo->create(101, 2, $rejectS, $rejectE, 'work', null, 1);
check_request_center($rOtherReject['ok'] === false, 'per-user override does NOT leak to other users');
$pdo->exec("DELETE FROM settings WHERE `key` = 'rc.work_hours_user_1'");

$capDay = date('Y-m-d', strtotime('+3 days'));
$repo->create(101, 1, $capDay . ' 08:30:00', $capDay . ' 09:15:00', 'work', null, 1);
$repo->create(101, 1, $capDay . ' 10:00:00', $capDay . ' 10:45:00', 'work', null, 1);
$pdo->exec("INSERT INTO settings (`key`,`value`) VALUES ('rc.daily_capacity_assignee','2')");
$rCap = $repo->create(101, 1, $capDay . ' 12:00:00', $capDay . ' 12:45:00', 'work', null, 1);
check_request_center($rCap['ok'] === false, 'third booking of the day is rejected once the assignee cap is hit');
check_request_center(mb_strpos((string) ($rCap['error'] ?? ''), 'ظرفیت روزانهٔ این کارشناس') !== false, 'capacity error is explicit (assignee)');
$pdo->exec("DELETE FROM settings WHERE `key` = 'rc.daily_capacity_assignee'");

$pdo->exec("UPDATE users SET department_id = 7 WHERE id IN (1, 2)");
$teamDay = date('Y-m-d', strtotime('+4 days'));
$pdo->exec("INSERT INTO settings (`key`,`value`) VALUES ('rc.team_daily_capacity_dept_7','3')");
$pdo->exec("INSERT INTO settings (`key`,`value`) VALUES ('rc.daily_capacity_assignee','8')");
$t1 = $repo->create(101, 1, $teamDay . ' 09:00:00', $teamDay . ' 09:45:00', 'work', null, 1);
$t2 = $repo->create(101, 2, $teamDay . ' 09:00:00', $teamDay . ' 09:45:00', 'work', null, 1);
$t3 = $repo->create(101, 1, $teamDay . ' 11:00:00', $teamDay . ' 11:45:00', 'work', null, 1);
$t4 = $repo->create(101, 2, $teamDay . ' 11:00:00', $teamDay . ' 11:45:00', 'work', null, 1);
check_request_center($t1['ok'] === true && $t2['ok'] === true && $t3['ok'] === true, 'team of 3 appointments succeed up to the dept cap');
check_request_center($t4['ok'] === false, 'fourth appointment under the dept cap is rejected');
check_request_center(mb_strpos((string) ($t4['error'] ?? ''), 'ظرفیت روزانهٔ تیم') !== false, 'capacity error is explicit (team)');

$pdo->exec("DELETE FROM settings WHERE `key` = 'rc.team_daily_capacity_dept_7'");
$t4b = $repo->create(101, 2, $teamDay . ' 11:00:00', $teamDay . ' 11:45:00', 'work', null, 1);
check_request_center($t4b['ok'] === true, 'removing the dept override restores the global team cap');
$pdo->exec("DELETE FROM settings WHERE `key` = 'rc.daily_capacity_assignee'");

$defaultDay = date('Y-m-d', strtotime('+5 days')) . ' 10:00:00';
$defaultEnd = date('Y-m-d', strtotime('+5 days')) . ' 10:45:00';
$dOk = $repo->create(101, 1, $defaultDay, $defaultEnd, 'work', null, 1);
check_request_center($dOk['ok'] === true, 'defaults restored: booking inside the global window with empty settings works');
echo "ALL-PHASE3-WORKING-HOURS-AND-CAPACITY-TESTS-PASSED\n";
