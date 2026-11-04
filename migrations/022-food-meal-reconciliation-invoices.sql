-- تطبیق سفارش و توزیع غذای روزانه و بایگانی فاکتورهای ماهانه.
-- افزایشی است؛ سفارش‌ها، درخواست مهمان و سوابق غیبت موجود را تغییر یا حذف نمی‌کند.

CREATE TABLE IF NOT EXISTS food_meal_reconciliations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    food_date DATE NOT NULL,
    meal_type ENUM('lunch','dinner') NOT NULL DEFAULT 'lunch',
    employee_orders_at_order INT UNSIGNED NULL,
    guest_meals_at_order INT UNSIGNED NULL,
    supplier_order_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    received_quantity INT UNSIGNED NULL,
    employee_served_quantity INT UNSIGNED NULL,
    guest_served_quantity INT UNSIGNED NULL,
    waste_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('ordered','closed') NOT NULL DEFAULT 'ordered',
    note VARCHAR(1000) NULL,
    ordered_by INT UNSIGNED NULL,
    ordered_at DATETIME NULL,
    snapshot_at DATETIME NULL,
    closed_by INT UNSIGNED NULL,
    closed_at DATETIME NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_food_meal_reconciliation_date_meal (food_date, meal_type),
    INDEX idx_food_meal_reconciliation_status_date (status, food_date),
    INDEX idx_food_meal_reconciliation_month_meal (food_date, meal_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS food_monthly_invoices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    invoice_month DATE NOT NULL,
    invoice_date DATE NOT NULL,
    supplier_name VARCHAR(190) NOT NULL,
    invoice_number VARCHAR(100) NOT NULL,
    portion_count INT UNSIGNED NOT NULL,
    amount_toman BIGINT UNSIGNED NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(96) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    size_bytes INT UNSIGNED NOT NULL,
    sha256 CHAR(64) NOT NULL,
    uploaded_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_food_monthly_invoice_number (invoice_month, supplier_name, invoice_number),
    UNIQUE KEY uq_food_monthly_invoice_stored_name (stored_name),
    INDEX idx_food_monthly_invoice_month (invoice_month, invoice_date),
    INDEX idx_food_monthly_invoice_uploaded_by (uploaded_by, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
