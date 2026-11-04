<?php
declare(strict_types=1);

$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-issue-test-' . bin2hex(random_bytes(5));
mkdir($testRoot, 0750, true);
define('APP_ROOT', $testRoot);

class LoggingTestPDO extends PDO
{
    public array $statements = [];
    public bool $transaction = false;
    public function __construct() {}
    public function inTransaction(): bool { return $this->transaction; }
    public function beginTransaction(): bool { $this->transaction = true; return true; }
    public function commit(): bool { $this->transaction = false; return true; }
    public function rollBack(): bool { $this->transaction = false; return true; }
    public function exec(string $statement): int|false { $this->statements[] = $statement; return 0; }
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $statement = new LoggingTestStatement($this, $query);
        $this->statements[] = $statement;
        return $statement;
    }
}

class LoggingTestStatement extends PDOStatement
{
    public array $params = [];
    public function __construct(private LoggingTestPDO $owner, public string $sql) {}
    public function execute(?array $params = null): bool
    {
        $this->params = $params ?? [];
        return true;
    }
    public function rowCount(): int { return 1; }
}

$GLOBALS['__logging_test_pdo'] = new LoggingTestPDO();
function db(bool $reconnect = false): PDO { return $GLOBALS['__logging_test_pdo']; }

require dirname(__DIR__) . '/lib/logging-core.php';

$checks = [];
$event = [
    'event_id' => logging_uuid(),
    'code' => 'SYS-DB-001',
    'category' => 'system',
    'level' => 'error',
    'context' => 'database',
    'message' => 'اتصال پایگاه داده برقرار نشد',
    'meta' => ['password' => 'do-not-store'],
];
$checks['UTF-8 truncation works without mbstring'] = logging_utf8_substr('سلام دنیا', 0, 4) === 'سلام' && logging_utf8_strlen('سلام') === 4;
$checks['system log insert succeeds and issue is aggregated'] = logging_store_system($event) === true
    && count($GLOBALS['__logging_test_pdo']->statements) === 3;
$issueStatement = $GLOBALS['__logging_test_pdo']->statements[2] ?? null;
$checks['issue aggregation uses SHA-256 fingerprint'] = $issueStatement instanceof LoggingTestStatement
    && preg_match('/^[a-f0-9]{64}$/', (string) ($issueStatement->params[0] ?? '')) === 1;
$firstFingerprint = (string) ($issueStatement->params[0] ?? '');
$event['event_id'] = logging_uuid();
logging_store_system($event);
$secondIssue = $GLOBALS['__logging_test_pdo']->statements[5] ?? null;
$checks['same issue maps to stable fingerprint across events'] = $secondIssue instanceof LoggingTestStatement
    && (string) ($secondIssue->params[0] ?? '') === $firstFingerprint;
$checks['issue aggregation reopens resolved issue and increments atomically'] = $secondIssue instanceof LoggingTestStatement
    && str_contains($secondIssue->sql, 'occurrences = occurrences + 1')
    && str_contains($secondIssue->sql, "status = IF(status = 'resolved', 'open', status)");

$GLOBALS['__logging_test_pdo']->transaction = true;
$event['event_id'] = logging_uuid();
$checks['technical log inside transaction is safely spooled'] = logging_store_system($event) === false
    && logging_spool_stats()['files'] >= 1;
$GLOBALS['__logging_test_pdo']->transaction = false;
$beforeSavepointStatements = count($GLOBALS['__logging_test_pdo']->statements);
$GLOBALS['__logging_test_pdo']->transaction = true;
$savepointEvent = ['event_id'=>logging_uuid(),'request_id'=>logging_request_id(),'code'=>'SYS-DB-001','category'=>'system','level'=>'error','context'=>'savepoint-test','message'=>'aggregation savepoint test','created_at'=>date('Y-m-d H:i:s')];
$savepointOk = logging_issue_upsert($savepointEvent, false);
$savepointSql = array_slice($GLOBALS['__logging_test_pdo']->statements, $beforeSavepointStatements);
$GLOBALS['__logging_test_pdo']->transaction = false;
$checks['aggregation uses a savepoint inside an outer business transaction'] = $savepointOk
    && in_array('SAVEPOINT logging_issue_' . substr(hash('sha256', 'sys-db-001|savepoint-test|aggregation savepoint test'), 0, 16), $savepointSql, true)
    && count(array_filter($savepointSql, static fn ($sql): bool => is_string($sql) && str_starts_with($sql, 'RELEASE SAVEPOINT'))) === 1;

$normalized = logging_normalize_activity_event([
    'username' => str_repeat('x', 250),
    'result' => 'invalid-result',
    'ip_address' => 'not-an-ip',
    'user_id' => -4,
    'target_id' => 'abc',
    'meta' => ['api_token' => 'secret'],
]);
$checks['activity boundary normalization constrains fields and sensitive metadata'] = strlen($normalized['username']) <= 190
    && $normalized['result'] === 'unknown'
    && $normalized['ip_address'] === null
    && $normalized['user_id'] === null
    && $normalized['target_id'] === 0
    && str_contains((string) $normalized['meta_json'], '[REDACTED]');

$failed = [];
foreach ($checks as $name => $passed) {
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$passed) $failed[] = $name;
}
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testRoot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($testRoot);
if ($failed !== []) { fwrite(STDERR, count($failed) . " issue test(s) failed.\n"); exit(1); }
echo count($checks) . " issue aggregation tests passed.\n";
