-- مهاجرت ۰۳۸: ستون‌های نقش خودکار مبتنی بر چارت سازمانی.
-- افزودن ستون‌های نقش خودکار مبتنی بر چارت سازمانی.
-- این دو ستون قبلاً به‌صورت DDL تنبل در organization.php ساخته می‌شدند،
-- اما مسیر مستقل Request Center در درخواست وب نباید برای ترمیم شِما DDL اجرا کند.
-- افزایشی و idempotent؛ هیچ نقش یا دادهٔ موجودی بازنویسی/حذف نمی‌شود.

SET @rc_org_auto_role_exists = ( SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'org_auto_role' );
SET @rc_org_auto_role_ddl = IF(@rc_org_auto_role_exists > 0, 'SELECT 1', 'ALTER TABLE `users` ADD COLUMN `org_auto_role` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_primary_admin`');
PREPARE rc_org_auto_role_stmt FROM @rc_org_auto_role_ddl;
EXECUTE rc_org_auto_role_stmt;
DEALLOCATE PREPARE rc_org_auto_role_stmt;

SET @rc_org_prev_role_exists = ( SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'org_prev_role' );
SET @rc_org_prev_role_ddl = IF(@rc_org_prev_role_exists > 0, 'SELECT 1', 'ALTER TABLE `users` ADD COLUMN `org_prev_role` VARCHAR(20) NULL AFTER `org_auto_role`');
PREPARE rc_org_prev_role_stmt FROM @rc_org_prev_role_ddl;
EXECUTE rc_org_prev_role_stmt;
DEALLOCATE PREPARE rc_org_prev_role_stmt;
