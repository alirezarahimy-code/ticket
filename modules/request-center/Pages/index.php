<?php
declare(strict_types=1);

/**
 * Request Center home and workspace router.
 *
 * Lifecycle POST actions are dispatched by request-center.php through the
 * module-owned POST runner before page routing. This file owns the home,
 * requester/agent list, and dedicated admin/supervisor/calendar workspaces.
 * No schema DDL or schema healing is allowed from this entry point.
 */
require dirname(__DIR__, 3) . '/bootstrap.php';

$user = require_login();
require_once dirname(__DIR__) . '/RequestCenterRepository.php';
require_once dirname(__DIR__) . '/lib/http-errors.php';
require_once dirname(__DIR__) . '/lib/html-feedback.php';

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
$statusLabels = [
    'new' => 'جدید',
    'manager_review' => 'در انتظار ارجاع',
    'assigned' => 'ارجاع‌شده',
    'in_progress' => 'در حال انجام',
    'waiting_user' => 'در انتظار پاسخ کاربر',
    'pending' => 'در انتظار پاسخ کاربر',
    'resolved' => 'حل‌شده',
    'closed' => 'بسته‌شده',
    'cancelled' => 'لغوشده',
    'rejected' => 'ردشده',
];
$priorityLabels = ['normal' => 'عادی', 'urgent' => 'فوری', 'critical' => 'بحرانی'];
$typeLabels = ['incident' => 'رخداد', 'request' => 'درخواست خدمت', 'problem' => 'مشکل', 'change' => 'تغییر'];
$workspaceLabels = [
    'requester' => 'درخواست‌دهنده',
    'agent' => 'کارشناس و مدیر رسیدگی',
    'supervisor' => 'سوپروایزر',
    'admin' => 'مدیر مرکز درخواست‌ها',
];
$workspaceOptions = [];
if (user_can($user, 'ticket.create') || user_can($user, 'ticket.view_own')) {
    $workspaceOptions['requester'] = $workspaceLabels['requester'];
}
if (user_can($user, 'ticket.edit') || user_can($user, 'queue.view') || user_can($user, 'ticket.view_unit') || user_can($user, 'ticket.view_all')) {
    $workspaceOptions['agent'] = $workspaceLabels['agent'];
}
if (user_can($user, 'supervisor.panel')) {
    $workspaceOptions['supervisor'] = $workspaceLabels['supervisor'];
}
if (user_is_primary_admin($user)) {
    $workspaceOptions['admin'] = $workspaceLabels['admin'];
}
$defaultWorkspace = user_is_primary_admin($user) ? 'admin'
    : (user_can($user, 'supervisor.panel') ? 'supervisor'
        : (isset($workspaceOptions['agent']) ? 'agent' : 'requester'));
$requestedWorkspace = (string) ($_GET['workspace'] ?? $defaultWorkspace);
$workspace = isset($workspaceOptions[$requestedWorkspace]) ? $requestedWorkspace : $defaultWorkspace;
$workspaceTitle = $workspaceOptions[$workspace] ?? $workspaceLabels['requester'];
$workspaceUrl = 'request-center.php?workspace=' . rawurlencode($workspace);
$agentView = 'mine';
if ($workspace === 'agent' && user_can($user, 'queue.view')
    && (string) ($_GET['agent_view'] ?? 'mine') === 'team') {
    $agentView = 'team';
}
if ($workspace === 'agent') {
    $workspaceTitle .= $agentView === 'team' ? ' — صف گروهی' : ' — صف شخصی';
}
if ($workspace === 'agent') {
    $workspaceUrl .= '&agent_view=' . rawurlencode($agentView);
}
if ($workspaceOptions === []) {
    request_center_error_page(403, 'دسترسی فعال نیست', 'دسترسی به مرکز درخواست‌ها برای این حساب فعال نیست. برای دریافت دسترسی با ادمین سامانه تماس بگیرید.');
}
$repo = new RequestCenterRepository(db());
$schemaProblems = $repo->schemaProblems();
if ($schemaProblems !== []) {
    request_center_error_page(503, 'ساختار پایگاه‌داده کامل نیست', 'این ورودی عمداً هیچ تغییری در Schema ایجاد نمی‌کند. ابتدا بستهٔ Migration رسمی همین نسخه را روی یک نسخهٔ پشتیبان‌شده از پایگاه‌داده اعمال کنید، سپس دوباره تلاش کنید.', $schemaProblems);
}
if ((string) ($_GET['calendar'] ?? '') === '1' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require __DIR__ . '/../Actions/appointment-actions.php';
    exit; // appointment-actions redirects back to the calendar
}
if ((string) ($_GET['calendar'] ?? '') === '1') {
    require __DIR__ . '/calendar.php';
    exit;
}

