<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/lib/logging-integrity.php';

$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logging-integrity-index-' . bin2hex(random_bytes(6)) . '.jsonl';
$records = [
    ['source' => 'system', 'id' => 102, 'created_at' => '2026-11-04 10:00:00'],
    ['source' => 'issue_event_marker', 'id' => 123, 'event_id' => 'event-existing', 'created_at' => '2026-11-04 10:00:00'],
];
$handle = fopen($path, 'xb');
foreach ($records as $record) fwrite($handle, json_encode(['record' => $record], JSON_THROW_ON_ERROR) . "\n");
fclose($handle);
try {
    $index = logging_integrity_read_archive_index($path);
    $lateLowerId = ['source' => 'system', 'id' => 101, 'created_at' => '2026-11-04 09:59:00'];
    if (!logging_integrity_row_is_late($lateLowerId, $index)) throw new RuntimeException('Lower auto-increment ID committed after snapshot was not detected.');
    $knownId = ['source' => 'system', 'id' => 102, 'created_at' => '2026-11-04 10:00:00'];
    if (logging_integrity_row_is_late($knownId, $index)) throw new RuntimeException('Existing append-only record was incorrectly marked late.');
    $lateOlderMarker = ['source' => 'issue_event_marker', 'id' => 124, 'event_id' => 'event-late', 'created_at' => '2026-11-04 09:58:00'];
    if (!logging_integrity_row_is_late($lateOlderMarker, $index)) throw new RuntimeException('Late marker with older timestamp was not detected.');
    $knownMarker = ['source' => 'issue_event_marker', 'id' => 123, 'event_id' => 'event-existing', 'created_at' => '2026-11-04 10:00:00'];
    if (logging_integrity_row_is_late($knownMarker, $index)) throw new RuntimeException('Existing marker was incorrectly marked late.');
    echo "4 integrity late-arrival identity tests passed.\n";
} finally {
    @unlink($path);
}
