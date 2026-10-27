from __future__ import annotations

import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class CentralReportsStaticTests(unittest.TestCase):
    def test_food_reports_live_as_tabs_inside_food_management_reports_view(self) -> None:
        panel = read("food-ticket-web/index.html")
        for label in [
            "گزارش‌گیری",
            "آمار سفارش‌ها",
            "سفارش‌های روز",
            "فیش‌های چاپ‌شده و غذای تحویل‌شده",
            "گزارش تجمعی",
        ]:
            self.assertIn(label, panel)
        self.assertIn("data-report-section", panel)
        self.assertIn("report-module-tabs", panel)
        self.assertIn("if(view==='reports'&&reportSection!=='attendance'", panel)
        self.assertIn("window.FOOD_TICKET_REPORT_TAB=reportSection", panel)
        self.assertIn("food-menu-root", panel)

    def test_food_report_navigation_stays_in_the_management_panel_without_iframes(self) -> None:
        index = read("index.php")
        module = read("assets/food-menu.js")
        panel = read("food-ticket-web/index.html")
        self.assertNotIn("report-hub-frame", index)
        self.assertNotIn("reportTabFrames", index)
        self.assertNotIn("report_view", panel)
        self.assertNotIn("page=reports", module)
        self.assertIn("data-report-section", panel)
        tab_code = panel[panel.index("const reportTabs="):panel.index("if(reportSection!=='attendance')", panel.index("const reportTabs="))]
        self.assertNotIn("href=", tab_code)

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
