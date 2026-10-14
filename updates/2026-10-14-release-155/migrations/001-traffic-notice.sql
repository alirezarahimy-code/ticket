-- تردد خاص (اطلاع ورود): ستون is_notice و امکان ثبت بدون کد ملی.
-- idempotent: اجرای دوباره خطا نمی‌دهد (MariaDB). ستون‌ها فقط افزایشی‌اند.
ALTER TABLE traffic_visits MODIFY national_code VARCHAR(10) NULL;
ALTER TABLE traffic_visits ADD COLUMN IF NOT EXISTS is_notice TINYINT(1) NOT NULL DEFAULT 0;
