<?php

declare(strict_types=1);

/**
 * actions/service-actions.php — ۷ اکشن POST خدمات، دسته‌های خدمات و پایگاه دانش (فاز ۲ بازسازی، برش ۴ — نسخهٔ ۱.۶۲.۱۴).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   add_service              افزودن خدمت جدید به فهرست خدمات
 *   add_service_field        افزودن فیلد سفارشی به فرم یک خدمت
 *   edit_service             ویرایش مشخصات خدمت موجود
 *   delete_service           حذف خدمت (با کنترل وابستگی‌ها)
 *   save_service_category    ایجاد/ویرایش دستهٔ خدمات
 *   delete_service_category  حذف دستهٔ خدمات
 *   add_knowledge            ثبت مقالهٔ جدید در پایگاه دانش
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده و هیچ تغییر
 * رفتاری ندارد (بلوک اسکن شد: بدون function/continue/کلوژر).
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: خدمات، دسته‌ها و پایگاه دانش ─────
        if ($action === 'add_service') {
            $serviceAdmin = require_permission('services.manage');
            $name = post_value('service_name');
            $code = strtoupper((string) preg_replace('/[^A-Za-z0-9_-]/', '', post_value('service_code')));
            $serviceGroup = valid_choice(post_value('service_group', 'it'), ['it', 'support'], 'it');
            $priority = valid_choice(post_value('default_priority', 'normal'), ['normal', 'urgent', 'critical'], 'normal');
            $defaultType = valid_choice(post_value('default_ticket_type', 'incident'), ['incident', 'request', 'problem', 'change'], 'incident');
            $handlingUnitId = (int) ($_POST['handling_unit_id'] ?? 0) ?: null;
            if ($handlingUnitId === null) {
                $unitQuery = db()->prepare('SELECT id FROM handling_units WHERE code = ? LIMIT 1');
                $unitQuery->execute([$serviceGroup]);
                $handlingUnitId = (int) ($unitQuery->fetchColumn() ?: 0) ?: null;
            } else {
                $unitCheck = db()->prepare('SELECT code FROM handling_units WHERE id = ? AND is_active = 1 LIMIT 1');
                $unitCheck->execute([$handlingUnitId]);
                if ((string) $unitCheck->fetchColumn() !== $serviceGroup) {
                    throw new RuntimeException('واحد رسیدگی باید با حوزه خدمت یکسان باشد.');
                }
            }
            if ($name === '' || $code === '') {
                throw new RuntimeException('نام و کد خدمت الزامی است.');
            }
            if ($serviceGroup === 'support') {
                $defaultType = in_array($defaultType, ['incident', 'request'], true) ? $defaultType : 'request';
            }
            db()->prepare('INSERT INTO service_catalog (name, code, description, service_group, default_priority, department_id, category_id, handling_unit_id, requires_asset, default_ticket_type, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([$name, $code, post_value('service_description'), $serviceGroup, $priority, (int) ($_POST['department_id'] ?? 0) ?: null, (int) ($_POST['category_id'] ?? 0) ?: null, $handlingUnitId, $serviceGroup === 'it' ? 1 : 0, $defaultType, $serviceAdmin['id']]);
            flash('success', 'خدمت جدید به کاتالوگ اضافه شد.');
            redirect('index.php?page=services#tab-list');
        }

        if ($action === 'add_service_field') {
            require_permission('service_field.manage');
            $serviceId = (int) ($_POST['service_id'] ?? 0);
            $fieldKey = strtolower((string) preg_replace('/[^a-zA-Z0-9_]/', '_', post_value('field_key')));
            $label = post_value('field_label');
            $fieldType = valid_choice(post_value('field_type', 'text'), ['text', 'textarea', 'number', 'select', 'date'], 'text');
            $options = array_values(array_filter(array_map('trim', preg_split('/\r?\n|,/', post_value('field_options')) ?: [])));
            if ($serviceId <= 0 || !preg_match('/^[a-z][a-z0-9_]{1,79}$/', $fieldKey) || $label === '') {
                throw new RuntimeException('خدمت، کلید انگلیسی و عنوان فیلد را کامل کنید.');
            }
            db()->prepare('INSERT INTO service_catalog_fields (service_id, field_key, label, field_type, options_json, is_required, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([$serviceId, $fieldKey, $label, $fieldType, json_encode($options, JSON_UNESCAPED_UNICODE), !empty($_POST['field_required']) ? 1 : 0, max(0, (int) ($_POST['sort_order'] ?? 0))]);
            flash('success', 'فیلد خدمت اضافه شد.');
            redirect('index.php?page=services&tab=tab-list#tab-list');
        }

        if ($action === 'edit_service') {
            $serviceAdmin = require_permission('services.manage');
            $serviceId = (int) post_value('service_id', '0');
            if ($serviceId <= 0) {
                throw new RuntimeException('خدمت انتخابی معتبر نیست.');
            }
            $name = post_value('service_name');
            $code = strtoupper((string) preg_replace('/[^A-Za-z0-9_-]/', '', post_value('service_code')));
            $serviceGroup = valid_choice(post_value('service_group', 'it'), ['it', 'support'], 'it');
            $priority = valid_choice(post_value('default_priority', 'normal'), ['normal', 'urgent', 'critical'], 'normal');
            $defaultType = valid_choice(post_value('default_ticket_type', 'incident'), ['incident', 'request', 'problem', 'change'], 'incident');
            if ($name === '' || $code === '') {
                throw new RuntimeException('نام و کد خدمت الزامی است.');
            }
            if ($serviceGroup === 'support') {
                $defaultType = in_array($defaultType, ['incident', 'request'], true) ? $defaultType : 'request';
            }
            $categoryId = (int) post_value('category_id', '0') ?: null;
            $handlingUnitId = (int) post_value('handling_unit_id', '0') ?: null;
            if ($handlingUnitId === null) {
                $unitQuery = db()->prepare('SELECT id FROM handling_units WHERE code = ? LIMIT 1');
                $unitQuery->execute([$serviceGroup]);
                $handlingUnitId = (int) ($unitQuery->fetchColumn() ?: 0) ?: null;
            }
            db()->prepare('UPDATE service_catalog SET name = ?, code = ?, description = ?, service_group = ?, default_priority = ?, department_id = ?, category_id = ?, handling_unit_id = ?, default_ticket_type = ?, requires_asset = ?, is_active = ? WHERE id = ?')
                ->execute([$name, $code, post_value('service_description'), $serviceGroup, $priority, (int) post_value('department_id', '0') ?: null, $categoryId, $handlingUnitId, $defaultType, $serviceGroup === 'it' ? 1 : 0, post_value('is_active') === '1' ? 1 : 0, $serviceId]);
            save_audit((int) $serviceAdmin['id'], 'service_updated', null, ['service_id' => $serviceId, 'name' => $name]);
            flash('success', 'خدمت ویرایش شد.');
            redirect('index.php?page=services#tab-list');
        }

        if ($action === 'delete_service') {
            $serviceAdmin = require_permission('services.manage');
            $serviceId = (int) post_value('service_id', '0');
            $existsQuery = db()->prepare('SELECT id FROM service_catalog WHERE id = ? LIMIT 1');
            $existsQuery->execute([$serviceId]);
            if ($serviceId <= 0 || !$existsQuery->fetchColumn()) {
                throw new RuntimeException('خدمت انتخابی معتبر نیست.');
            }
            db()->prepare('UPDATE service_catalog SET is_active = 0 WHERE id = ?')->execute([$serviceId]);
            save_audit((int) $serviceAdmin['id'], 'service_deleted', null, ['service_id' => $serviceId]);
            flash('success', 'خدمت از کاتالوگ حذف شد.');
            redirect('index.php?page=services#tab-list');
        }

        if ($action === 'save_service_category') {
            $serviceAdmin = require_permission('services.manage');
            $categoryId = (int) post_value('category_id', '0');
            $name = post_value('category_name');
            $code = strtoupper((string) preg_replace('/[^A-Za-z0-9_-]/', '', post_value('category_code')));
            $serviceGroup = valid_choice(post_value('category_group', 'it'), ['it', 'support'], 'it');
            if ($name === '') {
                throw new RuntimeException('نام دسته‌بندی الزامی است.');
            }
            $isActive = isset($_POST['category_active']) ? 1 : 0;
            if ($categoryId > 0) {
                db()->prepare('UPDATE categories SET name = ?, code = ?, service_group = ?, is_active = ? WHERE id = ?')
                    ->execute([$name, $code !== '' ? $code : null, $serviceGroup, $isActive, $categoryId]);
            } else {
                db()->prepare('INSERT INTO categories (name, code, service_group, is_active) VALUES (?, ?, ?, ?)')
                    ->execute([$name, $code !== '' ? $code : null, $serviceGroup, $isActive]);
            }
            save_audit((int) $serviceAdmin['id'], 'service_category_saved', null, ['category_id' => $categoryId, 'name' => $name]);
            flash('success', 'دسته‌بندی ذخیره شد.');
            redirect('index.php?page=services#tab-categories');
        }

        if ($action === 'delete_service_category') {
            $serviceAdmin = require_permission('services.manage');
            $categoryId = (int) post_value('category_id', '0');
            if ($categoryId <= 0) {
                throw new RuntimeException('دسته‌بندی انتخابی معتبر نیست.');
            }
            db()->prepare('UPDATE categories SET is_active = 0 WHERE id = ?')->execute([$categoryId]);
            save_audit((int) $serviceAdmin['id'], 'service_category_deleted', null, ['category_id' => $categoryId]);
            flash('success', 'دسته‌بندی غیرفعال شد.');
            redirect('index.php?page=services#tab-categories');
        }

        if ($action === 'add_knowledge') {
            $knowledgeUser = require_permission('knowledge.manage');
            $title = post_value('article_title');
            $body = post_value('article_body');
            if ($title === '' || $body === '') {
                throw new RuntimeException('عنوان و متن مقاله دانش‌نامه الزامی است.');
            }
            $slug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $title));
            $slug = trim($slug, '-') . '-' . bin2hex(random_bytes(3));
            db()->prepare('INSERT INTO knowledge_articles (title, slug, body, category, is_published, created_by) VALUES (?, ?, ?, ?, ?, ?)')->execute([$title, $slug, $body, post_value('article_category'), !empty($_POST['is_published']) ? 1 : 0, $knowledgeUser['id']]);
            flash('success', 'مقاله دانش‌نامه ذخیره شد.');
            redirect('index.php?page=knowledge');
        }
