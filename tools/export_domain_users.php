<?php
declare(strict_types=1);

/**
 * استخراج همهٔ کاربران دامین (auth_source = 'ldap') به فایل CSV
 * ----------------------------------------------------------------
 * خروجی در پوشهٔ «domain-users-export» کنار همین فایل ساخته می‌شود:
 *   tools/domain-users-export/domain-users-YYYY-MM-DD_HHMMSS.csv
 *
 * اجرا (از پوشهٔ ticket، در ویندوز XAMPP):
 *   C:\xampp\php\php.exe tools\export_domain_users.php
 *
 * فایل با UTF-8 (همراه BOM) و جداکنندهٔ «;» نوشته می‌شود تا در Excel فارسی درست باز شود.
 * فقط از خط فرمان اجرا می‌شود؛ از مرورگر اجازه داده نمی‌شود.
 */

function dud_export(PDO $pdo, string $outputDir): string
{
    if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
        throw new RuntimeException('ساخت پوشهٔ خروجی ممکن نشد: ' . $outputDir);
    }

    $stmt = $pdo->query(
        "SELECT id, username, full_name, first_name, last_name, employee_number, national_code,
                email, department, role, is_active
         FROM users
         WHERE auth_source = 'ldap'
         ORDER BY full_name, id"
    );
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $fileName = 'domain-users-' . date('Y-m-d_His') . '.csv';
    $path = rtrim($outputDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;

    $handle = fopen($path, 'wb');
    if ($handle === false) {
        throw new RuntimeException('نوشتن فایل خروجی ممکن نشد: ' . $path);
    }
    fwrite($handle, "\xEF\xBB\xBF"); // BOM برای Excel

    $headers = ['ردیف', 'شناسه', 'نام کاربری', 'نام کامل', 'نام', 'نام خانوادگی', 'کد پرسنلی', 'کد ملی', 'ایمیل', 'واحد', 'نقش', 'فعال'];
    fputcsv($handle, $headers, ';');

    $index = 0;
    foreach ($rows as $row) {
        $index++;
        fputcsv($handle, [
            $index,
            $row['id'],
            $row['username'],
            $row['full_name'],
            $row['first_name'] ?? '',
            $row['last_name'] ?? '',
            $row['employee_number'] ?? '',
            $row['national_code'] ?? '',
            $row['email'] ?? '',
            $row['department'] ?? '',
            $row['role'] ?? '',
            (int) ($row['is_active'] ?? 0) === 1 ? 'بله' : 'خیر',
        ], ';');
    }
    fclose($handle);

    return $path;
}

function dud_main(): void
{
    if (PHP_SAPI !== 'cli') {
        http_response_code(403);
        exit('این اسکریپت فقط از خط فرمان اجرا می‌شود.');
    }

    require_once __DIR__ . '/../bootstrap.php';

    $outputDir = __DIR__ . DIRECTORY_SEPARATOR . 'domain-users-export';
    $path = dud_export(db(), $outputDir);
    $count = max(0, count(file($path)) - 1);

    echo "انجام شد. {$count} کاربر دامین استخراج شد.\n";
    echo "فایل: {$path}\n";
}

if (!defined('DOMAIN_USERS_EXPORT_NO_RUN')) {
    dud_main();
}
