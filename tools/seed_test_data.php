#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * tools/seed_test_data.php — «دادهٔ نمونهٔ سامانهٔ تست»
 *
 * این اسکریپت یک سامانهٔ تازه‌نصب‌شده (مثلاً روی «سیستم تست شبکه») را با داده‌های
 * واقعی‌نما پر می‌کند تا بتوانید همهٔ قسمت‌های برنامه را با نمایش درست تست کنید:
 * کاربران با نقش‌های مختلف، واحدها، شناسنامهٔ اموال، درخواست‌ها در همهٔ وضعیت‌ها
 * (شامل دو درخواست با «لغو در انتظار تصمیم مسئول رسیدگی» برای تست رایج‌ترین مسیر)،
 * پیام‌ها، امتیازها، اعلان‌ها، پایگاه دانش، تعطیلات، ماژول غذا (کاتالوگ، تقویم
 * ماه جاری، سفارش‌ها، گروه سلف) و رکوردهای سی‌دی/دی‌وی‌دی.
 *
 * ⚠️ این اسکریپت را هرگز روی سامانهٔ عملیاتی اجرا نکنید.
 *
 * استفاده:
 *   php tools/seed_test_data.php --test-env                 ساخت دادهٔ نمونه (بار اول)
 *   php tools/seed_test_data.php --test-env --fresh         پاک‌کردن دادهٔ نمونهٔ قبلی و ساخت دوباره
 *   php tools/seed_test_data.php --test-env --password=XYZ  تعیین رمز یکسان برای همهٔ حساب‌های نمونه
 *
 * پیش‌فرض‌ها: نام پیکربندی از ITSM_CONFIG_PATH یا <ریشه>/config.php؛ رمز پیش‌فرض حساب‌ها Test@123456.
 * اجرای دوباره بدون --fresh اگر دادهٔ نمونه وجود داشته باشد، متوقف می‌شود (امن در برابر اجرای تصادفی دوباره).
 */

const SEED_MANIFEST_KEY = 'test_seed_manifest_v1';
const SEED_DEFAULT_PASSWORD = 'Test@123456';

// ───────────────────────────── آرگومان‌ها ─────────────────────────────
function seed_usage(): void
{
    $usage = <<<TXT
دادهٔ نمونهٔ سامانهٔ تست — هرگز روی سامانهٔ عملیاتی اجرا نکنید.

  php tools/seed_test_data.php --test-env [--fresh] [--password=رمزدلخواه]

  --test-env   تأیید اجباری این‌که سیستم تست است (الزامی)
  --fresh      پاک‌کردن دادهٔ نمونهٔ قبلی (بر اساس مانیفست ثبت‌شده) و ساخت دوباره
  --password   رمز یکسان برای همهٔ حساب‌های نمونه (پیش‌فرض: Test@123456)
TXT;
    fwrite(STDOUT, $usage . PHP_EOL);
}

$options = getopt('', ['test-env', 'fresh', 'password:', 'help']);
if (isset($options['help'])) {
    seed_usage();
    exit(0);
}
$testEnv = array_key_exists('test-env', $options);
$fresh = array_key_exists('fresh', $options);
$password = isset($options['password']) && is_string($options['password']) && $options['password'] !== ''
    ? $options['password']
    : (string) (getenv('SEED_TEST_PASSWORD') ?: SEED_DEFAULT_PASSWORD);

if (!$testEnv) {
    fwrite(STDERR, "خطا: برای محافظت از سامانهٔ عملیاتی، اجرای این اسکریپت بدون --test-env ممکن نیست.\n");
    fwrite(STDERR, "اگر مطمئن هستید این «سیستم تست» است، دوباره با --test-env اجرا کنید.\n");
    exit(2);
}
if (mb_strlen($password, 'UTF-8') < 8) {
    fwrite(STDERR, "خطا: رمز حساب‌های نمونه باید حداقل ۸ نویسه باشد.\n");
    exit(2);
}

