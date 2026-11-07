<?php
declare(strict_types=1);

/**
 * T07 — replay migrations 001..033 against an isolated import of the supplied
 * full database dump. No operational database is contacted or modified.
 *
 * The updater's schema_updates registry is expected to gain bookkeeping rows;
 * its table definition is fingerprinted, while its contents are checked
 * separately. All other table schemas and row contents must remain identical.
 */

$dumpPath = (string) (getenv('CI_DB_DUMP_PATH') ?: '');
$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');
$targetDb = 'rc_t07_full_clone';
$version = trim((string) file_get_contents(dirname(__DIR__) . '/VERSION'));
$mysql = (string) (getenv('ITSM_MYSQL_PATH') ?: 'mariadb');

function t07_fail(string $message): never
{
    fwrite(STDERR, "T07 FAIL: {$message}\n");
    exit(1);
}

function t07_check(bool $condition, string $message): void
{
    if (!$condition) {
        t07_fail($message);
    }
    echo "PASS {$message}\n";
}

function t07_ident(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

/** Execute a MariaDB client command with a SQL file connected to stdin. */
function t07_mysql_file(string $mysql, string $host, string $port, string $user, string $pass, string $database, string $file): void
{
    $command = [
        $mysql,
        '--skip-ssl',
        '--host=' . $host,
        '--port=' . $port,
        '--user=' . $user,
        '--default-character-set=utf8mb4',
        '--database=' . $database,
    ];
    $env = getenv();
    if (!is_array($env)) {
        $env = [];
    }
    if ($pass !== '') {
        $env['MYSQL_PWD'] = $pass;
    }
    $pipes = [];
    $process = proc_open(
        $command,
        [0 => ['file', $file, 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        dirname(__DIR__),
        $env
    );
    if (!is_resource($process)) {
        t07_fail('could not start MariaDB client');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== 0) {
        $code = 'unknown';
        if (preg_match('/ERROR\\s+(\\d+)/i', $stderr . "\n" . $stdout, $match)) {
            $code = $match[1];
        }
        t07_fail('SQL file failed: ' . basename($file) . ' (MariaDB error ' . $code . ')');
    }
}

function t07_tables(PDO $pdo): array
{
    return $pdo->query(
        "SELECT TABLE_NAME FROM information_schema.TABLES " .
        "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME"
    )->fetchAll(PDO::FETCH_COLUMN);
}

/** Per-table DDL and data hashes. schema_updates row data is tracked separately. */
function t07_fingerprint(PDO $pdo): array
{
    $result = [];
    foreach (t07_tables($pdo) as $table) {
        $quoted = t07_ident((string) $table);
        $create = $pdo->query('SHOW CREATE TABLE ' . $quoted)->fetch(PDO::FETCH_NUM);
        if (!isset($create[1])) {
            t07_fail('SHOW CREATE TABLE returned no DDL for ' . $table);
        }
        $ddl = preg_replace('/AUTO_INCREMENT\\s*=\\s*\\d+/i', 'AUTO_INCREMENT=<counter>', (string) $create[1]);
        $schemaHash = hash('sha256', (string) $ddl);

        if ($table === 'schema_updates') {
            $result[$table] = ['schema' => $schemaHash, 'data' => 'tracked-separately', 'rows' => null];
            continue;
        }

        $columns = $pdo->query('SHOW COLUMNS FROM ' . $quoted)->fetchAll(PDO::FETCH_ASSOC);
        $names = array_map(static fn(array $column): string => (string) $column['Field'], $columns);
        $order = implode(', ', array_map('t07_ident', $names));
        $stmt = $pdo->query('SELECT * FROM ' . $quoted . ($order !== '' ? ' ORDER BY ' . $order : ''));
        $ctx = hash_init('sha256');
        $rowCount = 0;
        while (($row = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
            $serialized = serialize($row);
            hash_update($ctx, pack('N', strlen($serialized)) . $serialized);
            $rowCount++;
        }
        $result[$table] = [
            'schema' => $schemaHash,
            'data' => hash_final($ctx),
            'rows' => $rowCount,
        ];
    }
    ksort($result);
    return $result;
}

function t07_registry_rows(PDO $pdo): array
{
    $rows = $pdo->query(
        'SELECT version, migration FROM schema_updates ORDER BY migration, version'
    )->fetchAll(PDO::FETCH_ASSOC);
    return array_map(
        static fn(array $row): string => (string) $row['version'] . "\0" . (string) $row['migration'],
        $rows
    );
}

t07_check($dumpPath !== '' && is_file($dumpPath), 'supplied database dump exists');
t07_check(filesize($dumpPath) >= 1_000_000, 'supplied database dump is full-sized');
t07_check($version !== '', 'application VERSION is readable');
t07_check($targetDb !== (string) (getenv('CI_DB_NAME') ?: ''), 'clone database is isolated from the workflow database');

$admin = new PDO(
    "mysql:host={$host};port={$port};charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
$admin->exec('DROP DATABASE IF EXISTS ' . t07_ident($targetDb));
$admin->exec('CREATE DATABASE ' . t07_ident($targetDb) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

// Strip only database-selection statements from the trusted dump so the import
// is forced into the disposable clone regardless of the source DB name.
$dump = file_get_contents($dumpPath);
if (!is_string($dump) || $dump === '') {
    t07_fail('could not read supplied database dump');
}
$dump = preg_replace(
    '/^[ \t]*(?:CREATE[ \t]+DATABASE(?:[ \t]+IF[ \t]+NOT[ \t]+EXISTS)?|DROP[ \t]+DATABASE(?:[ \t]+IF[ \t]+EXISTS)?|USE)[ \t]+[^;]+;[ \t]*$/mi',
    '',
    $dump
);
$tempDump = tempnam(sys_get_temp_dir(), 'rc-t07-');
if ($tempDump === false || file_put_contents($tempDump, $dump) === false) {
    t07_fail('could not prepare isolated dump import');
}
unset($dump);

try {
    t07_mysql_file($mysql, $host, $port, $user, $pass, $targetDb, $tempDump);
} finally {
    @unlink($tempDump);
}

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$targetDb};charset=utf8mb4",
    $user,
    $pass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => true,
    ]
);
$tables = t07_tables($pdo);
t07_check(count($tables) >= 80, 'imported clone contains the expected full schema footprint');
t07_check(in_array('schema_updates', $tables, true), 'clone contains the migration registry');

$before = t07_fingerprint($pdo);
$registryBefore = t07_registry_rows($pdo);
$registrySetBefore = array_fill_keys($registryBefore, true);

$migrationDir = dirname(__DIR__) . '/migrations';
$files = glob($migrationDir . '/*.sql') ?: [];
$byNumber = [];
foreach ($files as $file) {
    $name = basename($file);
    if (!preg_match('/^(\\d{3})-[a-z0-9-]+\\.sql$/i', $name, $match)) {
        continue;
    }
    if (stripos($name, 'rollback') !== false) {
        continue;
    }
    $number = (int) $match[1];
    if ($number < 1 || $number > 33) {
        continue;
    }
    if (isset($byNumber[$number])) {
        t07_fail('multiple forward migrations found for number ' . $number);
    }
    $byNumber[$number] = $file;
}
$missing = array_values(array_diff(range(1, 33), array_keys($byNumber)));
t07_check($missing === [], 'forward migration set 001..033 is complete');

ksort($byNumber);
$applied = [];
$skipped = [];
foreach ($byNumber as $number => $file) {
    $name = basename($file);
    $check = $pdo->prepare('SELECT COUNT(*) FROM schema_updates WHERE migration = ?');
    $check->execute([$name]);
    if ((int) $check->fetchColumn() > 0) {
        $skipped[] = $name;
        continue;
    }

    t07_mysql_file($mysql, $host, $port, $user, $pass, $targetDb, $file);
    $register = $pdo->prepare('INSERT INTO schema_updates (version, migration) VALUES (?, ?)');
    $register->execute([$version, $name]);
    $applied[] = $name;
}

$after = t07_fingerprint($pdo);
$registryAfter = t07_registry_rows($pdo);
$schemaChanges = [];
$dataChanges = [];
foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $table) {
    if (!isset($before[$table], $after[$table])) {
        $schemaChanges[] = $table . ' (table added/removed)';
        continue;
    }
    if ($before[$table]['schema'] !== $after[$table]['schema']) {
        $schemaChanges[] = $table;
    }
    if ($table !== 'schema_updates'
        && ($before[$table]['data'] !== $after[$table]['data'] || $before[$table]['rows'] !== $after[$table]['rows'])) {
        $dataChanges[] = $table;
    }
}

$registryAdded = array_values(array_diff($registryAfter, $registryBefore));
$missingRegistry = [];
foreach ($byNumber as $file) {
    $name = basename($file);
    $check = $pdo->prepare('SELECT COUNT(*) FROM schema_updates WHERE migration = ?');
    $check->execute([$name]);
    if ((int) $check->fetchColumn() === 0) {
        $missingRegistry[] = $name;
    }
}

echo 'T07 clone: ' . $targetDb . PHP_EOL;
echo 'MariaDB: ' . $pdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
echo 'Migrations applied: ' . count($applied) . '; skipped as already recorded: ' . count($skipped) . PHP_EOL;
echo 'Registry rows added: ' . count($registryAdded) . ' (bookkeeping only)' . PHP_EOL;
echo 'Application schema tables fingerprinted: ' . count($tables) . PHP_EOL;

if ($schemaChanges !== [] || $dataChanges !== [] || $missingRegistry !== []) {
    t07_fail(
        'unexpected schema changes=[' . implode(', ', $schemaChanges) .
        '], application data changes=[' . implode(', ', $dataChanges) .
        '], missing registry=[' . implode(', ', $missingRegistry) . ']'
    );
}

t07_check(true, 'schema and application-data fingerprints are unchanged; migration registry is reconciled');
$admin->exec('DROP DATABASE IF EXISTS ' . t07_ident($targetDb));
echo 'PASS T07 full-database migration replay and fingerprint comparison' . PHP_EOL;
