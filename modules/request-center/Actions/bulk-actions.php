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
            if ($bulkNote === '') {
                throw new RuntimeException('برای هر تغییر وضعیت گروهی، توضیح اقدام قابل مشاهده برای درخواست‌کننده الزامی است.');
            }
            if (mb_strlen($bulkNote, 'UTF-8') > 5000) {
                throw new RuntimeException('پیام گروهی نباید بیشتر از ۵۰۰۰ نویسه باشد.');
            }
            $ticketIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ticket_ids'] ?? [])))));
            if (!$ticketIds) {
                throw new RuntimeException('حداقل یک درخواست را انتخاب کنید.');
            }
            // سقف تعداد: حلقه داخل یک تراکنش اجرا می‌شود و به ازای هر درخواست یک SELECT ... FOR UPDATE
            // می‌گیرد. بدون سقف، یک ارسال بزرگ قفل‌های سطری را تا پایان حلقه نگه می‌دارد و بقیهٔ
            // کاربران پشت آن می‌مانند. صفحهٔ صف حداکثر ۱۰۰ ردیف نشان می‌دهد، پس ۲۰۰ دست‌ودل‌بازانه است.
            if (count($ticketIds) > 200) {
                throw new RuntimeException('در هر عملیات گروهی حداکثر ۲۰۰ درخواست قابل انتخاب است؛ انتخاب را کوچک‌تر کنید.');
            }
            $bulkChanged = 0;
            $bulkSkipped = 0;
            $bulkAlready = 0;
            db()->beginTransaction();
            foreach ($ticketIds as $ticketId) {
                $bulkTicket = fetch_ticket($ticketId);
                if (!$bulkTicket || !can_view_ticket($bulkTicket, $queueUser)) {
                    $bulkSkipped++;
                    continue;
                }
                // وضعیت اولیه از رکورد مجازشده خوانده می‌شود؛ قفل و بازبینی نهایی
                // وضعیت/مسئول/اولویت/درخواست لغو فقط در WorkflowService انجام می‌شود.
                // این کار از قفل‌گیری تکراری و دو مرجع متفاوت برای کنترل هم‌زمانی جلوگیری می‌کند.
                if (!empty($bulkTicket['cancel_requested_at'])) {
                    $bulkSkipped++;
                    continue;
                }
                $bulkOld = (string) $bulkTicket['status'];
                if ($bulkOld === $status) {
                    // از قبل در همین وضعیت بود: جداگانه شمرده می‌شود تا در گزارش گم نشود
                    // (رفتار قبلی نه «تغییر یافته» می‌شمرد نه «تغییر نکرده»، و مجموع جور درنمی‌آمد).
                    $bulkAlready++;
                    continue;
                }
                // فقط کارشناس مسئول یا مدیر واحد، فقط روی درخواست دارای کارشناس و فقط در مسیر مجاز وضعیت.
                if (ticket_handler_level($bulkTicket, $queueUser) === '' || empty($bulkTicket['assigned_to']) || !in_array($status, ticket_allowed_transitions($bulkOld), true)) {
                    $bulkSkipped++;
                    continue;
                }
                [$bulkDue, $bulkPauseAt, $bulkPauseMinutes] = ticket_sla_transition($bulkTicket, $status, (string) $bulkTicket['priority']);
                $bulkOla = ticket_ola_transition($bulkTicket, $status, (string) $bulkTicket['priority']);
                $workflow = new RequestCenterWorkflowService(db());
                $bulkUpdates = [
                    'due_at' => $bulkDue,
                    'sla_paused_at' => $bulkPauseAt,
                    'sla_pause_minutes' => $bulkPauseMinutes,
                    'ola_policy_id' => $bulkOla['policy_id'],
                    'ola_response_due_at' => $bulkOla['response_due_at'],
                    'ola_due_at' => $bulkOla['due_at'],
                    'ola_escalation_1_at' => $bulkOla['escalation_1_at'],
                    'ola_escalation_2_at' => $bulkOla['escalation_2_at'],
                    'resolved_at' => $status === 'resolved' ? RequestCenterSqlExpression::coalesceNow() : null,
                ];
                $bulkFirstResponseRecorded = $bulkNote !== '' && empty($bulkTicket['first_response_at']);
                if ($bulkFirstResponseRecorded) {
                    $bulkUpdates['first_response_at'] = RequestCenterSqlExpression::coalesceNow();
                }
                $bulkUpdated = $workflow->transition($ticketId, $bulkOld, $status, $bulkUpdates, 'standard', [
                    'requester_id' => $bulkTicket['requester_id'],
                    'assigned_to' => $bulkTicket['assigned_to'] ?? null,
                    'first_response_at' => $bulkTicket['first_response_at'] ?? null,
                    'priority' => $bulkTicket['priority'],
                    'department_id' => $bulkTicket['department_id'] ?? null,
                    'service_group' => $bulkTicket['service_group'] ?? null,
                    'due_at' => $bulkTicket['due_at'] ?? null,
                    'sla_minutes' => $bulkTicket['sla_minutes'] ?? null,
                    'sla_paused_at' => $bulkTicket['sla_paused_at'] ?? null,
                    'sla_pause_minutes' => $bulkTicket['sla_pause_minutes'] ?? null,
                    'ola_policy_id' => $bulkTicket['ola_policy_id'] ?? null,
                    'ola_response_due_at' => $bulkTicket['ola_response_due_at'] ?? null,
                    'ola_due_at' => $bulkTicket['ola_due_at'] ?? null,
                    'ola_escalation_1_at' => $bulkTicket['ola_escalation_1_at'] ?? null,
                    'ola_escalation_2_at' => $bulkTicket['ola_escalation_2_at'] ?? null,
                    'cancel_requested_at' => null,
                ]);
                if (!$bulkUpdated) {
                    $bulkSkipped++;
                    continue;
                }
                $bulkSlaOlaAudit = [
                    'due_at' => $bulkDue,
                    'sla_paused_at' => $bulkPauseAt,
                    'sla_pause_minutes' => $bulkPauseMinutes,
                    'ola_policy_id' => $bulkOla['policy_id'],
                    'ola_response_due_at' => $bulkOla['response_due_at'],
                    'ola_due_at' => $bulkOla['due_at'],
                    'ola_escalation_1_at' => $bulkOla['escalation_1_at'],
                    'ola_escalation_2_at' => $bulkOla['escalation_2_at'],
                    'first_response_recorded' => $bulkFirstResponseRecorded,
                ];
                if ($bulkNote !== '') {
                    (new RequestCenterWorkflowService(db()))->appendMessage($ticketId, (int) $queueUser['id'], $bulkNote);
                    log_ticket_event($ticketId, (int) $queueUser['id'], 'reply', $bulkOld, $status, [
                        'is_internal' => false,
                        'reason' => $bulkNote,
                        'sla_ola' => $bulkSlaOlaAudit,
                    ]);
                }
                log_ticket_event($ticketId, (int) $queueUser['id'], 'bulk_status_changed', $bulkOld, $status, [
                    'reason' => $bulkNote,
                    'sla_ola' => $bulkSlaOlaAudit,
                ]);
                save_audit_required((int) $queueUser['id'], 'ticket_updated', $ticketId, [
                    'from_status' => $bulkOld,
                    'to_status' => $status,
                    'bulk' => true,
                    'reason' => $bulkNote,
                    'first_response_recorded' => $bulkFirstResponseRecorded,
                    'sla_ola' => $bulkSlaOlaAudit,
                ]);
                if ($status === 'resolved') {
                    request_center_notify_ticket_resolved($ticketId, (int) $queueUser['id']);
                    record_asset_history((int) ($bulkTicket['asset_id'] ?? 0), 'ticket_resolved', 'حل مشکل درخواست ' . ticket_number($ticketId), (int) $queueUser['id'], $ticketId, 'وضعیت به حل‌شده تغییر کرد.');
                } else {
                    request_center_notify_ticket_parties($ticketId, 'bulk_status_changed', 'تغییر وضعیت درخواست', 'وضعیت درخواست ' . ticket_number($ticketId) . ' در عملیات گروهی به «' . request_center_status_label($status) . '» تغییر کرد.', (int) $queueUser['id']);
                }
                $bulkChanged++;
            }
            db_commit_or_fail('ticket:bulk_status_update');
            $bulkMessage = $bulkChanged . ' درخواست به‌روزرسانی شد.';
            if ($bulkAlready > 0) {
                $bulkMessage .= ' ' . $bulkAlready . ' درخواست از قبل در همین وضعیت بود.';
            }
            if ($bulkSkipped > 0) {
                $bulkMessage .= ' ' . $bulkSkipped . ' درخواست (بدون کارشناس مسئول، خارج از اختیار شما، درخواست لغو در انتظار یا مسیر وضعیت نامعتبر) تغییر نکرد.';
            }
            flash($bulkChanged > 0 ? 'success' : 'warning', $bulkMessage);
            redirect('request-center.php?page=queue');
        }
