from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


class AbsentLunchDeputyScopeTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.php = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
        cls.permissions = (ROOT / "permissions.php").read_text(encoding="utf-8")
        cls.source = (ROOT / "food-ticket-order-source.php").read_text(encoding="utf-8")
        cls.html = (ROOT / "food-ticket-web/index.html").read_text(encoding="utf-8")

    def test_separate_permission_and_strict_active_deputy_appointment(self):
        self.assertIn("food.absent.own_deputy", self.permissions)
        scope = self.php[self.php.index("function food_ticket_absent_lunch_scope"):self.php.index("function food_ticket_can_view_absent_lunch")]
        self.assertIn("$user['id']", scope)
        self.assertIn("ou.is_active = 1 AND ou.unit_type = 'deputy'", scope)
        self.assertIn("ou.manager_user_id = ? OR oum.user_id IS NOT NULL", scope)
        self.assertIn("user_can($user, 'food.absent.own_deputy')", scope)
        self.assertNotIn("$_GET", scope)
        self.assertNotIn("$_POST", scope)
        self.assertIn("food_ticket_is_primary_admin($user) || food_ticket_is_support_manager($user)", scope)

    def test_scope_is_current_server_chart_and_sql_filters_active_employees(self):
        source = self.source[self.source.index("function food_order_internal_items"):]
        self.assertIn("?array $orgUnitIds = null", source)
        self.assertIn("JOIN org_units scope_unit ON scope_unit.id = u.org_unit_id AND scope_unit.is_active = 1", source)
        self.assertIn("AND u.is_active = 1 AND scope_unit.id IN (", source)
        absent = self.php[self.php.index("function food_ticket_api_absent"):self.php.index("\nfunction ", self.php.index("function food_ticket_api_absent") + 10)]
        self.assertIn("food_ticket_api_orders($fromDate, '', $toDate, $scopeUnitIds)", absent)
        self.assertIn("unset($row['org_unit_id'], $row['user_id'], $row['deputy_name']);", absent)
        self.assertIn("unset($row['nat']);", absent)
        self.assertIn("$scopeEventWhere = $scopeEventClauses ? ' AND (' . implode(' OR ', $scopeEventClauses)", absent)
        self.assertIn("AND 1 = 0", absent)
        self.assertIn("food_ticket_api_absent($from, $to, $rangeTotals, is_array($user) ? $user : [])", self.php)

    def test_general_reports_do_not_bypass_absent_access_and_aggregate_exports_hide_it(self):
        route = self.php[self.php.index("function food_ticket_route_allowed"):self.php.index("function require_food_ticket_access")]
        self.assertIn("$route === 'absent'", route)
        self.assertIn("food_ticket_can_view_absent_lunch($user)", route)
        aggregate = self.php[self.php.index("function food_ticket_api_aggregate_report"):self.php.index("function food_ticket_aggregate_columns")]
        self.assertIn("unset($result['counts']['absent']", aggregate)
        self.assertIn("unset($result['diagnostics']);", aggregate)
        self.assertIn("$isDeputyScoped", aggregate)
        self.assertIn("$printedPeople = [];", aggregate)
        self.assertIn("$scopedCounts['lunch_orders'] = (int) ($absentRangeTotals['total_ordered_portions'] ?? 0)", aggregate)
        export = self.php[self.php.index("function food_ticket_aggregate_export"):self.php.index("function food_ticket_aggregate_row")]
        self.assertIn("array_key_exists('absent_people', $report)", export)
        self.assertIn("food_ticket_api_aggregate_report($fromDate, $toDate, $user)", export)

    def test_only_absent_view_is_given_to_users_without_general_food_reports(self):
        render = self.php[self.php.index("function food_ticket_render_page"):]
        self.assertIn("'reports' => $fpCanGeneralReports || $fpCanAbsentLunch", render)
        self.assertIn("FOOD_TICKET_CAN_ABSENT_LUNCH_REPORT", render)
        self.assertIn("FOOD_TICKET_ABSENT_LUNCH_ONLY", render)
        self.assertIn("FOOD_TICKET_ABSENT_LUNCH_SERVER_SCOPE", render)
        self.assertIn("function visibleReportTabs()", self.html)
        self.assertIn("window.FOOD_TICKET_ABSENT_LUNCH_ONLY===true", self.html)
        self.assertIn("key!=='absent-lunch'||canAbsent", self.html)
        self.assertIn("window.FOOD_TICKET_ABSENT_LUNCH_SERVER_SCOPE!==true", self.html)

    def test_charts_exports_printing_use_server_scoped_rows_and_denominators(self):
        self.assertIn("'expected_absent' => max(0, $totalOrderPortions - $printedOrderPortions)", self.php)
        self.assertIn("serverDeputy=String(it.deputy_name||it.dep||'').trim()", self.html)
        self.assertIn("person_ref||('pc:'+(it.pc||''))", self.html)
        self.assertIn("canSeeAbsent?`<div class=\"section-gap\"><h3>غایبین ناهار", self.html)
        self.assertIn("window.FOOD_TICKET_CAN_ABSENT_LUNCH_REPORT===true?'<h3>غایبین ناهار</h3>'", self.html)
        self.assertIn("fetch('index.php?action=export_view_xlsx'", self.html)
        self.assertIn("function preparePrint()", self.html)
        self.assertIn("گزارش تاریخ‌های گذشته نیز با چارت فعلی طبقه‌بندی می‌شود", self.html)


if __name__ == "__main__":
    unittest.main()
