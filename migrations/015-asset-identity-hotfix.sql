-- ۱.۶۲.۳۸ — هاتفیکس اضطراری: تکمیل امن شناسنامه بدون شکستن نصب.
-- تفاوت با ۰۱۴: هیچ ALTER TABLE ... MODIFY روی enum اجرا نمیشود (روی بعضی
-- نصبها بخاطر مقادیر قدیمی source شکست میخورد). فقط additive و idempotent است.

-- ۱) ستون کلید هویت (فقط اگر نباشد)
SET @has_key := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'hostname_key'
);
SET @ddl := IF(@has_key = 0,
  'ALTER TABLE assets ADD COLUMN hostname_key VARCHAR(191) NULL AFTER hostname',
  'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ۲) ایندکس معمولی (فقط اگر نباشد؛ UNIQUE توسط ابزار dedupe اضافه میشود)
SET @has_idx := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assets' AND INDEX_NAME = 'idx_assets_hostname_key'
);
SET @ddl := IF(@has_idx = 0,
  'ALTER TABLE assets ADD INDEX idx_assets_hostname_key (hostname_key)',
  'SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ۳) پرکردن کلید خالی با همان نرمالسازیِ asset-names.php::asset_identity_key()
UPDATE assets
   SET hostname_key = LOWER(TRIM(TRAILING '$' FROM SUBSTRING_INDEX(TRIM(COALESCE(NULLIF(hostname,''), asset_tag)), '.', 1)))
 WHERE (hostname_key IS NULL OR hostname_key = '')
   AND COALESCE(NULLIF(hostname,''), asset_tag, '') <> ''
   AND COALESCE(NULLIF(hostname,''), asset_tag) NOT REGEXP '^[0-9]{1,3}(\\.[0-9]{1,3}){3}$';
UPDATE assets
   SET hostname_key = LOWER(TRIM(COALESCE(NULLIF(hostname,''), asset_tag)))
 WHERE (hostname_key IS NULL OR hostname_key = '')
   AND COALESCE(NULLIF(hostname,''), asset_tag) REGEXP '^[0-9]{1,3}(\\.[0-9]{1,3}){3}$';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key, 8) WHERE hostname_key LIKE 'domain-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key, 8) WHERE hostname_key LIKE 'client-%';
UPDATE assets SET hostname_key = NULL WHERE hostname_key = '';
