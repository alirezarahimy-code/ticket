<?php
declare(strict_types=1);

$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-replay-offline-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);

class ReplayFakePDO extends PDO
{
    public array $system = [];
    public array $activity = [];
    public array $issues = [];
    public array $issueEvents = [];
    public $onSystemInsert = null;
    public bool $failSystemInserts = false;
    private bool $transaction = false;
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false { return new ReplayFakeStatement($this, $query); }
    public function inTransaction(): bool { return $this->transaction; }
    public function beginTransaction(): bool { $this->transaction = true; return true; }
    public function commit(): bool { $this->transaction = false; return true; }
    public function rollBack(): bool { $this->transaction = false; return true; }
}
class ReplayFakeStatement extends PDOStatement
{
    private array $params = [];
    public function __construct(private ReplayFakePDO $owner, private string $sql) {}
    public function execute(?array $params = null): bool
    {
        $this->params = $params ?? [];
        if (str_starts_with($this->sql, 'INSERT INTO system_logs')) {
            if ($this->owner->failSystemInserts) throw new PDOException('simulated database outage');
            $id = (string) ($this->params[0] ?? '');
            if (isset($this->owner->system[$id])) throw new PDOException('duplicate event id', 23000);
            $this->owner->system[$id] = $this->params;
            if (is_callable($this->owner->onSystemInsert)) {
                $callback = $this->owner->onSystemInsert;
                $this->owner->onSystemInsert = null;
                $callback();
            }
        } elseif (str_starts_with($this->sql, 'INSERT INTO activity_logs')) {
            $id = (string) ($this->params[0] ?? '');
            if (isset($this->owner->activity[$id])) throw new PDOException('duplicate event id', 23000);
            $this->owner->activity[$id] = $this->params;
        } elseif (str_starts_with($this->sql, 'INSERT IGNORE INTO log_issue_events')) {
            $id = (string) ($this->params[0] ?? '');
            if (isset($this->owner->issueEvents[$id])) $this->affected = 0;
            else $this->owner->issueEvents[$id] = $this->params;
        } elseif (str_starts_with($this->sql, 'INSERT INTO log_issues')) {
            $fp = (string) ($this->params[0] ?? '');
            if (isset($this->owner->issues[$fp])) $this->owner->issues[$fp]['occurrences']++;
            else $this->owner->issues[$fp] = ['occurrences'=>1,'status'=>'open'];
        } elseif (str_starts_with($this->sql, 'SELECT 1 FROM `system_logs`')) {
            $this->found = isset($this->owner->system[(string) ($this->params[0] ?? '')]);
        } elseif (str_starts_with($this->sql, 'SELECT 1 FROM `activity_logs`')) {
            $this->found = isset($this->owner->activity[(string) ($this->params[0] ?? '')]);
        }
        return true;
    }
    private bool $found = false;
    private int $affected = 1;
    public function rowCount(): int { return $this->affected; }
    public function fetchColumn(int $column = 0): mixed { return $this->found ? 1 : false; }
}
$GLOBALS['__replay_fake_pdo'] = new ReplayFakePDO();
function db(bool $reconnect = false): PDO { return $GLOBALS['__replay_fake_pdo']; }
require dirname(__DIR__) . '/lib/logging-core.php';
require dirname(__DIR__) . '/lib/logging-replay.php';

