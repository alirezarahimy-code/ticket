<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

require dirname(__DIR__) . '/bootstrap.php';

$limit = isset($argv[1]) ? (int) $argv[1] : 100;
$limit = max(1, min(1000, $limit));
$sent = 0;
$failed = 0;
logging_worker_heartbeat('issue-notifications', 'running');

try {
    // بازیابی enqueue: اگر درخواست وب هنگام ثبت صف اعلان شکست خورده باشد، Worker از روی Issueهای واجدشرایط صف را بازسازی می‌کند.
    $eligibleIssues = db()->query("SELECT i.fingerprint, i.code, i.title FROM log_issues i
        INNER JOIN (
            SELECT fingerprint FROM log_issue_events
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            GROUP BY fingerprint HAVING COUNT(*) >= 5
        ) recent ON recent.fingerprint = i.fingerprint
        WHERE i.status IN ('open','investigating')
          AND (i.last_notified_at IS NULL OR i.last_notified_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE))
        ORDER BY i.last_seen DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
    $admins = db()->query("SELECT id FROM users WHERE role = 'primary_admin'")->fetchAll(PDO::FETCH_COLUMN);
    if (function_exists('db_column_present') && db_column_present('users', 'is_primary_admin')) {
        $legacyAdmins = db()->query("SELECT id FROM users WHERE role = 'admin' AND is_primary_admin = 1")->fetchAll(PDO::FETCH_COLUMN);
        $admins = array_values(array_unique(array_merge($admins, $legacyAdmins)));
    }
    $window = (int) floor(time() / 1800);
    $enqueue = db()->prepare("INSERT IGNORE INTO log_issue_notifications (notification_key, fingerprint, recipient_id, state, attempts, next_attempt_at) VALUES (?, ?, ?, 'pending', 0, NOW())");
    $pendingCheck = db()->prepare("SELECT 1 FROM log_issue_notifications WHERE fingerprint = ? AND recipient_id = ? AND state IN ('pending','sending') LIMIT 1");
    foreach ($eligibleIssues as $issue) {
        foreach ($admins as $adminId) {
            $recipientId = (int) $adminId;
            if ($recipientId <= 0) continue;
            $pendingCheck->execute([(string) $issue['fingerprint'], $recipientId]);
            if ($pendingCheck->fetchColumn()) continue;
            $key = hash('sha256', (string) $issue['fingerprint'] . '|' . $recipientId . '|' . $window);
            $enqueue->execute([$key, (string) $issue['fingerprint'], $recipientId]);
        }
    }

    // ردیف‌های sending قدیمی‌تر از پنج دقیقه پس از توقف ناگهانی Worker دوباره قابل‌برداشت‌اند.
    $rows = db()->query("SELECT id, notification_key, fingerprint, recipient_id, attempts
        FROM log_issue_notifications
        WHERE (state = 'pending' AND next_attempt_at <= NOW())
           OR (state = 'sending' AND updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))
        ORDER BY id ASC LIMIT " . $limit)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        logging_begin_operation('worker:issue-notification:delivery');
        $claim = db()->prepare("UPDATE log_issue_notifications SET state = 'sending', attempts = attempts + 1, updated_at = NOW()
            WHERE id = ? AND ((state = 'pending' AND next_attempt_at <= NOW()) OR (state = 'sending' AND updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)))");
        $claim->execute([(int) $row['id']]);
        if ($claim->rowCount() !== 1) continue;
        try {
            $issue = db()->prepare('SELECT code, title FROM log_issues WHERE fingerprint = ? LIMIT 1');
            $issue->execute([(string) $row['fingerprint']]);
            $details = $issue->fetch(PDO::FETCH_ASSOC) ?: ['code' => 'LOG-ISSUE', 'title' => 'رخداد پرتکرار'];
            $deliveryKey = hash('sha256', 'logging-issue-notification|' . (string) $row['notification_key'] . '|' . (int) $row['recipient_id']);
            notify_user((int) $row['recipient_id'], 'system_health_alert', 'خطای پرتکرار در سامانه',
                (string) $details['code'] . ' — ' . (string) $details['title'] . ' (حداقل ۵ رخداد؛ اعلان با محدودیت ۳۰ دقیقه).', null, $deliveryKey);
            db()->prepare("UPDATE log_issue_notifications SET state = 'sent', delivered_at = NOW(), last_error = NULL WHERE id = ?")->execute([(int) $row['id']]);
            db()->prepare('UPDATE log_issues SET last_notified_at = NOW() WHERE fingerprint = ?')->execute([(string) $row['fingerprint']]);
            $sent++;
        } catch (Throwable $exception) {
            system_log('error', 'logging-notification-worker', 'تحویل اعلان رخداد ناموفق بود', [
                'code' => 'SYS-PHP-001',
                'error_type' => get_class($exception),
            ]);
            $attempt = (int) $row['attempts'] + 1;
            $delays = [60, 300, 900, 1800, 3600, 7200, 14400];
            $delay = $delays[min(max(0, $attempt - 1), count($delays) - 1)];
            $state = $attempt >= 8 ? 'failed' : 'pending';
            db()->prepare('UPDATE log_issue_notifications SET state = ?, next_attempt_at = DATE_ADD(NOW(), INTERVAL ? SECOND), last_error = ? WHERE id = ?')
                ->execute([$state, $delay, get_class($exception), (int) $row['id']]);
            $failed++;
        }
    }
} catch (Throwable $exception) {
    logging_emergency_failure('issue_notification_worker_failed', get_class($exception));
    logging_worker_heartbeat('issue-notifications', 'failure');
    fwrite(STDERR, 'Issue notification worker failed: ' . get_class($exception) . PHP_EOL);
    exit(2);
}

logging_worker_heartbeat('issue-notifications', $failed > 0 ? 'partial' : 'success', ['sent' => $sent, 'failed' => $failed]);
fwrite(STDOUT, sprintf("Issue notifications: %d sent, %d failed/retried\n", $sent, $failed));
exit($failed > 0 ? 1 : 0);
