<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [];
$coverage = [
    'authentication and profile' => ['actions/account-actions.php', ['login_user(', 'save_audit_required(']],
    'users and roles' => ['actions/role-actions.php', ['save_audit_required(']],
    'organization and user structure' => ['actions/user-structure-actions.php', ['save_audit_required(', 'system_log(']],
    'ticket lifecycle' => ['actions/ticket-actions.php', ['log_ticket_event(', 'save_audit_required(']],
    'asset and inventory' => ['actions/asset-actions.php', ['save_audit_required(', 'system_log(']],
    'service catalog and knowledge base' => ['actions/service-actions.php', ['save_audit_required(', 'service_created']],
    'food service and order' => ['food-order.php', ['food_order_log(', 'food_order_audit(']],
    'food print and delivery' => ['food-ticket.php', ['system_log(', 'save_audit_required(']],
    'traffic control and import' => ['traffic-control.php', ['save_audit_required(']],
    'traffic import' => ['traffic-import.php', ['save_audit_required(']],
    'settings' => ['actions/settings-actions.php', ['save_audit_required(']],
    'backup and restore' => ['actions/backup-actions.php', ['save_audit_required(']],
    'health and report management' => ['system-health.php', ['save_audit_required(', 'request_id']],
];
foreach ($coverage as $label => [$relative, $needles]) {
    $source = @file_get_contents($root . '/' . $relative);
    $checks[$label . ' uses central event/audit APIs'] = is_string($source)
        && count(array_filter($needles, static fn (string $needle): bool => str_contains($source, $needle))) === count($needles);
}
foreach (['cron/food_ticket_worker.php' => 'worker:food-ticket:cycle', 'cron/domain_scan_worker.php' => 'worker:domain-scan:cycle'] as $relative => $operation) {
    $source = (string) @file_get_contents($root . '/' . $relative);
    $checks[basename($relative) . ' starts an isolated correlation context per cycle'] = str_contains($source, "logging_begin_execution('" . $operation . "')")
        && str_contains($source, 'system_log(');
}
$notificationWorker = (string) file_get_contents($root . '/cron/deliver_logging_issue_notifications.php');
$checks['notification worker isolates each delivery operation and logs failures'] = str_contains($notificationWorker, "logging_begin_operation('worker:issue-notification:delivery')")
    && str_contains($notificationWorker, "system_log('error', 'logging-notification-worker'");
$replayWorker = (string) file_get_contents($root . '/cron/replay_logging_spool.php');
$checks['replay worker starts from the common CLI bootstrap'] = str_contains($replayWorker, "require dirname(__DIR__) . '/bootstrap.php';");
$core = (string) file_get_contents($root . '/lib/logging-core.php');
$checks['core exposes request trace operation and parent correlation context'] = str_contains($core, 'function logging_correlation_context()')
    && str_contains($core, "header('X-Request-Id:") && str_contains($core, "header('X-Trace-Id:");

$failed = [];
foreach ($checks as $name => $passed) {
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$passed) $failed[] = $name;
}
if ($failed !== []) {
    fwrite(STDERR, count($failed) . " trace coverage check(s) failed.\n");
    exit(1);
}
echo count($checks) . " trace coverage checks passed.\n";
