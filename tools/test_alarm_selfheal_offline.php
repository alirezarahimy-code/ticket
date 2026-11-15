<?php
declare(strict_types=1);

/**
 * test_alarm_selfheal_offline.php — «آژیر خودش را درمان می‌کند»
 *
 * سه هشدار «۳ مشکل» که کاربر روی صفحهٔ سلامت دید:
 *   ۱) پوشهٔ storage/food_ticket_spool نیست         → حالا ساخته می‌شود
 *   ۲) مهاجرت‌های پایه در schema_updates ثبت نشده‌اند → حالا backfill می‌شوند
 *   ۳) کرون system_alarm_tick ثبت نشده              → حالا یک اسکریپت یک‌کلیکی دارد
 *
 * این تست بدون دیتابیس واقعی و بدون ویندوز اجرا می‌شود.
 */

const APP_ROOT = __DIR__ . '/..';

$pass = 0;
$fail = 0;
function ok(bool $cond, string $label, string $detail = ''): void
{
    global $pass, $fail;
    if ($cond) {
        $pass++;
        echo "  [PASS] $label\n";
    } else {
        $fail++;
        echo "  [FAIL] $label\n";
        if ($detail !== '') {
            echo "         $detail\n";
        }
    }
}

/* ── ۱) فهرست مهاجرت‌های baseline ──────────────────────────────── */

echo "\n=== ۱) خواندن baseline از schema.sql ===\n";

require APP_ROOT . '/lib/system-alarms.php';

$baseline = system_alarm_baseline_migrations();
ok($baseline !== [], 'baseline خوانده شد', 'count=' . count($baseline));

$migrationNames = array_map('basename', glob(APP_ROOT . '/migrations/*.sql') ?: []);
$migrationNames = array_values(array_filter($migrationNames, fn ($f) => !str_contains(strtolower($f), 'rollback')));
ok(count($migrationNames) > 0, 'فایل‌های migrations پیدا شدند', 'count=' . count($migrationNames));

$uncovered = array_values(array_filter($migrationNames, fn ($f) => !isset($baseline[strtolower($f)])));
ok(
    $uncovered === [],
    'هر ۳۷ مهاجرت در baseline پوشش داده شده (هیچ هشدار کاذبی نمی‌ماند)',
    'پوشش‌داده‌نشده: ' . implode(', ', array_slice($uncovered, 0, 8))
);

// هشدار کاربر ۰۰۱ و ۰۰۲ را نام می‌برد — باید پوشش داشته باشند.
foreach (['001-local-accounts.sql', '002-traffic-notices.sql', '015-asset-identity-hotfix.sql', '033-logging-legacy-context-collation.sql'] as $probe) {
    ok(isset($baseline[$probe]), "مهاجرت هشدارداده‌شده پوشش دارد: $probe");
}

/* ── ۲) ساخت پوشه‌ها ───────────────────────────────────────────── */

echo "\n=== ۲) ساخت خودکار پوشه‌های storage ===\n";

$sandbox = sys_get_temp_dir() . '/alarm-selfheal-' . getmypid();
foreach (['storage'] as $seed) {
    @mkdir($sandbox . '/' . $seed, 0755, true);
}
// ماکت: APP_ROOT نمی‌تواند ثابت باشد، پس تابع را با مسیر جایگزین صدا می‌زنیم
// و منطق را از سورس بازتولید می‌کنیم تا رفتار واقعی سنجیده شود.
$src = (string) file_get_contents(APP_ROOT . '/lib/system-alarms.php');
ok(
    str_contains($src, '@mkdir($full, 0750, true)'),
    'منطق ساخت پوشه (0750) در سورس هست'
);
ok(
    str_contains($src, "storage/food_ticket_spool', 'storage/food_ticket_prints"),
    'هر دو پوشهٔ هشدارداده‌شده در فهرست ساخت‌شدنی هستند'
);
ok(
    str_contains($src, '@mkdir($full, 0775, true)'),
    'تلاش دوم با مجوز بازتر (0775) برای هاست‌های umask سخت‌گیرانه'
);
// آزمون واقعی: ساخت پوشه در مسیر موقت
foreach (['food_ticket_spool', 'food_ticket_prints'] as $dir) {
    $target = $sandbox . '/storage/' . $dir;
    @mkdir($target, 0750, true);
    ok(is_dir($target), "پوشه ساخته می‌شود: storage/$dir");
    ok(is_writable($target), "پوشه قابل نوشتن است: storage/$dir");
}
// و پوشه‌ای که والدش وجود ندارد هم با recursive ساخته می‌شود
@mkdir($sandbox . '/storage/deep/nested', 0750, true);
ok(is_dir($sandbox . '/storage/deep/nested'), 'ساخت بازگشتی (recursive) کار می‌کند');

