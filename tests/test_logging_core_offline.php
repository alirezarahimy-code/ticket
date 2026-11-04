<?php
declare(strict_types=1);

$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-logging-test-' . bin2hex(random_bytes(5));
mkdir($testRoot, 0750, true);
define('APP_ROOT', $testRoot);
$GLOBALS['__test_trusted_proxies'] = [];
function cfg(string $key, $default = null)
{
    if ($key === 'app.trusted_proxies') {
        return $GLOBALS['__test_trusted_proxies'];
    }
    return $default;
}

require dirname(__DIR__) . '/lib/logging-core.php';
require dirname(__DIR__) . '/lib/activity-audit.php';

$checks = [];
$checks['UUID is RFC4122-shaped'] = (bool) preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', logging_uuid());
$stableEventA = logging_deterministic_uuid('test', 'update-backup-001');
$stableEventB = logging_deterministic_uuid('test', 'update-backup-001');
$checks['deterministic UUIDv8-style event id is stable and accepted without weakening UUIDv4 request IDs'] = $stableEventA === $stableEventB && logging_is_uuid($stableEventA) && !logging_is_uuid_v4($stableEventA);
$firstRequestId = logging_request_id();
$checks['request id is stable within one request'] = $firstRequestId === logging_request_id();
$firstTraceId = logging_trace_id();
$firstOperationId = logging_operation_id();
$checks['trace and operation IDs are stable within one execution'] = $firstTraceId === logging_trace_id() && $firstOperationId === logging_operation_id();
$operationId = logging_begin_operation('test:sub-operation');
$checks['sub-operation rotates only operation ID'] = $operationId !== $firstOperationId && logging_request_id() === $firstRequestId && logging_trace_id() === $firstTraceId;
$beforeExecution = logging_correlation_context();
$nextExecution = logging_begin_execution('test:next-execution');
$checks['new execution rotates request trace and operation IDs'] = $nextExecution['request_id'] !== $beforeExecution['request_id'] && $nextExecution['trace_id'] !== $beforeExecution['trace_id'] && $nextExecution['operation_id'] !== $beforeExecution['operation_id'];
$checks['execution name is sanitized and retained'] = logging_operation_name() === 'test:next-execution';
$checks['correlation context exposes operation name'] = (logging_correlation_context()['operation_name'] ?? '') === 'test:next-execution';
$checks['sensitive keys are removed'] = logging_sanitize_value(['password' => 'secret', 'nested' => ['access_token' => 'abc']]) === ['password' => '[REDACTED]', 'nested' => ['access_token' => '[REDACTED]']];
$clean = logging_sanitize_text('Bearer abc.def token=secret user@example.com 1234567890 mysql:host=db;password=secret');
$checks['text sanitizer masks credentials and identifiers'] = !str_contains($clean, 'abc.def') && !str_contains($clean, 'secret') && !str_contains($clean, 'user@example.com') && !str_contains($clean, '1234567890') && !str_contains($clean, 'mysql:host=db') && !str_contains(logging_sanitize_text('کد ۱۲۳۴۵۶۷۸۹۰'), '۱۲۳۴۵۶۷۸۹۰');
$checks['catalog returns stable code'] = logging_catalog_resolve('SEC-AUTH-001')['category'] === 'security';
$checks['activity aliases map to stable audit codes'] = logging_action_event_definition('update_user_role')['code'] === 'AUD-ACL-001';
$checks['business activity defaults to business retention class'] = logging_action_event_definition('ticket_created')['category'] === 'business';
$checks['successful legacy login is classified as security'] = logging_action_event_definition('login')['category'] === 'security';
$checks['catalog normalizes unknown legacy code'] = logging_catalog_resolve('legacy action')['code'] === 'SYS-LEGACY-001';
$checks['spool write is durable at file level'] = logging_spool_write('system', ['event_id' => logging_uuid(), 'request_id' => $firstRequestId, 'message' => 'offline-test']);
$outsideSpoolPath = $testRoot . '/storage/logs/outside-' . date('Ymd') . '.jsonl';
$checks['spool rejects untrusted stream names without creating a path'] = logging_spool_write('../outside', ['message' => 'must-not-write']) === false && !is_file($outsideSpoolPath);
$normalizedActivity = logging_normalize_activity_event([
    'username' => 'operator',
    'module' => str_repeat('M', 100),
    'result' => 'unexpected-result',
    'ip_address' => 'not-an-ip',
    'meta' => ['token' => 'sensitive-value', 'count' => 3],
]);
$checks['activity event fields are normalized at storage boundary'] = $normalizedActivity['result'] === 'unknown' && strlen($normalizedActivity['module']) <= 80 && $normalizedActivity['ip_address'] === null && !str_contains((string) $normalizedActivity['meta_json'], 'sensitive-value');
$checks['valid activity result is preserved'] = logging_normalize_activity_event(['result' => 'denied'])['result'] === 'denied';
$checks['spool statistics sees queued file'] = logging_spool_stats()['files'] === 1;
logging_increment_counter('fallback_total');
$checks['fallback counter increments'] = logging_health_counters()['fallback_total'] === 1;

$_SERVER['REMOTE_ADDR'] = '198.51.100.20';
$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.9';
$GLOBALS['__test_trusted_proxies'] = [];
$checks['untrusted forwarded header is ignored'] = activity_client_ip() === '198.51.100.20';
$_SERVER['REMOTE_ADDR'] = '10.0.0.1';
$GLOBALS['__test_trusted_proxies'] = ['10.0.0.1'];
$checks['trusted proxy forwarded chain is accepted'] = activity_client_ip() === '203.0.113.9';

$heartbeatOk = logging_worker_heartbeat('offline-test-worker', 'running', ['processed' => 2])
    && logging_worker_heartbeat('offline-test-worker', 'success', ['processed' => 3]);
$heartbeatPath = APP_ROOT . '/storage/logs/worker-heartbeats/offline-test-worker.json';
$heartbeatData = is_file($heartbeatPath) ? json_decode((string) file_get_contents($heartbeatPath), true) : null;
$checks['worker heartbeat persists outside DB and records last success'] = $heartbeatOk && is_array($heartbeatData)
    && ($heartbeatData['state'] ?? '') === 'success' && isset($heartbeatData['last_success_at'])
    && ($heartbeatData['details']['processed'] ?? 0) === 3;

$failed = [];
foreach ($checks as $name => $passed) {
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$passed) {
        $failed[] = $name;
    }
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testRoot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) {
    $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
}
@rmdir($testRoot);

if ($failed !== []) {
    fwrite(STDERR, count($failed) . " logging test(s) failed.\n");
    exit(1);
}
echo count($checks) . " logging tests passed.\n";
