<?php
/**
 * تست آفلاین کتابخانهٔ «آزمون جامع Worker / چاپ / مدیریت سامانه»
 * (food-ticket-selftest.php) — همان کتابخانه‌ای که پنل و CLI استفاده می‌کنند.
 *
 * اجرا: php tools/test_selftest_offline.php
 *
 * چرا آفلاین؟ سرور واقعی به DB، Access و چاپگر نیاز دارد. این تست با یک
 * «دیتابیس ساختگی» همان مسیرهای تصمیم‌گیری را می‌سنجد تا مطمئن شویم:
 *   • «درست بیکار» هرگز FAIL نمی‌دهد (خواستهٔ صریح مالک)
 *   • خرابی واقعی FAIL می‌دهد
 *   • ساختار گزارش برای رندر پنل و متن CLI کافی است
 *   • هیچ نوشتنی در کد نیست (فقط‌خواندنی)
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
chdir(APP_ROOT);

$fails = 0;
$passes = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $fails, $passes;
    if ($ok) {
        $passes++;
        echo "[PASS] {$label}\n";
        return;
    }
    $fails++;
    echo "[FAIL] {$label}" . ($detail !== '' ? " :: {$detail}" : '') . "\n";
}

/**
 * دیتابیس ساختگی: هر کوئری با یک پاسخ آماده تطبیق داده می‌شود.
 * هدف، اجرای کد واقعیِ کتابخانه روی سناریوهای کنترل‌شده است.
 */
final class FakeStmt
{
    public function __construct(private array $rows) {}
    public function fetch(): array|false { return $this->rows[0] ?? false; }
    public function fetchAll(): array { return $this->rows; }
    public function fetchColumn(): mixed { return $this->rows[0]['v'] ?? ($this->rows[0][0] ?? false); }
    public function execute(array $a = []): bool { return true; }
}

final class FakeDb
{
    public array $log = [];
    public function __construct(private array $answers) {}
    public function query(string $sql): FakeStmt|false
    {
        $this->log[] = $sql;
        foreach ($this->answers as $needle => $rows) {
            if ($needle !== '' && stripos($sql, $needle) !== false) {
                return new FakeStmt($rows);
            }
        }
        return new FakeStmt([]);
    }
    public function prepare(string $sql): FakeStmt
    {
        $this->log[] = $sql;
        return new FakeStmt([]);
    }
}

$GLOBALS['__fakeDb'] = null;
if (!function_exists('db')) {
    function db(): FakeDb
    {
        return $GLOBALS['__fakeDb'];
    }
}
if (!function_exists('db_table_exists')) {
    function db_table_exists(string $table): bool
    {
        foreach ($GLOBALS['__fakeDb']->log as $sql) {
        }
        return in_array($table, $GLOBALS['__tables'] ?? [], true);
    }
}
if (!function_exists('cfg')) {
    function cfg(string $key, mixed $default = null): mixed
    {
        return $key === 'app.key' ? 'test-key' : $default;
    }
}
if (!function_exists('user_can')) {
    function user_can(array $user, string $code): bool { return true; }
}

require APP_ROOT . '/food-ticket-window.php';
require APP_ROOT . '/food-ticket-selftest.php';

$tz = food_ticket_window_timezone();
$at = static fn (string $d, string $t): DateTimeImmutable =>
    DateTimeImmutable::createFromFormat('Y-m-d H:i', $d . ' ' . $t, $tz);

$windowCfg = static fn (bool $enabled): array => [
    'enabled' => 1,
    'window_enabled' => $enabled ? 1 : 0,
    'window_start' => '11:00',
    'window_end' => '14:00',
    'window_days' => 'all',
    'window_margin_before_minutes' => 0,
    'window_margin_after_minutes' => 0,
    'active_poll_ms' => 500,
    'idle_poll_seconds' => 60,
    'printer_mode' => 'tcp_raw',
    'printer_host' => '',   // عمداً خالی: تست چاپگر جدا سنجیده می‌شود
    'attendance_path' => '',
    'cut_source_rows' => 0,
];

/** بررسی وجود یک نام در همهٔ بخش‌ها. */
$findCheck = static function (array $result, string $needle): ?array {
    foreach ($result['sections'] as $s) {
        foreach ($s['checks'] as $c) {
            if (mb_strpos((string) $c['name'], $needle) !== false) {
                return $c;
            }
        }
    }
    return null;
};

/* ── ۱) ساختار کلی ────────────────────────────────────────────────────── */
$GLOBALS['__tables'] = ['food_ticket_config', 'food_ticket_events', 'food_ticket_worker_status'];
$GLOBALS['__fakeDb'] = new FakeDb([
    'SELECT 1' => [['v' => 1]],
    'COUNT(*) FROM food_ticket_events WHERE' => [['v' => 0]],
    'print_status = \'printing\'' => [['v' => 0]],
    'MIN(updated_at)' => [['v' => null]],
    'punch_date = CURDATE()' => [['v' => 0]],
    'food_ticket_worker_status' => [],
]);

