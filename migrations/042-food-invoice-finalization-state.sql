-- 042 — فقط فاکتوری که از گردش قیمت‌گذاری جدید نهایی شده باشد ماه را قفل می‌کند.
-- فاکتورهای قدیمی نسخهٔ قبل، به‌طور پیش‌فرض نهایی محسوب نمی‌شوند.
ALTER TABLE food_monthly_invoices ADD COLUMN IF NOT EXISTS finalized_at DATETIME NULL;
ALTER TABLE food_monthly_invoices ADD INDEX idx_food_monthly_invoice_finalized (invoice_month, finalized_at);
