from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]


def source(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def test_lifecycle_pages_are_routed_inside_request_center():
    router = source("request-center.php")
    for page in ("new-ticket", "ticket", "queue", "supervisor", "services", "reports", "analytics"):
        assert f"'{page}'" in router
    assert "Pages/detail.php" in router
    assert "Pages/create.php" in router
    assert "Pages/queue.php" in router
    assert "Pages/supervisor.php" in router
    assert "require __DIR__ . '/index.php';" in router  # compatibility bridge remains for non-RC routes
    legacy = source("index.php")
    assert "request-center.php?' . http_build_query($requestCenterQuery)" in legacy
    assert legacy.index("Early compatibility bridge") < legacy.index("ticket_number_ensure_schema()")


def test_attachment_and_report_endpoints_are_independent():
    router = source("request-center.php")
    assert "request_center_download_attachment($downloadUser)" in router
    assert "request_center_export_report($reportUser)" in router
    assert "$downloadSchemaProblems = (new RequestCenterRepository(db()))->schemaProblems();" in router
    assert "$reportSchemaProblems = (new RequestCenterRepository(db()))->schemaProblems();" in router
    legacy = source("index.php")
    assert "request-center.php?action=download_attachment" in legacy
    assert "request-center.php?action=export_report" in legacy
    assert legacy.index("download_attachment") < legacy.index("ticket_number_ensure_schema()")
    assert legacy.index("export_report") < legacy.index("ticket_number_ensure_schema()")
    for path in (
        "modules/request-center/Pages/detail.php",
        "modules/request-center/Pages/index.php",
    ):
        page = source(path)
        assert "request-center.php?action=download_attachment" in page
        assert "index.php?action=download_attachment" not in page
    # A system-home link is allowed; operational forms and data routes remain RC-owned.
    assert 'href="index.php">بازگشت به سامانه' in source("modules/request-center/Pages/admin.php")
    assert 'href="index.php">بازگشت به سامانه' in source("modules/request-center/Pages/supervisor-workspace.php")
    assert 'href="request-center.php?action=export_report' in source("modules/request-center/Pages/reports.php")


def test_lifecycle_actions_return_to_independent_workspaces():
    paths = (
        "modules/request-center/Actions/ticket-actions.php",
        "modules/request-center/Actions/supervisor-actions.php",
        "modules/request-center/Actions/bulk-actions.php",
        "modules/request-center/Actions/service-actions.php",
    )
    for path in paths:
        text = source(path)
        assert "redirect('index.php?page=ticket&id='" not in text
        assert "redirect('index.php?page=supervisor')" not in text
        assert "redirect('index.php?page=queue')" not in text
    assert "request-center.php?page=ticket&id=" in source(paths[0])
    assert "request-center.php?page=supervisor" in source(paths[1])
    assert "request-center.php?page=queue" in source(paths[2])
    assert "request-center.php?page=services" in source(paths[3])


def test_create_request_uses_one_time_session_token_and_read_only_schema_preflight():
    page = source("modules/request-center/Pages/create.php")
    handler = source("modules/request-center/Actions/ticket-actions.php")
    helper = source("modules/request-center/lib/functions.php")
    assert 'name="request_token"' in page
    assert "$_SESSION['rc_create_tokens'][$creationToken] = time();" in page
    assert "unset($_SESSION['rc_create_tokens'][$requestToken]);" in handler
    assert "request_center_assert_create_schema();" in page
    assert "request_center_assert_create_schema();" in handler
    assert "information_schema.COLUMNS" in helper
    assert "ticket_service_ensure_schema();" in handler  # retained only for the legacy bridge branch
    assert "request_center_ticket_number_next(" in handler
    assert "defined('REQUEST_CENTER_INDEPENDENT_ENTRY')" in handler
    assert ": ticket_number_next(" in handler
    assert "request_center_ticket_number_peek(" in page
    assert not re.search(r"(?<!request_center_)ticket_number_peek\(", page)
    assert "if (defined('REQUEST_CENTER_INDEPENDENT_ENTRY'))" in handler


def test_independent_runtime_has_no_schema_ensure_or_autofill_calls():
    router = source("request-center.php")
    post = source("modules/request-center/post-handler.php")
    assert "schemaProblems()" in router
    assert "schemaProblems()" in post
    assert "ticket_number_ensure_schema" not in post
    assert "ticket_number_autofill" not in post
    assert "org_ensure_schema()" not in post
    assert "org_ticket_schema_ensure()" not in post


def test_sla_ola_history_is_visible_to_staff_without_exposing_internal_fields_to_requesters():
    detail = source("modules/request-center/Pages/detail.php")
    assert "'sla_pause_minutes'" in detail
    assert "'ola_response_due_at'" in detail
    assert "$detailKey === 'sla_ola' && is_array($detailValue)" in detail
    assert "if ($canSeeInternal)" in detail
    requester_keys = detail[detail.index("$safeKeys = ['reason'"):detail.index("if ($staff) $safeKeys = array_merge")]
    assert "'ola_response_due_at'" not in requester_keys
    assert "'sla_ola'" not in requester_keys
    assert 'if (!$canSeeInternal)' in detail
    assert "e.event_type NOT IN ('internal_note', 'sla_alert', 'ola_alert')" in detail
    assert "'sla_alert' => 'هشدار SLA ثبت شد'" in detail
    assert "'ola_alert' => 'هشدار OLA ثبت شد'" in detail
    cron = source("cron/sla_maintenance.php")
    assert "'sla_alert'" in cron and "'ola_alert'" in cron
    assert "log_ticket_event((int) $ticket['id'], null, 'sla_alert'" in cron
    assert "log_ticket_event((int) $ticket['id'], null, 'ola_alert'" in cron


def test_internal_note_content_is_not_copied_to_asset_history_or_requester_notifications():
    action = source("modules/request-center/Actions/ticket-actions.php")
    internal_branch = action.index("} elseif ($internal) {")
    requester_notify = action.index("request_center_notify_user(", internal_branch)
    asset_history = action.index("record_asset_history(", requester_notify)
    assert "request_center_notify_ticket_staff" in action[internal_branch:requester_notify]
    assert "request_center_notify_user" not in action[internal_branch:requester_notify]
    assert "if (!$internal)" in action[asset_history - 250:asset_history + 100]


def test_sla_ola_alert_claims_are_audited_before_commit():
    cron = source("cron/sla_maintenance.php")
    assert cron.count("activity_log([") >= 2
    assert "'action_code' => 'ticket_sla_alert'" in cron
    assert "'action_code' => 'ticket_ola_alert'" in cron
    assert cron.count("Required audit persistence failed") >= 2
    assert "INSERT IGNORE INTO ticket_sla_alerts" in cron
    assert "INSERT IGNORE INTO ticket_ola_alerts" in cron


def test_automatic_closure_uses_required_system_audit_and_rc_notifications():
    cron = source("cron/ticket_auto_close.php")
    assert "activity_log([" in cron
    assert "'user_id' => null" in cron
    assert "'action_code' => 'ticket_auto_closed'" in cron
    assert "Required audit persistence failed" in cron
    assert "request_center_notify_user(" in cron
    assert "request_center_notify_ticket_staff(" in cron
    assert "notify_user(" not in cron.replace("request_center_notify_user(", "")
    assert "notify_ticket_staff(" not in cron.replace("request_center_notify_ticket_staff(", "")


def test_bulk_updates_skip_cancellation_pending_requests_without_aborting_batch():
    bulk = source("modules/request-center/Actions/bulk-actions.php")
    guard = bulk.index("if (!empty($bulkTicket['cancel_requested_at']))")
    transition = bulk.index("$workflow->transition($ticketId, $bulkOld")
    assert guard < transition
    assert "$bulkSkipped++;" in bulk[guard:transition]


def test_rejected_state_is_preflighted_and_excluded_from_terminal_workloads():
    repository = source("modules/request-center/RequestCenterRepository.php")
    assert "COLUMN_TYPE" in repository
    assert "Migration 037" in repository
    assert "str_contains($ticketStatusType, \"'rejected'\")" in repository
    assert "'notifications' => 'idempotency_key'" in repository
    assert "'ticket_events' => 'event_id'" in repository
    assert "'activity_logs' => 'event_id'" in repository
    sla_cron = source("cron/sla_maintenance.php")
    assert sla_cron.count('"resolved", "closed", "cancelled", "rejected"') >= 2
    sla_lib = source("lib/tickets-sla.php")
    assert "'rejected' => 'ردشده'" in sla_lib


def test_ticket_maintenance_jobs_reject_web_before_schema_setup():
    for path in ("cron/ticket_auto_close.php", "cron/sla_maintenance.php"):
        cron = source(path)
        assert cron.index("if (PHP_SAPI !== 'cli')") < cron.index("ticket_hot_schema_ensure()")


def test_supervisor_reopen_requires_a_reason_server_side():
    action = source("modules/request-center/Actions/supervisor-actions.php")
    start = action.index("if ($action === 'supervisor_reopen')")
    end = action.index("if ($action === 'rate_ticket')", start)
    block = action[start:end]
    assert "$note = post_value('supervisor_note');" in block
    assert "if ($note === '')" in block
    assert "برای بازگشایی رسمی" in block


def test_rejection_is_distinct_terminal_state_with_audited_supervisor_path():
    workflow = source("modules/request-center/WorkflowService.php")
    action = source("modules/request-center/Actions/supervisor-actions.php")
    page = source("modules/request-center/Pages/supervisor.php")
    detail = source("modules/request-center/Pages/detail.php")
    assert "'rejected'" in workflow
    assert "'supervisor_reject'" in workflow
    assert "first_response_at" in workflow
    assert "save_audit_required" in action
    assert "'ticket_rejected'" in action
    assert "decision\" value=\"reject" in page
    assert "رد شده است" in detail or "رد شده" in detail


def test_lifecycle_notifications_are_idempotent_and_failure_isolated():
    helpers = source("modules/request-center/lib/functions.php")
    assert "function request_center_notify_user" in helpers
    assert "hash('sha256'" in helpers
    assert "notify_user($userId, $type, $title, $body, $ticketId, $key)" in helpers
    assert "function request_center_notify_ticket_resolved" in helpers
    for path in (
        "modules/request-center/Actions/ticket-actions.php",
        "modules/request-center/Actions/supervisor-actions.php",
        "modules/request-center/Actions/bulk-actions.php",
    ):
        text = source(path)
        assert not re.search(r"(?<!request_center_)notify_ticket_parties\(", text)
        assert not re.search(r"(?<!request_center_)notify_ticket_staff\(", text)
        assert not re.search(r"(?<!request_center_)notify_ticket_resolved\(", text)


def test_rejection_schema_is_migration_owned_not_runtime_ddl():
    migration = source("migrations/037-request-center-lifecycle-rejection.sql")
    fresh = source("install-empty-db.sql")
    schema = source("schema.sql")
    assert "'rejected'" in migration
    assert "rejected_at" in migration
    assert "requesting_unit_id" in migration
    assert "CREATE TABLE IF NOT EXISTS ticket_number_seq" in migration
    assert "'rejected'" in fresh
    assert "'rejected'" in schema
    assert "rejected_at DATETIME NULL" in fresh
    assert "rejected_at DATETIME NULL" in schema
    assert "migrations/034-logging-escalation.sql" not in migration
    assert "migrations/036-food-meal-leftover-disposition.sql" not in migration
