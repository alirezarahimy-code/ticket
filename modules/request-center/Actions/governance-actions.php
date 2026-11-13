<?php
declare(strict_types=1);

/** Request Center — «تغییر و مشکل» (ITSM Governance) POST actions.
 *
 * منطق این اکشن‌ها پیش‌تر داخل governance.php بود؛ با تصمیم مالک ۲۰۲۶-۱۱-۱۴ به ماژول منتقل شد.
 * قرارداد خطا: همان مرز خطای مرکز درخواست‌ها (lib/http-post + post-handler):
 * استثناهای RuntimeException با همان پیام به کاربر flash می‌شوند و به صفحهٔ تغییر و مشکل برمی‌گردند.
 */
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../lib/governance.php';

// رجیستری RC همهٔ فایل‌های اکشن را بدون قید بارگذاری می‌کند؛ پس گیت دسترسی فقط برای اکشن‌های
// خودِ تغییر و مشکل اعمال می‌شود تا اکشن‌های نامرتبط ماژول را بی‌خود رد نشود.
$governanceActions = [
    'create_change', 'update_change', 'submit_change', 'cab_decision', 'change_transition',
    'create_problem', 'update_problem', 'link_governance_ticket',
];
if (in_array((string) ($action ?? ''), $governanceActions, true)
    && function_exists('user_can') && !user_can($user, 'governance.manage')) {
    throw new RuntimeException('دسترسی ویرایش تغییر و مشکل برای نقش شما مجاز نیست.');
}

if ($action === 'create_change') {
    $title = gov_post('title');
    $description = gov_post('description');
    $ticketId = (int) ($_POST['ticket_id'] ?? 0);
    $ticket = $ticketId > 0 ? gov_fetch_ticket($ticketId) : null;
    if ($title === '' || $description === '') {
        throw new RuntimeException('عنوان و شرح تغییر الزامی است.');
    }
    if ($ticketId > 0 && (!$ticket || !gov_can_view_ticket($ticket, $user))) {
        throw new RuntimeException('درخواست مرجع پیدا نشد یا دسترسی ندارید.');
    }
    $departmentId = $ticket ? (int) ($ticket['department_id'] ?: 0) : (int) ($user['department_id'] ?? 0);
    if (in_array((string) $user['role'], ['manager', 'support_manager'], true)) {
        if ((int) ($user['department_id'] ?? 0) <= 0 || $departmentId <= 0 || $departmentId !== (int) $user['department_id']) {
            throw new RuntimeException('مدیر واحد بدون معاونت معتبر نمی‌تواند Change ثبت کند.');
        }
    }
    $start = gov_parse_datetime(gov_post('planned_start'));
    $end = gov_parse_datetime(gov_post('planned_end'));
    if (gov_post('planned_start') !== '' && $start === null || gov_post('planned_end') !== '' && $end === null) {
        throw new RuntimeException('تاریخ زمان‌بندی معتبر نیست.');
    }
    if ($start && $end && $end < $start) {
        throw new RuntimeException('پایان برنامه تغییر باید بعد از شروع آن باشد.');
    }
    db()->beginTransaction();
    db()->prepare('INSERT INTO change_records (change_number, title, description, justification, risk_level, impact, planned_start, planned_end, department_id, requester_id, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute(['TMP-' . bin2hex(random_bytes(8)), $title, $description, gov_post('justification') ?: null, gov_choice(gov_post('risk_level', 'medium'), ['low', 'medium', 'high', 'critical'], 'medium'), gov_post('impact') ?: null, $start, $end, $departmentId ?: null, $user['id'], $user['id']]);
    $changeId = (int) db()->lastInsertId();
    db()->prepare('UPDATE change_records SET change_number = ? WHERE id = ?')->execute([gov_change_number($changeId), $changeId]);
    if ($ticketId > 0) {
        db()->prepare('INSERT INTO change_ticket_links (change_id, ticket_id, created_by) VALUES (?, ?, ?)')->execute([$changeId, $ticketId, $user['id']]);
    }
    save_audit_required((int) $user['id'], 'change_created', $ticketId > 0 ? $ticketId : null, ['change_id' => $changeId, 'change_number' => gov_change_number($changeId)]);
    db()->commit();
    flash('success', 'درخواست تغییر ' . gov_change_number($changeId) . ' ساخته شد.');
    redirect('request-center.php?page=governance&change_id=' . $changeId);
}

if ($action === 'update_change') {
    $changeId = (int) ($_POST['change_id'] ?? 0);
    $change = gov_fetch_change($changeId);
    if (!$change || !gov_can_view_record($change, $user) || !in_array($change['status'], ['draft', 'rejected'], true)) {
        throw new RuntimeException('فقط پیش‌نویس یا تغییر ردشده قابل اصلاح است.');
    }
    $title = gov_post('title');
    $description = gov_post('description');
    $start = gov_parse_datetime(gov_post('planned_start'));
    $end = gov_parse_datetime(gov_post('planned_end'));
    if ($title === '' || $description === '') {
        throw new RuntimeException('عنوان و شرح تغییر الزامی است.');
    }
    if (gov_post('planned_start') !== '' && $start === null || gov_post('planned_end') !== '' && $end === null || ($start && $end && $end < $start)) {
        throw new RuntimeException('بازه زمان‌بندی تغییر معتبر نیست.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE change_records SET title = ?, description = ?, justification = ?, risk_level = ?, impact = ?, planned_start = ?, planned_end = ?, updated_at = NOW() WHERE id = ? AND status IN ("draft", "rejected")')->execute([$title, $description, gov_post('justification') ?: null, gov_choice(gov_post('risk_level', 'medium'), ['low', 'medium', 'high', 'critical'], 'medium'), gov_post('impact') ?: null, $start, $end, $changeId]);
        save_audit_required((int) $user['id'], 'change_updated', null, ['change_id' => $changeId, 'change_number' => $change['change_number']]);
        $pdo->commit();
    } catch (Throwable $updateError) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $updateError;
    }
    flash('success', 'اطلاعات تغییر اصلاح شد.');
    redirect('request-center.php?page=governance&change_id=' . $changeId);
}

