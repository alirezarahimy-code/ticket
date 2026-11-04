-- ثبت منطقی فیش‌های خودکار پس از پایان روز؛ افزایشی و امن برای اجرای مجدد.
-- هیچ سفارش، رویداد یا دادهٔ موجودی حذف یا بازنویسی نمی‌شود.

SET @food_guest_table_exists = (
    SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_guest_requests'
);
SET @food_guest_meal_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_guest_requests' AND COLUMN_NAME = 'meal_type'
);
SET @food_auto_ddl = IF(
    @food_guest_table_exists > 0 AND @food_guest_meal_exists = 0,
    'ALTER TABLE food_guest_requests ADD COLUMN meal_type ENUM(''lunch'',''dinner'') NOT NULL DEFAULT ''lunch'' AFTER request_date',
    'SELECT 1'
);
PREPARE food_auto_stmt FROM @food_auto_ddl;
EXECUTE food_auto_stmt;
DEALLOCATE PREPARE food_auto_stmt;

SET @food_events_table_exists = (
    SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_events'
);
SET @food_event_meal_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_events' AND COLUMN_NAME = 'meal_type'
);
SET @food_auto_ddl = IF(
    @food_events_table_exists > 0 AND @food_event_meal_exists = 0,
    'ALTER TABLE food_ticket_events ADD COLUMN meal_type ENUM(''lunch'',''dinner'') NOT NULL DEFAULT ''lunch'' AFTER event_type',
    'SELECT 1'
);
PREPARE food_auto_stmt FROM @food_auto_ddl;
EXECUTE food_auto_stmt;
DEALLOCATE PREPARE food_auto_stmt;

SET @food_event_type_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_events' AND COLUMN_NAME = 'event_type'
);
-- نگاشت enum فعلی و مقدار legacy «print» حفظ می‌شوند؛ دو نوع رویداد منطقی افزوده می‌شوند.
SET @food_auto_ddl = IF(
    @food_events_table_exists > 0 AND @food_event_type_exists > 0,
    'ALTER TABLE food_ticket_events MODIFY COLUMN event_type ENUM(''printed'',''print'',''print_error'',''no_food'',''unknown'',''inactive'',''repeat'',''guest'',''auto_printed'',''auto_guest'',''guest_limit'',''config_error'') NOT NULL',
    'SELECT 1'
);
PREPARE food_auto_stmt FROM @food_auto_ddl;
EXECUTE food_auto_stmt;
DEALLOCATE PREPARE food_auto_stmt;
