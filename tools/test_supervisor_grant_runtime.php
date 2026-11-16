<?php
declare(strict_types=1);

/**
 * test_supervisor_grant_runtime.php — اثبات رفتار واقعی «سوپروایز اعطایی».
 *
 * چرا این تست جدا از تست‌های متنی است: تست متنی می‌تواند `array_diff` را در کد
 * ببیند، ولی نمی‌تواند ثابت کند `user_can()` واقعاً برای کاربری با نقش agent
 * «درست» جواب می‌دهد. این تست با پایگاه‌دادهٔ واقعی و همان bootstrap سامانه
 * اجرا می‌شود.
 *
 * اجرا:
 *   ITSM_CONFIG_PATH=/path/to/config.php php tools/test_supervisor_grant_runtime.php
 *
 * پیش‌نیاز: config.php معتبر با پایگاه‌دادهٔ زنده.
 * اگر bootstrap در دسترس نباشد، با کد ۷۷ رد می‌شود (skip)، نه FAIL.
 */

$root = dirname(__DIR__);
$configPath = (string) (getenv('ITSM_CONFIG_PATH') ?: $root . '/config.php');
if (!is_file($configPath)) {
    fwrite(STDERR, "SKIP: config.php نیست — این تست به پایگاه‌دادهٔ زنده نیاز دارد.\n");
    exit(77);
}
// اگر پایگاه‌داده در دسترس نیست، این تست باید **رد شود** نه اینکه صفحهٔ خطا بدهد.
// دلیل: در CI و روی کلون تازه، سرویس دیتابیس معمولاً بالا نیست و یک Fatal
// کل مجموعهٔ آزمون‌ها را قرمز می‌کند، در حالی که هیچ باگ کدی وجود ندارد.
$__cfg = require $configPath;
$__db = is_array($__cfg['database'] ?? null) ? $__cfg['database'] : [];
try {
    $__probe = new PDO(
        sprintf('mysql:host=%s;port=%d', (string) ($__db['host'] ?? 'localhost'), (int) ($__db['port'] ?? 3306)),
        (string) ($__db['user'] ?? ''),
        (string) ($__db['password'] ?? ''),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
    );
    unset($__probe);
} catch (Throwable $e) {
    fwrite(STDERR, "SKIP: پایگاه‌داده در دسترس نیست — " . $e->getMessage() . "\n");
    exit(77);
}
unset($__cfg, $__db);
require_once $root . '/bootstrap.php';

$pass = 0; $fail = 0;
function ok(bool $c, string $l, string $d=''): void {
    global $pass, $fail;
    if ($c) { $pass++; echo "  [PASS] $l\n"; }
    else { $fail++; echo "  [FAIL] $l\n"; if ($d !== '') echo "         $d\n"; }
}

function setset(string $k, string $v): void {
    db()->prepare('INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)')->execute([$k, $v]);
    $GLOBALS['__settings_cache'] = null;
}

// ── صحنه: کاربر ۹۹ با نقش واقعی «agent»، فقط در فهرست سوپروایزرها ──
setset('role_supervisor_ids', '99');
setset('role_it_expert_ids', '');
setset('role_support_expert_ids', '');
setset('role_inspection_ids', '');

// نقش «user» عمداً: نقش agent خودش queue.view دارد و تست نمی‌توانست تفکیک کند
// که آن دسترسی از نقش آمده یا از فهرست سوپروایزر. با user، هر دسترسیِ اضافه
// حتماً از اعطای فهرست آمده است.
$agent = ['id' => 99, 'role' => 'user', 'is_active' => 1, 'is_primary_admin' => 0,
          'department_id' => 1, 'handling_unit_id' => null];

echo "=== الف) نقش اصلی دست نمی‌خورد ===\n";
ok(user_is_key_role($agent, 'supervisor'), 'کاربر ۹۹ در فهرست سوپروایزر شناسایی می‌شود');
ok(trim((string) setting('role_supervisor_ids')) === '99', 'تنظیم فهرست واقعاً ۹۹ است');

echo "\n=== ب) فقط پنل — دسترسی‌های اضافی گرفته شد (نقش user) ===\n";
// تصمیم مالک (۲۰۲۶-۱۱-۱۶): «دسترسی‌های اضافی سوپروایزر هم گرفته شود.»
ok(user_can($agent, 'supervisor.panel'), 'user_can(supervisor.panel) = true — تنها دسترسی اعطایی');
foreach (['supervisor.decide', 'supervisor.reopen', 'queue.view'] as $perm) {
    ok(!user_can($agent, $perm), "user_can($perm) = false — دسترسی اضافی گرفته شد");
}

echo "\n=== ج) دسترسی‌های سنگین نمی‌آید ===\n";
foreach (['settings.general','settings.users','logs.view','ticket.view_all'] as $perm) {
    ok(!user_can($agent, $perm), "user_can($perm) = false");
}

echo "\n=== د) کاربر بیرون فهرست هیچ‌کدام را ندارد ===\n";
$outsider = ['id' => 12345, 'role' => 'user', 'is_active' => 1, 'is_primary_admin' => 0];
ok(!user_can($outsider, 'supervisor.panel'), 'supervisor.panel = false');
ok(!user_can($outsider, 'supervisor.decide'), 'supervisor.decide = false');

echo "\n=== ه) نقش قدیمی supervisor سازگار می‌ماند ===\n";
$legacy = ['id' => 12346, 'role' => 'supervisor', 'is_active' => 1, 'is_primary_admin' => 0];
ok(user_can($legacy, 'supervisor.panel'), 'نقش supervisor هنوز پنل دارد');

echo "\n=== و) حذف از فهرست، دسترسی را می‌گیرد و نقش را نه ===\n";
setset('role_supervisor_ids', '');
ok(!user_can($agent, 'supervisor.panel'), 'پس از حذف: supervisor.panel = false');
ok(trim((string) setting('role_supervisor_ids')) === '', 'فهرست خالی شد');

echo "\n" . str_repeat('═', 60) . "\nPASS=$pass | FAIL=$fail\n";
exit($fail === 0 ? 0 : 1);
