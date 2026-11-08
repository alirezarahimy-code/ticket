<?php
declare(strict_types=1);

/**
 * Phase-3 bridge lifecycle — Request Center POST runner.
 *
 * Executes the RC action registry (modules/request-center/Actions/dispatch.php)
 * through the exact same POST protocol the legacy front controller uses
 * (lib/http-post.php), so logging, CSRF, transaction rollback, flash and
 * redirect behaviour are byte-identical whether the request entered through
 * index.php or through the independent entry request-center.php.
 *
 * This file never renders output and never returns for an RC-owned action;
 * it ends via redirect()/exit like the legacy dispatch path.
 */

const REQUEST_CENTER_POST_ACTIONS = [
    'add_asset_relation', 'add_knowledge', 'add_service', 'add_service_field',
    'bulk_update_tickets', 'cancel_ticket', 'claim_ticket', 'create_ticket',
    'decide_ticket_cancellation', 'delete_service', 'delete_service_category',
    'edit_service', 'rate_ticket', 'reopen_ticket', 'reply', 'save_service_category',
    'supervisor_assign_ticket', 'supervisor_decision', 'supervisor_reopen', 'update_ola_policy', 'update_ticket',
];

function request_center_is_post_action(string $action): bool
{
    return in_array($action, REQUEST_CENTER_POST_ACTIONS, true);
}

/**
 * If $_POST['action'] is RC-owned, runs the protocol + handler and never
 * returns. Otherwise returns false WITHOUT any side effect, so the caller can
 * forward the request elsewhere (in-process) intact.
 */
/**
 * Request Center-local error boundary. It preserves the shared POST failure
 * contract while returning lifecycle failures to their independent workspace.
 * Cross-module actions retain their original legacy destinations.
 */
function request_center_post_error(Throwable $exception, string $page): never
{
    if (isset($GLOBALS['activity_post'])) {
        $GLOBALS['activity_post']['failed'] = true;
    }
    if (db()->inTransaction()) {
        db_rollback('request-center.php');
    }
    if (function_exists('activity_log')) {
        try {
            activity_log([
                'action_code' => 'post_action_failed',
                'action_label' => 'عملیات POST ناموفق بود',
                'module' => (string) ($_GET['page'] ?? 'request-center'),
                'result' => 'failure',
                'meta' => [
                    'action' => (string) ($GLOBALS['activity_post']['action'] ?? 'unknown'),
                    'page' => (string) ($_GET['page'] ?? ''),
                    'error_type' => get_class($exception),
                ],
            ]);
        } catch (Throwable) {
        }
    }
    app_error_log('Request Center POST failed [action=' . (string) ($GLOBALS['activity_post']['action'] ?? 'unknown') . ']: ' . $exception->getMessage() . ' | ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
    flash('danger', $exception instanceof RuntimeException
        ? $exception->getMessage()
        : 'عملیات انجام نشد. گزارش خطا در لاگ سامانه ثبت شد.');

    $action = (string) ($GLOBALS['activity_post']['action'] ?? '');
    $ticketId = max(0, (int) ($_POST['ticket_id'] ?? 0));
    $target = match ($action) {
        'create_ticket' => 'request-center.php?page=new-ticket',
        'bulk_update_tickets' => 'request-center.php?page=queue&queue='
            . rawurlencode(in_array((string) ($_POST['rc_return_queue'] ?? 'all'), ['all', 'unassigned', 'overdue', 'mine', 'waiting_user', 'resolved', 'cancellation_requested', 'cancelled', 'rejected'], true)
                ? (string) $_POST['rc_return_queue'] : 'all')
            . '&p=' . max(1, min(100000, (int) ($_POST['rc_return_p'] ?? 1))),
        'supervisor_decision', 'supervisor_reopen' => $ticketId > 0
            ? request_center_ticket_return_url($ticketId) : 'request-center.php?workspace=supervisor',
        'update_ola_policy' => 'request-center.php?workspace=admin#ola-policies',
        'add_service', 'edit_service', 'delete_service', 'add_service_field',
        'save_service_category', 'delete_service_category' => 'request-center.php?page=services',
        'add_asset_relation' => 'index.php?page=inventory&id=' . max(0, (int) ($_POST['source_id'] ?? 0)),
        default => $ticketId > 0
            ? request_center_ticket_return_url($ticketId)
            : 'request-center.php',
    };
    redirect($target);
}

function request_center_handle_post(): bool
{
    $action = (string) ($_POST['action'] ?? '');
    if (!request_center_is_post_action($action)) {
        return false;
    }

    if (!defined('APP_ROOT')) {
        // Self-sufficient: the independent entry does not load the front controller.
        require_once dirname(__DIR__, 2) . '/bootstrap.php';
    }

    // The legacy front controller populated $user before including its action
    // registry. The independent runner must establish the same authenticated
    // execution context explicitly; lifecycle handlers such as reply rely on it.
    $user = require_login();

    // Load only the established provider contracts required by this action.
    // These files define adapters; no provider schema-ensure routine is called here.
    $rootDir = dirname(__DIR__, 2);
    $providerFiles = match ($action) {
        'create_ticket' => ['/organization.php', '/inventory.php', '/cd-dvd.php'],
        'add_asset_relation' => ['/inventory.php'],
        default => [],
    };
    foreach ($providerFiles as $stackFile) {
        $stackPath = $rootDir . $stackFile;
        if (is_file($stackPath)) {
            require_once $stackPath;
        }
    }

    // Mirror the legacy front controller's load order for module code.
    require_once __DIR__ . '/WorkflowService.php';
    require_once __DIR__ . '/lib/functions.php';

    require_once dirname(__DIR__, 2) . '/lib/http-post.php';
    post_context_begin();

    try {
        require_once __DIR__ . '/RequestCenterRepository.php';
        $schemaProblems = (new RequestCenterRepository(db()))->schemaProblems();
        if ($schemaProblems !== []) {
            throw new RuntimeException('ساختار پایگاه‌دادهٔ مرکز درخواست‌ها آماده نیست: ' . implode('؛ ', $schemaProblems));
        }
        require __DIR__ . '/Actions/dispatch.php';
    } catch (Throwable $exception) {
        // دقیقاً همان قرارداد خطای legacy: rollback + لاگ + فلش + redirect
        request_center_post_error($exception, (string) ($_GET['page'] ?? ''));
    }

    // Defensive fallback: every name in the registry is handled inside the
    // dispatcher (they redirect on success). Reached only if the registry and
    // the dispatcher diverge — fail loudly through the shared error contract.
    request_center_post_error(new RuntimeException('عملیات مرکز درخواست‌ها پیدا نشد.'), (string) ($_GET['page'] ?? ''));
}
