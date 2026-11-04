-- Logging fingerprint v2 — نسخهٔ 1.62.73
-- افزایش طول fingerprint از SHA-1 (40) به SHA-256 (64). داده‌های قدیمی بدون بازنویسی حفظ می‌شوند.
-- اجرای مجدد امن است و فقط در صورت نیاز نوع ستون را افزایش می‌دهد.
SET @logging_fp_len = (
    SELECT COALESCE(MAX(CHARACTER_MAXIMUM_LENGTH), 0)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'system_logs' AND COLUMN_NAME = 'fingerprint'
);
SET @logging_fp_ddl = IF(@logging_fp_len > 0 AND @logging_fp_len < 64,
    'ALTER TABLE system_logs MODIFY COLUMN fingerprint CHAR(64) NULL',
    'SELECT 1');
PREPARE logging_fp_stmt FROM @logging_fp_ddl;
EXECUTE logging_fp_stmt;
DEALLOCATE PREPARE logging_fp_stmt;
