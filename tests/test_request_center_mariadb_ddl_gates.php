<?php
declare(strict_types=1);

/**
 * T01-T03: prove MariaDB DDL transaction semantics on a disposable CI schema.
 * This test creates and drops only rc_t0*_probe tables in the ephemeral CI DB.
 * Never point CI_DB_* at an operational database.
 */
$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

function tcheck(bool $condition, string $label): void
{
    if (!$condition) {
        throw new RuntimeException('FAIL ' . $label);
    }
    echo 'PASS ' . $label . PHP_EOL;
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() === 1;
}

$version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
echo 'MariaDB/MySQL version: ' . $version . PHP_EOL;

$probeTables = [
    'rc_t01_ddl_probe',
    'rc_t01_marker',
    'rc_t02_ddl_probe',
    'rc_t02_marker',
    'rc_t03_alter_probe',
];

try {
    foreach ($probeTables as $table) {
        $pdo->exec('DROP TABLE IF EXISTS ' . $table);
    }

    // T01 — CREATE TABLE commits the open transaction implicitly.
    $pdo->exec('CREATE TABLE rc_t01_marker (id INT NOT NULL PRIMARY KEY) ENGINE=InnoDB');
    $pdo->beginTransaction();
    $pdo->exec('INSERT INTO rc_t01_marker (id) VALUES (1)');
    $pdo->exec('CREATE TABLE rc_t01_ddl_probe (id INT NOT NULL PRIMARY KEY) ENGINE=InnoDB');
    $transactionStillOpen = $pdo->inTransaction();
    if ($transactionStillOpen) {
        $pdo->rollBack();
    }
    $markerCount = (int) $pdo->query('SELECT COUNT(*) FROM rc_t01_marker WHERE id = 1')->fetchColumn();
    tcheck(
        !$transactionStillOpen && $markerCount === 1 && tableExists($pdo, 'rc_t01_ddl_probe'),
        'T01 CREATE TABLE inside transaction implicitly commits prior DML; ROLLBACK cannot undo it'
    );

    // T02 — IF NOT EXISTS does not make CREATE TABLE transactional.
    $pdo->exec('CREATE TABLE rc_t02_marker (id INT NOT NULL PRIMARY KEY) ENGINE=InnoDB');
    $pdo->beginTransaction();
    $pdo->exec('INSERT INTO rc_t02_marker (id) VALUES (2)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS rc_t02_ddl_probe (id INT NOT NULL PRIMARY KEY) ENGINE=InnoDB');
    $transactionStillOpen = $pdo->inTransaction();
    if ($transactionStillOpen) {
        $pdo->rollBack();
    }
    $markerCount = (int) $pdo->query('SELECT COUNT(*) FROM rc_t02_marker WHERE id = 2')->fetchColumn();
    tcheck(
        !$transactionStillOpen && $markerCount === 1 && tableExists($pdo, 'rc_t02_ddl_probe'),
        'T02 CREATE TABLE IF NOT EXISTS also implicitly commits the open transaction'
    );

    // T03 — MariaDB 10.4.32 syntax and repeatability for ADD COLUMN IF NOT EXISTS.
    $pdo->exec('CREATE TABLE rc_t03_alter_probe (id INT NOT NULL PRIMARY KEY, payload VARCHAR(40) NOT NULL) ENGINE=InnoDB');
    $pdo->exec("INSERT INTO rc_t03_alter_probe (id, payload) VALUES (1, 'preserve-me')");
    $pdo->exec('ALTER TABLE rc_t03_alter_probe ADD COLUMN IF NOT EXISTS probe_value INT NULL');
    $pdo->exec('ALTER TABLE rc_t03_alter_probe ADD COLUMN IF NOT EXISTS probe_value INT NULL');
    $columnCount = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rc_t03_alter_probe' AND COLUMN_NAME = 'probe_value'"
    )->fetchColumn();
    $payload = (string) $pdo->query('SELECT payload FROM rc_t03_alter_probe WHERE id = 1')->fetchColumn();
    tcheck(
        $columnCount === 1 && $payload === 'preserve-me',
        'T03 ADD COLUMN IF NOT EXISTS parses on this server, is repeatable, and preserves existing data'
    );

    echo 'PASS T01-T03 completed on ' . $version . PHP_EOL;
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    foreach ($probeTables as $table) {
        $pdo->exec('DROP TABLE IF EXISTS ' . $table);
    }
}
