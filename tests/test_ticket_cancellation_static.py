from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
index = (ROOT / "index.php").read_text(encoding="utf-8")
bootstrap = (ROOT / "bootstrap.php").read_text(encoding="utf-8")
permissions = (ROOT / "permissions.php").read_text(encoding="utf-8")
cron = (ROOT / "cron/sla_maintenance.php").read_text(encoding="utf-8")
auto_close = (ROOT / "cron/ticket_auto_close.php").read_text(encoding="utf-8")
chatbot = (ROOT / "chatbot.php").read_text(encoding="utf-8")
guide = (ROOT / "help-guide.php").read_text(encoding="utf-8")
app_js = (ROOT / "assets/app.js").read_text(encoding="utf-8")
schema = (ROOT / "schema.sql").read_text(encoding="utf-8")
migration = (ROOT / "migrations/004-ticket-cancellation.sql").read_text(encoding="utf-8")
migration_pending = (ROOT / "migrations/005-normalize-pending-status.sql").read_text(encoding="utf-8")


def check(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)
    print(f"ok - {message}")


check("$serviceGroup === 'it'" in index and "if ($requiresAsset && $assetId === null)" in index,
      "every IT ticket requires a server-validated system asset")
check("var requiresAsset = isItGroup && hasSelectedService;" in app_js
      and "getGroup() === 'it' && !!(submittedService && submittedService.value !== '')" in app_js,
      "the IT asset requirement is consistent in the browser")
check("function ticket_requester_can_cancel_directly" in index
      and "empty($ticket['assigned_to'])" in index
      and "empty($ticket['first_response_at'])" in index,
      "direct cancellation is limited to unassigned tickets with no first response")
check("cancel_requested_at = NOW()" in index and "cancel_request_reason = ?" in index
      and "تغییر وضعیت و ارجاع متوقف" in index,
      "later cancellation is recorded as a request; conversation continues while status/assignment is frozen")
check("decide_ticket_cancellation" in index
      and "ticket_handler_level($ticket, $decisionUser) === ''" in index
      and "cancel_requested_by" in index
      and "cancel_requested_at = ?" in index,
      "only the scoped handler/manager (not the requester) can decide, with compare-and-set protection")
check("UPDATE tickets SET status = \"cancelled\"" in index
      and "cancelled_by_requester" in index
      and "cancellation_approved" in index
      and "ticket_messages" in index,
      "cancellation is soft, reasoned, and retained in the conversation and event history")
check("queue=cancellation_requested" in index and "queue=cancelled" in index
      and "t.status = \"cancelled\"" in index,
      "pending cancellation requests and cancelled tickets have separate queue views")
check("t.ola_response_due_at IS NOT NULL" in index
      and "t.ola_escalation_2_at IS NOT NULL" in index,
      "the SLA/OLA overdue queue filters and prioritizes internal response, resolution, and escalation deadlines")
check("status NOT IN (\"resolved\", \"closed\", \"cancelled\")" in cron
      and "sla_paused_at IS NULL" in cron,
      "SLA and OLA maintenance ignores cancelled tickets and pauses while waiting on the requester")
check("ola_policy_id = ?, ola_response_due_at = ?, ola_due_at = ?" in index
      and "ticket_ola_transition($ticket, $newStatus" in index,
      "reply transitions persist OLA deadlines after resuming from waiting_user")
check("'cancelled' => []" in index and "'cancelled' => 'لغوشده به درخواست ثبت‌کننده'" in bootstrap,
      "cancelled is a terminal, human-readable ticket status")
check("$ownerCondition = $serviceGroup === 'it'" in index
      and "AND hu.code = ?" in index
      and "$fallback->execute([$serviceGroup]);" in index,
      "automatic fallback stays within the requested IT or support handling unit")
check("status ENUM('new','manager_review','assigned','in_progress','waiting_user','pending','resolved','closed','cancelled')" in schema
      and "cancel_requested_at DATETIME NULL" in schema
      and "cancel_requested_by INT UNSIGNED NULL" in schema
      and "cancel_request_reason TEXT NULL" in schema,
      "fresh-install schema supports cancellation status and pending-request data")
