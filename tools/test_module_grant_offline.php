<?php
declare(strict_types=1);

/**
 * آزمون دسترسی سلسله‌مراتبی چاپ فیش (بدون دیتابیس، فقط CLI):
 * بازرسی که فقط «گروه‌های غذا» را دارد باید فقط مسیرهای گروه‌ها را ببیند و بقیهٔ بخش‌ها بسته باشند.
 *   php tools/test_module_grant_offline.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$root = dirname(__DIR__);
require_once $root . '/permissions.php';

// کش نقش‌ها را به‌جای دیتابیس پر می‌کنیم: بازرس فقط گروه‌های غذا (مشاهده + ویرایش).
$GLOBALS['__role_permission_codes'] = [
    'inspector' => ['food.groups', 'food.groups_edit'],
    'user' => ['food.groups'],
    'manager' => ['food.monitor'],
    'support_manager' => ['food.db', 'food.db_edit'],
    'agent' => ['food.db'],
];
$GLOBALS['__role_permission_sets'] = [];

$pass = 0;
$fail = 0;
function check(string $name, bool $ok): void
{
    global $pass, $fail;
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . "\n";
}

$user = ['id' => 1, 'role' => 'inspector', 'is_primary_admin' => 0];
check('مجوز گروه‌های غذا فقط همان بخش را باز می‌کند', user_can($user, 'food.groups') && !user_can($user, 'food.orders'));
check('دسترسی بخش‌های دیگر چاپ فیش بسته است', !user_can($user, 'food.printer') && !user_can($user, 'food.db') && !user_can($user, 'food.employees'));

// مسیرهای فیش را از خود فایل می‌خوانیم تا آزمون با تغییرات واقعی هم‌خوان بماند.
if (!function_exists('food_ticket_route_allowed')) {
    $src = (string) file_get_contents($root . '/food-ticket.php');

    // Extract PHP functions by token boundaries, not a newline/brace regex.
    // The route functions contain nested arrays and closures, so a shallow regex
    // can stop at the wrong brace or fail when formatting changes.
    $extractFunction = static function (string $source, string $name): ?string {
        $tokens = token_get_all($source);
        $offsets = [];
        $offset = 0;
        foreach ($tokens as $token) {
            $value = is_array($token) ? $token[1] : $token;
            $offsets[] = $offset;
            $offset += strlen($value);
        }
        foreach ($tokens as $index => $token) {
            if (!is_array($token) || $token[0] !== T_FUNCTION) {
                continue;
            }
            $cursor = $index + 1;
            while (isset($tokens[$cursor]) && is_array($tokens[$cursor])
                && in_array($tokens[$cursor][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                $cursor++;
            }
            if (($tokens[$cursor] ?? null) === '&') {
                $cursor++;
            }
            if (!isset($tokens[$cursor]) || !is_array($tokens[$cursor])
                || $tokens[$cursor][0] !== T_STRING || $tokens[$cursor][1] !== $name) {
                continue;
            }
            $start = $offsets[$index];
            $depth = 0;
            $opened = false;
            for ($end = $cursor + 1; $end < count($tokens); $end++) {
                $value = is_array($tokens[$end]) ? $tokens[$end][1] : $tokens[$end];
                if ($value === '{') {
                    $depth++;
                    $opened = true;
                } elseif ($value === '}' && $opened) {
                    $depth--;
                    if ($depth === 0) {
                        return substr($source, $start, $offsets[$end] + 1 - $start);
                    }
                }
            }
        }
        return null;
    };

    $routeEditPermission = $extractFunction($src, 'food_ticket_route_edit_permission');
    $routeAllowed = $extractFunction($src, 'food_ticket_route_allowed');
    if ($routeAllowed === null || $routeEditPermission === null) {
        fwrite(STDERR, "توابع مسیر در food-ticket.php پیدا نشدند\n");
        exit(2);
    }

    // تابع کمکی فقط برای مسیر «ادمین اصلی» است؛ این آزمون بازرس را بررسی می‌کند.
    if (!function_exists('food_ticket_is_primary_admin')) {
        function food_ticket_is_primary_admin(array $user): bool { return user_is_primary_admin($user); }
    }
    eval($routeEditPermission . "\n" . 'function food_ticket_route_edit_always(string $route): bool { return $route === "diag-source_row-delete"; }' . "\n" . $routeAllowed);
}

$_SERVER['REQUEST_METHOD'] = 'GET';
$readRoutes = ['food-groups', 'dashboard', 'monitoring', 'orders', 'employees', 'guest-cards', 'config', 'printers', 'template', 'health', 'process', 'absence-status'];
foreach ($readRoutes as $route) {
    $ok = food_ticket_route_allowed($user, $route);
    $expect = $route === 'food-groups' || $route === 'absence-status';
    check("خواندن «{$route}» " . ($expect ? 'باز' : 'بسته'), $ok === $expect);
}

$_SERVER['REQUEST_METHOD'] = 'POST';
check('ثبت غیبت گروه‌ها با food.groups_edit باز است', food_ticket_route_allowed($user, 'absence-save'));
check('ثبت در فهرست سفارش‌ها بسته است', !food_ticket_route_allowed($user, 'orders-import'));

$groupViewer = ['id' => 2, 'role' => 'user', 'is_primary_admin' => 0];
$monitorViewer = ['id' => 3, 'role' => 'manager', 'is_primary_admin' => 0];
$dbEditor = ['id' => 4, 'role' => 'support_manager', 'is_primary_admin' => 0];
$dbViewer = ['id' => 5, 'role' => 'agent', 'is_primary_admin' => 0];
check('لغو تردد با دسترسی صرفاً مشاهدهٔ گروه‌ها بسته است', !food_ticket_route_allowed($groupViewer, 'food-visit-cancel'));
check('لغو تردد فقط با food.groups_edit باز است', food_ticket_route_allowed($user, 'food-visit-cancel'));
check('پاک‌سازی SOURCE_TABLE با دسترسی صرفاً نظارت بسته است', !food_ticket_route_allowed($monitorViewer, 'cleanup-source_row'));
check('پاک‌سازی SOURCE_TABLE با food.db_edit باز است', food_ticket_route_allowed($dbEditor, 'cleanup-source_row'));
check('حذف تشخیصی ردیف منبع حتی با GET به food.db_edit نیاز دارد', !food_ticket_route_allowed($dbViewer, 'diag-source_row-delete'));

// پیش‌فرض نقش user: بخش‌های چاپ فیش و نظارت برداشته شده‌اند؛ سفارش غذای خود باقی است.
$userDefaults = permission_defaults()['user'] ?? [];
foreach (['food.dashboard', 'food.monitor', 'food.orders', 'food.reports', 'governance.manage', 'governance.view', 'cddvd.view', 'traffic.view'] as $removed) {
    check("پیش‌فرض user بدون {$removed}", !in_array($removed, $userDefaults, true));
}
check('پیش‌فرض user شامل سفارش غذای خود است', in_array('foodorder.self', $userDefaults, true));
check('پیش‌فرض user شامل ثبت درخواست است', in_array('ticket.create', $userDefaults, true));

// چاپ فیش فقط برای ادمین اصلی، مدیر پشتیبانی (محدود) و بازرس (داشبورد و گروه‌ها).
$allDefaults = permission_defaults();
$foodOf = static fn (array $codes): array => array_values(array_filter($codes, static fn ($c) => str_starts_with((string) $c, 'food.')));
foreach (array_keys(permission_roles()) as $roleName) {
    if (in_array($roleName, ['primary_admin', 'support_manager', 'inspector'], true)) {
        continue;
    }
    check("نقش {$roleName} هیچ کد چاپ فیش ندارد", $foodOf($allDefaults[$roleName] ?? []) === []);
}
$inspFood = $foodOf($allDefaults['inspector'] ?? []);
sort($inspFood);
check('بازرس فقط داشبورد و گروه‌های غذا را دارد', $inspFood === ['food.dashboard', 'food.groups']);
$supportFood = $foodOf($allDefaults['support_manager'] ?? []);
check('مدیر پشتیبانی برنامهٔ غذایی را ثبت می‌کند', in_array('food.menu_edit', $supportFood, true));
check('مدیر پشتیبانی به تنظیمات چاپ فیش دسترسی ندارد', !in_array('food.db', $supportFood, true) && !in_array('food.printer', $supportFood, true) && !in_array('food.design', $supportFood, true) && !in_array('food.health', $supportFood, true) && !in_array('food.guest', $supportFood, true));
check('کد food.orders از کاتالوگ حذف شده', !in_array('food.orders', permission_all_codes(), true) && !in_array('food.orders_edit', permission_all_codes(), true));

echo "\nنتیجه: $pass موفق، $fail ناموفق\n";
exit($fail === 0 ? 0 : 1);
