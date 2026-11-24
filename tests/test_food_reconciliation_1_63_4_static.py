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


def test_leftover_person_rows_show_full_names_only_with_aligned_checkboxes():
    js = (ROOT / "assets/food-menu.js").read_text()
    start = js.index("m.people.map(function(person)")
    end = js.index(".join('');", start)
    row = js[start:end]
    assert "esc(person.name||'')" in row
    assert "employee_number" not in row
    css = (ROOT / "assets/food-menu.css").read_text()
    assert "grid-template-columns:20px max-content 76px" in css
    assert "overflow:visible;text-overflow:clip" in css


def test_leftover_popup_remains_compact_and_quantity_fields_align_left():
    js = (ROOT / "assets/food-menu.js").read_text()
    assert 'class="fm-leftover-backdrop"' in js
    assert 'fm-leftover-assignment-modal' not in js
    css = (ROOT / "assets/food-menu.css").read_text()
    assert ".fm-leftover-modal{width:min(520px,100%);max-height:92vh" in css
    assert ".fm-leftover-row:not(.fm-leftover-staff) .fm-leftover-count" in css


def test_reconciliation_panel_preserves_page_height_and_uses_full_width():
    js = (ROOT / "assets/food-menu.js").read_text()
    css = (ROOT / "assets/food-menu.css").read_text()
    assert "reconciliation-fullscreen" not in js
    assert "نمایش تمام‌صفحه" not in js and "بازگشت به نمای عادی" not in js
    assert ".fm-embedded-reconciliation .fm-reconciliation-report{width:calc(100vw - 40px)" in css
    assert ".fm-embedded-reconciliation.is-fullscreen" not in css
    assert ".fm-embedded-reconciliation .fm-reconciliation-table{width:100%;min-width:0;table-layout:fixed}" in css
    assert "overflow-x:hidden" in css


def test_reconciliation_invoice_content_is_separated_into_invoice_tab():
    js = (ROOT / "assets/food-menu.js").read_text()
    assert "data-tab=\"invoice\">مبلغ و فاکتور" in js
    assert "fm-reconciliation-invoice-filter" in js
    assert "var invoiceContent =" in js
    assert "invoiceTab ? invoiceContent : dailyContent" in js
    assert "فاکتورهای ثبت‌شدهٔ ماه‌های قبل و ماه جاری" in js
    php = (ROOT / "food-reconciliation.php").read_text()
    assert "function food_reconciliation_invoice_rows(?string $monthStart = null)" in php
    assert "'invoice_history' => $invoiceHistory" in php


def test_invoice_upload_form_follows_month_summary_and_pdf_images_render_inline():
    js = (ROOT / "assets/food-menu.js").read_text()
    invoice_content = js[js.index("var invoiceContent ="):js.index("return '<section class=\"fm-card fm-reconciliation-report")]
    assert invoice_content.index("invoiceSummary + invoiceForm") < invoice_content.index("monthlyPriceTable")
    php = (ROOT / "food-reconciliation.php").read_text()
    assert "'pdf' => 'application/pdf'" in php
    assert "'jpg' => 'image/jpeg'" in php and "'png' => 'image/png'" in php
    assert "($inline ? 'inline' : 'attachment')" in php
    assert "Content-Disposition: attachment; filename=\"food-invoice-'" not in php


def test_grouped_absence_tables_are_sortable_without_breaking_rows():
    html = (ROOT / "food-ticket-web/index.html").read_text()
    assert "tableHtml(gs[dep],S.blocks,true)" in html
    assert "return sorted(order);" in html
    assert "if(S.sort.key==='dep')" in html
    assert "else if(key==='jal'||key==='row'){x=a.iso;y=b.iso}" in html
