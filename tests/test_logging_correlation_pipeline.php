<?php
declare(strict_types=1);

$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-correlation-pipeline-' . bin2hex(random_bytes(5));
mkdir($testRoot, 0750, true);
define('APP_ROOT', $testRoot);

$GLOBALS['__correlation_sql_calls'] = [];
class LoggingCorrelationFakeStatement extends PDOStatement
{
    public function execute(?array $params = null): bool
    {
        $GLOBALS['__correlation_sql_calls'][] = $params ?? [];
        return true;
    }
}
class LoggingCorrelationFakePDO extends PDO
{
    public function __construct() {}
    public function inTransaction(): bool { return true; }
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new LoggingCorrelationFakeStatement();
    }
}
$GLOBALS['__correlation_fake_pdo'] = new LoggingCorrelationFakePDO();
function db(bool $reconnect = false): PDO
{
    return $GLOBALS['__correlation_fake_pdo'];
}

require dirname(__DIR__) . '/lib/logging-core.php';
require dirname(__DIR__) . '/lib/activity-audit.php';

$context = logging_begin_execution('test:e2e:cross-module');
$systemEventId = logging_uuid();
$systemOk = logging_store_system([
    'event_id' => $systemEventId,
    'code' => 'SYS-PHP-001',
    'category' => 'system',
    'level' => 'error',
    'context' => 'correlation-test',
    'message' => 'system event in transactional scope',
    'meta' => ['error_type' => 'TestException'],
]);

$activityEventId = logging_uuid();
$activityOk = logging_store_activity([
    'event_id' => $activityEventId,
    'result' => 'success',
    'code' => 'AUD-LEGACY-001',
    'category' => 'audit',
    'action_code' => 'correlation_test',
    'module' => 'test',
    'meta' => [],
]);

log_ticket_event(123, 7, 'correlation_test', null, null, ['source' => 'offline-test']);

$spoolFiles = glob($testRoot . '/storage/logs/spool/system-*.jsonl') ?: [];
$spoolPayload = [];
if ($spoolFiles !== []) {
    $lines = file($spoolFiles[0], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $decoded = json_decode((string) ($lines[0] ?? ''), true);
    $spoolPayload = (array) ($decoded['payload'] ?? []);
}
$activityParams = $GLOBALS['__correlation_sql_calls'][0] ?? [];
$ticketParams = $GLOBALS['__correlation_sql_calls'][1] ?? [];
$checks = [
    'system event is safely spooled inside business transaction' => $systemOk === false && ($spoolPayload['event_id'] ?? '') === $systemEventId,
    'system spool retains request trace and operation IDs' => ($spoolPayload['request_id'] ?? '') === $context['request_id'] && ($spoolPayload['trace_id'] ?? '') === $context['trace_id'] && ($spoolPayload['operation_id'] ?? '') === $context['operation_id'],
    'activity event shares the same request trace and operation IDs' => $activityOk && ($activityParams[1] ?? '') === $context['request_id'] && ($activityParams[2] ?? '') === $context['trace_id'] && ($activityParams[3] ?? '') === $context['operation_id'],
    'ticket event shares the same request trace and operation IDs' => ($ticketParams[1] ?? '') === $context['request_id'] && ($ticketParams[2] ?? '') === $context['trace_id'] && ($ticketParams[3] ?? '') === $context['operation_id'],
    'activity and ticket events reference the initiating system event' => ($activityParams[4] ?? '') === $systemEventId && ($ticketParams[4] ?? '') === $systemEventId,
];

$failed = [];
foreach ($checks as $name => $passed) {
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$passed) $failed[] = $name;
}
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testRoot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($testRoot);
if ($failed !== []) {
    fwrite(STDERR, count($failed) . " correlation pipeline test(s) failed.\n");
    exit(1);
}
echo count($checks) . " correlation pipeline tests passed.\n";
