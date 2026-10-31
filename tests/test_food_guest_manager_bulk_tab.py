from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


class FoodGuestManagerBulkTabTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.order = (ROOT / "food-order.php").read_text(encoding="utf-8")
        cls.ticket = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
        cls.ui = (ROOT / "assets/food-menu.js").read_text(encoding="utf-8")
        cls.web_ui = (ROOT / "food-ticket-web/assets/food-menu.js").read_text(encoding="utf-8")
        cls.web_index = (ROOT / "food-ticket-web/index.html").read_text(encoding="utf-8")

    def test_guest_manager_routes_require_emergency_permission_and_reviewer_role(self):
        for route in [
            "food-menu/guest-requests",
            "food-menu/guest-request-update",
            "food-menu/guest-request-cancel",
            "food-menu/guest-request-delete",
        ]:
            self.assertIn("'" + route + "'", self.ticket)
        self.assertIn("food_guest_request_is_reviewer($user)", self.order)
        self.assertIn("food_guest_request_list($user, true)", self.order)
        self.assertIn("food-menu/guest-request-update", self.ticket)
        self.assertIn("return function_exists('food_guest_request_is_reviewer') && food_guest_request_is_reviewer($user);", self.ticket)
        self.assertIn("FOOD_TICKET_CAN_GUEST_REQUEST_MANAGE", self.ticket)

    def test_support_view_includes_historical_requests_but_member_view_stays_scoped(self):
        self.assertIn("function food_guest_request_list(array $user, bool $includePast = false)", self.order)
        self.assertIn("$dateFilter = $includePast ? '' : 'WHERE r.request_date >= ?';", self.order)
        self.assertIn("$rowLimit = $includePast ? '' : 'LIMIT 300';", self.order)
        self.assertIn("$stmt->execute($includePast ? [] : [$today]);", self.order)
        self.assertIn("$canEdit = $canManage && $active && food_guest_request_change_window_open($date, $now);", self.order)

    def test_guest_manager_tab_and_actions_are_inside_group_operations(self):
        self.assertIn("['guest', 'مدیریت درخواست مهمان']", self.ui)
        self.assertIn("renderGuestRequestManager()", self.ui)
        self.assertIn("request('food-menu/guest-requests')", self.ui)
        self.assertIn("food-menu/guest-request-update", self.ui)
        self.assertIn("guestRequestAction(Number(action.dataset.id || 0), 'cancel')", self.ui)
        self.assertIn("guestRequestAction(Number(action.dataset.id || 0), 'delete')", self.ui)
        self.assertIn("if (item.can_edit)", self.ui)
        self.assertIn("if (item.can_cancel)", self.ui)
        self.assertIn("if (item.can_delete)", self.ui)
        self.assertIn("سوابق گذشته", self.ui)
        self.assertIn("row.request_jalali || row.request_date", self.ui)

    def test_served_asset_copy_and_cache_buster_match(self):
        self.assertEqual(self.ui, self.web_ui)
        self.assertIn('assets/food-menu.js?v=36', self.web_index)


if __name__ == "__main__":
    unittest.main()
