<?php
declare(strict_types=1);

/**
 * نمای طراحی امن برای اصلاح چینش صفحه‌های موجود؛ فقط مقدارهای نمایشی محدود ذخیره می‌شوند.
 * هیچ HTML، JavaScript یا CSS خامی از مرورگر پذیرفته نمی‌شود.
 */
const ADMIN_LAYOUT_DESIGN_SETTING = 'admin_page_layouts_v1';

function admin_layout_design_is_admin(array $user): bool
{
    return function_exists('user_is_primary_admin')
        ? user_is_primary_admin($user)
        : ((string) ($user['role'] ?? '') === 'primary_admin'
            || ((string) ($user['role'] ?? '') === 'admin' && (int) ($user['is_primary_admin'] ?? 0) === 1));
}

function admin_layout_design_page_key(mixed $value): string
{
    $page = strtolower(trim((string) $value));
    if ($page === '') {
        return 'dashboard';
    }
    if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,59}$/', $page)) {
        throw new InvalidArgumentException('شناسهٔ صفحه برای طراحی معتبر نیست.');
    }
    return $page;
}

function admin_layout_design_config(): array
{
    $raw = function_exists('setting') ? (string) setting(ADMIN_LAYOUT_DESIGN_SETTING, '{}') : '{}';
    $decoded = json_decode($raw, true);
    return is_array($decoded) && is_array($decoded['pages'] ?? null) ? $decoded : ['pages' => []];
}

function admin_layout_design_validate_path(mixed $value): array
{
    if (!is_array($value) || count($value) < 1 || count($value) > 18) {
        throw new InvalidArgumentException('مسیر جزء انتخاب‌شده معتبر نیست.');
    }
    $path = [];
    foreach ($value as $segment) {
        if (!is_array($segment)) {
            throw new InvalidArgumentException('مسیر جزء انتخاب‌شده معتبر نیست.');
        }
        $tag = strtolower((string) ($segment['tag'] ?? ''));
        $id = (string) ($segment['id'] ?? '');
        $index = filter_var($segment['index'] ?? null, FILTER_VALIDATE_INT);
        if (!preg_match('/^(?:main|section|article|div|form|fieldset|label|input|select|textarea|button|a|span|p|h[1-6]|table|thead|tbody|tr|th|td|ul|ol|li|header|footer|aside|nav)$/', $tag)
            || $index === false || $index < 1 || $index > 500) {
            throw new InvalidArgumentException('مسیر جزء انتخاب‌شده معتبر نیست.');
        }
        if ($id !== '' && !preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,79}$/', $id)) {
            $id = '';
        }
        $classes = [];
        $transientClasses = ['ald-hover', 'ald-selected', 'ald-drag-source', 'ald-drop-target', 'ald-dragging'];
        foreach ((array) ($segment['classes'] ?? []) as $class) {
            if (count($classes) >= 4) {
                break;
            }
            $class = (string) $class;
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_-]{0,59}$/', $class) && !in_array($class, $transientClasses, true)) {
                $classes[] = $class;
            }
        }
        $path[] = ['tag' => $tag, 'id' => $id, 'classes' => array_values(array_unique($classes)), 'index' => (int) $index];
    }
    // A unique id is a more durable anchor than the full wrapper chain. Keep
    // only the deepest id segment and its descendants so dynamic page wrappers
    // do not invalidate saved layout rules after a reload.
    for ($anchor = count($path) - 1; $anchor >= 0; $anchor--) {
        if (($path[$anchor]['id'] ?? '') !== '') {
            $path = array_slice($path, $anchor);
            break;
        }
    }
    return array_values($path);
}

function admin_layout_design_css_length(mixed $value, string $fallback = ''): string
{
    $value = strtolower(trim((string) $value));
    if ($value === '') {
        return $fallback;
    }
    if (in_array($value, ['auto', 'fit-content', 'max-content', 'min-content'], true)) {
        return $value;
    }
    if (preg_match('/^-?(?:0|[0-9]{1,4}(?:\.[0-9]{1,2})?)(?:px|%|rem|em|vw|vh)$/', $value)) {
        return $value;
    }
    return $fallback;
}

