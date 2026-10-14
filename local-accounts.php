<?php
declare(strict_types=1);

/**
 * حساب‌های محلی پرسنل غذا (نسخهٔ ۱.۵۰)
 * ----------------------------------------------------------------
 * - هر پرسنل غذایی که در دامین نیست، حساب محلی دارد: نام کاربری = کد پرسنلی.
 * - رمز اولیه ثابت است و در اولین ورود باید تغییر کند (must_change_password = 1).
 * - تغییر رمز: کاربر خودش (با رمز فعلی). ریست رمز: فقط ادمین اصلی.
 * - انتخاب نگهبان: فقط ادمین اصلی، در زیر فرم «کاربران محلی» با تیک زدن.
 */

if (!defined('LAC_DEFAULT_PASSWORD')) {
    define('LAC_DEFAULT_PASSWORD', 'A@a12345');
}

/** هش رمز پیش‌فرض برای حساب‌های محلی. */
function lac_default_hash(): string
{
    return password_hash(LAC_DEFAULT_PASSWORD, PASSWORD_DEFAULT);
}

/** آیا این کاربر باید به سفارش غذا هدایت شود (کاربر محلی یا نگهبان)؟ */
function lac_is_local_role(?array $user): bool
{
    return $user !== null && in_array((string) ($user['role'] ?? ''), ['local_user', 'guard'], true);
}

