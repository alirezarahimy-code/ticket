-- واحدهای مستقل (نمایندگی) کنار مدیرعامل در چارت سازمانی:
-- unit_type = 'liaison' با parent_id خالی؛ جایگاه نمایش در ستون chart_side ('right' یا 'left').
-- هیچ داده‌ای تغییر نمی‌کند یا حذف نمی‌شود.
ALTER TABLE org_units
    ADD COLUMN IF NOT EXISTS chart_side VARCHAR(10) NULL AFTER unit_type;
