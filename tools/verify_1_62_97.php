<?php
/**
 * verify_1_62_97.php — «آیا کارت آزمون جامع بعد از نصب ۱.۶۲.۹۷ (و ۱.۶۳) سر جایش هست؟»
 *
 * اجرا (روی سرور ویندوز، از ریشهٔ برنامه):
 *   chcp 65001
 *   C:\xampp\php\php.exe tools\verify_1_62_97.php
 *
 * چرا لازم است؟  ترتیب پیشنهادی مالک این است: ۹۷ ← ری‌استارت Worker ← تست‌ها ← ۱.۶۳.
 * نصب ۱.۶۳ فایل VERSION را به «1.63» می‌نویسد. این ابزار تأیید می‌کند که این تغییر
 * هیچ اثری روی کارت آزمون نگذاشته و همهٔ تکه‌های لازم سر جایشان هستند.
 *
 * بررسی‌ها (همه فقط‌خواندنی، بدون اتصال به Access/چاپگر):
 *   ۱) فایل کتابخانه present + قابل parse
 *   ۲) food-ticket.php: بارگذاری کتابخانه، مسیر API، هندلر
 *   ۳) food-ticket-web/index.html: selftestBox + فراخوانی در connections + دو اکشن
 *   ۴) توابع لازم کتابخانه وجود دارند
 *   ۵) VERSION فعلی و نتیجهٔ «آیا فیچر به VERSION گره خورده؟»
 *
 * کد خروج: 0 = سالم | 1 = چیزی سر جایش نیست
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only — این ابزار باید از خط فرمان اجرا شود.\n");
}

$root = dirname(__DIR__);
@chdir($root);

$fails = 0;
$warns = 0;
$ok   = static function (string $s): void { echo "  [OK]   {$s}\n"; };
$info = static function (string $s): void { echo "  [i]    {$s}\n"; };
$warn = static function (string $s) use (&$warns): void { $warns++; echo "  [WARN] {$s}\n"; };
$bad  = static function (string $s) use (&$fails): void { $fails++; echo "  [FAIL] {$s}\n"; };

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  تأیید نصب «آزمون جامع Worker / چاپ / مدیریت» (۱.۶۲.۹۷)     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo ' ریشه: ' . $root . ' | زمان: ' . date('Y-m-d H:i:s') . "\n\n";

// ── ۰) نسخهٔ نصب‌شده ───────────────────────────────────────────────────
echo "۰) نسخهٔ سامانه\n";
$version = 'نامعلوم';
if (is_file($root . '/VERSION')) {
    $version = trim((string) @file_get_contents($root . '/VERSION'));
    $info('VERSION = ' . $version);
} else {
    $warn('فایل VERSION پیدا نشد.');
}
$vc = 'نامعلوم';
if (is_file($root . '/config.php')) {
    try {
        require_once $root . '/bootstrap.php';
        if (function_exists('db')) {
            $vc = (string) (db()->query("SELECT VERSION()")->fetchColumn() ?: 'نامعلوم');
        }
    } catch (Throwable $e) {
        $warn('خواندن نسخهٔ پایگاه‌داده: ' . $e->getMessage());
    }
}
$info('پایگاه‌داده = ' . (is_string($vc) ? $vc : 'نامعلوم'));

// ── ۱) کتابخانه ────────────────────────────────────────────────────────
echo "\n۱) کتابخانهٔ آزمون (food-ticket-selftest.php)\n";
$libPath = $root . '/food-ticket-selftest.php';
if (!is_file($libPath)) {
    $bad('food-ticket-selftest.php موجود نیست — بستهٔ ۱.۶۲.۹۷ (یا ۱.۶۳ r16) نصب نشده است.');
} else {
    $ok('فایل موجود است (' . number_format((int) filesize($libPath)) . ' بایت).');
    $src = (string) file_get_contents($libPath);
    $required = ['food_ticket_selftest_run', 'food_ticket_selftest_section', 'food_ticket_selftest_check', 'food_ticket_selftest_text', 'food_ticket_selftest_age_label'];
    $missing = [];
    foreach ($required as $fn) {
        if (strpos($src, 'function ' . $fn . '(') === false) {
            $missing[] = $fn;
        }
    }
    $missing === [] ? $ok('همهٔ ۵ تابع لازم تعریف شده‌اند.') : $bad('تابع‌های غایب: ' . implode(', ', $missing));

    // فقط‌خواندنی بودن را دوباره تأیید کن.
    $writes = [];
    foreach (['INSERT INTO', 'UPDATE ', 'DELETE FROM', 'TRUNCATE', 'DROP TABLE', 'file_put_contents'] as $op) {
        if (stripos($src, $op) !== false) {
            $writes[] = $op;
        }
    }
    $writes === [] ? $ok('هیچ عملیات نوشتنی ندارد (فقط‌خواندنی).') : $bad('عملیات نوشتنی پیدا شد: ' . implode(', ', $writes));
}

// ── ۲) food-ticket.php ────────────────────────────────────────────────
echo "\n۲) اتصال به food-ticket.php\n";
$hostPath = $root . '/food-ticket.php';
if (!is_file($hostPath)) {
    $bad('food-ticket.php پیدا نشد.');
} else {
    $host = (string) file_get_contents($hostPath);
    $checks = [
        'بارگذاری کتابخانه'      => strpos($host, "require_once __DIR__ . '/food-ticket-selftest.php'") !== false,
        'مسیر API worker-selftest' => (bool) preg_match("/'worker-selftest'\s*=>\s*'food\.db'/", $host),
        'هندلر API آزمون'         => strpos($host, "route === 'worker-selftest'") !== false,
    ];
    foreach ($checks as $label => $present) {
        $present ? $ok($label . ' سر جایش است.') : $bad($label . ' پیدا نشد — food-ticket.php کامل کپی نشده.');
    }
}

// ── ۳) پنل ────────────────────────────────────────────────────────────
echo "\n۳) پنل (food-ticket-web/index.html)\n";
$panelPath = $root . '/food-ticket-web/index.html';
if (!is_file($panelPath)) {
    $bad('food-ticket-web/index.html پیدا نشد.');
} else {
    $panel = (string) file_get_contents($panelPath);
    $pchecks = [
        'تابع selftestBox'          => strpos($panel, 'function selftestBox(') !== false,
        'کارت در صفحهٔ اتصال'       => strpos($panel, '${selftestBox()}') !== false,
        'دکمهٔ اجرای آزمون'         => strpos($panel, "act==='run-worker-selftest'") !== false,
        'دکمهٔ کپی گزارش'           => strpos($panel, "act==='copy-worker-selftest'") !== false,
        'اتصال به API'              => strpos($panel, "api('/api/worker-selftest')") !== false,
    ];
    foreach ($pchecks as $label => $present) {
        $present ? $ok($label . ' موجود است.') : $bad($label . ' پیدا نشد — index.html کامل کپی نشده.');
    }
}

// ── ۴) آیا چیزی به VERSION گره خورده؟ ─────────────────────────────────
echo "\n۴) استقلال از شمارهٔ نسخه (مهم برای نصب ۱.۶۳ بعد از ۹۷)\n";
$versionGated = false;
$versionRefs = [];
foreach ([$root . '/food-ticket.php', $root . '/food-ticket-selftest.php'] as $f) {
    if (!is_file($f)) {
        continue;
    }
    $s = (string) file_get_contents($f);
    // فقط «خواندن واقعی فایل VERSION از دیسک» یا version_compare را نشانه می‌گیریم.
    // FOOD_TICKET_PANEL_VERSION (نسخهٔ پنل) و تگ XML در web.config نباید مثبت کاذب بدهند.
    if (preg_match('#(?:file_get_contents|include|require)[^;\n]*/VERSION|version_compare\s*\(#i', $s)) {
        $versionGated = true;
        $versionRefs[] = basename($f);
    }
}
if ($versionGated) {
    $warn('ارجاعی به VERSION/version_compare پیدا شد در: ' . implode(', ', $versionRefs) . ' — بررسی دستی لازم است.');
} else {
    $ok('هیچ فیچر غذایی به فایل VERSION گره نخورده — نصب ۱.۶۳ (که VERSION را به 1.63 می‌نویسد) کارت را نمی‌شکند.');
}
$info('VERSION فعلی: ' . $version . ' — اگر بعد از نصب ۱.۶۳ «1.63» شد، طبیعی و بی‌خطر است.');

// ── ۵) نتیجه ──────────────────────────────────────────────────────────
echo "\n══════════════════════════════════════════════════════════════\n";
if ($fails === 0 && $warns === 0) {
    echo " ✅ کارت آزمون جامع سر جایش است و آمادهٔ استفاده.\n";
    echo " در پنل: تنظیمات سیستم ← اتصال پایگاه‌ها ← انتهای صفحه ← «اجرای آزمون جامع»\n";
    exit(0);
}
if ($fails === 0) {
    echo " ⚠️ کارت هست، ولی {$warns} هشدار برای مرور دارد.\n";
    exit(0);
}
echo " ❌ {$fails} ایراد — کارت آزمون کامل نصب نشده. فایل‌های بسته را دوباره کپی کنید.\n";
echo " (بکاپ نصب‌های قبلی: پوشهٔ backups/ — فایل‌های food-ticket.php و food-ticket-web/ را برگردانید.)\n";
exit(1);
