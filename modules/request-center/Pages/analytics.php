<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/** Request Center analytics workspace, running in legacy front-controller scope. */

if ($page === 'analytics') {
    require_once dirname(__DIR__) . '/lib/http-errors.php';
    $analyticsUser = require_login();
    if (!user_can($analyticsUser, 'analytics.view')) {
        request_center_error_page(403, 'دسترسی مجاز نیست', 'دسترسی به تحلیل عملکرد فقط برای دارندهٔ مجوز «analytics.view» فعال است.');
    }
    [$analyticsScope, $analyticsParams] = ticket_scope($analyticsUser);
    $statusQuery = db()->prepare('SELECT t.status, COUNT(*) AS total FROM tickets t ' . $analyticsScope . ' GROUP BY t.status ORDER BY total DESC');
    $statusQuery->execute($analyticsParams);
    $statusRows = $statusQuery->fetchAll();
    $priorityQuery = db()->prepare('SELECT t.priority, COUNT(*) AS total FROM tickets t ' . $analyticsScope . ' GROUP BY t.priority ORDER BY total DESC');
    $priorityQuery->execute($analyticsParams);
    $priorityRows = $priorityQuery->fetchAll();
    $departmentQuery = db()->prepare('SELECT COALESCE(d.name, "بدون معاونت") AS label, COUNT(*) AS total FROM tickets t LEFT JOIN departments d ON d.id = t.department_id ' . $analyticsScope . ' GROUP BY t.department_id, d.name ORDER BY total DESC LIMIT 10');
    $departmentQuery->execute($analyticsParams);
    $departmentRows = $departmentQuery->fetchAll();
    $ratingConditions = ['(u.is_it_agent = 1 OR u.role = "agent")', 'u.is_active = 1'];
    $ratingParams = [];
    if (!is_global_ticket_role($analyticsUser['role'])) {
        $ratingConditions[] = 'hu.code = ?';
        $ratingParams[] = ($analyticsUser['role'] ?? '') === 'support_manager' ? 'support' : user_service_group($analyticsUser);
        if (is_department_scoped_role($analyticsUser['role']) && (int) ($analyticsUser['department_id'] ?? 0) > 0) {
            $ratingConditions[] = 'rt.department_id = ?';
            $ratingParams[] = (int) $analyticsUser['department_id'];
        }
    }
    $agentQuery = db()->prepare('SELECT u.full_name AS label, COUNT(tr.id) AS ratings, COALESCE(AVG(tr.score), 0) AS average FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id LEFT JOIN ticket_ratings tr ON tr.agent_id = u.id LEFT JOIN tickets rt ON rt.id = tr.ticket_id WHERE ' . implode(' AND ', $ratingConditions) . ' GROUP BY u.id ORDER BY average DESC, ratings DESC LIMIT 10');
    $agentQuery->execute($ratingParams);
    $agentRows = $agentQuery->fetchAll();
    $chart = static function (string $title, array $rows, callable $label, callable $value, string $suffix = ''): string {
        $max = 1;
        foreach ($rows as $row) { $max = max($max, (float) $value($row)); }
        $html = '<section class="card chart-card"><div class="chart-title"><h2>' . e($title) . '</h2><span>نمای تحلیلی</span></div>';
        if (!$rows) { return $html . '<p class="muted">داده‌ای برای نمایش وجود ندارد.</p></section>'; }
        foreach ($rows as $row) { $amount = (float) $value($row); $width = max(3, (int) round(($amount / $max) * 100)); $html .= '<div class="chart-row"><div class="chart-label"><span>' . e((string) $label($row)) . '</span><b>' . e(rtrim(rtrim(number_format($amount, 1, '.', ''), '0'), '.') . $suffix) . '</b></div><div class="chart-track"><i style="width:' . $width . '%"></i></div></div>'; }
        return $html . '</section>';
    };
    render_header('تحلیل و چارت‌ها', $analyticsUser);
     echo '<section class="page-heading"><div><span class="eyebrow">نمای تحلیلی مدیریتی</span><h1>تحلیل عملکرد سامانه</h1><p>توزیع وضعیت، اولویت، معاونت و رضایت از کارشناسان هر واحد.</p></div><a class="button secondary" href="request-center.php?page=reports">گزارش فیلترپذیر</a></section><section class="charts-grid">';
    echo $chart('توزیع وضعیت درخواست‌ها', $statusRows, static fn (array $row): string => request_center_status_label((string) $row['status']), static fn (array $row): float => (float) $row['total']);
    echo $chart('توزیع اولویت‌ها', $priorityRows, static fn (array $row): string => priority_label((string) $row['priority']), static fn (array $row): float => (float) $row['total']);
    echo $chart('درخواست بر اساس معاونت', $departmentRows, static fn (array $row): string => (string) $row['label'], static fn (array $row): float => (float) $row['total']);
    echo $chart('میانگین رضایت کارشناسان', $agentRows, static fn (array $row): string => (string) $row['label'] . ' (' . (int) $row['ratings'] . ' نظر)', static fn (array $row): float => (float) $row['average'], ' از ۵');
    echo '</section><section class="card analytics-note"><strong>راهنمای تحلیل</strong><span>امتیاز رضایت فقط پس از حل درخواست و توسط ثبت‌کننده ثبت می‌شود. گزارش‌های این صفحه بر اساس سطح دسترسی شما (حوزه و معاونت) فیلتر می‌شوند.</span></section>';
    echo '<script src="modules/request-center/Assets/request-center.js" defer></script>';
    render_footer();
    exit;
}
