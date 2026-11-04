<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

require dirname(__DIR__) . '/bootstrap.php';

$alerts = [];
$spool = logging_spool_stats();
$counters = logging_health_counters();
$workerPath = APP_ROOT . '/storage/logs/logging-worker-status.json';
$worker = is_file($workerPath) ? json_decode((string) @file_get_contents($workerPath), true) : [];
$issueRebuildPath = APP_ROOT . '/storage/logs/logging-issue-rebuild-status.json';
$issueRebuild = is_file($issueRebuildPath) ? json_decode((string) @file_get_contents($issueRebuildPath), true) : [];

// File-based heartbeats remain available during database outages and detect workers that stopped running.
$heartbeatDir = APP_ROOT . '/storage/logs/worker-heartbeats';
$readHeartbeat = static function (string $name) use ($heartbeatDir): array {
    $path = $heartbeatDir . DIRECTORY_SEPARATOR . $name . '.json';
    $data = is_file($path) ? json_decode((string) @file_get_contents($path), true) : null;
    return is_array($data) ? $data : [];
};
$notificationHeartbeat = $readHeartbeat('issue-notifications');
$rebuildHeartbeat = $readHeartbeat('issue-rebuild');
$replayHeartbeat = $readHeartbeat('spool-replay');
$updateAuditHeartbeat = $readHeartbeat('update-audit-reconcile');
$pendingUpdateAuditMarkers = glob(APP_ROOT . '/storage/backups/*/UPDATE-AUDIT-PENDING.json') ?: [];
if ($pendingUpdateAuditMarkers !== []) {
    $updateAuditBeatAt = !empty($updateAuditHeartbeat['last_finished_at']) ? strtotime((string) $updateAuditHeartbeat['last_finished_at']) : false;
    if ($updateAuditBeatAt === false || $updateAuditBeatAt < time() - 900 || in_array((string) ($updateAuditHeartbeat['state'] ?? ''), ['failure', 'partial'], true)) {
        $alerts['update_audit_reconcile_stale'] = count($pendingUpdateAuditMarkers) . ' نشانگر تکمیل Audit به‌روزرسانی باقی مانده و Worker بازیابی آن قدیمی یا ناموفق است.';
    }
}

