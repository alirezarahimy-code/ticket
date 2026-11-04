<?php

declare(strict_types=1);

/**
 * actions/access-group-actions.php — create/update/delete public user-access groups.
 * Loaded only from the authenticated POST-action dispatcher in index.php.
 */
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

if ($action === 'create_access_group') {
    $admin = require_primary_admin();
    if (!user_access_groups_schema_ready()) {
        throw new RuntimeException('ساختار گروه‌های دسترسی آماده نیست؛ مهاجرت پایگاه‌داده را اجرا کنید.');
    }

    $name = trim((string) ($_POST['group_name'] ?? ''));
    $description = trim((string) ($_POST['group_description'] ?? ''));
    if ($name === '' || mb_strlen($name, 'UTF-8') > 120 || mb_strlen($description, 'UTF-8') > 500) {
        throw new RuntimeException('نام گروه الزامی است و توضیح آن باید حداکثر ۵۰۰ نویسه باشد.');
    }
    $duplicate = db()->prepare('SELECT id FROM user_access_groups WHERE name = ? LIMIT 1');
    $duplicate->execute([$name]);
    if ($duplicate->fetchColumn() !== false) {
        throw new RuntimeException('گروهی با این نام از قبل وجود دارد.');
    }

    $insert = db()->prepare('INSERT INTO user_access_groups (name, description, created_by, updated_by) VALUES (?, ?, ?, ?)');
    $insert->execute([$name, $description !== '' ? $description : null, (int) $admin['id'], (int) $admin['id']]);
    $groupId = (int) db()->lastInsertId();
    save_audit((int) $admin['id'], 'access_group_created', null, ['group_id' => $groupId, 'name' => $name]);
    flash('success', 'گروه دسترسی ساخته شد؛ اعضا و مجوزهای آن را تنظیم کنید.');
    user_access_groups_redirect($groupId);
}

