-- 040 — نگاشت پایدار ریشه‌های سازمان به ردیف‌های رسیدگی (departments).
-- هدف: (۱) رفع تداخل هم‌نام «اداری» و مانند آن بین شرکت اصلی و شرکت همکار با پیشوند نام شرکت همکار («آریا ۲.اداری»)؛
--      (۲) پایداری انتساب در تغییر نام چارت/شرکت: از این پس ردیف رسیدگی با شناسهٔ واحد چارت (org_root_unit_id)
--          شناسایی می‌شود، نه با هم‌نام بودن؛ تغییر نام دیگر «ردیف رسیدگی جدید» نمی‌سازد و OLA/گزارش نمی‌شکند.
-- idempotent است؛ هیچ داده‌ای حذف یا بازنویسی نمی‌شود؛ ردیف‌های قبلی در اولین مسیریابی «ادعا» (claim) می‌شوند.
ALTER TABLE departments ADD COLUMN IF NOT EXISTS org_root_unit_id INT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_departments_org_root_unit_id ON departments (org_root_unit_id);
