<?php
declare(strict_types=1);

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'rc_migration_upgrade_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');
$pdo = new PDO(
    'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4',
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

function migration_check(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException($message);
    }
    echo "ok - {$message}\n";
}

$columns = $pdo->prepare(
    'SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()'
);
$columns->execute();
$columnMap = [];
foreach ($columns->fetchAll() as $row) {
    $columnMap[strtolower($row['TABLE_NAME'])][strtolower($row['COLUMN_NAME'])] = (string) ($row['COLUMN_TYPE'] ?? '');
}
migration_check(isset($columnMap['tickets']['ticket_no']), 'migration restores tickets.ticket_no');
migration_check(isset($columnMap['tickets']['requesting_unit_id']), 'migration restores tickets.requesting_unit_id');
migration_check(isset($columnMap['tickets']['rejected_at']), 'migration adds tickets.rejected_at');
migration_check(str_contains($columnMap['tickets']['status'] ?? '', "'rejected'"), 'migration adds rejected to the existing status enum');
migration_check(isset($columnMap['categories']['code']), 'migration restores categories.code');
migration_check(isset($columnMap['service_catalog']['category_id']), 'migration restores service_catalog.category_id');
migration_check(isset($columnMap['ticket_number_seq']['prefix'], $columnMap['ticket_number_seq']['seq']), 'migration restores ticket_number_seq');

$indexes = $pdo->prepare(
    'SELECT TABLE_NAME, COLUMN_NAME, NON_UNIQUE FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()'
);
$indexes->execute();
$unique = [];
foreach ($indexes->fetchAll() as $row) {
    if ((int) $row['NON_UNIQUE'] === 0) {
        $unique[strtolower($row['TABLE_NAME'])][strtolower($row['COLUMN_NAME'])] = true;
    }
}
migration_check(isset($unique['tickets']['ticket_no']), 'ticket number uniqueness is restored');
migration_check(isset($unique['categories']['code']), 'category code uniqueness is restored');

$seedCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM categories WHERE code LIKE 'IT-CAT-%' OR code LIKE 'SUP-CAT-%'"
)->fetchColumn();
migration_check($seedCount >= 16, 'Request Center category seed is idempotently populated');

echo "PASS: Request Center migration 037 upgrade path on MariaDB " .
    $pdo->query('SELECT VERSION()')->fetchColumn() . "\n";
