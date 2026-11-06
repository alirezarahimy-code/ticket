<?php
declare(strict_types=1);

/**
 * Independent Request Center read interface.
 *
 * Write operations deliberately remain on the established index.php POST
 * handlers during the first migration slice. No schema DDL or schema healing
 * is allowed from this entry point.
 */
require __DIR__ . '/bootstrap.php';

$user = require_login();
require_once __DIR__ . '/modules/request-center/RequestCenterRepository.php';

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
    $workspaceUrl .= '&amp;agent_view=' . rawurlencode($agentView);
}
if ($workspaceOptions === []) {
    http_response_code(403);
    exit('دسترسی به مرکز درخواست‌ها برای این حساب فعال نیست.');
}
$repo = new RequestCenterRepository(db());
$schemaProblems = $repo->schemaProblems();
if ($schemaProblems !== []) {
    http_response_code(503);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    ?><!doctype html>
    <html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>آماده‌سازی مرکز درخواست‌ها</title>
    <style>body{font-family:Tahoma,sans-serif;background:#f4f7fb;color:#172b4d;margin:0;padding:32px}.card{max-width:760px;margin:6vh auto;background:#fff;border:1px solid #e4eaf1;border-radius:14px;padding:24px;line-height:2}h1{font-size:22px}.muted{color:#66758b}</style></head>
    <body><section class="card"><h1>ساختار پایگاه‌داده برای مرکز درخواست‌ها کامل نیست</h1>
    <p>این ورودی عمداً هیچ تغییری در Schema ایجاد نمی‌کند. ابتدا بستهٔ Migration رسمی همین نسخه را روی یک نسخهٔ پشتیبان‌شده از پایگاه‌داده اعمال و سپس دوباره تلاش کنید.</p>
    <ul><?php foreach ($schemaProblems as $problem): ?><li><?= $escape($problem) ?></li><?php endforeach; ?></ul>
    <p class="muted">برای جلوگیری از تغییر ناخواسته، ادامهٔ درخواست متوقف شد.</p><a href="index.php">بازگشت به سامانه</a></section></body></html><?php
    exit;
}
$ticketId = max(0, (int) ($_GET['id'] ?? 0));
$page = max(1, (int) ($_GET['p'] ?? 1));

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: private, no-store');

?><!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>مرکز درخواست‌ها</title>
<style>
:root{color-scheme:light;--ink:#172b4d;--muted:#66758b;--line:#e4eaf1;--surface:#fff;--canvas:#f4f7fb;--brand:#2459a6;--soft:#edf4ff}
*{box-sizing:border-box}body{margin:0;background:var(--canvas);color:var(--ink);font-family:Tahoma,"Segoe UI",sans-serif;line-height:1.8}
a{color:var(--brand);text-decoration:none}a:hover{text-decoration:underline}
header{background:#fff;border-bottom:1px solid var(--line)}.bar{max-width:1180px;margin:auto;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;gap:16px}.workspace-nav{max-width:1180px;margin:auto;padding:0 22px 12px;display:flex;gap:8px;flex-wrap:wrap}.workspace-nav a{padding:6px 12px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--muted);font-size:13px}.workspace-nav a.active{background:var(--soft);border-color:#bfd5f2;color:var(--brand);font-weight:700}
.brand{font-size:19px;font-weight:700}.bar small{color:var(--muted)}main{max-width:1180px;margin:28px auto;padding:0 22px}
.heading{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:20px}.heading h1{font-size:25px;margin:0}.heading p{margin:4px 0 0;color:var(--muted)}
.button{display:inline-flex;align-items:center;justify-content:center;padding:9px 15px;border-radius:9px;background:var(--brand);color:#fff;font-weight:700}.button.secondary{background:#fff;color:var(--brand);border:1px solid var(--line)}
.card{background:var(--surface);border:1px solid var(--line);border-radius:14px;box-shadow:0 3px 14px #17365d0a;overflow:hidden}
.table{width:100%;border-collapse:collapse}.table th,.table td{padding:13px 15px;text-align:right;border-bottom:1px solid var(--line);vertical-align:middle}.table th{background:#f8fafd;color:var(--muted);font-size:12px}.table tr:last-child td{border-bottom:0}
.subject{font-weight:700;color:var(--ink)}.meta{display:block;color:var(--muted);font-size:12px}.status,.priority{display:inline-block;border-radius:30px;padding:2px 9px;background:var(--soft);font-size:12px;white-space:nowrap}.priority{background:#fff5e8;color:#89520a}
.empty{padding:44px 20px;text-align:center;color:var(--muted)}.pagination{display:flex;gap:7px;justify-content:center;padding:18px}.pagination a,.pagination span{padding:5px 11px;border:1px solid var(--line);border-radius:7px;background:#fff}.pagination .current{background:var(--brand);color:#fff}
.detail-grid{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:18px}.conversation,.sidebar{padding:18px}.message{padding:14px 0;border-bottom:1px solid var(--line)}.message:last-child{border-bottom:0}.message-head{display:flex;justify-content:space-between;gap:12px}.message-head small,.muted{color:var(--muted)}.message-body{white-space:pre-wrap;overflow-wrap:anywhere}.internal{background:#fff9e9;border-radius:9px;padding:12px;margin:8px 0}
dl{display:grid;grid-template-columns:100px 1fr;gap:7px 10px;margin:0}dt{color:var(--muted)}dd{margin:0;font-weight:600;overflow-wrap:anywhere}
.notice{padding:13px 16px;margin-bottom:16px;background:#eef5ff;border:1px solid #d7e6fb;border-radius:10px;color:#27476f}
@media(max-width:780px){.heading{align-items:flex-start;flex-direction:column}.detail-grid{grid-template-columns:1fr}.table th:nth-child(3),.table td:nth-child(3){display:none}.table th,.table td{padding:10px 8px;font-size:12px}}
</style>
</head>
<body>
<header><div class="bar"><div><div class="brand">مرکز درخواست‌ها</div><small><?= $escape($user['full_name'] ?? $user['username'] ?? '') ?></small></div><a class="button secondary" href="index.php">بازگشت به سامانه</a></div>
<?php if (count($workspaceOptions) > 1): ?><nav class="workspace-nav" aria-label="فضاهای کاری"><?php foreach ($workspaceOptions as $key => $label): ?><a class="<?= $workspace === $key ? 'active' : '' ?>" href="request-center.php?workspace=<?= $escape($key) ?>"><?= $escape($label) ?></a><?php endforeach; ?></nav><?php endif; ?>
</header>
<main>
<?php if ($ticketId > 0): ?>
<?php
$ticket = $repo->findVisible($ticketId, $user);
if ($ticket === null) {
    http_response_code(404);
    echo '<section class="card empty"><h2>درخواست پیدا نشد</h2><p>درخواست وجود ندارد یا در حوزهٔ دسترسی حساب شما نیست.</p><a class="button secondary" href="<?= $escape($workspaceUrl) ?>">بازگشت به فهرست</a></section>';
} else {
    $includeInternal = ticket_can_see_internal($ticket, $user);
    $messages = $repo->messages($ticketId, $includeInternal);
    $attachments = $repo->attachmentsForMessages($messages);
    $status = $statusLabels[(string) ($ticket['status'] ?? '')] ?? (string) ($ticket['status'] ?? '—');
    $priority = $priorityLabels[(string) ($ticket['priority'] ?? '')] ?? (string) ($ticket['priority'] ?? '—');
    ?>
    <section class="heading"><div><h1><?= $escape($ticket['subject']) ?></h1><p><?= $escape($ticket['ticket_no'] ?: ('#' . $ticketId)) ?> · <?= $escape($typeLabels[(string) ($ticket['ticket_type'] ?? '')] ?? 'درخواست') ?> · <?= $escape($status) ?></p></div><a class="button secondary" href="request-center.php">فهرست درخواست‌ها</a></section>
    <div class="notice">این نما در مرحلهٔ انتقال تدریجی، برای مشاهدهٔ مستقل فعال است. عملیات ثبت پاسخ، ارجاع، تغییر وضعیت، لغو و تأیید نهایی فعلاً از صفحهٔ سازگار فعلی انجام می‌شود.</div>
    <div class="detail-grid">
      <section class="card conversation">
        <h2>گفت‌وگو و پیوست‌ها</h2>
        <?php if (!$messages): ?><div class="empty">پیامی برای نمایش وجود ندارد.</div><?php endif; ?>
        <?php foreach ($messages as $message): ?>
          <article class="message <?= (int) ($message['is_internal'] ?? 0) === 1 ? 'internal' : '' ?>">
            <div class="message-head"><strong><?= $escape($message['full_name'] ?: 'کاربر') ?></strong><small><?= $escape($message['created_at']) ?><?= (int) ($message['is_internal'] ?? 0) === 1 ? ' · یادداشت داخلی' : '' ?></small></div>
            <div class="message-body"><?= $escape($message['body']) ?></div>
            <?php foreach ($attachments[(int) $message['id']] ?? [] as $attachment): ?>
              <p><a href="index.php?action=download_attachment&amp;id=<?= (int) $attachment['id'] ?>">پیوست: <?= $escape($attachment['original_name']) ?></a></p>
            <?php endforeach; ?>
          </article>
        <?php endforeach; ?>
      </section>
      <aside class="card sidebar">
        <h2>مشخصات درخواست</h2>
        <dl>
          <dt>ثبت‌کننده</dt><dd><?= $escape($ticket['requester_name']) ?></dd>
          <dt>مسئول</dt><dd><?= $escape($ticket['assignee_name'] ?: 'تخصیص داده نشده') ?></dd>
          <dt>واحد</dt><dd><?= $escape($ticket['department_name'] ?: 'ثبت نشده') ?></dd>
          <dt>حوزه</dt><dd><?= $escape(($ticket['service_group'] ?? '') === 'support' ? 'پشتیبانی' : 'فناوری اطلاعات') ?></dd>
          <dt>خدمت</dt><dd><?= $escape($ticket['service_name'] ?: 'ثبت نشده') ?></dd>
          <dt>دسته‌بندی</dt><dd><?= $escape($ticket['category_name'] ?: 'ثبت نشده') ?></dd>
          <dt>اولویت</dt><dd><?= $escape($priority) ?></dd>
          <dt>ثبت</dt><dd><?= $escape($ticket['created_at']) ?></dd>
          <dt>آخرین تغییر</dt><dd><?= $escape($ticket['updated_at']) ?></dd>
          <dt>مهلت SLA</dt><dd><?= $escape($ticket['due_at'] ?: 'ثبت نشده') ?></dd>
        </dl>
        <p><a class="button" href="index.php?page=ticket&amp;id=<?= $ticketId ?>">ورود به صفحهٔ عملیات و گردش‌کار</a></p>
      </aside>
    </div>
    <?php
}
?>
<?php else: ?>
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
$result = $repo->listVisible($user, $page, 50, $filters);
$pages = max(1, (int) ceil($result['total'] / 50));
$page = min($page, $pages);
$queryParams = ['workspace' => $workspace];
if ($workspace === 'agent') {
    $queryParams['agent_view'] = $agentView;
}
$queryString = http_build_query(array_merge($queryParams, array_filter($filters, static fn ($value): bool => $value !== '')));
?>
<?php if ($workspace === 'agent' && user_can($user, 'queue.view')): ?>
<nav class="workspace-nav" aria-label="نوع صف کارشناس">
<a class="<?= $agentView === 'mine' ? 'active' : '' ?>" href="request-center.php?workspace=agent&amp;agent_view=mine">صف شخصی من</a>
<a class="<?= $agentView === 'team' ? 'active' : '' ?>" href="request-center.php?workspace=agent&amp;agent_view=team">صف گروهی مجاز</a>
</nav>
<?php endif; ?>
<section class="heading"><div><h1><?= $escape($workspaceTitle) ?></h1><p><?= number_format($result['total']) ?> درخواست در محدودهٔ دسترسی این حساب</p></div>
<?php if (user_can($user, 'ticket.create')): ?><a class="button" href="index.php?page=new-ticket">ثبت درخواست جدید</a><?php endif; ?>
</section>
<form method="get" action="request-center.php" class="card" style="padding:14px;margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:end">
<input type="hidden" name="workspace" value="<?= $escape($workspace) ?>">
<?php if ($workspace === 'agent'): ?><input type="hidden" name="agent_view" value="<?= $escape($agentView) ?>"><?php endif; ?>
<label>جست‌وجو<input type="search" name="q" maxlength="100" value="<?= $escape($filters['q']) ?>" placeholder="شماره یا عنوان" style="display:block;padding:9px;border:1px solid var(--line);border-radius:8px"></label>
<label>وضعیت<select name="status" style="display:block;padding:9px;border:1px solid var(--line);border-radius:8px"><option value="">همه</option><?php foreach ($statusLabels as $key => $label): ?><option value="<?= $escape($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= $escape($label) ?></option><?php endforeach; ?></select></label>
<label>اولویت<select name="priority" style="display:block;padding:9px;border:1px solid var(--line);border-radius:8px"><option value="">همه</option><?php foreach ($priorityLabels as $key => $label): ?><option value="<?= $escape($key) ?>" <?= $filters['priority'] === $key ? 'selected' : '' ?>><?= $escape($label) ?></option><?php endforeach; ?></select></label>
<label>حوزه<select name="service_group" style="display:block;padding:9px;border:1px solid var(--line);border-radius:8px"><option value="">همه</option><option value="it" <?= $filters['service_group'] === 'it' ? 'selected' : '' ?>>فناوری اطلاعات</option><option value="support" <?= $filters['service_group'] === 'support' ? 'selected' : '' ?>>پشتیبانی</option></select></label>
<button class="button" type="submit">اعمال فیلتر</button><a class="button secondary" href="<?= $escape($workspaceUrl) ?>">پاک‌کردن</a>
</form>
<section class="card">
<?php if (!$result['rows']): ?><div class="empty">درخواستی در محدودهٔ دسترسی شما وجود ندارد.</div><?php else: ?>
<div style="overflow-x:auto"><table class="table"><thead><tr><th>درخواست</th><th>ثبت‌کننده</th><th>مسئول</th><th>وضعیت</th><th>اولویت</th><th>آخرین تغییر</th></tr></thead><tbody>
<?php foreach ($result['rows'] as $row): ?>
<tr>
<td><a class="subject" href="request-center.php?id=<?= (int) $row['id'] ?>&amp;workspace=<?= $escape(rawurlencode($workspace)) ?><?= $workspace === 'agent' ? '&amp;agent_view=' . $escape(rawurlencode($agentView)) : '' ?>"><?= $escape($row['ticket_no'] ?: ('#' . $row['id'])) ?> · <?= $escape($row['subject']) ?></a><small class="meta"><?= $escape($typeLabels[(string) ($row['ticket_type'] ?? '')] ?? 'درخواست') ?> · <?= $escape(($row['service_group'] ?? '') === 'support' ? 'پشتیبانی' : 'فناوری اطلاعات') ?></small></td>
<td><?= $escape($row['requester_name']) ?></td><td><?= $escape($row['assignee_name'] ?: 'بدون مسئول') ?></td>
<td><span class="status"><?= $escape($statusLabels[(string) $row['status']] ?? $row['status']) ?></span></td>
<td><span class="priority"><?= $escape($priorityLabels[(string) $row['priority']] ?? $row['priority']) ?></span></td>
<td><?= $escape($row['updated_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<?php if ($pages > 1): ?><nav class="pagination" aria-label="صفحه‌بندی"><?php for ($i=max(1,$page-3);$i<=min($pages,$page+3);$i++): ?><?php if ($i === $page): ?><span class="current"><?= $i ?></span><?php else: ?><a href="request-center.php?<?= $escape($queryString) ?><?= $queryString !== "" ? "&amp;" : "" ?>p=<?= $i ?>"><?= $i ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; ?>
</section>
<?php endif; ?>
</main>
</body>
</html>
