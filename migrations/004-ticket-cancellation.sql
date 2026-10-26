-- مسیر لغو قابل‌ردگیری: لغو مستقیم پیش از ارجاع، و درخواست/تصمیم مسئول پس از ارجاع.
-- پیش از اجرا از دیتابیس پشتیبان بگیرید. دستورات افزودن ستون با MySQL 5.7 و MariaDB سازگارند.
ALTER TABLE `tickets`
    MODIFY COLUMN `status` ENUM('new','manager_review','assigned','in_progress','waiting_user','pending','resolved','closed','cancelled') NOT NULL DEFAULT 'manager_review';

SET @ticket_cancel_requested_at_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'cancel_requested_at'
);
SET @ticket_cancel_requested_at_ddl = IF(
    @ticket_cancel_requested_at_exists > 0,
    'SELECT 1',
    'ALTER TABLE `tickets` ADD COLUMN `cancel_requested_at` DATETIME NULL'
);
PREPARE ticket_cancel_requested_at_stmt FROM @ticket_cancel_requested_at_ddl;
EXECUTE ticket_cancel_requested_at_stmt;
DEALLOCATE PREPARE ticket_cancel_requested_at_stmt;

SET @ticket_cancel_requested_by_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'cancel_requested_by'
);
SET @ticket_cancel_requested_by_ddl = IF(
    @ticket_cancel_requested_by_exists > 0,
    'SELECT 1',
    'ALTER TABLE `tickets` ADD COLUMN `cancel_requested_by` INT UNSIGNED NULL'
);
PREPARE ticket_cancel_requested_by_stmt FROM @ticket_cancel_requested_by_ddl;
EXECUTE ticket_cancel_requested_by_stmt;
DEALLOCATE PREPARE ticket_cancel_requested_by_stmt;

SET @ticket_cancel_request_reason_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'cancel_request_reason'
);
SET @ticket_cancel_request_reason_ddl = IF(
    @ticket_cancel_request_reason_exists > 0,
    'SELECT 1',
    'ALTER TABLE `tickets` ADD COLUMN `cancel_request_reason` TEXT NULL'
);
PREPARE ticket_cancel_request_reason_stmt FROM @ticket_cancel_request_reason_ddl;
EXECUTE ticket_cancel_request_reason_stmt;
DEALLOCATE PREPARE ticket_cancel_request_reason_stmt;

-- سیاست ثبت‌شده: همهٔ خدمات IT به شناسنامهٔ فنی سیستم نیاز دارند.
UPDATE `service_catalog` SET `requires_asset` = 1 WHERE `service_group` = 'it';
