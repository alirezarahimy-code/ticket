<?php
declare(strict_types=1);

/** Offline authorization tests for the shared IT asset scope predicate. */
const APP_ROOT = __DIR__ . '/..';

function user_is_primary_admin(array $user): bool
{
    return !empty($user['primary_admin']);
}
function is_global_ticket_role(?string $role): bool
{
    return in_array((string) $role, ['supervisor', 'inspector', 'primary_admin'], true);
}
function user_can(array $user, string $permission): bool
{
    return $permission === 'assets.own_unit' && !empty($user['own_unit']);
}

require APP_ROOT . '/lib/auth-scope.php';

$cases = [
    'IT agent with own-unit limiter' => [
        ['role' => 'agent', 'handling_unit_code' => 'it', 'department_id' => 12, 'own_unit' => true],
        ['WHERE a.department_id = ?', [12]],
    ],
    'IT manager with own-unit limiter' => [
        ['role' => 'manager', 'handling_unit_code' => 'it', 'department_id' => 27, 'own_unit' => true],
        ['WHERE a.department_id = ?', [27]],
    ],
    'IT user without own-unit limiter' => [
        ['role' => 'agent', 'handling_unit_code' => 'it', 'department_id' => 12, 'own_unit' => false],
        ['', []],
    ],
    'support user is denied IT inventory' => [
        ['role' => 'support_manager', 'handling_unit_code' => 'support', 'department_id' => 12, 'own_unit' => false],
        ['WHERE 1 = 0', []],
    ],
    'missing department fails closed when own-unit is required' => [
        ['role' => 'agent', 'handling_unit_code' => 'it', 'department_id' => 0, 'own_unit' => true],
        ['WHERE 1 = 0', []],
    ],
    'supervisor has global scope' => [
        ['role' => 'supervisor', 'handling_unit_code' => 'support', 'department_id' => 0],
        ['', []],
    ],
    'primary admin flag has global scope' => [
        ['role' => 'admin', 'primary_admin' => true, 'handling_unit_code' => 'support'],
        ['', []],
    ],
];

$failed = 0;
foreach ($cases as $label => [$user, $expected]) {
    $actual = asset_scope($user);
    $ok = $actual === $expected;
    echo ($ok ? 'PASS' : 'FAIL') . ': ' . $label . PHP_EOL;
    if (!$ok) {
        echo '  expected=' . var_export($expected, true) . PHP_EOL;
        echo '  actual=' . var_export($actual, true) . PHP_EOL;
        $failed++;
    }
}

try {
    asset_scope(['role' => 'agent', 'handling_unit_code' => 'it'], 'a; DROP TABLE assets');
    echo "FAIL: unsafe asset SQL alias accepted.\n";
    $failed++;
} catch (InvalidArgumentException) {
    echo "PASS: unsafe asset SQL alias rejected.\n";
}

echo $failed === 0 ? "All asset scope tests passed.\n" : "$failed asset scope test(s) failed.\n";
exit($failed === 0 ? 0 : 1);
