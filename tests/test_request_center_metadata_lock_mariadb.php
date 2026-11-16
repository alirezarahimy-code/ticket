<?php
declare(strict_types=1);

/**
 * T09: hold a long InnoDB transaction on the real tickets table while a second
 * MariaDB connection attempts ALTER TABLE. The DDL must time out, leave no
 * column behind, and succeed after the reader transaction is released.
 */

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');
$dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
$a = new PDO($dsn, $user, $pass, $options);
$b = new PDO($dsn, $user, $pass, $options);
$column = 'rc_t09_metadata_lock_probe';

function t09_check(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException('T09 failed: ' . $message);
    }
    echo 'PASS ' . $message . PHP_EOL;
}

function t09_column_exists(PDO $pdo, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute(['tickets', $column]);
    return (int) $stmt->fetchColumn() === 1;
}

try {
    $engine = $a->query(
        "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets'"
    )->fetchColumn();
    t09_check(strtoupper((string) $engine) === 'INNODB', 'tickets uses InnoDB');

    if (t09_column_exists($a, $column)) {
        $b->exec("ALTER TABLE tickets DROP COLUMN {$column}");
    }
    $ticketId = (int) $a->query('SELECT id FROM tickets ORDER BY id LIMIT 1')->fetchColumn();
    t09_check($ticketId > 0, 'T09 has a real ticket row to hold the transaction');

    $a->beginTransaction();
    $lock = $a->prepare('SELECT id, status FROM tickets WHERE id = ? FOR UPDATE');
    $lock->execute([$ticketId]);
    t09_check(is_array($lock->fetch()), 'connection A holds a row lock and metadata read lock');

    $b->exec('SET SESSION lock_wait_timeout = 1');
    $started = microtime(true);
    $timedOut = false;
    try {
        $b->exec("ALTER TABLE tickets ADD COLUMN {$column} INT NULL");
    } catch (PDOException $error) {
        $elapsed = microtime(true) - $started;
        $code = (int) ($error->errorInfo[1] ?? 0);
        $timedOut = $code === 1205;
        t09_check($timedOut, 'ALTER TABLE receives MariaDB metadata-lock timeout 1205 (code ' . $code . ')');
        t09_check($elapsed >= 0.5 && $elapsed < 10, 'metadata lock timeout respects the configured short wait (' . round($elapsed, 2) . 's)');
    }
    t09_check($timedOut, 'ALTER TABLE did not bypass the open transaction');
    t09_check(!t09_column_exists($a, $column), 'timed-out ALTER leaves no partial column');

    $a->rollBack();
    $b->exec('SET SESSION lock_wait_timeout = 5');
    $b->exec("ALTER TABLE tickets ADD COLUMN {$column} INT NULL");
    t09_check(t09_column_exists($b, $column), 'ALTER succeeds after the long transaction releases its metadata lock');
    echo 'PASS: T09 metadata lock/timeout on MariaDB ' . $b->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
} finally {
    if ($a->inTransaction()) {
        $a->rollBack();
    }
    if (t09_column_exists($b, $column)) {
        $b->exec("ALTER TABLE tickets DROP COLUMN {$column}");
    }
}
