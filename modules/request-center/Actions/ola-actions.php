<?php
declare(strict_types=1);

/** Request Center — «سیاست‌های OLA» POST actions.
 *
 * منطق این اکشن‌ها پیش‌تر داخل ola.php بود؛ با تصمیم مالک ۲۰۲۶-۱۱-۱۴ به ماژول منتقل شد.
 * مشاهده برای دارندگان «ola.view» و ثبت/ویرایش برای دارندگان «ola.manage» (بدون تغییر کد دسترسی).
 * قرارداد خطا: استثنا به مرز خطای مرکز درخواست‌ها برمی‌گردد و به صفحهٔ OLA هدایت می‌شود.
 */
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

// گیت فقط برای اکشن‌های خود OLA؛ رجیستری RC همهٔ فایل‌های اکشن را بارگذاری می‌کند.
$olaActions = ['save_ola_policy', 'toggle_ola_policy'];
if (in_array((string) ($action ?? ''), $olaActions, true)
    && function_exists('user_can') && !user_can($user, 'ola.manage')) {
    throw new RuntimeException('دسترسی ویرایش OLA برای نقش شما مجاز نیست.');
}

if ($action === 'save_ola_policy') {
    $policyId = (int) ($_POST['policy_id'] ?? 0);
    $name = post_value('name');
    $priority = post_value('priority', 'normal');
    $allowedPriorities = ['normal', 'urgent', 'critical'];
    $departmentId = (int) ($_POST['department_id'] ?? 0) ?: null;
    $response = max(1, (int) ($_POST['response_minutes'] ?? 0));
    $resolution = max(1, (int) ($_POST['resolution_minutes'] ?? 0));
    $escalation1 = max(1, (int) ($_POST['escalation_1_minutes'] ?? 0));
    $escalation2 = max(1, (int) ($_POST['escalation_2_minutes'] ?? 0));
    if ($name === '' || !in_array($priority, $allowedPriorities, true) || $response > $resolution || $resolution > $escalation1 || $escalation1 > $escalation2) {
        throw new RuntimeException('نام و بازه‌های OLA را کامل کنید؛ ترتیب باید پاسخ ≤ حل ≤ Escalation ۱ ≤ Escalation ۲ باشد.');
    }
    if ($policyId > 0) {
        db()->prepare('UPDATE ola_policies SET name = ?, department_id = ?, priority = ?, response_minutes = ?, resolution_minutes = ?, escalation_1_minutes = ?, escalation_2_minutes = ?, is_active = 1, updated_at = NOW() WHERE id = ?')->execute([$name, $departmentId, $priority, $response, $resolution, $escalation1, $escalation2, $policyId]);
        save_audit_required((int) $user['id'], 'ola_policy_updated', null, ['policy_id' => $policyId, 'priority' => $priority]);
        flash('success', 'سیاست OLA ویرایش شد.');
    } else {
        db()->prepare('INSERT INTO ola_policies (name, department_id, priority, response_minutes, resolution_minutes, escalation_1_minutes, escalation_2_minutes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute([$name, $departmentId, $priority, $response, $resolution, $escalation1, $escalation2, $user['id']]);
        save_audit_required((int) $user['id'], 'ola_policy_created', null, ['priority' => $priority]);
        flash('success', 'سیاست OLA ساخته شد.');
    }
    redirect('request-center.php?page=ola');
}

if ($action === 'toggle_ola_policy') {
    $policyId = (int) ($_POST['policy_id'] ?? 0);
    db()->prepare('UPDATE ola_policies SET is_active = 1 - is_active, updated_at = NOW() WHERE id = ?')->execute([$policyId]);
    save_audit_required((int) $user['id'], 'ola_policy_toggled', null, ['policy_id' => $policyId]);
    flash('success', 'وضعیت سیاست OLA تغییر کرد.');
    redirect('request-center.php?page=ola');
}
