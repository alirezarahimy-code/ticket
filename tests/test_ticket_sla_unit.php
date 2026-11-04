<?php
declare(strict_types=1);

// Tests use an in-memory event stub, never the production database.
const APP_ROOT = __DIR__ . '/..';
function cfg(string $key, mixed $default = null): mixed { return $key === 'app.timezone' ? 'Asia/Tehran' : $default; }
function ticket_is_waiting_status(string $status): bool { return in_array($status, ['waiting_user', 'pending'], true); }
function is_sla_holiday(DateTimeImmutable $date): bool { return false; }
class SlaFakeStatement {
    public function execute(array $parameters): void {}
    public function fetchColumn(): mixed { return $GLOBALS['fake_reopened_at'] ?? false; }
}
class SlaFakeDb {
    public function prepare(string $sql): SlaFakeStatement { return new SlaFakeStatement(); }
}
function db(): SlaFakeDb { static $db; return $db ??= new SlaFakeDb(); }
require APP_ROOT . '/lib/tickets-sla.php';
function sla_check(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
    echo "ok - $message\n";
}
$zone = new DateTimeZone('Asia/Tehran');
$ticket = [
    'id'=>1, 'created_at'=>'2026-01-01 09:00:00', 'priority'=>'normal',
    'status'=>'in_progress', 'due_at'=>'2026-11-15 10:00:00',
    'sla_pause_minutes'=>0, 'sla_paused_at'=>null,
];
[$due] = ticket_sla_transition($ticket, 'in_progress', 'normal');
sla_check($due === $ticket['due_at'], 'ordinary reply preserves deadline after reopen');
$GLOBALS['fake_reopened_at'] = '2026-11-02 09:00:00';
[$due] = ticket_sla_transition($ticket, 'in_progress', 'critical');
$expected = business_due_at(priority_sla_minutes('critical'), new DateTimeImmutable($GLOBALS['fake_reopened_at'], $zone))->format('Y-m-d H:i:s');
sla_check($due === $expected, 'priority change recalculates from latest reopen instead of initial creation');
$GLOBALS['fake_reopened_at'] = false;
[$due] = ticket_sla_transition($ticket, 'in_progress', 'critical');
$expected = business_due_at(priority_sla_minutes('critical'), new DateTimeImmutable($ticket['created_at'], $zone))->format('Y-m-d H:i:s');
sla_check($due === $expected, 'legacy ticket without reopen uses original creation time');
$ticket['status'] = 'resolved';
$ticket['sla_pause_minutes'] = 50;
[$due, $pauseAt, $pause] = ticket_sla_transition($ticket, 'in_progress', 'critical');
sla_check($due > '2026-01-01 09:00:00' && $pauseAt === null && $pause === 0, 'reopening a resolved ticket starts a fresh SLA cycle');
$ticket['status'] = 'in_progress';
$ticket['sla_pause_minutes'] = 0;
[$due, $pauseAt] = ticket_sla_transition($ticket, 'waiting_user', 'normal');
sla_check($due === $ticket['due_at'] && $pauseAt !== null, 'entering waiting preserves deadline and starts pause');
$ticket['status'] = 'waiting_user';
$ticket['sla_paused_at'] = (new DateTimeImmutable('now', $zone))->modify('-90 minutes')->format('Y-m-d H:i:s');
$expectedPause = business_elapsed_minutes(new DateTimeImmutable($ticket['sla_paused_at'], $zone), new DateTimeImmutable('now', $zone));
[$due, $pauseAt, $pause] = ticket_sla_transition($ticket, 'in_progress', 'normal');
$expectedDue = $expectedPause > 0 ? business_due_at($expectedPause, new DateTimeImmutable($ticket['due_at'], $zone))->format('Y-m-d H:i:s') : $ticket['due_at'];
sla_check($due === $expectedDue && $pauseAt === null && $pause === $expectedPause, 'resuming adds only the new business-time pause to the existing deadline');