$systemId = logging_uuid(); $systemRequest = logging_uuid();
$activityId = logging_uuid(); $activityRequest = logging_uuid();
logging_spool_write('system', ['event_id'=>$systemId,'request_id'=>$systemRequest,'code'=>'SYS-PHP-001','category'=>'system','level'=>'error','context'=>'offline-replay','message'=>'offline error','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]);
logging_spool_write('activity', ['event_id'=>$activityId,'request_id'=>$activityRequest,'result'=>'denied','code'=>'SEC-ACL-001','category'=>'security','user_id'=>null,'username'=>'test','full_name'=>'Test','role'=>'user','action_code'=>'access_denied','action_label'=>'Denied','module'=>'test','target_type'=>null,'target_id'=>null,'target_label'=>null,'meta'=>[],'ip_address'=>null,'computer_name'=>null,'hostname'=>null,'user_agent'=>'test','created_at'=>date('Y-m-d H:i:s')]);

$first = logging_replay_process_directory($root . '/storage/logs/spool', 1);
$second = logging_replay_process_directory($root . '/storage/logs/spool', 10);
$checks = [];
$checks['batch cap leaves unprocessed lines for next run'] = $first['processed'] === 1 && $first['remaining_files'] === 1;
$checks['system and activity streams both replay'] = $second['replayed'] === 1 && $second['remaining_files'] === 0
    && isset($GLOBALS['__replay_fake_pdo']->system[$systemId]) && isset($GLOBALS['__replay_fake_pdo']->activity[$activityId]);
$issueCount = array_sum(array_column($GLOBALS['__replay_fake_pdo']->issues, 'occurrences'));
$duplicateFile = $root . '/storage/logs/spool/system-' . date('Ymd') . '.jsonl';
$record = ['version'=>1,'stream'=>'system','queued_at'=>date('c'),'payload'=>['event_id'=>$systemId,'request_id'=>$systemRequest,'code'=>'SYS-PHP-001','category'=>'system','level'=>'error','context'=>'offline-replay','message'=>'offline error','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]];
file_put_contents($duplicateFile, json_encode($record, JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
$duplicate = logging_replay_process_directory($root . '/storage/logs/spool', 10);
$checks['duplicate event replay is idempotent and does not double-count issue'] = $duplicate['replayed'] === 1
    && array_sum(array_column($GLOBALS['__replay_fake_pdo']->issues, 'occurrences')) === $issueCount;

$poisonFile = $root . '/storage/logs/spool/system-' . date('Ymd') . '.jsonl';
$goodId = logging_uuid(); $goodRequest = logging_uuid();
$goodRecord = ['version'=>1,'stream'=>'system','queued_at'=>date('c'),'payload'=>['event_id'=>$goodId,'request_id'=>$goodRequest,'code'=>'SYS-PHP-001','category'=>'system','level'=>'error','context'=>'after-poison','message'=>'valid','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]];
file_put_contents($poisonFile, "{invalid-json}\n" . json_encode($goodRecord, JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
$poison = logging_replay_process_directory($root . '/storage/logs/spool', 10);
$retryFiles = glob($root . '/storage/logs/spool/*.retry-*.jsonl') ?: [];
$retryContents = implode('', array_map(static fn (string $path): string => (string) @file_get_contents($path), $retryFiles));
$checks['corrupt record is retained while later valid records still replay'] = $poison['failed'] === 1 && $poison['replayed'] === 1
    && str_contains($retryContents, '{invalid-json}') && isset($GLOBALS['__replay_fake_pdo']->system[$goodId]);

// رکورد poison با هفت retry قبلی باید به Dead Letter منتقل شود، نه اینکه صف فعال را بی‌نهایت اشغال کند.
$deadInput = $root . '/storage/logs/spool/system-' . date('Ymd') . '.deadtest.jsonl';
file_put_contents($deadInput, "{still-invalid-json}\n", LOCK_EX);
$deadRun = [];
for ($attempt = 1; $attempt <= 8; $attempt++) {
    $deadRun = logging_replay_process_directory($root . '/storage/logs/spool', 20);
}
$deadFiles = glob($root . '/storage/logs/spool/dead-letter/*.jsonl') ?: [];
$deadContents = implode('', array_map(static fn (string $path): string => (string) @file_get_contents($path), $deadFiles));
$checks['poison record is retained in dead letter after eight attempts'] = count($deadFiles) >= 1
    && str_contains($deadContents, '{still-invalid-json}')
    && ($deadRun['dead_letter_files'] ?? 0) >= 1
    && logging_spool_stats()['dead_letter_files'] >= 1;

// قطع دیتابیس در Worker باید رکورد را در retry نگه دارد و پس از بازگشت DB تحویل دهد.
$outageId = logging_uuid(); $outageRequest = logging_uuid();
$outageRecord = ['version'=>1,'stream'=>'system','queued_at'=>date('c'),'payload'=>['event_id'=>$outageId,'request_id'=>$outageRequest,'code'=>'SYS-DB-001','category'=>'system','level'=>'error','context'=>'db-outage-retry','message'=>'retry after outage','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]];
$outageFile = $root . '/storage/logs/spool/outage-test.jsonl';
file_put_contents($outageFile, json_encode($outageRecord, JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
$GLOBALS['__replay_fake_pdo']->failSystemInserts = true;
$outageFirst = logging_replay_process_directory($root . '/storage/logs/spool', 20);
$outageWasPending = !isset($GLOBALS['__replay_fake_pdo']->system[$outageId]);
$GLOBALS['__replay_fake_pdo']->failSystemInserts = false;
@unlink($root . '/storage/logs/db-circuit.json'); // آزمون بازگشت DB بدون انتظار زمان backoff
$outageSecond = logging_replay_process_directory($root . '/storage/logs/spool', 20);
$checks['database outage preserves spool record and later retry delivers it'] = $outageFirst['failed'] >= 1
    && $outageWasPending
    && $outageSecond['replayed'] >= 1
    && isset($GLOBALS['__replay_fake_pdo']->system[$outageId]);

// خط معتبر با خطای DB حتی پس از بیش از ۸ تلاش نباید به Dead Letter منتقل شود.
$transientId = logging_uuid(); $transientRequest = logging_uuid();
$transientFile = $root . '/storage/logs/spool/transient-db-test.jsonl';
$transientRecord = ['version'=>1,'stream'=>'system','queued_at'=>date('c'),'payload'=>['event_id'=>$transientId,'request_id'=>$transientRequest,'code'=>'SYS-DB-001','category'=>'system','level'=>'error','context'=>'transient-db-over-eight','message'=>'valid event must remain active','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]];
file_put_contents($transientFile, json_encode($transientRecord, JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
$GLOBALS['__replay_fake_pdo']->failSystemInserts = true;
for ($attempt = 0; $attempt < 9; $attempt++) {
    @unlink($root . '/storage/logs/db-circuit.json');
    logging_replay_process_directory($root . '/storage/logs/spool', 20);
}
$GLOBALS['__replay_fake_pdo']->failSystemInserts = false;
@unlink($root . '/storage/logs/db-circuit.json');
$transientDeadFiles = glob($root . '/storage/logs/spool/dead-letter/*.jsonl') ?: [];
$transientDeadContents = implode('', array_map(static fn (string $path): string => (string) @file_get_contents($path), $transientDeadFiles));
$transientRetryFiles = glob($root . '/storage/logs/spool/*.retry-*.jsonl') ?: [];
$transientRetryContents = implode('', array_map(static fn (string $path): string => (string) @file_get_contents($path), $transientRetryFiles));
$transientRecovered = logging_replay_process_directory($root . '/storage/logs/spool', 20);
$checks['valid event remains out of Dead Letter after nine transient DB failures'] = !str_contains($transientDeadContents, $transientId)
    && str_contains($transientRetryContents, $transientId)
    && isset($GLOBALS['__replay_fake_pdo']->system[$transientId]);

// فایل .processing باقی‌مانده از Crash بدون نیاز به claim مجدد بازیابی می‌شود.
$crashId = logging_uuid(); $crashRequest = logging_uuid();
$crashRecord = ['version'=>1,'stream'=>'system','queued_at'=>date('c'),'payload'=>['event_id'=>$crashId,'request_id'=>$crashRequest,'code'=>'SYS-WORKER-001','category'=>'system','level'=>'error','context'=>'crash-recovery','message'=>'recover processing file','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]];
$crashFile = $root . '/storage/logs/spool/crashed.jsonl.processing';
file_put_contents($crashFile, json_encode($crashRecord, JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
$crashRun = logging_replay_process_directory($root . '/storage/logs/spool', 20);
$checks['orphan processing file is recovered after worker crash'] = isset($GLOBALS['__replay_fake_pdo']->system[$crashId])
    && !is_file($crashFile);

// حین پردازش فایل .processing، writer باید بتواند فایل روزانهٔ جدید بسازد.
$concurrentId = logging_uuid(); $concurrentRequest = logging_uuid();
$duringId = logging_uuid(); $duringRequest = logging_uuid();
logging_spool_write('system', ['event_id'=>$concurrentId,'request_id'=>$concurrentRequest,'code'=>'SYS-PHP-001','category'=>'system','level'=>'error','context'=>'claim-lock-test','message'=>'claimed event','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]);
$GLOBALS['__replay_fake_pdo']->onSystemInsert = static function () use ($duringId, $duringRequest): void {
    logging_spool_write('system', ['event_id'=>$duringId,'request_id'=>$duringRequest,'code'=>'SYS-PHP-001','category'=>'system','level'=>'error','context'=>'writer-during-replay','message'=>'writer did not block','meta'=>[],'created_at'=>date('Y-m-d H:i:s')]);
};
$claimRun = logging_replay_process_directory($root . '/storage/logs/spool', 20);
$checks['writer can append while replay processes a claimed file'] = $claimRun['replayed'] === 1
    && isset($GLOBALS['__replay_fake_pdo']->system[$concurrentId])
    && is_file($root . '/storage/logs/spool/system-' . date('Ymd') . '.jsonl');
$followRun = logging_replay_process_directory($root . '/storage/logs/spool', 20);
$checks['event written during replay is consumed by the next worker pass'] = isset($GLOBALS['__replay_fake_pdo']->system[$duringId]) && $followRun['replayed'] === 1;

$failed = [];
foreach ($checks as $label=>$passed) { echo ($passed ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL; if (!$passed) $failed[] = $label; }
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed) { fwrite(STDERR, count($failed) . " replay test(s) failed.\n"); exit(1); }
echo count($checks) . " offline replay tests passed.\n";
