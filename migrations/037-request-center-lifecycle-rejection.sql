-- Request Center lifecycle completion: explicit rejection and removal of
-- request-time schema repair dependencies. MariaDB 10.4 compatible.
-- Scope: RC-owned tickets/categories/service_catalog only.
-- Does not modify Logging migration 034 or Food migration 036.

-- Ticket numbering is part of the Request Center create contract. Prepare
-- its schema here so the module never invokes ticket_number_ensure_schema().
CREATE TABLE IF NOT EXISTS ticket_number_seq (
    prefix VARCHAR(20) NOT NULL PRIMARY KEY,
    seq INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @rc_ticket_no_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'ticket_no');
SET @rc_ticket_no_ddl = IF(@rc_ticket_no_exists > 0, 'SELECT 1', 'ALTER TABLE `tickets` ADD COLUMN `ticket_no` VARCHAR(40) NULL AFTER `id`');
PREPARE rc_ticket_no_stmt FROM @rc_ticket_no_ddl;
EXECUTE rc_ticket_no_stmt;
DEALLOCATE PREPARE rc_ticket_no_stmt;

SET @rc_ticket_no_unique = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'ticket_no' AND NON_UNIQUE = 0);
SET @rc_ticket_no_unique_ddl = IF(@rc_ticket_no_unique > 0, 'SELECT 1', 'CREATE UNIQUE INDEX `uq_tickets_no` ON `tickets` (`ticket_no`)');
PREPARE rc_ticket_no_unique_stmt FROM @rc_ticket_no_unique_ddl;
EXECUTE rc_ticket_no_unique_stmt;
DEALLOCATE PREPARE rc_ticket_no_unique_stmt;

-- Preserve any additional enum values already present in an installation.
SET @rc_status_type = (
    SELECT COLUMN_TYPE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'status'
);
SET @rc_status_default = (
    SELECT COLUMN_DEFAULT FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'status'
);
SET @rc_status_ddl = CASE
    WHEN @rc_status_type IS NULL THEN 'SELECT 1'
    WHEN LOCATE('''rejected''', @rc_status_type) > 0 THEN 'SELECT 1'
    WHEN LOWER(@rc_status_type) LIKE 'enum(%' THEN CONCAT(
        'ALTER TABLE `tickets` MODIFY COLUMN `status` ',
        LEFT(@rc_status_type, CHAR_LENGTH(@rc_status_type) - 1),
        ',''rejected'') NOT NULL DEFAULT ',
        QUOTE(TRIM(BOTH CHAR(39) FROM COALESCE(@rc_status_default, 'manager_review')))
    )
    ELSE 'SELECT 1'
END;
PREPARE rc_status_stmt FROM @rc_status_ddl;
EXECUTE rc_status_stmt;
DEALLOCATE PREPARE rc_status_stmt;

SET @rc_rejected_at_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'rejected_at');
SET @rc_rejected_at_ddl = IF(@rc_rejected_at_exists > 0, 'SELECT 1', 'ALTER TABLE `tickets` ADD COLUMN `rejected_at` DATETIME NULL AFTER `closed_at`');
PREPARE rc_rejected_at_stmt FROM @rc_rejected_at_ddl;
EXECUTE rc_rejected_at_stmt;
DEALLOCATE PREPARE rc_rejected_at_stmt;

SET @rc_category_code_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'code');
SET @rc_category_code_ddl = IF(@rc_category_code_exists > 0, 'SELECT 1', 'ALTER TABLE `categories` ADD COLUMN `code` VARCHAR(60) NULL');
PREPARE rc_category_code_stmt FROM @rc_category_code_ddl;
EXECUTE rc_category_code_stmt;
DEALLOCATE PREPARE rc_category_code_stmt;

