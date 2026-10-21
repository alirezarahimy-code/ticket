from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class FoodManualAttendanceStaticTests(unittest.TestCase):
    def test_manual_attendance_uses_group_edit_permission_on_write(self):
        ticket = read("food-ticket.php")
        self.assertIn("'food-manual-attendance' => 'food.groups'", ticket)
        self.assertIn("'food-manual-attendance'], true))", ticket)
        self.assertIn("'food.groups_edit'", ticket)
        self.assertIn("$_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'food-manual-attendance'", ticket)

    def test_manual_record_is_today_only_audited_and_duplicate_safe(self):
        groups = read("food-ticket-groups.php")
        start = groups.index("function food_ticket_api_manual_attendance_save(")
        end = groups.index("\nfunction food_ticket_api_groups(", start)
        save = groups[start:end]
        self.assertIn("food_ticket_manual_attendance_today()", save)
        self.assertIn("food_ticket_decide_for_user($person, $today", save)
        self.assertIn("food_ticket_existing_ticket($ticketKey)", save)
        self.assertIn("$payload['event_type'] = 'printed'", save)
        self.assertIn("food_ticket_enqueue_print($eventId, $config)", save)
        self.assertIn("food_ticket_audit('food_ticket_manual_attendance'", save)
        self.assertIn("'physical_print' => $physicalPrint", save)
        self.assertIn("food_ticket_print_log", save)

    def test_manual_selection_does_not_expose_national_codes_and_ui_has_print_toggle(self):
        groups = read("food-ticket-groups.php")
        start = groups.index("function food_ticket_api_manual_attendance(")
        end = groups.index("\n/**\n * ثبت دستی فیش", start)
        listing = groups[start:end]
        self.assertIn("WHERE is_active = 1", listing)
        self.assertNotIn("national_code", listing)
        js = read("food-ticket-web/assets/food-groups.js")
        self.assertIn("/api/food-manual-attendance", js)
        self.assertIn('id="fg-manual-print" type="checkbox" checked', js)
        self.assertIn("ثبت‌های دستی امروز", js)
        self.assertIn("manual_status", groups)


if __name__ == "__main__":
    unittest.main()
