#!/usr/bin/env php
<?php
/**
 * تعمیر فیش‌های مهمانِ ثبت‌دستی که در گزارش‌های تحویل دیده نمی‌شوند.
 *
 * ── مشکل ──────────────────────────────────────────────────────────────
 * ثبت دستی فیش «مهمان» (صفحهٔ «ثبت دستی فیش») ردیف را با
 * `print_status = 'not_printed'` و `delivery_status = 'pending'` ذخیره می‌کرد.
 * اما هر دو گزارش تحویل — «غذای تحویلی» و «تطبیق خرید و توزیع» — شرط
 * `print_status = 'printed'` دارند. نتیجه: مهمان در پایگاه‌داده بود ولی در
 * هیچ گزارشی شمرده نمی‌شد؛ گزارش تطبیق «N پرس درخواستی، ۰ پرس تحویلی».
 *
 * ── چه می‌کند ──────────────────────────────────────────────────────────
 * فقط ردیف‌هایی را لمس می‌کند که **همهٔ** شرط‌های زیر را دارند:
 *   • source_uid = 'manual-attendance'   (ثبت دستی، نه کارت‌خوان)
 *   • event_type = 'guest'               (مهمان، نه کارمند)
 *   • print_status = 'not_printed'       (هرگز چاپ نشد)
 *   • delivery_status = 'pending'        (تحویل نشد)
 *   • print_job_id IS NULL               (هیچ‌وقت در صف چاپ نبود)
 *   • چاپ فیزیکی درخواست نشده           (source_payload.physical_print = false)
 *
 * یعنی ردیف «مهمان با چاپ فیزیکی» که واقعاً در صف چاپ است دست نمی‌خورد، و
 * ردیف «بدون فیش» (event_type = no_food) هم دست نمی‌خورد.
 *
 * پیش‌فرض: فقط گزارش. با --apply می‌نویسد.
 *
 * استفاده:
 *   php tools/fix_manual_guest_tickets.php                  # گزارش همه
 *   php tools/fix_manual_guest_tickets.php --apply          # اعمال
 *   php tools/fix_manual_guest_tickets.php --date=2026-11-05
 *   php tools/fix_manual_guest_tickets.php --date=2026-11-01 --to=2026-11-05 --apply
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("این ابزار فقط از خط فرمان اجرا می‌شود.\n");
}

$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';
// غذای همان الگوی cron/food_ticket_worker.php بارگذاری می‌شود: bootstrap ماژول
// غذا را نمی‌آورد و بدون آن هیچ‌کدام از توابع food_ticket_* وجود ندارند.
require_once $root . '/food-ticket.php';
foreach (['food-ticket-runtime.php', 'food-ticket-engine.php', 'food-ticket-groups.php', 'food-order.php', 'food-reconciliation.php'] as $extra) {
    $path = $root . '/' . $extra;
    if (is_file($path)) {
        require_once $path;
    }
}

if (!function_exists('db')) {
    fwrite(STDERR, "اتصال پایگاه‌داده در دسترس نیست (bootstrap).\n");
    exit(1);
}
if (!function_exists('food_ticket_events_ensure_delivery')) {
    fwrite(STDERR, "food-ticket.php بارگذاری نشد.\n");
    exit(1);
}
if (!food_ticket_events_ensure_delivery()) {
    fwrite(STDERR, "ستون‌های وضعیت تحویل در دسترس نیستند.\n");
    exit(1);
}

// ── آرگومان‌ها ───────────────────────────────────────────────────────────
$apply = false;
$fromDate = null;
$toDate = null;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--apply') {
        $apply = true;
    } elseif ($arg === '--help' || $arg === '-h') {
        fwrite(STDOUT, "استفاده: php tools/fix_manual_guest_tickets.php [--apply] [--date=YYYY-MM-DD] [--to=YYYY-MM-DD]\n");
        exit(0);
    } elseif (preg_match('/^--date=(\d{4}-\d{2}-\d{2})$/', $arg, $m)) {
        $fromDate = $m[1];
    } elseif (preg_match('/^--to=(\d{4}-\d{2}-\d{2})$/', $arg, $m)) {
        $toDate = $m[1];
    } else {
        fwrite(STDERR, "آرگومان ناشناخته: {$arg}\n");
        fwrite(STDERR, "استفاده: php tools/fix_manual_guest_tickets.php [--apply] [--date=YYYY-MM-DD] [--to=YYYY-MM-DD]\n");
        exit(2);
    }
}
$toDate = $toDate ?? $fromDate;

// ── واکشی ردیف‌های نامزد ────────────────────────────────────────────────
$where = [
    "source_uid = 'manual-attendance'",
    "event_type = 'guest'",
    "print_status = 'not_printed'",
    "(delivery_status IS NULL OR delivery_status <> 'delivered')",
    'print_job_id IS NULL',
];
$args = [];
if ($fromDate !== null) {
    $where[] = 'punch_date BETWEEN ? AND ?';
    $args[] = $fromDate;
    $args[] = $toDate;
}

$sql = 'SELECT id, punch_date, punch_time, COALESCE(meal_type, \'lunch\') AS meal_type, '
    . 'print_status, delivery_status, source_payload, last_error '
    . 'FROM food_ticket_events WHERE ' . implode(' AND ', $where) . ' ORDER BY punch_date, id';

try {
    $stmt = db()->prepare($sql);
    $stmt->execute($args);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    fwrite(STDERR, "خواندن ردیف‌ها ناموفق بود: " . $e->getMessage() . "\n");
    exit(1);
}

// ── فیلتر نهایی: چاپ فیزیکی درخواست نشده باشد ──────────────────────────
$toFix = [];
foreach ($candidates as $row) {
    $payload = json_decode((string) ($row['source_payload'] ?? ''), true);
    $manual = is_array($payload) ? (array) ($payload['manual_attendance'] ?? []) : [];
    // اگر چاپ فیزیکی خواسته شده بود، ردیف واقعاً در صف چاپ است — دست نزن.
    if (!empty($manual['physical_print'])) {
        continue;
    }
    // اگر with_ticket=false بوده، این ردیف باید no_food باشد نه guest؛
    // چنین چیزی نشانهٔ دادهٔ خراب است و بی‌صدا «تحویل‌شده» نمی‌شود.
    if (array_key_exists('with_ticket', $manual) && !$manual['with_ticket']) {
        continue;
    }
    $row['_payload'] = $manual;
    $toFix[] = $row;
}

echo "── گزارش فیش مهمانِ ثبت‌دستی ─────────────────────────────────────\n";

// ردیف‌هایی که در صف چاپ‌اند/چاپ فیزیکی خواسته‌اند (فقط اطلاع)
$queued = [];
foreach ($candidates as $row) {
    $payload = json_decode((string) ($row['source_payload'] ?? ''), true);
    $manual = is_array($payload) ? (array) ($payload['manual_attendance'] ?? []) : [];
    if (!empty($manual['physical_print'])) {
        $queued[] = $row;
    }
}

$total = db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE source_uid = 'manual-attendance' AND event_type = 'guest'")->fetchColumn();
echo "  کل فیش مهمانِ دستی ثبت‌شده   : " . (int) $total . "\n";
echo "  قابل تعمیر (تحویل‌نشده، بی‌چاپ): " . count($toFix) . "\n";
if ($queued !== []) {
    echo "  در صف چاپ (دست نمی‌خورد)     : " . count($queued) . "\n";
}
echo "\n";

if ($toFix === []) {
    echo "✅ هیچ ردیفی نیاز به تعمیر ندارد.\n";
    exit(0);
}

// ── نمایش برنامه ───────────────────────────────────────────────────────
echo "  شناسه  تاریخ        وعده   ساعت   توضیح\n";
echo "  " . str_repeat('─', 72) . "\n";
foreach ($toFix as $row) {
    printf("  %-6s %-12s %-6s %-7s %s\n",
        $row['id'],
        $row['punch_date'],
        ($row['meal_type'] === 'dinner' ? 'شام' : 'ناهار'),
        substr((string) ($row['punch_time'] ?? ''), 0, 5),
        'not_printed → printed، pending → delivered'
    );
}
echo "\n";

if ($queued !== []) {
    echo "  ℹ️  این ردیف‌ها چاپ فیزیکی خواسته‌اند و در صف چاپ‌اند — پس از چاپ موفق خودشان تحویل‌شده می‌شوند:\n";
    foreach ($queued as $row) {
        printf("     شناسه %s  (%s)\n", $row['id'], $row['punch_date']);
    }
    echo "\n";
}

if (!$apply) {
    echo "برای اعمال، همین فرمان را با --apply اجرا کنید:\n";
    echo "  php tools/fix_manual_guest_tickets.php" . ($fromDate !== null ? " --date={$fromDate}" . ($toDate !== $fromDate ? " --to={$toDate}" : '') : '') . " --apply\n";
    exit(0);
}

// ── اعمال ───────────────────────────────────────────────────────────────
$okCount = 0;
$failCount = 0;
$update = db()->prepare(
    'UPDATE food_ticket_events SET print_status = "printed", delivery_status = "delivered", '
    . 'delivered_at = COALESCE(delivered_at, NOW()), last_error = NULL WHERE id = ? '
    . 'AND print_status = "not_printed" AND (delivery_status IS NULL OR delivery_status <> "delivered")'
);

db()->beginTransaction();
try {
    foreach ($toFix as $row) {
        $update->execute([(int) $row['id']]);
        if ($update->rowCount() === 1) {
            $okCount++;
        } else {
            $failCount++;
            fwrite(STDERR, "  ⚠️ ردیف {$row['id']} تغییر نکرد (هم‌زمان عوض شده؟)\n");
        }
    }
    db()->commit();
} catch (Throwable $e) {
    db()->rollBack();
    fwrite(STDERR, "❌ اعمال ناموفق بود و همه چیز برگشت: " . $e->getMessage() . "\n");
    exit(1);
}

echo "✅ تعمیر شد: {$okCount} ردیف";
if ($failCount > 0) {
    echo " | ناموفق: {$failCount}";
}
echo "\n\n";

// ── اثبات بعد از اعمال ──────────────────────────────────────────────────
if (function_exists('food_order_printed_delivery_report') && $fromDate !== null) {
    try {
        $report = food_order_printed_delivery_report($fromDate, $toDate);
        $lunchGuests = (int) ($report['meal_counts']['lunch']['guest_count'] ?? 0);
        $dinnerGuests = (int) ($report['meal_counts']['dinner']['guest_count'] ?? 0);
        echo "── گزارش «غذای تحویلی» بلافاصله بعد از تعمیر ──\n";
        echo "  مهمان ناهار: {$lunchGuests}\n";
        echo "  مهمان شام : {$dinnerGuests}\n";
        echo "  مجموع مهمان در فهرست: " . count($report['items']) . " ردیف\n";
    } catch (Throwable $e) {
        echo "(گزارش تحویلی در دسترس نبود: " . $e->getMessage() . ")\n";
    }
}

echo "\nمرورگر را یک بار ببندید و باز کنید تا گزارش‌ها تازه خوانده شوند.\n";
