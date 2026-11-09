<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/** Request Center operational queue; executed in legacy front-controller scope. */

if ($page === 'queue') {
    require_once dirname(__DIR__) . '/lib/http-errors.php';
    $queueUser = require_login();
    if (!user_can($queueUser, 'queue.view')) {
        request_center_error_page(403, 'دسترسی مجاز نیست', 'دسترسی به صف کاری فقط برای دارندهٔ مجوز «queue.view» فعال است.');
    }
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
    echo '<style id="rc-agent-queue-page">
.page-heading{align-items:center;margin-bottom:18px}.page-heading h1{font-size:24px;letter-spacing:-.3px}.page-heading p{font-size:13px;color:#687990}.page-heading .actions{display:flex;gap:8px;flex-wrap:wrap}
.queue-filters{display:flex;align-items:end;gap:10px;flex-wrap:wrap;padding:16px!important;margin-bottom:14px!important;border:1px solid #e2e9f2;border-radius:13px}
.queue-filters label{display:grid;gap:5px;min-width:150px;flex:1;color:#526780;font-size:11px;font-weight:700}
.queue-filters input,.queue-filters select,.bulk-actions select,.bulk-actions textarea{width:100%;min-height:39px;padding:8px 10px;border:1px solid #d9e2ee;border-radius:8px;background:#fff;color:#172b4d;font:inherit;font-size:12px}
.queue-filters input:focus,.queue-filters select:focus,.bulk-actions select:focus,.bulk-actions textarea:focus{outline:0;border-color:#709bc9;box-shadow:0 0 0 3px rgba(36,89,166,.11)}
.queue-tabs{display:flex;gap:7px;overflow-x:auto;padding:3px 1px 12px;scrollbar-width:thin}
.queue-tabs a{flex:0 0 auto;padding:7px 12px;border:1px solid #e0e7f0;border-radius:999px;background:#fff;color:#596d85;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap}
.queue-tabs a:hover,.queue-tabs a.active{border-color:#b9cfe9;background:#edf5ff;color:#1e4f87}
.queue-table{overflow:hidden;border:1px solid #e2e9f2;border-radius:14px}
.queue-table .table-head,.queue-table .table-row{display:grid;grid-template-columns:minmax(280px,2.2fr) minmax(110px,.8fr) minmax(120px,.9fr) minmax(120px,.9fr) minmax(100px,.7fr);gap:12px;align-items:center}
.queue-select-all{display:inline-flex;align-items:center;gap:8px;cursor:pointer;white-space:nowrap}.queue-select-all input,.queue-table .ticket-title input{width:16px;height:16px;accent-color:#2459a6}.bulk-actions button:disabled{opacity:.5;cursor:not-allowed;transform:none}
.queue-table .table-head{padding:12px 16px;background:#f6f9fd;color:#61738b;font-size:11px;font-weight:800;border-bottom:1px solid #e7edf5}
.queue-table .table-row{padding:13px 16px;border-bottom:1px solid #edf1f6;font-size:12px}
.queue-table .table-row:last-child{border-bottom:0}
.queue-table .table-row:hover{background:#f9fbfe}
.queue-table .ticket-title{display:grid;grid-template-columns:auto minmax(0,1fr);gap:4px 9px;align-items:center;min-width:0}
.queue-table .ticket-title input{grid-row:1/3;accent-color:#2459a6}
.queue-table .ticket-title>b{color:#6c7d93;font-size:10px;direction:ltr}
.queue-table .ticket-title>a{min-width:0;color:#214d7e;text-decoration:none}
.queue-table .ticket-title>a:hover{text-decoration:underline}
.queue-table .ticket-title strong{display:block;overflow-wrap:anywhere;line-height:1.7}
.queue-table .ticket-title small{grid-column:2;color:#77869a;font-size:10px;line-height:1.6}
.queue-table .status{display:inline-flex;padding:4px 9px;border-radius:999px;font-style:normal;font-size:10px;white-space:nowrap}
.bulk-actions{display:grid;grid-template-columns:minmax(170px,.7fr) minmax(260px,2fr) auto;gap:12px;align-items:end;margin:14px 0;padding:16px;border:1px solid #e1e8f1;border-radius:13px;background:#fff;box-shadow:0 4px 15px rgba(23,43,77,.035)}
.bulk-actions .bulk-note{display:grid;gap:6px;color:#536780;font-size:11px;font-weight:700}
.bulk-actions textarea{min-height:72px;resize:vertical}
.bulk-actions .button{min-height:40px;white-space:nowrap}.bulk-actions .bulk-submit{display:grid;gap:6px;justify-items:stretch}.bulk-actions .bulk-submit small{color:#687990;font-size:11px;text-align:center}
.pagination{display:flex;justify-content:center;gap:7px;flex-wrap:wrap;padding:18px}
.pagination a,.pagination .active{min-width:34px;padding:6px 10px;border:1px solid #dfe7f0;border-radius:8px;background:#fff;text-align:center;color:#526780;text-decoration:none}
.pagination .active{background:#2459a6;color:#fff;border-color:#2459a6}
@media(max-width:900px){.queue-table .table-head{display:none}.queue-table .table-row{grid-template-columns:minmax(0,1fr) minmax(110px,.45fr);gap:8px 12px}.queue-table .ticket-title{grid-column:1/-1}.bulk-actions{grid-template-columns:1fr 1fr}.bulk-actions .bulk-note{grid-column:1/-1}}
@media(max-width:600px){.page-heading{align-items:flex-start;flex-direction:column}.page-heading .actions{width:100%}.queue-filters label{min-width:calc(50% - 8px)}.queue-table .table-row{grid-template-columns:1fr 1fr;padding:12px}.queue-table .ticket-title{grid-column:1/-1}.bulk-actions{grid-template-columns:1fr}.bulk-actions .bulk-note{grid-column:auto}.bulk-actions .button{width:100%}}
</style>';
     echo '<section class="page-heading"><div><span class="eyebrow">صف مرکزی خدمات</span><h1>صف کار واحد رسیدگی</h1><p>درخواست‌های هر حوزه جدا هستند؛ موارد خارج از SLA/OLA و اولویت‌های حیاتی بالاتر قرار می‌گیرند. نمایش ' . ($queueRows ? number_format($queueOffset + 1) . ' تا ' . number_format($queueOffset + count($queueRows)) . ' از ' . number_format($queueTotal) : '۰ از ' . number_format($queueTotal)) . ' درخواست.</p></div><div class="actions"><a class="button secondary" href="request-center.php?workspace=agent">داشبورد کارشناس</a><a class="button secondary" href="request-center.php?page=reports">گزارش کامل</a></div></section><form method="get" action="request-center.php" class="card queue-filters" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;padding:14px;margin-bottom:14px"><input type="hidden" name="page" value="queue"><input type="hidden" name="queue" value="' . e($queueName) . '"><label>جست‌وجو<input type="search" name="q" maxlength="100" value="' . e($queueFilters['q']) . '" placeholder="شماره یا عنوان درخواست"></label><label>وضعیت<select name="status"><option value="">همه وضعیت‌ها</option>' . (function () use ($queueFilters) { $html=''; foreach (['new'=>'جدید','manager_review'=>'در انتظار ارجاع','assigned'=>'ارجاع‌شده','in_progress'=>'در حال انجام','waiting_user'=>'انتظار کاربر','pending'=>'در انتظار پاسخ','resolved'=>'حل‌شده','closed'=>'بسته‌شده','cancelled'=>'لغوشده','rejected'=>'ردشده'] as $key=>$label) { $html .= '<option value="' . $key . '"' . ($queueFilters['status']===$key?' selected':'') . '>' . $label . '</option>'; } return $html; })() . '</select></label><label>اولویت<select name="priority"><option value="">همه اولویت‌ها</option><option value="normal"' . ($queueFilters['priority']==='normal'?' selected':'') . '>عادی</option><option value="urgent"' . ($queueFilters['priority']==='urgent'?' selected':'') . '>فوری</option><option value="critical"' . ($queueFilters['priority']==='critical'?' selected':'') . '>بحرانی</option></select></label><label>حوزه<select name="service_group"><option value="">همه حوزه‌ها</option><option value="it"' . ($queueFilters['service_group']==='it'?' selected':'') . '>فناوری اطلاعات</option><option value="support"' . ($queueFilters['service_group']==='support'?' selected':'') . '>پشتیبانی</option></select></label><button class="button" type="submit">اعمال فیلتر</button><a class="button secondary" href="request-center.php?page=queue&amp;queue=' . e($queueName) . '">پاک‌کردن</a></form><div class="queue-tabs"><a class="' . ($queueName === 'all' ? 'active' : '') . '" href="request-center.php?page=queue&queue=all' . $queueFilterSuffix . '">همه</a><a class="' . ($queueName === 'unassigned' ? 'active' : '') . '" href="request-center.php?page=queue&queue=unassigned' . $queueFilterSuffix . '">بدون مسئول</a><a class="' . ($queueName === 'overdue' ? 'active' : '') . '" href="request-center.php?page=queue&queue=overdue' . $queueFilterSuffix . '">خارج از SLA/OLA</a><a class="' . ($queueName === 'mine' ? 'active' : '') . '" href="request-center.php?page=queue&queue=mine' . $queueFilterSuffix . '">مسئول من</a><a class="' . ($queueName === 'waiting_user' ? 'active' : '') . '" href="request-center.php?page=queue&queue=waiting_user' . $queueFilterSuffix . '">انتظار کاربر</a><a class="' . ($queueName === 'resolved' ? 'active' : '') . '" href="request-center.php?page=queue&queue=resolved' . $queueFilterSuffix . '">حل‌شده</a><a class="' . ($queueName === 'cancellation_requested' ? 'active' : '') . '" href="request-center.php?page=queue&queue=cancellation_requested' . $queueFilterSuffix . '">درخواست لغو</a><a class="' . ($queueName === 'cancelled' ? 'active' : '') . '" href="request-center.php?page=queue&queue=cancelled' . $queueFilterSuffix . '">لغوشده</a><a class="' . ($queueName === 'rejected' ? 'active' : '') . '" href="request-center.php?page=queue&queue=rejected' . $queueFilterSuffix . '">ردشده</a></div><form method="post"><section class="card ticket-table queue-table"><div class="table-head"><span><label class="queue-select-all"><input type="checkbox" data-queue-select-all> انتخاب همه / درخواست</label></span><span>معاونت</span><span>ثبت‌کننده</span><span>مسئول</span><span>وضعیت</span></div>';
    foreach ($queueRows as $queueTicket) {
        echo '<label class="table-row queue-row"><span class="ticket-title"><input type="checkbox" name="ticket_ids[]" value="' . (int) $queueTicket['id'] . '"><b>' . e(ticket_number((int) $queueTicket['id'])) . '</b><a href="request-center.php?page=ticket&amp;id=' . (int) $queueTicket['id'] . '&amp;workspace=agent&amp;agent_view=team&amp;return_page=queue&amp;queue=' . rawurlencode($queueName) . '&amp;q=' . rawurlencode($queueFilters['q']) . '&amp;status=' . rawurlencode($queueFilters['status']) . '&amp;priority=' . rawurlencode($queueFilters['priority']) . '&amp;service_group=' . rawurlencode($queueFilters['service_group']) . '&amp;p=' . $queuePage . '"><strong>' . e($queueTicket['subject']) . '</strong></a><small>' . e(ticket_type_label($queueTicket['ticket_type'])) . ' • ' . e(priority_label($queueTicket['priority'])) . (!empty($queueTicket['cancel_requested_at']) ? ' • درخواست لغو در انتظار بررسی' : '') . '</small></span><span>' . e($queueTicket['department_name'] ?: 'بدون معاونت') . '</span><span>' . e($queueTicket['requester_name']) . '</span><span>' . e($queueTicket['assignee_name'] ?: 'بدون مسئول') . '</span><span><em class="status ' . e($queueTicket['status']) . '">' . e(request_center_status_label($queueTicket['status'])) . '</em></span></label>';
        }
    echo '</section><div class="bulk-actions"><input type="hidden" name="rc_return_queue" value="' . e($queueName) . '"><input type="hidden" name="rc_return_p" value="' . $queuePage . '"><input type="hidden" name="rc_return_q" value="' . e($queueFilters['q']) . '"><input type="hidden" name="rc_return_status" value="' . e($queueFilters['status']) . '"><input type="hidden" name="rc_return_priority" value="' . e($queueFilters['priority']) . '"><input type="hidden" name="rc_return_service_group" value="' . e($queueFilters['service_group']) . '"><input type="hidden" name="action" value="bulk_update_tickets">' . csrf_field() . '<select name="bulk_status"><option value="in_progress">در حال انجام</option><option value="waiting_user">در انتظار پاسخ کاربر</option><option value="resolved">حل‌شده</option></select><label class="bulk-note">دلیل و پیام مشترک (برای هر تغییر وضعیت الزامی)<textarea name="bulk_note" rows="2" maxlength="5000" required placeholder="دلیل اقدام را بنویسید؛ این پیام برای ثبت‌کنندهٔ همهٔ درخواست‌های انتخاب‌شده قابل مشاهده است"></textarea></label><div class="bulk-submit"><small data-queue-selection-count aria-live="polite">۰ درخواست انتخاب شده</small><button class="button" type="submit">اعمال روی انتخاب‌ها</button></div></div></form>';
    if ($queuePages > 1) {
        echo '<nav class="pagination" aria-label="صفحه‌بندی صف">';
        for ($queueNav = max(1, $queuePage - 3); $queueNav <= min($queuePages, $queuePage + 3); $queueNav++) {
            echo '<a class="' . ($queueNav === $queuePage ? 'active' : '') . '" href="' . e($queueBaseUrl) . '&p=' . $queueNav . '">' . number_format($queueNav) . '</a>';
        }
        echo '</nav>';
    }
    // اسکریپت درون‌صفحه: تضمین کارکرد سربرگ‌ها و مودال‌ها مستقل از کش app.js

    echo '<script id="rc-queue-interactions">
(function(){
 var root=document.querySelector(".queue-table");if(!root)return;
 var master=root.querySelector("[data-queue-select-all]");
 var items=Array.prototype.slice.call(root.querySelectorAll("input[name=\\"ticket_ids[]\\"]"));
 var form=root.closest("form");
 var submit=form?form.querySelector(".bulk-actions button[type=\\"submit\\"]"):null;
 var status=form?form.querySelector("[name=\\"bulk_status\\"]"):null;
 var note=form?form.querySelector("[name=\\"bulk_note\\"]"):null;
 var count=form?form.querySelector("[data-queue-selection-count]"):null;
 var update=function(){
  var selected=items.filter(function(item){return item.checked;}).length;
  if(master){master.checked=items.length>0&&selected===items.length;master.indeterminate=selected>0&&selected<items.length;}
  if(submit){submit.disabled=selected===0;submit.textContent=selected?("اعمال روی "+selected+" درخواست"):"ابتدا درخواست انتخاب کنید";}
  if(count){count.textContent=selected+" درخواست انتخاب شده";count.setAttribute("aria-live","polite");}
 };
 if(master){master.addEventListener("change",function(){items.forEach(function(item){item.checked=master.checked;});update();});}
 items.forEach(function(item){item.addEventListener("change",update);});
 if(form){form.addEventListener("submit",function(event){
  if(form.dataset.submitting==="1"){event.preventDefault();return;}
  var selected=items.filter(function(item){return item.checked;});
  if(!selected.length){event.preventDefault();window.alert("حداقل یک درخواست را انتخاب کنید.");return;}
  if(note&&!note.value.trim()){event.preventDefault();note.focus();window.alert("برای عملیات گروهی، دلیل اقدام را بنویسید.");return;}
  var labels={in_progress:"در حال انجام",waiting_user:"در انتظار پاسخ کاربر",resolved:"حل‌شده"};
  var target=status&&labels[status.value]?labels[status.value]:"وضعیت انتخاب‌شده";
  if(!window.confirm(selected.length+" درخواست به وضعیت «"+target+"» منتقل می‌شوند. دلیل اقدام نیز برای درخواست‌کنندگان ثبت خواهد شد. ادامه می‌دهید؟")){event.preventDefault();return;}
  form.dataset.submitting="1";
  if(submit){submit.disabled=true;submit.textContent="در حال اعمال روی "+selected.length+" درخواست…";}
 });}
 update();
})();
</script>'; 
    render_footer();
    exit;
}
