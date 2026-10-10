<?php
declare(strict_types=1);

// آزمون واقعی (نیاز به دیتابیس تست و ITSM_CONFIG_PATH): درخواست غذای مهمان، لغو، سقف حالت «درخواست مهمان».
// همهٔ داده‌های آزمون در پایان پاک می‌شوند.

$root = dirname(__DIR__);
if (!getenv('ITSM_CONFIG_PATH')) {
    fwrite(STDERR, "ITSM_CONFIG_PATH تنظیم نشده است.\n");
    exit(2);
}
require_once $root . '/bootstrap.php';
require_once $root . '/organization.php';
require_once $root . '/food-ticket.php'; // شامل food-order.php و food-ticket-engine.php

$pass = 0;
$fail = 0;
function check(string $label, bool $ok): void
{
    global $pass, $fail;
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[ok] ' : '[FAIL] ') . $label . "\n";
}
function expect_error(callable $fn): string
{
    try {
        $fn();
    } catch (Throwable $e) {
        return $e->getMessage();
    }
    return '';
}

$manager = ['id' => 999001, 'role' => 'manager', 'first_name' => 'تست', 'last_name' => 'مدیر'];
$inspector = ['id' => 999003, 'role' => 'inspector'];
$pdo0 = db();
$pdo0->prepare("INSERT INTO org_units (name, code, unit_type, is_active) VALUES ('معاونت آزمایشی گزارش', 'TST-GR-DEP', 'deputy', 1)")->execute();
$depId = (int) $pdo0->lastInsertId();
$depId2 = 0;
$pdo0->prepare("INSERT INTO org_units (name, code, unit_type, is_active) VALUES ('معاونت آزمایشی دوم', 'TST-GR-DEP2', 'deputy', 1)")->execute();
$depId2 = (int) $pdo0->lastInsertId();
$agent = ['id' => 999002, 'role' => 'agent'];
$createdIds = [];
$pdo0 = db();
$pdo0->exec("DELETE FROM food_guest_requests WHERE organization IN ('شرکت آزمایشی','سازمان دوم')");
$tomorrow = (new DateTimeImmutable(food_order_today() . ' +3 day'))->format('Y-m-d');

check('نقش مدیر مجاز است', food_guest_request_allowed($manager));
check('نقش کارشناس مجاز نیست', !food_guest_request_allowed($agent));
check('بازرسی جزو مدیران است و مجاز است', food_guest_request_allowed($inspector));

$base = ['request_date' => $tomorrow, 'organization' => 'شرکت آزمایشی', 'guest_count' => '3', 'food_id' => 0, 'requester_name' => 'علی تست', 'deputy_unit_id' => $depId];
$r1 = food_guest_request_create($manager, $base);
$createdIds[] = $r1['id'];
check('ثبت درخواست روز آینده', $r1['id'] > 0);

$r2 = food_guest_request_create($manager, ['request_date' => $tomorrow, 'organization' => 'سازمان دوم', 'guest_count' => '5', 'requester_name' => 'رضا تست', 'deputy_unit_id' => $depId2]);
$createdIds[] = $r2['id'];
check('جمع درخواست‌های فعال همان روز = 8', food_guest_requested_total($tomorrow) === 8);

check('امروز قابل ثبت نیست', str_contains(expect_error(fn () => food_guest_request_create($manager, array_merge($base, ['request_date' => food_order_today()]))), 'آینده'));
check('بدون معاونت ثبت نمی‌شود', str_contains(expect_error(fn () => food_guest_request_create($manager, array_merge($base, ['deputy_unit_id' => 0]))), 'معاونت'));
check('معاونت غیرفعال/نامعتبر ثبت نمی‌شود', str_contains(expect_error(fn () => food_guest_request_create($manager, array_merge($base, ['deputy_unit_id' => 987654]))), 'معاونت'));
check('تعداد صفر رد می‌شود', str_contains(expect_error(fn () => food_guest_request_create($manager, array_merge($base, ['guest_count' => '0']))), 'تعداد'));
check('تعداد بیشتر از ۵۰۰ رد می‌شود', str_contains(expect_error(fn () => food_guest_request_create($manager, array_merge($base, ['guest_count' => '501']))), 'تعداد'));
check('سازمان خالی رد می‌شود', str_contains(expect_error(fn () => food_guest_request_create($manager, array_merge($base, ['organization' => ' ']))), 'سازمان'));

