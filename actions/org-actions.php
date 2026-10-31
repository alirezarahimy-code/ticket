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

// درخواست‌های UI چارت برای جلوگیری از ثبت دوباره در double-click/replay محدود می‌شوند.
$orgTokenActions = ['save_org_node', 'save_org_unit', 'delete_org_unit', 'assign_org_manager', 'remove_org_manager', 'remove_org_experts', 'save_org_assign_users', 'reset_org_structure', 'save_org_ceo', 'save_user_org', 'sort_org_deputies', 'undo_org_action', 'redo_org_action', 'restore_org_backup'];
if (in_array($action, $orgTokenActions, true)) {
    $requestToken = trim((string) ($_POST['org_request_token'] ?? ''));
    if ($requestToken !== '' && !preg_match('/^[a-f0-9]{48}$/i', $requestToken)) {
        throw new RuntimeException('شناسهٔ درخواست معتبر نیست؛ صفحه را تازه کنید و دوباره تلاش کنید.');
    }
    if ($requestToken !== '') {
        $now = time();
        $seenTokens = (array) ($_SESSION['org_request_tokens'] ?? []);
        foreach ($seenTokens as $token => $createdAt) {
            if ((int) $createdAt < $now - 900) unset($seenTokens[$token]);
        }
        if (isset($seenTokens[$requestToken])) {
            throw new RuntimeException('این درخواست قبلاً دریافت شده و دوباره اجرا نشد.');
        }
        $seenTokens[$requestToken] = $now;
        if (count($seenTokens) > 100) $seenTokens = array_slice($seenTokens, -100, null, true);
        $_SESSION['org_request_tokens'] = $seenTokens;
    }
}

