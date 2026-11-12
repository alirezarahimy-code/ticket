<?php
declare(strict_types=1);

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');
$pdo = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4', $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

function org_role_schema_check(bool $ok, string $message): void
{
    if (!$ok) throw new RuntimeException($message);
    echo 'ok - ' . $message . PHP_EOL;
}

$columns = $pdo->prepare("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME IN ('org_auto_role', 'org_prev_role')");
$columns->execute();
$map = [];
foreach ($columns->fetchAll() as $row) $map[(string) $row['COLUMN_NAME']] = $row;
org_role_schema_check(isset($map['org_auto_role']), 'users.org_auto_role exists');
org_role_schema_check(isset($map['org_prev_role']), 'users.org_prev_role exists');
org_role_schema_check(isset($map['org_auto_role']) && stripos((string) $map['org_auto_role']['COLUMN_TYPE'], 'tinyint(1)') === 0 && strtoupper((string) $map['org_auto_role']['IS_NULLABLE']) === 'NO' && (string) $map['org_auto_role']['COLUMN_DEFAULT'] === '0', 'org_auto_role has NOT NULL DEFAULT 0 contract');
org_role_schema_check(isset($map['org_prev_role']) && stripos((string) $map['org_prev_role']['COLUMN_TYPE'], 'varchar(20)') === 0 && strtoupper((string) $map['org_prev_role']['IS_NULLABLE']) === 'YES', 'org_prev_role has nullable VARCHAR(20) contract');

$username = '__ci_org_role_sync_migration_037__';
$seed = $pdo->prepare("INSERT IGNORE INTO users (username, full_name, role, is_active) VALUES (?, 'CI Org Role Migration Fixture', 'agent', 1)");
$seed->execute([$username]);
$rowQuery = $pdo->prepare('SELECT id, role, org_auto_role, org_prev_role FROM users WHERE username = ? LIMIT 1');
$rowQuery->execute([$username]);
$row = $rowQuery->fetch();
org_role_schema_check(is_array($row), 'pre-existing user row survives role-sync migration');
org_role_schema_check(is_array($row) && (string) $row['role'] === 'agent' && (int) $row['org_auto_role'] === 0 && $row['org_prev_role'] === null, 'existing role is preserved and new columns receive safe defaults');
$probe = $pdo->prepare('SELECT COALESCE(u.org_auto_role, 0) FROM users u WHERE u.id = ?');
$probe->execute([(int) ($row['id'] ?? 0)]);
org_role_schema_check((int) $probe->fetchColumn() === 0, 'automatic-assignment filter query executes against users');
echo 'PASS: organization role-sync schema on MariaDB ' . $pdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
