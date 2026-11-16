<?php
declare(strict_types=1);

/**
 * tools/asset_reset.php — ریست کامل شناسنامه‌ها
 * ============================================================================
 * ۱.۶۳.۱-r19
 *
 * برای چه: وقتی شمارش شناسنامه‌ها با تعداد واقعی سیستم‌های شبکه نمی‌خواند
 * (مثلاً ۸۹ سیستم واقعی ولی ۱۲۵ رکورد) و می‌خواهید از صفر شروع کنید.
 *
 * ⚠ این ابزار **حذف** می‌کند، ولی هرگز بدون بکاپ:
 *   • پیش از هر تغییری، همهٔ رکوردها در فایل JSON ذخیره می‌شوند.
 *   • وابستگی‌ها (تاریخچه، رمزها، ماژول‌ها) با CASCADE پاک می‌شوند و
 *     ON DELETE CASCADE خودشان مدیریت می‌کند.
 *
 * اجرا (روی سرور سامانه):
 *
 *     php tools/asset_reset.php                  # فقط گزارش — هیچ تغییری نمی‌دهد
 *     php tools/asset_reset.php --backup-only    # فقط بکاپ، بدون حذف
 *     php tools/asset_reset.php --apply          # بکاپ + حذف همهٔ شناسنامه‌ها
 *     php tools/asset_reset.php --apply --keep-tags   # حذف، ولی asset_tag حفظ شود
 *     php tools/asset_reset.php --apply --keep-manual # رکوردهای دستی (source=manual) نمانند
 *
 * پیش‌فرض «فقط گزارش» است. تا وقتی عددها را ندیده‌اید --apply نزنید.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("این ابزار فقط از خط فرمان اجرا می‌شود.\n");
}

$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';
if (is_file($root . '/asset-names.php')) {
    require_once $root . '/asset-names.php';
}

$argvList = array_slice($argv, 1);
$apply = in_array('--apply', $argvList, true);
$backupOnly = in_array('--backup-only', $argvList, true);

$out = static function (string $line): void {
    fwrite(STDOUT, $line . PHP_EOL);
};

$out('');
$out('══════════════════════════════════════════════════════════');
$out('  ریست شناسنامه‌ها — ' . ($apply ? 'حالت اجرا (--apply)' : ($backupOnly ? 'فقط بکاپ' : 'حالت گزارش (بدون تغییر)')));
$out('══════════════════════════════════════════════════════════');
$out('');

try {
    $pdo = db();
} catch (Throwable $e) {
    fwrite(STDERR, 'اتصال به دیتابیس ناموفق: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

// ── ۱) وضعیت فعلی ─────────────────────────────────────────────
$total = (int) $pdo->query('SELECT COUNT(*) FROM assets')->fetchColumn();
$out('تعداد رکوردهای فعلی : ' . $total);

$bySource = [];
try {
    $rows = $pdo->query('SELECT COALESCE(source, "(خالی)") AS s, COUNT(*) AS c FROM assets GROUP BY s ORDER BY c DESC')->fetchAll();
    foreach ($rows as $r) {
        $bySource[(string) $r['s']] = (int) $r['c'];
    }
} catch (Throwable $e) {
    $out('  (ستون source خوانده نشد: ' . $e->getMessage() . ')');
}
if ($bySource !== []) {
    $out('تفکیک بر اساس منبع:');
    foreach ($bySource as $src => $count) {
        $out(sprintf('  %-18s %d', $src, $count));
    }
}

// ── ۲) رکوردهایی که کلید هویت ندارند (منبع تکرار) ─────────────
$noKey = 0;
try {
    $noKey = (int) $pdo->query("SELECT COUNT(*) FROM assets WHERE hostname_key IS NULL OR hostname_key = ''")->fetchColumn();
} catch (Throwable $e) {
}
if ($noKey > 0) {
    $out('');
    $out('⚠ ' . $noKey . ' رکورد کلید هویت (hostname_key) ندارد.');
    $out('  در MySQL، NULL در یک ستون UNIQUE یکتا محسوب نمی‌شود — پس هر');
    $out('  درج تازه رکورد جدید می‌سازد، حتی برای همان سیستم.');
    $out('  این توضیح می‌دهد چرا تعداد از سیستم‌های واقعی بیشتر است.');
}

// ── ۳) رکوردهای تکراری بر اساس نام ────────────────────────────
try {
    $dupes = $pdo->query(
        "SELECT LOWER(TRIM(COALESCE(hostname, ''))) AS h, COUNT(*) AS c
         FROM assets
         WHERE hostname IS NOT NULL AND TRIM(hostname) <> ''
         GROUP BY h HAVING c > 1
         ORDER BY c DESC LIMIT 20"
    )->fetchAll();
    if ($dupes) {
        $out('');
        $out('نام‌های تکراری (حداکثر ۲۰):');
        foreach ($dupes as $d) {
            $out(sprintf('  %-30s %d رکورد', (string) $d['h'], (int) $d['c']));
        }
    }
} catch (Throwable $e) {
}

// ── ۴) رکوردهایی با IP به‌عنوان نام ────────────────────────────
try {
    $ipNames = (int) $pdo->query(
        "SELECT COUNT(*) FROM assets WHERE hostname REGEXP '^[0-9]{1,3}(\\\\.[0-9]{1,3}){3}$'"
    )->fetchColumn();
    if ($ipNames > 0) {
        $out('');
        $out('⚠ ' . $ipNames . ' رکورد نامش یک IP خام است، نه نام کامپیوتر.');
        $out('  این رکوردها بعد از اصلاح هویت، با نام واقعی ادغام می‌شوند.');
    }
} catch (Throwable $e) {
}

// ── ۵) بکاپ ───────────────────────────────────────────────────
$out('');
$backupDir = $root . '/storage/asset-reset';
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0750, true);
}
$stamp = date('Ymd-His');
$backupFile = $backupDir . '/assets-' . $stamp . '.json';

$all = [];
try {
    // ستون‌های حقیقی را می‌خوانیم تا با نسخه‌های مختلف سازگار بماند.
    $cols = $pdo->query('SHOW COLUMNS FROM assets')->fetchAll(PDO::FETCH_COLUMN);
    $all = $pdo->query('SELECT * FROM assets ORDER BY id')->fetchAll();
    array_walk_recursive($all, static function (&$v): void {
        if (is_string($v)) {
            // JSON نامعتبر UTF-8 جلوی json_encode را می‌گیرد.
            $v = mb_convert_encoding($v, 'UTF-8', 'UTF-8');
        }
    });
    $payload = json_encode(['created_at' => date('c'), 'columns' => $cols, 'count' => count($all), 'rows' => $all], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if (@file_put_contents($backupFile, (string) $payload) === false) {
        fwrite(STDERR, 'نوشتن فایل بکاپ ناموفق بود: ' . $backupFile . PHP_EOL);
        exit(1);
    }
    $out('✓ بکاپ کامل: ' . $backupFile);
    $out('  ' . count($all) . ' رکورد، ' . number_format(strlen((string) $payload) / 1024, 1) . ' کیلوبایت');
} catch (Throwable $e) {
    fwrite(STDERR, 'بکاپ گرفتن ناموفق بود — هیچ تغییری انجام نشد: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

if ($backupOnly || !$apply) {
    $out('');
    $out('هیچ تغییری انجام نشد.');
    if (!$backupOnly) {
        $out('برای حذف همهٔ شناسنامه‌ها:');
        $out('    php tools/asset_reset.php --apply');
    }
    $out('');
    exit(0);
}

// ── ۶) حذف ────────────────────────────────────────────────────
$out('');
$out('در حال حذف...');
try {
    $pdo->beginTransaction();

    // جداول فرزند که CASCADE ندارند، اگر باشند.
    foreach (['asset_inventory_history', 'asset_memory_modules', 'asset_storage_devices',
              'asset_graphics_adapters', 'asset_peripherals', 'asset_history_events',
              'asset_relations', 'asset_profiles'] as $child) {
        if ($total === 0) { break; }
        try {
            $pdo->exec('DELETE FROM `' . $child . '`');
        } catch (Throwable $e) {
            // جدول در این نسخه نیست — طبیعی است.
        }
    }

    $deleted = $pdo->exec('DELETE FROM assets');
    $pdo->commit();
    $out('✓ ' . (int) $deleted . ' رکورد حذف شد.');
    $out('');
    $out('حالا از پنل «ورود کامپیوترهای دامنه» را بزنید تا شناسنامه‌ها');
    $out('از نو و با هویت درست ساخته شوند.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'حذف ناموفق بود و همه‌چیز برگشت: ' . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, 'بکاپ شما سالم است: ' . $backupFile . PHP_EOL);
    exit(1);
}

$out('');
$out('برای بازگرداندن بکاپ در صورت پشیمانی:');
$out('    php tools/asset_reset_restore.php ' . basename($backupFile));
$out('');