if (($spool['state'] ?? 'unknown') === 'unknown') {
    $alerts['spool_unknown'] = 'بخشی از صف اضطراری قابل خواندن یا تحلیل نیست؛ سن و حجم صف قطعی نیست.';
}
if (($spool['rewrite_temp_files'] ?? 0) > 0) {
    $alerts['spool_rewrite_temps'] = 'فایل موقت بازنویسی صف از اجرای Worker قبلی باقی مانده است؛ بازیابی .processing و اجرای Worker را بررسی کنید.';
}
if (($spool['dead_letter_files'] ?? 0) > 0) {
    $alerts['spool_dead_letter'] = 'رخدادهایی پس از ۸ تلاش ناموفق به صف Dead Letter منتقل شده‌اند؛ فایل‌ها باید بررسی و پس از رفع علت، آگاهانه بازپخش شوند.';
}
if ($spool['files'] > 0 && $spool['age_seconds'] > 600) {
    $alerts['spool_stale'] = 'صف اضطراری لاگ بیش از ۱۰ دقیقه قدیمی است. اتصال دیتابیس و اجرای Worker بازپخش را بررسی کنید.';
}
if ($spool['bytes'] > 100 * 1024 * 1024) {
    $alerts['spool_large'] = 'حجم صف اضطراری از ۱۰۰ مگابایت عبور کرده است؛ سلامت دیتابیس و Worker باید بررسی شود.';
}
if (in_array(($counters['state'] ?? 'unknown'), ['corrupt','unreadable','not_initialized'], true)) {
    $alerts['counter_unknown'] = 'فایل شمارندهٔ سلامت Logging خراب، ساخته‌نشده یا غیرقابل خواندن است؛ صفر فرض نمی‌شود.';
}
if (!empty($counters['dropped_last_at']) && strtotime((string) $counters['dropped_last_at']) >= time() - 86400) {
    $alerts['events_dropped'] = 'در ۲۴ ساعت اخیر حداقل یک رخداد لاگ به‌طور نهایی از دست رفته است. مجوزهای فایل، فضای دیسک و سرویس دیتابیس باید بررسی شود.';
}
if (!empty($counters['throttled_last_at']) && strtotime((string) $counters['throttled_last_at']) >= time() - 86400) {
    $alerts['php_error_throttled'] = 'در ۲۴ ساعت اخیر بخشی از Warning/Notice/Deprecatedهای تکراری یا بیش‌ازسقف برای جلوگیری از سیل لاگ محدود شده‌اند؛ شمارنده و خطاهای یکتا بررسی شوند.';
}
if (!empty($counters['schema_replay_last_at']) && strtotime((string) $counters['schema_replay_last_at']) >= time() - 86400) {
    $alerts['replay_schema_errors'] = 'در بازپخش Logging خطای Schema/FK دیده شده است؛ جدول/ستون/قیود واقعی و نسخهٔ Migrationها بررسی شوند.';
}
if (!empty($counters['unknown_replay_last_at']) && strtotime((string) $counters['unknown_replay_last_at']) >= time() - 86400) {
    $alerts['replay_unknown_errors'] = 'در بازپخش Logging خطای ناشناخته ثبت شده است؛ رکوردها در صف فعال باقی می‌مانند و باید علت بررسی شود.';
}
if ($spool['files'] > 0) {
    $lastSuccess = !empty($worker['last_success_at']) ? strtotime((string) $worker['last_success_at']) : false;
    if ($lastSuccess === false || $lastSuccess < time() - 900) {
        $alerts['replay_stale'] = 'در ۱۵ دقیقهٔ اخیر بازپخش موفق صف ثبت نشده است. زمان‌بندی Worker و گزارش اجرای آن را بررسی کنید.';
    }
}
$issueLastSuccess = !empty($issueRebuild['last_success_at']) ? strtotime((string) $issueRebuild['last_success_at']) : false;
if ($issueLastSuccess === false || $issueLastSuccess < time() - 86400) {
    $alerts['issue_rebuild_stale'] = 'در ۲۴ ساعت اخیر بازسازی موفق Issueهای Logging ثبت نشده است؛ زمان‌بندی Cron و وضعیت آخرین اجرا بررسی شود.';
}
if (($issueRebuild['last_result'] ?? '') === 'partial_failure' || ($issueRebuild['last_result'] ?? '') === 'failure') {
    $alerts['issue_rebuild_failure'] = 'آخرین اجرای بازسازی Issueهای Logging کامل نشده است؛ گزارش Worker و خطاهای DB بررسی شود.';
}

$notificationBeatAt = !empty($notificationHeartbeat['last_finished_at']) ? strtotime((string) $notificationHeartbeat['last_finished_at']) : false;
if ($notificationBeatAt === false || $notificationBeatAt < time() - 900 || in_array((string) ($notificationHeartbeat['state'] ?? ''), ['failure', 'partial'], true)) {
    $alerts['notification_worker_stale'] = 'Heartbeat Worker اعلان‌ها قدیمی، ناموجود یا ناموفق است؛ Task زمان‌بندی و صف اعلان‌ها بررسی شود.';
}
$rebuildBeatAt = !empty($rebuildHeartbeat['last_finished_at']) ? strtotime((string) $rebuildHeartbeat['last_finished_at']) : false;
if ($rebuildBeatAt === false || $rebuildBeatAt < time() - 86400 || in_array((string) ($rebuildHeartbeat['state'] ?? ''), ['failure', 'partial'], true)) {
    $alerts['issue_rebuild_worker_stale'] = 'Heartbeat مستقل Worker بازسازی Issue قدیمی یا ناموفق است.';
}
if ($spool['files'] > 0) {
    $replayBeatAt = !empty($replayHeartbeat['last_finished_at']) ? strtotime((string) $replayHeartbeat['last_finished_at']) : false;
    if ($replayBeatAt === false || $replayBeatAt < time() - 900 || in_array((string) ($replayHeartbeat['state'] ?? ''), ['failure', 'partial'], true)) {
        $alerts['replay_worker_heartbeat_stale'] = 'Heartbeat مستقل Worker بازپخش قدیمی یا ناموفق است.';
    }
}
$free = @disk_free_space(APP_ROOT);
$total = @disk_total_space(APP_ROOT);
if ($free !== false && $total !== false && $total > 0 && ($free / $total) < 0.20) {
    $alerts['disk_low'] = 'فضای آزاد دیسک کمتر از ۲۰ درصد است؛ رشد لاگ‌ها و فضای پشتیبان را بررسی کنید.';
}
try {
    $dbErrors = (int) db()->query("SELECT COUNT(*) FROM system_logs WHERE code = 'SYS-DB-001' AND created_at >= NOW() - INTERVAL 24 HOUR")->fetchColumn();
    if ($dbErrors > 0) $alerts['db_errors'] = 'در ۲۴ ساعت گذشته ' . $dbErrors . ' خطای پایگاه داده ثبت شده است.';
} catch (Throwable) {
    $alerts['db_health_unknown'] = 'وضعیت خطاهای دیتابیس قابل خواندن نیست؛ اتصال و Migrationها بررسی شوند.';
}
try {
    $tableMb = (float) db()->query("SELECT COALESCE((DATA_LENGTH + INDEX_LENGTH) / 1048576, 0) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs'")->fetchColumn();
    if ($tableMb >= 1024) $alerts['log_table_large'] = 'حجم جدول system_logs به ' . round($tableMb, 1) . ' مگابایت رسیده است.';
} catch (Throwable) {
    // اگر آمار جدول در دسترس نیست، خطای جداگانه‌ای به مدیر ارسال نمی‌شود تا اعلان کاذب ایجاد نشود.
}