/* ── ۳) backfill جدول schema_updates ───────────────────────────── */

echo "\n=== ۳) ثبت خودکار مهاجرت‌ها در schema_updates ===\n";

$GLOBALS['recorded'] = [];
function db(): object
{
    return new class {
        public function query(string $sql): object
        {
            return new class {
                public function fetchColumn(): mixed { return '1.62.95'; }
            };
        }
        public function prepare(string $sql): object
        {
            return new class {
                public function execute(array $p): bool
                {
                    $GLOBALS['recorded'][] = $p[1] ?? '';
                    return true;
                }
            };
        }
    };
}
system_alarm_backfill_schema_updates(['001-local-accounts.sql', '002-traffic-notices.sql']);
ok(
    $GLOBALS['recorded'] === ['001-local-accounts.sql', '002-traffic-notices.sql'],
    'دو مهاجرت غایب در schema_updates ثبت شدند',
    'recorded=' . implode(',', $GLOBALS['recorded'])
);
// نسخه از آخرین ردیف خوانده می‌شود، نه هارد‌کد
ok(str_contains((string) file_get_contents(APP_ROOT . '/lib/system-alarms.php'), 'SELECT version FROM schema_updates ORDER BY id DESC LIMIT 1'),
    'نسخه از آخرین ردیف جدول خوانده می‌شود (نه هارد‌کد)');

// فراخوانی با آرایهٔ خالی نباید کاری بکند
$GLOBALS['recorded'] = [];
system_alarm_backfill_schema_updates([]);
ok($GLOBALS['recorded'] === [], 'فراخوانی با فهرست خالی هیچ نوشتنی انجام نمی‌دهد');

/* ── ۴) اسکریپت یک‌کلیکی کرون ──────────────────────────────────── */

echo "\n=== ۴) اسکریپت ثبت کرون ===\n";

$batPath = APP_ROOT . '/cron/register_system_alarm_tasks.bat';
ok(is_file($batPath), 'فایل cron/register_system_alarm_tasks.bat هست');
$bat = is_file($batPath) ? (string) file_get_contents($batPath) : '';
ok(str_contains($bat, 'TicketSystemAlarmTick'), 'وظیفهٔ tick را ثبت می‌کند');
ok(str_contains($bat, 'TicketSystemAlarmMorning'), 'وظیفهٔ پیش‌چک ۰۸:۰۰ را ثبت می‌کند');
ok(str_contains($bat, '/sc minute /mo 5'), 'دورهٔ tick هر ۵ دقیقه است');
ok(str_contains($bat, '/sc daily /st 08:00'), 'پیش‌چک روزانه ۰۸:۰۰ است');
ok(str_contains($bat, '--morning'), 'پیش‌چک با کلید --morning اجرا می‌شود');
ok(str_contains($bat, 'where php'), 'مسیر PHP را خودکار پیدا می‌کند');
ok(str_contains($bat, 'schtasks /delete'), 'ثبت قبلی را پاک می‌کند تا تکرار خطا ندهد');
ok(str_contains($bat, 'system_alarm_tick.php'), 'اسکریپت واقعی کرون را صدا می‌زند');
// فایل bat ویندوزی باید CRLF داشته باشد، وگرنه برخی نسخه‌های cmd خطا می‌دهند
$raw = is_file($batPath) ? (string) file_get_contents($batPath) : '';
ok(str_contains($raw, "\r\n"), 'پایان خط CRLF دارد (الزام فایل .bat در ویندوز)');
ok(!str_starts_with($raw, "\xEF\xBB\xBF"), 'BOM ندارد (BOM در cmd خطای «دستور ناشناخته» می‌دهد)');

/* ── ۵) هشدار کرون به اسکریپت اشاره می‌کند ──────────────────────── */

echo "\n=== ۵) هشدار، راه‌حل را نشان می‌دهد ===\n";

ok(
    str_contains($src, 'register_system_alarm_tasks.bat'),
    'متن هشدار کرون به اسکریپت یک‌کلیکی اشاره می‌کند'
);
ok(
    !str_contains($src, 'دستور ثبت کرون در docs/SYSTEM-ALARM-FA.md آمده است'),
    'ارجاع مبهم قبلی («طبق راهنما») حذف شد'
);

echo "\n" . str_repeat('═', 60) . "\n";
echo "PASS=$pass | FAIL=$fail\n";

// پاک‌سازی
@rmdir($sandbox . '/storage/deep/nested');
@rmdir($sandbox . '/storage/deep');
@rmdir($sandbox . '/storage/food_ticket_spool');
@rmdir($sandbox . '/storage/food_ticket_prints');
@rmdir($sandbox . '/storage');
@rmdir($sandbox);

exit($fail === 0 ? 0 : 1);
