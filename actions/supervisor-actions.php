<?php

declare(strict_types=1);

/**
 * actions/supervisor-actions.php — ۴ اکشن POST تصمیم‌های سرپرست و امتیازدهی (فاز ۲ بازسازی، برش ۶ — نسخهٔ ۱.۶۲.۱۷).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   food_order_cancel_decide  رسیدگی سرپرست به درخواست لغو غذای ثبت‌شده
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
        if ($action === 'food_order_cancel_decide') {
            $reviewer = require_login();
            if (!function_exists('food_order_cancel_request_decide')) {
                throw new RuntimeException('ماژول سفارش غذا در دسترس نیست.');
            }
            $result = food_order_cancel_request_decide(
                (int) ($_POST['request_id'] ?? 0),
                (string) ($_POST['decision'] ?? ''),
                $reviewer,
                (string) ($_POST['note'] ?? '')
            );
            flash('success', (string) ($result['message'] ?? 'انجام شد.'));
            redirect('index.php?page=dashboard#food-cancel-requests');
        }
        if ($action === 'supervisor_decision') {
            $supervisor = require_permission('supervisor.panel');
            if (!user_can($supervisor, 'supervisor.decide')) {
                throw new RuntimeException('دسترسی تأیید یا برگشت درخواست برای نقش شما فعال نیست.');
            }
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $decision = valid_choice(post_value('decision'), ['approve', 'rework'], 'rework');
            $ticket = fetch_ticket($ticketId);
            if (!$ticket || !can_view_ticket($ticket, $supervisor) || $ticket['status'] !== 'resolved') {
                throw new RuntimeException('فقط درخواست حل‌شده و بسته‌نشده قابل تأیید یا برگشت برای اصلاح است.');
            }
            if (!empty($ticket['cancel_requested_at'])) {
                throw new RuntimeException('لغو این درخواست در انتظار تصمیم مسئول است؛ ابتدا آن را تأیید یا رد کنید.');
            }
            $note = post_value('supervisor_note');
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
            if (!$ticket || !can_view_ticket($ticket, $supervisor) || $ticket['status'] !== 'resolved' || !empty($ticket['cancel_requested_at'])
                || ($decision === 'approve' && !ticket_rating_is_current($ticket))) {
                throw new RuntimeException('وضعیت درخواست یا امتیاز آن هم‌زمان تغییر کرد؛ صفحه را تازه کنید.');
            }
            if ($decision === 'approve') {
                $closeQuery = db()->prepare('UPDATE tickets SET status = "closed", closed_at = COALESCE(closed_at, NOW()), supervisor_id = ?, supervisor_approved_at = NOW(), supervisor_note = ?, updated_at = NOW() WHERE id = ? AND status = "resolved" AND cancel_requested_at IS NULL');
                $closeQuery->execute([$supervisor['id'], $note, $ticketId]);
                if ($closeQuery->rowCount() < 1) {
                    throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد. صفحه را تازه کنید.');
                }
                log_ticket_event($ticketId, (int) $supervisor['id'], 'supervisor_approved', $ticket['status'], 'closed', ['note' => $note]);
                notify_ticket_parties($ticketId, 'ticket_closed', 'درخواست بسته شد', 'درخواست ' . ticket_number($ticketId) . ' توسط سوپروایزر تایید و بسته شد.', (int) $supervisor['id']);
                record_asset_history((int) ($ticket['asset_id'] ?? 0), 'ticket_closed', 'تأیید و بستن درخواست ' . ticket_number($ticketId), (int) $supervisor['id'], $ticketId, $note);
                flash('success', 'انجام کار تأیید شد و درخواست بسته شد.');
            } else {
                $reworkStatus = !empty($ticket['assigned_to']) ? 'in_progress' : 'manager_review';
                $reworkDue = business_due_at(priority_sla_minutes((string) $ticket['priority']))->format('Y-m-d H:i:s');
                $reworkOla = ola_deadlines((int) ($ticket['department_id'] ?? 0), (string) $ticket['priority'], (new DateTimeImmutable('now', new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('Y-m-d H:i:s'));
                $reworkQuery = db()->prepare('UPDATE tickets SET status = ?, resolved_at = NULL, due_at = ?, ola_policy_id = ?, ola_response_due_at = ?, ola_due_at = ?, ola_escalation_1_at = ?, ola_escalation_2_at = ?, sla_paused_at = NULL, sla_pause_minutes = 0, supervisor_id = ?, supervisor_note = ?, updated_at = NOW() WHERE id = ? AND status = "resolved" AND cancel_requested_at IS NULL');
                $reworkQuery->execute([$reworkStatus, $reworkDue, $reworkOla['policy_id'], $reworkOla['response_due_at'], $reworkOla['due_at'], $reworkOla['escalation_1_at'], $reworkOla['escalation_2_at'], $supervisor['id'], $note, $ticketId]);
                if ($reworkQuery->rowCount() < 1) {
                    throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد. صفحه را تازه کنید.');
                }
                db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')->execute([$ticketId, $supervisor['id'], 'برگشت برای اصلاح توسط سوپروایزر: ' . $note]);
                log_ticket_event($ticketId, (int) $supervisor['id'], 'supervisor_rework', $ticket['status'], $reworkStatus, ['note' => $note]);
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
            $note = post_value('supervisor_note', 'بازگشایی رسمی برای ادامه رسیدگی');
            $reopenStatus = !empty($ticket['assigned_to']) ? 'in_progress' : 'manager_review';
            $dueAt = business_due_at(priority_sla_minutes((string) $ticket['priority']))->format('Y-m-d H:i:s');
            $reopenOla = ola_deadlines((int) ($ticket['department_id'] ?? 0), (string) $ticket['priority'], (new DateTimeImmutable('now', new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('Y-m-d H:i:s'));
            $hadStaleCancel = !empty($ticket['cancel_requested_at']);
            db()->beginTransaction();
            $reopenQuery = db()->prepare('UPDATE tickets SET status = ?, closed_at = NULL, resolved_at = NULL, supervisor_approved_at = NULL, supervisor_note = ?, due_at = ?, ola_policy_id = ?, ola_response_due_at = ?, ola_due_at = ?, ola_escalation_1_at = ?, ola_escalation_2_at = ?, sla_paused_at = NULL, sla_pause_minutes = 0, cancel_requested_at = NULL, cancel_requested_by = NULL, cancel_request_reason = NULL, updated_at = NOW() WHERE id = ? AND status = "closed"');
            $reopenQuery->execute([$reopenStatus, $note, $dueAt, $reopenOla['policy_id'], $reopenOla['response_due_at'], $reopenOla['due_at'], $reopenOla['escalation_1_at'], $reopenOla['escalation_2_at'], $ticketId]);
            if ($reopenQuery->rowCount() < 1) {
                throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد. صفحه را تازه کنید.');
            }
            log_ticket_event($ticketId, (int) $supervisor['id'], 'supervisor_reopened', 'closed', $reopenStatus, ['note' => $note]);
            if ($hadStaleCancel) {
                log_ticket_event($ticketId, (int) $supervisor['id'], 'cancellation_rejected', 'closed', $reopenStatus, ['note' => 'پاک‌سازی خودکار درخواست لغو قدیمی هنگام بازگشایی رسمی']);
            }
            save_audit((int) $supervisor['id'], 'ticket_reopened', $ticketId, ['note' => $note]);
            notify_ticket_parties($ticketId, 'ticket_reopened', 'درخواست بازگشایی شد', 'درخواست ' . ticket_number($ticketId) . ' با تصمیم سوپروایزر بازگشایی شد.', (int) $supervisor['id']);
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
