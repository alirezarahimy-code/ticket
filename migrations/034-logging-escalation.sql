-- تشدید شدت + خلاصهٔ هوشمند لاگینگ — نسخهٔ 1.62.89
-- افزایشی و امن برای اجرای مجدد: هیچ ستون یا داده‌ای حذف/بازنویسی نمی‌شود.
-- دو ستون جدید به system_logs اضافه می‌شود تا شمارش تکرار و برچسب شدت
-- بدون نیاز به parsing JSON قابل کوئری باشند.

-- ۱) شمارش تکرار fingerprint در پنجرهٔ زمانی
SET @esc_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND COLUMN_NAME = 'escalation_count'
);
SET @esc_ddl = IF(@esc_exists = 0,
    'ALTER TABLE system_logs ADD COLUMN escalation_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER meta_json',
    'SELECT 1');
PREPARE esc_stmt FROM @esc_ddl; EXECUTE esc_stmt; DEALLOCATE PREPARE esc_stmt;

-- ۲) برچسب فارسی شدت (جزئی / تکراری / بحرانی / فاجعه)
SET @esc_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND COLUMN_NAME = 'escalation_label'
);
SET @esc_ddl = IF(@esc_exists = 0,
    'ALTER TABLE system_logs ADD COLUMN escalation_label VARCHAR(64) NULL AFTER escalation_count',
    'SELECT 1');
PREPARE esc_stmt FROM @esc_ddl; EXECUTE esc_stmt; DEALLOCATE PREPARE esc_stmt;

-- ۳) ایندکس برای کوئری خلاصهٔ هوشمند (فیلتر بر اساس سطح + زمان)
SET @esc_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND INDEX_NAME = 'idx_system_logs_level_time'
);
SET @esc_ddl = IF(@esc_exists = 0,
    'ALTER TABLE system_logs ADD KEY idx_system_logs_level_time (level, created_at)',
    'SELECT 1');
PREPARE esc_stmt FROM @esc_ddl; EXECUTE esc_stmt; DEALLOCATE PREPARE esc_stmt;