<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-audit-status-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
class AuditStatusPDO extends PDO
{
    public bool $transaction = false;
    public array $rows = [];
    public function __construct() {}
    public function inTransaction(): bool { return $this->transaction; }
    public function prepare(string $query, array $options = []): PDOStatement|false { return new AuditStatusStatement($this, $query); }
}
class AuditStatusStatement extends PDOStatement
{
    public function __construct(private AuditStatusPDO $owner, private string $sql) {}
    public function execute(?array $params = null): bool { $this->owner->rows[] = $params ?? []; return true; }
}
$GLOBALS['__audit_status_pdo'] = new AuditStatusPDO();
$GLOBALS['__app_pdo_instance'] = $GLOBALS['__audit_status_pdo'];
function db(bool $reconnect = false): PDO { return $GLOBALS['__audit_status_pdo']; }
require dirname(__DIR__) . '/lib/logging-core.php';

$fake = $GLOBALS['__audit_status_pdo'];
$fake->transaction = true;
$dir = $root . '/storage/logs';
mkdir($dir, 0750, true);
file_put_contents($dir . '/db-circuit.json', json_encode(['state'=>'open','failures'=>3,'open_until'=>time()+60]));
$eventId = logging_uuid();
$stored = logging_store_activity(['event_id'=>$eventId,'request_id'=>logging_uuid(),'result'=>'denied','user_id'=>23,'action_code'=>'access_denied','meta'=>['reason'=>'test']]);
$files = glob($dir . '/spool/*.jsonl') ?: [];
$record = $files ? json_decode(trim((string) file_get_contents($files[0])), true) : null;
$payload = is_array($record) ? ($record['payload'] ?? []) : [];
$checks = [];
$checks['failure/denied inside open-circuit transaction is spooled without DB insert'] = $stored && count($fake->rows) === 0 && count($files) === 1;
$checks['spool-only audit clears FK user_id and retains actor_id in metadata'] = (array_key_exists('user_id', $payload) && $payload['user_id'] === null) && (int) ($payload['meta']['actor_id'] ?? 0) === 23;
$checks['persistence status reports durable spool'] = logging_persistence_status() === 'durable_spool';

$failed = [];
foreach ($checks as $label => $passed) { echo ($passed ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL; if (!$passed) $failed[] = $label; }
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed) { fwrite(STDERR, count($failed) . " audit status test(s) failed.\n"); exit(1); }
echo count($checks) . " audit status tests passed.\n";
