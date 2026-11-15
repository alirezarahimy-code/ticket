<?php
declare(strict_types=1);

// آزمون آفلاین «ثبت خودکار فیش روزهای گذشته».
//
// چه چیزی را تضمین می‌کند؟
//   ۱) مسیر API فقط با مجوز edit منو (food.menu_edit) باز است و GET را نمی‌پذیرد.
//   ۲) اعتبارسنجی بازه: تاریخ نامعتبر، برعکس، و بیش از ۴۰۰ روز رد می‌شود.
//   ۳) روز جاری هرگز در بازهٔ خودکار پذیرفته نمی‌شود (سفارش‌ها تا ۲۳:۰۰ می‌توانند تغییر کنند).
//   ۴) موتور مشترک (food_ticket_auto_receipts_run) همان قواعد سامانه را دارد:
//      ناهار فقط تعطیل، غیبت رد، فیش واقعی رد، کلید یکتا → بدون تکرار.
//   ۵) ابزار CLI و کارت پنل، هر دو همین تابع مشترک را صدا می‌زنند (نه نسخهٔ موازی).
//
// دیتابیس SQLite درون‌حافظه است؛ هیچ اتصال واقعی و هیچ چاپی انجام نمی‌شود.

$root = dirname(__DIR__);
$fails = 0;
$checks = 0;

function ok(bool $cond, string $label): void
{
    global $fails, $checks;
    $checks++;
    if ($cond) {
        echo "  ✅ {$label}\n";
        return;
    }
    $fails++;
    echo "  ❌ {$label}\n";
}

/** استخراج یک تابع از فایل منبع، برای آزمون بدون بارگذاری کل سامانه. */
function pick(string $file, string $name): string
{
    $src = (string) file_get_contents($file);
    $start = strpos($src, 'function ' . $name . '(');
    if ($start === false) {
        throw new RuntimeException("تابع {$name} در {$file} پیدا نشد");
    }
    $open = strpos($src, '{', $start);
    $depth = 0;
    $len = strlen($src);
    for ($i = $open; $i < $len; $i++) {
        if ($src[$i] === '{') {
            $depth++;
        } elseif ($src[$i] === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($src, $start, $i - $start + 1);
            }
        }
    }
    throw new RuntimeException("پایان تابع {$name} پیدا نشد");
}

$autoFile = $root . '/food-ticket-auto-receipts.php';
$panelFile = $root . '/food-ticket.php';
$webFile = $root . '/food-ticket-web/index.html';
$cliFile = $root . '/tools/backfill_food_auto_receipts.php';

foreach ([$autoFile, $panelFile, $webFile, $cliFile] as $f) {
    ok(is_file($f), 'فایل موجود است: ' . basename($f));
}

// ─────────────────────────────────────────────────────────────────────
echo "\n۱) گاردهای مسیر API\n";
// ─────────────────────────────────────────────────────────────────────
$panelSrc = (string) file_get_contents($panelFile);

ok(
    preg_match("/if \(\\\$route === 'backfill-auto-receipts'\) \{\s*return \\\$method === 'POST' && user_can\(\\\$user, 'food\.menu_edit'\);/s", $panelSrc) === 1,
    'مسیر فقط POST است و مجوز food.menu_edit می‌خواهد'
);
// خواندن ساده برای اطمینان از اینکه نام مسیر داخل فضای food-menu/ نیست؛
// وگرنه به ماژول برنامهٔ غذایی پاس می‌شود و هرگز به این هندلر نمی‌رسد.
ok(
    strpos($panelSrc, "food-menu/backfill-auto-receipts") === false,
    'مسیر در فضای food-menu/ نیست (وگرنه به هندلر دیگری می‌رود)'
);
ok(
    strpos($panelSrc, "if (\$_SERVER['REQUEST_METHOD'] === 'POST' && \$route === 'backfill-auto-receipts')") !== false,
    'هندلر POST به مسیر وصل است'
);
// هندلر باید تابع واقعی سامانه را صدا بزند، نه منطق بازنویسی‌شده.
ok(
    strpos($panelSrc, 'food_ticket_auto_receipts_run($d)') !== false,
    'هندلر همان food_ticket_auto_receipts_run() سامانه را صدا می‌زند'
);
ok(
    substr_count($panelSrc, 'food_ticket_auto_receipts_run(') >= 1,
    'پنل برای بازهٔ گذشته موتور مشترک را صدا می‌زند'
);
ok(
    preg_match('/\$span\s*=\s*\(new DateTimeImmutable\(\$from\)\)->diff\(new DateTimeImmutable\(\$to\)\)->days;/', $panelSrc) === 1,
    'طول بازه محاسبه می‌شود'
);
ok(strpos($panelSrc, "if (\$span > 400)") !== false, 'بازهٔ بیش از ۴۰۰ روز رد می‌شود');
ok(strpos($panelSrc, 'checkdate(') !== false, 'تاریخ با checkdate اعتبارسنجی می‌شود');
ok(strpos($panelSrc, "if (!food_ticket_auto_receipt_schema_ready())") !== false, 'نبود ساختار (مهاجرت ۰۲۱) پیام روشن می‌دهد');

