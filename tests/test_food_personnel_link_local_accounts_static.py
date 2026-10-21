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
        self.assertIn("auth_source = 'local' AND role IN ('local_user', 'guard') AND COALESCE(is_primary_admin, 0) = 0", self.link)
        self.assertIn("function fpl_is_local_food_account(array $account)", self.link)
        self.assertIn("function fpl_code_candidate_rows(array $rows, string $code)", self.link)
        self.assertIn("auth_source IN ('food', 'local')", (ROOT / 'index.php').read_text(encoding='utf-8'))

    def test_merge_accepts_local_staff_account_and_keeps_backup_guard(self) -> None:
        self.assertIn("$isLocalFoodAccount = is_array($food) && fpl_is_local_food_account($food);", self.link)
        self.assertIn("['local_user', 'guard']", self.link)
        self.assertIn("ردیف‌های پیدا‌شده", (ROOT / 'index.php').read_text(encoding='utf-8'))
        self.assertIn("(int) ($account['is_primary_admin'] ?? 0) !== 1", self.link)
        self.assertIn("$backup = fpl_backup_users($pdo);", self.link)
        self.assertIn("UPDATE users SET employee_number = NULL, national_code = NULL, username = ? WHERE id = ?", self.link)

    def test_manual_code_lookup_supports_335_after_ad_code_conflict(self) -> None:
        self.assertIn("function fpl_find_food_by_code(PDO $pdo, string $code)", self.link)
        self.assertIn("fpl_food_personnel_code($food)", self.link)
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
        self.assertIn("$mergeResult = fpl_merge($pdo, $userId, $mergeFoodId, true, (string) $employeeNumberValue)", index)
        self.assertIn("if ($action === 'link_domain_food_by_code')", index)
        self.assertIn("name=\"action\" value=\"link_domain_food_by_code\"", index)
        self.assertIn("user_is_primary_admin($user) && (string) $managedUser['auth_source'] === 'ldap'", index)
        self.assertIn("$pdo->beginTransaction()", index)
        self.assertIn("پشتیبان: ' . $usersBackup", index)
        self.assertIn("$manualCode = fpl_normalize_code((string) ($authoritativeCode ?? ''))", self.link)
        self.assertIn("function fpl_merge(PDO $pdo, int $domainId, int $foodId, bool $allowNameMismatch = false, ?string $authoritativeCode = null, bool $sourceCodePrevalidated = false)", self.link)
        self.assertIn("$mergeResult = fpl_merge($pdo, $domainId, $foodId, true, (string) $authoritativeCode, true)", index)
        self.assertIn("foreach ($foodMatches as $foodMatch)", index)
        self.assertIn("selection_submitted", index)
        self.assertIn("fpl_select_food_matches($foodMatches, $selectedIds)", index)
        self.assertIn("name=\"food_ids[]\"", index)
        self.assertIn("UPDATE users SET employee_number = NULL WHERE id IN", index)
        self.assertIn("count($foodIds)", index)
        self.assertIn("value=\"\" placeholder=\"کد صحیح؛ مثلاً 335\"", index)
        self.assertIn("اتصال دستی ۳۳۵ کد قدیمی ۳۵ دامین را با اولویت ورودی جایگزین می‌کند", self.offline)
        self.assertIn("function fpl_codes_equal(string $left, string $right)", self.link)
        self.assertIn("'status'] = 'code_match'", self.link)
        self.assertIn("کد واردشده در این فیلد بر کد قبلی دامین مقدم است", self.link)

    def test_offline_merge_covers_references_and_report(self) -> None:
        self.assertIn("سفارش حساب محلی به کاربر دامین منتقل شد", self.offline)
        self.assertIn("گزارش وضعیت، ادغام حساب محلی را به‌عنوان تطبیق‌شده", self.offline)
        self.assertIn("fpl_reference_columns($pdo)", self.link)


if __name__ == "__main__":
    unittest.main()
