-- 1.62.52: دو شرکت با درخت مستقل؛ مهاجرت افزایشی برای DB موجود.
-- این فایل فقط از System Update اجرا شود؛ هیچ DDL در زمان بارگذاری صفحه اضافه نشده است.
CREATE TABLE IF NOT EXISTS org_companies (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(190) NOT NULL,
    company_type VARCHAR(20) NOT NULL DEFAULT 'partner',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_org_companies_active (is_active, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO org_companies (id, code, name, company_type, is_active) VALUES
    (1, 'own', 'شرکت شما', 'own', 1),
    (2, 'partner', 'شرکت همکار', 'partner', 1);

ALTER TABLE org_units ADD COLUMN IF NOT EXISTS company_id INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE users ADD COLUMN IF NOT EXISTS company_id INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE org_units ADD INDEX IF NOT EXISTS idx_org_units_company (company_id, is_active, unit_type);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_company (company_id, is_active, auth_source);

-- داده‌های فعلی به شرکت اصلی تعلق دارند؛ شناسه‌ها و ارجاع‌های موجود دست‌نخورده می‌مانند.
UPDATE org_units SET company_id = 1 WHERE company_id IS NULL OR company_id = 0;
UPDATE users SET company_id = 1 WHERE company_id IS NULL OR company_id = 0;

-- شرکت اصلی ریشهٔ مدیرعامل خود را نگه می‌دارد؛ ریشهٔ شرکت همکار «مدیر شرکت» است.
INSERT INTO org_units (company_id, name, code, parent_id, unit_type, manager_user_id, is_primary_admin, is_active)
SELECT 1, 'مدیرعامل', 'CEO', NULL, 'ceo', NULL, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM org_units WHERE company_id = 1 AND unit_type = 'ceo');

INSERT INTO org_units (company_id, name, code, parent_id, unit_type, manager_user_id, is_primary_admin, is_active)
SELECT 2, 'مدیر شرکت همکار', 'PARTNER', NULL, 'company_manager', NULL, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM org_units WHERE company_id = 2 AND unit_type = 'company_manager');
