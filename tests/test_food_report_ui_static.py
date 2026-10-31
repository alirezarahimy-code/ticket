from __future__ import annotations

import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class FoodReportUiStaticTests(unittest.TestCase):
    def test_all_groups_are_available_and_group_report_supports_menu_view(self) -> None:
        ui = read("food-ticket-web/assets/food-menu.js")
        backend = read("food-order.php")
        self.assertIn("value=\"all\"", ui)
        self.assertIn("همهٔ گروه‌ها", ui)
        self.assertIn("group-orders-view", ui)
        self.assertIn("orderByMenuHtml(rows || [])", ui)
        self.assertIn("$groupRaw === 'all' ? 0", backend)
        self.assertIn("'groups' => $groupResults", backend)
        self.assertIn("'items' => $allItems", backend)

    def test_daily_order_field_choices_persist_per_user_in_local_storage(self) -> None:
        ui = read("food-ticket-web/assets/food-menu.js")
        ticket = read("food-ticket.php")
        self.assertIn("itsm-food-order-report-fields-v1:", ui)
        self.assertIn("global.localStorage.getItem(orderFieldsStorageKey)", ui)
        self.assertIn("global.localStorage.setItem(orderFieldsStorageKey", ui)
        self.assertIn("saveOrderReportFields(fields)", ui)
        self.assertIn("window.FOOD_TICKET_USER_ID=", ticket)

    def test_food_report_prints_clone_only_result_lists(self) -> None:
        ui = read("food-ticket-web/assets/food-menu.js")
        css = read("food-ticket-web/assets/food-menu.css")
        self.assertIn("printList.classList.add('fm-print-list')", ui)
        self.assertIn(".fm-daily-orders .fm-table-wrap', 'fm-print-orders'", ui)
        self.assertIn(".fm-group-orders .fm-group-report-content", ui)
        self.assertIn(".fm-statistics .fm-statistics-results", ui)
        self.assertIn(".fm-print-list", css)
        self.assertIn("body.fm-print-statistics > *:not(.fm-print-host)", css)

    def test_multicolumn_reports_have_dividers_and_larger_type(self) -> None:
        css = read("food-ticket-web/assets/food-menu.css")
        theme = read("food-ticket-web/assets/food-ticket-theme.css")
        html = read("food-ticket-web/index.html")
        self.assertIn(".fm-statistics-people-column + .fm-statistics-people-column { border-right:1px solid #9aaeb2; }", css)
        self.assertIn(".fm-statistics-days--single", css)
        self.assertIn(".fm-statistics-days--multi { grid-template-columns:repeat(2,minmax(0,1fr)); }", css)
        self.assertIn(".fm-statistics-results--food.fm-statistics-days--single .fm-stat-day ul", css)
        self.assertIn(".fm-stat-day ul { display:flex; flex-wrap:nowrap;", css)
        self.assertIn(".fm-stat-day { min-width:0; border:1px solid var(--line); border-radius:10px;", css)
        self.assertIn(".fm-stat-day li + li { border-right:1px solid #9aaeb2; }", css)
        self.assertIn("body.fm-print-statistics > .fm-print-host .fm-stat-day { overflow:visible; }", css)
        self.assertIn("body.fm-print-statistics > .fm-print-host .fm-stat-day ul { display:flex; flex-wrap:nowrap; overflow:visible; }", css)
        self.assertIn("font-size:13px", css)
        self.assertIn("font-size:13px}", html)
        self.assertIn("font-size: 12pt !important", theme)
        self.assertNotIn("size:landscape", css)
        self.assertNotIn("page:fmStatistics", css)

    def test_employee_statistics_serials_run_down_each_column_and_fit_content(self) -> None:
        ui = read("food-ticket-web/assets/food-menu.js")
        css = read("food-ticket-web/assets/food-menu.css")
        self.assertIn("var peopleColumnCount = people.length ? Math.min(3, Math.max(1, Math.floor(people.length / 3))) : 0;", ui)
        self.assertIn("var peoplePerColumn = peopleColumnCount ? Math.floor(people.length / peopleColumnCount) : 0;", ui)
        self.assertIn("var extraPeopleRows = peopleColumnCount ? people.length % peopleColumnCount : 0;", ui)
        self.assertIn("var columnSize = peoplePerColumn + (group < extraPeopleRows ? 1 : 0);", ui)
        self.assertIn("people.slice(start, start + columnSize)", ui)
        self.assertIn("peopleOffset += columnSize;", ui)
        self.assertNotIn("line * 3 + group", ui)
        self.assertIn("min-width:42px", css)
        self.assertIn("min-width:58px", css)
        self.assertIn("table-layout:auto", css)
        self.assertIn(".fm-statistics-people-column + .fm-statistics-people-column { border-right:1px solid #9aaeb2; }", css)
        self.assertNotIn(".fm-daily-orders .fm-rows th:not(:last-child)", css)

    def test_food_statistics_layout_depends_on_the_selected_date_range(self) -> None:
        ui = read("food-ticket-web/assets/food-menu.js")
        css = read("food-ticket-web/assets/food-menu.css")
        self.assertIn("var singleDayRange = !!state.statisticsFrom && state.statisticsFrom === state.statisticsTo;", ui)
        self.assertIn("fm-statistics-days--single", ui)
        self.assertIn("fm-statistics-days--multi", ui)
        self.assertIn("flex-wrap:nowrap", css)
        self.assertIn(".fm-statistics-days--multi { grid-template-columns:repeat(2,minmax(0,1fr)); }", css)
        self.assertNotIn("size:landscape", css)
        self.assertNotIn("page:fmStatistics", css)

    def test_repeated_report_blocks_keep_only_their_boundary_dividers(self) -> None:
        ui = read("food-ticket-web/assets/food-menu.js")
        css = read("food-ticket-web/assets/food-menu.css")
        self.assertIn("var blockStart = b > 0 && colIndex === 0", ui)
        self.assertIn("var blockStart = group > 0 && colIndex === 0", ui)
        self.assertIn(".fm-order-report-table .fm-order-block-start { border-right:2px solid #9aaeb2; }", css)
        self.assertIn(".fm-statistics-people-column + .fm-statistics-people-column { border-right:1px solid #9aaeb2; }", css)

    def test_settings_code_link_is_modal_not_an_inline_second_form(self) -> None:
        index = read("index.php")
        script = read("assets/app.js")
        css = read("assets/style.css")
        self.assertIn("data-domain-code-dialog-open", index)
        self.assertIn("<dialog class=\"domain-code-link-dialog\"", index)
        self.assertIn("class=\"domain-code-link-dialog-form\"", index)
        self.assertNotIn("class=\"role-form domain-code-link-form\"", index)
        self.assertIn("data-domain-code-dialog-open", script)
        self.assertIn("dialog.showModal()", script)
        self.assertIn(".domain-code-link-dialog::backdrop", css)
        self.assertIn("assets/app.js?v=3.4.28", index)


if __name__ == "__main__":
    unittest.main()
