from pathlib import Path
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
        self.assertIn("زمان ثبت درخواست</th>", manager)
        self.assertIn("row.requested_at_label", manager)
        self.assertIn("assets/food-menu.js?v=23", read("food-ticket-web/index.html"))
        user_js = read("assets/food-order.js")
        self.assertIn("زمان ثبت درخواست:", user_js)
        self.assertIn("SELECT id, order_id, status, created_at FROM food_order_cancel_requests", source)


if __name__ == "__main__":
    unittest.main()
