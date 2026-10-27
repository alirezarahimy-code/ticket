from pathlib import Path
import re
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding='utf-8')


class FoodMealSeparationTests(unittest.TestCase):
    def test_fresh_schemas_keep_legacy_rows_as_lunch_and_allow_one_calendar_per_meal(self):
        for path in ('schema.sql', 'install-empty-db.sql', 'upgrade-1.38-food-orders.sql'):
            sql = read(path)
            self.assertIn("meal_type ENUM('lunch','dinner') NOT NULL DEFAULT 'lunch'", sql)
            self.assertIn('UNIQUE KEY uq_food_calendar_date_meal (food_date, meal_type)', sql)
            self.assertIn('UNIQUE KEY uq_food_order_employee_date_meal (employee_id, food_date, meal_type)', sql)

    def test_upgrade_migration_adds_lunch_defaults_and_replaces_date_only_keys(self):
        sql = read('migrations/006-food-meal-type.sql')
        self.assertEqual(sql.count('DEFAULT \'\'lunch\'\''), 2)
        self.assertIn('DROP INDEX uq_food_calendar_date', sql)
        self.assertIn('ADD UNIQUE KEY uq_food_calendar_date_meal (food_date, meal_type)', sql)
        self.assertIn('DROP INDEX uq_food_order_employee_date', sql)
        self.assertIn('ADD UNIQUE KEY uq_food_order_employee_date_meal (employee_id, food_date, meal_type)', sql)
        manifest = read('updates/2026-10-20-release-161-6/manifest.json')
        self.assertIn('006-food-meal-type.sql', manifest)

    def test_api_filters_calendar_orders_and_statistics_by_selected_meal(self):
        php = read('food-order.php')
        self.assertIn("AND c.meal_type = ?", php)
        self.assertIn("AND o.meal_type = ?", php)
        self.assertIn("food_order_meal_type($_GET['meal_type'] ?? 'lunch')", php)
        self.assertIn("food_order_meal_type($body['meal_type'] ?? 'lunch')", php)
        self.assertIn("'active_dinner_orders_today' => $counts['dinner']", php)

    def test_order_ui_and_manager_expose_meal_switch_and_separate_lists(self):
        employee = read('assets/food-order.js')
        manager = read('assets/food-menu.js')
        self.assertIn("state.mealType === 'dinner'", employee)
        self.assertIn('meal_type: state.mealType', employee)
        self.assertIn("state.mealType === 'dinner'", manager)
        self.assertIn('meal_type: state.mealType', manager)
        self.assertIn("state.mealType === 'lunch' ? '<button", manager)
        self.assertIn("state.mealType !== 'lunch'", manager)

    def test_receipt_order_sources_are_explicitly_lunch_only(self):
        source = read('food-ticket-order-source.php')
        self.assertGreaterEqual(source.count("meal_type = 'lunch'"), 2)


if __name__ == '__main__':
    unittest.main()
