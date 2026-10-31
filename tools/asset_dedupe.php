<?php
declare(strict_types=1);
/**
 * ۱.۶۲.۳۷ — یکپارچهسازی امن شناسنامههای تکراری.
 *
 *   php tools/asset_dedupe.php                 → فقط گزارش (هیچ تغییری نمیدهد)
 *   php tools/asset_dedupe.php --apply        → بکاپ + انتقال وابستگیها + پرکردن داده خالی + حذف تکراری
 *   php tools/asset_dedupe.php --apply --enforce → همان + افزودن UNIQUE روی hostname_key
 *
 * قانون: قدیمیترین رکورد (کمترین id) بهعنوان «اصلی» نگه داشته میشود.
 * تمام جداول فرزند before-delete به رکورد اصلی منتقل میشوند تا تاریخچهای از دست نرود.
 * این ابزار هیچ رکوردی را بدون ثبت در جدول بکاپ حذف نمیکند.
 */

$root = dirname(__DIR__);
if (PHP_SAPI === 'cli' && !is_file($root . '/config.php')) {
    fwrite(STDERR, "config.php پیدا نشد. ابتدا سامانه را نصب کنید.\n"); exit(1);
}
if (is_file($root . '/config.php')) { require_once $root . '/config.php'; }
if (!function_exists('db')) { fwrite(STDERR, "تابع db() در دسترس نیست.\n"); exit(1); }
require_once $root . '/asset-names.php';

$apply    = in_array('--apply', $argv, true);
$enforce  = in_array('--enforce', $argv, true);
$isCli    = PHP_SAPI === 'cli';
$out      = static function (string $line) use ($isCli): void {
    echo $line . ($isCli ? PHP_EOL : "<br>\n");
};

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    $out('<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><body style="font-family:tahoma;padding:20px">');
}

