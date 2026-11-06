from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


class RequestCenterFirstSliceTests(unittest.TestCase):
    def test_independent_entrypoint_bootstraps_and_authenticates(self):
        entry = read("request-center.php")
        self.assertIn("require __DIR__ . '/bootstrap.php';", entry)
        self.assertIn("$user = require_login();", entry)
        self.assertIn("RequestCenterRepository", entry)

    def test_repository_uses_existing_visibility_contracts(self):
        repo = read("modules/request-center/RequestCenterRepository.php")
        self.assertIn("ticket_scope($user, 't')", repo)
        self.assertIn("ticket_user_can_view($ticket, $user)", repo)
        self.assertIn("ticket_can_see_internal", read("request-center.php"))

    def test_internal_messages_are_filtered_in_sql(self):
        repo = read("modules/request-center/RequestCenterRepository.php")
        self.assertIn("COALESCE(m.is_internal, 0) = 0", repo)
        self.assertIn("if (!$includeInternal)", repo)

    def test_read_slice_has_no_schema_or_write_operations(self):
        files = [
            read("request-center.php"),
            read("modules/request-center/RequestCenterRepository.php"),
        ]
        combined = "\n".join(files).lower()
        forbidden = [
            "create table", "alter table", "drop table", "insert into",
            "update tickets", "delete from", "begintransaction",
        ]
        for marker in forbidden:
            with self.subTest(marker=marker):
                self.assertNotIn(marker, combined)

    def test_user_supplied_text_is_escaped_and_attachment_download_is_delegated(self):
        entry = read("request-center.php")
        self.assertIn("htmlspecialchars(", entry)
        self.assertIn("index.php?action=download_attachment", entry)
        repo = read("modules/request-center/RequestCenterRepository.php")
        self.assertNotIn("stored_name", repo)

    def test_legacy_workflow_is_not_removed_by_first_slice(self):
        action = read("actions/ticket-actions.php")
        self.assertIn("if ($action === 'create_ticket')", action)
        self.assertIn("if ($action === 'reply')", action)
        self.assertIn("if ($action === 'update_ticket')", action)
        self.assertIn("if ($action === 'cancel_ticket')", action)


if __name__ == "__main__":
    unittest.main()
