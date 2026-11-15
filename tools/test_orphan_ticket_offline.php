<?php
declare(strict_types=1);

// آزمون آفلاین «فیش یتیم» — فیشی که در صف چاپ ماند و هرگز چاپ نشد.
//
// رخداد واقعی (۱۴۰۵/۰۸/۲۵): پرینتر خراب شد و کارکنانی که تردد کرده بودند در صف
// چاپ ماندند. کاربر خواست همان روز را دستی ثبت کند، ولی:
//   ۱) فرانت آن‌ها را «دارای فیش این روز» نشان می‌داد و چک‌باکس غیرفعال بود.
//   ۲) سرور هر رویداد غیر از no_food را «تکراری» می‌دانست.
//   ۳) تصمیم‌گیر موتور هم repeat برمی‌گرداند.
//   ۴) گزارش تحویل آن‌ها را نشان نمی‌داد (چون تحویل ثبت نشده بود).
// نتیجه: غذا نه در گزارش بود و نه راهی برای اصلاح.
//
// این آزمون تضمین می‌کند:
//   الف) فیش واقعاً چاپ‌شده «یتیم» شمرده نمی‌شود (دست نمی‌خورد).
//   ب) فیش در صف (pending/printing/print_error/failed/held_absent) یتیم است.
//   ج) هر چهار لایه همان قاعده را دارند — نه سه‌تای هم‌خوان و یک ناهم‌خوان.
//   د) تبدیل همان ردیف انجام می‌شود، نه ساخت ردیف دوم.

$root = dirname(__DIR__);
$fails = 0;
$checks = 0;

function ok(bool $cond, string $label): void
{
    global $fails, $checks;
    $checks++;
    echo ($cond ? "  ✅ " : "  ❌ ") . $label . "\n";
    if (!$cond) {
        $fails++;
    }
}

$groupsFile = $root . '/food-ticket-groups.php';
$engineFile = $root . '/food-ticket-engine.php';
$jsFile = $root . '/food-ticket-web/assets/food-manual-attendance.js';

foreach ([$groupsFile, $engineFile, $jsFile] as $f) {
    ok(is_file($f), 'فایل موجود است: ' . basename($f));
}

$groupsSrc = (string) file_get_contents($groupsFile);
$engineSrc = (string) file_get_contents($engineFile);
$jsSrc = (string) file_get_contents($jsFile);

// ─────────────────────────────────────────────────────────────────────
echo "\n۱) تابع تشخیص فیش یتیم — همان منطقی که روی نصب اجرا می‌شود\n";
// ─────────────────────────────────────────────────────────────────────
// تابع را از فایل اصلی استخراج و همان‌جا اجرا می‌کنیم (نه بازنویسی).
require_once $groupsFile;
ok(function_exists('food_ticket_event_is_orphan_ticket'), 'تابع food_ticket_event_is_orphan_ticket() تعریف شده است');

$isOrphan = 'food_ticket_event_is_orphan_ticket';

// چاپ‌شده ⇒ فیش واقعی، دست نزن
ok($isOrphan(['print_status' => 'printed', 'delivery_status' => 'delivered']) === false,
    'چاپ‌شده و تحویل‌شده ⇒ یتیم نیست');
ok($isOrphan(['print_status' => 'printed', 'delivery_status' => 'pending']) === false,
    'چاپ‌شده ولی تحویل‌نشده ⇒ باز هم یتیم نیست (فیش کاغذی وجود دارد)');
ok($isOrphan(['print_status' => 'pending', 'delivery_status' => 'delivered']) === false,
    'تحویل‌شده ⇒ یتیم نیست، حتی اگر وضعیت چاپ نامشخص باشد');

// در صف چاپ ⇒ یتیم
ok($isOrphan(['print_status' => 'pending', 'delivery_status' => 'pending']) === true,
    'در صف چاپ (pending) ⇒ یتیم است');
ok($isOrphan(['print_status' => 'printing', 'delivery_status' => 'pending']) === true,
    'در حال چاپ (printing) ⇒ یتیم است');
ok($isOrphan(['print_status' => 'print_error', 'delivery_status' => 'pending']) === true,
    'خطای چاپ (print_error) ⇒ یتیم است');
ok($isOrphan(['print_status' => 'failed', 'delivery_status' => 'pending']) === true,
    'چاپ ناموفق (failed) ⇒ یتیم است');
ok($isOrphan(['print_status' => 'held_absent', 'delivery_status' => 'pending']) === true,
    'چاپ متوقف‌شده (held_absent) ⇒ یتیم است');
