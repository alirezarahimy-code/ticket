from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class FoodBulkOperationsStaticTests(unittest.TestCase):
    def test_bulk_api_and_food_menu_view_are_restricted_to_emergency_permission(self):
        ticket = read("food-ticket.php")
        self.assertIn("'food-menu/bulk-snapshot',", ticket)
        self.assertIn("'food-menu/bulk-apply',", ticket)
        self.assertIn("user_can($user, 'food.menu')", ticket)
        self.assertIn("user_can($user, 'food.menu_edit')", ticket)
        # مجوز اضطراری بازنشسته شد؛ عملیات گروهی با زوج food.menu/food.menu_edit کنترل می‌شود.
        self.assertIn("$fpCan('food.emergency_window')", ticket)
        self.assertNotIn("'food.order_close'", ticket)

        order = read("food-order.php")
        self.assertIn("if (str_starts_with($route, 'food-menu/bulk-'))", order)
        self.assertIn(
            "food_order_require_permission($user, $method === 'GET' && $route === 'food-menu/bulk-snapshot' ? 'food.menu' : 'food.menu_edit')",
            order,
        )
        self.assertIn("require_csrf();", order)

    def test_snapshot_includes_active_local_or_domain_staff_without_national_codes(self):
        order = read("food-order.php")
        start = order.index("function food_order_bulk_snapshot(")
        end = order.index("\nfunction food_order_bulk_apply(", start)
        snapshot = order[start:end]
        self.assertIn("WHERE u.is_active = 1 AND u.employee_number IS NOT NULL", snapshot)
        self.assertIn("'employee_number' =>", snapshot)
        self.assertIn("'home_group' =>", snapshot)
        self.assertIn("'locked' => $visitLocks[$groupId]", snapshot)
        self.assertIn("food_ticket_group_absence_locked($groupId, $date)", snapshot)
        self.assertNotIn("'national_code' =>", snapshot)
        self.assertNotIn("u.national_code", snapshot)

    def test_past_bulk_order_and_cancel_require_active_emergency_window(self):
        order = read("food-order.php")
        start = order.index("function food_order_bulk_apply(")
        end = order.index("\nfunction food_order_bulk_api_handle(", start)
        apply = order[start:end]
        self.assertIn("if ($date < food_order_today())", apply)
        self.assertIn("if (!in_array($operation, ['order', 'cancel'], true))", apply)
        self.assertIn("food_order_emergency_window_state($calendarId)['active']", apply)
        self.assertIn("$actor, null, true, $mealType, true", apply)
        self.assertIn("$actor, true, true, $reason", apply)
        self.assertIn("food_order_require_permission($actor, 'food.menu_edit')", apply)

        create_start = order.index("function food_order_create_or_change(")
        create_end = order.index("\nfunction ", create_start + 10)
        create = order[create_start:create_end]
        self.assertIn("food_order_ensure_orderable_day($pdo, $iso, $mealType, $emergency)", create)
        manager_lookup = create[create.index("$targetStmt = $pdo->prepare("):create.index("} elseif ($isProxy || $emergency)")]
        self.assertNotIn("national_code", manager_lookup)
        cancel_start = order.index("function food_order_cancel_proxy(")
        cancel_end = order.index("\nfunction ", cancel_start + 10)
        cancel = order[cancel_start:cancel_end]
        self.assertIn("food_order_lock_cancellable_day($pdo", cancel)
        self.assertIn("$managerBulk && $emergency", cancel)
        self.assertIn("&& !$emergency", cancel)
        self.assertIn("$reason !== '' ? $reason : null", cancel)


    def test_past_bulk_ui_is_enabled_only_for_order_or_cancel_with_live_window(self):
        js = read("food-ticket-web/assets/food-menu.js")
        self.assertIn("bulk.snapshot.late_order_window_open && ['order', 'cancel'].includes(bulk.operation)", js)
        self.assertIn("if (bulk.snapshot.is_past && !pastWindowAllowed) return false", js)
        self.assertIn("var isPastBlocked = isPast && !pastOperationAllowed", js)
        self.assertIn("var pastBlocked = Boolean(snapshot && snapshot.is_past && !pastWindowAllowed)", js)
        self.assertIn("این تاریخ گذشته است و مهلت اضطراری فعال ندارد", js)

    def test_canteen_change_uses_manager_override_but_keeps_print_lock_and_membership_guards(self):
        groups = read("food-ticket-groups.php")
        start = groups.index("function food_ticket_day_visit_save(")
        end = groups.index("\nfunction ", start + 10)
        save = groups[start:end]
        self.assertIn("bool $managerOverride = false", save)
        self.assertIn("food_ticket_group_absence_locked", save)
        self.assertIn("food_ticket_user_group_id", save)
        self.assertIn("دسترسی عملیات گروهی غذا ندارید", save)
        self.assertIn("food_ticket_audit", save)

    def test_bulk_tab_date_picker_and_removed_calendar_emergency_panel(self):
        js = read("food-ticket-web/assets/food-menu.js")
        self.assertIn("tabs.push(['bulk', 'عملیات گروهی'])", js)
        self.assertIn("state.tab === 'bulk' && canMenu ? renderBulkOperations()", js)
        self.assertIn("id=\"fm-bulk-date-form\"", js)
        self.assertIn("name=\"date\" data-jalali", js)
        self.assertIn("data-fm-action=\"bulk-confirm\"", js)
        render_start = js.index("function render()")
        render_end = js.index("\n  async function loadStatus", render_start)
        self.assertNotIn("emergencyPanel()", js[render_start:render_end])
        self.assertEqual(read("assets/food-menu.js"), js)
        self.assertEqual(read("assets/food-menu.css"), read("food-ticket-web/assets/food-menu.css"))


if __name__ == "__main__":
    unittest.main()
