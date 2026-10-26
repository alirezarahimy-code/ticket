<?php
declare(strict_types=1);

/**
 * بستن خودکار درخواست‌های «حل‌شده» که کاربر پس از N روز امتیاز نداده است.
 *
 * - مهلت از تنظیم ticket_auto_close_days خوانده می‌شود (پیش‌فرض ۳ روز؛ مقدار 0 = غیرفعال).
 * - درخواستی که کاربر امتیاز داده و منتظر تأیید سوپروایزر است دست‌نخورده می‌ماند.
 * - درخواستی که درخواست لغو در انتظار دارد بسته نمی‌شود؛ اول باید دربارهٔ لغو تصمیم گرفته شود.
 * - کاربر پس از بسته‌شدن خودکار هم هنوز می‌تواند امتیاز ثبت کند.
 *
 * اجرا (روزی یک‌بار، مثلاً با Task Scheduler):
 *   C:\xampp\php\php.exe C:\xampp\htdocs\ticket\cron\ticket_auto_close.php
 */
require dirname(__DIR__) . '/bootstrap.php';
if (function_exists('ticket_hot_schema_ensure')) {
    ticket_hot_schema_ensure();
}

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

$days = (int) (setting('ticket_auto_close_days', '3') ?? '3');
if ($days < 1) {
    echo "ticket_auto_close: disabled\n";
    exit(0);
}

$candidates = db()->query('SELECT t.id, t.requester_id, t.asset_id FROM tickets t LEFT JOIN ticket_ratings tr ON tr.ticket_id = t.id WHERE t.status = "resolved" AND t.cancel_requested_at IS NULL AND COALESCE(t.resolved_at, t.updated_at) <= DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY) AND (tr.id IS NULL OR tr.created_at < t.resolved_at) ORDER BY t.id LIMIT 500')->fetchAll();
$closeQuery = db()->prepare('UPDATE tickets SET status = "closed", closed_at = NOW(), supervisor_note = ?, updated_at = NOW() WHERE id = ? AND status = "resolved" AND cancel_requested_at IS NULL');
$closed = 0;

foreach ($candidates as $row) {
    $ticketId = (int) $row['id'];
    try {
        db()->beginTransaction();
        $closeQuery->execute(['بسته‌شدن خودکار: کاربر ظرف ' . $days . ' روز پس از حل‌شدن امتیاز ثبت نکرد.', $ticketId]);
        if ($closeQuery->rowCount() < 1) {
            db()->rollBack();
            continue;
        }
        log_ticket_event($ticketId, null, 'auto_closed', 'resolved', 'closed', ['days' => $days]);
        notify_user((int) $row['requester_id'], 'ticket_closed', 'درخواست شما بسته شد', 'درخواست ' . ticket_number($ticketId) . ' پس از ' . $days . ' روز بدون پاسخ بسته شد. هنوز می‌توانید امتیاز رضایت خود را ثبت کنید.', $ticketId);
        notify_ticket_staff($ticketId, 'ticket_closed', 'درخواست به‌صورت خودکار بسته شد', 'درخواست ' . ticket_number($ticketId) . ' پس از ' . $days . ' روز بدون امتیاز کاربر بسته شد.', null);
        record_asset_history((int) ($row['asset_id'] ?? 0), 'ticket_closed', 'بسته‌شدن خودکار درخواست ' . ticket_number($ticketId), null, $ticketId, 'بدون امتیاز کاربر');
        db()->commit();
        $closed++;
    } catch (Throwable $exception) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        app_error_log('ticket_auto_close failed for #' . $ticketId . ': ' . $exception->getMessage());
    }
}

echo 'ticket_auto_close: ' . $closed . " closed\n";