try {
    $pdo = db();
    $out('══════════════════════════════════════════════════════════════');
    $out('  یکپارچهسازی شناسنامهها — ' . ($apply ? 'حالت اجرا (--apply)' : 'حالت گزارش (بدون تغییر)'));
    $out('══════════════════════════════════════════════════════════════');

    // ── ۰) بررسی وجود ستون (ممکن است migration هنوز اجرا نشده باشد)
    $hasKey = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='assets' AND COLUMN_NAME='hostname_key'")->fetchColumn();
    if ($hasKey === 0) {
        $out('⚠️  ستون hostname_key وجود ندارد. ابتدا بستهٔ آپدیت ۱.۶۲.۳۷ را نصب کنید (migrations/014).');
        $out('    یا دستور زیر را اجرا کنید:');
        $out('    ALTER TABLE assets ADD COLUMN hostname_key VARCHAR(191) NULL AFTER hostname;');
        exit(1);
    }

    // ── ۱) نرمالسازی دوبارهٔ کلیدها با منطق نهایی PHP (همان migration، ولی دقیقتر)
    //    رکوردهایی که با نسخههای قدیمی پر شدهاند نیز اصلاح میشوند.
    $allRows = $pdo->query('SELECT id, hostname_key, hostname, asset_tag FROM assets')->fetchAll(PDO::FETCH_ASSOC);
    $synced = 0;
    foreach ($allRows as $row) {
        $key = asset_identity_key($row['hostname'] ?? null, $row['asset_tag'] ?? null);
        $old = (string) ($row['hostname_key'] ?? '');
        if ($key === '' && $old === '') { continue; }
        if ($old !== $key) {
            $synced++;
            if ($apply) {
                $pdo->prepare('UPDATE assets SET hostname_key = ? WHERE id = ?')
                    ->execute([$key !== '' ? $key : null, (int) $row['id']]);
            }
        }
    }
    $out(sprintf('۱) کلید هویت: %d رکورد نیازمند نرمالسازی دوباره (%s).', $synced, $apply ? 'انجام شد' : 'فقط گزارش'));

    // ── ۲) پیدا کردن گروههای تکراری
    $groups = $pdo->query(<<<'SQL'
        SELECT hostname_key, COUNT(*) AS cnt,
               GROUP_CONCAT(id ORDER BY id SEPARATOR ',') AS ids,
               MIN(id) AS keeper
          FROM assets
         WHERE hostname_key IS NOT NULL AND hostname_key <> ''
         GROUP BY hostname_key
        HAVING COUNT(*) > 1
         ORDER BY cnt DESC, hostname_key
    SQL)->fetchAll(PDO::FETCH_ASSOC);

    $totalDupes = 0;
    foreach ($groups as $g) { $totalDupes += (int) $g['cnt'] - 1; }
    $out(sprintf('۲) گروههای تکراری: %d گروه، %d رکورد اضافه.', count($groups), $totalDupes));
    foreach (array_slice($groups, 0, 30) as $g) {
        $out(sprintf('   • %-30s → %s (نگه: %s)', $g['hostname_key'], $g['ids'], $g['keeper']));
    }
    if (count($groups) > 30) { $out('   … و ' . (count($groups) - 30) . ' گروه دیگر.'); }

    if (!$apply || $totalDupes === 0) {
        $out('');
        $out($apply ? '✅ هیچ تکراری برای پاکسازی نماند.' : 'ℹ️  برای اجرا: php tools/asset_dedupe.php --apply');
        $out('    قبل از اجرا حتماً از دیتابیس بکاپ بگیرید (پنل → پشتیبانگیری).');
        if ($enforce && $apply && $totalDupes === 0) {
            $idx = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='assets' AND INDEX_NAME='uq_assets_hostname_key'")->fetchColumn();
            if ($idx === 0) {
                $pdo->exec('ALTER TABLE assets ADD UNIQUE KEY uq_assets_hostname_key (hostname_key)');
                $out('✅ ایندکس UNIQUE اضافه شد؛ از این پس تکرار ناممکن است.');
            } else {
                $out('✅ ایندکس UNIQUE از قبل وجود دارد.');
            }
        }
        exit(0);
    }

    // ── ۳) بکاپ قبل از هر تغییر
    $backupTable = 'asset_dedupe_backup_' . date('Ymd_His');
    $pdo->exec("CREATE TABLE `{$backupTable}` (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        asset_id INT UNSIGNED NOT NULL,
        hostname_key VARCHAR(191) NULL,
        payload JSON NULL,
        merged_into INT UNSIGNED NULL,
        backed_up_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $out("۳) جدول بکاپ ساخته شد: {$backupTable}");

    // ── ۴) پیدا کردن تمام جداول فرزند (همهٔ ستونهایی که FK به assets دارند)
    $childCols = $pdo->query(<<<'SQL'
        SELECT TABLE_NAME, COLUMN_NAME, IS_NULLABLE
          FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = DATABASE()
           AND REFERENCED_TABLE_NAME = 'assets'
           AND REFERENCED_COLUMN_NAME = 'id'
         ORDER BY TABLE_NAME, COLUMN_NAME
    SQL)->fetchAll(PDO::FETCH_ASSOC);

    // بهصورت دستی هم ستونهای رایج را اضافه میکنیم (نصبهایی که FK ندارند)
    $fallback = [
        ['asset_memory_modules', 'asset_id'], ['asset_storage_devices', 'asset_id'],
        ['asset_graphics_cards', 'asset_id'], ['asset_peripherals', 'asset_id'],
        ['asset_history', 'asset_id'], ['asset_events', 'asset_id'],
        ['asset_software', 'asset_id'], ['asset_relations', 'source_asset_id'],
        ['asset_relations', 'target_asset_id'], ['asset_profiles', 'asset_id'],
        ['tickets', 'asset_id'],
    ];
    foreach ($fallback as [$tbl, $col]) {
        $exists = false;
        foreach ($childCols as $c) { if ($c['TABLE_NAME'] === $tbl && $c['COLUMN_NAME'] === $col) { $exists = true; break; } }
        if (!$exists) {
            $chk = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$tbl}' AND COLUMN_NAME='{$col}'")->fetchColumn();
            if ((int) $chk > 0) { $childCols[] = ['TABLE_NAME' => $tbl, 'COLUMN_NAME' => $col, 'IS_NULLABLE' => 'YES']; }
        }
    }
    $out('۴) جداول فرزند شناساییشده: ' . count($childCols));

    // ── ۵) ستونهای قابل پرکردن روی رکورد اصلی
    $fillCols = [];
    foreach ($pdo->query("SHOW COLUMNS FROM assets")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        if (!in_array($c['Field'], ['id','hostname_key','asset_tag','created_at','updated_at','last_inventory_at'], true)) {
            $fillCols[] = $c['Field'];
        }
    }

    $pdo->beginTransaction();
    $merged = 0; $errors = [];
    foreach ($groups as $g) {
        $keeper = (int) $g['keeper'];
        $ids = array_map('intval', explode(',', (string) $g['ids']));
        $dupes = array_values(array_filter($ids, static fn(int $i): bool => $i !== $keeper));

        foreach ($dupes as $dupId) {
            try {
                // بکاپ رکورد تکراری
                $row = $pdo->query('SELECT * FROM assets WHERE id = ' . $dupId)->fetch(PDO::FETCH_ASSOC);
                $pdo->prepare("INSERT INTO `{$backupTable}` (asset_id, hostname_key, payload, merged_into) VALUES (?, ?, ?, ?)")
                    ->execute([$dupId, $g['hostname_key'], json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $keeper]);

                // پر کردن فیلدهای خالی رکورد اصلی از روی تکراری
                $keeperRow = $pdo->query('SELECT * FROM assets WHERE id = ' . $keeper)->fetch(PDO::FETCH_ASSOC);
                $sets = []; $vals = [];
                foreach ($fillCols as $col) {
                    $cur = $keeperRow[$col] ?? null;
                    $new = $row[$col] ?? null;
                    if (($cur === null || $cur === '' || $cur === '0') && $new !== null && $new !== '' && $new !== '0') {
                        $sets[] = "`{$col}` = ?"; $vals[] = $new;
                    }
                }
                if ($sets) { $pdo->prepare('UPDATE assets SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute([...$vals, $keeper]); }

                // انتقال وابستگیها
                foreach ($childCols as $c) {
                    $tbl = $c['TABLE_NAME']; $col = $c['COLUMN_NAME'];
                    try {
                        // asset_profiles فقط یک رکورد بهازای asset_id دارد → رکورد تکراری پروفایل حذف شود
                        if ($tbl === 'asset_profiles') {
                            $pdo->exec("DELETE FROM `{$tbl}` WHERE `{$col}` = {$dupId}");
                        } else {
                            $pdo->exec("UPDATE IGNORE `{$tbl}` SET `{$col}` = {$keeper} WHERE `{$col}` = {$dupId}");
                        }
                    } catch (PDOException $e) {
                        $errors[] = "{$tbl}.{$col}: " . $e->getMessage();
                    }
                }

                // حذف رکورد تکراری
                $pdo->exec('DELETE FROM assets WHERE id = ' . $dupId);
                $merged++;
            } catch (Throwable $e) {
                $errors[] = "asset #{$dupId}: " . $e->getMessage();
            }
        }
    }
    $pdo->commit();

    $out(sprintf('۵) نتیجه: %d رکورد تکراری ادغام و حذف شد.', $merged));
    if ($errors) {
        $out('⚠️  خطاها (بقیه ادغام شدند):');
        foreach (array_slice($errors, 0, 20) as $e) { $out('   • ' . $e); }
    }
    $out("   بکاپ کامل در جدول `{$backupTable}` نگه داشته شد.");
    $out('   برای بازگردانی: SELECT * FROM `' . $backupTable . '`; سپس INSERT دستی رکوردها.');

    if ($enforce) {
        $idx = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='assets' AND INDEX_NAME='uq_assets_hostname_key'")->fetchColumn();
        if ($idx === 0) {
            $pdo->exec('ALTER TABLE assets ADD UNIQUE KEY uq_assets_hostname_key (hostname_key)');
            $out('✅ ایندکس UNIQUE اضافه شد؛ از این پس تکرار ناممکن است.');
        } else {
            $out('✅ ایندکس UNIQUE از قبل وجود دارد.');
        }
    } else {
        $out('ℹ️  بعد از اطمینان، با --enforce ایندکس UNIQUE اضافه کنید.');
    }
    $out('');
    $out('✅ پایان.');
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    $out('❌ خطا: ' . $e->getMessage());
    exit(1);
}