ok($isOrphan(['print_status' => '', 'delivery_status' => 'pending']) === true,
    'وضعیت چاپ خالی ⇒ یتیم است');

// ورودی نامعتبر
ok($isOrphan(null) === false, 'ورودی null ⇒ یتیم نیست');
ok($isOrphan([]) === false, 'آرایهٔ خالی ⇒ یتیم نیست');

// ─────────────────────────────────────────────────────────────────────
echo "\n۲) هر چهار لایه از همین قاعده استفاده می‌کنند\n";
// ─────────────────────────────────────────────────────────────────────
// لایهٔ ۱ — تشخیص تکراری در food_ticket_groups
ok(strpos($groupsSrc, '$replaceOrphanTicket = $existingToday !== null') !== false,
    'لایهٔ ۱: تشخیص تکراری، فیش یتیم را جدا می‌کند');
ok(strpos($groupsSrc, '&& food_ticket_event_is_orphan_ticket($existingToday);') !== false,
    'لایهٔ ۱: از همان تابع مشترک استفاده می‌کند');
ok(strpos($groupsSrc, 'if ($existingToday !== null && !$replaceNoFood && !$replaceOrphanTicket) {') !== false,
    'لایهٔ ۱: فیش یتیم از خطای «تکراری» مستثنا شده است');

// لایهٔ ۲ — گارد ticket_key
ok(strpos($groupsSrc, 'if (!food_ticket_event_is_orphan_ticket($existingTicket)) {') !== false,
    'لایهٔ ۲: گارد ticket_key، فیش یتیم را رد نمی‌کند');

// لایهٔ ۳ — تبدیل همان ردیف
ok(strpos($groupsSrc, 'if ($replaceNoFood || $replaceOrphanTicket) {') !== false,
    'لایهٔ ۳: فیش یتیم همان ردیف را تبدیل می‌کند (نه ردیف دوم)');
ok(strpos($groupsSrc, 'WHERE id = ? AND print_status <> "printed" AND (delivery_status IS NULL OR delivery_status <> "delivered")') !== false,
    'لایهٔ ۳: به‌روزرسانی، بین چاپ‌نشده و چاپ‌شده تفاوت می‌گذارد');
ok(strpos($groupsSrc, 'converted_from_orphan_ticket') !== false,
    'لایهٔ ۳: تبدیل در source_payload ثبت می‌شود');

// لایهٔ ۴ — تصمیم‌گیر موتور
ok(strpos($engineSrc, 'food_ticket_event_is_orphan_ticket($existingTicket)') !== false,
    'لایهٔ ۴: تصمیم‌گیر موتور فیش یتیم را «تکرار» نمی‌داند');
ok(strpos($engineSrc, '$existingTicket = null;') !== false,
    'لایهٔ ۴: فیش یتیم در تصمیم‌گیر نادیده گرفته می‌شود');

// ─────────────────────────────────────────────────────────────────────
echo "\n۳) فرانت: چک‌باکس روی فیش یتیم فعال است\n";
// ─────────────────────────────────────────────────────────────────────
ok(strpos($jsSrc, 'const isOrphanTicket = (u) =>') !== false,
    'تابع isOrphanTicket در پنل تعریف شده است');
ok(strpos($jsSrc, 'isReplaceableOrphan') !== false,
    'ردیف یتیم «قابل جایگزینی» است');
ok(strpos($jsSrc, '|| isReplaceableOrphan(u))') !== false,
    'قابل انتخاب بودن ردیف، فیش یتیم را می‌پذیرد');
ok(strpos($jsSrc, "'pending', 'printing', 'print_error', 'failed', 'held_absent', ''") !== false,
    'همان فهرست وضعیت‌های صف که سرور دارد');
ok(strpos($jsSrc, "if (ps === 'printed' || ds === 'delivered') return false;") !== false,
    'فرانت هم چاپ‌شده/تحویل‌شده را کنار می‌گذارد');

// برچسب وضعیت نباید «دارای فیش این روز» باشد
ok(strpos($jsSrc, 'قابل ثبت دستی') !== false,
    'برچسب «قابل ثبت دستی» اضافه شد');
ok(preg_match('/if \(record\.event_type === \'printed\'\) \{\s*if \(isOrphanTicket\(u\)\)/s', $jsSrc) === 1,
    'برچسب چاپ‌نشده، پیش از «دارای فیش» بررسی می‌شود');