$r = food_ticket_selftest_run([
    'config' => $windowCfg(true),
    'at' => $at('2026-11-16', '10:30'),  // دوشنبه، بیرون از پنجره
    'source' => 'cli',
    'dry_print' => false,
]);

check('آزمون اجرا شد و ساختار استاندارد دارد', isset($r['sections'], $r['verdict'], $r['generated_at'], $r['elapsed_ms']));
check('حداقل ۷ بخش دارد', count($r['sections']) >= 7, 'count=' . count($r['sections']));
$keys = array_column($r['sections'], 'key');
foreach (['config', 'worker', 'queue', 'attendance', 'printer', 'management', 'readiness'] as $need) {
    check("بخش «{$need}» وجود دارد", in_array($need, $keys, true));
}
check('هر بخش checks غیرخالی دارد', array_reduce($r['sections'], static fn ($c, $s) => $c && count($s['checks']) > 0, true));

$statuses = [];
$nested = false;
foreach ($r['sections'] as $s) {
    foreach ($s['checks'] as $c) {
        $statuses[$c['status']] = true;
        foreach (['name', 'status', 'message', 'fix'] as $field) {
            if (!array_key_exists($field, $c)) {
                $nested = true;
            }
        }
    }
}
check('فیلدهای هر بررسی کامل‌اند (name/status/message/fix)', !$nested);
check('وضعیت‌ها فقط از مجموعهٔ مجازند', array_diff(array_keys($statuses), ['ok', 'warn', 'fail', 'info', 'skip']) === []);
check('حکم هم‌سو با شمارش‌ها است', ($r['verdict']['counts']['fail'] > 0) === ($r['verdict']['level'] === 'fail'));
check('منبع گزارش حفظ می‌شود', ($r['source'] ?? '') === 'cli');

/* ── ۲) «درست بیکار» نباید FAIL بدهد ─────────────────────────────────── */
$idle = food_ticket_selftest_run([
    'config' => $windowCfg(true),
    'at' => $at('2026-11-16', '10:30'),
    'dry_print' => false,
]);
$queueCheck = $findCheck($idle, 'ردیف‌های واجد شرایط چاپ');
check('صف خالی + پنجرهٔ بسته ⇒ FAIL نیست', ($queueCheck['status'] ?? '') === 'ok', (string) ($queueCheck['status'] ?? 'missing'));
$idleNote = $findCheck($idle, 'وضعیت بیکاری');
check('پیام «بیکاری درست» وجود دارد', $idleNote !== null && mb_strpos((string) $idleNote['message'], 'درست') !== false);

/* ── ۳) صف مانده در پنجرهٔ باز ⇒ FAIL ────────────────────────────────── */
$GLOBALS['__fakeDb'] = new FakeDb([
    'SELECT 1' => [['v' => 1]],
    'COUNT(*) FROM food_ticket_events WHERE' => [['v' => 5]],
    'print_status = \'printing\'' => [['v' => 0]],
    'punch_date = CURDATE()' => [['v' => 0]],
    'food_ticket_worker_status' => [],
]);
$busy = food_ticket_selftest_run([
    'config' => $windowCfg(true),
    'at' => $at('2026-11-16', '12:00'),  // داخل پنجره
    'dry_print' => false,
]);
$busyQueue = $findCheck($busy, 'ردیف‌های واجد شرایط چاپ');
check('صف مانده + پنجرهٔ باز ⇒ FAIL', ($busyQueue['status'] ?? '') === 'fail', (string) ($busyQueue['status'] ?? 'missing'));

/* ── ۴) صف مانده ولی پنجره بسته ⇒ فقط WARN ──────────────────────────── */
$GLOBALS['__fakeDb'] = new FakeDb([
    'SELECT 1' => [['v' => 1]],
    'COUNT(*) FROM food_ticket_events WHERE' => [['v' => 9]],
    'print_status = \'printing\'' => [['v' => 0]],
    'punch_date = CURDATE()' => [['v' => 0]],
    'food_ticket_worker_status' => [],
]);
$waiting = food_ticket_selftest_run([
    'config' => $windowCfg(true),
    'at' => $at('2026-11-16', '22:00'),
    'dry_print' => false,
]);
$waitCheck = $findCheck($waiting, 'ردیف‌های واجد شرایط چاپ');
check('صف مانده + پنجرهٔ بسته ⇒ WARN نه FAIL', ($waitCheck['status'] ?? '') === 'warn', (string) ($waitCheck['status'] ?? 'missing'));

/* ── ۵) پردازشگر غیرفعال ⇒ FAIL پایه ─────────────────────────────────── */
$offCfg = $windowCfg(true);
$offCfg['enabled'] = 0;
$off = food_ticket_selftest_run(['config' => $offCfg, 'at' => $at('2026-11-16', '12:00'), 'dry_print' => false]);
$proc = $findCheck($off, 'پردازشگر فیش غذا');
check('پردازشگر غیرفعال ⇒ FAIL', ($proc['status'] ?? '') === 'fail', (string) ($proc['status'] ?? 'missing'));
check('و حکم کل هم FAIL می‌شود', ($off['verdict']['level'] ?? '') === 'fail');

