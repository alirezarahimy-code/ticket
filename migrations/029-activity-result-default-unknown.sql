-- Audit result default hardening — نسخه 1.62.73
-- رخداد جدیدی که نتیجهٔ آن صریحاً مشخص نشده نباید به‌صورت موفق ثبت شود.
-- اجرای مجدد امن است و داده‌های قبلی را تغییر نمی‌دهد.
SET @activity_result_default = (
    SELECT REPLACE(COALESCE(COLUMN_DEFAULT, ''), CHAR(39), '')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'result'
    LIMIT 1
);
SET @activity_result_ddl = IF(@activity_result_default <> 'unknown',
    'ALTER TABLE activity_logs MODIFY COLUMN result VARCHAR(16) NOT NULL DEFAULT ''unknown''',
    'SELECT 1');
PREPARE activity_result_stmt FROM @activity_result_ddl;
EXECUTE activity_result_stmt;
DEALLOCATE PREPARE activity_result_stmt;