$root = dirname(__DIR__);
$configPath = (string) (getenv('ITSM_CONFIG_PATH') ?: $root . '/config.php');
if (!is_file($configPath)) {
    fwrite(STDERR, "خطا: config.php پیدا نشد ({$configPath}). ابتدا سامانه را نصب کنید.\n");
    exit(1);
}
$config = require $configPath;
$dbCfg = $config['database'] ?? null;
if (!is_array($dbCfg) || !isset($dbCfg['host'], $dbCfg['name'], $dbCfg['user'])) {
    fwrite(STDERR, "خطا: پیکربندی database در config.php کامل نیست.\n");
    exit(1);
}

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $dbCfg['host'],
        (int) ($dbCfg['port'] ?? 3306),
        $dbCfg['name'],
        $dbCfg['charset'] ?? 'utf8mb4'
    );
    $pdo = new PDO($dsn, (string) $dbCfg['user'], (string) ($dbCfg['password'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET NAMES utf8mb4");
} catch (Throwable $exception) {
    fwrite(STDERR, 'خطا در اتصال به پایگاه داده: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

// ───────────────────────────── ابزارها ─────────────────────────────
function seed_manifest_load(PDO $pdo): ?array
{
    $st = $pdo->prepare('SELECT value FROM settings WHERE `key` = ?');
    $st->execute([SEED_MANIFEST_KEY]);
    $value = $st->fetchColumn();
    if ($value === false) {
        return null;
    }
    $decoded = json_decode((string) $value, true);
    return is_array($decoded) ? $decoded : null;
}

/** @param array<string,mixed> $manifest */
function seed_manifest_save(PDO $pdo, array $manifest): void
{
    $st = $pdo->prepare('INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
    $st->execute([SEED_MANIFEST_KEY, json_encode($manifest, JSON_UNESCAPED_UNICODE)]);
}

function seed_manifest_clear(PDO $pdo): void
{
    $st = $pdo->prepare('DELETE FROM settings WHERE `key` = ?');
    $st->execute([SEED_MANIFEST_KEY]);
}

/** @param array<int,mixed> $ids */
function seed_delete_in(PDO $pdo, string $sql, array $ids): int
{
    $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
    if ($ids === []) {
        return 0;
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare(str_replace('{IN}', $placeholders, $sql));
    $st->execute($ids);
    return $st->rowCount();
}

function seed_col_exists(PDO $pdo, string $table, string $column): bool
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $st->execute([$table, $column]);
    return (int) $st->fetchColumn() > 0;
}

function seed_next_ticket_no(PDO $pdo, string $prefix): string
{
    try {
        $pdo->query('SELECT 1 FROM ticket_number_seq LIMIT 1');
    } catch (Throwable $exception) {
        // خارج از تراکنش اصلی ساخته می‌شود؛ اگر این‌جا هم شکست بخورد خطای بالادستی رخ می‌دهد.
        $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_number_seq (prefix VARCHAR(20) NOT NULL PRIMARY KEY, seq INT UNSIGNED NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    $prefix = strtoupper(substr(trim($prefix) !== '' ? trim($prefix) : 'TK', 0, 12));
    $ensure = $pdo->prepare('INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, 0) ON DUPLICATE KEY UPDATE prefix = VALUES(prefix)');
    $ensure->execute([$prefix]);
    $read = $pdo->prepare('SELECT seq FROM ticket_number_seq WHERE prefix = ? FOR UPDATE');
    $read->execute([$prefix]);
    $current = (int) ($read->fetchColumn() ?: 0);
    $pattern = '^' . preg_quote($prefix, '/') . '-[0-9]+$';
    $maxQuery = $pdo->prepare('SELECT MAX(CAST(SUBSTRING_INDEX(ticket_no, "-", -1) AS UNSIGNED)) FROM tickets WHERE ticket_no LIKE ? AND ticket_no REGEXP ?');
    $maxQuery->execute([$prefix . '-%', $pattern]);
    $existingMax = (int) ($maxQuery->fetchColumn() ?: 0);
    $sequence = max($current, $existingMax) + 1;
    $write = $pdo->prepare('UPDATE ticket_number_seq SET seq = ? WHERE prefix = ?');
    $write->execute([$sequence, $prefix]);
    return $prefix . '-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
}

/** تبدیل میلادی به شمسی (الگوریتم کلاسیک) برای رکوردهای سی‌دی/دی‌وی‌دی. */
function seed_gregorian_to_jalali(int $gy, int $gm, int $gd): array
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * intdiv($days, 12053));
    $days %= 12053;
    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += intdiv($days - 1, 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + intdiv($days, 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intdiv($days - 186, 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return [$jy, $jm, $jd];
}

/** @return array{0:int,1:int,2:int}|null */
function seed_manifest_ids(?array $manifest, string $key): array
{
    $value = $manifest[$key] ?? [];
    return is_array($value) ? array_values($value) : [];
}

/** پاک‌کردن دادهٔ نمونهٔ قبلی بر اساس مانیفست (ترتیب امن نسبت به کلیدهای خارجی). */
function seed_wipe(PDO $pdo, array $m): array
{
    $deleted = [];
    $ticketIds = seed_manifest_ids($m, 'tickets');
    $assetIds = seed_manifest_ids($m, 'assets');
    $userIds = seed_manifest_ids($m, 'users');
    $unitIds = seed_manifest_ids($m, 'org_units');
    $deptIds = seed_manifest_ids($m, 'departments');
    $kbIds = seed_manifest_ids($m, 'knowledge_articles');
    $holidayDates = $m['holiday_dates'] ?? [];
    $foodOrderIds = seed_manifest_ids($m, 'food_orders');
    $foodLogIds = seed_manifest_ids($m, 'food_order_logs');
    $calendarItemIds = seed_manifest_ids($m, 'food_calendar_items');
    $calendarIds = seed_manifest_ids($m, 'food_calendars');
    $catalogIds = seed_manifest_ids($m, 'food_catalog');
    $groupIds = seed_manifest_ids($m, 'food_groups');
    $guestRequestIds = seed_manifest_ids($m, 'food_guest_requests');
    $cdDvdIds = seed_manifest_ids($m, 'cd_dvd_records');

    if ($foodLogIds) {
        $deleted['food_order_logs'] = seed_delete_in($pdo, 'DELETE FROM food_order_logs WHERE id IN ({IN})', $foodLogIds);
    }
    if ($foodOrderIds) {
        $deleted['food_orders'] = seed_delete_in($pdo, 'DELETE FROM food_orders WHERE id IN ({IN})', $foodOrderIds);
    }
    if ($ticketIds) {
        $ph = implode(',', array_fill(0, count($ticketIds), '?'));
        $pdo->prepare("DELETE FROM ticket_sla_alerts WHERE ticket_id IN ($ph)")->execute($ticketIds);
        $pdo->prepare("DELETE FROM ticket_ratings WHERE ticket_id IN ($ph)")->execute($ticketIds);
        $pdo->prepare("DELETE FROM ticket_messages WHERE ticket_id IN ($ph)")->execute($ticketIds);
        $pdo->prepare("DELETE FROM ticket_events WHERE ticket_id IN ($ph)")->execute($ticketIds);
        $pdo->prepare("DELETE FROM notifications WHERE ticket_id IN ($ph)")->execute($ticketIds);
        $deleted['tickets'] = seed_delete_in($pdo, 'DELETE FROM tickets WHERE id IN ({IN})', $ticketIds);
    }
    if ($userIds) {
        $deleted['notifications'] = seed_delete_in($pdo, 'DELETE FROM notifications WHERE user_id IN ({IN})', $userIds);
    }
    if ($cdDvdIds) {
        $deleted['cd_dvd_records'] = seed_delete_in($pdo, 'DELETE FROM cd_dvd_records WHERE id IN ({IN})', $cdDvdIds);
    }
    if ($kbIds) {
        $deleted['knowledge_articles'] = seed_delete_in($pdo, 'DELETE FROM knowledge_articles WHERE id IN ({IN})', $kbIds);
    }
    if (is_array($holidayDates) && $holidayDates !== []) {
        $st = $pdo->prepare('DELETE FROM holidays WHERE holiday_date IN (' . implode(',', array_fill(0, count($holidayDates), '?')) . ')');
        $st->execute(array_values($holidayDates));
        $deleted['holidays'] = $st->rowCount();
    }
    if ($groupIds) {
        $pdo->prepare('DELETE FROM food_ticket_group_members WHERE group_id IN (' . implode(',', array_fill(0, count($groupIds), '?')) . ')')->execute($groupIds);
        $pdo->prepare('DELETE FROM food_ticket_group_runs WHERE group_id IN (' . implode(',', array_fill(0, count($groupIds), '?')) . ')')->execute($groupIds);
        $pdo->prepare('DELETE FROM food_ticket_daily_absence WHERE group_id IN (' . implode(',', array_fill(0, count($groupIds), '?')) . ')')->execute($groupIds);
        $deleted['food_groups'] = seed_delete_in($pdo, 'DELETE FROM food_ticket_groups WHERE id IN ({IN})', $groupIds);
    }
    if ($guestRequestIds) {
        $deleted['food_guest_requests'] = seed_delete_in($pdo, 'DELETE FROM food_guest_requests WHERE id IN ({IN})', $guestRequestIds);
    }
    if ($calendarItemIds) {
        $deleted['food_calendar_items'] = seed_delete_in($pdo, 'DELETE FROM food_calendar_items WHERE id IN ({IN})', $calendarItemIds);
    }
    if ($calendarIds) {
        $deleted['food_calendars'] = seed_delete_in($pdo, 'DELETE FROM food_calendar WHERE id IN ({IN})', $calendarIds);
    }
    if ($catalogIds) {
        $deleted['food_catalog'] = seed_delete_in($pdo, 'DELETE FROM food_catalog WHERE id IN ({IN})', $catalogIds);
    }
    if ($assetIds) {
        $deleted['assets'] = seed_delete_in($pdo, 'DELETE FROM assets WHERE id IN ({IN})', $assetIds);
    }
    // ارجاع‌های مدیریت کاربران به حساب‌های نمونه را صفر کنیم تا حذف کاربران با خطای FK روبه‌رو نشود.
    if ($userIds) {
        $ph = implode(',', array_fill(0, count($userIds), '?'));
        $pdo->prepare("UPDATE org_units SET manager_user_id = NULL WHERE manager_user_id IN ($ph)")->execute($userIds);
        $pdo->prepare("UPDATE departments SET manager_user_id = NULL WHERE manager_user_id IN ($ph)")->execute($userIds);
        $deleted['users'] = seed_delete_in($pdo, 'DELETE FROM users WHERE id IN ({IN})', $userIds);
    }
    if ($unitIds) {
        $deleted['org_units'] = seed_delete_in($pdo, 'DELETE FROM org_units WHERE id IN ({IN})', $unitIds);
    }
    if ($deptIds) {
        $deleted['departments'] = seed_delete_in($pdo, 'DELETE FROM departments WHERE id IN ({IN})', $deptIds);
    }
    seed_manifest_clear($pdo);
    return $deleted;
}

$manifest = seed_manifest_load($pdo);
if ($manifest !== null && !$fresh) {
    fwrite(STDERR, "دادهٔ نمونهٔ قبلی در این پایگاه داده ثبت شده است (کلید مانیفست: " . SEED_MANIFEST_KEY . ").\n");
    fwrite(STDERR, "برای پاک‌کردن آن و ساخت دوباره اجرا کنید: php tools/seed_test_data.php --test-env --fresh\n");
    exit(3);
}
if ($manifest !== null && $fresh) {
    try {
        $pdo->beginTransaction();
        $wiped = seed_wipe($pdo, $manifest);
        $pdo->commit();
        fwrite(STDOUT, "دادهٔ نمونهٔ قبلی پاک شد: " . ($wiped ? json_encode($wiped, JSON_UNESCAPED_UNICODE) : '(خالی)') . PHP_EOL);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, 'خطا در پاک‌کردن دادهٔ قبلی: ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}

// ───────────────────────────── ساخت دادهٔ نمونه ─────────────────────────────
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$created = [
    'departments' => [], 'org_units' => [], 'users' => [], 'assets' => [], 'tickets' => [],
    'knowledge_articles' => [], 'holiday_dates' => [], 'food_catalog' => [], 'food_calendars' => [],
    'food_calendar_items' => [], 'food_orders' => [], 'food_order_logs' => [], 'food_groups' => [],
    'food_guest_requests' => [], 'cd_dvd_records' => [],
];
$summary = [];

// جدول شماره‌گذاری را پیش از تراکنش اصلی بساز (CREATE در MySQL تراکنش را کرش می‌کند).
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_number_seq (prefix VARCHAR(20) NOT NULL PRIMARY KEY, seq INT UNSIGNED NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $exception) {
    // جدول از قبل وجود دارد یا دسترسی DDL محدود است؛ شماره‌گذاری در صورت نبودِ جدول دوباره تلاش می‌کند.
}

try {
    $pdo->beginTransaction();

    // ─── ۱) واحدهای سازمانی طرح (departments) ───
    $deptByCode = [];
    $departmentInsert = $pdo->prepare('INSERT INTO departments (name, code, is_active) VALUES (?, ?, 1)');
    foreach ([['فناوری اطلاعات', 'IT'], ['منابع انسانی', 'HR'], ['مالی', 'FIN'], ['اداری', 'ADM']] as [$name, $code]) {
        $st = $pdo->prepare('SELECT id FROM departments WHERE code = ?');
        $st->execute([$code]);
        $id = $st->fetchColumn();
        if ($id === false) {
            $departmentInsert->execute([$name, $code]);
            $id = (int) $pdo->lastInsertId();
            $created['departments'][] = (int) $id;
        }
        $deptByCode[$code] = (int) $id;
    }

    // ─── ۲) واحدهای سازمانی (org_units) زیر واحد عامل شرکت خودمان ───
    $ceoUnit = $pdo->query("SELECT id FROM org_units WHERE company_id = 1 AND unit_type = 'ceo' ORDER BY id LIMIT 1")->fetchColumn();
    if ($ceoUnit === false) {
        $pdo->exec("INSERT INTO org_units (company_id, name, code, unit_type, is_primary_admin, is_active, sort_order) VALUES (1, 'مدیرعامل', 'CEO', 'ceo', 0, 1, 0)");
        $ceoUnit = (int) $pdo->lastInsertId();
    } else {
        $ceoUnit = (int) $ceoUnit;
    }
    $orgUnitIds = [];
    $unitInsert = $pdo->prepare("INSERT INTO org_units (company_id, name, code, parent_id, unit_type, is_active, sort_order) VALUES (1, ?, ?, ?, 'department', 1, ?)");
    foreach ([['فناوری اطلاعات', 'ORG-IT', 1], ['منابع انسانی', 'ORG-HR', 2], ['مالی و اداری', 'ORG-FIN', 3], ['پشتیبانی فنی', 'ORG-SUP', 4], ['کارگاه مرکزی', 'ORG-WS', 5]] as [$name, $code, $sort]) {
        $st = $pdo->prepare('SELECT id FROM org_units WHERE company_id = 1 AND code = ?');
        $st->execute([$code]);
        $id = $st->fetchColumn();
        if ($id === false) {
            $unitInsert->execute([$name, $code, $ceoUnit, $sort]);
            $id = (int) $pdo->lastInsertId();
            $created['org_units'][] = (int) $id;
        }
        $orgUnitIds[$code] = (int) $id;
    }

    // ─── ۳) کاربران نمونه با نقش‌های اصلی ───
    // [username, full_name, role, dept_code, org_code, handling('it'|'support'|null), is_it_agent]
    $userSeeds = [
        ['seed_admin', 'مدیر تست سامانه', 'admin', 'IT', 'ORG-IT', null, 1],
        ['seed_it_manager', 'علی اکبری (مدیر فناوری اطلاعات)', 'manager', 'IT', 'ORG-IT', null, 1],
        ['seed_hr_manager', 'کاوه یوسفی (مدیر منابع انسانی)', 'manager', 'HR', 'ORG-HR', null, 0],
        ['seed_support_manager', 'مهدی رضایی (مدیر پشتیبانی)', 'support_manager', 'ADM', 'ORG-SUP', 'support', 0],
        ['seed_supervisor', 'زهرا موسوی (ناظر رسیدگی)', 'supervisor', 'IT', 'ORG-IT', null, 1],
        ['seed_inspector', 'حسین کاظمی (بازرس)', 'inspector', 'IT', 'ORG-IT', null, 0],
        ['seed_guard', 'پست نگهبانی (ورودی اصلی)', 'guard', 'ADM', 'ORG-WS', null, 0],
        ['seed_it_agent1', 'رضا کریمی (کارشناس فناوری)', 'agent', 'IT', 'ORG-IT', 'it', 1],
        ['seed_it_agent2', 'سارا نوری (کارشناس فناوری)', 'agent', 'IT', 'ORG-IT', 'it', 1],
        ['seed_support_agent', 'مریم صادقی (کارشناس پشتیبانی)', 'agent', 'ADM', 'ORG-SUP', 'support', 0],
        ['seed_user_amir', 'امیر حسینی', 'user', 'HR', 'ORG-HR', null, 0],
        ['seed_user_leila', 'لیلا قاسمی', 'user', 'FIN', 'ORG-FIN', null, 0],
        ['seed_user_negar', 'نگار جعفری', 'user', 'ADM', 'ORG-FIN', null, 0],
        ['seed_user_bahram', 'بهرام صادقی', 'user', 'IT', 'ORG-IT', null, 0],
        ['seed_user_sara', 'سارا ملکی', 'user', 'FIN', 'ORG-FIN', null, 0],
    ];
    $handlingUnits = $pdo->query("SELECT code, id FROM handling_units WHERE code IN ('it','support')")->fetchAll(PDO::FETCH_KEY_PAIR);
    if (!isset($handlingUnits['it']) || !isset($handlingUnits['support'])) {
        $pdo->exec("INSERT IGNORE INTO handling_units (code, name, is_active) VALUES ('it', 'واحد فناوری اطلاعات', 1), ('support', 'واحد پشتیبانی', 1)");
        $handlingUnits = $pdo->query("SELECT code, id FROM handling_units WHERE code IN ('it','support')")->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    $userIds = [];
    $userInsert = $pdo->prepare('INSERT INTO users (company_id, username, password_hash, full_name, email, employee_number, national_code, phone, department, department_id, requesting_unit_id, org_unit_id, handling_unit_id, is_it_agent, role, auth_source, is_active, must_change_password) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'local\', 1, 0)');
    $deptNames = ['IT' => 'فناوری اطلاعات', 'HR' => 'منابع انسانی', 'FIN' => 'مالی', 'ADM' => 'اداری'];
    $seq = 0;
    foreach ($userSeeds as [$username, $fullName, $role, $deptCode, $orgCode, $handling, $isItAgent]) {
        $seq++;
        $st = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $st->execute([$username]);
        $existing = $st->fetchColumn();
        if ($existing !== false) {
            fwrite(STDERR, "خطا: حساب {$username} از قبل وجود دارد ولی دادهٔ نمونه ثبت نشده. برای جلوگیری از تداخل، ابتدا پاک کنید: --fresh\n");
            throw new RuntimeException("کاربر {$username} از قبل وجود دارد.");
        }
        $employeeNumber = sprintf('SEED-%04d', $seq);
        $nationalCode = sprintf('001%07d', $seq);
        $userInsert->execute([
            $username, $passwordHash, $fullName, $username . '@seed.test.local',
            $employeeNumber, $nationalCode, '091200000' . str_pad((string) $seq, 2, '0', STR_PAD_LEFT),
            $deptNames[$deptCode], $deptByCode[$deptCode], $orgUnitIds[$orgCode] ?? null, $orgUnitIds[$orgCode] ?? null,
            $handling ? (int) $handlingUnits[$handling] : null,
            $isItAgent, $role,
        ]);
        $userIds[$username] = (int) $pdo->lastInsertId();
        $created['users'][] = $userIds[$username];
    }
    // مدیران واحدها
    $unitManager = $pdo->prepare('UPDATE org_units SET manager_user_id = ? WHERE id = ?');
    $unitManager->execute([$userIds['seed_it_manager'], $orgUnitIds['ORG-IT']]);
    $unitManager->execute([$userIds['seed_hr_manager'], $orgUnitIds['ORG-HR']]);
    $unitManager->execute([$userIds['seed_support_manager'], $orgUnitIds['ORG-SUP']]);
    $deptManager = $pdo->prepare('UPDATE departments SET manager_user_id = ? WHERE id = ?');
    $deptManager->execute([$userIds['seed_it_manager'], $deptByCode['IT']]);
    $deptManager->execute([$userIds['seed_hr_manager'], $deptByCode['HR']]);

    // ─── ۴) شناسنامهٔ اموال نمونه ───
    $assetSeeds = [
        ['SEED-PC-001', 'Desktop', 'آریا سیستم', 'فاراد-2400', 'seed_user_amir'],
        ['SEED-PC-002', 'Desktop', 'آریا سیستم', 'فاراد-2400', 'seed_user_leila'],
        ['SEED-PC-003', 'Desktop', 'رایانه پارسه', 'پارس-3600', 'seed_user_negar'],
        ['SEED-PC-004', 'Desktop', 'فن‌آوا', 'فن‌آوا-5200', 'seed_user_bahram'],
        ['SEED-PC-005', 'Desktop', 'آریا سیستم', 'فاراد-2400', 'seed_user_sara'],
        ['SEED-PC-006', 'Desktop', 'رایانه پارسه', 'پارس-3600', 'seed_it_agent1'],
        ['SEED-PC-007', 'Desktop', 'فن‌آوا', 'فن‌آوا-5200', 'seed_it_agent2'],
        ['SEED-PC-008', 'Desktop', 'آریا سیستم', 'فاراد-2400', 'seed_hr_manager'],
        ['SEED-LTP-001', 'Laptop', 'فن‌آوا', 'نوت‌بوک-14', 'seed_it_manager'],
        ['SEED-LTP-002', 'Laptop', 'رایانه پارسه', 'نوت‌بوک-15', 'seed_support_manager'],
        ['SEED-LTP-003', 'Laptop', 'آریا سیستم', 'نوت‌بوک-14', 'seed_supervisor'],
        ['SEED-SRV-001', 'Server', 'فن‌آوا', 'رکمونت-2U', null],
    ];
    $assetIds = [];
    $assetInsert = $pdo->prepare("INSERT INTO assets (asset_tag, hostname, hostname_key, serial_number, computer_type, manufacturer, model, asset_number, owner_user_id, department_id, operating_system, ip_address, source, lifecycle_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Windows 11 Pro', ?, 'manual', ?)");
    $assetSeq = 0;
    foreach ($assetSeeds as [$tag, $type, $mfr, $model, $owner]) {
        $assetSeq++;
        $ownerId = $owner ? $userIds[$owner] : null;
        $departmentId = $type === 'Server' ? $deptByCode['IT'] : ($owner ? null : $deptByCode['IT']);
        if ($ownerId !== null) {
            $st = $pdo->prepare('SELECT department_id FROM users WHERE id = ?');
            $st->execute([$ownerId]);
            $departmentId = (int) ($st->fetchColumn() ?: $deptByCode['IT']);
        }
        $assetInsert->execute([
            $tag, strtolower($tag), strtolower($tag), 'SN-SEED-' . str_pad((string) $assetSeq, 5, '0', STR_PAD_LEFT),
            $type, $mfr, $model, 'ASY-' . str_pad((string) $assetSeq, 6, '0', STR_PAD_LEFT),
            $ownerId, $departmentId, sprintf('10.10.%d.%d', 1 + intdiv($assetSeq, 250), $assetSeq), $ownerId !== null ? 'assigned' : 'in_stock',
        ]);
        $assetIds[$tag] = (int) $pdo->lastInsertId();
        $created['assets'][] = $assetIds[$tag];
    }

    // ─── ۵) درخواست‌های نمونه در همهٔ وضعیت‌های گردش کار ───
    // [subject, description, group, category_code, priority, status, requester, assignee, type, asset_tag|null, daysAgo, slaMinutes, cancel(array|null)]
    $ticketSeeds = [
        ['کندی شدید رایانه مالی', 'از صبح سیستم بخش مالی هنگ می‌کند و برنامهٔ «سما» بسته می‌شود.', 'it', 'IT-CAT-HW', 'urgent', 'manager_review', 'seed_user_leila', null, 'incident', 'SEED-PC-002', 0, 240, null],
        ['درخواست نصب نرم‌افزار آفیس', 'لطفاً آفیس ۲۰۲۴ روی سیستم جدید نصب شود.', 'it', 'IT-CAT-SW', 'normal', 'manager_review', 'seed_user_negar', null, 'request', null, 1, 1440, null],
        ['پرینتر بخش اداری چاپ نمی‌کند', 'پرینتر HP در راهروی اداری گیر می‌کند و خطای کاغذ می‌دهد.', 'support', 'SUP-CAT-STAT', 'normal', 'manager_review', 'seed_user_negar', null, 'incident', null, 1, 1440, null],
        ['درخواست دسترسی به سامانهٔ تنخواه', 'نیاز به دسترسی مشاهدهٔ گزارش تنخواه دارم.', 'it', 'IT-CAT-ACC', 'normal', 'new', 'seed_user_sara', null, 'request', null, 0, 1440, null],
        ['خرابی موس و صفحه‌کلید', 'کلیدهای صفحه‌کلید پشتی می‌چسبند.', 'it', 'IT-CAT-PERIPH', 'normal', 'new', 'seed_user_amir', null, 'request', 'SEED-PC-001', 0, 1440, null],
        ['مشکل اتصال به VPN از منزل', 'VPN وصل می‌شود ولی دسترسی به منابع داخلی قطع است.', 'it', 'IT-CAT-NET', 'urgent', 'assigned', 'seed_user_bahram', 'seed_it_agent1', 'incident', 'SEED-PC-004', 2, 240, null],
        ['درخواست تعویض مانیتور معیوب', 'مانیتور بخش فروش تصویر سوسو می‌زند و نیاز به تعویض دارد.', 'support', 'SUP-CAT-OFFICE', 'normal', 'assigned', 'seed_user_negar', 'seed_support_agent', 'request', null, 2, 1440, null],
        ['خطای نرم‌افزار حضور و غیاب', 'پس از به‌روزرسانی دیتابیس، نرم‌افزار خطای جدول وارد می‌کند.', 'it', 'IT-CAT-SW', 'critical', 'in_progress', 'seed_user_amir', 'seed_it_agent1', 'incident', 'SEED-PC-001', 3, 240, null],
        ['نصب آنتی‌ویروس روی لپ‌تاپ جدید', 'سیستم تصویربرداری به شبکهٔ داخلی وصل شده؛ آنتی‌ویروس لازم است.', 'it', 'IT-CAT-SETUP', 'normal', 'in_progress', 'seed_it_manager', 'seed_it_agent2', 'request', 'SEED-LTP-001', 3, 1440, null],
        ['نشتی کولر کارگاه', 'کولر آبی کارگاه مرکزی نشتی دارد و آب روی بردهای کنار می‌ریزد.', 'support', 'SUP-CAT-UTIL', 'urgent', 'in_progress', 'seed_guard', 'seed_support_agent', 'incident', null, 2, 240, null],
        ['درخواست دسترسی مدیر منابع انسانی به گزارش‌ها', 'نیاز به دسترسی مشاهده به داشبورد منابع انسانی دارم.', 'it', 'IT-CAT-ACC', 'normal', 'in_progress', 'seed_hr_manager', 'seed_it_agent2', 'request', null, 4, 1440, null],
        ['بازیابی ایمیل حذف‌شده', 'ایمیل‌های هفتهٔ گذشته به‌اشتباه پاک شده‌اند.', 'it', 'IT-CAT-BACKUP', 'urgent', 'waiting_user', 'seed_user_sara', 'seed_it_agent1', 'incident', 'SEED-PC-005', 5, 240, null],
        ['تأییدیه تنظیم جایگزین برای سیستم پرسنل', 'نقش جایگزین برای کاربران غایب تنظیم شد؛ لطفاً تست کنید.', 'it', 'IT-CAT-ACC', 'normal', 'waiting_user', 'seed_user_leila', 'seed_it_agent2', 'request', null, 5, 1440, null],
        ['نصب مجدد ویندوز کارگاه', 'سیستم کارگاه آلوده به بدافزار شده و نیاز به نصب مجدد دارد.', 'it', 'IT-CAT-SETUP', 'urgent', 'pending', 'seed_user_bahram', 'seed_it_agent1', 'incident', 'SEED-PC-004', 6, 240, null],
        // دو درخواست با «لغو در انتظار تصمیم مسئول رسیدگی» — مسیر اصلی تست بازسازی دهم:
        ['مشکل صفحه‌نمایش لپ‌تاپ مدیریتی', 'صفحه‌نمایش لپ‌تاپ چشمک می‌زند و رنگ‌ها اشتباه نمایش داده می‌شوند.', 'it', 'IT-CAT-PERIPH', 'normal', 'in_progress', 'seed_it_manager', 'seed_it_agent2', 'incident', 'SEED-LTP-001', 2, 1440, ['seed_it_manager', 'موضوع برطرف شد با ری‌استارت؛ نیازی به ادامهٔ رسیدگی نیست.']],
        ['تعمیر کولر اتاق جلسات', 'کولر اتاق جلسات در روزهای گرم روشن نمی‌شود.', 'support', 'SUP-CAT-UTIL', 'normal', 'assigned', 'seed_user_negar', 'seed_support_agent', 'incident', null, 1, 1440, ['seed_user_negar', 'هوا خنک شده و نیاز به تعمیر نداریم؛ لطفاً لغو شود.']],
        ['تعویض کارتریج پرینتر', 'کارتریج پرینتر داخلی نصف شده و کیفیت چاپ افت کرده است.', 'support', 'SUP-CAT-STAT', 'normal', 'resolved', 'seed_user_amir', 'seed_support_agent', 'request', null, 7, 240, null],
        ['تنظیم ایمیل روی گوشی', 'ایمیل سازمانی روی گوشی جدید تنظیم شود.', 'it', 'IT-CAT-ACC', 'normal', 'resolved', 'seed_user_leila', 'seed_it_agent2', 'request', null, 6, 1440, null],
        ['مشکل دسترسی به درایو مشترک', 'پس از تغییر رمز عبور، درایو مشترک سازمان باز نمی‌شود.', 'it', 'IT-CAT-ACC', 'urgent', 'resolved', 'seed_user_sara', 'seed_it_agent1', 'incident', null, 8, 240, null],
        ['درخواست قفسه‌بندی اتاق اسناد', 'برای طبقه‌بندی اسناد مالی قفسه‌های جدید لازم است.', 'support', 'SUP-CAT-FURN', 'normal', 'resolved', 'seed_user_negar', 'seed_support_agent', 'request', null, 9, 1440, null],
        ['افزایش حافظهٔ رم سیستم مالی', 'نرم‌افزار «سما» با حافظهٔ فعلی کند است.', 'it', 'IT-CAT-HW', 'normal', 'closed', 'seed_user_leila', 'seed_it_agent1', 'request', 'SEED-PC-002', 12, 1440, null],
        ['پشتیبان‌گیری سرور فایل', 'فایل‌های اشتراکی قبل از جابه‌جایی سرور پشتیبان گرفته شوند.', 'it', 'IT-CAT-BACKUP', 'critical', 'closed', 'seed_it_manager', 'seed_it_agent1', 'request', 'SEED-SRV-001', 14, 240, null],
        ['نصب درایور اسکنر بایگانی', 'اسکنر بایگانی پس از تعویض مادربرد شناسایی نمی‌شود.', 'it', 'IT-CAT-DRIVER', 'normal', 'closed', 'seed_user_negar', 'seed_it_agent2', 'request', null, 15, 1440, null],
        ['تمیزکاری اتاق سرور', 'گرد و غبار اتاق سرور باید جمع‌آوری شود.', 'support', 'SUP-CAT-CLEAN', 'normal', 'closed', 'seed_guard', 'seed_support_agent', 'request', null, 16, 1440, null],
        ['تنظیم VPN برای مدیرعامل', 'دسترسی VPN روی لپ‌تاپ مدیرعامل تنظیم و تست شود.', 'it', 'IT-CAT-NET', 'urgent', 'closed', 'seed_it_manager', 'seed_it_agent1', 'request', 'SEED-LTP-001', 18, 240, null],
        ['درخواست میز و صندلی جدید', 'برای کارشناس تازه‌وارد میز و صندلی اداری لازم است.', 'support', 'SUP-CAT-FURN', 'normal', 'closed', 'seed_hr_manager', 'seed_support_agent', 'request', null, 20, 1440, null],
        ['درخواست سرور تست برای تیم توسعه', 'برای تست نسخهٔ جدید نیاز به سرور آزمایشی داریم.', 'it', 'IT-CAT-SETUP', 'normal', 'cancelled', 'seed_user_bahram', null, 'request', null, 10, 1440, null],
        ['درخواست سرویس دوره‌ای چاپگر', 'چاپگر راهروی دوم بدون مشکل چاپ می‌کند؛ درخواست تکراری ثبت شده.', 'support', 'SUP-CAT-STAT', 'normal', 'rejected', 'seed_user_negar', null, 'request', null, 9, 1440, null],
        ['نصب نرم‌افزار حسابداری روی لپ‌تاپ', 'نرم‌افزار حسابداری روی لپ‌تاپ مسئول مالی تنظیم شود.', 'it', 'IT-CAT-SW', 'normal', 'in_progress', 'seed_user_sara', 'seed_it_agent1', 'request', null, 1, 1440, null],
        ['تعمیر درب ورودی انبار', 'درب ورودی انبار به‌درستی بسته نمی‌شود و قفل می‌کند.', 'support', 'SUP-CAT-UTIL', 'urgent', 'assigned', 'seed_guard', 'seed_support_agent', 'incident', null, 1, 240, null],
    ];

    $categoryIds = $pdo->query('SELECT code, id FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);
    $serviceIds = $pdo->query('SELECT code, id FROM service_catalog')->fetchAll(PDO::FETCH_KEY_PAIR);
    $ticketIds = [];
    $ticketInsert = $pdo->prepare('INSERT INTO tickets (ticket_no, subject, description, category_id, service_id, ticket_type, service_group, department_id, requesting_unit_id, asset_id, priority, status, requester_id, assigned_to, assigned_at, first_response_at, resolved_at, closed_at, rejected_at, cancel_requested_at, cancel_requested_by, cancel_request_reason, due_at, sla_minutes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $serviceByGroupCategory = [
        'IT-CAT-HW' => 'IT-COMPUTER', 'IT-CAT-SW' => 'IT-SOFTWARE', 'IT-CAT-NET' => 'IT-COMPUTER',
        'IT-CAT-ACC' => 'IT-COMPUTER', 'IT-CAT-BACKUP' => 'IT-COMPUTER', 'IT-CAT-SETUP' => 'IT-SOFTWARE',
        'IT-CAT-PERIPH' => 'IT-PERIPHERAL', 'IT-CAT-PRINT' => 'IT-PRINTER', 'IT-CAT-DRIVER' => 'IT-DRIVER',
        'SUP-CAT-OFFICE' => null, 'SUP-CAT-UTIL' => null, 'SUP-CAT-FURN' => null,
        'SUP-CAT-CLEAN' => null, 'SUP-CAT-STAT' => null, 'SUP-CAT-GEN' => null, 'SUP-CAT-MISC' => null,
    ];

    foreach ($ticketSeeds as $i => $ticketSeed) {
        [$subject, $description, $group, $catCode, $priority, $status, $requester, $assignee, $type, $assetTag, $daysAgo, $slaMinutes, $cancel] = $ticketSeed;
        $requesterId = $userIds[$requester];
        $assigneeId = $assignee ? $userIds[$assignee] : null;
        $assetId = $assetTag ? ($assetIds[$assetTag] ?? null) : null;
        $categoryId = $categoryIds[$catCode] ?? null;
        $serviceCode = $serviceByGroupCategory[$catCode] ?? null;
        $serviceId = $serviceCode && isset($serviceIds[$serviceCode]) ? (int) $serviceIds[$serviceCode] : null;
        $st = $pdo->prepare('SELECT department_id, org_unit_id FROM users WHERE id = ?');
        $st->execute([$requesterId]);
        $requesterRow = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        $departmentId = isset($requesterRow['department_id']) ? (int) $requesterRow['department_id'] : 0;
        $departmentId = $departmentId > 0 ? $departmentId : null;
        $requestingUnitId = seed_col_exists($pdo, 'tickets', 'requesting_unit_id') && !empty($requesterRow['org_unit_id'])
            ? (int) $requesterRow['org_unit_id']
            : null;
        $createdAt = (new DateTimeImmutable('now'))
            ->modify('-' . $daysAgo . ' days')
            ->setTime(8 + ($i % 9), ($i * 17) % 60)
            ->format('Y-m-d H:i:s');
        $assignedAt = $assigneeId ? (new DateTimeImmutable($createdAt))->modify('+4 hours')->format('Y-m-d H:i:s') : null;
        $firstResponseAt = in_array($status, ['in_progress', 'waiting_user', 'pending', 'resolved', 'closed'], true)
            ? (new DateTimeImmutable($createdAt))->modify('+5 hours')->format('Y-m-d H:i:s')
            : null;
        $resolvedAt = in_array($status, ['resolved', 'closed'], true)
            ? (new DateTimeImmutable($createdAt))->modify('+2 days')->format('Y-m-d H:i:s')
            : null;
        $closedAt = $status === 'closed' ? (new DateTimeImmutable($createdAt))->modify('+3 days')->format('Y-m-d H:i:s') : null;
        $rejectedAt = $status === 'rejected' ? (new DateTimeImmutable($createdAt))->modify('+1 day')->format('Y-m-d H:i:s') : null;
        $cancelRequestedAt = $cancel ? (new DateTimeImmutable('now'))->modify('-3 hours')->format('Y-m-d H:i:s') : null;
        $cancelRequestedBy = $cancel ? $userIds[$cancel[0]] : null;
        $cancelReason = $cancel ? $cancel[1] : null;
        $dueAt = (new DateTimeImmutable($createdAt))->modify('+' . (int) $slaMinutes . ' minutes')->format('Y-m-d H:i:s');
        $ticketNo = seed_next_ticket_no($pdo, $group === 'it' ? 'IT' : 'SUP');
        $ticketInsert->execute([
            $ticketNo, $subject, $description, $categoryId, $serviceId, $type, $group, $departmentId, $requestingUnitId,
            $assetId, $priority, $status, $requesterId, $assigneeId, $assignedAt, $firstResponseAt, $resolvedAt, $closedAt,
            $rejectedAt, $cancelRequestedAt, $cancelRequestedBy, $cancelReason, $dueAt, (int) $slaMinutes, $createdAt, $createdAt,
        ]);
        $ticketId = (int) $pdo->lastInsertId();
        $ticketIds[] = $ticketId;

        // پیام ثبت‌کننده + پاسخ کارشناس در صورت وجود
        $messageInsert = $pdo->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal, created_at) VALUES (?, ?, ?, ?, ?)');
        $messageInsert->execute([$ticketId, $requesterId, $description, 0, $createdAt]);
        if ($assigneeId && in_array($status, ['in_progress', 'waiting_user', 'pending', 'resolved', 'closed'], true)) {
            $replyAt = (new DateTimeImmutable($createdAt))->modify('+6 hours')->format('Y-m-d H:i:s');
            $reply = $status === 'resolved' || $status === 'closed'
                ? 'رسیدگی انجام شد و نتیجه در همین درخواست ثبت شده است؛ اگر مشکلی باقی مانده بفرمایید.'
                : 'در حال بررسی هستم و نتیجه را همین‌جا ثبت می‌کنم.';
            $messageInsert->execute([$ticketId, $assigneeId, $reply, 0, $replyAt]);
        }
        if ($status === 'waiting_user') {
            $messageInsert->execute([$ticketId, $assigneeId, 'برای ادامه، لطفاً اطلاعات بیشتری دربارهٔ خطا بفرستید (تصویر صفحه کافی است).', 0, (new DateTimeImmutable($createdAt))->modify('+7 hours')->format('Y-m-d H:i:s')]);
        }
        if ($cancel) {
            $cancelMessageAt = (new DateTimeImmutable('now'))->modify('-3 hours')->format('Y-m-d H:i:s');
            $messageInsert->execute([$ticketId, $cancelRequestedBy, 'درخواست لغو ثبت شد: ' . $cancelReason, 0, $cancelMessageAt]);
        }
        if ($i === 7) { // یک یادداشت داخلی برای نمایش تمایز پیام داخلی
            $messageInsert->execute([$ticketId, $assigneeId, 'یادداشت داخلی: این خطا به‌خاطر به‌روزرسانی دیتابیس رخ داده؛ مستندات پیوست شد.', 1, (new DateTimeImmutable($createdAt))->modify('+8 hours')->format('Y-m-d H:i:s')]);
        }

        // رویدادهای گردش کار (برای خط زمانی جزئیات درخواست)
        $eventInsert = $pdo->prepare('INSERT INTO ticket_events (ticket_id, user_id, event_type, from_status, to_status, details, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $eventInsert->execute([$ticketId, $requesterId, 'created', null, $status, json_encode(['source' => 'seed_test_data'], JSON_UNESCAPED_UNICODE), $createdAt]);
        if ($assigneeId) {
            $eventInsert->execute([$ticketId, $assigneeId, 'assigned', 'manager_review', 'assigned', json_encode(['assigned_to' => $assigneeId], JSON_UNESCAPED_UNICODE), $assignedAt]);
        }
        if ($firstResponseAt) {
            $eventInsert->execute([$ticketId, $assigneeId, 'status_changed', 'assigned', in_array($status, ['resolved', 'closed'], true) ? 'in_progress' : $status, json_encode([], JSON_UNESCAPED_UNICODE), $firstResponseAt]);
        }
        if ($resolvedAt) {
            $eventInsert->execute([$ticketId, $assigneeId, 'resolved', 'in_progress', 'resolved', json_encode([], JSON_UNESCAPED_UNICODE), $resolvedAt]);
        }
        if ($closedAt) {
            $eventInsert->execute([$ticketId, $requesterId, 'closed', 'resolved', 'closed', json_encode([], JSON_UNESCAPED_UNICODE), $closedAt]);
        }
        if ($cancel) {
            $eventInsert->execute([$ticketId, $cancelRequestedBy, 'cancellation_requested', $status, $status, json_encode(['reason' => $cancelReason, 'actor' => 'requester'], JSON_UNESCAPED_UNICODE), $cancelRequestedAt]);
        }

        // امتیاز برای درخواست‌های بسته‌شده
        if ($status === 'closed') {
            $score = [5, 4, 5, 3, 5, 4][$i % 6];
            $ratingComment = [5 => 'عالی و سریع', 4 => 'خوب بود', 3 => 'قابل قبول'][$score];
            $pdo->prepare('INSERT INTO ticket_ratings (ticket_id, agent_id, requester_id, score, comment, created_at) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$ticketId, $assigneeId, $requesterId, $score, $ratingComment, $closedAt]);
        }

        // اعلان‌ها برای مسئول رسیدگی و ثبت‌کننده
        $notificationInsert = $pdo->prepare("INSERT INTO notifications (user_id, ticket_id, notification_type, title, body, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($assigneeId) {
            $notificationInsert->execute([$assigneeId, $ticketId, 'ticket_assigned', 'درخواست جدید به شما ارجاع شد', 'درخواست «' . $subject . '» به شما ارجاع شد.', 0, $assignedAt ?: $createdAt]);
        }
        if ($cancel) {
            $notificationInsert->execute([$assigneeId, $ticketId, 'ticket_cancellation_requested', 'درخواست لغو درخواست ثبت شد', 'ثبت‌کننده خواستار لغو درخواست «' . $subject . '» است. تا تصمیم شما، تغییر وضعیت متوقف است.', 0, $cancelRequestedAt]);
        }
        if ($closedAt) {
            $notificationInsert->execute([$requesterId, $ticketId, 'ticket_closed', 'درخواست شما بسته شد', 'درخواست «' . $subject . '» بسته شد. امتیاز شما ثبت می‌شود.', 1, $closedAt]);
        }
    }
    $created['tickets'] = $ticketIds;
    $summary['tickets'] = count($ticketIds);

    // ─── ۶) پایگاه دانش نمونه ───
    $kbSeeds = [
        ['راهنمای اتصال به VPN سازمان', 'seed-vpn-guide', "۱) نرم‌افزار VPN را اجرا کنید.\n۲) آدرس سرور: vpn.test.local\n۳) با کاربری دامین خود وارد شوید.\n۴) پس از اتصال، منابع داخلی در Explorer قابل دسترسی‌اند.", 'دسترسی از راه دور'],
        ['راه‌اندازی مجدد سرویس چاپ شبکه', 'seed-print-spooler', "اگر پرینتر شبکه چاپ نمی‌کند:\n۱) سرویس Print Spooler را روی سرور چاپ Restart کنید.\n۲) صف چاپ را پاک کنید.\n۳) تست چاپ بگیرید؛ اگر حل نشد به پشتیبانی تیکت بزنید.", 'چاپ و اسکن'],
        ['چه زمانی تیکت لغو می‌شود؟', 'seed-cancel-policy', "تا پیش از ارجاع، ثبت‌کننده می‌تواند درخواست را مستقیماً لغو کند. پس از ارجاع به کارشناس، ثبت‌کننده فقط «درخواست لغو» ثبت می‌کند و تصمیم نهایی با مسئول رسیدگی (تأیید یا رد) است.", 'فرایندها'],
    ];
    foreach ($kbSeeds as [$title, $slug, $body, $category]) {
        $st = $pdo->prepare('SELECT id FROM knowledge_articles WHERE slug = ?');
        $st->execute([$slug]);
        if ($st->fetchColumn() !== false) {
            continue; // اجرای تکراری: از ساخت دوباره رد می‌شود
        }
        $pdo->prepare('INSERT INTO knowledge_articles (title, slug, body, category, is_published, created_by) VALUES (?, ?, ?, ?, 1, ?)')
            ->execute([$title, $slug, $body, $category, $userIds['seed_admin']]);
        $created['knowledge_articles'][] = (int) $pdo->lastInsertId();
    }

    // ─── ۷) تعطیلات پیش‌رو (برای تقویم سامانه) ───
    foreach ([['+10 days', 'تعطیلی آزمایشی — تعطیلات تست'], ['+24 days', 'تعطیلی آزمایشی — میلاد (نمونه)'], ['+45 days', 'تعطیلی آزمایشی — مناسبت (نمونه)']] as [$offset, $title]) {
        $date = (new DateTimeImmutable('now'))->modify($offset)->format('Y-m-d');
        $st = $pdo->prepare('SELECT holiday_date FROM holidays WHERE holiday_date = ?');
        $st->execute([$date]);
        if ($st->fetchColumn() !== false) {
            continue;
        }
        $pdo->prepare('INSERT INTO holidays (holiday_date, title, is_active, created_by) VALUES (?, ?, 1, ?)')
            ->execute([$date, $title, $userIds['seed_admin']]);
        $created['holiday_dates'][] = $date;
    }

    // ─── ۸) ماژول غذا: کاتالوگ، تقویم ماه جاری، سفارش‌ها، گروه سلف، مهمان ───
    $foodSeeds = [
        ['قورمه‌سبزی با چلو', 'main'],
        ['چلوکباب کوبیده', 'main'],
        ['قیمه با چلو', 'main'],
        ['خوراک ماکارونی', 'main'],
        ['خوراک مرغ و سبزیجات', 'main'],
        ['عدسی با نان', 'main'],
    ];
    $catalogIds = [];
    foreach ($foodSeeds as [$foodName, $category]) {
        $st = $pdo->prepare('SELECT id FROM food_catalog WHERE food_name = ?');
        $st->execute([$foodName]);
        $id = $st->fetchColumn();
        if ($id === false) {
            $pdo->prepare('INSERT INTO food_catalog (food_name, category, active, created_by) VALUES (?, ?, 1, ?)')
                ->execute([$foodName, $category, $userIds['seed_admin']]);
            $id = (int) $pdo->lastInsertId();
            $created['food_catalog'][] = (int) $id;
        }
        $catalogIds[] = (int) $id;
    }

    $monthStart = new DateTimeImmutable(date('Y-m-01'));
    $monthEnd = $monthStart->modify('last day of this month');
    $openDays = [];
    for ($day = $monthStart; $day <= $monthEnd; $day = $day->modify('+1 day')) {
        if ((int) $day->format('N') >= 6) {
            continue; // پنجشنبه و جمعه تعطیل سلف
        }
        $date = $day->format('Y-m-d');
        $st = $pdo->prepare("SELECT id FROM food_calendar WHERE food_date = ? AND meal_type = 'lunch'");
        $st->execute([$date]);
        $calendarId = $st->fetchColumn();
        if ($calendarId === false) {
            $pdo->prepare("INSERT INTO food_calendar (food_date, meal_type, order_status, note, created_by) VALUES (?, 'lunch', 'open', 'ثبت‌شده با دادهٔ نمونهٔ تست', ?)")
                ->execute([$date, $userIds['seed_admin']]);
            $calendarId = (int) $pdo->lastInsertId();
            $created['food_calendars'][] = $calendarId;
        }
        $openDays[] = ['date' => $date, 'calendar_id' => (int) $calendarId];
        foreach ($catalogIds as $sortOrder => $foodId) {
            $st = $pdo->prepare('SELECT id FROM food_calendar_items WHERE calendar_id = ? AND food_id = ?');
            $st->execute([(int) $calendarId, $foodId]);
            if ($st->fetchColumn() !== false) {
                continue;
            }
            $pdo->prepare('INSERT INTO food_calendar_items (calendar_id, food_id, sort_order, active) VALUES (?, ?, ?, 1)')
                ->execute([(int) $calendarId, $foodId, $sortOrder + 1]);
            $created['food_calendar_items'][] = (int) $pdo->lastInsertId();
        }
    }

    // سفارش‌ها: برای روزهای گذشته و امروزِ ماه جاری، هر کاربر سلف یک سفارش فعال
    $orderFoodItem = $pdo->prepare('SELECT id FROM food_calendar_items WHERE calendar_id = (SELECT id FROM food_calendar WHERE food_date = ? AND meal_type = \'lunch\') AND active = 1 ORDER BY sort_order LIMIT 1');
    $orderInsert = $pdo->prepare("INSERT INTO food_orders (employee_id, calendar_item_id, food_date, meal_type, created_by, status, created_at) VALUES (?, ?, ?, 'lunch', ?, 'active', ?)");
    $logInsert = $pdo->prepare("INSERT INTO food_order_logs (order_id, calendar_id, action, user_id, old_value, new_value, reason, created_at) VALUES (?, NULL, 'create', ?, NULL, ?, 'seed_test_data', ?)");
    $orderUsernames = ['seed_user_amir', 'seed_user_leila', 'seed_user_negar', 'seed_user_bahram', 'seed_user_sara', 'seed_it_agent1', 'seed_it_agent2', 'seed_support_agent'];
    $today = new DateTimeImmutable('today');
    $ordersMade = 0;
    foreach ($openDays as $openDay) {
        $date = $openDay['date'];
        if (new DateTimeImmutable($date) > $today) {
            continue; // فقط روزهای گذشته و امروز سفارش دارند
        }
        $orderFoodItem->execute([$date]);
        $calendarItemId = $orderFoodItem->fetchColumn();
        if ($calendarItemId === false) {
            continue;
        }
        foreach ($orderUsernames as $username) {
            $employeeId = $userIds[$username];
            $exists = $pdo->prepare("SELECT id FROM food_orders WHERE employee_id = ? AND food_date = ? AND meal_type = 'lunch'");
            $exists->execute([$employeeId, $date]);
            if ($exists->fetchColumn() !== false) {
                continue;
            }
            $createdAtOrder = $date . ' 09:00:00';
            $orderInsert->execute([$employeeId, (int) $calendarItemId, $date, $employeeId, 'active', $createdAtOrder]);
            $orderId = (int) $pdo->lastInsertId();
            $created['food_orders'][] = $orderId;
            $logInsert->execute([$orderId, $employeeId, 'seed order', $createdAtOrder]);
            $created['food_order_logs'][] = (int) $pdo->lastInsertId();
            $ordersMade++;
        }
    }
    $summary['food_orders'] = $ordersMade;

    // گروه سلف نمونه با چند عضو
    $st = $pdo->prepare('SELECT id FROM food_ticket_groups WHERE l_uid = ?');
    $st->execute(['SEED-L-0001']);
    $groupId = $st->fetchColumn();
    if ($groupId === false) {
        $pdo->prepare("INSERT INTO food_ticket_groups (title, l_uid, description, status, created_by) VALUES ('گروه نمونه تستی', 'SEED-L-0001', 'گروه سلف ساخته‌شده با دادهٔ نمونهٔ تست', 'active', ?)")
            ->execute([$userIds['seed_admin']]);
        $groupId = (int) $pdo->lastInsertId();
        $created['food_groups'][] = (int) $groupId;
        foreach (['seed_user_amir', 'seed_user_leila', 'seed_user_negar'] as $member) {
            $pdo->prepare('INSERT IGNORE INTO food_ticket_group_members (group_id, user_id) VALUES (?, ?)')
                ->execute([(int) $groupId, $userIds[$member]]);
        }
    }

    // درخواست مهمان نمونه برای هفتهٔ آینده
    $guestDate = (new DateTimeImmutable('now'))->modify('+7 days')->format('Y-m-d');
    $pdo->prepare("INSERT INTO food_guest_requests (request_date, meal_type, organization, guest_count, food_id, requester_name, deputy_unit_id, note, status, created_by) VALUES (?, 'lunch', 'شرکت همکار نمونه', 12, ?, ?, ?, 'ثبت‌شده با دادهٔ نمونهٔ تست', 'active', ?)")
        ->execute([$guestDate, $catalogIds[0], 'دبیر جلسات (نمونه)', $orgUnitIds['ORG-WS'] ?? null, $userIds['seed_admin']]);
    $created['food_guest_requests'][] = (int) $pdo->lastInsertId();

    // ─── ۹) رکوردهای سی‌دی/دی‌وی‌دی نمونه ───
    [$todayJy, $todayJm, $todayJd] = seed_gregorian_to_jalali((int) date('Y'), (int) date('n'), (int) date('j'));
    $jalaliDate = sprintf('%04d/%02d/%02d', $todayJy, $todayJm, $todayJd);
    $jalaliKey = $todayJy * 10000 + $todayJm * 100 + $todayJd;
    $cdDvdSeeds = [
        ['CD', 'OUT', 'SN-TEST-001', 'نرم‌افزار', 'تحویل نسخهٔ تست نرم‌افزار تنخواه به واحد مالی (نمونه)', '1250', 'علی اکبری (مدیر فناوری اطلاعات)', null],
        ['DVD', 'IN', 'SN-TEST-002', 'آرشیو', 'دریافت فایل پشتیبان بایگانی از شرکت همکار (نمونه)', null, null, 'SEED-PC-001'],
    ];
    foreach ($cdDvdSeeds as [$media, $direction, $serial, $infoType, $desc, $exitSheet, $receiver, $broughtByTag]) {
        $broughtBy = $broughtByTag ? 'امیر حسینی' : null;
        $broughtByUser = $broughtByTag ? $userIds['seed_user_amir'] : null;
        $st = $pdo->prepare('SELECT id FROM cd_dvd_records WHERE serial = ?');
        $st->execute([$serial]);
        if ($st->fetchColumn() !== false) {
            continue;
        }
        $pdo->prepare('INSERT INTO cd_dvd_records (media, direction, serial, info_type, info_desc, jy, jm, jd, date_str, sort_key, receiver, receiver_unit, brought_by, brought_by_user_id, exit_sheet, note, department_id, recorder_id, recorder_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([
                $media, $direction, $serial, $infoType, $desc, $todayJy, $todayJm, $todayJd, $jalaliDate, $jalaliKey,
                $receiver, $receiver ? 'واحد مالی و اداری' : null, $broughtBy, $broughtByUser,
                $exitSheet, 'ثبت‌شده با دادهٔ نمونهٔ تست', $deptByCode['IT'], $userIds['seed_admin'], 'مدیر تست سامانه',
            ]);
        $created['cd_dvd_records'][] = (int) $pdo->lastInsertId();
    }

    // ─── ثبت مانیفست و خاتمه ───
    $created['manifest_created_at'] = date('Y-m-d H:i:s');
    seed_manifest_save($pdo, $created);
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'خطا در ساخت دادهٔ نمونه (همه‌چیز به حالت قبل برگردانده شد): ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

// ───────────────────────────── گزارش پایانی ─────────────────────────────
$line = str_repeat('─', 72);
fwrite(STDOUT, $line . PHP_EOL);
fwrite(STDOUT, "دادهٔ نمونهٔ سامانهٔ تست ساخته شد.\n");
fwrite(STDOUT, $line . PHP_EOL);
fwrite(STDOUT, "کاربران ساخته‌شده (همگی با رمز: {$password})\n");
fwrite(STDOUT, "  seed_admin            admin            مدیر تست سامانه\n");
fwrite(STDOUT, "  seed_it_manager       manager          مدیر فناوری اطلاعات (تأییدکنندهٔ مرحله اول)\n");
fwrite(STDOUT, "  seed_hr_manager       manager          مدیر منابع انسانی\n");
fwrite(STDOUT, "  seed_support_manager  support_manager  مدیر پشتیبانی\n");
fwrite(STDOUT, "  seed_supervisor       supervisor       ناظر رسیدگی\n");
fwrite(STDOUT, "  seed_inspector        inspector        بازرس\n");
fwrite(STDOUT, "  seed_guard            guard            نگهبانی\n");
fwrite(STDOUT, "  seed_it_agent1        agent            کارشناس فناوری (مسئول رسیدگی)\n");
fwrite(STDOUT, "  seed_it_agent2        agent            کارشناس فناوری\n");
fwrite(STDOUT, "  seed_support_agent    agent            کارشناس پشتیبانی\n");
fwrite(STDOUT, "  seed_user_amir/leila/negar/bahram/sara  user  کاربران عادی (واحدهای مختلف)\n");
fwrite(STDOUT, $line . PHP_EOL);
fwrite(STDOUT, "برای تست مسیر «لغو درخواست در انتظار تصمیم مسئول رسیدگی» وارد «جزئیات درخواست»\n");
fwrite(STDOUT, "شوید — دو درخواست با وضعیت لغوِ معلق ساخته شد:\n");
fwrite(STDOUT, "  • «مشکل صفحه‌نمایش لپ‌تاپ مدیریتی» (مسئول: seed_it_agent2)\n");
fwrite(STDOUT, "  • «تعمیر کولر اتاق جلسات» (مسئول: seed_support_agent)\n");
fwrite(STDOUT, "با حساب مسئول رسیدگی وارد شوید و «تأیید لغو» یا «رد و ادامهٔ رسیدگی» را بزنید.\n");
fwrite(STDOUT, $line . PHP_EOL);
fwrite(STDOUT, "شمارش‌ها: درخواست‌ها: " . count($created['tickets'])
    . " | اموال: " . count($created['assets'])
    . " | کاربران: " . count($created['users'])
    . " | سفارش غذای ساخته‌شده: " . ($summary['food_orders'] ?? 0) . PHP_EOL);
fwrite(STDOUT, "برای حذف کامل این داده‌ها و ساخت دوباره: php tools/seed_test_data.php --test-env --fresh\n");
