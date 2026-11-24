#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * tools/seed_test_data.php — «دادهٔ نمونهٔ سامانهٔ تست»
 *
 * این اسکریپت یک سامانهٔ تازه‌نصب‌شده (مثلاً روی «سیستم تست شبکه») را با داده‌های
 * واقعی‌نما پر می‌کند تا بتوانید همهٔ قسمت‌های برنامه را با نمایش درست تست کنید:
 * کاربران با نقش‌های مختلف (۱۵ + ۲۰ کارمند نمونه)، واحدها، شناسنامهٔ اموال،
 * درخواست‌ها در همهٔ وضعیت‌ها (شامل دو درخواست با «لغو در انتظار تصمیم مسئول رسیدگی»
 * برای تست رایج‌ترین مسیر)، پیام‌ها، امتیازها، اعلان‌ها، پایگاه دانش، تعطیلات،
 * و ماژول غذا به‌صورت پرحجم: بیش از ۱۵ روز کاری (بازهٔ ۳۴ روز گذشته تا امروز) با
 * سفارش همهٔ پرسنل، چاپ/تحویل فیش (به‌ازای هر سفارش یک رویداد چاپ + تحویل، همراه
 * خطاهای چاپ و سفارش‌های بدون حضور)، مهمانان، تطبیق روزانهٔ توزیع، غیبت‌ها،
 * ثبت اجرای کارت گروهی، درخواست‌های لغو و درخواست مهمان تاریخی/آینده و
 * «وضعیت ورکر» تنظیم‌شده روی ok — یعنی همان چیزی که گزارش‌های سامانه برایش طراحی شده‌اند.
 *
 * پس از ساخت داده، برای دیدن «خروجی‌های سامانهٔ مدیریت غذا» این بازه را انتخاب کنید:
 * «گزارش‌های غذا» (food-order.php)، «تطبیق سفارش و توزیع غذا» (food-reconciliation.php)،
 * «گزارش تحویل غذا» و پایش ورکر؛ بازهٔ پیش‌فرض همهٔ صفحات: ۳۴ روز گذشته تا امروز.
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
    $eventIds = seed_manifest_ids($m, 'food_ticket_events');
    $printLogIds = seed_manifest_ids($m, 'food_ticket_print_log');
    $absenceIds = seed_manifest_ids($m, 'food_daily_absence');
    $groupRunIds = seed_manifest_ids($m, 'food_group_runs');
    $reconciliationIds = seed_manifest_ids($m, 'food_meal_reconciliations');
    $guestActualIds = seed_manifest_ids($m, 'food_guest_actuals');
    $guestCardIds = seed_manifest_ids($m, 'food_guest_cards');
    $cancelRequestIds = seed_manifest_ids($m, 'food_cancel_requests');

    if ($foodLogIds) {
        $deleted['food_order_logs'] = seed_delete_in($pdo, 'DELETE FROM food_order_logs WHERE id IN ({IN})', $foodLogIds);
    }
    if ($printLogIds) {
        $deleted['food_ticket_print_log'] = seed_delete_in($pdo, 'DELETE FROM food_ticket_print_log WHERE id IN ({IN})', $printLogIds);
    }
    if ($eventIds) {
        $deleted['food_ticket_events'] = seed_delete_in($pdo, 'DELETE FROM food_ticket_events WHERE id IN ({IN})', $eventIds);
    }
    if ($reconciliationIds) {
        $deleted['food_meal_reconciliations'] = seed_delete_in($pdo, 'DELETE FROM food_meal_reconciliations WHERE id IN ({IN})', $reconciliationIds);
    }
    if ($guestActualIds) {
        $deleted['food_guest_actuals'] = seed_delete_in($pdo, 'DELETE FROM food_guest_actuals WHERE id IN ({IN})', $guestActualIds);
    }
    if ($cancelRequestIds) {
        $deleted['food_cancel_requests'] = seed_delete_in($pdo, 'DELETE FROM food_order_cancel_requests WHERE id IN ({IN})', $cancelRequestIds);
    }
    if ($absenceIds) {
        $deleted['food_daily_absence'] = seed_delete_in($pdo, 'DELETE FROM food_ticket_daily_absence WHERE id IN ({IN})', $absenceIds);
    }
    if ($groupRunIds) {
        $deleted['food_group_runs'] = seed_delete_in($pdo, 'DELETE FROM food_ticket_group_runs WHERE id IN ({IN})', $groupRunIds);
    }
    if ($guestCardIds) {
        $deleted['food_guest_cards'] = seed_delete_in($pdo, 'DELETE FROM food_ticket_guest_cards WHERE id IN ({IN})', $guestCardIds);
    }
    // وضعیت تک‌ردیفهٔ ورکر را به حالت پیش‌فرض برگردان (بعداً دوباره با دادهٔ نمونه پر می‌شود).
    $pdo->exec("UPDATE food_ticket_worker_status SET host_name = '', process_id = 0, last_seen = NULL, status = 'ok', last_message = '', last_error = NULL, processed_count = 0, printed_count = 0, error_count = 0 WHERE id = 1");
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
    'food_guest_requests' => [], 'cd_dvd_records' => [], 'food_ticket_events' => [],
    'food_ticket_print_log' => [], 'food_daily_absence' => [], 'food_group_runs' => [],
    'food_meal_reconciliations' => [], 'food_guest_actuals' => [], 'food_guest_cards' => [],
    'food_cancel_requests' => [],
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
    // ۲۰ کارمند نمونه برای انبوه‌کردن داده‌های غذا و فهرست‌ها
    $employeeDepartments = ['IT', 'HR', 'FIN', 'ADM', 'ADM'];
    $employeeOrgUnits = ['IT' => 'ORG-IT', 'HR' => 'ORG-HR', 'FIN' => 'ORG-FIN', 'ADM' => 'ORG-FIN'];
    foreach (range(1, 20) as $n) {
        $deptCode = $employeeDepartments[$n % 5];
        $userSeeds[] = [sprintf('seed_emp_%02d', $n), sprintf('کارمند نمونهٔ بخش %02d', $n), 'user', $deptCode, $employeeOrgUnits[$deptCode], null, 0];
    }
    $handlingUnits = $pdo->query("SELECT code, id FROM handling_units WHERE code IN ('it','support')")->fetchAll(PDO::FETCH_KEY_PAIR);
    if (!isset($handlingUnits['it']) || !isset($handlingUnits['support'])) {
        $pdo->exec("INSERT IGNORE INTO handling_units (code, name, is_active) VALUES ('it', 'واحد فناوری اطلاعات', 1), ('support', 'واحد پشتیبانی', 1)");
        $handlingUnits = $pdo->query("SELECT code, id FROM handling_units WHERE code IN ('it','support')")->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    $userIds = [];
    $userMeta = [];
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
        $userMeta[$username] = ['full_name' => $fullName, 'employee_number' => $employeeNumber, 'national_code' => $nationalCode];
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
        ['مشکل کندی شبکه داخلی', 'سرعت شبکه در ساعات اوج بسیار پایین است و فایل‌سرور کند پاسخ می‌دهد.', 'it', 'IT-CAT-NET', 'urgent', 'in_progress', 'seed_user_sara', 'seed_it_agent1', 'incident', null, 1, 240, null],
        ['درخواست صندلی اداری', 'صندلی میز کار قدیمی است و نیاز به تعویض دارد.', 'support', 'SUP-CAT-FURN', 'normal', 'manager_review', 'seed_user_amir', null, 'request', null, 0, 1440, null],
        ['خطای ورود به درگاه حضور و غیاب', 'پرسنل با پیام «کاربر نامعتبر» روبه‌رو می‌شوند.', 'it', 'IT-CAT-ACC', 'critical', 'manager_review', 'seed_user_bahram', null, 'incident', null, 0, 240, null],
        ['نصب اسکنر جدید بایگانی', 'اسکنر تازه‌وارد باید روی سیستم بایگانی نصب و تنظیم شود.', 'it', 'IT-CAT-DRIVER', 'normal', 'resolved', 'seed_user_negar', 'seed_it_agent2', 'request', null, 6, 1440, null],
        ['جمع‌آوری زباله اتاق سرور', 'زباله‌ها و کارتن‌های خالی اتاق سرور باید خارج شوند.', 'support', 'SUP-CAT-CLEAN', 'normal', 'waiting_user', 'seed_it_manager', 'seed_support_agent', 'request', null, 4, 1440, null],
        ['به‌روزرسانی آنتی‌ویروس ایستگاه‌ها', 'آنتی‌ویروس چند ایستگاه تگ‌های قدیمی دارد و هشدار می‌دهد.', 'it', 'IT-CAT-SW', 'normal', 'closed', 'seed_it_manager', 'seed_it_agent2', 'request', 'SEED-PC-007', 17, 1440, null],
        ['درخواست توزیع آب معدنی در جلسات', 'برای جلسات هفتگی هیئت مدیره آب معدنی لازم است.', 'support', 'SUP-CAT-GEN', 'normal', 'cancelled', 'seed_hr_manager', null, 'request', null, 11, 1440, null],
        ['درخواست نصب دوربین جدید در انبار', 'برای پوشش نقطه کور انبار دوربین جدید لازم است.', 'support', 'SUP-CAT-OFFICE', 'urgent', 'assigned', 'seed_guard', 'seed_support_agent', 'request', null, 0, 240, null],
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

    // ─── ۸) ماژول غذا: کاتالوگ، تقویم ۳۴+روزه، سفارش‌ها، فیش چاپ/تحویل، تطبیق، مهمان ───
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

    // بازهٔ تقویم غذا: ۳۴ روز گذشته تا پایان ماه میلادی جاری (روزانه، بدون پنجشنبه/جمعهٔ سلف‌بسته).
    // روزهای گذشته «بسته» ثبت می‌شوند (مثل چرخهٔ واقعی سفارش) و روزهای آیندهٔ ماه «باز» می‌مانند.
    $today = new DateTimeImmutable('today');
    $todayStr = $today->format('Y-m-d');
    $rangeStart = $today->modify('-34 days');
    $monthEnd = (new DateTimeImmutable(date('Y-m-01')))->modify('last day of this month');
    $openDays = [];
    for ($day = $rangeStart; $day <= $monthEnd; $day = $day->modify('+1 day')) {
        if (in_array((int) $day->format('N'), [4, 5], true)) {
            continue; // پنجشنبه و جمعه تعطیل سلف
        }
        $date = $day->format('Y-m-d');
        $isPast = $date < $todayStr;
        $st = $pdo->prepare("SELECT id, order_status FROM food_calendar WHERE food_date = ? AND meal_type = 'lunch'");
        $st->execute([$date]);
        $calendarRow = $st->fetch(PDO::FETCH_ASSOC);
        if ($calendarRow === false) {
            if ($isPast) {
                $pdo->prepare("INSERT INTO food_calendar (food_date, meal_type, order_status, note, closed_by, closed_at, created_by) VALUES (?, 'lunch', 'closed', 'دادهٔ نمونهٔ تست (بسته‌شده)', ?, ?, ?)")
                    ->execute([$date, $userIds['seed_admin'], $date . ' 15:00:00', $userIds['seed_admin']]);
            } else {
                $pdo->prepare("INSERT INTO food_calendar (food_date, meal_type, order_status, note, created_by) VALUES (?, 'lunch', 'open', 'دادهٔ نمونهٔ تست', ?)")
                    ->execute([$date, $userIds['seed_admin']]);
            }
            $calendarId = (int) $pdo->lastInsertId();
            $created['food_calendars'][] = $calendarId;
        } else {
            $calendarId = (int) $calendarRow['id'];
        }
        $openDays[] = ['date' => $date, 'calendar_id' => $calendarId, 'is_past' => $isPast];
        foreach ($catalogIds as $sortOrder => $foodId) {
            $st = $pdo->prepare('SELECT id FROM food_calendar_items WHERE calendar_id = ? AND food_id = ?');
            $st->execute([$calendarId, $foodId]);
            if ($st->fetchColumn() !== false) {
                continue;
            }
            $pdo->prepare('INSERT INTO food_calendar_items (calendar_id, food_id, sort_order, active) VALUES (?, ?, ?, 1)')
                ->execute([$calendarId, $foodId, $sortOrder + 1]);
            $created['food_calendar_items'][] = (int) $pdo->lastInsertId();
        }
    }

    // کارکنانی که غذا سفارش می‌دهند: نقش‌های «user» به‌علاوهٔ کارشناسان (مثل سلف واقعی).
    $orderUsernames = [];
    foreach ($userSeeds as [$uname, , $role]) {
        if (in_array($role, ['user', 'agent'], true)) {
            $orderUsernames[] = $uname;
        }
    }
    $orderFoodItem = $pdo->prepare('SELECT id FROM food_calendar_items WHERE calendar_id = (SELECT id FROM food_calendar WHERE food_date = ? AND meal_type = \'lunch\') AND active = 1 ORDER BY sort_order LIMIT 1');
    $orderInsert = $pdo->prepare("INSERT INTO food_orders (employee_id, calendar_item_id, food_date, meal_type, created_by, status, created_at) VALUES (?, ?, ?, 'lunch', ?, ?, ?)");
    $logInsert = $pdo->prepare("INSERT INTO food_order_logs (order_id, calendar_id, action, user_id, old_value, new_value, reason, created_at) VALUES (?, NULL, ?, ?, ?, ?, 'seed_test_data', ?)");
    $ordersMade = 0;
    $dayIndex = 0;
    $seenCancelled = [];
    foreach ($openDays as $openDay) {
        $dayIndex++;
        $date = $openDay['date'];
        if (!$openDay['is_past'] && $date !== $todayStr) {
            continue; // سفارش فقط برای گذشته و امروز
        }
        $orderFoodItem->execute([$date]);
        $calendarItemId = $orderFoodItem->fetchColumn();
        if ($calendarItemId === false) {
            continue;
        }
        foreach ($orderUsernames as $userIndex => $username) {
            $employeeId = $userIds[$username];
            $exists = $pdo->prepare("SELECT id FROM food_orders WHERE employee_id = ? AND food_date = ? AND meal_type = 'lunch'");
            $exists->execute([$employeeId, $date]);
            if ($exists->fetchColumn() !== false) {
                continue;
            }
            // ~۸۰٪ پرسنل هر روز سفارش ثبت می‌کنند؛ ~هفت درصد از این سفارش‌ها بعد لغو می‌شود.
            if ((($userIndex * 7 + $dayIndex * 13) % 100) >= 80) {
                continue;
            }
            $cancelled = (($userIndex * 5 + $dayIndex * 17) % 100) < 7;
            $createdAtOrder = $date . sprintf(' 09:%02d:00', ($userIndex * 7 + $dayIndex * 3) % 90);
            $status = $cancelled ? 'cancelled' : 'active';
            $orderInsert->execute([$employeeId, (int) $calendarItemId, $date, $employeeId, $status, $createdAtOrder]);
            $orderId = (int) $pdo->lastInsertId();
            $created['food_orders'][] = $orderId;
            $logInsert->execute([$orderId, 'create', $employeeId, null, 'seed order', $createdAtOrder]);
            $created['food_order_logs'][] = (int) $pdo->lastInsertId();
            if ($cancelled) {
                $logInsert->execute([$orderId, 'cancel', $employeeId, 'active', 'cancelled', $date . ' 10:15:00']);
                $created['food_order_logs'][] = (int) $pdo->lastInsertId();
                $seenCancelled[] = ['order_id' => $orderId, 'employee_id' => $employeeId, 'food_date' => $date];
            }
            $ordersMade++;
        }
    }
    $summary['food_orders'] = $ordersMade;

    // گروه سلف نمونه با چند عضو (برای رانت/غیبت گروهی)
    $st = $pdo->prepare('SELECT id FROM food_ticket_groups WHERE l_uid = ?');
    $st->execute(['SEED-L-0001']);
    $groupId = $st->fetchColumn();
    if ($groupId === false) {
        $pdo->prepare("INSERT INTO food_ticket_groups (title, l_uid, description, status, created_by) VALUES ('گروه نمونه تستی', 'SEED-L-0001', 'گروه سلف ساخته‌شده با دادهٔ نمونهٔ تست', 'active', ?)")
            ->execute([$userIds['seed_admin']]);
        $groupId = (int) $pdo->lastInsertId();
        $created['food_groups'][] = (int) $groupId;
    }
    $groupId = (int) $groupId;
    foreach (['seed_user_amir', 'seed_user_leila', 'seed_user_negar'] as $member) {
        $pdo->prepare('INSERT IGNORE INTO food_ticket_group_members (group_id, user_id) VALUES (?, ?)')
            ->execute([$groupId, $userIds[$member]]);
    }

    // غیبت‌های نمونه: دو غیبت عادی + یک غیبت گروهی (برای «غایبین» گزارش تطبیق)
    $absenceSeeds = [];
    $absenceCandidates = [
        ['seed_user_bahram', 8, 'مرخصی استعلاجی'],
        ['seed_emp_03', 5, 'مأموریت اداری'],
        ['seed_emp_11', 12, 'مرخصی استحقاقی'],
        ['seed_emp_17', 20, 'مأموریت آموزشی'],
        ['seed_user_leila', 3, 'مرخصی استعلاجی (گروه)'],
    ];
    foreach ($absenceCandidates as [$uname, $daysAgo, $reason]) {
        if (!isset($userIds[$uname])) {
            continue;
        }
        $absenceDate = $today->modify("-{$daysAgo} days")->format('Y-m-d');
        if (in_array((int) (new DateTimeImmutable($absenceDate))->format('N'), [4, 5], true)) {
            $absenceDate = (new DateTimeImmutable($absenceDate))->modify('-3 days')->format('Y-m-d');
        }
        $linkGroup = str_contains($reason, 'گروه');
        $st = $pdo->prepare('SELECT id FROM food_ticket_daily_absence WHERE absence_date = ? AND user_id = ?');
        $st->execute([$absenceDate, $userIds[$uname]]);
        if ($st->fetchColumn() === false) {
            $pdo->prepare('INSERT INTO food_ticket_daily_absence (absence_date, group_id, user_id, reason, created_by) VALUES (?, ?, ?, ?, ?)')
                ->execute([$absenceDate, $linkGroup ? $groupId : null, $userIds[$uname], $reason, $userIds['seed_admin']]);
            $absenceId = (int) $pdo->lastInsertId();
            $created['food_daily_absence'][] = $absenceId;
            $absenceSeeds[$absenceDate][$userIds[$uname]] = true;
        }
    }
    $summary['food_absences'] = count($created['food_daily_absence']);

    // درخواست‌های لغو سفارش (یکی معلق برای اقدام، یکی تأییدشده)
    foreach (array_slice($seenCancelled, 0, 4) as $i => $cancelSeed) {
        $st = $pdo->prepare("SELECT id FROM food_order_cancel_requests WHERE order_id = ? AND status = 'pending'");
        $st->execute([$cancelSeed['order_id']]);
        if ($st->fetchColumn() !== false) {
            continue;
        }
        if ($i === 0) {
            // معلق: مدیر پشتیبانی باید تصمیم بگیرد
            $pdo->prepare("INSERT INTO food_order_cancel_requests (order_id, employee_id, requested_by, food_date, note, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', ?)")
                ->execute([$cancelSeed['order_id'], $cancelSeed['employee_id'], $cancelSeed['employee_id'], $cancelSeed['food_date'], 'خواهشمندم لغو شود (دادهٔ نمونه)', $cancelSeed['food_date'] . ' 10:20:00']);
        } else {
            // تأییدشده: برای نمایش در تاریخچهٔ تصمیمات
            $pdo->prepare("INSERT INTO food_order_cancel_requests (order_id, employee_id, requested_by, food_date, note, status, decided_by, decided_at, decision_note, created_at) VALUES (?, ?, ?, ?, ?, 'approved', ?, ?, ?, ?)")
                ->execute([$cancelSeed['order_id'], $cancelSeed['employee_id'], $cancelSeed['employee_id'], $cancelSeed['food_date'], 'لغو با تأیید (دادهٔ نمونه)', $userIds['seed_support_manager'], $cancelSeed['food_date'] . ' 11:00:00', 'بلامانع است (نمونه)', $cancelSeed['food_date'] . ' 10:20:00']);
        }
        $created['food_cancel_requests'][] = (int) $pdo->lastInsertId();
        if (count($created['food_cancel_requests']) >= 2) {
            break;
        }
    }

    // درخواست‌های مهمان: تاریخی (انجام‌شده) + آینده + یک لغوشده
    $pastGuestPlans = [
        [10, 'شرکت همکار الف', 8, 'دبیر هیئت مدیره'],
        [18, 'بازدیدکنندگان نمایشگاه', 15, 'روابط عمومی'],
        [25, 'شرکت تعاونی ساحل', 6, 'امور قراردادها'],
    ];
    $guestPlanIds = [];
    foreach ($pastGuestPlans as [$daysAgo, $org, $count, $requester]) {
        $reqDate = $today->modify("-{$daysAgo} days");
        if (in_array((int) $reqDate->format('N'), [4, 5], true)) {
            $reqDate = $reqDate->modify('-3 days');
        }
        $reqDateStr = $reqDate->format('Y-m-d');
        $st = $pdo->prepare("SELECT id FROM food_guest_requests WHERE request_date = ? AND organization = ?");
        $st->execute([$reqDateStr, $org]);
        $reqId = $st->fetchColumn();
        if ($reqId === false) {
            $pdo->prepare("INSERT INTO food_guest_requests (request_date, meal_type, organization, guest_count, food_id, requester_name, deputy_unit_id, note, status, created_by, created_at) VALUES (?, 'lunch', ?, ?, ?, ?, ?, 'دادهٔ نمونهٔ تست', 'active', ?, ?)")
                ->execute([$reqDateStr, $org, $count, $catalogIds[0], $requester, $orgUnitIds['ORG-WS'] ?? null, $userIds['seed_admin'], $reqDateStr . ' 08:30:00']);
            $reqId = (int) $pdo->lastInsertId();
            $created['food_guest_requests'][] = $reqId;
        }
        $guestPlanIds[$reqDateStr] = ['id' => (int) $reqId, 'count' => $count, 'organization' => $org, 'food_id' => $catalogIds[0]];
    }
    // مهمان آینده: یکی این هفته، یکی دو هفتهٔ بعد (علاوه بر مورد +۷ روز قدیمیِ قبلی حذف شد)
    foreach ([[14, 'شرکت همکار ب', 10, 'دبیر هیئت مدیره'], [21, 'هیئت مدیرهٔ مهمان', 20, 'دفتر مدیرعامل']] as [$daysAhead, $org, $count, $requester]) {
        $reqDate = $today->modify("+{$daysAhead} days");
        if (in_array((int) $reqDate->format('N'), [4, 5], true)) {
            $reqDate = $reqDate->modify('+2 days');
        }
        $st = $pdo->prepare("SELECT id FROM food_guest_requests WHERE request_date = ? AND organization = ?");
        $st->execute([$reqDate->format('Y-m-d'), $org]);
        if ($st->fetchColumn() !== false) {
            continue;
        }
        $pdo->prepare("INSERT INTO food_guest_requests (request_date, meal_type, organization, guest_count, food_id, requester_name, deputy_unit_id, note, status, created_by) VALUES (?, 'lunch', ?, ?, ?, ?, ?, 'دادهٔ نمونهٔ تست', 'active', ?)")
            ->execute([$reqDate->format('Y-m-d'), $org, $count, $catalogIds[1], $requester, $orgUnitIds['ORG-WS'] ?? null, $userIds['seed_admin']]);
        $created['food_guest_requests'][] = (int) $pdo->lastInsertId();
    }
    // یک درخواست مهمان لغوشده (برای آمار لغوها)
    $cancelledGuestDate = $today->modify('-6 days');
    if (in_array((int) $cancelledGuestDate->format('N'), [4, 5], true)) {
        $cancelledGuestDate = $cancelledGuestDate->modify('-3 days');
    }
    $st = $pdo->prepare("SELECT id FROM food_guest_requests WHERE organization = 'شرکت لغوشدهٔ نمونه'");
    $st->execute();
    if ($st->fetchColumn() === false) {
        $pdo->prepare("INSERT INTO food_guest_requests (request_date, meal_type, organization, guest_count, food_id, requester_name, deputy_unit_id, note, status, created_by, cancelled_by, cancelled_at, created_at) VALUES (?, 'lunch', 'شرکت لغوشدهٔ نمونه', 9, ?, 'دبیر جلسات (نمونه)', ?, 'لغو پس از ثبت (دادهٔ نمونه)', 'cancelled', ?, ?, ?, ?)")
            ->execute([$cancelledGuestDate->format('Y-m-d'), $catalogIds[2], $orgUnitIds['ORG-WS'] ?? null, $userIds['seed_admin'], $userIds['seed_support_manager'], $cancelledGuestDate->format('Y-m-d') . ' 12:00:00', $cancelledGuestDate->format('Y-m-d') . ' 08:30:00']);
        $created['food_guest_requests'][] = (int) $pdo->lastInsertId();
    }

    // کارت مهمان سلف: یکی با سقف روزانه، یکی بدون سقف
    foreach ([['SEED-GUEST-CARD-1', 'مهمان VIP نمونهٔ تست', 3], ['SEED-GUEST-CARD-2', 'مهمان بدون سقف (نمونه)', 0]] as [$cardUid, $guestName, $limit]) {
        $st = $pdo->prepare('SELECT id FROM food_ticket_guest_cards WHERE card_uid = ?');
        $st->execute([$cardUid]);
        if ($st->fetchColumn() !== false) {
            continue;
        }
        $pdo->prepare('INSERT INTO food_ticket_guest_cards (card_uid, guest_name, daily_limit, is_active, updated_by) VALUES (?, ?, ?, 1, ?)')
            ->execute([$cardUid, $guestName, $limit, $userIds['seed_admin']]);
        $created['food_guest_cards'][] = (int) $pdo->lastInsertId();
    }

    // ─── فیش‌های چاپ/تحویل (خروجی ورکر) برای روزهای گذشته ───
    // به‌ازای هر سفارشِ فعالِ روزِ گذشته، یک رویداد فیش ثبت می‌شود:
    //   ~۸۸٪ تحویل‌شده (printed + delivered)، ~۲٪ خطای چاپ، ~۱٪ بدون غذا، ~۹٪ اصلاً حضور نداشتند.
    // سفارش‌های لغوشده فیش ندارند (درست مثل روز واقعی).
    $eventInsert = $pdo->prepare("INSERT INTO food_ticket_events (source_key, source_uid, punch_date, punch_time, personnel_code, user_id, national_code, full_name, food_type, ticket_key, print_job_id, event_type, meal_type, print_status, delivery_status, delivered_at, delivered_by, print_attempts, retry_count, printer_name, processed_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'lunch', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $printLogInsert = $pdo->prepare("INSERT INTO food_ticket_print_log (event_id, print_job_id, source_key, ticket_key, printer, status, retry_count, message, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $orderFoodName = $pdo->prepare('SELECT c.food_name FROM food_orders o JOIN food_calendar_items i ON i.id = o.calendar_item_id JOIN food_catalog c ON c.id = i.food_id WHERE o.id = ?');
    $printerName = 'SEED-PRN-CANTEEN';
    $printedTotal = 0;
    $deliveredTotal = 0;
    $errorTotal = 0;
    foreach ($created['food_orders'] ? $pdo->query('SELECT id, employee_id, food_date, status FROM food_orders ORDER BY id')->fetchAll(PDO::FETCH_ASSOC) : [] as $orderRow) {
        $date = (string) $orderRow['food_date'];
        if ($date >= $todayStr) {
            continue; // امروز/آینده: هنوز فیشی ثبت نشده
        }
        if (($orderRow['status'] ?? '') !== 'active') {
            continue; // لغوشده‌ها فیش ندارند
        }
        $userId = (int) $orderRow['employee_id'];
        if (isset($absenceSeeds[$date][$userId])) {
            continue; // غایب: فیش چاپ نمی‌شود
        }
        $username = array_search($userId, $userIds, true);
        if ($username === false) {
            continue;
        }
        $meta = $userMeta[$username] ?? ['full_name' => 'پرسنل', 'employee_number' => '', 'national_code' => ''];
        $orderId = (int) $orderRow['id'];
        $orderFoodName->execute([$orderId]);
        $foodName = (string) ($orderFoodName->fetchColumn() ?: 'غذای روز');
        $dayIndexForHash = (int) substr(str_replace('-', '', $date), 6, 2) + (int) substr(str_replace('-', '', $date), 4, 2);
        $pick = ($userId * 13 + $dayIndexForHash * 7) % 100;
        $sourceKey = hash('sha256', 'seed-food-event|' . $date . '|' . $userId);
        $ticketKey = sprintf('SEED-T-%s-%d', str_replace('-', '', $date), $userId);
        $punchMinute = ($userId * 11 + $dayIndexForHash * 3) % 48;
        $punchTime = sprintf('12:%02d:00', $punchMinute);
        $deliveredAt = $date . ' ' . sprintf('12:%02d:00', min(59, $punchMinute + 4 + ($userId % 7)));

        if ($pick < 2) {
            // خطای چاپ: ورکر دیده و خطا ثبت کرده
            $eventInsert->execute([$sourceKey, 'SEED-CARD-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT), $date, $punchTime, $meta['employee_number'], $userId, $meta['national_code'], $meta['full_name'], $foodName, $ticketKey, 'SEED-JOB-' . str_replace('-', '', $date) . '-' . $userId, 'print_error', 'print_error', 'pending', null, null, 3, 2, $printerName, $date . ' ' . $punchTime, $date . ' ' . $punchTime]);
            $eventId = (int) $pdo->lastInsertId();
            $created['food_ticket_events'][] = $eventId;
            $printLogInsert->execute([$eventId, 'SEED-JOB-' . str_replace('-', '', $date) . '-' . $userId, $sourceKey, $ticketKey, $printerName, 'print_error', 2, 'گیر کاغذ/خطای چاپ (دادهٔ نمونه)', $date . ' ' . $punchTime]);
            $created['food_ticket_print_log'][] = (int) $pdo->lastInsertId();
            $errorTotal++;
            continue;
        }
        if ($pick < 3) {
            // بدون غذا: رویداد ثبت شد ولی فیش چاپ نشد
            $eventInsert->execute([$sourceKey, 'SEED-CARD-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT), $date, $punchTime, $meta['employee_number'], $userId, $meta['national_code'], $meta['full_name'], $foodName, $ticketKey, null, 'no_food', 'not_printed', 'pending', null, null, 1, 0, $printerName, $date . ' ' . $punchTime, $date . ' ' . $punchTime]);
            $created['food_ticket_events'][] = (int) $pdo->lastInsertId();
            continue;
        }
        if ($pick < 12) {
            continue; // اصلاً حاضر نشد (سفارش فعال ولی بدون فیش)
        }
        // تحویل‌شده
        $eventInsert->execute([$sourceKey, 'SEED-CARD-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT), $date, $punchTime, $meta['employee_number'], $userId, $meta['national_code'], $meta['full_name'], $foodName, $ticketKey, 'SEED-JOB-' . str_replace('-', '', $date) . '-' . $userId, 'printed', 'printed', 'delivered', $deliveredAt, $userIds['seed_guard'], 1, 0, $printerName, $deliveredAt, $date . ' ' . $punchTime]);
        $eventId = (int) $pdo->lastInsertId();
        $created['food_ticket_events'][] = $eventId;
        $printLogInsert->execute([$eventId, 'SEED-JOB-' . str_replace('-', '', $date) . '-' . $userId, $sourceKey, $ticketKey, $printerName, 'printed', 0, 'چاپ موفق (دادهٔ نمونه)', $deliveredAt]);
        $created['food_ticket_print_log'][] = (int) $pdo->lastInsertId();
        $printedTotal++;
        $deliveredTotal++;
    }
    $summary['food_events_printed'] = $printedTotal;
    $summary['food_events_delivered'] = $deliveredTotal;
    $summary['food_events_print_errors'] = $errorTotal;

    // رویدادهای چاپی مهمان برای روزهای تاریخیِ دارای درخواست مهمان فعال
    foreach ($guestPlanIds as $reqDateStr => $plan) {
        for ($guestIndex = 1; $guestIndex <= $plan['count']; $guestIndex++) {
            $sourceKey = hash('sha256', 'seed-guest-event|' . $reqDateStr . '|' . $plan['id'] . '|' . $guestIndex);
            $ticketKey = sprintf('SEED-G-T-%s-%d-%d', str_replace('-', '', $reqDateStr), $plan['id'], $guestIndex);
            $fullName = $plan['organization'] . ' — مهمان ' . $guestIndex;
            $punchTime = sprintf('12:%02d:00', 10 + ($guestIndex * 7) % 45);
            $deliveredAt = $reqDateStr . ' ' . sprintf('12:%02d:00', 15 + ($guestIndex * 7) % 40);
            $eventInsert->execute([$sourceKey, 'SEED-GUEST-CARD-1', $reqDateStr, $punchTime, '', null, '', $fullName, 'مهمان', $ticketKey, 'SEED-JOB-G-' . str_replace('-', '', $reqDateStr) . '-' . $guestIndex, 'guest', 'printed', 'delivered', $deliveredAt, $userIds['seed_guard'], 1, 0, $printerName, $deliveredAt, $reqDateStr . ' ' . $punchTime]);
            $eventId = (int) $pdo->lastInsertId();
            $created['food_ticket_events'][] = $eventId;
            $printLogInsert->execute([$eventId, 'SEED-JOB-G-' . str_replace('-', '', $reqDateStr) . '-' . $guestIndex, $sourceKey, $ticketKey, $printerName, 'printed', 0, 'چاپ موفق مهمان (دادهٔ نمونه)', $deliveredAt]);
            $created['food_ticket_print_log'][] = (int) $pdo->lastInsertId();
        }
    }

    // شمار واقعی تحویل‌ها برای تطبیق: از جدول رویدادها خوانده می‌شود (نه از شمارندهٔ داخلی).
    $servedMap = [];
    $servedStmt = $pdo->query("SELECT punch_date, COUNT(DISTINCT user_id) AS served FROM food_ticket_events WHERE punch_date < '" . $todayStr . "' AND delivery_status = 'delivered' AND event_type IN ('printed', 'auto_printed') AND user_id IS NOT NULL GROUP BY punch_date");
    foreach ($servedStmt->fetchAll(PDO::FETCH_ASSOC) as $servedRow) {
        $servedMap[(string) $servedRow['punch_date']] = (int) $servedRow['served'];
    }
    $guestServedMap = [];
    $guestStmt = $pdo->query("SELECT punch_date, COUNT(*) AS served FROM food_ticket_events WHERE punch_date < '" . $todayStr . "' AND event_type IN ('guest', 'auto_guest') AND print_status = 'printed' GROUP BY punch_date");
    foreach ($guestStmt->fetchAll(PDO::FETCH_ASSOC) as $guestRow) {
        $guestServedMap[(string) $guestRow['punch_date']] = (int) $guestRow['served'];
    }

    // تطبیق سفارش/توزیع و آرک فاکتورهای ماهانه برای روزهای گذشته
    $reconciliationInsert = $pdo->prepare("INSERT INTO food_meal_reconciliations (food_date, meal_type, employee_orders_at_order, guest_meals_at_order, supplier_order_quantity, received_quantity, employee_served_quantity, guest_served_quantity, waste_quantity, leftover_staff_quantity, leftover_waste_quantity, leftover_keep_quantity, leftover_other_quantity, status, note, ordered_by, ordered_at, snapshot_at, closed_by, closed_at, created_by, updated_by) VALUES (?, 'lunch', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'closed', ?, ?, ?, ?, ?, ?, ?, ?)");
    $guestActualInsert = $pdo->prepare("INSERT INTO food_guest_actuals (food_date, meal_type, root_unit_id, root_name, root_type, requested_count, actual_count, created_by, updated_by, created_at) VALUES (?, 'lunch', ?, ?, 'org_unit', ?, ?, ?, ?, ?)");
    $reconciliationsMade = 0;
    $dayCounter = 0;
    foreach ($openDays as $openDay) {
        $dayCounter++;
        $date = $openDay['date'];
        if ($date >= $todayStr) {
            continue;
        }
        $st = $pdo->prepare("SELECT COUNT(DISTINCT employee_id) AS active_orders FROM food_orders WHERE food_date = ? AND meal_type = 'lunch' AND status = 'active'");
        $st->execute([$date]);
        $activeOrders = (int) $st->fetchColumn();
        $guestMeals = 0;
        $st = $pdo->prepare("SELECT COALESCE(SUM(guest_count), 0) FROM food_guest_requests WHERE request_date = ? AND meal_type = 'lunch' AND status = 'active'");
        $st->execute([$date]);
        $guestMeals = (int) $st->fetchColumn();
        $demand = $activeOrders + $guestMeals;
        if ($demand <= 0) {
            continue;
        }
        $employeeServed = (int) ($servedMap[$date] ?? 0);
        $guestServed = (int) ($guestServedMap[$date] ?? 0);
        $servedTotal = $employeeServed + $guestServed;
        $supplierQuantity = $demand + (($dayCounter * 7) % 5) - 1; // -۱..+۳ عددی که پیمانکار فرستاده
        $receivedQuantity = $supplierQuantity + (($dayCounter % 3) === 0 ? -1 : 0); // گاهی یکی کمتر می‌رسد
        $remaining = max(0, $receivedQuantity - $servedTotal);
        $leftoverStaff = min(2, $remaining);
        $leftoverKeep = min(3, max(0, $remaining - $leftoverStaff));
        $leftoverWaste = max(0, $remaining - $leftoverStaff - $leftoverKeep);
        $st = $pdo->prepare('SELECT id FROM food_meal_reconciliations WHERE food_date = ? AND meal_type = ?');
        $st->execute([$date, 'lunch']);
        if ($st->fetchColumn() !== false) {
            continue;
        }
        $reconciliationInsert->execute([
            $date, $activeOrders, $guestMeals, $supplierQuantity, $receivedQuantity,
            $employeeServed, $guestServed, $leftoverWaste, $leftoverStaff, $leftoverWaste, $leftoverKeep,
            $dayCounter % 6 === 0 ? 'توزیع کامل و بدون اتلاف قابل‌توجه (دادهٔ نمونه)' : null,
            $userIds['seed_admin'], $date . ' 09:30:00', $date . ' 13:30:00', $userIds['seed_admin'], $date . ' 15:00:00', $userIds['seed_admin'], $userIds['seed_admin'],
        ]);
        $created['food_meal_reconciliations'][] = (int) $pdo->lastInsertId();
        $reconciliationsMade++;
        // واقعیت مهمان همان روز (برای «ویژهٔ مهمان» گزارش تطبیق)
        if ($guestServed > 0 && isset($orgUnitIds['ORG-WS'])) {
            $st = $pdo->prepare("SELECT id FROM food_guest_actuals WHERE food_date = ? AND meal_type = 'lunch' AND root_unit_id = ?");
            $st->execute([$date, $orgUnitIds['ORG-WS']]);
            if ($st->fetchColumn() === false) {
                $guestActualInsert->execute([$date, $orgUnitIds['ORG-WS'], 'کارگاه مرکزی', $guestMeals, $guestServed, $userIds['seed_admin'], $userIds['seed_admin'], $date . ' 13:30:00']);
                $created['food_guest_actuals'][] = (int) $pdo->lastInsertId();
            }
        }
    }
    $summary['food_reconciliations'] = $reconciliationsMade;

    // ثبت اجرای کارت گروهی سلف (run) برای روزهای کاری گذشته — آمار گروه برای گزارش‌ها
    $groupRunsMade = 0;
    foreach (array_slice($openDays, 0, 24) as $openDay) {
        $date = $openDay['date'];
        if ($date >= $todayStr) {
            continue;
        }
        $sourceKey = hash('sha256', 'seed-group-run|' . $date . '|' . $groupId);
        $membersTotal = 3;
        $absentForGroup = 0;
        foreach (['seed_user_amir', 'seed_user_leila', 'seed_user_negar'] as $member) {
            $memberId = $userIds[$member];
            if (isset($absenceSeeds[$date][$memberId])) {
                $absentForGroup++;
            }
        }
        $printedCount = $membersTotal - $absentForGroup;
        $st = $pdo->prepare('SELECT id FROM food_ticket_group_runs WHERE source_key = ?');
        $st->execute([$sourceKey]);
        if ($st->fetchColumn() !== false) {
            continue;
        }
        $pdo->prepare("INSERT INTO food_ticket_group_runs (group_id, group_title, rfid_card, trigger_kind, source_key, punch_date, punch_time, run_kind, status, members_total, printed_count, repeat_count, no_food_count, absent_count, skipped_count, error_count) VALUES (?, 'گروه نمونه تستی', 'SEED-G-RUN-CARD-1', 'card', ?, ?, '12:06:00', 'first', 'done', ?, ?, 0, 0, ?, 0, 0)")
            ->execute([$groupId, $sourceKey, $date, $membersTotal, $printedCount, $absentForGroup]);
        $created['food_group_runs'][] = (int) $pdo->lastInsertId();
        $groupRunsMade++;
    }
    $summary['food_group_runs'] = $groupRunsMade;

    // وضعیت ورکر: سالم و تازه، با شمارش‌های واقعی ساخته‌شده
    $totalEvents = count($created['food_ticket_events']);
    $printedAll = $pdo->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status = 'printed' AND event_type IN ('printed','guest','auto_printed','auto_guest')")->fetchColumn();
    $errorsAll = $pdo->query("SELECT COUNT(*) FROM food_ticket_events WHERE event_type = 'print_error'")->fetchColumn();
    $workerSeen = date('Y-m-d H:i:s', time() - 120);
    $pdo->prepare("UPDATE food_ticket_worker_status SET host_name = 'SEED-WORKER-01', process_id = 4242, last_seen = ?, status = 'ok', last_message = 'پردازش عادی (دادهٔ نمونهٔ تست)', last_error = NULL, processed_count = ?, printed_count = ?, error_count = ? WHERE id = 1")
        ->execute([$workerSeen, $totalEvents, (int) $printedAll, (int) $errorsAll]);
    $summary['food_worker'] = 'ok (SEED-WORKER-01)';

    // درخواست مهمان «آینده» قدیمیِ قبلی (برای سازگاری با مسیرهای قدیمی حفظ می‌شود)
    $guestDate = (new DateTimeImmutable('now'))->modify('+7 days')->format('Y-m-d');
    if (in_array((int) (new DateTimeImmutable($guestDate))->format('N'), [4, 5], true)) {
        $guestDate = (new DateTimeImmutable($guestDate))->modify('+2 days')->format('Y-m-d');
    }
    $st = $pdo->prepare("SELECT id FROM food_guest_requests WHERE request_date = ? AND organization = 'شرکت همکار نمونه'");
    $st->execute([$guestDate]);
    if ($st->fetchColumn() === false) {
        $pdo->prepare("INSERT INTO food_guest_requests (request_date, meal_type, organization, guest_count, food_id, requester_name, deputy_unit_id, note, status, created_by) VALUES (?, 'lunch', 'شرکت همکار نمونه', 12, ?, ?, ?, 'ثبت‌شده با دادهٔ نمونهٔ تست', 'active', ?)")
            ->execute([$guestDate, $catalogIds[0], 'دبیر جلسات (نمونه)', $orgUnitIds['ORG-WS'] ?? null, $userIds['seed_admin']]);
        $created['food_guest_requests'][] = (int) $pdo->lastInsertId();
    }

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
fwrite(STDOUT, "── ماژول غذا (دادهٔ نمونهٔ حجیم) ─────────────────────────────\n");
fwrite(STDOUT, "۳۴ روز گذشتهٔ کاری + امروز؛ سفارش «ثبت‌شده» برای روزهای گذشته با:\n");
fwrite(STDOUT, "  • فیش چاپ/تحویل‌شده (printed + delivered) به‌ازای سفارش‌های فعال\n");
fwrite(STDOUT, "  • خطای چاپ و «بدون غذا» برای واقع‌نمایی\n");
fwrite(STDOUT, "  • سفارشِ فعال بدون فیش (غایبین) و سفارش‌های لغوشده\n");
fwrite(STDOUT, "  • تطبیق سفارش/توزیع روزانهٔ بسته‌شده + واقعیت مهمان\n");
fwrite(STDOUT, "  • ثبت اجرای کارت گروهی سلف، غیبت‌ها، کارت مهمان، وضعیت «ورکر فعال»\n");
fwrite(STDOUT, "نمونهٔ خروجی‌ها: «گزارش‌های غذا» (فیلتر چاپ/تحویل)، «تطبیق سفارش و توزیع غذا»،\n");
fwrite(STDOUT, "«گزارش تحویل غذا» (food-order.php) و پایش ورکر در پنل مدیریت. بازهٔ پیش‌فرض: ۳۴ روز گذشته تا امروز.\n");
fwrite(STDOUT, $line . PHP_EOL);
fwrite(STDOUT, "شمارش‌ها: درخواست‌ها: " . count($created['tickets'])
    . " | اموال: " . count($created['assets'])
    . " | کاربران: " . count($created['users']) . PHP_EOL);
fwrite(STDOUT, "سفارش غذا: " . ($summary['food_orders'] ?? 0)
    . " | فیش تحویل‌شده: " . ($summary['food_events_delivered'] ?? 0)
    . " | خطای چاپ: " . ($summary['food_events_print_errors'] ?? 0)
    . " | تطبیق روزانه: " . ($summary['food_reconciliations'] ?? 0)
    . " | غیبت‌ها: " . ($summary['food_absences'] ?? 0)
    . " | اجرای کارت گروهی: " . ($summary['food_group_runs'] ?? 0) . PHP_EOL);
fwrite(STDOUT, "کاربران غذا: ۱۵ کارکن + ۲۰ کارمند نمونه (seed_emp_01..seed_emp_20) — همه با رمز {$password}\n");
fwrite(STDOUT, $line . PHP_EOL);
fwrite(STDOUT, "برای حذف کامل این داده‌ها و ساخت دوباره: php tools/seed_test_data.php --test-env --fresh\n");
