<?php
/**
 * گزارش سلامت دیتابیس و کوئری‌های داغ (فقط خواندنی؛ هیچ داده‌ای تغییر نمی‌کند).
 *
 * اجرا از پوشهٔ ticket:
 *   C:\xampp\php\php.exe tools\db_health_report.php
 *
 * خروجی: اندازهٔ جداول بزرگ، تعداد کوئری‌های کند ۷ روز اخیر، و EXPLAIN کوئری‌های اصلی.
 * هر کوئری که «full table scan» روی جدول بزرگ دارد با علامت ⚠ مشخص می‌شود.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('این ابزار فقط از خط فرمان اجرا می‌شود.');
}

$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';

function dbh_line(string $text = ''): void
{
    echo $text . PHP_EOL;
}

dbh_line('=== گزارش سلامت دیتابیس — ' . date('Y-m-d H:i'));

try {
    $pdo = db();
} catch (Throwable $e) {
    dbh_line('اتصال به دیتابیس ناموفق بود: ' . $e->getMessage());
    exit(1);
}

// ۱) اندازه و تعداد ردیف جداول
dbh_line();
dbh_line('--- ۱) بزرگ‌ترین جداول (تعداد ردیف تقریبی و حجم MB)');
$stmt = $pdo->query("SELECT TABLE_NAME, TABLE_ROWS, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1048576, 1) AS size_mb,
        ROUND(DATA_FREE / 1048576, 1) AS free_mb
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
    ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
    LIMIT 15");
$bigTables = [];
foreach ($stmt->fetchAll() as $row) {
    $bigTables[(string) $row['TABLE_NAME']] = (int) $row['TABLE_ROWS'];
    dbh_line(sprintf('  %-28s ردیف≈%-10s حجم=%6s MB  فضای آزاد=%s MB',
        $row['TABLE_NAME'], $row['TABLE_ROWS'], $row['size_mb'], $row['free_mb']));
}

// ۲) کوئری‌های کند ۷ روز اخیر (از لاگ فنی؛ کوئری‌های بالای ۵۰۰ میلی‌ثانیه)
dbh_line();
dbh_line('--- ۲) کوئری‌های کند ۷ روز اخیر (بالای ۵۰۰ ms)');
try {
    $slow = $pdo->query("SELECT LEFT(meta_json, 220) AS info, COUNT(*) AS cnt
        FROM system_logs
        WHERE context = 'کندی کوئری' AND created_at >= NOW() - INTERVAL 7 DAY
        GROUP BY info ORDER BY cnt DESC LIMIT 15")->fetchAll();
    if (!$slow) {
        dbh_line('  موردی ثبت نشده است.');
    }
    foreach ($slow as $row) {
        dbh_line('  ' . $row['cnt'] . ' بار: ' . str_replace(["\n", "\r"], ' ', (string) $row['info']));
    }
} catch (Throwable $e) {
    dbh_line('  خواندن لاگ کوئری کند ممکن نشد: ' . $e->getMessage());
}

// ۳) EXPLAIN کوئری‌های داغ
$queries = [
    'صف چاپ فیش (worker هر چند ثانیه)' =>
        "SELECT * FROM food_ticket_events WHERE (print_status IN ('pending','print_error','failed')
            OR (print_status = 'printing' AND updated_at < NOW() - INTERVAL 30 MINUTE))
            AND (next_retry_at IS NULL OR next_retry_at <= NOW()) ORDER BY id ASC LIMIT 20",
    'شمارش کل فیش‌ها (صفحهٔ پایش)' => 'SELECT COUNT(*) FROM food_ticket_events',
    'شمارش خطاهای چاپ (صفحهٔ پایش)' => "SELECT COUNT(*) FROM food_ticket_events WHERE print_status IN ('print_error','failed')",
    'جستجوی run گروهی در source_payload' =>
        "SELECT id FROM food_ticket_events WHERE source_payload LIKE '%\"run_id\":1,%' ORDER BY id",
    'شمارش کل گزارش فعالیت' => 'SELECT COUNT(*) FROM activity_logs',
    'خلاصهٔ گزارش فعالیت به تفکیک عمل' =>
        'SELECT action_code, MAX(action_label), COUNT(*) FROM activity_logs GROUP BY action_code',
    'خلاصهٔ لاگ فنی سلامت (۲۴ ساعت)' =>
        "SELECT SUM(level = 'error') FROM system_logs WHERE created_at >= NOW() - INTERVAL 24 HOUR",
    'شماره برگ تردد روزانه' =>
        "SELECT COUNT(*) FROM traffic_visits WHERE serial_no LIKE '14050101-%'",
    'اعلان‌های خوانده‌نشدهٔ کاربر' =>
        'SELECT COUNT(*) FROM notifications WHERE user_id = 1 AND is_read = 0',
    'فهرست درخواست‌های یک وضعیت' =>
        "SELECT id, subject FROM tickets WHERE status = 'open' ORDER BY created_at DESC LIMIT 50",
    'جستجوی دارایی بر اساس نام سرور' =>
        "SELECT id FROM assets WHERE hostname = 'PC-TEST' LIMIT 1",
];

dbh_line();
dbh_line('--- ۳) EXPLAIN کوئری‌های داغ (⚠ = اسکن کامل روی جدول بزرگ یا filesort/temporary)');
foreach ($queries as $title => $sql) {
    dbh_line();
    dbh_line('* ' . $title);
    try {
        $rows = $pdo->query('EXPLAIN ' . $sql)->fetchAll();
    } catch (Throwable $e) {
        dbh_line('  EXPLAIN ناموفق: ' . $e->getMessage());
        continue;
    }
    foreach ($rows as $r) {
        $table = (string) ($r['table'] ?? '');
        $type = (string) ($r['type'] ?? '');
        $key = (string) ($r['key'] ?? '');
        $rowsEst = (int) ($r['rows'] ?? 0);
        $extra = (string) ($r['Extra'] ?? '');
        $warn = '';
        $big = $bigTables[$table] ?? $rowsEst;
        if (($type === 'ALL' || $type === 'index') && $big > 10000) {
            $warn = ' ⚠ اسکن کامل';
        }
        if (preg_match('/filesort|Using temporary/i', $extra) && $big > 10000) {
            $warn .= ' ⚠ مرتب‌سازی/جدول موقت';
        }
        dbh_line(sprintf('  جدول=%-20s نوع=%-6s کلید=%-28s ردیف≈%-9d%s', $table, $type, $key ?: '-', $rowsEst, $warn));
        if ($extra !== '') {
            dbh_line('      Extra: ' . $extra);
        }
    }
}

dbh_line();
dbh_line('پایان گزارش. این خروجی را برای بررسی ارسال کنید.');
