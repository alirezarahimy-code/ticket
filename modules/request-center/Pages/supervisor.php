<?php
declare(strict_types=1);

// محافظ: این صفحه فقط از طریق ورودی مستقل Request Center یا پل سازگاری معتبر بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/**
 * Request Center supervisor decision queue.
 * Permission checks, ticket scope, POST contracts and pagination are shared by the
 * independent Request Center entry and the legacy compatibility route.
 */

if ($page === 'supervisor') {
    $supervisor = require_permission('supervisor.panel');
    [$where, $params] = ticket_scope($supervisor);
    $summaryQuery = db()->prepare('SELECT COUNT(*) AS total, SUM(status IN ("new", "manager_review", "assigned", "in_progress", "waiting_user", "pending")) AS active, SUM(status = "resolved") AS awaiting_approval, SUM(status = "closed") AS closed, SUM(status = "cancelled") AS cancelled, SUM(status = "rejected") AS rejected FROM tickets t ' . $where);
    $summaryQuery->execute($params);
    $summary = $summaryQuery->fetch() ?: ['total' => 0, 'active' => 0, 'awaiting_approval' => 0, 'closed' => 0, 'cancelled' => 0, 'rejected' => 0];
    $supervisorPerPage = 100;
    $supervisorPage = max(1, (int) ($_GET['p'] ?? 1));
    $supervisorPages = max(1, (int) ceil(((int) $summary['total']) / $supervisorPerPage));
    $supervisorPage = min($supervisorPage, $supervisorPages);
    $supervisorOffset = ($supervisorPage - 1) * $supervisorPerPage;
     $ticketsQuery = db()->prepare('SELECT t.*, d.name AS department_name, r.full_name AS requester_name, a.full_name AS assignee_name, tr.score AS rating_score, tr.created_at AS rating_at FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to LEFT JOIN ticket_ratings tr ON tr.ticket_id = t.id ' . $where . ' ORDER BY CASE WHEN t.status = "resolved" THEN 0 WHEN t.status IN ("new", "manager_review", "assigned", "in_progress", "waiting_user", "pending") THEN 1 ELSE 2 END, t.updated_at DESC LIMIT ' . $supervisorPerPage . ' OFFSET ' . $supervisorOffset);
    $ticketsQuery->execute($params);
    $supervisorRows = $ticketsQuery->fetchAll();
    ticket_number_cache_prime($supervisorRows); // ticket_no از همین SELECT آمده؛ جلوگیری از N+1
    render_header('پنل سوپروایزر', $supervisor);
    echo '<section class="page-heading"><div><span class="eyebrow">نظارت سراسری</span><h1>پنل سوپروایزر</h1><p>تمام مراحل از ثبت تا انجام و تأیید نهایی را کنترل کنید.</p></div><a class="button secondary" href="request-center.php?page=reports">گزارش کامل</a></section><section class="stat-grid"><div class="stat-card accent"><span class="stat-icon">▣</span><div><strong>' . (int) $summary['total'] . '</strong><small>کل درخواست‌ها</small></div></div><div class="stat-card"><span class="stat-icon blue">◈</span><div><strong>' . (int) $summary['active'] . '</strong><small>در گردش</small></div></div><div class="stat-card"><span class="stat-icon" style="background:#fff8e7;color:#9a6700">✓</span><div><strong>' . (int) $summary['awaiting_approval'] . '</strong><small>منتظر تأیید شما</small></div></div><div class="stat-card"><span class="stat-icon green">✓</span><div><strong>' . (int) $summary['closed'] . '</strong><small>بسته‌شده</small></div></div><div class="stat-card"><span class="stat-icon gray">×</span><div><strong>' . (int) $summary['cancelled'] . '</strong><small>لغوشده</small></div></div></section><section class="card supervisor-list"><div class="table-head"><span>درخواست</span><span>واحد</span><span>کارشناس</span><span>وضعیت</span><span>عملیات</span></div>';
    foreach ($supervisorRows as $ticket) {
        echo '<div class="table-row supervisor-row"><span class="ticket-title"><b>' . e(ticket_number((int) $ticket['id'])) . '</b><a href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;return_page=supervisor&amp;p=' . $supervisorPage . '"><strong>' . e($ticket['subject']) . '</strong></a><small>' . e($ticket['requester_name']) . '</small>' . (!empty($ticket['cancel_requested_at']) ? '<small class="warning-text">درخواست لغو در انتظار بررسی</small>' : '') . '</span><span>' . e($ticket['department_name'] ?: 'بدون واحد') . '</span><span>' . e($ticket['assignee_name'] ?: 'بدون کارشناس') . '</span><span><em class="status ' . e($ticket['status']) . '">' . e(request_center_status_label((string) $ticket['status'])) . '</em></span><span class="supervisor-actions">';
        if ($ticket['status'] === 'resolved' && empty($ticket['cancel_requested_at']) && !ticket_user_is_requester($ticket, $supervisor)) {
            if (user_can($supervisor, 'supervisor.decide')) {
                if (ticket_rating_is_current($ticket)) {
                    echo '<form method="post" class="supervisor-approve-form">' . csrf_field() . '<input type="hidden" name="rc_return_workspace" value="supervisor"><input type="hidden" name="rc_return_p" value="' . $supervisorPage . '"><input type="hidden" name="action" value="supervisor_decision"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><input type="hidden" name="decision" value="approve"><label>دلیل تأیید نهایی<input type="text" name="supervisor_note" required maxlength="500" placeholder="دلیل تأیید و بستن درخواست"></label><button class="button" type="submit">تأیید و بستن</button></form>';
                } else {
                    echo '<small class="muted">در انتظار امتیاز کاربر</small>';
                }
                echo '<form method="post">' . csrf_field() . '<input type="hidden" name="rc_return_workspace" value="supervisor"><input type="hidden" name="rc_return_p" value="' . $supervisorPage . '"><input type="hidden" name="action" value="supervisor_decision"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><input type="hidden" name="decision" value="rework"><input type="text" name="supervisor_note" required maxlength="500" placeholder="دلیل برگشت برای اصلاح"><button class="button secondary" type="submit">برگشت برای اصلاح</button></form>';
            } else {
                echo '<a class="button secondary" href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;p=' . $supervisorPage . '">مشاهده</a>';
            }
         } elseif ($ticket['status'] === 'closed' && !ticket_user_is_requester($ticket, $supervisor)) {
             echo '<a class="button secondary" href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;p=' . $supervisorPage . '">مشاهده</a>';
             if (user_can($supervisor, 'supervisor.reopen')) {
                 echo '<form method="post" class="supervisor-reopen-form">' . csrf_field() . '<input type="hidden" name="rc_return_workspace" value="supervisor"><input type="hidden" name="rc_return_p" value="' . $supervisorPage . '"><input type="hidden" name="action" value="supervisor_reopen"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><label class="sr-only" for="supervisor-reopen-note-' . (int) $ticket['id'] . '">دلیل بازگشایی رسمی</label><input id="supervisor-reopen-note-' . (int) $ticket['id'] . '" type="text" name="supervisor_note" required maxlength="1000" placeholder="دلیل بازگشایی رسمی"><button class="button secondary" type="submit">بازگشایی رسمی</button></form>';
             }
         } elseif (in_array((string) $ticket['status'], ['new', 'manager_review', 'assigned'], true)
             && empty($ticket['first_response_at']) && empty($ticket['cancel_requested_at']) && !ticket_user_is_requester($ticket, $supervisor) && user_can($supervisor, 'supervisor.decide')) {
             echo '<a class="button secondary" href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;p=' . $supervisorPage . '">مشاهده</a><form method="post" class="supervisor-reject-form">' . csrf_field() . '<input type="hidden" name="rc_return_workspace" value="supervisor"><input type="hidden" name="rc_return_p" value="' . $supervisorPage . '"><input type="hidden" name="action" value="supervisor_decision"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><input type="hidden" name="decision" value="reject"><input type="text" name="supervisor_note" required maxlength="500" placeholder="دلیل رد درخواست"><button class="button danger-button" type="submit" data-confirm="درخواست با این دلیل رد و به درخواست‌کننده اطلاع داده می‌شود. ادامه می‌دهید؟">رد درخواست</button></form>';
         } else {
             $viewLabel = 'مشاهده';
             if (!empty($ticket['cancel_requested_at']) && user_can($supervisor, 'supervisor.decide')
                 && !ticket_user_is_requester($ticket, $supervisor)) {
                 $viewLabel = 'مشاهده / تصمیم لغو';
             } elseif (empty($ticket['cancel_requested_at'])
                 && in_array((string) $ticket['status'], ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending'], true)
                 && user_can($supervisor, 'ticket.edit')
                 && !ticket_user_is_requester($ticket, $supervisor)) {
                 $viewLabel = 'مشاهده / تخصیص';
             }
             echo '<a class="button secondary" href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;p=' . $supervisorPage . '">' . $viewLabel . '</a>';
        }
        echo '</span></div>';
    }
    echo '<div class="card" style="padding:12px;margin:12px 0"><strong>' . (int) $summary['rejected'] . '</strong> درخواست ردشده</div>';
    echo '</section>';
    if ($supervisorPages > 1) {
        echo '<nav class="pagination" aria-label="صفحه‌بندی سوپروایزر">';
        for ($supervisorNav = max(1, $supervisorPage - 3); $supervisorNav <= min($supervisorPages, $supervisorPage + 3); $supervisorNav++) {
            echo '<a class="' . ($supervisorNav === $supervisorPage ? 'active' : '') . '" href="request-center.php?page=supervisor&p=' . $supervisorNav . '">' . number_format($supervisorNav) . '</a>';
        }
        echo '</nav>';
    }
    render_footer();
    exit;
}
