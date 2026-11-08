<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/** Primary-admin workspace dashboard. Read-only and scoped through the core policy. */
if (!user_is_primary_admin($user)) {
    http_response_code(403);
    exit('دسترسی به مدیریت مرکز درخواست‌ها مجاز نیست.');
}

$summary = $repo->workspaceSummary($user);
$recent = $repo->listVisible($user, 1, 8);
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
<header><div class="bar"><div><div class="brand">مرکز درخواست‌ها</div><small><?= $escapeAdmin($user['full_name'] ?? $user['username'] ?? '') ?> · مدیریت اصلی ماژول</small></div><a class="button secondary" href="index.php">بازگشت به سامانه</a></div>
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
<?php if (!$recent['rows']): ?><p class="muted">درخواستی در محدودهٔ دسترسی وجود ندارد.</p><?php else: ?>
<div style="overflow-x:auto"><table class="rc-admin-table"><thead><tr><th>درخواست</th><th>مسئول</th><th>وضعیت</th><th>موعد SLA</th></tr></thead><tbody>
<?php foreach ($recent['rows'] as $row): ?>
<tr><td><a href="request-center.php?id=<?= (int) $row['id'] ?>&amp;workspace=admin"><?= $escapeAdmin($row['ticket_no'] ?: ('#' . $row['id'])) ?> · <?= $escapeAdmin($row['subject']) ?></a></td><td><?= $escapeAdmin($row['assignee_name'] ?: 'بدون مسئول') ?></td><td><?= $escapeAdmin($adminStatusLabels[(string) $row['status']] ?? $row['status']) ?></td><td><?= $escapeAdmin($row['due_at'] ?: '—') ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</section>
<aside class="rc-admin-links" aria-label="مدیریت و گزارش‌ها">
<?php foreach ($adminLinks as $link): ?><a class="rc-admin-link" href="<?= $escapeAdmin($link['url']) ?>"><b><?= $escapeAdmin($link['title']) ?></b><small><?= $escapeAdmin($link['description']) ?></small></a><?php endforeach; ?>
</aside>
</div>
</main></body></html>
