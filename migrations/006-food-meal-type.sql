-- Split food calendars and reservations by meal. Existing records remain lunch by default.
SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_calendar' AND COLUMN_NAME = 'meal_type') = 0,
    'ALTER TABLE food_calendar ADD COLUMN meal_type ENUM(''lunch'',''dinner'') NOT NULL DEFAULT ''lunch'' AFTER food_date',
    'SELECT 1'
);
PREPARE food_meal_stmt FROM @ddl;
EXECUTE food_meal_stmt;
DEALLOCATE PREPARE food_meal_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_calendar' AND INDEX_NAME = 'uq_food_calendar_date') > 0,
    'ALTER TABLE food_calendar DROP INDEX uq_food_calendar_date',
    'SELECT 1'
);
PREPARE food_meal_stmt FROM @ddl;
EXECUTE food_meal_stmt;
DEALLOCATE PREPARE food_meal_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_calendar' AND INDEX_NAME = 'uq_food_calendar_date_meal') = 0,
    'ALTER TABLE food_calendar ADD UNIQUE KEY uq_food_calendar_date_meal (food_date, meal_type)',
    'SELECT 1'
);
PREPARE food_meal_stmt FROM @ddl;
EXECUTE food_meal_stmt;
DEALLOCATE PREPARE food_meal_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_orders' AND COLUMN_NAME = 'meal_type') = 0,
    'ALTER TABLE food_orders ADD COLUMN meal_type ENUM(''lunch'',''dinner'') NOT NULL DEFAULT ''lunch'' AFTER food_date',
    'SELECT 1'
);
PREPARE food_meal_stmt FROM @ddl;
EXECUTE food_meal_stmt;
DEALLOCATE PREPARE food_meal_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_orders' AND INDEX_NAME = 'uq_food_order_employee_date') > 0,
    'ALTER TABLE food_orders DROP INDEX uq_food_order_employee_date',
    'SELECT 1'
);
PREPARE food_meal_stmt FROM @ddl;
EXECUTE food_meal_stmt;
DEALLOCATE PREPARE food_meal_stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_orders' AND INDEX_NAME = 'uq_food_order_employee_date_meal') = 0,
    'ALTER TABLE food_orders ADD UNIQUE KEY uq_food_order_employee_date_meal (employee_id, food_date, meal_type)',
    'SELECT 1'
);
PREPARE food_meal_stmt FROM @ddl;
EXECUTE food_meal_stmt;
DEALLOCATE PREPARE food_meal_stmt;
