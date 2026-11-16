<?php
declare(strict_types=1);

/**
 * Stable Request Center entry point.
 *
 * Request Center lifecycle pages are rendered inside the module boundary.
 * Legacy URLs remain available through the compatibility bridge below; the
 * module's own create/detail/queue/supervisor routes no longer include index.php.
 */
if (!defined('REQUEST_CENTER_INDEPENDENT_ENTRY')) {
    define('REQUEST_CENTER_INDEPENDENT_ENTRY', true);
}

// خطاهای غیرمنتظره در هیچ مسیری نباید به صفحهٔ سفید 500 تبدیل شوند.
// قبل از هر چیز لایهٔ خطای ماژول را مطمئن کن؛ خودش بدون هیچ وابستگی است.
require_once __DIR__ . '/modules/request-center/lib/http-errors.php';
set_exception_handler(static function (Throwable $exception): void {
    error_log('[request-center] uncaught ' . get_class($exception) . ': ' . $exception->getMessage()
        . ' @ ' . $exception->getFile() . ':' . $exception->getLine());
    request_center_error_page(500, 'خطای داخلی مرکز درخواست‌ها',
        'عملیات به‌دلیل یک خطای غیرمنتظره کامل نشد. اگر تکرار شد، آن را با ادمین سامانه و نشانی این صفحه مطرح کنید.');
});

$rcMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$rcPage = trim((string) ($_GET['page'] ?? ''));

// Attachment downloads are Request Center-owned reads; serve them before the
// legacy front controller's global schema setup.
if ($rcMethod === 'GET' && (string) ($_GET['action'] ?? '') === 'download_attachment') {
    require_once __DIR__ . '/bootstrap.php';
    require_once __DIR__ . '/modules/request-center/WorkflowService.php';
    require_once __DIR__ . '/modules/request-center/lib/functions.php';
    require_once __DIR__ . '/modules/request-center/lib/endpoints.php';
    $downloadUser = require_login();
    require_once __DIR__ . '/modules/request-center/RequestCenterRepository.php';
    $downloadSchemaProblems = (new RequestCenterRepository(db()))->schemaProblems();
    if ($downloadSchemaProblems !== []) {
        request_center_error_page(503, 'ساختار پایگاه‌داده آماده نیست',
            'تا کامل شدن اسکیمای مرکز درخواست‌ها، دانلود پیوست در دسترس نیست.', $downloadSchemaProblems, $downloadUser);
    }
    request_center_download_attachment($downloadUser);
}

if ($rcMethod === 'GET' && (string) ($_GET['action'] ?? '') === 'export_decisions') {
    require_once __DIR__ . '/bootstrap.php';
    require_once __DIR__ . '/xlsx-writer.php';
    require_once __DIR__ . '/modules/request-center/WorkflowService.php';
    require_once __DIR__ . '/modules/request-center/lib/functions.php';
    require_once __DIR__ . '/modules/request-center/lib/endpoints.php';
    $decisionsUser = require_login();
    if (!user_can($decisionsUser, 'supervisor.panel') && !user_is_primary_admin($decisionsUser)) {
        request_center_error_page(403, 'دسترسی مجاز نیست', 'خروجی تصمیمات سوپروایزر فقط برای سوپروایزر یا ادمین اصلی مرکز فعال است.');
    }
    require_once __DIR__ . '/modules/request-center/RequestCenterRepository.php';
    $decisionsSchemaProblems = (new RequestCenterRepository(db()))->schemaProblems();
    if ($decisionsSchemaProblems !== []) {
        request_center_error_page(503, 'ساختار پایگاه‌داده آماده نیست',
            'تا کامل شدن اسکیمای مرکز درخواست‌ها، خروجی در دسترس نیست.', $decisionsSchemaProblems, $decisionsUser);
    }
    request_center_export_decisions($decisionsUser);
}

if ($rcMethod === 'GET' && (string) ($_GET['action'] ?? '') === 'export_report') {
    require_once __DIR__ . '/bootstrap.php';
    require_once __DIR__ . '/xlsx-writer.php';
    require_once __DIR__ . '/modules/request-center/WorkflowService.php';
    require_once __DIR__ . '/modules/request-center/lib/functions.php';
    require_once __DIR__ . '/modules/request-center/lib/endpoints.php';
    $reportUser = require_permission('reports.view');
    require_once __DIR__ . '/modules/request-center/RequestCenterRepository.php';
    $reportSchemaProblems = (new RequestCenterRepository(db()))->schemaProblems();
    if ($reportSchemaProblems !== []) {
        request_center_error_page(503, 'ساختار پایگاه‌داده آماده نیست',
            'تا کامل شدن اسکیمای مرکز درخواست‌ها، خروجی در دسترس نیست.', $reportSchemaProblems, $reportUser);
    }
    request_center_export_report($reportUser);
}

