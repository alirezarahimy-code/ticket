<?php
/**
 * apply_migration_033.php — اجرای امنِ مهاجرت ۰۳۳ و ثبت آن در schema_updates.
 *
 * اجرا:
 *   php tools/apply_migration_033.php --dry-run     فقط نشان بده چه می‌شود (پیش‌فرض)
 *   php tools/apply_migration_033.php --apply       واقعاً اجرا کن
 *
 * چرا این فایل لازم شد؟
 *   مهاجرت‌ها معمولاً از صفحهٔ به‌روزرسانی سامانه اجرا می‌شوند، ولی ۰۳۳ در هیچ
 *   بستهٔ جدیدی نیست (فقط در بسته‌های ۱.۶۲.۸۳ و ۱.۶۲.۸۸ و full بود). اگر آن بسته‌ها
 *   قبلاً نصب شده باشند فایل هست ولی در schema_updates ثبت نشده، و گارد سلامت
 *   هر بار هشدار «مهاجرت اجرا نشده» می‌دهد.
 *
 * چرا بی‌خطر است؟
 *   ۰۳۳ فقط یک UPDATE است: ستون context ردیف‌های log_issues را که مقدار
 *   «system» دارند با مقدار واقعی system_logs هم‌تراز می‌کند. هیچ ستون/جدولی
 *   ساخته یا حذف نمی‌شود و تکرارش بی‌خطر است (idempotent).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only — این ابزار باید از خط فرمان اجرا شود.\n");
}

$root = dirname(__DIR__);
@chdir($root);

$apply = in_array('--apply', $argv, true);
$dry = !$apply;

const MIGRATION = '033-logging-legacy-context-collation.sql';
const VERSION_TAG = '1.62.97'; // نسخه‌ای که این مهاجرت را ثبت می‌کند

$line = static function (string $s = ''): void { echo $s, PHP_EOL; };

$line('╔══════════════════════════════════════════════════════════════╗');
$line('║  مهاجرت ۰۳۳ — هم‌ترازی context در log_issues                  ║');
$line('╚══════════════════════════════════════════════════════════════╝');
$line(' حالت: ' . ($dry ? 'آزمایشی (--dry-run) — هیچ چیزی تغییر نمی‌کند' : '❗ اجرای واقعی (--apply)'));

$migrationFile = $root . '/migrations/' . MIGRATION;
if (!is_file($migrationFile)) {
    $line('');
    $line('[FAIL] فایل مهاجرت پیدا نشد: migrations/' . MIGRATION);
    $line('       یعنی این سیستم آن فایل را ندارد؛ پس این مهاجرت اصلاً لازم نیست.');
    exit(1);
}
$line(' فایل مهاجرت: موجود است.');

if (!is_file($root . '/config.php')) {
    $line('[FAIL] config.php پیدا نشد — از ریشهٔ برنامه اجرا کنید.');
    exit(1);
}
require_once $root . '/bootstrap.php';
foreach (['food-ticket.php'] as $f) {
    if (is_file($root . '/' . $f)) {
        require_once $root . '/' . $f;
    }
}

/* ── وضعیت فعلی ─────────────────────────────────────────────────────── */
$line('');
$line('۱) وضعیت فعلی');
$alreadyApplied = false;
$hasSchemaTable = false;
try {
    db()->query('SELECT 1');
    $line('  [OK]   اتصال پایگاه‌داده برقرار است.');
} catch (Throwable $e) {
    $line('  [FAIL] اتصال پایگاه‌داده: ' . $e->getMessage());
    exit(1);
}
try {
    $hasSchemaTable = db_table_exists('schema_updates');
    if ($hasSchemaTable) {
        $q = db()->prepare('SELECT COUNT(*) FROM schema_updates WHERE LOWER(migration) = ?');
        $q->execute([strtolower(MIGRATION)]);
        $alreadyApplied = (int) $q->fetchColumn() > 0;
    }
} catch (Throwable $e) {
    $line('  [WARN] خواندن schema_updates: ' . $e->getMessage());
}
if (!$hasSchemaTable) {
    $line('  [FAIL] جدول schema_updates وجود ندارد — سامانه نصب کامل نیست.');
    exit(1);
}
$line($alreadyApplied
    ? '  [OK]   مهاجرت ۰۳۳ قبلاً ثبت شده است — کاری لازم نیست.'
    : '  [i]    مهاجرت ۰۳۳ در schema_updates ثبت نشده است.');

