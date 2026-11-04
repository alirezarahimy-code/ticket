<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit("Not found\n"); }
require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/lib/logging-integrity.php';

$key = (string) getenv('LOGGING_INTEGRITY_KEY');
$backupDir = trim((string) getenv('LOGGING_INTEGRITY_BACKUP_DIR'));
if (strlen($key) < 32 || $backupDir === '') {
    fwrite(STDERR, "Set LOGGING_INTEGRITY_KEY (32+ chars) and LOGGING_INTEGRITY_BACKUP_DIR (separate local volume).\n");
    exit(2);
}
$cliArgs = array_slice($argv, 1);
$reconcileOnly = in_array('--reconcile-only', $cliArgs, true);
$dateArg = null;
foreach ($cliArgs as $argument) { if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', (string) $argument)) { $dateArg = (string) $argument; break; } }
$date = $dateArg ?? date('Y-m-d', strtotime('yesterday'));
$start = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
if (!$start || $start->format('Y-m-d') !== $date) { fwrite(STDERR, "Date must be YYYY-MM-DD.\n"); exit(2); }
$end = $start->modify('+1 day');
if (!is_dir($backupDir) && !@mkdir($backupDir, 0700, true) && !is_dir($backupDir)) { fwrite(STDERR, "Cannot create integrity backup directory.\n"); exit(2); }
$rootReal = realpath(APP_ROOT);
$backupReal = realpath($backupDir);
if (!$rootReal || !$backupReal || str_starts_with(strtolower($backupReal . DIRECTORY_SEPARATOR), strtolower(rtrim($rootReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR))) {
    fwrite(STDERR, "Integrity backup directory must be outside the application root.\n"); exit(2);
}
if (!is_writable($backupReal)) { fwrite(STDERR, "Integrity backup directory is not writable.\n"); exit(2); }
$baseArchivePath = $backupReal . DIRECTORY_SEPARATOR . 'logging-integrity-' . $date . '.jsonl';
$baseManifestPath = $baseArchivePath . '.manifest.json';
$exportLockPath = $backupReal . DIRECTORY_SEPARATOR . '.logging-integrity-' . $date . '.export.lock';
$exportLock = @fopen($exportLockPath, 'c');
if ($exportLock === false || !@flock($exportLock, LOCK_EX | LOCK_NB)) {
    if (is_resource($exportLock)) fclose($exportLock);
    fwrite(STDERR, "Integrity export blocked: another exporter is handling this date.\n");
    exit(3);
}
$lateMode = false;
$lateIndex = ['max_ids' => [], 'mutable_hashes' => [], 'event_ids' => []];
$supplementSequence = 0;
$previousSupplementHmac = str_repeat('0', 64);
$baseManifest = null;
if (is_file($baseArchivePath) && is_file($baseManifestPath)) {
    $baseManifest = json_decode((string) @file_get_contents($baseManifestPath), true);
    if (!is_array($baseManifest) || !logging_integrity_verify_daily_manifest($baseArchivePath, $baseManifest, $key)) {
        fwrite(STDERR, "Existing daily snapshot is invalid; late-arrival reconciliation is blocked.\n");
        exit(1);
    }
    $lateMode = true;
    $lateIndex = logging_integrity_read_archive_index($baseArchivePath);
    $existingSupplements = glob($backupReal . DIRECTORY_SEPARATOR . 'logging-integrity-' . $date . '.late-*.jsonl') ?: [];
    sort($existingSupplements, SORT_STRING);
    if ($existingSupplements !== []) {
        $lastSupplement = $existingSupplements[count($existingSupplements) - 1];
        $chainError = logging_integrity_verify_supplement_chain($lastSupplement, $key);
        if ($chainError !== null) {
            fwrite(STDERR, "Existing late-arrival supplement chain is invalid: " . $chainError . "\n");
            exit(1);
        }
        foreach ($existingSupplements as $supplementPath) {
            $supplementManifest = json_decode((string) @file_get_contents($supplementPath . '.manifest.json'), true);
            if (!is_array($supplementManifest)) { fwrite(STDERR, "Late-arrival supplement manifest is unreadable.\n"); exit(1); }
            $lateIndex = logging_integrity_merge_archive_indexes($lateIndex, logging_integrity_read_archive_index($supplementPath));
        }
        $lastManifest = json_decode((string) @file_get_contents($lastSupplement . '.manifest.json'), true);
        $supplementSequence = (int) ($lastManifest['supplement_sequence'] ?? 0);
        $previousSupplementHmac = (string) ($lastManifest['hmac_sha256'] ?? '');
    }
    $archivePath = $baseArchivePath . '.late-' . str_pad((string) ($supplementSequence + 1), 6, '0', STR_PAD_LEFT) . '-' . gmdate('Ymd\THis\Z') . '-' . bin2hex(random_bytes(3)) . '.jsonl';
    $manifestPath = $archivePath . '.manifest.json';
} elseif (file_exists($baseArchivePath) || file_exists($baseManifestPath)) {
    fwrite(STDERR, "Incomplete prior integrity snapshot exists; inspect it manually before retrying.\n");
    exit(1);
} else {
    if ($reconcileOnly) { fwrite(STDOUT, "No base daily snapshot for {$date}; reconciliation-only run skipped without creating a new chain root.\n"); exit(0); }
    $archivePath = $baseArchivePath;
    $manifestPath = $baseManifestPath;
}

// Freeze replay and spool writers while checking backlog; otherwise late replay can be omitted
// from an immutable daily snapshot. A non-empty queue (including processing/DLQ/temp files)
// blocks export conservatively; operators must drain or investigate it and rerun.
$spoolDir = APP_ROOT . '/storage/logs/spool';
$integrityLocks = [];
if (!is_dir($spoolDir) && !@mkdir($spoolDir, 0750, true) && !is_dir($spoolDir)) {
    fwrite(STDERR, "Integrity export blocked: cannot initialize logging spool gate.\n");
    exit(3);
}
foreach (['replay.lock', '.writer.lock'] as $lockName) {
    $lockHandle = @fopen($spoolDir . DIRECTORY_SEPARATOR . $lockName, 'c');
    if ($lockHandle === false || !@flock($lockHandle, LOCK_EX | LOCK_NB)) {
        if (is_resource($lockHandle)) fclose($lockHandle);
        fwrite(STDERR, "Integrity export blocked: logging spool worker/writer is active.\n");
        exit(3);
    }
    $integrityLocks[] = $lockHandle;
}
$pendingSpool = array_merge(
    glob($spoolDir . DIRECTORY_SEPARATOR . '*.jsonl') ?: [],
    glob($spoolDir . DIRECTORY_SEPARATOR . '*.jsonl.processing') ?: [],
    glob($spoolDir . DIRECTORY_SEPARATOR . '*.rewrite.tmp') ?: [],
    glob($spoolDir . DIRECTORY_SEPARATOR . 'dead-letter' . DIRECTORY_SEPARATOR . '*.jsonl') ?: [],
    glob($spoolDir . DIRECTORY_SEPARATOR . '.*.tmp') ?: []
);
$pendingSpool = array_values(array_filter($pendingSpool, static fn (string $path): bool => is_file($path) && (int) @filesize($path) > 0));
if ($pendingSpool !== []) {
    fwrite(STDERR, "Integrity export blocked: spool backlog/dead-letter evidence must be resolved first (" . count($pendingSpool) . " file(s)).\n");
    exit(3);
}
$sql = "SELECT id, created_at, 'system' AS source, event_id, request_id, trace_id, operation_id, parent_event_id,
            code, category, level AS severity, context AS module, message, meta_json, NULL AS result, NULL AS action_code,
            NULL AS target_type, NULL AS target_id
        FROM system_logs WHERE created_at >= ? AND created_at < ?
        UNION ALL
        SELECT id, created_at, 'activity' AS source, event_id, request_id, trace_id, operation_id, parent_event_id,
            code, category, result AS severity, module, action_label AS message, meta_json, result, action_code,
            target_type, target_id
        FROM activity_logs WHERE created_at >= ? AND created_at < ?
        UNION ALL
        SELECT id, created_at, 'ticket' AS source, event_id, request_id, trace_id, operation_id, parent_event_id,
            NULL AS code, 'business' AS category, event_type AS severity, 'ticket' AS module,
            CONCAT('درخواست #', ticket_id, ': ', event_type) AS message, CAST(details AS CHAR) AS meta_json,
            NULL AS result, event_type AS action_code, 'ticket' AS target_type, ticket_id AS target_id
        FROM ticket_events WHERE created_at >= ? AND created_at < ?
        UNION ALL
        SELECT id, created_at, 'issue_history' AS source, NULL AS event_id, request_id, NULL AS trace_id, NULL AS operation_id, NULL AS parent_event_id,
            'AUD-ISSUE-001' AS code, 'audit' AS category, after_status AS severity, 'issue' AS module,
            CONCAT('Issue ', fingerprint, ': ', COALESCE(before_status, 'unknown'), ' -> ', after_status) AS message,
            JSON_OBJECT('actor_id', actor_id, 'before_assigned_to', before_assigned_to, 'after_assigned_to', after_assigned_to,
                        'before_note', before_note, 'after_note', after_note) AS meta_json,
            after_status AS result, 'issue_history' AS action_code, 'issue' AS target_type, NULL AS target_id
        FROM log_issue_history WHERE created_at >= ? AND created_at < ?
        UNION ALL
        SELECT id, created_at, 'issue_notification' AS source, NULL AS event_id, NULL AS request_id, NULL AS trace_id, NULL AS operation_id, NULL AS parent_event_id,
            'SYS-LOG-ALERT-001' AS code, 'system' AS category, state AS severity, 'notification' AS module,
            CONCAT('Issue notification ', state) AS message,
            JSON_OBJECT('fingerprint', fingerprint, 'recipient_id', recipient_id, 'attempts', attempts,
                        'last_error', last_error, 'delivered_at', delivered_at) AS meta_json,
            state AS result, 'issue_notification' AS action_code, 'issue' AS target_type, NULL AS target_id
        FROM log_issue_notifications WHERE created_at >= ? AND created_at < ?
        UNION ALL
        SELECT CAST(CRC32(event_id) AS UNSIGNED) AS id, created_at, 'issue_event_marker' AS source, event_id, NULL AS request_id, NULL AS trace_id, NULL AS operation_id, NULL AS parent_event_id,
            'SYS-ISSUE-MARKER' AS code, 'system' AS category, 'processed' AS severity, 'issue-aggregator' AS module,
            CONCAT('Issue event marker ', event_id) AS message, JSON_OBJECT('fingerprint', fingerprint) AS meta_json,
            'processed' AS result, 'issue_event_marker' AS action_code, 'issue' AS target_type, NULL AS target_id
        FROM log_issue_events WHERE created_at >= ? AND created_at < ?
        UNION ALL
        SELECT id, updated_at AS created_at, 'issue_state' AS source, last_event_id AS event_id, last_request_id AS request_id, NULL AS trace_id, NULL AS operation_id, NULL AS parent_event_id,
            code, category, severity, context AS module, title AS message,
            JSON_OBJECT('fingerprint', fingerprint, 'status', status, 'occurrences', occurrences,
                        'assigned_to', assigned_to, 'resolution_note', resolution_note,
                        'first_seen', first_seen, 'last_seen', last_seen) AS meta_json,
            status AS result, 'issue_state' AS action_code, 'issue' AS target_type, id AS target_id
        FROM log_issues WHERE updated_at >= ? AND updated_at < ?
        UNION ALL
        SELECT CAST(CRC32(alert_key) AS UNSIGNED) AS id, updated_at AS created_at, 'health_alert_state' AS source, NULL AS event_id, NULL AS request_id, NULL AS trace_id, NULL AS operation_id, NULL AS parent_event_id,
            'SYS-HEALTH-ALERT' AS code, 'system' AS category, last_state AS severity, 'health-monitor' AS module,
            CONCAT('Health alert ', alert_key) AS message,
            JSON_OBJECT('alert_key', alert_key, 'last_sent_at', last_sent_at, 'occurrences', occurrences) AS meta_json,
            last_state AS result, 'health_alert_state' AS action_code, 'health_alert' AS target_type, alert_key AS target_id
        FROM logging_health_alerts WHERE updated_at >= ? AND updated_at < ?
        ORDER BY created_at ASC, source ASC, id ASC";
$stmt = db()->prepare($sql);
$stmt->execute([
    $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'),
    $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'),
    $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'),
    $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'),
    $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'),
    $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'),
    $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'),
    $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'),
]);

$keyId = substr(hash('sha256', $key), 0, 16);
$previousExportDate = '';
$previousExportHmac = str_repeat('0', 64);
if (!$lateMode) {
    $previousExportDate = $start->modify('-1 day')->format('Y-m-d');
    $previousManifestPath = $backupReal . DIRECTORY_SEPARATOR . 'logging-integrity-' . $previousExportDate . '.jsonl.manifest.json';
    if (is_file($previousManifestPath)) {
        $previousManifest = json_decode((string) @file_get_contents($previousManifestPath), true);
        $previousArchivePath = $backupReal . DIRECTORY_SEPARATOR . 'logging-integrity-' . $previousExportDate . '.jsonl';
        if (!is_array($previousManifest) || !is_file($previousArchivePath)
            || (string) ($previousManifest['date'] ?? '') !== $previousExportDate
            || !logging_integrity_verify_daily_manifest($previousArchivePath, $previousManifest, $key)) {
            fwrite(STDERR, "Previous integrity manifest/archive is invalid or missing; export chain cannot continue.\n"); exit(1);
        }
        $previousExportHmac = (string) $previousManifest['hmac_sha256'];
    } else {
        if (logging_integrity_has_older_snapshot($backupReal, $date)) {
            fwrite(STDERR, "Integrity export gap detected before {$date}; missing predecessor {$previousExportDate}.\n"); exit(1);
        }
        $previousExportDate = '';
    }
}
$tmpPath = $archivePath . '.' . bin2hex(random_bytes(5)) . '.tmp';
$out = @fopen($tmpPath, 'xb');
if ($out === false) { fwrite(STDERR, "Cannot create temporary integrity archive.\n"); exit(2); }
$previous = str_repeat('0', 64);
$count = 0;
$firstId = null;
$lastId = null;
try {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($lateMode && !logging_integrity_row_is_late($row, $lateIndex)) continue;
        $row['meta_json'] = isset($row['meta_json']) ? (string) $row['meta_json'] : null;
        $canonical = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        $hash = hash('sha256', $previous . "\n" . $canonical);
        $envelope = json_encode(['previous_hash'=>$previous, 'record_hash'=>$hash, 'record'=>$row], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        if (fwrite($out, $envelope) !== strlen($envelope)) throw new RuntimeException('short integrity archive write');
        $previous = $hash;
        $count++;
        $firstId ??= (string) $row['source'] . ':' . (string) $row['id'];
        $lastId = (string) $row['source'] . ':' . (string) $row['id'];
    }
    if (!fflush($out)) throw new RuntimeException('integrity archive flush failed');
    if (function_exists('fsync')) @fsync($out);
    fclose($out); $out = null;
    if ($lateMode && $count === 0) {
        @unlink($tmpPath);
        fwrite(STDOUT, "No late-arriving or changed records found; immutable daily snapshot and supplement chain remain unchanged.\n");
        exit(0);
    }
    if ($lateMode) {
        $baseHmac = (string) ($baseManifest['hmac_sha256'] ?? '');
        $baseFinal = (string) ($baseManifest['final_hash'] ?? '');
        $sequence = $supplementSequence + 1;
        $manifest = [
            'format' => 3, 'date' => $date, 'records' => $count,
            'first_record' => $firstId, 'last_record' => $lastId,
            'final_hash' => $previous, 'base_manifest_hmac' => $baseHmac,
            'base_final_hash' => $baseFinal, 'supplement_sequence' => $sequence,
            'prev_supplement_hmac' => $previousSupplementHmac, 'key_id' => $keyId,
            'hmac_sha256' => hash_hmac('sha256', $date . '|' . $count . '|' . $previous . '|' . $baseHmac . '|' . $baseFinal . '|' . $sequence . '|' . $previousSupplementHmac . '|' . $keyId, $key),
            'created_at' => date('c'),
        ];
    } else {
        $manifest = [
            'format' => 2, 'date' => $date, 'records' => $count,
            'first_record' => $firstId, 'last_record' => $lastId,
            'final_hash' => $previous,
            'prev_export_date' => $previousExportDate !== '' ? $previousExportDate : null,
            'prev_export_hmac' => $previousExportHmac,
            'key_id' => $keyId,
            'hmac_sha256' => hash_hmac('sha256', $date . '|' . $count . '|' . $previous . '|' . $previousExportHmac . '|' . $keyId, $key),
            'created_at' => date('c'),
        ];
    }
    $manifestTmp = $manifestPath . '.' . bin2hex(random_bytes(4)) . '.tmp';
    file_put_contents($manifestTmp, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), LOCK_EX);
    if (!@rename($tmpPath, $archivePath) || !@rename($manifestTmp, $manifestPath)) throw new RuntimeException('atomic integrity archive publish failed');
    @chmod($archivePath, 0600); @chmod($manifestPath, 0600);
    fwrite(STDOUT, sprintf("Integrity %s created: %d records | %s\n", $lateMode ? 'late-arrival supplement' : 'daily archive', $count, $archivePath));
} catch (Throwable $exception) {
    if (is_resource($out)) fclose($out);
    @unlink($tmpPath);
    logging_emergency_failure('logging_integrity_export_failed', get_class($exception));
    fwrite(STDERR, 'Integrity export failed: ' . get_class($exception) . PHP_EOL);
    exit(1);
}