SET @rc_category_code_index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'code' AND NON_UNIQUE = 0);
SET @rc_category_code_index_ddl = IF(@rc_category_code_index_exists > 0, 'SELECT 1', 'CREATE UNIQUE INDEX `uq_categories_code` ON `categories` (`code`)');
PREPARE rc_category_code_index_stmt FROM @rc_category_code_index_ddl;
EXECUTE rc_category_code_index_stmt;
DEALLOCATE PREPARE rc_category_code_index_stmt;

SET @rc_service_category_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_catalog' AND COLUMN_NAME = 'category_id');
SET @rc_service_category_ddl = IF(@rc_service_category_exists > 0, 'SELECT 1', 'ALTER TABLE `service_catalog` ADD COLUMN `category_id` INT UNSIGNED NULL AFTER `department_id`');
PREPARE rc_service_category_stmt FROM @rc_service_category_ddl;
EXECUTE rc_service_category_stmt;
DEALLOCATE PREPARE rc_service_category_stmt;

SET @rc_service_category_index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_catalog' AND INDEX_NAME = 'idx_services_category');
SET @rc_service_category_index_ddl = IF(@rc_service_category_index_exists > 0, 'SELECT 1', 'CREATE INDEX `idx_services_category` ON `service_catalog` (`category_id`)');
PREPARE rc_service_category_index_stmt FROM @rc_service_category_index_ddl;
EXECUTE rc_service_category_index_stmt;
DEALLOCATE PREPARE rc_service_category_index_stmt;

SET @rc_requesting_unit_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'requesting_unit_id');
SET @rc_requesting_unit_ddl = IF(@rc_requesting_unit_exists > 0, 'SELECT 1', 'ALTER TABLE `tickets` ADD COLUMN `requesting_unit_id` INT UNSIGNED NULL AFTER `department_id`');
PREPARE rc_requesting_unit_stmt FROM @rc_requesting_unit_ddl;
EXECUTE rc_requesting_unit_stmt;
DEALLOCATE PREPARE rc_requesting_unit_stmt;

SET @rc_requesting_unit_index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND INDEX_NAME = 'idx_tickets_requesting_unit');
SET @rc_requesting_unit_index_ddl = IF(@rc_requesting_unit_index_exists > 0, 'SELECT 1', 'CREATE INDEX `idx_tickets_requesting_unit` ON `tickets` (`requesting_unit_id`)');
PREPARE rc_requesting_unit_index_stmt FROM @rc_requesting_unit_index_ddl;
EXECUTE rc_requesting_unit_index_stmt;
DEALLOCATE PREPARE rc_requesting_unit_index_stmt;

INSERT INTO categories (name, code, service_group) VALUES
('سخت‌افزار', 'IT-CAT-HW', 'it'),
('نرم‌افزار', 'IT-CAT-SW', 'it'),
('شبکه و اینترنت', 'IT-CAT-NET', 'it'),
('حساب کاربری و دسترسی', 'IT-CAT-ACC', 'it'),
('چاپ و اسکن', 'IT-CAT-PRINT', 'it'),
('تجهیزات جانبی', 'IT-CAT-PERIPH', 'it'),
('پشتیبان‌گیری و بازیابی', 'IT-CAT-BACKUP', 'it'),
('نصب و راه‌اندازی', 'IT-CAT-SETUP', 'it'),
('سایر خدمات فناوری اطلاعات', 'IT-CAT-MISC', 'it'),
('تجهیزات و فضای اداری', 'SUP-CAT-OFFICE', 'support'),
('تأسیسات و انرژی', 'SUP-CAT-UTIL', 'support'),
('اثاثیه', 'SUP-CAT-FURN', 'support'),
('خدمات و نظافت', 'SUP-CAT-CLEAN', 'support'),
('ملزومات اداری', 'SUP-CAT-STAT', 'support'),
('خدمات عمومی', 'SUP-CAT-GEN', 'support'),
('سایر خدمات پشتیبانی', 'SUP-CAT-MISC', 'support')
ON DUPLICATE KEY UPDATE name = VALUES(name), service_group = VALUES(service_group), is_active = 1;

