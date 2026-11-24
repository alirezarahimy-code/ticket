-- 041 — قیمت قطعی ماهانه، تخصیص مستقل مانده و مبلغ فاکتور به ریال.
ALTER TABLE food_monthly_invoices ADD COLUMN IF NOT EXISTS amount_irr BIGINT UNSIGNED NULL;
UPDATE food_monthly_invoices SET amount_irr = amount_toman * 10 WHERE amount_irr IS NULL AND amount_toman IS NOT NULL;

CREATE TABLE IF NOT EXISTS food_monthly_prices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    invoice_month DATE NOT NULL,
    food_id INT UNSIGNED NOT NULL,
    price_irr BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_food_month_price (invoice_month, food_id),
    INDEX idx_food_month_price_food_month (food_id, invoice_month),
    CONSTRAINT fk_food_month_price_food FOREIGN KEY (food_id) REFERENCES food_catalog(id) ON DELETE RESTRICT,
    CONSTRAINT fk_food_month_price_invoice FOREIGN KEY (invoice_id) REFERENCES food_monthly_invoices(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS food_leftover_allocations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    reconciliation_id BIGINT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_food_leftover_reconciliation (reconciliation_id),
    INDEX idx_food_leftover_employee (employee_id, created_at),
    CONSTRAINT fk_food_leftover_reconciliation FOREIGN KEY (reconciliation_id) REFERENCES food_meal_reconciliations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS food_reconciliation_change_audit (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    reconciliation_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(80) NOT NULL,
    changed_by INT UNSIGNED NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reason VARCHAR(500) NULL,
    old_value LONGTEXT NULL,
    new_value LONGTEXT NULL,
    INDEX idx_food_recon_audit_target (reconciliation_id, changed_at),
    INDEX idx_food_recon_audit_user (changed_by, changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
