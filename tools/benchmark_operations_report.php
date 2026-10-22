<?php
declare(strict_types=1);

/** Independent database-side validation for one isolated synthetic operations run. */
$root = dirname(__DIR__);
$config = require $root . '/config.php';
$db = $config['database'] ?? [];
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', (string) ($db['host'] ?? '127.0.0.1'), (int) ($db['port'] ?? 3306), (string) ($db['name'] ?? 'ticket_benchmark'));
$pdo = new PDO($dsn, (string) ($db['user'] ?? 'root'), (string) ($db['password'] ?? ''), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$token = strtoupper(preg_replace('/[^A-F0-9]/', '', (string) ($argv[1] ?? '')));
$since = (string) ($argv[2] ?? '1970-01-01 00:00:00');
if (strlen($token) !== 8 || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $since)) {
    throw new InvalidArgumentException('A run token and UTC start timestamp are required.');
}

$ticketPrefix = 'OPS-' . $token . '-%';
$countStmt = $pdo->prepare('SELECT status, COUNT(*) AS n FROM tickets WHERE subject LIKE ? GROUP BY status ORDER BY status');
$countStmt->execute([$ticketPrefix]);
$statusCounts = [];
foreach ($countStmt->fetchAll() as $row) {
    $statusCounts[(string) $row['status']] = (int) $row['n'];
}
$createdTickets = array_sum($statusCounts);
$resolvedTickets = $statusCounts['resolved'] ?? 0;
$idsStmt = $pdo->prepare('SELECT id FROM tickets WHERE subject LIKE ? ORDER BY id');
$idsStmt->execute([$ticketPrefix]);
$ticketIds = array_map('intval', $idsStmt->fetchAll(PDO::FETCH_COLUMN));
$messageCount = 0;
$eventCount = 0;
if ($ticketIds !== []) {
    $marks = implode(',', array_fill(0, count($ticketIds), '?'));
    $messages = $pdo->prepare('SELECT COUNT(*) FROM ticket_messages WHERE ticket_id IN (' . $marks . ')');
    $messages->execute($ticketIds);
    $messageCount = (int) $messages->fetchColumn();
    $events = $pdo->prepare('SELECT COUNT(*) FROM ticket_events WHERE ticket_id IN (' . $marks . ')');
    $events->execute($ticketIds);
    $eventCount = (int) $events->fetchColumn();
}

$mediaCounts = [];
foreach ($pdo->query('SELECT media, direction, COUNT(*) AS n FROM cd_dvd_records GROUP BY media, direction ORDER BY media, direction')->fetchAll() as $row) {
    $mediaCounts[(string) $row['media']][(string) $row['direction']] = (int) $row['n'];
}
$allMedia = $pdo->query('SELECT media, serial, direction, sort_key, id FROM cd_dvd_records ORDER BY media, serial, sort_key, id')->fetchAll();
$previous = [];
$repeated = [];
foreach ($allMedia as $row) {
    $key = (string) $row['media'] . ':' . (string) $row['serial'];
    if (isset($previous[$key]) && $previous[$key]['direction'] === (string) $row['direction']) {
        $repeated[] = [
            'media' => (string) $row['media'], 'serial' => (string) $row['serial'],
            'direction' => (string) $row['direction'], 'first_record_id' => $previous[$key]['id'],
            'repeat_record_id' => (int) $row['id'],
        ];
    }
    $previous[$key] = ['direction' => (string) $row['direction'], 'id' => (int) $row['id']];
}
$runMedia = $pdo->prepare('SELECT COUNT(*) FROM cd_dvd_records WHERE created_at >= ?');
$runMedia->execute([$since]);
$mediaRecordsForRun = (int) $runMedia->fetchColumn();

$errorStmt = $pdo->prepare("SELECT level, context, message, meta_json, request_uri, created_at FROM system_logs WHERE created_at >= ? AND level IN ('error','warning') ORDER BY id LIMIT 100");
$errorStmt->execute([$since]);
$systemErrors = [];
foreach ($errorStmt->fetchAll() as $row) {
    $message = (string) ($row['message'] ?? '');
    $context = (string) ($row['context'] ?? '');
    $expectedRejection = str_contains($message, 'این شماره از سازمان خارج شده است.')
        || str_contains($message, 'این رسانه در حال حاضر داخل سازمان ثبت شده است.');
    $slowQuery = $context === 'کندی کوئری';
    $meta = json_decode((string) ($row['meta_json'] ?? ''), true);
    $systemErrors[] = [
        'level' => (string) $row['level'], 'context' => $context,
        'expected_duplicate_state_rejection' => $expectedRejection, 'slow_query_warning' => $slowQuery,
        'slow_query_ms' => $slowQuery ? (int) ($meta['ms'] ?? 0) : null,
        'slow_query' => $slowQuery ? mb_substr((string) ($meta['technical'] ?? ''), 0, 300) : null,
        'message' => mb_substr($message, 0, 300),
        'request_uri' => (string) ($row['request_uri'] ?? ''), 'created_at' => (string) $row['created_at'],
    ];
}
$countsStmt = $pdo->prepare("SELECT COUNT(*) AS total,
    SUM(CASE WHEN message LIKE '%این شماره از سازمان خارج شده است.%' OR message LIKE '%این رسانه در حال حاضر داخل سازمان ثبت شده است.%' THEN 1 ELSE 0 END) AS expected_rejections,
    SUM(CASE WHEN context = 'کندی کوئری' THEN 1 ELSE 0 END) AS slow_queries
    FROM system_logs WHERE created_at >= ? AND level IN ('error','warning')");
$countsStmt->execute([$since]);
$systemLogCounts = $countsStmt->fetch() ?: ['total' => 0, 'expected_rejections' => 0, 'slow_queries' => 0];
$unexpectedSystemErrorCount = max(0, (int) $systemLogCounts['total'] - (int) $systemLogCounts['expected_rejections'] - (int) $systemLogCounts['slow_queries']);

$result = [
    'collection_status' => 'ok',
    'tickets' => [
        'created_for_run' => $createdTickets, 'resolved_for_run' => $resolvedTickets,
        'status_counts' => $statusCounts, 'message_rows' => $messageCount, 'event_rows' => $eventCount,
        'synthetic_ticket_ids_sample' => array_slice($ticketIds, 0, 20),
    ],
    'cd_dvd' => [
        'records_for_run' => $mediaRecordsForRun, 'all_records_in_disposable_database' => count($allMedia),
        'media_direction_counts' => $mediaCounts, 'consecutive_same_direction_pairs' => count($repeated),
        'same_direction_violation_samples' => array_slice($repeated, 0, 30),
    ],
    'application_system_errors_since_start' => (int) $systemLogCounts['total'],
    'expected_duplicate_state_rejections' => (int) $systemLogCounts['expected_rejections'],
    'slow_query_warning_count' => (int) $systemLogCounts['slow_queries'],
    'unexpected_application_system_error_count' => $unexpectedSystemErrorCount,
    'application_system_error_samples' => array_slice($systemErrors, 0, 100),
];
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
