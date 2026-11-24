from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_041_migration_contains_month_prices_invoice_amount_and_allocation_history():
    migration = (ROOT / "migrations/041-food-monthly-prices-and-leftover-allocations.sql").read_text()
    assert "food_monthly_prices" in migration
    assert "price_irr" in migration
    assert "amount_irr" in migration
    assert "food_leftover_allocations" in migration
    assert "is_current" in migration
    assert "food_reconciliation_change_audit" in migration


def test_invoice_finalization_requires_prices_amount_and_file_and_is_transactional():
    php = (ROOT / "food-reconciliation.php").read_text()
    assert "prices_json" in php
    assert "amount_irr" in php
    assert "move_uploaded_file" in php
    assert "beginTransaction()" in php
    assert "food_monthly_invoice_finalized" in php
    assert "SELECT id,invoice_month,invoice_date,supplier_name,invoice_number,portion_count" in php
    assert "FROM food_monthly_invoices WHERE invoice_month=? ORDER BY id LIMIT 2 FOR UPDATE" in php
    assert "ON DUPLICATE KEY UPDATE price_irr=VALUES(price_irr)" in php
    assert "oldPriceAudit" in php
    assert "invoice_date,supplier_name,invoice_number,portion_count" in php


def test_closed_day_guard_only_locks_after_month_invoice():
    php = (ROOT / "food-reconciliation.php").read_text()
    guard = php[php.index("function food_meal_closed_guard("):php.index("function food_reconciliation_day_remainder(")]
    assert "food_monthly_invoices WHERE invoice_month = ? AND finalized_at IS NOT NULL" in guard
    assert "food_meal_reconciliations WHERE food_date" not in guard
    assert "فاکتور ماه نهایی شده" in guard


def test_monthly_price_modal_and_individual_leftover_allocations_are_present():
    js = (ROOT / "assets/food-menu.js").read_text()
    assert "renderInvoicePriceModal" in js
    assert "prices_json" in js
    assert "data-leftover-person-id" in js
    assert "leftover_allocations" in js
    assert "invoice_finalized" in js
    assert "data-leftover-staff-total" in js
    assert "checked?asciiDigits(selected[String(id)]):'1'" in js
    assert "leftoverSelectedTotal" in js
    assert "maxlength=\"12\"" in js
    assert "fm-price-currency" in js
    assert "قیمت‌های غذا در ماه" in js
    assert "دریافت‌کنندگان:" in js


def test_reconciliation_xlsx_exports_invoice_amount_and_individual_allocation():
    php = (ROOT / "food-reconciliation.php").read_text()
    assert "مبلغ کل فاکتور ماه (ریال)" in php
    assert "تخصیص فردی غذای باقیمانده" in php
    assert "leftover_allocations" in php


def test_absence_response_has_price_amount_and_independent_allocation_table():
    php = (ROOT / "food-ticket.php").read_text()
    assert "price_irr" in php
    assert "amount_irr" in php
    assert "food_leftover_allocations" not in php


def test_fresh_schema_marks_041_and_has_irr_column():
    for name in ("schema.sql", "install-empty-db.sql"):
        sql = (ROOT / name).read_text()
        assert "('1.63.4', '041-food-monthly-prices-and-leftover-allocations.sql')" in sql
        assert "('1.63.4', '042-food-invoice-finalization-state.sql')" in sql
        assert "amount_irr BIGINT UNSIGNED" in sql
        assert "CREATE TABLE IF NOT EXISTS food_monthly_prices" in sql


def test_leftover_employee_list_is_lazy_and_submission_keeps_quantities():
    js = (ROOT / "assets/food-menu.js").read_text()
    start = js.index("function renderLeftoverModal()")
    end = js.index("function leftoverInlineError(", start)
    modal = js[start:end]
    assert "peopleLoading: false" not in modal  # initial state is set only when the dialog opens
    assert "data-leftover-person-step" not in modal
    assert "function loadLeftoverPeople()" in js
    assert "if(input.checked&&!lm.peopleLoaded&&!lm.peopleLoading)" in js
    assert "saveLeftoverDraftFromDom" in js
    assert "var allocations=allocationsDraft" in js
    assert "leftover_allocations: allocations" in js


def test_food_modals_use_top_layer_popovers_for_all_three_dialogs():
    js = (ROOT / "assets/food-menu.js").read_text()
    assert js.count('popover="manual" data-fm-action=') >= 3
    assert "function showFoodModals()" in js
    assert "modal.showPopover()" in js
    assert "showFoodModals();" in js
    css = (ROOT / "assets/food-menu.css").read_text()
    assert ".fm-leftover-backdrop{position:fixed" in css
    assert "z-index:2147483000" in css
