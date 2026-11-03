from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


def source(path):
    return (ROOT / path).read_text(encoding='utf-8')


def section(text, begin, end):
    return text[text.index(begin):text.index(end, text.index(begin))]


class TicketWorkflowSafetyTests(unittest.TestCase):
    def test_notification_candidates_are_checked_for_actual_visibility(self):
        text = source('lib/notify-users.php')
        helper = section(text, 'function ticket_notification_recipients(', 'function notify_ticket_parties(')
        self.assertIn('u.is_active = 1', helper)
        self.assertIn('ticket_user_can_view($ticket, $recipient, $permissionCheck', helper)
        self.assertEqual(text.count('ticket_notification_recipients($ticket, array_merge('), 2)
        self.assertIn('t.requester_id, t.assigned_to, t.department_id, t.service_group', text)
        self.assertIn('function ticket_unassigned_notify_candidates(', text)
        self.assertIn('"agent", "manager", "support_manager", "primary_admin"', text)
        self.assertEqual(text.count('= ticket_unassigned_notify_candidates();'), 2)

    def test_assignment_checks_existing_assignee_and_role_department(self):
        action = source('actions/ticket-actions.php')
        update = section(action, "if ($action === 'update_ticket')", "flash('success', 'تغییرات درخواست ذخیره شد.')")
        self.assertIn('if ($assignee) {', update)
        self.assertIn('$assigneeDepartmentOk', update)
        self.assertIn("in_array($assigneeRole, ['agent', 'manager'], true)", update)
        self.assertIn("$lockedTicket['assigned_to']", update)
        self.assertIn("$lockedTicket['priority']", update)
        chooser = section(source('index.php'), 'function choose_handling_agent(', 'function ticket_allowed_transitions(')
        self.assertIn('u.role IN ("agent", "manager") AND hu.code = ?', chooser)
        self.assertEqual(chooser.count("ticket_assignee_is_eligible($routingTicket, $candidate"), 2)
        self.assertIn("ticket_assignee_is_eligible($ticket, $assigneeRow", update)

    def test_requester_and_nonhandler_reply_guards(self):
        action = source('actions/ticket-actions.php')
        reply = section(action, "if ($action === 'reply')", "if ($action === 'update_ticket')")
        self.assertIn('ticket_user_is_requester($ticket, $user)', reply)
        self.assertIn('ticket_reply_starts_work($ticket, $user)', reply)
        self.assertIn("$isStaffUser && !$asRequester && ticket_handler_level($ticket, $user) !== ''", reply)
        self.assertIn("if (!empty($_POST['is_internal']) && (!$isStaffUser || $asRequester))", reply)
        self.assertIn("$lockedReply['assigned_to']", reply)
        self.assertIn('$requesterView = $isRequester;', source('index.php'))
        self.assertIn("!user_can($user, 'ticket.edit') || ticket_handler_level($ticket, $user) === ''", reply)
        self.assertIn("$requiresRating && !user_can($user, 'ticket.rate')", source('index.php'))

    def test_concurrent_rating_and_auto_close_revalidate_under_lock(self):
        actions = source('actions/supervisor-actions.php')
        rating = actions[actions.index("if ($action === 'rate_ticket')"):]
        self.assertLess(rating.index('FOR UPDATE'), rating.index('$ticket = fetch_ticket($ticketId);', rating.index('FOR UPDATE')))
        self.assertIn('ticket_rating_is_current($ticket)', rating[rating.index('FOR UPDATE'):])
        decision = section(actions, "if ($action === 'supervisor_decision')", "if ($action === 'supervisor_reopen')")
        self.assertIn('ticket_rating_is_current($ticket)', decision[decision.index('FOR UPDATE'):])
        cron = source('cron/ticket_auto_close.php')
        self.assertLess(cron.index('FOR UPDATE'), cron.index("SELECT score, created_at FROM ticket_ratings"))
        self.assertLess(cron.index("SELECT score, created_at FROM ticket_ratings"), cron.index('$closeQuery->execute'))
        self.assertIn('COALESCE(resolved_at, updated_at) <= DATE_SUB', cron)

    def test_claim_uses_full_assignee_eligibility(self):
        action = source('actions/ticket-actions.php')
        claim = section(action, "if ($action === 'claim_ticket')", "if ($action === 'reopen_ticket')")
        self.assertIn("ticket_assignee_is_eligible($ticket, $claimUser", claim)
        self.assertIn("ticket_assignee_is_eligible($ticket, $user", source('index.php'))

    def test_supervisor_actions_check_ticket_visibility(self):
        actions = source('actions/supervisor-actions.php')
        decision = section(actions, "if ($action === 'supervisor_decision')", "if ($action === 'supervisor_reopen')")
        reopen = section(actions, "if ($action === 'supervisor_reopen')", "if ($action === 'rate_ticket')")
        self.assertGreaterEqual(decision.count('can_view_ticket($ticket, $supervisor)'), 2)
        self.assertIn('can_view_ticket($ticket, $supervisor)', reopen)

    def test_autoclose_candidates_do_not_starve_on_current_same_second_ratings(self):
        cron = source('cron/ticket_auto_close.php')
        self.assertIn('tr.created_at = t.resolved_at', cron)
        self.assertIn('SELECT MAX(e.id)', cron)
        self.assertIn('e.event_type = "rated"', cron)
        self.assertIn('e.to_status = "resolved"', cron)
        self.assertIn('ticket_rating_is_current($current)', cron)

    def test_reopen_sla_cycle_is_used_on_priority_change(self):
        sla = source('lib/tickets-sla.php')
        self.assertIn('function ticket_sla_cycle_start(', sla)
        self.assertIn("new DateTimeImmutable((string) $ticket['due_at']", sla)
        self.assertIn('ola_deadlines($departmentId, $priority, ticket_sla_cycle_start($ticket))', sla)

    def test_direct_cancel_and_bulk_defend_stale_assignment(self):
        action = source('actions/ticket-actions.php')
        direct = section(action, 'if ($directCancellation)', '} else {')
        self.assertIn('assigned_to IS NULL AND first_response_at IS NULL AND resolved_at IS NULL', direct)
        bulk = source('actions/bulk-actions.php')
        self.assertIn("$lockedBulkTicket['assigned_to']", bulk)
        self.assertIn("$lockedBulkTicket['priority']", bulk)


if __name__ == '__main__':
    unittest.main()
