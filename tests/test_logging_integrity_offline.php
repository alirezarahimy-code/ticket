<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-integrity-index-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
require dirname(__DIR__) . '/lib/logging-integrity.php';
$records = [
    ['source' => 'system', 'id' => 100, 'event_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'meta_json' => '{}'],
    ['source' => 'issue_state', 'id' => 5, 'event_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 'meta_json' => '{"status":"open"}'],
    ['source' => 'issue_event_marker', 'id' => 123, 'event_id' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc', 'created_at' => '2026-11-04 10:00:00', 'meta_json' => '{}'],
];
$path = $root . '/index.jsonl';
$handle = fopen($path, 'wb');
$previous = str_repeat('0', 64);
foreach ($records as $record) {
    $canonical = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $hash = hash('sha256', $previous . "\n" . $canonical);
    fwrite($handle, json_encode(['previous_hash' => $previous, 'record_hash' => $hash, 'record' => $record], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
    $previous = $hash;
}
fclose($handle);
$index = logging_integrity_read_archive_index($path);
$checks = [
    'existing append-only record is not late' => !logging_integrity_row_is_late($records[0], $index),
    'higher append-only id is late' => logging_integrity_row_is_late(['source' => 'system', 'id' => 101], $index),
    'unchanged mutable issue state is not late' => !logging_integrity_row_is_late($records[1], $index),
    'changed mutable issue state is late' => logging_integrity_row_is_late(['source' => 'issue_state', 'id' => 5, 'event_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 'meta_json' => '{"status":"resolved"}'], $index),
    'existing event marker is not late' => !logging_integrity_row_is_late($records[2], $index),
    'new event marker is late' => logging_integrity_row_is_late(['source' => 'issue_event_marker', 'id' => 124, 'event_id' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd', 'created_at' => '2026-11-04 10:00:01'], $index),
];
$failed = [];
foreach ($checks as $name => $ok) { echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL; if (!$ok) $failed[] = $name; }
@unlink($path); @rmdir($root);
if ($failed) exit(1);
echo count($checks) . " integrity index tests passed.\n";