// payload سرور باید delivery_status بدهد
ok(strpos($groupsSrc, "'delivery_status' => (string) (\$row['delivery_status'] ?? '')") !== false,
    'payload فهرست، delivery_status را می‌فرستد (بدون آن فرانت نمی‌تواند تشخیص دهد)');
ok(strpos($groupsSrc, 'SELECT id, user_id, event_type, print_status, delivery_status, source_uid FROM food_ticket_events') !== false,
    'ستون delivery_status در کوئری فهرست خوانده می‌شود');

// ─────────────────────────────────────────────────────────────────────
echo "\n۴) دکمهٔ ثبت گروهی فیش‌های چاپ‌نشده\n";
// ─────────────────────────────────────────────────────────────────────
// رخداد واقعی یعنی ده‌ها نفر یک‌جا در صف مانده‌اند. کلیک نفر‌به‌نفر عملاً همان
// ثبت دستی است که کاربر نمی‌خواهد؛ یک دکمه باید همه را انتخاب کند.
ok(strpos($jsSrc, 'manual-select-orphans') !== false,
    'دکمهٔ «ثبت گروهی … فیشِ چاپ‌نشده» وجود دارد');
// شمارش از کل کارکنان است، نه از نتیجهٔ جستجو: اگر کاربر چیزی تایپ کرده
// باشد، ردیف یتیم بیرون از نتیجه می‌ماند و دکمه ناپدید می‌شد.
ok(strpos($jsSrc, "const orphanRows = S.manualEmployees.filter((u) => isOrphanTicket(u) && canIssueManualTicket());") !== false,
    'شمارش فیش‌های یتیم از کل کارکنان است (مستقل از جستجو)');
ok(strpos($jsSrc, "const orphanRows = filtered.filter(") === false,
    'شمارش دیگر به نتیجهٔ جستجو وابسته نیست');
ok(preg_match("/manual-select-orphans'\]?\)?.*?isOrphanTicket\(u\)/s", $jsSrc) === 1
   || strpos($jsSrc, "if (act === 'manual-select-orphans')") !== false,
    'هندلر دکمه فیش‌های یتیم را انتخاب می‌کند');
ok(strpos($jsSrc, 'S.manualWithTicket = true;') !== false,
    'دکمه حالت «با فیش» را روشن می‌کند (وگرنه تبدیل انجام نمی‌شود)');
ok(strpos($jsSrc, 'فیش‌های چاپ‌نشدهٔ صف چاپ به‌صورت گروهی ثبت شدند.') !== false,
    'علت پیش‌فرض برای ثبت گروهی پر می‌شود');
ok(strpos($jsSrc, 'orphanCount') !== false,
    'تعداد فیش‌های چاپ‌نشده روی دکمه نمایش داده می‌شود');

