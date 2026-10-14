<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

$days = activity_log_retention_days();
$deleted = activity_logs_prune($days);
activity_log([
    'action_code' => 'activity_logs_pruned',
    'action_label' => 'پاک‌سازی خودکار لاگ‌های قدیمی‌تر از ' . $days . ' روز',
    'module' => 'cron',
    'meta' => ['days' => $days, 'deleted' => $deleted],
]);
$systemDeleted = system_logs_prune(30);
$lowValue = ops_low_value_tables_prune();
fwrite(STDOUT, 'Low-value tables pruned: ' . json_encode($lowValue) . "\n");
fwrite(STDOUT, 'System (technical) log prune: ' . $systemDeleted . " rows older than 30 days removed.\n");
fwrite(STDOUT, 'Activity log prune complete. Deleted ' . $deleted . ' rows older than ' . $days . " days.\n");
