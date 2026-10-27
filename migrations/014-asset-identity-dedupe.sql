-- ۱.۶۲.۳۷ — یکپارچهسازی شناسنامهها: جلوگیری از تکرار نام سیستم.
-- فقط additive و idempotent؛ هیچ رکوردی حذف نمیشود. پاکسازی تکراریها جداگانه در tools/asset_dedupe.php است.
-- بدون ADD COLUMN IF NOT EXISTS، تا روی MySQL خالص هم اجرا شود (mysql CLI این فایل را اجرا میکند).

-- ۱) گستردن enum منبع (نصبهای قدیمی 'domain' را نمیپذیرفتند)
ALTER TABLE assets
  MODIFY source ENUM('manual','server_inventory','agent','domain') NOT NULL DEFAULT 'manual';

-- ۲) ستون کلید هویت (فقط در صورتی که وجود نداشته باشد)
SET @has_hostname_key := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'hostname_key'
);
SET @ddl := IF(@has_hostname_key = 0,
  'ALTER TABLE assets ADD COLUMN hostname_key VARCHAR(191) NULL AFTER hostname',
  'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ۳) ایندکس معمولی (فقط در صورتی که نباشد؛ UNIQUE بعد از dedupe اضافه میشود)
SET @has_idx := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assets' AND INDEX_NAME = 'idx_assets_hostname_key'
);
SET @ddl := IF(@has_idx = 0,
  'ALTER TABLE assets ADD INDEX idx_assets_hostname_key (hostname_key)',
  'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ۴) پرکردن کلید برای رکوردهای خالی — دقیقاً همان منطق lib/asset-identity.php::asset_identity_key()
--    الف) trim و پاکسازی کاراکترهای جهتده نامریی
UPDATE assets
   SET hostname_key = TRIM(COALESCE(NULLIF(hostname,''), asset_tag))
 WHERE (hostname_key IS NULL OR hostname_key = '')
   AND COALESCE(NULLIF(hostname,''), asset_tag, '') <> '';
UPDATE assets SET hostname_key = REPLACE(hostname_key, UNHEX('E2808C'), '');
UPDATE assets SET hostname_key = REPLACE(hostname_key, UNHEX('E2808F'), '');
UPDATE assets SET hostname_key = REPLACE(hostname_key, UNHEX('E2808E'), '');
UPDATE assets SET hostname_key = TRIM(hostname_key);
--    ب) پسوند $ حساب کامپیوتر
UPDATE assets SET hostname_key = TRIM(TRAILING '$' FROM hostname_key);
--    ج) حذف بخش دامنه (فقط برای غیر IP؛ IP دستنخورده میماند)
UPDATE assets SET hostname_key = SUBSTRING_INDEX(hostname_key, '.', 1)
 WHERE hostname_key NOT REGEXP '^[0-9]{1,3}(\\.[0-9]{1,3}){3}$';
--    د) حذف پیشوندهای خودکار DOMAIN- / CLIENT- (تا ۳ بار، مثل PHP)
UPDATE assets SET hostname_key = SUBSTRING(hostname_key, 8) WHERE hostname_key LIKE 'domain-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key, 8) WHERE hostname_key LIKE 'client-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key, 8) WHERE hostname_key LIKE 'domain-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key, 8) WHERE hostname_key LIKE 'client-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key, 8) WHERE hostname_key LIKE 'domain-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key, 8) WHERE hostname_key LIKE 'client-%';
--    هـ) lowercase نهایی
UPDATE assets SET hostname_key = LOWER(TRIM(hostname_key));
UPDATE assets SET hostname_key = NULL WHERE hostname_key = '';
