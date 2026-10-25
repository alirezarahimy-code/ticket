from __future__ import annotations

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TABLES = {
    "food_catalog",
    "food_calendar",
    "food_calendar_items",
    "food_orders",
    "food_order_logs",
}


def read(relative: str) -> str:
    return (ROOT / relative).read_text(encoding="utf-8")


def strip_sql_comments(sql: str) -> str:
    return re.sub(r"--[^\n]*", "", sql)


def create_tables(sql: str) -> list[str]:
    return re.findall(
        r"\bCREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([A-Za-z0-9_]+)`?",
        strip_sql_comments(sql),
        flags=re.IGNORECASE,
    )


class FoodOrdersStaticTests(unittest.TestCase):
    def test_upgrade_creates_exactly_five_new_tables_only(self) -> None:
        sql = strip_sql_comments(read("upgrade-1.38-food-orders.sql"))
        self.assertEqual(set(create_tables(sql)), TABLES)
        self.assertEqual(len(create_tables(sql)), 5)
        statements = [part.strip().upper() for part in sql.split(";") if part.strip()]
        self.assertTrue(all(statement.startswith("CREATE TABLE ") for statement in statements))
        self.assertNotRegex(sql, r"\bcapacity\b|ظرفیت")

    def test_rollback_drops_only_the_five_module_tables(self) -> None:
        sql = strip_sql_comments(read("rollback-1.38-food-orders.sql"))
        drops = re.findall(r"\bDROP\s+TABLE\s+IF\s+EXISTS\s+`?([A-Za-z0-9_]+)`?", sql, re.I)
        self.assertEqual(drops, [
            "food_order_logs",
            "food_orders",
            "food_calendar_items",
            "food_calendar",
            "food_catalog",
        ])
        self.assertEqual(set(drops), TABLES)

    def test_fresh_install_schemas_include_same_five_tables(self) -> None:
        for filename in ("schema.sql", "install-empty-db.sql"):
            with self.subTest(filename=filename):
                found = [name for name in create_tables(read(filename)) if name in TABLES]
                self.assertEqual(set(found), TABLES)
                self.assertEqual(len(found), 5)

    def test_table_definitions_match_across_upgrade_and_fresh_install(self) -> None:
        pattern = re.compile(
            r"CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+"
            r"(food_catalog|food_calendar|food_calendar_items|food_orders|food_order_logs)"
            r"\s*\((.*?)\)\s*ENGINE=InnoDB[^;]*;",
            re.IGNORECASE | re.DOTALL,
        )
        normalized = {}
        for filename in ("upgrade-1.38-food-orders.sql", "schema.sql", "install-empty-db.sql"):
            sql = strip_sql_comments(read(filename))
            normalized[filename] = {
                name.lower(): re.sub(r"\s+", " ", body).strip().lower()
                for name, body in pattern.findall(sql)
            }
        # food_catalog was extended after 1.38 by the standalone 2026-10-13 migration.
        # Compare the effective upgraded shape, not the historical base migration by itself.
        category_migration = read("updates/2026-10-13-food-catalog/migrations/001-food-catalog-category.sql")
        self.assertIn("ADD COLUMN IF NOT EXISTS category VARCHAR(40) NULL AFTER food_name", category_migration)
        upgraded_food_catalog = normalized["upgrade-1.38-food-orders.sql"]["food_catalog"].replace(
            "food_name varchar(190) not null,",
            "food_name varchar(190) not null, category varchar(40) null,",
        )
        for table in TABLES:
            with self.subTest(table=table):
                baseline = upgraded_food_catalog if table == "food_catalog" else normalized["upgrade-1.38-food-orders.sql"][table]
                self.assertEqual(baseline, normalized["schema.sql"][table])
                self.assertEqual(baseline, normalized["install-empty-db.sql"][table])

    def test_runtime_schema_helper_is_read_only(self) -> None:
        php = read("food-order.php")
        start = php.index("function food_order_schema_ensure(")
        end = php.index("\nfunction ", start + 10)
        helper = php[start:end]
        self.assertIn("information_schema.TABLES", helper)
        self.assertNotIn("CREATE TABLE", helper)
        self.assertNotIn("ALTER TABLE", helper)

    def test_guest_requests_allow_today_only_through_a_configured_cutoff(self) -> None:
        order = read("food-order.php")
        self.assertIn("function food_guest_request_same_day_cutoff()", order)
        self.assertIn("setting('food_guest_request_same_day_cutoff', '09:30')", order)
        self.assertIn("$now->format('H:i') <= food_guest_request_same_day_cutoff()", order)
        self.assertIn("food_guest_request_date_error($date)", order)
        self.assertIn("'same_day_cutoff' => food_guest_request_same_day_cutoff()", order)
        self.assertIn("'same_day_open' => food_guest_request_same_day_is_open($now)", order)

        ticket = read("food-ticket.php")
        self.assertIn("'guestRequestSameDayCutoff' => food_guest_request_same_day_cutoff()", ticket)
        self.assertIn("save_setting('food_guest_request_same_day_cutoff', $guestRequestSameDayCutoff)", ticket)
        self.assertIn("'config' => 'food.db'", ticket)
        self.assertIn("return 'food.db_edit';", ticket)

        panel = read("food-ticket-web/index.html")
        self.assertIn('id="guest-request-cutoff"', panel)
        self.assertIn("guestRequestSameDayCutoff:state.guests.requestSameDayCutoff||'09:30'", panel)
        guest_form = read("assets/food-order-guest.js")
        self.assertIn("data.same_day_open !== false", guest_form)
        self.assertIn("تا پایان دقیقهٔ", guest_form)

        guide = read("help-guide.php")
        self.assertIn("تا پایان دقیقهٔ ۹:۳۰ صبح", guide)
        self.assertIn("ساعت ۱۲:۰۱", guide)
        chatbot = read("chatbot.php")
        self.assertIn("food_guest_request_deadline", chatbot)

    def test_worker_has_one_internal_order_map_and_no_access_fallback(self) -> None:
        engine = read("food-ticket-engine.php")
        start = engine.index("function food_ticket_order_map_lazy(")
        end = engine.index("\nfunction ", start + 10)
        lazy_map = engine[start:end]
        self.assertIn("food_order_internal_map($foodDate)", lazy_map)
        self.assertNotIn("food_ticket_odbc", lazy_map)
        self.assertNotIn("orders_path", engine)
        self.assertNotIn("ordersHolder", engine)
        self.assertIn("food_ticket_odbc($attendancePath", engine)
        groups = read("food-ticket-groups.php")
        self.assertIn("food_ticket_order_map_lazy(null, 'food_orders', $date)", groups)

    def test_legacy_access_order_fields_are_not_live_configuration(self) -> None:
        php = read("food-ticket.php")
        self.assertNotIn("orders_path", php)
        self.assertNotIn("orders_table", php)
        self.assertNotIn("ordersPath", php)
        self.assertNotIn("ordersTable", php)
        self.assertFalse((ROOT / "food-ticket-web" / "Program.cs").exists())
        self.assertIn("if ($kind !== 'attendance')", php)
        self.assertIn("نوع فایل پشتیبانی نمی‌شود.", php)
        self.assertNotIn("بارگذاری Access سفارش غذا غیرفعال است", php)
        worker = read("food-ticket-engine.php")
        self.assertNotIn("$config['orders_path']", worker)
        panel = read("food-ticket-web/index.html")
        self.assertNotIn("ordersPath", panel)
        self.assertNotIn("ordersPassword", panel)

    def test_legacy_order_mode_cannot_be_changed_through_the_api(self) -> None:
        php = read("food-order.php")
        self.assertIn("function food_order_mode(bool $reload = false)", php)
        self.assertIn("return 'INTERNAL-DB';", php)
        self.assertNotIn("function food_order_set_internal_mode(", php)
        self.assertNotIn("food-menu/mode", php)
        ticket = read("food-ticket.php")
        self.assertNotIn("food-menu/mode", ticket)

    def test_employee_search_and_order_visibility_are_scoped(self) -> None:
        php = read("food-order.php")
        search_start = php.index("function food_order_employee_search(")
        search_end = php.index("\nfunction ", search_start + 10)
        search = php[search_start:search_end]
        self.assertIn("SELECT id, full_name, first_name, last_name", search)
        self.assertNotIn("national_code' =>", search)
        self.assertNotIn("employee_number' =>", search)
        self.assertIn("food_order_verify_employee_national_code", php)
        self.assertIn("hash_equals($expected, $actual)", php)
        self.assertIn("WHERE o.employee_id = ?", php)
        self.assertNotIn("WHERE o.created_by = ?", php)
        self.assertNotIn("food_order_require_permission($user, 'foodorder.proxy')", search)
        self.assertNotIn("food_order_require_permission($user, 'foodorder.self')", search)

    def test_proxy_permission_is_explicit_not_a_support_manager_default(self) -> None:
        permissions = read("permissions.php")
        self.assertIn("['code' => 'foodorder.proxy'", permissions)
        start = permissions.index("'support_manager' => array_values")
        end = permissions.index("\n        'inspector'", start)
        self.assertNotIn("foodorder.proxy", permissions[start:end])
        self.assertIn("'foodorder.self'", permissions[permissions.index("$all ="):permissions.index("$common =")])

    def test_panel_and_employee_assets_are_present_and_locally_loaded(self) -> None:
        main_index = read("index.php")
        self.assertIn("index.php?page=food-order", main_index)
        self.assertNotIn("'food-order' => 'foodorder.self'", main_index)
        php = read("food-order.php")
        self.assertIn('href="assets/food-order.css?v=17"', php)
        self.assertIn('src="assets/food-order-calendar.js?v=1"', php)
        self.assertIn('src="assets/food-order.js?v=21"', php)
        employee_css = read("assets/food-order.css")
        self.assertIn(".fo-my-orders .table-head,.fo-my-orders .table-row", employee_css)
        self.assertIn("grid-template-columns:42px minmax(140px,1.35fr)", employee_css)
        self.assertIn(".fo-my-orders .ticket-table { width:100%; min-width:980px", employee_css)
        order_grid = re.search(r"\.fo-my-orders \.table-head,\.fo-my-orders \.table-row \{ grid-template-columns:([^;]+);", employee_css)
        self.assertIsNotNone(order_grid)
        self.assertEqual(order_grid.group(1).count("minmax(") + 1, 9)
        self.assertIn(".fo-my-orders .table-row > td:last-child .button { white-space:nowrap; }", employee_css)

        panel_html = read("food-ticket-web/index.html")
        self.assertIn('href="assets/food-order.css?v=13"', panel_html)
        self.assertIn('href="assets/food-menu.css?v=32"', panel_html)
        self.assertIn('src="assets/jalali-calendar.js?v=2"', panel_html)
        self.assertIn('src="assets/food-order-calendar.js?v=1"', panel_html)
        self.assertIn('src="assets/food-menu.js?v=31"', panel_html)
        self.assertIn("FoodMenuUI.mount()", panel_html)
        for path in (
            "assets/food-order.css",
            "assets/food-order-calendar.js",
            "assets/food-order.js",
            "assets/jalali-calendar.js",
            "assets/food-menu.css",
            "assets/food-menu.js",
            "food-ticket-web/assets/food-order-calendar.js",
            "food-ticket-web/assets/jalali-calendar.js",
            "food-ticket-web/assets/food-order.css",
            "food-ticket-web/assets/food-menu.css",
            "food-ticket-web/assets/food-menu.js",
        ):
            with self.subTest(path=path):
                self.assertTrue((ROOT / path).is_file())

    def test_daily_report_fields_and_repeated_compact_record_blocks(self) -> None:
        js = read("assets/food-menu.js")
        php = read("food-order.php")
        self.assertIn("data-fm-order-field", js)
        self.assertIn("columns.length <= 3 ? 3", js)
        self.assertIn("fm-order-report-table", js)
        self.assertIn("نام و نام خانوادگی", js)
        self.assertIn("array $selectedFields = []", php)
        self.assertIn("'fields' => $selectedFields", php)

    def test_food_group_daily_order_report_uses_dated_membership(self) -> None:
        js = read("assets/food-menu.js")
        php = read("food-order.php")
        ticket = read("food-ticket.php")
        self.assertIn("لیست سفارش گروه‌های غذا", js)
        self.assertIn("food-menu/group-orders", js)
        self.assertIn("food_ticket_group_members_for_date($groupId, $iso)", php)
        self.assertIn("food_order_manager_orders($iso, $mealType)", php)
        self.assertIn("'food-menu/group-orders', 'food-menu/groups' => 'food.order_close'", ticket)

    def test_food_statistics_are_grouped_by_day_with_jalali_weekday(self) -> None:
        js = read("assets/food-menu.js")
        php = read("food-order.php")
        self.assertIn("GROUP BY o.food_date, f.id, f.food_name", php)
        self.assertIn("'food_date_jalali' => food_order_jalali_label($date)", php)
        self.assertIn("'weekday' => $weekdays[$weekdayIndex]", php)
        self.assertIn("fm-statistics-days", js)
        self.assertIn("day.food_date_jalali", js)
        self.assertIn("$day['weekday'], $day['food_date_jalali']", php)


    def test_food_dashboard_prints_each_printed_event_once_and_has_asset_fallback(self) -> None:
        panel = read("food-ticket-web/index.html")
        self.assertIn("if(event.status==='printed')daily[day].printed++", panel)
        self.assertIn("if(kind!=='printed'&&daily[day][kind]!==undefined)daily[day][kind]++", panel)
        self.assertIn("if(window.FoodCapitalioDashboard){window.FoodCapitalioDashboard.render(container,payload);return}", panel)
        self.assertIn("نمای کامل داشبورد بارگذاری نشد", panel)
        self.assertIn("food-capitalio-dashboard-r42.js?v=1.62.2-dashboard-fix", panel)
        self.assertIn("food-capitalio-dashboard-r42.css?v=1.62.2-dashboard-fix", panel)

    def test_food_panel_theme_toggle_updates_global_mode(self) -> None:
        panel = read("food-ticket-web/index.html")
        self.assertIn('data-action="toggle-dashboard-theme"', panel)
        self.assertIn("document.documentElement.setAttribute('data-theme-mode',next)", panel)
        self.assertIn("document.body.setAttribute('data-theme-mode',next)", panel)
        self.assertIn("if(act==='toggle-dashboard-theme')", panel)
        self.assertIn('food-ticket-global-theme-r42.css?v=45', panel)


    def test_selected_staff_report_loads_a_checklist_and_places_dates_before_it(self) -> None:
        panel_js = read("food-ticket-web/assets/food-menu.js")
        order = read("food-order.php")
        start = panel_js.index("function renderStatistics()")
        end = panel_js.index("\n  function bulkOperationTitle", start)
        view = panel_js[start:end]
        self.assertIn("data-stat-employee-checkbox", view)
        rendered = view[view.index("return '<section class=\"fm-card fm-statistics\""): ]
        self.assertLess(rendered.index("fm-order-date"), rendered.index("+ picker +"))
        self.assertIn("searchSelectedReportEmployees('')", panel_js)
        self.assertIn("reportEmployeeResultsLoaded", panel_js)
        self.assertIn("LIMIT 500", order)
        self.assertIn("LIMIT 50", order)
        self.assertIn("data-stat-employee-checkbox", panel_js[panel_js.index("function eventChange("):])

    def test_global_dark_theme_covers_food_forms_and_schedule_surfaces(self) -> None:
        css = read("assets/theme-mode.css")
        panel_css = read("food-ticket-web/assets/food-ticket-global-theme-r42.css")
        self.assertIn(".fm-card", css)
        self.assertIn(".fo-day.has-order", css)
        self.assertIn(".fm-field select option", css)
        self.assertIn(".fm-card", panel_css)
        self.assertIn(".fo-day.is-holiday", panel_css)
        self.assertIn(".fo-modal .fo-form-field input", panel_css)

    def test_panel_assets_are_synchronized(self) -> None:
        for name in ("food-menu.js", "food-menu.css", "food-order.css", "food-order-calendar.js", "jalali-calendar.js"):
            self.assertEqual(read(f"assets/{name}"), read(f"food-ticket-web/assets/{name}"))

    def test_day_impact_confirmation_locks_and_reuses_the_confirmed_plan(self) -> None:
        js = read("assets/food-menu.js")
        self.assertIn("if (!canMenu || state.daySaving) return;", js)
        self.assertIn("body = Object.assign({}, state.pendingImpact);", js)
        self.assertIn("Boolean(state.daySaving || impactPending ||", js)
        self.assertIn("state.daySaving = false; render();", js)

    def test_selected_employee_orders_report_has_search_dates_and_excel_export(self) -> None:
        php = read("food-order.php")
        self.assertIn("function food_order_report_employee_search(string $query): array", php)
        self.assertIn("food-menu/employee-search", php)
        self.assertIn("food_order_require_permission($user, 'food.order_close')", php)
        self.assertIn("food_order_report_employee_search($query)", php)
        self.assertIn("function food_order_selected_orders_range(", php)
        self.assertIn("function food_order_export_selected_orders_range(", php)
        self.assertIn("food_order_audit('food_order_selected_staff_export'", php)
        self.assertIn("food_order_send_xlsx('سفارش-پرسنل-انتخابی-'", php)
        js = read("food-ticket-web/assets/food-menu.js")
        self.assertIn('value="selected"', js)
        self.assertIn("data-stat-employee-search", js)
        self.assertIn("statistics-add-employee", js)
        self.assertIn("statistics-remove-employee", js)
        self.assertIn("params.employee_ids", js)

    def test_emergency_order_window_is_under_system_settings_and_primary_admin_only(self) -> None:
        php = read("food-order.php")
        self.assertIn("function food_order_require_primary_admin(array $user): void", php)
        grant_start = php.index("function food_order_emergency_window_grant(")
        grant_end = php.index("\nfunction ", grant_start + 10)
        self.assertIn("food_order_require_primary_admin($user)", php[grant_start:grant_end])
        revoke_start = php.index("function food_order_emergency_window_revoke(")
        revoke_end = php.index("\nfunction ", revoke_start + 10)
        self.assertIn("food_order_require_primary_admin($user)", php[revoke_start:revoke_end])
        self.assertIn("food-menu/emergency-window-state", php)
        self.assertIn("food_order_emergency_window_state($calendarId)", php)

        ticket = read("food-ticket.php")
        allowed_start = ticket.index("function food_ticket_route_allowed(")
        allowed_end = ticket.index("\nfunction ", allowed_start + 10)
        allowed = ticket[allowed_start:allowed_end]
        self.assertIn("in_array($route, ['food-menu/emergency-window', 'food-menu/emergency-window-state'], true)", allowed)
        self.assertIn("return false;", allowed)
        self.assertIn("'emergency-window' => food_ticket_is_primary_admin($user)", ticket)

        panel = read("food-ticket-web/index.html")
        manual = panel.index("sections.push(['manual-attendance','ثبت دستی فیش','sub'])")
        emergency = panel.index("sections.push(['emergency-window','ثبت اضطراری سفارش غذا','sub'])")
        self.assertLess(manual, emergency)
        self.assertIn("if(isPrimaryAdmin()&&available('emergency-window'))", panel)
        self.assertIn("function emergencyWindowSettingsForm()", panel)
        self.assertIn('id="emergency-window-settings-form"', panel)
        self.assertIn("food-menu/emergency-window-state?date=", panel)

        js = read("food-ticket-web/assets/food-menu.js")
        self.assertNotIn("renderEmergencyWindowManager", js)
        self.assertNotIn("fm-emergency-window-form", js)
        self.assertNotIn("late-window-revoke", js)
        self.assertEqual(read("assets/food-menu.js"), js)

    def test_daily_orders_and_statistics_modes_are_separate_and_bounded(self) -> None:
        php = read("food-order.php")
        self.assertIn("function food_order_manager_orders(string $date, string $mealType = 'lunch'): array", php)
        self.assertIn("function food_order_export_manager_orders(string $date, array $user, string $mealType = 'lunch', array $selectedFields = []): never", php)
        self.assertIn("food-menu/orders-export", php)
        self.assertIn("WHERE o.food_date = ?", php)
        self.assertNotIn("food_order_manager_orders_range", php)
        self.assertIn("function food_order_statistics_range(string $fromDate, string $toDate, string $mode = 'food', string $mealType = 'lunch'): array", php)
        self.assertIn("GROUP BY o.food_date, f.id, f.food_name", php)
        self.assertIn("GROUP BY u.id, u.full_name, u.first_name, u.last_name", php)
        self.assertIn("'full_name' => food_order_full_name($row)", php)
        self.assertIn("food_order_statistics_range($fromIso, $toIso, $mode, $mealType)", php)
        self.assertIn("food_order_export_statistics_range", php)
        self.assertIn("['ردیف', 'نام پرسنل', 'تعداد سفارش فعال']", php)
        self.assertIn("['روز هفته', 'تاریخ شمسی', 'نوع غذا', 'تعداد سفارش فعال']", php)
        self.assertIn("function food_order_selected_orders_range(string $fromDate, string $toDate, array $employeeIds", php)
        self.assertIn("function food_order_export_selected_orders_range(", php)
        self.assertIn("$span > 366", php)
        self.assertIn("count($ids) > 100", php)
        self.assertIn("o.status = 'active'", php)
        self.assertIn("'employee_ids'", php)
        export_start = php.index("function food_order_export_statistics_range(")
        export_end = php.index("\nfunction ", export_start + 10)
        export = php[export_start:export_end]
        self.assertIn("$item['full_name']", export)
        self.assertIn("$food['food_name']", export)
        self.assertIn("$item['count']", export)
        self.assertNotIn("national_code", export)
        self.assertNotIn("employee_number", export)
        self.assertNotIn("food_order_export_excel_range", php)
        ticket = read("food-ticket.php")
        self.assertIn("'food-menu/orders', 'food-menu/orders-export', 'food-menu/statistics', 'food-menu/export', 'food-menu/cancel-requests', 'food-menu/cancel-request-decide' => 'food.order_close'", ticket)

        js = read("assets/food-menu.js")
        orders_start = js.index("function renderOrders()")
        statistics_start = js.index("function renderStatistics()", orders_start)
        daily_panel = js[orders_start:statistics_start]
        self.assertIn('name="date" data-jalali', daily_panel)
        self.assertNotIn('name="from"', daily_panel)
        self.assertNotIn('name="to"', daily_panel)
        self.assertIn("return daily + renderGroupOrders();", daily_panel)
        self.assertNotIn("renderStatistics()", daily_panel)
        statistics_panel = js[statistics_start:]
        self.assertIn('name="from" data-jalali', statistics_panel)
        self.assertIn('name="to" data-jalali', statistics_panel)
        self.assertIn('name="statistics_mode" value="food"', statistics_panel)
        self.assertIn('name="statistics_mode" value="employee"', statistics_panel)
        self.assertIn("['statistics', 'آمار سفارش‌ها']", js)
        self.assertIn("async function loadOrders(date)", js)
        self.assertIn("request('food-menu/orders', { date: selected, meal_type: state.mealType })", js)
        self.assertIn("data-fm-action=\"orders-export\"", daily_panel)
        self.assertIn("data-fm-action=\"orders-print\"", daily_panel)
        self.assertIn("function downloadOrders()", js)
        self.assertIn("downloadOrdersCsv();", js)
        self.assertIn("function printOrders()", js)
        self.assertIn("fm-print-orders", js)
        self.assertIn("request('food-menu/statistics', query)", js)
        self.assertIn("if (selectedMode === 'selected') query.employee_ids = selectedIds.join(',')", js)
        self.assertIn("form.id === 'fm-statistics-date'", js)
        self.assertIn("{ from: state.statisticsFrom, to: state.statisticsTo, mode: state.statisticsMode || 'food', meal_type: state.mealType }", js)
        self.assertIn("fm-statistics-results--people", statistics_panel)
        self.assertEqual(statistics_panel.count('<th>ردیف</th><th>نام و نام خانوادگی</th><th>تعداد</th>'), 1)
        self.assertIn("peopleColumns.push('<section class=\"fm-statistics-people-column\"", statistics_panel)
        self.assertIn("people.slice(start, start + columnSize)", statistics_panel)
        self.assertIn("global.ItsmJalali.enhanceAll(root)", js)

    def test_day_status_form_drops_reason_but_backend_still_checks_close_permission(self) -> None:
        js = read("assets/food-menu.js")
        editor_start = js.index("function renderDayEditor()")
        editor_end = js.index("\n  function renderCatalog()", editor_start)
        editor = js[editor_start:editor_end]
        self.assertIn('value=\"open\"', editor)
        self.assertIn('value=\"closed\"', editor)
        self.assertNotIn('name="reason"', editor)
        save_start = js.index("async function saveDay(")
        save_end = js.index("\n  async function saveCatalog", save_start)
        save = js[save_start:save_end]
        self.assertNotIn("formData.get('reason')", save)
        self.assertNotIn("برای بستن سفارش‌گیری، دلیل را وارد کنید", save)
        php = read("food-order.php")
        day_start = php.index("function food_order_save_day(")
        day_end = php.index("\nfunction ", day_start + 10)
        day_save = php[day_start:day_end]
        self.assertIn("if ($status === 'closed' || $status === 'open')", day_save)
        self.assertIn("food_order_user_can($user, 'food.order_close')", day_save)
        self.assertNotIn("$body['reason']", day_save)

    def test_proxy_calendar_reads_only_the_verified_recipient_and_marks_their_order(self) -> None:
        php = read("food-order.php")
        route_start = php.index("if ($method === 'POST' && $route === 'proxy-month')")
        route_end = php.index("if ($method === 'GET' && $route === 'my-orders')", route_start)
        proxy_route = php[route_start:route_end]
        self.assertNotIn("food_order_require_permission($user, 'foodorder.proxy')", proxy_route)
        self.assertIn("food_order_verify_employee_national_code", proxy_route)
        self.assertIn("food_order_month_status((string) ($body['month'] ?? ''), $target, false, false, food_order_meal_type($body['meal_type'] ?? 'lunch'))", proxy_route)
        js = read("assets/food-order.js")
        self.assertIn("request('proxy-month'", js)
        self.assertIn("request('cancel-proxy'", js)
        self.assertIn("async function cancelProxyOrder(orderId)", js)
        self.assertIn("normalizedDigits(state.nationalCode)", js)
        self.assertIn("var selectedOrder = day && day.my_order ? day.my_order : null", js)
        self.assertIn("if (selectedOrder) classes.push('has-order')", js)
        self.assertIn("fo-day-order-marker", js)
        modal_start = js.index("function renderModal()")
        modal_end = js.index("\n  function render()", modal_start)
        modal = js[modal_start:modal_end]
        self.assertIn("state.targetMode === 'proxy' && existingOrder", modal)
        self.assertIn("renderProxyCancelControl(existingOrder.id", modal)
        self.assertIn("&& !isPast(state.modalDate) && day && day.order_status === 'open')", modal)
        month_start = php.index("function food_order_month_status(")
        month_end = php.index("\nfunction ", month_start + 10)
        month = php[month_start:month_end]
        self.assertIn("WHERE o.employee_id = ? AND o.food_date BETWEEN ? AND ? AND o.meal_type = ? AND o.status = 'active'", month)

    def test_proxy_order_list_uses_only_the_verified_recipient_month(self) -> None:
        php = read("food-order.php")
        self.assertNotIn("function food_order_proxy_orders(", php)
        route_start = php.index("if ($method === 'POST' && $route === 'proxy-orders')")
        route_end = php.index("if ($method === 'GET' && $route === 'search-users')", route_start)
        route = php[route_start:route_end]
        self.assertIn("food_order_verify_employee_national_code(", route)
        self.assertIn("food_order_list_range((string) ($body['from'] ?? ''), (string) ($body['to'] ?? ''), (int) $target['id'])", route)
        self.assertIn("food_order_my_orders((int) $target['id'], 400, $range['from'], $range['to'])", route)

        list_start = php.index("function food_order_my_orders(")
        list_end = php.index("\nfunction ", list_start + 10)
        order_list = php[list_start:list_end]
        self.assertIn("WHERE o.employee_id = ?{$range}", order_list)
        self.assertIn("AND o.food_date BETWEEN ? AND ?", order_list)
        self.assertIn("ORDER BY o.food_date DESC, o.id DESC LIMIT {$limit}", order_list)

        js = read("assets/food-order.js")
        load_start = js.index("async function loadProxyOrders()")
        load_end = js.index("\n  async function loadEmployeeChoices", load_start)
        load = js[load_start:load_end]
        self.assertIn("if (code.length !== 10)", load)
        self.assertIn("request('proxy-orders'", load)
        self.assertIn("employee_id: state.targetId, national_code: code", load)
        self.assertIn("from: state.listFrom, to: state.listTo", load)
        self.assertIn("function renderProxyOrders()", js)
        proxy_start = js.index("function renderProxyOrders()")
        proxy_end = js.index("\n  function currentDay()", proxy_start)
        proxy_view = js[proxy_start:proxy_end]
        self.assertIn("state.proxyOrders", proxy_view)
        self.assertIn("renderProxyCancelControl(row.id, 'button secondary')", proxy_view)
        self.assertIn("<th>نام پرسنل</th>", proxy_view)
        self.assertNotIn("national_code", proxy_view)
        self.assertNotIn("employee_number", proxy_view)
        self.assertIn("state.targetName || '—'", proxy_view)
        self.assertIn("reserve_date: order.reserve_date || ''", proxy_view)
        self.assertIn("reserve_time: order.reserve_time || ''", proxy_view)
        self.assertIn("<th>ردیف</th><th>نام پرسنل</th><th>نوع غذا</th><th>وعده</th><th>تاریخ غذا</th><th>تاریخ رزرو</th><th>ساعت رزرو</th><th>وضعیت</th><th>عملیات</th>", proxy_view)
        self.assertIn("var canCancel = active && !isPast(row.food_date) && row.day_status !== 'closed' && row.id > 0", proxy_view)

    def test_proxy_cancel_is_server_scoped_and_reverifies_the_recipient(self) -> None:
        php = read("food-order.php")
        start = php.index("function food_order_cancel_proxy(")
        end = php.index("\nfunction ", start + 10)
        cancel = php[start:end]
        self.assertNotIn("food_order_require_permission($actor, 'foodorder.proxy')", cancel)
        self.assertIn("food_order_verify_employee_national_code($employeeId, $nationalCode, true, $pdo)", cancel)
        self.assertIn("WHERE id = ? AND employee_id = ? FOR UPDATE", cancel)
        self.assertIn("food_order_lock_cancellable_day($pdo", cancel)
        self.assertIn("function food_order_lock_cancellable_day(PDO $pdo, string $isoDate, string $mealType = 'lunch', bool $allowPastEmergency = false)", php)
        self.assertIn("UPDATE food_orders SET status = 'cancelled'", cancel)
        self.assertIn("AND status = 'active'", cancel)
        self.assertIn("$update->rowCount() !== 1", cancel)
        self.assertIn("'order_proxy_cancel'", cancel)
        self.assertNotIn("created_by =", cancel)
        route_start = php.index("if ($method === 'POST' && $route === 'cancel-proxy')")
        route_end = php.index("food_order_send_json(['error' => 'مسیر API سفارش غذا پیدا نشد.'", route_start)
        route = php[route_start:route_end]
        self.assertIn("food_order_cancel_proxy(", route)
        self.assertIn("employee_id", route)
        self.assertIn("national_code", route)

    def test_proxy_cancellation_has_visible_confirmation_and_never_silently_relies_on_browser_modal(self) -> None:
        js = read("assets/food-order.js")
        helper_start = js.index("function renderProxyCancelControl(")
        helper_end = js.index("\n  function renderProxyOrders()", helper_start)
        helper = js[helper_start:helper_end]
        self.assertIn("data-fo-action=\"confirm-cancel-proxy\"", helper)
        self.assertIn("data-fo-action=\"dismiss-cancel-proxy\"", helper)
        self.assertIn("function requestProxyCancelConfirmation(orderId)", js)
        cancel_start = js.index("async function cancelProxyOrder(orderId)")
        cancel_end = js.index("\n  async function cancelOrder", cancel_start)
        cancel = js[cancel_start:cancel_end]
        self.assertNotIn("window.confirm", cancel)
        self.assertIn("request('cancel-proxy'", cancel)
        self.assertIn("national_code: normalizedDigits(state.nationalCode)", cancel)
        self.assertIn("if (act === 'confirm-cancel-proxy') cancelProxyOrder", js)
        request_start = js.index("async function request(")
        request_end = js.index("\n  function toast", request_start)
        self.assertIn("response.status", js[request_start:request_end])

    def test_excel_date_range_metadata_and_print_scope_are_present(self) -> None:
        index = read("index.php")
        helper_start = index.index("function excel_download(")
        helper_end = index.index("\nfunction ", helper_start + 10)
        helper = index[helper_start:helper_end]
        self.assertIn("تاریخ شروع", helper)
        self.assertIn("تاریخ پایان", helper)
        self.assertIn("xlsx_build(", helper)  # خروجی واقعی XLSX
        self.assertIn("xlsx_send(", helper)
        self.assertNotIn("application/vnd.ms-excel", helper)
        self.assertIn("$excelFrom = jalali_input_to_gregorian((string) ($_GET['from'] ?? ''))", index)
        self.assertIn("['from' => $excelFrom !== null ? substr($excelFrom, 0, 10) : ''", index)
        self.assertIn("['from' => $from, 'to' => $to]", read("food-order.php"))
        self.assertIn("['from' => $fromDate, 'to' => $toDate]", read("traffic-control.php"))

        root_css = read("assets/style.css")
        self.assertIn("table th, table td", root_css)
        self.assertIn("cd-dvd-print-selected", root_css)
        self.assertIn("body.cd-dvd-print-selected > *:not(.cd-dvd-print-host)", root_css)
        cddvd_js = read("assets/cd-dvd.js")
        self.assertIn("report.querySelectorAll('.actions')", cddvd_js)
        manager_css = read("assets/food-menu.css")
        self.assertIn("fm-print-statistics", manager_css)
        self.assertIn("fm-print-orders", manager_css)
        self.assertIn("body.fm-print-statistics > *:not(.fm-print-host)", manager_css)
        self.assertIn("body.fm-print-orders > *:not(.fm-print-host)", manager_css)
        manager_js = read("assets/food-menu.js")
        self.assertIn("printScopedReport('.fm-statistics .fm-statistics-results'", manager_js)
        self.assertIn("printScopedReport('.fm-daily-orders .fm-table-wrap'", manager_js)
        self.assertIn("target.cloneNode(true)", manager_js)
        panel_css = read("food-ticket-web/assets/food-ticket-theme.css")
        self.assertIn("body.printing-report > *:not(.print-preview-layer)", panel_css)
        panel = read("food-ticket-web/index.html")
        self.assertIn("[q('تاریخ شروع'),q(faDate(from))]", panel)
        self.assertIn('<th>تاریخ شروع</th><td colspan="5">${faDate(from)}</td>', panel)
        self.assertIn('<th>تاریخ پایان</th><td colspan="5">${faDate(to)}</td>', panel)

    def test_personal_order_table_uses_row_number_instead_of_national_code(self) -> None:
        js = read("assets/food-order.js")
        start = js.index("function renderOrders()")
        end = js.index("\n  function renderProxyOrders()", start)
        personal = js[start:end]
        self.assertIn("<th>ردیف</th>", personal)
        self.assertNotIn("<th>کد ملی</th>", personal)
        self.assertIn("rows.map(function (row, index)", personal)

    def test_order_source_explanations_are_not_rendered_in_the_panel(self) -> None:
        manager = read("food-ticket-web/assets/food-menu.js")
        panel = read("food-ticket-web/index.html")
        self.assertNotIn("function renderStatus()", manager)
        self.assertNotIn("fm-mode-form", manager)
        self.assertNotIn("MySQL داخلی", manager)
        self.assertNotIn("INTERNAL-DB", manager)
        self.assertNotIn("پایگاه داخلی سفارش", panel)
        self.assertNotIn("در حال آزمون Access تردد TENTER", panel)
        self.assertNotIn("Access سفارش غذا غیرفعال است", panel)
        self.assertEqual(read("assets/food-menu.js"), manager)

    def test_admin_order_edit_route_and_controls_are_removed_owner_flow_remains(self) -> None:
        ticket = read("food-ticket.php")
        self.assertNotIn("food-menu/order-edit", ticket)
        self.assertIn("'food-menu/orders', 'food-menu/orders-export', 'food-menu/statistics', 'food-menu/export', 'food-menu/cancel-requests', 'food-menu/cancel-request-decide' => 'food.order_close'", ticket)

        order_php = read("food-order.php")
        self.assertNotIn("function food_order_admin_edit(", order_php)
        self.assertNotIn("food-menu/order-edit", order_php)
        api_start = order_php.index("function food_order_menu_api_handle(")
        api_end = order_php.index("\nfunction ", api_start + 10)
        self.assertNotIn("order-edit", order_php[api_start:api_end])
        order_start = order_php.index("if ($method === 'POST' && $route === 'order')")
        self.assertIn("food_order_create_or_change(", order_php[order_start:])
        create_start = order_php.index("function food_order_create_or_change(")
        create_end = order_php.index("\nfunction ", create_start + 10)
        create = order_php[create_start:create_end]
        self.assertIn("$isProxy = $employeeId !== $actorId", create)
        self.assertNotIn("food_order_require_permission($actor, 'foodorder.self')", create)

        js = read("assets/food-menu.js")
        orders_start = js.index("function renderOrders()")
        orders_end = js.index("\n  function renderStatistics()", orders_start)
        orders_ui = js[orders_start:orders_end]
        self.assertNotIn("fm-edit-food", orders_ui)
        self.assertNotIn("edit-order", orders_ui)
        self.assertNotIn("async function editOrder", js)
        self.assertNotIn("food-menu/order-edit", js)

    def test_order_highlight_colors_and_both_datepicker_arrow_handlers(self) -> None:
        css = read("assets/food-order.css")
        self.assertIn(".fo-day.has-order", css)
        self.assertIn(".fo-day.is-weekend,.fo-day.is-holiday", css)
        self.assertIn("background:rgba(224,242,254,.62)", css)
        js = read("assets/food-order.js")
        self.assertIn("classes.push('has-order')", js)
        self.assertIn("data-has-order=", js)
        picker = read("assets/jalali-calendar.js")
        self.assertIn("function installOutsideClickHandler()", picker)
        self.assertIn("global.__itsmJalaliOutsideClickInstalled = true", picker)
        self.assertIn("document.querySelectorAll('.jalali-calendar:not([hidden])')", picker)
        self.assertIn("}, true);", picker)
        panel = read("food-ticket-web/index.html")
        self.assertIn('[data-action^="calendar-"]', panel)
        self.assertIn("button.holiday,.calendar-grid button.weekend", panel)

    def test_employee_onboarding_is_initial_gate_and_day_dialog_only_selects_food(self) -> None:
        js = read("assets/food-order.js")
        self.assertIn("flowStage: 'choice'", js)
        self.assertIn("recipientReady: false", js)
        self.assertIn("flowAct === 'self'", js)
        self.assertIn("flowAct === 'other'", js)
        self.assertIn("state.flowDismissed = true", js)
        self.assertIn("page.classList.toggle('is-flow-dismissed'", js)
        self.assertIn("if (!state.recipientReady || !isCurrentMonth) return;", js)
        modal_start = js.index("function renderModal()")
        modal_end = js.index("\n  function render()", modal_start)
        day_dialog = js[modal_start:modal_end]
        self.assertNotIn("fo-search-name", day_dialog)
        self.assertNotIn("fo-national-code", day_dialog)
        self.assertIn("renderRecipientBanner()", js)
        self.assertIn("var searchPanel = state.dropdownOpen", js)
        self.assertIn('data-fo-flow-action="toggle-users"', js)
        self.assertIn("flowAct === 'toggle-users'", js)
        proxy_start = js.index("function beginProxyChoice()")
        proxy_end = js.index("\n  async function loadEmployeeChoices()", proxy_start)
        self.assertIn("state.dropdownOpen = false", js[proxy_start:proxy_end])
        self.assertIn("renderRecipientBanner() + renderCalendar()", js)
        self.assertIn("var selfOrder = state.targetMode === 'self' ? existingOrder : null", js)

    def test_proxy_order_submission_targets_selected_employee_and_reports_result(self) -> None:
        js = read("assets/food-order.js")
        start = js.index("async function saveOrder()")
        end = js.index("\n  async function cancelOrder", start)
        save = js[start:end]
        self.assertIn("employee_id: isProxy ? state.targetId : selfId", save)
        self.assertIn("body.national_code = normalizedDigits(state.nationalCode)", save)
        self.assertIn("request('order'", save)
        self.assertIn("var refresh = [loadMonth(), loadMyOrders()]", save)
        self.assertIn("await Promise.all(refresh)", save)
        self.assertNotIn("loadProxyOrders", save)
        self.assertIn("state.pageNotice = { message: notice, type: 'success' }", save)
        self.assertIn("state.pageNotice = { message: errorMessage, type: 'danger' }", save)
        self.assertIn("response.order_id", save)
        self.assertIn("data-fo-action=\"dismiss-notice\"", js)
        php = read("food-order.php")
        create_start = php.index("function food_order_create_or_change(")
        create_end = php.index("\nfunction ", create_start + 10)
        create = php[create_start:create_end]
        self.assertIn("$isProxy = $employeeId !== $actorId", create)
        self.assertIn("food_order_verify_employee_national_code($employeeId", create)
        self.assertIn("INSERT INTO food_orders (employee_id, calendar_item_id, food_date, meal_type, created_by, status)", create)
        self.assertIn("execute([$employeeId, $itemId, $iso, $mealType, $actorId])", create)

    def test_proxy_recipient_is_verified_server_side_and_employee_list_has_no_codes(self) -> None:
        php = read("food-order.php")
        route_start = php.index("if ($method === 'POST' && $route === 'verify-proxy')")
        route_end = php.index("if ($method === 'POST' && $route === 'order')", route_start)
        route = php[route_start:route_end]
        self.assertNotIn("food_order_require_permission($user, 'foodorder.proxy')", route)
        self.assertIn("food_order_verify_employee_national_code", route)
        self.assertIn("'person' => [", route)
        self.assertIn("'name' => food_order_full_name($target)", route)
        self.assertNotIn("'national_code' =>", route)
        self.assertNotIn("'employee_number' =>", route)
        search_start = php.index("function food_order_employee_search(")
        search_end = php.index("\nfunction ", search_start + 10)
        search = php[search_start:search_end]
        self.assertIn("SELECT id, full_name, first_name, last_name", search)
        self.assertIn("if ($query !== '')", search)
        self.assertNotIn("national_code", search)
        self.assertNotIn("employee_number' =>", search)

    def test_adjacent_calendar_days_are_disabled_and_manager_shows_every_selected_food(self) -> None:
        employee_js = read("assets/food-order.js")
        manager_js = read("assets/food-menu.js")
        self.assertIn('disabled aria-disabled="true" tabindex="-1"', employee_js)
        self.assertIn('disabled aria-disabled="true" tabindex="-1"', manager_js)
        self.assertIn("if (!currentMonth) return;", manager_js)
        self.assertIn("var mealText = meals.map(", manager_js)
        self.assertNotIn("meals.slice(0, 2)", manager_js)
        self.assertNotIn("غذای دیگر", manager_js)
        manager_css = read("assets/food-menu.css")
        self.assertRegex(manager_css, re.compile(r"@media\s*\(max-width:\s*430px\).*?\.fm-calendar-card\s+\.fo-day-meals\s*\{\s*display:flex;", re.S))
        self.assertIn('class="fm-field fm-food-selection"', manager_js)
        self.assertIn(".fm-food-selection > .fm-food-checks { display:grid; grid-template-columns:repeat(2,minmax(0,1fr))", manager_css)
        self.assertIn("grid-template-columns:minmax(0,1fr)", manager_css)

    def test_closed_day_cancel_request_and_masked_national_code(self) -> None:
        order = read("food-order.php")
        self.assertIn("function food_order_cancel_request_create(", order)
        self.assertIn("function food_order_cancel_request_decide(", order)
        self.assertIn("$route === 'cancel-request'", order)
        self.assertIn("$route === 'food-menu/cancel-requests'", order)
        self.assertIn("function food_order_cancel_reviewer_ids(", order)
        self.assertIn("is_primary_admin", order)
        self.assertIn("'support_manager'", order)
        reviewer_start = order.index("function food_order_user_is_cancel_reviewer")
        reviewer_end = order.index("\nfunction ", reviewer_start + 10)
        reviewer = order[reviewer_start:reviewer_end]
        self.assertNotIn("food.order_close", reviewer)
        self.assertIn("'support_manager'", reviewer)
        ids_start = order.index("function food_order_cancel_reviewer_ids")
        ids_end = order.index("\nfunction ", ids_start + 10)
        ids_body = order[ids_start:ids_end]
        self.assertNotIn("'admin'", ids_body)
        self.assertIn("'support_manager'", ids_body)
        self.assertIn("food_order_render_pending_cancels_card", order)
        self.assertIn("CREATE TABLE IF NOT EXISTS food_order_cancel_requests", order)
        self.assertIn("CREATE TABLE IF NOT EXISTS food_order_cancel_requests", read("schema.sql"))
        self.assertIn("CREATE TABLE IF NOT EXISTS food_order_cancel_requests", read("install-empty-db.sql"))

        js = read("assets/food-order.js")
        self.assertIn('id="fo-national-code"', js)
        self.assertIn('type="password"', js)
        self.assertIn("fo-national-secret", js)
        self.assertIn("act === 'cancel-request'", js)
        self.assertIn("confirm-cancel-request", js)
        self.assertIn("markCancelPending", js)
        self.assertIn("درخواست لغو", js)
        css = read("assets/food-order.css")
        self.assertIn("-webkit-text-security: disc", css)

        manager = read("assets/food-menu.js")
        self.assertIn("cancel-requests", manager)
        self.assertIn("food-menu/cancel-request-decide", manager)
        self.assertEqual(read("assets/food-menu.js"), read("food-ticket-web/assets/food-menu.js"))

        guide = read("help-guide.php")
        self.assertIn("درخواست لغو", guide)
        self.assertIn("درخواست‌های لغو غذا", guide)
        self.assertIn("کاربر عادی این اعلان را نمی‌بیند", guide)
        self.assertIn("ارقام داخل فیلد به‌صورت دایره یا ستاره", guide)
        chatbot = read("chatbot.php")
        self.assertIn("food_order_closed_cancel", chatbot)
        self.assertIn("کاربر عادی این اعلان را در زنگوله نمی‌بیند", chatbot)
        bootstrap = read("bootstrap.php")
        self.assertIn("function notification_visibility_sql(", bootstrap)
        self.assertIn("food_cancel_request", bootstrap)
        self.assertIn("notification_visibility_sql(", read("index.php"))
        self.assertIn("food_cafeteria_deadline", chatbot)
        self.assertIn("food_order_list_range", chatbot)
        self.assertIn("food_group_members_list", chatbot)
        # از ۱.۶۲.۱۷ اکشن food_order_cancel_decide در actions/supervisor-actions.php است (برش ۶ فاز ۲)
        index_php = read("index.php") + read("actions/supervisor-actions.php")
        self.assertIn("food_order_cancel_decide", index_php)
        self.assertIn("food_order_render_pending_cancels_card", index_php)
        live = read("food-ticket-web/index.html")
        self.assertIn("<th>ردیف</th><th>کد پرسنلی</th>", live)
        groups_js = read("food-ticket-web/assets/food-groups.js")
        self.assertIn('data-fg="members"', groups_js)
        self.assertIn("اعضای دائم گروه", groups_js)
        self.assertIn("اعضای مهمان یا موقت امروز", groups_js)

    def test_proxy_order_rate_allows_many_people(self) -> None:
        order = read("food-order.php")
        self.assertNotIn("food_order_proxy_attempt_' . $actorId, $emergency ? 30 : 5, 60)", order)
        self.assertNotIn("food_order_proxy_verify_' . (int) $user['id'], 5, 60)", order)
        self.assertIn("food_order_proxy_attempt_' . $actorId, $emergency ? 120 : 80, 60)", order)
        self.assertIn("food_order_proxy_verify_' . (int) $user['id'], 80, 60)", order)
        index_php = read("index.php")
        self.assertIn("food_order_api_'", index_php)
        self.assertIn("$foodOrderIsWrite ? 180 : 300", index_php)

    def test_order_list_default_range_includes_next_jalali_month(self) -> None:
        php = read("food-order.php")
        start = php.index("function food_order_list_range(")
        end = php.index("\nfunction ", start + 10)
        helper = php[start:end]
        self.assertIn("$employeeId = 0", helper)
        self.assertIn("nextMonthEnd", helper)
        self.assertIn("MAX(food_date)", helper)
        js = read("assets/food-order.js")
        self.assertIn("از امروز تا پایان ماه بعد", js)
        self.assertIn("بازنشانی (از امروز)", js)
        self.assertNotIn("ماه جاری (از امروز)", js)
        guide = read("help-guide.php")
        self.assertIn("از امروز تا پایان ماه شمسی بعد", guide)
        self.assertIn("سفارش آبان هم دیده می‌شود", guide)


if __name__ == "__main__":
    unittest.main(verbosity=2)
