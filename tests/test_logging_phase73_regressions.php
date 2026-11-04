<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-logging-phase73-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
require dirname(__DIR__) . '/lib/logging-core.php';
$checks = [];
$fp1 = logging_fingerprint_v2('SYS-PHP-001', 'request', 'Failure 550e8400-e29b-41d4-a716-446655440000 at 2026-11-04 12:30:00');
$fp2 = logging_fingerprint_v2('SYS-PHP-001', 'request', 'Failure 550e8400-e29b-41d4-a716-446655440001 at 2026-11-04 12:31:00');
$checks['fingerprint v2 is SHA-256 and normalizes volatile UUID/timestamp values'] = strlen($fp1) === 64 && hash_equals($fp1, $fp2);
$trace = logging_exception_trace(new RuntimeException('password=secret-value'));
$traceJson = json_encode($trace, JSON_UNESCAPED_SLASHES);
$checks['exception trace is bounded, redacted and excludes argument payloads'] = count($trace) === 1 && count($trace[0]['frames']) <= 13 && !str_contains((string) $traceJson, 'secret-value') && !str_contains((string) $traceJson, 'args');
logging_db_circuit_failure(new RuntimeException('synthetic outage'));
$checks['database circuit opens after transient failure'] = logging_db_circuit_should_skip() === true;
$checks['permanent SQL errors do not open the database circuit'] = logging_db_error_is_transient(new PDOException('Unknown column in field list')) === false;
$statePath = logging_db_circuit_path();
$state = json_decode((string) file_get_contents($statePath), true);
$state['open_until'] = time() - 1;
file_put_contents($statePath, json_encode($state), LOCK_EX);
$firstProbe = logging_db_circuit_should_skip();
$secondProbe = logging_db_circuit_should_skip();
$checks['half-open probe lock is held by the probing process'] = $firstProbe === false && is_resource($GLOBALS['__logging_db_probe_lock'] ?? null) && $secondProbe === false;
logging_db_circuit_success();
$checks['successful half-open probe closes the circuit'] = !is_file($statePath) && logging_db_circuit_should_skip() === false;
$checks['stack trace survives event-specific allowlist and remains redacted'] = isset(logging_sanitize_event_meta(['stack_trace'=>$trace], 'SYS-PHP-001', 'request')['stack_trace']) && !str_contains(json_encode(logging_sanitize_event_meta(['stack_trace'=>$trace], 'SYS-PHP-001', 'request')), 'secret-value');
$failed=[];
foreach ($checks as $label=>$ok) { echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL; if (!$ok) $failed[]=$label; }
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($it as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed) { fwrite(STDERR, count($failed)." phase73 regression(s) failed\n"); exit(1); }
echo count($checks)." phase73 regression tests passed.\n";