if ($action === 'submit_change') {
    $changeId = (int) ($_POST['change_id'] ?? 0);
    $change = gov_fetch_change($changeId);
    if (!$change || !gov_can_view_record($change, $user) || !in_array($change['status'], ['draft', 'rejected'], true)) {
        throw new RuntimeException('این تغییر قابل ارسال برای CAB نیست.');
    }
    db()->beginTransaction();
    $submit = db()->prepare('UPDATE change_records SET status = "submitted", cab_decided_by = NULL, cab_decided_at = NULL, cab_note = NULL, updated_at = NOW() WHERE id = ? AND status IN ("draft", "rejected")');
    $submit->execute([$changeId]);
    if ($submit->rowCount() !== 1) {
        throw new RuntimeException('وضعیت تغییر هم‌زمان تغییر کرده است؛ صفحه را تازه‌سازی کنید.');
    }
    db()->prepare('INSERT INTO change_approvals (change_id, approver_id, decision, note) VALUES (?, ?, "submitted", ?)')->execute([$changeId, $user['id'], gov_post('note') ?: null]);
    save_audit_required((int) $user['id'], 'change_submitted', null, ['change_id' => $changeId, 'change_number' => $change['change_number']]);
    db()->commit();
    flash('success', 'تغییر برای بررسی CAB ارسال شد.');
    redirect('request-center.php?page=governance&change_id=' . $changeId);
}

if ($action === 'cab_decision') {
    $cabUser = require_supervisor();
    $changeId = (int) ($_POST['change_id'] ?? 0);
    $decision = gov_choice(gov_post('decision'), ['approved', 'rejected'], 'rejected');
    $change = gov_fetch_change($changeId);
    if (!$change || !gov_can_view_record($change, $cabUser) || !in_array($change['status'], ['submitted', 'cab_review'], true)) {
        throw new RuntimeException('این تغییر در صف تصمیم CAB نیست.');
    }
    $note = gov_post('note');
    db()->beginTransaction();
    $cabUpdate = db()->prepare('UPDATE change_records SET status = ?, cab_decided_by = ?, cab_decided_at = NOW(), cab_note = ?, updated_at = NOW() WHERE id = ? AND status IN ("submitted", "cab_review")');
    $cabUpdate->execute([$decision, $cabUser['id'], $note ?: null, $changeId]);
    if ($cabUpdate->rowCount() !== 1) {
        throw new RuntimeException('تصمیم CAB هم‌زمان ثبت شده است؛ صفحه را تازه‌سازی کنید.');
    }
    db()->prepare('INSERT INTO change_approvals (change_id, approver_id, decision, note) VALUES (?, ?, ?, ?)')->execute([$changeId, $cabUser['id'], $decision, $note ?: null]);
    save_audit_required((int) $cabUser['id'], 'change_cab_' . $decision, null, ['change_id' => $changeId, 'change_number' => $change['change_number']]);
    gov_notify_linked_tickets('change_ticket_links', 'change_id', $changeId, 'change_cab_' . $decision, 'تصمیم CAB برای تغییر', 'تغییر ' . $change['change_number'] . ' به وضعیت «' . gov_change_status_label($decision) . '» رسید.', (int) $cabUser['id']);
    db()->commit();
    flash($decision === 'approved' ? 'success' : 'info', $decision === 'approved' ? 'تغییر توسط CAB تأیید شد.' : 'تغییر توسط CAB رد شد.');
    redirect('request-center.php?page=governance&change_id=' . $changeId);
}

