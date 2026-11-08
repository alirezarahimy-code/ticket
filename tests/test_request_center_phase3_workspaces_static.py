from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def source(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def test_requester_workspace_exposes_owned_lifecycle_actions():
    detail = source("modules/request-center/Pages/detail.php")
    actions = source("modules/request-center/Actions/ticket-actions.php")
    assert 'name="action" value="cancel_ticket"' in detail
    assert 'name="action" value="reply"' in detail
    assert 'name="action" value="rate_ticket"' in detail
    assert 'name="action" value="reopen_ticket"' in detail
    for action in ("cancel_ticket", "reply", "rate_ticket", "reopen_ticket"):
        assert f"$action === '{action}'" in actions or f"$action === '{action}'" in source("modules/request-center/Actions/supervisor-actions.php")


def test_agent_workspace_exposes_claim_reply_internal_note_and_queue_actions():
    detail = source("modules/request-center/Pages/detail.php")
    queue = source("modules/request-center/Pages/queue.php")
    actions = source("modules/request-center/Actions/ticket-actions.php")
    assert 'name="action" value="claim_ticket"' in detail
    assert 'name="action" value="reply"' in detail
    assert 'name="is_internal"' in detail
    assert 'name="action" value="bulk_update_tickets"' in queue
    assert "$action === 'claim_ticket'" in actions
    assert "ticket_can_see_internal($ticket, $user)" in actions


def test_supervisor_workspace_exposes_assignment_and_decisions():
    page = source("modules/request-center/Pages/supervisor.php")
    actions = source("modules/request-center/Actions/supervisor-actions.php")
    assert 'name="action" value="supervisor_decision"' in page
    assert 'name="action" value="supervisor_reopen"' in page
    assert 'name="action" value="supervisor_assign_ticket"' in page or 'name="action" value="supervisor_assign_ticket"' in source("modules/request-center/Pages/detail.php")
    for action in ("supervisor_decision", "supervisor_reopen", "supervisor_assign_ticket"):
        assert f"$action === '{action}'" in actions
    assert "request_center_can_supervise_ticket" in actions


def test_admin_workspace_has_scoped_audit_and_separate_configuration_destinations():
    admin = source("modules/request-center/Pages/admin.php")
    services = source("modules/request-center/Pages/services.php")
    assert "ticket_scope($user)" in admin
    assert "FROM ticket_events e JOIN tickets t" in admin
    assert "ORDER BY e.created_at DESC, e.id DESC LIMIT 20" in admin
    # Audit view intentionally omits event details and message bodies.
    assert "e.details" not in admin
    assert "m.body" not in admin
    assert "request-center.php?page=services" in admin
    assert "id=\"ola-policies\"" in admin
    assert 'name="action" value="update_ola_policy"' in admin
    admin_action = source("modules/request-center/Actions/admin-actions.php")
    assert "user_is_primary_admin($admin)" in admin_action
    assert "SELECT id, name, department_id, priority" in admin_action
    assert "save_audit_required" in admin_action
    assert "'update_ola_policy'" in source("modules/request-center/post-handler.php")
    assert "name=\"action\" value=\"add_service\"" in services
