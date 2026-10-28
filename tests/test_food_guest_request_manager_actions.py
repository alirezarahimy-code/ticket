from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


class FoodGuestRequestManagerActionTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.order = (ROOT / "food-order.php").read_text(encoding="utf-8")
        cls.guest_js = (ROOT / "assets/food-order-guest.js").read_text(encoding="utf-8")

    def test_owner_and_support_manager_permissions_are_enforced_server_side(self):
        self.assertIn("function food_guest_request_is_reviewer(array $user)", self.order)
        self.assertIn("food_order_user_is_cancel_reviewer($user)", self.order)
        self.assertIn("user_service_group($user) === 'support'", self.order)
        self.assertIn("(int) ($row['created_by'] ?? 0) === $uid", self.order)
        self.assertIn("food_guest_request_can_manage($user, $row)", self.order)

    def test_update_delete_and_cancel_have_separate_api_routes_and_audit(self):
        for route in ["guest-request-update", "guest-request-delete", "guest-request-cancel"]:
            self.assertIn("$route === '" + route + "'", self.order)
        self.assertIn("food_order_audit('food_guest_request_update'", self.order)
        self.assertIn("food_order_audit('food_guest_request_delete'", self.order)
        self.assertIn("food_order_audit('food_guest_request_cancel'", self.order)
        self.assertIn("UPDATE food_guest_requests SET request_date = ?, organization = ?, guest_count = ?, requester_name = ?, note = ?", self.order)
        self.assertIn("DELETE FROM food_guest_requests WHERE id = ?", self.order)

    def test_ui_renders_authorized_edit_cancel_delete_actions(self):
        self.assertIn("if (r.can_edit)", self.guest_js)
        self.assertIn("if (r.can_cancel)", self.guest_js)
        self.assertIn("if (r.can_delete)", self.guest_js)
        self.assertIn("request(editingId ? 'guest-request-update' : 'guest-request'", self.guest_js)
        self.assertIn("request('guest-request-delete'", self.guest_js)
        self.assertIn("request('guest-request-cancel'", self.guest_js)
        self.assertIn("created_by_name", self.guest_js)

    def test_current_day_deadline_and_past_date_are_guarded(self):
        self.assertIn("function food_guest_request_change_window_open(", self.order)
        self.assertIn("food_guest_request_same_day_is_open($now)", self.order)
        self.assertIn("food_guest_request_change_window_open((string) ($row['request_date'] ?? ''))", self.order)


if __name__ == "__main__":
    unittest.main()