$orgCompanyActions = ['save_org_node', 'undo_org_action', 'redo_org_action', 'assign_org_manager', 'remove_org_manager', 'remove_org_experts', 'save_org_assign_users', 'reset_org_structure', 'save_org_ceo', 'save_org_unit', 'delete_org_unit', 'save_user_org', 'sort_org_deputies', 'restore_org_backup'];
$orgCompanyId = 0;
if (in_array($action, $orgCompanyActions, true)) {
    if (!org_has_company_schema()) throw new RuntimeException('ابتدا migration شرکت‌ها را از مسیر رسمی به‌روزرسانی اجرا کنید.');
    $orgCompanyId = org_company_context_id($user, (int) post_value('company_id', '0'));
    $orgRootLabel = org_company_root_title($orgCompanyId);
    $postedUnitIds = array_merge([(int) post_value('unit_id', '0'), (int) post_value('member_unit_id', '0'), (int) post_value('org_unit_id', '0'), (int) post_value('parent_id', '0')], array_map('intval', (array) ($_POST['unit_ids'] ?? [])));
    foreach (array_unique(array_filter($postedUnitIds)) as $postedUnitId) {
        $postedUnit = org_unit_by_id((int) $postedUnitId);
        if ($postedUnit !== null && (int) ($postedUnit['company_id'] ?? 1) !== $orgCompanyId) throw new RuntimeException('این واحد متعلق به شرکت دیگری است؛ عملیات بین دو درخت مجاز نیست.');
    }
    $postedUserIds = array_merge([(int) post_value('user_id', '0'), (int) post_value('ceo_user_id', '0'), (int) post_value('unit_manager_id', '0'), (int) post_value('manager_user_id', '0'), (int) post_value('member_manager_id', '0')], array_map('intval', (array) ($_POST['user_ids'] ?? [])), array_map('intval', (array) ($_POST['manager_ids'] ?? [])));
    $postedUserIds = array_values(array_unique(array_filter($postedUserIds)));
    if ($postedUserIds !== []) {
        $placeholders = implode(',', array_fill(0, count($postedUserIds), '?'));
        $validCompanyUsers = db()->prepare('SELECT id FROM users WHERE id IN (' . $placeholders . ') AND COALESCE(auth_source, "") <> "food"');
        $validCompanyUsers->execute($postedUserIds);
        $validIds = array_map('intval', $validCompanyUsers->fetchAll(PDO::FETCH_COLUMN));
        if (count($validIds) !== count($postedUserIds)) throw new RuntimeException('حساب انتخابی پیدا نشد یا برای پرسنل غذاست.');
        foreach ($postedUserIds as $postedUserId) if (!org_user_available_for_company((int) $postedUserId, $orgCompanyId)) throw new RuntimeException('این کاربر در درخت شرکت دیگری معرفی شده است؛ ابتدا او را از درخت فعلی جدا کنید.');
    }
}

        // ───── گروه اکشن: ساختار سازمانی (چارت) ─────
        if ($action === 'save_org_node') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ساختار سازمانی را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
            $nodeType = valid_choice(post_value('node_type'), [org_company_root_type($orgCompanyId), 'deputy', 'management', 'office', 'department', 'member', 'liaison'], 'department');
            $ceoId = org_ceo_unit_id($orgCompanyId);

            if ($nodeType === org_company_root_type($orgCompanyId)) {
                $ceoUserId = max(0, (int) post_value('ceo_user_id', '0'));
                if ($ceoUserId > 0) {
                    $check = db()->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1 AND COALESCE(auth_source, '') <> 'food' LIMIT 1");
                    $check->execute([$ceoUserId]);
                    if (!$check->fetchColumn()) {
                        throw new RuntimeException('کاربر ' . $orgRootLabel . ' معتبر نیست.');
                    }
                }
                db()->prepare('UPDATE org_units SET manager_user_id = ? WHERE id = ?')->execute([$ceoUserId > 0 ? $ceoUserId : null, $ceoId]);
                db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([$ceoId]);
                if ($ceoUserId > 0) {
                    db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, 1)')->execute([$ceoId, $ceoUserId]);
                    // نقش مدیر به‌صورت همگام و قابل‌بازگشت در org_sync_position_roles تنظیم می‌شود.
                }
                org_record_undo('تغییر ' . $orgRootLabel, $orgUndoSnapshot, $orgCompanyId);
                save_audit((int) $orgAdmin['id'], 'org_ceo_saved', null, ['user_id' => $ceoUserId]);
                flash('success', $orgRootLabel . ' ذخیره شد.');
                redirect(org_company_redirect_url($orgCompanyId, 'unit-' . $ceoId));
            }

            if ($nodeType === 'member') {
                $targetUserId = max(0, (int) post_value('user_id', '0'));
                $unitId = max(0, (int) post_value('member_unit_id', '0'));
                $managerId = max(0, (int) post_value('member_manager_id', '0'));
                $target = db()->prepare("SELECT id FROM users WHERE id = ? AND COALESCE(auth_source, '') <> 'food' LIMIT 1");
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
                org_record_undo('تغییر عضویت سازمانی کاربر', $orgUndoSnapshot, $orgCompanyId);
                save_audit((int) $orgAdmin['id'], 'user_org_updated', null, ['user_id' => $targetUserId, 'unit_id' => $unitId, 'manager_user_id' => $managerId]);
                flash('success', $unitId > 0 ? 'کارشناس به اداره وصل شد.' : 'کارشناس از اداره جدا شد.');
                redirect(org_company_redirect_url($orgCompanyId, 'user-' . $targetUserId));
            }

            $unitId = max(0, (int) post_value('unit_id', '0'));
            $name = trim((string) post_value('unit_name', ''));
            $code = trim((string) post_value('unit_code', ''));
            if ($name === '') {
                throw new RuntimeException('نام واحد الزامی است.');
            }
            $chartSide = null;
            if (in_array($nodeType, ['deputy', 'management', 'office'], true)) {
                $parentId = $ceoId;
            } elseif ($nodeType === 'liaison') {
                // ۱.۶۲.۱۰: واحد مستقل (نمایندگی) کنار مدیرعامل؛ والد ندارد و زیرمجموعهٔ مدیرعامل نیست.
                $parentId = 0;
                $chartSide = valid_choice(post_value('chart_side'), ['right', 'left'], 'right');
                $sideTaken = db()->prepare('SELECT id FROM org_units WHERE company_id = ? AND unit_type = "liaison" AND chart_side = ? AND is_active = 1 AND id <> ? LIMIT 1');
                $sideTaken->execute([$orgCompanyId, $chartSide, $unitId]);
                if ($sideTaken->fetchColumn()) {
                    throw new RuntimeException('در این سمت مدیرعامل قبلاً یک واحد مستقل تعریف شده است؛ ابتدا آن را ویرایش یا حذف کنید.');
                }
            } else {
                $parentId = max(0, (int) post_value('parent_id', '0'));
                $parentUnit = $parentId > 0 ? org_unit_by_id($parentId) : null;
                if ($parentUnit === null || (int) ($parentUnit['is_active'] ?? 0) !== 1 || !in_array((string) $parentUnit['unit_type'], ['deputy', 'management', 'department', org_company_root_type($orgCompanyId)], true)) {
                    throw new RuntimeException('اداره باید زیر معاونت، مدیریت مستقل یا ادارهٔ فعال تعریف شود.');
                }
                if ($unitId > 0 && in_array($parentId, org_unit_subtree_ids($unitId), true)) {
                    throw new RuntimeException('انتقال واحد به خودش یا یکی از زیرمجموعه‌هایش مجاز نیست.');
                }
            }
            if ($nodeType === 'office') {
                $officeExists = db()->prepare('SELECT id FROM org_units WHERE company_id = ? AND unit_type = "office" AND is_active = 1 AND id <> ? LIMIT 1');
                $officeExists->execute([$orgCompanyId, $unitId]);
                if ($officeExists->fetchColumn()) throw new RuntimeException('برای این شرکت فقط یک دفتر مدیرعامل مجاز است؛ دفتر موجود را ویرایش کنید.');
            }
            $managerId = max(0, (int) post_value('unit_manager_id', '0'));
            if ($managerId > 0) {
                $check = db()->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1 AND COALESCE(auth_source, '') <> 'food' LIMIT 1");
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
                if ((string) $existing['unit_type'] === org_company_root_type($orgCompanyId)) {
                    throw new RuntimeException('مدیر ریشه از بخش تعیین مدیر شرکت تغییر می‌کند.');
                }
                if ($parentId === $unitId) {
                    throw new RuntimeException('واحد نمی‌تواند والد خودش باشد.');
                }
                db()->prepare('UPDATE org_units SET name = ?, code = ?, parent_id = ?, unit_type = ?, chart_side = ?, manager_user_id = ?, is_primary_admin = ? WHERE id = ?')
                    ->execute([$name, $code !== '' ? $code : null, $parentId > 0 ? $parentId : null, $nodeType, $chartSide, $managerId > 0 ? $managerId : null, $isPrimary, $unitId]);
            } else {
                db()->prepare('INSERT INTO org_units (company_id, name, code, parent_id, unit_type, chart_side, manager_user_id, is_primary_admin, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)')
                    ->execute([$orgCompanyId, $name, $code !== '' ? $code : null, $parentId > 0 ? $parentId : null, $nodeType, $chartSide, $managerId > 0 ? $managerId : null, $isPrimary]);
                $unitId = (int) db()->lastInsertId();
                if (org_has_sort_order()) {
                    db()->prepare('UPDATE org_units SET sort_order = ? WHERE id = ?')->execute([org_next_sort_order($parentId, $orgCompanyId), $unitId]);
                }
            }
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([$unitId]);
            if ($managerId > 0) {
                db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, 1)')->execute([$unitId, $managerId]);
                if ($nodeType !== 'liaison') {
                    db()->prepare('UPDATE users SET org_unit_id = ? WHERE id = ?')->execute([$unitId, $managerId]);
                } else {
                    // نمایندهٔ واحد مستقل فقط به واحد وصل می‌شود؛ نقش او تغییر نمی‌کند.
                    db()->prepare('UPDATE users SET org_unit_id = ? WHERE id = ?')->execute([$unitId, $managerId]);
                }
            }
            org_record_undo($unitId > 0 ? 'ویرایش واحد سازمانی' : 'افزودن واحد سازمانی', $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_unit_saved', null, ['unit_id' => $unitId, 'name' => $name, 'type' => $nodeType, 'chart_side' => $chartSide, 'manager' => $managerId]);
            flash('success', match ($nodeType) { 'deputy' => 'معاونت ذخیره شد.', 'management' => 'مدیریت مستقل ذخیره شد.', 'office' => 'دفتر مدیرعامل ذخیره شد.', 'liaison' => 'واحد مستقل ذخیره شد.', default => 'اداره ذخیره شد.' });
            redirect(org_company_redirect_url($orgCompanyId, 'unit-' . $unitId));
        }

        if ($action === 'undo_org_action') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند عملیات سازمان را بازگرداند.');
            }
            org_ensure_schema();
            $undo = org_undo_state($orgCompanyId);
            if ($undo === null || empty($undo['can_undo'])) {
                throw new RuntimeException('عملیاتی برای بازگشت وجود ندارد.');
            }
            org_restore_snapshot($undo['before'], $undo['after'], $orgCompanyId);
            $_SESSION['org_undo']['can_undo'] = false;
            $_SESSION['org_undo']['can_redo'] = true;
            save_audit((int) $orgAdmin['id'], 'org_action_undone', null, ['label' => (string) ($undo['label'] ?? '')]);
            flash('success', 'آخرین تغییر ساختاری بازگردانده شد؛ در همین نشست امکان انجام دوباره وجود دارد.');
            redirect(org_company_redirect_url($orgCompanyId));
        }

        if ($action === 'redo_org_action') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند عملیات سازمان را دوباره انجام دهد.');
            }
            org_ensure_schema();
            $redo = org_undo_state($orgCompanyId);
            if ($redo === null || empty($redo['can_redo'])) {
                throw new RuntimeException('عملیاتی برای انجام دوباره وجود ندارد.');
            }
            org_restore_snapshot($redo['after'], $redo['before'], $orgCompanyId);
            $_SESSION['org_undo']['can_undo'] = true;
            $_SESSION['org_undo']['can_redo'] = false;
            save_audit((int) $orgAdmin['id'], 'org_action_redone', null, ['label' => (string) ($redo['label'] ?? '')]);
            flash('success', 'آخرین تغییر ساختاری دوباره انجام شد.');
            redirect(org_company_redirect_url($orgCompanyId));
        }

        if ($action === 'sort_org_deputies') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ترتیب معاونت‌ها را تغییر دهد.');
            }
            org_ensure_schema();
            if (!org_has_sort_order()) {
                throw new RuntimeException('برای ذخیرهٔ ترتیب دستی، ابتدا مهاجرت 017 را اجرا کنید؛ داده‌ها تغییر نکردند.');
            }
            $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['unit_ids'] ?? [])))));
            $ceoId = org_ceo_unit_id($orgCompanyId);
            $current = db()->prepare('SELECT id FROM org_units WHERE parent_id = ? AND company_id = ? AND unit_type = "deputy" AND is_active = 1 ORDER BY COALESCE(sort_order,0), name, id');
            $current->execute([$ceoId, $orgCompanyId]);
            $expected = array_map('intval', $current->fetchAll(PDO::FETCH_COLUMN));
            $copy = $ids;
            sort($copy);
            $sortedExpected = $expected;
            sort($sortedExpected);
            if ($ids === [] || $copy !== $sortedExpected || count($ids) !== count($expected)) {
                throw new RuntimeException('فهرست معاونت‌ها معتبر نیست؛ صفحه را تازه کنید و دوباره تلاش کنید.');
            }
            $snapshot = org_snapshot($orgCompanyId);
            db()->beginTransaction();
            try {
                $updateOrder = db()->prepare('UPDATE org_units SET sort_order = ? WHERE id = ? AND parent_id = ? AND company_id = ? AND unit_type = "deputy"');
                foreach ($ids as $index => $id) $updateOrder->execute([($index + 1) * 10, $id, $ceoId, $orgCompanyId]);
                db()->commit();
            } catch (Throwable $sortError) {
                if (db()->inTransaction()) db()->rollBack();
                throw $sortError;
            }
            org_record_undo('مرتب‌سازی معاونت‌ها', $snapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_deputies_sorted', null, ['unit_ids' => $ids]);
            flash('success', 'ترتیب معاونت‌ها ذخیره شد.');
            redirect(org_company_redirect_url($orgCompanyId));
        }

        if ($action === 'assign_org_manager') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند مدیران سازمان را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
            $targetType = valid_choice(post_value('target_type'), [org_company_root_type($orgCompanyId), 'ceo', 'company_manager', 'deputy', 'management', 'office', 'department', 'liaison'], 'department');
            $targetId = max(0, (int) post_value('unit_id', '0'));
            $target = in_array($targetType, ['ceo', 'company_manager'], true) ? org_unit_by_company(org_ceo_unit_id($orgCompanyId), $orgCompanyId) : org_unit_by_company($targetId, $orgCompanyId);
            if ($target === null || (int) $target['is_active'] !== 1 || (string) $target['unit_type'] !== $targetType) {
                throw new RuntimeException('موقعیت سازمانی انتخاب‌شده معتبر نیست.');
            }
            $userId = max(0, (int) post_value('user_id', '0'));
            $userQuery = db()->prepare("SELECT id, full_name FROM users WHERE id = ? AND is_active = 1 AND COALESCE(auth_source, '') <> 'food' LIMIT 1");
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
            db()->prepare('UPDATE org_units SET manager_user_id = NULL WHERE manager_user_id = ? AND company_id = ?')->execute([$userId, $orgCompanyId]);
            db()->prepare('DELETE m FROM org_unit_managers m JOIN org_units ou ON ou.id = m.unit_id WHERE m.user_id = ? AND ou.company_id = ?')->execute([$userId, $orgCompanyId]);
            db()->prepare('UPDATE org_units SET manager_user_id = ? WHERE id = ?')->execute([$userId, (int) $target['id']]);
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([(int) $target['id']]);
            db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, 1)')->execute([(int) $target['id'], $userId]);
            if (in_array($targetType, ['ceo', 'company_manager'], true)) {
                db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE id = ?')->execute([$userId]);
            } elseif ($targetType === 'liaison') {
                // نمایندهٔ واحد مستقل: فقط اتصال به واحد؛ نقش کاربر دست‌نخورده می‌ماند.
                db()->prepare('UPDATE users SET org_unit_id = ?, manager_user_id = NULL WHERE id = ?')->execute([(int) $target['id'], $userId]);
            } else {
                db()->prepare('UPDATE users SET org_unit_id = ?, manager_user_id = NULL WHERE id = ?')->execute([(int) $target['id'], $userId]);
            }
            db_commit('index.php');
            org_record_undo('انتصاب مدیر سازمان', $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_manager_assigned', null, ['unit_id' => (int) $target['id'], 'target_type' => $targetType, 'user_id' => $userId]);
            flash('success', '«' . (string) $managerUser['full_name'] . '» برای «' . (string) $target['name'] . '» منصوب شد.');
            redirect(org_company_redirect_url($orgCompanyId, 'unit-' . (int) $target['id']));
        }

        if ($action === 'remove_org_manager') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند مدیران سازمان را عزل کند.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
            $targetType = valid_choice(post_value('target_type'), [org_company_root_type($orgCompanyId), 'ceo', 'company_manager', 'deputy', 'management', 'office', 'department', 'liaison'], 'department');
            $targetId = max(0, (int) post_value('unit_id', '0'));
            $target = in_array($targetType, ['ceo', 'company_manager'], true) ? org_unit_by_company(org_ceo_unit_id($orgCompanyId), $orgCompanyId) : org_unit_by_company($targetId, $orgCompanyId);
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
            org_record_undo('عزل مدیر سازمان', $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_manager_removed', null, ['unit_id' => (int) $target['id'], 'target_type' => $targetType, 'user_id' => $managerId]);
            flash('success', 'مدیر «' . (string) $target['name'] . '» عزل شد.');
            redirect(org_company_redirect_url($orgCompanyId, 'unit-' . (int) $target['id']));
        }

        if ($action === 'remove_org_experts') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند کارشناسان را عزل کند.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
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
            $query = db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE org_unit_id = ? AND id IN (' . $placeholders . ') AND COALESCE(auth_source, "") <> "food"');
            $query->execute($params);
            org_record_undo('عزل کارشناسان', $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_experts_removed', null, ['unit_id' => $unitId, 'user_ids' => $userIds]);
            flash('success', 'کارشناسان انتخاب‌شده از واحد عزل شدند.');
            redirect(org_company_redirect_url($orgCompanyId, 'unit-' . $unitId));
        }

        if ($action === 'save_org_assign_users') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند اعضای سازمان را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
            $unitId = max(0, (int) post_value('member_unit_id', '0'));
            $unit = $unitId > 0 ? org_unit_by_id($unitId) : null;
            if (!org_unit_accepts_experts($unit)) {
                throw new RuntimeException('کارشناس را می‌توان به معاونت، مدیریت مستقل، اداره یا واحد مستقل وصل کرد.');
            }
            $userIds = $_POST['user_ids'] ?? [];
            $userIds = is_array($userIds) ? array_values(array_unique(array_filter(array_map('intval', $userIds)))) : [];
            if ($userIds === []) {
                throw new RuntimeException('حداقل یک کارشناس انتخاب کنید.');
            }
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $validUsers = db()->prepare('SELECT id FROM users WHERE id IN (' . $placeholders . ') AND is_active = 1 AND COALESCE(auth_source, "") <> "food"');
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
            org_record_undo('افزودن کارشناسان', $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_users_assigned', null, ['unit_id' => $unitId, 'user_ids' => $validIds]);
            flash('success', count($validIds) . ' کارشناس به «' . (string) $unit['name'] . '» اضافه شد.');
            redirect(org_company_redirect_url($orgCompanyId, 'unit-' . $unitId));
        }

        if ($action === 'restore_org_backup') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند پشتیبان ساختار سازمان را بازیابی کند.');
            }
            org_ensure_schema();
            $backupRaw = (string) post_value('backup_json', '');
            if ($backupRaw === '' || strlen($backupRaw) > 5 * 1024 * 1024) {
                throw new RuntimeException('فایل پشتیبان خالی است یا از حد ۵ مگابایت بیشتر است.');
            }
            $backup = json_decode($backupRaw, true);
            if (!is_array($backup) || ($backup['format'] ?? '') !== 'persian-org-tree-json' || !is_array($backup['units'] ?? null) || !is_array($backup['users'] ?? null)) {
                throw new RuntimeException('ساختار فایل پشتیبان معتبر نیست.');
            }
            // پیش از چندشرکتی، همهٔ پشتیبان‌ها متعلق به شرکت اصلی بودند.
            $backupCompanyId = array_key_exists('companyId', $backup) ? (int) $backup['companyId'] : 1;
            if ($backupCompanyId !== $orgCompanyId) throw new RuntimeException('این پشتیبان متعلق به شرکت دیگری است. فایل همان شرکت را انتخاب کنید.');
            $rootType = org_company_root_type($orgCompanyId);
            $backupUnits = [];
            foreach ($backup['units'] as $backupUnit) {
                if (!is_array($backupUnit)) throw new RuntimeException('یکی از واحدهای فایل پشتیبان معتبر نیست.');
                $unitId = (int) ($backupUnit['id'] ?? 0);
                $unitType = (string) ($backupUnit['type'] ?? '');
                $unitName = trim((string) ($backupUnit['name'] ?? ''));
                if ($unitId <= 0 || $unitName === '' || mb_strlen($unitName, 'UTF-8') > 190 || !in_array($unitType, [$rootType, 'deputy', 'management', 'office', 'department', 'liaison'], true) || isset($backupUnits[$unitId])) {
                    throw new RuntimeException('شناسه، نام یا نوع یکی از واحدهای پشتیبان معتبر نیست.');
                }
                $backupUnit['id'] = $unitId; $backupUnit['type'] = $unitType; $backupUnit['name'] = $unitName;
                $backupUnit['parentId'] = max(0, (int) ($backupUnit['parentId'] ?? 0));
                $backupUnit['managerId'] = max(0, (int) ($backupUnit['managerId'] ?? 0));
                $existingUnit = org_unit_by_id($unitId);
                if ($existingUnit !== null && (int) ($existingUnit['company_id'] ?? 1) !== $orgCompanyId) throw new RuntimeException('پشتیبان به شناسهٔ واحدی از شرکت دیگر اشاره می‌کند؛ انتقال بین شرکت‌ها مجاز نیست.');
                $backupUnits[$unitId] = $backupUnit;
            }
            $rootIds = array_keys(array_filter($backupUnits, static fn (array $backupUnit): bool => $backupUnit['type'] === $rootType));
            if (count($rootIds) !== 1 || (int) $rootIds[0] !== org_ceo_unit_id($orgCompanyId)) throw new RuntimeException('پشتیبان باید ریشهٔ معتبر همین شرکت را داشته باشد.');
            $backupUsers = [];
            foreach ($backup['users'] as $backupUser) {
                if (!is_array($backupUser)) throw new RuntimeException('یکی از انتساب‌های کاربر در پشتیبان معتبر نیست.');
                $userId = (int) ($backupUser['id'] ?? 0);
                if ($userId <= 0 || isset($backupUsers[$userId])) throw new RuntimeException('شناسهٔ یکی از کاربران پشتیبان معتبر نیست یا تکراری است.');
                $backupUser['unitId'] = max(0, (int) ($backupUser['unitId'] ?? 0));
                $backupUser['managerId'] = max(0, (int) ($backupUser['managerId'] ?? 0));
                if ($backupUser['unitId'] > 0 && !isset($backupUnits[$backupUser['unitId']])) throw new RuntimeException('کاربر پشتیبان به واحدی اشاره می‌کند که در همان فایل وجود ندارد.');
                $backupUsers[$userId] = $backupUser;
            }
            $liaisonSides = [];
            $officeCount = 0;
            foreach ($backupUnits as $unitId => $backupUnit) {
                $parentId = (int) $backupUnit['parentId'];
                $type = (string) $backupUnit['type'];
                if ($type === $rootType && $parentId !== 0) throw new RuntimeException('ریشهٔ شرکت در پشتیبان نباید والد داشته باشد.');
                if (in_array($type, ['deputy', 'management', 'office'], true) && $parentId !== (int) $rootIds[0]) throw new RuntimeException('معاونت، مدیریت مستقل و دفتر باید مستقیماً زیر ریشهٔ شرکت باشند.');
                if ($type === 'office' && ++$officeCount > 1) throw new RuntimeException('پشتیبان بیش از یک دفتر مدیرعامل دارد.');
                if ($type === 'department' && ($parentId <= 0 || !isset($backupUnits[$parentId]) || !in_array((string) $backupUnits[$parentId]['type'], [$rootType, 'deputy', 'management', 'department'], true))) throw new RuntimeException('والد اداره در پشتیبان معتبر نیست.');
                if ($type === 'liaison') {
                    $side = (string) ($backupUnit['chartSide'] ?? '');
                    if ($parentId !== 0 || !in_array($side, ['left', 'right'], true)) throw new RuntimeException('واحد هم‌تراز مدیرعامل باید مستقل و دارای سمت معتبر باشد.');
                    if (isset($liaisonSides[$side])) throw new RuntimeException('در هر سمت مدیرعامل فقط یک واحد هم‌تراز مجاز است.');
                    $liaisonSides[$side] = true;
                }
                $seenParents = [$unitId => true]; $cursor = $parentId; $guard = 0;
                while ($cursor > 0 && isset($backupUnits[$cursor]) && $guard++ < count($backupUnits) + 1) {
                    if (isset($seenParents[$cursor])) throw new RuntimeException('در پشتیبان حلقهٔ والد/فرزند وجود دارد.');
                    $seenParents[$cursor] = true; $cursor = (int) ($backupUnits[$cursor]['parentId'] ?? 0);
                }
                if ($cursor > 0) throw new RuntimeException('والد یکی از واحدها در پشتیبان پیدا نشد.');
                $managerIds = array_values(array_unique(array_filter(array_map('intval', (array) ($backupUnit['managerIds'] ?? [])))));
                if ((int) $backupUnit['managerId'] > 0 && !in_array((int) $backupUnit['managerId'], $managerIds, true)) array_unshift($managerIds, (int) $backupUnit['managerId']);
                $backupUnit['managerIds'] = $managerIds; $backupUnits[$unitId] = $backupUnit;
                foreach ($managerIds as $managerId) if (!isset($backupUsers[$managerId])) throw new RuntimeException('مدیر یکی از واحدها در فهرست کاربران پشتیبان نیست.');
            }
            if ($backupUsers !== []) {
                $userPlaceholders = implode(',', array_fill(0, count($backupUsers), '?'));
                $validBackupUsers = db()->prepare('SELECT id FROM users WHERE id IN (' . $userPlaceholders . ') AND COALESCE(auth_source, "") <> "food"');
                $validBackupUsers->execute(array_keys($backupUsers));
                $validBackupIds = array_map('intval', $validBackupUsers->fetchAll(PDO::FETCH_COLUMN));
                if (count($validBackupIds) !== count($backupUsers)) throw new RuntimeException('یک یا چند کاربر پشتیبان در این سامانه وجود ندارد یا از نوع حساب مجاز نیست.');
                foreach (array_keys($backupUsers) as $backupUserId) if (!org_user_available_for_company((int) $backupUserId, $orgCompanyId)) throw new RuntimeException('یکی از کاربران پشتیبان اکنون در درخت شرکت دیگری معرفی شده است؛ بازیابی متوقف شد.');
            }
            foreach ($backupUsers as $backupUser) if ($backupUser['managerId'] > 0 && !isset($backupUsers[$backupUser['managerId']])) throw new RuntimeException('مدیر ثبت‌شده برای یکی از کاربران پشتیبان پیدا نشد.');
            $snapshot = org_snapshot($orgCompanyId);
            $currentCompanyUserIds = org_company_member_user_ids($orgCompanyId);
            $pdo = db();
            $hasSortOrder = org_has_sort_order();
            $pdo->beginTransaction();
            try {
                $pdo->prepare('UPDATE org_units SET is_active = 0, manager_user_id = NULL WHERE company_id = ? AND unit_type <> ?')->execute([$orgCompanyId, $rootType]);
                $pdo->prepare('DELETE m FROM org_unit_managers m JOIN org_units ou ON ou.id = m.unit_id WHERE ou.company_id = ?')->execute([$orgCompanyId]);
                if ($hasSortOrder) {
                    $saveUnit = $pdo->prepare('INSERT INTO org_units (id, company_id, name, code, parent_id, unit_type, chart_side, manager_user_id, is_primary_admin, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE company_id = VALUES(company_id), name = VALUES(name), code = VALUES(code), parent_id = VALUES(parent_id), unit_type = VALUES(unit_type), chart_side = VALUES(chart_side), manager_user_id = VALUES(manager_user_id), is_primary_admin = VALUES(is_primary_admin), is_active = 1, sort_order = VALUES(sort_order)');
                } else {
                    $saveUnit = $pdo->prepare('INSERT INTO org_units (id, company_id, name, code, parent_id, unit_type, chart_side, manager_user_id, is_primary_admin, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE company_id = VALUES(company_id), name = VALUES(name), code = VALUES(code), parent_id = VALUES(parent_id), unit_type = VALUES(unit_type), chart_side = VALUES(chart_side), manager_user_id = VALUES(manager_user_id), is_primary_admin = VALUES(is_primary_admin), is_active = 1');
                }
                $saveManager = $pdo->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, ?)');
                foreach ($backupUnits as $unitId => $backupUnit) {
                    $managerId = (int) $backupUnit['managerId'];
                    $saveValues = [$unitId, $orgCompanyId, $backupUnit['name'], trim((string) ($backupUnit['code'] ?? '')) ?: null, (int) $backupUnit['parentId'] > 0 ? (int) $backupUnit['parentId'] : null, $backupUnit['type'], $backupUnit['type'] === 'liaison' ? (string) $backupUnit['chartSide'] : null, $managerId > 0 ? $managerId : null, !empty($backupUnit['primaryAdmin']) ? 1 : 0];
                    if ($hasSortOrder) $saveValues[] = max(0, (int) ($backupUnit['sortOrder'] ?? 0));
                    $saveUnit->execute($saveValues);
                    foreach ($backupUnit['managerIds'] as $managerIndex => $unitManagerId) $saveManager->execute([$unitId, $unitManagerId, $managerIndex === 0 ? 1 : 0]);
                }
                if ($currentCompanyUserIds !== []) {
                    $clearIds = implode(',', array_fill(0, count($currentCompanyUserIds), '?'));
                    $pdo->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE id IN (' . $clearIds . ') AND COALESCE(auth_source, "") <> "food"')->execute($currentCompanyUserIds);
                }
                $saveAssignment = $pdo->prepare('UPDATE users SET org_unit_id = ?, manager_user_id = ? WHERE id = ? AND COALESCE(auth_source, "") <> "food"');
                foreach ($backupUsers as $userId => $backupUser) $saveAssignment->execute([$backupUser['unitId'] > 0 ? $backupUser['unitId'] : null, $backupUser['managerId'] > 0 ? $backupUser['managerId'] : null, $userId]);
                $pdo->commit();
            } catch (Throwable $restoreError) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $restoreError;
            }
            org_record_undo('بازیابی پشتیبان ساختار سازمانی', $snapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_backup_restored', null, ['units' => count($backupUnits), 'users' => count($backupUsers)]);
            flash('success', 'پشتیبان همین شرکت و انتساب‌ها بازیابی شد؛ عضویت شرکت و نقش‌های دستی تغییر نکرد. نقش‌های خودکار مدیر طبق جایگاه‌های فعال همگام می‌شوند.');
            redirect(org_company_redirect_url($orgCompanyId));
        }

        if ($action === 'reset_org_structure') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ساختار سازمانی را پاک کند.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
            $currentCompanyUserIds = org_company_member_user_ids($orgCompanyId);
            $ceoId = org_ceo_unit_id($orgCompanyId);
            db()->beginTransaction();
            if ($currentCompanyUserIds !== []) {
                $clearIds = implode(',', array_fill(0, count($currentCompanyUserIds), '?'));
                db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE id IN (' . $clearIds . ') AND COALESCE(auth_source, "") <> "food"')->execute($currentCompanyUserIds);
            }
            db()->prepare('DELETE m FROM org_unit_managers m JOIN org_units ou ON ou.id = m.unit_id WHERE ou.company_id = ?')->execute([$orgCompanyId]);
            db()->prepare('UPDATE org_units SET manager_user_id = NULL, is_active = 1 WHERE id = ? AND company_id = ?')->execute([$ceoId, $orgCompanyId]);
            db()->prepare('UPDATE org_units SET is_active = 0, manager_user_id = NULL WHERE company_id = ? AND id <> ?')->execute([$orgCompanyId, $ceoId]);
            db_commit('index.php');
            org_record_undo('ریست ساختار سازمانی', $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_structure_reset');
            flash('success', 'ساختار سازمانی پاک شد.');
            redirect(org_company_redirect_url($orgCompanyId));
        }

        if ($action === 'save_org_ceo') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ' . $orgRootLabel . ' را تعیین کند.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
            $ceoUserId = max(0, (int) post_value('ceo_user_id', '0'));
            if ($ceoUserId > 0) {
                $ceoCheck = db()->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1 AND COALESCE(auth_source, '') <> 'food' LIMIT 1");
                $ceoCheck->execute([$ceoUserId]);
                if (!$ceoCheck->fetchColumn()) {
                    throw new RuntimeException('کاربر ' . $orgRootLabel . ' معتبر نیست.');
                }
            }
            $ceoId = org_ceo_unit_id($orgCompanyId);
            db()->prepare('UPDATE org_units SET manager_user_id = ? WHERE id = ?')->execute([$ceoUserId > 0 ? $ceoUserId : null, $ceoId]);
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([$ceoId]);
            if ($ceoUserId > 0) {
                db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, 1)')->execute([$ceoId, $ceoUserId]);
                // نقش مدیر به‌صورت همگام و قابل‌بازگشت در org_sync_position_roles تنظیم می‌شود.
            }
            org_record_undo('تغییر ' . $orgRootLabel, $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_ceo_saved', null, ['user_id' => $ceoUserId]);
            flash('success', $ceoUserId > 0 ? $orgRootLabel . ' تعیین شد.' : $orgRootLabel . ' حذف شد.');
            redirect(org_company_redirect_url($orgCompanyId, 'unit-' . $ceoId));
        }

        if ($action === 'save_org_unit') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ساختار سازمانی را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
            $unitId = max(0, (int) post_value('unit_id', '0'));
            $name = trim((string) post_value('unit_name', ''));
            $code = trim((string) post_value('unit_code', ''));
            $unitType = valid_choice(post_value('unit_type'), ['deputy', 'management', 'office', 'department'], 'department');
            $parentId = max(0, (int) post_value('parent_id', '0'));
            if ($name === '') {
                throw new RuntimeException('نام واحد الزامی است.');
            }
            $ceoId = org_ceo_unit_id($orgCompanyId);
            if (in_array($unitType, ['deputy', 'management', 'office'], true)) {
                $parentId = $ceoId;
            } else {
                $parentUnit = $parentId > 0 ? org_unit_by_id($parentId) : null;
                if ($parentUnit === null || (int) ($parentUnit['is_active'] ?? 0) !== 1 || !in_array((string) $parentUnit['unit_type'], ['deputy', 'management', 'department', org_company_root_type($orgCompanyId)], true)) {
                    throw new RuntimeException('اداره باید زیر معاونت، مدیریت مستقل یا ادارهٔ فعال تعریف شود.');
                }
                if ($unitId > 0 && in_array($parentId, org_unit_subtree_ids($unitId), true)) {
                    throw new RuntimeException('انتقال واحد به خودش یا یکی از زیرمجموعه‌هایش مجاز نیست.');
                }
            }
            if ($unitType === 'office') {
                $officeExists = db()->prepare('SELECT id FROM org_units WHERE company_id = ? AND unit_type = "office" AND is_active = 1 AND id <> ? LIMIT 1');
                $officeExists->execute([$orgCompanyId, $unitId]);
                if ($officeExists->fetchColumn()) throw new RuntimeException('برای این شرکت فقط یک دفتر مدیرعامل مجاز است؛ دفتر موجود را ویرایش کنید.');
            }
            if ($unitId > 0) {
                $existing = org_unit_by_id($unitId);
                if ($existing === null) {
                    throw new RuntimeException('واحد سازمانی یافت نشد.');
                }
                if ((string) $existing['unit_type'] === org_company_root_type($orgCompanyId)) {
                    throw new RuntimeException('مدیر ریشه از بخش تعیین مدیر شرکت تغییر می‌کند.');
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
                $valid = db()->prepare('SELECT id FROM users WHERE id IN (' . $placeholders . ') AND is_active = 1 AND COALESCE(auth_source, "") <> "food"');
                $valid->execute($managerIds);
                $managerIds = array_map('intval', $valid->fetchAll(PDO::FETCH_COLUMN));
            }
            $isPrimary = post_value('is_primary_admin') === '1' ? 1 : 0;
            if ($unitId > 0) {
                db()->prepare('UPDATE org_units SET name = ?, code = ?, parent_id = ?, unit_type = ?, manager_user_id = ?, is_primary_admin = ? WHERE id = ?')
                    ->execute([$name, $code !== '' ? $code : null, $parentId > 0 ? $parentId : null, $unitType, $managerIds[0] ?? null, $isPrimary, $unitId]);
            } else {
                db()->prepare('INSERT INTO org_units (company_id, name, code, parent_id, unit_type, manager_user_id, is_primary_admin, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)')
                    ->execute([$orgCompanyId, $name, $code !== '' ? $code : null, $parentId > 0 ? $parentId : null, $unitType, $managerIds[0] ?? null, $isPrimary]);
                $unitId = (int) db()->lastInsertId();
                if (org_has_sort_order()) {
                    db()->prepare('UPDATE org_units SET sort_order = ? WHERE id = ?')->execute([org_next_sort_order($parentId, $orgCompanyId), $unitId]);
                }
            }
            db()->prepare('DELETE FROM org_unit_managers WHERE unit_id = ?')->execute([$unitId]);
            $insertManager = db()->prepare('INSERT INTO org_unit_managers (unit_id, user_id, is_primary) VALUES (?, ?, ?)');
            foreach ($managerIds as $index => $managerId) {
                $insertManager->execute([$unitId, $managerId, $index === 0 ? 1 : 0]);
                db()->prepare('UPDATE users SET org_unit_id = ? WHERE id = ?')->execute([$unitId, $managerId]);
            }
            org_record_undo($unitId > 0 ? 'ویرایش واحد سازمانی' : 'افزودن واحد سازمانی', $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_unit_saved', null, ['unit_id' => $unitId, 'name' => $name, 'managers' => $managerIds]);
            flash('success', 'واحد سازمانی ذخیره شد.');
            redirect(org_company_redirect_url($orgCompanyId, 'unit-' . $unitId));
        }

        if ($action === 'delete_org_unit') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند ساختار سازمانی را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
            $unitId = max(0, (int) post_value('unit_id', '0'));
            $unit = org_unit_by_id($unitId);
            if ($unit === null) {
                throw new RuntimeException('واحد سازمانی یافت نشد.');
            }
            if ((string) $unit['unit_type'] === org_company_root_type($orgCompanyId)) {
                throw new RuntimeException('ریشهٔ شرکت قابل حذف نیست.');
            }
            $unitIds = org_unit_subtree_ids($unitId);
            $placeholders = implode(',', array_fill(0, count($unitIds), '?'));
            $managerIds = db()->prepare('SELECT manager_user_id FROM org_units WHERE id IN (' . $placeholders . ') AND manager_user_id IS NOT NULL');
            $managerIds->execute($unitIds);
            $managerIds = array_map('intval', $managerIds->fetchAll(PDO::FETCH_COLUMN));
            db()->beginTransaction();
            if ($managerIds !== []) {
                $managerPlaceholders = implode(',', array_fill(0, count($managerIds), '?'));
                db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE (org_unit_id IN (' . $placeholders . ') OR manager_user_id IN (' . $managerPlaceholders . ')) AND COALESCE(auth_source, "") <> "food"')->execute(array_merge($unitIds, $managerIds));
            } else {
                db()->prepare('UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE org_unit_id IN (' . $placeholders . ') AND COALESCE(auth_source, "") <> "food"')->execute($unitIds);
            }
            db()->prepare('DELETE m FROM org_unit_managers m JOIN users u ON u.id = m.user_id WHERE m.unit_id IN (' . $placeholders . ') AND COALESCE(u.auth_source, "") <> "food"')->execute($unitIds);
            db()->prepare('UPDATE org_units SET is_active = 0, manager_user_id = NULL WHERE id IN (' . $placeholders . ')')->execute($unitIds);
            db_commit('index.php');
            org_record_undo('حذف واحد سازمانی', $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'org_unit_deleted', null, ['unit_id' => $unitId, 'subtree_ids' => $unitIds]);
            flash('success', 'واحد و زیرمجموعه‌های آن غیرفعال شدند.');
            redirect(org_company_redirect_url($orgCompanyId));
        }

        if ($action === 'save_user_org') {
            $orgAdmin = require_permission('org.manage');
            if (!user_can_manage_organization($orgAdmin)) {
                throw new RuntimeException('فقط ادمین اصلی می‌تواند عضویت سازمانی را تغییر دهد.');
            }
            org_ensure_schema();
            $orgUndoSnapshot = org_snapshot($orgCompanyId);
            $targetUserId = max(0, (int) post_value('user_id', '0'));
            $unitId = max(0, (int) post_value('org_unit_id', '0'));
            $managerId = max(0, (int) post_value('manager_user_id', '0'));
            $target = db()->prepare("SELECT id FROM users WHERE id = ? AND COALESCE(auth_source, '') <> 'food' LIMIT 1");
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
            org_record_undo('تغییر عضویت سازمانی کاربر', $orgUndoSnapshot, $orgCompanyId);
            save_audit((int) $orgAdmin['id'], 'user_org_updated', null, ['user_id' => $targetUserId, 'unit_id' => $unitId, 'manager_user_id' => $managerId]);
            flash('success', 'عضویت سازمانی کاربر ذخیره شد.');
            redirect(org_company_redirect_url($orgCompanyId, 'user-' . $targetUserId));
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
            redirect('index.php?page=settings#org-admin-repair');
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
            redirect('index.php?page=settings#org-admin-repair');
        }
