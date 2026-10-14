<?php
declare(strict_types=1);

/**
 * ایمپورت فهرست پرسنل غذا از خط فرمان (نسخهٔ ۱.۵۰)
 * ----------------------------------------------------------------
 * حالت پیش‌فرض (پیش‌نمایش): هیچ تغییری در دیتابیس نمی‌دهد.
 *   C:\xampp\php\php.exe tools\preview_food_import.php "C:\Users\ADMIN\Desktop\food-list.csv"
 *
 * حالت اعمال: اول نسخهٔ پشتیبان جدول users ساخته می‌شود، بعد ایمپورت انجام می‌شود.
 *   C:\xampp\php\php.exe tools\preview_food_import.php "C:\Users\ADMIN\Desktop\food-list.csv" --apply
 *
 * فایل CSV (UTF-8) یا Excel (xlsx) پذیرفته می‌شود.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('این ابزار فقط از خط فرمان اجرا می‌شود.');
}

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../food-ticket-import.php';
require_once __DIR__ . '/../food-personnel-link.php';

$args = array_slice($argv, 1);
$apply = in_array('--apply', $args, true);
$args = array_values(array_filter($args, static fn (string $a): bool => $a !== '--apply'));
$path = $args[0] ?? '';
if ($path === '' || !is_file($path)) {
    fwrite(STDERR, "فایل پیدا نشد. مسیر فایل را بعد از نام ابزار بنویسید.\n");
    exit(1);
}

$parsed = food_ticket_import_parse_file($path, basename($path));
if (!($parsed['ok'] ?? false)) {
    fwrite(STDERR, 'خواندن فایل ناموفق بود: ' . (string) ($parsed['error'] ?? '') . "\n");
    exit(1);
}

$backupName = '';
if ($apply) {
    $backupName = (string) fpl_backup_users(db());
    echo "پشتیبان جدول users ساخته شد: $backupName\n";
}
$result = food_ticket_import_apply($parsed, true, !$apply);

echo ($apply ? "ایمپورت انجام شد.\n" : "پیش‌نمایش (هیچ تغییری ذخیره نشد).\n");
echo 'ردیف‌های فایل: ' . $parsed['total'] . "\n";
echo 'کاربر محلی جدید: ' . ($result['local_created'] ?? 0) . "\n";
echo 'رکورد موجود که به‌روزرسانی شد: ' . $result['updated'] . "\n";
echo '  از این تعداد، تبدیل به حساب محلی: ' . ($result['converted'] ?? 0) . "\n";
echo 'ساخته نشد (نام شبیه حساب دامین بدون کد): ' . ($result['domain_duplicates'] ?? 0) . "\n";
echo 'رد شده / خطا: ' . $result['failed'] . "\n";
echo 'تکراری داخل فایل: ' . $result['duplicates'] . "\n\n";

foreach ($result['rows'] as $row) {
    if (!in_array($row['status'], ['domain_duplicate', 'error', 'skipped', 'duplicate'], true)) {
        continue;
    }
    echo 'ردیف ' . $row['row'] . ' | کد ' . $row['pc'] . ' | ' . $row['name'] . ' | ' . $row['status'] . "\n";
    foreach ($row['messages'] as $message) {
        echo '    - ' . $message . "\n";
    }
}
if (!$apply) {
    echo "\nبرای اعمال واقعی همین دستور را با --apply در انتها اجرا کنید.\n";
}
