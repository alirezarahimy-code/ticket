<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/** Supervisor workspace dashboard. All records are restricted by ticket_scope(). */
if (!user_can($user, 'supervisor.panel')) {
    http_response_code(403);
    exit('دسترسی به فضای کاری سوپروایزر مجاز نیست.');
}

$summary = $repo->workspaceSummary($user);
$recent = $repo->listVisible($user, 1, 8);
$overdue = $repo->listQueue($user, 'overdue', 1, 8);
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
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>فضای کاری سوپروایزر</title>
<link rel="stylesheet" href="modules/request-center/Assets/request-center.css">
<style>
.rc-supervisor-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:18px 0}
.rc-supervisor-stat{background:#fff;border:1px solid #e4eaf1;border-radius:13px;padding:16px}
.rc-supervisor-stat strong{display:block;font-size:27px}.rc-supervisor-stat span{font-size:12px;color:#66758b}
.rc-supervisor-columns{display:grid;grid-template-columns:1fr 1fr;gap:16px}
 .rc-supervisor-list{padding:16px}.rc-supervisor-item{padding:12px 0;border-bottom:1px solid #e4eaf1}
.rc-supervisor-workload{margin:16px 0;padding:16px;overflow:auto}.rc-supervisor-workload table{width:100%;border-collapse:collapse;min-width:620px}.rc-supervisor-workload th,.rc-supervisor-workload td{padding:10px 12px;text-align:right;border-bottom:1px solid #e4eaf1}.rc-supervisor-workload th{font-size:12px;color:#66758b}.rc-supervisor-workload td{font-size:14px}
.rc-supervisor-item:last-child{border-bottom:0}.rc-supervisor-item b{display:block}.rc-supervisor-item small{color:#66758b}
@media(max-width:800px){.rc-supervisor-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.rc-supervisor-columns{grid-template-columns:1fr}}
</style>
</head>
<body>
<header><div class="bar"><div><div class="brand">مرکز درخواست‌ها</div><small><?= $escapeSupervisor($user['full_name'] ?? $user['username'] ?? '') ?> · نظارت بر درخواست‌ها</small></div><a class="button secondary" href="request-center.php">خانه مرکز درخواست‌ها</a></div>
<?php if (count($workspaceOptions) > 1): ?><nav class="workspace-nav" aria-label="فضاهای کاری"><?php foreach ($workspaceOptions as $key => $label): ?><a class="<?= $key === 'supervisor' ? 'active' : '' ?>" href="request-center.php?workspace=<?= $escapeSupervisor($key) ?>"><?= $escapeSupervisor($label) ?></a><?php endforeach; ?></nav><?php endif; ?>
</header>
<main>
<section class="heading"><div><h1>فضای کاری سوپروایزر</h1><p>پایش درخواست‌های حوزهٔ نظارت، موارد خارج از تعهد و درخواست‌های منتظر تصمیم.</p></div><div style="display:flex;gap:8px;flex-wrap:wrap"><a class="button secondary" href="request-center.php?workspace=supervisor&amp;calendar=1">تقویم موعدها</a><a class="button" href="request-center.php?page=supervisor">ورود به صف تصمیم‌ها</a></div></section>
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
<?php foreach ($overdue['rows'] as $row): ?><article class="rc-supervisor-item"><b><a href="request-center.php?id=<?= (int) $row['id'] ?>&amp;workspace=supervisor"><?= $escapeSupervisor($row['ticket_no'] ?: ('#' . $row['id'])) ?> · <?= $escapeSupervisor($row['subject']) ?></a></b><small><?= $escapeSupervisor($row['assignee_name'] ?: 'بدون مسئول') ?> · <?= $escapeSupervisor($supervisorStatusLabels[(string) $row['status']] ?? $row['status']) ?> · موعد <?= $escapeSupervisor($row['due_at'] ?: 'ثبت نشده') ?></small></article><?php endforeach; ?>
<?php endif; ?>
<?php if (user_can($user, 'queue.view')): ?><a class="button secondary" href="request-center.php?page=queue&amp;queue=overdue">مشاهده صف کامل موارد معوق</a><?php endif; ?>
</section>
<section class="card rc-supervisor-list"><h2>آخرین تغییرات درخواست‌ها</h2>
<?php if (!$recent['rows']): ?><p class="muted">درخواستی برای نمایش وجود ندارد.</p><?php else: ?>
<?php foreach ($recent['rows'] as $row): ?><article class="rc-supervisor-item"><b><a href="request-center.php?id=<?= (int) $row['id'] ?>&amp;workspace=supervisor"><?= $escapeSupervisor($row['ticket_no'] ?: ('#' . $row['id'])) ?> · <?= $escapeSupervisor($row['subject']) ?></a></b><small><?= $escapeSupervisor($row['assignee_name'] ?: 'بدون مسئول') ?> · <?= $escapeSupervisor($supervisorStatusLabels[(string) $row['status']] ?? $row['status']) ?> · <?= $escapeSupervisor($row['updated_at']) ?></small></article><?php endforeach; ?>
<?php endif; ?>
</section>
</div>
</main></body></html>
