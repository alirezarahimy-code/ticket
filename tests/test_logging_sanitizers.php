<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-sanitizer-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
$GLOBALS['test_logging_cfg'] = ['logging.strict_national_code' => false];
function cfg(string $key, mixed $default = null): mixed { return $GLOBALS['test_logging_cfg'][$key] ?? $default; }
require dirname(__DIR__) . '/lib/logging-core.php';
$multiline = logging_sanitize_text("alpha\0beta\r\ngamma\tend\x01");
$single = logging_sanitize_single_line("alpha\r\nbeta\tgamma\0end");
$uuidNumericTail = 'aaaaaaaa-aaaa-4aaa-8aaa-123456789012';
$uuidClean = logging_sanitize_value(['event_id'=>$uuidNumericTail,'request_id'=>$uuidNumericTail,'trace_id'=>$uuidNumericTail,'operation_id'=>$uuidNumericTail,'parent_event_id'=>$uuidNumericTail]);
$validNationalCode = '0012345679';
$invalidNationalCode = '0012345678';
$GLOBALS['test_logging_cfg']['logging.strict_national_code'] = true;
$strictValid = logging_sanitize_text('national=' . $validNationalCode);
$strictInvalid = logging_sanitize_text('reference=' . $invalidNationalCode);
$strictPersian = logging_sanitize_text('کد=' . strtr($validNationalCode, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']));
$strictArabic = logging_sanitize_text('كود=' . strtr($validNationalCode, ['0'=>'٠','1'=>'١','2'=>'٢','3'=>'٣','4'=>'٤','5'=>'٥','6'=>'٦','7'=>'٧','8'=>'٨','9'=>'٩']));
$checks = [
    'UUID correlation identifiers survive national-code text sanitization' => count(array_filter($uuidClean, static fn ($value): bool => $value === $uuidNumericTail)) === 5,
    'invalid structured UUID is rejected instead of silently rewritten' => logging_sanitize_value(['event_id'=>'not-a-uuid'])['event_id'] === '[INVALID_UUID]',
    'general sanitizer removes C0 controls except LF and TAB' => $multiline === "alphabeta\ngamma\tend",
    'single-line sanitizer collapses CR/LF/TAB and removes controls' => $single === 'alpha beta gammaend',
    'strict national-code mode redacts valid checksum only' => str_contains($strictValid, '[NATIONAL_ID_REDACTED]') && str_contains($strictInvalid, $invalidNationalCode),
    'strict national-code mode recognizes Persian digits' => str_contains($strictPersian, '[NATIONAL_ID_REDACTED]'),
    'strict national-code mode recognizes Arabic digits' => str_contains($strictArabic, '[NATIONAL_ID_REDACTED]'),
];
$failed = [];
foreach ($checks as $label => $passed) { echo ($passed ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL; if (!$passed) $failed[] = $label; }
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if ($failed) { fwrite(STDERR, count($failed) . " sanitizer test(s) failed.\n"); exit(1); }
echo count($checks) . " sanitizer tests passed.\n";
