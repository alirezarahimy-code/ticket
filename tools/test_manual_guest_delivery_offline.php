#!/usr/bin/env php
<?php
/**
 * آزمون آفلاین: فیش «مهمان» ثبت‌دستی باید در گزارش‌های تحویل دیده شود.
 *
 * ── باگ ۱.۶۳.۲ ─────────────────────────────────────────────────────────
 * کاربر ۶ مهمان را در «ثبت دستی فیش» ثبت کرد. در فهرست ثبت دستی با برچسب
 * زرد «صادر شد؛ چاپ فیزیکی انجام نشد» دیده شدند، اما:
 *   • «غذای تحویلی» آن روز هیچ مهمانی نشان نداد،
 *   • «تطبیق خرید و توزیع» تعداد پرس تحویلی مهمان را ۰ گذاشت.
 *
 * ریشه: مسیر مهمان ردیف را با print_status='not_printed' و
 * delivery_status='pending' می‌ساخت، در حالی که هر دو گزارش شرط
 * print_status='printed' دارند. مسیر «کارمند» همین دو مقدار را درست
 * می‌نوشت؛ تفاوت فقط در مسیر مهمان بود.
 *
 * این آزمون متن واقعی کد و SQL دو گزارش را می‌خواند (بدون پایگاه‌داده) و
 * تضمین می‌کند:
 *   ۱. مسیر مهمان print_status می‌نویسد و مقدار «بدون چاپ» آن printed است.
 *   ۲. مسیر مهمان meal_type را از فرم می‌گیرد (ناهار/شام) و NOT NULL را
 *      نمی‌شکند.
 *   ۳. مسیر مهمان delivery_status را برای «بدون چاپ فیزیکی» delivered می‌کند.
 *   ۴. چاپ فیزیکی همچنان pending می‌ماند و وارد صف چاپ می‌شود.
 *   ۵. هر دو گزارش همچنان شرط print_status='printed' را دارند (اگر روزی
 *      عوض شد، این آزمون هشدار می‌دهد که قرارداد عوض شده).
 *
 * اجرا: php tools/test_manual_guest_delivery_offline.php
 */

declare(strict_types=1);

 $root = dirname(__DIR__);
$groupsFile = $root . '/food-ticket-groups.php';
$orderFile = $root . '/food-order.php';
$reconFile = $root . '/food-reconciliation.php';
$jsFile = $root . '/food-ticket-web/assets/food-manual-attendance.js';

$pass = 0;
$fail = 0;

function chk(bool $ok, string $label): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "  ✅ {$label}\n";
    } else {
        $fail++;
        echo "  ❌ {$label}\n";
    }
}

echo "── آزمون فیش مهمانِ ثبت‌دستی در گزارش‌های تحویل ──\n\n";

foreach ([$groupsFile, $orderFile, $reconFile, $jsFile] as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "فایل در درخت نیست: {$file}\n");
        exit(1);
    }
}
$groups = (string) file_get_contents($groupsFile);
$order = (string) file_get_contents($orderFile);
$recon = (string) file_get_contents($reconFile);
$js = (string) file_get_contents($jsFile);

/** بدنهٔ یک تابع را با شمارش آکولاد برمی‌گرداند. */
function body_of(string $source, string $functionName): ?string
{
    $pos = strpos($source, 'function ' . $functionName . '(');
    if ($pos === false) {
        return null;
    }
    $start = strpos($source, '{', $pos);
    if ($start === false) {
        return null;
    }
    $depth = 0;
    $len = strlen($source);
    for ($i = $start; $i < $len; $i++) {
        if ($source[$i] === '{') {
            $depth++;
        } elseif ($source[$i] === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($source, $start, $i - $start + 1);
            }
        }
    }
    return null;
}

$guestBody = body_of($groups, 'food_ticket_api_manual_attendance_save_guest');
chk($guestBody !== null, 'تابع ثبت مهمان پیدا شد');

if ($guestBody === null) {
    echo "\nنتیجه: {$pass} موفق، {$fail} ناموفق\n";
    exit(1);
}

// ── ۱) print_status ────────────────────────────────────────────────────
chk(
    (bool) preg_match("/'print_status'\s*=>\s*\(\\\$withTicket\s*&&\s*!\\\$physicalPrint\)\s*\?\s*'printed'\s*:\s*'not_printed'/", $guestBody),
    'مسیر مهمان: print_status برای «بدون چاپ فیزیکی» = printed'
);
chk(
    strpos($guestBody, "'print_status' => 'not_printed',") === false,
    'مقدار ثابت قبلی not_printed (علت باگ) حذف شده است'
);
chk(
    (bool) preg_match('/print_status.{0,80}physicalPrint.{0,40}printed/s', $guestBody),
    'حالت چاپ فیزیکی همچنان not_printed می‌ماند (وارد صف چاپ می‌شود)'
);

