from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class EmergencyOrderWindowTests(unittest.TestCase):
    def test_grant_is_single_day_bounded_and_reasoned(self) -> None:
        php = read("food-order.php")
        start = php.index("function food_order_emergency_window_grant(")
        end = php.index("\nfunction ", start + 10)
        grant = php[start:end]
        self.assertIn("food_order_require_primary_admin($user)", grant)
        self.assertIn("[1, 2, 4, 8, 24]", grant)
        self.assertIn("mb_strlen($reason, 'UTF-8') < 5", grant)
        self.assertIn("$date > $today", grant)
        self.assertIn("$daysBack > 7", grant)
        self.assertIn("food_order_log(null, $calendarId, 'emergency_window_grant'", grant)
        self.assertIn("food_order_audit('food_order_emergency_window_grant'", grant)

    def test_only_active_window_bypasses_past_or_closed_day_guard(self) -> None:
        php = read("food-order.php")
        start = php.index("function food_order_ensure_orderable_day(")
        end = php.index("\nfunction ", start + 10)
        helper = php[start:end]
        self.assertIn("food_order_emergency_window_state((int) $calendar['id'])", helper)
        self.assertIn("empty($lateWindow['active'])", helper)
        self.assertIn("'late_order_window_open' => !empty($lateWindow['active'])", helper)
        self.assertIn("ORDER BY id DESC LIMIT 1", php)
        self.assertIn("'emergency_window_revoke'", php)

    def test_manager_api_and_calendar_controls_are_permission_checked(self) -> None:
        php = read("food-order.php")
        ticket = read("food-ticket.php")
        panel = read("food-ticket-web/index.html")
        self.assertIn("$route === 'food-menu/emergency-window'", php)
        self.assertIn("food_order_require_primary_admin($user)", php)
        self.assertIn("if (in_array($route, ['food-menu/emergency-window', 'food-menu/emergency-window-state'], true))", ticket)
        self.assertIn("emergency-window-settings-form", panel)
        self.assertIn("api('/food-menu/emergency-window'", panel)
        self.assertIn("duration_hours:hours", panel)

    def test_employee_ui_requires_backend_window_for_late_order(self) -> None:
        php = read("food-order.php")
        frontend = read("assets/food-order.js")
        self.assertIn("$day['late_order_window_open']", php)
        self.assertIn("if (day.late_order_window_open) return ''", frontend)
        self.assertIn("day.late_order_window_open ||", frontend)
        self.assertIn("مهلت اضطراری ثبت سفارش", frontend)

    def test_order_sort_uses_surname_then_first_name(self) -> None:
        php = read("food-order.php")
        frontend = read("assets/food-menu.js")
        self.assertIn("u.last_name, u.first_name, u.full_name", php)
        self.assertIn("if (key === 'full_name')", frontend)
        self.assertIn("row.last_name", frontend)
        self.assertIn("row.first_name", frontend)
        self.assertEqual(frontend, read("food-ticket-web/assets/food-menu.js"))


if __name__ == "__main__":
    unittest.main()
