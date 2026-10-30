-- افزودن ترتیب قابل‌تنظیم معاونت‌ها و واحدها؛ افزایشی و بدون تغییر شناسه‌ها، والدها یا انتساب‌ها.
-- این migration باید صریحاً در پنجرهٔ نگهداری اجرا شود؛ برنامه هنگام بازشدن صفحه ALTER TABLE نمی‌زند.
ALTER TABLE org_units
    ADD COLUMN IF NOT EXISTS sort_order INT NOT NULL DEFAULT 0 AFTER chart_side;
