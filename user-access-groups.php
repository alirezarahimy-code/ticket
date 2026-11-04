<?php
declare(strict_types=1);

/**
 * Public access-group administration helpers.
 *
 * These tables are created by migrations/023-user-access-groups.sql (and by the
 * fresh-install schemas). The permission check deliberately degrades to the
 * existing role-only behavior until that additive migration is present.
 */

function user_access_groups_schema_ready(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        db()->query('SELECT id FROM user_access_groups LIMIT 0');
        db()->query('SELECT group_id FROM user_access_group_members LIMIT 0');
        db()->query('SELECT group_id FROM user_access_group_permissions LIMIT 0');
        $ready = true;
    } catch (Throwable) {
        $ready = false;
    }
    return $ready;
}

/** @return list<array<string, mixed>> */
function user_access_groups_all(): array
{
    if (!user_access_groups_schema_ready()) {
        return [];
    }

    $query = db()->query(
        "SELECT g.id, g.name, g.description, g.created_at, g.updated_at,
                COUNT(DISTINCT CASE WHEN group_user.is_active = 1 AND group_user.auth_source <> 'food' THEN m.user_id END) AS member_count,
                COUNT(DISTINCT p.permission_code) AS permission_count
         FROM user_access_groups g
         LEFT JOIN user_access_group_members m ON m.group_id = g.id
         LEFT JOIN users group_user ON group_user.id = m.user_id
         LEFT JOIN user_access_group_permissions p ON p.group_id = g.id
         GROUP BY g.id, g.name, g.description, g.created_at, g.updated_at
         ORDER BY g.name ASC, g.id ASC"
    );
    return $query->fetchAll();
}

/** @return list<array<string, mixed>> */
function user_access_groups_admin_users(): array
{
    return db()->query(
        "SELECT id, username, full_name, role, auth_source
         FROM users
         WHERE is_active = 1 AND auth_source <> 'food'
         ORDER BY full_name ASC, username ASC"
    )->fetchAll();
}

/** @return array<int, true> */
function user_access_group_member_set(int $groupId): array
{
    if ($groupId <= 0 || !user_access_groups_schema_ready()) {
        return [];
    }
    $query = db()->prepare(
        "SELECT gm.user_id
         FROM user_access_group_members gm
         INNER JOIN users u ON u.id = gm.user_id
         WHERE gm.group_id = ? AND u.is_active = 1 AND u.auth_source <> 'food'"
    );
    $query->execute([$groupId]);
    return array_fill_keys(array_map('intval', $query->fetchAll(PDO::FETCH_COLUMN)), true);
}

/** @return array<int, array<int, true>> */
function user_access_groups_member_sets(): array
{
    if (!user_access_groups_schema_ready()) {
        return [];
    }
    $rows = db()->query(
        "SELECT gm.group_id, gm.user_id
         FROM user_access_group_members gm
         INNER JOIN users u ON u.id = gm.user_id
         WHERE u.is_active = 1 AND u.auth_source <> 'food'"
    )->fetchAll();
    $sets = [];
    foreach ($rows as $row) {
        $groupId = (int) $row['group_id'];
        $sets[$groupId][(int) $row['user_id']] = true;
    }
    return $sets;
}

/** @return array<int, array<string, true>> */
function user_access_groups_permission_sets(): array
{
    if (!user_access_groups_schema_ready()) {
        return [];
    }
    $rows = db()->query('SELECT group_id, permission_code FROM user_access_group_permissions')->fetchAll();
    $codesByGroup = [];
    foreach ($rows as $row) {
        $codesByGroup[(int) $row['group_id']][] = (string) $row['permission_code'];
    }

    $valid = permission_all_codes();
    $sets = [];
    foreach ($codesByGroup as $groupId => $codes) {
        $codes = array_values(array_intersect(permission_food_legacy_codes($codes), $valid));
        $sets[$groupId] = array_fill_keys($codes, true);
    }
    return $sets;
}

/** @return array<int, true> */
function user_access_group_permission_set_for_group(int $groupId): array
{
    if ($groupId <= 0 || !user_access_groups_schema_ready()) {
        return [];
    }
    $query = db()->prepare('SELECT permission_code FROM user_access_group_permissions WHERE group_id = ?');
    $query->execute([$groupId]);
    $validCodes = permission_all_codes();
    $codes = array_values(array_intersect(
        permission_food_legacy_codes(array_map('strval', $query->fetchAll(PDO::FETCH_COLUMN))),
        $validCodes
    ));
    return array_fill_keys($codes, true);
}

