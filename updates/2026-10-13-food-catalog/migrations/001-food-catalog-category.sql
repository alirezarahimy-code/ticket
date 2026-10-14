SET NAMES utf8mb4;
-- بانک غذا: دسته‌بندی غذا (کبابی، خورشتی، ساندویچ، پلو، خوراک، رژیمی). افزایشی و idempotent.
ALTER TABLE food_catalog ADD COLUMN IF NOT EXISTS category VARCHAR(40) NULL AFTER food_name;
