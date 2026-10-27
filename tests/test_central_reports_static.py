from __future__ import annotations

import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class CentralReportsStaticTests(unittest.TestCase):
    def test_report_hub_keeps_request_report_and_exposes_food_reports_as_tabs(self) -> None:
        index = read("index.php")
        for label in [
            "گزارش درخواست‌ها",
            "گزارش‌گیری غذا",
            "آمار سفارش‌ها",
            "سفارش‌های روز",
            "فیش‌های چاپ‌شده و غذای تحویل‌شده",
            "گزارش تجمعی",
        ]:
            self.assertIn(label, index)
        self.assertIn("report-hub-frame", index)
        self.assertIn("report_tab", index)
        self.assertIn("SUBSTRING_INDEX(TRIM(r.full_name)", index)
        self.assertIn("r.id, t.id DESC", index)
        self.assertIn("report_view' => 'food-attendance'", index)

    def test_legacy_food_report_view_query_preempts_saved_view_and_hides_inner_tabs(self) -> None:
        panel = read("food-ticket-web/index.html")
        self.assertIn("const embeddedFoodViews={'food-attendance':'reports',orders:'food-menu',statistics:'food-menu',delivered:'food-menu',aggregate:'food-menu'}", panel)
        self.assertIn("embeddedReportMode?embeddedFoodViews[requestedReportView]", panel)
        self.assertIn("embeddedReportMode?'':'<nav class=\"tabs\"", panel)
        self.assertIn("html.food-report-embed .topbar{display:none}", panel)

    def test_daily_reconciliation_uses_real_absence_report_and_surfaces_difference(self) -> None:
        order = read("food-order.php")
        start = order.index("function food_order_daily_reconciliation_report(")
        end = order.index("function food_order_printed_delivery_report(", start)
        report = order[start:end]
        self.assertIn("food_ticket_api_absent($from, $to)", report)
        self.assertIn("event_type IN ('printed', 'print') AND print_status = 'printed'", report)
        self.assertIn("event_type = 'guest' AND print_status = 'printed'", report)
        self.assertIn("$totalOrders - ($guestTickets + $printedPeople)", report)
        self.assertIn("'equation_matches' => $absentees === $calculatedAbsentees", report)
        ui = read("food-ticket-web/assets/food-menu.js")
        self.assertIn("state.tab === 'aggregate' && canClose ? renderDailyAggregateReport()", ui)
        self.assertIn("گزارش تجمعی غذا", ui)
        self.assertIn("hasMismatch ? 'error' : 'good'", ui)
        self.assertIn("$route === 'food-menu/daily-reconciliation'", order)

    def test_all_person_name_reports_and_exports_use_surname_order(self) -> None:
        order = read("food-order.php")
        source = read("food-ticket-order-source.php")
        ticket = read("food-ticket.php")
        index = read("index.php")
        self.assertIn("ORDER BY COALESCE(NULLIF(u.last_name, ''), SUBSTRING_INDEX(TRIM(u.full_name), ' ', -1))", order)
        self.assertIn("ORDER BY o.food_date, COALESCE(NULLIF(u.last_name, ''), SUBSTRING_INDEX(TRIM(u.full_name), ' ', -1))", source)
        self.assertIn("ORDER BY user_last_name, user_first_name, e.id DESC", ticket)
        self.assertIn("SUBSTRING_INDEX(TRIM(r.full_name)", index)
        self.assertIn("r.id, t.id DESC", index)
        panel = read("food-ticket-web/index.html")
        self.assertIn("function sortReportRows(rows)", panel)
        self.assertIn("rows:sortReportRows(rows)", panel)


if __name__ == "__main__":
    unittest.main()
