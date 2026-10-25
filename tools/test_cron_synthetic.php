<?php
declare(strict_types=1);

/**
 * Integration smoke for cron entry points against a disposable synthetic CI database only.
 * Does not contact LDAP, a domain host, an attendance device, or a physical printer.
 */

$root = dirname(__DIR__);
if (getenv('TICKET_SYNTHETIC_CRON_TEST') !== '1') {
    fwrite(STDERR, "Refusing cron integration test: TICKET_SYNTHETIC_CRON_TEST must equal 1.\n");
    exit(2);
}
require $root . '/bootstrap.php';

$pdo = db();
$databaseName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
$databaseHost = strtolower(trim((string) cfg('database.host', '')));
if (!preg_match('/^ticket_benchmark(?:_[a-z0-9_]+)?$/i', $databaseName)
    || !in_array($databaseHost, ['127.0.0.1', 'localhost', '::1'], true)) {
    fwrite(STDERR, "Refusing cron integration test on non-disposable target: {$databaseHost}/{$databaseName}\n");
    exit(2);
}

$checks = 0;
function cron_assert(bool $condition, string $message): void
{
    global $checks;
    $checks++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    fwrite(STDOUT, "PASS: {$message}\n");
}

/** @return array{code:int,stdout:string,stderr:string} */
function cron_run(string $script, array $args = []): array
{
    global $root;
    $command = array_merge([PHP_BINARY, $root . '/' . $script], $args);
    $pipes = [];
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
    if (!is_resource($process)) {
        throw new RuntimeException('Could not start ' . $script);
    }
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($process);
    return ['code' => $code, 'stdout' => is_string($stdout) ? $stdout : '', 'stderr' => is_string($stderr) ? $stderr : ''];
}

function cron_insert_ticket(PDO $pdo, int $requesterId, int $departmentId, string $ticketNo, string $status,
                            array $extra = []): int
{
    $fields = [
        'ticket_no', 'subject', 'description', 'service_group', 'department_id', 'priority', 'status',
        'requester_id', 'created_at', 'updated_at', 'resolved_at', 'cancel_requested_at', 'due_at',
        'first_response_at', 'ola_response_due_at', 'ola_due_at', 'ola_escalation_1_at', 'ola_escalation_2_at',
        'sla_paused_at', 'closed_at',
    ];
    $values = [
        $ticketNo, 'Synthetic cron test ' . $ticketNo, 'Disposable CI cron integration fixture.', 'it', $departmentId,
        'normal', $status, $requesterId, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), null, null, null,
        null, null, null, null, null, null, null,
    ];
    foreach ($extra as $field => $value) {
        if (!in_array($field, $fields, true)) {
            throw new InvalidArgumentException('Unsupported synthetic ticket field: ' . $field);
        }
        $values[array_search($field, $fields, true)] = $value;
    }
    $quoted = implode(', ', array_map(static fn (string $field): string => '`' . $field . '`', $fields));
    $placeholders = implode(', ', array_fill(0, count($fields), '?'));
    $query = $pdo->prepare('INSERT INTO tickets (' . $quoted . ') VALUES (' . $placeholders . ')');
    $query->execute($values);
    return (int) $pdo->lastInsertId();
}

function cron_run_assert(string $script, array $args = []): array
{
    $result = cron_run($script, $args);
    if ($result['code'] !== 0) {
        throw new RuntimeException($script . ' exited ' . $result['code'] . ': ' . trim($result['stdout'] . "\n" . $result['stderr']));
    }
    return $result;
}