/* ── ۶) پنجرهٔ غیرفعال هشدار می‌دهد نه FAIL ──────────────────────────── */
$noWin = food_ticket_selftest_run(['config' => $windowCfg(false), 'at' => $at('2026-11-16', '03:00'), 'dry_print' => false]);
$winCheck = $findCheck($noWin, 'پنجرهٔ کاری تردد');
check('پنجرهٔ غیرفعال ⇒ WARN (۲۴/۷) نه FAIL', ($winCheck['status'] ?? '') === 'warn', (string) ($winCheck['status'] ?? 'missing'));

/* ── ۷) چاپگر: آدرس خالی ⇒ FAIL ──────────────────────────────────────── */
$printerCheck = $findCheck($r, 'آدرس چاپگر شبکه');
check('حالت شبکه + IP خالی ⇒ FAIL', ($printerCheck['status'] ?? '') === 'fail', (string) ($printerCheck['status'] ?? 'missing'));

/* ── ۸) منبع تردد: مسیر خالی ⇒ FAIL ──────────────────────────────────── */
$attCheck = $findCheck($r, 'مسیر فایل تردد');
check('مسیر تردد خالی ⇒ FAIL', ($attCheck['status'] ?? '') === 'fail', (string) ($attCheck['status'] ?? 'missing'));

/* ── ۹) پیش‌بینی ۷ روز آینده ─────────────────────────────────────────── */
$ready = $findCheck($r, 'روز کاری بعدی با پنجرهٔ باز');
check('پیش‌بینی روز باز بعدی وجود دارد', $ready !== null);
check('در ۷ روز آینده حداقل یک روز باز است', ($ready['status'] ?? '') === 'ok', (string) ($ready['status'] ?? 'missing'));

/* ── ۱۰) متن گزارش برای کپی/CLI ──────────────────────────────────────── */
$text = food_ticket_selftest_text($r);
check('متن گزارش ساخته می‌شود', $text !== '' && mb_strlen($text) > 200);
foreach (['آزمون جامع', 'حکم:', 'OK=', 'WARN=', 'FAIL='] as $needle) {
    check("متن گزارش شامل «{$needle}» است", mb_strpos($text, $needle) !== false);
}
check('متن گزارش تعداد بخش‌ها را دارد', mb_substr_count($text, ')') >= 7);

/* ── ۱۱) فقط‌خواندنی بودن کتابخانه (بازرسی متن سورس) ─────────────────── */
$src = (string) file_get_contents(APP_ROOT . '/food-ticket-selftest.php');
$writeOps = [];
foreach (['INSERT INTO', 'UPDATE ', 'DELETE FROM', 'DROP TABLE', 'TRUNCATE', 'ALTER TABLE', 'file_put_contents', 'unlink('] as $op) {
    if (stripos($src, $op) !== false) {
        $writeOps[] = $op;
    }
}
check('کتابخانه هیچ عملیات نوشتنی ندارد', $writeOps === [], implode(', ', $writeOps));
check('کتابخانه هیچ بایتی به چاپگر نمی‌فرستد', stripos($src, 'food_ticket_send_raw_payload') === false);
check('کتابخانه چاپ واقعی انجام نمی‌دهد', stripos($src, 'food_ticket_send_to_printer') === false);

/* ── ۱۲) اتصال به پنل و CLI ──────────────────────────────────────────── */
$hostSrc = (string) file_get_contents(APP_ROOT . '/food-ticket.php');
check('food-ticket.php کتابخانه را بارگذاری می‌کند', strpos($hostSrc, "food-ticket-selftest.php") !== false);
check('مسیر worker-selftest با food.db ثبت شده', (bool) preg_match("/'worker-selftest'\\s*=>\\s*'food\\.db'/", $hostSrc));
check('هندلر API آزمون وجود دارد', strpos($hostSrc, "route === 'worker-selftest'") !== false);

$panel = (string) file_get_contents(APP_ROOT . '/food-ticket-web/index.html');
check('پنل تابع selftestBox را دارد', strpos($panel, 'function selftestBox(') !== false);
check('پنل در صفحهٔ اتصال پایگاه‌ها فراخوانی می‌کند', strpos($panel, '${selftestBox()}') !== false);
check('دکمهٔ اجرای آزمون در پنل سیم‌کشی شده', strpos($panel, "act==='run-worker-selftest'") !== false);
check('دکمهٔ کپی گزارش سیم‌کشی شده', strpos($panel, "act==='copy-worker-selftest'") !== false);
check('صفحهٔ آزمون به API واقعی وصل است', strpos($panel, "api('/api/worker-selftest')") !== false);

/* ── خلاصه ───────────────────────────────────────────────────────────── */
echo "\n" . str_repeat('=', 60) . "\n";
echo 'PASS=' . $passes . ' | FAIL=' . $fails . "\n";
if ($fails > 0) {
    echo "❌ آزمون کتابخانه ناموفق بود.\n";
    exit(1);
}
echo "✅ همهٔ بررسی‌های آفلاین کتابخانهٔ آزمون سالم است.\n";
exit(0);
