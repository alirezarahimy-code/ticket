<?php
declare(strict_types=1);

if (!function_exists('pcntl_fork')) {
    fwrite(STDOUT, "SKIP: pcntl is unavailable on this platform; run the Windows parallel test from the staging checklist.\n");
    exit(0);
}
$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-spool-race-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
require dirname(__DIR__) . '/lib/logging-core.php';
$children = [];
$writers = 4;
$perWriter = 100;
for ($i = 0; $i < $writers; $i++) {
    $pid = pcntl_fork();
    if ($pid === -1) throw new RuntimeException('fork failed');
    if ($pid === 0) {
        for ($n = 0; $n < $perWriter; $n++) {
            if (!logging_spool_write('system', ['event_id' => logging_uuid(), 'request_id' => logging_request_id(), 'writer' => $i, 'sequence' => $n])) exit(10);
        }
        exit(0);
    }
    $children[] = $pid;
}
$ok = true;
foreach ($children as $pid) {
    pcntl_waitpid($pid, $status);
    if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) $ok = false;
}
$files = glob($root . '/storage/logs/spool/*.jsonl') ?: [];
$valid = 0;
foreach ($files as $file) {
    $handle = fopen($file, 'rb');
    while (($line = fgets($handle)) !== false) {
        $decoded = json_decode($line, true);
        if (!is_array($decoded) || !is_array($decoded['payload'] ?? null)) { $ok = false; continue; }
        $valid++;
    }
    fclose($handle);
}
$expected = $writers * $perWriter;
echo ($ok && $valid === $expected ? '[PASS] ' : '[FAIL] ') . "concurrent writers produced {$valid}/{$expected} complete JSONL records.\n";
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
exit($ok && $valid === $expected ? 0 : 1);
