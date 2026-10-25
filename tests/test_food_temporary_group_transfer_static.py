from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class FoodTemporaryGroupTransferStaticTests(unittest.TestCase):
    def test_manager_transfer_uses_day_visit_record_and_never_changes_permanent_membership(self):
        groups = read("food-ticket-groups.php")
        start = groups.index("function food_ticket_day_visit_save(")
        end = groups.index("\nfunction food_ticket_day_visit_clear(", start)
        save = groups[start:end]
        self.assertIn("$homeId !== null && !$managerOverride", save)
        self.assertIn("food_day_visit_temporary_transfer", save)
        self.assertIn("temporary_transfer' => $temporaryTransfer", save)
        self.assertIn("food_ticket_group_absence_locked($homeId, $date)", save)
        self.assertIn("INSERT INTO food_ticket_day_visits", save)
        self.assertIn("UPDATE food_ticket_day_visits", save)
        self.assertNotIn("UPDATE food_ticket_group_members", save)
        self.assertNotIn("DELETE FROM food_ticket_group_members", save)

    def test_moved_permanent_member_is_excluded_from_home_group_and_only_in_destination(self):
        groups = read("food-ticket-groups.php")
        start = groups.index("function food_ticket_group_members_for_date(")
        end = groups.index("\nfunction food_ticket_day_visit_row(", start)
        members = groups[start:end]
        self.assertIn("SELECT user_id, group_id FROM food_ticket_day_visits WHERE visit_date = ?", members)
        self.assertIn("$visitGroupByUser[$id] === $groupId", members)
        self.assertIn("food_ticket_day_visit_users($groupId, $date)", members)
        self.assertIn("temporary_transfer", groups)

    def test_absence_and_group_views_mark_temporary_members_separately(self):
        groups = read("food-ticket-groups.php")
        start = groups.index("function food_ticket_api_absence_status(")
        end = groups.index("\nfunction ", start + 10)
        absence = groups[start:end]
        self.assertIn("food_ticket_group_members_for_date($groupId, $date)", absence)
        self.assertIn("'temporary_transfer' => $isVisitor && $homeGroupId > 0", absence)
        self.assertIn("'home_group_title'", absence)
        self.assertIn("$row['temporary_transfer'] = !empty($r['home_group_id'])", groups)

    def test_bulk_ui_allows_temporary_transfer_and_return_but_respects_locks(self):
        menu = read("food-ticket-web/assets/food-menu.js")
        start = menu.index("function bulkCanSelect(")
        end = menu.index("\n  function loadBulkSnapshot", start)
        can_select = menu[start:end]
        self.assertIn("person.home_group.locked", can_select)
        self.assertIn("visitId !== homeId", can_select)
        self.assertIn("person.visit.locked", can_select)
        self.assertIn("انتقال موقت", menu)
        self.assertIn("عضویت ثابت را تغییر نمی‌دهد", menu)
        self.assertEqual(read("assets/food-menu.js"), menu)

    def test_return_removes_daily_transfer_with_audit_and_checks_both_group_locks(self):
        groups = read("food-ticket-groups.php")
        start = groups.index("function food_ticket_day_visit_clear(")
        end = groups.index("\nfunction food_ticket_api_cancel_day_visit(", start)
        clear = groups[start:end]
        self.assertIn("food_ticket_group_absence_locked($homeId, $date)", clear)
        self.assertIn("food_day_visit_temporary_transfer_returned", clear)
        self.assertIn("DELETE FROM food_ticket_day_visits", clear)
        self.assertIn("عضویت دائمی این فرد", clear)


if __name__ == "__main__":
    unittest.main()
