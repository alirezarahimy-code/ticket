from __future__ import annotations

from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


class FoodPersonnelLocalLinkStaticTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        cls.link = (ROOT / "food-personnel-link.php").read_text(encoding="utf-8")
        cls.offline = (ROOT / "tools/test_food_personnel_link_offline.php").read_text(encoding="utf-8")
        cls.bootstrap = (ROOT / "bootstrap.php").read_text(encoding="utf-8")

    def test_candidates_include_local_food_staff_not_admins(self) -> None:
        self.assertIn("auth_source = 'local' AND role = 'local_user' AND COALESCE(is_primary_admin, 0) = 0", self.link)

    def test_merge_accepts_local_staff_account_and_keeps_backup_guard(self) -> None:
        self.assertIn("$isLocalFoodAccount = $foodSource === 'local'", self.link)
        self.assertIn("(int) ($food['is_primary_admin'] ?? 0) !== 1", self.link)
        self.assertIn("$backup = fpl_backup_users($pdo);", self.link)
        self.assertIn("UPDATE users SET employee_number = NULL, national_code = NULL, username = ? WHERE id = ?", self.link)

    def test_manual_code_lookup_supports_335_after_ad_code_conflict(self) -> None:
        self.assertIn("function fpl_find_food_by_code(PDO $pdo, string $code)", self.link)
        self.assertIn("fpl_food_personnel_code($row)", self.link)
        self.assertIn("fpl_normalize_code((string) ($food['username'] ?? ''))", self.link)
        self.assertIn("fpl_apply($pdo, [], ['23' => '335'])", self.offline)
        self.assertIn("جستجوی کد محلی", self.offline)
        self.assertIn("'employeeid'][0]", self.bootstrap)
        self.assertIn("if ($codeConflict) {\n        $newCode = null;", self.bootstrap)

    def test_domain_entered_employee_code_is_authoritative_and_transactionally_merges_owner(self) -> None:
        index = (ROOT / "index.php").read_text(encoding="utf-8")
        self.assertIn("require_once __DIR__ . '/food-personnel-link.php'", index)
        self.assertIn("$ownerId = employee_number_owner($employeeNumberValue, $userId)", index)
        self.assertIn("fpl_find_food_by_code(db(), (string) $employeeNumberValue)", index)
        self.assertIn("$usersBackup = fpl_backup_users(db())", index)
        self.assertIn("$mergeResult = fpl_merge($pdo, $userId, $mergeFoodId, true)", index)
        self.assertIn("$pdo->beginTransaction()", index)
        self.assertIn("پشتیبان: ' . $usersBackup", index)
        self.assertIn("function fpl_codes_equal(string $left, string $right)", self.link)
        self.assertIn("'status'] = 'code_match'", self.link)
        self.assertIn("کد ثبت‌شده در حساب دامین", self.link)

    def test_offline_merge_covers_references_and_report(self) -> None:
        self.assertIn("سفارش حساب محلی به کاربر دامین منتقل شد", self.offline)
        self.assertIn("گزارش وضعیت، ادغام حساب محلی را به‌عنوان تطبیق‌شده", self.offline)
        self.assertIn("fpl_reference_columns($pdo)", self.link)


if __name__ == "__main__":
    unittest.main()
