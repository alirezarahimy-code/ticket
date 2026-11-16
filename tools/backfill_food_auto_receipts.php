<?php
declare(strict_types=1);

/**
 * tools/backfill_food_auto_receipts.php — ثبت فیش خودکار برای روزهای گذشته
 *
 * ── چرا این ابزار لازم شد ────────────────────────────────────────────────
 *
 * قابلیت «ثبت خودکار فیش» یک **گذر پایان‌روز** است: Worker هر روز ساعت ۲۳ به بعد
 * یک بار اجرا می‌کند و فقط **همان روز** و **دیروز** را پر می‌کند:
 *
 *     food_ticket_auto_receipts_run(دیروز)
 *     food_ticket_auto_receipts_run(امروز, allowToday: true)
 *
 * پس روزهایی که Worker نصب نبود، خاموش بود، یا کد این قابلیت را نداشت،
 * هرگز پر نشدند. روزهای بعد، آن عقب‌ماندگی **جبران نمی‌شود** — برای همیشه
 * سوراخ می‌مانند.
 *
 * این ابزار همان کار را برای یک بازهٔ دلخواه انجام می‌دهد، با **همان تابع
 * واقعی** سامانه (food_ticket_auto_receipts_run) — نه یک بازنویسی موازی.
 * پس هر قاعده‌ای که سامانه دارد (تعطیلات، غیبت، فیش واقعی، idempotency)
 * عیناً همین‌جا هم برقرار است.
 *
 * ── ایمنی ───────────────────────────────────────────────────────────────
 *
 *   • idempotent: هر ردیف کلید یکتای sha256 دارد؛ اجرای دوباره چیزی را
 *     دوباره نمی‌سازد. می‌توانید هر چند بار که خواستید اجرا کنید.
 *   • فقط سفارش‌های **فعال** را می‌بیند.
 *   • اگر برای آن وعده **فیش واقعی چاپ‌شده** باشد، رد می‌شود.
 *   • اگر کاربر آن روز در جدول غیبت باشد، رد می‌شود.
 *   • ناهار فقط در روزهای تعطیل (پنجشنبه/جمعه/تعطیل رسمی) ساخته می‌شود.
 *   • هیچ چاپ فیزیکی رخ نمی‌دهد.
 *   • پیش‌فرض فقط گزارش است؛ برای نوشتن --apply لازم است.
 *
 * ── اجرا ────────────────────────────────────────────────────────────────
 *
 *   php tools/backfill_food_auto_receipts.php --from 2026-10-01 --to 2026-11-15
 *   php tools/backfill_food_auto_receipts.php --from 2026-10-01 --to 2026-11-15 --apply
 *   php tools/backfill_food_auto_receipts.php --days 30 --apply
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/food-ticket-auto-receipts.php';

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

$opt = static function (string $name, ?string $default = null) use ($argv): ?string {
    foreach ($argv as $i => $arg) {
        if ($arg === '--' . $name && isset($argv[$i + 1])) {
            return (string) $argv[$i + 1];
        }
        if (str_starts_with($arg, '--' . $name . '=')) {
            return substr($arg, strlen($name) + 3);
        }
    }
    return $default;
};

$apply = in_array('--apply', $argv, true);
$days  = (int) ($opt('days', '0') ?? 0);

$tz = new DateTimeZone('Asia/Tehran');
$today = new DateTimeImmutable('now', $tz);
// «امروز» را عمداً نمی‌گیریم: گذر پایان‌روز خودش ساعت ۲۳ انجامش می‌دهد
// و تا آن ساعت سفارش‌ها ممکن است هنوز تغییر کنند.
$defaultTo = $today->modify('-1 day')->format('Y-m-d');

$fromRaw = (string) ($opt('from', '') ?? '');
$toRaw   = (string) ($opt('to', $defaultTo) ?? $defaultTo);

$validDate = static function (string $d): bool {
    return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && checkdate((int) substr($d, 5, 2), (int) substr($d, 8, 2), (int) substr($d, 0, 4));
};

if ($fromRaw === '') {
    // بدون --from: عقب‌ترین روزی که در سامانه سفارش دارد.
    $firstStmt = db()->query("SELECT MIN(food_date) FROM food_orders WHERE status = 'active'");
    $fromRaw = (string) ($firstStmt->fetchColumn() ?: '');
    if ($fromRaw === '') {
        fwrite(STDERR, "هیچ سفارش فعالی در سامانه نیست. با --from تاریخ بدهید.\n");
        exit(1);
    }
    echo "بدون --from: از اولین سفارش سامانه شروع می‌کنیم ({$fromRaw}).\n";
}

if ($days > 0) {
    $fromRaw = (new DateTimeImmutable($defaultTo, $tz))->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
    echo "--days {$days}: بازه {$fromRaw} … {$defaultTo}\n";
}

if (!$validDate($fromRaw) || !$validDate($toRaw)) {
    fwrite(STDERR, "تاریخ نامعتبر است. قالب درست: YYYY-MM-DD\n");
    exit(1);
}
if ($fromRaw > $toRaw) {
    fwrite(STDERR, "--from بعد از --to است.\n");
    exit(1);
}

if (!food_ticket_auto_receipt_schema_ready()) {
    fwrite(STDERR, "ساختار ثبت خودکار فیش آماده نیست (مهاجرت ۰۲۱).\n");
    exit(1);
}

echo str_repeat('═', 70) . "\n";
printf("بازهٔ بررسی: %s … %s   (%s)\n", $fromRaw, $toRaw, $apply ? 'APPLY' : 'گزارش فقط');
echo str_repeat('═', 70) . "\n\n";

$cursor = new DateTimeImmutable($fromRaw, $tz);
$end    = new DateTimeImmutable($toRaw, $tz);

$totals = ['days' => 0, 'staff' => 0, 'guest' => 0, 'absence' => 0, 'real_event' => 0, 'already' => 0, 'holiday_lunch_days' => 0];
$perDay = [];

while ($cursor <= $end) {
    $date = $cursor->format('Y-m-d');

    // پیش‌شمارش: چند وعده در این روز اصلاً کاندید است؟ (بدون اجرا)
    $cand = db()->prepare(
        "SELECT
           SUM(CASE WHEN o.meal_type = 'dinner' THEN 1 ELSE 0 END) AS dinners,
           SUM(CASE WHEN o.meal_type = 'lunch'  THEN 1 ELSE 0 END) AS lunches
         FROM food_orders o
         WHERE o.food_date = ? AND o.status = 'active' AND o.meal_type IN ('lunch','dinner')"
    );
    $cand->execute([$date]);
    $counts = $cand->fetch() ?: [];
    $dinners = (int) ($counts['dinners'] ?? 0);
    $lunches = (int) ($counts['lunches'] ?? 0);

    if ($dinners === 0 && $lunches === 0) {
        $cursor = $cursor->modify('+1 day');
        continue;
    }

    $totals['days']++;

    if (!$apply) {
        // در حالت گزارش، فقط تعداد کاندید را نشان می‌دهیم (اجرای واقعی نمی‌کنیم
        // تا نوشتن ناخواسته رخ ندهد و کند نباشد).
        $holiday = food_ticket_auto_receipt_holiday($date);
        $eligibleLunch = $holiday['holiday'] ? $lunches : 0;
        printf(
            "  %s  %s   شام: %d   ناهار%s: %d\n",
            $date,
            $holiday['holiday'] ? 'تعطیل' : 'کاری ',
            $dinners,
            $holiday['holiday'] ? ' (تعطیل)' : ' (کاری—رد)',
            $eligibleLunch
        );
        if ($holiday['holiday'] && $lunches > 0) {
            $totals['holiday_lunch_days']++;
        }
        $cursor = $cursor->modify('+1 day');
        continue;
    }

    try {
        $r = food_ticket_auto_receipts_run($date);
    } catch (Throwable $e) {
        printf("  %s  ✗ %s\n", $date, $e->getMessage());
        $cursor = $cursor->modify('+1 day');
        continue;
    }

    if (!empty($r['locked'])) {
        printf("  %s  ⏸ قفل بود (Worker همین حالا مشغول است) — بعداً دوباره اجرا کنید\n", $date);
        $cursor = $cursor->modify('+1 day');
        continue;
    }

    $created = (int) $r['staff_created'] + (int) $r['guest_created'];
    $totals['staff']   += (int) $r['staff_created'];
    $totals['guest']   += (int) $r['guest_created'];
    $totals['absence'] += (int) $r['skipped_absence'];
    $totals['real_event'] += (int) $r['skipped_real_event'];
    $totals['already'] += (int) $r['already_registered'];
    if ($r['holiday']) {
        $totals['holiday_lunch_days']++;
    }

    $perDay[$date] = $r;

    printf(
        "  %s  %s  +%d تازه   (%d پرسنل، %d مهمان)   ردشده: %d غیبت، %d فیش واقعی   از قبل: %d\n",
        $date,
        $r['holiday'] ? 'تعطیل' : 'کاری ',
        $created,
        (int) $r['staff_created'],
        (int) $r['guest_created'],
        (int) $r['skipped_absence'],
        (int) $r['skipped_real_event'],
        (int) $r['already_registered']
    );

    $cursor = $cursor->modify('+1 day');
}

echo "\n" . str_repeat('─', 70) . "\n";

if (!$apply) {
    printf("روزهای دارای سفارش: %d\n", $totals['days']);
    echo "این فقط گزارش بود. برای ثبت واقعی:\n";
    printf("  php tools/backfill_food_auto_receipts.php --from %s --to %s --apply\n", $fromRaw, $toRaw);
    exit(0);
}

printf("روزهای پردازش‌شده : %d\n", $totals['days']);
printf("فیش تازه ساخته‌شده : %d  (%d پرسنل + %d مهمان)\n", $totals['staff'] + $totals['guest'], $totals['staff'], $totals['guest']);
printf("ردشده — غیبت      : %d\n", $totals['absence']);
printf("ردشده — فیش واقعی : %d   (این‌ها را سامانهٔ چاپ خودش ثبت کرده)\n", $totals['real_event']);
printf("از قبل موجود بود  : %d   (idempotent — دوباره ساخته نشد)\n", $totals['already']);
printf("روزهای تعطیل      : %d   (ناهار فقط این روزها ثبت می‌شود)\n", $totals['holiday_lunch_days']);
echo "\nهیچ چاپ فیزیکی انجام نشد. فیش‌ها در «گزارش تحویل غذا» دیده می‌شوند.\n";
