"""Regression checks from the audit of the purchase/distribution report."""
from __future__ import annotations

from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]
PHP = (ROOT / "food-reconciliation.php").read_text(encoding="utf-8")
JS_PATHS = [ROOT / "assets/food-menu.js", ROOT / "food-ticket-web/assets/food-menu.js"]
JS = [path.read_text(encoding="utf-8") for path in JS_PATHS]


def section(start: str, end: str) -> str:
    return PHP[PHP.index(start) : PHP.index(end, PHP.index(start))]


class ReconciliationAuditTests(unittest.TestCase):
    def test_staff_distribution_uses_only_delivered_employee_receipt_types(self) -> None:
        served = section(
            "function food_reconciliation_served_counts(",
            "function food_reconciliation_absentee_counts(",
        )
        self.assertIn("event_type IN ('printed', 'print', 'auto_printed')", served)
        self.assertIn("source_uid = 'manual-attendance' AND event_type = 'printed'", served)
        self.assertNotIn("event_type NOT IN ('guest', 'auto_guest')", served)
        self.assertIn("event_type IN ('guest', 'auto_guest')", served)
        self.assertIn("AND print_status = 'printed'", served)

    def test_absentees_match_delivered_fish_to_the_same_active_order_identity(self) -> None:
        absent = section(
            "function food_reconciliation_absentee_counts(",
            "function food_reconciliation_disposition_summary(",
        )
        self.assertIn("LEFT JOIN food_ticket_events AS e", absent)
        self.assertIn("e.user_id = o.employee_id", absent)
        self.assertIn("e.punch_date = o.food_date", absent)
        self.assertIn("COALESCE(e.meal_type, 'lunch') = o.meal_type", absent)
        self.assertIn("COUNT(DISTINCT CASE WHEN e.id IS NOT NULL THEN o.employee_id END) AS served_active_people", absent)
        self.assertIn("o.status = 'active'", absent)

    def test_day_union_and_served_count_have_the_same_staff_receipt_gate(self) -> None:
        rows = section(
            "function food_reconciliation_rows(",
            "function food_reconciliation_invoice_rows(",
        )
        self.assertIn("event_type IN ('printed', 'print', 'auto_printed')", rows)
        self.assertIn("source_uid = 'manual-attendance' AND event_type = 'printed'", rows)
        self.assertNotIn("event_type NOT IN ('guest', 'auto_guest')", rows)

    def test_monthly_comparison_marks_ticket_only_distribution_as_missing(self) -> None:
        helper = section(
            "function food_reconciliation_received_missing(",
            "function food_reconciliation_report(",
        )
        self.assertIn("active_demand", helper)
        self.assertIn("served_total", helper)
        self.assertIn("$item['id']", helper)
        self.assertEqual(PHP.count("food_reconciliation_received_missing($item)"), 2)
        self.assertIn("$monthSummary['daily_received_missing_rows'] === 0", PHP)

    def test_current_demand_basis_is_explicit_in_ui_and_excel_title(self) -> None:
        for source in JS:
            self.assertIn("نه snapshot زمان ثبت سفارش پیمانکار", source)
        self.assertIn("مبنای تقاضا: وضعیت فعلی سفارش‌ها/درخواست‌ها", PHP)
        self.assertIn("$summary['supplier_order_quantity']", PHP)
        self.assertIn("$summary['received_quantity']", PHP)

    def test_table_and_excel_display_the_same_contractor_quantity(self) -> None:
        self.assertTrue(all("item.received_quantity != null ? item.received_quantity : item.supplier_order_quantity" in source for source in JS))
        self.assertIn("($item['received_quantity'] ?? $item['supplier_order_quantity']) ?? '—'", PHP)

    def test_guest_events_are_counted_per_portion_and_auto_generation_is_idempotent(self) -> None:
        self.assertIn("COUNT(*) AS served", section(
            "function food_reconciliation_served_counts(",
            "function food_reconciliation_absentee_counts(",
        ))
        auto = (ROOT / "food-ticket-auto-receipts.php").read_text(encoding="utf-8")
        self.assertIn("for ($guestIndex = 1; $guestIndex <= $guestCount; $guestIndex++)", auto)
        self.assertIn("'auto-guest-receipt|' . $date . '|' . (int) $request['id'] . '|' . $mealType . '|' . $guestIndex", auto)
        schema = (ROOT / "schema.sql").read_text(encoding="utf-8")
        self.assertIn("source_key CHAR(64) NOT NULL UNIQUE", schema)

    def test_report_ui_mirrors_are_identical(self) -> None:
        self.assertEqual(JS_PATHS[0].read_bytes(), JS_PATHS[1].read_bytes())


if __name__ == "__main__":
    unittest.main()
