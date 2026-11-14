<?php
/**
 * تست آفلاین ابزار «تست پیش از پنجرهٔ چاپ» (tools/prewindow_check.php).
 * اجرا: php tools/test_prewindow_check_offline.php
 *
 * چون ابزار واقعی به DB و پرینتر نیاز دارد، این تست:
 *  ۱) منطق پنجره را با تنظیمات ۱۱–۱۴ روی ساعت‌های مرزی می‌سنجد (همان تابعی که Worker صدا می‌زند)
 *  ۲) ساختار و ایمنی خودِ اسکریپت را بازرسی می‌کند (فقط‌خواندنی بودن، CLI-only، تشخیص --force)
 */
declare(strict_types=1);

define('APP_ROOT', __DIR__ . '/..');
chdir(APP_ROOT);

$fails = 0;
$passes = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $fails, $passes;
    if ($ok) { $passes++; echo "[PASS] {$label}\n"; return; }
    $fails++;
    echo "[FAIL] {$label}" . ($detail !== '' ? " :: {$detail}" : '') . "\n";
}

require APP_ROOT . '/bootstrap.php';
require APP_ROOT . '/food-ticket-window.php';

$tz = food_ticket_window_timezone();
$cfg = [
    'window_enabled' => 1, 'window_start' => '11:00', 'window_end' => '14:00',
    'window_days' => 'all', 'window_margin_before_minutes' => 0, 'window_margin_after_minutes' => 0,
    'active_poll_ms' => 500, 'idle_poll_seconds' => 60,
];
$at = static function (string $day, string $time) use ($tz) {
    return DateTimeImmutable::createFromFormat('Y-m-d H:i', $day . ' ' . $time, $tz);
};

/* ── ۱) مرزهای پنجره (روز کاری) ───────────────────────────────────────── */
$cases = [
    ['2026-11-16', '10:59', false, 'قبل از باز شدن'],
    ['2026-11-16', '11:00', true, 'لحظهٔ باز شدن'],
    ['2026-11-16', '12:30', true, 'وسط پنجره'],
    ['2026-11-16', '13:59', true, 'آخرین دقیقه'],
    ['2026-11-16', '14:00', false, 'لحظهٔ بسته شدن (بازه [شروع، پایان) است)'],
    ['2026-11-16', '14:01', false, 'یک دقیقه بعد'],
    ['2026-11-16', '20:00', false, 'شب'],
];
$bad = [];
foreach ($cases as [$day, $time, $expectOpen, $label]) {
    $state = food_ticket_window_state($cfg, $at($day, $time));
    if ((bool) $state['open'] !== $expectOpen) {
        $bad[] = $label . ' → ' . ($state['open'] ? 'باز' : 'بسته') . ' (انتظار: ' . ($expectOpen ? 'باز' : 'بسته') . ')';
    }
}
check('۱: مرزهای پنجرهٔ ۱۱–۱۴ دقیقاً درست است', $bad === [], implode(' | ', $bad));

/* ── ۲) پنجرهٔ غیرفعال ⇒ همیشه باز ───────────────────────────────────── */
$offCfg = $cfg;
$offCfg['window_enabled'] = 0;
$offState = food_ticket_window_state($offCfg, $at('2026-11-16', '03:00'));
check('۲: با پنجرهٔ غیرفعال، Worker شبانه‌روز فعال است',
    (bool) $offState['open'] === true && (string) $offState['reason_code'] === 'disabled',
    (string) ($offState['reason_code'] ?? ''));

/* ── ۳) hameٔ روزهای هفته ────────────────────────────────────────────── */
// فقط روزهایی که در تقویم این نصب «تعطیل» نیستند؛ تعطیلات در DB تعریف می‌شوند
// و بررسی جداگانه‌ای دارند (تست ۱۶).
$days = ['2026-11-16', '2026-11-17', '2026-11-18'];
$closedDays = [];
foreach ($days as $d) {
    $s = food_ticket_window_state($cfg, $at($d, '12:00'));
    if (!(bool) $s['open']) { $closedDays[] = $d . '(' . (string) ($s['reason_code'] ?? '') . ')'; }
}
check('۳: در روزهای کاری، پنجره ساعت ۱۲ باز است', $closedDays === [], implode(', ', $closedDays));

