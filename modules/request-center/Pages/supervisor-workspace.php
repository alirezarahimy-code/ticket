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
:root{color-scheme:light;--rc-ink:#172b4d;--rc-muted:#64748b;--rc-line:#e3eaf3;--rc-brand:#245b9b;--rc-brand-dark:#173f70;--rc-canvas:#f3f6fb}
*{box-sizing:border-box}
html{min-height:100%;background:var(--rc-canvas)}
body{margin:0;background:radial-gradient(1000px 360px at 85% -160px,#dceafa 0,transparent 70%),var(--rc-canvas);color:var(--rc-ink);font-family:Tahoma,"Segoe UI",sans-serif;line-height:1.8}
body>header{position:sticky;top:0;z-index:20;background:rgba(255,255,255,.94);border-bottom:1px solid var(--rc-line);box-shadow:0 4px 18px rgba(20,43,75,.05);backdrop-filter:blur(12px)}
body>header .bar{max-width:1280px;margin:auto;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px}
body>header .brand{font-size:17px;font-weight:800;color:var(--rc-brand-dark)}
body>header small{display:block;color:var(--rc-muted);font-size:12px}
.workspace-nav{max-width:1280px;margin:auto;padding:0 24px 10px;display:flex;gap:7px;overflow-x:auto}
.workspace-nav a{white-space:nowrap;text-decoration:none;color:#53657d;border:1px solid transparent;border-radius:9px;padding:6px 12px;font-size:12px;font-weight:700}
.workspace-nav a:hover,.workspace-nav a.active{background:#eaf1fb;color:var(--rc-brand-dark);border-color:#d7e4f5}
body>main{max-width:1280px;margin:0 auto;padding:28px 24px 48px}
.heading{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin:0 0 18px}
.heading h1{font-size:25px;line-height:1.5;margin:0 0 5px;color:#172b4d}
.heading p{margin:0;color:var(--rc-muted);font-size:13px}
.button{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:38px;padding:8px 14px;border:1px solid var(--rc-brand);border-radius:9px;background:var(--rc-brand);color:#fff;text-decoration:none;font:inherit;font-size:12px;font-weight:700;white-space:nowrap;cursor:pointer;transition:background .15s,border-color .15s,transform .15s}
.button:hover{background:var(--rc-brand-dark);border-color:var(--rc-brand-dark);transform:translateY(-1px)}
.button.secondary{background:#fff;color:var(--rc-brand-dark);border-color:#d6e0ec}
.button.secondary:hover{background:#f2f6fc;border-color:#b9cde5}
.card,.rc-supervisor-stat{background:#fff;border:1px solid var(--rc-line);border-radius:14px;box-shadow:0 5px 18px rgba(20,43,75,.045)}
.rc-supervisor-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:18px 0}
.rc-supervisor-stat{position:relative;min-height:104px;padding:16px 18px;overflow:hidden}
.rc-supervisor-stat:before{content:"";position:absolute;inset-inline-start:0;top:0;bottom:0;width:3px;background:var(--rc-brand)}
.rc-supervisor-stat strong{display:block;font-size:28px;line-height:1.45;font-weight:800;color:#1d4778;font-variant-numeric:tabular-nums}
.rc-supervisor-stat span{display:block;color:var(--rc-muted);font-size:12px}
.rc-supervisor-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;align-items:start}
.rc-supervisor-list{padding:18px;min-width:0}
.rc-supervisor-list h2,.rc-supervisor-workload h2{font-size:15px;margin:0 0 10px;color:#213b5d}
.rc-supervisor-workload{margin:16px 0;padding:18px;overflow:auto}
.rc-supervisor-workload .muted{font-size:12px;color:var(--rc-muted);margin:0 0 12px}
.rc-supervisor-workload table{width:100%;border-collapse:separate;border-spacing:0;min-width:620px}
.rc-supervisor-workload th,.rc-supervisor-workload td{padding:11px 13px;text-align:right;border-bottom:1px solid #edf1f6}
.rc-supervisor-workload th{font-size:11px;color:#60718a;background:#f7f9fc;white-space:nowrap}
.rc-supervisor-workload td{font-size:13px}
.rc-supervisor-workload tbody tr:hover{background:#f8fbff}
.rc-supervisor-item{padding:12px 0;border-bottom:1px solid #edf1f6}
.rc-supervisor-item:last-child{border-bottom:0}
.rc-supervisor-item b{display:block;font-size:13px;line-height:1.8}
.rc-supervisor-item a{color:#245b9b;text-decoration:none}
.rc-supervisor-item a:hover{text-decoration:underline}
.rc-supervisor-item small{display:block;color:var(--rc-muted);font-size:11px;margin-top:3px}
.muted{color:var(--rc-muted)}
@media(max-width:900px){.rc-supervisor-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.rc-supervisor-columns{grid-template-columns:1fr}}
@media(max-width:600px){body>header .bar{padding:10px 14px;align-items:flex-start}body>main{padding:20px 14px 36px}.workspace-nav{padding:0 14px 9px}.heading{align-items:flex-start;flex-direction:column}.heading h1{font-size:21px}.heading>div:last-child{width:100%;display:flex;flex-wrap:wrap}.heading .button{flex:1 1 auto}.rc-supervisor-grid{gap:8px}.rc-supervisor-stat{padding:12px;min-height:90px}.rc-supervisor-stat strong{font-size:23px}.rc-supervisor-stat span{font-size:11px}.rc-supervisor-list,.rc-supervisor-workload{padding:14px}}
@media(prefers-reduced-motion:reduce){*,*:before,*:after{scroll-behavior:auto!important;transition:none!important}}
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