-- Restore the category relation when existing service data has no orphaned category IDs.
-- If legacy data contains orphan references, preserve those rows and leave the FK absent;
-- the migration verification reports that data condition instead of deleting it.
SET @rc_service_category_fk_exists = (
    SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_catalog'
      AND COLUMN_NAME = 'category_id' AND REFERENCED_TABLE_NAME = 'categories'
);
SET @rc_service_category_orphans = (
    SELECT COUNT(*) FROM service_catalog s
    LEFT JOIN categories c ON c.id = s.category_id
    WHERE s.category_id IS NOT NULL AND c.id IS NULL
);
SET @rc_service_category_fk_ddl = IF(
    @rc_service_category_fk_exists > 0 OR @rc_service_category_orphans > 0,
    'SELECT 1',
    'ALTER TABLE `service_catalog` ADD CONSTRAINT `fk_services_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL'
);
PREPARE rc_service_category_fk_stmt FROM @rc_service_category_fk_ddl;
EXECUTE rc_service_category_fk_stmt;
DEALLOCATE PREPARE rc_service_category_fk_stmt;

UPDATE service_catalog s
JOIN categories c ON c.code = CASE s.code
    WHEN 'IT-COMPUTER' THEN 'IT-CAT-HW'
    WHEN 'IT-BOOT' THEN 'IT-CAT-HW'
    WHEN 'IT-BLUE' THEN 'IT-CAT-HW'
    WHEN 'IT-HARDWARE' THEN 'IT-CAT-HW'
    WHEN 'IT-PROFILE' THEN 'IT-CAT-SETUP'
    WHEN 'IT-DRIVER' THEN 'IT-CAT-SETUP'
    WHEN 'IT-INSTALL-PC' THEN 'IT-CAT-SETUP'
    WHEN 'IT-INVENTORY' THEN 'IT-CAT-SETUP'
    WHEN 'IT-SOFTWARE' THEN 'IT-CAT-SW'
    WHEN 'IT-SOFT-ERROR' THEN 'IT-CAT-SW'
    WHEN 'IT-PRINTER' THEN 'IT-CAT-PRINT'
    WHEN 'IT-PERIPHERAL' THEN 'IT-CAT-PERIPH'
    WHEN 'IT-MOVE' THEN 'IT-CAT-PERIPH'
    WHEN 'IT-NET' THEN 'IT-CAT-NET'
    WHEN 'IT-SHARE' THEN 'IT-CAT-NET'
    WHEN 'IT-ACCOUNT' THEN 'IT-CAT-ACC'
    WHEN 'IT-ACCESS' THEN 'IT-CAT-ACC'
    WHEN 'IT-EMAIL' THEN 'IT-CAT-ACC'
    WHEN 'IT-BACKUP' THEN 'IT-CAT-BACKUP'
    WHEN 'IT-OTHER' THEN 'IT-CAT-MISC'
    WHEN 'SUP-OFFICE' THEN 'SUP-CAT-OFFICE'
    WHEN 'SUP-ELEC' THEN 'SUP-CAT-UTIL'
    WHEN 'SUP-COOLING' THEN 'SUP-CAT-UTIL'
    WHEN 'SUP-FURN' THEN 'SUP-CAT-FURN'
    WHEN 'SUP-CLEAN' THEN 'SUP-CAT-CLEAN'
    WHEN 'SUP-STATIONERY' THEN 'SUP-CAT-STAT'
    WHEN 'SUP-CDDVD' THEN 'SUP-CAT-GEN'
    WHEN 'SUP-GENERAL' THEN 'SUP-CAT-GEN'
    WHEN 'SUP-OTHER' THEN 'SUP-CAT-MISC'
    ELSE NULL
END
SET s.category_id = c.id
WHERE s.category_id IS NULL OR s.category_id = 0;
