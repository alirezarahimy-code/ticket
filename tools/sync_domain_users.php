<?php
declare(strict_types=1);

/**
 * همگام‌سازی کاربران دامین از AD (خط فرمان، معادل دکمهٔ «معرفی کاربران دامین» در تنظیمات)
 * ----------------------------------------------------------------
 * اجرا (از پوشهٔ ticket):
 *   C:\xampp\php\php.exe tools\sync_domain_users.php
 * حساب‌های غیرفعال AD که قبلاً در سامانه نیستند، ایمپورت نمی‌شوند.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('این ابزار فقط از خط فرمان اجرا می‌شود.');
}

require_once __DIR__ . '/../bootstrap.php';

try {
    $result = ldap_sync_all_users();
} catch (Throwable $e) {
    fwrite(STDERR, 'همگام‌سازی ناموفق بود: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "همگام‌سازی کاربران دامین انجام شد.\n";
$labels = [
    'created' => 'ساخته شد',
    'updated' => 'به‌روزرسانی شد',
    'disabled' => 'غیرفعال (در AD)',
    'skipped_disabled' => 'غیرفعال و ایمپورت نشد',
    'code_conflict' => 'تعارض کد پرسنلی',
];
foreach ($labels as $key => $label) {
    echo $label . ': ' . (int) ($result[$key] ?? 0) . "\n";
}
echo 'رد شده (خطا برای یک کاربر): ' . (int) ($result['skipped'] ?? 0) . "\n";
foreach (($result['errors'] ?? []) as $line) {
    echo '  - ' . $line . "\n";
}
