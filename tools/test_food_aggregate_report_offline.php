<?php
declare(strict_types=1);

/** Offline regression for aggregate-report identity reconciliation. */
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
require_once dirname(__DIR__) . '/food-ticket.php';

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

$orders = [
    ['foodDate' => '2026-11-06', 'nat' => '۰۰۱۲۳', 'pc' => 'E1', 'first' => 'Ali', 'last' => 'One'],
    // Duplicate input rows for one date/identity count as one reportable person.
    ['foodDate' => '2026-11-06', 'nat' => '00123', 'pc' => 'E1', 'first' => 'Ali', 'last' => 'One'],
    // National code can be blank; personnel code is the supported fallback.
    ['foodDate' => '2026-11-06', 'nat' => '', 'pc' => 'E2', 'first' => 'Sara', 'last' => 'Two'],
    ['foodDate' => '2026-11-06', 'nat' => '', 'pc' => '', 'first' => 'No', 'last' => 'Identifier'],
    // Same national code on another date is another daily order identity.
    ['foodDate' => '2026-11-07', 'nat' => '00123', 'pc' => 'E1', 'first' => 'Ali', 'last' => 'One'],
];
$printed = [
    ['event_id' => 1, 'date' => '2026-11-06', 'nat' => '۰۰۱۲۳', 'pc' => '', 'event_type' => 'printed', 'meal_type' => 'lunch'],
    // A second receipt for the same person must not add a second matched identity.
    ['event_id' => 6, 'date' => '2026-11-06', 'nat' => '00123', 'pc' => 'E1', 'event_type' => 'auto_printed', 'meal_type' => 'lunch'],
    // A legacy print whose national code differs still matches by personnel code.
    ['event_id' => 2, 'date' => '2026-11-06', 'nat' => '999', 'pc' => 'E2', 'event_type' => 'print', 'meal_type' => 'lunch'],
    ['event_id' => 3, 'date' => '2026-11-06', 'nat' => '', 'pc' => 'E9', 'event_type' => 'auto_printed', 'meal_type' => 'lunch'],
    ['event_id' => 4, 'date' => '2026-11-06', 'nat' => '00123', 'pc' => 'E1', 'event_type' => 'auto_printed', 'meal_type' => 'dinner'],
    ['event_id' => 5, 'date' => '2026-11-06', 'nat' => '', 'pc' => 'G1', 'event_type' => 'guest', 'meal_type' => 'lunch'],
];
$metrics = food_ticket_aggregate_identity_sets($orders, $printed);
check($metrics['unique_lunch_identities'] === 3, 'daily identities dedupe duplicate rows but keep separate dates');
check($metrics['matched_printed_lunch_identities'] === 2, 'national-code and personnel-code matches are counted once each');
check($metrics['expected_absent_count'] === 1, 'expected absences = unique order identities minus matched lunch identities');
check($metrics['orders_without_national_code_count'] === 2, 'orders without national codes are diagnosed');
check($metrics['orders_without_identity_count'] === 1, 'orders without either national or personnel code are separately diagnosed');
check($metrics['unmatched_printed_event_count'] === 1, 'unmatched staff receipt is diagnosed, not subtracted from absences');
check($metrics['legacy_printed_event_count'] === 1, 'legacy print is tracked separately as a staff receipt');
check(count($metrics['matched']) === 2, 'matched identity set has no event-row overcount');
$firstIdentity = reset($metrics['identities']);
check(!array_key_exists('nat', $firstIdentity), 'national codes are not retained in reportable diagnostics rows');

printf("PASS: %d aggregate identity reconciliation checks\n", $checks);
