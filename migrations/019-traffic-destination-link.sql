-- شناسهٔ مقصد را نگه می‌دارد تا گزارش با تغییر عنوان مقصد نیز پیوندش را از دست ندهد.
-- نام مقصد و تأییدکنندهٔ ثبت‌شده، همچنان snapshot تاریخی مستقل هستند.
SET @traffic_col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'traffic_visits' AND COLUMN_NAME = 'destination_id');
SET @traffic_ddl = IF(@traffic_col_exists = 0, 'ALTER TABLE traffic_visits ADD COLUMN destination_id INT UNSIGNED NULL AFTER meeting_with', 'SELECT 1');
PREPARE traffic_stmt FROM @traffic_ddl; EXECUTE traffic_stmt; DEALLOCATE PREPARE traffic_stmt;
SET @traffic_index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'traffic_visits' AND INDEX_NAME = 'idx_traffic_destination_id');
SET @traffic_ddl = IF(@traffic_index_exists = 0, 'ALTER TABLE traffic_visits ADD INDEX idx_traffic_destination_id (destination_id)', 'SELECT 1');
PREPARE traffic_stmt FROM @traffic_ddl; EXECUTE traffic_stmt; DEALLOCATE PREPARE traffic_stmt;
-- فقط عناوینی که هنوز در جدول مقصد وجود دارند قابل تطبیق‌اند؛ سوابق مبهم یا ناشناخته تغییر نمی‌کنند.
UPDATE traffic_visits v JOIN traffic_destinations d ON v.meeting_with = d.title
SET v.destination_id = d.id WHERE v.destination_id IS NULL AND v.meeting_with IS NOT NULL AND v.meeting_with <> '';
