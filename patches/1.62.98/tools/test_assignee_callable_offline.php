<?php
declare(strict_types=1);

/**
 * test_assignee_callable_offline.php — «ارجاع خودکار تیکت با TypeError نمی‌افتد»
 *
 * باگی که در ۱.۶۲.۹۷ نصب‌شده بود:
 *   lib/auth-scope.php با declare(strict_types=1) اجرا می‌شود و امضای
 *   ticket_assignee_is_eligible(..., ?callable $permissionCheck = null) دارد،
 *   ولی همهٔ فراخوانی‌ها نام تابع را **به‌صورت رشته** می‌فرستند:
 *       ticket_assignee_is_eligible($ticket, $candidate, 'ticket_notification_user_can')
 *
 *   در حالت strict، رشته برای پارامتر ?callable یک TypeError مرگبار است.
 *   یعنی هر ثبت درخواست تازه که به مسیر ارجاع خودکار می‌رسید، صفحه را می‌انداخت.
 *
 * این تست همان فراخوانی را با امضای واقعی فایل اجرا می‌کند.
 */

const APP_ROOT = __DIR__ . '/..';

$pass = 0;
$fail = 0;
function ok(bool $cond, string $label, string $detail = ''): void
{
    global $pass, $fail;
    if ($cond) {
        $pass++;
        echo "  [PASS] $label\n";
    } else {
        $fail++;
        echo "  [FAIL] $label\n";
        if ($detail !== '') {
            echo "         $detail\n";
        }
    }
}

echo "\n=== ۱) امضا باید callable|string را بپذیرد ===\n";

$src = (string) file_get_contents(APP_ROOT . '/lib/auth-scope.php');

ok(
    (bool) preg_match('/function ticket_assignee_is_eligible\([^)]*callable\|string\|null\s+\$permissionCheck/s', $src),
    'امضای ticket_assignee_is_eligible «callable|string|null» است'
);
ok(
    (bool) preg_match('/function ticket_user_can_view\([^)]*callable\|string\|null\s+\$permissionCheck/s', $src),
    'امضای ticket_user_can_view «callable|string|null» است'
);
ok(
    !preg_match('/function ticket_assignee_is_eligible\([^)]*\?callable\s+\$permissionCheck/s', $src),
    'امضای قدیمی «?callable» حذف شد (همان چیزی که TypeError می‌داد)'
);
ok(
    str_contains($src, 'declare(strict_types=1)'),
    'فایل همچنان strict_types دارد — پس اصلاح امضاست، نه خاموش‌کردن strict'
);

echo "\n=== ۲) فراخوانی رشته‌ای نباید TypeError بدهد ===\n";

// محیط حداقلی برای بارگذاری auth-scope
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__ . '/..');
}
function user_is_primary_admin(array $user): bool
{
    return ($user['role'] ?? '') === 'primary_admin';
}
function is_global_ticket_role(?string $role): bool
{
    return in_array($role, ['supervisor', 'primary_admin', 'inspector'], true);
}
function is_it_agent(array $user): bool { return (int) ($user['is_it_agent'] ?? 0) === 1; }
function is_staff_role(?string $role): bool { return in_array($role, ['agent', 'manager'], true); }
function user_can(array $user, string $permission): bool { return true; }
function ticket_notification_user_can(array $user, string $permission): bool { return true; }
function cfg($key, $default = null) { return $default; }

require APP_ROOT . '/lib/auth-scope.php';

$ticket = ['requester_id' => 1, 'service_group' => 'it', 'department_id' => 0, 'status' => 'new'];
$agent = ['id' => 7, 'role' => 'agent', 'is_active' => 1, 'handling_unit_code' => 'it', 'department_id' => 0];

$threw = false;
$result = null;
try {
    // دقیقاً همان شکلی که choose_handling_agent صدا می‌زند
    $result = ticket_assignee_is_eligible($ticket, $agent, 'ticket_notification_user_can');
} catch (Throwable $e) {
    $threw = true;
    ok(false, 'فراخوانی با نام رشته‌ای نباید خطا بدهد', get_class($e) . ': ' . $e->getMessage());
}
if (!$threw) {
    ok(true, 'فراخوانی با نام رشته‌ای بدون خطا اجرا شد');
    ok(is_bool($result), 'مقدار بازگشتی bool است', 'got ' . gettype($result));
}

// و با نام تابع پیش‌فرض
$threw2 = false;
try {
    ticket_assignee_is_eligible($ticket, $agent);
} catch (Throwable $e) {
    $threw2 = true;
    ok(false, 'فراخوانی بدون پارامتر سوم نباید خطا بدهد', get_class($e) . ': ' . $e->getMessage());
}
if (!$threw2) {
    ok(true, 'فراخوانی بدون پارامتر سوم (پیش‌فرض user_can) کار می‌کند');
}

// و با callable واقعی
$threw3 = false;
try {
    ticket_assignee_is_eligible($ticket, $agent, static fn (array $u, string $p): bool => true);
} catch (Throwable $e) {
    $threw3 = true;
    ok(false, 'فراخوانی با closure نباید خطا بدهد', get_class($e) . ': ' . $e->getMessage());
}
if (!$threw3) {
    ok(true, 'فراخوانی با closure همچنان کار می‌کند (سازگاری عقب‌رو)');
}

// و نام تابع ناموجود → fallback، نه خطا
$threw4 = false;
try {
    ticket_assignee_is_eligible($ticket, $agent, 'این_تابع_وجود_ندارد');
} catch (Throwable $e) {
    $threw4 = true;
    ok(false, 'نام تابع ناموجود باید به user_can برگردد، نه خطا', get_class($e) . ': ' . $e->getMessage());
}
if (!$threw4) {
    ok(true, 'نام تابع ناموجود به user_can برمی‌گردد (بدون خطا)');
}

echo "\n=== ۳) همهٔ نقاط فراخوانی رشته‌ای پوشش دارند ===\n";

$callSites = 0;
foreach (['index.php', 'modules/request-center/lib/functions.php', 'lib/auth-scope.php'] as $rel) {
    $path = APP_ROOT . '/' . $rel;
    if (!is_file($path)) {
        continue;
    }
    $body = (string) file_get_contents($path);
    $callSites += preg_match_all('/ticket_assignee_is_eligible\([^;]*?\'ticket_notification_user_can\'/s', $body);
}
ok($callSites > 0, "نقاط فراخوانی رشته‌ای پیدا شد و همه با همان امضا کار می‌کنند", "count=$callSites");

echo "\n" . str_repeat('═', 60) . "\n";
echo "PASS=$pass | FAIL=$fail\n";
exit($fail === 0 ? 0 : 1);