// Dedicated role workspaces are routed before the generic requester/agent list.
if ($workspace === 'admin') {
    require __DIR__ . '/admin.php';
    exit;
}
if ($workspace === 'supervisor') {
    require __DIR__ . '/supervisor-workspace.php';
    exit;
}

$ticketId = max(0, (int) ($_GET['id'] ?? 0));
$page = max(1, (int) ($_GET['p'] ?? 1));

// One canonical operational detail page serves every workspace.
// Forward older workspace links/bookmarks containing ?id= here, preserving
// validated list context instead of rendering a duplicate read-only detail view.
if ($ticketId > 0) {
    $detailParams = [
        'page' => 'ticket',
        'id' => $ticketId,
        'workspace' => $workspace,
        'p' => $page,
    ];
    if ($workspace === 'agent') {
        $detailParams['agent_view'] = $agentView;
        $queueNames = ['all', 'unassigned', 'overdue', 'mine', 'waiting_user', 'resolved', 'cancellation_requested', 'cancelled', 'rejected'];
        $requestedQueue = (string) ($_GET['queue'] ?? ($agentView === 'mine' ? 'mine' : 'all'));
        $detailParams['queue'] = in_array($requestedQueue, $queueNames, true) ? $requestedQueue : 'all';
    }
    foreach (['q' => 100, 'status' => 32, 'priority' => 16, 'service_group' => 16] as $key => $maxLength) {
        $value = trim((string) ($_GET[$key] ?? ''));
        if ($value !== '' && strlen($value) <= $maxLength) {
            $detailParams[$key] = $value;
        }
    }
    redirect('request-center.php?' . http_build_query($detailParams));
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: private, no-store');
?>
<?php
// پوستهٔ مرکزی سامانه (render_header) + ناوبری ماژول؛ تم سراسری اعمال می‌شود.
$__rcRender = dirname(__DIR__, 3) . '/lib/render.php';
if (is_file($__rcRender)) { require_once $__rcRender; }
render_header('مرکز درخواست‌ها — ' . $workspaceTitle, $user);
?>
<div class="rc-page">
<style>
.rc-workspace-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:4px 0 14px}
.rc-workspace-tabs a{padding:7px 14px;border:1px solid var(--line);border-radius:999px;background:var(--white,#fffdf8);color:var(--muted);font-size:13px;font-weight:600}
.rc-workspace-tabs a:hover{border-color:var(--navy-2);color:var(--navy-2);text-decoration:none}
.rc-workspace-tabs a.active{background:var(--navy);border-color:var(--navy);color:#fff}
.workspace-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0 0 18px}
.workspace-metric{background:var(--white,#fffdf8);border:1px solid var(--line);border-radius:14px;padding:12px 14px}
.workspace-metric strong{display:block;font-size:23px;font-variant-numeric:tabular-nums}
.workspace-metric span{font-size:12px;color:var(--muted)}
@media(max-width:700px){.workspace-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}
.deadline{display:inline-flex;flex-direction:column;gap:1px;white-space:nowrap;font-size:11px}
.deadline b{font-weight:700}.deadline small{font-size:10px}
.deadline-late{color:#b42318}.deadline-soon{color:#9a6700}.deadline-paused,.deadline-empty{color:var(--muted)}.deadline-normal{color:#276749}
.deadline-badge.danger{color:#b42318;font-weight:700}.deadline-badge.warn{color:#9a6700;font-weight:700}
.rc-breach-alert-item{display:block;border:1px solid #f3c1bb;background:#fff5f5;border-radius:10px;padding:8px 10px;margin:6px 0;font-size:12px}
.heading{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:20px;flex-wrap:wrap}
.heading h1{font-size:25px;margin:0}.heading p{margin:4px 0 0;color:var(--muted)}
.table{width:100%;border-collapse:collapse}.table th,.table td{padding:12px 13px;text-align:right;border-bottom:1px solid var(--line);vertical-align:middle;font-size:12px}.table th{background:#faf7ec;color:var(--muted)}.table tr:last-child td{border-bottom:0}
.subject{font-weight:700}.meta{display:block;color:var(--muted);font-size:12px}
.empty{padding:44px 20px;text-align:center;color:var(--muted)}
@media(max-width:780px){.heading{align-items:flex-start;flex-direction:column}.table td:nth-child(3){display:none}.table th:nth-child(3){display:none}}
</style>
<?php if (count($workspaceOptions) > 1): ?><nav class="rc-workspace-tabs" aria-label="فضاهای کاری"><?php foreach ($workspaceOptions as $key => $label): ?><a class="<?= $workspace === $key ? 'active' : '' ?>" href="request-center.php?workspace=<?= $escape($key) ?>"><?= $escape($label) ?></a><?php endforeach; ?></nav><?php endif; ?>
<?php request_center_render_flashes(); ?>
<script src="modules/request-center/Assets/request-center.js" defer></script>
<?php
$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'status' => (string) ($_GET['status'] ?? ''),
    'priority' => (string) ($_GET['priority'] ?? ''),
    'service_group' => (string) ($_GET['service_group'] ?? ''),
];
if (!in_array($filters['status'], array_keys($statusLabels), true)) $filters['status'] = '';
if (!in_array($filters['priority'], array_keys($priorityLabels), true)) $filters['priority'] = '';
if (!in_array($filters['service_group'], ['it', 'support'], true)) $filters['service_group'] = '';
$filters['q'] = function_exists('mb_substr') ? mb_substr($filters['q'], 0, 100, 'UTF-8') : substr($filters['q'], 0, 100);
if ($workspace === 'requester') {
    $filters['requester_id'] = (int) $user['id'];
} elseif ($workspace === 'agent' && $agentView === 'mine') {
    $filters['assigned_to'] = (int) $user['id'];
}
$agentQueueName = 'mine';
if ($workspace === 'agent') {
    $allowedAgentQueues = $agentView === 'mine'
        ? ['mine']
        : (user_can($user, 'queue.view')
            ? ['all', 'unassigned', 'overdue', 'mine', 'waiting_user', 'resolved', 'cancellation_requested', 'cancelled', 'rejected']
            : ['mine']);
    $requestedQueue = (string) ($_GET['queue'] ?? ($agentView === 'mine' ? 'mine' : 'all'));
    $agentQueueName = in_array($requestedQueue, $allowedAgentQueues, true)
        ? $requestedQueue
        : ($agentView === 'mine' ? 'mine' : 'all');
    if ($agentView === 'mine' && !user_can($user, 'queue.view')) {
        $agentQueueName = 'mine';
    }
    $queueData = $repo->listQueue($user, $agentQueueName, $page, 50, $filters);
    $result = ['total' => $queueData['total'], 'rows' => $queueData['rows']];
    $pages = $queueData['pages'];
    $page = $queueData['page'];
} else {
    $result = $repo->listVisible($user, $page, 50, $filters);
    $pages = max(1, (int) ceil($result['total'] / 50));
    $page = min($page, $pages);
}
$workspaceSummary = null;
if ($workspace === 'requester') {
    $workspaceSummary = $repo->workspaceSummary($user, ['requester_id' => (int) $user['id']]);
} elseif ($workspace === 'agent') {
    $workspaceSummaryFilters = $agentView === 'mine' ? ['assigned_to' => (int) $user['id']] : [];
    $workspaceSummary = $repo->workspaceSummary($user, $workspaceSummaryFilters);
}
// هشدارهای تخطی SLA/OLA (ثبت‌شده توسط cron) در محدودهٔ دسترسی همین کارشناس — فراتر از «لیست معوق»
$agentBreachAlerts = [];
$agentBreachCounts = ['sla_alert' => 0, 'ola_alert' => 0];
if ($workspace === 'agent') {
    try {
        [$breachScopeWhere, $breachScopeParams] = ticket_scope($user, 't');
        $breachConditions = [];
        if ($breachScopeWhere !== '') {
            $breachConditions[] = '(' . substr($breachScopeWhere, strlen('WHERE ')) . ')';
        }
        $breachParams = $breachScopeParams;
        if ($agentView === 'mine') {
            $breachConditions[] = 't.assigned_to = ?';
            $breachParams[] = (int) $user['id'];
        }
        $breachWhere = $breachConditions !== [] ? 'WHERE ' . implode(' AND ', $breachConditions) . ' AND ' : 'WHERE ';
        $breachSql = 'SELECT COUNT(*) AS c, e.event_type FROM ticket_events e JOIN tickets t ON t.id = e.ticket_id '
            . $breachWhere
            . "e.event_type IN ('sla_alert', 'ola_alert') GROUP BY e.event_type";
        $breachCountQuery = db()->prepare($breachSql);
        $breachCountQuery->execute($breachParams);
        foreach ($breachCountQuery->fetchAll(PDO::FETCH_ASSOC) as $breachRow) {
            $agentBreachCounts[(string) $breachRow['event_type']] = (int) $breachRow['c'];
        }
        $breachListSql = 'SELECT e.event_type, e.created_at, t.id AS ticket_id, t.ticket_no, t.subject, t.due_at, t.ola_due_at, t.status '
            . 'FROM ticket_events e JOIN tickets t ON t.id = e.ticket_id '
            . $breachWhere
            . "e.event_type IN ('sla_alert', 'ola_alert') ORDER BY e.created_at DESC, e.id DESC LIMIT 6";
        $breachListQuery = db()->prepare($breachListSql);
        $breachListQuery->execute($breachParams);
        $agentBreachAlerts = $breachListQuery->fetchAll();
    } catch (Throwable) {
        $agentBreachAlerts = [];
        $agentBreachCounts = ['sla_alert' => 0, 'ola_alert' => 0];
    }
}
$deadlineBadge = static function (mixed $value, bool $paused = false) use ($escape): string {
    $raw = trim((string) $value);
    if ($raw === '') {
        return '<span class="deadline-empty">—</span>';
    }
    $timestamp = strtotime($raw);
    if ($timestamp === false) {
        return $escape($raw);
    }
    $state = $paused ? 'paused' : ($timestamp < time() ? 'late' : ($timestamp <= time() + 86400 ? 'soon' : 'normal'));
    $label = $paused ? 'متوقف' : ($state === 'late' ? 'گذشته' : ($state === 'soon' ? 'نزدیک' : 'فعال'));
    return '<span class="deadline deadline-' . $state . '"><b>' . $escape(date('Y/m/d H:i', $timestamp)) . '</b><small>' . $label . '</small></span>';
};
$queryParams = ['workspace' => $workspace];
if ($workspace === 'agent') {
    $queryParams['agent_view'] = $agentView;
    $queryParams['queue'] = $agentQueueName;
}
$queryString = http_build_query(array_merge($queryParams, array_filter($filters, static fn ($value): bool => $value !== '')));
$detailContextParams = array_merge($queryParams, array_filter($filters, static fn ($value): bool => $value !== ''));
$detailContextParams['p'] = $page;
?>
<?php
$agentQueueFilterParams = array_filter([
    'q' => $filters['q'],
    'status' => $filters['status'],
    'priority' => $filters['priority'],
    'service_group' => $filters['service_group'],
], static fn ($value): bool => $value !== '');
$agentQueueFilterSuffix = $agentQueueFilterParams !== []
    ? '&amp;' . $escape(http_build_query($agentQueueFilterParams))
    : '';
?>
<?php if ($workspace === 'agent' && user_can($user, 'queue.view')): ?>
<nav class="rc-workspace-tabs" aria-label="نوع صف کارشناس">
<a class="<?= $agentView === 'mine' ? 'active' : '' ?>" href="request-center.php?workspace=agent&amp;agent_view=mine">صف شخصی من</a>
<a class="<?= $agentView === 'team' ? 'active' : '' ?>" href="request-center.php?workspace=agent&amp;agent_view=team">صف گروهی مجاز</a>
</nav>
<?php endif; ?>
<?php if ($workspace === 'agent'): ?>
<nav class="rc-workspace-tabs" aria-label="فیلتر صف عملیاتی">
<?php
$agentQueueLabels = ['all' => 'همه', 'unassigned' => 'بدون مسئول', 'overdue' => 'خارج از SLA/OLA', 'mine' => 'مسئول من', 'waiting_user' => 'انتظار کاربر', 'resolved' => 'حل‌شده', 'cancellation_requested' => 'درخواست لغو', 'cancelled' => 'لغوشده', 'rejected' => 'ردشده'];
foreach ($agentQueueLabels as $queueKey => $queueLabel):
    if (!user_can($user, 'queue.view') && $queueKey !== 'mine') continue;
?>
<a class="<?= $agentQueueName === $queueKey ? 'active' : '' ?>" href="request-center.php?workspace=agent&amp;agent_view=<?= $escape($agentView) ?>&amp;queue=<?= $escape($queueKey) ?><?= $agentQueueFilterSuffix ?>"><?= $escape($queueLabel) ?></a>
<?php endforeach; ?>
</nav>
<?php endif; ?>
<?php if (is_array($workspaceSummary)): ?>
<?php
$metricItems = $workspace === 'requester'
    ? [
        ['total', 'کل درخواست‌های من'],
        ['active', 'در حال پیگیری'],
        ['waiting_user', 'منتظر پاسخ من'],
        ['awaiting_supervisor', 'منتظر بررسی نهایی'],
    ]
    : [
        ['total', 'کل درخواست‌های قابل مشاهده'],
        ['active', 'در گردش'],
        ['overdue', 'خارج از SLA/OLA'],
        ['due_soon', 'موعد در ۲۴ ساعت آینده'],
    ];
?>
<section class="workspace-metrics" aria-label="خلاصه وضعیت فضای کاری">
<?php foreach ($metricItems as [$metricKey, $metricLabel]): ?>
<div class="workspace-metric"><strong><?= number_format($workspaceSummary[$metricKey] ?? 0) ?></strong><span><?= $escape($metricLabel) ?></span></div>
<?php endforeach; ?>
</section>
<?php endif; ?>
<section class="heading"><div><h1><?= $escape($workspaceTitle) ?></h1><p><?= number_format($result['total']) ?> درخواست در محدودهٔ دسترسی این حساب</p></div><div style="display:flex;gap:8px;flex-wrap:wrap">
<?php if ($workspace === 'agent'): ?><a class="button secondary" href="request-center.php?workspace=<?= $escape($workspace) ?>&amp;calendar=1&amp;agent_view=<?= $escape($agentView) ?>">تقویم موعد کارشناسان</a><?php endif; ?>
<?php if (user_can($user, 'ticket.create')): ?><a class="button" href="request-center.php?page=new-ticket">ثبت درخواست جدید</a><?php endif; ?>
</div></section>
<form method="get" action="request-center.php" class="card" style="padding:14px;margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:end">
<input type="hidden" name="workspace" value="<?= $escape($workspace) ?>">
<?php if ($workspace === 'agent'): ?><input type="hidden" name="agent_view" value="<?= $escape($agentView) ?>"><input type="hidden" name="queue" value="<?= $escape($agentQueueName) ?>"><?php endif; ?>
<label>جست‌وجو<input type="search" name="q" maxlength="100" value="<?= $escape($filters['q']) ?>" placeholder="شماره یا عنوان" style="display:block;padding:9px;border:1px solid var(--line);border-radius:8px"></label>
<label>وضعیت<select name="status" style="display:block;padding:9px;border:1px solid var(--line);border-radius:8px"><option value="">همه</option><?php foreach ($statusLabels as $key => $label): ?><option value="<?= $escape($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= $escape($label) ?></option><?php endforeach; ?></select></label>
<label>اولویت<select name="priority" style="display:block;padding:9px;border:1px solid var(--line);border-radius:8px"><option value="">همه</option><?php foreach ($priorityLabels as $key => $label): ?><option value="<?= $escape($key) ?>" <?= $filters['priority'] === $key ? 'selected' : '' ?>><?= $escape($label) ?></option><?php endforeach; ?></select></label>
<label>حوزه<select name="service_group" style="display:block;padding:9px;border:1px solid var(--line);border-radius:8px"><option value="">همه</option><option value="it" <?= $filters['service_group'] === 'it' ? 'selected' : '' ?>>فناوری اطلاعات</option><option value="support" <?= $filters['service_group'] === 'support' ? 'selected' : '' ?>>پشتیبانی</option></select></label>
<button class="button" type="submit">اعمال فیلتر</button><a class="button secondary" href="<?= $escape($workspaceUrl . ($workspace === 'agent' ? '&queue=' . rawurlencode($agentQueueName) : '')) ?>">پاک‌کردن فیلترها</a>
</form>
<?php if ($workspace === 'agent' && (array_sum($agentBreachCounts) > 0 || $agentBreachAlerts)): ?>
<section class="card" style="padding:14px 16px;margin-bottom:16px" aria-labelledby="rc-breach-alerts-title">
<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px">
<h2 id="rc-breach-alerts-title" style="margin:0;font-size:16px">هشدارهای تخطی SLA/OLA <span style="font-size:12px;color:#b91c1c;font-weight:400">(فراتر از لیست معوق)</span></h2>
<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
<span class="deadline-badge danger" style="background:#fef2f2;color:#b91c1c;border-radius:999px;padding:3px 10px;font-size:12px">SLA: <?= number_format($agentBreachCounts['sla_alert']) ?></span>
<span class="deadline-badge warn" style="background:#fffbeb;color:#b45309;border-radius:999px;padding:3px 10px;font-size:12px">OLA: <?= number_format($agentBreachCounts['ola_alert']) ?></span>
<a class="button secondary" style="min-height:30px;padding:4px 10px;font-size:12px" href="request-center.php?workspace=agent&amp;agent_view=<?= $escape($agentView) ?>&amp;queue=overdue">لیست کامل تخطی‌ها</a>
</div></div>
<?php if (!$agentBreachAlerts): ?><p class="muted" style="margin:0">هشدار ثبت‌شدهٔ فعالی وجود ندارد.</p><?php else: ?>
<div style="display:flex;gap:10px;flex-wrap:wrap">
<?php foreach ($agentBreachAlerts as $breachAlert): ?>
<a class="rc-breach-alert-item" href="request-center.php?page=ticket&amp;id=<?= (int) $breachAlert['ticket_id'] ?>&amp;workspace=agent&amp;agent_view=<?= $escape($agentView) ?>" style="display:block;border:1px solid #fcd34d;background:#fffbeb;border-radius:10px;padding:8px 12px;text-decoration:none;color:inherit;max-width:340px">
<b><?= $escape($breachAlert['ticket_no'] ?: ('#' . (int) $breachAlert['ticket_id'])) ?> · <?= $escape($breachAlert['subject']) ?></b>
<small style="display:block;color:#92400e"><?= $escape($breachAlert['event_type'] === 'sla_alert' ? 'هشدار SLA' : 'هشدار OLA') ?> · <?= $escape((string) $breachAlert['created_at']) ?><br>موعد: SLA <?= $escape($breachAlert['due_at'] ?: '—') ?> · OLA <?= $escape($breachAlert['ola_due_at'] ?: '—') ?></small>
</a>
<?php endforeach; ?>
</div><?php endif; ?>
</section>
<?php endif; ?>
<section class="card">
<?php if (!$result['rows']): ?><div class="empty"><strong>درخواستی مطابق شرایط فعلی پیدا نشد.</strong><p>فیلترهای جست‌وجو یا وضعیت را تغییر دهید؛ برای مشاهدهٔ دوبارهٔ کل موارد مجاز می‌توانید فیلترها را پاک کنید.</p><a class="button secondary" href="request-center.php?<?= $escape(http_build_query($queryParams)) ?>">پاک‌کردن فیلترها و نمایش همه</a></div><?php else: ?>
<div style="overflow-x:auto"><table class="table"><thead><tr><th>درخواست</th><th>ثبت‌کننده</th><th>مسئول</th><th>وضعیت</th><th>اولویت</th><th>مهلت SLA</th><?php if ($workspace === 'agent'): ?><th>موعد پاسخ OLA</th><th>موعد حل OLA</th><?php endif; ?><th>آخرین تغییر</th></tr></thead><tbody>
<?php foreach ($result['rows'] as $row): ?>
<tr>
<td><a class="subject" href="request-center.php?<?= $escape(http_build_query(array_merge($detailContextParams, ['page' => 'ticket', 'id' => (int) $row['id']]))) ?>"><?= $escape($row['ticket_no'] ?: ('#' . $row['id'])) ?> · <?= $escape($row['subject']) ?></a><small class="meta"><?= $escape($typeLabels[(string) ($row['ticket_type'] ?? '')] ?? 'درخواست') ?> · <?= $escape(($row['service_group'] ?? '') === 'support' ? 'پشتیبانی' : 'فناوری اطلاعات') ?></small></td>
<td><?= $escape($row['requester_name']) ?></td><td><?= $escape($row['assignee_name'] ?: 'بدون مسئول') ?></td>
<td><span class="status"><?= $escape($statusLabels[(string) $row['status']] ?? $row['status']) ?></span></td>
<td><span class="priority"><?= $escape($priorityLabels[(string) $row['priority']] ?? $row['priority']) ?></span></td>
<td><?= $deadlineBadge($row['due_at'] ?? null, !empty($row['sla_paused_at'])) ?></td>
<?php if ($workspace === 'agent'): ?>
<td><?= $deadlineBadge($row['ola_response_due_at'] ?? null, !empty($row['sla_paused_at'])) ?></td>
<td><?= $deadlineBadge($row['ola_due_at'] ?? null, !empty($row['sla_paused_at'])) ?></td>
<?php endif; ?>
<td><?= $escape($row['updated_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<?php if ($pages > 1): ?><nav class="pagination" aria-label="صفحه‌بندی"><?php for ($i=max(1,$page-3);$i<=min($pages,$page+3);$i++): ?><?php if ($i === $page): ?><span class="current"><?= $i ?></span><?php else: ?><a href="request-center.php?<?= $escape($queryString) ?><?= $queryString !== "" ? "&amp;" : "" ?>p=<?= $i ?>"><?= $i ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; ?>
</section>

<script id="rc-workspace-filter-interactions">
(function(){
 var form=document.querySelector('form[action="request-center.php"][method="get"]');
 if(!form)return;
 var fields=['q','status','priority','service_group'];
 var labels={q:'عبارت جست‌وجو',status:'وضعیت',priority:'اولویت',service_group:'حوزه'};
 var values={};
 fields.forEach(function(name){
  var field=form.elements.namedItem(name);
  if(!field)return;
  var option=field.tagName==='SELECT'&&field.selectedOptions?field.selectedOptions[0]:null;
  var value=field.value;
  if(value)values[name]=name==='status'||name==='priority'||name==='service_group'?(option?option.textContent.trim():value):value;
 });
 var active=Object.keys(values);
 if(!active.length)return;
 var summary=document.createElement('div');
 summary.className='rc-filter-summary';
 summary.setAttribute('aria-label','فیلترهای فعال');
 summary.style.cssText='display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin:-5px 0 14px;color:#66758b;font-size:12px';
 var caption=document.createElement('span');caption.textContent='فیلترهای فعال:';summary.appendChild(caption);
 active.forEach(function(name){
  var chip=document.createElement('button');chip.type='button';chip.className='button secondary';
  chip.style.cssText='min-height:30px;padding:4px 9px;font-size:11px';
  chip.textContent=labels[name]+': '+values[name]+' ×';
  chip.setAttribute('aria-label','حذف فیلتر '+labels[name]);
  chip.addEventListener('click',function(){
   var field=form.elements.namedItem(name);if(!field)return;
   field.value='';
   form.submit();
  });
  summary.appendChild(chip);
 });
 var reset=document.createElement('a');reset.className='button secondary';reset.style.cssText='min-height:30px;padding:4px 9px;font-size:11px';
 reset.href='request-center.php?'+new URLSearchParams(Array.from(form.elements).filter(function(el){return el.name&&el.type==='hidden'&&el.value;}).map(function(el){return [el.name,el.value];})).toString();
 reset.textContent='حذف همه فیلترها';summary.appendChild(reset);
 form.insertAdjacentElement('afterend',summary);
})();
</script>
</div>
<?php render_footer(); ?>
