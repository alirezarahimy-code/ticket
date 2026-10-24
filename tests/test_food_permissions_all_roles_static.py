from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding='utf-8')


class FoodPermissionsAllRolesTests(unittest.TestCase):
    def test_food_permission_catalog_is_rendered_for_every_role(self):
        index = read('index.php')
        self.assertIn("'key' => 'food'", read('permissions.php'))
        self.assertNotIn("if ($groupAllFood && !in_array($roleCode, print_fish_allowed_roles(), true))", index)
        self.assertIn("foreach (permission_roles() as $roleCode => $roleLabel)", index)
        self.assertIn("'local_user' => 'کاربر محلی'", read('permissions.php'))
        self.assertIn("'local_user' => ['icon' => 'user', 'desc' => 'کاربر محلی (پرسنل غذا)'", index)

    def test_food_grants_are_not_stripped_when_saving_custom_roles(self):
        php = read('permissions.php')
        start = php.index('function role_permissions_save(')
        end = php.index('\nfunction role_permissions_reset(', start)
        save = php[start:end]
        self.assertNotIn("str_starts_with((string) $c, 'food.')", save)
        self.assertIn("$codes = array_values(array_intersect($codes, $valid));", save)
        self.assertIn('role_permissions_mark_custom($role, !role_permissions_same_set(', save)

    def test_old_restriction_migration_no_longer_deletes_food_permissions(self):
        php = read('permissions.php')
        start = php.index('function role_permissions_migrate_once(')
        end = php.index('\nfunction role_permission_codes(', start)
        migration = php[start:end]
        self.assertIn("setting('print_fish_roles_v2')", migration)
        self.assertNotIn("permission LIKE 'food.%'", migration)
        self.assertNotIn("DELETE FROM role_permissions WHERE permission IN ('food.orders'", migration)

    def test_domain_and_local_users_share_role_permission_enforcement(self):
        php = read('permissions.php')
        self.assertIn('role_permission_set(user_role_code($user))', php)
        index = read('index.php')
        self.assertIn("$managedUser['auth_source'] === 'ldap' ? 'دامینی' : 'محلی'", index)
        self.assertIn('مجوزها را می‌گیرند', index)


if __name__ == '__main__':
    unittest.main()
