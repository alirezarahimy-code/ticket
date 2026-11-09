<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/** Request Center operational queue; executed in legacy front-controller scope. */

if ($page === 'queue') {
    $queueUser = require_permission('queue.view');
    $queueName = valid_choice((string) ($_GET['queue'] ?? 'all'), ['all', 'unassigned', 'overdue', 'mine', 'waiting_user', 'resolved', 'cancellation_requested', 'cancelled', 'rejected'], 'all');
    $queueFilters = [
        'q' => trim((string) ($_GET['q'] ?? '')),
        'status' => valid_choice((string) ($_GET['status'] ?? ''), ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved', 'closed', 'cancelled', 'rejected'], ''),
        'priority' => valid_choice((string) ($_GET['priority'] ?? ''), ['normal', 'urgent', 'critical'], ''),
        'service_group' => valid_choice((string) ($_GET['service_group'] ?? ''), ['it', 'support'], ''),
    ];
    $queueFilters['q'] = function_exists('mb_substr')
        ? mb_substr($queueFilters['q'], 0, 100, 'UTF-8')
        : substr($queueFilters['q'], 0, 100);
    $queueQueryParams = array_filter($queueFilters, static fn ($value): bool => $value !== '');
    $queueQueryParams = ['page' => 'queue', 'queue' => $queueName] + $queueQueryParams;
    $queueBaseUrl = 'request-center.php?' . http_build_query($queueQueryParams);
    $queueFilterQuery = http_build_query(array_filter($queueFilters, static fn ($value): bool => $value !== ''));
    $queueFilterSuffix = $queueFilterQuery !== '' ? '&amp;' . e($queueFilterQuery) : '';
    require_once dirname(__DIR__) . '/RequestCenterRepository.php';
    $queuePerPage = 100;
    $queuePage = max(1, (int) ($_GET['p'] ?? 1));
    $queueData = (new RequestCenterRepository(db()))->listQueue($queueUser, $queueName, $queuePage, $queuePerPage, $queueFilters);
    $queueTotal = $queueData['total'];
    $queuePages = $queueData['pages'];
    $queuePage = $queueData['page'];
    $queueOffset = $queueData['offset'];
    $queueRows = $queueData['rows'];
    // ستون ticket_no با همین SELECT آمده است؛ بدون این خط هر ticket_number() داخل حلقه
    // یک کوئری جدا می‌زد (۱۰۰ کوئری اضافه در هر بار باز کردن صف).
    ticket_number_cache_prime($queueRows);
    render_header('صف کاری', $queueUser);
     echo '<section class="page-heading"><div><span class="eyebrow">صف مرکزی خدمات</span><h1>صف کار واحد رسیدگی</h1><p>درخواست‌های هر حوزه جدا هستند؛ موارد خارج از SLA/OLA و اولویت‌های حیاتی بالاتر قرار می‌گیرند. نمایش ' . ($queueRows ? number_format($queueOffset + 1) . ' تا ' . number_format($queueOffset + count($queueRows)) . ' از ' . number_format($queueTotal) : '۰ از ' . number_format($queueTotal)) . ' درخواست.</p></div><div class="actions"><a class="button secondary" href="request-center.php?workspace=agent">داشبورد کارشناس</a><a class="button secondary" href="request-center.php?page=reports">گزارش کامل</a></div></section><form method="get" action="request-center.php" class="card queue-filters" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;padding:14px;margin-bottom:14px"><input type="hidden" name="page" value="queue"><input type="hidden" name="queue" value="' . e($queueName) . '"><label>جست‌وجو<input type="search" name="q" maxlength="100" value="' . e($queueFilters['q']) . '" placeholder="شماره یا عنوان درخواست"></label><label>وضعیت<select name="status"><option value="">همه وضعیت‌ها</option>' . (function () use ($queueFilters) { $html=''; foreach (['new'=>'جدید','manager_review'=>'در انتظار ارجاع','assigned'=>'ارجاع‌شده','in_progress'=>'در حال انجام','waiting_user'=>'انتظار کاربر','pending'=>'در انتظار پاسخ','resolved'=>'حل‌شده','closed'=>'بسته‌شده','cancelled'=>'لغوشده','rejected'=>'ردشده'] as $key=>$label) { $html .= '<option value="' . $key . '"' . ($queueFilters['status']===$key?' selected':'') . '>' . $label . '</option>'; } return $html; })() . '</select></label><label>اولویت<select name="priority"><option value="">همه اولویت‌ها</option><option value="normal"' . ($queueFilters['priority']==='normal'?' selected':'') . '>عادی</option><option value="urgent"' . ($queueFilters['priority']==='urgent'?' selected':'') . '>فوری</option><option value="critical"' . ($queueFilters['priority']==='critical'?' selected':'') . '>بحرانی</option></select></label><label>حوزه<select name="service_group"><option value="">همه حوزه‌ها</option><option value="it"' . ($queueFilters['service_group']==='it'?' selected':'') . '>فناوری اطلاعات</option><option value="support"' . ($queueFilters['service_group']==='support'?' selected':'') . '>پشتیبانی</option></select></label><button class="button" type="submit">اعمال فیلتر</button><a class="button secondary" href="request-center.php?page=queue&amp;queue=' . e($queueName) . '">پاک‌کردن</a></form><div class="queue-tabs"><a class="' . ($queueName === 'all' ? 'active' : '') . '" href="request-center.php?page=queue&queue=all' . $queueFilterSuffix . '">همه</a><a class="' . ($queueName === 'unassigned' ? 'active' : '') . '" href="request-center.php?page=queue&queue=unassigned' . $queueFilterSuffix . '">بدون مسئول</a><a class="' . ($queueName === 'overdue' ? 'active' : '') . '" href="request-center.php?page=queue&queue=overdue' . $queueFilterSuffix . '">خارج از SLA/OLA</a><a class="' . ($queueName === 'mine' ? 'active' : '') . '" href="request-center.php?page=queue&queue=mine' . $queueFilterSuffix . '">مسئول من</a><a class="' . ($queueName === 'waiting_user' ? 'active' : '') . '" href="request-center.php?page=queue&queue=waiting_user' . $queueFilterSuffix . '">انتظار کاربر</a><a class="' . ($queueName === 'resolved' ? 'active' : '') . '" href="request-center.php?page=queue&queue=resolved' . $queueFilterSuffix . '">حل‌شده</a><a class="' . ($queueName === 'cancellation_requested' ? 'active' : '') . '" href="request-center.php?page=queue&queue=cancellation_requested' . $queueFilterSuffix . '">درخواست لغو</a><a class="' . ($queueName === 'cancelled' ? 'active' : '') . '" href="request-center.php?page=queue&queue=cancelled' . $queueFilterSuffix . '">لغوشده</a><a class="' . ($queueName === 'rejected' ? 'active' : '') . '" href="request-center.php?page=queue&queue=rejected' . $queueFilterSuffix . '">ردشده</a></div><form method="post"><section class="card ticket-table queue-table"><div class="table-head"><span>انتخاب / درخواست</span><span>معاونت</span><span>ثبت‌کننده</span><span>مسئول</span><span>وضعیت</span></div>';
    foreach ($queueRows as $queueTicket) {
        echo '<label class="table-row queue-row"><span class="ticket-title"><input type="checkbox" name="ticket_ids[]" value="' . (int) $queueTicket['id'] . '"><b>' . e(ticket_number((int) $queueTicket['id'])) . '</b><a href="request-center.php?page=ticket&amp;id=' . (int) $queueTicket['id'] . '&amp;workspace=agent&amp;agent_view=team&amp;return_page=queue&amp;queue=' . rawurlencode($queueName) . '&amp;q=' . rawurlencode($queueFilters['q']) . '&amp;status=' . rawurlencode($queueFilters['status']) . '&amp;priority=' . rawurlencode($queueFilters['priority']) . '&amp;service_group=' . rawurlencode($queueFilters['service_group']) . '&amp;p=' . $queuePage . '"><strong>' . e($queueTicket['subject']) . '</strong></a><small>' . e(ticket_type_label($queueTicket['ticket_type'])) . ' • ' . e(priority_label($queueTicket['priority'])) . (!empty($queueTicket['cancel_requested_at']) ? ' • درخواست لغو در انتظار بررسی' : '') . '</small></span><span>' . e($queueTicket['department_name'] ?: 'بدون معاونت') . '</span><span>' . e($queueTicket['requester_name']) . '</span><span>' . e($queueTicket['assignee_name'] ?: 'بدون مسئول') . '</span><span><em class="status ' . e($queueTicket['status']) . '">' . e(request_center_status_label($queueTicket['status'])) . '</em></span></label>';
        }
    echo '</section><div class="bulk-actions"><input type="hidden" name="rc_return_queue" value="' . e($queueName) . '"><input type="hidden" name="rc_return_p" value="' . $queuePage . '"><input type="hidden" name="rc_return_q" value="' . e($queueFilters['q']) . '"><input type="hidden" name="rc_return_status" value="' . e($queueFilters['status']) . '"><input type="hidden" name="rc_return_priority" value="' . e($queueFilters['priority']) . '"><input type="hidden" name="rc_return_service_group" value="' . e($queueFilters['service_group']) . '"><input type="hidden" name="action" value="bulk_update_tickets">' . csrf_field() . '<select name="bulk_status"><option value="in_progress">در حال انجام</option><option value="waiting_user">در انتظار پاسخ کاربر</option><option value="resolved">حل‌شده</option></select><label class="bulk-note">دلیل و پیام مشترک (برای هر تغییر وضعیت الزامی)<textarea name="bulk_note" rows="2" maxlength="5000" required placeholder="دلیل اقدام را بنویسید؛ این پیام برای ثبت‌کنندهٔ همهٔ درخواست‌های انتخاب‌شده قابل مشاهده است"></textarea></label><button class="button" type="submit">اعمال روی انتخاب‌ها</button></div></form>';
    if ($queuePages > 1) {
        echo '<nav class="pagination" aria-label="صفحه‌بندی صف">';
        for ($queueNav = max(1, $queuePage - 3); $queueNav <= min($queuePages, $queuePage + 3); $queueNav++) {
            echo '<a class="' . ($queueNav === $queuePage ? 'active' : '') . '" href="' . e($queueBaseUrl) . '&p=' . $queueNav . '">' . number_format($queueNav) . '</a>';
        }
        echo '</nav>';
    }
    // اسکریپت درون‌صفحه: تضمین کارکرد سربرگ‌ها و مودال‌ها مستقل از کش app.js

    render_footer();
    exit;
}
