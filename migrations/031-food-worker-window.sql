-- پنجرهٔ کاری Worker فیش غذا + کاهش تأخیر چاپ.
-- افزایشی و امن برای اجرای مجدد: هیچ ستون یا داده‌ای حذف/بازنویسی نمی‌شود.
-- مقادیر پیش‌فرض طوری انتخاب شده‌اند که نصب‌های موجود بدون تنظیم چیزی،
-- دقیقاً مثل قبل رفتار کنند مگر اینکه ادمین پنجره را فعال کند.

SET @ftw_table_exists = (
    SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config'
);

-- ۱) فعال/غیرفعال بودن پنجرهٔ کاری
SET @ftw_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config' AND COLUMN_NAME = 'window_enabled'
);
SET @ftw_ddl = IF(@ftw_table_exists > 0 AND @ftw_col = 0,
    'ALTER TABLE food_ticket_config ADD COLUMN window_enabled TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT 1');
PREPARE ftw_stmt FROM @ftw_ddl; EXECUTE ftw_stmt; DEALLOCATE PREPARE ftw_stmt;

-- ۲) روزهای فعال هفته به‌صورت ISO-8601 (دوشنبه=1 … یکشنبه=7). پیش‌فرض: شنبه تا چهارشنبه.
SET @ftw_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config' AND COLUMN_NAME = 'window_days'
);
SET @ftw_ddl = IF(@ftw_table_exists > 0 AND @ftw_col = 0,
    'ALTER TABLE food_ticket_config ADD COLUMN window_days VARCHAR(32) NOT NULL DEFAULT ''6,7,1,2,3''',
    'SELECT 1');
PREPARE ftw_stmt FROM @ftw_ddl; EXECUTE ftw_stmt; DEALLOCATE PREPARE ftw_stmt;

-- ۳) ساعت شروع پنجره
SET @ftw_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config' AND COLUMN_NAME = 'window_start'
);
SET @ftw_ddl = IF(@ftw_table_exists > 0 AND @ftw_col = 0,
    'ALTER TABLE food_ticket_config ADD COLUMN window_start TIME NOT NULL DEFAULT ''11:00:00''',
    'SELECT 1');
PREPARE ftw_stmt FROM @ftw_ddl; EXECUTE ftw_stmt; DEALLOCATE PREPARE ftw_stmt;

-- ۴) ساعت پایان پنجره
SET @ftw_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config' AND COLUMN_NAME = 'window_end'
);
SET @ftw_ddl = IF(@ftw_table_exists > 0 AND @ftw_col = 0,
    'ALTER TABLE food_ticket_config ADD COLUMN window_end TIME NOT NULL DEFAULT ''14:00:00''',
    'SELECT 1');
PREPARE ftw_stmt FROM @ftw_ddl; EXECUTE ftw_stmt; DEALLOCATE PREPARE ftw_stmt;

-- ۵) حاشیهٔ پیش از شروع (دقیقه) — جبران اختلاف ساعت دستگاه تردد و سرور
SET @ftw_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config' AND COLUMN_NAME = 'window_margin_before_minutes'
);
SET @ftw_ddl = IF(@ftw_table_exists > 0 AND @ftw_col = 0,
    'ALTER TABLE food_ticket_config ADD COLUMN window_margin_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 30',
    'SELECT 1');
PREPARE ftw_stmt FROM @ftw_ddl; EXECUTE ftw_stmt; DEALLOCATE PREPARE ftw_stmt;

-- ۶) حاشیهٔ پس از پایان (دقیقه) — فرصت نوشتن آخرین تردد و تخلیهٔ صف چاپ
SET @ftw_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config' AND COLUMN_NAME = 'window_margin_after_minutes'
);
SET @ftw_ddl = IF(@ftw_table_exists > 0 AND @ftw_col = 0,
    'ALTER TABLE food_ticket_config ADD COLUMN window_margin_after_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 15',
    'SELECT 1');
PREPARE ftw_stmt FROM @ftw_ddl; EXECUTE ftw_stmt; DEALLOCATE PREPARE ftw_stmt;

-- ۷) فاصلهٔ پایش بیرون از پنجره (ثانیه)
SET @ftw_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config' AND COLUMN_NAME = 'idle_poll_seconds'
);
SET @ftw_ddl = IF(@ftw_table_exists > 0 AND @ftw_col = 0,
    'ALTER TABLE food_ticket_config ADD COLUMN idle_poll_seconds SMALLINT UNSIGNED NOT NULL DEFAULT 60',
    'SELECT 1');
PREPARE ftw_stmt FROM @ftw_ddl; EXECUTE ftw_stmt; DEALLOCATE PREPARE ftw_stmt;

-- ۸) فاصلهٔ پایش داخل پنجره (میلی‌ثانیه) — جانشین دقیق‌تر poll_seconds
SET @ftw_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config' AND COLUMN_NAME = 'active_poll_ms'
);
SET @ftw_ddl = IF(@ftw_table_exists > 0 AND @ftw_col = 0,
    'ALTER TABLE food_ticket_config ADD COLUMN active_poll_ms SMALLINT UNSIGNED NOT NULL DEFAULT 500',
    'SELECT 1');
PREPARE ftw_stmt FROM @ftw_ddl; EXECUTE ftw_stmt; DEALLOCATE PREPARE ftw_stmt;

-- ۹) نگه‌داشتن اتصال Access بین چرخه‌ها (حذف هزینهٔ باز/بستهٔ مکرر)
SET @ftw_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_config' AND COLUMN_NAME = 'persistent_source_connection'
);
SET @ftw_ddl = IF(@ftw_table_exists > 0 AND @ftw_col = 0,
    'ALTER TABLE food_ticket_config ADD COLUMN persistent_source_connection TINYINT(1) NOT NULL DEFAULT 1',
    'SELECT 1');
PREPARE ftw_stmt FROM @ftw_ddl; EXECUTE ftw_stmt; DEALLOCATE PREPARE ftw_stmt;
