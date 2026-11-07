#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * آزمون یکپارچگی «نصب تازه + مهاجرت‌ها» روی MariaDB/MySQL واقعی.
 *
 * چرا وجود دارد؟ تا قبل از این، هیچ آزمونی روی دیتابیس واقعی اجرا نمی‌شد؛
 * فقط بررسی‌های ایستا و منطق PHP دایند. این آزمون در CI (با سرویس MariaDB) اجرا می‌شود.
 *
 * حالت‌ها:
 *   php tests/test_mariadb_fresh_install.php fresh-install      — بلافاصله بعد از بارگذاری install-empty-db.sql
 *   php tests/test_mariadb_fresh_install.php migrations-applied — بعد از اعمال دوبارهٔ همهٔ مهاجرت‌ها
 *
 * متغیرهای محیطی:
 *   CI_DB_HOST (پیش‌فرض 127.0.0.1) | CI_DB_PORT (3306) | CI_DB_NAME (persian_ticketing_ci)
 *   CI_DB_USER (root) | CI_DB_PASS (خالی) | CI_DB_REQUIRED (در CI=1: بدون سرور FAIL؛ وگرنه SKIP با exit 0)
 */

$mode = $argv[1] ?? 'fresh-install';
if (!in_array($mode, ['fresh-install', 'migrations-applied'], true)) {
    fwrite(STDERR, "استفاده: php tests/test_mariadb_fresh_install.php [fresh-install|migrations-applied]\n");
    exit(2);
}

$host     = getenv('CI_DB_HOST') ?: '127.0.0.1';
$port     = (int) (getenv('CI_DB_PORT') ?: 3306);
$name     = getenv('CI_DB_NAME') ?: 'persian_ticketing_ci';
$user     = getenv('CI_DB_USER') ?: 'root';
$pass     = getenv('CI_DB_PASS') ?: '';
$required = in_array(strtolower((string) getenv('CI_DB_REQUIRED')), ['1', 'true', 'yes'], true);

$passes   = 0;
$failures = 0;

function check(bool $ok, string $label): void
{
    global $passes, $failures;
    if ($ok) {
        $passes++;
        echo "PASS  {$label}\n";
    } else {
        $failures++;
        echo "FAIL  {$label}\n";
    }
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT            => 5,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    $pdo->exec('SET NAMES utf8mb4');
} catch (Throwable $e) {
    if ($required) {
        fwrite(STDERR, "FAIL  اتصال به دیتابیس ناموفق بود: {$e->getMessage()}\n");
        exit(1);
    }
    echo "SKIP  سرور دیتابیس در دسترس نیست؛ آزمون رد شد (محلی). برای اجرا: CI_DB_REQUIRED=1\n";
    exit(0);
}

echo 'سرور دیتابیس: ' . (string) $pdo->query('SELECT VERSION()')->fetchColumn() . "\n";
echo "آزمون: {$mode}\n";

$quotedDb = $pdo->quote($name);

if ($mode === 'fresh-install') {
    // ۱) انکدینگ دیتابیس
    $charset = (string) $pdo->query("SELECT default_character_set_name FROM information_schema.SCHEMATA WHERE schema_name = {$quotedDb}")->fetchColumn();
    check($charset === 'utf8mb4', "کاراکترست دیتابیس utf8mb4 است ({$charset})");

    // ۲) تعداد جدول‌ها دقیقاً با شمار CREATE TABLE در install-empty-db.sql برابر باشد
    $sql = file_get_contents(dirname(__DIR__) . '/install-empty-db.sql');
    check($sql !== false, 'خواندن install-empty-db.sql');
    $expected = $sql === false ? 0 : preg_match_all('/CREATE TABLE/i', $sql);
    $actual   = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = {$quotedDb} AND TABLE_TYPE = 'BASE TABLE'")->fetchColumn();
    check($expected > 0 && $actual === $expected, "تعداد جدول‌ها ({$actual}) با install-empty-db.sql ({$expected}) برابر است");

    // ۳) جدول‌های حیاتی وجود دارند
    $requiredTables = ['settings', 'departments', 'org_units', 'users', 'tickets', 'assets', 'system_logs', 'activity_logs', 'schema_updates'];
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    foreach ($requiredTables as $table) {
        $stmt->execute([$name, $table]);
        check((int) $stmt->fetchColumn() === 1, "جدول «{$table}» وجود دارد");
    }

    // ۴) یک پرس‌وجوی واقعی روی settings (ستون‌های utf8mb4)
    $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
    check(true, 'SELECT روی settings بدون خطا اجرا شد');
}

if ($mode === 'migrations-applied') {
    // ۱) رجیستری مهاجرت‌ها حداقل baseline (001..032) را ثبت کرده باشد
    $count = (int) $pdo->query('SELECT COUNT(DISTINCT migration) FROM schema_updates')->fetchColumn();
    check($count >= 32, "تعداد مهاجرت‌های ثبت‌شده در رجیستری ≥ ۳۲ ({$count})");

    // ۲) هیچ جفت (version, migration) تکراری نباشد
    $dupes = (int) $pdo->query('SELECT COUNT(*) FROM (SELECT version, migration, COUNT(*) c FROM schema_updates GROUP BY version, migration HAVING c > 1) t')->fetchColumn();
    check($dupes === 0, 'هیچ مهاجرت تکراری در رجیستری نیست');

    // ۳) همهٔ فایل‌های 001 تا 032 در رجیستری دیده می‌شوند
    $files = glob(dirname(__DIR__) . '/migrations/[0-9][0-9][0-9]-*.sql') ?: [];
    sort($files, SORT_STRING);
    foreach ($files as $file) {
        $base = basename($file);
        if (str_contains($base, '-rollback')) {
            continue; // فایل rollback عمداً خارج از زنجیرهٔ اعمال است
        }
        $num = (int) substr($base, 0, 3);
        if ($num > 32) {
            echo "NOTICE  {$base} در baseline رجیستری نیست؛ ثبت آن بر عهدهٔ جریان به‌روزرسانی است\n";
            continue;
        }
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM schema_updates WHERE migration = ?');
        $stmt->execute([$base]);
        check((int) $stmt->fetchColumn() >= 1, "مهاجرت {$base} در رجیستری ثبت شده");
    }
}

echo "──────────────────────────────────────\n";
if ($failures === 0) {
    echo "PASS  همهٔ بررسی‌ها ({$passes} مورد) سبز است.\n";
    exit(0);
}
echo "FAIL  {$failures} بررسی ناموفق بود ({$passes} موفق).\n";
exit(1);
