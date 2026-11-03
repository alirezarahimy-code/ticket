-- بستهٔ تجمیعی: اگر ۰۱۹ قبلاً از راه به‌روزرسان اجرا شده، backfill را تکرار نکنید.
-- جدول schema_updates را به‌روزرسان قبل از اجرای مهاجرت‌ها می‌سازد.
SET @traffic_link_previously_applied = (SELECT COUNT(*) FROM schema_updates WHERE migration IN ('019-traffic-destination-link.sql', '020-traffic-destination-upgrade.sql'));
SET @traffic_col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'traffic_visits' AND COLUMN_NAME = 'destination_id');
SET @traffic_ddl = IF(@traffic_col_exists = 0, 'ALTER TABLE traffic_visits ADD COLUMN destination_id INT UNSIGNED NULL AFTER meeting_with', 'SELECT 1');
PREPARE traffic_stmt FROM @traffic_ddl; EXECUTE traffic_stmt; DEALLOCATE PREPARE traffic_stmt;
SET @traffic_index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'traffic_visits' AND INDEX_NAME = 'idx_traffic_destination_id');
SET @traffic_ddl = IF(@traffic_index_exists = 0, 'ALTER TABLE traffic_visits ADD INDEX idx_traffic_destination_id (destination_id)', 'SELECT 1');
PREPARE traffic_stmt FROM @traffic_ddl; EXECUTE traffic_stmt; DEALLOCATE PREPARE traffic_stmt;
SET @traffic_ddl = IF(@traffic_link_previously_applied = 0, 'UPDATE traffic_visits v JOIN traffic_destinations d ON v.meeting_with = d.title SET v.destination_id = d.id WHERE v.destination_id IS NULL AND v.meeting_with IS NOT NULL AND CHAR_LENGTH(v.meeting_with) > 0', 'SELECT 1');
PREPARE traffic_stmt FROM @traffic_ddl; EXECUTE traffic_stmt; DEALLOCATE PREPARE traffic_stmt;
