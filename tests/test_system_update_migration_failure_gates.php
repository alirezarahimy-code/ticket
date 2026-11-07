<?php
declare(strict_types=1);

/**
 * T05/T06 fault-injection tests for the actual system_update_apply() runner.
 *
 * Uses a disposable MariaDB database and synthetic, idempotent ZIP packages.
 * Backup creation and the external mysql process are stubbed; SQL statements are
 * executed against real MariaDB through PDO. This tests runner ordering,
 * partial-DDL persistence, registry behavior and safe retry—not real backup/CLI.
 */

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$adminUser = (string) (getenv('CI_DB_USER') ?: 'root');
$adminPass = (string) (getenv('CI_DB_PASS') ?: '');
$dbName = 'rc_update_gates_ci_' . bin2hex(random_bytes(3));
$admin = new PDO(
    "mysql:host={$host};port={$port};charset=utf8mb4",
    $adminUser,
    $adminPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$admin->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$testPdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4",
    $adminUser,
    $adminPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$appRoot = sys_get_temp_dir() . '/rc-update-root-' . bin2hex(random_bytes(5));
$backupDir = sys_get_temp_dir() . '/rc-update-backups-' . bin2hex(random_bytes(5));
$GLOBALS['rcUpdatePackageFiles'] = [];
mkdir($appRoot, 0700, true);
mkdir($backupDir, 0700, true);
mkdir($appRoot . '/storage', 0700, true);
file_put_contents($appRoot . '/VERSION', "1.62.82\n");
define('APP_ROOT', $appRoot);
$GLOBALS['rcUpdatePdo'] = $testPdo;
$GLOBALS['rcUpdateConfig'] = [
    'database.host' => $host,
    'database.port' => (int) $port,
    'database.user' => $adminUser,
    'database.name' => $dbName,
];

function db(): PDO
{
    return $GLOBALS['rcUpdatePdo'];
}

function cfg(string $key, mixed $default = null): mixed
{
    return $GLOBALS['rcUpdateConfig'][$key] ?? $default;
}

function backup_mysql_binary(string $name): ?string
{
    return 'pdo-mariadb-test-runner';
}

function create_backup_bundle(): array
{
    return ['path' => $GLOBALS['rcUpdateBackupDir'], 'name' => 'synthetic-test-backup.zip'];
}

function backup_run_process(string $binary, array $args, string $inputFile): string
{
    $sql = file_get_contents($inputFile);
    if (!is_string($sql)) {
        throw new RuntimeException('ERROR migration SQL file is unreadable');
    }
    foreach (preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [] as $statement) {
        $statement = trim($statement);
        if ($statement === '') {
            continue;
        }
        try {
            $GLOBALS['rcUpdatePdo']->exec($statement);
        } catch (Throwable $error) {
            throw new RuntimeException('ERROR ' . $error->getMessage(), 0, $error);
        }
    }
    return 'OK';
}

function save_audit_required(int $actorId, string $event, ?int $entityId = null, array $metadata = []): bool
{
    return true;
}

$GLOBALS['rcUpdateBackupDir'] = $backupDir;
require dirname(__DIR__) . '/system-update.php';

function rc_update_check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('T05/T06 failure: ' . $message);
    }
    echo 'PASS ' . $message . PHP_EOL;
}

function rc_update_package(string $version, string $requires, string $migration, string $sql): string
{
    $path = tempnam(sys_get_temp_dir(), 'rc-update-pkg-');
    if (!is_string($path)) {
        throw new RuntimeException('Could not create temporary ZIP path');
    }
    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('Could not open temporary ZIP');
    }
    $manifest = [
        'version' => $version,
        'requires_version' => $requires,
        'description' => 'Disposable migration fault-injection package',
        'migrations' => [$migration],
        'files' => [],
        'sha256' => [],
    ];
    $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR));
    $zip->addFromString('migrations/' . $migration, $sql);
    $zip->close();
    $GLOBALS['rcUpdatePackageFiles'][] = $path;
    return $path;
}

function rc_update_upload(string $path, string $name): array
{
    return [
        'error' => UPLOAD_ERR_OK,
        'name' => $name,
        'size' => filesize($path),
        'tmp_name' => $path,
    ];
}

function rc_update_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() === 1;
}