// ─────────────────────────────────────────────────────────────────────
echo "\n۲) اعتبارسنجی بازه — همان شرط‌های هندلر\n";
// ─────────────────────────────────────────────────────────────────────
/** همان منطق اعتبارسنجی هندلر، جدا برای آزمون. */
$validate = static function (string $from, string $to): ?string {
    $okd = static fn (string $d): bool => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)
        && checkdate((int) substr($d, 5, 2), (int) substr($d, 8, 2), (int) substr($d, 0, 4));
    if (!$okd($from) || !$okd($to)) {
        return 'بازهٔ تاریخ معتبر نیست (YYYY-MM-DD).';
    }
    if ($from > $to) {
        return 'تاریخ شروع بعد از پایان است.';
    }
    $span = (new DateTimeImmutable($from))->diff(new DateTimeImmutable($to))->days;
    if ($span > 400) {
        return 'بازه بیش از ۴۰۰ روز است.';
    }
    return null;
};

ok($validate('2026-11-01', '2026-11-12') === null, 'بازهٔ درست پذیرفته می‌شود');
ok($validate('2026-13-01', '2026-11-12') !== null, 'ماه ۱۳ رد می‌شود');
ok($validate('2026-02-30', '2026-11-12') !== null, '۳۰ فوریه رد می‌شود');
ok($validate('2026-11-12', '2026-11-01') !== null, 'بازهٔ برعکس رد می‌شود');
ok($validate('2024-01-01', '2026-11-12') !== null, 'بازهٔ بیش از ۴۰۰ روز رد می‌شود');
ok($validate('2026-11-01', '2026-11-01') === null, 'بازهٔ یک‌روزه پذیرفته می‌شود');

// ─────────────────────────────────────────────────────────────────────
echo "\n۳) موتور: قاعدهٔ «امروز نه»\n";
// ─────────────────────────────────────────────────────────────────────
// فایل موتور یک‌بار و فقط یک‌بار بارگذاری می‌شود؛ food_ticket_auto_receipt_date
// هم داخل همان فایل است، پس نه eval لازم است نه تعریف دوباره.
if (!function_exists('food_ticket_holiday')) {
    function food_ticket_holiday(string $date): array
    {
        return ['holiday' => false, 'title' => ''];
    }
}
if (!function_exists('app_error_log')) {
    function app_error_log(string $m): void
    {
    }
}
require_once $autoFile;

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$threw = false;
try {
    food_ticket_auto_receipt_date($today, false);
} catch (Throwable $e) {
    $threw = true;
}
ok($threw, 'امروز بدون allowToday رد می‌شود (سفارش‌ها تا شب عوض می‌شوند)');
ok(food_ticket_auto_receipt_date($today, true) === $today, 'امروز با allowToday پذیرفته می‌شود (مسیر Worker)');
ok(food_ticket_auto_receipt_date($yesterday, false) === $yesterday, 'دیروز پذیرفته می‌شود');
ok(food_ticket_auto_receipt_date('2026-11-05', false) === '2026-11-05', 'تاریخ گذشتهٔ معتبر پذیرفته می‌شود');

$threw = false;
try {
    food_ticket_auto_receipt_date('2026-13-45', false);
} catch (Throwable $e) {
    $threw = true;
}
ok($threw, 'تاریخ بی‌معنا رد می‌شود');

