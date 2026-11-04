import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


class UserAccessGroupStaticTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.permissions = (ROOT / "permissions.php").read_text(encoding="utf-8")
        cls.group_helpers = (ROOT / "user-access-groups.php").read_text(encoding="utf-8")
        cls.actions = (ROOT / "actions/access-group-actions.php").read_text(encoding="utf-8")
        cls.index = (ROOT / "index.php").read_text(encoding="utf-8")
        cls.js = (ROOT / "assets/app.js").read_text(encoding="utf-8")
        cls.migration = (ROOT / "migrations/023-user-access-groups.sql").read_text(encoding="utf-8")

    def test_group_permissions_are_additive_and_primary_admin_stays_unrestricted(self):
        user_can = self.permissions.split("function user_can(array $user, string $permission): bool", 1)[1]
        user_can = user_can.split("function user_can_any", 1)[0]
        self.assertLess(user_can.index("user_is_primary_admin($user)"), user_can.index("user_access_group_permission_set($user)"))
        self.assertIn("isset(role_permission_set(user_role_code($user))[$permission])", user_can)
        self.assertIn("|| isset(user_access_group_permission_set($user)[$permission])", user_can)
        self.assertIn("SELECT DISTINCT gp.permission_code", self.permissions)
        self.assertIn("FROM user_access_group_members gm", self.permissions)
        self.assertIn("INNER JOIN user_access_group_permissions gp", self.permissions)
        self.assertIn("group_user.is_active = 1", self.permissions)
        self.assertIn("group_user.auth_source <> \\'food\\'", self.permissions)
        self.assertIn("permission_filter_role_codes(user_role_code($user)", self.permissions)
        self.assertIn("role_permissions_add_parents($codes)", self.permissions)

    def test_schema_supports_many_groups_per_user_and_cascades_group_cleanup(self):
        self.assertIn("PRIMARY KEY (group_id, user_id)", self.migration)
        self.assertIn("ON DELETE CASCADE", self.migration)
        self.assertIn("PRIMARY KEY (group_id, permission_code)", self.migration)
        self.assertIn("CREATE TABLE IF NOT EXISTS user_access_groups", self.migration)
        self.assertIn("CREATE TABLE IF NOT EXISTS user_access_group_members", self.migration)
        self.assertIn("CREATE TABLE IF NOT EXISTS user_access_group_permissions", self.migration)
        for schema_name in ("schema.sql", "install-empty-db.sql"):
            schema = (ROOT / schema_name).read_text(encoding="utf-8")
            for table in ("user_access_groups", "user_access_group_members", "user_access_group_permissions"):
                self.assertIn(f"CREATE TABLE IF NOT EXISTS {table}", schema)

    def test_create_update_and_delete_are_primary_admin_only_and_audited(self):
        for action in ("create_access_group", "save_access_group", "delete_access_group"):
            block = self.actions.split(f"if ($action === '{action}')", 1)[1]
            self.assertIn("$admin = require_primary_admin();", block[:260])
        self.assertIn("db()->beginTransaction()", self.actions)
        self.assertIn("db_commit('index.php')", self.actions)
        self.assertIn("access_group_created", self.actions)
        self.assertIn("access_group_updated", self.actions)
        self.assertIn("access_group_deleted", self.actions)
        self.assertIn("permission_all_codes()", self.actions)
        self.assertIn("is_active = 1 AND auth_source <> 'food'", self.actions)
        self.assertIn("foreach (array_chunk($requestedIds, 1000) as $memberChunk)", self.actions)
        self.assertIn("$existingMembersQuery", self.actions)
        self.assertIn("$existingMemberSet", self.actions)
        self.assertNotIn("DELETE FROM user_access_group_members WHERE group_id = ?'", self.actions)
        self.assertIn("require_csrf();", self.index)

    def test_settings_ui_reuses_permission_catalog_and_member_search(self):
        self.assertIn("require_once __DIR__ . '/user-access-groups.php';", self.index)
        self.assertIn('id="access-groups"', self.index)
        self.assertIn("user_access_group_permission_matrix_markup($selectedPermissions, $catalog)", self.index)
        self.assertIn("$accessGroupMemberSets = user_access_groups_member_sets();", self.index)
        self.assertIn("$accessGroupPermissionSets = user_access_groups_permission_sets();", self.index)
        self.assertIn("$selectedMembers = $accessGroupMemberSets[$accessGroupId] ?? [];", self.index)
        self.assertIn("$selectedPermissions = array_keys($accessGroupPermissionSets[$accessGroupId] ?? []);", self.index)
        self.assertIn("COUNT(DISTINCT CASE WHEN group_user.is_active = 1", self.group_helpers)
        self.assertNotIn("member.is_active", self.group_helpers)
        self.assertIn("name=\"members[]\"", self.index)
        self.assertIn("data-access-member-search", self.index)
        self.assertIn("['access-groups', 'گروه‌های دسترسی']", self.js)
        self.assertIn("document.querySelectorAll('.roles-shell')", self.js)
        self.assertIn("[data-access-member-select-visible]", self.js)
        self.assertIn("[data-access-member-clear-visible]", self.js)
        self.assertIn("data-access-group-field=\"name\"", self.index)
        self.assertIn("originalMembers", self.js)
        self.assertIn("refreshChanges();", self.js)
        self.assertIn("require_primary_admin()", self.actions)

    def test_special_role_constraints_and_documentation_are_preserved(self):
        self.assertIn("permission_filter_role_codes(user_role_code($user)", self.permissions)
        self.assertIn("cddvd.edit", self.group_helpers)
        self.assertIn('id="access-groups"', (ROOT / "help-guide.php").read_text(encoding="utf-8"))
        self.assertIn("user-access-group", (ROOT / "docs/USER-ACCESS-GROUPS-FA.md").read_text(encoding="utf-8"))


if __name__ == "__main__":
    unittest.main()
