<?php
declare(strict_types=1);

/** A migration recorded by an earlier package version must never run again. */
const APP_ROOT = __DIR__ . '/..';

final class MigrationRegistryFakeStatement
{
    private array $params = [];

    public function __construct(private string $sql) {}

    public function execute(array $params): bool
    {
        if (str_contains(strtolower($this->sql), 'version = ?')) {
            throw new RuntimeException('Migration registry lookup must not be scoped to the current package version.');
        }
        if (count($params) !== 1) {
            throw new RuntimeException('Migration registry lookup must bind exactly one migration filename.');
        }
        $this->params = $params;
        return true;
    }

    public function fetchColumn(): int
    {
        // Simulate a migration recorded by version 1.62.77 while the new package is 1.62.83.
        return ($this->params[0] ?? null) === '026-logging-legacy-result-unknown.sql' ? 1 : 0;
    }
}

final class MigrationRegistryFakeDb
{
    public string $lastSql = '';

    public function prepare(string $sql): MigrationRegistryFakeStatement
    {
        $this->lastSql = $sql;
        return new MigrationRegistryFakeStatement($sql);
    }
}

$GLOBALS['migration_registry_fake_db'] = new MigrationRegistryFakeDb();

function db(): MigrationRegistryFakeDb
{
    return $GLOBALS['migration_registry_fake_db'];
}
function save_audit(...$args): void {}
function app_error_log(string $message): bool { return true; }
function cfg(string $key, mixed $default = null): mixed { return $default; }

require APP_ROOT . '/system-update.php';

$alreadyApplied = system_update_migration_applied('1.62.83', '026-logging-legacy-result-unknown.sql');
$notApplied = system_update_migration_applied('1.62.83', '033-logging-legacy-context-collation.sql');

if (!$alreadyApplied || $notApplied) {
    fwrite(STDERR, "FAIL: migration registry did not recognize an earlier-version migration record.\n");
    exit(1);
}
if (str_contains(strtolower(db()->lastSql), 'version = ?')) {
    fwrite(STDERR, "FAIL: migration registry still scopes lookups to package version.\n");
    exit(1);
}

echo "PASS: migrations are globally idempotent across package versions.\n";
