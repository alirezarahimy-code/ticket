<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/lib/http-errors.php';
require_once dirname(__DIR__) . '/lib/html-feedback.php';

/** Supervisor workspace dashboard. All records are restricted by ticket_scope(). */
if (!user_can($user, 'supervisor.panel')) {
    request_center_error_page(403, 'دسترسی مجاز نیست', 'دسترسی به فضای کاری سوپروایزر فقط برای دارندهٔ مجوز «supervisor.panel» فعال است.');
}

$summary = $repo->workspaceSummary($user);
$recent = $repo->listVisible($user, 1, 8);
$overdue = $repo->listQueue($user, 'overdue', 1, 8);
// هشدارهای فعال SLA/OLA که cron ثبت کرده (محدود به محدودهٔ دسترسی همین کاربر)
$activeAlerts = [];
try {
    [$alertWhere, $alertParams] = ticket_scope($user, 't');
    $alertSql = 'SELECT e.event_type, e.created_at, t.id AS ticket_id, t.ticket_no, t.subject, t.due_at '
        . 'FROM ticket_events e JOIN tickets t ON t.id = e.ticket_id '
        . ($alertWhere !== '' ? $alertWhere . ' AND ' : 'WHERE ')
        . "e.event_type IN ('sla_alert', 'ola_alert') ORDER BY e.created_at DESC, e.id DESC LIMIT 8";
    $alertQuery = db()->prepare($alertSql);
    $alertQuery->execute($alertParams);
    $activeAlerts = $alertQuery->fetchAll();
} catch (Throwable) {
    $activeAlerts = [];
}
$workload = $repo->supervisorWorkload($user);
$escapeSupervisor = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$supervisorStatusLabels = [
    'new' => 'جدید', 'manager_review' => 'در انتظار ارجاع', 'assigned' => 'ارجاع‌شده',
    'in_progress' => 'در حال انجام', 'waiting_user' => 'در انتظار پاسخ کاربر',
    'pending' => 'در انتظار پاسخ کاربر', 'resolved' => 'حل‌شده', 'closed' => 'بسته‌شده',
    'cancelled' => 'لغوشده', 'rejected' => 'ردشده',
];
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: private, no-store');
?>
<?php
$__rcRender = dirname(__DIR__, 3) . '/lib/render.php';
if (is_file($__rcRender)) { require_once $__rcRender; }
render_header('فضای کاری سوپروایزر', $user);
?>
<div class="rc-page rc-supervisor-page">
<link rel="stylesheet" href="modules/request-center/Assets/request-center.css">
<style>
.rc-supervisor-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:18px 0}
.rc-supervisor-stat{position:relative;min-height:104px;padding:16px 18px;overflow:hidden;background:var(--white,#fffdf8);border:1px solid var(--line);border-radius:14px;box-shadow:0 5px 18px rgba(65,70,44,.06)}
.rc-supervisor-stat:before{content:"";position:absolute;inset-inline-start:0;top:0;bottom:0;width:3px;background:var(--navy-2)}
.rc-supervisor-stat strong{display:block;font-size:28px;line-height:1.45;font-weight:800;color:var(--navy-2);font-variant-numeric:tabular-nums}
.rc-supervisor-stat span{display:block;color:var(--muted);font-size:12px}
.rc-supervisor-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;align-items:start}
.rc-supervisor-list{padding:18px;min-width:0}
.rc-supervisor-list h2,.rc-supervisor-workload h2{font-size:15px;margin:0 0 10px;color:var(--navy)}
.rc-supervisor-workload{margin:16px 0;padding:18px;overflow:auto}
.rc-supervisor-workload .muted{font-size:12px;color:var(--muted);margin:0 0 12px}
.rc-supervisor-workload table{width:100%;border-collapse:separate;border-spacing:0;min-width:620px}
.rc-supervisor-workload th,.rc-supervisor-workload td{padding:11px 13px;text-align:right;border-bottom:1px solid var(--line)}
.rc-supervisor-workload th{font-size:11px;color:var(--muted);background:#faf7ec;white-space:nowrap}
.rc-supervisor-workload td{font-size:13px}
.rc-supervisor-workload tbody tr:hover{background:#f8fbfc}
.rc-supervisor-item{padding:12px 0;border-bottom:1px solid var(--line)}
.rc-supervisor-item:last-child{border-bottom:0}
.rc-supervisor-item b{display:block;font-size:13px;line-height:1.8}
.rc-supervisor-item a{color:var(--navy-2);text-decoration:none}
.rc-supervisor-item a:hover{text-decoration:underline}
.rc-supervisor-item small{display:block;color:var(--muted);font-size:11px;margin-top:3px}
.heading{display:flex;justify-content:space-between;align-items:center;gap:18px;margin:0 0 18px;flex-wrap:wrap}
.heading h1{font-size:23px;line-height:1.5;margin:0 0 5px}
.heading p{margin:0;color:var(--muted);font-size:13px}
.rc-workspace-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:4px 0 14px}
.rc-workspace-tabs a{padding:7px 14px;border:1px solid var(--line);border-radius:999px;background:var(--white,#fffdf8);color:var(--muted);font-size:13px;font-weight:600;white-space:nowrap;text-decoration:none}
.rc-workspace-tabs a.active{background:var(--navy);border-color:var(--navy);color:#fff}
@media(max-width:900px){.rc-supervisor-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.rc-supervisor-columns{grid-template-columns:1fr}}
@media(max-width:600px){.heading{align-items:flex-start;flex-direction:column}.heading h1{font-size:21px}.rc-supervisor-grid{gap:8px}.rc-supervisor-stat{padding:12px;min-height:90px}.rc-supervisor-stat strong{font-size:23px}.rc-supervisor-list,.rc-supervisor-workload{padding:14px}}
</style>
<?php if (count($workspaceOptions) > 1): ?><nav class="rc-workspace-tabs" aria-label="فضاهای کاری"><?php foreach ($workspaceOptions as $key => $label): ?><a class="<?= $key === 'supervisor' ? 'active' : '' ?>" href="request-center.php?workspace=<?= $escapeSupervisor($key) ?>"><?= $escapeSupervisor($label) ?></a><?php endforeach; ?></nav><?php endif; ?>
<?php request_center_render_flashes(); ?>
<script src="modules/request-center/Assets/request-center.js" defer></script>
<section class="heading"><div><h1>فضای کاری سوپروایزر</h1><p>پایش درخواست‌های حوزهٔ نظارت، موارد خارج از تعهد و درخواست‌های منتظر تصمیم.</p></div><div style="display:flex;gap:8px;flex-wrap:wrap"><a class="button secondary" href="request-center.php?workspace=supervisor&amp;calendar=1">تقویم موعدها</a><a class="button" href="request-center.php?page=supervisor">ورود به صف تصمیم‌ها</a><a class="button secondary" href="request-center.php?action=export_decisions">خروجی Excel تصمیمات</a><?php if (user_can($user, 'queue.view')): ?><a class="button secondary" href="request-center.php?page=queue&amp;queue=overdue">پیگیری موارد معوق</a><a class="button secondary" href="request-center.php?page=queue&amp;queue=unassigned">درخواست‌های بدون مسئول</a><?php endif; ?></div></section>
<section class="rc-supervisor-grid">
<div class="rc-supervisor-stat"><strong><?= number_format($summary['total']) ?></strong><span>کل درخواست‌های تحت نظارت</span></div>
<div class="rc-supervisor-stat"><strong><?= number_format($summary['active']) ?></strong><span>در گردش</span></div>
<div class="rc-supervisor-stat"><strong><?= number_format($summary['overdue']) ?></strong><span>خارج از SLA/OLA</span></div>
<div class="rc-supervisor-stat"><strong><?= number_format($summary['awaiting_supervisor']) ?></strong><span>منتظر تصمیم نهایی</span></div>
<div class="rc-supervisor-stat"><strong><?= number_format($summary['unassigned']) ?></strong><span>بدون مسئول</span></div>
<div class="rc-supervisor-stat"><strong><?= number_format($summary['awaiting_assignment']) ?></strong><span>منتظر ارجاع</span></div>
<div class="rc-supervisor-stat"><strong><?= number_format($summary['waiting_user']) ?></strong><span>منتظر پاسخ کاربر</span></div>
<div class="rc-supervisor-stat"><strong><?= number_format($summary['due_soon']) ?></strong><span>موعد در ۲۴ ساعت آینده</span></div>
<div class="rc-supervisor-stat"><strong><?= number_format($summary['rejected']) ?></strong><span>ردشده</span></div>
</section>
<section class="card rc-supervisor-workload">
<h2>توزیع بار جاری کارشناسان</h2>
<p class="muted">مبنای توزیع، تعداد درخواست‌های فعال، موارد منتظر پاسخ کاربر، موارد خارج از تعهد و اولویت‌های بالا است؛ سهمیهٔ ظرفیت ثابت در Schema فعلی تعریف نشده است.</p>
<?php if (!$workload): ?><p class="muted">در حال حاضر درخواست فعالِ دارای مسئول در محدودهٔ نظارت وجود ندارد.</p><?php else: ?>
<table><thead><tr><th>کارشناس</th><th>درخواست فعال</th><th>منتظر پاسخ کاربر</th><th>خارج از تعهد</th><th>اولویت بالا</th></tr></thead><tbody>
<?php foreach ($workload as $load): ?><tr><td><?= $escapeSupervisor($load['full_name']) ?></td><td><?= number_format((int) $load['active_count']) ?></td><td><?= number_format((int) $load['waiting_user_count']) ?></td><td><?= number_format((int) $load['overdue_count']) ?></td><td><?= number_format((int) $load['high_priority_count']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</section>
<div class="rc-supervisor-columns">
<section class="card rc-supervisor-list"><h2>موارد خارج از SLA/OLA</h2>
<?php if (!$overdue['rows']): ?><p class="muted">در حال حاضر موردی در محدودهٔ دسترسی شما خارج از تعهد نیست.</p><?php else: ?>
<?php foreach ($overdue['rows'] as $row): ?><article class="rc-supervisor-item"><b><a href="request-center.php?page=ticket&amp;id=<?= (int) $row['id'] ?>&amp;workspace=supervisor"><?= $escapeSupervisor($row['ticket_no'] ?: ('#' . $row['id'])) ?> · <?= $escapeSupervisor($row['subject']) ?></a></b><small><?= $escapeSupervisor($row['assignee_name'] ?: 'بدون مسئول') ?> · <?= $escapeSupervisor($supervisorStatusLabels[(string) $row['status']] ?? $row['status']) ?> · موعد <?= $escapeSupervisor($row['due_at'] ?: 'ثبت نشده') ?></small></article><?php endforeach; ?>
<?php endif; ?>
<?php if (user_can($user, 'queue.view')): ?><a class="button secondary" href="request-center.php?page=queue&amp;queue=overdue">مشاهده صف کامل موارد معوق</a><?php endif; ?>
</section>
<section class="card rc-supervisor-list" style="grid-column:1/-1"><h2>هشدارهای فعال SLA/OLA (cron)</h2>
<?php if (!$activeAlerts): ?><p class="muted">هشدار فعالی در محدودهٔ دسترسی شما ثبت نشده است.</p><?php else: ?>
<?php foreach ($activeAlerts as $alert): ?><article class="rc-supervisor-item"><b><a href="request-center.php?page=ticket&amp;id=<?= (int) $alert['ticket_id'] ?>&amp;workspace=supervisor"><?= $escapeSupervisor($alert['ticket_no'] ?: ('#' . (int) $alert['ticket_id'])) ?> · <?= $escapeSupervisor($alert['subject']) ?></a></b><small><?= $escapeSupervisor($alert['event_type'] === 'sla_alert' ? 'هشدار SLA' : 'هشدار OLA') ?> · <?= $escapeSupervisor((string) $alert['created_at']) ?> · موعد <?= $escapeSupervisor($alert['due_at'] ?: 'ثبت نشده') ?></small></article><?php endforeach; ?>
<?php endif; ?>
</section>
<section class="card rc-supervisor-list"><h2>آخرین تغییرات درخواست‌ها</h2>
<?php if (!$recent['rows']): ?><p class="muted">درخواستی برای نمایش وجود ندارد.</p><?php else: ?>
<?php foreach ($recent['rows'] as $row): ?><article class="rc-supervisor-item"><b><a href="request-center.php?page=ticket&amp;id=<?= (int) $row['id'] ?>&amp;workspace=supervisor"><?= $escapeSupervisor($row['ticket_no'] ?: ('#' . $row['id'])) ?> · <?= $escapeSupervisor($row['subject']) ?></a></b><small><?= $escapeSupervisor($row['assignee_name'] ?: 'بدون مسئول') ?> · <?= $escapeSupervisor($supervisorStatusLabels[(string) $row['status']] ?? $row['status']) ?> · <?= $escapeSupervisor($row['updated_at']) ?></small></article><?php endforeach; ?>
<?php endif; ?>
</section>
</div>
</div>
<?php render_footer(); ?>
