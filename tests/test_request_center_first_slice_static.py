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
        self.assertIn("$schemaProblems = $repo->schemaProblems();", entry)
        self.assertIn("public function schemaProblems()", read("modules/request-center/RequestCenterRepository.php"))

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

    def test_transition_policy_is_owned_by_module_service(self):
        service = read("modules/request-center/WorkflowService.php")
        index = read("index.php")
        self.assertIn("public static function allowedTransitions(", service)
        self.assertIn("public function transition(", service)
        self.assertIn("FOR UPDATE", service)
        self.assertIn("RequestCenterWorkflowService::allowedTransitions($from)", index)

    def test_migrated_status_writes_use_the_central_workflow_service(self):
        paths = (
            "actions/ticket-actions.php",
            "actions/supervisor-actions.php",
            "actions/bulk-actions.php",
            "cron/ticket_auto_close.php",
        )
        for path in paths:
            source = read(path)
            self.assertNotRegex(
                source,
                r"UPDATE\s+tickets\s+SET\s+status\s*=",
                f"direct ticket status write remains in {path}",
            )
            self.assertIn("WorkflowService", source, f"workflow service is not loaded/used in {path}")
        self.assertIn("require_once __DIR__ . '/modules/request-center/WorkflowService.php';", read("index.php"))
        self.assertIn("require_once dirname(__DIR__) . '/modules/request-center/WorkflowService.php';", read("cron/ticket_auto_close.php"))

    def test_user_supplied_text_is_escaped_and_attachment_download_is_delegated(self):
        entry = read("request-center.php")
        self.assertIn("htmlspecialchars(", entry)
        self.assertIn("index.php?action=download_attachment", entry)
        repo = read("modules/request-center/RequestCenterRepository.php")
        self.assertNotIn("stored_name", repo)

    def test_workspace_views_are_permission_gated_and_scope_bound(self):
        entry = read("request-center.php")
        repo = read("modules/request-center/RequestCenterRepository.php")
        self.assertIn("user_can($user, 'supervisor.panel')", entry)
        self.assertIn("user_is_primary_admin($user)", entry)
        self.assertIn("$filters['requester_id'] = (int) $user['id'];", entry)
        self.assertIn("$filters['assigned_to'] = (int) $user['id'];", entry)
        self.assertIn("$workspaceUrl .= '&agent_view='", entry)
        self.assertIn('href="<?= $escape($workspaceUrl) ?>"', entry)
        self.assertIn("user_can($user, 'queue.view')", entry)
        self.assertIn("$clauses[] = 't.assigned_to = ?';", repo)
        self.assertIn("ticket_scope($user, 't')", repo)

    def test_not_found_and_detail_navigation_preserve_workspace(self):
        entry = read("request-center.php")
        self.assertIn('<a class="button secondary" href="<?= $escape($workspaceUrl) ?>">بازگشت به فهرست</a>', entry)
        self.assertIn('<a class="button secondary" href="<?= $escape($workspaceUrl) ?>">فهرست درخواست‌ها</a>', entry)
        self.assertNotIn('href="<?= $escape($workspaceUrl) ?>"', entry.split("echo '<section class=\"card empty\"")[0] if "echo '<section class=\"card empty\"" in entry else "")

    def test_legacy_workflow_is_not_removed_by_first_slice(self):
        action = read("actions/ticket-actions.php")
        self.assertIn("if ($action === 'create_ticket')", action)
        self.assertIn("if ($action === 'reply')", action)
        self.assertIn("if ($action === 'update_ticket')", action)
        self.assertIn("if ($action === 'cancel_ticket')", action)


if __name__ == "__main__":
    unittest.main()