/** فرم تغییر رمز (برای کاربر وارد شده). */
function lac_change_password_request(): never
{
    $user = require_login();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf();
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([(int) $user['id']]);
        $hash = (string) $stmt->fetchColumn();
        if ($hash === '' || !password_verify($current, $hash)) {
            flash('danger', 'رمز فعلی درست نیست.');
        } elseif (mb_strlen($new, 'UTF-8') < 8) {
            flash('danger', 'رمز جدید باید حداقل ۸ نویسه باشد.');
        } elseif ($new !== $confirm) {
            flash('danger', 'تکرار رمز جدید یکسان نیست.');
        } elseif ($new === LAC_DEFAULT_PASSWORD) {
            flash('danger', 'رمز جدید نباید همان رمز پیش‌فرض باشد.');
        } else {
            db()->prepare('UPDATE users SET password_hash = ?, must_change_password = 0 WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), (int) $user['id']]);
            flash('success', 'رمز شما تغییر کرد.');
            redirect('index.php');
        }
        redirect('index.php?page=change-password');
    }

    $forced = (int) ($user['must_change_password'] ?? 0) === 1;
    render_header('تغییر رمز', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">حساب کاربری</span><h1>تغییر رمز</h1>';
    if ($forced) {
        echo '<p>برای ادامه باید رمز خود را تغییر دهید.</p>';
    }
    echo '</div></section>';
    echo '<div class="panel"><form method="post" action="index.php?page=change-password">' . csrf_field();
    echo '<p><label>رمز فعلی<br><input type="password" name="current_password" required autocomplete="current-password"></label></p>';
    echo '<p><label>رمز جدید (حداقل ۸ نویسه)<br><input type="password" name="new_password" required minlength="8" autocomplete="new-password"></label></p>';
    echo '<p><label>تکرار رمز جدید<br><input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"></label></p>';
    echo '<button type="submit" class="button primary">ذخیرهٔ رمز جدید</button></form></div>';
    render_footer();
    exit;
}

/** زیر فرم «کاربران محلی» (فقط ادمین اصلی): جدا از کاربران دامین؛ انتخاب نگهبان با تیک و ریست رمز. */
function lac_admin_request(): never
{
    $admin = require_primary_admin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf();
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'save_roles') {
            $guardIds = array_map('intval', (array) ($_POST['guard'] ?? []));
            $changed = 0;
            $select = db()->prepare("SELECT id, role FROM users WHERE auth_source = 'local' AND is_primary_admin = 0 AND role IN ('local_user','guard')");
            $select->execute();
            $update = db()->prepare('UPDATE users SET role = ? WHERE id = ?');
            foreach ($select->fetchAll() as $row) {
                $newRole = in_array((int) $row['id'], $guardIds, true) ? 'guard' : 'local_user';
                if ($newRole !== (string) $row['role']) {
                    $update->execute([$newRole, (int) $row['id']]);
                    $changed++;
                }
            }
            if (function_exists('save_audit')) {
                save_audit((int) ($admin['id'] ?? 0), 'local_guard_roles_saved', null, ['changed' => $changed]);
            }
            flash('success', 'نقش‌های نگهبان ذخیره شد. تعداد حساب‌های تغییریافته: ' . $changed . '.');
        } elseif ($action === 'reset') {
            $targetId = (int) ($_POST['user_id'] ?? 0);
            $check = db()->prepare("SELECT id FROM users WHERE id = ? AND auth_source = 'local' AND is_primary_admin = 0 LIMIT 1");
            $check->execute([$targetId]);
            if ($targetId > 0 && $check->fetchColumn() !== false) {
                db()->prepare('UPDATE users SET password_hash = ?, must_change_password = 1 WHERE id = ?')
                    ->execute([lac_default_hash(), $targetId]);
                if (function_exists('save_audit')) {
                    save_audit((int) ($admin['id'] ?? 0), 'local_password_reset', null, ['user_id' => $targetId]);
                }
                flash('success', 'رمز به حالت پیش‌فرض برگشت و در ورود بعدی باید تغییر کند.');
            } else {
                flash('danger', 'حساب محلی پیدا نشد.');
            }
        }
        redirect('index.php?page=local-accounts');
    }

    $rows = db()->query("SELECT id, username, full_name, national_code, role, is_active, must_change_password, is_primary_admin FROM users WHERE auth_source = 'local' ORDER BY role = 'guard' DESC, username, id")->fetchAll();
    $roles = permission_roles();
    $guardCount = 0;
    foreach ($rows as $row) {
        if ((string) $row['role'] === 'guard') {
            $guardCount++;
        }
    }
    render_header('کاربران محلی', $admin);
    echo '<section class="page-heading"><div><span class="eyebrow">تنظیمات کاربران</span><h1>کاربران محلی</h1>';
    echo '<p>این فهرست کاملاً جدا از کاربران دامین است. نام کاربری هر حساب کد پرسنلی است. ';
    echo 'کاربری که تیک «نگهبان» بخورد دسترسی نگهبان می‌گیرد؛ بقیه کاربر محلی می‌مانند.</p></div></section>';
    echo '<div class="panel">';
    echo '<p>تعداد کل حساب‌های محلی: ' . count($rows) . ' — نگهبان‌ها: ' . $guardCount . '</p>';
    echo '<p><label>جستجو (نام، نام کاربری یا کد ملی): <input type="search" id="lacFilter" data-lac-filter autocomplete="off" placeholder="نام، نام کاربری یا کد ملی"></label> <small data-lac-count aria-live="polite"></small></p>';

    echo '<form method="post" action="index.php?page=local-accounts">' . csrf_field() . '<input type="hidden" name="action" value="save_roles">';
    echo '<table class="data-table"><thead><tr><th>نگهبان</th><th>نام کاربری (کد پرسنلی)</th><th>نام کامل</th><th>کد ملی</th><th>نقش</th><th>فعال</th><th>تغییر رمز اجباری</th><th>ریست رمز</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $id = (int) $row['id'];
        $editable = (int) $row['is_primary_admin'] !== 1 && in_array((string) $row['role'], ['local_user', 'guard'], true);
        $national = (string) ($row['national_code'] ?? '');
        echo '<tr data-lac="1"><td>';
        echo $editable
            ? '<input type="checkbox" name="guard[]" value="' . $id . '"' . ((string) $row['role'] === 'guard' ? ' checked' : '') . '>'
            : '—';
        echo '</td><td>' . e((string) $row['username']) . '</td><td>' . e((string) $row['full_name']) . '</td><td>' . e($national !== '' ? $national : '—') . '</td>';
        echo '<td>' . e($roles[(string) $row['role']] ?? (string) $row['role']) . '</td>';
        echo '<td>' . ((int) $row['is_active'] === 1 ? 'بله' : 'خیر') . '</td>';
        echo '<td>' . ((int) $row['must_change_password'] === 1 ? 'بله' : 'خیر') . '</td>';
        echo '<td>';
        if ($editable) {
            echo '<button type="submit" form="lac-reset-' . $id . '" class="button secondary" onclick="return confirm(\'رمز این حساب به حالت پیش‌فرض برگردد؟\')">ریست رمز</button>';
        }
        echo '</td></tr>';
    }
    echo '</tbody></table>';
    echo '<p><button type="submit" class="button primary">ذخیرهٔ نگهبان‌ها</button></p></form>';
    // فرم‌های ریست رمز جدا هستند (داخل فرم اصلی نیستند)
    foreach ($rows as $row) {
        if ((int) $row['is_primary_admin'] === 1 || !in_array((string) $row['role'], ['local_user', 'guard'], true)) {
            continue;
        }
        echo '<form id="lac-reset-' . (int) $row['id'] . '" method="post" action="index.php?page=local-accounts" style="display:none">' . csrf_field();
        echo '<input type="hidden" name="action" value="reset"><input type="hidden" name="user_id" value="' . (int) $row['id'] . '"></form>';
    }
    echo '</div>';
    render_footer();
    exit;
}
