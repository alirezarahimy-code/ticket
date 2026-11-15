<?php
declare(strict_types=1);

/**
 * test_it_expert_routing_offline.php — «مسئول تیکت از فهرست تنظیمات خوانده می‌شود»
 *
 * تصمیم مالک (۲۰۲۶-۱۱-۱۶):
 *   تیکت IT        → فقط فهرست «کارشناس آی تی» در تنظیمات ← نقش‌ها
 *   تیکت پشتیبانی  → فقط فهرست «کارشناس پشتیبانی»
 *   ادمین اصلی     → از ارجاع خودکار حذف، فقط با ارجاع دستی
 *
 * این تست بدون دیتابیس و بدون وب اجرا می‌شود: تابع‌های لازم با شبیه‌سازی
 * جایگزین می‌شوند و منطق واقعی choose_handling_agent() سنجیده می‌شود.
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

/* ── شبیه‌سازی محیط ───────────────────────────────────────────── */

// فهرست تنظیمات — همان چیزی که دیتابیس در settings نگه می‌دارد.
$GLOBALS['fake_key_roles'] = [
    'supervisor' => [],
    'inspection' => [],
    'it_expert' => [],
    'support_expert' => [],
];
function key_role_user_ids(string $role): array
{
    return $GLOBALS['fake_key_roles'][$role] ?? [];
}
function user_is_key_role(array $user, string $role): bool
{
    return in_array((int) ($user['id'] ?? 0), key_role_user_ids($role), true);
}
function user_is_primary_admin(array $user): bool
{
    return ($user['role'] ?? '') === 'primary_admin'
        || (($user['role'] ?? '') === 'admin' && (int) ($user['is_primary_admin'] ?? 0) === 1);
}
function is_it_agent(array $user): bool
{
    return (int) ($user['is_active'] ?? 0) === 1 && (int) ($user['is_it_agent'] ?? 0) === 1;
}
function ticket_notification_user_can(array $user, string $permission): bool
{
    return true; // دسترسی در این تست جدا سنجیده می‌شود
}
function cfg($key, $default = null) { return $default; }
function is_global_ticket_role(?string $role): bool
{
    return in_array($role, ['supervisor', 'primary_admin', 'inspector'], true);
}
function is_staff_role(?string $role): bool
{
    return in_array($role, ['agent', 'manager', 'support_manager', 'supervisor', 'primary_admin', 'inspector'], true);
}


/* ── دیتابیس جعلی ─────────────────────────────────────────────── */

final class FakeStmt
{
    public function __construct(public string $sql) {}
    public array $params = [];
    public function execute(array $params = []): bool { $this->params = $params; return true; }
    public function fetchAll(?int $mode = null): array
    {
        // شبیه‌سازی: کاربرانِ فعالی که با شرط کوئری می‌خوانند.
        global $ALL_USERS;
        $rows = array_values(array_filter($ALL_USERS, fn ($u) => (int) $u['is_active'] === 1));

        // کوئری «فهرست صریح» — با IN (?, ?)
        if (str_contains($this->sql, 'u.id IN (')) {
            $ids = array_map('intval', $this->params);
            $rows = array_values(array_filter($ALL_USERS, fn ($u) => in_array((int) $u['id'], $ids, true)));
        } elseif (str_contains($this->sql, 'primary_admin')) {
            // پشتیبان IT ← ادمین اصلی
            $rows = array_values(array_filter($ALL_USERS, fn ($u) =>
                (int) $u['is_active'] === 1
                && ($u['role'] === 'primary_admin' || ($u['role'] === 'admin' && (int) ($u['is_primary_admin'] ?? 0) === 1))));
        } elseif (str_contains($this->sql, 'support_manager')) {
            $rows = array_values(array_filter($ALL_USERS, fn ($u) =>
                (int) $u['is_active'] === 1 && $u['role'] === 'support_manager'));
        } elseif (str_contains($this->sql, 'hu.code = ')) {
            // fallback: agent/manager با handling_unit
            $code = $this->params[0] ?? '';
            $rows = array_values(array_filter($ALL_USERS, fn ($u) =>
                (int) $u['is_active'] === 1
                && in_array($u['role'], ['agent', 'manager'], true)
                && ($u['handling_unit_code'] ?? '') === $code));
        }
        return $rows;
    }
}
final class FakeDb
{
    public function prepare(string $sql): FakeStmt { return new FakeStmt($sql); }
    public function query(string $sql): FakeStmt { return new FakeStmt($sql); }
}
function db(): FakeDb { static $d; return $d ??= new FakeDb(); }
function ticket_org_manager_rank_sql(): string { return ''; }
function ticket_org_auto_role_sql(): string { return ''; }

