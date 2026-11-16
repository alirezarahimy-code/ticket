<?php
declare(strict_types=1);

/**
 * Phase-3 bridge lifecycle — shared POST protocol for the legacy front
 * controller and the Request Center independent entry.
 *
 * Both functions carry the legacy protocol byte-for-byte (CSRF gate,
 * logging_begin_operation, the activity_post shutdown recorder and the
 * transactional rollback + activity_log('post_action_failed') + flash +
 * redirect error contract). Do NOT diverge their semantics: the Request
 * Center module depends on identical log records, flash and redirects.
 */

/**
 * Begin a POST operation context: CSRF enforcement, operation logging and the
 * fire-and-forget success-audit shutdown hook. Returns the resolved action.
 */
function post_context_begin(): string
{
    require_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if (function_exists('logging_begin_operation')) logging_begin_operation('post:' . $action);
    $GLOBALS['activity_post'] = ['action' => $action, 'page' => (string) ($_GET['page'] ?? ''), 'failed' => false];
    register_shutdown_function(static function (): void {
        $pending = $GLOBALS['activity_post'] ?? null;
        if (!$pending || $pending['action'] === '' || $pending['failed'] || !function_exists('activity_log')) {
            return;
        }
        if (!empty($GLOBALS['activity_audited'])) {
            return;
        }
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
        if ($isAjax || str_starts_with((string) ($pending['page'] ?? ''), 'api')) {
            return;
        }
        static $skip = ['login', 'logout'];
        if (in_array($pending['action'], $skip, true)) {
            return;
        }
        activity_log([
            'action_code' => $pending['action'],
            'module' => $pending['page'] !== '' ? $pending['page'] : 'action',
        ]);
    });
    return $action;
}

/**
 * Terminal error handler for the POST try/catch: rolls back any open
 * transaction, records the failure in the activity log and flash, and
 * redirects to the same page the legacy front controller used.
 */
function post_context_on_error(Throwable $exception, string $page): never
{
    if (isset($GLOBALS['activity_post'])) {
        $GLOBALS['activity_post']['failed'] = true;
    }
    if (db()->inTransaction()) {
        db_rollback('index.php');
    }
    if (function_exists('activity_log')) {
        try {
            activity_log([
                'action_code' => 'post_action_failed',
                'action_label' => 'عملیات POST ناموفق بود',
                'module' => (string) ($_GET['page'] ?? 'action'),
                'result' => 'failure',
                'meta' => [
                    'action' => (string) ($GLOBALS['activity_post']['action'] ?? 'unknown'),
                    'page' => (string) ($_GET['page'] ?? ''),
                    'error_type' => get_class($exception),
                ],
            ]);
        } catch (Throwable) {}
    }
    // app_error_log خودش یک رخداد فنی ساخت‌یافته ثبت می‌کند؛ system_log دوم باعث دوباره‌شماری همین خطا می‌شد.
    app_error_log('ITSM request failed: ' . $exception->getMessage() . ' | ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
    flash('danger', $exception instanceof RuntimeException ? $exception->getMessage() : 'عملیات انجام نشد. گزارش خطا در لاگ سامانه ثبت شد.');
    $action = (string) ($GLOBALS['activity_post']['action'] ?? '');
    $errorRedirect = 'index.php?page=' . urlencode($page);
    if ($page === 'settings' && in_array($action, ['create_access_group', 'save_access_group', 'delete_access_group'], true)) {
        $failedGroupId = max(0, (int) ($_POST['group_id'] ?? 0));
        if ($failedGroupId > 0) {
            $errorRedirect .= '&access_group_id=' . $failedGroupId;
        }
        $errorRedirect .= '#access-groups';
    }
    redirect($errorRedirect);
}
