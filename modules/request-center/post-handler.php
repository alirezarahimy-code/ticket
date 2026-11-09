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
    'create_ola_policy', 'save_rc_work_hours', 'save_rc_capacity', 'rc_holiday_save', 'rc_holiday_delete', 'supervisor_assign_ticket', 'supervisor_decision', 'supervisor_reopen', 'update_ola_policy', 'update_ticket', 'update_due_date',
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
    if ($action === 'create_ticket' && session_status() === PHP_SESSION_ACTIVE) {
        // Preserve bounded, non-file form values so a rejected submission can be
        // corrected without retyping. Uploads and one-time request tokens are
        // deliberately excluded from the session payload.
        $retryCustom = [];
        $postedCustom = is_array($_POST['custom'] ?? null) ? $_POST['custom'] : [];
        foreach (array_slice($postedCustom, 0, 50, true) as $fieldKey => $fieldValue) {
            $fieldKey = (string) $fieldKey;
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $fieldKey) || is_array($fieldValue) || is_object($fieldValue)) {
                continue;
            }
            $retryCustom[$fieldKey] = mb_substr(trim((string) $fieldValue), 0, 2000, 'UTF-8');
        }
        $_SESSION['rc_create_retry'] = [
            'subject' => mb_substr(trim((string) ($_POST['subject'] ?? '')), 0, 255, 'UTF-8'),
            'description' => mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 5000, 'UTF-8'),
            'service_group' => in_array((string) ($_POST['service_group'] ?? ''), ['it', 'support'], true) ? (string) $_POST['service_group'] : '',
            'service_id' => max(0, (int) ($_POST['service_id'] ?? 0)),
            'requesting_unit_id' => max(0, (int) ($_POST['requesting_unit_id'] ?? 0)),
            'priority' => in_array((string) ($_POST['priority'] ?? ''), ['normal', 'urgent', 'critical'], true) ? (string) $_POST['priority'] : '',
            'asset_id' => max(0, (int) ($_POST['asset_id'] ?? 0)),
            'support_location' => mb_substr(trim((string) ($_POST['support_location'] ?? '')), 0, 255, 'UTF-8'),
            'support_equipment' => mb_substr(trim((string) ($_POST['support_equipment'] ?? '')), 0, 2000, 'UTF-8'),
            'parent_ticket_id' => max(0, (int) ($_POST['parent_ticket_id'] ?? 0)),
            'custom' => $retryCustom,
            'saved_at' => time(),
        ];
    }
    $ticketId = max(0, (int) ($_POST['ticket_id'] ?? 0));
    $target = match ($action) {
        'create_ticket' => 'request-center.php?page=new-ticket',
        'bulk_update_tickets' => 'request-center.php?page=queue&queue='
            . rawurlencode(in_array((string) ($_POST['rc_return_queue'] ?? 'all'), ['all', 'unassigned', 'overdue', 'mine', 'waiting_user', 'resolved', 'cancellation_requested', 'cancelled', 'rejected'], true)
                ? (string) $_POST['rc_return_queue'] : 'all')
            . '&p=' . max(1, min(100000, (int) ($_POST['rc_return_p'] ?? 1)))
            . (trim((string) ($_POST['rc_return_q'] ?? '')) !== '' ? '&q=' . rawurlencode(substr((string) $_POST['rc_return_q'], 0, 100)) : '')
            . (in_array((string) ($_POST['rc_return_status'] ?? ''), ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved', 'closed', 'cancelled', 'rejected'], true) ? '&status=' . rawurlencode((string) $_POST['rc_return_status']) : '')
            . (in_array((string) ($_POST['rc_return_priority'] ?? ''), ['normal', 'urgent', 'critical'], true) ? '&priority=' . rawurlencode((string) $_POST['rc_return_priority']) : '')
            . (in_array((string) ($_POST['rc_return_service_group'] ?? ''), ['it', 'support'], true) ? '&service_group=' . rawurlencode((string) $_POST['rc_return_service_group']) : ''),
        'supervisor_decision', 'supervisor_reopen', 'supervisor_assign_ticket' => $ticketId > 0
            ? request_center_ticket_return_url($ticketId) : 'request-center.php?workspace=supervisor',
        'save_rc_work_hours' => 'request-center.php?workspace=admin#sla-work-hours',
        'save_rc_capacity' => 'request-center.php?workspace=admin#rc-capacity',
        'rc_holiday_save' => 'request-center.php?workspace=admin#rc-holidays',
        'rc_holiday_delete' => 'request-center.php?workspace=admin#rc-holidays',
        'create_ola_policy', 'update_ola_policy' => 'request-center.php?workspace=admin#ola-policies',
        'add_service', 'edit_service', 'delete_service', 'add_service_field',
        'save_service_category', 'delete_service_category' => 'request-center.php?page=services',
        'add_knowledge' => 'index.php?page=knowledge',
        'add_asset_relation' => 'index.php?page=inventory&id=' . max(0, (int) ($_POST['source_asset_id'] ?? 0)),
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
    // Module helpers resolve the active actor through the shared request scope.
    // The independent runner is a function boundary, unlike the legacy global
    // controller, so explicitly publish the authenticated actor for that contract.
    $GLOBALS['user'] = $user;

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
