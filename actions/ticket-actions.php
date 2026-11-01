<?php

declare(strict_types=1);

/**
 * actions/ticket-actions.php — ۷ اکشن POST چرخهٔ کامل درخواست‌ها (فاز ۲ بازسازی، برش ۲ — نسخهٔ ۱.۶۲.۱۳).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   create_ticket               ثبت درخواست جدید (با پیوست‌ها، حوزه، و ارجاع خودکار به کارشناس)
 *   cancel_ticket               ثبت درخواستِ لغو از سوی کاربر ثبت‌کننده
 *   decide_ticket_cancellation  تصمیم مسئول دربارهٔ لغو (تأیید یا رد)
 *   claim_ticket                برداشتن درخواست از صف توسط کارشناس (به عهده گرفتن)
 *   reopen_ticket               بازگشایی درخواستِ بسته‌شده
 *   reply                       ثبت پاسخ/گفتگو روی درخواست (با پیوست و تغییر وضعیت)
 *   update_ticket               ویرایش مشخصات درخواست (اولویت، حوزه، مسئول و ...)
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود ($action، $user و ...). متن اکشن‌ها بایت‌به‌بایت از
 * index.php منتقل شده و پیش از انتقال از نظر دام‌های include (return/function/goto/break/
 * continue سطح بلوک، __DIR__، ?>) اسکن و پاک تأیید شده است.
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: چرخهٔ کامل درخواست‌ها (ثبت/پاسخ/ارجاع/بستن) ─────
        if ($action === 'create_ticket') {
            // مجوز باید در handler سمت سرور هم کنترل شود؛ پنهان‌کردن فرم کافی نیست.
            $user = require_permission('ticket.create');
            ticket_service_ensure_schema();
            org_ticket_schema_ensure();
            $subject = post_value('subject');
            $description = post_value('description');
            $categoryId = (int) ($_POST['category_id'] ?? 0) ?: null;
            $serviceId = (int) ($_POST['service_id'] ?? 0) ?: null;
            $ticketType = valid_choice(post_value('ticket_type', 'incident'), ['incident', 'request', 'problem', 'change'], 'incident');
            if (!is_staff_role($user['role']) && in_array($ticketType, ['problem', 'change'], true)) {
                $ticketType = 'request';
            }
             $serviceGroup = valid_choice(post_value('service_group', user_service_group($user)), ['it', 'support'], 'support');
             $handlingUnitId = null;
             $service = null;
             $customFields = [];
             $cdDvdRequestId = (int) ($_POST['cd_dvd_record_id'] ?? 0);
             $cdDvdRequestRecord = null;
             if ($cdDvdRequestId > 0) {
                 $cdDvdRequestRecord = cd_dvd_fetch_record($cdDvdRequestId);
                 if (!$cdDvdRequestRecord || cd_dvd_can_edit_records($user) || !cd_dvd_can_view_record($cdDvdRequestRecord, $user)) {
                     throw new RuntimeException('رکورد CD/DVD برای درخواست اصلاح معتبر نیست.');
                 }
                 $customFields['_cd_dvd_request'] = 'edit';
                 $customFields['_cd_dvd_record_id'] = (string) $cdDvdRequestId;
                 $serviceGroup = 'support';
                 $serviceId = null;
                 $categoryId = null;
             }
            if ($serviceId !== null) {
                $serviceQuery = db()->prepare('SELECT * FROM service_catalog WHERE id = ? AND is_active = 1 LIMIT 1');
                $serviceQuery->execute([$serviceId]);
                $service = $serviceQuery->fetch();
                if (!$service) {
                    throw new RuntimeException('خدمت انتخاب‌شده فعال نیست.');
                }
                $serviceGroup = (string) $service['service_group'];
                if ($categoryId === null && (int) ($service['category_id'] ?? 0) > 0) {
                    $categoryId = (int) $service['category_id'];
                }
                $handlingUnitId = (int) ($service['handling_unit_id'] ?? 0) ?: null;
                $ticketType = valid_choice((string) ($service['default_ticket_type'] ?? $ticketType), ['incident', 'request', 'problem', 'change'], $ticketType);
                $fieldQuery = db()->prepare('SELECT * FROM service_catalog_fields WHERE service_id = ? ORDER BY sort_order, id');
                $fieldQuery->execute([$serviceId]);
                foreach ($fieldQuery->fetchAll() as $field) {
                    $fieldValue = trim((string) ($_POST['custom'][$field['field_key']] ?? ''));
                    if ((int) $field['is_required'] === 1 && $fieldValue === '') {
                        throw new RuntimeException('فیلد «' . $field['label'] . '» الزامی است.');
                    }
                    if ($field['field_type'] === 'select' && $fieldValue !== '') {
                        $options = json_decode((string) $field['options_json'], true) ?: [];
                        if (!in_array($fieldValue, $options, true)) {
                            throw new RuntimeException('مقدار فیلد «' . $field['label'] . '» معتبر نیست.');
                        }
                    }
                    if ($field['field_type'] === 'date' && $fieldValue !== '' && jalali_input_to_gregorian($fieldValue) === null) {
                        throw new RuntimeException('تاریخ فیلد «' . $field['label'] . '» باید به‌صورت جلالی وارد شود.');
                    }
                    $customFields[$field['field_key']] = $fieldValue;
                 }
             }
             if ($cdDvdRequestRecord) {
                 $serviceGroup = 'support';
                 $ticketType = 'request';
             }
             if ($serviceId === null && !$cdDvdRequestRecord) {
                 throw new RuntimeException('نوع خدمت را از فهرست انتخاب کنید.');
             }
             if ($categoryId !== null) {
                $categoryCheck = db()->prepare('SELECT service_group FROM categories WHERE id = ? AND is_active = 1 LIMIT 1');
                $categoryCheck->execute([$categoryId]);
                if ((string) $categoryCheck->fetchColumn() !== $serviceGroup) {
                    throw new RuntimeException('دسته‌بندی انتخاب‌شده با حوزه خدمت سازگار نیست.');
                }
            }
            if ($handlingUnitId === null) {
                $unitQuery = db()->prepare('SELECT id FROM handling_units WHERE code = ? AND is_active = 1 LIMIT 1');
                $unitQuery->execute([$serviceGroup]);
                $handlingUnitId = (int) ($unitQuery->fetchColumn() ?: 0) ?: null;
            }
            $departmentId = (int) ($_POST['department_id'] ?? 0) ?: (int) ($user['department_id'] ?? 0) ?: null;
            if (!is_global_ticket_role($user['role']) && !is_it_agent($user)) {
                $departmentId = (int) ($user['department_id'] ?? 0) ?: null;
            }
            // معاونت سازمانی درخواست‌کننده از چارت سازمانی (org_units) جدا از مسیر رسیدگی
            // در ستون مستقل ذخیره می‌شود و درخواست به همان معاونت مسیردهی می‌شود.
            $requestingUnitId = (int) ($_POST['requesting_unit_id'] ?? 0) ?: null;
            if ($requestingUnitId !== null && function_exists('org_unit_by_id')) {
                $requestingUnit = org_unit_by_id($requestingUnitId);
                if (!$requestingUnit || (string) ($requestingUnit['unit_type'] ?? '') !== 'deputy' || (int) ($requestingUnit['is_active'] ?? 0) !== 1) {
                    throw new RuntimeException('معاونت سازمانی انتخاب‌شده معتبر نیست؛ از فهرست معاونت‌ها یک گزینه فعال انتخاب کنید.');
                }
            }
            if ($requestingUnitId === null && function_exists('org_deputy_units') && org_deputy_units() !== []) {
                throw new RuntimeException('انتخاب «معاونت سازمانی درخواست‌کننده» الزامی است.');
            }
            if ($requestingUnitId !== null && function_exists('org_unit_routing_department_id')) {
                $routingDepartmentId = org_unit_routing_department_id($requestingUnitId);
                if ($routingDepartmentId > 0) {
                    $departmentId = $routingDepartmentId;
                }
            }
            $assetId = (int) ($_POST['asset_id'] ?? 0) ?: null;
            // طبق سیاست ثبت درخواست، برای همهٔ خدمات IT انتخاب شناسنامهٔ فنی الزامی است.
            $requiresAsset = $serviceGroup === 'it';
            if ($requiresAsset && $assetId === null) {
                throw new RuntimeException('برای همهٔ خدمات IT، انتخاب «سیستم مرتبط» الزامی است. از فهرست سیستم‌ها یکی را انتخاب کنید؛ اگر دارایی در فهرست نیست، با واحد IT تماس بگیرید.');
            }
            if ($assetId !== null) {
                $assetCheck = db()->prepare('SELECT id, owner_user_id, department_id, ip_address FROM assets WHERE id = ? LIMIT 1');
                $assetCheck->execute([$assetId]);
                $assetRow = $assetCheck->fetch();
                $sameClient = $assetRow && inventory_client_ip() !== '' && (string) $assetRow['ip_address'] === inventory_client_ip();
                $requesterAssetLimited = !is_staff_role((string) $user['role']) && !is_it_agent($user);
                if (!$assetRow || ($requesterAssetLimited && (int) $assetRow['owner_user_id'] !== (int) $user['id'] && !$sameClient) || (is_department_scoped_role($user['role']) && !is_it_agent($user) && (int) $assetRow['department_id'] !== (int) ($user['department_id'] ?? 0))) {
                    throw new RuntimeException('سیستم انتخاب‌شده برای این حساب قابل استفاده نیست.');
                }
            }
             $supportLocation = post_value('support_location');
             $supportEquipment = post_value('support_equipment');
             if ($cdDvdRequestRecord && $supportLocation === '') {
                 $supportLocation = 'کنترل CD/DVD';
             }
            if ($serviceGroup === 'support' && $supportLocation === '') {
                throw new RuntimeException('برای درخواست پشتیبانی، محل خدمت یا اتاق را وارد کنید.');
            }
            $priority = valid_choice(post_value('priority') ?: (string) ($service['default_priority'] ?? 'normal'), ['normal', 'urgent', 'critical'], 'normal');
            $parentTicketId = (int) ($_POST['parent_ticket_id'] ?? 0) ?: null;
            $canReferenceTicket = is_staff_role($user['role']) || is_it_agent($user);
            if ($parentTicketId !== null) {
                $parentTicket = $canReferenceTicket ? fetch_ticket($parentTicketId) : null;
                if (!$parentTicket || !can_view_ticket($parentTicket, $user)) {
                    $parentTicketId = null;
                    flash('warning', 'درخواست مرجع نامعتبر بود و نادیده گرفته شد.');
                }
            }
            $slaMinutes = priority_sla_minutes($priority);
            $createdAt = new DateTimeImmutable('now', new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran')));
            $dueAt = business_due_at($slaMinutes, $createdAt)->format('Y-m-d H:i:s');
            $ola = ola_deadlines($departmentId, $priority, $createdAt->format('Y-m-d H:i:s'));
            if ($subject === '' || $description === '') {
                throw new RuntimeException('عنوان و شرح مشکل الزامی است.');
            }
            // Reserve and commit the sequential identifier before opening the ticket
            // transaction; a later rollback must not make an already observed number reusable.
            $ticketNo = ticket_number_next(ticket_number_prefix($serviceGroup));
            db()->beginTransaction();
            $agentId = choose_handling_agent($serviceGroup, $departmentId);
            $initialStatus = $agentId ? 'assigned' : 'manager_review';
            $query = db()->prepare('INSERT INTO tickets (ticket_no, subject, description, category_id, service_id, requesting_unit_id, ticket_type, parent_ticket_id, custom_fields, service_group, department_id, asset_id, handling_unit_id, support_location, support_equipment, priority, requester_id, assigned_to, assigned_at, status, sla_minutes, due_at, ola_policy_id, ola_response_due_at, ola_due_at, ola_escalation_1_at, ola_escalation_2_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ' . ($agentId ? 'NOW()' : 'NULL') . ', "' . $initialStatus . '", ?, ?, ?, ?, ?, ?, ?)');
            $query->execute([$ticketNo, $subject, $description, $categoryId, $serviceId, $requestingUnitId, $ticketType, $parentTicketId, json_encode($customFields, JSON_UNESCAPED_UNICODE), $serviceGroup, $departmentId, $assetId, $handlingUnitId, $supportLocation ?: null, $supportEquipment ?: null, $priority, $user['id'], $agentId, $slaMinutes, $dueAt, $ola['policy_id'], $ola['response_due_at'], $ola['due_at'], $ola['escalation_1_at'], $ola['escalation_2_at']]);
            $ticketId = (int) db()->lastInsertId();
            if ($ticketId > 0) {
                ticket_number_cache_set($ticketId, $ticketNo);
            }
            $message = db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body) VALUES (?, ?, ?)');
            $message->execute([$ticketId, $user['id'], $description]);
            store_attachment((int) db()->lastInsertId(), $_FILES['attachment'] ?? []);
             $ticketAuditMeta = ['subject' => $subject];
             $ticketEventDetails = ['department_id' => $departmentId, 'sla_minutes' => $slaMinutes, 'ticket_type' => $ticketType, 'service_id' => $serviceId];
             if ($cdDvdRequestRecord) {
                 $ticketAuditMeta['cd_dvd_record_id'] = $cdDvdRequestId;
                 $ticketEventDetails['cd_dvd_record_id'] = $cdDvdRequestId;
             }
             save_audit((int) $user['id'], 'ticket_created', $ticketId, $ticketAuditMeta);
             log_ticket_event($ticketId, (int) $user['id'], 'created', null, $initialStatus, $ticketEventDetails);
            if ($agentId) {
                log_ticket_event($ticketId, (int) $user['id'], 'auto_assigned', 'manager_review', 'assigned', ['assigned_to' => $agentId]);
            }
            notify_ticket_parties($ticketId, 'ticket_created', 'درخواست جدید ثبت شد', 'درخواست ' . ticket_number($ticketId) . ' برای رسیدگی ثبت شد.', (int) $user['id']);
            if ($assetId) {
                record_asset_history((int) $assetId, 'ticket_created', 'ثبت درخواست ' . ticket_number($ticketId), (int) $user['id'], $ticketId, $subject);
            }
            db_commit('index.php');
            flash('success', 'درخواست ' . ticket_number($ticketId) . ' ثبت شد.');
            redirect('index.php?page=ticket&id=' . $ticketId);
        }

        if ($action === 'cancel_ticket') {
            $cancelUser = require_permission('ticket.create');
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $ticket = fetch_ticket($ticketId);
            if (!$ticket || !ticket_requester_can_cancel($ticket, $cancelUser)) {
                throw new RuntimeException('فقط ثبت‌کننده می‌تواند برای درخواست بازِ خودش که درخواست لغوِ درانتظار ندارد، لغو ثبت کند.');
            }
            $oldStatus = (string) $ticket['status'];
            $reason = post_value('cancel_reason');
            if ($reason === '') {
                throw new RuntimeException('برای لغو یا درخواست لغو درخواست، دلیل را بنویسید.');
            }
            if (mb_strlen($reason, 'UTF-8') > 1000) {
                throw new RuntimeException('دلیل لغو نباید بیشتر از ۱۰۰۰ نویسه باشد.');
            }

            $directCancellation = ticket_requester_can_cancel_directly($ticket);
            db()->beginTransaction();
            if ($directCancellation) {
                [$cancelDueAt, $cancelPauseAt, $cancelPauseMinutes] = ticket_sla_transition($ticket, 'cancelled', (string) $ticket['priority']);
                $cancelOla = ticket_ola_transition($ticket, 'cancelled', (string) $ticket['priority']);
                $cancelQuery = db()->prepare('UPDATE tickets SET status = "cancelled", due_at = ?, sla_paused_at = ?, sla_pause_minutes = ?, ola_policy_id = ?, ola_response_due_at = ?, ola_due_at = ?, ola_escalation_1_at = ?, ola_escalation_2_at = ?, updated_at = NOW() WHERE id = ? AND requester_id = ? AND status = ? AND cancel_requested_at IS NULL AND assigned_to IS NULL AND first_response_at IS NULL AND resolved_at IS NULL');
                $cancelQuery->execute([$cancelDueAt, $cancelPauseAt, $cancelPauseMinutes, $cancelOla['policy_id'], $cancelOla['response_due_at'], $cancelOla['due_at'], $cancelOla['escalation_1_at'], $cancelOla['escalation_2_at'], $ticketId, (int) $cancelUser['id'], $oldStatus]);
                if ($cancelQuery->rowCount() < 1) {
                    throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد؛ صفحه را تازه کنید.');
                }
                db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')->execute([$ticketId, (int) $cancelUser['id'], 'درخواست پیش از ارجاع به کارشناس، به درخواست ثبت‌کننده لغو شد: ' . $reason]);
                save_audit((int) $cancelUser['id'], 'ticket_cancelled_by_requester', $ticketId, ['reason' => $reason]);
                log_ticket_event($ticketId, (int) $cancelUser['id'], 'cancelled_by_requester', $oldStatus, 'cancelled', ['reason' => $reason, 'mode' => 'direct_before_assignment']);
                notify_ticket_parties($ticketId, 'ticket_cancelled', 'درخواست لغو شد', 'درخواست ' . ticket_number($ticketId) . ' پیش از ارجاع به کارشناس، به درخواست ثبت‌کننده لغو شد: ' . $reason, (int) $cancelUser['id']);
                record_asset_history((int) ($ticket['asset_id'] ?? 0), 'ticket_cancelled', 'لغو درخواست ' . ticket_number($ticketId), (int) $cancelUser['id'], $ticketId, $reason);
                db_commit('index.php');
                flash('success', 'درخواست لغو شد؛ سابقه و پیام‌های آن برای پیگیری حفظ می‌شوند.');
            } else {
                $requestQuery = db()->prepare('UPDATE tickets SET cancel_requested_at = NOW(), cancel_requested_by = ?, cancel_request_reason = ?, updated_at = NOW() WHERE id = ? AND requester_id = ? AND status = ? AND cancel_requested_at IS NULL');
                $requestQuery->execute([(int) $cancelUser['id'], $reason, $ticketId, (int) $cancelUser['id'], $oldStatus]);
                if ($requestQuery->rowCount() < 1) {
                    throw new RuntimeException('وضعیت درخواست یا درخواست لغو هم‌زمان تغییر کرد؛ صفحه را تازه کنید.');
                }
                db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')->execute([$ticketId, (int) $cancelUser['id'], 'درخواست لغو درخواست ثبت شد: ' . $reason]);
                save_audit((int) $cancelUser['id'], 'ticket_cancellation_requested', $ticketId, ['reason' => $reason, 'status' => $oldStatus]);
                log_ticket_event($ticketId, (int) $cancelUser['id'], 'cancellation_requested', $oldStatus, $oldStatus, ['reason' => $reason]);
                notify_ticket_parties($ticketId, 'ticket_cancellation_requested', 'درخواست لغو درخواست ثبت شد', 'ثبت‌کننده درخواست لغو درخواست ' . ticket_number($ticketId) . ' را ثبت کرده است. تا تصمیم مسئول، گفت‌وگو ادامه دارد اما تغییر وضعیت و ارجاع متوقف است.', (int) $cancelUser['id']);
                db_commit('index.php');
                flash('info', 'درخواست لغو ثبت شد؛ تا تصمیم مسئول، گفت‌وگو ادامه دارد اما تغییر وضعیت و ارجاع متوقف است.');
            }
            redirect('index.php?page=ticket&id=' . $ticketId);
        }

        if ($action === 'decide_ticket_cancellation') {
            $decisionUser = require_login();
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $ticket = fetch_ticket($ticketId);
            if (!$ticket || !can_view_ticket($ticket, $decisionUser) || ticket_handler_level($ticket, $decisionUser) === '' || (int) ($ticket['cancel_requested_by'] ?? 0) === (int) $decisionUser['id']) {
                throw new RuntimeException('فقط مسئول رسیدگی یا مدیر مجاز همان حوزه و غیر از درخواست‌کننده می‌تواند درخواست لغو را بررسی کند.');
            }
            if (empty($ticket['cancel_requested_at'])) {
                throw new RuntimeException('درخواست لغوِ درانتظاری برای این درخواست وجود ندارد.');
            }
            $decidableStatuses = array_merge(ticket_requester_cancelable_statuses(), ['resolved']);
            if (!in_array((string) $ticket['status'], $decidableStatuses, true) && !in_array((string) $ticket['status'], ['closed', 'cancelled'], true)) {
                throw new RuntimeException('درخواست لغوِ درانتظاری برای این درخواست وجود ندارد.');
            }
            $decision = valid_choice(post_value('decision'), ['approve', 'reject'], 'reject');
            $note = post_value('decision_note');
            if ($decision === 'reject' && $note === '') {
                throw new RuntimeException('برای رد درخواست لغو، دلیل تصمیم را بنویسید.');
            }
            if (in_array((string) $ticket['status'], ['closed', 'cancelled'], true) && $decision === 'approve') {
                throw new RuntimeException('درخواست قبلاً بسته/لغو شده است؛ برای پاک‌سازی درخواست قدیمی، «رد و ادامهٔ رسیدگی» را بزنید.');
            }
            if (mb_strlen($note, 'UTF-8') > 1000) {
                throw new RuntimeException('یادداشت تصمیم نباید بیشتر از ۱۰۰۰ نویسه باشد.');
            }
            $oldStatus = (string) $ticket['status'];
            $requestAt = (string) $ticket['cancel_requested_at'];
            $requestReason = (string) ($ticket['cancel_request_reason'] ?? '');
            db()->beginTransaction();
            if ($decision === 'approve') {
                [$cancelDueAt, $cancelPauseAt, $cancelPauseMinutes] = ticket_sla_transition($ticket, 'cancelled', (string) $ticket['priority']);
                $cancelOla = ticket_ola_transition($ticket, 'cancelled', (string) $ticket['priority']);
                $approveQuery = db()->prepare('UPDATE tickets SET status = "cancelled", resolved_at = NULL, cancel_requested_at = NULL, cancel_requested_by = NULL, cancel_request_reason = NULL, due_at = ?, sla_paused_at = ?, sla_pause_minutes = ?, ola_policy_id = ?, ola_response_due_at = ?, ola_due_at = ?, ola_escalation_1_at = ?, ola_escalation_2_at = ?, updated_at = NOW() WHERE id = ? AND status = ? AND cancel_requested_at = ?');
                $approveQuery->execute([$cancelDueAt, $cancelPauseAt, $cancelPauseMinutes, $cancelOla['policy_id'], $cancelOla['response_due_at'], $cancelOla['due_at'], $cancelOla['escalation_1_at'], $cancelOla['escalation_2_at'], $ticketId, $oldStatus, $requestAt]);
                if ($approveQuery->rowCount() < 1) {
                    throw new RuntimeException('درخواست لغو یا وضعیت درخواست هم‌زمان تغییر کرد؛ صفحه را تازه کنید.');
                }
                $decisionBody = 'درخواست لغو درخواست تأیید شد: ' . $requestReason . ($note !== '' ? "\nیادداشت مسئول: " . $note : '');
                db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')->execute([$ticketId, (int) $decisionUser['id'], $decisionBody]);
                save_audit((int) $decisionUser['id'], 'ticket_cancellation_approved', $ticketId, ['reason' => $requestReason, 'note' => $note]);
                log_ticket_event($ticketId, (int) $decisionUser['id'], 'cancellation_approved', $oldStatus, 'cancelled', ['reason' => $requestReason, 'note' => $note, 'requested_at' => $requestAt]);
                notify_ticket_parties($ticketId, 'ticket_cancelled', 'درخواست لغو تأیید شد', 'درخواست لغو درخواست ' . ticket_number($ticketId) . ' توسط مسئول رسیدگی تأیید شد.', (int) $decisionUser['id']);
                record_asset_history((int) ($ticket['asset_id'] ?? 0), 'ticket_cancelled', 'تأیید لغو درخواست ' . ticket_number($ticketId), (int) $decisionUser['id'], $ticketId, $requestReason . ($note !== '' ? "\n" . $note : ''));
                db_commit('index.php');
                flash('success', 'درخواست لغو تأیید شد؛ تاریخچهٔ درخواست حفظ شده است.');
            } else {
                $rejectQuery = db()->prepare('UPDATE tickets SET cancel_requested_at = NULL, cancel_requested_by = NULL, cancel_request_reason = NULL, updated_at = NOW() WHERE id = ? AND status = ? AND cancel_requested_at = ?');
                $rejectQuery->execute([$ticketId, $oldStatus, $requestAt]);
                if ($rejectQuery->rowCount() < 1) {
                    throw new RuntimeException('درخواست لغو هم‌زمان بررسی شده است؛ صفحه را تازه کنید.');
                }
                $isStaleClosed = in_array($oldStatus, ['closed', 'cancelled'], true);
                $rejectBody = $isStaleClosed
                    ? 'درخواست لغو قدیمی پاک‌سازی شد (درخواست قبلاً ' . status_label($oldStatus) . ' شده بود): ' . $note
                    : 'درخواست لغو رد شد و رسیدگی ادامه می‌یابد: ' . $note;
                db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')->execute([$ticketId, (int) $decisionUser['id'], $rejectBody]);
                save_audit((int) $decisionUser['id'], 'ticket_cancellation_rejected', $ticketId, ['reason' => $requestReason, 'note' => $note]);
                log_ticket_event($ticketId, (int) $decisionUser['id'], 'cancellation_rejected', $oldStatus, $oldStatus, ['reason' => $requestReason, 'note' => $note, 'requested_at' => $requestAt]);
                notify_ticket_parties($ticketId, 'ticket_cancellation_rejected', 'درخواست لغو رد شد', $isStaleClosed ? 'درخواست لغو قدیمی درخواست ' . ticket_number($ticketId) . ' پاک‌سازی شد: ' . $note : 'مسئول رسیدگی درخواست لغو درخواست ' . ticket_number($ticketId) . ' را رد کرد. رسیدگی ادامه می‌یابد: ' . $note, (int) $decisionUser['id']);
                db_commit('index.php');
                flash('info', $isStaleClosed ? 'درخواست لغو قدیمی پاک‌سازی شد.' : 'درخواست لغو رد شد و درخواست برای ادامهٔ رسیدگی فعال ماند.');
            }
            redirect('index.php?page=ticket&id=' . $ticketId);
        }

        if ($action === 'claim_ticket') {
            $claimUser = require_login();
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $ticket = fetch_ticket($ticketId);
            if (!$ticket || !can_view_ticket($ticket, $claimUser)) {
                throw new RuntimeException('درخواست پیدا نشد یا دسترسی ندارید.');
            }
            if (!ticket_assignee_is_eligible($ticket, $claimUser, 'ticket_notification_user_can')) {
                throw new RuntimeException('برداشتن درخواست فقط برای مسئول فعال و دارای دسترسی رسیدگی در همین حوزه و معاونت مجاز است.');
            }
            $claimTicketGroup = (string) ($ticket['service_group'] ?? '');
            if (($claimUser['role'] ?? '') === 'support_manager' && $claimTicketGroup !== 'support') {
                throw new RuntimeException('این درخواست به حوزه شما تعلق ندارد.');
            }
            if (in_array(($claimUser['role'] ?? ''), ['agent', 'manager'], true) && $claimTicketGroup !== user_service_group($claimUser)) {
                throw new RuntimeException('این درخواست به حوزه شما تعلق ندارد.');
            }
            if (!empty($ticket['cancel_requested_at'])) {
                throw new RuntimeException('لغو این درخواست در انتظار تصمیم مسئول است؛ ابتدا آن را تأیید یا رد کنید.');
            }
            if (!empty($ticket['assigned_to']) || !in_array($ticket['status'], ['new', 'manager_review'], true)) {
                throw new RuntimeException('این درخواست قبلاً به کارشناس ارجاع شده است.');
            }
            db()->beginTransaction();
            $claimQuery = db()->prepare('UPDATE tickets SET assigned_to = ?, assigned_at = NOW(), status = "assigned", updated_at = NOW() WHERE id = ? AND assigned_to IS NULL AND status IN ("new", "manager_review") AND cancel_requested_at IS NULL');
            $claimQuery->execute([(int) $claimUser['id'], $ticketId]);
            if ($claimQuery->rowCount() < 1) {
                throw new RuntimeException('این درخواست هم‌زمان توسط شخص دیگری برداشته شد.');
            }
            save_audit((int) $claimUser['id'], 'ticket_claimed', $ticketId);
            log_ticket_event($ticketId, (int) $claimUser['id'], 'claimed', (string) $ticket['status'], 'assigned', ['assigned_to' => (int) $claimUser['id']]);
            notify_ticket_parties($ticketId, 'ticket_updated', 'درخواست به کارشناس ارجاع شد', 'درخواست ' . ticket_number($ticketId) . ' توسط ' . ($claimUser['full_name'] ?? 'کارشناس') . ' برای رسیدگی برداشته شد.', (int) $claimUser['id']);
            db_commit('index.php');
            flash('success', 'درخواست به شما ارجاع شد.');
            redirect('index.php?page=ticket&id=' . $ticketId);
        }

        if ($action === 'reopen_ticket') {
            $user = require_permission('ticket.reply');
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $ticket = fetch_ticket($ticketId);
            if (!$ticket || (int) $ticket['requester_id'] !== (int) $user['id']) {
                throw new RuntimeException('فقط ثبت‌کننده درخواست می‌تواند آن را بازگشایی کند.');
            }
            if ($ticket['status'] !== 'resolved') {
                throw new RuntimeException('فقط درخواست حل‌شده و هنوز بسته‌نشده قابل بازگشایی است.');
            }
            if (!empty($ticket['cancel_requested_at'])) {
                throw new RuntimeException('لغو این درخواست در انتظار تصمیم مسئول است؛ پس از تصمیم او دربارهٔ بازگشایی اقدام کنید.');
            }
            $reason = post_value('reopen_reason');
            if ($reason === '') {
                throw new RuntimeException('توضیح دهید چه چیزی هنوز برطرف نشده است.');
            }
            $reopenStatus = !empty($ticket['assigned_to']) ? 'in_progress' : 'manager_review';
            $reopenDue = business_due_at(priority_sla_minutes((string) $ticket['priority']))->format('Y-m-d H:i:s');
            $reopenOla = ola_deadlines((int) ($ticket['department_id'] ?? 0), (string) $ticket['priority'], (new DateTimeImmutable('now', new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('Y-m-d H:i:s'));
            db()->beginTransaction();
            $reopenQuery = db()->prepare('UPDATE tickets SET status = ?, resolved_at = NULL, due_at = ?, ola_policy_id = ?, ola_response_due_at = ?, ola_due_at = ?, ola_escalation_1_at = ?, ola_escalation_2_at = ?, sla_paused_at = NULL, sla_pause_minutes = 0, updated_at = NOW() WHERE id = ? AND status = "resolved" AND cancel_requested_at IS NULL');
            $reopenQuery->execute([$reopenStatus, $reopenDue, $reopenOla['policy_id'], $reopenOla['response_due_at'], $reopenOla['due_at'], $reopenOla['escalation_1_at'], $reopenOla['escalation_2_at'], $ticketId]);
            if ($reopenQuery->rowCount() < 1) {
                throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد. صفحه را تازه کنید.');
            }
            db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')->execute([$ticketId, $user['id'], 'مشکل حل نشده است (بازگشایی توسط کاربر): ' . $reason]);
            save_audit((int) $user['id'], 'ticket_reopened_by_user', $ticketId, ['reason' => $reason]);
            log_ticket_event($ticketId, (int) $user['id'], 'reopened_by_user', 'resolved', $reopenStatus, ['reason' => $reason]);
            notify_ticket_staff($ticketId, 'ticket_reopened', 'کاربر اعلام کرد مشکل حل نشده است', 'درخواست ' . ticket_number($ticketId) . ' توسط ثبت‌کننده بازگشایی شد: ' . $reason, (int) $user['id']);
            db_commit('index.php');
            flash('info', 'درخواست دوباره برای رسیدگی ارسال شد.');
            redirect('index.php?page=ticket&id=' . $ticketId);
        }

        if ($action === 'reply') {
            if (!user_can($user, 'ticket.reply')) {
                throw new RuntimeException('دسترسی پاسخ به درخواست برای نقش شما مجاز نیست.');
            }
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $ticket = fetch_ticket($ticketId);
            if (!$ticket || !can_view_ticket($ticket, $user)) {
                throw new RuntimeException('درخواست پیدا نشد یا دسترسی ندارید.');
            }
            $isStaffUser = is_staff_role((string) $user['role']) || user_is_primary_admin($user);
            $asRequester = ticket_user_is_requester($ticket, $user);
            if ((int) $ticket['requester_id'] === (int) $user['id'] && $ticket['status'] === 'closed' && !ticket_rating_is_current($ticket)) {
                throw new RuntimeException('ثبت امتیاز رضایت برای این درخواست الزامی است.');
            }
            if ($ticket['status'] === 'cancelled') {
                throw new RuntimeException('درخواست لغوشده قابل ادامهٔ مکالمه نیست؛ برای نیاز جدید، درخواست تازه ثبت کنید.');
            }
            if ($ticket['status'] === 'closed') {
                throw new RuntimeException('درخواست بسته‌شده قابل ویرایش نیست.');
            }
            if ($asRequester && $ticket['status'] === 'resolved') {
                throw new RuntimeException('این درخواست حل‌شده است. امتیاز خود را ثبت کنید، یا اگر مشکل برطرف نشده «مشکل حل نشده» را بزنید.');
            }
            $body = post_value('body');
            if ($body === '') {
                throw new RuntimeException('متن پاسخ را وارد کنید.');
            }
            if (!empty($_POST['is_internal']) && (!$isStaffUser || $asRequester)) {
                throw new RuntimeException('یادداشت داخلی برای درخواست‌کننده مجاز نیست؛ پیام عمومی ارسال کنید.');
            }
            $internal = !empty($_POST['is_internal']) ? 1 : 0;
            $oldStatus = (string) $ticket['status'];
            $newStatus = $oldStatus;
            $cancelPendingReply = !empty($ticket['cancel_requested_at']);
            // بازگشت خودکار از انتظار با پاسخ کاربر؛ هنگام لغو در انتظار، فقط گفت‌وگو ثبت می‌شود.
            $implicitRequesterResume = false;
            if (!$internal) {
                if ($asRequester) {
                    // پاسخ کاربر فقط وقتی وضعیت را عوض می‌کند که درخواست منتظر او بوده است.
                    if (ticket_is_waiting_status($oldStatus)) {
                        if ($cancelPendingReply) {
                            $newStatus = $oldStatus;
                        } else {
                            $newStatus = !empty($ticket['assigned_to']) ? 'in_progress' : 'manager_review';
                            $implicitRequesterResume = true;
                        }
                    }
                } else {
                    if (ticket_reply_starts_work($ticket, $user)) {
                        $newStatus = 'in_progress';
                    }
                    $afterStatus = valid_choice(post_value('after_status'), ['', 'waiting_user', 'resolved'], '');
                    if ($afterStatus !== '') {
                        if ($cancelPendingReply) {
                            throw new RuntimeException('لغو این درخواست در انتظار تصمیم مسئول است؛ ابتدا آن را تأیید یا رد کنید.');
                        }
                        if (!user_can($user, 'ticket.edit') || ticket_handler_level($ticket, $user) === '') {
                            throw new RuntimeException('فقط کارشناس مسئول یا مدیر واحد می‌تواند وضعیت درخواست را تغییر دهد.');
                        }
                        if (empty($ticket['assigned_to'])) {
                            throw new RuntimeException('برای تغییر وضعیت، ابتدا درخواست باید به کارشناس ارجاع شود.');
                        }
                        if (!in_array($afterStatus, ticket_allowed_transitions($oldStatus), true)) {
                            throw new RuntimeException('تغییر وضعیت از «' . status_label($oldStatus) . '» به «' . status_label($afterStatus) . '» مجاز نیست.');
                        }
                        $newStatus = $afterStatus;
                    } elseif ($cancelPendingReply && $newStatus !== $oldStatus) {
                        // هنگام درخواست لغو، پاسخ کارشناس وضعیت خودکار را هم عوض نمی‌کند؛ فقط گفت‌وگو ثبت می‌شود.
                        $newStatus = $oldStatus;
                    }
                }
            }
            $firstResponseSql = ($isStaffUser && !$asRequester && ticket_handler_level($ticket, $user) !== '' && !$internal) ? ', first_response_at = COALESCE(first_response_at, NOW())' : '';
            $resolvedSql = ($newStatus === 'resolved' && $oldStatus !== 'resolved') ? ', resolved_at = NOW()' : '';
            [$replyDueAt, $replyPauseAt, $replyPauseMinutes] = ticket_sla_transition($ticket, $newStatus, (string) $ticket['priority']);
            $replyOla = ticket_ola_transition($ticket, $newStatus, (string) $ticket['priority']);
            db()->beginTransaction();
            $replyLock = db()->prepare('SELECT status, cancel_requested_at, assigned_to, priority FROM tickets WHERE id = ? FOR UPDATE');
            $replyLock->execute([$ticketId]);
            $lockedReply = $replyLock->fetch();
            if (!$lockedReply || (string) $lockedReply['status'] !== $oldStatus
                || (int) ($lockedReply['assigned_to'] ?? 0) !== (int) ($ticket['assigned_to'] ?? 0)
                || (string) $lockedReply['priority'] !== (string) $ticket['priority']) {
                throw new RuntimeException('وضعیت درخواست هم‌زمان تغییر کرد؛ پیام شما ذخیره نشد. صفحه را تازه کنید.');
            }
            if (!empty($lockedReply['cancel_requested_at']) && $newStatus !== $oldStatus) {
                if ($implicitRequesterResume) {
                    // مسابقهٔ ثبت درخواست لغو با پاسخ کاربر: پیام ثبت می‌شود، وضعیت دست‌نخورده می‌ماند.
                    $newStatus = $oldStatus;
                    [$replyDueAt, $replyPauseAt, $replyPauseMinutes] = ticket_sla_transition($ticket, $newStatus, (string) $ticket['priority']);
                    $replyOla = ticket_ola_transition($ticket, $newStatus, (string) $ticket['priority']);
                } else {
                    throw new RuntimeException('درخواست لغو هم‌زمان ثبت شد؛ تغییر وضعیت متوقف است. بدون تغییر وضعیت دوباره ارسال کنید.');
                }
            }
            $query = db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, ?)');
            $query->execute([$ticketId, $user['id'], $body, $internal]);
            $messageId = (int) db()->lastInsertId();
            store_attachment($messageId, $_FILES['attachment'] ?? []);
            $replyUpdate = db()->prepare('UPDATE tickets SET status = ?, due_at = ?, sla_paused_at = ?, sla_pause_minutes = ?, ola_policy_id = ?, ola_response_due_at = ?, ola_due_at = ?, ola_escalation_1_at = ?, ola_escalation_2_at = ?, updated_at = NOW()' . $firstResponseSql . $resolvedSql . ' WHERE id = ? AND status = ?');
            $replyUpdate->execute([$newStatus, $replyDueAt, $replyPauseAt, $replyPauseMinutes, $replyOla['policy_id'], $replyOla['response_due_at'], $replyOla['due_at'], $replyOla['escalation_1_at'], $replyOla['escalation_2_at'], $ticketId, $oldStatus]);
            save_audit((int) $user['id'], 'ticket_replied', $ticketId);
            if ($newStatus !== $oldStatus) {
                log_ticket_event($ticketId, (int) $user['id'], 'status_changed', $oldStatus, $newStatus);
            }
            log_ticket_event($ticketId, (int) $user['id'], $internal ? 'internal_note' : 'reply');
            if ($newStatus === 'resolved' && $oldStatus !== 'resolved') {
                notify_ticket_resolved($ticketId, (int) $user['id']);
                record_asset_history((int) ($ticket['asset_id'] ?? 0), 'ticket_resolved', 'حل مشکل درخواست ' . ticket_number($ticketId), (int) $user['id'], $ticketId, 'وضعیت به حل‌شده تغییر کرد.');
            } elseif ($internal) {
                notify_ticket_staff($ticketId, 'internal_note', 'یادداشت داخلی جدید', 'در درخواست ' . ticket_number($ticketId) . ' یادداشت داخلی ثبت شد.', (int) $user['id']);
            } elseif (!$asRequester && $isStaffUser) {
                notify_user((int) $ticket['requester_id'], 'ticket_reply', 'پاسخ جدید به درخواست', 'برای درخواست ' . ticket_number($ticketId) . ' پاسخ جدید ثبت شد.', (int) $ticketId);
            } else {
                notify_ticket_staff($ticketId, 'ticket_reply', 'پاسخ جدید کاربر', 'کاربر در درخواست ' . ticket_number($ticketId) . ' پاسخ جدید ثبت کرد.', (int) $user['id']);
            }
            record_asset_history((int) ($ticket['asset_id'] ?? 0), $internal ? 'ticket_internal_note' : 'ticket_reply', $internal ? 'یادداشت داخلی درخواست ' . ticket_number($ticketId) : 'پاسخ جدید در درخواست ' . ticket_number($ticketId), (int) $user['id'], $ticketId, $body);
            db_commit('index.php');
            flash('success', 'پاسخ شما ثبت شد.');
            redirect('index.php?page=ticket&id=' . $ticketId);
        }

        if ($action === 'update_ticket') {
            $staffUser = require_login();
            if (!user_can($staffUser, 'ticket.edit')) {
                throw new RuntimeException('دسترسی ویرایش درخواست برای نقش شما مجاز نیست.');
            }
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            $ticket = fetch_ticket($ticketId);
            if (!$ticket || !can_view_ticket($ticket, $staffUser)) {
                throw new RuntimeException('درخواست پیدا نشد یا به واحد شما تعلق ندارد.');
            }
            $handlerLevel = ticket_handler_level($ticket, $staffUser);
            if ($handlerLevel === '') {
                throw new RuntimeException('فقط مدیر واحد یا کارشناس مسئول همین درخواست می‌تواند آن را تغییر دهد.');
            }
            if (in_array((string) $ticket['status'], ['closed', 'cancelled'], true)) {
                throw new RuntimeException('درخواست بسته‌شده یا لغوشده از مسیر عادی قابل تغییر نیست.');
            }
            if (!empty($ticket['cancel_requested_at'])) {
                throw new RuntimeException('لغو این درخواست در انتظار تصمیم مسئول است؛ ابتدا آن را تأیید یا رد کنید.');
            }
            $oldStatus = (string) $ticket['status'];
            $status = valid_choice(post_value('status'), ['new','manager_review','assigned','in_progress','waiting_user','pending','resolved','closed'], $oldStatus);
            if ($status === 'closed') {
                throw new RuntimeException('بستن نهایی فقط از طریق تأیید سوپروایزر انجام می‌شود.');
            }
            $canAssign = $handlerLevel === 'manager';
            // کارشناس مسئول فقط وضعیت کار خودش را عوض می‌کند؛ ارجاع و اولویت با مدیر واحد است.
            $priority = $canAssign ? valid_choice(post_value('priority'), ['normal','urgent','critical'], (string) $ticket['priority']) : (string) $ticket['priority'];
            $assignee = $canAssign ? ((int) ($_POST['assigned_to'] ?? 0) ?: null) : ((int) ($ticket['assigned_to'] ?? 0) ?: null);
            if (!$canAssign && !in_array($status, ['in_progress', 'waiting_user', 'resolved', $oldStatus], true)) {
                throw new RuntimeException('این وضعیت فقط توسط مدیر واحد قابل تنظیم است.');
            }
            $assigneeChanged = (int) $assignee !== (int) ($ticket['assigned_to'] ?? 0);
            if ($oldStatus === 'resolved' && $status === 'resolved' && $assigneeChanged) {
                throw new RuntimeException('برای تغییر مسئول درخواست حل‌شده، ابتدا وضعیت را برای ادامهٔ رسیدگی تغییر دهید.');
            }
            $ticketGroupForAssign = (string) ($ticket['service_group'] ?? 'it');
            if ($assignee) {
                 $assigneeQuery = db()->prepare('SELECT u.id, u.department_id, u.role, u.is_active, u.is_it_agent, u.is_primary_admin, hu.code AS handling_unit_code FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.id = ? AND u.is_active = 1 LIMIT 1');
                 $assigneeQuery->execute([$assignee]);
                 $assigneeRow = $assigneeQuery->fetch();
                 $assigneeRole = (string) ($assigneeRow['role'] ?? '');
                 $assigneeHu = (string) ($assigneeRow['handling_unit_code'] ?? '');
                 $assigneeGroupOk = $assigneeRow
                     && ((user_is_primary_admin($assigneeRow) && $ticketGroupForAssign === 'it')
                         || ($assigneeRole === 'support_manager' && $ticketGroupForAssign === 'support')
                         || (in_array($assigneeRole, ['agent', 'manager'], true) && $assigneeHu === $ticketGroupForAssign));
                 $assigneeDepartmentOk = $assigneeRole !== 'manager'
                     || ((int) ($ticket['department_id'] ?? 0) > 0
                         && (int) ($assigneeRow['department_id'] ?? 0) === (int) $ticket['department_id']);
                 $staffGroupOk = true;
                 if (($staffUser['role'] ?? '') === 'support_manager' && $ticketGroupForAssign !== 'support') {
                     $staffGroupOk = false;
                 } elseif (in_array(($staffUser['role'] ?? ''), ['agent', 'manager'], true) && user_service_group($staffUser) !== $ticketGroupForAssign) {
                     $staffGroupOk = false;
                 }
                 $staffDept = (int) ($staffUser['department_id'] ?? 0);
                 $deptOk = true;
                 if (($staffUser['role'] ?? '') === 'manager' && user_service_group($staffUser) === 'support' && $staffDept > 0) {
                     $deptOk = (int) ($assigneeRow['department_id'] ?? 0) === $staffDept
                         && (int) ($ticket['department_id'] ?? 0) === $staffDept;
                 }
                 if (!$assigneeRow || !ticket_assignee_is_eligible($ticket, $assigneeRow, 'ticket_notification_user_can') || !$assigneeGroupOk || !$assigneeDepartmentOk || !$staffGroupOk || !$deptOk) {
                    throw new RuntimeException('کارشناس انتخاب‌شده به این واحد تعلق ندارد.');
                }
            }
            if ($assignee && in_array($status, ['new', 'manager_review'], true)) {
                $status = 'assigned';
            }
            if (!$assignee) {
                if (in_array($status, ['assigned', 'in_progress', 'waiting_user', 'pending'], true)) {
                    $status = 'manager_review';
                } elseif ($status === 'resolved') {
                    throw new RuntimeException('برای «حل‌شده» درخواست باید کارشناس مسئول داشته باشد.');
                }
            }
            if ($status !== $oldStatus && !in_array($status, ticket_allowed_transitions($oldStatus), true)) {
                throw new RuntimeException('تغییر وضعیت از «' . status_label($oldStatus) . '» به «' . status_label($status) . '» مجاز نیست.');
            }
            $updateNote = post_value('update_note');
            if ($status !== $oldStatus && in_array($status, ['waiting_user', 'resolved'], true) && $updateNote === '') {
                throw new RuntimeException($status === 'waiting_user'
                    ? 'برای «در انتظار پاسخ کاربر»، سؤال یا اطلاعات لازم را در پیام بنویسید تا کاربر بداند چه چیزی ارسال کند.'
                    : 'برای «حل‌شده»، شرح راه‌حل یا نتیجهٔ کار را در پیام بنویسید.');
            }
            if (mb_strlen($updateNote, 'UTF-8') > 5000) {
                throw new RuntimeException('پیام مدیریت درخواست نباید بیشتر از ۵۰۰۰ نویسه باشد.');
            }
            $fields = 'status = ?, priority = ?, assigned_to = ?, updated_at = NOW()';
            $params = [$status, $priority, $assignee];
            if ($assignee) {
                $fields .= $assigneeChanged ? ', assigned_at = NOW()' : ', assigned_at = COALESCE(assigned_at, NOW())';
            } else {
                $fields .= ', assigned_at = NULL';
            }
            if ($status === 'resolved') {
                $fields .= ', resolved_at = COALESCE(resolved_at, NOW())';
            } else {
                $fields .= ', resolved_at = NULL';
            }
             $slaMinutes = priority_sla_minutes($priority);
             [$newDueAt, $newPauseAt, $newPauseMinutes] = ticket_sla_transition($ticket, $status, $priority);
             $ola = ticket_ola_transition($ticket, $status, $priority);
             $fields .= ', sla_minutes = ?, due_at = ?, ola_policy_id = ?, ola_response_due_at = ?, ola_due_at = ?, ola_escalation_1_at = ?, ola_escalation_2_at = ?, sla_paused_at = ?, sla_pause_minutes = ?';
             array_push($params, $slaMinutes, $newDueAt, $ola['policy_id'], $ola['response_due_at'], $ola['due_at'], $ola['escalation_1_at'], $ola['escalation_2_at'], $newPauseAt, $newPauseMinutes);
            $params[] = $ticketId;
            $params[] = $oldStatus;
            db()->beginTransaction();
            $updateLock = db()->prepare('SELECT status, cancel_requested_at, assigned_to, priority FROM tickets WHERE id = ? FOR UPDATE');
            $updateLock->execute([$ticketId]);
            $lockedTicket = $updateLock->fetch();
            if (!$lockedTicket || (string) $lockedTicket['status'] !== $oldStatus || !empty($lockedTicket['cancel_requested_at'])
                || (int) ($lockedTicket['assigned_to'] ?? 0) !== (int) ($ticket['assigned_to'] ?? 0)
                || (string) $lockedTicket['priority'] !== (string) $ticket['priority']) {
                throw new RuntimeException('وضعیت یا درخواست لغو درخواست هم‌زمان تغییر کرد؛ صفحه را تازه کنید.');
            }
            db()->prepare('UPDATE tickets SET ' . $fields . ' WHERE id = ? AND status = ? AND cancel_requested_at IS NULL')->execute($params);
            save_audit((int) $staffUser['id'], 'ticket_updated', $ticketId, ['status' => $status, 'priority' => $priority]);
            if ($updateNote !== '') {
                db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 0)')->execute([$ticketId, (int) $staffUser['id'], $updateNote]);
                log_ticket_event($ticketId, (int) $staffUser['id'], 'reply');
                record_asset_history((int) ($ticket['asset_id'] ?? 0), 'ticket_reply', 'پیام مدیریت در درخواست ' . ticket_number($ticketId), (int) $staffUser['id'], $ticketId, $updateNote);
            }
            if ($status !== $oldStatus) {
                log_ticket_event($ticketId, (int) $staffUser['id'], $assignee && $assigneeChanged ? 'assigned' : 'status_changed', $oldStatus, $status, ['assigned_to' => $assignee]);
                if ($status === 'resolved') {
                    record_asset_history((int) ($ticket['asset_id'] ?? 0), 'ticket_resolved', 'حل مشکل درخواست ' . ticket_number($ticketId), (int) $staffUser['id'], $ticketId, 'وضعیت به ' . status_label($status) . ' تغییر کرد.');
                }
            } elseif ($assigneeChanged) {
                log_ticket_event($ticketId, (int) $staffUser['id'], 'assigned', $oldStatus, $status, ['assigned_to' => $assignee]);
            }
            if ((string) $ticket['priority'] !== $priority) {
                log_ticket_event($ticketId, (int) $staffUser['id'], 'priority_changed', $status, $status, ['from' => (string) $ticket['priority'], 'to' => $priority]);
            }
            if ($status === 'resolved' && $oldStatus !== 'resolved') {
                notify_ticket_resolved($ticketId, (int) $staffUser['id']);
            } elseif ($status !== $oldStatus || $assigneeChanged) {
                notify_ticket_parties($ticketId, 'ticket_updated', 'درخواست به‌روزرسانی شد', 'وضعیت درخواست ' . ticket_number($ticketId) . ' به «' . status_label($status) . '» تغییر کرد.', (int) $staffUser['id']);
            }
            db_commit('index.php');
            flash('success', 'تغییرات درخواست ذخیره شد.');
            redirect('index.php?page=ticket&id=' . $ticketId);
        }
