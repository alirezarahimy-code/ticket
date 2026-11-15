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
ok(strpos($jsSrc, "const orphanRows = filtered.filter((u) => isOrphanTicket(u) && canIssueManualTicket());") !== false,
    'شمارش فیش‌های یتیم فقط روی ردیف‌های واجد شرایط است');
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
    ok($assetVer >= 6, 'نسخهٔ کش حداقل ۶ است (v=' . $assetVer . ') — تغییر JS دیده می‌شود');
} else {
    echo "  ⏭  index.html روی این نصب نیست — بررسی کش انجام نشد\n";
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
