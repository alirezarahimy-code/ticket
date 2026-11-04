<?php
declare(strict_types=1);

if (!defined('APP_ROOT')) exit;

/** شمارندهٔ جانبی per-line برای JSON غیرقابل parse؛ چنین خطی امکان نگهداری attempts داخل JSON را ندارد. */
function logging_replay_poison_attempts(string $spoolDir, string $line, bool $increment = false, bool $clear = false): int
{
    $path = $spoolDir . DIRECTORY_SEPARATOR . '.poison-attempts.json';
    $lockPath = $path . '.lock';
    $lock = @fopen($lockPath, 'c');
    if ($lock === false || !@flock($lock, LOCK_EX)) {
        if (is_resource($lock)) fclose($lock);
        throw new RuntimeException('poison attempts lock unavailable');
    }
    try {
        $data = is_file($path) ? json_decode((string) @file_get_contents($path), true) : [];
        if (!is_array($data)) throw new RuntimeException('poison attempts state is corrupt');
        $key = hash('sha256', rtrim($line, "\r\n"));
        $attempts = max(0, (int) ($data[$key] ?? 0));
        if ($clear) unset($data[$key]);
        elseif ($increment) $data[$key] = ++$attempts;
        if ($clear || $increment) {
            $tmp = $spoolDir . DIRECTORY_SEPARATOR . 'poison-attempts-' . bin2hex(random_bytes(4)) . '.rewrite.tmp';
            $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $handle = @fopen($tmp, 'xb');
            if ($handle === false) throw new RuntimeException('poison attempts temp unavailable');
            try {
                if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) throw new RuntimeException('poison attempts write failed');
                if (function_exists('fsync')) @fsync($handle);
            } finally { fclose($handle); }
            @chmod($tmp, 0640);
            $published = function_exists('logging_atomic_replace_file')
                ? logging_atomic_replace_file($tmp, $path)
                : @rename($tmp, $path);
            if (!$published) { @unlink($tmp); throw new RuntimeException('poison attempts publish failed'); }
        }
        return $attempts;
    } finally {
        @flock($lock, LOCK_UN);
        fclose($lock);
    }
}

/**
 * پردازش صف JSONL با مصرف حافظهٔ محدود و بازنویسی اتمیک فایل.
 * فراخواننده قفل replay.lock را می‌گیرد؛ قفل .writer.lock فقط برای claim کوتاه هر فایل استفاده می‌شود.
 */
