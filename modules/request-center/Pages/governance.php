<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/**
 * Request Center — «تغییر و مشکل» (ITSM Governance).
 * پیش‌تر صفحهٔ مستقل governance.php بود؛ با تصمیم مالک ۲۰۲۶-۱۱-۱۴ به ماژول منتقل شد.
 * ثبت/ویرایش رکوردها فقط از مسیر اکشن‌های «*_change/problem» با کد «governance.manage» انجام می‌شود
 * (modules/request-center/Actions/governance-actions.php).
 */

if ($page === 'governance') {
    require_once dirname(__DIR__) . '/lib/http-errors.php';
    require_once dirname(__DIR__) . '/lib/governance.php';

    $user = require_staff();
    // ۱.۳۷.۷ — هم‌راستا با منو و کاتالوگ: ورود به «تغییر و مشکل» گذشته از نقش کارمندی، کد دسترسی هم
    // می‌خواهد. پیش‌فرض همهٔ نقش‌های کارمندی این کد را دارند؛ فقط اگر ادمین اصلی آن را از نقشی
    // بگیرد اثر می‌کند.
    if (function_exists('user_can') && !user_can($user, 'governance.view')) {
        request_center_error_page(403, 'دسترسی مجاز نیست', 'دسترسی به بخش تغییر و مشکل برای نقش شما مجاز نیست.');
    }

    $governanceTables = ['change_records', 'change_approvals', 'change_ticket_links', 'problem_records', 'problem_ticket_links'];
    foreach ($governanceTables as $governanceTable) {
        if (!db_table_exists($governanceTable)) {
            render_header('مدیریت تغییر و مشکل', $user);
            echo '<section class="card empty-state"><h2>ماژول حاکمیت ITSM هنوز نصب نشده است.</h2><p>فایل <code>upgrade-1.7-governance.sql</code> را یک‌بار روی پایگاه‌داده اجرا کنید.</p><a class="button" href="index.php">بازگشت به داشبورد</a></section>';
            render_footer();
            exit;
        }
    }

    $governanceTicketColumns = db_missing_columns('tickets', ['subject', 'requester_id', 'department_id', 'ticket_type', 'service_group', 'created_at']);
    if ($governanceTicketColumns) {
        render_header('مدیریت تغییر و مشکل', $user);
        echo '<section class="card empty-state"><h2>ساختار درخواست برای این ماژول کامل نیست.</h2><p>ابتدا Migrationهای درخواست و مسیر‌دهی را اجرا کنید، سپس صفحه را تازه‌سازی کنید.</p><p><code>upgrade-1.2-itsm.sql</code> و <code>upgrade-1.8-routing.sql</code></p><p class="muted">ستون‌های ناقص: ' . e(implode(', ', $governanceTicketColumns)) . '</p><a class="button" href="index.php">بازگشت به داشبورد</a></section>';
        render_footer();
        exit;
    }


    $changeId = (int) ($_GET['change_id'] ?? 0);
    $problemId = (int) ($_GET['problem_id'] ?? 0);
    $selectedChange = $changeId > 0 ? gov_fetch_change($changeId) : null;
    $selectedProblem = $problemId > 0 ? gov_fetch_problem($problemId) : null;
    if (($selectedChange && !gov_can_view_record($selectedChange, $user)) || ($selectedProblem && !gov_can_view_record($selectedProblem, $user))) {
        http_response_code(403);
        exit('دسترسی به این رکورد مجاز نیست.');
    }

    [$changeWhere, $changeParams] = gov_scope($user, 'c');
    $changeQuery = db()->prepare('SELECT c.*, d.name AS department_name, o.full_name AS owner_name FROM change_records c LEFT JOIN departments d ON d.id = c.department_id LEFT JOIN users o ON o.id = c.owner_id ' . ($changeWhere ?: 'WHERE 1 = 1') . ' ORDER BY CASE WHEN c.status IN ("submitted", "cab_review") THEN 0 ELSE 1 END, c.updated_at DESC LIMIT 100');
    $changeQuery->execute($changeParams);
    $changes = $changeQuery->fetchAll();
    [$problemWhere, $problemParams] = gov_scope($user, 'p');
    $problemQuery = db()->prepare('SELECT p.*, d.name AS department_name, o.full_name AS owner_name FROM problem_records p LEFT JOIN departments d ON d.id = p.department_id LEFT JOIN users o ON o.id = p.owner_id ' . ($problemWhere ?: 'WHERE 1 = 1') . ' ORDER BY CASE WHEN p.status IN ("open", "investigation", "known_error") THEN 0 ELSE 1 END, p.updated_at DESC LIMIT 100');
    $problemQuery->execute($problemParams);
    $problems = $problemQuery->fetchAll();
    $preselectedTicket = (int) ($_GET['ticket_id'] ?? 0);

    render_header('مدیریت تغییر و مشکل', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">ITSM Governance</span><h1>مدیریت تغییر و مشکل</h1><p>درخواست‌های تغییر با تأیید CAB و مشکلات ریشه‌ای با Known Error و راه‌حل موقت ثبت و پیگیری می‌شوند.</p></div></section>';
    echo '<section class="settings-grid"><div class="card form-card"><h2>ثبت درخواست تغییر</h2><p class="muted">تغییر پس از ارسال برای تصمیم سوپروایزر/CAB آماده می‌شود.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="create_change"><div class="form-grid"><label class="full">عنوان تغییر<input name="title" required maxlength="255" placeholder="مثلاً ارتقای سرور مالی"></label><label>ریسک<select name="risk_level"><option value="low">کم</option><option value="medium" selected>متوسط</option><option value="high">بالا</option><option value="critical">بحرانی</option></select></label><label>درخواست مرجع<select name="ticket_id">' . gov_ticket_options($user, $preselectedTicket) . '</select></label><label>شروع برنامه<input type="datetime-local" name="planned_start"></label><label>پایان برنامه<input type="datetime-local" name="planned_end"></label><label class="full">شرح فنی<textarea name="description" required rows="4"></textarea></label><label class="full">دلیل و منفعت<textarea name="justification" rows="3"></textarea></label><label class="full">اثر و سامانه‌های متاثر<textarea name="impact" rows="3"></textarea></label></div><button class="button" type="submit">ساخت پیش‌نویس تغییر</button></form></div>';
    echo '<div class="card form-card"><h2>ثبت مشکل ریشه‌ای</h2><p class="muted">چند رخداد مرتبط را می‌توان بعداً به یک Problem متصل کرد.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="create_problem"><div class="form-grid"><label class="full">عنوان مشکل<input name="title" required maxlength="255" placeholder="مثلاً قطعی تکرارشونده چاپ در واحد مالی"></label><label>اولویت<select name="priority"><option value="normal">عادی</option><option value="urgent">فوری</option><option value="critical">حیاتی</option></select></label><label>درخواست مرجع<select name="ticket_id">' . gov_ticket_options($user, $preselectedTicket) . '</select></label><label class="full">شرح نشانه‌ها و دامنه اثر<textarea name="description" required rows="4"></textarea></label><label class="full">علت ریشه‌ای، در صورت مشخص‌بودن<textarea name="root_cause" rows="3"></textarea></label><label class="full">راه‌حل موقت / Workaround<textarea name="workaround" rows="3"></textarea></label><label class="check-label"><input type="checkbox" name="known_error" value="1"> خطای شناخته‌شده است</label></div><button class="button" type="submit">ساخت رکورد مشکل</button></form></div></section>';

    echo '<section class="organization-grid"><section class="card form-card"><h2>درخواست‌های تغییر</h2><div class="category-list">';
    foreach ($changes as $change) {
        echo '<div><span><a href="request-center.php?page=governance&change_id=' . (int) $change['id'] . '"><strong>' . e($change['change_number']) . ' • ' . e($change['title']) . '</strong></a><small>' . e($change['department_name'] ?: 'بدون معاونت') . ' • مالک: ' . e($change['owner_name'] ?: 'تعیین نشده') . '</small></span><span class="status ' . e($change['status']) . '">' . e(gov_change_status_label($change['status'])) . '</span></div>';
    }
    if (!$changes) {
        echo '<div class="empty-state"><h3>درخواستی ثبت نشده است.</h3></div>';
    }
    echo '</div></section><section class="card form-card"><h2>مشکلات و Known Error</h2><div class="category-list">';
    foreach ($problems as $problem) {
        echo '<div><span><a href="request-center.php?page=governance&problem_id=' . (int) $problem['id'] . '"><strong>' . e($problem['problem_number']) . ' • ' . e($problem['title']) . '</strong></a><small>' . e($problem['department_name'] ?: 'بدون معاونت') . ' • ' . e(priority_label($problem['priority'])) . '</small></span><span class="status ' . e($problem['status']) . '">' . e(gov_problem_status_label($problem['status'])) . '</span></div>';
    }
    if (!$problems) {
        echo '<div class="empty-state"><h3>مشکلی ثبت نشده است.</h3></div>';
    }
    echo '</div></section></section>';

    if ($selectedChange) {
        $linkQuery = db()->prepare('SELECT l.ticket_id, t.subject, t.ticket_type FROM change_ticket_links l JOIN tickets t ON t.id = l.ticket_id WHERE l.change_id = ? ORDER BY l.created_at DESC');
        $linkQuery->execute([$selectedChange['id']]);
        $linkedTickets = array_values(array_filter($linkQuery->fetchAll(), static function (array $linkedTicket) use ($user): bool {
            $ticket = gov_fetch_ticket((int) $linkedTicket['ticket_id']);
            return $ticket !== null && gov_can_view_ticket($ticket, $user);
        }));
        $approvalQuery = db()->prepare('SELECT a.*, u.full_name FROM change_approvals a LEFT JOIN users u ON u.id = a.approver_id WHERE a.change_id = ? ORDER BY a.created_at DESC');
        $approvalQuery->execute([$selectedChange['id']]);
        echo '<section class="card form-card governance-detail"><div class="page-heading"><div><span class="eyebrow">Change Record</span><h2>' . e($selectedChange['change_number']) . ' • ' . e($selectedChange['title']) . '</h2><p>' . e(gov_change_status_label($selectedChange['status'])) . ' • ریسک ' . e(gov_risk_label($selectedChange['risk_level'])) . '</p></div><a class="button secondary" href="request-center.php?page=governance">بستن جزئیات</a></div><div class="form-grid"><div><strong>شرح فنی</strong><p class="muted">' . nl2br(e($selectedChange['description'])) . '</p></div><div><strong>دلیل و منفعت</strong><p class="muted">' . nl2br(e($selectedChange['justification'] ?: 'ثبت نشده')) . '</p></div><div><strong>اثر و سامانه‌های متاثر</strong><p class="muted">' . nl2br(e($selectedChange['impact'] ?: 'ثبت نشده')) . '</p></div><div><strong>زمان برنامه</strong><p class="muted">' . e($selectedChange['planned_start'] ?: 'ثبت نشده') . ' تا ' . e($selectedChange['planned_end'] ?: 'ثبت نشده') . '</p></div></div><div class="actions">';
        if (in_array($selectedChange['status'], ['draft', 'rejected'], true)) {
            echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="submit_change"><input type="hidden" name="change_id" value="' . (int) $selectedChange['id'] . '"><button class="button" type="submit">ارسال برای CAB</button></form>';
        }
        if (in_array($user['role'], ['supervisor', 'primary_admin', 'support_manager'], true) && in_array($selectedChange['status'], ['submitted', 'cab_review'], true)) {
            echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="cab_decision"><input type="hidden" name="change_id" value="' . (int) $selectedChange['id'] . '"><input type="hidden" name="decision" value="approved"><input name="note" placeholder="یادداشت CAB اختیاری"><button class="button" type="submit">تأیید CAB</button></form><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="cab_decision"><input type="hidden" name="change_id" value="' . (int) $selectedChange['id'] . '"><input type="hidden" name="decision" value="rejected"><input name="note" placeholder="دلیل رد"><button class="button danger-button" type="submit">رد CAB</button></form>';
        }
        $transitions = ['approved' => 'زمان‌بندی', 'scheduled' => 'ثبت اجرای موفق', 'implemented' => 'بستن تغییر', 'rolled_back' => 'بستن پس از بازگشت'];
        foreach ($transitions as $from => $label) {
            if ($selectedChange['status'] === $from) {
                $to = $from === 'approved' ? 'scheduled' : ($from === 'scheduled' ? 'implemented' : 'closed');
                echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="change_transition"><input type="hidden" name="change_id" value="' . (int) $selectedChange['id'] . '"><input type="hidden" name="transition" value="' . $to . '"><input name="implementation_note" placeholder="یادداشت اجرا اختیاری"><button class="button secondary" type="submit">' . $label . '</button></form>';
            }
        }
        if (in_array($selectedChange['status'], ['scheduled', 'implemented'], true)) {
            echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="change_transition"><input type="hidden" name="change_id" value="' . (int) $selectedChange['id'] . '"><input type="hidden" name="transition" value="rolled_back"><input name="implementation_note" placeholder="دلیل بازگشت"><button class="button danger-button" type="submit">بازگشت تغییر</button></form>';
        }
        if (in_array($selectedChange['status'], ['draft', 'rejected'], true)) {
            echo '</div><h3>اصلاح پیش‌نویس تغییر</h3><form method="post" class="governance-edit">' . csrf_field() . '<input type="hidden" name="action" value="update_change"><input type="hidden" name="change_id" value="' . (int) $selectedChange['id'] . '"><div class="form-grid"><label class="full">عنوان تغییر<input name="title" required maxlength="255" value="' . e($selectedChange['title']) . '"></label><label>ریسک<select name="risk_level">';
            foreach (['low', 'medium', 'high', 'critical'] as $risk) {
                echo '<option value="' . $risk . '" ' . ($selectedChange['risk_level'] === $risk ? 'selected' : '') . '>' . e(gov_risk_label($risk)) . '</option>';
            }
            echo '</select></label><label>شروع برنامه<input type="datetime-local" name="planned_start" value="' . e(gov_datetime_input($selectedChange['planned_start'])) . '"></label><label>پایان برنامه<input type="datetime-local" name="planned_end" value="' . e(gov_datetime_input($selectedChange['planned_end'])) . '"></label><label class="full">شرح فنی<textarea name="description" required rows="4">' . e($selectedChange['description']) . '</textarea></label><label class="full">دلیل و منفعت<textarea name="justification" rows="3">' . e($selectedChange['justification'] ?: '') . '</textarea></label><label class="full">اثر و سامانه‌های متاثر<textarea name="impact" rows="3">' . e($selectedChange['impact'] ?: '') . '</textarea></label></div><button class="button secondary" type="submit">ذخیره اصلاحات</button></form>';
        }
        if (!in_array($selectedChange['status'], ['draft', 'rejected'], true)) {
            echo '</div>';
        }
        echo '<h3>درخواست‌های مرتبط</h3><div class="category-list">';
        foreach ($linkedTickets as $linkedTicket) {
            echo '<div><span><strong>' . e(ticket_number((int) $linkedTicket['ticket_id'])) . ' • ' . e($linkedTicket['subject']) . '</strong><small>' . e(ticket_type_label($linkedTicket['ticket_type'])) . '</small></span></div>';
        }
        echo '</div><form method="post" class="inline-form">' . csrf_field() . '<input type="hidden" name="action" value="link_governance_ticket"><input type="hidden" name="kind" value="change"><input type="hidden" name="record_id" value="' . (int) $selectedChange['id'] . '"><label>اتصال درخواست<select name="ticket_id" required>' . gov_ticket_options($user) . '</select></label><button class="button secondary" type="submit">اتصال</button></form><h3>تاریخچه CAB و اجرا</h3><div class="event-list">';
        foreach ($approvalQuery->fetchAll() as $approval) {
            echo '<div class="event-item"><span class="event-dot"></span><div><strong>' . e(gov_change_status_label($approval['decision'])) . '</strong><small>' . e($approval['full_name'] ?: 'سامانه') . ' • ' . e(jalali_date($approval['created_at'])) . '</small></div><em>' . e($approval['note'] ?: '') . '</em></div>';
        }
        echo '</div></section>';
    }

    if ($selectedProblem) {
        $linkQuery = db()->prepare('SELECT l.ticket_id, t.subject, t.ticket_type FROM problem_ticket_links l JOIN tickets t ON t.id = l.ticket_id WHERE l.problem_id = ? ORDER BY l.created_at DESC');
        $linkQuery->execute([$selectedProblem['id']]);
        $linkedTickets = array_values(array_filter($linkQuery->fetchAll(), static function (array $linkedTicket) use ($user): bool {
            $ticket = gov_fetch_ticket((int) $linkedTicket['ticket_id']);
            return $ticket !== null && gov_can_view_ticket($ticket, $user);
        }));
        echo '<section class="card form-card governance-detail"><div class="page-heading"><div><span class="eyebrow">Problem Record</span><h2>' . e($selectedProblem['problem_number']) . ' • ' . e($selectedProblem['title']) . '</h2><p>' . e(gov_problem_status_label($selectedProblem['status'])) . ' • ' . e(priority_label($selectedProblem['priority'])) . '</p></div><a class="button secondary" href="request-center.php?page=governance">بستن جزئیات</a></div><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="update_problem"><input type="hidden" name="problem_id" value="' . (int) $selectedProblem['id'] . '"><div class="form-grid"><label>وضعیت<select name="status">';
        foreach (['open', 'investigation', 'known_error', 'resolved', 'closed'] as $status) {
            echo '<option value="' . $status . '" ' . ($selectedProblem['status'] === $status ? 'selected' : '') . '>' . e(gov_problem_status_label($status)) . '</option>';
        }
        echo '</select></label><label>اولویت<select name="priority">';
        foreach (['normal', 'urgent', 'critical'] as $priority) {
            echo '<option value="' . $priority . '" ' . ($selectedProblem['priority'] === $priority ? 'selected' : '') . '>' . e(priority_label($priority)) . '</option>';
        }
        echo '</select></label><label class="full">علت ریشه‌ای<textarea name="root_cause" rows="4">' . e($selectedProblem['root_cause'] ?: '') . '</textarea></label><label class="full">راه‌حل موقت / Workaround<textarea name="workaround" rows="4">' . e($selectedProblem['workaround'] ?: '') . '</textarea></label><label class="check-label"><input type="checkbox" name="known_error" value="1" ' . ((int) $selectedProblem['known_error'] === 1 ? 'checked' : '') . '> خطای شناخته‌شده</label></div><button class="button" type="submit">ذخیره تحلیل مشکل</button></form><h3>درخواست‌های مرتبط</h3><div class="category-list">';
        foreach ($linkQuery->fetchAll() as $linkedTicket) {
            echo '<div><span><strong>' . e(ticket_number((int) $linkedTicket['ticket_id'])) . ' • ' . e($linkedTicket['subject']) . '</strong><small>' . e(ticket_type_label($linkedTicket['ticket_type'])) . '</small></span></div>';
        }
        echo '</div><form method="post" class="inline-form">' . csrf_field() . '<input type="hidden" name="action" value="link_governance_ticket"><input type="hidden" name="kind" value="problem"><input type="hidden" name="record_id" value="' . (int) $selectedProblem['id'] . '"><label>اتصال درخواست<select name="ticket_id" required>' . gov_ticket_options($user) . '</select></label><button class="button secondary" type="submit">اتصال</button></form></section>';
    }

    render_footer();
    exit;
}
