<?php
declare(strict_types=1);

// محافظ: این فایل فقط از طریق نقاط ورود معتبر (index.php هسته یا request-center.php) بارگذاری می‌شود؛ دسترسی مستقیم قطعاً ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/** Request Center service catalog administration page in legacy front-controller scope. */

if ($page === 'services') {
    $serviceUser = require_login();
    $isServiceAdmin = is_admin_role((string) $serviceUser['role']) || (int) ($serviceUser['is_primary_admin'] ?? 0) === 1;
    $serviceGroupFilter = valid_choice((string) ($_GET['group'] ?? ''), ['', 'it', 'support'], '');
    $listSql = 'SELECT s.*, d.name AS department_name, c.name AS category_name, hu.name AS handling_unit_name, COUNT(f.id) AS field_count FROM service_catalog s LEFT JOIN departments d ON d.id = s.department_id LEFT JOIN categories c ON c.id = s.category_id LEFT JOIN handling_units hu ON hu.id = s.handling_unit_id LEFT JOIN service_catalog_fields f ON f.service_id = s.id WHERE s.is_active = 1';
    $listParams = [];
    if ($serviceGroupFilter !== '') {
        $listSql .= ' AND s.service_group = ?';
        $listParams[] = $serviceGroupFilter;
    }
    $listSql .= ' GROUP BY s.id, d.name, c.name, hu.name ORDER BY s.service_group, s.name';
    $listQuery = db()->prepare($listSql);
    $listQuery->execute($listParams);
    $services = $listQuery->fetchAll();
    $serviceFieldsById = [];
    if ($services) {
        $serviceIds = array_map(static fn (array $service): int => (int) $service['id'], $services);
        $fieldQuery = db()->prepare('SELECT * FROM service_catalog_fields WHERE service_id IN (' . implode(',', array_fill(0, count($serviceIds), '?')) . ') ORDER BY service_id, sort_order, id');
        $fieldQuery->execute($serviceIds);
        foreach ($fieldQuery->fetchAll() as $field) {
            $serviceFieldsById[(int) $field['service_id']][] = $field;
        }
    }
    $categories = db()->query('SELECT * FROM categories ORDER BY is_active DESC, service_group, name')->fetchAll();
    $handlingUnits = db()->query('SELECT id, code, name FROM handling_units WHERE is_active = 1 ORDER BY code')->fetchAll();
    $serviceDepartments = db()->query('SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name')->fetchAll();
    $categoryOptions = static function (int $selected) use ($categories): string {
        $html = '<option value="">بدون دسته</option>';
        foreach ($categories as $category) {
            if ((int) $category['is_active'] !== 1) {
                continue;
            }
            $html .= '<option value="' . (int) $category['id'] . '" ' . ($selected === (int) $category['id'] ? 'selected' : '') . '>' . e($category['name']) . '</option>';
        }
        return $html;
    };
    $handlingOptions = static function (int $selected) use ($handlingUnits): string {
        $html = '';
        foreach ($handlingUnits as $unit) {
            $html .= '<option value="' . (int) $unit['id'] . '" ' . ($selected === (int) $unit['id'] ? 'selected' : '') . '>' . e($unit['name']) . '</option>';
        }
        return $html;
    };
    $departmentOptions = static function (int $selected) use ($serviceDepartments): string {
        $html = '<option value="">سراسری</option>';
        foreach ($serviceDepartments as $department) {
            $html .= '<option value="' . (int) $department['id'] . '" ' . ($selected === (int) $department['id'] ? 'selected' : '') . '>' . e($department['name']) . '</option>';
        }
        return $html;
    };
    $ticketTypeOptions = static function (string $selected): string {
        $types = ['incident' => 'رخداد', 'request' => 'درخواست خدمت', 'problem' => 'مشکل ریشه‌ای', 'change' => 'تغییر'];
        $html = '';
        foreach ($types as $value => $label) {
            $html .= '<option value="' . $value . '" ' . ($selected === $value ? 'selected' : '') . '>' . $label . '</option>';
        }
        return $html;
    };
    $priorityOptions = static function (string $selected): string {
        $items = ['normal' => 'عادی', 'urgent' => 'فوری', 'critical' => 'حیاتی'];
        $html = '';
        foreach ($items as $value => $label) {
            $html .= '<option value="' . $value . '" ' . ($selected === $value ? 'selected' : '') . '>' . $label . '</option>';
        }
        return $html;
    };
    // فرم مشترک ساخت/ویرایش خدمت (بدون تگ form، تا در مودال و فرم ایجاد استفاده شود)
    $serviceFormFields = static function (array $service) use ($categoryOptions, $handlingOptions, $departmentOptions, $ticketTypeOptions, $priorityOptions): string {
        $group = (string) ($service['service_group'] ?? 'it');
        return '<div class="form-grid">'
            . '<label>نام خدمت<input name="service_name" required value="' . e((string) ($service['name'] ?? '')) . '" placeholder="مثلاً دسترسی به سامانه داخلی"></label>'
            . '<label>کد خدمت انگلیسی<input name="service_code" required value="' . e((string) ($service['code'] ?? '')) . '" placeholder="IT-ACCESS"></label>'
            . '<label>حوزه خدمت<select name="service_group"><option value="it" ' . ($group === 'it' ? 'selected' : '') . '>IT</option><option value="support" ' . ($group === 'support' ? 'selected' : '') . '>پشتیبانی</option></select></label>'
            . '<label>دسته‌بندی<select name="category_id">' . $categoryOptions((int) ($service['category_id'] ?? 0)) . '</select></label>'
            . '<label>واحد رسیدگی<select name="handling_unit_id">' . $handlingOptions((int) ($service['handling_unit_id'] ?? 0)) . '</select></label>'
            . '<label>معاونت مالک<select name="department_id">' . $departmentOptions((int) ($service['department_id'] ?? 0)) . '</select></label>'
            . '<label>نوع پیش‌فرض<select name="default_ticket_type">' . $ticketTypeOptions((string) ($service['default_ticket_type'] ?? 'incident')) . '</select></label>'
            . '<label>اولویت پیش‌فرض<select name="default_priority">' . $priorityOptions((string) ($service['default_priority'] ?? 'normal')) . '</select></label>'
            . '<label class="full">توضیح خدمت<textarea name="service_description" rows="3">' . e((string) ($service['description'] ?? '')) . '</textarea></label>'
            . '<p class="field-help full">برای همهٔ خدمات حوزهٔ IT، انتخاب سیستم از شناسنامهٔ فنی الزامی است؛ این شرط برای خدمات پشتیبانی اعمال نمی‌شود.</p>'
            . '<label class="check-label"><input type="checkbox" name="is_active" value="1" ' . ((int) ($service['is_active'] ?? 1) === 1 ? 'checked' : '') . '> فعال</label>'
            . '</div>';
    };
    render_header('کاتالوگ خدمات', $serviceUser);
    echo '<section class="page-heading"><div><span class="eyebrow">Service Catalog</span><h1>کاتالوگ خدمات سازمان</h1><p>هر خدمت فرم تکمیلی، اولویت پیش‌فرض و مسیر رسیدگی مستقل دارد. با سربرگ‌ها بین بخش‌ها جابه‌جا شوید.</p></div><a class="button" href="request-center.php?page=new-ticket">ثبت درخواست</a></section>';

    // --- سربرگ‌ها (تب‌ها) ---
    echo '<div class="settings-tabs service-tabs" data-service-tabs>';
    echo '<a href="#tab-create" data-service-tab="tab-create">تعریف خدمت</a>';
    echo '<a href="#tab-list" data-service-tab="tab-list">لیست خدمات</a>';
    if ($isServiceAdmin) {
        echo '<a href="#tab-categories" data-service-tab="tab-categories">دسته‌بندی‌ها</a>';
    }
    echo '</div>';

    // --- تب ۱: تعریف خدمت (فقط فرم ایجاد خدمت) ---
    echo '<div class="service-tab-panels">';
    echo '<section class="card form-card service-tab-panel" id="tab-create">';
    if ($isServiceAdmin) {
        echo '<h2>تعریف خدمت جدید</h2><p class="muted">حوزه، دسته‌بندی، واحد رسیدگی و الزام شناسنامه فنی را از همین‌جا تعیین کنید. فیلدهای تکمیلی هر خدمت را بعد از ساخت، از دکمهٔ «فیلدها» در فهرست خدمات مدیریت کنید.</p><form method="post"><input type="hidden" name="action" value="add_service">' . csrf_field() . $serviceFormFields([]) . '<div class="actions"><button class="button" type="submit">ایجاد خدمت</button></div></form>';
    } else {
        echo '<h2>تعریف خدمت</h2><p class="muted">فقط ادمین‌ها می‌توانند خدمت جدید تعریف کنند.</p>';
    }
    echo '</section>';

    // --- تب ۲: لیست خدمات + فیلتر ---
    echo '<section class="card form-card service-tab-panel" id="tab-list" hidden>';
    echo '<h2>لیست خدمات</h2><p class="muted">با انتخاب حوزه، فهرست سرویس‌ها فیلتر می‌شود.</p>';
     echo '<form method="get" class="service-filter-bar"><input type="hidden" name="page" value="services"><input type="hidden" name="tab" value="tab-list"><label>فیلتر حوزه خدمت<select name="group" data-service-filter><option value="" ' . ($serviceGroupFilter === '' ? 'selected' : '') . '>همه حوزه‌ها</option><option value="it" ' . ($serviceGroupFilter === 'it' ? 'selected' : '') . '>خدمات IT</option><option value="support" ' . ($serviceGroupFilter === 'support' ? 'selected' : '') . '>خدمات پشتیبانی</option></select></label><button class="button secondary service-filter-submit" type="submit">اعمال فیلتر</button></form>';
    echo '<div class="service-list"><div class="service-row service-row-head"><span>خدمت</span><span>حوزه</span><span>دسته</span><span>واحد رسیدگی</span><span>اولویت</span>' . ($isServiceAdmin ? '<span data-no-sort>عملیات</span>' : '') . '</div>';
    foreach ($services as $service) {
        $sid = (int) $service['id'];
        echo '<div class="service-row"><span class="service-name"><strong>' . e($service['name']) . '</strong><small>' . e($service['code']) . ' • ' . (int) $service['field_count'] . ' فیلد' . ((int) $service['requires_asset'] === 1 ? ' • نیازمند شناسنامه' : '') . '</small></span>';
        echo '<span>' . ($service['service_group'] === 'it' ? 'IT' : 'پشتیبانی') . '</span>';
        echo '<span>' . e($service['category_name'] ?: '—') . '</span>';
        echo '<span>' . e($service['handling_unit_name'] ?: 'تعیین‌نشده') . '</span>';
        echo '<span><i class="priority-dot ' . e($service['default_priority']) . '"></i>' . e(priority_label($service['default_priority'])) . '</span>';
        if ($isServiceAdmin) {
            echo '<span class="service-actions"><button type="button" class="mini-button" data-modal-open="service-fields-' . $sid . '">فیلدها</button>';
            echo '<button type="button" class="mini-button" data-modal-open="service-edit-' . $sid . '">ویرایش</button>';
            echo '<form method="post" class="service-delete" data-confirm="خدمت از فهرست فعال خارج شود؟"><input type="hidden" name="action" value="delete_service"><input type="hidden" name="service_id" value="' . $sid . '">' . csrf_field() . '<button class="mini-button danger" type="submit">حذف</button></form></span>';
        }
        echo '</div>';
    }
    if (!$services) {
        echo '<div class="service-row"><span class="muted">با این فیلتر خدمتی پیدا نشد.</span></div>';
    }
    echo '</div></section>';

    // --- تب ۳: دسته‌بندی‌ها ---
    if ($isServiceAdmin) {
        echo '<section class="card form-card service-tab-panel" id="tab-categories" hidden>';
        echo '<h2>دسته‌بندی خدمات</h2><p class="muted">دسته‌بندی لایهٔ میانی بین حوزه و خدمت است؛ هر خدمت به یک دسته وصل می‌شود.</p>';
        echo '<form method="post" class="inline-form"><input type="hidden" name="action" value="save_service_category">' . csrf_field() . '<label>نام دسته<input name="category_name" required placeholder="مثلاً شبکه و اینترنت"></label><label>کد<input name="category_code" placeholder="IT-CAT-NET"></label><label>حوزه<select name="category_group"><option value="it">IT</option><option value="support">پشتیبانی</option></select></label><button class="button" type="submit">افزودن دسته</button></form>';
        echo '<div class="category-list">';
        foreach ($categories as $category) {
            $cid = (int) $category['id'];
            echo '<div><span><strong>' . e($category['name']) . '</strong><small>' . e($category['code'] ?: 'بدون کد') . ' • ' . ($category['service_group'] === 'it' ? 'IT' : 'پشتیبانی') . ' • ' . ((int) $category['is_active'] === 1 ? 'فعال' : 'غیرفعال') . '</small></span><span class="service-actions"><button type="button" class="mini-button" data-modal-open="category-edit-' . $cid . '">ویرایش</button><form method="post" class="service-delete" data-confirm="دسته غیرفعال شود؟ خدمت‌های وابسته حذف نمی‌شوند."><input type="hidden" name="action" value="delete_service_category"><input type="hidden" name="category_id" value="' . $cid . '">' . csrf_field() . '<button class="mini-button danger" type="submit">حذف</button></form></span></div>';
        }
        echo '</div></section>';
    }
    echo '</div>';

    // --- پنجره‌های ویرایش/فیلد (rendered outside the tab panels, fixed overlay) ---
    $serviceModals = '';
    if ($isServiceAdmin) {
        foreach ($services as $service) {
            $sid = (int) $service['id'];
            $serviceFields = $serviceFieldsById[$sid] ?? [];
            $serviceModals .= '<div class="service-modal" role="dialog" aria-modal="true" aria-labelledby="service-edit-title-' . $sid . '" data-modal="service-edit-' . $sid . '" hidden><div class="service-modal-card"><div class="service-modal-head"><h3 id="service-edit-title-' . $sid . '">ویرایش خدمت</h3><p class="muted">' . e($service['name']) . ' • ' . e($service['code']) . '</p><button type="button" class="service-modal-close" aria-label="بستن" data-modal-close>&times;</button></div><div class="service-modal-body"><form method="post" id="service-edit-form-' . $sid . '" class="service-edit-form"><input type="hidden" name="action" value="edit_service"><input type="hidden" name="service_id" value="' . $sid . '">' . csrf_field() . $serviceFormFields($service) . '</form></div><div class="service-modal-foot"><button type="button" class="button secondary" data-modal-close>انصراف</button><button type="submit" class="button" form="service-edit-form-' . $sid . '">ذخیره</button></div></div></div>';
            $serviceModals .= '<div class="service-modal" role="dialog" aria-modal="true" aria-labelledby="service-fields-title-' . $sid . '" data-modal="service-fields-' . $sid . '" hidden><div class="service-modal-card"><div class="service-modal-head"><h3 id="service-fields-title-' . $sid . '">فیلدهای تکمیلی خدمت</h3><p class="muted">خدمت: ' . e($service['name']) . '</p><button type="button" class="service-modal-close" aria-label="بستن" data-modal-close>&times;</button></div><div class="service-modal-body"><p class="muted">فیلد پویا یعنی پرسش اضافی که هنگام ثبت درخواست برای همین خدمت از درخواست‌کننده پرسیده می‌شود؛ در فرم ثبت خدمت نمایش داده نمی‌شود.</p><div class="category-list service-field-list">';
            foreach ($serviceFields as $field) {
                $serviceModals .= '<div><span><strong>' . e($field['label']) . '</strong><small>' . e($field['field_key']) . ' • ' . e($field['field_type']) . '</small></span><span>' . ((int) $field['is_required'] === 1 ? 'اجباری' : 'اختیاری') . '</span></div>';
            }
            if (!$serviceFields) {
                $serviceModals .= '<div><span class="muted">هنوز فیلد تکمیلی برای این خدمت تعریف نشده است.</span></div>';
            }
            $serviceModals .= '</div><form method="post" id="service-fields-form-' . $sid . '" class="service-field-form">' . csrf_field() . '<input type="hidden" name="action" value="add_service_field"><input type="hidden" name="service_id" value="' . $sid . '"><h4>افزودن فیلد برای فرم ثبت درخواست</h4><div class="form-grid"><label>کلید انگلیسی<input name="field_key" pattern="[a-z][a-z0-9_]{1,79}" required placeholder="device_model"></label><label>عنوان نمایشی<input name="field_label" required placeholder="مثلاً مدل دستگاه"></label><label>نوع فیلد<select name="field_type"><option value="text">متن</option><option value="textarea">متن چندخطی</option><option value="number">عدد</option><option value="select">انتخابی</option><option value="date">تاریخ</option></select></label><label>ترتیب نمایش<input type="number" name="sort_order" value="0"></label><label class="full">گزینه‌های فیلد انتخابی<small>هر گزینه در یک خط یا با ویرگول جدا شود</small><textarea name="field_options" rows="3"></textarea></label><label class="check-label"><input type="checkbox" name="field_required" value="1"> اجباری</label></div></form></div><div class="service-modal-foot"><button type="button" class="button secondary" data-modal-close>بستن</button><button type="submit" class="button" form="service-fields-form-' . $sid . '">افزودن فیلد</button></div></div></div>';
        }
        foreach ($categories as $category) {
            $cid = (int) $category['id'];
            $serviceModals .= '<div class="service-modal" role="dialog" aria-modal="true" aria-labelledby="category-edit-title-' . $cid . '" data-modal="category-edit-' . $cid . '" hidden><div class="service-modal-card"><div class="service-modal-head"><h3 id="category-edit-title-' . $cid . '">ویرایش دسته‌بندی</h3><p class="muted">' . e($category['name']) . '</p><button type="button" class="service-modal-close" aria-label="بستن" data-modal-close>&times;</button></div><div class="service-modal-body"><form method="post" id="category-edit-form-' . $cid . '" class="service-edit-form"><input type="hidden" name="action" value="save_service_category"><input type="hidden" name="category_id" value="' . $cid . '">' . csrf_field() . '<div class="form-grid"><label>نام<input name="category_name" required value="' . e($category['name']) . '"></label><label>کد<input name="category_code" value="' . e($category['code'] ?? '') . '"></label><label>حوزه<select name="category_group"><option value="it" ' . ($category['service_group'] === 'it' ? 'selected' : '') . '>IT</option><option value="support" ' . ($category['service_group'] === 'support' ? 'selected' : '') . '>پشتیبانی</option></select></label></div><label class="check-label"><input type="checkbox" name="category_active" value="1" ' . ((int) $category['is_active'] === 1 ? 'checked' : '') . '> فعال</label></form></div><div class="service-modal-foot"><button type="button" class="button secondary" data-modal-close>انصراف</button><button type="submit" class="button" form="category-edit-form-' . $cid . '">ذخیره</button></div></div></div>';
        }
    }
    if ($serviceModals !== '') {
        echo '<div class="service-modal-layer" data-service-modal-layer hidden>' . $serviceModals . '</div>';
    }
    render_footer();
    exit;
}
