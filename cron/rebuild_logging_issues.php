<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

require dirname(__DIR__) . '/bootstrap.php';

$limit = isset($argv[1]) ? (int) $argv[1] : 500;
$limit = max(1, min(5000, $limit));
$processed = 0;
$failed = 0;

$statusPath = APP_ROOT . '/storage/logs/logging-issue-rebuild-status.json';
$writeStatus = static function (array $data) use ($statusPath): void {
    $dir = dirname($statusPath);
    if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) return;
    $tmp = $statusPath . '.' . bin2hex(random_bytes(4)) . '.tmp';
    $json = json_encode($data + ['updated_at' => date('c')], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if (is_string($json) && @file_put_contents($tmp, $json, LOCK_EX) !== false) {
        @chmod($tmp, 0640);
        if (!logging_atomic_replace_file($tmp, $statusPath)) @unlink($tmp);
    }
};
$writeStatus(['last_attempt_at' => date('c'), 'last_result' => 'running']);
logging_worker_heartbeat('issue-rebuild', 'running');

try {
    $rows = db()->query("SELECT s.id, s.event_id, s.request_id, s.code, s.category, s.level, s.context, s.message, s.created_at
        FROM system_logs s
        LEFT JOIN log_issue_events e ON e.event_id = s.event_id
        WHERE s.level IN ('warning','error','critical') AND s.event_id IS NOT NULL AND e.event_id IS NULL
        ORDER BY s.id ASC LIMIT " . $limit)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        if (logging_issue_upsert($row, false)) $processed++; else $failed++;
    }
} catch (Throwable $exception) {
    logging_emergency_failure('issue_rebuild_failed', get_class($exception));
    $writeStatus(['last_attempt_at' => date('c'), 'last_result' => 'failure', 'last_error_class' => get_class($exception)]);
    logging_worker_heartbeat('issue-rebuild', 'failure');
    fwrite(STDERR, 'Issue rebuild failed: ' . get_class($exception) . PHP_EOL);
    exit(2);
}

$status = ['last_attempt_at' => date('c'), 'last_result' => $failed > 0 ? 'partial_failure' : 'success', 'processed' => $processed, 'failed' => $failed];
if ($failed === 0) $status['last_success_at'] = date('c');
$writeStatus($status);
logging_worker_heartbeat('issue-rebuild', $failed > 0 ? 'partial' : 'success', ['processed' => $processed, 'failed' => $failed]);
fwrite(STDOUT, sprintf("Issue rebuild: %d processed, %d failed\n", $processed, $failed));
exit($failed > 0 ? 1 : 0);
