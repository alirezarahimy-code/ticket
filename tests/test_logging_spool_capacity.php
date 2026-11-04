<?php
declare(strict_types=1);

$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-spool-capacity-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
define('LOGGING_SPOOL_MAX_BYTES', 1);
define('LOGGING_SPOOL_MAX_FILES', 1);
define('LOGGING_SPOOL_MIN_FREE_BYTES', 0);
require dirname(__DIR__) . '/lib/logging-core.php';

$event = ['event_id'=>logging_uuid(),'request_id'=>logging_uuid(),'code'=>'SYS-CAP-001','category'=>'system','level'=>'error','context'=>'capacity-test','message'=>'must not be lost silently','meta'=>[]];
$accepted = logging_record_failure('system', $event);
$counters = logging_health_counters();
$spoolFiles = glob($root . '/storage/logs/spool/*.jsonl') ?: [];
$emergency = is_file($root . '/storage/logs/emergency.log') ? (string) file_get_contents($root . '/storage/logs/emergency.log') : '';
$checks = [
    'capacity limit rejects oversized event' => $accepted === false && $spoolFiles === [],
    'dropped counter records capacity rejection' => ($counters['dropped_total'] ?? 0) === 1,
    'independent emergency path records drop without payload' => str_contains($emergency, 'event_dropped') && !str_contains($emergency, 'must not be lost silently'),
];
$failed = [];
foreach ($checks as $label=>$ok) { echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL; if (!$ok) $failed[] = $label; }
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed !== []) { fwrite(STDERR, count($failed) . " capacity test(s) failed.\n"); exit(1); }
echo count($checks) . " spool capacity tests passed.\n";