function user_access_group_role_label(string $role): string
{
    return permission_roles()[$role] ?? ($role !== '' ? $role : 'بدون نقش');
}

/** Render the existing permission catalog in the same matrix pattern as role permissions. */
function user_access_group_permission_matrix_markup(array $selectedCodes, array $catalog): string
{
    $selected = array_fill_keys($selectedCodes, true);
    $editMap = permission_edit_map();
    $editChildren = array_values($editMap);
    $html = '';

    foreach ($catalog as $group) {
        $groupCodes = array_map(static fn (array $item): string => (string) $item['code'], $group['items']);
        $checkedCount = count(array_intersect($groupCodes, $selectedCodes));
        $isEmpty = $checkedCount === 0;
        $isFull = $checkedCount === count($groupCodes);
        $countClass = $isFull ? 'full' : ($isEmpty ? 'empty' : 'partial');
        $pct = count($groupCodes) > 0 ? (int) round($checkedCount / count($groupCodes) * 100) : 0;
        $groupKey = (string) $group['key'];
        $groupLabel = (string) $group['label'];

        $html .= '<div class="group-card" data-perm-group-card="' . e($groupKey) . '" data-group-label="' . e($groupLabel) . '">'
            . '<div class="group-header" data-group-toggle="' . e($groupKey) . '"><span class="group-toggle">◀</span>'
            . '<span class="group-icon">' . permission_icon('g-' . $groupKey) . '</span>'
            . '<span class="group-info"><span class="group-name">' . e($groupLabel) . '</span></span>'
            . '<span class="group-progress"><span class="group-count ' . $countClass . '" data-group-count>' . $checkedCount . ' از ' . count($groupCodes) . '</span>'
            . '<span class="group-bar"><span class="group-bar-fill" style="width:' . $pct . '%"></span></span></span>'
            . '<button type="button" class="group-select-all" data-group-all="' . e($groupKey) . '">' . ($isFull ? 'حذف همه' : 'انتخاب همه') . '</button></div><div class="group-body">';

        $itemsByCode = [];
        foreach ($group['items'] as $item) {
            $itemsByCode[(string) $item['code']] = $item;
        }
        $renderPermission = static function (array $item, bool $isChild) use (&$html, $selected, $groupKey): void {
            $code = (string) $item['code'];
            $label = (string) $item['label'];
            $checked = isset($selected[$code]);
            $restriction = $code === 'cddvd.edit'
                ? '<small class="perm-lock-note">برای نقش‌های مجاز CD/DVD</small>'
                : '';
            $html .= '<label class="perm-item' . ($isChild ? ' perm-child' : '') . ($checked ? ' checked' : '') . '" data-perm-item'
                . ($isChild ? ' data-perm-edit="1"' : '')
                . ' data-perm-label="' . e($label) . '" data-perm-group="' . e($groupKey) . '">'
                . '<input type="checkbox" name="permissions[]" value="' . e($code) . '"' . ($checked ? ' checked' : '')
                . ($code === 'cddvd.edit' ? ' title="این دسترسی فقط برای نقش‌های مجاز در کنترل CD/DVD مؤثر است."' : '') . '>'
                . '<span class="perm-check">✓</span><span class="perm-label">' . e($label) . '</span>' . $restriction . '</label>';
        };

        foreach ($group['items'] as $item) {
            $code = (string) $item['code'];
            if (in_array($code, $editChildren, true)) {
                continue;
            }
            $renderPermission($item, false);
            if (isset($editMap[$code], $itemsByCode[$editMap[$code]])) {
                $renderPermission($itemsByCode[$editMap[$code]], true);
            }
        }
        $html .= '</div></div>';
    }

    return $html . '<div class="perm-empty" data-perm-empty hidden>دسترسی‌ای با این عبارت یافت نشد.</div>';
}

function user_access_groups_redirect(int $groupId = 0): never
{
    $url = 'index.php?page=settings';
    if ($groupId > 0) {
        $url .= '&access_group_id=' . $groupId;
    }
    redirect($url . '#access-groups');
}
