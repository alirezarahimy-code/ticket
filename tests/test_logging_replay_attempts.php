<?php
declare(strict_types=1);

$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-replay-attempts-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
class ReplayAttemptsPDO extends PDO
{
    public bool $failData = true;
    public bool $failSchema = false;
    public array $inserted = [];
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false { return new ReplayAttemptsStatement($this, $query); }
}
class ReplayAttemptsStatement extends PDOStatement
{
    public function __construct(private ReplayAttemptsPDO $owner, private string $sql) {}
    public function execute(?array $params = null): bool
    {
        $params ??= [];
        if (str_starts_with($this->sql, 'INSERT INTO system_logs')) {
            $message = (string) ($params[10] ?? '');
            if ($message === 'schema-missing' && $this->owner->failSchema) {
                $e = new PDOException('unknown column', 42); $e->errorInfo = ['42S22', 1054, 'Unknown column']; throw $e;
            }
            $this->owner->inserted[(string) ($params[0] ?? '')] = true;
        } elseif (str_starts_with($this->sql, 'INSERT IGNORE INTO log_issue_events')) {
            // The issue aggregator is not part of this replay-attempt test.
        }
        return true;
    }
}
$GLOBALS['__replay_attempts_pdo'] = new ReplayAttemptsPDO();
function db(bool $reconnect = false): PDO { return $GLOBALS['__replay_attempts_pdo']; }
require dirname(__DIR__) . '/lib/logging-core.php';
require dirname(__DIR__) . '/lib/logging-replay.php';

