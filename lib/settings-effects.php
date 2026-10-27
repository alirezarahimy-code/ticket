<?php
declare(strict_types=1);

/**
 * lib/settings-effects.php — تنظیمات، افکت‌های نمایشی و نقش‌های کلیدی — جداشده از bootstrap.php در نسخهٔ ۱.۶۲.۲۰ (فاز ۲ بازسازی).
 * محتوا: setting و save_setting / تنظیمات افکت‌های triangle و particle و فعال‌سازی صفحه‌ای / key_role_* و user_is_key_role.
 * ترتیب تعریف توابع و کدهای سطح‌بالا عیناً مانند bootstrap قبلی حفظ شده است.
 */

if (!defined('APP_ROOT')) {
    exit;
}

function setting(string $key, ?string $default = null): ?string
{
    if (!isset($GLOBALS['__settings_cache'])) {
        // همهٔ تنظیمات را یک‌بار می‌خوانیم تا هر setting() یک کوئری جدا نزند
        // (قبلاً هر صفحه ۲۵+ کوئری تک‌کلیدی داشت).
        $GLOBALS['__settings_cache'] = [];
        try {
            foreach (db()->query('SELECT `key`, value FROM settings') as $row) {
                $GLOBALS['__settings_cache'][(string) $row['key']] = $row['value'] === null ? null : (string) $row['value'];
            }
        } catch (Throwable) {
            $GLOBALS['__settings_cache'] = [];
        }
    }
    $cache = &$GLOBALS['__settings_cache'];
    if (!array_key_exists($key, $cache)) {
        $query = db()->prepare('SELECT value FROM settings WHERE `key` = ? LIMIT 1');
        $query->execute([$key]);
        $value = $query->fetchColumn();
        $cache[$key] = $value === false ? null : (string) $value;
    }
    return $cache[$key] ?? $default;
}

/**
 * بعد از ذخیرهٔ تنظیمات، کش درون‌درخواستی را بی‌اعتبار می‌کند.
 */
function setting_forget_cache(): void
{
    $GLOBALS['__settings_cache'] = null;
}

function save_setting(string $key, string $value): void
{
    $query = db()->prepare(
        'INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)'
    );
    $query->execute([$key, $value]);
    if (isset($GLOBALS['__settings_cache']) && is_array($GLOBALS['__settings_cache'])) {
        $GLOBALS['__settings_cache'][$key] = (string) $value;
    }
}

/**
 * فهرست صفحه‌های قابل انتخاب برای افکت نمایشی.
 * کلیدهای صفحه باید با ?page= در index.php و نام فایل برای صفحات مستقل یکی باشند.
 * صفحات چاپ مستقل عمداً کنار گذاشته شده‌اند تا پس‌زمینه روی خروجی چاپ اثر نگذارد.
 */
function triangle_effect_page_catalog(): array
{
    return [
        'عمومی' => [
            'login' => 'ورود به سامانه',
            'dashboard' => 'داشبورد',
            'help' => 'راهنما',
            'notifications' => 'اعلان‌ها',
            'search' => 'جست‌وجو',
            'profile' => 'پروفایل کاربر',
            'change-password' => 'تغییر رمز عبور',
        ],
        'پشتیبانی و دارایی' => [
            'new-ticket' => 'ثبت درخواست جدید',
            'ticket' => 'جزئیات درخواست',
            'queue' => 'صف کاری',
            'services' => 'خدمات',
            'knowledge' => 'دانش‌نامه',
            'assets' => 'شناسنامه‌های فنی',
            'asset' => 'جزئیات شناسنامه فنی',
            'asset-history' => 'تاریخچه دارایی',
            'inventory' => 'موجودی (Inventory)',
            'inventory-diagnostics' => 'عیب‌یابی موجودی',
            'domain-scan' => 'اسکن دامنه',
            'supervisor' => 'پنل سوپروایزر',
            'governance' => 'تغییر و مشکل',
            'ola' => 'OLA',
            'holidays' => 'تعطیلات',
        ],
        'کنترل و غذا' => [
            'cd-dvd' => 'کنترل CD/DVD',
            'cd-dvd-history' => 'تاریخچه CD/DVD',
            'traffic-control' => 'کنترل تردد',
            'food-order' => 'سفارش غذا',
        ],
        'گزارش‌ها' => [
            'reports' => 'گزارش درخواست‌ها',
            'analytics' => 'چارت‌ها',
            'activity-log' => 'لاگ سامانه',
            'logs' => 'گزارش خطا',
            'audit' => 'ممیزی',
            'system-health' => 'سلامت سامانه',
        ],
        'مدیریت' => [
            'organization' => 'سازمان',
            'settings' => 'تنظیمات',
            'backup' => 'به‌روزرسانی و پشتیبان‌گیری',
            'chatbot-admin' => 'بات پاسخ‌گو',
            'local-accounts' => 'کاربران محلی',
        ],
    ];
}

