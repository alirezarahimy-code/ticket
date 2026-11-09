<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/** Primary-admin workspace for module configuration, scoped audit and operations. */
if (!user_is_primary_admin($user)) {
    http_response_code(403);
    exit('دسترسی به مدیریت مرکز درخواست‌ها مجاز نیست.');
}

$summary = $repo->workspaceSummary($user);
$adminRecentFilters = [
    'q' => trim((string) ($_GET['recent_q'] ?? $_GET['q'] ?? '')),
    'status' => valid_choice((string) ($_GET['recent_status'] ?? $_GET['status'] ?? ''), ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved', 'closed', 'cancelled', 'rejected'], ''),
    'priority' => valid_choice((string) ($_GET['recent_priority'] ?? $_GET['priority'] ?? ''), ['normal', 'urgent', 'critical'], ''),
    'service_group' => valid_choice((string) ($_GET['recent_service_group'] ?? $_GET['service_group'] ?? ''), ['it', 'support'], ''),
];
$adminRecentFilters['q'] = function_exists('mb_substr')
    ? mb_substr($adminRecentFilters['q'], 0, 100, 'UTF-8')
    : substr($adminRecentFilters['q'], 0, 100);
$recent = $repo->listVisible($user, 1, 12, array_filter($adminRecentFilters, static fn ($value): bool => $value !== ''));

// The primary-admin workspace exposes a scoped operational audit trail.
// Never render event details here: they may contain internal-note or private payloads.
[$adminAuditWhere, $adminAuditParams] = ticket_scope($user);
$adminAuditQuery = db()->prepare(
    'SELECT e.id, e.event_type, e.from_status, e.to_status, e.created_at, '
    . 'u.full_name AS actor_name, t.id AS ticket_id, t.ticket_no, t.subject '
    . 'FROM ticket_events e JOIN tickets t ON t.id = e.ticket_id '
    . 'LEFT JOIN users u ON u.id = e.user_id ' . $adminAuditWhere
    . ' ORDER BY e.created_at DESC, e.id DESC LIMIT 20'
);
$adminAuditQuery->execute($adminAuditParams);
$adminAuditRows = $adminAuditQuery->fetchAll();
// Configuration changes are stored in the central audit stream, not ticket_events.
$adminConfigAuditQuery = db()->prepare(
    'SELECT a.id, a.action_code, a.created_at, a.result, u.full_name AS actor_name '
    . 'FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id '
    . "WHERE a.action_code IN ('request_center_work_hours_updated', 'ola_policy_created', 'ola_policy_updated') "
    . 'ORDER BY a.created_at DESC, a.id DESC LIMIT 20'
);
$adminConfigAuditQuery->execute();
$adminConfigAuditRows = $adminConfigAuditQuery->fetchAll(PDO::FETCH_ASSOC);
$businessHours = function_exists('sla_business_hours')
    ? sla_business_hours()
    : ['start' => 8, 'end' => 16, 'weekend' => [4, 5]];
$olaPolicies = [];
$olaPolicyAvailable = false;
$adminDepartments = db()->query('SELECT id, name FROM departments ORDER BY name, id')->fetchAll(PDO::FETCH_ASSOC);
try {
    $olaPolicyAvailable = (bool) db()->query("SHOW TABLES LIKE 'ola_policies'")->fetchColumn();
    if ($olaPolicyAvailable) {
        $olaPolicies = db()->query(
            'SELECT p.id, p.name, p.department_id, p.priority, p.response_minutes, '
            . 'p.resolution_minutes, p.escalation_1_minutes, p.escalation_2_minutes, p.is_active, '
            . 'd.name AS department_name FROM ola_policies p '
            . 'LEFT JOIN departments d ON d.id = p.department_id '
            . 'ORDER BY p.department_id IS NULL DESC, p.department_id, p.priority, p.id'
        )->fetchAll();
    }
} catch (Throwable) {
    // The OLA policy table is optional on pre-migration installations.
    $olaPolicyAvailable = false;
    $olaPolicies = [];
}
$adminAuditLabels = [
    'created' => 'ثبت درخواست', 'assigned' => 'ارجاع', 'assignment_changed' => 'تغییر مسئول',
    'status_changed' => 'تغییر وضعیت', 'reply' => 'پاسخ', 'internal_note' => 'یادداشت داخلی',
    'claimed' => 'دریافت توسط کارشناس', 'rated' => 'ثبت رضایت',
    'supervisor_approved' => 'تأیید سوپروایزر', 'supervisor_rework' => 'بازگشت برای اصلاح',
    'supervisor_reopened' => 'بازگشایی سوپروایزر', 'ticket_rejected' => 'رد درخواست',
    'cancellation_requested' => 'درخواست لغو', 'cancellation_approved' => 'تأیید لغو',
    'cancellation_rejected' => 'رد درخواست لغو', 'auto_closed' => 'بستن خودکار',
    'bulk_status_changed' => 'تغییر گروهی وضعیت', 'priority_changed' => 'تغییر اولویت',
    'ola_policy_created' => 'ایجاد سیاست OLA', 'ola_policy_updated' => 'ویرایش سیاست OLA',
    'request_center_work_hours_updated' => 'تغییر تقویم کاری SLA/OLA',
];
$escapeAdmin = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$adminStatusLabels = [
    'new' => 'جدید', 'manager_review' => 'در انتظار ارجاع', 'assigned' => 'ارجاع‌شده',
    'in_progress' => 'در حال انجام', 'waiting_user' => 'در انتظار پاسخ کاربر',
    'pending' => 'در انتظار پاسخ کاربر', 'resolved' => 'حل‌شده', 'closed' => 'بسته‌شده',
    'cancelled' => 'لغوشده', 'rejected' => 'ردشده',
];
// فقط مقصدهایی را نشان بده که همین حساب مجوز ورود به آن‌ها را دارد؛
 // وجود نقش primary_admin به‌تنهایی جایگزین مجوزهای مستقل صف/نظارت/گزارش نیست.
$adminLinks = [
    ['title' => 'کاتالوگ خدمات و فرم‌ها', 'description' => 'خدمات، دسته‌بندی‌ها و فیلدهای تکمیلی فرم ثبت درخواست', 'url' => 'request-center.php?page=services'],
];
if (user_can($user, 'reports.view')) {
    $adminLinks[] = ['title' => 'گزارش‌های SLA/OLA', 'description' => 'گزارش درخواست‌ها، موعدها و موارد خارج از تعهد در محدودهٔ مجاز', 'url' => 'request-center.php?page=reports'];
}
if (user_can($user, 'analytics.view')) {
    $adminLinks[] = ['title' => 'تحلیل عملکرد', 'description' => 'توزیع وضعیت، اولویت، واحدها و رضایت کارشناسان', 'url' => 'request-center.php?page=analytics'];
}
$adminLinks[] = ['title' => 'تقویم موعد کارشناسان', 'description' => 'نمای ماهانهٔ موعد SLA، پاسخ OLA و حل OLA در محدودهٔ دسترسی مدیر اصلی', 'url' => 'request-center.php?workspace=admin&calendar=1'];
if (user_can($user, 'queue.view')) {
    $adminLinks[] = ['title' => 'صف عملیاتی همه درخواست‌ها', 'description' => 'ورود به صف مجاز برای ارجاع، پیگیری و عملیات گروهی', 'url' => 'request-center.php?page=queue'];
    foreach ([
        'unassigned' => 'درخواست‌های بدون مسئول',
        'overdue' => 'درخواست‌های خارج از SLA/OLA',
        'waiting_user' => 'درخواست‌های منتظر پاسخ کاربر',
        'cancellation_requested' => 'درخواست‌های منتظر تصمیم لغو',
        'resolved' => 'درخواست‌های حل‌شده',
    ] as $queue => $label) {
        $adminLinks[] = [
            'title' => $label,
            'description' => 'بازکردن صف عملیاتی با حفظ کنترل دامنه و مجوز سمت سرور',
            'url' => 'request-center.php?page=queue&queue=' . $queue,
        ];
    }
}
if (user_can($user, 'supervisor.panel')) {
    $adminLinks[] = ['title' => 'پنل نظارت', 'description' => 'بررسی درخواست‌های حل‌شده و تصمیم‌های مدیریتی با مجوز سوپروایزر', 'url' => 'request-center.php?page=supervisor'];
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: private, no-store');
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>مدیریت مرکز درخواست‌ها</title>
<link rel="stylesheet" href="modules/request-center/Assets/request-center.css">
<style>
.rc-admin-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:18px 0}
.rc-admin-stat{background:#fff;border:1px solid #e4eaf1;border-radius:13px;padding:16px;min-width:0}
.rc-admin-stat strong{display:block;font-size:27px;line-height:1.4;color:#172b4d}
.rc-admin-stat span{color:#66758b;font-size:12px}
.rc-admin-layout{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(280px,.8fr);gap:16px}
.rc-admin-links{display:grid;gap:10px}
.rc-admin-link{display:block;background:#fff;border:1px solid #e4eaf1;border-radius:12px;padding:14px 16px;color:#172b4d}
.rc-admin-link b{display:block;margin-bottom:3px}.rc-admin-link small{color:#66758b}
.rc-admin-table{width:100%;border-collapse:collapse}.rc-admin-table th,.rc-admin-table td{padding:11px 12px;text-align:right;border-bottom:1px solid #e4eaf1}
.rc-admin-table th{font-size:12px;color:#66758b;background:#f8fafd}
@media(max-width:850px){.rc-admin-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.rc-admin-layout{grid-template-columns:1fr}}
@media(max-width:480px){.rc-admin-grid{grid-template-columns:1fr 1fr}.rc-admin-stat strong{font-size:22px}}
</style>
</head>
<body>
<header><div class="bar"><div><div class="brand">مرکز درخواست‌ها</div><small><?= $escapeAdmin($user['full_name'] ?? $user['username'] ?? '') ?> · مدیریت اصلی ماژول</small></div><a class="button secondary" href="request-center.php">خانه مرکز درخواست‌ها</a></div>
<?php if (count($workspaceOptions) > 1): ?><nav class="workspace-nav" aria-label="فضاهای کاری"><?php foreach ($workspaceOptions as $key => $label): ?><a class="<?= $key === 'admin' ? 'active' : '' ?>" href="request-center.php?workspace=<?= $escapeAdmin($key) ?>"><?= $escapeAdmin($label) ?></a><?php endforeach; ?></nav><?php endif; ?>
</header>
<main>
<section class="heading"><div><h1>مدیریت مرکز درخواست‌ها</h1><p>نمای عملیاتی و میان‌برهای پیکربندی ماژول؛ دسترسی داده‌ها همچنان تابع سیاست دامنهٔ هسته است.</p></div><a class="button" href="request-center.php?page=new-ticket">ثبت درخواست</a></section>
<section class="rc-admin-grid" aria-label="شاخص‌های کلیدی">
<div class="rc-admin-stat"><strong><?= number_format($summary['total']) ?></strong><span>کل درخواست‌های قابل مشاهده</span></div>
<div class="rc-admin-stat"><strong><?= number_format($summary['active']) ?></strong><span>درخواست‌های در گردش</span></div>
<div class="rc-admin-stat"><strong><?= number_format($summary['overdue']) ?></strong><span>خارج از SLA/OLA</span></div>
<div class="rc-admin-stat"><strong><?= number_format($summary['unassigned']) ?></strong><span>بدون مسئول</span></div>
<div class="rc-admin-stat"><strong><?= number_format($summary['awaiting_assignment']) ?></strong><span>در انتظار ارجاع</span></div>
<div class="rc-admin-stat"><strong><?= number_format($summary['awaiting_supervisor']) ?></strong><span>در انتظار بررسی سوپروایزر</span></div>
<div class="rc-admin-stat"><strong><?= number_format($summary['closed']) ?></strong><span>بسته‌شده</span></div>
<div class="rc-admin-stat"><strong><?= number_format($summary['rejected']) ?></strong><span>ردشده</span></div>
</section>
<div class="rc-admin-layout">
<section class="card" style="padding:16px">
<h2>آخرین درخواست‌های به‌روزشده</h2>
<form method="get" action="request-center.php" style="display:flex;gap:9px;flex-wrap:wrap;align-items:end;margin:12px 0 16px">
<input type="hidden" name="workspace" value="admin">
<label>جست‌وجو<input type="search" name="recent_q" maxlength="100" value="<?= $escapeAdmin($adminRecentFilters['q']) ?>" placeholder="شماره یا عنوان"></label>
<label>وضعیت<select name="recent_status"><option value="">همه وضعیت‌ها</option><?php foreach ($adminStatusLabels as $key => $label): ?><option value="<?= $escapeAdmin($key) ?>" <?= $adminRecentFilters['status'] === $key ? 'selected' : '' ?>><?= $escapeAdmin($label) ?></option><?php endforeach; ?></select></label>
<label>اولویت<select name="recent_priority"><option value="">همه اولویت‌ها</option><?php foreach (['normal' => 'عادی', 'urgent' => 'فوری', 'critical' => 'بحرانی'] as $key => $label): ?><option value="<?= $key ?>" <?= $adminRecentFilters['priority'] === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></label>
<label>حوزه خدمت<select name="recent_service_group"><option value="">همه حوزه‌ها</option><option value="it" <?= $adminRecentFilters['service_group'] === 'it' ? 'selected' : '' ?>>فناوری اطلاعات</option><option value="support" <?= $adminRecentFilters['service_group'] === 'support' ? 'selected' : '' ?>>پشتیبانی</option></select></label>
<button class="button" type="submit">جست‌وجو</button><a class="button secondary" href="request-center.php?workspace=admin">پاک‌کردن</a>
</form>
<?php if (!$recent['rows']): ?><p class="muted">درخواستی در محدودهٔ دسترسی وجود ندارد.</p><?php else: ?>
<div style="overflow-x:auto"><table class="rc-admin-table"><thead><tr><th>درخواست</th><th>مسئول</th><th>وضعیت</th><th>موعد SLA</th></tr></thead><tbody>
<?php foreach ($recent['rows'] as $row): ?>
<tr><td><a href="request-center.php?<?= $escapeAdmin(http_build_query(array_merge(['page' => 'ticket', 'id' => (int) $row['id'], 'workspace' => 'admin'], $adminRecentFilters))) ?>"><?= $escapeAdmin($row['ticket_no'] ?: ('#' . $row['id'])) ?> · <?= $escapeAdmin($row['subject']) ?></a></td><td><?= $escapeAdmin($row['assignee_name'] ?: 'بدون مسئول') ?></td><td><?= $escapeAdmin($adminStatusLabels[(string) $row['status']] ?? $row['status']) ?></td><td><?= $escapeAdmin($row['due_at'] ?: '—') ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</section>
<aside class="rc-admin-links" aria-label="مدیریت و گزارش‌ها">
<?php foreach ($adminLinks as $link): ?><a class="rc-admin-link" href="<?= $escapeAdmin($link['url']) ?>"><b><?= $escapeAdmin($link['title']) ?></b><small><?= $escapeAdmin($link['description']) ?></small></a><?php endforeach; ?>
</aside>
</div>
<section class="card" id="sla-work-hours" style="padding:16px;margin-top:18px">
<h2>تقویم کاری محاسبه SLA/OLA</h2>
<p class="muted">این ساعت‌ها و روزهای تعطیل مبنای محاسبهٔ مهلت‌های کاری هستند. تغییر آن‌ها موعدهای ذخیره‌شدهٔ درخواست‌های موجود را بازنویسی نمی‌کند.</p>
<form method="post" class="rc-admin-link" style="display:flex;gap:14px;flex-wrap:wrap;align-items:end">
<?= csrf_field() ?><input type="hidden" name="action" value="save_rc_work_hours">
<label>ساعت شروع<input type="number" min="0" max="23" name="work_start_hour" required value="<?= (int) $businessHours['start'] ?>"></label>
<label>ساعت پایان<input type="number" min="1" max="24" name="work_end_hour" required value="<?= (int) $businessHours['end'] ?>"></label>
<fieldset style="border:1px solid #e4eaf1;border-radius:9px;padding:8px 12px"><legend>روزهای تعطیل هفتگی</legend>
<?php foreach ([1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه', 6 => 'شنبه', 7 => 'یکشنبه'] as $day => $label): ?>
<label style="display:inline-flex;gap:5px;align-items:center;margin:4px 8px"><input type="checkbox" name="weekend_days[]" value="<?= $day ?>" <?= in_array($day, $businessHours['weekend'], true) ? 'checked' : '' ?>><?= $label ?></label>
<?php endforeach; ?>
</fieldset>
<button class="button" type="submit">ذخیره تقویم کاری</button>
</form>
</section>
<section class="card" id="ola-policies" style="padding:16px;margin-top:18px">
<h2>سیاست‌های OLA</h2>
<p class="muted">این تنظیمات مهلت پاسخ، حل و پله‌های ارجاع را برای درخواست‌های جدید و محاسبه‌های مجدد بعدی تعیین می‌کند؛ تغییر سیاست، موعد ثبت‌شدهٔ درخواست‌های موجود را بی‌صدا بازنویسی نمی‌کند.</p>
<?php if ($olaPolicyAvailable): ?>
<form method="post" class="rc-admin-link" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;align-items:end;margin-bottom:14px">
<?= csrf_field() ?><input type="hidden" name="action" value="create_ola_policy">
<label>نام سیاست جدید<input name="policy_name" required maxlength="190" placeholder="نام قابل تشخیص سیاست"></label>
<label>حوزه سازمانی<select name="department_id"><option value="0">سراسری</option><?php foreach ($adminDepartments as $department): ?><option value="<?= (int) $department['id'] ?>"><?= $escapeAdmin($department['name']) ?></option><?php endforeach; ?></select></label>
<label>اولویت<select name="priority" required><option value="">انتخاب اولویت</option><?php foreach (['critical' => 'بحرانی', 'urgent' => 'فوری', 'normal' => 'عادی'] as $key => $label): ?><option value="<?= $key ?>"><?= $label ?></option><?php endforeach; ?></select></label>
<label>مهلت پاسخ (دقیقه)<input type="number" min="1" max="525600" name="response_minutes" required></label>
<label>مهلت حل (دقیقه)<input type="number" min="1" max="525600" name="resolution_minutes" required></label>
<label>ارجاع اول (دقیقه)<input type="number" min="1" max="525600" name="escalation_1_minutes" required></label>
<label>ارجاع دوم (دقیقه)<input type="number" min="1" max="525600" name="escalation_2_minutes" required></label>
<label class="check-label"><input type="checkbox" name="is_active" value="1"> فعال‌سازی پس از ایجاد</label>
<button class="button" type="submit">ایجاد سیاست OLA</button>
<small class="muted">سیاست جدید به‌صورت پیش‌فرض غیرفعال ساخته می‌شود. فعال‌سازی فقط در صورت نبود سیاست فعال هم‌حوزه و هم‌اولویت مجاز است.</small>
</form>
<?php endif; ?>
<?php if (!$olaPolicyAvailable): ?><p class="muted">جدول سیاست OLA در این نصب موجود نیست؛ این بخش بدون تغییر Schema غیرفعال است.</p>
<?php elseif (!$olaPolicies): ?><p class="muted">هنوز سیاستی ثبت نشده است؛ می‌توانید از فرم بالا سیاست موردنیاز سازمان را تعریف کنید.</p>
<?php else: ?>
<div class="rc-admin-links">
<?php foreach ($olaPolicies as $policy): ?>
<form method="post" class="rc-admin-link" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;align-items:end">
<?= csrf_field() ?><input type="hidden" name="action" value="update_ola_policy"><input type="hidden" name="policy_id" value="<?= (int) $policy['id'] ?>">
<label>نام سیاست<input name="policy_name" required maxlength="190" value="<?= $escapeAdmin($policy['name']) ?>"></label>
<label>حوزه سازمانی<select name="department_id"><option value="0">سراسری</option><?php foreach ($adminDepartments as $department): ?><option value="<?= (int) $department['id'] ?>" <?= (int) ($policy['department_id'] ?? 0) === (int) $department['id'] ? 'selected' : '' ?>><?= $escapeAdmin($department['name']) ?></option><?php endforeach; ?></select></label>
<label>اولویت<select name="priority"><?php foreach (['critical' => 'بحرانی', 'urgent' => 'فوری', 'normal' => 'عادی'] as $key => $label): ?><option value="<?= $key ?>" <?= $policy['priority'] === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></label>
<label>مهلت پاسخ (دقیقه)<input type="number" min="1" max="525600" name="response_minutes" required value="<?= (int) $policy['response_minutes'] ?>"></label>
<label>مهلت حل (دقیقه)<input type="number" min="1" max="525600" name="resolution_minutes" required value="<?= (int) $policy['resolution_minutes'] ?>"></label>
<label>ارجاع اول (دقیقه)<input type="number" min="1" max="525600" name="escalation_1_minutes" required value="<?= (int) $policy['escalation_1_minutes'] ?>"></label>
<label>ارجاع دوم (دقیقه)<input type="number" min="1" max="525600" name="escalation_2_minutes" required value="<?= (int) $policy['escalation_2_minutes'] ?>"></label>
<label class="check-label"><input type="checkbox" name="is_active" value="1" <?= (int) $policy['is_active'] === 1 ? 'checked' : '' ?>> فعال</label>
<button class="button" type="submit">ذخیره سیاست</button>
</form>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
<section class="card" style="padding:16px;margin-top:18px" aria-labelledby="rc-admin-audit-title">
<h2 id="rc-admin-audit-title">آخرین رویدادهای عملیاتی</h2>
<p class="muted">فقط رخدادهای درخواست‌های قابل مشاهده در دامنهٔ دسترسی این حساب نمایش داده می‌شود. محتوای پیام‌ها و جزئیات یادداشت‌های داخلی در این فهرست بارگذاری یا افشا نمی‌شود.</p>
<?php if (!$adminAuditRows): ?><p class="muted">رخداد قابل نمایشی وجود ندارد.</p><?php else: ?>
<div style="overflow-x:auto"><table class="rc-admin-table"><thead><tr><th>زمان</th><th>درخواست</th><th>رخداد</th><th>عامل</th><th>تغییر وضعیت</th></tr></thead><tbody>
<?php foreach ($adminAuditRows as $event): ?>
<tr>
<td><?= $escapeAdmin($event['created_at']) ?></td>
<td><a href="request-center.php?id=<?= (int) $event['ticket_id'] ?>&amp;workspace=admin"><?= $escapeAdmin($event['ticket_no'] ?: ('#' . $event['ticket_id'])) ?> · <?= $escapeAdmin($event['subject']) ?></a></td>
<td><?= $escapeAdmin($adminAuditLabels[(string) $event['event_type']] ?? $event['event_type']) ?></td>
<td><?= $escapeAdmin($event['actor_name'] ?: 'سامانه') ?></td>
<td><?= $escapeAdmin($event['from_status'] ?: '—') ?> ← <?= $escapeAdmin($event['to_status'] ?: '—') ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</section>
<section class="card" style="padding:16px;margin-top:18px" aria-labelledby="rc-admin-config-audit-title">
<h2 id="rc-admin-config-audit-title">آخرین تغییرات پیکربندی مرکز درخواست‌ها</h2>
<p class="muted">تغییرات تقویم کاری و سیاست‌های OLA از دفتر ثبت مرکزی خوانده می‌شوند؛ جزئیات فنی یا داده‌های حساس در این فهرست نمایش داده نمی‌شود.</p>
<?php if (!$adminConfigAuditRows): ?><p class="muted">تغییر پیکربندی ثبت‌شده‌ای وجود ندارد.</p><?php else: ?>
<div style="overflow-x:auto"><table class="rc-admin-table"><thead><tr><th>زمان</th><th>عملیات</th><th>عامل</th><th>نتیجه</th></tr></thead><tbody>
<?php foreach ($adminConfigAuditRows as $event): ?>
<tr>
<td><?= $escapeAdmin($event['created_at']) ?></td>
<td><?= $escapeAdmin($adminAuditLabels[(string) $event['action_code']] ?? $event['action_code']) ?></td>
<td><?= $escapeAdmin($event['actor_name'] ?: 'سامانه') ?></td>
<td><?= $escapeAdmin(['success' => 'موفق', 'failure' => 'ناموفق', 'denied' => 'ردشده', 'unknown' => 'نامشخص'][(string) $event['result']] ?? $event['result']) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</section>
</main></body></html>
