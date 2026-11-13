#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * آزمون رفتاری migration 037 روی جدول موقت MariaDB.
 *
 * جدول موقت با نام food_ticket_events فقط در همین اتصال دیده می‌شود و جدول واقعی
 * یا داده‌های عملیاتی را نمی‌خواند/تغییر نمی‌دهد. هدف، قفل‌کردن دامنهٔ UPDATE و
 * اطمینان از idempotency و حفظ delivered_at موجود است.
 */

$host = getenv('CI_DB_HOST') ?: '127.0.0.1';
$port = (int) (getenv('CI_DB_PORT') ?: 3306);
$name = getenv('CI_DB_NAME') ?: 'persian_ticketing_ci';
$user = getenv('CI_DB_USER') ?: 'root';
$pass = getenv('CI_DB_PASS') ?: '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Throwable $error) {
    fwrite(STDERR, "FAIL: اتصال MariaDB برای آزمون migration 037 برقرار نشد: {$error->getMessage()}\n");
    exit(1);
}

$temporaryTableCreated = false;
try {
    // جدول موقت هم‌نام، جدول دائمی را فقط در این session می‌پوشاند.
    $pdo->exec(
        "CREATE TEMPORARY TABLE food_ticket_events (
            id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
            event_type VARCHAR(32) NOT NULL,
            print_status VARCHAR(32) NOT NULL,
            delivery_status VARCHAR(32) NOT NULL,
            delivered_at DATETIME NULL,
            processed_at DATETIME NULL
        ) ENGINE=InnoDB"
    );
    $temporaryTableCreated = true;

    $insert = $pdo->prepare(
        'INSERT INTO food_ticket_events (id, event_type, print_status, delivery_status, delivered_at, processed_at)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $fixtures = [
        [1, 'auto_printed', 'printed', 'pending', null, '2026-11-12 20:00:00'],
        [2, 'auto_printed', 'printed', 'delivered', '2026-11-11 19:30:00', '2026-11-11 19:00:00'],
        [3, 'auto_printed', 'pending', 'pending', null, '2026-11-12 20:01:00'],
        [4, 'auto_guest', 'printed', 'pending', null, '2026-11-12 20:02:00'],
        [5, 'printed', 'printed', 'pending', null, '2026-11-12 20:03:00'],
        [6, 'auto_printed', 'print_error', 'pending', null, '2026-11-12 20:04:00'],
    ];
    foreach ($fixtures as $fixture) {
        $insert->execute($fixture);
    }

    $migrationPath = dirname(__DIR__) . '/migrations/037-food-auto-receipts-delivered.sql';
    $migrationSql = file_get_contents($migrationPath);
    if (!is_string($migrationSql) || trim($migrationSql) === '') {
        throw new RuntimeException('فایل migration 037 خالی یا غیرقابل‌خواندن است.');
    }

    $firstAffected = $pdo->exec($migrationSql);
    if ($firstAffected !== 1) {
        throw new RuntimeException("انتظار می‌رفت اجرای اول دقیقاً ۱ ردیف را تغییر دهد؛ مقدار واقعی: {$firstAffected}");
    }

    $secondAffected = $pdo->exec($migrationSql);
    if ($secondAffected !== 0) {
        throw new RuntimeException("اجرای دوم باید بدون تغییر باشد؛ مقدار واقعی: {$secondAffected}");
    }

    $rows = $pdo->query(
        'SELECT id, event_type, print_status, delivery_status, delivered_at, processed_at
         FROM food_ticket_events ORDER BY id'
    )->fetchAll();
    $byId = [];
    foreach ($rows as $row) {
        $byId[(int) $row['id']] = $row;
    }

    $expect = static function (bool $condition, string $message): void {
        if (!$condition) {
            throw new RuntimeException($message);
        }
        echo "PASS: {$message}\n";
    };

    $expect(
        $byId[1]['delivery_status'] === 'delivered'
        && $byId[1]['delivered_at'] === $byId[1]['processed_at'],
        'auto_printed/printed/pending به delivered تبدیل و delivered_at از processed_at پر شد'
    );
    $expect(
        $byId[2]['delivery_status'] === 'delivered'
        && $byId[2]['delivered_at'] === '2026-11-11 19:30:00',
        'زمان تحویل قبلیِ رکورد delivered حفظ شد'
    );
    $expect(
        $byId[3]['delivery_status'] === 'pending',
        'auto_printed که هنوز print_status=printed ندارد تغییر نکرد'
    );
    $expect(
        $byId[4]['delivery_status'] === 'pending',
        'رویداد auto_guest تغییر نکرد'
    );
    $expect(
        $byId[5]['delivery_status'] === 'pending',
        'رویداد چاپ فیزیکی معمولی تغییر نکرد'
    );
    $expect(
        $byId[6]['delivery_status'] === 'pending',
        'رویداد auto_printed ناموفق/دارای print_error تغییر نکرد'
    );
    echo "PASS: migration 037 روی جدول موقت، با اجرای دومِ بدون تغییر، idempotent است.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "FAIL: آزمون رفتاری migration 037: {$error->getMessage()}\n");
    exitCode:
    exit(1);
} finally {
    if ($temporaryTableCreated) {
        try {
            $pdo->exec('DROP TEMPORARY TABLE IF EXISTS food_ticket_events');
        } catch (Throwable) {
            // بستن اتصال نیز جدول موقت را حذف می‌کند.
        }
    }
}
