<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-phase74-features-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
$GLOBALS['__test_config'] = ['logging' => ['fingerprint_version' => 2, 'strict_national_code' => false]];
function cfg(string $path, mixed $default = null): mixed { $v = $GLOBALS['__test_config']; foreach (explode('.', $path) as $p) { if (!is_array($v) || !array_key_exists($p, $v)) return $default; $v = $v[$p]; } return $v; }
require dirname(__DIR__) . '/lib/logging-core.php';
$checks = [];
$checks['Persian and Arabic digits normalize to ASCII'] = logging_normalize_digits('۰۱۲۳۴۵۶۷۸۹ ٠١٢٣٤٥٦٧٨٩') === '0123456789 0123456789';
$checks['Iranian national-code checksum accepts valid code with leading zero'] = logging_is_valid_iranian_national_code('۰۰۱۲۳۴۵۶۷۹');
$checks['Iranian national-code checksum rejects repeated digits'] = !logging_is_valid_iranian_national_code('۱۱۱۱۱۱۱۱۱۱');
$checks['strict national-code validation remains disabled by default'] = logging_strict_national_code_enabled() === false;
$checks['fingerprint v2 remains default and v3 is opt-in'] = logging_fingerprint_current('SYS-TEST', 'ctx', 'message') === logging_fingerprint_v2('SYS-TEST', 'ctx', 'message');
$GLOBALS['__test_config']['logging']['fingerprint_version'] = 3;
$checks['fingerprint v3 uses a separate version namespace'] = logging_fingerprint_current('SYS-TEST', 'ctx', 'message') === logging_fingerprint_v3('SYS-TEST', 'ctx', 'message')
    && logging_fingerprint_current('SYS-TEST', 'ctx', 'message') !== logging_fingerprint_v2('SYS-TEST', 'ctx', 'message');
logging_increment_counter('throttled_total');
$health = logging_health_counters();
$checks['throttle counter is persisted and readable'] = $health['state'] === 'known' && $health['throttled_total'] === 1 && !empty($health['throttled_last_at']);
$destination = $root . '/status.json'; $temporary = $root . '/status.tmp';
file_put_contents($destination, 'old'); file_put_contents($temporary, 'new');
$checks['atomic replace helper replaces status files on the current platform'] = logging_atomic_replace_file($temporary, $destination) && file_get_contents($destination) === 'new' && !is_file($temporary);
$failed = [];
foreach ($checks as $label => $passed) { echo ($passed ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL; if (!$passed) $failed[] = $label; }
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed) { fwrite(STDERR, count($failed) . " phase74 feature test(s) failed.\n"); exit(1); }
echo count($checks) . " phase74 feature tests passed.\n";