// ─────────────────────────────────────────────────────────────────────
echo "\n۴.۵) کش مرورگر — وگرنه هیچ‌کدام از این اصلاحات دیده نمی‌شود\n";
// ─────────────────────────────────────────────────────────────────────
// اگر index.html همان ?v= قدیمی را نگه دارد، مرورگر فایل JS قدیمی را از کش
// می‌خواند و کاربر چک‌باکس‌های قفل را می‌بیند — یعنی باگ «حل‌شده» به‌نظر می‌رسد
// ولی در عمل هنوز هست. این بررسی مانع تکرار آن است.
$htmlFile = $root . '/food-ticket-web/index.html';
if (is_file($htmlFile)) {
    $html = (string) file_get_contents($htmlFile);
    ok(preg_match('/assets\/food-manual-attendance\.js\?v=(\d+)/', $html, $m) === 1,
        'food-manual-attendance.js با شمارهٔ نسخه بارگذاری می‌شود (کش‌شکنی)');
    $assetVer = isset($m[1]) ? (int) $m[1] : 0;
    ok($assetVer >= 10, 'نسخهٔ کش حداقل ۱۰ است (v=' . $assetVer . ') — تغییر JS دیده می‌شود');
    // فیلد تاریخ «شمسی» است ولی API میلادی می‌خواهد. اگر تجزیه‌گر شمسی را
    // نشناسد، تاریخ بی‌صدا عوض نمی‌شود و فهرست روی «امروز» قفل می‌ماند —
    // نتیجه: فیش‌های یتیم دیروز هرگز دیده نمی‌شوند. کاربر همین را گزارش کرد.
    $jsFile = $root . '/food-ticket-web/assets/food-manual-attendance.js';
    if (is_file($jsFile)) {
        $js = (string) file_get_contents($jsFile);
        ok(strpos($js, 'ItsmJalali') !== false,
            'تجزیه‌گر تاریخ ورودی، شمسی را می‌شناسد (ItsmJalali)');
        ok(strpos($js, 'j2g') !== false,
            'تبدیل شمسی → میلادی در فیلد تاریخ ثبت دستی انجام می‌شود');
        // ردیف یتیم نباید کد پرسنلی بخواهد؛ فیشش از قبل ساخته شده و
        // فقط منتظر تحویل است. بدون این استثنا، کاربران بدون کد پرسنلی
        // از انتخاب حذف می‌شدند و دکمهٔ گروهی برایشان ظاهر نمی‌شد.
        ok(strpos($js, '|| isReplaceableOrphan(u)') !== false,
            'ردیف یتیم بدون کد پرسنلی هم قابل انتخاب است');
        // کارت «ثبت خودکار فیش روزهای گذشته» هم باید شمسی بگیرد. با
        // type="date" تقویم میلادی باز می‌شد و کاربر «۱ آبان» را میلادی
        // وارد می‌کرد؛ سرور آن را با «بازهٔ تاریخ معتبر نیست» رد می‌کرد.
        ok(strpos($html, 'از تاریخ (شمسی)') !== false,
            'کارت backfill برچسب «شمسی» دارد (نه تقویم میلادی)');
        ok(strpos($html, 'jalaliToIso') !== false,
            'کارت backfill تاریخ شمسی را پیش از ارسال به میلادی تبدیل می‌کند');
        // تقویم بازشو: بدون data-jalali فقط کادر متنی ساده بود و کاربر
        // مجبور می‌شد تاریخ را دستی تایپ کند.
        ok(preg_match('/id="ft-backfill-from"[^>]*data-jalali/', $html) === 1,
            'فیلد «از تاریخ» تقویم بازشو دارد (data-jalali)');
        ok(preg_match('/id="ft-backfill-to"[^>]*data-jalali/', $html) === 1,
            'فیلد «تا تاریخ» تقویم بازشو دارد (data-jalali)');
        ok(strpos($html, '#ft-backfill-from, #ft-backfill-to') !== false,
            'فیلدهای backfill در enhanceDateFields ثبت می‌شوند (پس از هر رندر)');
    }
} else {
    echo "  ⏭  index.html روی این نصب نیست — بررسی کش انجام نشد\n";
}

