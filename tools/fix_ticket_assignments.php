<?php
declare(strict_types=1);

/**
 * tools/fix_ticket_assignments.php — اصلاح ارجاع‌های اشتباه به ادمین‌های اصلی
 *
 * پاسخ کوتاه به سؤال «این انتقال خودکار است یا دستی؟»:
 *
 *   خودکار **نیست**. نسخهٔ ۱.۶۳.۱ فقط جلوی ارجاع‌های *تازه* به ادمین اصلی را
 *   می‌گیرد؛ تیکتی که دیروز به یک ادمین اصلی ارجاع داده شده، تاریخچه دارد و
 *   سامانه نباید بی‌اجازه آن را جابه‌جا کند. این ابزار همان جابه‌جایی را
 *   **با تأیید شما** انجام می‌دهد.
 *
 * پیش‌فرض: فقط گزارش. برای اعمال، --apply بدهید.
 *
 * مثال:
 *   php tools/fix_ticket_assignments.php                 # فقط نشان بده
 *   php tools/fix_ticket_assignments.php --apply         # اعمال کن
 *   php tools/fix_ticket_assignments.php --apply --ticket 123
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

require_once dirname(__DIR__) . '/bootstrap.php';
// choose_handling_agent در ماژول مرکز درخواست‌هاست، نه در bootstrap.
require_once dirname(__DIR__) . '/modules/request-center/lib/functions.php';

// کنسول ویندوز (cmd) با code page 65001 کار می‌کند؛ بدون این، نام‌های فارسی
// به‌صورت Ù†Ø§Ù… نمایش داده می‌شوند و کاربر فکر می‌کند داده خراب شده.
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (PHP_SAPI === 'cli' && DIRECTORY_SEPARATOR === '\\') {
    @shell_exec('chcp 65001 >NUL 2>&1');
}

$apply  = in_array('--apply', $argv, true);
$onlyId = 0;
foreach ($argv as $i => $arg) {
    if ($arg === '--ticket' && isset($argv[$i + 1])) {
        $onlyId = (int) $argv[$i + 1];
    }
}

$openStatuses = ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending'];
$marks = implode(', ', array_fill(0, count($openStatuses), '?'));

// ── ۱) تیکت‌های بازی که مسئولشان ادمین اصلی است ولی در هیچ فهرست کارشناسی نیست ──
$sql = 'SELECT t.id, t.ticket_no, t.subject, t.service_group, t.status,
               t.department_id, t.requester_id,
               t.assigned_to, u.full_name AS assignee_name, u.role AS assignee_role
        FROM tickets t
        JOIN users u ON u.id = t.assigned_to
        WHERE t.status IN (' . $marks . ')
          AND (u.role = "primary_admin" OR (u.role = "admin" AND u.is_primary_admin = 1))'
    . ($onlyId > 0 ? ' AND t.id = ?' : '')
    . ' ORDER BY t.id';

$params = $openStatuses;
if ($onlyId > 0) {
    $params[] = $onlyId;
}
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

echo "=== تیکت‌های باز با مسئولِ ادمین اصلی ===\n\n";

if ($rows === []) {
    echo "هیچ تیکتی پیدا نشد. اگر انتظار داشتید پیدا شود، --ticket را چک کنید\n";
    echo "یا ببینید آن تیکت هنوز باز است (بسته/حل‌شده جابه‌جا نمی‌شود).\n";
    exit(0);
}

$toFix = [];
foreach ($rows as $row) {
    $isDesignated = function_exists('user_is_key_role')
        && ($row['service_group'] === 'it'
            ? user_is_key_role(['id' => (int) $row['assigned_to']], 'it_expert')
            : user_is_key_role(['id' => (int) $row['assigned_to']], 'support_expert'));

    $target = $isDesignated ? null : choose_handling_agent(
        (string) $row['service_group'],
        (int) ($row['department_id'] ?? 0),
        (int) ($row['requester_id'] ?? 0)
    );

    printf(
        "#%s [%s] %s ← %s (%s)\n",
        $row['id'],
        (string) $row['ticket_no'],
        mb_substr((string) $row['subject'], 0, 40),
        $row['assignee_name'],
        $row['assignee_role']
    );

    if ($isDesignated) {
        echo "      ✓ در فهرست کارشناسان هست — دست نمی‌زنیم (نقش ادمینش حفظ می‌شود).\n\n";
        continue;
    }

    if ($target === null) {
        echo "      ⚠ هیچ کارشناس واجدی پیدا نشد. در تنظیمات ← کاربران «کارشناس آی تی»\n";
        echo "        یا «کارشناس پشتیبانی» را پر کنید، بعد دوباره اجرا کنید.\n\n";
        continue;
    }

    $targetRow = db()->prepare('SELECT full_name FROM users WHERE id = ?');
    $targetRow->execute([$target]);
    $targetName = (string) ($targetRow->fetchColumn() ?: '#' . $target);
    echo "      → {$targetName} (id={$target})\n\n";
    $toFix[] = ['id' => (int) $row['id'], 'to' => $target, 'name' => $targetName];
}

echo str_repeat('─', 60) . "\n";
printf("قابل اصلاح: %d تیکت\n", count($toFix));

if (!$apply) {
    printf("\nاین فقط گزارش بود. برای اعمال (فقط همین %d تیکت):\n", count($toFix));
    echo "  php tools/fix_ticket_assignments.php --apply\n";
    exit(0);
}

if ($toFix === []) {
    echo "چیزی برای اعمال نبود.\n";
    exit(0);
}

echo "\nاعمال می‌شود…\n";
$updated = 0;
db()->beginTransaction();
try {
    foreach ($toFix as $fix) {
        // فقط اگر مسئول هنوز همان ادمین اصلی است — تا تغییر هم‌زمان کسی را زیر پا نگذاریم.
        $upd = db()->prepare(
            'UPDATE tickets t
             JOIN users u ON u.id = t.assigned_to
             SET t.assigned_to = ?
             WHERE t.id = ?
               AND (u.role = "primary_admin" OR (u.role = "admin" AND u.is_primary_admin = 1))'
        );
        $upd->execute([$fix['to'], $fix['id']]);
        if ($upd->rowCount() === 0) {
            echo "  #{$fix['id']}: رد شد (مسئولش هم‌زمان عوض شده بود)\n";
            continue;
        }
        $updated++;

        // ثبت در تاریخچهٔ تیکت — همان مسیری که ارجاع معمولی استفاده می‌کند.
        if (function_exists('log_ticket_event')) {
            log_ticket_event(
                $fix['id'],
                null,
                'assigned',
                null,
                'assigned',
                ['note' => 'ارجاع از ادمین اصلی به کارشناس فهرست اصلاح شد (۱.۶۳.۱)', 'to_user_id' => $fix['to']]
            );
        }
        echo "  ✓ #{$fix['id']} → {$fix['name']}\n";
    }
    db()->commit();
} catch (Throwable $e) {
    db()->rollBack();
    fwrite(STDERR, "\nخطا — هیچ تغییری ذخیره نشد: " . $e->getMessage() . "\n");
    exit(1);
}

printf("\n%d تیکت اصلاح شد.\n", $updated);
echo "توجه: اعلان‌ها و پیام‌های قبلی جابه‌جا نمی‌شوند؛ فقط مسئول فعلی عوض شد.\n";
