-- نسخهٔ ۱.۵۰: تردد خاص (اطلاع ورود برای افزایش سقف مهمان غذا) داخل traffic_visits
-- MariaDB: IF NOT EXISTS کار می‌کند. در MySQL 8 خط ADD COLUMN را بدون IF NOT EXISTS اجرا کنید
-- (اگر ستون از قبل باشد، خطا می‌دهد؛ همان را نادیده بگیرید).
ALTER TABLE traffic_visits MODIFY national_code VARCHAR(10) NULL;
ALTER TABLE traffic_visits ADD COLUMN IF NOT EXISTS is_notice TINYINT(1) NOT NULL DEFAULT 0;
