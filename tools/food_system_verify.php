<?php
declare(strict_types=1);

/** Read-only assertions against the disposable food-system stress dataset. */
$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';

$seedPath = $root . '/benchmark-results/food-system-seed.json';
$runPath = $root . '/benchmark-results/food-system-run.json';
$seed = json_decode((string) file_get_contents($seedPath), true, 512, JSON_THROW_ON_ERROR);
$run = json_decode((string) file_get_contents($runPath), true, 512, JSON_THROW_ON_ERROR);
if (empty($seed['synthetic_only']) || (int) ($seed['user_count'] ?? 0) !== 250 || (int) ($seed['manager_count'] ?? 0) !== 250) {
    throw new RuntimeException('Expected exactly 250 synthetic food users and 250 synthetic food managers.');
}
$pdo = db();
$employeeIds = array_map('intval', $seed['employee_ids']);
$chunks = array_chunk($employeeIds, 100);
$orders = [];
foreach ($chunks as $ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, employee_id, food_date, meal_type, status FROM food_orders
        WHERE employee_id IN ({$placeholders}) AND food_date IN (?, ?) ORDER BY food_date, employee_id");
    $stmt->execute(array_merge($ids, [$seed['dates']['self'], $seed['dates']['bulk']]));
    $orders = array_merge($orders, $stmt->fetchAll(PDO::FETCH_ASSOC));
}
$self = array_values(array_filter($orders, static fn (array $row): bool => (string) $row['food_date'] === $seed['dates']['self']));
$bulk = array_values(array_filter($orders, static fn (array $row): bool => (string) $row['food_date'] === $seed['dates']['bulk']));
$assertions = [
    'exactly_250_self_orders' => count($self) === 250,
    'exactly_250_bulk_orders' => count($bulk) === 250,
    'all_orders_cancelled_after_lifecycle' => count(array_filter($orders, static fn (array $row): bool => $row['status'] !== 'cancelled')) === 0,
    'self_orders_one_per_employee' => count(array_unique(array_column($self, 'employee_id'))) === 250,
    'bulk_orders_one_per_employee' => count(array_unique(array_column($bulk, 'employee_id'))) === 250,
];
$orderIds = array_map('intval', array_column($orders, 'id'));
$orderLogs = 0;
$logActions = [];
foreach (array_chunk($orderIds, 250) as $ids) {
    if ($ids === []) {
        continue;
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT action, COUNT(*) AS total FROM food_order_logs WHERE order_id IN ({$placeholders}) GROUP BY action");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $logActions[(string) $row['action']] = ($logActions[(string) $row['action']] ?? 0) + (int) $row['total'];
        $orderLogs += (int) $row['total'];
    }
}
$assertions['at_least_1000_order_audit_entries'] = $orderLogs >= 1000;

$marker = (string) $seed['marker'];
$catalog = $pdo->prepare('SELECT active FROM food_catalog WHERE food_name = ? LIMIT 1');
$catalog->execute(['Food Stress Synthetic Item ' . $marker]);
$catalogActive = $catalog->fetchColumn();
$assertions['catalog_item_crud_left_inactive_test_item'] = $catalogActive !== false && (int) $catalogActive === 0;
$menu = $pdo->prepare("SELECT id, note FROM food_calendar WHERE food_date = ? AND meal_type = 'lunch' LIMIT 1");
$menu->execute([$seed['dates']['menu']]);
$menuRow = $menu->fetch(PDO::FETCH_ASSOC);
$menuItems = 0;
if ($menuRow) {
    $itemCount = $pdo->prepare('SELECT COUNT(*) FROM food_calendar_items WHERE calendar_id = ? AND active = 1 AND food_id IN (?, ?)');
    $itemCount->execute([(int) $menuRow['id'], (int) $seed['food_ids'][0], (int) $seed['food_ids'][1]]);
    $menuItems = (int) $itemCount->fetchColumn();
}
$assertions['menu_day_saved_and_updated'] = $menuRow !== false
    && (string) $menuRow['note'] === 'Food Stress API day ' . $marker
    && $menuItems === 2;
$group = $pdo->prepare('SELECT COUNT(*) FROM food_ticket_groups WHERE title = ?');
$group->execute(['Food Stress Group ' . $marker]);
$assertions['synthetic_group_cleanup'] = (int) $group->fetchColumn() === 0;
$guest = $pdo->prepare('SELECT COUNT(*) FROM food_ticket_guest_cards WHERE card_uid = ?');
$guest->execute(['FS' . substr(hash('sha256', $marker), 0, 20)]);
$assertions['synthetic_guest_card_cleanup'] = (int) $guest->fetchColumn() === 0;

$summary = is_array($run['summary'] ?? null) ? $run['summary'] : [];
$assertions['self_write_and_readback_checks'] = (int) ($summary['self_order_lifecycles_verified'] ?? 0) === 250;
$assertions['bulk_order_and_cancel_checks'] = (int) ($summary['bulk_orders_created'] ?? 0) === 250 && (int) ($summary['bulk_orders_cancelled'] ?? 0) === 250;
$assertions['manager_read_checks'] = (int) ($summary['manager_read_responses_validated'] ?? 0) >= 250;
$assertions['manager_crud_checks'] = (int) ($summary['manager_crud_checks_passed'] ?? 0) >= 6;

$out = [
    'synthetic_only' => true,
    'food_orders' => ['self' => count($self), 'bulk' => count($bulk), 'active_after_cleanup' => count(array_filter($orders, static fn (array $row): bool => $row['status'] === 'active'))],
    'food_order_log_rows' => $orderLogs,
    'food_order_log_actions' => $logActions,
    'menu_day_active_items' => $menuItems,
    'assertions' => $assertions,
    'passed' => count(array_filter($assertions)),
    'failed' => count(array_filter($assertions, static fn (bool $ok): bool => !$ok)),
];
$reportPath = $root . '/benchmark-results/food-system-verification.json';
file_put_contents($reportPath, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL, LOCK_EX);
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
exit($out['failed'] === 0 ? 0 : 1);
