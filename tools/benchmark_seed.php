<?php
declare(strict_types=1);

/**
 * CI-only synthetic dataset bootstrap. Never point this script at a production database.
 * It creates config.php, 250 throwaway local primary-admin accounts, and representative rows.
 */
$root = dirname(__DIR__);
$database = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int) (getenv('DB_PORT') ?: 3306),
    'name' => getenv('DB_NAME') ?: 'ticket_benchmark',
    'user' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
];
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $database['host'], $database['port'], $database['name']);
$pdo = new PDO($dsn, $database['user'], $database['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$config = require $root . '/config.sample.php';
$config['app']['key'] = bin2hex(random_bytes(32));
$config['app']['timezone'] = 'UTC';
$config['database'] = $database;
$config['ldap']['enabled'] = false;
$config['food_ticket']['attendance_path'] = '';
$config['security']['inventory_token'] = bin2hex(random_bytes(32));
$configPath = $root . '/config.php';
file_put_contents($configPath, "<?php\ndeclare(strict_types=1);\nreturn " . var_export($config, true) . ";\n", LOCK_EX);
@chmod($configPath, 0600);

$password = getenv('BENCHMARK_PASSWORD');
if (!is_string($password) || strlen($password) < 16) {
    throw new RuntimeException('BENCHMARK_PASSWORD must be generated for this isolated run.');
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$pdo->beginTransaction();
try {
    $department = $pdo->prepare('INSERT INTO departments (name, code, is_active) VALUES (?, ?, 1)');
    $department->execute(['Benchmark Synthetic Unit', 'BENCH']);
    $departmentId = (int) $pdo->lastInsertId();

    $userInsert = $pdo->prepare('INSERT INTO users (username, password_hash, full_name, department, department_id, role, auth_source, is_active, is_primary_admin, must_change_password) VALUES (?, ?, ?, ?, ?, \'primary_admin\', \'local\', 1, 1, 0)');
    $userIds = [];
    for ($i = 1; $i <= 250; $i++) {
        $username = sprintf('loadtest-%03d', $i);
        $userInsert->execute([$username, $hash, sprintf('Load Test User %03d', $i), 'Benchmark Synthetic Unit', $departmentId]);
        $userIds[] = (int) $pdo->lastInsertId();
    }

    $pdo->exec("INSERT INTO handling_units (code, name, is_active) VALUES ('it', 'Benchmark IT', 1), ('support', 'Benchmark Support', 1)");
    $pdo->exec("INSERT INTO categories (name, code, service_group, is_active) VALUES ('Benchmark Request', 'BENCH-REQ', 'it', 1)");
    $categoryId = (int) $pdo->lastInsertId();

    $assetInsert = $pdo->prepare('INSERT INTO assets (asset_tag, hostname, computer_type, manufacturer, model, owner_user_id, department_id, operating_system, ip_address, memory_mb, source, lifecycle_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'manual\', \'assigned\')');
    $assetIds = [];
    for ($i = 1; $i <= 500; $i++) {
        $ownerId = $userIds[($i - 1) % count($userIds)];
        $assetInsert->execute([sprintf('BENCH-PC-%04d', $i), sprintf('BENCH-PC-%04d', $i), 'Desktop', 'Synthetic', 'Load Model', $ownerId, $departmentId, 'Windows 11', sprintf('10.250.%d.%d', intdiv($i, 250), ($i - 1) % 250 + 1), 16384]);
        $assetIds[] = (int) $pdo->lastInsertId();
    }

    $ticketInsert = $pdo->prepare('INSERT INTO tickets (ticket_no, subject, description, category_id, service_group, department_id, asset_id, priority, status, requester_id, created_at, updated_at) VALUES (?, ?, ?, ?, \'it\', ?, ?, ?, ?, ?, ?, ?)');
    $statuses = ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'resolved', 'closed'];
    $priorities = ['normal', 'urgent', 'critical'];
    $ticketIds = [];
    for ($i = 1; $i <= 2500; $i++) {
        $requesterId = $userIds[($i - 1) % count($userIds)];
        $assetId = $assetIds[($i - 1) % count($assetIds)];
        $createdAt = gmdate('Y-m-d H:i:s', time() - (($i % 180) * 86400));
        $ticketInsert->execute([sprintf('BENCH-%06d', $i), sprintf('Synthetic ticket %d', $i), 'Synthetic benchmark row; no real employee or operational data.', $categoryId, $departmentId, $assetId, $priorities[$i % count($priorities)], $statuses[$i % count($statuses)], $requesterId, $createdAt, $createdAt]);
        $ticketIds[] = (int) $pdo->lastInsertId();
    }

    $notificationInsert = $pdo->prepare('INSERT INTO notifications (user_id, ticket_id, notification_type, title, body, is_read) VALUES (?, ?, \'benchmark\', ?, ?, ?)');
    for ($i = 0; $i < count($userIds); $i++) {
        for ($j = 0; $j < 4; $j++) {
            $notificationInsert->execute([$userIds[$i], $ticketIds[($i * 4 + $j) % count($ticketIds)], 'Synthetic notification', 'CI-only load-test data.', $j % 2]);
        }
    }

    $pdo->exec("INSERT INTO food_catalog (food_name, category, active) VALUES ('Synthetic lunch', 'main', 1)");
    $foodId = (int) $pdo->lastInsertId();
    $monthStart = new DateTimeImmutable('first day of this month', new DateTimeZone('UTC'));
    $calendar = $pdo->prepare("INSERT INTO food_calendar (food_date, meal_type, order_status, note) VALUES (?, 'lunch', 'open', 'CI-only synthetic calendar')");
    $calendarItem = $pdo->prepare('INSERT INTO food_calendar_items (calendar_id, food_id, sort_order, active) VALUES (?, ?, 1, 1)');
    $days = (int) $monthStart->format('t');
    for ($day = 1; $day <= $days; $day++) {
        $calendar->execute([$monthStart->setDate((int) $monthStart->format('Y'), (int) $monthStart->format('n'), $day)->format('Y-m-d')]);
        $calendarItem->execute([(int) $pdo->lastInsertId(), $foodId]);
    }

    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}

printf("Seeded 250 synthetic accounts, 500 assets, 2500 tickets, 1000 notifications, and %d food calendar days.\n", (int) $monthStart->format('t'));
