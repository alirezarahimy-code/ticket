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
    echo '<style id="rc-service-catalog-page">
.page-heading{align-items:center;margin-bottom:18px}.page-heading h1{font-size:24px;letter-spacing:-.3px}.page-heading p{font-size:13px;color:#687990}
.service-toolbar,.service-filters{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin:0 0 15px}
.service-toolbar>*{min-width:0}
.category-list{display:grid;gap:8px}
.category-list>div{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 13px;border:1px solid #e5ebf3;border-radius:10px;background:#fff}
.category-list>div>span:first-child{display:grid;gap:2px;min-width:0}
.category-list strong{font-size:12px;color:#294564;overflow-wrap:anywhere}
.category-list small,.muted{font-size:11px;color:#78879a}
.service-card,.service-item,.category-card{min-width:0;border:1px solid #e2e9f2;border-radius:13px;background:#fff;box-shadow:0 4px 15px rgba(23,43,77,.035)}
.service-card{padding:16px}
.service-card h2,.service-card h3{margin:0 0 8px;color:#263f5e;font-size:15px}
.service-modal-layer{position:fixed;inset:0;z-index:1000;display:grid;place-items:center;padding:20px;background:rgba(17,31,50,.48);backdrop-filter:blur(3px)}
.service-modal-layer[hidden],.service-modal[hidden]{display:none!important}
.service-modal{width:min(720px,100%);max-height:min(88vh,850px);overflow:auto}
.service-modal-card{overflow:hidden;border:1px solid #dfe7f1;border-radius:16px;background:#fff;box-shadow:0 20px 60px rgba(12,28,48,.22)}
.service-modal-head,.service-modal-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;background:#f8fafd;border-bottom:1px solid #e8edf4}
.service-modal-head h3{margin:0;font-size:15px;color:#213c5d}.service-modal-head p{margin:3px 0 0}
.service-modal-body{padding:18px}
.service-modal-foot{justify-content:flex-end;border-top:1px solid #e8edf4;border-bottom:0}
.service-modal-close{width:34px;height:34px;border:1px solid #e0e7f0;border-radius:9px;background:#fff;color:#526780;font-size:20px;cursor:pointer}
.service-modal .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.service-modal label{display:grid;gap:5px;min-width:0;font-size:11px;color:#536780}
.service-modal input:not([type=checkbox]),.service-modal select,.service-modal textarea{width:100%;min-height:38px;padding:8px 10px;border:1px solid #d9e2ee;border-radius:8px;background:#fff;color:#172b4d;font:inherit;font-size:12px}
.service-modal input:focus,.service-modal select:focus,.service-modal textarea:focus{outline:0;border-color:#709bc9;box-shadow:0 0 0 3px rgba(36,89,166,.11)}
.service-modal .full{grid-column:1/-1}
.service-modal .check-label{display:flex;align-items:center;gap:7px}
@media(max-width:700px){.page-heading{align-items:flex-start;flex-direction:column}.service-modal-layer{padding:10px}.service-modal-body{padding:13px}.service-modal .form-grid{grid-template-columns:1fr}.service-modal .full{grid-column:auto}}
</style>';
    echo '<section class="page-heading"><div><span class="eyebrow">Service Catalog</span><h1>کاتالوگ خدمات سازمان</h1><p>هر خدمت فرم تکمیلی، اولویت پیش‌فرض و مسیر رسیدگی مستقل دارد. با سربرگ‌ها بین بخش‌ها جابه‌جا شوید.</p></div><div class="actions"><a class="button secondary" href="request-center.php">خانه مرکز درخواست‌ها</a>' . (user_is_primary_admin($serviceUser) ? '<a class="button secondary" href="request-center.php?workspace=admin">مدیریت مرکز درخواست‌ها</a>' : '') . '<a class="button" href="request-center.php?page=new-ticket">ثبت درخواست</a></div></section>';

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
    echo '<script id="rc-service-catalog-interactions">
(function(){
 var tabs=document.querySelector("[data-service-tabs]"),panels=Array.prototype.slice.call(document.querySelectorAll(".service-tab-panel"));
 function activate(id,updateUrl){
  var panel=document.getElementById(id);if(!panel||!panel.classList.contains("service-tab-panel"))return;
  panels.forEach(function(item){item.hidden=item!==panel;});
  if(tabs)tabs.querySelectorAll("[data-service-tab]").forEach(function(link){var active=link.dataset.serviceTab===id;link.classList.toggle("active",active);link.setAttribute("aria-current",active?"page":"false");});
  if(updateUrl&&window.history&&window.history.replaceState){var url=new URL(window.location.href);url.searchParams.set("tab",id);window.history.replaceState({}, "",url.toString());}
 }
 if(tabs){
  tabs.querySelectorAll("[data-service-tab]").forEach(function(link){link.addEventListener("click",function(event){event.preventDefault();activate(link.dataset.serviceTab,true);});});
  var requested=new URLSearchParams(window.location.search).get("tab")||window.location.hash.slice(1)||"tab-create";
  activate(requested,false);
 }
 var layer=document.querySelector("[data-service-modal-layer]"),previousFocus=null;
 function closeModal(){
  if(!layer)return;layer.querySelectorAll(".service-modal").forEach(function(modal){modal.hidden=true;});
  layer.hidden=true;document.body.style.overflow="";if(previousFocus&&previousFocus.focus)previousFocus.focus();
 }
 function openModal(id,trigger){
  if(!layer)return;var modal=layer.querySelector("#"+CSS.escape(id));if(!modal)return;
  previousFocus=trigger||document.activeElement;layer.hidden=false;
  layer.querySelectorAll(".service-modal").forEach(function(item){item.hidden=item!==modal;});
  document.body.style.overflow="hidden";var target=modal.querySelector("input:not([type=hidden]):not([disabled]),select:not([disabled]),textarea:not([disabled]),button:not([disabled])");if(target)target.focus();
 }
 document.addEventListener("click",function(event){
  var opener=event.target.closest("[data-modal-open]");if(opener){event.preventDefault();openModal(opener.dataset.modalOpen,opener);return;}
  if(event.target.closest("[data-modal-close]")){event.preventDefault();closeModal();return;}
  if(layer&&!layer.hidden&&event.target===layer)closeModal();
 });
 document.addEventListener("keydown",function(event){
  if(!layer||layer.hidden)return;
  if(event.key==="Escape"){event.preventDefault();closeModal();return;}
  if(event.key!=="Tab")return;
  var modal=layer.querySelector(".service-modal:not([hidden])");if(!modal)return;
  var focusable=Array.prototype.slice.call(modal.querySelectorAll("a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex='-1'])")).filter(function(el){return el.offsetParent!==null;});
  if(!focusable.length){event.preventDefault();return;}
  var first=focusable[0],last=focusable[focusable.length-1];
  if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus();}
  else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus();}
 });
 document.querySelectorAll(".service-delete").forEach(function(form){
  form.addEventListener("submit",function(event){var button=form.querySelector("button[type=submit]");if(button&&button.dataset.confirm&&!window.confirm(button.dataset.confirm)){event.preventDefault();return;}if(button){button.disabled=true;button.textContent="در حال ثبت…";}});
 });
 document.querySelectorAll(".service-field-form").forEach(function(form){
  var type=form.querySelector("[name=field_type]"),options=form.querySelector("[name=field_options]");
  function sync(){if(!type||!options)return;var active=type.value==="select";options.disabled=!active;options.required=active;options.closest("label").style.opacity=active?"1":".55";}
  if(type){type.addEventListener("change",sync);sync();}
 });
})();
</script>'; 
    render_footer();
    exit;
}
