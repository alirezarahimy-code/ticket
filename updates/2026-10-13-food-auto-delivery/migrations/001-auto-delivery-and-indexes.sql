-- 1.53: تحویل خودکار فیش هنگام چاپ موفق + ایندکس‌های صف چاپ و منوی غذا
-- idempotent و فقط افزایشی است.

ALTER TABLE `food_ticket_events` ADD COLUMN IF NOT EXISTS `delivery_status` ENUM('pending','delivered') NOT NULL DEFAULT 'pending' AFTER `print_status`;
ALTER TABLE `food_ticket_events` ADD COLUMN IF NOT EXISTS `delivered_at` DATETIME NULL AFTER `delivery_status`;
ALTER TABLE `food_ticket_events` ADD COLUMN IF NOT EXISTS `delivered_by` INT UNSIGNED NULL AFTER `delivered_at`;

-- فیش‌هایی که قبلاً چاپ شده‌اند ولی دستی تحویل نشده‌اند، چون با قاعدهٔ جدید «چاپ = تحویل» تحویل‌شده حساب می‌شوند.
UPDATE `food_ticket_events`
   SET `delivery_status` = 'delivered', `delivered_at` = COALESCE(`delivered_at`, `processed_at`, NOW())
 WHERE `print_status` = 'printed' AND `delivery_status` = 'pending';

-- ایندکس صف چاپ: پردازش صف دیگر جدول را کامل اسکن نمی‌کند.
CREATE INDEX IF NOT EXISTS `idx_fte_print_queue` ON `food_ticket_events` (`print_status`, `next_retry_at`);

-- ایندکس بانک غذا برای فهرست و منوی روزانه.
CREATE INDEX IF NOT EXISTS `idx_food_catalog_active_name` ON `food_catalog` (`active`, `food_name`);
