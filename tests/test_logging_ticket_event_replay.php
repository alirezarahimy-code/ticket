<?php
declare(strict_types=1);

$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-ticket-event-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);

class TicketReplayFakePDO extends PDO
{
    public array $events = [];
    public bool $fail = false;
    private bool $transaction = false;
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false { return new TicketReplayFakeStatement($this, $query); }
    public function inTransaction(): bool { return $this->transaction; }
    public function beginTransaction(): bool { $this->transaction = true; return true; }
    public function commit(): bool { $this->transaction = false; return true; }
    public function rollBack(): bool { $this->transaction = false; return true; }
}
class TicketReplayFakeStatement extends PDOStatement
{
    private bool $found = false;
    public function __construct(private TicketReplayFakePDO $owner, private string $sql) {}
    public function execute(?array $params = null): bool
    {
        $params ??= [];
        if (str_starts_with($this->sql, 'INSERT INTO ticket_events')) {
            if ($this->owner->fail) throw new PDOException('simulated database outage');
            $id = (string) ($params[0] ?? '');
            if (isset($this->owner->events[$id])) throw new PDOException('duplicate event id', 23000);
            $this->owner->events[$id] = $params;
        } elseif (str_starts_with($this->sql, 'SELECT 1 FROM `ticket_events`')) {
            $this->found = isset($this->owner->events[(string) ($params[0] ?? '')]);
        }
        return true;
    }
    public function fetchColumn(int $column = 0): mixed { return $this->found ? 1 : false; }
}
$GLOBALS['__ticket_replay_fake_pdo'] = new TicketReplayFakePDO();
function db(bool $reconnect = false): PDO { return $GLOBALS['__ticket_replay_fake_pdo']; }
require dirname(__DIR__) . '/lib/logging-core.php';
require dirname(__DIR__) . '/lib/activity-audit.php';
require dirname(__DIR__) . '/lib/logging-replay.php';

$checks = [];
$fake = $GLOBALS['__ticket_replay_fake_pdo'];
$fake->fail = true;
log_ticket_event(41, 7, 'status_changed', 'open', 'closed', ['source' => 'test']);
$spoolFiles = glob($root . '/storage/logs/spool/*.jsonl') ?: [];
$checks['transient failure outside transaction is spooled'] = count($spoolFiles) === 1;
$fake->fail = false;
@unlink($root . '/storage/logs/db-circuit.json');
$replay = logging_replay_process_directory($root . '/storage/logs/spool', 20);
$checks['ticket event stream is replayed into ticket_events'] = $replay['replayed'] === 1 && count($fake->events) === 1;

$fake->fail = true;
$fake->beginTransaction();
$thrown = false;
try { log_ticket_event(42, 7, 'status_changed', 'open', 'pending', []); }
catch (PDOException) { $thrown = true; }
$fake->rollBack();
$filesAfterTransactionalFailure = glob($root . '/storage/logs/spool/*.jsonl') ?: [];
$checks['failure inside business transaction is rethrown and not independently spooled'] = $thrown && count($filesAfterTransactionalFailure) === 0;

$failed = [];
foreach ($checks as $label => $passed) { echo ($passed ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL; if (!$passed) $failed[] = $label; }
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed) { fwrite(STDERR, count($failed) . " ticket event test(s) failed.\n"); exit(1); }
echo count($checks) . " ticket event tests passed.\n";
