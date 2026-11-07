#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * اجرای یک فایل .sql روی دیتابیس CI از مسیر PDO (همان چیزی که در
 * .github/workflows/logging-acceptance.yml با موفقیت به‌کار می‌رود).
 *
 * چرا: گام‌های shell با `mariadb < file.sql` در CI خطای واقعی را فقط در لاگ رانر
 * چاپ می‌کنند که از بیرون دیده نمی‌شود؛ این ابزار همان خطا را به‌صورت
 * annotation ‏(::error::) منتشر می‌کند تا بدون دسترسی به لاگ قابل‌خواندن باشد.
 *
 * استفاده (در GitHub Actions):
 *   php tools/ci_load_schema.php install-empty-db.sql
 * متغیرهای محیطی: CI_DB_HOST (127.0.0.1) | CI_DB_PORT (3306) | CI_DB_NAME (persian_ticketing_ci)
 *                 CI_DB_USER (root) | CI_DB_PASS (خالی)
 */

$file = $argv[1] ?? 'install-empty-db.sql';
$host = getenv('CI_DB_HOST') ?: '127.0.0.1';
$port = getenv('CI_DB_PORT') ?: '3306';
$name = getenv('CI_DB_NAME') ?: 'persian_ticketing_ci';
$user = getenv('CI_DB_USER') ?: 'root';
$pass = getenv('CI_DB_PASS') ?: '';

function ci_error(string $title, string $message): void
{
    $encoded = str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $message);
    echo '::error title=' . $title . '::' . mb_substr($encoded, 0, 900) . "\n";
    fwrite(STDERR, $title . ' — ' . mb_substr($message, 0, 400) . "\n");
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
    );
} catch (Throwable $e) {
    ci_error('ci-schema-load', 'اتصال به دیتابیس ناموفق بود: ' . $e->getMessage());
    exit(1);
}

$sql = @file_get_contents($file);
if ($sql === false) {
    ci_error('ci-schema-load', "خواندن فایل ممکن نشد: {$file}");
    exit(1);
}

// تقسیم بر اساس «;» با رعایت رشته‌های تک‌نقل‌قول (دو نقل‌قول پشت‌سرهم درون رشته = خودِ نقل‌قول)
$statements = [];
$buffer = '';
$inString = false;
$length = strlen($sql);
for ($i = 0; $i < $length; $i++) {
    $ch = $sql[$i];
    if ($inString) {
        if ($ch === "'") {
            if ($i + 1 < $length && $sql[$i + 1] === "'") {
                $buffer .= "''";
                $i++;
                continue;
            }
            $inString = false;
        }
        $buffer .= $ch;
        continue;
    }
    if ($ch === "'") {
        $inString = true;
        $buffer .= $ch;
        continue;
    }
    if ($ch === ';') {
        $statements[] = $buffer;
        $buffer = '';
        continue;
    }
    $buffer .= $ch;
}
if (trim($buffer) !== '') {
    $statements[] = $buffer;
}

$executed = 0;
foreach ($statements as $index => $statement) {
    $stripped = preg_replace('~(^|\n)\s*--[^\n]*~', '$1', $statement);
    if (trim((string) $stripped) === '') {
        continue;
    }
    try {
        $pdo->exec($statement);
        $executed++;
    } catch (Throwable $e) {
        $preview = mb_substr((string) preg_replace('/\s+/', ' ', trim($statement)), 0, 220);
        ci_error(
            'ci-schema-load',
            'دستور شمارهٔ ' . ($index + 1) . ' شکست خورد — ' . $e->getMessage() . ' || SQL: ' . $preview
        );
        exit(1);
    }
}

echo "OK: {$executed} دستور از «{$file}» با موفقیت اجرا شد.\n";
exit(0);
