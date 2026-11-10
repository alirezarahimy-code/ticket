<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/lib/http-errors.php';
require_once dirname(__DIR__) . '/lib/html-feedback.php';

/**
 * «فضای مدیر واحد» — برای رئیسِ واحدهای چارت سازمانی (طراحی مالک).
 *
 * مرز اختیار او دقیقاً همان چیزی است که در چارت سازمانی مشخص شده:
 *   تیکت‌های ثبت‌شده توسط خودش + هر فرد زیرمجموعهٔ واحدهایی که رئیس آن‌هاست + ارجاع‌شده‌های به خودش.
 *   نه بیشتر، نه کمتر (هیچ فیلتر service_group اعمال نمی‌شود).
 *
 * خارج از این فضای کاری، این فرد در صف کارشناس/تقویم/گزارش‌های عملیاتی شرکت نمی‌کند
 * (برای نقش‌های صرفِ مدیر واحد). وارد کردن او به «فضای کارشناس» عمداً پیاده‌سازی نشده،
 * چون در آن‌صورت درخواست‌ها به او ارجاع می‌شد و می‌توانست مستقیماً در جریان رسیدگی دخل و تصرف کند.
 */
if (!function_exists('request_center_is_unit_head') || !request_center_is_unit_head($user)) {
    request_center_error_page(403, 'دسترسی مجاز نیست', 'این فضا فقط برای رئیس واحدهای فعال در چارت سازمانی فعال است. اگر در چارت مدیر واحدی هستید و این صفحه را نمی‌بینید، با ادمین سامانه یا صفحهٔ «چارت سازمانی» تماس بگیرید.');
}

$escapeUnit = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

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
$groupLabels = ['it' => 'فنی (IT)', 'support' => 'پشتیبانی'];
$openStatuses = ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending'];

$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'status' => (string) ($_GET['status'] ?? ''),
    'priority' => (string) ($_GET['priority'] ?? ''),
    'service_group' => (string) ($_GET['service_group'] ?? ''),
];
if (!in_array($filters['status'], array_keys($statusLabels), true)) {
    $filters['status'] = '';
}
if (!in_array($filters['priority'], array_keys($priorityLabels), true)) {
    $filters['priority'] = '';
}
if (!in_array($filters['service_group'], ['it', 'support'], true)) {
    $filters['service_group'] = '';
}
$filters['q'] = function_exists('mb_substr') ? mb_substr($filters['q'], 0, 100, 'UTF-8') : substr($filters['q'], 0, 100);

$page = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 25;
$summary = $repo->unitHeadSummary($user);
$result = $repo->listUnitHead($user, $page, $perPage, $filters);
$total = (int) $result['total'];
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$rows = $result['rows'];
$nowTs = time();