function triangle_effect_valid_keys(): array
{
    $keys = [];
    foreach (triangle_effect_page_catalog() as $group) {
        foreach ($group as $key => $_label) {
            $keys[] = (string) $key;
        }
    }
    return $keys;
}

/** [حداقل، حداکثر، پیش‌فرض، تعداد رقم اعشار] */
function triangle_effect_config_spec(): array
{
    return [
        'cellSize' => [28, 70, 46, 0],
        'mouseRadiusMult' => [1, 12, 5.0, 1],
        'repulsionMult' => [0, 4, 1.35, 2],
        'springK' => [0.01, 0.3, 0.085, 3],
        'damping' => [0.5, 0.98, 0.80, 2],
        'waveSpeed' => [100, 1500, 620, 0],
        'waveDuration' => [0.3, 4, 1.6, 1],
        'waveWidth' => [20, 200, 70, 0],
        'waveAmpMult' => [0, 1.5, 0.32, 2],
        'idleAmp' => [0, 6, 1.6, 1],
        'opacity' => [0.1, 1, 1, 2],
    ];
}

function triangle_effect_sanitize_config(array $input): array
{
    $out = [];
    foreach (triangle_effect_config_spec() as $key => [$min, $max, $default, $decimals]) {
        $raw = $input[$key] ?? $default;
        $value = is_numeric($raw) ? (float) $raw : (float) $default;
        if (!is_finite($value)) {
            $value = (float) $default;
        }
        $value = max((float) $min, min((float) $max, $value));
        $out[$key] = $decimals > 0 ? round($value, $decimals) : (int) round($value);
    }
    foreach (['meshColor' => '#475569', 'dotColor' => '#334155'] as $key => $default) {
        $raw = $input[$key] ?? $default;
        $raw = is_string($raw) ? $raw : $default;
        $out[$key] = preg_match('/^#[0-9a-fA-F]{6}$/', $raw) === 1 ? strtolower($raw) : $default;
    }
    foreach (['showDots', 'maskVignette'] as $key) {
        $raw = $input[$key] ?? true;
        if (is_bool($raw)) {
            $out[$key] = $raw;
        } elseif (is_int($raw) || is_float($raw)) {
            $out[$key] = (float) $raw !== 0.0;
        } elseif (is_string($raw)) {
            $parsed = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $out[$key] = is_bool($parsed) ? $parsed : true;
        } else {
            $out[$key] = true;
        }
    }
    return $out;
}

function display_effect_type(): string
{
    $effect = setting('display_effect_type', 'triangle');
    return in_array($effect, ['triangle', 'particles'], true) ? $effect : 'triangle';
}

/** [حداقل، حداکثر، پیش‌فرض، تعداد رقم اعشار] */
function particle_effect_config_spec(): array
{
    return [
        'density' => [1800, 9000, 4500, 0],
        'maxParticles' => [40, 450, 320, 0],
        'speed' => [0, 1, 0.25, 2],
        'linkDistance' => [50, 240, 130, 0],
        'mouseRadius' => [60, 320, 180, 0],
        'mouseStrength' => [0, 2, 1, 1],
        'opacity' => [0.1, 1, 1, 2],
        'dotScale' => [0.5, 2.5, 1, 1],
    ];
}

function particle_effect_sanitize_config(array $input): array
{
    $out = [];
    foreach (particle_effect_config_spec() as $key => [$min, $max, $default, $decimals]) {
        $raw = $input[$key] ?? $default;
        $value = is_numeric($raw) ? (float) $raw : (float) $default;
        if (!is_finite($value)) {
            $value = (float) $default;
        }
        $value = max((float) $min, min((float) $max, $value));
        $out[$key] = $decimals > 0 ? round($value, $decimals) : (int) round($value);
    }
    $rawColor = $input['color'] ?? '#172b36';
    $rawColor = is_string($rawColor) ? $rawColor : '#172b36';
    $out['color'] = preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor) === 1 ? strtolower($rawColor) : '#172b36';
    foreach (['showDots', 'showLinks', 'connectCursor', 'autoMove'] as $key) {
        $raw = $input[$key] ?? true;
        if (is_bool($raw)) {
            $out[$key] = $raw;
        } elseif (is_int($raw) || is_float($raw)) {
            $out[$key] = (float) $raw !== 0.0;
        } elseif (is_string($raw)) {
            $parsed = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $out[$key] = is_bool($parsed) ? $parsed : true;
        } else {
            $out[$key] = true;
        }
    }
    return $out;
}

function particle_effect_config(): array
{
    $raw = (string) setting('particle_effect_config', '');
    $decoded = $raw !== '' ? json_decode($raw, true) : null;
    return particle_effect_sanitize_config(is_array($decoded) ? $decoded : []);
}