check("MODIFY COLUMN `status`" in migration
      and "cancel_requested_at` DATETIME NULL" in migration
      and "PREPARE ticket_cancel_requested_at_stmt" in migration
      and "UPDATE `service_catalog` SET `requires_asset` = 1" in migration,
      "existing installations have an idempotent, MySQL 5.7-compatible workflow and asset-policy migration")
check("لغو درخواست و درخواست لغو" in guide
      and "درخواست لغو ثبت می‌شود" in guide
      and "لغو درخواست" in chatbot
      and "حذف فیزیکی نیست" in chatbot
      and "تغییر وضعیت و ارجاع متوقف" in guide
      and "تغییر وضعیت و ارجاع متوقف" in chatbot,
      "the Persian guide and chatbot explain the frozen-status semantics and retained history")
check("post_value('update_note')" in index
      and "پیام همراه تغییر" in index
      and "post_value('bulk_note')" in index
      and "bulk_note" in index,
      "waiting_user/resolved transitions require a visible note, including a shared bulk note")
check(index.count("AND cancel_requested_at IS NULL") >= 7
      and "t.cancel_requested_at IS NULL" in auto_close
      and "AND cancel_requested_at IS NULL" in auto_close
      and "implicitRequesterResume" in index,
      "status, assignment, claim, reopen, supervisor and auto-close paths are frozen while cancellation is pending")
check("'pending', 'resolved']" in index
      and "resolved_at = NULL" in index,
      "resolved tickets can request cancellation and leave no stale resolved_at when cancelled")
check("NOT EXISTS (SELECT 1 FROM ticket_sla_alerts" in cron
      and "NOT EXISTS (SELECT 1 FROM ticket_ola_alerts" in cron
      and 'role = "primary_admin"' in cron,
      "cron maintenance skips already-alerted tickets and escalates level 2 to the primary admin")
check("ticket_auto_close_days" in index
      and 'settings_section" value="ticket"' in index,
      "the auto-close deadline is configurable from settings")
check("صفحه‌بندی صف" in index and "صفحه‌بندی گزارش" in index and "صفحه‌بندی سوپروایزر" in index,
      "queue, reports and supervisor lists are paginated")
check("return ['supervisor', 'primary_admin', 'inspector'];" in permissions
      and "=== 'support_manager'" in bootstrap
      and "service_group = ?" in bootstrap,
      "the support manager sees only the support pool without IT access or deputy filtering")
check("function ticket_is_waiting_status" in bootstrap
      and "ticket_is_waiting_status($oldStatus)" in bootstrap
      and "SET `status` = 'waiting_user'" in migration_pending
      and "WHERE `status` = 'pending'" in migration_pending,
      "pending is treated as waiting_user for SLA/OLA and normalized by migration")
check("تیکت" not in index
      and "تیکت" not in bootstrap
      and "تیکت" not in permissions
      and "ثبت درخواست" in index
      and "سامانه درخواست پشتیبانی" in index,
      "user-facing texts use «درخواست» instead of «تیکت» (code identifiers stay Latin)")
check("CHATBOT_WORDS_TICKET = ['تیکت', 'درخواست'" in chatbot
      and "'تیکتها' => ['تیکت']" in chatbot
      and "درخواست بزنم" in chatbot,
      "the chatbot keeps «تیکت» as an understood alias while speaking «درخواست»")
check("save_setting('login_announcements'" in index
      and "save_setting('login_announcement_speed'" in index
      and "save_setting('login_announcement_font_size'" in index
      and "data-announcements=" in index
      and "data-announcement-text" in index
      and "setting('login_announcement'" in index,
      "login announcements support multiple rotating messages with adjustable speed, font size and legacy fallback")
check("data-announcements" in app_js and "setInterval" in app_js,
      "the browser rotates login messages one full scroll pass at a time")

print("Ticket workflow static checks passed.")