if ($action === 'change_transition') {
    $changeId = (int) ($_POST['change_id'] ?? 0);
    $transition = gov_choice(gov_post('transition'), ['scheduled', 'implemented', 'rolled_back', 'closed'], 'scheduled');
    $change = gov_fetch_change($changeId);
    $allowed = ['scheduled' => ['approved'], 'implemented' => ['scheduled'], 'rolled_back' => ['scheduled', 'implemented'], 'closed' => ['implemented', 'rolled_back']];
    if (!$change || !gov_can_view_record($change, $user) || !in_array($change['status'], $allowed[$transition], true)) {
        throw new RuntimeException('انتقال انتخاب‌شده برای وضعیت فعلی تغییر مجاز نیست.');
    }
    $note = gov_post('implementation_note');
    db()->beginTransaction();
    $allowedStatuses = implode(', ', array_map(static fn (string $status): string => '"' . $status . '"', $allowed[$transition]));
    $transitionUpdate = db()->prepare('UPDATE change_records SET status = ?, implementation_note = ?, updated_at = NOW() WHERE id = ? AND status IN (' . $allowedStatuses . ')');
    $transitionUpdate->execute([$transition, $note ?: null, $changeId]);
    if ($transitionUpdate->rowCount() !== 1) {
        throw new RuntimeException('وضعیت تغییر هم‌زمان عوض شده است؛ صفحه را تازه‌سازی کنید.');
    }
    db()->prepare('INSERT INTO change_approvals (change_id, approver_id, decision, note) VALUES (?, ?, ?, ?)')->execute([$changeId, $user['id'], $transition, $note ?: null]);
    save_audit_required((int) $user['id'], 'change_' . $transition, null, ['change_id' => $changeId, 'change_number' => $change['change_number']]);
    gov_notify_linked_tickets('change_ticket_links', 'change_id', $changeId, 'change_' . $transition, 'به‌روزرسانی تغییر', 'تغییر ' . $change['change_number'] . ' به وضعیت «' . gov_change_status_label($transition) . '» رسید.', (int) $user['id']);
    db()->commit();
    flash('success', 'وضعیت تغییر به‌روزرسانی شد.');
    redirect('request-center.php?page=governance&change_id=' . $changeId);
}