try {
    $primaryAdminId = (int) $pdo->query("SELECT id FROM users WHERE username = 'loadtest-001' LIMIT 1")->fetchColumn();
    $requesterId = (int) $pdo->query("SELECT id FROM users WHERE username = 'loadtest-requester' LIMIT 1")->fetchColumn();
    $departmentId = (int) $pdo->query("SELECT id FROM departments WHERE code = 'BENCH' LIMIT 1")->fetchColumn();
    cron_assert($primaryAdminId > 0 && $requesterId > 0 && $departmentId > 0,
        'synthetic prerequisite identities and department exist');

    // An explicitly finished scan validates the worker's lifecycle/CLI path without invoking PowerShell or network I/O.
    require_once $root . '/domain-scan.php';
    domain_scan_ensure_schema();
    $scanInsert = $pdo->prepare("INSERT INTO domain_scan_runs (started_by, status, total, done) VALUES (?, 'finished', 0, 0)");
    $scanInsert->execute([$primaryAdminId]);
    $scanId = (int) $pdo->lastInsertId();
    $scan = cron_run_assert('cron/domain_scan_worker.php', ['--run=' . $scanId]);
    cron_assert(str_contains($scan['stdout'], 'no active run'),
        'domain-scan worker handles a finished synthetic run without contacting a host');

    // CI config disables LDAP; the job must exit before invoking any LDAP connection.
    cron_assert(!(bool) cfg('ldap.enabled', false), 'LDAP is disabled in synthetic test configuration');
    $ldap = cron_run_assert('cron/ldap_sync.php');
    cron_assert(str_contains($ldap['stdout'], 'LDAP sync skipped'),
        'LDAP cron exits safely when the external provider is disabled');

    // Ensure queue-only worker cannot connect to or write to a real printer in this test.
    $pdo->exec("UPDATE food_ticket_config SET printer_host = '', printer_share = '' WHERE id = 1");
    $pendingPrints = (int) $pdo->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status IN ('pending','print_error','failed','printing')")->fetchColumn();
    cron_assert($pendingPrints === 0, 'synthetic print queue is empty before queue-worker probe');
    $foodWorker = cron_run_assert('cron/food_ticket_worker.php', ['--queue-only']);
    cron_assert(str_contains($foodWorker['stdout'], 'start loop=0') && !str_contains($foodWorker['stderr'], 'Fatal'),
        'food-ticket queue worker completes one synthetic empty-queue cycle without device access');

    // SLA and OLA workers: two explicit active synthetic tickets with near/past deadlines.
    $now = new DateTimeImmutable('now');
    $dueSoon = cron_insert_ticket($pdo, $requesterId, $departmentId, 'CI-CRON-SLA-SOON-' . bin2hex(random_bytes(3)), 'assigned', [
        'due_at' => $now->modify('+30 minutes')->format('Y-m-d H:i:s'),
        'ola_response_due_at' => $now->modify('-4 days')->format('Y-m-d H:i:s'),
        'ola_due_at' => $now->modify('-3 days')->format('Y-m-d H:i:s'),
        'ola_escalation_1_at' => $now->modify('-2 days')->format('Y-m-d H:i:s'),
        'ola_escalation_2_at' => $now->modify('-1 day')->format('Y-m-d H:i:s'),
    ]);
    $overdue = cron_insert_ticket($pdo, $requesterId, $departmentId, 'CI-CRON-SLA-LATE-' . bin2hex(random_bytes(3)), 'assigned', [
        'due_at' => $now->modify('-30 minutes')->format('Y-m-d H:i:s'),
        'ola_response_due_at' => $now->modify('-4 days')->format('Y-m-d H:i:s'),
        'ola_due_at' => $now->modify('-3 days')->format('Y-m-d H:i:s'),
        'ola_escalation_1_at' => $now->modify('-2 days')->format('Y-m-d H:i:s'),
        'ola_escalation_2_at' => $now->modify('-1 day')->format('Y-m-d H:i:s'),
    ]);
    $sla = cron_run_assert('cron/sla_maintenance.php');
    $slaRows = $pdo->prepare('SELECT alert_type, COUNT(*) FROM ticket_sla_alerts WHERE ticket_id IN (?, ?) GROUP BY alert_type');
    $slaRows->execute([$dueSoon, $overdue]);
    $slaTypes = array_column($slaRows->fetchAll(PDO::FETCH_NUM), 1, 0);
    cron_assert((int) ($slaTypes['due_soon'] ?? 0) === 1 && (int) ($slaTypes['overdue'] ?? 0) === 1,
        'SLA maintenance emits the correct due-soon and overdue alerts');
    $olaRows = $pdo->prepare('SELECT alert_type, COUNT(*) FROM ticket_ola_alerts WHERE ticket_id IN (?, ?) GROUP BY alert_type');
    $olaRows->execute([$dueSoon, $overdue]);
    $olaTypes = array_column($olaRows->fetchAll(PDO::FETCH_NUM), 1, 0);
    foreach (['response_overdue', 'resolution_overdue', 'escalation_1', 'escalation_2'] as $type) {
        cron_assert((int) ($olaTypes[$type] ?? 0) === 2, 'OLA maintenance emits two synthetic ' . $type . ' alerts');
    }
    cron_assert(str_contains($sla['stdout'], 'SLA alerts created:'), 'SLA/OLA worker reports its completed cycle');

    // Auto-close should close only the eligible fixture, not rated or cancellation-pending fixtures.
    $pdo->exec("UPDATE tickets SET resolved_at = NOW(), updated_at = NOW() WHERE ticket_no LIKE 'BENCH-%' AND status = 'resolved'");
    $pdo->exec("INSERT INTO settings (`key`, `value`) VALUES ('ticket_auto_close_days', '1') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
    $oldResolved = date('Y-m-d H:i:s', time() - 2 * 86400);
    $eligibleNo = 'CI-CRON-CLOSE-ELIGIBLE-' . bin2hex(random_bytes(3));
    $ratedNo = 'CI-CRON-CLOSE-RATED-' . bin2hex(random_bytes(3));
    $cancelNo = 'CI-CRON-CLOSE-CANCEL-' . bin2hex(random_bytes(3));
    $recentNo = 'CI-CRON-CLOSE-RECENT-' . bin2hex(random_bytes(3));
    $eligibleId = cron_insert_ticket($pdo, $requesterId, $departmentId, $eligibleNo, 'resolved', ['resolved_at' => $oldResolved, 'updated_at' => $oldResolved]);
    $ratedId = cron_insert_ticket($pdo, $requesterId, $departmentId, $ratedNo, 'resolved', ['resolved_at' => $oldResolved, 'updated_at' => $oldResolved]);
    $cancelId = cron_insert_ticket($pdo, $requesterId, $departmentId, $cancelNo, 'resolved', [
        'resolved_at' => $oldResolved, 'updated_at' => $oldResolved, 'cancel_requested_at' => date('Y-m-d H:i:s'),
    ]);
    $recentId = cron_insert_ticket($pdo, $requesterId, $departmentId, $recentNo, 'resolved');
    $rating = $pdo->prepare('INSERT INTO ticket_ratings (ticket_id, requester_id, score, comment, created_at) VALUES (?, ?, 5, ?, NOW())');
    $rating->execute([$ratedId, $requesterId, 'synthetic cron test']);
    $autoClose = cron_run_assert('cron/ticket_auto_close.php');
    $statuses = $pdo->prepare('SELECT id, status FROM tickets WHERE id IN (?, ?, ?, ?)');
    $statuses->execute([$eligibleId, $ratedId, $cancelId, $recentId]);
    $statusById = [];
    foreach ($statuses->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $statusById[(int) $row['id']] = (string) $row['status'];
    }
    cron_assert(($statusById[$eligibleId] ?? '') === 'closed', 'auto-close closes an eligible old resolved ticket');
    cron_assert(($statusById[$ratedId] ?? '') === 'resolved', 'auto-close preserves a recently rated ticket');
    cron_assert(($statusById[$cancelId] ?? '') === 'resolved', 'auto-close preserves a cancellation-pending ticket');
    cron_assert(($statusById[$recentId] ?? '') === 'resolved', 'auto-close preserves a recently resolved ticket');
    cron_assert(str_contains($autoClose['stdout'], 'ticket_auto_close: 1 closed'), 'auto-close reports exactly one closure');

    // Prune only synthetic rows deliberately older than the documented retention windows.
    $oldActivity = $pdo->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action_code, module, created_at) VALUES (?, 'loadtest-001', 'Synthetic Admin', 'primary_admin', ?, 'cron-smoke', DATE_SUB(NOW(), INTERVAL 61 DAY))");
    $oldActivity->execute([$primaryAdminId, 'cron_smoke_old_' . bin2hex(random_bytes(3))]);
    $oldActivityId = (int) $pdo->lastInsertId();
    $freshActivity = $pdo->prepare("INSERT INTO activity_logs (user_id, username, full_name, role, action_code, module, created_at) VALUES (?, 'loadtest-001', 'Synthetic Admin', 'primary_admin', ?, 'cron-smoke', NOW())");
    $freshActivity->execute([$primaryAdminId, 'cron_smoke_fresh_' . bin2hex(random_bytes(3))]);
    $freshActivityId = (int) $pdo->lastInsertId();
    $oldSystemLog = $pdo->prepare("INSERT INTO system_logs (level, context, message, created_at) VALUES ('info', 'cron-smoke', 'synthetic old row', DATE_SUB(NOW(), INTERVAL 31 DAY))");
    $oldSystemLog->execute();
    $oldSystemLogId = (int) $pdo->lastInsertId();
    $freshSystemLog = $pdo->prepare("INSERT INTO system_logs (level, context, message, created_at) VALUES ('info', 'cron-smoke', 'synthetic fresh row', NOW())");
    $freshSystemLog->execute();
    $freshSystemLogId = (int) $pdo->lastInsertId();
    $prune = cron_run_assert('cron/prune_activity_logs.php');
    $activityCheck = $pdo->prepare('SELECT COUNT(*) FROM activity_logs WHERE id = ?');
    $activityCheck->execute([$oldActivityId]);
    $oldActivityLeft = (int) $activityCheck->fetchColumn();
    $activityCheck->execute([$freshActivityId]);
    $freshActivityLeft = (int) $activityCheck->fetchColumn();
    $systemCheck = $pdo->prepare('SELECT COUNT(*) FROM system_logs WHERE id = ?');
    $systemCheck->execute([$oldSystemLogId]);
    $oldSystemLeft = (int) $systemCheck->fetchColumn();
    $systemCheck->execute([$freshSystemLogId]);
    $freshSystemLeft = (int) $systemCheck->fetchColumn();
    cron_assert($oldActivityLeft === 0 && $freshActivityLeft === 1, 'activity-log pruning deletes stale synthetic rows and keeps fresh rows');
    cron_assert($oldSystemLeft === 0 && $freshSystemLeft === 1, 'system-log pruning deletes rows older than 30 days only');
    cron_assert(str_contains($prune['stdout'], 'Activity log prune complete'), 'activity pruning job reports completion');

    printf("Synthetic cron integration checks passed: %d\n", $checks);
} catch (Throwable $exception) {
    fwrite(STDERR, "Synthetic cron integration failed after {$checks} checks: " . $exception->getMessage() . "\n");
    exit(1);
}
