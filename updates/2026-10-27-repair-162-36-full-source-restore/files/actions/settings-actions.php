<?php

declare(strict_types=1);

/**
 * actions/settings-actions.php — ۵ اکشن POST نگهداری لاگ‌ها/LDAP و تنظیمات عمومی (فاز ۲ بازسازی، برش ۵ — نسخهٔ ۱.۶۲.۱۶).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   prune_activity_logs    پاک‌سازی لاگ‌های قدیمی‌تر از بازهٔ نگهداری (فقط مدیر اصلی)
 *   sync_ldap_users        همگام‌سازی کاربران از Active Directory
 *   diagnose_ldap          عیب‌یابی اتصال دامنه
 *   save_triangle_effect   ذخیرهٔ تنظیمات افکت نمایشی مثلثی صفحات
 *   save_settings          ذخیرهٔ تنظیمات عمومی سامانه (بخش‌به‌بخش با کنترل مجوز)
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده؛ تنها موارد
 * function/continue داخل بلوک، کلوژر static و continue داخل حلقه‌اند که برای include
 * کاملاً بی‌خطرند (اسکن و بازبینی شد).
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: نگهداری لاگ‌ها و LDAP ─────
        if ($action === 'prune_activity_logs') {
            $pruneAdmin = require_primary_admin();
            $days = (int) post_value('retention_days', (string) activity_log_retention_days());
            if ($days > 0) {
                save_setting('activity_log_retention_days', (string) $days);
            } else {
                $days = activity_log_retention_days();
            }
            $deleted = activity_logs_prune($days);
            save_audit((int) $pruneAdmin['id'], 'activity_logs_pruned', null, ['days' => $days, 'deleted' => $deleted]);
            flash('success', 'لاگ‌های قدیمی‌تر از ' . $days . ' روز پاک شد. تعداد حذف‌شده: ' . $deleted . '.');
            redirect('index.php?page=settings#general');
        }

        if ($action === 'sync_ldap_users') {
            $settingsAdmin = require_permission('settings.users_edit');
            $syncResult = ldap_sync_all_users();
            save_audit((int) $settingsAdmin['id'], 'ldap_bulk_sync', null, $syncResult);
            flash('success', 'همگام‌سازی کاربران دامین انجام شد. جدید: ' . $syncResult['created'] . '، به‌روزشده: ' . $syncResult['updated'] . '، غیرفعال: ' . $syncResult['disabled'] . '.');
            redirect('index.php?page=settings#users');
        }

        if ($action === 'diagnose_ldap') {
            $diagnoseAdmin = require_permission('settings.domain_edit');
            $diagnoseResult = ldap_diagnose();
            save_audit((int) $diagnoseAdmin['id'], 'ldap_diagnosed', null, ['ok' => $diagnoseResult['ok']]);
            if ($diagnoseResult['ok']) {
                flash('success', 'اتصال دامنه سالم است. ' . $diagnoseResult['summary']);
            } else {
                flash('danger', 'اشکال در اتصال دامنه: ' . $diagnoseResult['summary']);
            }
            redirect('index.php?page=settings#ldap-sync');
        }


        // ───── گروه اکشن: تنظیمات عمومی و افکت نمایشی ─────
        if ($action === 'save_triangle_effect') {
            $effectUser = require_permission('settings.general_edit');
            $postedPages = $_POST['triangle_pages'] ?? [];
            if (!is_array($postedPages)) {
                $postedPages = [];
            }
            $selectedPageKeys = [];
            foreach ($postedPages as $pageKey) {
                if (!is_string($pageKey) || strlen($pageKey) > 80) {
                    continue;
                }
                $pageKey = trim($pageKey);
                if ($pageKey !== '') {
                    $selectedPageKeys[$pageKey] = true;
                }
            }
            $enabledPages = array_values(array_filter(
                triangle_effect_valid_keys(),
                static fn(string $pageKey): bool => isset($selectedPageKeys[$pageKey])
            ));

            $postedTriangleConfig = $_POST['tm'] ?? null;
            $postedTriangleConfig = is_array($postedTriangleConfig) ? $postedTriangleConfig : triangle_effect_config();
            foreach (['showDots', 'maskVignette'] as $booleanKey) {
                $value = $postedTriangleConfig[$booleanKey] ?? null;
                $postedTriangleConfig[$booleanKey] = is_scalar($value) && (string) $value === '1';
            }
            $newTriangleConfig = triangle_effect_sanitize_config($postedTriangleConfig);

            $postedParticleConfig = $_POST['particle'] ?? null;
            if (is_array($postedParticleConfig)) {
                foreach (['showDots', 'showLinks', 'connectCursor', 'autoMove'] as $booleanKey) {
                    $value = $postedParticleConfig[$booleanKey] ?? null;
                    $postedParticleConfig[$booleanKey] = is_scalar($value) && (string) $value === '1';
                }
                $newParticleConfig = particle_effect_sanitize_config($postedParticleConfig);
            } else {
                $newParticleConfig = particle_effect_config();
            }

            $postedEffect = $_POST['effect_type'] ?? display_effect_type();
            $activeEffect = is_string($postedEffect) && in_array(trim($postedEffect), ['triangle', 'particles'], true)
                ? trim($postedEffect)
                : 'triangle';

            $settingsPdo = db();
            $settingsPdo->beginTransaction();
            try {
                save_setting('triangle_effect_pages', (string) json_encode($enabledPages, JSON_UNESCAPED_UNICODE));
                save_setting('triangle_effect_config', (string) json_encode(
                    $newTriangleConfig,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
                ));
                save_setting('particle_effect_config', (string) json_encode(
                    $newParticleConfig,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
                ));
                save_setting('display_effect_type', $activeEffect);
                $settingsPdo->commit();
            } catch (Throwable $settingsError) {
                if ($settingsPdo->inTransaction()) {
                    $settingsPdo->rollBack();
                }
                setting_forget_cache();
                throw $settingsError;
            }
            save_audit((int) $effectUser['id'], 'triangle_effect_saved', null, [
                'pages' => count($enabledPages),
                'effect' => $activeEffect,
            ]);
            flash('success', 'تنظیمات افکت نمایشی ذخیره شد.');
            redirect('index.php?page=settings#triangle');
        }

        if ($action === 'save_settings') {
            $settingsUser = require_login();
            if (post_value('settings_section') === 'inspection_alarm') {
                $settingsAdmin = require_primary_admin();
                $enabled = post_value('inspection_food_absence_alarm_enabled', '0') === '1' ? '1' : '0';
                save_setting('inspection_food_absence_alarm_enabled', $enabled);
                save_audit((int) $settingsAdmin['id'], 'inspection_food_absence_alarm_updated', null, ['enabled' => $enabled === '1']);
                flash('success', $enabled === '1' ? 'یادآور روزانهٔ بازرسی فعال شد.' : 'یادآور روزانهٔ بازرسی غیرفعال شد.');
                redirect('index.php?page=settings#general');
            }
            $sectionPermission = [
                'general' => 'settings.general_edit',
                'ticket' => 'settings.general_edit',
                'ldap' => 'settings.domain_edit',
                'domain_scan' => 'settings.domain_edit',
                'login_branding' => 'settings.general_edit',
            ][post_value('settings_section')] ?? 'settings.general_edit';
            if (!user_can($settingsUser, $sectionPermission)) {
                http_response_code(403);
                exit('دسترسی به این بخش مجاز نیست.');
            }
            $appName = post_value('app_name');
            if ($appName === '') {
                throw new RuntimeException('نام سامانه نمی‌تواند خالی باشد.');
            }
            save_setting('app_name', $appName);
            if (post_value('settings_section') === 'general' && array_key_exists('asset_hostname_style', $_POST)) {
                // ۱.۳۷.۵ — حالت نمایش نام کامپیوترها: adminit2 (پیش‌فرض) | ADMINIT2 | ADMINIT2.DOMAIN.LOCAL
                save_setting('asset_hostname_style', valid_choice(post_value('asset_hostname_style'), ['short_lower', 'short', 'full'], 'short_lower'));
            }
            if (post_value('settings_section') === 'ticket') {
                $autoCloseDays = array_key_exists('ticket_auto_close_days', $_POST) ? max(0, min(30, (int) post_value('ticket_auto_close_days'))) : 3;
                save_setting('ticket_auto_close_days', (string) $autoCloseDays);
            }
            if (post_value('settings_section') === 'ldap') {
                $groupMap = post_value('ldap_group_map');
                if (array_key_exists('ldap_group_map', $_POST) && $groupMap !== '' && !is_array(json_decode($groupMap, true))) {
                    throw new RuntimeException('نگاشت گروه‌های AD باید JSON معتبر باشد.');
                }
                $ldapKeys = ['ldap_enabled','ldap_host','ldap_port','ldap_ssl','ldap_base_dn','ldap_bind_dn','ldap_domain_suffix'];
                if (array_key_exists('ldap_group_map', $_POST)) {
                    $ldapKeys[] = 'ldap_group_map';
                }
                foreach ($ldapKeys as $key) {
                    save_setting($key, post_value($key));
                }
                if (post_value('ldap_bind_password') !== '') {
                    save_setting('ldap_bind_password', post_value('ldap_bind_password'));
                }
            }
            if (post_value('settings_section') === 'domain_scan') {
                foreach (['domain_scan_domain', 'domain_scan_timeout'] as $key) {
                    save_setting($key, post_value($key));
                }
                // اعتبارنامهٔ قبلیِ ذخیره‌شده در نسخه‌های قدیمی را پاک می‌کنیم؛ هر اسکن رمز را جداگانه می‌گیرد.
                save_setting('domain_scan_username', '');
                save_setting('domain_scan_password', '');
                // ترتیب پروتکل اتصال برای استخراج از راه دور (۱.۳۷): برای شبکه‌هایی که DCOM بسته است.
                save_setting('domain_scan_protocols', valid_choice(post_value('domain_scan_protocols'), ['', 'Dcom,Wsman', 'Wsman,Dcom', 'Dcom', 'Wsman'], ''));
            }
            if (post_value('settings_section') === 'login_branding') {
                foreach (['login_title' => 120, 'login_intro' => 500, 'login_art_title' => 100, 'login_art_text' => 300, 'login_note_title' => 100, 'login_note_text' => 300] as $loginKey => $maxLength) {
                    $value = post_value($loginKey);
                    if ((function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value)) > $maxLength) {
                        throw new RuntimeException('طول یکی از متن‌های صفحه ورود بیش از حد مجاز است.');
                    }
                    save_setting($loginKey, $value);
                }
                // پیام‌های متحرک صفحه ورود (۱.۵۷): هر پیام در یک خط، حداکثر ۵ پیام ۲۴۰ نویسه‌ای.
                $announcementLines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) ($_POST['login_announcements'] ?? '')) ?: [])));
                $announcementLines = array_slice($announcementLines, 0, 5);
                foreach ($announcementLines as $announcementLine) {
                    if ((function_exists('mb_strlen') ? mb_strlen($announcementLine, 'UTF-8') : strlen($announcementLine)) > 240) {
                        throw new RuntimeException('هر پیام متحرک صفحه ورود حداکثر ۲۴۰ نویسه می‌تواند باشد.');
                    }
                }
                save_setting('login_announcements', implode("\n", $announcementLines));
                // سازگاری با نسخه‌های قبل: کلید تکی، همیشه همان پیام اول است.
                save_setting('login_announcement', $announcementLines[0] ?? '');
                save_setting('login_announcement_speed', (string) max(5, min(60, (int) post_value('login_announcement_speed', '18'))));
                save_setting('login_announcement_font_size', (string) max(11, min(22, (int) post_value('login_announcement_font_size', '12'))));
                if (!empty($_FILES['login_logo']['tmp_name']) && is_uploaded_file($_FILES['login_logo']['tmp_name'])) {
                    $mime = mime_content_type($_FILES['login_logo']['tmp_name']) ?: '';
                    $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
                    if (!isset($allowed[$mime]) || (int) $_FILES['login_logo']['size'] > 2 * 1024 * 1024) {
                        throw new RuntimeException('لوگوی صفحه ورود باید PNG یا JPG و حداکثر ۲ مگابایت باشد.');
                    }
                    @mkdir(APP_ROOT . '/assets/uploads', 0755, true);
                    $filename = 'login-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                    if (!move_uploaded_file($_FILES['login_logo']['tmp_name'], APP_ROOT . '/assets/uploads/' . $filename)) {
                        throw new RuntimeException('ذخیره لوگوی صفحه ورود انجام نشد.');
                    }
                    save_setting('app_login_logo', 'assets/uploads/' . $filename);
                }
            }
            if (!empty($_FILES['logo']['tmp_name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
                $mime = mime_content_type($_FILES['logo']['tmp_name']) ?: '';
                $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
                if (isset($allowed[$mime]) && (int) $_FILES['logo']['size'] <= 2 * 1024 * 1024) {
                    @mkdir(APP_ROOT . '/assets/uploads', 0755, true);
                    $filename = 'logo-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                    move_uploaded_file($_FILES['logo']['tmp_name'], APP_ROOT . '/assets/uploads/' . $filename);
                    save_setting('app_logo', 'assets/uploads/' . $filename);
                }
            }
            flash('success', 'تنظیمات سامانه ذخیره شد.');
            redirect('index.php?page=settings');
        }
