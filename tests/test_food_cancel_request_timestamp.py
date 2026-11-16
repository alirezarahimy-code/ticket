from pathlib import Path
import re
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class FoodCancelRequestTimestampTests(unittest.TestCase):
    def test_request_time_is_saved_and_included_in_audit_and_notifications(self):
        source = read("food-order.php")
        self.assertIn("SELECT created_at FROM food_order_cancel_requests WHERE id = ?", source)
        self.assertIn("'requested_at' => $requestCreatedAt", source)
        self.assertIn("زمان ثبت درخواست: ' . $requestCreatedAtLabel", source)

    def test_dashboard_and_manager_request_report_show_request_time(self):
        source = read("food-order.php")
        manager = read("assets/food-menu.js")
        self.assertIn("'requested_at_label' => function_exists('jalali_date')", source)
        self.assertIn("class=\"food-cancel-request-time\">زمان ثبت درخواست:", source)
        # The active table builds sortable headers through cancelHead(); the label is
        # intentionally a button rather than a literal <th> text node.
        self.assertIn("cancelHead('requested_at', 'زمان ثبت درخواست')", manager)
        self.assertIn("row.requested_at_label || row.requested_at || row.created_at", manager)
        self.assertRegex(read("food-ticket-web/index.html"), r"assets/food-menu\.js\?v=[A-Za-z0-9._-]+")
        user_js = read("assets/food-order.js")
        self.assertIn("زمان ثبت درخواست:", user_js)
        self.assertIn("SELECT id, order_id, status, created_at FROM food_order_cancel_requests", source)


if __name__ == "__main__":
    unittest.main()
