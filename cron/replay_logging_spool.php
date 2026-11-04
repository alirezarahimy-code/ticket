<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

require dirname(__DIR__) . '/bootstrap.php';
require_once APP_ROOT . '/lib/logging-replay.php';

$spoolDir = APP_ROOT . '/storage/logs/spool';
$statusPath = APP_ROOT . '/storage/logs/logging-worker-status.json';
if (!is_dir($spoolDir)) {
    fwrite(STDOUT, "Logging spool is empty.\n");
    exit(0);
}

$lockPath = $spoolDir . DIRECTORY_SEPARATOR . 'replay.lock';
$lock = @fopen($lockPath, 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
    fwrite(STDOUT, "Another logging replay worker is active.\n");
    exit(0);
}
$workerStatus = static function (array $data) use ($statusPath): void {
    $existing = [];
    if (is_file($statusPath)) {
        $decoded = json_decode((string) @file_get_contents($statusPath), true);
        if (is_array($decoded)) $existing = $decoded;
    }
    $data = array_merge($existing, $data, ['updated_at' => date('c')]);
    $tmp = $statusPath . '.' . bin2hex(random_bytes(4)) . '.tmp';
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if (is_string($json) && @file_put_contents($tmp, $json, LOCK_EX) !== false) {
        @chmod($tmp, 0640);
        logging_atomic_replace_file($tmp, $statusPath);
    }
    if (is_file($tmp)) @unlink($tmp);
};
$workerStatus(['last_attempt_at' => date('c'), 'last_result' => 'running']);
logging_worker_heartbeat('spool-replay', 'running');


$processed = 0;
$replayed = 0;
$failed = 0;
$maxPerRun = 500;
$startedAt = microtime(true);

try {
    $result = logging_replay_process_directory($spoolDir, $maxPerRun);
    $processed = (int) $result['processed'];
    $replayed = (int) $result['replayed'];
    $failed = (int) $result['failed'];

    $remainingFiles = (int) $result['remaining_files'];
    $finalStatus = [
        'last_attempt_at' => date('c'),
        'last_result' => $failed > 0 ? 'partial_failure' : ($remainingFiles > 0 ? 'partial' : 'success'),
        'processed' => $processed,
        'replayed' => $replayed,
        'pending' => $remainingFiles,
        'failed_records' => $failed,
        'remaining_files' => $remainingFiles,
        'remaining_bytes' => (int) $result['remaining_bytes'],
        'dead_letter_files' => (int) ($result['dead_letter_files'] ?? 0),
        'dead_letter_bytes' => (int) ($result['dead_letter_bytes'] ?? 0),
        'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
    ];
    if ($failed === 0 && $remainingFiles === 0) $finalStatus['last_success_at'] = date('c');
$workerStatus($finalStatus);
    logging_worker_heartbeat('spool-replay', $failed > 0 ? 'partial' : 'success', ['processed' => $processed, 'replayed' => $replayed, 'pending' => $remainingFiles, 'failed' => $failed]);
    fwrite(STDOUT, sprintf("Processed: %d | replayed/idempotent: %d | pending/errors: %d | remaining files: %d\n", $processed, $replayed, $failed, (int) $result['remaining_files']));
} catch (Throwable $exception) {
    $workerStatus(['last_result' => 'failure', 'last_error_class' => get_class($exception)]);
    logging_worker_heartbeat('spool-replay', 'failure');
    throw $exception;
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