/* ── ۴) حاشیه‌ها (margin_before/after) ───────────────────────────────── */
$marginCfg = $cfg;
$marginCfg['window_margin_before_minutes'] = 30;
$marginCfg['window_margin_after_minutes'] = 15;
$before = food_ticket_window_state($marginCfg, $at('2026-11-16', '10:30'));
$after = food_ticket_window_state($marginCfg, $at('2026-11-16', '14:14'));
$outside = food_ticket_window_state($marginCfg, $at('2026-11-16', '14:15'));
check('۴: حاشیهٔ ۳۰ دقیقه قبل و ۱۵ دقیقه بعد درست اعمال می‌شود',
    (bool) $before['open'] && (bool) $after['open'] && !(bool) $outside['open'],
    'before=' . ($before['open'] ? 'باز' : 'بسته') . ' after=' . ($after['open'] ? 'باز' : 'بسته') . ' outside=' . ($outside['open'] ? 'باز' : 'بسته'));

/* ── ۴ب) تعطیلات واقعی: پنجره باید در تعطیل بسته شود ─────────────────── */
$holidayProbe = '2026-11-19';
$holidayState = food_ticket_window_state($cfg, $at($holidayProbe, '12:00'));
$isHoliday = function_exists('food_ticket_window_is_holiday') ? food_ticket_window_is_holiday($holidayProbe) : null;
if ($isHoliday === null) {
    echo "[SKIP] تعطیلات سنجیده نشد (تابع food_ticket_window_is_holiday نیست)\n";
} elseif ($isHoliday) {
    check('۱۶: در روز تعطیل، پنجره حتی ساعت ۱۲ هم بسته است', !(bool) $holidayState['open'], (string) ($holidayState['reason_code'] ?? ''));
} else {
    check('۱۶: روز غیرتعطیل، پنجره ساعت ۱۲ باز است', (bool) $holidayState['open'], (string) ($holidayState['reason_code'] ?? ''));
}

/* ── ۵) ساختار و ایمنی خودِ ابزار ────────────────────────────────────── */
$tool = (string) file_get_contents(APP_ROOT . '/tools/prewindow_check.php');
check('۵: ابزار فقط از CLI اجرا می‌شود (جلوگیری از اجرای وب)', str_contains($tool, "PHP_SAPI !== 'cli'"));
check('۶: هیچ کوئری تغییردهنده‌ای ندارد',
    !preg_match('/\b(INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM|DROP\s+TABLE|ALTER\s+TABLE|TRUNCATE)\b/i', $tool));
check('۷: هیچ تنظیمی را تغییر نمی‌دهد',
    !str_contains($tool, 'save_setting') && !str_contains($tool, 'food_ticket_config_save'));
check('۸: از همان تابع پنجرهٔ Worker استفاده می‌کند (نه پیاده‌سازی موازی)',
    str_contains($tool, 'food_ticket_window_state'));
check('۹: برای پرینتر فقط اتصال TCP تست می‌کند و بایتی نمی‌فرستد',
    str_contains($tool, 'fsockopen') && !preg_match('/fwrite\s*\(\s*\$socket/', $tool) && !str_contains($tool, 'fputs($socket'));
check('۱۰: حالت «چاپ واقعی» ندارد (بدون --send)',
    !str_contains($tool, '--send') && !preg_match('/food_ticket_netprint_send|food_ticket_print_event/i', $tool));
check('۱۱: گزینهٔ --at برای شبیه‌سازی ساعت دارد', str_contains($tool, "--at"));
check('۱۲: حکم نهایی (آماده / هشدار / مسدودکننده) را چاپ می‌کند',
    str_contains($tool, 'آمادهٔ فردا') && str_contains($tool, 'ایراد مسدودکننده'));
check('۱۳: کد خروج غیرصفر در صورت وجود ایراد (برای اسکریپت خودکار)',
    str_contains($tool, 'exit($fails === 0 ? 0 : 1)'));
check('۱۴: موارد «printing گیرکرده» و «قفل کهنه» را می‌سنجد',
    str_contains($tool, 'printing') && str_contains($tool, 'قفل کهنه'));

/* ── ۶) درستی پیام‌های راهنما در فایل ───────────────────────────────── */
check('۱۵: مسیر اجرای ویندوزی و chcp در راهنما آمده',
    str_contains($tool, 'C:\\xampp\\php\\php.exe') && str_contains($tool, 'chcp 65001'));

echo "\n" . ($fails === 0
    ? 'همهٔ ' . ($passes + $fails) . ' بررسی «تست پیش از پنجره» موفق بود.'
    : $fails . ' بررسی ناموفق از ' . ($passes + $fails)) . "\n";
exit($fails === 0 ? 0 : 1);
