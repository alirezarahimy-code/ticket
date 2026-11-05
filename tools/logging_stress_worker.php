<?php
declare(strict_types=1);

/**
 * Synthetic Logging stress worker for release 1.62.88.
 * CLI only. It writes tagged activity-audit rows; it does not create/update tickets,
 * users, settings, notifications, or business records.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

$options = getopt('', ['run-id:', 'count::', 'worker::', 'verify']);
$runId = strtolower((string)($options['run-id'] ?? ''));
if (!preg_match('/^[a-f0-9]{32}$/D', $runId)) {
    fwrite(STDERR, "Invalid --run-id. Expected 32 lowercase hexadecimal characters.\n");
    exit(2);
}

require dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/lib/activity-audit.php';

$targetLabel = 'STRESS-' . $runId;
$pdo = db();
if (!$pdo instanceof PDO) {
    fwrite(STDERR, "Application database connection is unavailable.\n");
    exit(3);
}

if (isset($options['verify'])) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total,
                COUNT(DISTINCT event_id) AS unique_events,
                SUM(request_id IS NOT NULL AND trace_id IS NOT NULL AND operation_id IS NOT NULL) AS correlated,
                SUM(result = 'success') AS successful
         FROM activity_logs
         WHERE module = 'logging-stress'
           AND target_type = 'logging_stress'
           AND target_label = ?
           AND action_code = 'logging_stress_probe'"
    );
    $stmt->execute([$targetLabel]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $result = [
        'mode' => 'verify',
        'run_id' => $runId,
        'total' => (int)($row['total'] ?? 0),
        'unique_events' => (int)($row['unique_events'] ?? 0),
        'correlated' => (int)($row['correlated'] ?? 0),
        'successful' => (int)($row['successful'] ?? 0),
    ];
    echo json_encode($result, JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
}

$count = filter_var($options['count'] ?? 0, FILTER_VALIDATE_INT);
$worker = filter_var($options['worker'] ?? 1, FILTER_VALIDATE_INT);
if ($count === false || $count < 1 || $count > 10000 || $worker === false || $worker < 1) {
    fwrite(STDERR, "Invalid count/worker arguments. count must be 1..10000.\n");
    exit(2);
}

$ok = 0;
$failed = 0;
$started = microtime(true);
for ($i = 1; $i <= $count; $i++) {
    try {
        logging_begin_execution('logging-stress-worker');
        $stored = activity_log([
            'action_code' => 'logging_stress_probe',
            'action_label' => 'Synthetic Logging stress probe',
            'module' => 'logging-stress',
            'target_type' => 'logging_stress',
            'target_id' => $i,
            'target_label' => $targetLabel,
            'result' => 'success',
            'meta' => [
                'stress_run_id' => $runId,
                'worker' => (int)$worker,
                'sequence' => $i,
                'synthetic' => true,
            ],
        ]);
        if ($stored) {
            $ok++;
        } else {
            $failed++;
        }
    } catch (Throwable $exception) {
        $failed++;
        fwrite(STDERR, 'Event ' . $i . ' failed: ' . get_class($exception) . PHP_EOL);
    }
}

$result = [
    'mode' => 'write',
    'run_id' => $runId,
    'worker' => (int)$worker,
    'requested' => $count,
    'stored' => $ok,
    'failed' => $failed,
    'duration_ms' => (int)round((microtime(true) - $started) * 1000),
];
echo json_encode($result, JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($failed === 0 && $ok === $count ? 0 : 1);
