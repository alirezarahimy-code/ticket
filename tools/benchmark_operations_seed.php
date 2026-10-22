<?php
declare(strict_types=1);

/** Extend the disposable CI dataset with a valid service and org unit for write-path tests. */
$root = dirname(__DIR__);
require $root . '/tools/benchmark_seed.php';
$config = require $root . '/config.php';
$db = $config['database'];
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $db['name']);
$pdo = new PDO($dsn, $db['user'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

$department = $pdo->query("SELECT id FROM departments WHERE code = 'BENCH' ORDER BY id LIMIT 1")->fetchColumn();
$itHandling = $pdo->query("SELECT id FROM handling_units WHERE code = 'it' AND is_active = 1 ORDER BY id LIMIT 1")->fetchColumn();
$category = $pdo->query("SELECT id FROM categories WHERE code = 'BENCH-REQ' ORDER BY id LIMIT 1")->fetchColumn();
if (!$department || !$itHandling || !$category) {
    throw new RuntimeException('Synthetic benchmark prerequisites are missing.');
}

$pdo->beginTransaction();
try {
    $unitInsert = $pdo->prepare('INSERT INTO org_units (name, code, parent_id, unit_type, is_primary_admin, is_active) VALUES (?, ?, NULL, \'deputy\', 0, 1)');
    $unitInsert->execute(['Benchmark Synthetic Unit', 'BENCH-OPS']);
    $unitId = (int) $pdo->lastInsertId();

    $serviceInsert = $pdo->prepare('INSERT INTO service_catalog (name, code, description, service_group, default_priority, department_id, category_id, handling_unit_id, requires_asset, default_ticket_type, is_active) VALUES (?, ?, ?, \'it\', \'normal\', ?, ?, ?, 1, \'incident\', 1)');
    $serviceInsert->execute(['Synthetic operations stress test', 'BENCH-OPS-IT', 'CI-only synthetic service; no operational data.', $department, $category, $itHandling]);
    $serviceId = (int) $pdo->lastInsertId();
    $assets = $pdo->query("SELECT id FROM assets WHERE asset_tag LIKE 'BENCH-PC-%' ORDER BY id LIMIT 500")->fetchAll(PDO::FETCH_COLUMN);
    $accounts = $pdo->query("SELECT username FROM users WHERE username LIKE 'loadtest-%' ORDER BY username LIMIT 250")->fetchAll(PDO::FETCH_COLUMN);
    if (count($assets) !== 500 || count($accounts) !== 250) {
        throw new RuntimeException('Synthetic account or asset count does not match the expected 250/500.');
    }
    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}

$out = [
    'synthetic_only' => true,
    'department_id' => (int) $department,
    'org_unit_id' => $unitId,
    'service_id' => $serviceId,
    'asset_ids' => array_map('intval', $assets),
    'account_count' => count($accounts),
];
$path = $root . '/benchmark-results/operation-seed.json';
@mkdir(dirname($path), 0755, true);
file_put_contents($path, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL, LOCK_EX);
printf("Prepared synthetic operations service %d and deputy unit %d.\n", $serviceId, $unitId);