/* ── چه چیزی تغییر می‌کند؟ ──────────────────────────────────────────── */
$line('');
$line('۲) تأثیر روی داده (فقط شمارش، بدون تغییر)');
$affected = null;
try {
    $affected = (int) db()->query("SELECT COUNT(*) FROM log_issues WHERE context = 'system'")->fetchColumn();
    $line('  ردیف‌های log_issues با context=\'system\': ' . $affected);
    if ($affected === 0) {
        $line('  ⇒ این مهاجرت هیچ داده‌ای را تغییر نمی‌دهد؛ فقط «ثبت» می‌شود تا هشدار کاذب برود.');
    }
} catch (Throwable $e) {
    $line('  [WARN] جدول log_issues خوانده نشد: ' . mb_substr($e->getMessage(), 0, 120));
    $line('         (اگر مرکزی‌سازی لاگینگ نصب نیست، این مهاجرت لازم نیست.)');
}

if ($alreadyApplied) {
    $line('');
    $line('══════════════════════════════════════════════════════════════');
    $line(' ✅ همه‌چیز مرتب است. کاری برای انجام نیست.');
    exit(0);
}

if ($dry) {
    $line('');
    $line('══════════════════════════════════════════════════════════════');
    $line(' این فقط آزمایش بود. برای اجرای واقعی:');
    $line('   php tools\\apply_migration_033.php --apply');
    exit(0);
}

/* ── اجرا ───────────────────────────────────────────────────────────── */
$line('');
$line('۳) اجرا');
$sql = (string) file_get_contents($migrationFile);
// حذف کامنت‌ها برای اجرای تمیز (فایل فقط یک UPDATE دارد).
$statements = [];
foreach (explode(";\n", $sql) as $chunk) {
    $clean = '';
    foreach (explode("\n", $chunk) as $ln) {
        if (preg_match('/^\s*--/', $ln)) {
            continue;
        }
        $clean .= $ln . "\n";
    }
    $clean = trim($clean);
    if ($clean !== '' && $clean !== ';') {
        $statements[] = rtrim($clean, ';');
    }
}
$line('  ' . count($statements) . ' دستور پیدا شد.');
$done = 0;
try {
    foreach ($statements as $i => $stmt) {
        db()->exec($stmt);
        $done++;
        $line('  [OK]   دستور ' . ($i + 1) . ' اجرا شد.');
    }
} catch (Throwable $e) {
    $line('  [FAIL] دستور ' . ($done + 1) . ' ناموفق: ' . $e->getMessage());
    $line('  (مهاجرت ثبت نشد؛ سیستم در وضعیت قبلی می‌ماند.)');
    exit(1);
}

// ثبت در schema_updates — همان کاری که صفحهٔ به‌روزرسانی می‌کند.
try {
    db()->prepare('INSERT INTO schema_updates (version, migration) VALUES (?, ?)')
        ->execute([VERSION_TAG, MIGRATION]);
    $line('  [OK]   در schema_updates ثبت شد (version=' . VERSION_TAG . ').');
} catch (Throwable $e) {
    $line('  [WARN] ثبت در schema_updates ناموفق: ' . $e->getMessage());
    $line('         ممکن است رکورد تکراری باشد؛ بررسی کنید.');
}

$line('');
$line('══════════════════════════════════════════════════════════════');
$line(' ✅ مهاجرت ۰۳۳ اجرا و ثبت شد — هشدار «مهاجرت اجرا نشده» باید برطرف شود.');
$line('    برای تأیید: php tools\\explain_alarms.php');
exit(0);
