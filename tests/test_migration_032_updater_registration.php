#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Regression: migration 032 must not pre-register its own filename under
 * the target package version. system-update.php records it immediately after
 * the SQL succeeds; doing both causes a duplicate-key failure after the
 * baseline SQL has already changed the database.
 *
 * A same-session TEMPORARY table shadows schema_updates and leaves the CI
 * database's permanent registry untouched.
 */

$host = getenv('CI_DB_HOST') ?: '127.0.0.1';
$port = (int) (getenv('CI_DB_PORT') ?: 3306);
$name = getenv('CI_DB_NAME') ?: 'persian_ticketing_ci';
$user = getenv('CI_DB_USER') ?: 'root';
$pass = getenv('CI_DB_PASS') ?: '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Throwable $error) {
    fwrite(STDERR, "FAIL: MariaDB connection: {$error->getMessage()}\n");
    exit(1);
}

$temporaryTableCreated = false;
try {
    $pdo->exec(
        "CREATE TEMPORARY TABLE schema_updates (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(32) NOT NULL,
            migration VARCHAR(190) NOT NULL,
            applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_schema_update (version, migration)
        ) ENGINE=InnoDB"
    );
    $temporaryTableCreated = true;

    $migrationPath = dirname(__DIR__) . '/migrations/032-schema-updates-baseline.sql';
    $sql = file_get_contents($migrationPath);
    if (!is_string($sql) || trim($sql) === '') {
        throw new RuntimeException('Migration 032 is empty or unreadable.');
    }

    $pdo->exec($sql);

    $baselineCount = (int) $pdo->query(
        "SELECT COUNT(*) FROM schema_updates
         WHERE version = '1.62.83'
           AND migration <> '032-schema-updates-baseline.sql'"
    )->fetchColumn();
    if ($baselineCount !== 31) {
        throw new RuntimeException("Expected 31 historical baseline rows; found {$baselineCount}.");
    }

    // Exact registration performed by system-update.php after the SQL succeeds.
    $register = $pdo->prepare(
        'INSERT INTO schema_updates (version, migration) VALUES (?, ?)'
    );
    try {
        $register->execute(['1.62.83', '032-schema-updates-baseline.sql']);
    } catch (PDOException $error) {
        throw new RuntimeException(
            'Updater registration collided with a row inserted by migration 032: ' . $error->getMessage(),
            0,
            $error
        );
    }

    $selfCount = (int) $pdo->query(
        "SELECT COUNT(*) FROM schema_updates
         WHERE version = '1.62.83'
           AND migration = '032-schema-updates-baseline.sql'"
    )->fetchColumn();
    if ($selfCount !== 1) {
        throw new RuntimeException("Expected one updater-owned 032 registry row; found {$selfCount}.");
    }

    // Re-running the baseline remains safe and must not duplicate the updater row.
    $pdo->exec($sql);
    $afterRetry = (int) $pdo->query(
        "SELECT COUNT(*) FROM schema_updates
         WHERE version = '1.62.83'
           AND migration = '032-schema-updates-baseline.sql'"
    )->fetchColumn();
    if ($afterRetry !== 1) {
        throw new RuntimeException("Re-running migration 032 changed its registry row count to {$afterRetry}.");
    }

    echo "PASS: migration 032 inserts 31 historical baseline rows without self-registering.\n";
    echo "PASS: updater registers migration 032 exactly once under the target version.\n";
    echo "PASS: re-running migration 032 preserves the updater-owned registry row.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "FAIL: migration 032 updater-registration regression: {$error->getMessage()}\n");
    exit(1);
} finally {
    if ($temporaryTableCreated) {
        try {
            $pdo->exec('DROP TEMPORARY TABLE IF EXISTS schema_updates');
        } catch (Throwable) {
            // Closing the connection also removes the temporary table.
        }
    }
}
