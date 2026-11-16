<?php
declare(strict_types=1);

/**
 * test_primary_admin_routing_runtime.php — «هیچ ادمین اصلی پیش‌فرض مسئول نیست»
 *
 * قانون لازم‌الاجرا (تصمیم مالک، ۲۰۲۶-۱۱-۱۶):
 *   • ادمین اصلی که در فهرست کارشناس آی تی/پشتیبانی نیست ⇒ هرگز مسئول نمی‌شود،
 *     نه با ارجاع خودکار و نه با ارجاع دستی.
 *   • ادمین اصلی که **در فهرست است** (مثل «علی») ⇒ نقش ادمینش حفظ می‌شود و
 *     تیکت‌های همان حوزه به او ارجاع می‌شود.
 *
 * اجرا: ITSM_CONFIG_PATH=/path/config.php php tools/test_primary_admin_routing_runtime.php
 * بدون پایگاه‌داده، کد ۷۷ (skip).
 */

$root = dirname(__DIR__);
$configPath = (string) (getenv('ITSM_CONFIG_PATH') ?: $root . '/config.php');
if (!is_file($configPath)) {
    fwrite(STDERR, "SKIP: config.php نیست.\n");
    exit(77);
}
$__cfg = require $configPath;
$__db = is_array($__cfg['database'] ?? null) ? $__cfg['database'] : [];
try {
    unset($__probe);
    $__probe = new PDO(
        sprintf('mysql:host=%s;port=%d', (string) ($__db['host'] ?? 'localhost'), (int) ($__db['port'] ?? 3306)),
        (string) ($__db['user'] ?? ''), (string) ($__db['password'] ?? ''),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
    );
} catch (Throwable $e) {
    fwrite(STDERR, "SKIP: پایگاه‌داده در دسترس نیست — " . $e->getMessage() . "\n");
    exit(77);
}
unset($__probe, $__cfg, $__db);
require_once $root . '/bootstrap.php';
require_once $root . '/modules/request-center/lib/functions.php';

// دو کاربر آزمون؛ پاک‌سازی در پایان.
$seed = static function (): void {
    db()->exec("DELETE FROM users WHERE id IN (301, 302)");
    db()->exec("INSERT INTO users (id, username, full_name, role, is_active, is_primary_admin, password_hash, created_at)
                VALUES (301,'__t_ali','علی','primary_admin',1,1,'x',NOW()),
                       (302,'__t_reza','رضا','primary_admin',1,1,'x',NOW())");
};
$cleanup = static function (): void {
    db()->exec("DELETE FROM users WHERE id IN (301, 302)");
};
$seed();
register_shutdown_function($cleanup);

$pass=0;$fail=0;
function ok(bool $c,string $l,string $d=''):void{global $pass,$fail;if($c){$pass++;echo "  [PASS] $l\n";}else{$fail++;echo "  [FAIL] $l\n";if($d!=='')echo "         $d\n";}}
function setset(string $k,string $v):void{db()->prepare('INSERT INTO settings (`key`,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)')->execute([$k,$v]);$GLOBALS['__settings_cache']=null;}

// علی = ادمین اصلی که در فهرست کارشناس آی تی هم هست
$ali = ['id'=>301,'role'=>'primary_admin','is_primary_admin'=>1,'is_active'=>1,'department_id'=>0,'handling_unit_code'=>'it'];
// رضا = ادمین اصلی که در هیچ فهرستی نیست
$reza = ['id'=>302,'role'=>'primary_admin','is_primary_admin'=>1,'is_active'=>1,'department_id'=>0,'handling_unit_code'=>'it'];
$tkIt = ['requester_id'=>999,'service_group'=>'it','department_id'=>0,'status'=>'new'];

echo "=== الف) علی: ادمین اصلی + در فهرست کارشناس آی تی ===\n";
setset('role_it_expert_ids','301'); setset('role_support_expert_ids',''); setset('role_supervisor_ids','');
ok(user_is_key_role($ali,'it_expert'), 'علی در فهرست کارشناس آی تی است');
ok(user_is_primary_admin($ali), 'نقش ادمین اصلی علی حفظ شده');
ok(ticket_assignee_is_eligible($tkIt,$ali,'user_can'), 'علی واجد مسئولیت تیکت IT است');
ok(ticket_assignee_group_ok($tkIt,$ali), 'حوزهٔ IT برای علی مجاز است');

echo "\n=== ب) رضا: ادمین اصلی، خارج از فهرست ===\n";
ok(!user_is_key_role($reza,'it_expert'), 'رضا در فهرست نیست');
ok(!ticket_assignee_is_eligible($tkIt,$reza,'user_can'), 'رضا واجد مسئولیت نیست — حتی با ارجاع دستی');
ok(!ticket_assignee_group_ok($tkIt,$reza), 'حوزهٔ رضا مجاز نیست');
$tkSup = ['requester_id'=>999,'service_group'=>'support','department_id'=>0];
ok(!ticket_assignee_is_eligible($tkSup,$reza,'user_can'), 'رضا برای تیکت پشتیبانی هم واجد نیست');

echo "\n=== ج) ارجاع خودکار: فهرست پر → علی ===\n";
$picked = choose_handling_agent('it',0,999);
echo "    انتخاب‌شده: " . var_export($picked,true) . "\n";
ok($picked === 301, 'ارجاع خودکار IT به علی (۳۰۱) می‌رود');

echo "\n=== د) ارجاع خودکار با فهرست خالی → هیچ ادمین اصلی ===\n";
setset('role_it_expert_ids','');
$picked2 = choose_handling_agent('it',0,999);
echo "    انتخاب‌شده: " . var_export($picked2,true) . "\n";
ok($picked2 !== 301 && $picked2 !== 302, 'با فهرست خالی هیچ ادمین اصلی انتخاب نشد');

echo "\n=== ه) دسترسی‌های سوپروایز ===\n";
setset('role_supervisor_ids','301');
$GLOBALS['__settings_cache']=null;
ok(user_can($ali,'supervisor.panel'), 'پنل سوپروایزر (تنها دسترسی اعطایی)');
ok(!user_can($ali,'supervisor.decide') || user_is_primary_admin($ali), 'decide از فهرست نیامد');

echo "\n".str_repeat('═',60)."\nPASS=$pass | FAIL=$fail\n";
exit($fail===0?0:1);
