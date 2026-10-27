from __future__ import annotations

import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class PrintedDeliveryReportStaticTests(unittest.TestCase):
    def test_report_uses_printed_delivered_employee_events_and_printed_guest_events(self) -> None:
        backend = read("food-order.php")
        start = backend.index("function food_order_printed_delivery_report(")
        end = backend.index("function food_order_export_printed_delivery_report(", start)
        report = backend[start:end]
        self.assertIn("FROM food_ticket_events e", report)
        self.assertIn("food_ticket_manual_attendance_reconcile_range($from, $to)", report)
        self.assertIn("e.source_uid = 'manual-attendance' AND e.event_type = 'printed' AND e.delivery_status = 'delivered'", report)
        self.assertIn("e.punch_date BETWEEN ? AND ?", report)
        self.assertIn("e.print_status = 'printed'", report)
        self.assertIn("e.event_type = 'printed' AND e.user_id IS NOT NULL AND e.delivery_status = 'delivered'", report)
        self.assertIn("OR e.event_type = 'guest'", report)
        self.assertIn("JOIN food_calendar_items i ON i.calendar_id = cal.id", report)
        self.assertIn("'employee_count' => count($employeeIds)", report)
        self.assertIn("'guest_count' => $guestCount", report)
        export_start = backend.index("function food_order_export_printed_delivery_report(")
        export_end = backend.index("function food_order_ensure_orderable_day(", export_start)
        export = backend[export_start:export_end]
        self.assertIn("'employee_number' => (string) ($item['employee_number'] ?? '')", export)

    def test_report_api_is_restricted_to_food_order_close(self) -> None:
        ticket = read("food-ticket.php")
        order = read("food-order.php")
        self.assertIn("'food-menu/export', 'food-menu/printed-delivery', 'food-menu/printed-delivery-export', 'food-menu/cancel-requests'", ticket)
        self.assertIn("=> 'food.order_close'", ticket)
        self.assertIn("$route === 'food-menu/printed-delivery'", order)
        self.assertIn("$route === 'food-menu/printed-delivery-export'", order)
        self.assertIn("food_order_require_permission($user, 'food.order_close');", order)

    def test_report_is_appended_after_statistics_and_matches_daily_order_controls(self) -> None:
        ui = read("food-ticket-web/assets/food-menu.js")
        stats = ui[ui.index("function renderStatistics()") : ui.index("function render()", ui.index("function renderStatistics()"))]
        self.assertIn("+ renderPrintedDeliveryReport()", stats)
        self.assertIn("data-fm-delivery-field", ui)
        self.assertIn("itsm-food-printed-delivery-report-fields-v1", ui)
        self.assertIn("data-delivery-view=\"menu\"", ui)
        self.assertIn("fm-order-block-start", ui)
        self.assertIn("تأیید مستقل دریافت غذا نیست", ui)
        self.assertIn("food-menu/printed-delivery", ui)
        self.assertIn("delivered-report-export", ui)
        self.assertIn("delivered-report-print", ui)

    def test_mirrored_assets_and_cache_versions_are_updated(self) -> None:
        panel = read("food-ticket-web/index.html")
        self.assertIn('assets/food-menu.css?v=34', panel)
        self.assertIn('assets/food-menu.js?v=33', panel)
        self.assertEqual(read("assets/food-menu.js"), read("food-ticket-web/assets/food-menu.js"))
        self.assertEqual(read("assets/food-menu.css"), read("food-ticket-web/assets/food-menu.css"))


if __name__ == "__main__":
    unittest.main()
