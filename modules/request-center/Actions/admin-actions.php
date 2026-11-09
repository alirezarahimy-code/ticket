<?php
declare(strict_types=1);

/** Request Center primary-admin configuration actions. */
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

if ($action === 'save_rc_work_hours') {
    $admin = require_login();
    if (!user_is_primary_admin($admin)) {
        throw new RuntimeException('فقط ادمین اصلی مرکز درخواست‌ها می‌تواند تقویم کاری SLA را تغییر دهد.');
    }

    $startRaw = post_value('work_start_hour', '8');
    $endRaw = post_value('work_end_hour', '16');
    if (!preg_match('/^(?:[0-9]|1[0-9]|2[0-3])$/', $startRaw)) {
        throw new RuntimeException('ساعت شروع کار باید عدد صحیح بین ۰ تا ۲۳ باشد.');
    }
    if (!preg_match('/^(?:[1-9]|1[0-9]|2[0-4])$/', $endRaw) || (int) $endRaw <= (int) $startRaw) {
        throw new RuntimeException('ساعت پایان باید عدد صحیح، بزرگ‌تر از شروع و حداکثر ۲۴ باشد.');
    }
    $weekendDays = [];
    foreach ((array) ($_POST['weekend_days'] ?? []) as $rawDay) {
        if (!is_scalar($rawDay) || !preg_match('/^[1-7]$/', (string) $rawDay)) {
            throw new RuntimeException('روز تعطیل هفتگی معتبر نیست.');
        }
        $day = (int) $rawDay;
        if (!in_array($day, $weekendDays, true)) {
            $weekendDays[] = $day;
        }
    }
    sort($weekendDays);
    if (count($weekendDays) >= 7) {
        throw new RuntimeException('دست‌کم یک روز هفته باید روز کاری بماند.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        save_setting('sla.work_start_hour', $startRaw);
        save_setting('sla.work_end_hour', $endRaw);
        save_setting('sla.weekend_days', implode(',', $weekendDays));
        save_audit_required((int) $admin['id'], 'request_center_work_hours_updated', null, [
            'work_start_hour' => (int) $startRaw,
            'work_end_hour' => (int) $endRaw,
            'weekend_days' => implode(',', $weekendDays),
        ]);
        db_commit_or_fail('request-center:save_work_hours');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            db()->rollBack();
        }
        throw $exception;
    }
    if (function_exists('setting_forget_cache')) {
        setting_forget_cache();
    }
    if (function_exists('sla_business_hours_forget_cache')) {
        sla_business_hours_forget_cache();
    }
    flash('success', 'تقویم کاری ذخیره شد. موعدهای ذخیره‌شدهٔ درخواست‌های موجود تغییر نکردند.');
    redirect('request-center.php?workspace=admin#sla-work-hours');
}

if ($action === 'create_ola_policy') {
    $admin = require_login();
    if (!user_is_primary_admin($admin)) {
        throw new RuntimeException('فقط ادمین اصلی مرکز درخواست‌ها می‌تواند سیاست OLA ایجاد کند.');
    }

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
    if ($name === '' || mb_strlen($name, 'UTF-8') > 190 || $priority === '') {
        throw new RuntimeException('نام و اولویت سیاست OLA معتبر نیست.');
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

    $isActive = !empty($_POST['is_active']) ? 1 : 0;
    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Keep active (department, priority) policies unique under concurrent
        // admin submissions, using the same catalogue lock as policy updates.
        $pdo->query('SELECT id FROM ola_policies ORDER BY id FOR UPDATE')->fetchAll(PDO::FETCH_COLUMN);
        if ($isActive === 1) {
            $duplicate = $pdo->prepare(
                'SELECT id FROM ola_policies WHERE is_active = 1 AND priority = ? '
                . 'AND ((department_id IS NULL AND ? IS NULL) OR department_id = ?) LIMIT 1'
            );
            $duplicate->execute([$priority, $departmentId, $departmentId]);
            if ($duplicate->fetchColumn()) {
                throw new RuntimeException('برای این حوزه و اولویت، سیاست فعال دیگری وجود دارد. ابتدا آن را غیرفعال یا اصلاح کنید.');
            }
        }

        $insert = $pdo->prepare(
            'INSERT INTO ola_policies (name, department_id, priority, response_minutes, resolution_minutes, '
            . 'escalation_1_minutes, escalation_2_minutes, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([
            $name, $departmentId, $priority, $minutes['response_minutes'], $minutes['resolution_minutes'],
            $minutes['escalation_1_minutes'], $minutes['escalation_2_minutes'], $isActive, (int) $admin['id'],
        ]);
        $policyId = (int) $pdo->lastInsertId();
        save_audit_required((int) $admin['id'], 'ola_policy_created', null, [
            'policy_id' => $policyId,
            'name' => $name,
            'department_id' => $departmentId,
            'priority' => $priority,
            'response_minutes' => $minutes['response_minutes'],
            'resolution_minutes' => $minutes['resolution_minutes'],
            'escalation_1_minutes' => $minutes['escalation_1_minutes'],
            'escalation_2_minutes' => $minutes['escalation_2_minutes'],
            'is_active' => $isActive,
        ]);
        db_commit('request-center.php');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            db_rollback('request-center.php');
        }
        throw $exception;
    }

    flash('success', 'سیاست OLA جدید ایجاد شد.');
    redirect('request-center.php?workspace=admin#ola-policies');
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
        // Serialize policy edits before checking the active (department, priority)
        // uniqueness rule. Locking only the edited row allows two administrators
        // editing different rows to activate duplicate policies concurrently.
        // The policy catalogue is small and this action is infrequent, so a
        // deterministic row lock is preferable to a schema/index change.
        $lock = $pdo->query(
            'SELECT id, name, department_id, priority, response_minutes, resolution_minutes, '
            . 'escalation_1_minutes, escalation_2_minutes, is_active '
            . 'FROM ola_policies ORDER BY id FOR UPDATE'
        );
        $lockedPolicies = $lock->fetchAll(PDO::FETCH_ASSOC);
        $before = null;
        foreach ($lockedPolicies as $lockedPolicy) {
            if ((int) $lockedPolicy['id'] === $policyId) {
                $before = $lockedPolicy;
                break;
            }
        }
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
