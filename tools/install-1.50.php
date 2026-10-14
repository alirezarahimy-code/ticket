<?php
declare(strict_types=1);

/**
 * نصب دیتابیس نسخهٔ ۱.۵۰ (فقط خط فرمان)
 * ----------------------------------------------------------------
 * کارهایی که انجام می‌دهد (هر کدام فقط در صورت نیاز):
 *   ۱. پشتیبان جدول users به نام users_backup_1_50_<زمان>
 *   ۲. افزودن نقش‌های local_user و guard به ستون role (بدون حذف مقادیر قبلی سرور)
 *   ۳. افزودن ستون must_change_password
 *   ۴. گزارش نتیجه
 * اجرا (از پوشهٔ ticket):
 *   C:\xampp\php\php.exe tools\install-1.50.php
 * قابل اجرای چندباره است.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('این ابزار فقط از خط فرمان اجرا می‌شود.');
}

require_once __DIR__ . '/../bootstrap.php';

$appRoot = dirname(__DIR__);
$version = trim((string) @file_get_contents($appRoot . '/VERSION'));
echo "نسخهٔ فایل‌های نصب‌شده: " . ($version !== '' ? $version : 'نامشخص') . "\n";
if ($version !== '' && version_compare($version, '1.50', '<')) {
    fwrite(STDERR, "خطا: فایل‌های نسخهٔ ۱.۵۰ هنوز کپی نشده‌اند (VERSION = $version). اول پوشهٔ ticket را کپی کنید.\n");
    exit(1);
}

$pdo = db();
$dbName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
echo "پایگاه داده: $dbName\n";

$columnType = static function (string $column) use ($pdo, $dbName): ?string {
    $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = ?");
    $stmt->execute([$dbName, $column]);
    $value = $stmt->fetchColumn();
    return $value === false ? null : (string) $value;
};

// اگر دیتابیس از قبل کامل است، چیزی تغییر نمی‌کند و پشتیبان هم لازم نیست.
$alreadyRole = str_contains((string) $columnType('role'), "'guard'") && str_contains((string) $columnType('role'), "'local_user'");
if ($alreadyRole && $columnType('must_change_password') !== null) {
    echo "دیتابیس از قبل برای ۱.۵۰ آماده است؛ تغییری لازم نبود.\n";
    exit(0);
}

// ۱. پشتیبان
$backup = 'users_backup_1_50_' . date('Ymd_His') . '_' . bin2hex(random_bytes(2));
$pdo->exec("CREATE TABLE `$backup` AS SELECT * FROM users");
echo "۱. پشتیبان جدول users ساخته شد: $backup\n";

// ۲. نقش‌ها: مقادیر فعلی ENUM حفظ می‌شود و فقط نقش‌های جدید اضافه می‌شوند
$roleType = $columnType('role');
if ($roleType === null) {
    throw new RuntimeException('ستون role در جدول users پیدا نشد.');
}
preg_match("/^enum\((.*)\)$/i", $roleType, $m);
$values = [];
if (isset($m[1])) {
    preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $m[1], $parts);
    $values = $parts[1] ?? [];
}
$missing = array_values(array_diff(['local_user', 'guard'], $values));
if ($missing === []) {
    echo "۲. نقش‌های local_user و guard از قبل در ستون role هستند.\n";
} else {
    $all = array_merge($values, $missing);
    $list = implode(',', array_map(static fn (string $v): string => "'" . str_replace("'", "\\'", $v) . "'", $all));
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM($list) NOT NULL DEFAULT 'user'");
    echo "۲. نقش‌های " . implode('، ', $missing) . " به ستون role اضافه شد.\n";
}

// ۳. ستون اجبار تغییر رمز
if ($columnType('must_change_password') === null) {
    $pdo->exec('ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0');
    echo "۳. ستون must_change_password اضافه شد.\n";
} else {
    echo "۳. ستون must_change_password از قبل وجود دارد.\n";
}

// ۴. بررسی نهایی
$finalRole = (string) $columnType('role');
$ok = str_contains($finalRole, "'local_user'") && str_contains($finalRole, "'guard'") && $columnType('must_change_password') !== null;
$count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
echo "۴. تعداد کاربران: $count\n";
echo $ok ? "نتیجه: نصب دیتابیس نسخهٔ ۱.۵۰ با موفقیت انجام شد.\n"
         : "نتیجه: بررسی ناموفق بود. پیام‌ها را برای پشتیبانی بفرستید.\n";
exit($ok ? 0 : 1);