/** فقط declarationهای محدودشدهٔ زیر قابل ذخیره هستند. */
function admin_layout_design_validate_styles(mixed $value): array
{
    if (!is_array($value)) {
        throw new InvalidArgumentException('تنظیم‌های ظاهری معتبر نیستند.');
    }
    $styles = [];
    $lengthProperties = ['width', 'min-width', 'max-width', 'height', 'min-height', 'max-height', 'margin-top', 'margin-bottom', 'padding', 'gap', 'row-gap', 'column-gap'];
    foreach ($lengthProperties as $property) {
        if (array_key_exists($property, $value)) {
            $rawLength = strtolower(trim((string) $value[$property]));
            $safe = in_array($property, ['max-width', 'max-height'], true) && $rawLength === 'none'
                ? 'none'
                : admin_layout_design_css_length($rawLength);
            if ($safe !== '') {
                $styles[$property] = $safe;
            }
        }
    }
    if (array_key_exists('order', $value)) {
        $order = filter_var($value['order'], FILTER_VALIDATE_INT);
        if ($order !== false && $order >= 0 && $order <= 500) {
            $styles['order'] = (string) $order;
        }
    }
    foreach (['offset-x', 'offset-y'] as $offsetProperty) {
        if (array_key_exists($offsetProperty, $value)) {
            $offset = filter_var($value[$offsetProperty], FILTER_VALIDATE_INT);
            if ($offset !== false && $offset >= -500 && $offset <= 500) {
                $styles[$offsetProperty] = (string) $offset;
            }
        }
    }
    if (array_key_exists('font-size', $value)) {
        $font = (int) $value['font-size'];
        if ($font >= 8 && $font <= 48) {
            $styles['font-size'] = $font . 'px';
        }
    }
    if (array_key_exists('line-height', $value)) {
        $lineHeight = trim((string) $value['line-height']);
        if (preg_match('/^(?:0\.[8-9]|[1-3](?:\.[0-9])?|4(?:\.0)?)$/', $lineHeight)) {
            $styles['line-height'] = $lineHeight;
        }
    }
    $choices = [
        'text-align' => ['right', 'left', 'center', 'start', 'end'],
        'white-space' => ['normal', 'nowrap', 'pre-wrap'],
        'display' => ['block', 'inline-block', 'inline-flex', 'flex', 'grid'],
        'flex-wrap' => ['nowrap', 'wrap', 'wrap-reverse'],
        'justify-content' => ['start', 'end', 'center', 'space-between', 'space-around', 'space-evenly'],
        'align-items' => ['start', 'end', 'center', 'stretch', 'baseline'],
    ];
    foreach ($choices as $property => $allowed) {
        if (isset($value[$property]) && in_array((string) $value[$property], $allowed, true)) {
            $styles[$property] = (string) $value[$property];
        }
    }
    if (isset($value['grid-template-columns'])) {
        $grid = trim((string) $value['grid-template-columns']);
        if ($grid !== '' && strlen($grid) <= 240
            && preg_match('/^(?:(?:[0-9]{1,4}(?:\.[0-9]{1,2})?(?:px|fr|%|rem|em))|auto|min-content|max-content)(?:\s+(?:(?:[0-9]{1,4}(?:\.[0-9]{1,2})?(?:px|fr|%|rem|em))|auto|min-content|max-content)){0,11}$/', $grid)) {
            $styles['grid-template-columns'] = $grid;
        }
    }
    return $styles;
}