/* ── بارگذاری منطق واقعی ──────────────────────────────────────── */

require APP_ROOT . '/lib/auth-scope.php';

/**
 * choose_handling_agent() در modules/request-center/lib/functions.php تعریف شده است،
 * ولی آن فایل به ده‌ها تابع دیگر وابسته است. فقط همان یک تابع را از سورس
 * بیرون می‌کشیم تا رفتار واقعی‌اش سنجیده شود — نه یک بازنویسی موازی.
 */
function load_choose_handling_agent(): void
{
    if (function_exists('choose_handling_agent')) {
        return;
    }
    $src = file_get_contents(APP_ROOT . '/modules/request-center/lib/functions.php');
    $start = strpos($src, 'function key_role_designated_agent_ids(');
    $end = strpos($src, '/**', strpos($src, 'function choose_handling_agent('));
    if ($start === false) {
        throw new RuntimeException('key_role_designated_agent_ids در سورس پیدا نشد.');
    }
    if ($end === false) {
        throw new RuntimeException('پایان choose_handling_agent پیدا نشد.');
    }
    $block = substr($src, $start, $end - $start);
    eval($block);
}
load_choose_handling_agent();

/* ── سناریوها ─────────────────────────────────────────────────── */

echo "\n=== ۱) فهرست «کارشناس آی تی» مرجع است، نه نقش سیستمی ===\n";

$GLOBALS['ALL_USERS'] = [
    // ادمین‌های اصلی سامانه — نباید خودکار مسئول شوند
    ['id' => 1, 'role' => 'primary_admin', 'is_primary_admin' => 1, 'is_active' => 1,
     'full_name' => 'ادمین اصلی الف', 'department_id' => 0, 'is_it_agent' => 0, 'handling_unit_code' => 'it'],
    ['id' => 2, 'role' => 'admin', 'is_primary_admin' => 1, 'is_active' => 1,
     'full_name' => 'ادمین اصلی ب', 'department_id' => 0, 'is_it_agent' => 0, 'handling_unit_code' => 'it'],
    // کارشناس واقعی — با نقش سیستمی user، فقط از فهرست تنظیمات
    ['id' => 7, 'role' => 'user', 'is_primary_admin' => 0, 'is_active' => 1,
     'full_name' => 'کارشناس آی تی ۱', 'department_id' => 0, 'is_it_agent' => 1, 'handling_unit_code' => 'it'],
    ['id' => 8, 'role' => 'agent', 'is_primary_admin' => 0, 'is_active' => 1,
     'full_name' => 'کارشناس آی تی ۲', 'department_id' => 0, 'is_it_agent' => 1, 'handling_unit_code' => 'it'],
];

$GLOBALS['fake_key_roles']['it_expert'] = [7, 8];

$picked = choose_handling_agent('it', 0, 99);
ok($picked === 7, 'تیکت IT به کارشناس فهرست (id=7) می‌رود، نه ادمین اصلی', 'picked=' . var_export($picked, true));
ok($picked !== 1 && $picked !== 2, 'هیچ ادمین اصلی خودکار مسئول نشد');

echo "\n=== ۲) کارشناس با نقش سیستمی user پذیرفته می‌شود ===\n";

$candidate7 = $GLOBALS['ALL_USERS'][2];
$ticketIt = ['requester_id' => 99, 'service_group' => 'it', 'department_id' => 0, 'status' => 'new'];
ok(
    ticket_assignee_is_eligible($ticketIt, $candidate7, 'ticket_notification_user_can'),
    'کارشناس IT با role=user و بدون handling_unit واجد شرایط است'
);

$notExpert = $GLOBALS['ALL_USERS'][0];
ok(
    !ticket_assignee_is_eligible($ticketIt, $notExpert, 'ticket_notification_user_can')
    || user_is_primary_admin($notExpert),
    'کاربر خارج از فهرست (ادمین) از مسیر «کارشناس» واجد نیست'
);

