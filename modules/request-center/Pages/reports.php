<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/** Request Center reports workspace, executed in legacy front-controller scope. */

if ($page === 'reports') {
    require_once dirname(__DIR__) . '/lib/http-errors.php';
    $reportUser = require_login();
    if (!user_can($reportUser, 'reports.view')) {
        request_center_error_page(403, 'دسترسی مجاز نیست', 'دسترسی به گزارش‌ها فقط برای دارندهٔ مجوز «reports.view» فعال است.');
    }
    if (function_exists('request_center_is_org_unit_head') && request_center_is_org_unit_head($reportUser)) {
        request_center_error_page(403, 'دسترسی مجاز نیست', 'این بخش برای «مدیر واحد» فعال نیست؛ از «فضای مدیر واحد» استفاده کنید.');
    }
    $reportMissingColumns = db_missing_columns('tickets', ['subject', 'description', 'status', 'priority', 'requester_id', 'department_id', 'service_group', 'created_at', 'updated_at', 'assigned_to', 'ticket_type', 'assigned_at', 'first_response_at', 'resolved_at', 'closed_at', 'rejected_at', 'due_at', 'sla_minutes']);
    if ($reportMissingColumns) {
        http_response_code(503);
        render_header('گزارش‌ها', $reportUser);
        echo '<section class="card empty-state"><h2>ساختار گزارش‌ها کامل نیست.</h2><p>این نسخه به ستون‌های جدید درخواست نیاز دارد. فایل‌های Migration را به‌ترتیب روی همان دیتابیس اجرا کنید، سپس صفحه را تازه‌سازی کنید.</p><p><code>upgrade-1.2-itsm.sql</code> و در صورت نیاز <code>upgrade-1.8-routing.sql</code></p><p class="muted">ستون‌های ناقص: ' . e(implode(', ', $reportMissingColumns)) . '</p><a class="button" href="request-center.php">بازگشت به مرکز درخواست‌ها</a></section>';
        render_footer();
        exit;
    }
    [$where, $params] = report_query_filters($reportUser);
    $summaryQuery = db()->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(t.status IN ("new", "manager_review", "assigned", "in_progress", "waiting_user", "pending")), 0) AS active, COALESCE(SUM(t.status = "resolved"), 0) AS resolved, COALESCE(SUM(t.status = "closed"), 0) AS closed, COALESCE(SUM(t.status = "cancelled"), 0) AS cancelled, COALESCE(SUM(t.status = "rejected"), 0) AS rejected, COALESCE(SUM(t.status NOT IN ("resolved", "closed", "cancelled", "rejected") AND t.sla_paused_at IS NULL AND t.due_at IS NOT NULL AND t.due_at < NOW()), 0) AS overdue, AVG(CASE WHEN t.resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at) END) AS avg_resolution FROM tickets t LEFT JOIN users r ON r.id = t.requester_id ' . $where);
    $summaryQuery->execute($params);
    $summary = $summaryQuery->fetch() ?: ['total' => 0, 'active' => 0, 'resolved' => 0, 'closed' => 0, 'cancelled' => 0, 'rejected' => 0, 'overdue' => 0, 'avg_resolution' => 0];
    $reportPerPage = 100;
    $reportPage = max(1, (int) ($_GET['p'] ?? 1));
    $reportPages = max(1, (int) ceil(((int) $summary['total']) / $reportPerPage));
    $reportPage = min($reportPage, $reportPages);
    $reportOffset = ($reportPage - 1) * $reportPerPage;
     $rowsQuery = db()->prepare('SELECT t.*, d.name AS department_name, r.full_name AS requester_name, a.full_name AS assignee_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to ' . $where . ' ORDER BY t.created_at DESC LIMIT ' . $reportPerPage . ' OFFSET ' . $reportOffset);
    $rowsQuery->execute($params);
    $reportRows = $rowsQuery->fetchAll();
    ticket_number_cache_prime($reportRows); // ticket_no از همین SELECT آمده؛ جلوگیری از N+1
    $departments = is_global_ticket_role($reportUser['role']) ? db()->query('SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name')->fetchAll() : [];
    $queryString = http_build_query(array_filter($_GET, static fn ($value, $key): bool => $key !== 'page' && $key !== 'action' && $value !== '', ARRAY_FILTER_USE_BOTH));
    $reportReturnWorkspace = user_is_primary_admin($reportUser) ? 'admin'
        : (user_can($reportUser, 'supervisor.panel') ? 'supervisor'
            : ((user_can($reportUser, 'ticket.edit') || user_can($reportUser, 'queue.view')
                || user_can($reportUser, 'ticket.view_unit') || user_can($reportUser, 'ticket.view_all'))
                ? 'agent' : 'requester'));
    $reportReturnParams = ['workspace' => $reportReturnWorkspace, 'p' => $reportPage];
    foreach (['q' => 100, 'status' => 32, 'priority' => 16, 'service_group' => 16, 'from' => 24, 'to' => 24] as $returnKey => $maxLength) {
        $returnValue = trim((string) ($_GET[$returnKey] ?? ''));
        if ($returnValue !== '' && strlen($returnValue) <= $maxLength) {
            $reportReturnParams[$returnKey] = $returnValue;
        }
    }
    foreach (['department_id', 'assigned_to'] as $returnIdKey) {
        $returnId = (int) ($_GET[$returnIdKey] ?? 0);
        if ($returnId > 0) {
            $reportReturnParams[$returnIdKey] = $returnId;
        }
    }
    $reportDetailContext = http_build_query($reportReturnParams);
    render_header('گزارش‌ها', $reportUser);
     echo '<section class="page-heading"><div><span class="eyebrow">کنترل عملکرد پشتیبانی</span><h1>گزارش و پیگیری</h1><p>گزارش‌ها را فیلتر کنید و خروجی قابل استفاده در Excel بگیرید.</p></div><a class="button secondary" href="request-center.php?action=export_report' . ($queryString ? '&' . e($queryString) : '') . '">خروجی Excel</a></section><section class="card filter-card"><form method="get"><input type="hidden" name="page" value="reports"><div class="filter-grid"><label>از تاریخ جلالی<input type="text" name="from" placeholder="۱۴۰۵/۰۱/۰۱" value="' . e($_GET['from'] ?? '') . '"></label><label>تا تاریخ جلالی<input type="text" name="to" placeholder="۱۴۰۵/۰۱/۳۱" value="' . e($_GET['to'] ?? '') . '"></label><label>وضعیت<select name="status"><option value="">همه وضعیت‌ها</option>';
    // گزینه‌های گروهی هم اضافه شدند تا کارت‌های داشبورد (?status=active/resolved/all) در فرم دیده شوند
    foreach (['all' => 'همهٔ درخواست‌ها', 'active' => 'باز و در حال پیگیری', 'new' => 'جدید', 'manager_review' => null, 'assigned' => null, 'in_progress' => null, 'waiting_user' => null, 'pending' => null, 'resolved' => null, 'closed' => null, 'cancelled' => null, 'rejected' => null] as $status => $customLabel) {
        echo '<option value="' . $status . '" ' . ((string) ($_GET['status'] ?? '') === $status ? 'selected' : '') . '>' . e($customLabel ?? request_center_status_label($status)) . '</option>';
    }
    echo '</select></label>';
    if ($departments) {
        echo '<label>واحد<select name="department_id"><option value="">همه واحدها</option>';
        foreach ($departments as $department) {
            echo '<option value="' . (int) $department['id'] . '" ' . ((int) ($_GET['department_id'] ?? 0) === (int) $department['id'] ? 'selected' : '') . '>' . e($department['name']) . '</option>';
        }
        echo '</select></label>';
    }
    echo '<label>اولویت<select name="priority"><option value="">همه اولویت‌ها</option>';
     foreach (['normal','urgent','critical'] as $priority) {
        echo '<option value="' . $priority . '" ' . ((string) ($_GET['priority'] ?? '') === $priority ? 'selected' : '') . '>' . priority_label($priority) . '</option>';
    }
     echo '</select></label><label>حوزه خدمت<select name="service_group"><option value="">همه حوزه‌ها</option><option value="it" ' . ((string) ($_GET['service_group'] ?? '') === 'it' ? 'selected' : '') . '>IT</option><option value="support" ' . ((string) ($_GET['service_group'] ?? '') === 'support' ? 'selected' : '') . '>پشتیبانی</option></select></label><label>نوع درخواست<select name="ticket_type"><option value="">همه انواع</option>';
     foreach (['incident','request','problem','change'] as $ticketType) { echo '<option value="' . $ticketType . '" ' . ((string) ($_GET['ticket_type'] ?? '') === $ticketType ? 'selected' : '') . '>' . ticket_type_label($ticketType) . '</option>'; }
     echo '</select></label><label class="filter-search">جست‌وجو<input name="q" value="' . e($_GET['q'] ?? '') . '" placeholder="عنوان، شرح یا ثبت‌کننده"></label><button class="button" type="submit">اعمال فیلتر</button></div></form></section><section class="stat-grid report-stats"><div class="stat-card accent"><span class="stat-icon">▣</span><div><strong>' . (int) $summary['total'] . '</strong><small>کل درخواست‌ها</small></div></div><div class="stat-card"><span class="stat-icon blue">◈</span><div><strong>' . (int) $summary['active'] . '</strong><small>باز و در حال انجام</small></div></div><div class="stat-card"><span class="stat-icon green">✓</span><div><strong>' . ((int) $summary['resolved'] + (int) $summary['closed']) . '</strong><small>حل‌شده یا بسته</small></div></div><div class="stat-card"><span class="stat-icon gray">×</span><div><strong>' . (int) $summary['cancelled'] . '</strong><small>لغوشده</small></div></div><div class="stat-card"><span class="stat-icon gray">!</span><div><strong>' . (int) $summary['rejected'] . '</strong><small>ردشده</small></div></div><div class="stat-card"><span class="stat-icon gray">◷</span><div><strong>' . ($summary['avg_resolution'] ? round((float) $summary['avg_resolution'] / 60, 1) . ' ساعت' : '-') . '</strong><small>میانگین زمان حل</small></div></div><div class="stat-card"><span class="stat-icon" style="background:#fef3f2;color:#b42318">!</span><div><strong>' . (int) $summary['overdue'] . '</strong><small>عبور از SLA</small></div></div></section><section class="card ticket-table report-table"><div class="table-head"><span>درخواست</span><span>واحد</span><span>ثبت‌کننده</span><span>وضعیت</span><span>زمان ثبت</span></div>';
    foreach ($reportRows as $row) {
         echo '<a class="table-row" href="request-center.php?page=ticket&amp;id=' . (int) $row['id'] . '&amp;' . e($reportDetailContext) . '"><span class="ticket-title"><b>' . e(ticket_number((int) $row['id'])) . '</b><strong>' . e($row['subject']) . '</strong><small>' . e(ticket_type_label($row['ticket_type'])) . ' • ' . e($row['assignee_name'] ?: 'بدون کارشناس') . '</small></span><span>' . e($row['department_name'] ?: 'بدون واحد') . '</span><span>' . e($row['requester_name']) . '</span><span><em class="status ' . e($row['status']) . '">' . e(request_center_status_label($row['status'])) . '</em></span><span class="date-cell">' . persian_date($row['created_at']) . '</span></a>';
    }
    echo '</section>';
    if ($reportPages > 1) {
        $reportBaseArgs = array_filter($_GET, static fn ($value, $key): bool => $key !== 'p' && $key !== 'page' && $value !== '', ARRAY_FILTER_USE_BOTH);
        echo '<nav class="pagination" aria-label="صفحه‌بندی گزارش">';
        for ($reportNav = max(1, $reportPage - 3); $reportNav <= min($reportPages, $reportPage + 3); $reportNav++) {
            $reportArgs = array_merge(['page' => 'reports'], $reportBaseArgs, ['p' => $reportNav]);
            echo '<a class="' . ($reportNav === $reportPage ? 'active' : '') . '" href="request-center.php?' . e(http_build_query($reportArgs)) . '">' . number_format($reportNav) . '</a>';
        }
        echo '</nav>';
    }
    echo '<script src="modules/request-center/Assets/request-center.js" defer></script>';
    render_footer();
    exit;
}
