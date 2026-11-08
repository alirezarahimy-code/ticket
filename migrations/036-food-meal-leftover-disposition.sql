-- سرنوشت غذای باقیمانده برای تطبیق روزانهٔ غذا.
-- افزایشی: این فایل هیچ ستونی حذف نمی‌کند و هیچ داده‌ای را بازنویسی نمی‌کند.
-- برای دیتابیس‌های تازه و دیتابیس‌های ارتقا، هر دو حالت امن است (MariaDB/XAMPP از ADD COLUMN IF NOT EXISTS پشتیبانی می‌کند).
ALTER TABLE food_meal_reconciliations
    ADD COLUMN IF NOT EXISTS leftover_staff_quantity INT UNSIGNED NULL AFTER waste_quantity,
    ADD COLUMN IF NOT EXISTS leftover_waste_quantity INT UNSIGNED NULL AFTER leftover_staff_quantity,
    ADD COLUMN IF NOT EXISTS leftover_keep_quantity INT UNSIGNED NULL AFTER leftover_waste_quantity,
    ADD COLUMN IF NOT EXISTS leftover_other_quantity INT UNSIGNED NULL AFTER leftover_keep_quantity,
    ADD COLUMN IF NOT EXISTS leftover_other_note VARCHAR(500) NULL AFTER leftover_other_quantity;
