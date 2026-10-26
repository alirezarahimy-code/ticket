<?php
declare(strict_types=1);

/** Disposable, food-only CI seed. Inserts only synthetic users and food-module rows. */
$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';
require_once $root . '/food-ticket.php';

$pdo = db();
$stamp = gmdate('ymdHis') . bin2hex(random_bytes(3));
$today = new DateTimeImmutable(food_order_today(), new DateTimeZone('UTC'));
$dates = [
    'self' => $today->modify('+2 days')->format('Y-m-d'),
    'bulk' => $today->modify('+3 days')->format('Y-m-d'),
    'menu' => $today->modify('+4 days')->format('Y-m-d'),
];

$pdo->beginTransaction();
try {
    $insertUser = $pdo->prepare("INSERT INTO users
        (username, password_hash, full_name, first_name, last_name, employee_number, national_code,
         is_primary_admin, role, auth_source, is_active, must_change_password)
        VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, 'local', 1, 0)");
    $employeeIds = [];
    for ($i = 1; $i <= 250; $i++) {
        $username = sprintf('foodstress-%s-u%03d', $stamp, $i);
        $first = 'Food';
        $last = sprintf('Synthetic %03d', $i);
        $employee = sprintf('FS%sU%03d', $stamp, $i);
        $national = sprintf('9%09d', $i);
        $insertUser->execute([$username, $first . ' ' . $last, $first, $last, $employee, $national, 0, 'user']);
        $employeeIds[] = (int) $pdo->lastInsertId();
    }

    $adminIds = [];
    for ($i = 1; $i <= 250; $i++) {
        $username = sprintf('foodstress-%s-a%03d', $stamp, $i);
        $first = 'Food Admin';
        $last = sprintf('Synthetic %03d', $i);
        $employee = sprintf('FS%sA%03d', $stamp, $i);
        $national = sprintf('8%09d', $i);
        $insertUser->execute([$username, $first . ' ' . $last, $first, $last, $employee, $national, 1, 'primary_admin']);
        $adminIds[] = (int) $pdo->lastInsertId();
    }

    $insertFood = $pdo->prepare('INSERT INTO food_catalog (food_name, category, active, created_by) VALUES (?, ?, 1, ?)');
    $foods = [];
    foreach (['Synthetic Lunch One', 'Synthetic Lunch Two', 'Synthetic Dinner'] as $name) {
        $insertFood->execute([$name . ' ' . $stamp, 'سایر', $adminIds[0]]);
        $foods[] = (int) $pdo->lastInsertId();
    }

    $insertDay = $pdo->prepare("INSERT INTO food_calendar (food_date, meal_type, order_status, note, created_by) VALUES (?, ?, 'open', ?, ?)");
    $insertItem = $pdo->prepare('INSERT INTO food_calendar_items (calendar_id, food_id, sort_order, active) VALUES (?, ?, ?, 1)');
    $calendarItems = [];
    for ($offset = 1; $offset <= 40; $offset++) {
        $date = $today->modify('+' . $offset . ' days')->format('Y-m-d');
        foreach (['lunch', 'dinner'] as $meal) {
            $insertDay->execute([$date, $meal, 'Food-only synthetic CI fixture', $adminIds[0]]);
            $calendarId = (int) $pdo->lastInsertId();
            $foodList = $meal === 'lunch' ? [$foods[0], $foods[1]] : [$foods[2]];
            foreach ($foodList as $sort => $foodId) {
                $insertItem->execute([$calendarId, $foodId, $sort + 1]);
                $itemId = (int) $pdo->lastInsertId();
                if ($date === $dates['self'] && $meal === 'lunch' && $sort === 0) {
                    $calendarItems['self'] = $itemId;
                }
                if ($date === $dates['bulk'] && $meal === 'lunch' && $sort === 1) {
                    $calendarItems['bulk'] = $itemId;
                }
            }
        }
    }
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $e;
}

if (count($employeeIds) !== 250 || count($adminIds) !== 250 || empty($calendarItems['self']) || empty($calendarItems['bulk'])) {
    throw new RuntimeException('Food-only synthetic fixture is incomplete.');
}

// Prebuild application sessions instead of using the general login route; the latter performs
// unrelated client-inventory collection. Each session still resolves through current_user().
$sessionDir = $root . '/storage/sessions';
if (!is_dir($sessionDir) && !mkdir($sessionDir, 0700, true) && !is_dir($sessionDir)) {
    throw new RuntimeException('Cannot create isolated PHP session directory.');
}
if (!is_writable($sessionDir)) {
    throw new RuntimeException('PHP session directory is not writable.');
}
ini_set('session.save_path', $sessionDir);
ini_set('session.use_strict_mode', '0');
ini_set('session.use_cookies', '0');
$createSession = static function (int $userId): array {
    $sessionId = bin2hex(random_bytes(13));
    $csrf = bin2hex(random_bytes(32));
    session_id($sessionId);
    if (!session_start()) {
        throw new RuntimeException('Could not create a synthetic food API session.');
    }
    $_SESSION = ['user_id' => $userId, 'csrf' => $csrf];
    session_write_close();
    return ['user_id' => $userId, 'session_id' => $sessionId, 'csrf' => $csrf];
};
$employeeSessions = array_map($createSession, $employeeIds);
$managerSessions = array_map($createSession, $adminIds);

$jalali = food_order_jalali_label($dates['self']);
$out = [
    'synthetic_only' => true,
    'marker' => 'FOODSTRESS_' . $stamp,
    'created_at_utc' => gmdate('c'),
    'employee_ids' => $employeeIds,
    'admin_ids' => $adminIds,
    'employee_sessions' => $employeeSessions,
    'manager_sessions' => $managerSessions,
    'food_ids' => $foods,
    'dates' => $dates,
    'calendar_item_ids' => $calendarItems,
    'jalali_month' => substr($jalali, 0, 7),
    'user_count' => count($employeeIds),
    'manager_count' => count($adminIds),
];
$outPath = $root . '/benchmark-results/food-system-seed.json';
if (!is_dir(dirname($outPath)) && !mkdir(dirname($outPath), 0755, true) && !is_dir(dirname($outPath))) {
    throw new RuntimeException('Cannot create benchmark-results directory.');
}
file_put_contents($outPath, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL, LOCK_EX);
printf("Seeded %d synthetic food-order users, %d synthetic food admins, three food items, and open meal calendars.\n", count($employeeIds), count($adminIds));
