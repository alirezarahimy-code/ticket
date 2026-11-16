-- اسکریپت بازگشت «دستی» برای 035 — فقط روی کلون آزموده‌شده و پس از بکاپ اجرا شود.
-- این فایل را داخل migrations/ یا فهرست manifest بسته نگذارید (runner آن را مهاجرت تلقی می‌کند).
-- اجرا نشده/آزموده‌نشده است: پیش از هر استفاده روی MariaDB 10.4/10.11 و MySQL 8 تست شود.
--
-- چه چیزی برگردانده می‌شود:
--   * حذف جدول request_center_appointments
--   * حذف مقدار rejected از ENUM tickets.status و حذف ستون tickets.rejected_at
-- چه چیزی «برنمی‌گردد» (عمداً؛ بی‌خطر و ممکن است توسط کد قدیمی هم استفاده شود):
--   tickets.ticket_no/uq_tickets_no، tickets.requesting_unit_id، categories.code و دسته‌های درج‌شده،
--   service_catalog.category_id/FK و مقادیر پرشده، جدول ticket_number_seq.
-- شرط توقف: اگر تیکتی با وضعیت rejected یا rejected_at پر، یا نوبتی در جدول نوبت‌ها باشد، هیچ تغییری اعمال نمی‌شود.

SET @rc_rb_rejected = (SELECT COUNT(*) FROM tickets WHERE status = 'rejected');

SET @rc_rb_has_rejected_at = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'rejected_at');
SET @rc_rb_sql = IF(@rc_rb_has_rejected_at > 0, 'SELECT COUNT(*) INTO @rc_rb_rejected_at_rows FROM tickets WHERE rejected_at IS NOT NULL', 'SET @rc_rb_rejected_at_rows = 0');
PREPARE rc_rb_stmt FROM @rc_rb_sql; EXECUTE rc_rb_stmt; DEALLOCATE PREPARE rc_rb_stmt;

SET @rc_rb_has_appt = (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'request_center_appointments');
SET @rc_rb_sql = IF(@rc_rb_has_appt > 0, 'SELECT COUNT(*) INTO @rc_rb_appt_rows FROM request_center_appointments', 'SET @rc_rb_appt_rows = 0');
PREPARE rc_rb_stmt FROM @rc_rb_sql; EXECUTE rc_rb_stmt; DEALLOCATE PREPARE rc_rb_stmt;

SET @rc_rb_blocked = IF(@rc_rb_rejected > 0 OR @rc_rb_rejected_at_rows > 0 OR @rc_rb_appt_rows > 0, 1, 0);
SELECT IF(@rc_rb_blocked = 1,
          'ABORT: وضعیت rejected یا نوبت ثبت‌شده وجود دارد؛ هیچ تغییری اعمال نشد. ابتدا داده‌ها را بررسی/خروجی بگیرید.',
          'OK: بازگشت اعمال می‌شود.') AS rc_rollback_status,
       @rc_rb_rejected AS rejected_tickets, @rc_rb_rejected_at_rows AS rejected_at_rows, @rc_rb_appt_rows AS appointment_rows;

-- ۱) برگرداندن ENUM وضعیت (حذف rejected)
SET @rc_rb_type = (SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'status');
SET @rc_rb_sql = IF(@rc_rb_blocked = 0 AND @rc_rb_type LIKE '%''rejected''%',
    CONCAT('ALTER TABLE `tickets` MODIFY COLUMN `status` ', REPLACE(@rc_rb_type, ',''rejected''', ''), ' NOT NULL DEFAULT ''manager_review'''),
    'SELECT 1');
PREPARE rc_rb_stmt FROM @rc_rb_sql; EXECUTE rc_rb_stmt; DEALLOCATE PREPARE rc_rb_stmt;

-- ۲) حذف ستون rejected_at
SET @rc_rb_sql = IF(@rc_rb_blocked = 0 AND @rc_rb_has_rejected_at > 0, 'ALTER TABLE `tickets` DROP COLUMN `rejected_at`', 'SELECT 1');
PREPARE rc_rb_stmt FROM @rc_rb_sql; EXECUTE rc_rb_stmt; DEALLOCATE PREPARE rc_rb_stmt;

-- ۳) حذف جدول نوبت‌ها (فقط وقتی خالی است)
SET @rc_rb_sql = IF(@rc_rb_blocked = 0, 'DROP TABLE IF EXISTS `request_center_appointments`', 'SELECT 1');
PREPARE rc_rb_stmt FROM @rc_rb_sql; EXECUTE rc_rb_stmt; DEALLOCATE PREPARE rc_rb_stmt;

-- پس از موفقیت، اگر لازم است 035 دوباره اعمال شود:
--   DELETE FROM schema_updates WHERE migration = '035-request-center-appointments.sql';