// ─────────────────────────────────────────────────────────────────────
echo "\n۴.۶) مسیر ثبت — پنج مانعی که ثبت گروهی را بی‌صدا رد می‌کرد\n";
// ─────────────────────────────────────────────────────────────────────
// این پنج مورد با آزمون زنده روی نصب واقعی پیدا شدند؛ هر کدام به‌تنهایی
// کافی بود که «ثبت گروهی» کار نکند، بدون این‌که پیام روشنی به کاربر برسد.
$groupsFile = $root . '/food-ticket-groups.php';
if (is_file($groupsFile)) {
    $groups = (string) file_get_contents($groupsFile);
    // ۱) خطای سفارش کل فهرست را نمی‌کشد.
    ok(strpos($groups, '$orderMapError = \'\';') !== false,
        'خطای نقشهٔ سفارش کل فهرست ثبت دستی را از کار نمی‌اندازد');
    // ۲) ردیفی که رخداد دارد از فهرست حذف نمی‌شود.
    ok(strpos($groups, 'if (isset($attendanceByUser[$uid])) {') !== false,
        'ردیف دارای رخداد حتی بدون سفارش در فهرست می‌ماند');
    // ۳) فیش یتیم بدون کد پرسنلی هم ثبت می‌شود.
    ok(strpos($groups, '$employeeNumber === \'\' && $existingOrphan === null') !== false,
        'فیش یتیم بدون کد پرسنلی قابل ثبت است');
    // ۴) فیش یتیم از موتور تصمیم نمی‌پرسد.
    ok(strpos($groups, '$decision = [\'print\' => true, \'payload\' => $base];') !== false,
        'فیش یتیم بدون پرسیدن از موتور تصمیم ثبت می‌شود');
    // ۵) ticket_key ردیف یتیم بازنویسی نمی‌شود.
    ok(strpos($groups, '$updated->execute($replaceOrphanTicket') !== false,
        'ticket_key ردیف یتیم حفظ می‌شود (UNIQUE نمی‌شکند)');
    // not_printed مقدار پیش‌فرض ستون است؛ ردیفی که هرگز به صف نرسید همین را
    // دارد و همان ردیفی است که کاربر باید نجاتش دهد. جا افتادنش یعنی
    // ردیف‌های واقعی خرابیِ چاپگر «یتیم» شمرده نمی‌شدند.
    ok(strpos($groups, "'not_printed', 'pending', 'printing'") !== false,
        'سرور not_printed را هم یتیم می‌شمارد');
    $jsOrphan = $root . '/food-ticket-web/assets/food-manual-attendance.js';
    if (is_file($jsOrphan)) {
        ok(strpos((string) file_get_contents($jsOrphan), "'not_printed', 'pending', 'printing'") !== false,
            'کلاینت not_printed را هم یتیم می‌شمارد (هم‌تراز سرور)');
    }
    // mount نباید بی‌صدا شکست بخورد.
    ok(strpos((string) file_get_contents($root . '/food-ticket-web/assets/food-manual-attendance.js'),
        'اتصال به سرور برقرار نشد؛ صفحه را از پنل اصلی باز کنید.') !== false,
        'mount در نبود API خطا نشان می‌دهد (بی‌صدا برنمی‌گردد)');
    // ── فاجعهٔ داده: کسی که غایب بود از فهرست غایبان ناپدید می‌شد ──
    // reconcile هر فیش printed را علامت «حاضر» می‌گرفت و رکورد غیبت را
    // حذف می‌کرد. نتیجه: ۲۷ غایب حتمی → ۲۳ ردیف، و غایب‌ها «دارای فیش».
    ok(strpos($groups, 'e.source_uid = "manual-attendance" AND e.event_type IN ("printed", "no_food")') !== false,
        'غیبت فقط با «حضور دستی» پاک می‌شود، نه با هر فیش چاپ‌شده');
    ok(strpos($groups, 'OR (e.event_type = "printed" AND e.print_status = "printed"))\'') === false,
        'شرط پاک‌کنندهٔ غیبت دیگر هر فیش printed را شامل نمی‌شود');
    ok(strpos($groups, "'is_absent' => isset(\$absentUsers[\$id])") !== false,
        'API وضعیت غیبت را به کلاینت می‌فرستد (is_absent)');
    $jsFile2 = $root . '/food-ticket-web/assets/food-manual-attendance.js';
    if (is_file($jsFile2)) {
        $js2 = (string) file_get_contents($jsFile2);
        ok(strpos($js2, 'غایب است — فیشش چاپ شده') !== false,
            'ردیف «غایب با فیش چاپ‌شده» حاضر نشان داده نمی‌شود');
        // دکمهٔ گروهی نباید به نتیجهٔ جستجو وابسته باشد.
        ok(strpos($js2, 'const orphanRows = S.manualEmployees.filter') !== false,
            'دکمهٔ ثبت گروهی مستقل از فیلتر جستجو ساخته می‌شود');
    }
} else {
    echo "  ⏭  food-ticket-groups.php روی این نصب نیست\n";
}

// ─────────────────────────────────────────────────────────────────────
echo "\n۵) گزارش تحویل دست‌نخورده است (عمدی)\n";
// ─────────────────────────────────────────────────────────────────────
// گزارش همچنان فقط «تحویل‌شده» را نشان می‌دهد. فیش یتیم *باید* غایب باشد،
// چون تحویل نشده. اصلاح این است که کاربر بتواند ثبتش کند، نه اینکه گزارش دروغ بگوید.
// food-order.php عمداً در بستهٔ افزایشی نیست (تغییر نکرده). اگر روی نصب هست
// بررسی می‌شود؛ وگرنه اعلام می‌شود که بررسی نشد — نه این‌که شکست بخورد.
$orderFile = $root . '/food-order.php';
if (is_file($orderFile)) {
    $orderSrc = (string) file_get_contents($orderFile);
    ok(strpos($orderSrc, "AND e.delivery_status = 'delivered'") !== false,
        'گزارش تحویل همچنان فقط تحویل‌شده‌ها را می‌آورد');
    ok(strpos($orderSrc, 'if (function_exists(\'food_ticket_manual_attendance_reconcile_range\'))') !== false,
        'گزارش پیش از خواندن، ثبت‌های دستی را هم‌تراز می‌کند');
} else {
    echo "  ⏭  food-order.php روی این نصب نیست (در بستهٔ افزایشی حمل نمی‌شود) — گزارش بررسی نشد\n";
}

// ─────────────────────────────────────────────────────────────────────
echo "\n" . str_repeat('─', 66) . "\n";
printf("  %d بررسی — %d ایراد\n", $checks, $fails);
echo str_repeat('─', 66) . "\n";
exit($fails === 0 ? 0 : 1);
