-- اصلاح معنای نتیجه در سوابق قدیمی: نبود شواهد موفقیت/شکست به معنی موفقیت نیست.
-- فقط ردیف‌های قدیمی فاقد Event ID و Request ID به unknown تبدیل می‌شوند؛ رخدادهای جدید دست‌نخورده می‌مانند.
UPDATE activity_logs
SET result = 'unknown'
WHERE result = 'success'
  AND event_id IS NULL
  AND request_id IS NULL;


-- Issueهای تجمیع‌شده اکنون context دارند تا یک مدل مرجع یکتا باشد.
SET @logging_context_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'log_issues' AND COLUMN_NAME = 'context');
SET @logging_context_ddl = IF(@logging_context_exists = 0, 'ALTER TABLE log_issues ADD COLUMN context VARCHAR(120) NOT NULL DEFAULT ''system'' AFTER category', 'SELECT 1');
PREPARE logging_context_stmt FROM @logging_context_ddl; EXECUTE logging_context_stmt; DEALLOCATE PREPARE logging_context_stmt;

UPDATE log_issues i
JOIN system_logs s ON s.event_id = i.last_event_id
SET i.context = s.context
WHERE i.context = 'system';

CREATE TABLE IF NOT EXISTS log_issue_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fingerprint CHAR(64) NOT NULL,
    actor_id INT UNSIGNED NULL,
    request_id CHAR(36) NULL,
    before_status VARCHAR(20) NULL,
    after_status VARCHAR(20) NOT NULL,
    before_assigned_to INT UNSIGNED NULL,
    after_assigned_to INT UNSIGNED NULL,
    before_note VARCHAR(1000) NULL,
    after_note VARCHAR(1000) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_log_issue_history_fingerprint_time (fingerprint, created_at),
    KEY idx_log_issue_history_actor_time (actor_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- شناسهٔ رخدادهای تجمیع‌شده برای بازسازی idempotent از روی system_logs.
CREATE TABLE IF NOT EXISTS log_issue_events (
    event_id CHAR(36) NOT NULL PRIMARY KEY,
    fingerprint CHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_log_issue_events_fingerprint_time (fingerprint, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- رخدادهای موجود پیش از فعال‌شدن retry به‌عنوان سابقهٔ قبلی علامت می‌خورند تا بازسازی آینده شمارنده‌ها را دوباره افزایش ندهد.
INSERT IGNORE INTO log_issue_events (event_id, fingerprint, created_at)
SELECT s.event_id,
       SHA2(CONCAT(LOWER(COALESCE(s.code, 'SYS-LEGACY-001')), '|', LOWER(s.context), '|', LOWER(s.message)), 256),
       s.created_at
FROM system_logs s
WHERE s.event_id IS NOT NULL AND s.level IN ('warning','error','critical');


-- صف مستقل برای تحویل اعلان‌ها؛ timestamp فقط پس از موفقیت واقعی ارسال ثبت می‌شود.
CREATE TABLE IF NOT EXISTS log_issue_notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    notification_key CHAR(64) NOT NULL,
    fingerprint CHAR(64) NOT NULL,
    recipient_id INT UNSIGNED NOT NULL,
    state ENUM('pending','sending','sent','failed') NOT NULL DEFAULT 'pending',
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    next_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_error VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    delivered_at DATETIME NULL,
    UNIQUE KEY uq_log_issue_notification_key (notification_key),
    KEY idx_log_issue_notification_due (state, next_attempt_at),
    KEY idx_log_issue_notification_fingerprint (fingerprint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
