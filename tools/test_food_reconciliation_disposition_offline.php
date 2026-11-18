<?php
declare(strict_types=1);

/** Offline regression test for exact leftover disposition and stale UI snapshots. */
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!function_exists('food_order_normalize_digits')) {
    function food_order_normalize_digits(mixed $value): string
    {
        return strtr((string) ($value ?? ''), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
require_once dirname(__DIR__) . '/food-reconciliation.php';

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    $checks++;
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function rejects(callable $operation, string $fragment): bool
{
    try {
        $operation();
    } catch (RuntimeException $error) {
        return str_contains($error->getMessage(), $fragment);
    }
    return false;
}

$exact = food_reconciliation_disposition_parse([
    'leftover_staff_quantity' => '۹',
    'leftover_waste_quantity' => '۳',
], 12);
check($exact['leftover_staff_quantity'] === 9 && $exact['leftover_waste_quantity'] === 3,
    '9 staff + 3 waste is accepted for 12 remaining');

check(rejects(static fn () => food_reconciliation_disposition_parse([
    'leftover_staff_quantity' => 9,
    'leftover_waste_quantity' => 2,
], 12), 'کمتر'),
    'a total below the server remaining quantity is rejected');
check(rejects(static fn () => food_reconciliation_disposition_parse([
    'leftover_staff_quantity' => 9,
    'leftover_waste_quantity' => 4,
], 12), 'بیشتر'),
    'the reported 9 + 4 = 13 case is rejected when 12 remain');
check(rejects(static fn () => food_reconciliation_disposition_parse([], 12), 'حداقل یک گزینه'),
    'an empty positive-remaining disposition is rejected');

$none = food_reconciliation_disposition_parse([], 0);
check($none['leftover_staff_quantity'] === null && $none['leftover_waste_quantity'] === null,
    'zero remaining needs no disposition');
check(rejects(static fn () => food_reconciliation_disposition_parse([
    'leftover_staff_quantity' => 1,
], 0), 'باید صفر باشد'),
    'nonzero disposition is rejected when nothing remains');

food_reconciliation_expected_remaining_assert(['leftover_expected_remaining' => '۱۲'], 12);
check(rejects(static fn () => food_reconciliation_expected_remaining_assert([
    'leftover_expected_remaining' => 12,
], 13), 'گزارش را تازه‌سازی'),
    'a stale UI value of 12 is rejected when the server now calculates 13');
food_reconciliation_expected_remaining_assert([], 13);
check(true, 'legacy clients without a snapshot still use server-side exact-sum validation');

check(food_reconciliation_received_missing([
    'received_quantity' => null, 'active_demand' => 0, 'served_total' => 3, 'id' => null,
]), 'ticket-only distribution with no reconciliation is an unrecorded receipt');
check(food_reconciliation_received_missing([
    'received_quantity' => null, 'active_demand' => 2, 'served_total' => 0, 'id' => null,
]), 'active demand without receipt is an unrecorded receipt');
check(food_reconciliation_received_missing([
    'received_quantity' => null, 'active_demand' => 0, 'served_total' => 0, 'id' => 7,
]), 'an existing order row without a received quantity is incomplete');
check(!food_reconciliation_received_missing([
    'received_quantity' => 0, 'active_demand' => 2, 'served_total' => 0, 'id' => 7,
]), 'an explicitly recorded zero receipt is complete data');
check(!food_reconciliation_received_missing([
    'received_quantity' => null, 'active_demand' => 0, 'served_total' => 0, 'id' => null,
]), 'an unused day with no reconciliation is not a missing receipt row');

printf("PASS: %d reconciliation disposition checks\n", $checks);
