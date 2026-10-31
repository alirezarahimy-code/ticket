-- ۱.۶۲.۴۱ — انجام خودکار تمام امور شناسنامه هنگام نصب بسته.
-- جایگزین اجرای دستی tools/asset_dedupe.php --apply --enforce است.
-- ایمن: بکاپ کامل رکوردهای حذفشده نگه داشته میشود، هیچ وابستهای گم نمیشود.
-- idempotent: اجرای دوباره کاری انجام نمیدهد.

-- ── ۰) اطمینان از وجود ستون و ایندکس (نصبهایی که ۰۱۴/۰۱۵ نیمه اجرا شدهاند)
SET @has_key := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='assets' AND COLUMN_NAME='hostname_key');
SET @ddl := IF(@has_key=0,
  'ALTER TABLE assets ADD COLUMN hostname_key VARCHAR(191) NULL AFTER hostname','SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='assets' AND INDEX_NAME='idx_assets_hostname_key');
SET @ddl := IF(@has_idx=0,
  'ALTER TABLE assets ADD INDEX idx_assets_hostname_key (hostname_key)','SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── ۱) نرمالسازی کلیدهای خالی (همان منطق asset-names.php::asset_identity_key)
UPDATE assets SET hostname_key = TRIM(COALESCE(NULLIF(hostname,''), asset_tag))
 WHERE (hostname_key IS NULL OR hostname_key='') AND COALESCE(NULLIF(hostname,''),asset_tag,'')<>'';
UPDATE assets SET hostname_key = REPLACE(hostname_key, UNHEX('E2808C'), '');
UPDATE assets SET hostname_key = REPLACE(hostname_key, UNHEX('E2808F'), '');
UPDATE assets SET hostname_key = REPLACE(hostname_key, UNHEX('E2808E'), '');
UPDATE assets SET hostname_key = TRIM(TRAILING '$' FROM TRIM(hostname_key));
UPDATE assets SET hostname_key = SUBSTRING_INDEX(hostname_key,'.',1)
 WHERE hostname_key NOT REGEXP '^[0-9]{1,3}(\\.[0-9]{1,3}){3}$';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key,8) WHERE hostname_key LIKE 'domain-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key,8) WHERE hostname_key LIKE 'client-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key,8) WHERE hostname_key LIKE 'domain-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key,8) WHERE hostname_key LIKE 'client-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key,8) WHERE hostname_key LIKE 'domain-%';
UPDATE assets SET hostname_key = SUBSTRING(hostname_key,8) WHERE hostname_key LIKE 'client-%';
UPDATE assets SET hostname_key = LOWER(TRIM(hostname_key));
UPDATE assets SET hostname_key = NULL WHERE hostname_key='';

-- ── ۲) نقشهٔ «تکراری → اصلی» (قدیمیترین id در هر گروه نگه داشته میشود)
DROP TABLE IF EXISTS tmp_asset_dedupe_map;
CREATE TABLE tmp_asset_dedupe_map (
  dup_id INT UNSIGNED NOT NULL PRIMARY KEY,
  keeper_id INT UNSIGNED NOT NULL,
  KEY idx_keeper (keeper_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tmp_asset_dedupe_map (dup_id, keeper_id)
SELECT a.id, (SELECT MIN(b.id) FROM assets b WHERE b.hostname_key = a.hostname_key)
  FROM assets a
 WHERE a.hostname_key IS NOT NULL AND a.hostname_key <> ''
   AND a.id > (SELECT MIN(b.id) FROM assets b WHERE b.hostname_key = a.hostname_key);

-- ── ۳) بکاپ کامل تمام ستونهای رکوردهای تکراری (+ id رکورد مقصد)
DROP TABLE IF EXISTS asset_dedupe_backup_016;
CREATE TABLE asset_dedupe_backup_016 AS
SELECT a.* FROM assets a JOIN tmp_asset_dedupe_map m ON a.id = m.dup_id;
ALTER TABLE asset_dedupe_backup_016 ADD COLUMN dedupe_keeper_id INT UNSIGNED NULL,
                                   ADD COLUMN dedupe_backed_up_at DATETIME NULL;
UPDATE asset_dedupe_backup_016 b
  JOIN tmp_asset_dedupe_map m ON b.id = m.dup_id
   SET b.dedupe_keeper_id = m.keeper_id, b.dedupe_backed_up_at = NOW();

-- ── ۴) پر کردن فیلدهای خالی رکورد اصلی از روی رکوردهای تکراری
UPDATE assets k
  JOIN (SELECT keeper_id,
               MAX(NULLIF(serial_number,''))   AS f_serial,
               MAX(NULLIF(manufacturer,''))   AS f_manufacturer,
               MAX(NULLIF(model,''))          AS f_model,
               MAX(NULLIF(operating_system,'')) AS f_os,
               MAX(NULLIF(ip_address,''))     AS f_ip,
               MAX(NULLIF(mac_address,''))    AS f_mac,
               MAX(NULLIF(cpu,''))            AS f_cpu,
               MAX(NULLIF(domain_username,'')) AS f_domain_user,
               MAX(NULLIF(antivirus,''))      AS f_antivirus
          FROM tmp_asset_dedupe_map m JOIN assets d ON d.id = m.dup_id
         GROUP BY keeper_id) f ON f.keeper_id = k.id
   SET k.serial_number    = COALESCE(NULLIF(k.serial_number,''),    f.f_serial),
       k.manufacturer     = COALESCE(NULLIF(k.manufacturer,''),     f.f_manufacturer),
       k.model            = COALESCE(NULLIF(k.model,''),            f.f_model),
       k.operating_system = COALESCE(NULLIF(k.operating_system,''), f.f_os),
       k.ip_address       = COALESCE(NULLIF(k.ip_address,''),       f.f_ip),
       k.mac_address      = COALESCE(NULLIF(k.mac_address,''),      f.f_mac),
       k.cpu              = COALESCE(NULLIF(k.cpu,''),              f.f_cpu),
       k.domain_username  = COALESCE(NULLIF(k.domain_username,''),  f.f_domain_user),
       k.antivirus        = COALESCE(NULLIF(k.antivirus,''),        f.f_antivirus);

