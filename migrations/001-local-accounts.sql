-- ۱.۵۰: نقش‌های کاربر محلی و نگهبان + اجبار تغییر رمز در اولین ورود.
-- idempotent: اجرای دوباره خطا نمی‌دهد (MariaDB/XAMPP از ADD COLUMN IF NOT EXISTS پشتیبانی می‌کند).
ALTER TABLE users
    MODIFY COLUMN role ENUM('user','agent','manager','supervisor','admin','primary_admin','support_manager','inspector','local_user','guard') NOT NULL DEFAULT 'user';
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS must_change_password TINYINT(1) NOT NULL DEFAULT 0;