// سقف حالت درخواست مهمان: سقف = جمع درخواست‌ها
$cap = food_guest_requested_total($tomorrow) ?? 0;
check('سقف درخواست‌شده ۸ است و هشتمین فیش مجاز، نهمی بسته', food_ticket_guest_cap_reached(7, $cap, 0, 0, 0) === false && food_ticket_guest_cap_reached(8, $cap, 0, 0, 0) === true);
check('روز بدون درخواست: سقف صفر یعنی بسته', food_ticket_guest_cap_reached(0, food_guest_requested_total('2999-01-01') ?? 0, 20, 0, 0) === true);

// گزارش: فیلتر معاونت
$all = food_ticket_api_guest_requests_report($tomorrow, $tomorrow, 'all');
$allIds = array_map(fn ($i) => $i['id'], $all['items']); sort($allIds);
check('گزارش «همه» هر دو درخواست فعال را دارد', $allIds === [min($r1['id'], $r2['id']), max($r1['id'], $r2['id'])]);
$onlyFirst = food_ticket_api_guest_requests_report($tomorrow, $tomorrow, (string) $depId);
$ids = array_map(fn ($i) => $i['id'], $onlyFirst['items']);
check('فیلتر معاونت اول فقط درخواست همان معاونت را برمی‌گرداند', in_array($r1['id'], $ids, true) && !in_array($r2['id'], $ids, true));
$onlySecond = food_ticket_api_guest_requests_report($tomorrow, $tomorrow, (string) $depId2);
check('فیلتر معاونت دوم فقط درخواست خودش را دارد', array_map(fn ($i) => $i['id'], $onlySecond['items']) === [$r2['id']]);
check('فهرست معاونت‌ها در گزارش برگردانده می‌شود', in_array($depId, array_column($all['deputies'], 'id'), true));
check('نام معاونت در ردیف گزارش است', $onlyFirst['items'][0]['deputy_name'] === 'معاونت آزمایشی گزارش');
check('بازهٔ خارج از تاریخ، نتیجه خالی', food_ticket_api_guest_requests_report('2990-01-01', '2990-01-02', 'all')['items'] === []);
food_guest_request_cancel($manager, $r2['id']);
check('درخواست لغوشده در گزارش نیست', array_map(fn ($i) => $i['id'], food_ticket_api_guest_requests_report($tomorrow, $tomorrow, 'all')['items']) === [$r1['id']]);
$createdIds = array_values(array_diff($createdIds, [$r2['id']]));
check('لغو درخواست، جمع را به ۳ می‌رساند', food_guest_requested_total($tomorrow) === 3);
check('لغو دوباره رد می‌شود', str_contains(expect_error(fn () => food_guest_request_cancel($manager, $r2['id'])), 'قبلاً'));
check('درخواست لغوشده در فهرست با وضعیت لغو دیده می‌شود', (function () use ($r2) {
    foreach (food_guest_request_list()['items'] as $it) {
        if ($it['id'] === $r2['id']) return $it['status'] === 'cancelled' && $it['editable'] === false;
    }
    return false;
})());
check('فهرست فیلد نام ثبت‌کننده را دارد', (function () use ($r1) {
    foreach (food_guest_request_list()['items'] as $it) {
        if ($it['id'] === $r1['id']) return array_key_exists('created_by_name', $it) && is_string($it['created_by_name']);
    }
    return false;
})());

// پاک‌سازی
$pdo = db();
foreach ($createdIds as $id) {
    $pdo->prepare('DELETE FROM food_guest_requests WHERE id = ?')->execute([$id]);
}
$pdo->exec("DELETE FROM food_guest_requests WHERE organization IN ('شرکت آزمایشی','سازمان دوم')");
$pdo->prepare('DELETE FROM org_units WHERE code IN (?, ?)')->execute(['TST-GR-DEP', 'TST-GR-DEP2']);
check('داده‌های آزمون پاک شد', (int) $pdo->query("SELECT COUNT(*) FROM food_guest_requests WHERE organization IN ('شرکت آزمایشی','سازمان دوم')")->fetchColumn() === 0);

echo "\nنتیجه: {$pass} موفق، {$fail} ناموفق\n";
exit($fail > 0 ? 1 : 0);
