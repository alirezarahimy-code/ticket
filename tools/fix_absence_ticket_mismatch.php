<?php
declare(strict_types=1);

/**
 * ابزار تشخیص و اصلاح ناسازگاری «غایب» و «فیش» — نسخهٔ ۱.۶۳.۱
 *
 * ── چه مشکلی را نشان می‌دهد؟ ──────────────────────────────────────────
 * نسخه‌های پیش از ۱.۶۳.۱ یک باگ داشتند: هر فیشی که صبح چاپ می‌شد،
 * رکورد غیبت همان شخص را حذف می‌کرد. نتیجه، دو ناسازگاری بود:
 *
 *   ۱) «غایبِ حتمی» در فهرست غایبان نبود (۲۷ → ۲۳) ولی فهرست فیش‌ها
 *      می‌گفت «دارای فیش این روز».
 *
 *   ۲) شخصی که واقعاً آمده و غذا گرفته بود، در فهرست غایبان می‌آمد.
 *
 * ── چه کاری انجام می‌دهد؟ ──────────────────────────────────────────────
 *   • فهرست می‌کند کسانی را که «غایب» ثبت شده‌اند ولی فیش چاپ‌شده دارند.
 *   • فهرست می‌کند کسانی را که فیش چاپ‌شده دارند ولی هیچ ردیف غیبتی ندارند
 *     (این‌ها همان‌هایی هستند که باگ قبلی از فهرست غایبان حذفشان کرده).
 *   • با --apply فقط کسانی اصلاح می‌شوند که شما با --users مشخص کرده‌اید.
 *     هیچ اصلاح خودکاری بدون انتخاب شما انجام نمی‌شود.
 *
 * ── اجرا ───────────────────────────────────────────────────────────────
 *   php tools\fix_absence_ticket_mismatch.php --date 2026-11-10
 *       فقط نمایش (هیچ تغییری نمی‌دهد)
 *
 *   php tools\fix_absence_ticket_mismatch.php --date 2026-11-10 --apply --users 301,302
 *       ثبت غیبت برای این افراد (چون واقعاً غایب بودند ولی فیش دارند)
 *
 *   php tools\fix_absence_ticket_mismatch.php --date 2026-11-10 --apply --users 301 --present
 *       حذف غیبت این افراد (چون واقعاً آمده بودند)
 *
 * حالت «--present» برای همان علامت زردِ «تردد این روز ثبت شده» است:
 * کسی که نمایندهٔ گروه ترددش را ثبت کرده و فیش هم گرفته، نباید در
 * فهرست غایبان بماند.
 *
 * نکته: «--absent» حالت پیش‌فرض --apply است. دلیل به‌طور خودکار ثبت می‌شود.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$configPath = (string) (getenv('ITSM_CONFIG_PATH') ?: dirname(__DIR__) . '/config.php');
if (!is_file($configPath)) {
    fwrite(STDERR, "❌ فایل تنظیمات پیدا نشد: {$configPath}\n"
        . "   این ابزار باید روی همان سروری اجرا شود که سامانه نصب شده و فایل config.php را دارد.\n");
    exit(1);
}

require dirname(__DIR__) . '/bootstrap.php';

$args = [];
$rawArgs = array_slice($argv ?? [], 1);
for ($i = 0; $i < count($rawArgs); $i++) {
    $rawArg = (string) $rawArgs[$i];
    if (!str_starts_with($rawArg, '--')) {
        continue;
    }
    if (str_contains($rawArg, '=')) {
        $parts = explode('=', $rawArg, 2);
        $args[substr($parts[0], 2)] = $parts[1];
        continue;
    }
    $key = substr($rawArg, 2);
    // --date 2026-11-10  → مقدار از آرگومان بعدی
    $next = $rawArgs[$i + 1] ?? null;
    if (in_array($key, ['date', 'users'], true) && is_string($next) && !str_starts_with($next, '--')) {
        $args[$key] = $next;
        $i++;
        continue;
    }
    $args[$key] = true;
}

$date = (string) ($args['date'] ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    fwrite(STDERR, "❌ تاریخ را با --date بدهید (میلادی، نمونه 2026-11-10)\n");
    exit(1);
}

$apply = isset($args['apply']);
$present = isset($args['present']);
$usersRaw = (string) ($args['users'] ?? '');
$userIds = [];
foreach (explode(',', $usersRaw) as $piece) {
    $piece = trim($piece);
    if ($piece !== '' && ctype_digit($piece) && (int) $piece > 0) {
        $userIds[(int) $piece] = (int) $piece;
    }
}
$userIds = array_values($userIds);

function fx_name(array $row): string
{
    $name = trim((string) ($row['full_name'] ?? ''));
    if ($name !== '') {
        return $name;
    }
    $pc = trim((string) ($row['employee_number'] ?? ''));
    if ($pc !== '') {
        return 'کد ' . $pc;
    }
    $user = trim((string) ($row['username'] ?? ''));
    return $user !== '' ? $user : ('کاربر #' . (string) ($row['id'] ?? '?'));
}

$pdo = db();

// ── ۱) غایب با فیش چاپ‌شده ────────────────────────────────────────────
$absentWithTicket = $pdo->prepare(
    'SELECT u.id, u.full_name, u.employee_number, u.username, '
    . 'a.reason, a.group_id, e.print_status, e.delivery_status, e.food_type '
    . 'FROM food_ticket_daily_absence a '
    . 'INNER JOIN food_ticket_events e ON e.user_id = a.user_id AND e.punch_date = a.absence_date '
    . 'INNER JOIN users u ON u.id = a.user_id '
    . 'WHERE a.absence_date = ? AND e.event_type = "printed" AND e.print_status = "printed" '
    . 'ORDER BY u.id'
);
$absentWithTicket->execute([$date]);
$groupA = $absentWithTicket->fetchAll() ?: [];

// ── ۲) فیش چاپ‌شده بدون هیچ ردیف غیبت ─────────────────────────────────
$a = $pdo->prepare(
    'SELECT u.id, u.full_name, u.employee_number, u.username, '
    . 'e.print_status, e.delivery_status, e.food_type, e.source_uid '
    . 'FROM food_ticket_events e '
    . 'INNER JOIN users u ON u.id = e.user_id '
    . 'LEFT JOIN food_ticket_daily_absence ab ON ab.user_id = e.user_id AND ab.absence_date = e.punch_date '
    . 'WHERE e.punch_date = ? AND e.event_type = "printed" AND e.print_status = "printed" '
    . 'AND ab.id IS NULL ORDER BY u.id'
);
$a->execute([$date]);
$groupB = $a->fetchAll() ?: [];

echo "══════════════════════════════════════════════════════════════\n";
echo " تاریخ بررسی: {$date}\n";
echo "══════════════════════════════════════════════════════════════\n\n";

echo "الف) غایب ثبت شده‌اند ولی فیش چاپ‌شده دارند — " . count($groupA) . " نفر\n";
echo "    (اگر واقعاً آمده بودند، با --present اصلاح کنید)\n";
foreach ($groupA as $row) {
    printf(
        "    id=%-5s %-22s غذ=%-12s وضعیت=%s\n",
        (string) ($row['id'] ?? '?'),
        fx_name($row),
        (string) (($row['food_type'] ?? '') ?: '—'),
        (string) ($row['print_status'] ?? '?')
    );
}
if (!$groupA) {
    echo "    (هیچ)\n";
}

echo "\nب) فیش چاپ‌شده دارند ولی هیچ ردیف غیبتی ندارند — " . count($groupB) . " نفر\n";
echo "    (این‌ها را باگ قدیمی از فهرست غایبان حذف کرده بود؛ بررسی کنید کدام واقعاً غایب بود)\n";
foreach ($groupB as $row) {
    printf(
        "    id=%-5s %-22s غذ=%-12s منبع=%s\n",
        (string) ($row['id'] ?? '?'),
        fx_name($row),
        (string) (($row['food_type'] ?? '') ?: '—'),
        (string) (($row['source_uid'] ?? '') ?: '—')
    );
}
if (!$groupB) {
    echo "    (هیچ)\n";
}

if (!$apply) {
    echo "\n──────────── حالت نمایش ────────────\n";
    echo "هیچ تغییری انجام نشد.\n\n";
    echo "برای اصلاح، افراد را با --users مشخص کنید:\n";
    echo "  ثبت غیبت : php tools\\fix_absence_ticket_mismatch.php --date {$date} --apply --users 301,302\n";
    echo "  حذف غیبت : php tools\\fix_absence_ticket_mismatch.php --date {$date} --apply --users 305 --present\n";
    exit(0);
}

if (!$userIds) {
    fwrite(STDERR, "❌ با --apply باید --users بدهید (شناسه‌ها با کاما). هیچ تغییری انجام نشد.\n");
    exit(1);
}

// ── اعمال ─────────────────────────────────────────────────────────────
$validUsers = [];
$in = implode(',', array_fill(0, count($userIds), '?'));
$q = $pdo->prepare("SELECT id FROM users WHERE id IN ({$in})");
$q->execute($userIds);
foreach ($q->fetchAll() ?: [] as $r) {
    $validUsers[(int) $r['id']] = true;
}

$reason = $present
    ? ('اصلاح ناسازگاری غیبت/فیش ' . $date . ' — شخص حاضر بوده است')
    : ('اصلاح ناسازگاری غیبت/فیش ' . $date . ' — شخص غایب بوده است');
$done = 0;
$skipped = 0;

foreach ($userIds as $uid) {
    if (!isset($validUsers[$uid])) {
        echo "  ⏭  کاربر {$uid} وجود ندارد\n";
        $skipped++;
        continue;
    }
    if ($present) {
        $del = $pdo->prepare('DELETE FROM food_ticket_daily_absence WHERE absence_date = ? AND user_id = ?');
        $del->execute([$date, $uid]);
        $n = $del->rowCount();
        echo ($n > 0 ? '  ✅ ' : '  ⏭ ') . "کاربر {$uid}: حذف غیبت" . ($n > 0 ? '' : ' (غیبتی نبود)') . "\n";
        $done += $n > 0 ? 1 : 0;
    } else {
        $ins = $pdo->prepare(
            'INSERT INTO food_ticket_daily_absence (absence_date, group_id, user_id, reason, created_by) '
            . 'VALUES (?, NULL, ?, ?, NULL) '
            . 'ON DUPLICATE KEY UPDATE reason = VALUES(reason)'
        );
        $ins->execute([$date, $uid, mb_substr($reason, 0, 300)]);
        echo "  ✅ کاربر {$uid}: ثبت غیبت\n";
        $done++;
    }
}

echo "\n──────────── نتیجه ────────────\n";
echo "  اصلاح‌شده: {$done} | رد‌شده: {$skipped}\n";
echo "  برای دیدن وضعیت تازه دوباره همین ابزار را بدون --apply اجرا کنید.\n";
exit(0);