function admin_layout_design_validate_rules(mixed $value): array
{
    if (!is_array($value) || count($value) > 120) {
        throw new InvalidArgumentException('تعداد اجزای قابل طراحی از حد مجاز بیشتر است.');
    }
    $rules = [];
    $seen = [];
    foreach ($value as $rule) {
        if (!is_array($rule)) {
            continue;
        }
        $path = admin_layout_design_validate_path($rule['path'] ?? null);
        $styles = admin_layout_design_validate_styles($rule['styles'] ?? []);
        if ($styles === []) {
            continue;
        }
        $key = json_encode($path, JSON_UNESCAPED_SLASHES);
        if (!is_string($key) || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $rules[] = ['path' => $path, 'styles' => $styles];
    }
    return $rules;
}

function admin_layout_design_stored_rules(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }
    $rules = [];
    $seen = [];
    foreach (array_slice($value, 0, 120) as $rule) {
        if (!is_array($rule)) {
            continue;
        }
        try {
            $path = admin_layout_design_validate_path($rule['path'] ?? null);
            $styles = admin_layout_design_validate_styles($rule['styles'] ?? []);
        } catch (Throwable) {
            continue;
        }
        if ($styles === []) {
            continue;
        }
        $key = json_encode($path, JSON_UNESCAPED_SLASHES);
        if (!is_string($key) || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $rules[] = ['path' => $path, 'styles' => $styles];
    }
    return $rules;
}

function admin_layout_design_selector(array $path): string
{
    $segments = [];
    foreach ($path as $segment) {
        $part = (string) $segment['tag'];
        $hasId = (string) ($segment['id'] ?? '') !== '';
        if ($hasId) {
            $part .= '#' . $segment['id'];
        }
        foreach ((array) ($segment['classes'] ?? []) as $class) {
            $part .= '.' . $class;
        }
        if (!$hasId) {
            $part .= ':nth-of-type(' . (int) $segment['index'] . ')';
        }
        $segments[] = $part;
    }
    return implode(' > ', $segments);
}

function admin_layout_design_css_for_page(string $page): string
{
    try {
        $page = admin_layout_design_page_key($page);
    } catch (Throwable) {
        return '';
    }
    $config = admin_layout_design_config();
    $rules = (array) ($config['pages'][$page] ?? []);
    $blocks = [];
    foreach ($rules as $rule) {
        if (!is_array($rule)) {
            continue;
        }
        try {
            $path = admin_layout_design_validate_path($rule['path'] ?? null);
            $styles = admin_layout_design_validate_styles($rule['styles'] ?? []);
        } catch (Throwable) {
            continue;
        }
        if ($styles === []) {
            continue;
        }
        $declarations = [];
        $offsetX = (int) ($styles['offset-x'] ?? 0);
        $offsetY = (int) ($styles['offset-y'] ?? 0);
        unset($styles['offset-x'], $styles['offset-y']);
        if ($offsetX !== 0 || $offsetY !== 0) {
            $declarations[] = 'translate:' . $offsetX . 'px ' . $offsetY . 'px!important';
        }
        foreach ($styles as $property => $value) {
            $declarations[] = $property . ':' . $value . '!important';
        }
        if ($declarations === []) {
            continue;
        }
        $blocks[] = 'body[data-layout-page="' . $page . '"] ' . admin_layout_design_selector($path) . '{' . implode(';', $declarations) . '}';
    }
    return implode("\n", $blocks);
}

function admin_layout_design_page_label(string $page): string
{
    $labels = [
        'dashboard' => 'داشبورد', 'new-ticket' => 'ثبت درخواست', 'ticket' => 'جزئیات درخواست',
        'food-order' => 'سفارش غذا', 'cd-dvd' => 'کنترل CD/DVD', 'cd-dvd-history' => 'تاریخچه CD/DVD',
        'traffic-control' => 'کنترل تردد', 'traffic-control-print' => 'چاپ کنترل تردد', 'settings' => 'تنظیمات سامانه',
        'profile' => 'پروفایل کاربری', 'change-password' => 'تغییر رمز عبور', 'reports' => 'گزارش‌ها',
        'analytics' => 'تحلیل و چارت‌ها', 'supervisor' => 'پنل سوپروایزر', 'queue' => 'صف کاری',
        'assets' => 'دارایی‌ها', 'asset' => 'شناسنامه دارایی', 'asset-history' => 'تاریخچه دارایی',
        'inventory' => 'شناسنامه سیستم‌ها', 'inventory-diagnostics' => 'تست جمع‌آوری اطلاعات',
        'organization' => 'مدیریت سازمان', 'knowledge' => 'دانش‌نامه', 'services' => 'کاتالوگ خدمات',
        'notifications' => 'اعلان‌ها', 'search' => 'جست‌وجو', 'activity-log' => 'لاگ سامانه', 'logs' => 'گزارش خطا',
        'holidays' => 'تقویم SLA', 'backup' => 'به‌روزرسانی و پشتیبان‌گیری', 'chatbot-admin' => 'مدیریت بات',
    ];
    return $labels[$page] ?? str_replace(['-', '_'], ' ', $page);
}

/** JSON API؛ تنها ادمین اصلی می‌تواند طراحی صفحه‌ها را تغییر دهد. */
function admin_layout_design_api_handle(?array $user): never
{
    require_primary_admin();
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    try {
        $page = admin_layout_design_page_key($_GET['layout_page'] ?? 'dashboard');
        if ($method === 'GET') {
            $config = admin_layout_design_config();
            echo json_encode([
                'ok' => true,
                'page' => $page,
                'label' => admin_layout_design_page_label($page),
                'rules' => admin_layout_design_stored_rules($config['pages'][$page] ?? []),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'روش درخواست مجاز نیست.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        require_csrf();
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || strlen($raw) > 90000) {
            throw new InvalidArgumentException('حجم تنظیم طراحی از حد مجاز بیشتر است.');
        }
        $body = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($body)) {
            throw new InvalidArgumentException('محتوای طراحی معتبر نیست.');
        }
        $page = admin_layout_design_page_key($body['page'] ?? $page);
        $config = admin_layout_design_config();
        $pages = (array) ($config['pages'] ?? []);
        $action = (string) ($body['action'] ?? 'save');
        if ($action === 'reset') {
            unset($pages[$page]);
            $ruleCount = 0;
        } elseif ($action === 'save') {
            $rules = admin_layout_design_validate_rules($body['rules'] ?? []);
            $pages[$page] = $rules;
            $ruleCount = count($rules);
        } else {
            throw new InvalidArgumentException('عملیات طراحی معتبر نیست.');
        }
        if (count($pages) > 100) {
            throw new InvalidArgumentException('تعداد صفحه‌های طراحی‌شده از حد مجاز بیشتر است.');
        }
        save_setting(ADMIN_LAYOUT_DESIGN_SETTING, (string) json_encode(['pages' => $pages], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
        if ($user && function_exists('save_audit')) {
            save_audit((int) ($user['id'] ?? 0), $action === 'reset' ? 'page_layout_reset' : 'page_layout_saved', null, ['page' => $page, 'rules' => $ruleCount]);
        }
        echo json_encode(['ok' => true, 'page' => $page, 'rules' => $ruleCount], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    } catch (Throwable $e) {
        http_response_code($e instanceof InvalidArgumentException ? 422 : 400);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
