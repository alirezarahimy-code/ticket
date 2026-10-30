<?php

declare(strict_types=1);

/**
 * actions/role-actions.php — ۴ اکشن POST نقش‌های کلیدی و دسترسی‌ها (فاز ۲ بازسازی، برش ۵ — نسخهٔ ۱.۶۲.۱۶).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   save_key_roles          ذخیرهٔ نقش‌های کلیدی سامانه (سرپرست‌ها و مسئولان منتخب)
 *   save_role_permissions   ذخیرهٔ دسترسی‌های یک نقش در ماتریس مجوزها
 *   reset_role_defaults     بازگرداندن نقش‌های کلیدی به پیش‌فرض
 *   reset_role_permissions  بازگرداندن دسترسی‌های یک نقش به پیش‌فرض
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده؛ تنها موارد
 * function داخل بلوک، کلوژرهای static بی‌نام‌اند که برای include کاملاً بی‌خطرند (اسکن شد).
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: نقش‌های کلیدی و دسترسی‌ها ─────
        if ($action === 'save_key_roles') {
            $rolesAdmin = require_permission('settings.key_roles_edit');
            $collectRoleIds = static function (string $field): array {
                $raw = $_POST[$field] ?? [];
                if (!is_array($raw)) {
                    $raw = [];
                }
                $ids = [];
                foreach ($raw as $value) {
                    $id = (int) $value;
                    if ($id > 0 && !in_array($id, $ids, true)) {
                        $ids[] = $id;
                    }
                }
                return array_slice($ids, 0, 2);
            };
            $supervisors = $collectRoleIds('supervisor_ids');
            $inspectors = $collectRoleIds('inspection_ids');
            $itExperts = $collectRoleIds('it_expert_ids');
            $allIds = array_values(array_unique(array_merge($supervisors, $inspectors, $itExperts)));
            if ($allIds !== []) {
                $placeholders = implode(', ', array_fill(0, count($allIds), '?'));
                $validQuery = db()->prepare('SELECT id FROM users WHERE id IN (' . $placeholders . ') AND is_active = 1');
                $validQuery->execute($allIds);
                $validIds = array_map('intval', $validQuery->fetchAll(PDO::FETCH_COLUMN));
                $filterValid = static fn (array $list): array => array_values(array_intersect($list, $validIds));
                $supervisors = $filterValid($supervisors);
                $inspectors = $filterValid($inspectors);
                $itExperts = $filterValid($itExperts);
            }
            $oldSupervisors = key_role_user_ids('supervisor');
            $oldExperts = key_role_user_ids('it_expert');
            // The key-role editor may manage supervisor/inspection assignments, but only
            // the primary admin can add or remove an IT expert (which grants primary-admin status).
            if (!user_is_primary_admin($rolesAdmin)) {
                $itExperts = $oldExperts;
            }
            db()->beginTransaction();
            try {
                foreach (array_diff($supervisors, $oldSupervisors) as $id) {
                    db()->prepare('UPDATE users SET role = "supervisor" WHERE id = ? AND role NOT IN ("admin", "primary_admin")')->execute([$id]);
                }
                foreach (array_diff($oldSupervisors, $supervisors) as $id) {
                    db()->prepare('UPDATE users SET role = "user" WHERE id = ? AND role = "supervisor"')->execute([$id]);
                }
                foreach (array_diff($itExperts, $oldExperts) as $id) {
                    db()->prepare('UPDATE users SET role = "primary_admin", is_primary_admin = 1, is_active = 1 WHERE id = ?')->execute([$id]);
                }
                foreach (array_diff($oldExperts, $itExperts) as $id) {
                    db()->prepare('UPDATE users SET is_primary_admin = 0 WHERE id = ?')->execute([$id]);
                    db()->prepare('UPDATE users SET role = "user" WHERE id = ? AND role IN ("admin", "primary_admin")')->execute([$id]);
                }
                $primaryCount = (int) db()->query('SELECT COUNT(*) FROM users WHERE role = "primary_admin" AND is_primary_admin = 1 AND is_active = 1')->fetchColumn();
                if ($primaryCount < 1) {
                    throw new RuntimeException('حداقل یک ادمین اصلی باید باقی بماند.');
                }
                if (db()->inTransaction()) {
                    db_commit('index.php');
                }
            } catch (Throwable $roleException) {
                if (db()->inTransaction()) {
                    db_rollback('index.php');
                }
                throw $roleException;
            }
            save_setting('role_supervisor_ids', implode(',', $supervisors));
            save_setting('role_inspection_ids', implode(',', $inspectors));
            save_setting('role_it_expert_ids', implode(',', $itExperts));
            save_setting('cd_dvd_inspection_user_id', (string) ($inspectors[0] ?? 0));
            save_audit((int) $rolesAdmin['id'], 'key_roles_updated', null, ['supervisors' => $supervisors, 'inspectors' => $inspectors, 'it_experts' => $itExperts]);
            flash('success', 'نقش‌های کلیدی ذخیره شد (سوپروایز: ' . count($supervisors) . '، بازرسی: ' . count($inspectors) . '، کارشناس IT: ' . count($itExperts) . ').');
            redirect('index.php?page=settings#key-roles');
        }

        if ($action === 'save_role_permissions') {
            $permAdmin = require_primary_admin();
            $targetRole = post_value('role');
            if (!isset(permission_roles()[$targetRole])) {
                flash('error', 'نقش نامعتبر است.');
                redirect('index.php?page=settings#roles');
            }
            $codes = $_POST['permissions'] ?? [];
            if (!is_array($codes)) {
                $codes = [];
            }
            role_permissions_save($targetRole, $codes);
            save_audit((int) $permAdmin['id'], 'role_permissions_updated', null, ['role' => $targetRole, 'permissions' => array_values($codes)]);
            flash('success', 'دسترسی‌های نقش «' . permission_roles()[$targetRole] . '» ذخیره شد.');
            redirect('index.php?page=settings#roles');
        }

        if ($action === 'reset_role_defaults') {
            $permAdmin = require_primary_admin();
            $targetRole = post_value('role');
            if (!isset(permission_roles()[$targetRole])) {
                flash('error', 'نقش نامعتبر است.');
                redirect('index.php?page=settings#roles');
            }
            role_permissions_reset($targetRole);
            save_audit((int) $permAdmin['id'], 'reset_role_defaults', null, ['role' => $targetRole]);
            flash('success', 'دسترسی‌های نقش «' . permission_roles()[$targetRole] . '» به پیش‌فرض داخل کد برگشت.');
            redirect('index.php?page=settings#roles');
        }

        if ($action === 'reset_role_permissions') {
            $permAdmin = require_primary_admin();
            // ۱.۳۷.۷ — به‌جای ریختن دوبارهٔ پیش‌فرض‌ها در جدول (که نقش را «ویرایش‌شده» علامت می‌زد)،
            // همهٔ نقش‌ها به حالت «دست‌نخورده» برمی‌گردند تا از این پس پیش‌فرض‌های کد اعمال شوند.
            foreach (array_keys(permission_roles()) as $resetRole) {
                role_permissions_reset($resetRole);
            }
            save_audit((int) $permAdmin['id'], 'role_permissions_reset', null, ['roles' => array_keys(permission_roles())]);
            flash('success', 'همهٔ نقش‌ها به پیش‌فرض داخل کد بازگشتند.');
            redirect('index.php?page=settings#roles');
        }
