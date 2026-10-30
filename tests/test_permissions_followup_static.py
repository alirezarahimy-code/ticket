from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class PermissionFollowupStaticTests(unittest.TestCase):
    def test_health_and_traffic_destinations_have_view_edit_pairs(self):
        permissions = read("permissions.php")
        self.assertIn("['code' => 'system.health_edit'", permissions)
        self.assertIn("'system.health' => 'system.health_edit'", permissions)
        self.assertIn("'traffic.destinations' => 'traffic.destinations_edit'", permissions)
        self.assertIn("['code' => 'traffic.destinations_edit'", permissions)

        health = read("system-health.php")
        self.assertIn("require_permission('system.health_edit')", health)
        self.assertIn("$canManage = user_can($user, 'system.health_edit')", health)
        self.assertIn("if (!user_can($user, 'system.health'))", health)

        traffic = read("traffic-control.php")
        self.assertIn("user_can($user, 'traffic.destinations')", traffic)
        self.assertIn("user_can($user, 'traffic.destinations_edit')", traffic)
        self.assertIn("$tab === 'destinations' && traffic_can_view_destinations($user)", traffic)
        self.assertIn("traffic_can_manage_destinations($user)", traffic)

    def test_cd_dvd_incoming_defaults_and_direct_edit_allowlist(self):
        permissions = read("permissions.php")
        self.assertIn("return ['primary_admin', 'supervisor', 'inspector', 'guard']", permissions)
        self.assertIn("'cddvd.submit_in'", permissions)
        self.assertIn("'cddvd.edit'", permissions)
        self.assertNotIn("'cddvd.view' => 'cddvd.edit'", permissions)
        self.assertIn("permission_filter_role_codes($role, $codes)", permissions)
        self.assertIn("DELETE FROM role_permissions WHERE permission = 'cddvd.edit' AND role NOT IN ('primary_admin', 'supervisor', 'inspector', 'guard')", permissions)

        cd_dvd = read("cd-dvd.php")
        self.assertIn("if (!in_array((string) ($user['role'] ?? ''), $allowedRoles, true))", cd_dvd)
        self.assertIn("return cd_dvd_can_edit_records($user);", cd_dvd)
        self.assertIn("if (!$record || !cd_dvd_can_edit_records($user))", cd_dvd)
        self.assertIn("cd_dvd_validate_payload($_POST, $user, $id, (string) ($record['direction'] ?? ''))", cd_dvd)
        self.assertIn("درخواست اصلاح", cd_dvd)

    def test_primary_admin_promotion_is_not_delegated(self):
        role_actions = read("actions/role-actions.php")
        self.assertIn("if (!user_is_primary_admin($rolesAdmin))", role_actions)
        self.assertIn("$itExperts = $oldExperts;", role_actions)

    def test_proxy_permission_is_not_listed_as_an_independent_role_grant(self):
        permissions = read("permissions.php").split("function permission_all_codes()", 1)[0]
        self.assertNotIn("['code' => 'foodorder.proxy'", permissions)


if __name__ == "__main__":
    unittest.main()