// Queue age and terminal failures are monitored separately from the notification worker heartbeat.
try {
    if (db_table_exists('log_issue_notifications')) {
        $queue = db()->query("SELECT
            SUM(state = 'pending' AND next_attempt_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)) AS overdue_pending,
            SUM(state = 'sending' AND updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)) AS stale_sending,
            SUM(state = 'failed') AS terminal_failed
            FROM log_issue_notifications")->fetch(PDO::FETCH_ASSOC) ?: [];
        $overduePending = (int) ($queue['overdue_pending'] ?? 0);
        $staleSending = (int) ($queue['stale_sending'] ?? 0);
        $terminalFailed = (int) ($queue['terminal_failed'] ?? 0);
        if ($overduePending > 0) $alerts['notification_queue_overdue'] = $overduePending . ' اعلان بیش از ۱۵ دقیقه در صف pending مانده است.';
        if ($staleSending > 0) $alerts['notification_queue_stale_sending'] = $staleSending . ' اعلان در وضعیت sending بیش از ۵ دقیقه بدون heartbeat مانده است.';
        if ($terminalFailed > 0) $alerts['notification_queue_failed'] = $terminalFailed . ' اعلان پس از پایان retryها به وضعیت failed رسیده است.';
    }
} catch (Throwable $queueException) {
    $alerts['notification_queue_unknown'] = 'وضعیت صف اعلان‌ها قابل خواندن نیست؛ Schema و دسترسی DB بررسی شود.';
}
try {
    $ticketEventsMb = (float) db()->query("SELECT COALESCE((DATA_LENGTH + INDEX_LENGTH) / 1048576, 0) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_events'")->fetchColumn();
    if ($ticketEventsMb >= 5120) $alerts['ticket_events_critical_size'] = 'حجم ticket_events از ۵ گیگابایت عبور کرده است؛ ظرفیت، ایندکس و طرح آرشیو باید فوراً بررسی شود.';
    elseif ($ticketEventsMb >= 1024) $alerts['ticket_events_large'] = 'حجم ticket_events به ' . round($ticketEventsMb, 1) . ' مگابایت رسیده است؛ رشد و نیاز آرشیو پایش شود.';
} catch (Throwable) {
    // Optional table statistics are advisory; do not hide other health checks.
}
// تشخیص اختلاف timezone؛ این بررسی هیچ timezone یا داده‌ای را تغییر نمی‌دهد.
try {
    $tzRow = db()->query('SELECT @@global.time_zone AS global_tz, @@session.time_zone AS session_tz, TIME_TO_SEC(TIMEDIFF(NOW(), UTC_TIMESTAMP())) AS session_offset_seconds')->fetch(PDO::FETCH_ASSOC);
    $phpOffset = (new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get())))->getOffset();
    $dbOffset = isset($tzRow['session_offset_seconds']) ? (int) $tzRow['session_offset_seconds'] : null;
    if ($dbOffset !== null && abs($dbOffset - $phpOffset) > 60) {
        $alerts['timezone_mismatch'] = 'اختلاف timezone بین PHP و نشست دیتابیس تشخیص داده شد؛ PHP=' . date_default_timezone_get()
            . '، DB global=' . (string) ($tzRow['global_tz'] ?? 'unknown') . '، DB session=' . (string) ($tzRow['session_tz'] ?? 'unknown')
            . '. هیچ اصلاح خودکاری انجام نشده است.';
    }
} catch (Throwable) {
    // نبود مجوز خواندن timezone فقط به‌عنوان دادهٔ تشخیصی ناموجود تلقی می‌شود.
}