function particle_effect_enabled_for_current_page(): bool
{
    try {
        return display_effect_type() === 'particles'
            && in_array(triangle_effect_current_page_key(), triangle_effect_enabled_pages(), true);
    } catch (Throwable) {
        return false;
    }
}

function triangle_effect_config(): array
{
    $raw = (string) setting('triangle_effect_config', '');
    $decoded = $raw !== '' ? json_decode($raw, true) : null;
    return triangle_effect_sanitize_config(is_array($decoded) ? $decoded : []);
}

function triangle_effect_enabled_pages(): array
{
    $raw = (string) setting('triangle_effect_pages', '');
    $decoded = $raw !== '' ? json_decode($raw, true) : null;
    if (!is_array($decoded)) {
        return [];
    }
    $allowed = array_fill_keys(triangle_effect_valid_keys(), true);
    $selected = [];
    foreach ($decoded as $pageKey) {
        if (!is_string($pageKey)) {
            continue;
        }
        $pageKey = trim($pageKey);
        if ($pageKey !== '' && isset($allowed[$pageKey])) {
            $selected[$pageKey] = true;
        }
    }
    return array_values(array_filter(
        triangle_effect_valid_keys(),
        static fn(string $pageKey): bool => isset($selected[$pageKey])
    ));
}

function triangle_effect_current_page_key(): string
{
    $script = strtolower(pathinfo((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'), PATHINFO_FILENAME));
    if ($script !== 'index') {
        return $script;
    }
    if (!current_user()) {
        return 'login';
    }
    $page = $_GET['page'] ?? '';
    $page = is_string($page) || is_int($page) ? strtolower(trim((string) $page)) : '';
    return $page !== '' ? $page : 'dashboard';
}

/** اسکریپت موتور، و در صفحهٔ تنظیمات پیش‌نمایش زنده را فقط یک‌بار بارگذاری می‌کند. */
function triangle_effect_script_tag(): string
{
    try {
        $pageKey = triangle_effect_current_page_key();
        $enabled = display_effect_type() === 'triangle' && in_array($pageKey, triangle_effect_enabled_pages(), true);
        $user = current_user();
        $canPreview = $pageKey === 'settings' && $user !== null && user_can($user, 'settings.general');
        if (!$enabled && !$canPreview) {
            return '';
        }
        $tag = '<script src="assets/triangle-mesh.js?v=1.56" defer';
        if ($enabled) {
            $json = json_encode(
                triangle_effect_config(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            );
            if (!is_string($json)) {
                return '';
            }
            $tag .= ' data-config="' . htmlspecialchars($json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
        }
        $tag .= '></script>';
        if ($canPreview) {
            $tag .= '<script src="assets/triangle-settings.js?v=1.56.1" defer></script>';
        }
        return $tag;
    } catch (Throwable) {
        return '';
    }
}

/** موتور ذرات فقط برای نوع افکت انتخاب‌شده و صفحه‌های ذخیره‌شده بارگذاری می‌شود. */
function particle_effect_script_tag(): string
{
    try {
        $pageKey = triangle_effect_current_page_key();
        $enabled = particle_effect_enabled_for_current_page();
        $user = current_user();
        $canPreview = $pageKey === 'settings' && $user !== null && user_can($user, 'settings.general');
        if (!$enabled && !$canPreview) {
            return '';
        }
        $tag = '<script src="assets/particle-network.js?v=1.56.1" defer';
        if ($enabled) {
            $json = json_encode(
                particle_effect_config(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            );
            if (!is_string($json)) {
                return '';
            }
            $tag .= ' data-config="' . htmlspecialchars($json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
        }
        $tag .= '></script>';
        if ($canPreview) {
            $tag .= '<script src="assets/particle-settings.js?v=1.56.1" defer></script>';
        }
        return $tag;
    } catch (Throwable) {
        return '';
    }
}

function key_role_setting_map(): array
{
    return [
        'supervisor' => 'role_supervisor_ids',
        'inspection' => 'role_inspection_ids',
        'it_expert' => 'role_it_expert_ids',
    ];
}

function key_role_user_ids(string $role): array
{
    $map = key_role_setting_map();
    if (!isset($map[$role])) {
        return [];
    }
    $raw = (string) (setting($map[$role], '') ?? '');
    $ids = [];
    foreach (explode(',', $raw) as $part) {
        $id = (int) trim($part);
        if ($id > 0 && !in_array($id, $ids, true)) {
            $ids[] = $id;
        }
    }
    return array_slice($ids, 0, 2);
}

function user_is_key_role(array $user, string $role): bool
{
    return in_array((int) ($user['id'] ?? 0), key_role_user_ids($role), true);
}

