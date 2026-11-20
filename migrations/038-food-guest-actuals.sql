-- C1 — پاپ‌آپ انتساب مهمان هنگام بستن روز (بند ۱۲ طرح کاربر).
-- جدول واقعی‌های مهمان به‌تفکیک سرشاخهٔ ۶ بخشی؛ افزایشی و امن برای اجرای مجدد.
-- root_unit_id = 0 یعنی «بدون جایگاه» (فقط داده‌های تاریخی/استثنا؛ هرگز برای ثبت‌های جدید ساخته نمی‌شود چون ثبت جدید بی‌جایگاه مسدود است).
CREATE TABLE IF NOT EXISTS food_guest_actuals (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    food_date DATE NOT NULL,
    meal_type VARCHAR(16) NOT NULL DEFAULT 'lunch',
    root_unit_id INT UNSIGNED NOT NULL DEFAULT 0,
    root_name VARCHAR(190) NOT NULL DEFAULT '',
    root_type VARCHAR(32) NOT NULL DEFAULT '',
    requested_count INT UNSIGNED NOT NULL DEFAULT 0,
    actual_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_food_guest_actuals_day (food_date, meal_type, root_unit_id),
    KEY idx_food_guest_actuals_root (root_unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
