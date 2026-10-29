<?php
declare(strict_types=1);
/**
 * ۱.۶۲.۴۱ — بکاپ کامل از فایلهای پوشهٔ سامانه (جدا از بکاپ دیتابیس).
 *
 * این فایل کاملاً مستقل است: به config.php، دیتابیس، یا bootstrap.php نیاز ندارد.
 * یعنی حتی اگر سامانه ۵۰۰ بدهد هم باز میشود و بکاپ میگیرد.
 *
 * استفاده: لینک زیر را در مرورگر باز کنید → یک فایل ZIP دانلود میشود
 *          و همان ZIP در storage/backups-files/ هم ذخیره میشود.
 *
 *   http://آدرس-سامانه/tools/backup-files.php
 *
 * برای برگردانی: ZIP را extract کنید و محتوای آن را روی پوشهٔ سامانه کپی کنید.
 * دیتابیس جداگانه از پنل ← پشتیبانگیری برگردانده میشود.
 */

set_time_limit(0);
@ini_set('memory_limit', '512M');

// کلید سادهٔ دسترسی — بعد از گرفتن بکاپ، این فایل را از روی سرور پاک کنید.
$accessKey = 'itsm-backup-16241';
if (($_GET['k'] ?? '') !== $accessKey) {
    http_response_code(403);
    echo 'دسترسی مجاز نیست. لینک را همراه ?k=... باز کنید.';
    exit;
}

$root = realpath(dirname(__DIR__));
if ($root === false) {
    http_response_code(500);
    echo 'پوشهٔ ریشه پیدا نشد.';
    exit;
}

// پوشههایی که نباید داخل بکاپ بروند (حجیم، موقتی، یا شامل بکاپهای قبلی)
$skip = [
    'storage', 'uploads', 'downloads', '.git', 'node_modules', 'vendor',
    '.cache', '.idea', '.vscode', '__pycache__', 'preview', 'updates',
    'backups-files', 'temp', 'tmp',
];
$skipFiles = ['config.php.bak', '.env', 'error.log'];

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    echo 'افزونهٔ ZipArchive در PHP فعال نیست.';
    exit;
}

$storeDir = $root . '/storage/backups-files';
if (!is_dir($storeDir) && !mkdir($storeDir, 0750, true) && !is_dir($storeDir)) {
    http_response_code(500);
    echo 'ساخت پوشهٔ بکاپ ممکن نیست: ' . htmlspecialchars($storeDir, ENT_QUOTES, 'UTF-8');
    exit;
}

$name = 'system-files-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.zip';
$path = $storeDir . '/' . $name;

$zip = new ZipArchive();
if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo 'ساخت فایل ZIP ممکن نیست.';
    exit;
}

$count = 0;
$bytes = 0;
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($iterator as $item) {
    $relative = substr($item->getPathname(), strlen($root) + 1);
    $relative = str_replace('\\', '/', $relative);
    $top = explode('/', $relative)[0];
    if (in_array($top, $skip, true)) { continue; }
    if ($item->isDir()) { continue; }
    if (in_array(basename($relative), $skipFiles, true)) { continue; }
    // بکاپهای ZIP قبلی را دوباره داخل بکاپ نگذار
    if (str_starts_with($top, 'system-files-') && str_ends_with($top, '.zip')) { continue; }
    if ($item->getSize() > 25 * 1024 * 1024) { continue; } // فایلهای خیلی حجیم را رد کن
    if (!$zip->addFile($item->getPathname(), $relative)) { continue; }
    $count++;
    $bytes += $item->getSize();
}

$readme = "بکاپ فایلهای سامانه\n"
    . "تاریخ: " . date('Y-m-d H:i:s') . "\n"
    . "نسخه: " . (is_file($root . '/VERSION') ? trim((string) file_get_contents($root . '/VERSION')) : 'نامشخص') . "\n"
    . "تعداد فایلها: {$count}\n"
    . "حجم تقریبی: " . number_format($bytes) . " بایت\n\n"
    . "برای برگردانی: این ZIP را extract کنید و محتوایش را روی پوشهٔ اصلی سامانه کپی کنید.\n"
    . "توجه: config.php داخل بکاپ هست؛ دیتابیس جداگانه از پنل ← پشتیبانگیری برگردانده شود.\n";
$zip->addFromString('BACKUP-INFO.txt', $readme);
$zip->close();

// هدر دانلود
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
header('Content-Length: ' . (string) filesize($path));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
readfile($path);
exit;
