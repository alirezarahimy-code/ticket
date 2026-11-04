<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-audit-atomic-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
class AtomicAuditPDO extends PDO
{
    public bool $transaction = false;
    public array $committed = [];
    public array $pending = [];
    public function __construct() {}
    public function inTransaction(): bool { return $this->transaction; }
    public function beginTransaction(): bool { $this->transaction = true; $this->pending = []; return true; }
    public function commit(): bool { $this->committed = array_merge($this->committed, $this->pending); $this->pending = []; $this->transaction = false; return true; }
    public function rollBack(): bool { $this->pending = []; $this->transaction = false; return true; }
    public function prepare(string $query, array $options = []): PDOStatement|false { return new AtomicAuditStatement($this, $query); }
}
class AtomicAuditStatement extends PDOStatement
{
    public function __construct(private AtomicAuditPDO $owner, private string $sql) {}
    public function execute(?array $params = null): bool
    {
        if (str_starts_with($this->sql, 'INSERT INTO activity_logs')) {
            $row = ['sql' => $this->sql, 'params' => $params ?? []];
            if ($this->owner->transaction) $this->owner->pending[] = $row;
            else $this->owner->committed[] = $row;
        }
        return true;
    }
}
$pdo = new AtomicAuditPDO();
$GLOBALS['__atomic_audit_pdo'] = $pdo;
$GLOBALS['__app_pdo_instance'] = $pdo;
function db(bool $reconnect = false): PDO { return $GLOBALS['__atomic_audit_pdo']; }
require dirname(__DIR__) . '/lib/logging-core.php';
require dirname(__DIR__) . '/lib/activity-audit.php';

$checks = [];
$pdo->beginTransaction();
save_audit_required(17, 'change_updated', null, ['change_id' => 81, 'change_number' => 'CHG-0081']);
$checks['required success audit is inserted on the same active business transaction'] = count($pdo->pending) === 1 && count($pdo->committed) === 0;
$checks['governance audit receives module and target context'] = ($pdo->pending[0]['params'][14] ?? null) === 'governance'
    && ($pdo->pending[0]['params'][15] ?? null) === 'change'
    && (int) ($pdo->pending[0]['params'][16] ?? 0) === 81
    && ($pdo->pending[0]['params'][17] ?? null) === 'CHG-0081';
$governance = logging_action_event_definition('change_cab_approved');
$ldap = logging_action_event_definition('ldap_bulk_sync');
$profile = logging_action_event_definition('profile_updated');
$catalogGovernance = logging_catalog_resolve($governance['code']);
$catalogLdap = logging_catalog_resolve($ldap['code']);
$catalogProfile = logging_catalog_resolve($profile['code']);
$cdDvd = logging_action_event_definition('cd_dvd_updated');
$cdMeta = logging_sanitize_event_meta(['record_id' => 9, 'serial' => 'P86-0009', 'media' => 'DVD', 'before' => ['password' => 'secret', 'direction' => 'IN']], $cdDvd['code'], 'cd_dvd_updated');
$checks['CD/DVD metadata survives allowlist while nested secrets remain redacted'] = ($cdMeta['serial'] ?? '') === 'P86-0009'
    && ($cdMeta['media'] ?? '') === 'DVD'
    && ($cdMeta['before']['password'] ?? '') === '[REDACTED]';
$checks['CAB, LDAP and Profile resolve to documented audit catalog entries'] = $governance['category'] === 'audit'
    && $ldap['category'] === 'audit' && $profile['category'] === 'audit'
    && $catalogGovernance['category'] === 'audit' && $catalogLdap['category'] === 'audit'
    && $catalogProfile['category'] === 'audit' && $catalogGovernance['title'] !== 'رخداد سامانه';
$checks['audit context maps governance and CD/DVD targets to their own modules'] = activity_audit_context('change_updated', null, ['change_id' => 99, 'change_number' => 'CHG-0099'])['module'] === 'governance'
    && activity_audit_context('cd_dvd_updated', null, ['record_id' => 9, 'serial' => 'P86-0009'])['target_type'] === 'cd_dvd_record';
$pdo->rollBack();
$checks['business rollback also removes the success audit row'] = count($pdo->committed) === 0 && count($pdo->pending) === 0;

$logDir = $root . '/storage/logs';
@mkdir($logDir, 0750, true);
file_put_contents($logDir . '/db-circuit.json', json_encode(['state' => 'open', 'failures' => 3, 'open_until' => time() + 120]));
$pdo->beginTransaction();
$blocked = false;
try {
    save_audit_required(17, 'change_updated', null, ['change_id' => 82, 'change_number' => 'CHG-0082']);
} catch (RuntimeException) {
    $blocked = true;
}
$spoolFiles = glob($logDir . '/spool/*.jsonl') ?: [];
$checks['circuit-open transaction refuses to spool a success audit independently'] = $blocked && count($pdo->pending) === 0 && count($pdo->committed) === 0 && count($spoolFiles) === 0;
$pdo->rollBack();

$failed = [];
foreach ($checks as $name => $passed) { echo ($passed ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL; if (!$passed) $failed[] = $name; }
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed) { fwrite(STDERR, count($failed) . " atomic audit test(s) failed.\n"); exit(1); }
echo count($checks) . " atomic audit tests passed.\n";
