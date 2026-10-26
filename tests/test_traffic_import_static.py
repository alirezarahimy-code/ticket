from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(relative: str) -> str:
    return (ROOT / relative).read_text(encoding="utf-8")


class TrafficImportStaticTest(unittest.TestCase):
    def test_import_page_is_direct_only_and_requires_existing_permissions(self):
        control = read("traffic-control.php")
        index = read("index.php")
        permissions = read("permissions.php")
        self.assertIn("'import'", control)
        self.assertIn("page=traffic-control&amp;tab=import", control)
        self.assertIn("require_permission('traffic.view')", control)
        self.assertIn("require_permission('traffic.manage')", control)
        self.assertIn("user_can($user, 'traffic.manage')", control)
        self.assertIn("ثبت، ویرایش و ورود گروهی سوابق", permissions)
        # The ordinary traffic menu points to the default tab; no import tab was added to navigation.
        self.assertIn("index.php?page=traffic-control', 'کنترل تردد'", index)
        self.assertNotIn("index.php?page=traffic-control&tab=import", index)

    def test_post_import_is_csrf_protected_and_confirmation_matches_preview_file(self):
        index = read("index.php")
        control = read("traffic-control.php")
        helper = read("traffic-import.php")
        csrf_at = index.index("if ($_SERVER['REQUEST_METHOD'] === 'POST')")
        post_handler_at = index.index("traffic_handle_post($action, $user)")
        self.assertLess(index.index("require_csrf();", csrf_at), post_handler_at)
        self.assertIn("traffic_import_preview", control)
        self.assertIn("traffic_import_apply", control)
        self.assertIn("hash_equals((string) $preview['hash'], (string) $read['hash'])", helper)
        self.assertIn("$pdo->beginTransaction();", helper)
        self.assertIn("$pdo->commit();", helper)
        self.assertIn("traffic_import_apply_plan_in_transaction", helper)

    def test_parser_limits_duplicate_protection_and_privacy_are_present(self):
        helper = read("traffic-import.php")
        self.assertIn("TRAFFIC_IMPORT_MAX_BYTES = 10485760", helper)
        self.assertIn("TRAFFIC_IMPORT_MAX_ROWS = 10000", helper)
        self.assertIn("TRAFFIC_IMPORT_MAX_COLUMNS = 64", helper)
        self.assertIn("traffic_import_csv_shape_error", helper)
        self.assertIn("ZipArchive", helper)
        self.assertIn("food_ticket_import_read_csv", helper)
        self.assertIn("traffic_import_identity", helper)
        self.assertIn("explicitSerialCounts", helper)
        self.assertIn("str_repeat('•'", helper)
        self.assertIn("random_bytes(16)", helper)
        self.assertIn("traffic_records_imported", helper)
        self.assertNotRegex(helper, r"(?is)->(?:exec|query)\s*\(\s*['\"]\s*(?:ALTER|CREATE)\s+TABLE")

    def test_help_and_chatbot_surface_the_hidden_workflow(self):
        guide = read("help-guide.php")
        chatbot = read("chatbot.php")
        self.assertIn("page=traffic-control&amp;tab=import", guide)
        self.assertIn('data-kw="ترددهای گذشته', guide)
        self.assertIn("تأییدکننده", guide)
        self.assertIn("خودرو", guide)
        self.assertIn("'ایمپورت'", chatbot)
        self.assertIn("'آپلود'", chatbot)


if __name__ == "__main__":
    unittest.main(verbosity=2)
