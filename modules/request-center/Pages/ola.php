<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/**
 * Request Center — «سیاست‌های OLA».
 * پیش‌تر صفحهٔ مستقل ola.php بود؛ با تصمیم مالک ۲۰۲۶-۱۱-۱۴ به ماژول منتقل شد.
 * مشاهده با «ola.view» و ثبت/ویرایش با «ola.manage» (بدون تغییر کدهای دسترسی).
 * اکشن‌های POST در modules/request-center/Actions/ola-actions.php هستند.
 */

if ($page === 'ola') {
    require_once dirname(__DIR__) . '/lib/http-errors.php';
    $user = require_login();
    if (!user_can($user, 'ola.view')) {
        request_center_error_page(403, 'دسترسی مجاز نیست', 'دسترسی به بخش OLA برای نقش شما مجاز نیست.');
    }
    $tableCheck = db()->query("SHOW TABLES LIKE 'ola_policies'");
    if (!$tableCheck->fetchColumn()) {
        render_header('سیاست‌های OLA', $user);
        echo '<section class="card empty-state"><h2>ماژول OLA هنوز نصب نشده است.</h2><p>فایل <code>upgrade-1.8-final.sql</code> را یک‌بار روی پایگاه‌داده اجرا کنید.</p><a class="button" href="request-center.php">بازگشت به مرکز درخواست‌ها</a></section>';
        render_footer();
        exit;
    }

    ensure_default_ola_policies();
    $editId = (int) ($_GET['edit'] ?? 0);
    $editPolicy = null;
    if ($editId > 0) {
        $editQuery = db()->prepare('SELECT * FROM ola_policies WHERE id = ? LIMIT 1');
        $editQuery->execute([$editId]);
        $editPolicy = $editQuery->fetch() ?: null;
    }
    $departments = db()->query('SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name')->fetchAll();
    $policies = db()->query('SELECT o.*, d.name AS department_name FROM ola_policies o LEFT JOIN departments d ON d.id = o.department_id ORDER BY o.department_id IS NULL DESC, FIELD(o.priority, "critical", "urgent", "normal"), o.id')->fetchAll();
    $value = static fn (string $key, mixed $default = ''): string => e((string) ($editPolicy[$key] ?? $default));

    render_header('سیاست‌های OLA', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">Operational Level Agreement</span><h1>سیاست‌های OLA و Escalation</h1><p>مهلت پاسخ داخلی، حل داخلی و دو سطح پیگیری مدیریتی بر اساس ساعات کاری سامانه محاسبه می‌شوند.</p></div><a class="button secondary" href="request-center.php?page=reports">گزارش‌ها</a></section><section class="settings-grid"><div class="card form-card"><h2>' . ($editPolicy ? 'ویرایش سیاست OLA' : 'سیاست پیش‌فرض جدید') . '</h2><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="save_ola_policy"><input type="hidden" name="policy_id" value="' . (int) ($editPolicy['id'] ?? 0) . '"><div class="form-grid"><label class="full">نام سیاست<input name="name" required value="' . $value('name', 'OLA سراسری') . '"></label><label>اولویت<select name="priority">';
    foreach (['critical' => 'حیاتی', 'urgent' => 'فوری', 'normal' => 'عادی'] as $key => $label) {
        echo '<option value="' . $key . '" ' . (($editPolicy['priority'] ?? '') === $key ? 'selected' : '') . '>' . $label . '</option>';
    }
    echo '</select></label><label>معاونت<select name="department_id"><option value="">سراسری</option>';
    foreach ($departments as $department) {
        echo '<option value="' . (int) $department['id'] . '" ' . ((int) ($editPolicy['department_id'] ?? 0) === (int) $department['id'] ? 'selected' : '') . '>' . e($department['name']) . '</option>';
    }
    echo '</select></label><label>پاسخ داخلی، دقیقه<input type="number" min="1" name="response_minutes" value="' . $value('response_minutes', 60) . '" required></label><label>حل داخلی، دقیقه<input type="number" min="1" name="resolution_minutes" value="' . $value('resolution_minutes', 480) . '" required></label><label>Escalation سطح ۱، دقیقه<input type="number" min="1" name="escalation_1_minutes" value="' . $value('escalation_1_minutes', 480) . '" required></label><label>Escalation سطح ۲، دقیقه<input type="number" min="1" name="escalation_2_minutes" value="' . $value('escalation_2_minutes', 960) . '" required></label></div><button class="button" type="submit">ذخیره سیاست</button></form></div><div class="card form-card"><h2>منطق اجرا</h2><p class="muted">هر درخواست جدید بر اساس معاونت و اولویت به دقیق‌ترین سیاست فعال متصل می‌شود. اگر سیاست معاونت وجود نداشته باشد، سیاست سراسری همان اولویت استفاده می‌شود.</p><div class="category-list"><div><span><strong>پاسخ داخلی</strong><small>اگر اولین پاسخ ثبت نشود، اعلان OLA ساخته می‌شود.</small></span></div><div><span><strong>حل داخلی</strong><small>عبور از زمان حل، اعلان جداگانه برای تیم رسیدگی می‌سازد.</small></span></div><div><span><strong>Escalation سطح ۱</strong><small>برای پیگیری مدیریتی اولیه اعلان ایجاد می‌شود.</small></span></div><div><span><strong>Escalation سطح ۲</strong><small>سوپروایزرها و ادمین‌ها نیز اعلان مستقیم می‌گیرند.</small></span></div></div></div></section><section class="card form-card"><h2>سیاست‌های فعال و غیرفعال</h2><div class="category-list">';
    foreach ($policies as $policy) {
        echo '<div><span><strong>' . e($policy['name']) . '</strong><small>' . e(priority_label($policy['priority'])) . ' • ' . e($policy['department_name'] ?: 'سراسری') . ' • پاسخ ' . (int) $policy['response_minutes'] . ' دقیقه • حل ' . (int) $policy['resolution_minutes'] . ' دقیقه • E1 ' . (int) $policy['escalation_1_minutes'] . ' • E2 ' . (int) $policy['escalation_2_minutes'] . '</small></span><span class="actions"><em class="status ' . ((int) $policy['is_active'] === 1 ? 'open' : 'closed') . '">' . ((int) $policy['is_active'] === 1 ? 'فعال' : 'غیرفعال') . '</em><a class="mini-button" href="request-center.php?page=ola&edit=' . (int) $policy['id'] . '">ویرایش</a><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="toggle_ola_policy"><input type="hidden" name="policy_id" value="' . (int) $policy['id'] . '"><button class="mini-button" type="submit">تغییر وضعیت</button></form></span></div>';
    }
    if (!$policies) {
        echo '<div class="empty-state"><h3>سیاستی ثبت نشده است.</h3></div>';
    }
    echo '</div></section>';
    render_footer();
    exit;
}