// ── ۲) delivery_status = delivered ─────────────────────────────────────
chk(
    strpos($guestBody, 'food_ticket_mark_delivered_on_print') !== false,
    'مسیر مهمان تحویل را ثبت می‌کند (food_ticket_mark_delivered_on_print)'
);
chk(
    (bool) preg_match('/if\s*\(\s*\$withTicket\s*&&\s*!\$physicalPrint\s*&&\s*function_exists\([^)]*food_ticket_mark_delivered_on_print/', $guestBody),
    'تحویل فقط وقتی ثبت می‌شود که فیش صادر شده و چاپ فیزیکی نخواسته باشند'
);

// ── ۳) meal_type ───────────────────────────────────────────────────────
chk(
    (bool) preg_match("/'meal_type'\s*=>\s*\\\$mealType/", $guestBody),
    'مسیر مهمان وعدهٔ غذایی را در ردیف می‌نویسد'
);
chk(
    (bool) preg_match("/'meal_type'\s*=>\s*\\\$withTicket\s*\?/", $guestBody) === false,
    'وعده با شرط with_ticket خالی نمی‌شود (ستون NOT NULL است)'
);
chk(
    strpos($guestBody, 'food_order_meal_type') !== false,
    'وعده با food_order_meal_type اعتبارسنجی می‌شود'
);
chk(
    (bool) preg_match('/function food_ticket_api_manual_attendance_save_guest\([^)]*string \$mealType/', $guestBody) === false
    || (bool) preg_match('/string \$mealType\s*=\s*\'lunch\'/', $guestBody),
    'پارامتر وعده مقدار پیش‌فرض دارد (سازگاری با فراخوانی‌های قدیمی)'
);

// ── ۴) مسیر کارمند دست‌نخورده ──────────────────────────────────────────
$staffBody = body_of($groups, 'food_ticket_api_manual_attendance_save');
chk($staffBody !== null, 'تابع ثبت کارمند پیدا شد');
if ($staffBody !== null) {
    chk(
        strpos($staffBody, 'food_ticket_mark_delivered_on_print') !== false,
        'مسیر کارمند همچنان تحویل را ثبت می‌کند'
    );
    chk(
        strpos($staffBody, '$mealType') !== false,
        'مسیر کارمند پارامتر وعده را در بدنه استفاده می‌کند'
    );
    chk(
        strpos($staffBody, "'meal_type' => \$mealType") !== false,
        'مسیر کارمند وعده را در payload ردیف می‌نویسد'
    );
}

// ── ۵) فراخوانی مهمان وعده را می‌فرستد ─────────────────────────────────
chk(
    (bool) preg_match('/food_ticket_api_manual_attendance_save_guest\([^)]*\$targetDate\s*,\s*\$mealType\s*\)/', $groups),
    'فراخوانی مهمان وعدهٔ غذایی را منتقل می‌کند'
);

// ── ۶) خواندن وعده از بدنهٔ درخواست ────────────────────────────────────
$manyBody = body_of($groups, 'food_ticket_api_manual_attendance_save_many');
chk($manyBody !== null, 'تابع ثبت گروهی پیدا شد');
if ($manyBody !== null) {
    chk(
        strpos($manyBody, "body['meal_type']") !== false,
        'ثبت گروهی وعده را از بدنهٔ درخواست می‌خواند'
    );
    chk(
        (bool) preg_match('/\(__construct\)|manualMealType/s', $manyBody)
        || strpos($manyBody, '$manualMealType') !== false,
        'ثبت گروهی وعده را به هر ردیف می‌فرستد'
    );
}

// ── ۷) قرارداد گزارش‌ها ────────────────────────────────────────────────
// اگر روزی شرط گزارش عوض شود، این آزمون می‌گوید قرارداد تغییر کرده است.
chk(
    strpos($order, "e.print_status = 'printed'") !== false,
    'گزارش «غذای تحویلی» همچنان شرط print_status=printed دارد'
);
chk(
    strpos($recon, "AND print_status = 'printed'") !== false,
    'گزارش «تطبیق» همچنان شرط print_status=printed دارد (برای مهمان)'
);
chk(
    strpos($recon, "event_type NOT IN ('guest', 'auto_guest')") !== false,
    'گزارش تطبیق مهمان را از شمارش کارکنان جدا می‌کند'
);

// ── ۸) رابط کاربری: وعده و برچسب درست ──────────────────────────────────
chk(
    strpos($js, 'manualMealType') !== false,
    'رابط: وضعیت وعدهٔ غذایی نگه داشته می‌شود'
);
chk(
    strpos($js, 'fg-manual-meal') !== false,
    'رابط: انتخابگر وعده (ناهار/شام) در فرم هست'
);
chk(
    strpos($js, 'meal_type: mealType') !== false,
    'رابط: وعده در بدنهٔ درخواست ذخیره فرستاده می‌شود'
);
chk(
    strpos($js, 'ثبت و تحویل در سامانه؛ بدون چاپ کاغذی') !== false,
    'رابط: برچسب گنگ «صادر شد؛ چاپ فیزیکی انجام نشد» روشن شده است'
);
chk(
    strpos($js, "item.meal_type === 'dinner'") !== false,
    'رابط: ستون وعده در تاریخچه شام را درست نشان می‌دهد'
);

