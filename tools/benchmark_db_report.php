<?php
declare(strict_types=1);

/** CI-only collector for application-level SQL warnings; uses synthetic benchmark DB. */
$root = dirname(__DIR__);
$config = require $root . '/config.php';
$db = $config['database'] ?? [];
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', (string) ($db['host'] ?? '127.0.0.1'), (int) ($db['port'] ?? 3306), (string) ($db['name'] ?? 'ticket_benchmark'));
$pdo = new PDO($dsn, (string) ($db['user'] ?? 'root'), (string) ($db['password'] ?? ''), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

$result = [
    'source' => 'system_logs records emitted by AppPDO/AppSlowStatement',
    'coverage' => 'Only queries whose execute()/query() call took at least 500 ms are logged by the application; this is not a full per-query histogram.',
    'slow_query_threshold_ms' => 500,
    'slow_queries' => [],
    'application_error_count' => 0,
    'application_error_samples' => [],
];

$stmt = $pdo->query("SELECT level, context, message, meta_json, request_uri, created_at FROM system_logs ORDER BY id ASC");
foreach ($stmt->fetchAll() as $row) {
    $meta = json_decode((string) ($row['meta_json'] ?? ''), true);
    if (($row['context'] ?? '') === 'کندی کوئری') {
        $result['slow_queries'][] = [
            'ms' => (int) ($meta['ms'] ?? 0),
            'query' => (string) ($meta['technical'] ?? ''),
            'request_uri' => (string) ($row['request_uri'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }
    if (($row['level'] ?? '') === 'error') {
        $result['application_error_count']++;
        if (count($result['application_error_samples']) < 30) {
            $result['application_error_samples'][] = [
                'context' => (string) ($row['context'] ?? ''),
                'message' => mb_substr((string) ($row['message'] ?? ''), 0, 240),
                'request_uri' => (string) ($row['request_uri'] ?? ''),
            ];
        }
    }
}

$times = array_values(array_filter(array_map(static fn (array $row): int => (int) $row['ms'], $result['slow_queries']), static fn (int $ms): bool => $ms > 0));
sort($times);
$count = count($times);
$result['slow_query_summary'] = [
    'count_at_or_above_threshold' => $count,
    'max_ms' => $count ? max($times) : 0,
    'p50_ms_of_logged_slow_queries' => $count ? $times[(int) floor(($count - 1) * 0.50)] : 0,
    'p95_ms_of_logged_slow_queries' => $count ? $times[(int) floor(($count - 1) * 0.95)] : 0,
];
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
