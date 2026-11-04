-- مرحلهٔ تکمیلی Logging: تجمیع Issueها و کنترل اعلان‌های سلامت.
-- پیش از اجرا از پایگاه‌داده نسخهٔ پشتیبان تهیه شود. اجرای مجدد امن است.

CREATE TABLE IF NOT EXISTS log_issues (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    fingerprint CHAR(64) NOT NULL,
    code VARCHAR(40) NOT NULL,
    category VARCHAR(24) NOT NULL DEFAULT 'system',
    severity VARCHAR(20) NOT NULL DEFAULT 'error',
    title VARCHAR(255) NOT NULL,
    first_seen DATETIME NOT NULL,
    last_seen DATETIME NOT NULL,
    occurrences BIGINT UNSIGNED NOT NULL DEFAULT 1,
    last_event_id CHAR(36) NULL,
    last_request_id CHAR(36) NULL,
    status ENUM('open','investigating','resolved') NOT NULL DEFAULT 'open',
    resolution_note VARCHAR(1000) NULL,
    assigned_to INT UNSIGNED NULL,
    last_notified_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_log_issues_fingerprint (fingerprint),
    KEY idx_log_issues_status_severity (status, severity),
    KEY idx_log_issues_last_seen (last_seen),
    KEY idx_log_issues_code_category (code, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logging_health_alerts (
    alert_key VARCHAR(80) NOT NULL,
    last_sent_at DATETIME NULL,
    last_state VARCHAR(20) NOT NULL DEFAULT 'clear',
    occurrences BIGINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (alert_key),
    KEY idx_logging_alert_sent (last_sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- شاخص‌های گزارش‌گیری؛ ایجاد فقط یک‌بار در Migration و نه در مسیر درخواست وب.
SET @logging_index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND INDEX_NAME = 'idx_system_logs_code_category_level_time');
SET @logging_index_ddl = IF(@logging_index_exists = 0, 'ALTER TABLE system_logs ADD INDEX idx_system_logs_code_category_level_time (code, category, level, created_at)', 'SELECT 1');
PREPARE logging_index_stmt FROM @logging_index_ddl;
EXECUTE logging_index_stmt;
DEALLOCATE PREPARE logging_index_stmt;

SET @logging_index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND INDEX_NAME = 'idx_activity_category_result_time');
SET @logging_index_ddl = IF(@logging_index_exists = 0, 'ALTER TABLE activity_logs ADD INDEX idx_activity_category_result_time (category, result, created_at)', 'SELECT 1');
PREPARE logging_index_stmt FROM @logging_index_ddl;
EXECUTE logging_index_stmt;
DEALLOCATE PREPARE logging_index_stmt;
