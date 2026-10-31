from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class FoodManualAttendanceStaticTests(unittest.TestCase):
    def test_manual_attendance_is_in_settings_and_removed_from_food_groups_page(self):
        index = read("food-ticket-web/index.html")
        groups_js = read("food-ticket-web/assets/food-groups.js")
        manual_js = read("food-ticket-web/assets/food-manual-attendance.js")
        self.assertIn("['manual-attendance','ثبت دستی فیش/تردد']", index)
        self.assertIn("'manual-attendance':()=>window.FoodManualAttendanceUI?window.FoodManualAttendanceUI.html()", index)
        self.assertIn("FoodManualAttendanceUI.mount()", index)
        self.assertIn('src="assets/food-manual-attendance.js?v=5"', index)
        self.assertNotIn("manualAttendance", groups_js)
        self.assertIn("ثبت دستی فیش", manual_js)
        self.assertIn("فهرست فقط کارکنان فعالِ دارای سفارش ناهارِ روز انتخاب‌شده را نشان می‌دهد", manual_js)
        self.assertIn("هیچ کارمند فعالی با سفارش ناهار واجد شرایط پیدا نشد", manual_js)
        self.assertIn("id=\"manual-attendance-root\"", manual_js)

    def test_manual_write_requires_group_edit_permission_and_today(self):
        ticket = read("food-ticket.php")
        self.assertIn("'food-manual-attendance' => 'food.manual_attendance'", ticket)
        self.assertIn("'food.manual_attendance_edit'", ticket)
        self.assertIn("'food.groups_edit'", ticket)
        self.assertIn("$_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'food-manual-attendance'", ticket)
        self.assertIn("food_ticket_api_manual_attendance_save_many($body, $user)", ticket)
        self.assertIn("$body['date'] = $manualDate", ticket)
        self.assertNotIn("$manualDate !== food_ticket_manual_attendance_today()", ticket)

    def test_list_contains_only_active_people_with_the_same_eligible_order_map(self):
        groups = read("food-ticket-groups.php")
        start = groups.index("function food_ticket_api_manual_attendance(")
        end = groups.index("/** ثبت دستی یک تردد برای روز انتخاب‌شده", start)
        listing = groups[start:end]
        self.assertIn("food_ticket_group_order_map($orderMaps, $date)", listing)
        self.assertIn("if (!is_array($orderMap))", listing)
        self.assertIn("$hasEligibleOrder = food_ticket_group_food_lookup($orderMap)", listing)
        self.assertIn("SELECT id, employee_number, national_code, full_name, username, is_active FROM users", listing)
        self.assertIn("WHERE is_active = 1", listing)
        self.assertIn("return $hasEligibleOrder($row) !== null", listing)
        self.assertNotIn("'national_code' =>", listing)
        self.assertIn("'today_record' => $attendanceByUser[$id] ?? null", listing)
        self.assertIn("'ticket_issued' =>", listing)

    def test_single_and_bulk_save_support_ticket_and_no_ticket_separately(self):
        groups = read("food-ticket-groups.php")
        start = groups.index("function food_ticket_api_manual_attendance_save(")
        end = groups.index("\nfunction food_ticket_api_groups(", start)
        save = groups[start:end]
        self.assertIn("bool $withTicket, bool $physicalPrint", save)
        self.assertIn("if (!$withTicket)", save)
        self.assertIn("'event_type' => 'no_food'", save)
        self.assertIn("'with_ticket' => false", save)
        self.assertIn("food_ticket_decide_for_user($person, $targetDate", save)
        self.assertIn("food_ticket_group_food_lookup($orderMap)($person)", save)
        self.assertIn("سفارش فعال ناهار در روز انتخاب‌شده پیدا نشد", save)
        self.assertIn("'print_status' => 'not_printed'", save)
        self.assertIn("$payload['print_status'] = $physicalPrint ? 'pending' : 'printed'", save)
        self.assertIn("food_ticket_enqueue_print($eventId, $config)", save)
        self.assertIn("'with_ticket' => $withTicket", save)
        self.assertIn("food_ticket_audit('food_ticket_manual_attendance'", save)
        self.assertIn("function food_ticket_api_manual_attendance_save_many(array $body, array $actor)", save)
        self.assertIn("foreach ($userIds as $userId)", save)
        self.assertIn("catch (Throwable $exception)", save)
        self.assertIn("'results' => $results", save)

    def test_ui_has_live_search_selection_results_and_independent_physical_print(self):
        js = read("food-ticket-web/assets/food-manual-attendance.js")
        self.assertIn("id=\"fg-manual-filter\" type=\"search\"", js)
        self.assertIn("data-manual-person", js)
        self.assertIn("value=\"yes\"", js)
        self.assertIn("value=\"no\"", js)
        self.assertIn("id=\"fg-manual-print\" type=\"checkbox\"", js)
        self.assertIn("user_ids: batch", js)
        self.assertIn("offset += 100", js)
        self.assertIn("results.push(...((latest && latest.results) || []))", js)
        self.assertNotIn('id="fg-manual-user"', js)

    def test_monitoring_distinguishes_no_ticket_from_ticket_not_physically_printed(self):
        ticket = read("food-ticket.php")
        start = ticket.index("function food_ticket_api_monitoring(")
        end = ticket.index("\n\n/** کلیدهای ۸رقمی", start)
        monitoring = ticket[start:end]
        self.assertIn("تردد دستی (بدون فیش)", monitoring)
        self.assertIn("فیش صادر شد؛ چاپ فیزیکی انجام نشد", monitoring)


if __name__ == "__main__":
    unittest.main()
