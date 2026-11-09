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
    require_once dirname(__DIR__) . '/lib/http-errors.php';
    $supervisor = require_login();
    if (!user_can($supervisor, 'supervisor.panel')) {
        request_center_error_page(403, 'دسترسی مجاز نیست', 'دسترسی به صف تصمیم‌های سوپروایزر فقط برای دارندهٔ مجوز «supervisor.panel» فعال است.');
    }
    [$where, $params] = ticket_scope($supervisor);
    $supervisorFilters = [
        'q' => trim((string) ($_GET['q'] ?? '')),
        'status' => valid_choice((string) ($_GET['status'] ?? ''), ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved', 'closed', 'cancelled', 'rejected'], ''),
        'priority' => valid_choice((string) ($_GET['priority'] ?? ''), ['normal', 'urgent', 'critical'], ''),
        'service_group' => valid_choice((string) ($_GET['service_group'] ?? ''), ['it', 'support'], ''),
    ];
    $supervisorFilters['q'] = function_exists('mb_substr')
        ? mb_substr($supervisorFilters['q'], 0, 100, 'UTF-8')
        : substr($supervisorFilters['q'], 0, 100);
    $supervisorFilterSql = [];
    if ($supervisorFilters['q'] !== '') {
        $supervisorFilterSql[] = '(t.subject LIKE ? OR t.ticket_no LIKE ?)';
        $params[] = '%' . $supervisorFilters['q'] . '%';
        $params[] = '%' . $supervisorFilters['q'] . '%';
    }
    if ($supervisorFilters['status'] !== '') {
        $supervisorFilterSql[] = 't.status = ?';
        $params[] = $supervisorFilters['status'];
    }
    if ($supervisorFilters['priority'] !== '') {
        $supervisorFilterSql[] = 't.priority = ?';
        $params[] = $supervisorFilters['priority'];
    }
    if ($supervisorFilters['service_group'] !== '') {
        $supervisorFilterSql[] = 't.service_group = ?';
        $params[] = $supervisorFilters['service_group'];
    }
    if ($supervisorFilterSql !== []) {
        $where .= ($where === '' ? ' WHERE ' : ' AND ') . implode(' AND ', $supervisorFilterSql);
    }
    $supervisorQueryParams = ['page' => 'supervisor'] + array_filter($supervisorFilters, static fn ($value): bool => $value !== '');
    $supervisorBaseUrl = 'request-center.php?' . http_build_query($supervisorQueryParams);
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
    echo '<style id="rc-supervisor-decision-page">
.page-heading{align-items:center;margin-bottom:18px}.page-heading h1{font-size:24px;letter-spacing:-.3px}.page-heading p{font-size:13px;color:#687990}.page-heading .actions{display:flex;gap:8px;flex-wrap:wrap}
.supervisor-filters{display:flex;align-items:end;gap:10px;flex-wrap:wrap;padding:16px!important;margin-bottom:14px!important;border:1px solid #e2e9f2;border-radius:13px}
.supervisor-filters label{display:grid;gap:5px;min-width:145px;flex:1;color:#526780;font-size:11px;font-weight:700}
.supervisor-filters input,.supervisor-filters select,.supervisor-actions input[type=text]{width:100%;min-height:38px;padding:8px 10px;border:1px solid #d9e2ee;border-radius:8px;background:#fff;color:#172b4d;font:inherit;font-size:12px}
.supervisor-filters input:focus,.supervisor-filters select:focus,.supervisor-actions input:focus{outline:0;border-color:#709bc9;box-shadow:0 0 0 3px rgba(36,89,166,.11)}
.stat-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0 0 16px}
.stat-card{display:flex;align-items:center;gap:12px;min-width:0;padding:14px;border:1px solid #e2e9f2;border-radius:12px;background:#fff;box-shadow:0 3px 13px rgba(23,43,77,.035)}
.stat-card>div{min-width:0}.stat-card strong{display:block;font-size:24px;line-height:1.4;color:#1d4778;font-variant-numeric:tabular-nums}.stat-card small{font-size:11px;color:#687990}
.stat-icon{display:grid;place-items:center;width:38px;height:38px;flex:0 0 auto;border-radius:11px;background:#edf4ff;color:#2459a6;font-size:18px}
.supervisor-list{overflow:hidden;border:1px solid #e2e9f2;border-radius:14px}
.supervisor-list .table-head,.supervisor-list .table-row{display:grid;grid-template-columns:minmax(260px,2fr) minmax(100px,.7fr) minmax(110px,.8fr) minmax(100px,.7fr) minmax(230px,1.6fr);gap:12px;align-items:center}
.supervisor-list .table-head{padding:12px 16px;background:#f6f9fd;color:#61738b;font-size:11px;font-weight:800;border-bottom:1px solid #e7edf5}
.supervisor-list .table-row{padding:14px 16px;border-bottom:1px solid #edf1f6;font-size:12px}
.supervisor-list .table-row:last-child{border-bottom:0}
.supervisor-list .table-row:hover{background:#f9fbfe}
.supervisor-list .ticket-title{display:flex;flex-direction:column;gap:3px;min-width:0}
.supervisor-list .ticket-title>a{color:#214d7e;text-decoration:none;overflow-wrap:anywhere}
.supervisor-list .ticket-title>a:hover{text-decoration:underline}
.supervisor-list .ticket-title small{font-size:10px;color:#77869a}
.supervisor-list .status{display:inline-flex;padding:4px 9px;border-radius:999px;font-style:normal;font-size:10px;white-space:nowrap}
.supervisor-actions{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-width:0}
.supervisor-actions form{display:flex;align-items:center;gap:6px;flex-wrap:wrap;min-width:0}
.supervisor-actions input[type=text]{width:170px;min-width:120px}
.supervisor-actions .button{min-height:34px;padding:6px 10px;font-size:10px;white-space:nowrap}
.supervisor-actions .danger-button{background:#b42318;border-color:#b42318;color:#fff}
.supervisor-reopen-form{margin:0}
.warning-text{color:#a15c00!important;font-weight:700}
.pagination{display:flex;justify-content:center;gap:7px;flex-wrap:wrap;padding:18px}
.pagination a,.pagination .active{min-width:34px;padding:6px 10px;border:1px solid #dfe7f0;border-radius:8px;background:#fff;text-align:center;color:#526780;text-decoration:none}
.pagination .active{background:#2459a6;color:#fff;border-color:#2459a6}
@media(max-width:1050px){.supervisor-list .table-head{display:none}.supervisor-list .table-row{grid-template-columns:minmax(0,1fr) minmax(140px,.6fr);gap:10px}.supervisor-list .ticket-title{grid-column:1/-1}.supervisor-actions{grid-column:1/-1}.stat-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:600px){.page-heading{align-items:flex-start;flex-direction:column}.page-heading .actions{width:100%}.stat-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.stat-card{padding:10px;gap:8px}.stat-card strong{font-size:20px}.stat-icon{width:32px;height:32px}.supervisor-list .table-row{grid-template-columns:1fr;padding:12px}.supervisor-actions{grid-column:auto}.supervisor-actions input[type=text]{width:100%;flex:1 1 100%}.supervisor-actions form{width:100%}.supervisor-actions .button{flex:1}}
</style>';
    echo '<section class="page-heading"><div><span class="eyebrow">نظارت سراسری</span><h1>پنل سوپروایزر</h1><p>تمام مراحل از ثبت تا انجام و تأیید نهایی را کنترل کنید.</p></div><div class="actions"><a class="button secondary" href="request-center.php?workspace=supervisor">داشبورد سوپروایزر</a><a class="button secondary" href="request-center.php?page=reports">گزارش کامل</a></div></section><form method="get" action="request-center.php" class="card supervisor-filters" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;padding:14px;margin-bottom:14px"><input type="hidden" name="page" value="supervisor"><label>جست‌وجو<input type="search" name="q" maxlength="100" value="' . e($supervisorFilters['q']) . '" placeholder="شماره یا عنوان درخواست"></label><label>وضعیت<select name="status"><option value="">همه وضعیت‌ها</option>' . (function () use ($supervisorFilters) { $html=''; foreach (['new'=>'جدید','manager_review'=>'در انتظار ارجاع','assigned'=>'ارجاع‌شده','in_progress'=>'در حال انجام','waiting_user'=>'انتظار کاربر','pending'=>'در انتظار پاسخ','resolved'=>'حل‌شده','closed'=>'بسته‌شده','cancelled'=>'لغوشده','rejected'=>'ردشده'] as $key=>$label) { $html .= '<option value="' . $key . '"' . ($supervisorFilters['status']===$key?' selected':'') . '>' . $label . '</option>'; } return $html; })() . '</select></label><label>اولویت<select name="priority"><option value="">همه اولویت‌ها</option><option value="normal"' . ($supervisorFilters['priority']==='normal'?' selected':'') . '>عادی</option><option value="urgent"' . ($supervisorFilters['priority']==='urgent'?' selected':'') . '>فوری</option><option value="critical"' . ($supervisorFilters['priority']==='critical'?' selected':'') . '>بحرانی</option></select></label><label>حوزه خدمت<select name="service_group"><option value="">همه حوزه‌ها</option><option value="it"' . ($supervisorFilters['service_group']==='it'?' selected':'') . '>فناوری اطلاعات</option><option value="support"' . ($supervisorFilters['service_group']==='support'?' selected':'') . '>پشتیبانی</option></select></label><button class="button" type="submit">اعمال فیلتر</button><a class="button secondary" href="request-center.php?page=supervisor">پاک‌کردن</a></form><section class="stat-grid"><div class="stat-card accent"><span class="stat-icon">▣</span><div><strong>' . (int) $summary['total'] . '</strong><small>کل درخواست‌ها</small></div></div><div class="stat-card"><span class="stat-icon blue">◈</span><div><strong>' . (int) $summary['active'] . '</strong><small>در گردش</small></div></div><div class="stat-card"><span class="stat-icon" style="background:#fff8e7;color:#9a6700">✓</span><div><strong>' . (int) $summary['awaiting_approval'] . '</strong><small>منتظر تأیید شما</small></div></div><div class="stat-card"><span class="stat-icon green">✓</span><div><strong>' . (int) $summary['closed'] . '</strong><small>بسته‌شده</small></div></div><div class="stat-card"><span class="stat-icon gray">×</span><div><strong>' . (int) $summary['cancelled'] . '</strong><small>لغوشده</small></div></div></section><section class="card supervisor-list"><div class="table-head"><span>درخواست</span><span>واحد</span><span>کارشناس</span><span>وضعیت</span><span>عملیات</span></div>';
    foreach ($supervisorRows as $ticket) {
        echo '<div class="table-row supervisor-row"><span class="ticket-title"><b>' . e(ticket_number((int) $ticket['id'])) . '</b><a href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;return_page=supervisor&amp;q=' . rawurlencode($supervisorFilters['q']) . '&amp;status=' . rawurlencode($supervisorFilters['status']) . '&amp;priority=' . rawurlencode($supervisorFilters['priority']) . '&amp;service_group=' . rawurlencode($supervisorFilters['service_group']) . '&amp;p=' . $supervisorPage . '"><strong>' . e($ticket['subject']) . '</strong></a><small>' . e($ticket['requester_name']) . '</small>' . (!empty($ticket['cancel_requested_at']) ? '<small class="warning-text">درخواست لغو در انتظار بررسی</small>' : '') . '</span><span>' . e($ticket['department_name'] ?: 'بدون واحد') . '</span><span>' . e($ticket['assignee_name'] ?: 'بدون کارشناس') . '</span><span><em class="status ' . e($ticket['status']) . '">' . e(request_center_status_label((string) $ticket['status'])) . '</em></span><span class="supervisor-actions">';
        if ($ticket['status'] === 'resolved' && empty($ticket['cancel_requested_at']) && !ticket_user_is_requester($ticket, $supervisor)) {
            if (user_can($supervisor, 'supervisor.decide')) {
                if (ticket_rating_is_current($ticket)) {
                    echo '<form method="post" class="supervisor-approve-form">' . csrf_field() . '<input type="hidden" name="rc_return_workspace" value="supervisor"><input type="hidden" name="rc_return_origin_page" value="supervisor"><input type="hidden" name="rc_return_p" value="' . $supervisorPage . '"><input type="hidden" name="rc_return_q" value="' . e($supervisorFilters['q']) . '"><input type="hidden" name="rc_return_status" value="' . e($supervisorFilters['status']) . '"><input type="hidden" name="rc_return_priority" value="' . e($supervisorFilters['priority']) . '"><input type="hidden" name="rc_return_service_group" value="' . e($supervisorFilters['service_group']) . '"><input type="hidden" name="action" value="supervisor_decision"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><input type="hidden" name="decision" value="approve"><label>دلیل تأیید نهایی<input type="text" name="supervisor_note" required maxlength="500" placeholder="دلیل تأیید و بستن درخواست"></label><button class="button" type="submit">تأیید و بستن</button></form>';
                } else {
                    echo '<small class="muted">در انتظار امتیاز کاربر</small>';
                }
                echo '<form method="post">' . csrf_field() . '<input type="hidden" name="rc_return_workspace" value="supervisor"><input type="hidden" name="rc_return_origin_page" value="supervisor"><input type="hidden" name="rc_return_p" value="' . $supervisorPage . '"><input type="hidden" name="rc_return_q" value="' . e($supervisorFilters['q']) . '"><input type="hidden" name="rc_return_status" value="' . e($supervisorFilters['status']) . '"><input type="hidden" name="rc_return_priority" value="' . e($supervisorFilters['priority']) . '"><input type="hidden" name="rc_return_service_group" value="' . e($supervisorFilters['service_group']) . '"><input type="hidden" name="action" value="supervisor_decision"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><input type="hidden" name="decision" value="rework"><input type="text" name="supervisor_note" required maxlength="500" placeholder="دلیل برگشت برای اصلاح"><button class="button secondary" type="submit">برگشت برای اصلاح</button></form>';
            } else {
                echo '<a class="button secondary" href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;return_page=supervisor&amp;q=' . rawurlencode($supervisorFilters['q']) . '&amp;status=' . rawurlencode($supervisorFilters['status']) . '&amp;priority=' . rawurlencode($supervisorFilters['priority']) . '&amp;service_group=' . rawurlencode($supervisorFilters['service_group']) . '&amp;p=' . $supervisorPage . '">مشاهده</a>';
            }
         } elseif ($ticket['status'] === 'closed' && !ticket_user_is_requester($ticket, $supervisor)) {
             echo '<a class="button secondary" href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;return_page=supervisor&amp;q=' . rawurlencode($supervisorFilters['q']) . '&amp;status=' . rawurlencode($supervisorFilters['status']) . '&amp;priority=' . rawurlencode($supervisorFilters['priority']) . '&amp;service_group=' . rawurlencode($supervisorFilters['service_group']) . '&amp;p=' . $supervisorPage . '">مشاهده</a>';
             if (user_can($supervisor, 'supervisor.reopen')) {
                 echo '<form method="post" class="supervisor-reopen-form">' . csrf_field() . '<input type="hidden" name="rc_return_workspace" value="supervisor"><input type="hidden" name="rc_return_origin_page" value="supervisor"><input type="hidden" name="rc_return_p" value="' . $supervisorPage . '"><input type="hidden" name="rc_return_q" value="' . e($supervisorFilters['q']) . '"><input type="hidden" name="rc_return_status" value="' . e($supervisorFilters['status']) . '"><input type="hidden" name="rc_return_priority" value="' . e($supervisorFilters['priority']) . '"><input type="hidden" name="rc_return_service_group" value="' . e($supervisorFilters['service_group']) . '"><input type="hidden" name="action" value="supervisor_reopen"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><label class="sr-only" for="supervisor-reopen-note-' . (int) $ticket['id'] . '">دلیل بازگشایی رسمی</label><input id="supervisor-reopen-note-' . (int) $ticket['id'] . '" type="text" name="supervisor_note" required maxlength="1000" placeholder="دلیل بازگشایی رسمی"><button class="button secondary" type="submit">بازگشایی رسمی</button></form>';
             }
         } elseif (in_array((string) $ticket['status'], ['new', 'manager_review', 'assigned'], true)
             && empty($ticket['first_response_at']) && empty($ticket['cancel_requested_at']) && !ticket_user_is_requester($ticket, $supervisor) && user_can($supervisor, 'supervisor.decide')) {
             echo '<a class="button secondary" href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;return_page=supervisor&amp;q=' . rawurlencode($supervisorFilters['q']) . '&amp;status=' . rawurlencode($supervisorFilters['status']) . '&amp;priority=' . rawurlencode($supervisorFilters['priority']) . '&amp;service_group=' . rawurlencode($supervisorFilters['service_group']) . '&amp;p=' . $supervisorPage . '">مشاهده</a><form method="post" class="supervisor-reject-form">' . csrf_field() . '<input type="hidden" name="rc_return_workspace" value="supervisor"><input type="hidden" name="rc_return_origin_page" value="supervisor"><input type="hidden" name="rc_return_p" value="' . $supervisorPage . '"><input type="hidden" name="rc_return_q" value="' . e($supervisorFilters['q']) . '"><input type="hidden" name="rc_return_status" value="' . e($supervisorFilters['status']) . '"><input type="hidden" name="rc_return_priority" value="' . e($supervisorFilters['priority']) . '"><input type="hidden" name="rc_return_service_group" value="' . e($supervisorFilters['service_group']) . '"><input type="hidden" name="action" value="supervisor_decision"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><input type="hidden" name="decision" value="reject"><input type="text" name="supervisor_note" required maxlength="500" placeholder="دلیل رد درخواست"><button class="button danger-button" type="submit" data-confirm="درخواست با این دلیل رد و به درخواست‌کننده اطلاع داده می‌شود. ادامه می‌دهید؟">رد درخواست</button></form>';
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
             echo '<a class="button secondary" href="request-center.php?page=ticket&amp;id=' . (int) $ticket['id'] . '&amp;workspace=supervisor&amp;return_page=supervisor&amp;q=' . rawurlencode($supervisorFilters['q']) . '&amp;status=' . rawurlencode($supervisorFilters['status']) . '&amp;priority=' . rawurlencode($supervisorFilters['priority']) . '&amp;service_group=' . rawurlencode($supervisorFilters['service_group']) . '&amp;p=' . $supervisorPage . '">' . $viewLabel . '</a>';
        }
        echo '</span></div>';
    }
    echo '<div class="card" style="padding:12px;margin:12px 0"><strong>' . (int) $summary['rejected'] . '</strong> درخواست ردشده</div>';
    echo '</section>';
    if ($supervisorPages > 1) {
        echo '<nav class="pagination" aria-label="صفحه‌بندی سوپروایزر">';
        for ($supervisorNav = max(1, $supervisorPage - 3); $supervisorNav <= min($supervisorPages, $supervisorPage + 3); $supervisorNav++) {
            echo '<a class="' . ($supervisorNav === $supervisorPage ? 'active' : '') . '" href="' . e($supervisorBaseUrl) . '&p=' . $supervisorNav . '">' . number_format($supervisorNav) . '</a>';
        }
        echo '</nav>';
    }
    echo '<script id="rc-supervisor-decision-interactions">
(function(){
 var confirmations={
  approve:"با تأیید نهایی، درخواست بسته می‌شود. ادامه می‌دهید؟",
  rework:"درخواست برای اصلاح به جریان رسیدگی بازمی‌گردد. ادامه می‌دهید؟",
  reject:"درخواست رد و نتیجه به درخواست‌کننده اعلام می‌شود. ادامه می‌دهید؟",
  supervisor_reopen:"درخواست بسته‌شده دوباره بازگشایی می‌شود. ادامه می‌دهید؟"
 };
 document.querySelectorAll(".supervisor-actions form").forEach(function(form){
  var note=form.querySelector("[name=\"supervisor_note\"]");
  if(note){
   note.addEventListener("input",function(){note.setCustomValidity("");});
   note.addEventListener("blur",function(){
    if(note.value.trim())note.value=note.value.replace(/^\\s+|\\s+$/g,"");
   });
  }
  form.addEventListener("submit",function(event){
   if(note&&!note.value.trim()){
    event.preventDefault();
    note.setCustomValidity("ثبت دلیل تصمیم الزامی است.");
    note.reportValidity();
    note.focus();
    return;
   }
   if(note)note.setCustomValidity("");
   var submitter=event.submitter||form.querySelector("button[type=\"submit\"]");
   var decision=form.querySelector("[name=\"decision\"]");
   var action=form.querySelector("[name=\"action\"]");
   var key=decision?decision.value:(action?action.value:"");
   var message=(submitter&&submitter.dataset.confirm)||confirmations[key];
   if(message&&!window.confirm(message)){event.preventDefault();return;}
   form.querySelectorAll("button[type=\"submit\"],input[type=\"submit\"]").forEach(function(button){
    button.disabled=true;
    if(button.tagName==="BUTTON"){button.dataset.originalText=button.textContent;button.textContent="در حال ثبت…";}
    else{button.dataset.originalValue=button.value;button.value="در حال ثبت…";}
   });
  });
 });
})();
</script>'; 
    echo '<script src="modules/request-center/Assets/request-center.js" defer></script>';
    render_footer();
    exit;
}
