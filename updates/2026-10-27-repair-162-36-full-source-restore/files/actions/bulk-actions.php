<?php

declare(strict_types=1);

/**
 * actions/bulk-actions.php — ۲ اکشن POST ارتباط دارایی و عملیات گروهی درخواست‌ها (فاز ۲ بازسازی، برش ۸ — نسخهٔ ۱.۶۲.۱۹).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   add_asset_relation    اتصال یک دارایی به درخواست (ارتباط دارایی-درخواست)
 *   bulk_update_tickets   عملیات گروهی روی چند درخواست (وضعیت/ارجاع/یادداشت مشترک، تراکنشی)
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده؛ تنها موارد
 * continue داخل بلوک، continue داخل حلقه‌های پیمایش‌اند که برای include کاملاً بی‌خطرند (اسکن شد).
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: ارتباط دارایی و عملیات گروهی درخواست‌ها ─────
        if ($action === 'add_asset_relation') {
            $relationUser = require_permission('ticket.attach_asset');
            $sourceId = (int) ($_POST['source_asset_id'] ?? 0);
            $targetId = (int) ($_POST['target_asset_id'] ?? 0);
            $relationType = valid_choice(post_value('relation_type', 'related_to'), ['depends_on', 'connected_to', 'replaces', 'located_with', 'related_to'], 'related_to');
            [$relationScope, $relationParams] = asset_scope($relationUser);
            if ($sourceId <= 0 || $targetId <= 0 || $sourceId === $targetId) {
                throw new RuntimeException('دو دارایی متفاوت را انتخاب کنید.');
            }
            $assetCheck = db()->prepare('SELECT COUNT(*) FROM assets a ' . ($relationScope ? $relationScope . ' AND a.id IN (?, ?)' : 'WHERE a.id IN (?, ?)'));
            $assetCheck->execute(array_merge($relationParams, [$sourceId, $targetId]));
            if ((int) $assetCheck->fetchColumn() !== 2) {
                throw new RuntimeException('به یکی از دارایی‌ها دسترسی ندارید.');
            }
            db()->prepare('INSERT INTO asset_relations (source_asset_id, target_asset_id, relation_type, created_by) VALUES (?, ?, ?, ?)')->execute([$sourceId, $targetId, $relationType, $relationUser['id']]);
            record_asset_history($sourceId, 'asset_relation_added', 'ارتباط CMDB اضافه شد', (int) $relationUser['id'], null, $relationType . ' -> asset #' . $targetId);
            flash('success', 'ارتباط بین دو دارایی ثبت شد.');
            redirect('index.php?page=inventory&id=' . $sourceId);
        }

        if ($action === 'bulk_update_tickets') {
            $queueUser = require_permission('queue.view');
            if (!user_can($queueUser, 'ticket.bulk')) {
                throw new RuntimeException('عملیات گروهی روی درخواست‌ها برای نقش شما فعال نیست.');
            }
            $status = valid_choice(post_value('bulk_status'), ['in_progress', 'waiting_user', 'resolved'], 'in_progress');
            $bulkNote = post_value('bulk_note');
            if (in_array($status, ['waiting_user', 'resolved'], true) && $bulkNote === '') {
                throw new RuntimeException($status === 'waiting_user'
                    ? 'برای عملیات گروهی «در انتظار پاسخ کاربر»، پیام مشترک (سؤال روشن) را بنویسید.'
                    : 'برای عملیات گروهی «حل‌شده»، پیام مشترک (شرح راه‌حل) را بنویسید.');
            }
            if (mb_strlen($bulkNote, 'UTF-8') > 5000) {
                throw new RuntimeException('پیام گروهی نباید بیشتر از ۵۰۰۰ نویسه باشد.');
            }
            $ticketIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ticket_ids'] ?? [])))));
            if (!$ticketIds) {
                throw new RuntimeException('حداقل یک درخواست را انتخاب کنید.');
            }
            $bulkChanged = 0;
            $bulkSkipped = 0;
            db()->beginTransaction();
            foreach ($ticketIds as $ticketId) {
                $bulkTicket = fetch_ticket($ticketId);
                if (!$bulkTicket || !can_view_ticket($bulkTicket, $queueUser)) {
                    $bulkSkipped++;
                    continue;
                }
                $bulkOld = (string) $bulkTicket['status'];
                $bulkLock = db()->prepare('SELECT status, cancel_requested_at FROM tickets WHERE id = ? FOR UPDATE');
                $bulkLock->execute([$ticketId]);
                $lockedBulkTicket = $bulkLock->fetch();
                if (!$lockedBulkTicket || (string) $lockedBulkTicket['status'] !== $bulkOld || !empty($lockedBulkTicket['cancel_requested_at'])) {
                    $bulkSkipped++;
                    continue;
                }
                if ($bulkOld === $status) {
                    continue;
                }
                // فقط کارشناس مسئول یا مدیر واحد، فقط روی درخواست دارای کارشناس و فقط در مسیر مجاز وضعیت.
                if (ticket_handler_level($bulkTicket, $queueUser) === '' || empty($bulkTicket['assigned_to']) || !in_array($status, ticket_allowed_transitions($bulkOld), true)) {
                    $bulkSkipped++;
                    continue;
                }
                [$bulkDue, $bulkPauseAt, $bulkPauseMinutes] = ticket_sla_transition($bulkTicket, $status, (string) $bulkTicket['priority']);
                $bulkOla = ticket_ola_transition($bulkTicket, $status, (string) $bulkTicket['priority']);
                $resolvedAt = $status === 'resolved' ? 'COALESCE(resolved_at, NOW())' : 'NULL';
                $bulkUpdate = db()->prepare('UPDATE tickets SET status = ?, due_at = ?, sla_paused_at = ?, sla_pause_minutes = ?, ola_policy_id = ?, ola_response_due_at = ?, ola_due_at = ?, ola_escalation_1_at = ?, ola_escalation_2_at = ?, resolved_at = ' . $resolvedAt . ', updated_at = NOW() WHERE id = ? AND status = ? AND cancel_requested_at IS NULL');
                $bulkUpdate->execute([$status, $bulkDue, $bulkPauseAt, $bulkPauseMinutes, $bulkOla['policy_id'], $bulkOla['response_due_at'], $bulkOla['due_at'], $bulkOla['escalation_1_at'], $bulkOla['escalation_2_at'], $ticketId, $bulkOld]);
                if ($bulkUpdate->rowCount() < 1) {
                    $bulkSkipped++;
                    continue;
                }
                if ($bulkNote !== '') {
                    db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')->execute([$ticketId, (int) $queueUser['id'], $bulkNote]);
                    log_ticket_event($ticketId, (int) $queueUser['id'], 'reply');
                }
                log_ticket_event($ticketId, (int) $queueUser['id'], 'bulk_status_changed', $bulkOld, $status);
                save_audit((int) $queueUser['id'], 'ticket_updated', $ticketId, ['status' => $status, 'bulk' => true]);
                if ($status === 'resolved') {
                    notify_ticket_resolved($ticketId, (int) $queueUser['id']);
                    record_asset_history((int) ($bulkTicket['asset_id'] ?? 0), 'ticket_resolved', 'حل مشکل درخواست ' . ticket_number($ticketId), (int) $queueUser['id'], $ticketId, 'وضعیت به حل‌شده تغییر کرد.');
                } else {
                    notify_ticket_parties($ticketId, 'bulk_status_changed', 'تغییر وضعیت درخواست', 'وضعیت درخواست ' . ticket_number($ticketId) . ' در عملیات گروهی به «' . status_label($status) . '» تغییر کرد.', (int) $queueUser['id']);
                }
                $bulkChanged++;
            }
            db_commit('index.php');
            $bulkMessage = $bulkChanged . ' درخواست به‌روزرسانی شد.';
            if ($bulkSkipped > 0) {
                $bulkMessage .= ' ' . $bulkSkipped . ' درخواست (بدون کارشناس مسئول، خارج از اختیار شما، درخواست لغو در انتظار یا مسیر وضعیت نامعتبر) تغییر نکرد.';
            }
            flash($bulkChanged > 0 ? 'success' : 'warning', $bulkMessage);
            redirect('index.php?page=queue');
        }
