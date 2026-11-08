from pathlib import Path

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
    assert "if (defined('REQUEST_CENTER_INDEPENDENT_ENTRY'))" in handler


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


def test_rejection_schema_is_migration_owned_not_runtime_ddl():
    migration = source("migrations/037-request-center-lifecycle-rejection.sql")
    fresh = source("install-empty-db.sql")
    schema = source("schema.sql")
    assert "'rejected'" in migration
    assert "rejected_at" in migration
    assert "requesting_unit_id" in migration
    assert "'rejected'" in fresh
    assert "'rejected'" in schema
    assert "rejected_at DATETIME NULL" in fresh
    assert "rejected_at DATETIME NULL" in schema
    assert "migrations/034-logging-escalation.sql" not in migration
    assert "migrations/036-food-meal-leftover-disposition.sql" not in migration