if ($action === 'save_access_group') {
    $admin = require_primary_admin();
    if (!user_access_groups_schema_ready()) {
        throw new RuntimeException('ساختار گروه‌های دسترسی آماده نیست؛ مهاجرت پایگاه‌داده را اجرا کنید.');
    }

    $groupId = max(0, (int) ($_POST['group_id'] ?? 0));
    $groupQuery = db()->prepare('SELECT id, name FROM user_access_groups WHERE id = ?');
    $groupQuery->execute([$groupId]);
    $oldGroup = $groupQuery->fetch();
    if (!$oldGroup) {
        throw new RuntimeException('گروه دسترسی پیدا نشد؛ صفحه را تازه کنید.');
    }

    $name = trim((string) ($_POST['group_name'] ?? ''));
    $description = trim((string) ($_POST['group_description'] ?? ''));
    if ($name === '' || mb_strlen($name, 'UTF-8') > 120 || mb_strlen($description, 'UTF-8') > 500) {
        throw new RuntimeException('نام گروه الزامی است و توضیح آن باید حداکثر ۵۰۰ نویسه باشد.');
    }
    $duplicate = db()->prepare('SELECT id FROM user_access_groups WHERE name = ? AND id <> ? LIMIT 1');
    $duplicate->execute([$name, $groupId]);
    if ($duplicate->fetchColumn() !== false) {
        throw new RuntimeException('گروه دیگری با این نام وجود دارد.');
    }

    $allowedCodes = permission_all_codes();
    $postedCodes = is_array($_POST['permissions'] ?? null) ? $_POST['permissions'] : [];
    $submittedCodes = [];
    foreach ($postedCodes as $code) {
        if (is_string($code) || is_int($code)) {
            $submittedCodes[] = (string) $code;
        }
    }
    $permissions = array_values(array_intersect(
        permission_food_legacy_codes(role_permissions_normalize_codes($submittedCodes)),
        $allowedCodes
    ));

    $postedMembers = is_array($_POST['members'] ?? null) ? $_POST['members'] : [];
    $requestedIdSet = [];
    foreach ($postedMembers as $value) {
        if (is_scalar($value)) {
            $id = (int) $value;
            if ($id > 0) {
                $requestedIdSet[$id] = $id;
            }
        }
    }
    $requestedIds = array_values($requestedIdSet);

    db()->beginTransaction();
    try {
        $update = db()->prepare('UPDATE user_access_groups SET name = ?, description = ?, updated_by = ? WHERE id = ?');
        $update->execute([$name, $description !== '' ? $description : null, (int) $admin['id'], $groupId]);

        $memberIds = [];
        foreach (array_chunk($requestedIds, 1000) as $memberChunk) {
            $placeholders = implode(', ', array_fill(0, count($memberChunk), '?'));
            $validMembers = db()->prepare(
                "SELECT id FROM users
                 WHERE id IN ($placeholders) AND is_active = 1 AND auth_source <> 'food'"
            );
            $validMembers->execute($memberChunk);
            $memberIds = array_merge($memberIds, array_map('intval', $validMembers->fetchAll(PDO::FETCH_COLUMN)));
        }

        // Keep existing disabled/non-interactive memberships intact. They cannot log in,
        // and preserving them avoids silently dropping membership during an edit.
        $preserved = db()->prepare(
            "SELECT gm.user_id
             FROM user_access_group_members gm
             INNER JOIN users u ON u.id = gm.user_id
             WHERE gm.group_id = ? AND (u.is_active <> 1 OR u.auth_source = 'food')"
        );
        $preserved->execute([$groupId]);
        $memberIds = array_values(array_unique(array_merge(
            $memberIds,
            array_map('intval', $preserved->fetchAll(PDO::FETCH_COLUMN))
        )));

        $existingMembersQuery = db()->prepare('SELECT user_id FROM user_access_group_members WHERE group_id = ?');
        $existingMembersQuery->execute([$groupId]);
        $existingMemberIds = array_map('intval', $existingMembersQuery->fetchAll(PDO::FETCH_COLUMN));
        $existingMemberSet = array_fill_keys($existingMemberIds, true);
        $desiredMemberSet = array_fill_keys($memberIds, true);

        $deleteMember = db()->prepare('DELETE FROM user_access_group_members WHERE group_id = ? AND user_id = ?');
        foreach ($existingMemberIds as $existingMemberId) {
            if (!isset($desiredMemberSet[$existingMemberId])) {
                $deleteMember->execute([$groupId, $existingMemberId]);
            }
        }
        $insertMember = db()->prepare('INSERT INTO user_access_group_members (group_id, user_id, added_by) VALUES (?, ?, ?)');
        foreach ($memberIds as $memberId) {
            if (!isset($existingMemberSet[$memberId])) {
                $insertMember->execute([$groupId, $memberId, (int) $admin['id']]);
            }
        }

        db()->prepare('DELETE FROM user_access_group_permissions WHERE group_id = ?')->execute([$groupId]);
        $insertPermission = db()->prepare('INSERT INTO user_access_group_permissions (group_id, permission_code, assigned_by) VALUES (?, ?, ?)');
        foreach ($permissions as $permission) {
            $insertPermission->execute([$groupId, $permission, (int) $admin['id']]);
        }
        db_commit('index.php');
    } catch (Throwable $exception) {
        if (db()->inTransaction()) {
            db_rollback('index.php');
        }
        throw $exception;
    }

    user_access_group_permissions_flush_cache();
    save_audit((int) $admin['id'], 'access_group_updated', null, [
        'group_id' => $groupId,
        'name' => $name,
        'member_count' => count($memberIds),
        'permission_count' => count($permissions),
    ]);
    flash('success', 'اعضای گروه و دسترسی‌های تکمیلی آن ذخیره شد.');
    user_access_groups_redirect($groupId);
}

if ($action === 'delete_access_group') {
    $admin = require_primary_admin();
    if (!user_access_groups_schema_ready()) {
        throw new RuntimeException('ساختار گروه‌های دسترسی آماده نیست؛ مهاجرت پایگاه‌داده را اجرا کنید.');
    }

    $groupId = max(0, (int) ($_POST['group_id'] ?? 0));
    $groupQuery = db()->prepare('SELECT id, name FROM user_access_groups WHERE id = ?');
    $groupQuery->execute([$groupId]);
    $group = $groupQuery->fetch();
    if (!$group) {
        throw new RuntimeException('گروه دسترسی پیدا نشد؛ صفحه را تازه کنید.');
    }

    db()->prepare('DELETE FROM user_access_groups WHERE id = ?')->execute([$groupId]);
    user_access_group_permissions_flush_cache();
    save_audit((int) $admin['id'], 'access_group_deleted', null, ['group_id' => $groupId, 'name' => (string) $group['name']]);
    flash('success', 'گروه دسترسی و عضویت‌ها و مجوزهای وابستهٔ آن حذف شد.');
    user_access_groups_redirect();
}