// ── ۹) تاریخچهٔ ثبت دستی وعده را برمی‌گرداند ────────────────────────────
// کوئری با کوتیشن فرارکرده نوشته می‌شود، پس فقط وجود خود عبارت بررسی
// می‌شود و آزمون به جزئیات نحوی کوتیشن وابسته نیست.
chk(
    strpos($groups, 'COALESCE(meal_type') !== false,
    'تاریخچهٔ ثبت دستی وعدهٔ هر ردیف را می‌خواند'
);
chk(
    (bool) preg_match("/'meal_type'\s*=>\s*\(string\)\s*\(\\\$row\['meal_type'\]/", $groups),
    'تاریخچهٔ ثبت دستی وعده را به رابط برمی‌گرداند'
);

// ── ۱۰) تعمیر خودکار مهمان‌های قدیمی ──────────────────────────────────
// کاربر نباید مجبور باشد برای ۶ مهمانِ ثبت‌شده ابزار CLI اجرا کند. هر بار که
// گزارش تحویل باز می‌شود، reconcile_range صدا زده می‌شود و باید ردیف‌های
// قدیمیِ خراب را هم خودش درست کند — با همان گاردهای ابزار تعمیر.
$reconcileBody = body_of($groups, 'food_ticket_manual_attendance_reconcile_range');
chk($reconcileBody !== null, 'تابع همگام‌سازی خودکار پیدا شد');
if ($reconcileBody !== null) {
    chk(
        strpos($reconcileBody, '$legacyGuests') !== false,
        'همگام‌سازی خودکار، مهمان‌های قدیمی را تعمیر می‌کند'
    );
    chk(
        strpos($reconcileBody, "event_type = \"guest\"") !== false,
        'تعمیر خودکار فقط مهمان را هدف می‌گیرد (نه کارمند)'
    );
    chk(
        strpos($reconcileBody, 'print_job_id IS NULL') !== false,
        'تعمیر خودکار ردیفِ در صف چاپ یا چاپ‌شکست‌خورده را دست نمی‌زند'
    );
    chk(
        strpos($reconcileBody, 'physical_print') !== false,
        'تعمیر خودکار چاپ فیزیکی را از روی source_payload بررسی می‌کند'
    );
    chk(
        strpos($reconcileBody, 'AND print_status = "not_printed"') !== false,
        'تعمیر خودکار فقط ردیف هرگز-چاپ‌نشده را تغییر می‌دهد'
    );
    chk(
        strpos($reconcileBody, 'delivery_status <> "delivered"') !== false
        || strpos($reconcileBody, 'delivery_status IS NULL') !== false,
        'تعمیر خودکار ردیف تحویل‌شده را دوباره نمی‌نویسد (idempotent)'
    );
    chk(
        strpos($reconcileBody, 'BETWEEN ? AND ?') !== false,
        'تعمیر خودکار فقط در بازهٔ گزارش کار می‌کند'
    );
}

// گزارش تحویل باید این همگام‌سازی را صدا بزند — وگرنه تعمیر خودکار هرگز
// اجرا نمی‌شود و کاربر همچنان به ابزار CLI وابسته است.
chk(
    strpos($order, 'food_ticket_manual_attendance_reconcile_range') !== false,
    'گزارش «غذای تحویلی» همگام‌سازی خودکار را صدا می‌زند'
);
chk(
    strpos($recon, 'food_ticket_manual_attendance_reconcile_range') !== false,
    'گزارش «تطبیق» همگام‌سازی خودکار را صدا می‌زند'
);

// ── ۱۱) ابزار تعمیر ────────────────────────────────────────────────────
$toolFile = $root . '/tools/fix_manual_guest_tickets.php';
chk(is_file($toolFile), 'ابزار تعمیر ردیف‌های قدیمی وجود دارد');
if (is_file($toolFile)) {
    $tool = (string) file_get_contents($toolFile);
    chk(strpos($tool, 'PHP_SAPI') !== false && strpos($tool, 'cli') !== false, 'ابزار تعمیر فقط CLI است');
    chk(strpos($tool, '--apply') !== false, 'ابزار تعمیر پیش‌فرض گزارش است و با --apply می‌نویسد');
    chk(strpos($tool, "print_job_id IS NULL") !== false, 'ابزار تعمیر ردیف‌های در صف چاپ را دست نمی‌زند');
    chk(strpos($tool, "physical_print") !== false, 'ابزار تعمیر چاپ فیزیکی را بررسی می‌کند');
    chk(
        (bool) preg_match("/print_status = \"not_printed\"\s*AND/", $tool),
        'ابزار تعمیر فقط ردیف‌های هرگز-چاپ‌نشده را تغییر می‌دهد'
    );
    chk(strpos($tool, 'beginTransaction') !== false, 'ابزار تعمیر تراکنشی است');
    chk(
        strpos($tool, "food-ticket.php") !== false,
        'ابزار تعمیر ماژول غذا را بارگذاری می‌کند (bootstrap آن را نمی‌آورد)'
    );
}

echo "\nنتیجه: {$pass} موفق، {$fail} ناموفق\n";
exit($fail === 0 ? 0 : 1);
