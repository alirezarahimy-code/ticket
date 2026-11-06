<?php
declare(strict_types=1);

$dsn = (string) getenv('ITSM_TEST_DSN');
$user = (string) getenv('ITSM_TEST_DB_USER');
$pass = (string) getenv('ITSM_TEST_DB_PASS');
$dbName = '';
if (preg_match('/dbname=([^;]+)/i', $dsn, $m)) $dbName = trim($m[1]);
if (getenv('ITSM_TEST_ALLOW_DESTRUCTIVE') !== 'YES' || $dsn === '' || !str_contains(strtolower($dbName), 'test')) {
    fwrite(STDOUT, "SKIP: set ITSM_TEST_ALLOW_DESTRUCTIVE=YES and ITSM_TEST_DSN to a dedicated database whose name contains 'test'.\n");
    exit(0);
}
if (!extension_loaded('pdo_mysql')) {
    fwrite(STDOUT, "SKIP: pdo_mysql is unavailable.\n");
    exit(0);
}

$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]);
$pdo->exec('DROP TABLE IF EXISTS schema_updates, log_issue_notifications, log_issue_history, log_issue_events, logging_health_alerts, log_issues, notifications, ticket_events, tickets, activity_logs, system_logs');
$pdo->exec("CREATE TABLE schema_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(32) NOT NULL,
    migration VARCHAR(190) NOT NULL,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_schema_update (version, migration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE system_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, event_id CHAR(36) NULL UNIQUE, request_id CHAR(36) NULL, trace_id CHAR(36) NULL, operation_id CHAR(36) NULL, parent_event_id CHAR(36) NULL,
    code VARCHAR(40) NULL, category VARCHAR(24) NOT NULL DEFAULT 'system', fingerprint CHAR(64) NULL,
    level VARCHAR(20) NOT NULL DEFAULT 'error', context VARCHAR(120) NULL, message TEXT NULL, meta_json JSON NULL,
    user_id INT UNSIGNED NULL, request_uri VARCHAR(255) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE activity_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, event_id CHAR(36) NULL UNIQUE, request_id CHAR(36) NULL, trace_id CHAR(36) NULL, operation_id CHAR(36) NULL, parent_event_id CHAR(36) NULL, result VARCHAR(16) NOT NULL DEFAULT 'unknown', code VARCHAR(40) NULL, category VARCHAR(24) NOT NULL DEFAULT 'audit', user_id INT UNSIGNED NULL, username VARCHAR(190) NULL, full_name VARCHAR(190) NULL, role VARCHAR(40) NULL, action_code VARCHAR(120) NOT NULL DEFAULT 'test', action_label VARCHAR(190) NULL, module VARCHAR(80) NULL, target_type VARCHAR(40) NULL, target_id BIGINT UNSIGNED NULL, target_label VARCHAR(255) NULL, meta_json JSON NULL, ip_address VARCHAR(64) NULL, computer_name VARCHAR(190) NULL, hostname VARCHAR(190) NULL, user_agent VARCHAR(255) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE tickets (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE ticket_events (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, event_id CHAR(36) NULL, request_id CHAR(36) NULL, trace_id CHAR(36) NULL, operation_id CHAR(36) NULL, parent_event_id CHAR(36) NULL, ticket_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NULL, event_type VARCHAR(80) NOT NULL, from_status VARCHAR(40) NULL, to_status VARCHAR(40) NULL, details JSON NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_events_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE notifications (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, ticket_id INT UNSIGNED NULL, notification_type VARCHAR(80) NOT NULL, title VARCHAR(255) NOT NULL, body TEXT NOT NULL, is_read TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, read_at DATETIME NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-mysql-logging-test-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
$GLOBALS['__logging_mysql_test_pdo'] = $pdo;
$GLOBALS['__app_pdo_instance'] = $pdo;
function db(bool $reconnect = false): PDO { return $GLOBALS['__logging_mysql_test_pdo']; }
function execute_migration_statement(PDO $pdo, string $sql): void
{
    $statement = $pdo->query($sql);
    if ($statement instanceof PDOStatement) {
        do {
            $statement->fetchAll(PDO::FETCH_ASSOC);
        } while ($statement->nextRowset());
        $statement->closeCursor();
    }
}
function split_sql_statements(string $sql): array
{
    $statements = [];
    $buffer = '';
    $quote = null;
    $lineComment = false;
    $blockComment = false;
    $length = strlen($sql);
    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $sql[$i + 1] ?? '';
        if ($lineComment) {
            if ($char === "\n") {
                $lineComment = false;
                $buffer .= "\n";
            }
            continue;
        }
        if ($blockComment) {
            if ($char === '*' && $next === '/') {
                $blockComment = false;
                $i++;
            }
            continue;
        }
        if ($quote !== null) {
            $buffer .= $char;
            if ($char === '\\' && $i + 1 < $length) {
                $buffer .= $sql[++$i];
                continue;
            }
            if ($char === $quote) {
                if ($next === $quote) {
                    $buffer .= $sql[++$i];
                    continue;
                }
                $quote = null;
            }
            continue;
        }
        if ($char === '-' && $next === '-' && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) {
            $lineComment = true;
            $i++;
            continue;
        }
        if ($char === '#') {
            $lineComment = true;
            continue;
        }
        if ($char === '/' && $next === '*') {
            $blockComment = true;
            $i++;
            continue;
        }
        if ($char === "'" || $char === '"' || ord($char) === 96) {
            $quote = $char;
            $buffer .= $char;
            continue;
        }
        if ($char === ';') {
            $statement = trim($buffer);
            if ($statement !== '') $statements[] = $statement;
            $buffer = '';
            continue;
        }
        $buffer .= $char;
    }
    $statement = trim($buffer);
    if ($statement !== '') $statements[] = $statement;
    return $statements;
}
require dirname(__DIR__) . '/lib/logging-core.php';
require dirname(__DIR__) . '/lib/logging-replay.php';
require dirname(__DIR__) . '/lib/activity-audit.php';

$migration = file_get_contents(dirname(__DIR__) . '/migrations/025-logging-issues-alerts.sql');
if (!is_string($migration)) throw new RuntimeException('Migration 025 could not be read.');
foreach (split_sql_statements($migration) as $statement) {
    $statement = preg_replace('/^\s*--.*$/m', '', trim($statement));
    if ($statement !== '') execute_migration_statement($pdo, $statement);
}
foreach (['026-logging-legacy-result-unknown.sql', '027-logging-correlation-ids.sql', '028-logging-fingerprint-v2.sql', '029-activity-result-default-unknown.sql', '030-logging-notification-idempotency-and-ticket-history.sql', '033-logging-legacy-context-collation.sql'] as $migrationName) {
    $migration = file_get_contents(dirname(__DIR__) . '/migrations/' . $migrationName);
    if (!is_string($migration)) throw new RuntimeException($migrationName . ' could not be read.');
    foreach (split_sql_statements($migration) as $statement) {
        $statement = preg_replace('/^\s*--.*$/m', '', trim($statement));
        if ($statement !== '') execute_migration_statement($pdo, $statement);
    }
}
// Migration 025 must remain safe when the updater retries a migration after an interrupted release.
$migration025 = file_get_contents(dirname(__DIR__) . '/migrations/030-logging-notification-idempotency-and-ticket-history.sql');
foreach (split_sql_statements((string) $migration025) as $statement) {
    $statement = preg_replace('/^\s*--.*$/m', '', trim($statement));
    if ($statement !== '') execute_migration_statement($pdo, $statement);
}

$checks = [];

// Migration 032 must not pre-register itself: the updater records the current
// migration after its SQL completes. A self-insert would make that final INSERT
// fail on uq_schema_update(version, migration) and abort the package update.
$baselineSql = file_get_contents(dirname(__DIR__) . '/migrations/032-schema-updates-baseline.sql');
if (!is_string($baselineSql)) throw new RuntimeException('Migration 032 could not be read.');
foreach (split_sql_statements($baselineSql) as $statement) {
    $statement = preg_replace('/^\\s*--.*$/m', '', trim($statement));
    if ($statement !== '') execute_migration_statement($pdo, $statement);
}
$updaterRegistryInsertSucceeded = true;
try {
    $pdo->prepare('INSERT INTO schema_updates (version, migration) VALUES (?, ?)')
        ->execute(['1.62.83', '032-schema-updates-baseline.sql']);
} catch (PDOException) {
    $updaterRegistryInsertSucceeded = false;
}
$checks['migration 032 leaves its own registry row for the updater'] = $updaterRegistryInsertSucceeded
    && (int) $pdo->query("SELECT COUNT(*) FROM schema_updates WHERE version='1.62.83' AND migration='032-schema-updates-baseline.sql'")->fetchColumn() === 1;
$event = ['event_id'=>logging_uuid(),'code'=>'SYS-DB-001','category'=>'system','level'=>'error','context'=>'integration-test','message'=>'database integration failure','meta'=>[]];
$checks['system log inserts and creates one aggregated issue'] = logging_store_system($event)
    && (int) $pdo->query('SELECT COUNT(*) FROM system_logs')->fetchColumn() === 1
    && (int) $pdo->query('SELECT COUNT(*) FROM log_issues')->fetchColumn() === 1;
$event['event_id'] = logging_uuid();
$checks['repeated error increments the same issue'] = logging_store_system($event)
    && (int) $pdo->query('SELECT occurrences FROM log_issues')->fetchColumn() === 2;
$pdo->exec("UPDATE log_issues SET status='resolved' WHERE code='SYS-DB-001'");
$event['event_id'] = logging_uuid();
$checks['new occurrence reopens resolved issue'] = logging_store_system($event)
    && (string) $pdo->query('SELECT status FROM log_issues')->fetchColumn() === 'open';
$dedupeRow = $pdo->query('SELECT event_id, request_id, code, category, level, context, message, created_at FROM system_logs ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$beforeDedupeCount = (int) $pdo->query('SELECT occurrences FROM log_issues')->fetchColumn();
$dedupeOk = logging_issue_upsert($dedupeRow, false);
$afterDedupeCount = (int) $pdo->query('SELECT occurrences FROM log_issues')->fetchColumn();
$checks['issue aggregation is idempotent for an already processed Event ID'] = $dedupeOk && $beforeDedupeCount === $afterDedupeCount;

$replayId = logging_uuid();
$insertSql = 'INSERT INTO system_logs (event_id, level, context, message) VALUES (?, ?, ?, ?)';
$first = logging_replay_insert('system_logs', $insertSql, [$replayId, 'error', 'replay', 'test'], $replayId);
$second = logging_replay_insert('system_logs', $insertSql, [$replayId, 'error', 'replay', 'test'], $replayId);
$checks['replay insert is idempotent by event id'] = $first === true && $second === false;

$pdo->beginTransaction();
$event['event_id'] = logging_uuid();
$spooled = logging_store_system($event) === false && logging_spool_stats()['files'] > 0;
$pdo->rollBack();
$checks['open transaction routes technical log to spool'] = $spooled;
$replayResult = logging_replay_process_directory($root . '/storage/logs/spool', 500);
$checks['actual replay worker drains transaction fallback into MySQL'] = $spooled
    && $replayResult['replayed'] === 1 && $replayResult['remaining_files'] === 0
    && (int) $pdo->query('SELECT COUNT(*) FROM system_logs')->fetchColumn() === 5;

$activityEventId = logging_uuid();
$activityRequestId = logging_uuid();
logging_spool_write('activity', ['event_id'=>$activityEventId,'request_id'=>$activityRequestId,'result'=>'denied','code'=>'SEC-ACL-001','category'=>'security','user_id'=>null,'username'=>'test','full_name'=>'Test','role'=>'user','action_code'=>'access_denied','action_label'=>'Denied','module'=>'test','target_type'=>null,'target_id'=>null,'target_label'=>null,'meta'=>[],'ip_address'=>null,'computer_name'=>null,'hostname'=>null,'user_agent'=>'test','created_at'=>date('Y-m-d H:i:s')]);
$activityReplay = logging_replay_process_directory($root . '/storage/logs/spool', 500);
$checks['actual replay worker restores activity audit events'] = $activityReplay['replayed'] === 1
    && (int) $pdo->query('SELECT COUNT(*) FROM activity_logs WHERE event_id = ' . $pdo->quote($activityEventId))->fetchColumn() === 1;
$auditCountBeforeRollback = (int) $pdo->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn();
$pdo->beginTransaction();
save_audit_required(1, 'change_updated', null, ['change_id' => 901, 'change_number' => 'CHG-0901']);
$auditVisibleInsideTransaction = (int) $pdo->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn() === $auditCountBeforeRollback + 1;
$pdo->rollBack();
$checks['successful required audit is rolled back with the business transaction'] = $auditVisibleInsideTransaction
    && (int) $pdo->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn() === $auditCountBeforeRollback;

$circuitDir = $root . '/storage/logs';
@mkdir($circuitDir, 0750, true);
$spoolBeforeCircuitSuccess = logging_spool_stats()['files'];
file_put_contents($circuitDir . '/db-circuit.json', json_encode(['state' => 'open', 'failures' => 3, 'open_until' => time() + 120]));
$pdo->beginTransaction();
$circuitBlocked = false;
try {
    save_audit_required(1, 'change_updated', null, ['change_id' => 902, 'change_number' => 'CHG-0902']);
} catch (RuntimeException) {
    $circuitBlocked = true;
}
$pdo->rollBack();
@unlink($circuitDir . '/db-circuit.json');
$checks['circuit-open transaction does not create an independent success spool record'] = $circuitBlocked
    && logging_spool_stats()['files'] === $spoolBeforeCircuitSuccess
    && (int) $pdo->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn() === $auditCountBeforeRollback;

$badFile = $root . '/storage/logs/spool/system-' . date('Ymd') . '.jsonl';
@mkdir(dirname($badFile), 0750, true);
$badLine = "{not-json}\n";
$goodEvent = ['event_id'=>logging_uuid(),'request_id'=>logging_uuid(),'code'=>'SYS-PHP-001','category'=>'system','level'=>'error','context'=>'poison-test','message'=>'valid event after poison','meta'=>[],'created_at'=>date('Y-m-d H:i:s')];
$goodLine = json_encode(['version'=>1,'stream'=>'system','queued_at'=>date('c'),'payload'=>$goodEvent], JSON_UNESCAPED_SLASHES) . "\n";
file_put_contents($badFile, $badLine . $goodLine, FILE_APPEND | LOCK_EX);
$poisonResult = logging_replay_process_directory($root . '/storage/logs/spool', 500);
// The replay worker atomically renames the source file and publishes a retry file.
$remainingPoison = '';
foreach (glob($root . '/storage/logs/spool/system*.jsonl') ?: [] as $activeSpoolFile) {
    $activeContents = @file_get_contents($activeSpoolFile);
    if (is_string($activeContents)) {
        $remainingPoison .= $activeContents;
    }
}
$checks['invalid spool line is retained in the retry queue without blocking later valid records'] = $poisonResult['replayed'] === 1
    && $poisonResult['failed'] === 1 && str_contains($remainingPoison, '{not-json}')
    && (int) $pdo->query("SELECT COUNT(*) FROM system_logs WHERE context = 'poison-test'")->fetchColumn() === 1;
$checks['migration created alert throttle table'] = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'logging_health_alerts'")->fetchColumn() === 1;

// Verify migration 025 changes the live notification schema and makes retries idempotent.
require dirname(__DIR__) . '/lib/notify-users.php';
$notifyKey = hash('sha256', 'mysql-integration-notification');
notify_user(1, 'system_health_alert', 'test', 'body', null, $notifyKey);
notify_user(1, 'system_health_alert', 'test', 'body', null, $notifyKey);
$checks['notification retry with same idempotency key inserts one row'] = (int) $pdo->query('SELECT COUNT(*) FROM notifications')->fetchColumn() === 1;
$completionEventId = logging_deterministic_uuid('ITSM-SYSTEM-UPDATE-COMPLETION', 'backup-integration-test');
$spoolBeforeDuplicateCompletion = logging_spool_stats()['files'];
$completionOne = activity_log(['event_id' => $completionEventId, 'user_id' => 1, 'action_code' => 'system_updated', 'result' => 'success', 'meta' => ['version' => '1.62.76', 'mode' => 'upgrade', 'from' => '1.62.75', 'migrations' => [], 'files' => 0, 'backup' => 'backup-integration-test']]);
$completionTwo = activity_log(['event_id' => $completionEventId, 'user_id' => 1, 'action_code' => 'system_updated', 'result' => 'success', 'meta' => ['version' => '1.62.76', 'mode' => 'upgrade', 'from' => '1.62.75', 'migrations' => [], 'files' => 0, 'backup' => 'backup-integration-test']]);
$checks['deterministic update completion Event ID is idempotent in activity audit without duplicate spool fallback'] = $completionOne === true && $completionTwo === false && (int) $pdo->query('SELECT COUNT(*) FROM activity_logs WHERE event_id = ' . $pdo->quote($completionEventId))->fetchColumn() === 1 && logging_spool_stats()['files'] === $spoolBeforeDuplicateCompletion;
$deleteRule = $pdo->query("SELECT DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME='ticket_events' AND CONSTRAINT_NAME='fk_events_ticket'")->fetchColumn();
$pdo->exec('INSERT INTO tickets (id) VALUES (77)');
$pdo->exec("INSERT INTO ticket_events (ticket_id, event_type) VALUES (77, 'created')");
$deleteBlocked = false;
try { $pdo->exec('DELETE FROM tickets WHERE id=77'); } catch (PDOException) { $deleteBlocked = true; }
$checks['ticket deletion is restricted while audit history exists'] = $deleteRule === 'RESTRICT' && $deleteBlocked && (int) $pdo->query('SELECT COUNT(*) FROM ticket_events WHERE ticket_id=77')->fetchColumn() === 1;

$failed = [];
foreach ($checks as $name=>$passed) {
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$passed) $failed[] = $name;
}
$pdo->exec('DROP TABLE IF EXISTS schema_updates, log_issue_notifications, log_issue_history, log_issue_events, logging_health_alerts, log_issues, notifications, ticket_events, tickets, activity_logs, system_logs');
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed !== []) { fwrite(STDERR, count($failed) . " MySQL integration test(s) failed.\n"); exit(1); }
echo count($checks) . " MySQL integration tests passed.\n";
