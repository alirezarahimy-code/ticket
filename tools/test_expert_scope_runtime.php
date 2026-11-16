<?php
declare(strict_types=1);

/**
 * test_expert_scope_runtime.php — اثبات دامنهٔ دید کارشناس برگزیده.
 *
 * سه چیز را روی پایگاه‌دادهٔ واقعی می‌سنجد:
 *   ۱) کارشناس IT فقط حوزهٔ IT را می‌بیند (نه همه‌چیز)
 *   ۲) کارشناس پشتیبانی فقط حوزهٔ پشتیبانی
 *   ۳) کاربری که هم‌زمان در هر دو فهرست است، هر دو حوزه را می‌بیند
 *
 * اجرا: ITSM_CONFIG_PATH=/path/config.php php tools/test_expert_scope_runtime.php
 * بدون config معتبر، با کد ۷۷ رد می‌شود (skip).
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
require_once $root . '/modules/request-center/lib/functions.php';

$pass=0;$fail=0;
function ok(bool $c,string $l,string $d=''):void{global $pass,$fail;if($c){$pass++;echo "  [PASS] $l\n";}else{$fail++;echo "  [FAIL] $l\n";if($d!=='')echo "         $d\n";}}
function setset(string $k,string $v):void{db()->prepare('INSERT INTO settings (`key`, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)')->execute([$k,$v]);$GLOBALS['__settings_cache']=null;}

echo "=== صحنه: کارشناس IT (نقش user) + کارشناس پشتیبانی (نقش user) ===\n";
setset('role_it_expert_ids','201');
setset('role_support_expert_ids','202');
setset('role_supervisor_ids','');

echo "\n=== الف) دامنهٔ دید — فقط حوزهٔ خودش ===\n";
$u201=['id'=>201,'role'=>'user','is_active'=>1,'is_primary_admin'=>0];
$u202=['id'=>202,'role'=>'user','is_active'=>1,'is_primary_admin'=>0];
[$w201,$p201]=ticket_scope($u201,'t');
[$w202,$p202]=ticket_scope($u202,'t');
echo "    ۲۰۱: $w201 " . json_encode($p201) . "\n";
echo "    ۲۰۲: $w202 " . json_encode($p202) . "\n";
ok($p201===['it'], 'کارشناس IT فقط حوزهٔ it را می‌بیند', json_encode($p201));
ok($p202===['support'], 'کارشناس پشتیبانی فقط حوزهٔ support', json_encode($p202));

echo "\n=== ب) کاربر دو-حوزه‌ای: هر دو ===\n";
setset('role_it_expert_ids','201,202');
[$w,$p]=ticket_scope($u202,'t');
echo "    ۲۰۲ حالا: $w " . json_encode($p) . "\n";
ok($w==='WHERE t.service_group IN (?, ?)', 'عبارت IN برای هر دو حوزه', $w);
ok(sort($p) && $p===['it','support'] || $p===['it','support'], 'هر دو حوزه در پارامترها', json_encode($p));

echo "\n=== ج) این کارشناس به‌عنوان ثبت‌کننده مسئول خودش نمی‌شود ===\n";
$tk=['id'=>7,'service_group'=>'it','requester_id'=>201,'department_id'=>null];
ok(!ticket_assignee_is_eligible($tk,$u201), 'کارشناس، مسئول تیکتِ خودش نمی‌شود');

echo "\n=== د) کاربر بیرون فهرست، صف ندارد ===\n";
$outsider=['id'=>4242,'role'=>'user','is_active'=>1,'is_primary_admin'=>0];
[$wo,$po]=ticket_scope($outsider,'t');
echo "    بیرونی: " . ($wo===''?'(بدون فیلتر!)':$wo) . "\n";
ok($wo!=='', 'کاربر عادی فیلتر دارد — همه‌چیز نمی‌بیند', $wo);

echo "\n".str_repeat('═',60)."\nPASS=$pass | FAIL=$fail\n";
exit($fail===0?0:1);