$adminIds = [];
try {
    $adminIds = db()->query("SELECT id FROM users WHERE role = 'primary_admin'")->fetchAll(PDO::FETCH_COLUMN);
    if (function_exists('db_column_present') && db_column_present('users', 'is_primary_admin')) {
        $legacyIds = db()->query("SELECT id FROM users WHERE role = 'admin' AND is_primary_admin = 1")->fetchAll(PDO::FETCH_COLUMN);
        $adminIds = array_values(array_unique(array_merge($adminIds, $legacyIds)));
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Cannot resolve primary administrators: ' . get_class($exception) . "\n");
    exit(2);
}

$sent = 0;
foreach ($alerts as $key => $message) {
    try {
        db()->prepare('INSERT IGNORE INTO logging_health_alerts (alert_key, last_state, occurrences) VALUES (?, \'clear\', 0)')->execute([$key]);
        // ابتدا فقط claim می‌کنیم؛ last_sent_at تا تحویل موفق به همهٔ گیرندگان تغییر نمی‌کند.
        $gate = db()->prepare("UPDATE logging_health_alerts SET last_state = 'sending', occurrences = occurrences + 1, updated_at = NOW()
            WHERE alert_key = ? AND ((last_sent_at IS NULL OR last_sent_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE))
            OR (last_state = 'retry' AND updated_at < DATE_SUB(NOW(), INTERVAL 1 MINUTE)))
            AND (last_state <> 'sending' OR updated_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))");
        $gate->execute([$key]);
        if ($gate->rowCount() > 0) {
            $deliveryOk = true;
            foreach ($adminIds as $adminId) {
                try {
                    $window = (int) floor(time() / 1800);
                    $idempotencyKey = hash('sha256', 'logging-health-alert|' . $key . '|' . (int) $adminId . '|' . $window);
                    notify_user((int) $adminId, 'system_health_alert', 'هشدار سلامت سیستم لاگینگ', $message, null, $idempotencyKey);
                    $sent++;
                } catch (Throwable $deliveryException) {
                    $deliveryOk = false;
                    logging_emergency_failure('logging_health_alert_delivery_failed', get_class($deliveryException));
                }
            }
            if ($deliveryOk && $adminIds !== []) {
                db()->prepare("UPDATE logging_health_alerts SET last_sent_at = NOW(), last_state = 'active', updated_at = NOW() WHERE alert_key = ?")->execute([$key]);
            } else {
                db()->prepare("UPDATE logging_health_alerts SET last_state = 'retry', updated_at = NOW() WHERE alert_key = ?")->execute([$key]);
            }
        }
    } catch (Throwable $exception) {
        logging_emergency_failure('logging_health_alert_failed', get_class($exception));
    }
}

// وضعیت‌هایی که برطرف شده‌اند، بدون حذف تاریخچه به حالت clear برمی‌گردند.
try {
    $activeKeys = array_keys($alerts);
    if ($activeKeys === []) {
        db()->exec("UPDATE logging_health_alerts SET last_state = 'clear' WHERE last_state <> 'clear'");
    } else {
        $placeholders = implode(',', array_fill(0, count($activeKeys), '?'));
        db()->prepare("UPDATE logging_health_alerts SET last_state = 'clear' WHERE last_state <> 'clear' AND alert_key NOT IN ($placeholders)")->execute($activeKeys);
    }
} catch (Throwable) {
    // Clear-state failure does not block the health check.
}

logging_worker_heartbeat('health-check', 'success', ['alerts' => count($alerts), 'notifications' => $sent]);
fwrite(STDOUT, sprintf("Active alerts: %d | notifications sent: %d\n", count($alerts), $sent));
exit(0);
