<?php
declare(strict_types=1);

/** HMAC input for legacy daily snapshots (format 1/2) and late-arrival supplements (format 3). */
function logging_integrity_manifest_hmac(array $manifest, string $key): ?string
{
    $format = (int) ($manifest['format'] ?? 0);
    $date = (string) ($manifest['date'] ?? '');
    $records = (int) ($manifest['records'] ?? -1);
    $finalHash = (string) ($manifest['final_hash'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) || $records < 0 || !preg_match('/^[a-f0-9]{64}$/i', $finalHash)) return null;
    if ($format === 1) return hash_hmac('sha256', $date . '|' . $records . '|' . $finalHash, $key);
    if ($format === 2) {
        $prevHmac = (string) ($manifest['prev_export_hmac'] ?? '');
        $keyId = (string) ($manifest['key_id'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/i', $prevHmac) || !preg_match('/^[a-f0-9]{16}$/i', $keyId)) return null;
        return hash_hmac('sha256', $date . '|' . $records . '|' . $finalHash . '|' . $prevHmac . '|' . $keyId, $key);
    }
    if ($format === 3) {
        $baseHmac = (string) ($manifest['base_manifest_hmac'] ?? '');
        $baseFinal = (string) ($manifest['base_final_hash'] ?? '');
        $sequence = (int) ($manifest['supplement_sequence'] ?? 0);
        $previousSupplement = (string) ($manifest['prev_supplement_hmac'] ?? '');
        $keyId = (string) ($manifest['key_id'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/i', $baseHmac) || !preg_match('/^[a-f0-9]{64}$/i', $baseFinal)
            || $sequence < 1 || !preg_match('/^[a-f0-9]{64}$/i', $previousSupplement)
            || !preg_match('/^[a-f0-9]{16}$/i', $keyId)) return null;
        return hash_hmac('sha256', $date . '|' . $records . '|' . $finalHash . '|' . $baseHmac . '|' . $baseFinal . '|' . $sequence . '|' . $previousSupplement . '|' . $keyId, $key);
    }
    return null;
}

function logging_integrity_verify_archive_file(string $archive, array $manifest): bool
{
    if (!is_file($archive)) return false;
    $previous = str_repeat('0', 64); $count = 0;
    $handle = @fopen($archive, 'rb');
    if ($handle === false) return false;
    try {
        while (($line = fgets($handle)) !== false) {
            $item = json_decode($line, true);
            if (!is_array($item) || !is_array($item['record'] ?? null) || !hash_equals($previous, (string) ($item['previous_hash'] ?? ''))) return false;
            $canonical = json_encode($item['record'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
            $expected = hash('sha256', $previous . "\n" . $canonical);
            if (!hash_equals($expected, (string) ($item['record_hash'] ?? ''))) return false;
            $previous = $expected; $count++;
        }
    } catch (Throwable) {
        return false;
    } finally {
        fclose($handle);
    }
    return $count === (int) ($manifest['records'] ?? -1)
        && hash_equals((string) ($manifest['final_hash'] ?? ''), $previous);
}

function logging_integrity_has_older_snapshot(string $directory, string $date): bool
{
    foreach (glob($directory . DIRECTORY_SEPARATOR . 'logging-integrity-*.jsonl*') ?: [] as $candidatePath) {
        if (!preg_match('/logging-integrity-(\d{4}-\d{2}-\d{2})\.jsonl(?:\.manifest\.json)?$/', basename($candidatePath), $matches)) continue;
        if ($matches[1] < $date) return true;
    }
    return false;
}

function logging_integrity_verify_predecessor_chain(array $manifest, string $directory, string $key, string $keyId): ?string
{
    $cursor = $manifest;
    for ($depth = 0; $depth < 10000; $depth++) {
        $format = (int) ($cursor['format'] ?? 0);
        if ($format === 1) {
            if (logging_integrity_has_older_snapshot($directory, (string) ($cursor['date'] ?? ''))) return 'Earlier export exists before legacy chain root; gap detected.';
            return null;
        }
        if ($format !== 2) return 'Unsupported predecessor manifest format.';
        if (!hash_equals($keyId, (string) ($cursor['key_id'] ?? ''))) return 'Integrity key rotation boundary mismatch.';
        $previousDate = $cursor['prev_export_date'] ?? null;
        $previousHmac = (string) ($cursor['prev_export_hmac'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/i', $previousHmac)) return 'Invalid previous export HMAC.';
        if ($previousDate === null) {
            if (!hash_equals(str_repeat('0', 64), $previousHmac)) return 'Invalid genesis chain marker.';
            if (logging_integrity_has_older_snapshot($directory, (string) ($cursor['date'] ?? ''))) return 'Earlier export exists but this manifest claims genesis; chain gap detected.';
            return null;
        }
        $expectedPreviousDate = (new DateTimeImmutable((string) ($cursor['date'] ?? '')))->modify('-1 day')->format('Y-m-d');
        if ((string) $previousDate !== $expectedPreviousDate) return 'Previous export date is not contiguous.';
        $previousArchive = $directory . DIRECTORY_SEPARATOR . 'logging-integrity-' . $expectedPreviousDate . '.jsonl';
        $previousManifestPath = $previousArchive . '.manifest.json';
        if (!is_file($previousArchive) || !is_file($previousManifestPath)) return 'Previous export is missing; chain gap detected.';
        $previousManifest = json_decode((string) @file_get_contents($previousManifestPath), true);
        if (!is_array($previousManifest) || (string) ($previousManifest['date'] ?? '') !== $expectedPreviousDate) return 'Previous export manifest is invalid.';
        if (!hash_equals($previousHmac, (string) ($previousManifest['hmac_sha256'] ?? ''))) return 'Previous export HMAC does not match.';
        $expectedManifestHmac = logging_integrity_manifest_hmac($previousManifest, $key);
        if ($expectedManifestHmac === null || !hash_equals((string) ($previousManifest['hmac_sha256'] ?? ''), $expectedManifestHmac)) return 'Previous manifest authentication failed.';
        if (!logging_integrity_verify_archive_file($previousArchive, $previousManifest)) return 'Previous archive record chain or final hash is invalid.';
        $cursor = $previousManifest;
    }
    return 'Integrity chain exceeds the maximum verification depth.';
}

function logging_integrity_verify_daily_manifest_error(string $archive, array $manifest, string $key): ?string
{
    if (!in_array((int) ($manifest['format'] ?? 0), [1, 2], true)) return 'Unsupported daily manifest format.';
    if (!logging_integrity_verify_archive_file($archive, $manifest)) return 'Daily archive record chain, count or final hash is invalid.';
    $expected = logging_integrity_manifest_hmac($manifest, $key);
    if ($expected === null || !hash_equals((string) ($manifest['hmac_sha256'] ?? ''), $expected)) return 'Daily integrity HMAC verification failed.';
    if ((int) ($manifest['format'] ?? 0) === 2) {
        $keyId = substr(hash('sha256', $key), 0, 16);
        if (!hash_equals($keyId, (string) ($manifest['key_id'] ?? ''))) return 'Integrity key ID mismatch.';
        $chainError = logging_integrity_verify_predecessor_chain($manifest, dirname($archive), $key, $keyId);
        if ($chainError !== null) return $chainError;
    }
    return null;
}

function logging_integrity_verify_daily_manifest(string $archive, array $manifest, string $key): bool
{
    return logging_integrity_verify_daily_manifest_error($archive, $manifest, $key) === null;
}

/** Read a verified archive's append-only watermarks and hashes for mutable state rows. */
function logging_integrity_read_archive_index(string $archive): array
{
    // Exact identities are required: auto-increment IDs can commit out of order.
    // A max-ID watermark alone would miss a lower ID committed after the snapshot.
    $index = ['max_ids' => [], 'ids' => [], 'mutable_hashes' => [], 'event_marker_max_created_at' => '', 'event_ids_at_max_created_at' => [], 'event_ids' => []];
    $appendOnly = ['system', 'activity', 'ticket', 'issue_history', 'issue_notification'];
    $mutable = ['issue_state', 'health_alert_state'];
    $handle = @fopen($archive, 'rb');
    if ($handle === false) throw new RuntimeException('Cannot read integrity archive index.');
    try {
        while (($line = fgets($handle)) !== false) {
            $item = json_decode($line, true);
            $record = is_array($item) ? ($item['record'] ?? null) : null;
            if (!is_array($record)) throw new RuntimeException('Malformed integrity archive while indexing.');
            $source = (string) ($record['source'] ?? '');
            $id = (string) ($record['id'] ?? '');
            if (in_array($source, $appendOnly, true) && ctype_digit($id)) {
                $index['max_ids'][$source] = max((int) ($index['max_ids'][$source] ?? 0), (int) $id);
                $index['ids'][$source][$id] = true;
            } elseif ($source === 'issue_event_marker') {
                $eventId = (string) ($record['event_id'] ?? '');
                if ($eventId !== '') $index['event_ids'][$eventId] = true;
                $createdAt = (string) ($record['created_at'] ?? '');
                if ($createdAt > (string) $index['event_marker_max_created_at']) {
                    $index['event_marker_max_created_at'] = $createdAt;
                    $index['event_ids_at_max_created_at'] = [];
                }
                if ($eventId !== '' && $createdAt === (string) $index['event_marker_max_created_at']) $index['event_ids_at_max_created_at'][$eventId] = true;
            } elseif (in_array($source, $mutable, true)) {
                $canonical = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
                $index['mutable_hashes'][$source . ':' . $id . ':' . hash('sha256', $canonical)] = true;
            }
        }
    } finally {
        fclose($handle);
    }
    return $index;
}

function logging_integrity_merge_archive_indexes(array $target, array $source): array
{
    foreach ((array) ($source['max_ids'] ?? []) as $stream => $id) $target['max_ids'][$stream] = max((int) ($target['max_ids'][$stream] ?? 0), (int) $id);
    foreach ((array) ($source['ids'] ?? []) as $stream => $ids) {
        foreach ((array) $ids as $id => $_) $target['ids'][$stream][(string) $id] = true;
    }
    foreach ((array) ($source['event_ids'] ?? []) as $eventId => $_) $target['event_ids'][(string) $eventId] = true;
    foreach ((array) ($source['mutable_hashes'] ?? []) as $key => $_) $target['mutable_hashes'][$key] = true;
    $incomingMarkerTime = (string) ($source['event_marker_max_created_at'] ?? '');
    $currentMarkerTime = (string) ($target['event_marker_max_created_at'] ?? '');
    if ($incomingMarkerTime > $currentMarkerTime) {
        $target['event_marker_max_created_at'] = $incomingMarkerTime;
        $target['event_ids_at_max_created_at'] = (array) ($source['event_ids_at_max_created_at'] ?? []);
    } elseif ($incomingMarkerTime !== '' && $incomingMarkerTime === $currentMarkerTime) {
        foreach ((array) ($source['event_ids_at_max_created_at'] ?? []) as $key => $_) $target['event_ids_at_max_created_at'][$key] = true;
    }
    return $target;
}

function logging_integrity_row_is_late(array $row, array $index): bool
{
    $source = (string) ($row['source'] ?? '');
    $id = (string) ($row['id'] ?? '');
    if (in_array($source, ['system', 'activity', 'ticket', 'issue_history', 'issue_notification'], true) && ctype_digit($id)) {
        // Do not compare only against max_ids: a lower auto-increment ID may commit late.
        return !isset($index['ids'][$source][$id]);
    }
    if ($source === 'issue_event_marker') {
        $eventId = (string) ($row['event_id'] ?? '');
        return $eventId !== '' && !isset($index['event_ids'][$eventId]);
    }
    if (in_array($source, ['issue_state', 'health_alert_state'], true)) {
        $canonical = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        return !isset($index['mutable_hashes'][$source . ':' . $id . ':' . hash('sha256', $canonical)]);
    }
    $canonical = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    return !isset($index['mutable_hashes'][$source . ':' . $id . ':' . hash('sha256', $canonical)]);
}

/** Validate every supplement from sequence 1 through the requested archive; missing/tampered links fail. */
function logging_integrity_verify_supplement_chain(string $targetArchive, string $key): ?string
{
    if (!is_file($targetArchive) || !preg_match('/^logging-integrity-(\d{4}-\d{2}-\d{2})\.late-.+\.jsonl$/', basename($targetArchive), $matches)) return 'Invalid late-arrival archive name.';
    $date = $matches[1];
    $directory = dirname($targetArchive);
    $baseArchive = $directory . DIRECTORY_SEPARATOR . 'logging-integrity-' . $date . '.jsonl';
    $baseManifestPath = $baseArchive . '.manifest.json';
    $baseManifest = is_file($baseManifestPath) ? json_decode((string) @file_get_contents($baseManifestPath), true) : null;
    if (!is_array($baseManifest) || !logging_integrity_verify_daily_manifest($baseArchive, $baseManifest, $key)) return 'Base daily snapshot is missing or invalid.';
    $keyId = substr(hash('sha256', $key), 0, 16);
    $baseHmac = (string) ($baseManifest['hmac_sha256'] ?? '');
    $baseFinal = (string) ($baseManifest['final_hash'] ?? '');
    $supplements = glob($directory . DIRECTORY_SEPARATOR . 'logging-integrity-' . $date . '.late-*.jsonl') ?: [];
    sort($supplements, SORT_STRING);
    $previousHmac = str_repeat('0', 64);
    $sequence = 0;
    $foundTarget = false;
    foreach ($supplements as $supplement) {
        $manifestPath = $supplement . '.manifest.json';
        $manifest = is_file($manifestPath) ? json_decode((string) @file_get_contents($manifestPath), true) : null;
        $sequence++;
        if (!is_array($manifest) || (int) ($manifest['format'] ?? 0) !== 3
            || (string) ($manifest['date'] ?? '') !== $date
            || (int) ($manifest['supplement_sequence'] ?? 0) !== $sequence
            || !hash_equals($previousHmac, (string) ($manifest['prev_supplement_hmac'] ?? ''))
            || !hash_equals($baseHmac, (string) ($manifest['base_manifest_hmac'] ?? ''))
            || !hash_equals($baseFinal, (string) ($manifest['base_final_hash'] ?? ''))
            || !hash_equals($keyId, (string) ($manifest['key_id'] ?? ''))
            || !logging_integrity_verify_archive_file($supplement, $manifest)) return 'Late-arrival supplement chain metadata or record chain is invalid.';
        $expected = logging_integrity_manifest_hmac($manifest, $key);
        if ($expected === null || !hash_equals((string) ($manifest['hmac_sha256'] ?? ''), $expected)) return 'Late-arrival supplement HMAC is invalid.';
        $previousHmac = (string) $manifest['hmac_sha256'];
        if (realpath($supplement) === realpath($targetArchive)) $foundTarget = true;
    }
    if (!$foundTarget) return 'Requested supplement is not part of the verified supplement chain.';
    return null;
}
