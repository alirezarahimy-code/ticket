<?php
declare(strict_types=1);

/**
 * Stable Request Center entry point.
 *
 * Request Center lifecycle pages are rendered inside the module boundary.
 * Legacy URLs remain available through the compatibility bridge below; the
 * module's own create/detail/queue/supervisor routes no longer include index.php.
 */
$rcMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$rcPage = trim((string) ($_GET['page'] ?? ''));

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
    // Non-RC actions keep their original payload and compatibility semantics.
    require __DIR__ . '/index.php';
    exit;
}

// Route lifecycle pages through the module. Do not load the system front
// controller for these pages: it is a compatibility bridge, not their runtime.
$rcLifecyclePages = ['new-ticket', 'ticket', 'queue', 'supervisor', 'services', 'reports', 'analytics'];
if ($rcMethod === 'GET' && ($rcPage !== '' && in_array($rcPage, $rcLifecyclePages, true)
    || $rcPage === '' && (int) ($_GET['id'] ?? 0) > 0)) {
    require_once __DIR__ . '/bootstrap.php';
    require_once __DIR__ . '/modules/request-center/WorkflowService.php';
    require_once __DIR__ . '/modules/request-center/lib/functions.php';

    $user = require_login();

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
}

// Workspace dashboards and the scoped read interface already belong to RC.
if ($rcMethod === 'GET' && $rcPage === '') {
    require __DIR__ . '/modules/request-center/Pages/index.php';
    exit;
}

// Compatibility bridge for older bookmarks and system-wide pages/actions.
// New lifecycle navigation and successful RC operations must not land here.
require __DIR__ . '/index.php';
exit;
