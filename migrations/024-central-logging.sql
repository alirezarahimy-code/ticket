-- مهاجرت افزایشی و قابل تکرار هستهٔ Logging برای نسخهٔ 1.62.60 به 1.62.61.
-- ستون‌های قدیمی حذف یا بازنویسی نمی‌شوند؛ ردیف‌های قدیمی در category حسابرسی به‌صورت محافظه‌کارانه نگهداری می‌شوند.
-- هر تغییر جداگانه بررسی می‌شود تا اجرای مجدد پس از شکست میانی امن باشد.

-- system_logs
SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND COLUMN_NAME = 'event_id');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE system_logs ADD COLUMN event_id CHAR(36) NULL AFTER id', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND COLUMN_NAME = 'request_id');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE system_logs ADD COLUMN request_id CHAR(36) NULL AFTER event_id', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND COLUMN_NAME = 'code');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE system_logs ADD COLUMN code VARCHAR(40) NULL AFTER request_id', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND COLUMN_NAME = 'category');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE system_logs ADD COLUMN category VARCHAR(24) NOT NULL DEFAULT ''system'' AFTER code', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND COLUMN_NAME = 'fingerprint');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE system_logs ADD COLUMN fingerprint CHAR(40) NULL AFTER category', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND INDEX_NAME = 'uq_system_event_id');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE system_logs ADD UNIQUE KEY uq_system_event_id (event_id)', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND INDEX_NAME = 'idx_system_logs_request');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE system_logs ADD KEY idx_system_logs_request (request_id)', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND INDEX_NAME = 'idx_system_logs_fingerprint_time');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE system_logs ADD KEY idx_system_logs_fingerprint_time (fingerprint, created_at)', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

-- activity_logs؛ داده‌های قدیمی با category=audit حفظ می‌شوند تا Retention محافظه‌کارانه اعمال شود.
SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'event_id');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE activity_logs ADD COLUMN event_id CHAR(36) NULL AFTER id', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'request_id');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE activity_logs ADD COLUMN request_id CHAR(36) NULL AFTER event_id', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'result');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE activity_logs ADD COLUMN result VARCHAR(16) NOT NULL DEFAULT ''unknown'' AFTER request_id', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'code');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE activity_logs ADD COLUMN code VARCHAR(40) NULL AFTER result', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'category');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE activity_logs ADD COLUMN category VARCHAR(24) NOT NULL DEFAULT ''audit'' AFTER code', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND INDEX_NAME = 'uq_activity_event_id');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE activity_logs ADD UNIQUE KEY uq_activity_event_id (event_id)', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND INDEX_NAME = 'idx_activity_request');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE activity_logs ADD KEY idx_activity_request (request_id)', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

-- ticket_events
SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_events' AND COLUMN_NAME = 'event_id');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE ticket_events ADD COLUMN event_id CHAR(36) NULL AFTER id', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_events' AND COLUMN_NAME = 'request_id');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE ticket_events ADD COLUMN request_id CHAR(36) NULL AFTER event_id', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_events' AND INDEX_NAME = 'uq_ticket_event_id');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE ticket_events ADD UNIQUE KEY uq_ticket_event_id (event_id)', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;

SET @logging_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_events' AND INDEX_NAME = 'idx_ticket_events_request');
SET @logging_ddl = IF(@logging_exists = 0, 'ALTER TABLE ticket_events ADD KEY idx_ticket_events_request (request_id)', 'SELECT 1');
PREPARE logging_stmt FROM @logging_ddl; EXECUTE logging_stmt; DEALLOCATE PREPARE logging_stmt;