if ($action === 'create_problem') {
    $title = gov_post('title');
    $description = gov_post('description');
    $ticketId = (int) ($_POST['ticket_id'] ?? 0);
    $ticket = $ticketId > 0 ? gov_fetch_ticket($ticketId) : null;
    if ($title === '' || $description === '') {
        throw new RuntimeException('عنوان و شرح مشکل الزامی است.');
    }
    if ($ticketId > 0 && (!$ticket || !gov_can_view_ticket($ticket, $user))) {
        throw new RuntimeException('درخواست مرجع پیدا نشد یا دسترسی ندارید.');
    }
    $departmentId = $ticket ? (int) ($ticket['department_id'] ?: 0) : (int) ($user['department_id'] ?? 0);
    if (in_array((string) $user['role'], ['manager', 'support_manager'], true)) {
        if ((int) ($user['department_id'] ?? 0) <= 0 || $departmentId <= 0 || $departmentId !== (int) $user['department_id']) {
            throw new RuntimeException('مدیر واحد بدون معاونت معتبر نمی‌تواند Problem ثبت کند.');
        }
    }
    $knownError = !empty($_POST['known_error']) ? 1 : 0;
    db()->beginTransaction();
    db()->prepare('INSERT INTO problem_records (problem_number, title, description, root_cause, workaround, known_error, priority, status, department_id, created_by, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute(['TMP-' . bin2hex(random_bytes(8)), $title, $description, gov_post('root_cause') ?: null, gov_post('workaround') ?: null, $knownError, gov_choice(gov_post('priority', 'normal'), ['normal', 'urgent', 'critical'], 'normal'), $knownError ? 'known_error' : 'open', $departmentId ?: null, $user['id'], $user['id']]);
    $problemId = (int) db()->lastInsertId();
    db()->prepare('UPDATE problem_records SET problem_number = ? WHERE id = ?')->execute([gov_problem_number($problemId), $problemId]);
    if ($ticketId > 0) {
        db()->prepare('INSERT INTO problem_ticket_links (problem_id, ticket_id, created_by) VALUES (?, ?, ?)')->execute([$problemId, $ticketId, $user['id']]);
    }
    save_audit_required((int) $user['id'], 'problem_created', $ticketId > 0 ? $ticketId : null, ['problem_id' => $problemId, 'problem_number' => gov_problem_number($problemId)]);
    db()->commit();
    flash('success', 'رکورد مشکل ' . gov_problem_number($problemId) . ' ساخته شد.');
    redirect('request-center.php?page=governance&problem_id=' . $problemId);
}

if ($action === 'update_problem') {
    $problemId = (int) ($_POST['problem_id'] ?? 0);
    $problem = gov_fetch_problem($problemId);
    $status = gov_choice(gov_post('status'), ['open', 'investigation', 'known_error', 'resolved', 'closed'], 'open');
    if (!$problem || !gov_can_view_record($problem, $user)) {
        throw new RuntimeException('مشکل پیدا نشد یا دسترسی ندارید.');
    }
    if ($status === 'closed' && $problem['status'] !== 'resolved') {
        throw new RuntimeException('بستن مشکل فقط پس از حل‌شدن آن مجاز است.');
    }
    $knownError = $status === 'known_error' || !empty($_POST['known_error']) ? 1 : 0;
    if ($knownError === 1 && in_array($status, ['open', 'investigation'], true)) {
        $status = 'known_error';
    }
    $resolvedAt = in_array($status, ['resolved', 'closed'], true) ? ($problem['resolved_at'] ?: date('Y-m-d H:i:s')) : null;
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE problem_records SET root_cause = ?, workaround = ?, known_error = ?, priority = ?, status = ?, owner_id = ?, resolved_at = ?, updated_at = NOW() WHERE id = ?')->execute([gov_post('root_cause') ?: null, gov_post('workaround') ?: null, $knownError, gov_choice(gov_post('priority', 'normal'), ['normal', 'urgent', 'critical'], 'normal'), $status, $user['id'], $resolvedAt, $problemId]);
        save_audit_required((int) $user['id'], 'problem_updated', null, ['problem_id' => $problemId, 'problem_number' => $problem['problem_number'], 'status' => $status]);
        gov_notify_linked_tickets('problem_ticket_links', 'problem_id', $problemId, 'problem_updated', 'مشکل مرتبط به‌روزرسانی شد', 'رکورد مشکل ' . $problem['problem_number'] . ' به وضعیت «' . gov_problem_status_label($status) . '» رسید.', (int) $user['id']);
        $pdo->commit();
    } catch (Throwable $updateError) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $updateError;
    }
    flash('success', 'رکورد مشکل به‌روزرسانی شد.');
    redirect('request-center.php?page=governance&problem_id=' . $problemId);
}

if ($action === 'link_governance_ticket') {
    $kind = gov_choice(gov_post('kind'), ['change', 'problem'], 'problem');
    $recordId = (int) ($_POST['record_id'] ?? 0);
    $ticketId = (int) ($_POST['ticket_id'] ?? 0);
    $record = $kind === 'change' ? gov_fetch_change($recordId) : gov_fetch_problem($recordId);
    $ticket = gov_fetch_ticket($ticketId);
    if (!$record || !$ticket || !gov_can_view_record($record, $user) || !gov_can_view_ticket($ticket, $user)) {
        throw new RuntimeException('رکورد یا درخواست برای اتصال معتبر نیست.');
    }
    $table = $kind === 'change' ? 'change_ticket_links' : 'problem_ticket_links';
    $column = $kind === 'change' ? 'change_id' : 'problem_id';
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT IGNORE INTO ' . $table . ' (' . $column . ', ticket_id, created_by) VALUES (?, ?, ?)')->execute([$recordId, $ticketId, $user['id']]);
        save_audit_required((int) $user['id'], $kind . '_ticket_linked', $ticketId, [$column => $recordId, 'ticket_id' => $ticketId]);
        $pdo->commit();
    } catch (Throwable $linkError) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $linkError;
    }
    flash('success', 'درخواست به رکورد مدیریتی متصل شد.');
    redirect('request-center.php?page=governance&' . ($kind === 'change' ? 'change_id' : 'problem_id') . '=' . $recordId);
}
