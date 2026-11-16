<?php
declare(strict_types=1);

/**
 * tools/repair_source_rows.php — پاک‌سازی ردیف‌های جاماندهٔ جدول تردد (SOURCE_TABLE)
 * ============================================================================
 * ۱.۶۳.۱-r18
 *
 * برای چه: پیش از این نسخه، شرط حذف برای ستون زمان برابری دقیق می‌ساخت
 * (`C_Time = #12:34:56#`) ولی Access کسری از ثانیه ذخیره می‌کند، پس ردیف
 * تطبیق نمی‌خورد و برای همیشه در جدول تردد می‌ماند. این ابزار همان
 * ردیف‌های جامانده را با منطق اصلاح‌شده پاک می‌کند.
 *
 * اجرا (روی سرور سامانه، در خط فرمان):
 *
 *     php tools/repair_source_rows.php              # فقط بررسی (هیچ چیزی پاک نمی‌شود)
 *     php tools/repair_source_rows.php --apply      # پاک‌سازی واقعی
 *     php tools/repair_source_rows.php --days=14    # بازهٔ بیشتر از ۷ روز
 *
 * پیش‌فرض «فقط بررسی» است. تا وقتی خروجی را نگرفته‌اید --apply نزنید.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("این ابزار فقط از خط فرمان اجرا می‌شود.\n");
}

$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';
require_once $root . '/food-ticket.php';
if (is_file($root . '/food-ticket-engine.php')) {
    require_once $root . '/food-ticket-engine.php';
}

$apply = in_array('--apply', array_slice($argv, 1), true);
$days = 7;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--days=(\d+)$/', (string) $arg, $m) === 1) {
        $days = max(1, min(60, (int) $m[1]));
    }
}
$limit = 60;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--limit=(\d+)$/', (string) $arg, $m) === 1) {
        $limit = max(1, min(300, (int) $m[1]));
    }
}

fwrite(STDOUT, "\n=== پاک‌سازی ردیف‌های جاماندهٔ جدول تردد ===\n");
fwrite(STDOUT, 'حالت: ' . ($apply ? "پاک‌سازی واقعی (--apply)\n" : "فقط بررسی — هیچ چیزی پاک نمی‌شود\n"));
fwrite(STDOUT, "بازه: {$days} روز گذشته | سقف: {$limit} ردیف\n\n");

try {
    $result = food_ticket_cleanup_source_rows($days, !$apply, $limit);
} catch (Throwable $e) {
    fwrite(STDERR, "خطا: " . $e->getMessage() . "\n");
    fwrite(STDERR, "اگر پیام «مسیر فایل منبع تردد تنظیم نشده است» بود، اول در تنظیمات ← سفارش غذا مسیر فایل Access را وارد کنید.\n");
    exit(1);
}

fwrite(STDOUT, 'حالت اجرا      : ' . (string) ($result['mode'] ?? '') . "\n");
fwrite(STDOUT, 'بررسی‌شده      : ' . (int) ($result['checked'] ?? 0) . "\n");
fwrite(STDOUT, 'تطبیق‌داده‌شده   : ' . (int) ($result['matched'] ?? 0) . "\n");
fwrite(STDOUT, 'حذف‌شده        : ' . (int) ($result['deleted'] ?? 0) . "\n");
fwrite(STDOUT, 'ناموفق         : ' . (int) ($result['failed'] ?? 0) . "\n");
fwrite(STDOUT, 'رد‌شده         : ' . (int) ($result['skipped'] ?? 0) . "\n");

$items = is_array($result['items'] ?? null) ? $result['items'] : [];
if ($items !== []) {
    fwrite(STDOUT, "\nجزئیات:\n");
    $shown = 0;
    foreach ($items as $item) {
        if ($shown >= 25) {
            fwrite(STDOUT, '  … و ' . (count($items) - 25) . " مورد دیگر\n");
            break;
        }
        $shown++;
        if (is_array($item)) {
            $line = json_encode($item, JSON_UNESCAPED_UNICODE);
            fwrite(STDOUT, '  • ' . mb_substr((string) $line, 0, 220) . "\n");
        }
    }
}

$deleted = (int) ($result['deleted'] ?? 0);
$failed = (int) ($result['failed'] ?? 0);
fwrite(STDOUT, "\n");
if (!$apply && (int) ($result['matched'] ?? 0) > 0) {
    fwrite(STDOUT, "برای پاک‌سازی واقعی همین دستور را با --apply اجرا کنید:\n");
    fwrite(STDOUT, "    php tools/repair_source_rows.php --apply\n\n");
} elseif ($apply && $deleted > 0 && $failed === 0) {
    fwrite(STDOUT, "✓ {$deleted} ردیف پاک شد. حالا جدول تردد باید خالی از ردیف‌های مصرف‌شده باشد.\n\n");
} elseif ($failed > 0) {
    fwrite(STDOUT, "⚠ {$failed} ردیف پاک نشد. دلیل هر کدام در فایل لاگ سامانه ثبت شده است:\n");
    fwrite(STDOUT, "    app_error_log → '[food-ticket] delete_source FAILED'\n");
}

exit(0);