function logging_replay_process_directory(string $spoolDir, int $maxPerRun = 500): array
{
    $maxPerRun = max(1, min(5000, $maxPerRun));
    if (!is_dir($spoolDir)) return ['processed'=>0,'replayed'=>0,'failed'=>0,'remaining_files'=>0,'remaining_bytes'=>0];
    if (function_exists('logging_db_circuit_should_skip') && logging_db_circuit_should_skip()) return ['processed'=>0,'replayed'=>0,'failed'=>0,'remaining_files'=>count(glob($spoolDir . DIRECTORY_SEPARATOR . '*.jsonl') ?: []),'remaining_bytes'=>0,'db_circuit_open'=>true];
    $processed = 0; $replayed = 0; $failed = 0;
    $writerLockPath = $spoolDir . DIRECTORY_SEPARATOR . '.writer.lock';

    // Tempهای rewrite از اجرای crash شده قابل بازیابی نیستند؛ فایل .processing منبع اصلی باقی می‌ماند.
    foreach (glob($spoolDir . DIRECTORY_SEPARATOR . '*.rewrite.tmp') ?: [] as $staleTemp) @unlink($staleTemp);

    // Claim اتمیک فایل‌های عادی؛ قفل فقط هنگام rename گرفته می‌شود.
    foreach (glob($spoolDir . DIRECTORY_SEPARATOR . '*.jsonl') ?: [] as $file) {
        $lock = @fopen($writerLockPath, 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            if (is_resource($lock)) fclose($lock);
            $failed++;
            continue;
        }
        $processing = $file . '.processing';
        if (is_file($file) && !is_file($processing) && !@rename($file, $processing)) $failed++;
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    $processingFiles = glob($spoolDir . DIRECTORY_SEPARATOR . '*.jsonl.processing') ?: [];
    sort($processingFiles, SORT_STRING);
    foreach ($processingFiles as $processingFile) {
        if ($processed >= $maxPerRun) break;
        // نام فایل فقط telemetry است؛ attemptها برای هر رکورد جداگانه نگهداری می‌شوند.
        $deadLines = 0;
        $deadTemp = null;
        $deadOutput = null;
        $fileRetrySequence = 0;
        if (preg_match('/\.retry-(\d+)-/', basename($processingFile), $fileRetryMatch)) $fileRetrySequence = (int) $fileRetryMatch[1];
        $input = @fopen($processingFile, 'rb');
        if ($input === false) { $failed++; continue; }
        $temp = $processingFile . '.' . bin2hex(random_bytes(4)) . '.rewrite.tmp';
        $output = @fopen($temp, 'wb');
        if ($output === false) { fclose($input); $failed++; continue; }
        $pendingLines = 0;
        try {
            while (($line = fgets($input)) !== false) {
                if (trim($line) === '') continue;
                if ($processed >= $maxPerRun) {
                    if (fwrite($output, $line) !== strlen($line)) throw new RuntimeException('pending spool write failed');
                    $pendingLines++;
                    continue;
                }
                if (function_exists('logging_db_circuit_should_skip') && logging_db_circuit_should_skip()) {
                    if (fwrite($output, $line) !== strlen($line)) throw new RuntimeException('pending spool write failed');
                    $pendingLines++;
                    continue;
                }
                $processed++;
                $replayDbSuccess = true;
                $record = null;
                try {
                    $record = json_decode($line, true, 32, JSON_THROW_ON_ERROR);
                    if (!is_array($record) || (int) ($record['version'] ?? 0) !== 1 || !is_array($record['payload'] ?? null)) throw new UnexpectedValueException('invalid spool record');
                    $payload = $record['payload'];
                    $stream = (string) ($record['stream'] ?? '');
                    $eventId = (string) ($payload['event_id'] ?? '');
                    $requestId = (string) ($payload['request_id'] ?? '');
                    if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $eventId)
                        || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $requestId)) throw new UnexpectedValueException('invalid event/request id');
                    if ($stream === 'system') {
                        $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
                        $metaJson = json_encode(logging_sanitize_value($meta), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
                        $sql = 'INSERT INTO system_logs (event_id, request_id, trace_id, operation_id, parent_event_id, code, category, fingerprint, level, context, message, meta_json, user_id, request_uri, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                        $wasInserted = logging_replay_insert('system_logs', $sql, [
                            $eventId, $requestId, $payload['trace_id'] ?? $requestId, $payload['operation_id'] ?? $requestId, $payload['parent_event_id'] ?? null, $payload['code'] ?? null, $payload['category'] ?? 'system',
                            $payload['fingerprint'] ?? logging_fingerprint_current((string) ($payload['code'] ?? 'SYS-LEGACY-001'), (string) ($payload['context'] ?? 'system'), (string) ($payload['message'] ?? '')),
                            $payload['level'] ?? 'error', logging_utf8_substr((string) ($payload['context'] ?? 'system'), 0, 120),
                            logging_sanitize_text((string) ($payload['message'] ?? ''), 2000), $metaJson,
                            isset($payload['user_id']) ? (int) $payload['user_id'] : null,
                            isset($payload['request_uri']) ? substr((string) $payload['request_uri'], 0, 255) : null,
                            $payload['created_at'] ?? date('Y-m-d H:i:s'),
                        ], $eventId);
                        if ($wasInserted) {
                            $replayDbSuccess = logging_issue_upsert([
                                'event_id'=>$eventId, 'request_id'=>$requestId,
                                'code'=>(string) ($payload['code'] ?? 'SYS-LEGACY-001'),
                                'category'=>(string) ($payload['category'] ?? 'system'),
                                'level'=>(string) ($payload['level'] ?? 'error'),
                                'context'=>(string) ($payload['context'] ?? 'system'),
                                'message'=>(string) ($payload['message'] ?? 'رخداد فنی'),
                                'created_at'=>(string) ($payload['created_at'] ?? date('Y-m-d H:i:s')),
                            ], false);
                        }
                    } elseif ($stream === 'activity') {
                        $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
                        $metaJson = json_encode(logging_sanitize_value($meta), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
                        $sql = 'INSERT INTO activity_logs (event_id, request_id, trace_id, operation_id, parent_event_id, result, code, category, user_id, username, full_name, role, action_code, action_label, module, target_type, target_id, target_label, meta_json, ip_address, computer_name, hostname, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                        logging_replay_insert('activity_logs', $sql, [
                            $eventId, $requestId, $payload['trace_id'] ?? $requestId, $payload['operation_id'] ?? $requestId, $payload['parent_event_id'] ?? null, $payload['result'] ?? 'unknown', $payload['code'] ?? 'AUD-LEGACY-001', $payload['category'] ?? 'audit', $payload['user_id'] ?? null,
                            $payload['username'] ?? null, $payload['full_name'] ?? null, $payload['role'] ?? null,
                            $payload['action_code'] ?? 'unknown', $payload['action_label'] ?? null, $payload['module'] ?? null,
                            $payload['target_type'] ?? null, $payload['target_id'] ?? null, $payload['target_label'] ?? null,
                            $metaJson, $payload['ip_address'] ?? null, $payload['computer_name'] ?? null,
                            $payload['hostname'] ?? null, $payload['user_agent'] ?? null, $payload['created_at'] ?? date('Y-m-d H:i:s'),
                        ], $eventId);
                    } elseif ($stream === 'ticket_event') {
                        $detailsJson = logging_json(is_array($payload['details'] ?? null) ? $payload['details'] : []);
                        $sql = 'INSERT INTO ticket_events (event_id, request_id, trace_id, operation_id, parent_event_id, ticket_id, user_id, event_type, from_status, to_status, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                        logging_replay_insert('ticket_events', $sql, [
                            $eventId, $requestId, $payload['trace_id'] ?? $requestId, $payload['operation_id'] ?? $requestId,
                            $payload['parent_event_id'] ?? null, (int) ($payload['ticket_id'] ?? 0),
                            isset($payload['user_id']) ? (int) $payload['user_id'] : null,
                            (string) ($payload['event_type'] ?? 'unknown'), $payload['from_status'] ?? null,
                            $payload['to_status'] ?? null, $detailsJson,
                        ], $eventId);
                    } else {
                        throw new UnexpectedValueException('unknown spool stream');
                    }
                    if ($replayDbSuccess && function_exists('logging_db_circuit_success')) logging_db_circuit_success();
                    $replayed++;
                } catch (Throwable $lineError) {
                    $classification = function_exists('logging_replay_error_classify')
                        ? logging_replay_error_classify($lineError)
                        : (($lineError instanceof JsonException || $lineError instanceof UnexpectedValueException) ? 'malformed' : 'unknown');
                    if ($classification === 'transient' && function_exists('logging_db_circuit_failure')) logging_db_circuit_failure($lineError);
                    if ($classification === 'schema' && function_exists('logging_increment_counter')) logging_increment_counter('schema_replay_error_total');
                    if ($classification === 'unknown' && function_exists('logging_increment_counter')) logging_increment_counter('unknown_replay_error_total');

                    // attempts متعلق به هر رکورد معتبر است؛ نام فایل فقط برای JSON غیرقابل parse قدیمی fallback است.
                    $retryLine = $line;
                    $consumesAttempt = in_array($classification, ['malformed', 'permanent_data'], true);
                    if (is_array($record)) {
                        $attempts = max(0, (int) ($record['attempts'] ?? 0));
                    } elseif ($classification === 'malformed' && function_exists('logging_replay_poison_attempts')) {
                        $attempts = logging_replay_poison_attempts($spoolDir, $line, true);
                    } else {
                        $attempts = 0;
                    }
                    if ($consumesAttempt) {
                        if (is_array($record)) $attempts++;
                        if (is_array($record)) {
                            $record['attempts'] = $attempts;
                            $encodedRetry = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
                            if (is_string($encodedRetry)) $retryLine = $encodedRetry . (str_ends_with($line, "\n") ? "\n" : '');
                        }
                    }
                    $toDeadLetter = $consumesAttempt && $attempts >= 8;
                    if ($toDeadLetter) {
                        $deadDir = $spoolDir . DIRECTORY_SEPARATOR . 'dead-letter';
                        if (!is_dir($deadDir)) @mkdir($deadDir, 0750, true);
                        if (is_dir($deadDir)) {
                            if (!is_resource($deadOutput)) {
                                $deadTemp = $spoolDir . DIRECTORY_SEPARATOR . '.dead-' . bin2hex(random_bytes(6)) . '.tmp';
                                $deadOutput = @fopen($deadTemp, 'wb');
                            }
                            if (is_resource($deadOutput) && fwrite($deadOutput, $retryLine) === strlen($retryLine)) {
                                $deadLines++;
                                if (!is_array($record) && function_exists('logging_replay_poison_attempts')) logging_replay_poison_attempts($spoolDir, $line, false, true);
                            } else {
                                if (is_resource($deadOutput)) { fclose($deadOutput); $deadOutput = null; }
                                if (fwrite($output, $retryLine) !== strlen($retryLine)) throw new RuntimeException('pending spool write failed');
                                $pendingLines++;
                            }
                        } else {
                            if (fwrite($output, $retryLine) !== strlen($retryLine)) throw new RuntimeException('pending spool write failed');
                            $pendingLines++;
                        }
                    } else {
                        // transient/schema/unknown attempt را مصرف نمی‌کنند و همیشه در صف فعال می‌مانند.
                        if (fwrite($output, $retryLine) !== strlen($retryLine)) throw new RuntimeException('pending spool write failed');
                        $pendingLines++;
                    }
                    $failed++;
                }
            }
            if (!fflush($output)) throw new RuntimeException('pending spool flush failed');
            if (function_exists('fsync')) @fsync($output);
        } catch (Throwable $exception) {
            fclose($input); fclose($output); @unlink($temp); throw $exception;
        }
        fclose($input); fclose($output);
        if (is_resource($deadOutput)) {
            if (!fflush($deadOutput)) { fclose($deadOutput); @unlink((string) $deadTemp); throw new RuntimeException('dead letter flush failed'); }
            if (function_exists('fsync')) @fsync($deadOutput);
            fclose($deadOutput);
            $deadName = $spoolDir . DIRECTORY_SEPARATOR . 'dead-letter' . DIRECTORY_SEPARATOR . basename((string) $processingFile) . '.dead-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.jsonl';
            if (!@rename((string) $deadTemp, $deadName)) { @unlink((string) $deadTemp); throw new RuntimeException('dead letter publish failed'); }
        }

        if ($pendingLines === 0) {
            @unlink($processingFile); @unlink($temp);
        } else {
            // فقط خط‌های Poison پس از ۸ بار به DLQ رفتند؛ خط‌های معتبرِ دارای خطای DB همیشه در صف فعال می‌مانند.
            $nextAttempt = $fileRetrySequence + 1;
            $retryBase = preg_replace('/\.processing$/', '', basename($processingFile));
            $retryBase = preg_replace('/(?:\.retry-[0-9]+-.*)?\.jsonl$/', '', (string) $retryBase);
            $retryFile = $spoolDir . DIRECTORY_SEPARATOR . $retryBase . '.retry-' . $nextAttempt . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.jsonl';
            if (!@rename($temp, $retryFile)) {
                @unlink($temp); $failed++; continue; // فایل .processing کامل برای اجرای بعد باقی می‌ماند.
            }
            @unlink($processingFile);
        }
    }
    $remainingFiles = array_merge(
        glob($spoolDir . DIRECTORY_SEPARATOR . '*.jsonl') ?: [],
        glob($spoolDir . DIRECTORY_SEPARATOR . '*.jsonl.processing') ?: []
    );
    $remainingBytes = 0;
    foreach ($remainingFiles as $remainingFile) $remainingBytes += (int) @filesize($remainingFile);
    $deadFiles = glob($spoolDir . DIRECTORY_SEPARATOR . 'dead-letter' . DIRECTORY_SEPARATOR . '*.jsonl') ?: [];
    $deadBytes = 0;
    foreach ($deadFiles as $deadFile) $deadBytes += (int) @filesize($deadFile);
    if (function_exists('logging_spool_stats_cache_invalidate')) logging_spool_stats_cache_invalidate();
    return ['processed'=>$processed,'replayed'=>$replayed,'failed'=>$failed,'remaining_files'=>count($remainingFiles),'remaining_bytes'=>$remainingBytes,'dead_letter_files'=>count($deadFiles),'dead_letter_bytes'=>$deadBytes];
}