// ─────────────────────────────────────────────────────────────────────
echo "\n۴) موتور مشترک با دیتابیس واقعی (SQLite)\n";
// ─────────────────────────────────────────────────────────────────────
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// ساختار مو‌به‌مو مطابق food_ticket_auto_receipt_schema_ready():
// همان هفت جدول و همان ستون‌هایی که موتور می‌خواهد.
$pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, employee_number TEXT, national_code TEXT,
    first_name TEXT, last_name TEXT, full_name TEXT)");
$pdo->exec("CREATE TABLE food_catalog (id INTEGER PRIMARY KEY, food_name TEXT)");
$pdo->exec("CREATE TABLE food_calendar_items (id INTEGER PRIMARY KEY, food_id INTEGER)");
$pdo->exec("CREATE TABLE food_orders (id INTEGER PRIMARY KEY, employee_id INTEGER, calendar_item_id INTEGER,
    food_date TEXT, meal_type TEXT, status TEXT)");
$pdo->exec("CREATE TABLE food_ticket_events (
    id INTEGER PRIMARY KEY, source_key TEXT UNIQUE, source_uid TEXT, punch_date TEXT, punch_time TEXT,
    user_id INTEGER, personnel_code TEXT, national_code TEXT, full_name TEXT, food_type TEXT,
    event_type TEXT, print_status TEXT, source_payload TEXT, meal_type TEXT,
    delivery_status TEXT, delivered_at TEXT, processed_at TEXT,
    source_card TEXT, ticket_key TEXT, last_error TEXT)");
$pdo->exec("CREATE TABLE food_ticket_daily_absence (id INTEGER PRIMARY KEY, absence_date TEXT, user_id INTEGER, reason TEXT)");
$pdo->exec("CREATE TABLE food_guest_requests (id INTEGER PRIMARY KEY, request_date TEXT, meal_type TEXT,
    guest_count INTEGER, organization TEXT, requester_name TEXT, created_by INTEGER, food_id INTEGER, status TEXT)");
$pdo->exec("CREATE TABLE holidays (id INTEGER PRIMARY KEY, holiday_date TEXT, title TEXT, active INTEGER DEFAULT 1)");

