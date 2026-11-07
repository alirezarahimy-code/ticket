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
    'supervisor_decision', 'supervisor_reopen', 'update_ticket',
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

    // همان لایه‌های ریشه‌ای که index.php در هر درخواست تولید به‌صورت تضمینی بارگذاری می‌کند.
    // org: org_ticket_schema_ensure (فراخوان اول create_ticket — بدون آن، مسیر POST مستقل Fatal می‌داد)
    // inventory: inventory_client_ip (مسیر اعتبارسنجی دارایی در خدمات IT)
    // cd-dvd: cd_dvd_fetch_record/cd_dvd_can_* (کنترل «درخواست اصلاح CD/DVD» در create_ticket)
    $rootDir = dirname(__DIR__, 2);
    foreach (['/organization.php', '/inventory.php', '/cd-dvd.php'] as $stackFile) {
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

    // همان پیش‌شرط‌های هر درخواست production: seq/autofill/loggerها آماده باشد.
    if (function_exists('ticket_number_ensure_schema')) {
        ticket_number_ensure_schema();
        ticket_number_autofill();
    }

    try {
        require __DIR__ . '/Actions/dispatch.php';
    } catch (Throwable $exception) {
        // دقیقاً همان قرارداد خطای legacy: rollback + لاگ + فلش + redirect
        post_context_on_error($exception, (string) ($_GET['page'] ?? ''));
    }

    // Defensive fallback: every name in the registry is handled inside the
    // dispatcher (they redirect on success). Reached only if the registry and
    // the dispatcher diverge — fail loudly through the shared error contract.
    post_context_on_error(new RuntimeException('عملیات مرکز درخواست‌ها پیدا نشد.'), (string) ($_GET['page'] ?? ''));
}