// The existing appointment endpoint remains on its established contract.
// It is intentionally not expanded as part of lifecycle phase 3.
if ($rcMethod === 'POST' && (string) ($_GET['calendar'] ?? '') === '1') {
    require __DIR__ . '/modules/request-center/Pages/index.php';
    exit;
}

if ($rcMethod === 'POST') {
    require_once __DIR__ . '/modules/request-center/post-handler.php';
    $rcPostAction = (string) ($_POST['action'] ?? '');
    if (request_center_is_post_action($rcPostAction)) {
        request_center_handle_post();
        exit;
    }
    // Non-RC platform actions are handled inline — the RC runtime never loads the legacy front controller.
    if ($rcPostAction === 'logout') {
        require_once __DIR__ . '/bootstrap.php';
        if (function_exists('require_csrf')) {
            require_csrf();
        }
        if (function_exists('activity_log')) {
            activity_log(['action_code' => 'logout', 'module' => 'auth']);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        setcookie('itsm_csrf', '', time() - 42000, '/');
        unset($_COOKIE['itsm_csrf']);
        session_destroy();
        if (function_exists('redirect')) {
            redirect('index.php?page=login');
        }
        header('Location: index.php?page=login', true, 303);
        exit;
    }
    request_center_error_page(404, 'عملیات ناشناخته', 'این عملیات در مرکز درخواست‌ها پشتیبانی نمی‌شود.');
    exit;
}

// Route lifecycle pages through the module. Do not load the system front
// controller for these pages: it is a compatibility bridge, not their runtime.
$rcLifecyclePages = ['new-ticket', 'ticket', 'queue', 'supervisor', 'services', 'reports', 'analytics', 'knowledge', 'governance', 'ola'];
if ($rcMethod === 'GET' && ($rcPage !== '' && in_array($rcPage, $rcLifecyclePages, true)
    || $rcPage === '' && (int) ($_GET['id'] ?? 0) > 0)) {
    require_once __DIR__ . '/bootstrap.php';
    require_once __DIR__ . '/modules/request-center/WorkflowService.php';
    require_once __DIR__ . '/modules/request-center/lib/functions.php';
    // جزئیات و ثبت درخواست هر دو از helperهای asset_* استفاده می‌کنند؛ در مسیر
    // مستقل ماژول front controller بارگذاری نمی‌شود، پس این قرارداد را همینجا بار می‌کنیم.
    require_once __DIR__ . '/asset-names.php';
    // The lifecycle pages use the shared site chrome, which was extracted from
    // index.php into lib/render.php during Phase 2. Load that contract directly
    // instead of pulling the legacy front controller into the RC runtime.
    require_once __DIR__ . '/lib/render.php';

    $user = require_login();

    require_once __DIR__ . '/modules/request-center/RequestCenterRepository.php';
    $rcSchemaProblems = (new RequestCenterRepository(db()))->schemaProblems();
    if ($rcSchemaProblems !== []) {
        // دستی‌سازی چرام عمداً در اینجا انجام نمی‌شود: چرام ممکن است به ستون‌های ناقص وابسته باشد.
        request_center_error_page(503, 'مرکز درخواست‌ها در دسترس نیست',
            'برای جلوگیری از اجرای ناقص عملیات، این بخش تا رفع موارد زیر غیرفعال شد.', $rcSchemaProblems, $user);
    }

    // Load the optional chatbot only after Request Center schema preflight.
    // Its shared-layout widget is needed for healthy pages, but must not be
    // allowed to touch incomplete dependencies before the controlled 503.
    $rcChatbotProvider = __DIR__ . '/chatbot.php';
    if (!function_exists('chatbot_render_widget') && is_file($rcChatbotProvider)) {
        require_once $rcChatbotProvider;
    }
    if (!function_exists('chatbot_render_widget')) {
        function chatbot_render_widget(?array $user): string
        {
            return '';
        }
    }

    // Only the creation form consumes these existing provider contracts.
    // Other lifecycle workspaces do not load unrelated modules.
    if ($rcPage === 'new-ticket') {
        foreach (['/organization.php', '/inventory.php', '/cd-dvd.php'] as $rcProvider) {
            $rcProviderPath = __DIR__ . $rcProvider;
            if (is_file($rcProviderPath)) {
                require_once $rcProviderPath;
            }
        }
    }
    if ($rcPage === '' && (int) ($_GET['id'] ?? 0) > 0) {
        $rcPage = 'ticket';
    }
    $page = $rcPage;

    if ($page === 'new-ticket') {
        require __DIR__ . '/modules/request-center/Pages/create.php';
        exit;
    }
    if ($page === 'ticket') {
        require __DIR__ . '/modules/request-center/Pages/detail.php';
        exit;
    }
    if ($page === 'queue') {
        require __DIR__ . '/modules/request-center/Pages/queue.php';
        exit;
    }
    if ($page === 'supervisor') {
        require __DIR__ . '/modules/request-center/Pages/supervisor.php';
        exit;
    }
    if ($page === 'services') {
        require __DIR__ . '/modules/request-center/Pages/services.php';
        exit;
    }
    if ($page === 'reports') {
        require __DIR__ . '/modules/request-center/Pages/reports.php';
        exit;
    }
    if ($page === 'analytics') {
        require __DIR__ . '/modules/request-center/Pages/analytics.php';
        exit;
    }
    if ($page === 'knowledge') {
        require __DIR__ . '/modules/request-center/Pages/knowledge.php';
        exit;
    }
    if ($page === 'governance') {
        require __DIR__ . '/modules/request-center/Pages/governance.php';
        exit;
    }
    if ($page === 'ola') {
        require __DIR__ . '/modules/request-center/Pages/ola.php';
        exit;
    }
}

// Workspace dashboards and the scoped read interface already belong to RC.
if ($rcMethod === 'GET' && $rcPage === '') {
    // داشبورد از request_center_is_unit_head/is_org_unit_head برای ساخت فضای کار استفاده می‌کند؛
    // توابع ماژول اینجا بارگذاری نمی‌شدند و فضای «مدیر واحد» بی‌صدا غایب می‌شد.
    require_once __DIR__ . '/modules/request-center/lib/functions.php';
    require __DIR__ . '/modules/request-center/Pages/index.php';
    exit;
}

// Compatibility bridge for older bookmarks and system-wide pages/actions.
// محدود به هدف‌های شناخته‌شدهٔ پوستهٔ اصلی سامانه: مسیرهای ناشناخته با 404 صریح پاسخ می‌گیرند
// و بدون اجازهٔ load شدن هستهٔ legacy از مسیر RC اجرا نمی‌شوند.
// New lifecycle navigation and successful RC operations must not land here.
$rcLegacyParams = [];
parse_str((string) ($_SERVER['QUERY_STRING'] ?? ''), $rcLegacyParams);
$rcLegacyPage = is_string($rcLegacyParams['page'] ?? null) ? strtolower(trim($rcLegacyParams['page'])) : '';
$rcLegacyAction = is_string($rcLegacyParams['action'] ?? null) ? strtolower(trim($rcLegacyParams['action'])) : '';
// فهرست صریح صفحه‌های legacy که در خود index.php پشتیبانی می‌شوند (استخراج‌شده از لینک‌های واقعی کل مخزن).
$rcBridgePages = [
    'activity-log', 'analytics', 'asset-history', 'assets', 'backup',
    'cd-dvd', 'cd-dvd-history', 'change-password', 'chatbot-admin', 'chatbot-api',
    'dashboard', 'domain-scan', 'food-order', 'food-personnel-link', 'food-ticket',
    'help', 'holidays', 'inventory', 'inventory-diagnostics', 'knowledge',
    'local-accounts', 'logging-dashboard', 'login', 'logs', 'new-ticket',
    'notifications', 'organization', 'profile', 'queue', 'reports',
    'search', 'services', 'settings', 'supervisor', 'system-health',
    'ticket', 'tickets', 'traffic-control', 'traffic-control-print',
];
// خروجی‌ها/دانلودهای legacy (همان‌هایی که در مخزن استفاده می‌شوند).
$rcBridgeActions = [
    'export_view_xlsx', 'download_backup', 'export_report', 'export_assets',
    'download_attachment', 'profile_photo', 'notification_feed',
    'export_cd_dvd', 'export_activity_logs', 'diagnose_ldap',
];
$rcBridgeAllowed = ($rcLegacyPage !== '' && in_array($rcLegacyPage, $rcBridgePages, true))
    || ($rcLegacyAction !== '' && in_array($rcLegacyAction, $rcBridgeActions, true));
if (!$rcBridgeAllowed) {
    request_center_error_page(404, 'مسیر ناشناخته',
        'این نشانی در مرکز درخواست‌ها تعریف نشده است. از «مرکز درخواست‌ها» یا منوی سامانه ادامه دهید.');
    exit;
}
require __DIR__ . '/index.php';
exit;