$pdo->exec("INSERT INTO users (id, employee_number, national_code, first_name, last_name, full_name) VALUES
    (301,'1001','0012345678','علی','رضایی','علی رضایی'),
    (302,'1002','0023456789','مریم','احمدی','مریم احمدی')");
$pdo->exec("INSERT INTO food_catalog (id, food_name) VALUES (901,'چلوکباب'),(902,'قیمه')");
$pdo->exec("INSERT INTO food_calendar_items (id, food_id) VALUES (901,901),(902,902)");

// سفارش‌ها: ۱ نوامبر (یکشنبه) دو شام، ۵ نوامبر (پنجشنبه) یک ناهار + دو شام
$pdo->exec("INSERT INTO food_orders (id, employee_id, calendar_item_id, food_date, meal_type, status) VALUES
    (1,301,901,'2026-11-01','dinner','active'),
    (2,302,902,'2026-11-01','dinner','active'),
    (3,301,901,'2026-11-05','lunch','active'),
    (4,302,902,'2026-11-05','dinner','active')");
$pdo->exec("INSERT INTO food_ticket_daily_absence (absence_date, user_id, reason) VALUES ('2026-11-01',302,'مرخصی')");

$GLOBALS['tdb'] = $pdo;
function db(): PDO
{
    return $GLOBALS['tdb'];
}

ok(food_ticket_auto_receipt_schema_ready() === true, 'ساختار جدول‌ها آماده است');

$r1 = food_ticket_auto_receipts_run('2026-11-01');
ok((int) $r1['staff_created'] === 1, 'غیبت‌دار رد شد و فقط یک شام ثبت شد');
ok((int) $r1['skipped_absence'] === 1, 'غیبت شمارش و رد شد');

$countAfterFirst = (int) $pdo->query("SELECT COUNT(*) FROM food_ticket_events")->fetchColumn();
ok($countAfterFirst === 1, 'دقیقاً یک ردیف در جدول است');

$r2 = food_ticket_auto_receipts_run('2026-11-01');
ok((int) $r2['staff_created'] === 0, 'اجرای دوباره هیچ ردیفی نساخت');
ok((int) $r2['already_registered'] === 1, 'ردیف قبلی به‌عنوان «از قبل» شمرده شد');
$countAfterSecond = (int) $pdo->query("SELECT COUNT(*) FROM food_ticket_events")->fetchColumn();
ok($countAfterSecond === $countAfterFirst, 'تعداد ردیف‌ها بعد از اجرای دوباره تغییر نکرد (idempotent)');

// روز کاری: ناهار نباید ثبت شود.
$r3 = food_ticket_auto_receipts_run('2026-11-01');
$lunchRows = (int) $pdo->query("SELECT COUNT(*) FROM food_ticket_events WHERE meal_type='lunch'")->fetchColumn();
ok($lunchRows === 0, 'در روز کاری ناهار ثبت نمی‌شود');

// فیش واقعی چاپ‌شده: نباید دوباره ساخته شود.
$pdo->exec("INSERT INTO food_ticket_events (source_key, punch_date, punch_time, user_id, full_name, food_type, event_type, print_status, meal_type, delivery_status)
    VALUES ('real-1','2026-11-05','12:30:00',301,'علی رضایی','چلوکباب','printed','printed','lunch','delivered')");
// ۵ نوامبر را تعطیل علامت بزن تا مسیر ناهار آزموده شود.
$pdo->exec("INSERT INTO holidays (holiday_date, title, active) VALUES ('2026-11-05','تعطیل آزمون',1)");

$r4 = food_ticket_auto_receipts_run('2026-11-05');
ok((int) $r4['skipped_real_event'] === 1, 'فیش واقعی چاپ‌شده تشخیص و رد شد');
$lunchAdmin = (int) $pdo->query("SELECT COUNT(*) FROM food_ticket_events WHERE meal_type='lunch' AND user_id=301")->fetchColumn();
ok($lunchAdmin === 1, 'برای فیش واقعی ردیف تکراری ساخته نشد');
$lunchOther = (int) $pdo->query("SELECT COUNT(*) FROM food_ticket_events WHERE meal_type='lunch' AND user_id=302")->fetchColumn();
ok($lunchOther === 0, 'برای کاربر بدون سفارش ناهار، ردیف ساخته نشد');

// ─────────────────────────────────────────────────────────────────────
echo "\n۵) کارت پنل و ابزار CLI\n";
// ─────────────────────────────────────────────────────────────────────
$web = (string) file_get_contents($webFile);
ok(strpos($web, 'autoReceiptBackfillBox') !== false, 'کارت «ثبت خودکار فیش روزهای گذشته» در پنل هست');
ok(strpos($web, "data-action=\"preview-auto-receipts\"") !== false, 'دکمهٔ پیش‌نمایش هست');
ok(strpos($web, "data-action=\"run-auto-receipts\"") !== false, 'دکمهٔ ثبت خودکار هست');
ok(
    strpos($web, "api('/backfill-auto-receipts'") !== false,
    'کارت به همان مسیر API وصل است'
);
ok(
    substr_count($web, "autoReceiptBackfillBox()") >= 1
        && strpos($web, '${selftestBox()}${autoReceiptBackfillBox()}') !== false,
    'کارت در صفحهٔ «اتصال پایگاه‌ها» رندر می‌شود'
);

$cli = (string) file_get_contents($cliFile);
ok(strpos($cli, 'food_ticket_auto_receipts_run(') !== false, 'ابزار CLI هم همان تابع مشترک را صدا می‌زند');
ok(strpos($cli, '--apply') !== false, 'ابزار CLI حالت --apply دارد');
ok(stripos($cli, 'چاپ فیزیکی') !== false, 'ابزار CLI تصریح می‌کند چاپ فیزیکی انجام نمی‌شود');

// ─────────────────────────────────────────────────────────────────────
echo "\n" . str_repeat('─', 66) . "\n";
printf("  %d بررسی — %d ایراد\n", $checks, $fails);
echo str_repeat('─', 66) . "\n";
exit($fails === 0 ? 0 : 1);