$spool = $root . '/storage/logs/spool';
mkdir($spool, 0750, true);
$badId = 'invalid-event-id'; $badRequest = logging_uuid();
$bad2Id = 'invalid-event-id-2'; $bad2Request = logging_uuid();
$goodId = logging_uuid(); $goodRequest = logging_uuid();
$bad = ['version'=>1,'stream'=>'system','queued_at'=>date('c'),'attempts'=>0,'payload'=>['event_id'=>$badId,'request_id'=>$badRequest,'code'=>'SYS-TEST-001','category'=>'system','level'=>'info','context'=>'attempt-test','message'=>'malformed-id','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]];
$bad2 = ['version'=>1,'stream'=>'system','queued_at'=>date('c'),'attempts'=>0,'payload'=>['event_id'=>$bad2Id,'request_id'=>$bad2Request,'code'=>'SYS-TEST-001','category'=>'system','level'=>'info','context'=>'attempt-test','message'=>'malformed-id-2','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]];
$good = ['version'=>1,'stream'=>'system','queued_at'=>date('c'),'attempts'=>0,'payload'=>['event_id'=>$goodId,'request_id'=>$goodRequest,'code'=>'SYS-TEST-001','category'=>'system','level'=>'info','context'=>'attempt-test','message'=>'good-data','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]];
$file = $spool . '/mixed.jsonl';
file_put_contents($file, json_encode($bad) . "\n" . json_encode($bad2) . "\n" . json_encode($good) . "\n");
$first = logging_replay_process_directory($spool, 20);
$retryFiles = glob($spool . '/*.retry-*.jsonl') ?: [];
$retryRows = [];
foreach ($retryFiles as $retryFile) foreach (file($retryFile, FILE_IGNORE_NEW_LINES) ?: [] as $line) { $decoded = json_decode($line, true); if (is_array($decoded)) $retryRows[] = $decoded; }
$checks = [];
$retryById = []; foreach ($retryRows as $row) $retryById[(string) ($row['payload']['event_id'] ?? '')] = (int) ($row['attempts'] ?? -1);
$checks['malformed records in one file keep independent per-record attempts'] = $first['failed'] === 2 && $first['replayed'] === 1
    && count($retryRows) === 2 && ($retryById[$badId] ?? -1) === 1 && ($retryById[$bad2Id] ?? -1) === 1;

// Seven additional real permanent-data failures reach the per-record threshold of eight.
for ($i = 0; $i < 7; $i++) logging_replay_process_directory($spool, 20);
$deadFiles = glob($spool . '/dead-letter/*.jsonl') ?: [];
$deadText = implode('', array_map(static fn(string $f): string => (string) file_get_contents($f), $deadFiles));
$checks['malformed records enter DLQ at eight attempts independently'] = str_contains($deadText, $badId) && str_contains($deadText, $bad2Id) && !str_contains($deadText, $goodId);

// Unparseable JSON cannot carry an attempts field; its raw-line hash has an independent sidecar counter.
$rawA = '{broken-a}'; $rawB = '{broken-b}';
file_put_contents($spool . '/raw-poison.jsonl', $rawA . "\n" . $rawB . "\n");
logging_replay_process_directory($spool, 20);
$checks['unparseable JSON lines use independent hash-keyed attempt counters'] = logging_replay_poison_attempts($spool, $rawA . "\n") === 1
    && logging_replay_poison_attempts($spool, $rawB . "\n") === 1;
for ($i = 0; $i < 7; $i++) logging_replay_process_directory($spool, 20);
$rawDead = implode('', array_map(static fn(string $f): string => (string) file_get_contents($f), glob($spool . '/dead-letter/*.jsonl') ?: []));
$checks['unparseable JSON lines are quarantined independently after eight attempts'] = str_contains($rawDead, $rawA) && str_contains($rawDead, $rawB)
    && logging_replay_poison_attempts($spool, $rawA . "\n") === 0 && logging_replay_poison_attempts($spool, $rawB . "\n") === 0;

// Schema errors must stay active and must not consume the per-record attempt counter.
$schemaId = logging_uuid(); $schemaRequest = logging_uuid();
$schemaRecord = ['version'=>1,'stream'=>'system','queued_at'=>date('c'),'attempts'=>0,'payload'=>['event_id'=>$schemaId,'request_id'=>$schemaRequest,'code'=>'SYS-TEST-002','category'=>'system','level'=>'info','context'=>'schema-test','message'=>'schema-missing','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]];
file_put_contents($spool . '/schema.jsonl', json_encode($schemaRecord) . "\n");
$GLOBALS['__replay_attempts_pdo']->failSchema = true;
for ($i = 0; $i < 9; $i++) { @unlink($root . '/storage/logs/db-circuit.json'); logging_replay_process_directory($spool, 20); }
$schemaRetryFiles = glob($spool . '/*.retry-*.jsonl') ?: [];
$schemaAttempts = null;
foreach ($schemaRetryFiles as $retryFile) foreach (file($retryFile, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
    $decoded = json_decode($line, true);
    if (($decoded['payload']['event_id'] ?? '') === $schemaId) $schemaAttempts = $decoded['attempts'] ?? 0;
}
$deadText = implode('', array_map(static fn(string $f): string => (string) file_get_contents($f), glob($spool . '/dead-letter/*.jsonl') ?: []));
$checks['schema error retries without incrementing attempts or entering DLQ'] = $schemaAttempts === 0 && !str_contains($deadText, $schemaId);

$dataError = new PDOException('data too long', 22001); $dataError->errorInfo = ['22001', 1406, 'Data too long'];
$schemaError = new PDOException('unknown column', 42); $schemaError->errorInfo = ['42S22', 1054, 'Unknown column'];
$checks['SQL data errors are classified as permanent data'] = logging_replay_error_classify($dataError) === 'permanent_data';
$checks['schema and foreign-key errors are classified as retryable schema'] = logging_replay_error_classify($schemaError) === 'schema';

$failed = [];
foreach ($checks as $label => $passed) { echo ($passed ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL; if (!$passed) $failed[] = $label; }
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed) { fwrite(STDERR, count($failed) . " replay attempt test(s) failed.\n"); exit(1); }
echo count($checks) . " replay attempt tests passed.\n";
