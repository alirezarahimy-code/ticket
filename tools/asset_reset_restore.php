<?php
declare(strict_types=1);

/**
 * tools/asset_reset_restore.php — بازگرداندن بکاپ ریست
 * ============================================================================
 * ۱.۶۳.۱-r19
 *
 * اجرا:
 *     php tools/asset_reset_restore.php                       # فهرست بکاپ‌ها
 *     php tools/asset_reset_restore.php assets-20261116-140000.json
 *     php tools/asset_reset_restore.php <file> --apply        # بازگردانی واقعی
 *
 * پیش‌فرض «فقط بررسی» است. رکوردهایی که همین حالا وجود دارند (بر اساس id)
 * رد می‌شوند تا بازگردانی دوباره چیزی را دوبار نسازد.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("این ابزار فقط از خط فرمان اجرا می‌شود.\n");
}

$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';

$backupDir = $root . '/storage/asset-reset';
$args = array_slice($argv, 1);
$apply = in_array('--apply', $args, true);
$file = '';
foreach ($args as $a) {
    if ($a !== '--apply' && !str_starts_with((string) $a, '--')) {
        $file = (string) $a;
        break;
    }
}

$out = static function (string $l): void { fwrite(STDOUT, $l . PHP_EOL); };

$out('');
if ($file === '') {
    $out('بکاپ‌های موجود در ' . $backupDir . ':');
    $found = @glob($backupDir . '/*.json') ?: [];
    if (!$found) {
        $out('  (هیچ بکاپی پیدا نشد)');
    }
    foreach ($found as $f) {
        $out(sprintf('  %-42s %s', basename($f), number_format(filesize($f) / 1024, 1) . ' KB'));
    }
    $out('');
    $out('برای بازگردانی:');
    $out('    php tools/asset_reset_restore.php <نام-فایل-بکاپ>');
    $out('');
    exit(0);
}

if (!str_contains($file, '/') && !str_contains($file, '\\')) {
    $file = $backupDir . '/' . $file;
}
if (!is_file($file)) {
    fwrite(STDERR, 'فایل بکاپ پیدا نشد: ' . $file . PHP_EOL);
    exit(1);
}

$data = json_decode((string) file_get_contents($file), true);
if (!is_array($data) || !isset($data['rows']) || !is_array($data['rows'])) {
    fwrite(STDERR, 'ساختار بکاپ معتبر نیست.' . PHP_EOL);
    exit(1);
}

$rows = $data['rows'];
$out('فایل    : ' . basename($file));
$out('تاریخ    : ' . (string) ($data['created_at'] ?? 'نامشخص'));
$out('رکوردها : ' . count($rows));
$out('حالت    : ' . ($apply ? 'بازگردانی واقعی' : 'فقط بررسی — چیزی نوشته نمی‌شود'));
$out('');

try {
    $pdo = db();
} catch (Throwable $e) {
    fwrite(STDERR, 'اتصال به دیتابیس ناموفق: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$existing = [];
try {
    $existing = $pdo->query('SELECT id FROM assets')->fetchAll(PDO::FETCH_COLUMN);
    $existing = array_flip(array_map('intval', $existing));
} catch (Throwable $e) {
}

$toInsert = 0;
$skip = 0;
foreach ($rows as $row) {
    if (!is_array($row)) { continue; }
    $id = (int) ($row['id'] ?? 0);
    if ($id > 0 && isset($existing[$id])) {
        $skip++;
        continue;
    }
    $toInsert++;
}

$out('قابل بازگردانی : ' . $toInsert);
$out('از قبل موجود   : ' . $skip . ' (رد می‌شوند تا چیزی دوبار ساخته نشود)');
$out('');

if (!$apply) {
    $out('برای بازگردانی واقعی همین دستور را با --apply اجرا کنید:');
    $out('    php tools/asset_reset_restore.php ' . basename($file) . ' --apply');
    $out('');
    exit(0);
}

if ($toInsert === 0) {
    $out('چیزی برای بازگردانی نیست.');
    exit(0);
}

$inserted = 0;
$failed = 0;
try {
    $pdo->beginTransaction();
    foreach ($rows as $row) {
        if (!is_array($row)) { continue; }
        $id = (int) ($row['id'] ?? 0);
        if ($id > 0 && isset($existing[$id])) { continue; }
        $cols = array_keys($row);
        // نام ستون‌ها فقط از بکاپ خودمان می‌آید، ولی باز هم اعتبارسنجی می‌کنیم.
        foreach ($cols as $c) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', (string) $c)) {
                throw new RuntimeException('نام ستون نامعتبر در بکاپ: ' . $c);
            }
        }
        $sql = 'INSERT INTO assets (`' . implode('`,`', $cols) . '`) VALUES ('
            . implode(',', array_fill(0, count($cols), '?')) . ')';
        try {
            $pdo->prepare($sql)->execute(array_values($row));
            $inserted++;
        } catch (Throwable $e) {
            $failed++;
            if ($failed <= 5) {
                fwrite(STDERR, '  رد شد (id=' . $id . '): ' . $e->getMessage() . PHP_EOL);
            }
        }
    }
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    fwrite(STDERR, 'بازگردانی ناموفق و برگشت داده شد: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$out('');
$out('✓ ' . $inserted . ' رکورد بازگردانده شد.');
if ($failed > 0) {
    $out('⚠ ' . $failed . ' رکورد رد شد (احتمالاً تعارض کلید یکتا). جزئیات بالا.');
}
$out('');