try {
    // T05: SQL fails after a committed DDL/DML prefix; no registry row or version bump is allowed.
    $t05Migration = '900-t05-fail-after-create.sql';
    $t05Sql = "CREATE TABLE IF NOT EXISTS t05_probe (id INT PRIMARY KEY) ENGINE=InnoDB;\n"
        . "INSERT IGNORE INTO t05_probe (id) VALUES (1);\n"
        . "ALTER TABLE t05_missing ADD COLUMN marker INT NULL;\n";
    $t05Zip = rc_update_package('1.62.83', '1.62.82', $t05Migration, $t05Sql);
    $t05Failed = false;
    try {
        system_update_apply(rc_update_upload($t05Zip, 't05.zip'), ['id' => 1]);
    } catch (RuntimeException $error) {
        $t05Failed = str_contains($error->getMessage(), 'مهاجرت ' . $t05Migration . ' ناموفق بود');
    }
    rc_update_check($t05Failed, 'T05 runner reports a migration that fails after earlier statements');
    rc_update_check(rc_update_table_exists($testPdo, 't05_probe'), 'T05 proves prior CREATE TABLE persists after later DDL failure');
    rc_update_check((int) $testPdo->query('SELECT COUNT(*) FROM t05_probe')->fetchColumn() === 1, 'T05 proves prior DML persists after later DDL failure');
    rc_update_check(!system_update_migration_applied('1.62.83', $t05Migration), 'T05 does not mark a partially failed migration as applied');
    rc_update_check(trim((string) file_get_contents(APP_ROOT . '/VERSION')) === '1.62.82', 'T05 leaves VERSION unchanged after failure');

    // Repair the missing prerequisite, then retry the same package. Its SQL must be idempotent.
    $testPdo->exec('CREATE TABLE t05_missing (id INT PRIMARY KEY) ENGINE=InnoDB');
    $t05Result = system_update_apply(rc_update_upload($t05Zip, 't05.zip'), ['id' => 1]);
    rc_update_check(in_array($t05Migration, $t05Result['migrations'], true), 'T05 safely retries and completes the same migration');
    rc_update_check((int) $testPdo->query('SELECT COUNT(*) FROM t05_probe')->fetchColumn() === 1, 'T05 retry does not duplicate prior rows');
    rc_update_check(system_update_migration_applied('1.62.83', $t05Migration), 'T05 records the migration only after successful completion');
    rc_update_check(trim((string) file_get_contents(APP_ROOT . '/VERSION')) === '1.62.83', 'T05 advances VERSION only after successful completion');

    // T06: SQL succeeds, but the registry INSERT fails. Schema persists and registry remains absent.
    $testPdo->exec(
        "CREATE TRIGGER t06_fail_registry BEFORE INSERT ON schema_updates
         FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'forced registry failure'"
    );
    $t06Migration = '900-t06-registry-fail.sql';
    $t06Sql = "CREATE TABLE IF NOT EXISTS t06_probe (id INT PRIMARY KEY) ENGINE=InnoDB;\n"
        . "INSERT IGNORE INTO t06_probe (id) VALUES (1);\n";
    $t06Zip = rc_update_package('1.62.84', '1.62.83', $t06Migration, $t06Sql);
    $t06Failed = false;
    try {
        system_update_apply(rc_update_upload($t06Zip, 't06.zip'), ['id' => 1]);
    } catch (Throwable $error) {
        $t06Failed = str_contains($error->getMessage(), 'forced registry failure');
    }
    rc_update_check($t06Failed, 'T06 injects failure exactly at schema_updates registry INSERT');
    rc_update_check(rc_update_table_exists($testPdo, 't06_probe'), 'T06 proves migration SQL persisted before registry failure');
    rc_update_check((int) $testPdo->query('SELECT COUNT(*) FROM t06_probe')->fetchColumn() === 1, 'T06 proves migration DML persisted before registry failure');
    rc_update_check(!system_update_migration_applied('1.62.84', $t06Migration), 'T06 does not report a registry-failed migration as applied');
    rc_update_check(trim((string) file_get_contents(APP_ROOT . '/VERSION')) === '1.62.83', 'T06 leaves VERSION unchanged when registry write fails');

    // Removing the fault and retrying must safely finish and register the same migration.
    $testPdo->exec('DROP TRIGGER t06_fail_registry');
    $t06Result = system_update_apply(rc_update_upload($t06Zip, 't06.zip'), ['id' => 1]);
    rc_update_check(in_array($t06Migration, $t06Result['migrations'], true), 'T06 safely retries after registry service is restored');
    rc_update_check((int) $testPdo->query('SELECT COUNT(*) FROM t06_probe')->fetchColumn() === 1, 'T06 retry does not duplicate prior rows');
    rc_update_check(system_update_migration_applied('1.62.84', $t06Migration), 'T06 records the migration after successful retry');
    rc_update_check(trim((string) file_get_contents(APP_ROOT . '/VERSION')) === '1.62.84', 'T06 advances VERSION only after registry success');

    echo "PASS: T05/T06 updater fault-injection gates on MariaDB " .
        $testPdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
} finally {
    try {
        $admin->exec("DROP DATABASE IF EXISTS `{$dbName}`");
    } catch (Throwable) {
    }
    foreach ($GLOBALS['rcUpdatePackageFiles'] as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
    $removeTree = static function (string $path) use (&$removeTree): void {
        if (!is_dir($path)) {
            if (is_file($path)) { @unlink($path); }
            return;
        }
        foreach (new DirectoryIterator($path) as $item) {
            if ($item->isDot()) { continue; }
            $target = $item->getPathname();
            if ($item->isDir() && !$item->isLink()) { $removeTree($target); }
            else { @unlink($target); }
        }
        @rmdir($path);
    };
    $removeTree($appRoot);
    $removeTree($backupDir);
}