echo "\n=== ۳) تیکت پشتیبانی از فهرست «کارشناس پشتیبانی» ===\n";

$GLOBALS['ALL_USERS'][] = ['id' => 11, 'role' => 'user', 'is_primary_admin' => 0, 'is_active' => 1,
    'full_name' => 'کارشناس پشتیبانی ۱', 'department_id' => 0, 'is_it_agent' => 0, 'handling_unit_code' => 'support'];
$GLOBALS['fake_key_roles']['support_expert'] = [11];

$pickedSupport = choose_handling_agent('support', 0, 99);
ok($pickedSupport === 11, 'تیکت پشتیبانی به کارشناس پشتیبانی (id=11) می‌رود', 'picked=' . var_export($pickedSupport, true));

$candidate11 = $GLOBALS['ALL_USERS'][array_key_last($GLOBALS['ALL_USERS'])];
$ticketSupport = ['requester_id' => 99, 'service_group' => 'support', 'department_id' => 0, 'status' => 'new'];
ok(
    ticket_assignee_is_eligible($ticketSupport, $candidate11, 'ticket_notification_user_can'),
    'کارشناس پشتیبانی واجد شرایط مسئولیت تیکت پشتیبانی است'
);

echo "\n=== ۴) فهرست گروهِ دیگر را نمی‌گیرد ===\n";

// کارشناس IT نباید مسئول تیکت پشتیبانی شود
ok(
    !ticket_assignee_is_eligible(['requester_id' => 99, 'service_group' => 'support', 'department_id' => 0], $candidate7, 'ticket_notification_user_can'),
    'کارشناس IT مسئول تیکت پشتیبانی نمی‌شود'
);
// و برعکس
ok(
    !ticket_assignee_is_eligible(['requester_id' => 99, 'service_group' => 'it', 'department_id' => 0], $candidate11, 'ticket_notification_user_can'),
    'کارشناس پشتیبانی مسئول تیکت IT نمی‌شود'
);

echo "\n=== ۵) ثبت‌کنندهٔ خودش مسئول نمی‌شود ===\n";

$selfTicket = ['requester_id' => 7, 'service_group' => 'it', 'department_id' => 0];
ok(
    !ticket_assignee_is_eligible($selfTicket, $candidate7, 'ticket_notification_user_can'),
    'کارشناس نمی‌تواند مسئول تیکت خودش شود'
);

echo "\n=== ۶) فهرست خالی → پشتیبان (سامانه بی‌صاحب نمی‌ماند) ===\n";

$GLOBALS['fake_key_roles']['it_expert'] = [];
$pickedFallback = choose_handling_agent('it', 0, 99);
ok($pickedFallback === 1 || $pickedFallback === 2, 'با فهرست خالی، ادمین اصلی پشتیبان می‌شود', 'picked=' . var_export($pickedFallback, true));

echo "\n=== ۷) کارشناس غیرفعال رد می‌شود ===\n";

$GLOBALS['ALL_USERS'][2]['is_active'] = 0; // id=7 غیرفعال
$GLOBALS['fake_key_roles']['it_expert'] = [7, 8];
$pickedInactive = choose_handling_agent('it', 0, 99);
ok($pickedInactive === 8, 'کارشناس غیرفعال (id=7) رد و نفر بعدی (id=8) انتخاب می‌شود', 'picked=' . var_export($pickedInactive, true));
$GLOBALS['ALL_USERS'][2]['is_active'] = 1;

echo "\n=== ۸) کاربر حذف‌شده از فهرست دیگر مسئول نمی‌شود ===\n";

$GLOBALS['fake_key_roles']['it_expert'] = [8]; // 7 حذف شد
$candidate7b = ['id' => 7, 'role' => 'user', 'is_primary_admin' => 0, 'is_active' => 1, 'is_it_agent' => 1];
ok(
    !ticket_assignee_is_eligible($ticketIt, $candidate7b, 'ticket_notification_user_can'),
    'کاربری که از فهرست «کارشناس IT» حذف شده، دیگر واجد مسئولیت نیست'
);

echo "\n" . str_repeat('═', 60) . "\n";
echo "PASS=$pass | FAIL=$fail\n";
exit($fail === 0 ? 0 : 1);
