<?php

declare(strict_types=1);

/**
 * actions/org-actions.php — ۱۳ اکشن POST چارت سازمانی (فاز ۲ بازسازی، برش ۱ — نسخهٔ ۱.۶۲.۱۲).
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require فراخوانی می‌شود و
 * در همان حوزهٔ متغیرها اجرا می‌شود ($action، $user و ...). متن اکشن‌ها بدون هیچ
 * تغییری (بایت‌به‌بایت) از index.php منتقل شده است — اثبات در tests و CHANGE-REPORT.
 * اکشن‌ها: save_org_node، undo_org_action، assign_org_manager، remove_org_manager،
 * remove_org_experts، save_org_assign_users، reset_org_structure، save_org_ceo،
 * save_org_unit، delete_org_unit، save_user_org، repair_org_manager_admin_roles،
 * repair_org_manager_support_roles.
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: ساختار سازمانی (چارت) ─────
        if ($action === 'save_org_node') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ساختار سازمانی را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $nodeType = valid_choice(post_value('node_type'), ['ceo', 'deputy', 'department', 'member', 'liaison'], 'department');
            $ceoId = org_ceo_unit_id();

            if ($nodeType === 'ceo') {
                $ceoUserId = max(0, (int) post_value('ceo_user_id', '0'));
                if ($ceoUserId > 0) {
                    $check = db()->prepare('SELECT id FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
                    $check->execute([$ceoUserId]);
                    if (!$check->fetchColumn()) {
                        throw new RuntimeException('کاربر مدیرعامل معتبر نیست.');
                    }
                }
                db()->prepare('UPDATE org_units SET manager_user_id = ? WHERE id = ?')->execute([$ceoUserId > 0 ? $ceoUserId : null, $ceoId]);
                db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([$ceoId]);
                if ($ceoUserId > 0) {
                    db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, 1)')->execute([$ceoId, $ceoUserId]);
                    db()->prepare('UPDATE users SET role = "manager" WHERE id = ? AND role IN ("user", "agent")')->execute([$ceoUserId]);
                }
                org_record_undo('تغییر مدیرعامل', $orgUndoSnapshot);
                save_audit((int) $orgAdmin['id'], 'org_ceo_saved', null, ['user_id' => $ceoUserId]);
                flash('success', 'مدیرعامل ذخیره شد.');
                redirect('index.php?page=organization#unit-' . $ceoId);
            }

            if ($nodeType === 'member') {
                $targetUserId = max(0, (int) post_value('user_id', '0'));
                $unitId = max(0, (int) post_value('member_unit_id', '0'));
                $managerId = max(0, (int) post_value('member_manager_id', '0'));
                $target = db()->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
                $target->execute([$targetUserId]);
                if (!$target->fetchColumn()) {
                    throw new RuntimeException('کاربر یافت نشد.');
                }
                if ($unitId > 0 && org_unit_by_id($unitId) === null) {
                    throw new RuntimeException('اداره انتخابی یافت نشد.');
                }
                if ($managerId === 0 && $unitId > 0) {
                    $memberUnit = org_unit_by_id($unitId);
                    $managerId = (int) ($memberUnit['manager_user_id'] ?? 0);
                }
                if ($managerId === $targetUserId) {
                    $managerId = 0;
                }
                db()->prepare('UPDATE users SET org_unit_id = ?, manager_user_id = ? WHERE id = ?')
                    ->execute([$unitId > 0 ? $unitId : null, $managerId > 0 ? $managerId : null, $targetUserId]);
                org_record_undo('تغییر عضویت سازمانی کاربر', $orgUndoSnapshot);
                save_audit((int) $orgAdmin['id'], 'user_org_updated', null, ['user_id' => $targetUserId, 'unit_id' => $unitId, 'manager_user_id' => $managerId]);
                flash('success', $unitId > 0 ? 'کارشناس به اداره وصل شد.' : 'کارشناس از اداره جدا شد.');
                redirect('index.php?page=organization#user-' . $targetUserId);
            }

            $unitId = max(0, (int) post_value('unit_id', '0'));
            $name = trim((string) post_value('unit_name', ''));
            $code = trim((string) post_value('unit_code', ''));
            if ($name === '') {
                throw new RuntimeException('نام واحد الزامی است.');
            }
            $chartSide = null;
            if ($nodeType === 'deputy') {
                $parentId = $ceoId;
            } elseif ($nodeType === 'liaison') {
                // ۱.۶۲.۱۰: واحد مستقل (نمایندگی) کنار مدیرعامل؛ والد ندارد و زیرمجموعهٔ مدیرعامل نیست.
                $parentId = 0;
                $chartSide = valid_choice(post_value('chart_side'), ['right', 'left'], 'right');
                $sideTaken = db()->prepare('SELECT id FROM org_units WHERE unit_type = "liaison" AND chart_side = ? AND is_active = 1 AND id <> ? LIMIT 1');
                $sideTaken->execute([$chartSide, $unitId]);
                if ($sideTaken->fetchColumn()) {
                    throw new RuntimeException('در این سمت مدیرعامل قبلاً یک واحد مستقل تعریف شده است؛ ابتدا آن را ویرایش یا حذف کنید.');
                }
            } else {
                $parentId = max(0, (int) post_value('parent_id', '0'));
                $parentUnit = $parentId > 0 ? org_unit_by_id($parentId) : null;
                if ($parentUnit === null || !in_array((string) $parentUnit['unit_type'], ['deputy', 'ceo'], true)) {
                    throw new RuntimeException('اداره باید زیر یک معاونت تعریف شود.');
                }
            }
            $managerId = max(0, (int) post_value('unit_manager_id', '0'));
            if ($managerId > 0) {
                $check = db()->prepare('SELECT id FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
                $check->execute([$managerId]);
                if (!$check->fetchColumn()) {
                    throw new RuntimeException('مدیر انتخابی معتبر نیست.');
                }
            }
            $isPrimary = isset($_POST['is_primary_admin']) ? 1 : 0;
            if ($unitId > 0) {
                $existing = org_unit_by_id($unitId);
                if ($existing === null) {
                    throw new RuntimeException('واحد سازمانی یافت نشد.');
                }
                if ((string) $existing['unit_type'] === 'ceo') {
                    throw new RuntimeException('مدیرعامل از طریق نوع «مدیرعامل» تغییر می‌کند.');
                }
                if ($parentId === $unitId) {
                    throw new RuntimeException('واحد نمی‌تواند والد خودش باشد.');
                }
                db()->prepare('UPDATE org_units SET name = ?, code = ?, parent_id = ?, unit_type = ?, chart_side = ?, manager_user_id = ?, is_primary_admin = ? WHERE id = ?')
                    ->execute([$name, $code !== '' ? $code : null, $parentId > 0 ? $parentId : null, $nodeType, $chartSide, $managerId > 0 ? $managerId : null, $isPrimary, $unitId]);
            } else {
                db()->prepare('INSERT INTO org_units (name, code, parent_id, unit_type, chart_side, manager_user_id, is_primary_admin, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)')
                    ->execute([$name, $code !== '' ? $code : null, $parentId > 0 ? $parentId : null, $nodeType, $chartSide, $managerId > 0 ? $managerId : null, $isPrimary]);
                $unitId = (int) db()->lastInsertId();
            }
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([$unitId]);
            if ($managerId > 0) {
                db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, 1)')->execute([$unitId, $managerId]);
                if ($nodeType !== 'liaison') {
                    db()->prepare('UPDATE users SET role = "manager", org_unit_id = ? WHERE id = ? AND role IN ("user", "agent")')->execute([$unitId, $managerId]);
                } else {
                    // نمایندهٔ واحد مستقل فقط به واحد وصل می‌شود؛ نقش او تغییر نمی‌کند.
                    db()->prepare('UPDATE users SET org_unit_id = ? WHERE id = ?')->execute([$unitId, $managerId]);
                }
            }
            org_record_undo($unitId > 0 ? 'ویرایش واحد سازمانی' : 'افزودن واحد سازمانی', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'org_unit_saved', null, ['unit_id' => $unitId, 'name' => $name, 'type' => $nodeType, 'chart_side' => $chartSide, 'manager' => $managerId]);
            flash('success', $nodeType === 'deputy' ? 'معاونت ذخیره شد.' : ($nodeType === 'liaison' ? 'واحد مستقل (نمایندگی) ذخیره شد.' : 'اداره ذخیره شد.'));
            redirect('index.php?page=organization#unit-' . $unitId);
        }

        if ($action === 'undo_org_action') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند عملیات سازمان را بازگرداند.');
            }
            org_ensure_schema();
            $undo = org_undo_state();
            if ($undo === null) {
                throw new RuntimeException('عملیاتی برای بازگشت وجود ندارد.');
            }
            org_restore_snapshot($undo['snapshot']);
            unset($_SESSION['org_undo']);
            save_audit((int) $orgAdmin['id'], 'org_action_undone', null, ['label' => (string) ($undo['label'] ?? '')]);
            flash('success', 'آخرین عملیات سازمان بازگردانده شد.');
            redirect('index.php?page=organization');
        }

        if ($action === 'assign_org_manager') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند مدیران سازمان را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $targetType = valid_choice(post_value('target_type'), ['ceo', 'deputy', 'department', 'liaison'], 'department');
            $targetId = max(0, (int) post_value('unit_id', '0'));
            $target = $targetType === 'ceo' ? org_unit_by_id(org_ceo_unit_id()) : org_unit_by_id($targetId);
            if ($target === null || (int) $target['is_active'] !== 1 || (string) $target['unit_type'] !== $targetType) {
                throw new RuntimeException('موقعیت سازمانی انتخاب‌شده معتبر نیست.');
            }
            $userId = max(0, (int) post_value('user_id', '0'));
            $userQuery = db()->prepare('SELECT id, full_name FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
            $userQuery->execute([$userId]);
            $managerUser = $userQuery->fetch();
            if (!$managerUser) {
                throw new RuntimeException('کاربر انتخاب‌شده فعال نیست.');
            }
            $oldTargetManagerId = (int) ($target['manager_user_id'] ?? 0);
            db()->beginTransaction();
            if ($oldTargetManagerId > 0 && $oldTargetManagerId !== $userId) {
                db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE id = ? AND org_unit_id = ?')->execute([$oldTargetManagerId, (int) $target['id']]);
            }
            db()->prepare('UPDATE org_units SET manager_user_id = NULL WHERE manager_user_id = ?')->execute([$userId]);
            db()->prepare('DELETE FROM org_unit_managers WHERE user_id = ?')->execute([$userId]);
            db()->prepare('UPDATE org_units SET manager_user_id = ? WHERE id = ?')->execute([$userId, (int) $target['id']]);
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([(int) $target['id']]);
            db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, 1)')->execute([(int) $target['id'], $userId]);
            if ($targetType === 'ceo') {
                db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL, role = CASE WHEN role IN ("user", "agent") THEN "manager" ELSE role END WHERE id = ?')->execute([$userId]);
            } elseif ($targetType === 'liaison') {
                // نمایندهٔ واحد مستقل: فقط اتصال به واحد؛ نقش کاربر دست‌نخورده می‌ماند.
                db()->prepare('UPDATE users SET org_unit_id = ?, manager_user_id = NULL WHERE id = ?')->execute([(int) $target['id'], $userId]);
            } else {
                db()->prepare('UPDATE users SET org_unit_id = ?, manager_user_id = NULL, role = CASE WHEN role IN ("user", "agent") THEN "manager" ELSE role END WHERE id = ?')->execute([(int) $target['id'], $userId]);
            }
            db_commit('index.php');
            org_record_undo('انتصاب مدیر سازمان', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'org_manager_assigned', null, ['unit_id' => (int) $target['id'], 'target_type' => $targetType, 'user_id' => $userId]);
            flash('success', '«' . (string) $managerUser['full_name'] . '» برای «' . (string) $target['name'] . '» منصوب شد.');
            redirect('index.php?page=organization#unit-' . (int) $target['id']);
        }

        if ($action === 'remove_org_manager') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند مدیران سازمان را عزل کند.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $targetType = valid_choice(post_value('target_type'), ['ceo', 'deputy', 'department', 'liaison'], 'department');
            $targetId = max(0, (int) post_value('unit_id', '0'));
            $target = $targetType === 'ceo' ? org_unit_by_id(org_ceo_unit_id()) : org_unit_by_id($targetId);
            if ($target === null || (int) $target['is_active'] !== 1 || (string) $target['unit_type'] !== $targetType) {
                throw new RuntimeException('موقعیت سازمانی انتخاب‌شده معتبر نیست.');
            }
            $managerId = (int) ($target['manager_user_id'] ?? 0);
            db()->beginTransaction();
            db()->prepare('UPDATE org_units SET manager_user_id = NULL WHERE id = ?')->execute([(int) $target['id']]);
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([(int) $target['id']]);
            if ($managerId > 0) {
                db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE id = ? AND org_unit_id = ?')->execute([$managerId, (int) $target['id']]);
            }
            db_commit('index.php');
            org_record_undo('عزل مدیر سازمان', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'org_manager_removed', null, ['unit_id' => (int) $target['id'], 'target_type' => $targetType, 'user_id' => $managerId]);
            flash('success', 'مدیر «' . (string) $target['name'] . '» عزل شد.');
            redirect('index.php?page=organization#unit-' . (int) $target['id']);
        }

        if ($action === 'remove_org_experts') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند کارشناسان را عزل کند.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $unitId = max(0, (int) post_value('unit_id', '0'));
            $unit = $unitId > 0 ? org_unit_by_id($unitId) : null;
            if (!org_unit_accepts_experts($unit)) {
                throw new RuntimeException('واحد انتخابی برای کارشناس معتبر نیست.');
            }
            $userIds = $_POST['user_ids'] ?? [];
            $userIds = is_array($userIds) ? array_values(array_unique(array_filter(array_map('intval', $userIds)))) : [];
            if ($userIds === []) {
                throw new RuntimeException('کارشناسی برای عزل انتخاب نشده است.');
            }
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $params = array_merge([$unitId], $userIds);
            $query = db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE org_unit_id = ? AND id IN (' . $placeholders . ')');
            $query->execute($params);
            org_record_undo('عزل کارشناسان', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'org_experts_removed', null, ['unit_id' => $unitId, 'user_ids' => $userIds]);
            flash('success', 'کارشناسان انتخاب‌شده از واحد عزل شدند.');
            redirect('index.php?page=organization#unit-' . $unitId);
        }

        if ($action === 'save_org_assign_users') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند اعضای سازمان را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $unitId = max(0, (int) post_value('member_unit_id', '0'));
            $unit = $unitId > 0 ? org_unit_by_id($unitId) : null;
            if (!org_unit_accepts_experts($unit)) {
                throw new RuntimeException('کارشناس را می‌توان به معاونت یا اداره وصل کرد.');
            }
            $userIds = $_POST['user_ids'] ?? [];
            $userIds = is_array($userIds) ? array_values(array_unique(array_filter(array_map('intval', $userIds)))) : [];
            if ($userIds === []) {
                throw new RuntimeException('حداقل یک کارشناس انتخاب کنید.');
            }
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $validUsers = db()->prepare('SELECT id FROM users WHERE id IN (' . $placeholders . ') AND is_active = 1');
            $validUsers->execute($userIds);
            $validIds = array_map('intval', $validUsers->fetchAll(PDO::FETCH_COLUMN));
            if (count($validIds) !== count($userIds)) {
                throw new RuntimeException('یکی از کاربران انتخاب‌شده فعال نیست.');
            }
            $managerId = (int) ($unit['manager_user_id'] ?? 0);
            db()->beginTransaction();
            $assign = db()->prepare('UPDATE users SET org_unit_id = ?, manager_user_id = ? WHERE id = ?');
            foreach ($validIds as $userId) {
                $assign->execute([$unitId, $managerId > 0 && $managerId !== $userId ? $managerId : null, $userId]);
            }
            db_commit('index.php');
            org_record_undo('افزودن کارشناسان', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'org_users_assigned', null, ['unit_id' => $unitId, 'user_ids' => $validIds]);
            flash('success', count($validIds) . ' کارشناس به «' . (string) $unit['name'] . '» اضافه شد.');
            redirect('index.php?page=organization#unit-' . $unitId);
        }

        if ($action === 'reset_org_structure') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ساختار سازمانی را پاک کند.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $ceoId = org_ceo_unit_id();
            db()->beginTransaction();
            db()->exec('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL');
            db()->exec('DELETE FROM org_unit_managers');
            db()->prepare('UPDATE org_units SET manager_user_id = NULL, is_active = 1 WHERE id = ?')->execute([$ceoId]);
            db()->prepare('UPDATE org_units SET is_active = 0 WHERE id <> ?')->execute([$ceoId]);
            db_commit('index.php');
            org_record_undo('ریست ساختار سازمانی', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'org_structure_reset');
            flash('success', 'ساختار سازمانی پاک شد.');
            redirect('index.php?page=organization');
        }

        if ($action === 'save_org_ceo') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند مدیرعامل را تعیین کند.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $ceoUserId = max(0, (int) post_value('ceo_user_id', '0'));
            if ($ceoUserId > 0) {
                $ceoCheck = db()->prepare('SELECT id FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
                $ceoCheck->execute([$ceoUserId]);
                if (!$ceoCheck->fetchColumn()) {
                    throw new RuntimeException('کاربر مدیرعامل معتبر نیست.');
                }
            }
            $ceoId = org_ceo_unit_id();
            db()->prepare('UPDATE org_units SET manager_user_id = ? WHERE id = ?')->execute([$ceoUserId > 0 ? $ceoUserId : null, $ceoId]);
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([$ceoId]);
            if ($ceoUserId > 0) {
                db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, 1)')->execute([$ceoId, $ceoUserId]);
                db()->prepare('UPDATE users SET role = "manager" WHERE id = ? AND role IN ("user", "agent")')->execute([$ceoUserId]);
            }
            org_record_undo('تغییر مدیرعامل', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'org_ceo_saved', null, ['user_id' => $ceoUserId]);
            flash('success', $ceoUserId > 0 ? 'مدیرعامل تعیین شد.' : 'مدیرعامل حذف شد.');
            redirect('index.php?page=organization#unit-' . $ceoId);
        }

        if ($action === 'save_org_unit') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ساختار سازمانی را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $unitId = max(0, (int) post_value('unit_id', '0'));
            $name = trim((string) post_value('unit_name', ''));
            $code = trim((string) post_value('unit_code', ''));
            $unitType = valid_choice(post_value('unit_type'), ['deputy', 'department'], 'department');
            $parentId = max(0, (int) post_value('parent_id', '0'));
            if ($name === '') {
                throw new RuntimeException('نام واحد الزامی است.');
            }
            $ceoId = org_ceo_unit_id();
            if ($unitType === 'deputy') {
                $parentId = $ceoId;
            } else {
                $parentUnit = $parentId > 0 ? org_unit_by_id($parentId) : null;
                if ($parentUnit === null || !in_array((string) $parentUnit['unit_type'], ['deputy', 'ceo'], true)) {
                    throw new RuntimeException('اداره باید زیر یک معاونت (یا مدیرعامل) تعریف شود.');
                }
            }
            if ($unitId > 0) {
                $existing = org_unit_by_id($unitId);
                if ($existing === null) {
                    throw new RuntimeException('واحد سازمانی یافت نشد.');
                }
                if ((string) $existing['unit_type'] === 'ceo') {
                    throw new RuntimeException('مدیرعامل از طریق بخش مدیرعامل تغییر می‌کند.');
                }
                if ($parentId === $unitId) {
                    throw new RuntimeException('واحد نمی‌تواند والد خودش باشد.');
                }
            }
            $managerIds = $_POST['manager_ids'] ?? null;
            if ($managerIds === null && post_value('manager_user_id', '0') !== '') {
                $managerIds = [(int) post_value('manager_user_id', '0')];
            }
            $managerIds = is_array($managerIds) ? array_values(array_unique(array_filter(array_map('intval', $managerIds)))) : [];
            if ($managerIds !== []) {
                $placeholders = implode(', ', array_fill(0, count($managerIds), '?'));
                $valid = db()->prepare('SELECT id FROM users WHERE id IN (' . $placeholders . ') AND is_active = 1');
                $valid->execute($managerIds);
                $managerIds = array_map('intval', $valid->fetchAll(PDO::FETCH_COLUMN));
            }
            $isPrimary = post_value('is_primary_admin') === '1' ? 1 : 0;
            if ($unitId > 0) {
                db()->prepare('UPDATE org_units SET name = ?, code = ?, parent_id = ?, unit_type = ?, manager_user_id = ?, is_primary_admin = ? WHERE id = ?')
                    ->execute([$name, $code !== '' ? $code : null, $parentId > 0 ? $parentId : null, $unitType, $managerIds[0] ?? null, $isPrimary, $unitId]);
            } else {
                db()->prepare('INSERT INTO org_units (name, code, parent_id, unit_type, manager_user_id, is_primary_admin, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)')
                    ->execute([$name, $code !== '' ? $code : null, $parentId > 0 ? $parentId : null, $unitType, $managerIds[0] ?? null, $isPrimary]);
                $unitId = (int) db()->lastInsertId();
            }
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([$unitId]);
            $insertManager = db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, ?)');
            foreach ($managerIds as $index => $managerId) {
                $insertManager->execute([$unitId, $managerId, $index === 0 ? 1 : 0]);
                db()->prepare('UPDATE users SET role = "manager", org_unit_id = ? WHERE id = ? AND role IN ("user", "agent")')->execute([$unitId, $managerId]);
            }
            org_record_undo($unitId > 0 ? 'ویرایش واحد سازمانی' : 'افزودن واحد سازمانی', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'org_unit_saved', null, ['unit_id' => $unitId, 'name' => $name, 'managers' => $managerIds]);
            flash('success', 'واحد سازمانی ذخیره شد.');
            redirect('index.php?page=organization#unit-' . $unitId);
        }

        if ($action === 'delete_org_unit') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ساختار سازمانی را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $unitId = max(0, (int) post_value('unit_id', '0'));
            $unit = org_unit_by_id($unitId);
            if ($unit === null) {
                throw new RuntimeException('واحد سازمانی یافت نشد.');
            }
            if ((string) $unit['unit_type'] === 'ceo') {
                throw new RuntimeException('واحد مدیرعامل قابل حذف نیست.');
            }
            $unitIds = org_unit_subtree_ids($unitId);
            $placeholders = implode(',', array_fill(0, count($unitIds), '?'));
            $managerIds = db()->prepare('SELECT manager_user_id FROM org_units WHERE id IN (' . $placeholders . ') AND manager_user_id IS NOT NULL');
            $managerIds->execute($unitIds);
            $managerIds = array_map('intval', $managerIds->fetchAll(PDO::FETCH_COLUMN));
            db()->beginTransaction();
            if ($managerIds !== []) {
                $managerPlaceholders = implode(',', array_fill(0, count($managerIds), '?'));
                db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE org_unit_id IN (' . $placeholders . ') OR manager_user_id IN (' . $managerPlaceholders . ')')->execute(array_merge($unitIds, $managerIds));
            } else {
                db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE org_unit_id IN (' . $placeholders . ')')->execute($unitIds);
            }
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id IN (' . $placeholders . ')')->execute($unitIds);
            db()->prepare('UPDATE org_units SET is_active = 0, manager_user_id = NULL WHERE id IN (' . $placeholders . ')')->execute($unitIds);
            db_commit('index.php');
            org_record_undo('حذف واحد سازمانی', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'org_unit_deleted', null, ['unit_id' => $unitId, 'subtree_ids' => $unitIds]);
            flash('success', 'واحد و زیرمجموعه‌های آن غیرفعال شدند.');
            redirect('index.php?page=organization');
        }

        if ($action === 'save_user_org') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند عضویت سازمانی را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot();
            $targetUserId = max(0, (int) post_value('user_id', '0'));
            $unitId = max(0, (int) post_value('org_unit_id', '0'));
            $managerId = max(0, (int) post_value('manager_user_id', '0'));
            $target = db()->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
            $target->execute([$targetUserId]);
            if (!$target->fetchColumn()) {
                throw new RuntimeException('کاربر یافت نشد.');
            }
            if ($unitId > 0 && org_unit_by_id($unitId) === null) {
                throw new RuntimeException('واحد سازمانی یافت نشد.');
            }
            if ($managerId === 0 && $unitId > 0) {
                $memberUnit = org_unit_by_id($unitId);
                $managerId = (int) ($memberUnit['manager_user_id'] ?? 0);
            }
            if ($managerId === $targetUserId) {
                $managerId = 0;
            }
            db()->prepare('UPDATE users SET org_unit_id = ?, manager_user_id = ? WHERE id = ?')
                ->execute([$unitId > 0 ? $unitId : null, $managerId > 0 ? $managerId : null, $targetUserId]);
            org_record_undo('تغییر عضویت سازمانی کاربر', $orgUndoSnapshot);
            save_audit((int) $orgAdmin['id'], 'user_org_updated', null, ['user_id' => $targetUserId, 'unit_id' => $unitId, 'manager_user_id' => $managerId]);
            flash('success', 'عضویت سازمانی کاربر ذخیره شد.');
            redirect('index.php?page=organization#user-' . $targetUserId);
        }


        if ($action === 'repair_org_manager_admin_roles') {
            $repairAdmin = require_primary_admin();
            org_ensure_schema();
            $requestedIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['user_ids'] ?? [])))));
            if ($requestedIds === []) {
                throw new RuntimeException('حداقل یک مدیر سازمانی را برای اصلاح انتخاب کنید.');
            }
            $eligible = [];
            $candidateQuery = db()->prepare('SELECT u.id FROM users u WHERE u.id = ? AND u.is_active = 1 AND (u.role = "primary_admin" OR (u.role = "admin" AND u.is_primary_admin = 1)) AND EXISTS (SELECT 1 FROM org_units ou WHERE ou.is_active = 1 AND (ou.manager_user_id = u.id OR EXISTS (SELECT 1 FROM org_unit_managers oum WHERE oum.unit_id = ou.id AND oum.user_id = u.id))) LIMIT 1');
            foreach ($requestedIds as $candidateId) {
                if ($candidateId === (int) $repairAdmin['id']) {
                    throw new RuntimeException('حساب مدیر اصلیِ واردشده قابل تنزل نیست؛ ابتدا یک مدیر اصلی دیگر را نگه دارید.');
                }
                $candidateQuery->execute([$candidateId]);
                if (!$candidateQuery->fetchColumn()) {
                    throw new RuntimeException('یکی از کاربران انتخاب‌شده دیگر مدیر سازمانیِ ادمین اصلی نیست؛ فهرست را تازه کنید.');
                }
                $eligible[] = $candidateId;
            }
            $primaryCount = (int) db()->query('SELECT COUNT(*) FROM users WHERE is_active = 1 AND (role = "primary_admin" OR (role = "admin" AND is_primary_admin = 1))')->fetchColumn();
            if ($primaryCount - count($eligible) < 1) {
                throw new RuntimeException('برای حفظ دسترسی سامانه، حداقل یک ادمین اصلی باید باقی بماند. اصلاح را در چند مرحله انجام دهید و دست‌کم یک مدیر اصلی واقعی را نگه دارید.');
            }
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $placeholders = implode(',', array_fill(0, count($eligible), '?'));
                $pdo->prepare('UPDATE users SET role = "manager", is_primary_admin = 0 WHERE id IN (' . $placeholders . ')')->execute($eligible);
                $pdo->commit();
            } catch (Throwable $repairError) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $repairError;
            }
            save_audit((int) $repairAdmin['id'], 'org_manager_primary_admin_repaired', null, ['user_ids' => $eligible]);
            flash('success', count($eligible) . ' کاربر مدیر سازمانی به نقش «مدیر» برگردانده شدند؛ انتساب معاونت/اداره و عضویت آن‌ها حفظ شد.');
            redirect('index.php?page=organization');
        }

        if ($action === 'repair_org_manager_support_roles') {
            // ۱.۶۲.۶ — همتای ترمیم ادمین اصلی برای حوزهٔ پشتیبانی: مدیر سازمانی نباید «مدیر پشتیبانی» بماند،
            // وگرنه درخواست‌های پشتیبانی به‌صورت خودکار به او ارجاع می‌شوند.
            $repairAdmin = require_primary_admin();
            org_ensure_schema();
            $requestedIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['user_ids'] ?? [])))));
            if ($requestedIds === []) {
                throw new RuntimeException('حداقل یک مدیر سازمانی را برای اصلاح انتخاب کنید.');
            }
            $eligible = [];
            $candidateQuery = db()->prepare('SELECT u.id FROM users u WHERE u.id = ? AND u.is_active = 1 AND u.role = "support_manager" AND EXISTS (SELECT 1 FROM org_units ou WHERE ou.is_active = 1 AND (ou.manager_user_id = u.id OR EXISTS (SELECT 1 FROM org_unit_managers oum WHERE oum.unit_id = ou.id AND oum.user_id = u.id))) LIMIT 1');
            foreach ($requestedIds as $candidateId) {
                $candidateQuery->execute([$candidateId]);
                if (!$candidateQuery->fetchColumn()) {
                    throw new RuntimeException('یکی از کاربران انتخاب‌شده دیگر مدیر سازمانیِ دارای نقش «مدیر پشتیبانی» نیست؛ فهرست را تازه کنید.');
                }
                $eligible[] = $candidateId;
            }
            $supportManagerCount = (int) db()->query('SELECT COUNT(*) FROM users WHERE is_active = 1 AND role = "support_manager"')->fetchColumn();
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $placeholders = implode(',', array_fill(0, count($eligible), '?'));
                $pdo->prepare('UPDATE users SET role = "manager", is_primary_admin = 0 WHERE id IN (' . $placeholders . ')')->execute($eligible);
                $pdo->commit();
            } catch (Throwable $repairError) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $repairError;
            }
            save_audit((int) $repairAdmin['id'], 'org_manager_support_role_repaired', null, ['user_ids' => $eligible]);
            $remaining = $supportManagerCount - count($eligible);
            flash('success', count($eligible) . ' مدیر سازمانی از نقش «مدیر پشتیبانی» به «مدیر» برگردانده شدند؛ چارت سازمانی حفظ شد.'
                . ($remaining < 1 ? ' توجه: دیگر مدیر پشتیبانی فعالی باقی نمانده؛ درخواست‌های پشتیبانی تا تعیین مدیر پشتیبانی واقعی بدون مسئول در «بررسی مدیر» می‌مانند.' : ''));
            redirect('index.php?page=organization');
        }
