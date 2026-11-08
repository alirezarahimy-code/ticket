<?php

declare(strict_types=1);

/**
 * actions/supervisor-actions.php — ۳ اکشن POST تصمیم‌های سرپرست و امتیازدهی (فاز ۲ بازسازی، برش ۶ — نسخهٔ ۱.۶۲.۱۷).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   supervisor_decision       تصمیم سرپرست دربارهٔ درخواست (تأیید/رد/ارجاع)
 *   supervisor_reopen         بازگشایی درخواست بسته‌شده توسط سرپرست
 *   rate_ticket               ثبت امتیاز رضایت درخواست‌کننده پس از انجام کار
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده؛ تنها مورد
 * function داخل بلوک، فراخوانی function_exists است (تعریف تابع نیست — بی‌خطر).
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: تصمیم‌های سرپرست (غذا و درخواست‌ها) ─────
        if ($action === 'supervisor_decision') {
            $supervisor = require_permission('supervisor.panel');
            if (!user_can($supervisor, 'supervisor.decide')) {
                throw new RuntimeException('دسترسی تأیید یا برگشت درخواست برای نقش شما فعال نیست.');
            }
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $decision = valid_choice(post_value('decision'), ['approve', 'rework', 'reject'], 'rework');
            $ticket = fetch_ticket($ticketId);
            $isPreServiceRejection = $decision === 'reject';
            $rejectionEligible = $ticket
                && in_array((string) $ticket['status'], ['new', 'manager_review', 'assigned'], true)
                && empty($ticket['first_response_at'])
                && empty($ticket['resolved_at']);
            $validDecisionState = $ticket && ($isPreServiceRejection
                ? $rejectionEligible
                : (string) $ticket['status'] === 'resolved');
            if (!$ticket || !can_view_ticket($ticket, $supervisor)
                || ticket_user_is_requester($ticket, $supervisor) || !$validDecisionState) {
                throw new RuntimeException($isPreServiceRejection
                    ? 'رد درخواست فقط پیش از شروع رسیدگی و در مرحلهٔ ارزیابی اولیه مجاز است.'
                    : 'فقط درخواست حل‌شده و بسته‌نشده قابل تأیید یا برگشت برای اصلاح است.');
            }
            if (!empty($ticket['cancel_requested_at'])) {
                throw new RuntimeException('لغو این درخواست در انتظار تصمیم مسئول است؛ ابتدا آن را تأیید یا رد کنید.');
            }
            $note = post_value('supervisor_note');
            if (mb_strlen($note, 'UTF-8') > 500) {
                throw new RuntimeException('دلیل تصمیم سوپروایزر نباید بیشتر از ۵۰۰ نویسه باشد.');
            }
            if ($isPreServiceRejection && $note === '') {
                throw new RuntimeException('برای رد درخواست، دلیل روشن و قابل مشاهده برای درخواست‌کننده الزامی است.');
            }
            if ($decision === 'approve' && $note === '') {
                throw new RuntimeException('برای تأیید نهایی و بستن درخواست، دلیل تصمیم را ثبت کنید.');
            }
            if ($decision === 'approve' && !ticket_rating_is_current($ticket)) {
                throw new RuntimeException('بستن نهایی پس از ثبت امتیاز رضایت کاربر امکان‌پذیر است.');
            }
            if ($decision === 'rework' && $note === '') {
                throw new RuntimeException('دلیل برگشت درخواست برای اصلاح را بنویسید.');
            }
            db()->beginTransaction();
            $decisionLock = db()->prepare('SELECT id FROM tickets WHERE id = ? FOR UPDATE');
            $decisionLock->execute([$ticketId]);
            $ticket = fetch_ticket($ticketId);
            $rejectionEligible = $ticket
                && in_array((string) $ticket['status'], ['new', 'manager_review', 'assigned'], true)
                && empty($ticket['first_response_at'])
                && empty($ticket['resolved_at']);
            $validDecisionState = $ticket && ($isPreServiceRejection
                ? $rejectionEligible
                : (string) $ticket['status'] === 'resolved');
            if (!$ticket || !can_view_ticket($ticket, $supervisor) || ticket_user_is_requester($ticket, $supervisor)
                || !$validDecisionState || !empty($ticket['cancel_requested_at'])
                || ($decision === 'approve' && !ticket_rating_is_current($ticket))) {
                throw new RuntimeException('وضعیت درخواست یا مجوز تصمیم هم‌زمان تغییر کرد؛ صفحه را تازه کنید.');
            }
            if ($isPreServiceRejection) {
                $workflow = new RequestCenterWorkflowService(db());
                $rejected = $workflow->transition($ticketId, (string) $ticket['status'], 'rejected', [
                    'rejected_at' => RequestCenterSqlExpression::now(),
                    'supervisor_id' => (int) $supervisor['id'],
                    'supervisor_note' => $note,
                ], 'supervisor_reject', [
                    'cancel_requested_at' => null,
                    'first_response_at' => null,
                    'resolved_at' => null,
                ]);
                if (!$rejected) {
                    throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد. صفحه را تازه کنید.');
                }
                db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')
                    ->execute([$ticketId, (int) $supervisor['id'], 'درخواست توسط سوپروایزر رد شد: ' . $note]);
                save_audit_required((int) $supervisor['id'], 'ticket_rejected', $ticketId, [
                    'reason' => $note,
                    'from_status' => (string) $ticket['status'],
                    'to_status' => 'rejected',
                    'first_response_at' => $ticket['first_response_at'] ?? null,
                ]);
                log_ticket_event($ticketId, (int) $supervisor['id'], 'ticket_rejected', (string) $ticket['status'], 'rejected', [
                    'reason' => $note,
                ]);
                notify_ticket_parties(
                    $ticketId,
                    'ticket_rejected',
                    'درخواست شما رد شد',
                    'درخواست ' . ticket_number($ticketId) . ' پس از ارزیابی اولیه رد شد. دلیل: ' . $note,
                    (int) $supervisor['id']
                );
                record_asset_history((int) ($ticket['asset_id'] ?? 0), 'ticket_rejected', 'رد درخواست ' . ticket_number($ticketId), (int) $supervisor['id'], $ticketId, $note);
                flash('info', 'درخواست با ثبت دلیل و اطلاع‌رسانی به درخواست‌کننده رد شد.');
            } elseif ($decision === 'approve') {
                $workflow = new RequestCenterWorkflowService(db());
                $closed = $workflow->transition($ticketId, 'resolved', 'closed', [
                    'closed_at' => RequestCenterSqlExpression::coalesceNow(),
                    'supervisor_id' => (int) $supervisor['id'],
                    'supervisor_approved_at' => RequestCenterSqlExpression::now(),
                    'supervisor_note' => $note,
                ], 'supervisor_close', ['cancel_requested_at' => null]);
                if (!$closed) {
                    throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد. صفحه را تازه کنید.');
                }
                save_audit_required((int) $supervisor['id'], 'ticket_supervisor_approved', $ticketId, [
                    'reason' => $note,
                    'from_status' => 'resolved',
                    'to_status' => 'closed',
                ]);
                log_ticket_event($ticketId, (int) $supervisor['id'], 'supervisor_approved', $ticket['status'], 'closed', [
                    'reason' => $note,
                    'note' => $note,
                ]);
                notify_ticket_parties($ticketId, 'ticket_closed', 'درخواست بسته شد', 'درخواست ' . ticket_number($ticketId) . ' توسط سوپروایزر تأیید و بسته شد. دلیل: ' . $note, (int) $supervisor['id']);
                record_asset_history((int) ($ticket['asset_id'] ?? 0), 'ticket_closed', 'تأیید و بستن درخواست ' . ticket_number($ticketId), (int) $supervisor['id'], $ticketId, $note);
                flash('success', 'انجام کار تأیید شد و درخواست بسته شد.');
            } else {
                $reworkStatus = !empty($ticket['assigned_to']) ? 'in_progress' : 'manager_review';
                $reworkDue = business_due_at(priority_sla_minutes((string) $ticket['priority']))->format('Y-m-d H:i:s');
                $reworkOla = ola_deadlines((int) ($ticket['department_id'] ?? 0), (string) $ticket['priority'], (new DateTimeImmutable('now', new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('Y-m-d H:i:s'));
                $workflow = new RequestCenterWorkflowService(db());
                $reworked = $workflow->transition($ticketId, 'resolved', $reworkStatus, [
                    'resolved_at' => null,
                    'due_at' => $reworkDue,
                    'ola_policy_id' => $reworkOla['policy_id'],
                    'ola_response_due_at' => $reworkOla['response_due_at'],
                    'ola_due_at' => $reworkOla['due_at'],
                    'ola_escalation_1_at' => $reworkOla['escalation_1_at'],
                    'ola_escalation_2_at' => $reworkOla['escalation_2_at'],
                    'sla_paused_at' => null,
                    'sla_pause_minutes' => 0,
                    'supervisor_id' => (int) $supervisor['id'],
                    'supervisor_note' => $note,
                ], 'supervisor_rework', ['cancel_requested_at' => null]);
                if (!$reworked) {
                    throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد. صفحه را تازه کنید.');
                }
                db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')->execute([$ticketId, $supervisor['id'], 'برگشت برای اصلاح توسط سوپروایزر: ' . $note]);
                save_audit_required((int) $supervisor['id'], 'ticket_supervisor_rework', $ticketId, [
                    'reason' => $note,
                    'from_status' => 'resolved',
                    'to_status' => $reworkStatus,
                ]);
                log_ticket_event($ticketId, (int) $supervisor['id'], 'supervisor_rework', $ticket['status'], $reworkStatus, [
                    'reason' => $note,
                    'note' => $note,
                ]);
                notify_ticket_parties($ticketId, 'supervisor_rework', 'درخواست برای اصلاح برگشت خورد', 'درخواست ' . ticket_number($ticketId) . ' توسط سوپروایزر برای اصلاح برگشت داده شد: ' . $note, (int) $supervisor['id']);
                flash('info', 'درخواست برای اصلاح و ادامه کار برگشت داده شد.');
            }
            db_commit('index.php');
            redirect('index.php?page=supervisor');
        }

        if ($action === 'supervisor_reopen') {
            $supervisor = require_permission('supervisor.panel');
            if (!user_can($supervisor, 'supervisor.reopen')) {
                throw new RuntimeException('دسترسی بازگشایی درخواست برای نقش شما فعال نیست.');
            }
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $ticket = fetch_ticket($ticketId);
            if (!$ticket || !can_view_ticket($ticket, $supervisor) || $ticket['status'] !== 'closed') {
                throw new RuntimeException('فقط درخواست بسته‌شده قابل بازگشایی رسمی است.');
            }
            $note = post_value('supervisor_note');
            if ($note === '' || mb_strlen($note, 'UTF-8') > 1000) {
                throw new RuntimeException('برای بازگشایی رسمی، دلیل روشن حداکثر ۱۰۰۰ نویسه‌ای الزامی است.');
            }
            $reopenStatus = !empty($ticket['assigned_to']) ? 'in_progress' : 'manager_review';
            $dueAt = business_due_at(priority_sla_minutes((string) $ticket['priority']))->format('Y-m-d H:i:s');
            $reopenOla = ola_deadlines((int) ($ticket['department_id'] ?? 0), (string) $ticket['priority'], (new DateTimeImmutable('now', new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('Y-m-d H:i:s'));
            $hadStaleCancel = !empty($ticket['cancel_requested_at']);
            db()->beginTransaction();
            $workflow = new RequestCenterWorkflowService(db());
            $reopened = $workflow->transition($ticketId, 'closed', $reopenStatus, [
                'closed_at' => null,
                'resolved_at' => null,
                'supervisor_approved_at' => null,
                'supervisor_note' => $note,
                'due_at' => $dueAt,
                'ola_policy_id' => $reopenOla['policy_id'],
                'ola_response_due_at' => $reopenOla['response_due_at'],
                'ola_due_at' => $reopenOla['due_at'],
                'ola_escalation_1_at' => $reopenOla['escalation_1_at'],
                'ola_escalation_2_at' => $reopenOla['escalation_2_at'],
                'sla_paused_at' => null,
                'sla_pause_minutes' => 0,
                'cancel_requested_at' => null,
                'cancel_requested_by' => null,
                'cancel_request_reason' => null,
            ], 'supervisor_reopen', [
                'requester_id' => $ticket['requester_id'],
                'assigned_to' => $ticket['assigned_to'] ?? null,
                'priority' => $ticket['priority'],
                'department_id' => $ticket['department_id'] ?? null,
                'cancel_requested_at' => $ticket['cancel_requested_at'] ?? null,
            ]);
            if (!$reopened) {
                throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد. صفحه را تازه کنید.');
            }
            log_ticket_event($ticketId, (int) $supervisor['id'], 'supervisor_reopened', 'closed', $reopenStatus, ['reason' => $note, 'note' => $note]);
            if ($hadStaleCancel) {
                log_ticket_event($ticketId, (int) $supervisor['id'], 'cancellation_rejected', 'closed', $reopenStatus, ['note' => 'پاک‌سازی خودکار درخواست لغو قدیمی هنگام بازگشایی رسمی']);
            }
            save_audit_required((int) $supervisor['id'], 'ticket_reopened_by_supervisor', $ticketId, [
                'reason' => $note,
                'from_status' => 'closed',
                'to_status' => $reopenStatus,
            ]);
            notify_ticket_parties($ticketId, 'ticket_reopened', 'درخواست بازگشایی شد', 'درخواست ' . ticket_number($ticketId) . ' با تصمیم سوپروایزر بازگشایی شد. دلیل: ' . $note, (int) $supervisor['id']);
            db_commit('index.php');
            flash('info', $hadStaleCancel ? 'درخواست بازگشایی شد؛ درخواست لغو قدیمی هم پاک‌سازی شد.' : 'درخواست با تصمیم سوپروایزر بازگشایی شد.');
            redirect('index.php?page=supervisor');
        }


        // ───── گروه اکشن: امتیازدهی درخواست ─────
        if ($action === 'rate_ticket') {
            if (!user_can($user, 'ticket.rate')) {
                throw new RuntimeException('دسترسی امتیازدهی به درخواست برای نقش شما مجاز نیست.');
            }
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $ticket = fetch_ticket($ticketId);
            $score = (int) ($_POST['score'] ?? 0);
            if (!$ticket || (int) $ticket['requester_id'] !== (int) $user['id']) {
                throw new RuntimeException('فقط ثبت‌کننده درخواست می‌تواند امتیاز ثبت کند.');
            }
            if (!in_array($ticket['status'], ['resolved', 'closed'], true)) {
                throw new RuntimeException('امتیاز فقط پس از حل‌شدن درخواست قابل ثبت است.');
            }
            if (ticket_rating_is_current($ticket)) {
                throw new RuntimeException('برای این درخواست قبلاً امتیاز ثبت شده است.');
            }
            if ($score < 1 || $score > 5) {
                throw new RuntimeException('امتیاز باید بین ۱ تا ۵ باشد.');
            }
            $comment = post_value('rating_comment');
            db()->beginTransaction();
            // Lock the ticket first, then re-read the rating and status: reopening and
            // automatic closure use the same lock and cannot race this decision.
            $ratingLock = db()->prepare('SELECT id FROM tickets WHERE id = ? FOR UPDATE');
            $ratingLock->execute([$ticketId]);
            $ticket = fetch_ticket($ticketId);
            if (!$ticket || (int) $ticket['requester_id'] !== (int) $user['id']
                || !in_array($ticket['status'], ['resolved', 'closed'], true)
                || ticket_rating_is_current($ticket)) {
                throw new RuntimeException('وضعیت درخواست یا امتیاز آن هم‌زمان تغییر کرد؛ صفحه را تازه کنید.');
            }
            // اگر درخواست پس از امتیاز قبلی دوباره باز و حل شده باشد، امتیاز جدید جایگزین می‌شود.
            db()->prepare('INSERT INTO ticket_ratings (ticket_id, agent_id, requester_id, score, comment) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE agent_id = VALUES(agent_id), score = VALUES(score), comment = VALUES(comment), created_at = NOW()')->execute([$ticketId, $ticket['assigned_to'] ?: null, $user['id'], $score, $comment !== '' ? $comment : null]);
            save_audit((int) $user['id'], 'ticket_rated', $ticketId, ['score' => $score]);
            log_ticket_event($ticketId, (int) $user['id'], 'rated', (string) $ticket['status'], (string) $ticket['status'], ['score' => $score]);
            notify_ticket_staff($ticketId, 'ticket_rated', 'رضایت کاربر ثبت شد', 'برای درخواست ' . ticket_number($ticketId) . ' امتیاز ' . $score . ' از ۵ ثبت شد.' . ($ticket['status'] === 'resolved' ? ' درخواست آمادهٔ تأیید نهایی است.' : ''), (int) $user['id']);
            record_asset_history((int) ($ticket['asset_id'] ?? 0), 'ticket_rated', 'ثبت رضایت برای درخواست ' . ticket_number($ticketId), (int) $user['id'], $ticketId, 'امتیاز: ' . $score . ' از ۵');
            db_commit('index.php');
            flash('success', 'امتیاز رضایت شما با موفقیت ثبت شد.');
            redirect('index.php?page=ticket&id=' . $ticketId);
        }
