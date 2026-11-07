<?php
declare(strict_types=1);

/** Request Center operational queue; executed in legacy front-controller scope. */

if ($page === 'queue') {
    $queueUser = require_permission('queue.view');
    $queueName = valid_choice((string) ($_GET['queue'] ?? 'all'), ['all', 'unassigned', 'overdue', 'mine', 'waiting_user', 'resolved', 'cancellation_requested', 'cancelled'], 'all');
    [$queueScope, $queueParams] = ticket_scope($queueUser);
    $queueConditions = $queueScope !== '' ? [substr($queueScope, 6)] : [];
    if ($queueName === 'unassigned') { $queueConditions[] = 't.assigned_to IS NULL'; }
    if ($queueName === 'overdue') { $queueConditions[] = 't.sla_paused_at IS NULL AND t.status NOT IN ("resolved", "closed", "cancelled") AND ((t.due_at IS NOT NULL AND t.due_at < NOW()) OR (t.first_response_at IS NULL AND t.ola_response_due_at IS NOT NULL AND t.ola_response_due_at < NOW()) OR (t.ola_due_at IS NOT NULL AND t.ola_due_at < NOW()) OR (t.ola_escalation_1_at IS NOT NULL AND t.ola_escalation_1_at < NOW()) OR (t.ola_escalation_2_at IS NOT NULL AND t.ola_escalation_2_at < NOW()))'; }
    if ($queueName === 'mine') { $queueConditions[] = 't.assigned_to = ?'; $queueParams[] = (int) $queueUser['id']; }
    if ($queueName === 'waiting_user') { $queueConditions[] = 't.status IN ("waiting_user", "pending")'; }
    if ($queueName === 'resolved') { $queueConditions[] = 't.status = "resolved"'; }
    if ($queueName === 'cancellation_requested') { $queueConditions[] = 't.cancel_requested_at IS NOT NULL'; }
    if ($queueName === 'cancelled') { $queueConditions[] = 't.status = "cancelled"'; }
    else { $queueConditions[] = 't.status <> "cancelled"'; }
    $queueWhere = $queueConditions ? 'WHERE ' . implode(' AND ', $queueConditions) : '';
    $queuePerPage = 100;
    $queuePage = max(1, (int) ($_GET['p'] ?? 1));
    $queueCountQuery = db()->prepare('SELECT COUNT(*) FROM tickets t ' . $queueWhere);
    $queueCountQuery->execute($queueParams);
    $queueTotal = (int) $queueCountQuery->fetchColumn();
    $queuePages = max(1, (int) ceil($queueTotal / $queuePerPage));
    $queuePage = min($queuePage, $queuePages);
    $queueOffset = ($queuePage - 1) * $queuePerPage;
        $queueQuery = db()->prepare('SELECT t.*, d.name AS department_name, r.full_name AS requester_name, a.full_name AS assignee_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to ' . $queueWhere . ' ORDER BY CASE WHEN t.cancel_requested_at IS NOT NULL THEN 0 WHEN (t.status NOT IN ("resolved", "closed", "cancelled") AND t.sla_paused_at IS NULL AND ((t.due_at IS NOT NULL AND t.due_at < NOW()) OR (t.first_response_at IS NULL AND t.ola_response_due_at IS NOT NULL AND t.ola_response_due_at < NOW()) OR (t.ola_due_at IS NOT NULL AND t.ola_due_at < NOW()) OR (t.ola_escalation_1_at IS NOT NULL AND t.ola_escalation_1_at < NOW()) OR (t.ola_escalation_2_at IS NOT NULL AND t.ola_escalation_2_at < NOW()))) THEN 1 ELSE 2 END, FIELD(t.priority, "critical", "urgent", "normal"), t.created_at ASC LIMIT ' . $queuePerPage . ' OFFSET ' . $queueOffset);
    $queueQuery->execute($queueParams);
    $queueRows = $queueQuery->fetchAll();
    // ستون ticket_no با همین SELECT آمده است؛ بدون این خط هر ticket_number() داخل حلقه
    // یک کوئری جدا می‌زد (۱۰۰ کوئری اضافه در هر بار باز کردن صف).
    ticket_number_cache_prime($queueRows);
    render_header('صف کاری', $queueUser);
     echo '<section class="page-heading"><div><span class="eyebrow">صف مرکزی خدمات</span><h1>صف کار واحد رسیدگی</h1><p>درخواست‌های هر حوزه جدا هستند؛ موارد خارج از SLA/OLA و اولویت‌های حیاتی بالاتر قرار می‌گیرند. نمایش ' . ($queueRows ? number_format($queueOffset + 1) . ' تا ' . number_format($queueOffset + count($queueRows)) . ' از ' . number_format($queueTotal) : '۰ از ' . number_format($queueTotal)) . ' درخواست.</p></div><a class="button secondary" href="index.php?page=reports">گزارش کامل</a></section><div class="queue-tabs"><a class="' . ($queueName === 'all' ? 'active' : '') . '" href="index.php?page=queue&queue=all">همه</a><a class="' . ($queueName === 'unassigned' ? 'active' : '') . '" href="index.php?page=queue&queue=unassigned">بدون مسئول</a><a class="' . ($queueName === 'overdue' ? 'active' : '') . '" href="index.php?page=queue&queue=overdue">خارج از SLA/OLA</a><a class="' . ($queueName === 'mine' ? 'active' : '') . '" href="index.php?page=queue&queue=mine">مسئول من</a><a class="' . ($queueName === 'waiting_user' ? 'active' : '') . '" href="index.php?page=queue&queue=waiting_user">انتظار کاربر</a><a class="' . ($queueName === 'resolved' ? 'active' : '') . '" href="index.php?page=queue&queue=resolved">حل‌شده</a><a class="' . ($queueName === 'cancellation_requested' ? 'active' : '') . '" href="index.php?page=queue&queue=cancellation_requested">درخواست لغو</a><a class="' . ($queueName === 'cancelled' ? 'active' : '') . '" href="index.php?page=queue&queue=cancelled">لغوشده</a></div><form method="post"><section class="card ticket-table queue-table"><div class="table-head"><span>انتخاب / درخواست</span><span>معاونت</span><span>ثبت‌کننده</span><span>مسئول</span><span>وضعیت</span></div>';
    foreach ($queueRows as $queueTicket) {
        echo '<label class="table-row queue-row"><span class="ticket-title"><input type="checkbox" name="ticket_ids[]" value="' . (int) $queueTicket['id'] . '"><b>' . e(ticket_number((int) $queueTicket['id'])) . '</b><a href="index.php?page=ticket&id=' . (int) $queueTicket['id'] . '"><strong>' . e($queueTicket['subject']) . '</strong></a><small>' . e(ticket_type_label($queueTicket['ticket_type'])) . ' • ' . e(priority_label($queueTicket['priority'])) . (!empty($queueTicket['cancel_requested_at']) ? ' • درخواست لغو در انتظار بررسی' : '') . '</small></span><span>' . e($queueTicket['department_name'] ?: 'بدون معاونت') . '</span><span>' . e($queueTicket['requester_name']) . '</span><span>' . e($queueTicket['assignee_name'] ?: 'بدون مسئول') . '</span><span><em class="status ' . e($queueTicket['status']) . '">' . e(status_label($queueTicket['status'])) . '</em></span></label>';
        }
    echo '</section><div class="bulk-actions"><input type="hidden" name="action" value="bulk_update_tickets">' . csrf_field() . '<select name="bulk_status"><option value="in_progress">در حال انجام</option><option value="waiting_user">در انتظار پاسخ کاربر</option><option value="resolved">حل‌شده</option></select><label class="bulk-note">پیام مشترک (برای انتظار/حل‌شده الزامی)<textarea name="bulk_note" rows="2" maxlength="5000" placeholder="این پیام در همهٔ درخواست‌های انتخاب‌شده ثبت می‌شود"></textarea></label><button class="button" type="submit">اعمال روی انتخاب‌ها</button></div></form>';
    if ($queuePages > 1) {
        echo '<nav class="pagination" aria-label="صفحه‌بندی صف">';
        for ($queueNav = max(1, $queuePage - 3); $queueNav <= min($queuePages, $queuePage + 3); $queueNav++) {
            echo '<a class="' . ($queueNav === $queuePage ? 'active' : '') . '" href="index.php?page=queue&queue=' . e($queueName) . '&p=' . $queueNav . '">' . number_format($queueNav) . '</a>';
        }
        echo '</nav>';
    }
    // اسکریپت درون‌صفحه: تضمین کارکرد سربرگ‌ها و مودال‌ها مستقل از کش app.js

    render_footer();
    exit;
}
