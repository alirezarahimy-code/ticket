<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
if (function_exists('ticket_hot_schema_ensure')) {
    ticket_hot_schema_ensure();
}

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

$query = db()->query('SELECT t.id, t.subject, t.due_at FROM tickets t WHERE t.due_at IS NOT NULL AND t.status NOT IN ("resolved", "closed", "cancelled") AND t.sla_paused_at IS NULL AND t.due_at <= DATE_ADD(NOW(), INTERVAL 60 MINUTE) AND NOT EXISTS (SELECT 1 FROM ticket_sla_alerts a WHERE a.ticket_id = t.id AND (a.alert_type = "overdue" OR (a.alert_type = "due_soon" AND t.due_at > NOW()))) ORDER BY t.due_at LIMIT 1000');
$claim = db()->prepare('INSERT IGNORE INTO ticket_sla_alerts (ticket_id, alert_type, due_at) VALUES (?, ?, ?)');
$alerts = 0;
$now = new DateTimeImmutable('now');

foreach ($query->fetchAll() as $ticket) {
    $dueAt = new DateTimeImmutable((string) $ticket['due_at']);
    $type = $dueAt <= $now ? 'overdue' : 'due_soon';
    db()->beginTransaction();
    try {
        $claim->execute([(int) $ticket['id'], $type, $ticket['due_at']]);
        if ($claim->rowCount() === 1) {
            $title = $type === 'overdue' ? 'درخواست از SLA عبور کرد' : 'مهلت SLA درخواست نزدیک است';
            $body = $type === 'overdue'
                ? 'درخواست ' . ticket_number((int) $ticket['id']) . ' از مهلت SLA عبور کرده است.'
                : 'مهلت درخواست ' . ticket_number((int) $ticket['id']) . ' در کمتر از یک ساعت آینده است.';
            notify_ticket_staff((int) $ticket['id'], 'sla_' . $type, $title, $body);
            $alerts++;
        }
        db()->commit();
    } catch (Throwable $exception) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        app_error_log('ITSM SLA maintenance failed for ticket ' . (int) $ticket['id'] . ': ' . $exception->getMessage());
    }
}

$olaAlerts = 0;
try {
    // هر هشدار OLA در کوئری جداگانه و فقط برای سررسیدهای گذشته‌ای که هنوز هشدار نگرفته‌اند؛
    // این کار هم گرسنگی کرون (پرشدن LIMIT با درخواست‌های قبلاً هشدارگرفته) را رفع می‌کند
    // و هم هشدارهای تکراری برای پاسخ‌های بعدی را حذف می‌کند.
    $olaTypes = [
        'response_overdue' => ['column' => 'ola_response_due_at', 'extra' => 'AND t.first_response_at IS NULL'],
        'resolution_overdue' => ['column' => 'ola_due_at', 'extra' => ''],
        'escalation_1' => ['column' => 'ola_escalation_1_at', 'extra' => ''],
        'escalation_2' => ['column' => 'ola_escalation_2_at', 'extra' => ''],
    ];
    $olaLabels = [
        'response_overdue' => ['پاسخ OLA عقب افتاد', 'مهلت پاسخ داخلی درخواست %s گذشته است.'],
        'resolution_overdue' => ['حل OLA عقب افتاد', 'مهلت حل داخلی درخواست %s گذشته است.'],
        'escalation_1' => ['Escalation سطح اول', 'درخواست %s به سطح اول پیگیری داخلی رسید.'],
        'escalation_2' => ['Escalation سطح دوم', 'درخواست %s به سطح دوم پیگیری مدیریتی رسید.'],
    ];
    $olaClaim = db()->prepare('INSERT IGNORE INTO ticket_ola_alerts (ticket_id, alert_type, due_at) VALUES (?, ?, ?)');
    foreach ($olaTypes as $type => $spec) {
        $column = $spec['column'];
        $olaQuery = db()->query('SELECT t.id, t.' . $column . ' AS due_text FROM tickets t WHERE t.status NOT IN ("resolved", "closed", "cancelled") AND t.sla_paused_at IS NULL AND t.' . $column . ' IS NOT NULL AND t.' . $column . ' <= NOW() ' . $spec['extra'] . ' AND NOT EXISTS (SELECT 1 FROM ticket_ola_alerts a WHERE a.ticket_id = t.id AND a.alert_type = "' . $type . '") ORDER BY t.' . $column . ' LIMIT 500');
        foreach ($olaQuery->fetchAll() as $ticket) {
            $dueAtText = (string) $ticket['due_text'];
            db()->beginTransaction();
            try {
                $olaClaim->execute([(int) $ticket['id'], $type, $dueAtText]);
                if ($olaClaim->rowCount() === 1) {
                    $number = ticket_number((int) $ticket['id']);
                    $labels = [$olaLabels[$type][0], sprintf($olaLabels[$type][1], $number)];
                    notify_ticket_staff((int) $ticket['id'], 'ola_' . $type, $labels[0], $labels[1]);
                    if ($type === 'escalation_2') {
                        $admins = db()->query('SELECT id FROM users WHERE role = "primary_admin" AND is_active = 1')->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($admins as $adminId) {
                            notify_user((int) $adminId, 'ola_escalation_2', $labels[0], $labels[1], (int) $ticket['id']);
                        }
                    }
                    $olaAlerts++;
                }
                db()->commit();
            } catch (Throwable $exception) {
                if (db()->inTransaction()) {
                    db()->rollBack();
                }
                app_error_log('ITSM OLA maintenance failed for ticket ' . (int) $ticket['id'] . ': ' . $exception->getMessage());
            }
        }
    }
} catch (Throwable $exception) {
    app_error_log('ITSM OLA maintenance unavailable: ' . $exception->getMessage());
}

fwrite(STDOUT, "SLA alerts created: {$alerts}; OLA alerts created: {$olaAlerts}\n");
