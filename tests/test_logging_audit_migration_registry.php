<?php
declare(strict_types=1);

/** Offline regression test for the canonical schema_updates(version,migration) registry. */
const APP_ROOT = __DIR__ . '/..';
$GLOBALS['audit_table_exists'] = true;
$GLOBALS['audit_applied_migrations'] = [];
$GLOBALS['audit_last_query'] = '';

function db_table_exists(string $table): bool
{
    return $table === 'schema_updates' && (bool) $GLOBALS['audit_table_exists'];
}

final class AuditMigrationStatement
{
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT): array
    {
        return $GLOBALS['audit_applied_migrations'];
    }
}
final class AuditMigrationDatabase
{
    public function query(string $sql): AuditMigrationStatement
    {
        $GLOBALS['audit_last_query'] = $sql;
        if ($sql !== 'SELECT migration FROM schema_updates') {
            throw new RuntimeException('Unexpected schema_updates query: ' . $sql);
        }
        return new AuditMigrationStatement();
    }
}
function db(): AuditMigrationDatabase
{
    static $database;
    return $database ??= new AuditMigrationDatabase();
}

require APP_ROOT . '/lib/logging-audit.php';

function audit_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
    echo "PASS: {$message}\n";
}

$migrations = [];
foreach (glob(APP_ROOT . '/migrations/*.sql') ?: [] as $file) {
    if (str_contains(strtolower(basename($file)), 'rollback')) { continue; }
    $migrations[] = strtolower(basename($file));
}
$GLOBALS['audit_applied_migrations'] = $migrations;
$clean = logging_audit_check_db_pending_migrations();
audit_assert($GLOBALS['audit_last_query'] === 'SELECT migration FROM schema_updates', 'audit reads the real migration column');
audit_assert($clean['findings'] === [], 'all registered migration filenames produce a clean result');

$GLOBALS['audit_applied_migrations'] = array_slice($migrations, 1);
$pending = logging_audit_check_db_pending_migrations();
audit_assert(count($pending['findings']) === (count($migrations) > 0 ? 1 : 0), 'an unregistered migration is reported as pending');
if ($migrations) {
    audit_assert($pending['findings'][0]['where'] === 'migrations/' . basename(glob(APP_ROOT . '/migrations/*.sql')[0]), 'pending finding identifies the missing migration');
}

$GLOBALS['audit_table_exists'] = false;
$missingTable = logging_audit_check_db_pending_migrations();
audit_assert(count($missingTable['findings']) === 1 && $missingTable['findings'][0]['where'] === 'schema_updates', 'missing registry table fails closed');

echo "Logging migration registry regression tests passed.\n";
