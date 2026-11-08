<?php
declare(strict_types=1);

/** Request Center primary-admin configuration actions. */
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

if ($action === 'update_ola_policy') {
    $admin = require_login();
    if (!user_is_primary_admin($admin)) {
        throw new RuntimeException('فقط ادمین اصلی مرکز درخواست‌ها می‌تواند سیاست OLA را تغییر دهد.');
    }

    $policyId = (int) post_value('policy_id', '0');
    $name = post_value('policy_name');
    $priority = valid_choice(post_value('priority'), ['critical', 'urgent', 'normal'], '');
    $departmentId = (int) post_value('department_id', '0') ?: null;
    $minutes = [];
    foreach (['response_minutes', 'resolution_minutes', 'escalation_1_minutes', 'escalation_2_minutes'] as $field) {
        $raw = post_value($field, '');
        if (!preg_match('/^[1-9][0-9]{0,5}$/', $raw)) {
            throw new RuntimeException('همه مهلت‌های OLA باید عدد صحیح بین ۱ تا ۵۲۵۶۰۰ دقیقه باشند.');
        }
        $minutes[$field] = (int) $raw;
    }
    if ($policyId <= 0 || $name === '' || mb_strlen($name, 'UTF-8') > 150 || $priority === '') {
        throw new RuntimeException('شناسه، نام و اولویت سیاست OLA معتبر نیست.');
    }
    if ($minutes['escalation_2_minutes'] < $minutes['escalation_1_minutes']) {
        throw new RuntimeException('مهلت ارجاع دوم نمی‌تواند از ارجاع اول کوتاه‌تر باشد.');
    }
    if ($departmentId !== null) {
        $departmentCheck = db()->prepare('SELECT id FROM departments WHERE id = ? LIMIT 1');
        $departmentCheck->execute([$departmentId]);
        if (!$departmentCheck->fetchColumn()) {
            throw new RuntimeException('معاونت انتخاب‌شده وجود ندارد.');
        }
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $lock = $pdo->prepare('SELECT id, name, department_id, priority, response_minutes, resolution_minutes, escalation_1_minutes, escalation_2_minutes, is_active FROM ola_policies WHERE id = ? FOR UPDATE');
        $lock->execute([$policyId]);
        $before = $lock->fetch(PDO::FETCH_ASSOC);
        if (!$before) {
            throw new RuntimeException('سیاست OLA موردنظر پیدا نشد.');
        }
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        if ($isActive === 1) {
            $duplicate = $pdo->prepare(
                'SELECT id FROM ola_policies WHERE is_active = 1 AND priority = ? '
                . 'AND ((department_id IS NULL AND ? IS NULL) OR department_id = ?) AND id <> ? LIMIT 1'
            );
            $duplicate->execute([$priority, $departmentId, $departmentId, $policyId]);
            if ($duplicate->fetchColumn()) {
                throw new RuntimeException('برای این حوزه و اولویت، سیاست فعال دیگری وجود دارد. ابتدا آن را غیرفعال یا اصلاح کنید.');
            }
        }
        $update = $pdo->prepare(
            'UPDATE ola_policies SET name = ?, department_id = ?, priority = ?, response_minutes = ?, '
            . 'resolution_minutes = ?, escalation_1_minutes = ?, escalation_2_minutes = ?, is_active = ? WHERE id = ?'
        );
        $update->execute([
            $name, $departmentId, $priority, $minutes['response_minutes'], $minutes['resolution_minutes'],
            $minutes['escalation_1_minutes'], $minutes['escalation_2_minutes'], $isActive, $policyId,
        ]);
        save_audit_required((int) $admin['id'], 'ola_policy_updated', null, [
            'policy_id' => $policyId,
            'before' => $before,
            'after' => [
                'name' => $name, 'department_id' => $departmentId, 'priority' => $priority,
                'response_minutes' => $minutes['response_minutes'],
                'resolution_minutes' => $minutes['resolution_minutes'],
                'escalation_1_minutes' => $minutes['escalation_1_minutes'],
                'escalation_2_minutes' => $minutes['escalation_2_minutes'], 'is_active' => $isActive,
            ],
        ]);
        db_commit('request-center.php');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            db_rollback('request-center.php');
        }
        throw $exception;
    }
    flash('success', 'سیاست OLA ذخیره شد. موعدهای ثبت‌شدهٔ درخواست‌های موجود تغییر نکردند.');
    redirect('request-center.php?workspace=admin#ola-policies');
}
