<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/**
 * Request Center request detail and requester/handler interaction page.
 *
 * Executes in the legacy front-controller scope during the incremental move.
 * Keep current helpers, permissions, POST action names, and redirects intact.
 */

if ($page === 'ticket') {
    // Preserve the originating Request Center workspace when returning from a
    // detail view. Only known workspace names and server-authorized views are
    // accepted; arbitrary return URLs are never trusted.
    $detailReturnWorkspace = (string) ($_GET['workspace'] ?? '');
    $detailReturnAllowed = [
        'requester' => user_can($user, 'ticket.create') || user_can($user, 'ticket.view_own'),
        'agent' => user_can($user, 'ticket.edit') || user_can($user, 'queue.view')
            || user_can($user, 'ticket.view_unit') || user_can($user, 'ticket.view_all'),
        'supervisor' => user_can($user, 'supervisor.panel'),
        'admin' => user_is_primary_admin($user),
    ];
    $detailReturnParams = [];
    if (isset($detailReturnAllowed[$detailReturnWorkspace]) && $detailReturnAllowed[$detailReturnWorkspace]) {
        $detailReturnParams['workspace'] = $detailReturnWorkspace;
        $requestedReturnPage = (string) ($_GET['return_page'] ?? '');
        if ($detailReturnWorkspace === 'agent' && $requestedReturnPage === 'queue'
            && user_can($user, 'queue.view')) {
            $detailReturnParams['page'] = 'queue';
        } elseif ($detailReturnWorkspace === 'supervisor' && $requestedReturnPage === 'supervisor'
            && user_can($user, 'supervisor.panel')) {
            $detailReturnParams['page'] = 'supervisor';
        }
        if (isset($detailReturnParams['page'])) {
            $detailReturnParams['return_page'] = $requestedReturnPage;
        }
        if ($detailReturnWorkspace === 'agent') {
            $requestedReturnAgentView = (string) ($_GET['agent_view'] ?? 'mine');
            $detailReturnParams['agent_view'] = $requestedReturnAgentView === 'team'
                && user_can($user, 'queue.view') ? 'team' : 'mine';
            $requestedQueue = (string) ($_GET['queue'] ?? 'all');
            $detailReturnParams['queue'] = in_array($requestedQueue, ['all','unassigned','overdue','mine','waiting_user','resolved','cancellation_requested','cancelled','rejected'], true) ? $requestedQueue : 'all';
        }
        foreach (['q' => 100, 'status' => 32, 'priority' => 16, 'service_group' => 16, 'from' => 24, 'to' => 24] as $contextKey => $maxLength) {
            $contextValue = trim((string) ($_GET[$contextKey] ?? ''));
            if ($contextValue !== '' && strlen($contextValue) <= $maxLength) {
                $detailReturnParams[$contextKey] = $contextValue;
            }
        }
        foreach (['department_id', 'assigned_to'] as $returnIdKey) {
            $returnId = (int) ($_GET[$returnIdKey] ?? 0);
            if ($returnId > 0) {
                $detailReturnParams[$returnIdKey] = $returnId;
            }
        }
        $requestedTicketType = valid_choice((string) ($_GET['ticket_type'] ?? ''), ['incident', 'request', 'problem', 'change'], '');
        if ($requestedTicketType !== '') {
            $detailReturnParams['ticket_type'] = $requestedTicketType;
        }
        if ((string) ($_GET['calendar'] ?? '') === '1'
            && in_array($detailReturnWorkspace, ['agent', 'supervisor', 'admin'], true)) {
            $detailReturnParams['calendar'] = 1;
            $requestedCalendarMonth = (string) ($_GET['jmonth'] ?? '');
            if (preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $requestedCalendarMonth)) {
                $detailReturnParams['jmonth'] = $requestedCalendarMonth;
            }
            $detailReturnParams['view'] = (string) ($_GET['view'] ?? '') === 'agenda' ? 'agenda' : 'month';
            $requestedCalendarKind = (string) ($_GET['kind'] ?? '');
            if (in_array($requestedCalendarKind, ['sla', 'work', 'visit', 'await', 'followup'], true)) {
                $detailReturnParams['kind'] = $requestedCalendarKind;
            }
        }
        $detailReturnParams['p'] = max(1, min(100000, (int) ($_GET['p'] ?? 1)));
    }
    $detailReturnUrl = 'request-center.php' . ($detailReturnParams !== []
        ? '?' . http_build_query($detailReturnParams)
        : '');
    $ticket = fetch_ticket((int) ($_GET['id'] ?? 0));
    if (!$ticket || !can_view_ticket($ticket, $user)) {
        http_response_code(404);
        render_header('درخواست پیدا نشد', $user);
        echo '<section class="empty-state"><div class="empty-icon">؟</div><h2>درخواست پیدا نشد</h2><a class="button" href="' . e($detailReturnUrl) . '">بازگشت</a></section>';
        render_footer();
        exit;
    }
    $messagesQuery = db()->prepare('SELECT m.*, u.full_name, u.role FROM ticket_messages m JOIN users u ON u.id = m.user_id WHERE m.ticket_id = ? ORDER BY m.created_at');
    $messagesQuery->execute([$ticket['id']]);
    $messages = $messagesQuery->fetchAll();
    $attachments = [];
    if ($messages) {
        $ids = array_column($messages, 'id');
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $attachmentQuery = db()->prepare('SELECT * FROM ticket_attachments WHERE message_id IN (' . $marks . ') ORDER BY id');
        $attachmentQuery->execute($ids);
        foreach ($attachmentQuery->fetchAll() as $attachment) {
            $attachments[(int) $attachment['message_id']][] = $attachment;
        }
    }
    $staff = is_staff_role($user['role']) || user_is_primary_admin($user);
    $canManageTicket = $staff && user_can($user, 'ticket.edit');
    $isRequester = ticket_user_is_requester($ticket, $user);
    $requesterView = $isRequester;
    $canCancelOwnTicket = ticket_requester_can_cancel($ticket, $user);
    $handlerLevel = ticket_handler_level($ticket, $user);
    // نمای فعال: 'requester' | 'assignee' | 'supervisor'. تنها منبع حقیقتِ «چه چیزی
    // نشان داده شود»، تا نما و اختیار از هم جدا نیفتند.
    $viewMode = ticket_view_mode($ticket, $user);
    $canSeeInternal = ticket_can_see_internal($ticket, $user);
    $cancelHasRequest = !empty($ticket['cancel_requested_at']);
    $cancelDecidable = $cancelHasRequest
        && in_array((string) $ticket['status'], ticket_requester_cancelable_statuses(), true);
    $cancelStaleClosed = $cancelHasRequest
        && in_array((string) $ticket['status'], ['closed', 'cancelled'], true);
    $cancelRequestPending = $cancelDecidable || $cancelStaleClosed;
    $canDecideCancellation = $cancelRequestPending
        && ($handlerLevel !== '' || (request_center_can_supervise_ticket($ticket, $user) && user_can($user, 'supervisor.decide')))
        && (int) ($ticket['cancel_requested_by'] ?? 0) !== (int) $user['id'];
    $canAssignTicket = $handlerLevel === 'manager';
    $canSupervisorAssign = $handlerLevel === ''
        && request_center_can_supervise_ticket($ticket, $user)
        && in_array((string) $ticket['status'], ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending'], true)
        && !$cancelHasRequest;
    $agents = [];
    if (($canAssignTicket && !$cancelHasRequest && in_array((string) $ticket['status'], ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending'], true)) || $canSupervisorAssign) {
         if ($canAssignTicket || $canSupervisorAssign) {
              $ticketGroupForList = (string) ($ticket['service_group'] ?? 'it');
              $agentSql = 'SELECT u.id, u.full_name, u.username, u.role, u.is_active, u.is_primary_admin, u.department_id, u.is_it_agent, hu.code AS handling_unit_code FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.is_active = 1 AND (((u.role = "primary_admin" OR (u.role = "admin" AND u.is_primary_admin = 1)) AND ? = "it") OR (u.role = "support_manager" AND ? = "support") OR (u.role IN ("agent", "manager") AND hu.code = ?)) AND (u.role <> "manager" OR (? > 0 AND u.department_id = ?))';
              $ticketDepartmentForList = (int) ($ticket['department_id'] ?? 0);
              $agentParams = [$ticketGroupForList, $ticketGroupForList, $ticketGroupForList, $ticketDepartmentForList, $ticketDepartmentForList];
              if (($user['role'] ?? '') === 'manager' && $ticketGroupForList === 'support' && (int) ($user['department_id'] ?? 0) > 0) {
                  $agentSql .= ' AND u.department_id = ?';
                  $agentParams[] = (int) $user['department_id'];
              }
              $agentSql .= ' ORDER BY u.full_name';
              $agentQuery = db()->prepare($agentSql);
              $agentQuery->execute($agentParams);
              $agents = array_values(array_filter($agentQuery->fetchAll(), static fn ($candidate) => ticket_assignee_is_eligible($ticket, $candidate, 'ticket_notification_user_can')));
        }

    }
    $requiresRating = $isRequester && in_array($ticket['status'], ['resolved', 'closed'], true) && !ticket_rating_is_current($ticket);
    // «مرحلهٔ جاری و گام بعدی» — راهنمای کوتاهِ دامنه‌دار برای همین بیننده.
    // فقط خواندنی؛ هیچ وضعیتی را تغییر نمی‌دهد و از همان اختیاراتی می‌آید که
    // فرم‌های همین صفحه را کنترل می‌کنند.
    $guidanceStage = '';
    $guidanceNext = '';
    $guidanceActions = [];
    $ticketStatus = (string) $ticket['status'];
    if ($cancelRequestPending) {
        $guidanceStage = 'لغو در انتظار تصمیم';
        $guidanceNext = $isRequester
            ? 'درخواست لغو شما ثبت شده و منتظر تصمیم مسئول رسیدگی است؛ تا آن زمان کاری بر عهدهٔ شما نیست.'
            : 'ثبت‌کننده درخواست لغو داده است؛ نتیجه را در همین صفحه با ذکر دلیل ثبت کنید.';
    } elseif ($isRequester) {
        if (in_array($ticketStatus, ['new', 'manager_review'], true)) {
            $guidanceStage = 'ثبت‌شده — در انتظار ارجاع';
            $guidanceNext = 'واحد مربوطه درخواست را بررسی و کارشناس مسئول را مشخص می‌کند؛ به اقدام شما نیازی نیست.';
            if ($canCancelOwnTicket) {
                $guidanceActions[] = 'اگر دیگر به این درخواست نیاز ندارید، می‌توانید آن را لغو کنید.';
            }
        } elseif (in_array($ticketStatus, ['assigned', 'in_progress'], true)) {
            $guidanceStage = 'در حال رسیدگی';
            $guidanceNext = 'کارشناس مسئول روی درخواست کار می‌کند؛ اگر پاسخی از شما خواسته شود، در بخش پاسخ ثبت کنید.';
            if ($canCancelOwnTicket) {
                $guidanceActions[] = 'می‌توانید درخواست لغو را با ذکر دلیل ثبت کنید.';
            }
        } elseif (in_array($ticketStatus, ['waiting_user', 'pending'], true)) {
            $guidanceStage = 'منتظر پاسخ شما';
            $guidanceNext = 'رسیدگی تا پاسخ شما متوقف است؛ لطفاً پاسخ را در همین صفحه ثبت کنید.';
        } elseif ($ticketStatus === 'resolved') {
            $guidanceStage = 'حل‌شده — منتظر نظر شما';
            $guidanceNext = 'اگر مشکل برطرف شده امتیاز رضایت خود را ثبت کنید؛ اگر هنوز ادامه دارد «بازگشایی» را انتخاب کنید.';
            if (!ticket_rating_is_current($ticket)) {
                $guidanceActions[] = 'ثبت امتیاز رضایت (برای بسته‌شدن نهایی لازم است).';
            }
        } elseif ($ticketStatus === 'closed') {
            $guidanceStage = 'بسته شده';
            $guidanceNext = 'پروندهٔ این درخواست بسته است؛ در صورت نیاز درخواست جدیدی ثبت کنید.';
        } elseif ($ticketStatus === 'rejected') {
            $guidanceStage = 'رد شده';
            $guidanceNext = 'درخواست رد شده است؛ دلیل رد را در تاریخچهٔ رویدادها ببینید و در صورت نیاز درخواست جدیدی با توضیح کامل‌تر ثبت کنید.';
        } elseif ($ticketStatus === 'cancelled') {
            $guidanceStage = 'لغو شده';
            $guidanceNext = 'این درخواست لغو شده است.';
        }
    } elseif ($staff) {
        if (in_array($ticketStatus, ['new', 'manager_review'], true)) {
            $guidanceStage = empty($ticket['assigned_to']) ? 'بدون مسئول' : 'ارجاع‌شده';
            $guidanceNext = empty($ticket['assigned_to'])
                ? 'این درخواست هنوز کارشناس مسئول ندارد؛ اگر مجاز باشید می‌توانید از همین صفحه آن را بردارید (یا منتظر ارجاع مدیر/سوپروایزر بمانید).'
                : 'ارجاع انجام شده است؛ مسئول رسیدگی پیگیری می‌کند.';
        } elseif (in_array($ticketStatus, ['assigned', 'in_progress'], true)) {
            $guidanceStage = 'در حال رسیدگی';
            $guidanceNext = $viewMode === 'supervisor'
                ? 'کارشناس در حال رسیدگی است؛ پیشرفت را دنبال کنید و در صورت نیاز بازتخصیص یا تصمیم بگیرید.'
                : 'پاسخ یا تغییر وضعیت را ثبت کنید؛ پس از رفع مشکل، وضعیت را به «حل شد» تغییر دهید.';
        } elseif (in_array($ticketStatus, ['waiting_user', 'pending'], true)) {
            $guidanceStage = 'منتظر پاسخ کاربر';
            $guidanceNext = 'کاربر باید پاسخ دهد؛ در صورت عدم پاسخ پیگیری کنید یا یادداشت داخلی ثبت کنید.';
        } elseif ($ticketStatus === 'resolved') {
            $guidanceStage = 'حل‌شده — منتظر امتیاز کاربر';
            $guidanceNext = 'پس از ثبت امتیاز یا بستن خودکار، سوپروایزر تأیید نهایی می‌کند (یا برای اصلاح بازمی‌گرداند).';
        } elseif ($ticketStatus === 'closed') {
            $guidanceStage = 'بسته شده';
            $guidanceNext = 'پرونده بسته است؛ فقط تاریخچه در دسترس است.';
        } elseif ($ticketStatus === 'rejected') {
            $guidanceStage = 'رد شده';
            $guidanceNext = 'درخواست رد شده است؛ پرونده بسته است.';
        } elseif ($ticketStatus === 'cancelled') {
            $guidanceStage = 'لغو شده';
            $guidanceNext = 'درخواست لغو شده است؛ پرونده بسته است.';
        }
    }
    if ($guidanceStage === '') {
        $guidanceStage = request_center_status_label($ticketStatus);
        $guidanceNext = 'جزئیات پرونده در همین صفحه در دسترس است.';
    }
    render_header('جزئیات ' . ticket_number((int) $ticket['id']), $user);
    echo '<style id="rc-ticket-detail-page">
.page-heading{margin-bottom:16px}.page-heading h1{font-size:23px;line-height:1.7;overflow-wrap:anywhere}.back-link{display:inline-flex;margin-bottom:7px;color:#526f91;font-size:12px;text-decoration:none}.back-link:hover{color:#1f548f;text-decoration:underline}
.guidance-card{border-inline-start:3px solid #2459a6}.guidance-card ul{margin:10px 0 0;padding-inline-start:18px;color:#334b68;font-size:12px;line-height:2}.ticket-meta{display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-top:10px}.ticket-meta>span{padding:4px 9px;border:1px solid #e1e8f1;border-radius:999px;background:#fff;color:#5c6f87;font-size:10px}.ticket-meta .status,.ticket-meta .priority{font-style:normal;font-weight:700}
.ticket-layout{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:16px;align-items:start}
.conversation,.ticket-sidebar .card{min-width:0;border:1px solid #e2e9f2;border-radius:14px;background:#fff;box-shadow:0 5px 18px rgba(23,43,77,.04)}
.conversation{padding:clamp(14px,2vw,22px)}
.message{position:relative;margin:0 0 13px;padding:14px 15px;border:1px solid #e8edf4;border-radius:12px;background:#fbfcfe;min-width:0}
.message.mine{border-color:#d8e7f7;background:#f3f8ff}
.message.agent{border-inline-start:3px solid #4380bd}
.message.supervisor{border-inline-start:3px solid #7b61b4}
.message.internal{border-color:#f0dfad;background:#fff9e8}
.message-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px;color:#334c69;font-size:12px}.message-head span{color:#8290a3;font-size:10px;white-space:nowrap}
.message-body{color:#263b55;font-size:13px;line-height:2;overflow-wrap:anywhere}
.attachments{display:flex;gap:7px;flex-wrap:wrap;margin-top:11px}
.attachment{display:inline-flex;max-width:100%;padding:6px 10px;border:1px solid #dce6f1;border-radius:8px;background:#fff;color:#245b91;font-size:11px;text-decoration:none;overflow-wrap:anywhere}
.ticket-sidebar{display:grid;gap:13px;min-width:0}.ticket-sidebar .card{padding:16px}
.ticket-sidebar h3{margin:0 0 12px;color:#243e5e;font-size:14px}
.ticket-sidebar dl{display:grid;grid-template-columns:minmax(90px,.7fr) minmax(0,1.3fr);gap:8px 10px;margin:0}
.ticket-sidebar dt{color:#7a899d;font-size:10px}.ticket-sidebar dd{color:#334b68;font-size:11px;overflow-wrap:anywhere}
.reply-form,.ticket-sidebar form{min-width:0}.reply-form label,.ticket-sidebar label{display:grid;gap:6px;color:#526780;font-size:11px}
.reply-form textarea,.reply-form input:not([type=hidden]):not([type=file]),.ticket-sidebar input:not([type=hidden]),.ticket-sidebar select,.ticket-sidebar textarea{width:100%;max-width:100%;min-height:39px;padding:9px 11px;border:1px solid #d9e2ee;border-radius:8px;background:#fff;color:#172b4d;font:inherit;font-size:12px}
.reply-form textarea,.ticket-sidebar textarea{min-height:95px;resize:vertical;line-height:1.8}
.reply-form textarea:focus,.reply-form input:focus,.ticket-sidebar input:focus,.ticket-sidebar select:focus,.ticket-sidebar textarea:focus{outline:0;border-color:#709bc9;box-shadow:0 0 0 3px rgba(36,89,166,.11)}
.reply-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin-top:10px}
.reply-actions .button{min-height:38px}.reply-actions .file-input{max-width:100%}
.cancellation-pending{padding:14px;border:1px solid #f0d69b;border-radius:11px;background:#fff9e8}
@media(max-width:980px){.ticket-layout{grid-template-columns:minmax(0,1fr) 280px;gap:12px}}
@media(max-width:760px){.ticket-layout{grid-template-columns:1fr}.ticket-sidebar{grid-row:1}.ticket-sidebar .card{padding:14px}.page-heading h1{font-size:20px}}
@media(max-width:480px){.conversation{padding:11px}.message{padding:11px}.message-head{align-items:flex-start;flex-direction:column;gap:3px}.ticket-meta>span{font-size:9px}.ticket-sidebar dl{grid-template-columns:95px minmax(0,1fr)}.reply-actions .button{width:100%}}
</style>';
     echo '<section class="page-heading"><div><a class="back-link" href="' . e($detailReturnUrl) . '">← بازگشت به فهرست</a><h1>' . e($ticket['subject']) . '</h1><div class="ticket-meta"><span>' . ticket_number((int) $ticket['id']) . '</span><span>' . persian_date($ticket['created_at']) . '</span><span class="tag">' . e(ticket_type_label((string) $ticket['ticket_type'])) . '</span>' . ($ticket['service_name'] ? '<span class="tag">' . e($ticket['service_name']) . '</span>' : '') . '<span class="status ' . e($ticket['status']) . '">' . e(request_center_status_label($ticket['status'])) . '</span><span class="priority ' . e($ticket['priority']) . '">' . e(priority_label($ticket['priority'])) . '</span></div></div></section><div class="ticket-layout" data-view-mode="' . e($viewMode) . '"><section class="conversation">';
    foreach ($messages as $message) {
        if ((int) $message['is_internal'] === 1 && !$canSeeInternal) {
            continue;
        }
         $messageRole = ((int) $message['is_internal'] === 1) ? 'internal' : (is_staff_role($message['role']) ? 'agent' : ($message['role'] === 'supervisor' ? 'supervisor' : 'user'));
         echo '<article class="message ' . $messageRole . ' ' . ((int) $message['user_id'] === (int) $user['id'] ? 'mine' : '') . '"><div class="message-head"><strong>' . e($message['full_name']) . '</strong><span>' . persian_date($message['created_at']) . '</span></div><div class="message-body">' . nl2br(e($message['body'])) . '</div>';
        if (!empty($attachments[(int) $message['id']])) {
            echo '<div class="attachments">';
            foreach ($attachments[(int) $message['id']] as $attachment) {
                 echo '<a class="attachment" href="request-center.php?action=download_attachment&id=' . (int) $attachment['id'] . '">📎 ' . e($attachment['original_name']) . '</a>';
            }
            echo '</div>';
        }
        echo '</article>';
    }
     if ($cancelRequestPending) {
        $pendingNote = $cancelStaleClosed
            ? 'این درخواست مربوط به پیش از بسته‌شدن است و باید پاک‌سازی شود.'
            : 'تا تصمیم مسئول، گفت‌وگو ادامه دارد اما تغییر وضعیت و ارجاع متوقف است.';
        echo '<div class="card cancellation-pending"><h3>درخواست لغو در انتظار بررسی مسئول است</h3><p><strong>دلیل ثبت‌شده:</strong> ' . nl2br(e((string) ($ticket['cancel_request_reason'] ?? ''))) . '</p><small class="muted">درخواست در ' . e(persian_date((string) $ticket['cancel_requested_at'])) . ' ثبت شده است. ' . $pendingNote . '</small></div>';
    } elseif ($canCancelOwnTicket) {
        $cancelNeedsApproval = !ticket_requester_can_cancel_directly($ticket);
        $cancelButton = $cancelNeedsApproval ? 'ثبت درخواست لغو' : 'لغو درخواست پیش از ارجاع';
        $cancelConfirm = $cancelNeedsApproval
            ? 'درخواست لغو ثبت می‌شود؛ تا تصمیم مسئول، گفت‌وگو ادامه دارد اما تغییر وضعیت متوقف است. ادامه می‌دهید؟'
            : 'درخواست پیش از ارجاع به کارشناس فوراً لغو می‌شود؛ سابقه حفظ خواهد شد. ادامه می‌دهید؟';
        $cancelHelp = $cancelNeedsApproval
            ? 'چون درخواست ارجاع شده یا رسیدگی آغاز شده است، ابتدا درخواست شما ثبت می‌شود و مسئول همان حوزه باید آن را تأیید کند؛ تا آن زمان گفت‌وگو ادامه دارد اما تغییر وضعیت و ارجاع متوقف است.'
            : 'چون هنوز به کارشناس ارجاع نشده است، لغو فوراً انجام می‌شود؛ حذف فیزیکی نمی‌شود و تاریخچه می‌ماند.';
        echo '<form class="card cancel-ticket-card" method="post" data-confirm="' . e($cancelConfirm) . '">' . csrf_field() . '<input type="hidden" name="action" value="cancel_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>دیگر به این درخواست نیاز ندارید؟</h3><label>دلیل لغو<textarea name="cancel_reason" required maxlength="1000" rows="2" placeholder="مثلاً درخواست اشتباهی ثبت شده است…"></textarea></label><button class="button danger-button" type="submit">' . e($cancelButton) . '</button><small class="muted">' . e($cancelHelp) . '</small></form>';
    }
    if ($canDecideCancellation) {
        if ($cancelStaleClosed) {
            echo '<form class="card cancellation-decision" method="post">' . csrf_field() . '<input type="hidden" name="action" value="decide_ticket_cancellation"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>پاک‌سازی درخواست لغو قدیمی</h3><p class="muted">درخواست قبلاً ' . e(request_center_status_label((string) $ticket['status'])) . ' شده است؛ این درخواست قدیمی باید با ذکر دلیل پاک‌سازی شود.</p><label>یادداشت پاک‌سازی (الزامی)<textarea name="decision_note" required maxlength="1000" rows="2" placeholder="مثلاً: درخواست پیش از بسته‌شدن ثبت شده بود؛ بایگانی شد."></textarea></label><div class="actions"><button class="button secondary" type="submit" name="decision" value="reject">پاک‌سازی درخواست قدیمی</button></div></form>';
        } else {
            echo '<form class="card cancellation-decision" method="post">' . csrf_field() . '<input type="hidden" name="action" value="decide_ticket_cancellation"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>تصمیم مسئول رسیدگی دربارهٔ لغو</h3><label>یادداشت تصمیم (برای رد، نوشتن دلیل الزامی است)<textarea name="decision_note" maxlength="1000" rows="2" placeholder="در صورت رد، توضیح دهید رسیدگی چگونه ادامه می‌یابد."></textarea></label><div class="actions"><button class="button danger-button" type="submit" name="decision" value="approve" data-confirm="درخواست لغو را تأیید کنید؟ با این کار درخواست از صف فعال خارج می‌شود و سابقه حفظ می‌ماند.">تأیید لغو</button><button class="button secondary" type="submit" name="decision" value="reject">رد و ادامهٔ رسیدگی</button></div></form>';
        }
    }
    if ($requiresRating && !user_can($user, 'ticket.rate')) {
        echo '<div class="card"><p>دسترسی ثبت امتیاز برای حساب شما فعال نیست؛ برای بررسی دسترسی با مدیر سامانه تماس بگیرید.</p></div>';
    } elseif ($requiresRating) {
         echo '<form class="card rating-card required-rating" method="post">' . csrf_field() . '<input type="hidden" name="action" value="rate_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>ثبت امتیاز رضایت الزامی است</h3><p class="muted">برای نهایی‌شدن این درخواست، میزان رضایت خود از واحد رسیدگی را ثبت کنید.</p><div class="rating-options">';
         for ($score = 1; $score <= 5; $score++) {
             echo '<label><input type="radio" name="score" value="' . $score . '" required><span>' . $score . '★</span></label>';
         }
         echo '</div><label>نظر اختیاری<textarea name="rating_comment" rows="3" placeholder="اگر توضیحی دارید بنویسید..."></textarea></label><button class="button" type="submit">ثبت امتیاز</button></form>';
     }
     if ($ticket['status'] === 'rejected') {
         echo '<div class="card rejection-notice"><h3>این درخواست پس از ارزیابی اولیه رد شده است</h3><p><strong>دلیل تصمیم:</strong></p><p>' . nl2br(e((string) ($ticket['supervisor_note'] ?? 'دلیل ثبت نشده'))) . '</p><small class="muted">زمان تصمیم: ' . e(persian_date((string) ($ticket['rejected_at'] ?? $ticket['updated_at']))) . '</small></div>';
     } elseif ($ticket['status'] === 'cancelled') {
         echo '<div class="card"><p class="muted">این درخواست به درخواست ثبت‌کننده یا با تأیید مسئول لغو شده است. سابقه برای پیگیری حفظ می‌شود؛ برای نیاز جدید، درخواست تازه ثبت کنید.</p></div>';
     } elseif ($requesterView && $ticket['status'] === 'resolved') {
         if (!$requiresRating) {
             echo '<div class="card"><p class="muted">امتیاز شما ثبت شد. درخواست در انتظار تأیید نهایی سوپروایزر است.</p></div>';
         }
         if ($cancelHasRequest) {
             echo '<div class="card"><p class="muted">لغو این درخواست در انتظار تصمیم مسئول است؛ بازگشایی پس از تصمیم او امکان‌پذیر می‌شود.</p></div>';
         } elseif (user_can($user, 'ticket.reply')) {
             echo '<form class="card reply-box" method="post" data-confirm="با بازگشایی، درخواست دوباره وارد چرخهٔ رسیدگی می‌شود و توضیح شما در تاریخچه ثبت خواهد شد.">' . csrf_field() . '<input type="hidden" name="action" value="reopen_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>مشکل برطرف نشده است؟</h3><label>توضیح دهید چه چیزی هنوز برطرف نشده<textarea name="reopen_reason" required rows="3" placeholder="مثلاً: همچنان خطا می‌دهد..."></textarea></label><button class="button secondary" type="submit">مشکل حل نشده، بازگشایی درخواست</button></form>';
         }
     } elseif ($ticket['status'] === 'closed') {
         if (!$requiresRating) {
             echo '<div class="card"><p class="muted">این درخواست بسته شده است. برای ادامه، درخواست جدید ثبت کنید.</p></div>';
         }
     } elseif (!$requiresRating && user_can($user, 'ticket.reply')) {
         $afterOptions = (!$isRequester && user_can($user, 'ticket.edit') && $handlerLevel !== '' && !empty($ticket['assigned_to']) && !$cancelHasRequest) ? array_values(array_intersect(['waiting_user', 'resolved'], ticket_allowed_transitions((string) $ticket['status']))) : [];
         $afterSelect = '';
         if ($afterOptions) {
             $afterSelect = '<label>پس از ارسال<select name="after_status"><option value="">بدون تغییر وضعیت</option>';
             foreach ($afterOptions as $afterOption) {
                 $afterSelect .= '<option value="' . $afterOption . '">' . ($afterOption === 'resolved' ? 'حل شد (منتظر امتیاز کاربر)' : e(request_center_status_label($afterOption))) . '</option>';
             }
             $afterSelect .= '</select></label>';
         } elseif ($cancelHasRequest && $handlerLevel !== '') {
             $afterSelect = '<small class="muted">تا تصمیم لغو، تغییر وضعیت از مسیر پاسخ متوقف است؛ پیام شما به‌عنوان گفت‌وگو ثبت می‌شود.</small>';
         }
         echo '<form class="card reply-box" method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="reply"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><label>پاسخ شما<textarea name="body" required rows="5" placeholder="پیام خود را بنویسید..."></textarea></label><div class="reply-actions"><label class="file-input compact">پیوست<input type="file" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.doc,.docx,.xls,.xlsx,.txt,.zip"><small class="field-help" data-reply-attachment-feedback aria-live="polite">حداکثر ۸ مگابایت</small></label>' . ($canSeeInternal ? '<label class="check-label"><input type="checkbox" name="is_internal" value="1"> یادداشت داخلی</label>' : '') . $afterSelect . '<button class="button" type="submit">ارسال پاسخ</button></div></form>';
     }
     echo '</section>';
        echo '<aside class="ticket-sidebar"><div class="card detail-card guidance-card"><h3>مرحلهٔ جاری: ' . e($guidanceStage) . '</h3><p class="muted">' . e($guidanceNext) . '</p>';
    if ($guidanceActions !== []) {
        echo '<ul>' . implode('', array_map(static fn ($action): string => '<li>' . e((string) $action) . '</li>', $guidanceActions)) . '</ul>';
    }
    echo '</div><div class="card detail-card"><h3>اطلاعات درخواست</h3><dl><dt>ثبت‌کننده</dt><dd>' . e($ticket['requester_name']) . '</dd><dt>حوزه</dt><dd>' . (($ticket['service_group'] ?? 'it') === 'support' ? 'پشتیبانی' : 'IT') . '</dd><dt>نوع</dt><dd>' . e(ticket_type_label((string) $ticket['ticket_type'])) . '</dd><dt>خدمت</dt><dd>' . e($ticket['service_name'] ?: 'بدون خدمت') . '</dd><dt>واحد رسیدگی</dt><dd>' . e(($ticket['service_group'] ?? 'it') === 'support' ? 'واحد پشتیبانی' : 'واحد فناوری اطلاعات') . '</dd><dt>معاونت سازمانی</dt><dd>' . e($ticket['requesting_unit_name'] ?: ($ticket['department_name'] ?: 'بدون معاونت')) . '</dd><dt>دسته‌بندی</dt><dd>' . e($ticket['category_name'] ?: 'بدون دسته‌بندی') . '</dd><dt>کارشناس</dt><dd>' . e($ticket['assignee_name'] ?: 'تخصیص داده نشده') . '</dd>' . (($ticket['service_group'] ?? 'it') === 'support' ? '<dt>محل خدمت</dt><dd>' . e($ticket['support_location'] ?: 'ثبت نشده') . '</dd><dt>تجهیز / مورد</dt><dd>' . e($ticket['support_equipment'] ?: 'ثبت نشده') . '</dd>' : '<dt>سیستم</dt><dd>' . e($ticket['hostname'] ? asset_display_name($ticket['hostname']) . (asset_is_auto_domain_tag($ticket['asset_tag']) ? '' : ' (' . $ticket['asset_tag'] . ')') : 'بدون سیستم مرتبط') . '</dd>') . '<dt>مهلت SLA</dt><dd>' . e($ticket['due_at'] ? persian_date($ticket['due_at']) : 'ثبت نشده') . '</dd><dt>مهلت پاسخ OLA</dt><dd>' . e($ticket['ola_response_due_at'] ? persian_date($ticket['ola_response_due_at']) : 'ثبت نشده') . '</dd><dt>مهلت حل OLA</dt><dd>' . e($ticket['ola_due_at'] ? persian_date($ticket['ola_due_at']) : 'ثبت نشده') . '</dd>' . (ticket_rating_is_current($ticket) ? '<dt>رضایت کاربر</dt><dd>' . e((string) $ticket['rating_score']) . ' از ۵</dd>' : '') . '</dl></div>';
    if ($staff && empty($ticket['assigned_to']) && in_array($ticket['status'], ['new', 'manager_review'], true) && ticket_assignee_is_eligible($ticket, $user, 'ticket_notification_user_can') && !$cancelHasRequest) {
        echo '<div class="card detail-card"><h3>برداشتن درخواست</h3><p class="muted">این درخواست هنوز کارشناس مسئول ندارد.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="claim_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><button class="button wide" type="submit">برداشتن و شروع رسیدگی</button></form></div>';
    }
    if ($canSupervisorAssign) {
        $currentAssigneeId = (int) ($ticket['assigned_to'] ?? 0);
        $eligibleAssigneeIds = array_map('intval', array_column($agents ?? [], 'id'));
        echo '<div class="card detail-card supervisor-assignment-card"><h3>تخصیص و بازتخصیص</h3><p class="muted">سوپروایزر می‌تواند مسئول رسیدگی را در همین حوزه و معاونت تعیین یا تغییر دهد. این عملیات در زمان درخواست لغو غیرفعال است.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="supervisor_assign_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><label>کارشناس مسئول<select name="assigned_to"><option value=""' . ($currentAssigneeId === 0 ? ' selected' : '') . '>بدون مسئول (بازگشت به صف)</option>';
        if ($currentAssigneeId > 0 && !in_array($currentAssigneeId, $eligibleAssigneeIds, true)) {
            echo '<option value="' . $currentAssigneeId . '" selected>' . e((string) ($ticket['assignee_name'] ?: 'مسئول فعلی')) . ' (مسئول فعلی)</option>';
        }
        foreach (($agents ?? []) as $agent) {
            $agentId = (int) $agent['id'];
            echo '<option value="' . $agentId . '"' . ($agentId === $currentAssigneeId ? ' selected' : '') . '>' . e((string) $agent['full_name']) . '</option>';
        }
        echo '</select></label><label>دلیل تخصیص / بازتخصیص (در تاریخچه قابل مشاهده)<input type="text" name="assignment_reason" required maxlength="500" placeholder="دلیل انتخاب یا تغییر مسئول رسیدگی"></label>';
        if (ticket_can_see_internal($ticket, $user)) {
            echo '<label>یادداشت داخلی ارجاع (اختیاری)<input type="text" name="assignment_note" maxlength="500" placeholder="توضیح محرمانه برای تیم رسیدگی"></label>';
        }
        echo '<button class="button wide" type="submit">ثبت تخصیص / بازتخصیص</button></form>';
        if (empty($agents)) {
            echo '<small class="muted">در حال حاضر کارشناس فعال و واجد شرایطی برای این حوزه پیدا نشد.</small>';
        }
        echo '</div>';
    }
    if ($canManageTicket && $handlerLevel !== '' && $cancelHasRequest && !in_array((string) $ticket['status'], ['closed', 'cancelled'], true)) {
        echo '<div class="card detail-card"><h3>مدیریت درخواست</h3><p class="muted">تا تصمیم دربارهٔ درخواست لغو، تغییر وضعیت، اولویت و ارجاع متوقف است. گفت‌وگو از ستون مکالمه ادامه دارد.</p></div>';
    }
    if ($canManageTicket && $handlerLevel !== '' && !$cancelHasRequest && !in_array((string) $ticket['status'], ['closed', 'cancelled'], true)) {
        echo '<div class="card detail-card"><h3>مدیریت درخواست</h3><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="update_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><label>وضعیت<select name="status">';
        $statusChoices = array_values(array_unique(array_merge([(string) $ticket['status']], array_filter(ticket_allowed_transitions((string) $ticket['status']), fn ($allowedStatus) => $canAssignTicket || in_array($allowedStatus, ['in_progress', 'waiting_user', 'resolved'], true)))));
        foreach ($statusChoices as $status) {
            echo '<option value="' . $status . '" ' . ($ticket['status'] === $status ? 'selected' : '') . '>' . request_center_status_label($status) . '</option>';
        }
        echo '</select></label>';
        if ($canAssignTicket) {
            echo '<label>اولویت<select name="priority">';
            foreach (['normal','urgent','critical'] as $priority) {
                echo '<option value="' . $priority . '" ' . ($ticket['priority'] === $priority ? 'selected' : '') . '>' . priority_label($priority) . '</option>';
            }
            echo '</select></label>';
        } else {
            echo '<input type="hidden" name="priority" value="' . e((string) $ticket['priority']) . '">';
        }
        if ($canAssignTicket) {
            echo '<label>ارجاع به<select name="assigned_to"><option value="">بدون تخصیص</option>';
            if (!empty($ticket['assigned_to']) && !in_array((int) $ticket['assigned_to'], array_map('intval', array_column($agents, 'id')), true)) {
                echo '<option value="' . (int) $ticket['assigned_to'] . '" selected>' . e((string) ($ticket['assignee_name'] ?? '')) . '</option>';
            }
            foreach ($agents as $agent) {
                echo '<option value="' . (int) $agent['id'] . '" ' . ((int) $ticket['assigned_to'] === (int) $agent['id'] ? 'selected' : '') . '>' . e($agent['full_name']) . '</option>';
            }
            echo '</select></label>';
        } else {
            echo '<input type="hidden" name="assigned_to" value="' . (int) $ticket['assigned_to'] . '"><p class="muted">ارجاع درخواست فقط توسط مدیر واحد انجام می‌شود.</p>';
        }
        echo '<label>پیام و دلیل تغییر (برای هر تغییر وضعیت، تخصیص و تغییر اولویت الزامی)<textarea name="update_note" rows="3" maxlength="5000" placeholder="دلیل تغییر مسئول/اولویت؛ سؤال روشن برای انتظار؛ شرح راه‌حل برای حل‌شده"></textarea><small class="field-help">این پیام برای ثبت‌کننده قابل مشاهده است و در مکالمه و تاریخچه ثبت می‌شود.</small></label>';
        echo '<button class="button wide" type="submit">ذخیره تغییرات</button></form></div>';
    }
    if ($canManageTicket && $handlerLevel !== '' && !$cancelHasRequest
        && !in_array((string) $ticket['status'], ['closed', 'cancelled', 'rejected'], true)) {
        $dueInputValue = !empty($ticket['due_at']) ? date('Y-m-d\TH:i', strtotime((string) $ticket['due_at'])) : '';
        echo '<div class="card detail-card"><h3>تغییر موعد پاسخ/حل</h3><p class="muted">مهلت فعلی: ' . e(!empty($ticket['due_at']) ? persian_date((string) $ticket['due_at']) : 'ثبت نشده') . '</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="update_due_date"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><label>مهلت جدید<input type="datetime-local" name="due_at" required value="' . e($dueInputValue) . '"></label><label>دلیل تغییر (الزامی؛ برای ثبت‌کننده قابل مشاهده است)<textarea name="due_note" required maxlength="1000" rows="2" placeholder="مثلاً: منتظر تهیهٔ قطعات از انبار هستیم؛ پیمانکار ۱۲ آبان اعلام کرد"></textarea></label><button class="button secondary" type="submit">ذخیرهٔ مهلت</button></form></div>';
    }
    echo '</aside></div>';
    $eventSearch = trim((string) ($_GET['event_q'] ?? ''));
    $eventConditions = ['e.ticket_id = ?'];
    $eventParams = [(int) $ticket['id']];
    if (!$canSeeInternal) {
        // Internal notes are not part of the requester-visible activity stream.
        $eventConditions[] = "e.event_type NOT IN ('internal_note', 'sla_alert', 'ola_alert')";
    }
    if ($eventSearch !== '') {
        $needle = '%' . logging_utf8_substr($eventSearch, 0, 100) . '%';
        $searchParts = ['e.event_type LIKE ?', 'e.from_status LIKE ?', 'e.to_status LIKE ?', 'u.full_name LIKE ?'];
        array_push($eventParams, $needle, $needle, $needle, $needle);
        if ($canSeeInternal) {
            $searchParts[] = 'CAST(e.details AS CHAR) LIKE ?';
            $eventParams[] = $needle;
        } else {
            // Requesters may search only the same safe detail keys that are rendered to them.
            foreach (['reason','score','from','to','mode','department_id','sla_minutes','ticket_type','service_id','from_status','to_status','status','priority'] as $safeSearchKey) {
                $searchParts[] = "JSON_UNQUOTE(JSON_EXTRACT(e.details, '$." . $safeSearchKey . "')) LIKE ?";
                $eventParams[] = $needle;
            }
        }
        $eventConditions[] = '(' . implode(' OR ', $searchParts) . ')';
    }
    $eventCountQuery = db()->prepare('SELECT COUNT(*) FROM ticket_events e LEFT JOIN users u ON u.id = e.user_id WHERE ' . implode(' AND ', $eventConditions));
    $eventCountQuery->execute($eventParams);
    $eventTotal = (int) $eventCountQuery->fetchColumn();
    $eventPageSize = 100;
    $eventPages = max(1, (int) ceil($eventTotal / $eventPageSize));
    $eventPage = min(max(1, (int) ($_GET['event_page'] ?? 1)), $eventPages);
    $eventOffset = ($eventPage - 1) * $eventPageSize;
    $eventsQuery = db()->prepare('SELECT e.*, u.full_name FROM ticket_events e LEFT JOIN users u ON u.id = e.user_id WHERE ' . implode(' AND ', $eventConditions) . ' ORDER BY e.created_at DESC, e.id DESC LIMIT ' . $eventPageSize . ' OFFSET ' . $eventOffset);
    $eventsQuery->execute($eventParams);
    $ticketEvents = $eventsQuery->fetchAll();
    $detailEventContext = $detailReturnParams;
    unset($detailEventContext['page']);
    echo '<section class="card event-log"><div class="section-title"><div><h3>ردگیری و سوابق انجام کار</h3><p class="muted">' . number_format($eventTotal) . ' رخداد مطابق فیلتر؛ صفحه ' . number_format($eventPage) . ' از ' . number_format($eventPages) . '. جزئیات قابل‌مشاهده بر اساس سطح دسترسی پالایش می‌شود.</p></div></div>';
    echo '<form method="get" action="request-center.php" class="inline-form"><input type="hidden" name="page" value="ticket"><input type="hidden" name="id" value="' . (int) $ticket['id'] . '">';
    foreach ($detailReturnParams as $returnKey => $returnValue) {
        echo '<input type="hidden" name="' . e((string) $returnKey) . '" value="' . e((string) $returnValue) . '">';
    }
    echo '<label>جست‌وجوی رویداد<input type="search" name="event_q" maxlength="100" value="' . e($eventSearch) . '" placeholder="نوع رویداد، وضعیت، توضیحات یا عامل"></label><button class="button secondary" type="submit">جست‌وجو</button>' . ($eventSearch !== '' ? '<a class="button secondary" href="request-center.php?' . e(http_build_query(array_merge(['page' => 'ticket', 'id' => (int) $ticket['id']], $detailEventContext))) . '">پاک‌کردن</a>' : '') . '</form><div class="event-list">';
    foreach ($ticketEvents as $event) {
        $eventText = ['created' => 'درخواست ثبت شد', 'assigned' => 'درخواست به کارشناس ارجاع شد', 'assignment_changed' => 'مسئول رسیدگی تغییر کرد', 'status_changed' => 'وضعیت درخواست تغییر کرد', 'reply' => 'پاسخ ثبت شد', 'internal_note' => 'یادداشت داخلی ثبت شد', 'claimed' => 'درخواست توسط کارشناس برداشته شد', 'rated' => 'امتیاز رضایت کاربر ثبت شد', 'reopened_by_user' => 'کاربر اعلام کرد مشکل حل نشده است', 'supervisor_approved' => 'تأیید نهایی و بسته‌شدن', 'supervisor_rework' => 'برگشت برای اصلاح توسط سوپروایزر', 'supervisor_reopened' => 'بازگشایی رسمی توسط سوپروایزر', 'cancelled_by_requester' => 'لغو مستقیم توسط ثبت‌کننده پیش از ارجاع', 'ticket_rejected' => 'رد درخواست پس از ارزیابی اولیه', 'cancellation_requested' => 'درخواست لغو در انتظار تصمیم مسئول', 'cancellation_approved' => 'تأیید لغو توسط مسئول رسیدگی', 'cancellation_rejected' => 'رد درخواست لغو و ادامهٔ رسیدگی', 'auto_assigned' => 'ارجاع خودکار به مسئول حوزه', 'auto_closed' => 'بسته‌شدن خودکار (بدون امتیاز کاربر)', 'sla_alert' => 'هشدار SLA ثبت شد', 'ola_alert' => 'هشدار OLA ثبت شد', 'bulk_status_changed' => 'تغییر وضعیت گروهی', 'priority_changed' => 'اولویت تغییر کرد', 'due_date_changed' => 'تغییر موعد پاسخ/حل'][$event['event_type']] ?? $event['event_type'];
        $eventDetails = json_decode((string) ($event['details'] ?? ''), true);
        $eventDetails = is_array($eventDetails) ? $eventDetails : [];
        $visibleDetails = [];
        $safeKeys = ['reason','score','from','to','mode','department_id','sla_minutes','ticket_type','service_id','from_status','to_status','status','priority','from_assignee_name','to_assignee_name'];
        if ($canSeeInternal) {
            $safeKeys = array_merge($safeKeys, [
                'note', 'assigned_to', 'from_assigned_to', 'to_assigned_to', 'from_assignee_name', 'to_assignee_name', 'assignment_source', 'requested_at', 'actor_id', 'message_id', 'is_internal',
                'due_at', 'sla_paused_at', 'sla_pause_minutes', 'ola_policy_id',
                'ola_response_due_at', 'ola_due_at', 'ola_escalation_1_at', 'ola_escalation_2_at',
                'first_response_set', 'alert_type',
            ]);
        }
        foreach ($eventDetails as $detailKey => $detailValue) {
            if (in_array((string) $detailKey, $safeKeys, true)) {
                $visibleDetails[$detailKey] = $detailValue;
            } elseif ($canSeeInternal && $detailKey === 'sla_ola' && is_array($detailValue)) {
                foreach ($detailValue as $slaKey => $slaValue) {
                    $visibleDetails['sla_ola_' . (string) $slaKey] = $slaValue;
                }
            }
        }
        $detailLines = [];
        if ($visibleDetails) {
            $visibleJson = json_encode($visibleDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (function_exists('activity_log_meta_lines')) {
                $detailLines = activity_log_meta_lines((string) $visibleJson);
            } else {
                // The independent RC entry does not load Logging's presentation layer.
                // Render only the already allowlisted RC event fields without that optional helper.
                $detailLabels = [
                    'reason' => 'دلیل', 'score' => 'امتیاز', 'from' => 'از', 'to' => 'به',
                    'mode' => 'نوع عملیات', 'department_id' => 'شناسه حوزه', 'sla_minutes' => 'مهلت SLA',
                    'ticket_type' => 'نوع درخواست', 'service_id' => 'شناسه خدمت',
                    'from_status' => 'وضعیت قبلی', 'to_status' => 'وضعیت جدید', 'status' => 'وضعیت',
                    'priority' => 'اولویت', 'from_assignee_name' => 'مسئول قبلی',
                    'to_assignee_name' => 'مسئول جدید', 'note' => 'یادداشت',
                    'assigned_to' => 'شناسه مسئول', 'from_assigned_to' => 'مسئول قبلی',
                    'to_assigned_to' => 'مسئول جدید', 'assignment_source' => 'منبع ارجاع',
                    'requested_at' => 'زمان درخواست', 'actor_id' => 'شناسه عامل',
                    'message_id' => 'شناسه پیام', 'is_internal' => 'یادداشت داخلی',
                    'due_at' => 'موعد SLA', 'sla_paused_at' => 'شروع مکث SLA',
                    'sla_pause_minutes' => 'مدت مکث SLA', 'ola_policy_id' => 'سیاست OLA',
                    'ola_response_due_at' => 'موعد پاسخ OLA', 'ola_due_at' => 'موعد حل OLA',
                    'ola_escalation_1_at' => 'موعد تشدید اول', 'ola_escalation_2_at' => 'موعد تشدید دوم',
                    'first_response_set' => 'ثبت اولین پاسخ', 'alert_type' => 'نوع هشدار',
                ];
                foreach ($visibleDetails as $detailKey => $detailValue) {
                    if (is_array($detailValue)) {
                        $detailValue = json_encode($detailValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }
                    if ($detailValue === null) {
                        continue;
                    }
                    $detailLines[] = [
                        $detailLabels[(string) $detailKey] ?? (string) $detailKey,
                        is_bool($detailValue) ? ($detailValue ? 'بله' : 'خیر') : (string) $detailValue,
                    ];
                }
            }
        }
        $detailHtml = $detailLines ? '<details class="log-details"><summary>جزئیات (' . count($detailLines) . ')</summary><dl>' . implode('', array_map(static fn (array $line): string => '<div><dt>' . e((string) $line[0]) . '</dt><dd>' . e((string) $line[1]) . '</dd></div>', $detailLines)) . '</dl></details>' : '';
        $fromStatus = trim((string) ($event['from_status'] ?? ''));
        $toStatus = trim((string) ($event['to_status'] ?? ''));
        $transition = ($fromStatus !== '' || $toStatus !== '') ? '<small class="muted">' . e($fromStatus !== '' ? request_center_status_label($fromStatus) : '—') . ' ← ' . e($toStatus !== '' ? request_center_status_label($toStatus) : '—') . '</small>' : '';
        $correlationHtml = $staff && !empty($event['request_id'])
            ? '<small class="muted" dir="ltr">Request: ' . e((string) $event['request_id']) . (!empty($event['trace_id']) ? ' · Trace: ' . e((string) $event['trace_id']) : '') . '</small>'
            : '';
        echo '<div class="event-item"><span class="event-dot"></span><div><strong>' . e($eventText) . '</strong><small>' . e($event['full_name'] ?: 'سامانه') . ' • ' . persian_date($event['created_at']) . '</small>' . $transition . $detailHtml . $correlationHtml . '</div><em>' . e($toStatus !== '' ? request_center_status_label($toStatus) : '—') . '</em></div>';
    }
    if (!$ticketEvents) echo '<p class="muted">رخدادی با این عبارت پیدا نشد.</p>';
    if ($eventPages > 1) {
        $eventBase = 'request-center.php?' . e(http_build_query(array_merge(['page' => 'ticket', 'id' => (int) $ticket['id']], $detailEventContext, $eventSearch !== '' ? ['event_q' => $eventSearch] : []))) . '&amp;event_page=';
        echo '<div class="actions">' . ($eventPage > 1 ? '<a class="button secondary" href="' . $eventBase . ($eventPage - 1) . '">رخدادهای قدیمی‌تر</a>' : '') . ($eventPage < $eventPages ? '<a class="button secondary" href="' . $eventBase . ($eventPage + 1) . '">رخدادهای جدیدتر</a>' : '') . '</div>';
    }
    echo '</div></section>';
    // Preserve the originating workspace/filter context across every lifecycle POST.
    // The POST handler re-validates all values and the workspace permission.
    $postReturnContext = $detailReturnParams;
    unset($postReturnContext['workspace'], $postReturnContext['page'], $postReturnContext['return_page']);
    $postReturnOriginPage = in_array((string) ($detailReturnParams['page'] ?? ''), ['queue', 'supervisor'], true)
        ? (string) $detailReturnParams['page'] : '';
    echo '<script>(function(){var context=' . json_encode($postReturnContext, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';document.querySelectorAll("form").forEach(function(form){if((form.method||"get").toLowerCase()!=="post")return;Object.keys(context).forEach(function(key){var input=document.createElement("input");input.type="hidden";input.name="rc_return_"+key;input.value=String(context[key]);form.appendChild(input);});var workspace=document.createElement("input");workspace.type="hidden";workspace.name="rc_return_workspace";workspace.value=' . json_encode($detailReturnWorkspace, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';form.appendChild(workspace);var origin=document.createElement("input");origin.type="hidden";origin.name="rc_return_origin_page";origin.value=' . json_encode($postReturnOriginPage, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';form.appendChild(origin);});})();</script>';
    echo '<script id="rc-ticket-detail-interactions">
(function(){
 document.querySelectorAll(".cancel-ticket-card,.cancellation-decision,.rating-card,.reply-box").forEach(function(form){
  var textareas=Array.prototype.slice.call(form.querySelectorAll("textarea"));
   var attachment=form.querySelector("[name=attachment]"),attachmentFeedback=form.querySelector("[data-reply-attachment-feedback]");
   if(attachment){attachment.addEventListener("change",function(){var file=attachment.files&&attachment.files[0];if(!file){attachment.setCustomValidity("");if(attachmentFeedback)attachmentFeedback.textContent="حداکثر ۸ مگابایت";return;}if(file.size>8*1024*1024){attachment.value="";attachment.setCustomValidity("حجم فایل پیوست نباید بیشتر از ۸ مگابایت باشد.");if(attachmentFeedback)attachmentFeedback.textContent="فایل انتخاب‌شده بیش از ۸ مگابایت است.";attachment.reportValidity();return;}attachment.setCustomValidity("");if(attachmentFeedback)attachmentFeedback.textContent=file.name+" · "+(file.size/1024/1024).toFixed(2)+" مگابایت";});}

  textareas.forEach(function(area){
   var counter=document.createElement("small");counter.className="field-help rc-character-count";counter.setAttribute("aria-live","polite");
   function update(){var max=area.maxLength>0?area.maxLength:0;counter.textContent=max?(area.value.length+" / "+max+" نویسه"):(area.value.length+" نویسه");}
   area.insertAdjacentElement("afterend",counter);area.addEventListener("input",update);update();
  });
  form.addEventListener("submit",function(event){
   var submitter=event.submitter,confirmText=submitter&&submitter.dataset.confirm?submitter.dataset.confirm:form.dataset.confirm;
   if(confirmText&&!window.confirm(confirmText)){event.preventDefault();return;}
   var requiredText=Array.prototype.slice.call(form.querySelectorAll("textarea[required],input[required]"));
   for(var i=0;i<requiredText.length;i++){if(!requiredText[i].value.trim()){event.preventDefault();requiredText[i].focus();return;}}
   if(form.classList.contains("rating-card")&&!form.querySelector("[name=score]:checked")){event.preventDefault();var first=form.querySelector("[name=score]");if(first)first.focus();return;}
   if(form.dataset.submitting==="1"){event.preventDefault();return;}
   form.dataset.submitting="1";
   form.querySelectorAll("button[type=\"submit\"],input[type=\"submit\"]").forEach(function(button){
    button.disabled=true;
    if(button.tagName==="BUTTON"){button.dataset.originalText=button.textContent;button.textContent="در حال ثبت…";}
    else{button.dataset.originalValue=button.value;button.value="در حال ثبت…";}
   });
  });
 });
 document.querySelectorAll(".rating-options input[type=radio]").forEach(function(input){
  input.addEventListener("change",function(){var group=input.closest(".rating-options");if(group)group.querySelectorAll("label").forEach(function(label){label.classList.toggle("selected",!!label.querySelector("input:checked"));});});
 });
 document.querySelectorAll(".reply-box input[type=file],.cancel-ticket-card textarea,.reply-box textarea").forEach(function(input){input.addEventListener("change",function(){input.setCustomValidity("");});});
})();
</script>'; 
    render_footer();
    exit;
}