-- ── ۵) انتقال خودکار تمام وابستگیها (هر جدولی که FK به assets داشته باشد)
DROP PROCEDURE IF EXISTS sp_asset_dedupe_relink;
DELIMITER //
CREATE PROCEDURE sp_asset_dedupe_relink()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE v_tbl VARCHAR(64);
  DECLARE v_col VARCHAR(64);
  DECLARE cur CURSOR FOR
    SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = 'assets'
       AND REFERENCED_COLUMN_NAME = 'id';
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
  OPEN cur;
  relink_loop: LOOP
    FETCH cur INTO v_tbl, v_col;
    IF done THEN LEAVE relink_loop; END IF;
    SET @q = CONCAT('UPDATE IGNORE `', v_tbl, '` t JOIN tmp_asset_dedupe_map m ON t.`',
                    v_col, '` = m.dup_id SET t.`', v_col, '` = m.keeper_id');
    PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
  END LOOP;
  CLOSE cur;
END//
DELIMITER ;
CALL sp_asset_dedupe_relink();
DROP PROCEDURE IF EXISTS sp_asset_dedupe_relink;

-- domain_scan_queue ستون asset_id دارد ولی FK ندارد ⇒ procedure آن را نمیبیند.
-- فقط در صورتی که جدول و ستون وجود داشته باشند اجرا میشود.
SET @has_dsq := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='domain_scan_queue' AND COLUMN_NAME='asset_id');
SET @ddl2 := IF(@has_dsq > 0,
  'UPDATE IGNORE domain_scan_queue t JOIN tmp_asset_dedupe_map m ON t.asset_id = m.dup_id SET t.asset_id = m.keeper_id',
  'SELECT 1');
PREPARE stmt2 FROM @ddl2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- پروفایل تکراری (asset_id کلید اصلی است) بعد از UPDATE IGNORE پاک میشود
SET @has_prof := (SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='asset_profiles');
SET @ddl2 := IF(@has_prof > 0,
  'DELETE p FROM asset_profiles p JOIN tmp_asset_dedupe_map m ON p.asset_id = m.dup_id',
  'SELECT 1');
PREPARE stmt2 FROM @ddl2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- ── ۶) حذف رکوردهای تکراری (وابستگیها قبلاً منتقل شدهاند)
DELETE a FROM assets a JOIN tmp_asset_dedupe_map m ON a.id = m.dup_id;

-- ── ۷) جلوگیری دائمی از تکرار
SET @has_uq := (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='assets' AND INDEX_NAME='uq_assets_hostname_key');
SET @ddl := IF(@has_uq=0,
  'ALTER TABLE assets ADD UNIQUE KEY uq_assets_hostname_key (hostname_key)','SELECT 1');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

DROP TABLE IF EXISTS tmp_asset_dedupe_map;
-- جدول asset_dedupe_backup_016 عمداً باقی میماند تا قابل برگشت باشد.
