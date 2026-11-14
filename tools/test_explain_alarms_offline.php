<?php
/**
 * تست آفلاین ابزارهای تفسیر هشدار و اجرای مهاجرت ۰۳۳.
 * اجرا: php tools/test_explain_alarms_offline.php
 *
 * چون هر دو ابزار به DB نیاز دارند، این تست ساختار و ایمنی‌شان را می‌سنجد:
 *   • explain_alarms.php هیچ چیزی را تغییر نمی‌دهد
 *   • apply_migration_033.php فقط با --apply می‌نویسد (پیش‌فرض آزمایشی است)
 *   • مهاجرت ۰۳۳ واقعاً فقط UPDATE است و ساختار را عوض نمی‌کند
 *   • دستورهای پیشنهادی به فایل‌های واقعی اشاره می‌کنند
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
chdir(APP_ROOT);

$fails = 0;
$passes = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $fails, $passes;
    if ($ok) {
        $passes++;
        echo "[PASS] {$label}\n";
        return;
    }
    $fails++;
    echo "[FAIL] {$label}" . ($detail !== '' ? " :: {$detail}" : '') . "\n";
}

$explain = (string) @file_get_contents('tools/explain_alarms.php');
$apply = (string) @file_get_contents('tools/apply_migration_033.php');
$mig = (string) @file_get_contents('migrations/033-logging-legacy-context-collation.sql');

check('tools/explain_alarms.php وجود دارد', $explain !== '');
check('tools/apply_migration_033.php وجود دارد', $apply !== '');
check('migrations/033 منطبق وجود دارد', $mig !== '');

/* ── ۱) explain_alarms فقط‌خواندنی است ────────────────────────────────── */
$writes = [];
// فقط «کد» را می‌سنجیم نه متن توضیحی: کامنت‌های // و کاراکترهای داخل رشته‌ها
// را کنار می‌گذاریم، بعد دنبال SQL نوشتنی و توابع نوشتن فایل می‌گردیم.
$codeOnly = preg_replace('#^\s*//.*$#m', '', $explain) ?? '';
$codeOnly = preg_replace('#^\s*\*.*$#m', '', $codeOnly) ?? '';
// fwrite(STDERR, ...) نوشتن در سیستم نیست، فقط پیام خطا روی کنسول است.
$codeOnly = str_replace('fwrite(STDERR', '/*stderr*/', $codeOnly);
foreach (['db()->exec', 'db()->prepare', '->execute(', 'file_put_contents', 'unlink(', 'fwrite(', 'mkdir(', 'rename('] as $op) {
    if (strpos($codeOnly, $op) !== false) {
        $writes[] = $op;
    }
}
// SQL نوشتنی فقط اگر خارج از رشتهٔ توضیحی باشد (در explain هیچ کوئری نوشتنی نیست).
if (preg_match('/[\'"]\s*(INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM|DROP\s+TABLE|TRUNCATE|ALTER\s+TABLE)\s/i', $codeOnly)) {
    $writes[] = 'SQL نوشتنی';
}
check('explain_alarms هیچ عملیات نوشتنی ندارد', $writes === [], implode(', ', $writes));
check('explain_alarms چهار هشدار را می‌شناسد', substr_count($explain, '«') >= 4);
foreach (['پوشهٔ ضروری', 'Worker', '۰۳۳', 'system_alarm'] as $needle) {
    check("explain_alarms هشدار «{$needle}» را پوشش می‌دهد", mb_strpos($explain, $needle) !== false);
}
check('explain_alarms فقط CLI است', strpos($explain, "PHP_SAPI !== 'cli'") !== false);
check('explain_alarms دستور ۰۳۳ درست را پیشنهاد می‌دهد', strpos($explain, 'apply_migration_033.php') !== false);
check('explain_alarms دستور ناموجود run_migration را پیشنهاد نمی‌دهد', strpos($explain, 'run_migration.php') === false);

/* ── ۲) apply_migration: پیش‌فرض آزمایشی ─────────────────────────────── */
check('apply_migration پیش‌فرض آزمایشی است', strpos($apply, '$apply = in_array(\'--apply\', $argv, true)') !== false);
check('apply_migration فقط CLI است', strpos($apply, "PHP_SAPI !== 'cli'") !== false);
check('apply_migration قبل از اجرا شمارش می‌کند', strpos($apply, "COUNT(*) FROM log_issues WHERE context = 'system'") !== false);
check('apply_migration در schema_updates ثبت می‌کند', strpos($apply, 'INSERT INTO schema_updates') !== false);
check('apply_migration اگر قبلاً ثبت شده باشد کاری نمی‌کند', strpos($apply, '$alreadyApplied') !== false);
check('apply_migration کامنت‌های SQL را حذف می‌کند', strpos($apply, 'preg_match(\'/^\\s*--/\'') !== false);

/* ── ۳) مهاجرت ۰۳۳ ایمن است ──────────────────────────────────────────── */
check('۰۳۳ عملیات تخریبی ندارد', !preg_match('/\b(DROP|TRUNCATE|DELETE)\b/i', $mig));
check('۰۳۳ ساختار را عوض نمی‌کند', stripos($mig, 'ALTER TABLE') === false && stripos($mig, 'CREATE TABLE') === false);
check('۰۳۳ فقط UPDATE است', stripos($mig, 'UPDATE log_issues') !== false);
check('۰۳۳ idempotent است (شرط WHERE دارد)', stripos($mig, "WHERE i.context = 'system'") !== false);

/* ── ۴) ابزار اجرا خروجی درست می‌دهد (بدون DB نباید crash کند) ───────── */
$out = [];
$code = 0;
@exec(PHP_BINARY . ' tools/explain_alarms.php 2>&1', $out, $code);
$text = implode("\n", $out);
check('explain_alarms اجرا می‌شود', $text !== '');
check('منطق خروجی با حکم نهایی هم‌خوان است',
    (strpos($text, 'ایراد واقعی: 0') !== false) === ($code === 0),
    'code=' . $code);

echo "\n" . str_repeat('=', 60) . "\n";
echo 'PASS=' . $passes . ' | FAIL=' . $fails . "\n";
if ($fails > 0) {
    echo "❌ ابزارهای تفسیر هشدار ایراد دارند.\n";
    exit(1);
}
echo "✅ ابزارهای تفسیر هشدار و مهاجرت ۰۳۳ سالم و ایمن‌اند.\n";
exit(0);