$queryBase = ['workspace' => 'unit'];
if ($filters['q'] !== '') { $queryBase['q'] = $filters['q']; }
if ($filters['status'] !== '') { $queryBase['status'] = $filters['status']; }
if ($filters['priority'] !== '') { $queryBase['priority'] = $filters['priority']; }
if ($filters['service_group'] !== '') { $queryBase['service_group'] = $filters['service_group']; }
$queryString = http_build_query($queryBase);

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: private, no-store');
?>
<?php
$__rcRender = dirname(__DIR__, 3) . '/lib/render.php';
if (is_file($__rcRender)) { require_once $__rcRender; }
render_header('فضای مدیر واحد', $user);
?>
<div class="rc-page rc-unit-page">
<link rel="stylesheet" href="modules/request-center/Assets/request-center.css?v=20261111rc3">
<style>
.heading{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin:0 0 16px;flex-wrap:wrap}
.heading h1{font-size:24px;line-height:1.6;margin:0 0 4px}.heading p{margin:0;color:var(--muted);font-size:13px;max-width:640px}
.rc-workspace-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:4px 0 14px}
.rc-workspace-tabs a{padding:7px 14px;border:1px solid var(--line);border-radius:999px;background:var(--white,#fffdf8);color:var(--muted);font-size:13px;font-weight:600;white-space:nowrap;text-decoration:none}
.rc-workspace-tabs a.active{background:var(--navy);border-color:var(--navy);color:#fff}
.rc-unit-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0 0 16px}
.rc-unit-stat{position:relative;padding:12px 14px;background:var(--white,#fffdf8);border:1px solid var(--line);border-radius:14px;overflow:hidden}
.rc-unit-stat:before{content:"";position:absolute;inset-inline-start:0;top:0;bottom:0;width:3px;background:var(--navy-2)}
.rc-unit-stat.danger:before{background:var(--danger,#b83b32)}
.rc-unit-stat strong{display:block;font-size:24px;font-weight:800;color:var(--navy-2);font-variant-numeric:tabular-nums}
.rc-unit-stat.danger strong{color:var(--red,#b83b32)}
.rc-unit-stat span{display:block;color:var(--muted);font-size:12px}
.rc-unit-filters{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;background:var(--white,#fffdf8);border:1px solid var(--line);border-radius:12px;padding:12px;margin:0 0 14px}
.rc-unit-filters label{font-size:11px;color:var(--muted);display:flex;flex-direction:column;gap:4px;min-width:120px}
.rc-unit-filters input,.rc-unit-filters select{min-height:36px;padding:6px 10px;border:1px solid var(--line);border-radius:8px;font:inherit;font-size:13px;background:#fff}
.rc-unit-table{width:100%;border-collapse:collapse;background:var(--white,#fffdf8);border:1px solid var(--line);border-radius:12px;overflow:hidden;font-size:13px}
.rc-unit-table th,.rc-unit-table td{padding:11px 12px;text-align:right;border-bottom:1px solid var(--line);vertical-align:middle}
.rc-unit-table th{background:#faf7ec;color:var(--muted);font-size:11px;white-space:nowrap}
.rc-unit-table tr:last-child td{border-bottom:0}
.rc-unit-table tbody tr:hover{background:#f6faf7}
.rc-unit-table tr.is-overdue td{background:#fff4f1}
.rc-unit-badge{display:inline-block;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:700;white-space:nowrap}
.rc-unit-badge.status{background:#ece7d8;color:#4a4436}
.rc-unit-badge.critical{background:#f8d7d3;color:#a32b23}
.rc-unit-badge.urgent{background:#fdf0d0;color:#8a6d1a}
.rc-unit-badge.normal{background:#e8f0e8;color:#2d5a3d}
.rc-unit-badge.paused{background:#e9edf7;color:#3c4a72}
.rc-unit-badge.overdue{background:#b83b32;color:#fff}
.rc-unit-subject{font-weight:700;color:var(--navy-2);text-decoration:none}
.rc-unit-subject:hover{text-decoration:underline}
.rc-unit-meta{display:block;color:var(--muted);font-size:11px;margin-top:2px}
.empty{padding:36px 20px;text-align:center;color:var(--muted)}
.pagination{display:flex;gap:6px;margin:14px 0 0;flex-wrap:wrap}
.pagination a,.pagination span{padding:6px 12px;border:1px solid var(--line);border-radius:8px;text-decoration:none;color:var(--navy-2)}
.pagination .current{background:var(--navy);border-color:var(--navy);color:#fff}
@media(max-width:900px){.rc-unit-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
<?php if (count($workspaceOptions) > 1): ?><nav class="rc-workspace-tabs" aria-label="فضاهای کاری"><?php foreach ($workspaceOptions as $key => $label): ?><a class="<?= $key === 'unit' ? 'active' : '' ?>" href="request-center.php?workspace=<?= $escapeUnit($key) ?>"><?= $escapeUnit($label) ?></a><?php endforeach; ?></nav><?php endif; ?>
<?php request_center_render_flashes(); ?>
<script src="modules/request-center/Assets/request-center.js" defer></script>
<section class="heading">
  <div>
    <h1>فضای مدیر واحد</h1>
    <p>فقط درخواست‌های <strong>خودت</strong> و درخواست‌های افراد <strong>زیرمجموعهٔ چارت سازمانی‌ات</strong> — نه بیشتر، نه کمتر. این صفحه برای پایش است؛ رسیدگی و ارجاع همان‌طور که همیشه انجام می‌شود در اختیار کارشناسان همان حوزه‌ها می‌ماند.</p>
  </div>
</section>
<section class="rc-unit-kpis" aria-label="خلاصهٔ واحد">
  <div class="rc-unit-stat"><strong><?= number_format($summary['open']) ?></strong><span>درخواست باز در واحد</span></div>
  <div class="rc-unit-stat <?= $summary['overdue'] > 0 ? 'danger' : '' ?>"><strong><?= number_format($summary['overdue']) ?></strong><span>خارج از موعد SLA</span></div>
  <div class="rc-unit-stat"><strong><?= number_format($summary['waiting_user']) ?></strong><span>در انتظار پاسخ کاربر</span></div>
  <div class="rc-unit-stat"><strong><?= number_format($summary['resolved_30d']) ?></strong><span>حل‌شده در ۳۰ روز اخیر</span></div>
</section>
<form class="rc-unit-filters" method="get" action="request-center.php">
  <input type="hidden" name="workspace" value="unit">
  <label>جست‌وجو<input type="search" name="q" value="<?= $escapeUnit($filters['q']) ?>" placeholder="شماره یا موضوع درخواست" maxlength="100"></label>
  <label>وضعیت<select name="status"><option value="">همه</option><?php foreach ($statusLabels as $key => $label): ?><option value="<?= $escapeUnit($key) ?>"<?= $filters['status'] === $key ? ' selected' : '' ?>><?= $escapeUnit($label) ?></option><?php endforeach; ?></select></label>
  <label>اولویت<select name="priority"><option value="">همه</option><?php foreach ($priorityLabels as $key => $label): ?><option value="<?= $escapeUnit($key) ?>"<?= $filters['priority'] === $key ? ' selected' : '' ?>><?= $escapeUnit($label) ?></option><?php endforeach; ?></select></label>
  <label>حوزهٔ خدماتی<select name="service_group"><option value="">هر دو</option><?php foreach ($groupLabels as $key => $label): ?><option value="<?= $escapeUnit($key) ?>"<?= $filters['service_group'] === $key ? ' selected' : '' ?>><?= $escapeUnit($label) ?></option><?php endforeach; ?></select></label>
  <button class="button secondary" type="submit">اعمال فیلتر</button>
  <?php if ($queryString !== 'workspace=unit'): ?><a class="button secondary" href="request-center.php?workspace=unit">حذف فیلتر</a><?php endif; ?>
</form>
<table class="rc-unit-table">
  <thead><tr><th>شماره</th><th>درخواست</th><th>درخواست‌دهنده</th><th>اولویت</th><th>وضعیت</th><th>حوزه</th><th>موعد SLA</th></tr></thead>
  <tbody>
  <?php if ($rows === []): ?>
    <tr><td colspan="7" class="empty">درخواستی در محدودهٔ شما پیدا نشد.</td></tr>
  <?php else: ?>
    <?php foreach ($rows as $row):
        $tid = (int) $row['id'];
        $status = (string) ($row['status'] ?? '');
        $priority = (string) ($row['priority'] ?? 'normal');
        $dueRaw = trim((string) ($row['due_at'] ?? ''));
        $dueTs = $dueRaw !== '' ? strtotime($dueRaw) : false;
        $isOpen = in_array($status, $openStatuses, true);
        $isOverdue = $isOpen && $dueTs !== false && $dueTs < $nowTs;
        $isPaused = !empty($row['sla_paused_at']);
        $dueJalali = $dueRaw !== '' && function_exists('jalali_date') ? jalali_date($dueRaw, false) : $dueRaw;
        $returnParams = array_merge(['page' => 'ticket', 'id' => $tid, 'workspace' => 'unit', 'p' => $page], array_filter(['q' => $filters['q'], 'status' => $filters['status'], 'priority' => $filters['priority'], 'service_group' => $filters['service_group']], static fn ($v) => $v !== ''));
    ?>
    <tr class="<?= $isOverdue ? 'is-overdue' : '' ?>">
      <td style="white-space:nowrap"><a class="rc-unit-subject" href="request-center.php?<?= $escapeUnit(http_build_query($returnParams)) ?>"><?= $escapeUnit((string) ($row['ticket_no'] !== '' ? $row['ticket_no'] : ('#' . $tid))) ?></a></td>
      <td><span title="<?= $escapeUnit((string) $row['subject']) ?>"><?= $escapeUnit(mb_substr((string) $row['subject'], 0, 90, 'UTF-8')) ?></span></td>
      <td><?= $escapeUnit((string) ($row['requester_name'] ?? '')) ?></td>
      <td><span class="rc-unit-badge <?= $escapeUnit($priority) ?>"><?= $escapeUnit($priorityLabels[$priority] ?? $priority) ?></span></td>
      <td><span class="rc-unit-badge status"><?= $escapeUnit($statusLabels[$status] ?? $status) ?></span><?= $isPaused ? ' <span class="rc-unit-badge paused">مکث SLA</span>' : '' ?></td>
      <td><?= $escapeUnit($groupLabels[(string) $row['service_group']] ?? (string) $row['service_group']) ?></td>
      <td style="white-space:nowrap"><?= $dueRaw !== '' ? $escapeUnit($dueJalali) : '—' ?><?= $isOverdue ? ' <span class="rc-unit-badge overdue">معوق</span>' : '' ?></td>
    </tr>
    <?php endforeach; ?>
  <?php endif; ?>
  </tbody>
</table>
<?php if ($pages > 1): ?><nav class="pagination" aria-label="صفحه‌بندی"><?php for ($i = max(1, $page - 3); $i <= min($pages, $page + 3); $i++): ?><?php if ($i === $page): ?><span class="current"><?= (int) $i ?></span><?php else: ?><a href="request-center.php?<?= $escapeUnit($queryString) ?>&amp;p=<?= (int) $i ?>"><?= (int) $i ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; ?>
</div>
<?php render_footer(); ?>
