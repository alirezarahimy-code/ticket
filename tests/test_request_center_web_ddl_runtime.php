<?php
declare(strict_types=1);

/**
 * T04 runtime diagnostic for a disposable CI database.
 *
 * - Starts index.php and request-center.php through PHP's HTTP server.
 * - Captures MariaDB general_log statements from a dedicated application DB user.
 * - Measures a healthy fresh-install request.
 * - Seeds an authenticated primary-admin session and proves the independent RC
 *   entry point fails closed without DDL when a required column is missing.
 * - Removes one nullable RC column, then proves whether the legacy index path
 *   performs DDL to heal the schema.
 * - Calls the exact ticket_service_ensure_schema() function through a CI-only
 *   probe endpoint and records DDL/DML side effects.
 *
 * This is diagnostic evidence, not a T04 pass: the policy forbids schema healing
 * in normal web requests. The database is the disposable CI service only.
 */

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$rootUser = (string) (getenv('CI_DB_USER') ?: 'root');
$rootPass = (string) (getenv('CI_DB_PASS') ?: '');
$webUser = 'rc_web_ci';
$webPass = 'rc-web-' . bin2hex(random_bytes(12));
$root = dirname(__DIR__);
$portHttp = 18765;
$address = '127.0.0.1:' . $portHttp;
$configPath = tempnam(sys_get_temp_dir(), 'rc-t04-config-');
$server = null;
$pipes = [];

function t04_check(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException('T04 diagnostic failed: ' . $message);
    }
    echo ($ok ? 'PASS ' : 'FAIL ') . $message . PHP_EOL;
}

function t04_reset_log(PDO $pdo): void
{
    $pdo->exec('SET GLOBAL general_log = OFF');
    $pdo->exec("SET GLOBAL log_output = 'TABLE'");
    $pdo->exec('TRUNCATE TABLE mysql.general_log');
    $pdo->exec('SET GLOBAL general_log = ON');
}

function t04_log_window(PDO $pdo, string $start, string $user): array
{
    $stmt = $pdo->prepare(
        "SELECT command_type, argument FROM mysql.general_log
         WHERE event_time >= ? AND user_host LIKE ?
           AND command_type IN ('Query', 'Execute')
         ORDER BY event_time"
    );
    $stmt->execute([$start, '%' . $user . '%']);
    $queries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ddl = [];
    $dml = [];
    foreach ($queries as $row) {
        $sql = trim((string) $row['argument']);
        if (preg_match('/^(CREATE|ALTER|DROP|RENAME|TRUNCATE)\b/i', $sql)) {
            $ddl[] = $sql;
        } elseif (preg_match('/^(INSERT|UPDATE|DELETE|REPLACE)\b/i', $sql)) {
            $dml[] = $sql;
        }
    }
    return ['queries' => $queries, 'ddl' => $ddl, 'dml' => $dml];
}

function t04_http(string $url, array $extraHeaders = []): array
{
    $extraHeaders[] = 'Connection: close';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 20,
            'ignore_errors' => true,
            'follow_location' => 0,
            'header' => implode("\r\n", $extraHeaders) . "\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];
    $first = (string) ($headers[0] ?? '');
    if (!preg_match('/\s([0-9]{3})\s/', $first, $match)) {
        throw new RuntimeException('No HTTP status from ' . $url . '; response=' . substr((string) $body, 0, 300));
    }
    return ['status' => (int) $match[1], 'body' => (string) $body, 'headers' => $headers];
}

function t04_wait_server(int $port): void
{
    for ($i = 0; $i < 60; $i++) {
        $socket = @stream_socket_client('tcp://127.0.0.1:' . $port, $errno, $errstr, 0.2);
        if (is_resource($socket)) {
            fclose($socket);
            return;
        }
        usleep(100000);
    }
    throw new RuntimeException('PHP built-in server did not start');
}

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
    $rootUser,
    $rootPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

try {
    $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
    if (stripos($version, 'MariaDB') === false) {
        throw new RuntimeException('Expected MariaDB, received ' . $version);
    }
    echo 'T04 database: ' . $version . PHP_EOL;

    $pdo->exec("DROP USER IF EXISTS '{$webUser}'@'%'");
    $pdo->exec("CREATE USER '{$webUser}'@'%' IDENTIFIED BY '{$webPass}'");
    $pdo->exec("GRANT ALL PRIVILEGES ON `{$name}`.* TO '{$webUser}'@'%'");

    $configArray = [
        'app' => [
            'name' => 'Request Center T04 CI',
            'timezone' => 'Asia/Tehran',
            'base_url' => 'http://' . $address,
            'key' => str_repeat('a', 64),
        ],
        'database' => [
            'host' => $host,
            'port' => (int) $port,
            'name' => $name,
            'user' => $webUser,
            'password' => $webPass,
            'charset' => 'utf8mb4',
        ],
        'ldap' => [
            'enabled' => false, 'host' => '', 'port' => 389, 'ssl' => false,
            'base_dn' => '', 'bind_dn' => '', 'bind_password' => '',
            'domain_suffix' => '', 'default_role' => 'user',
            'group_map' => ['agent' => [], 'manager' => [], 'supervisor' => [], 'admin' => []],
        ],
        'security' => [
            'session_idle_days' => 0, 'max_upload_mb' => 8,
            'allowed_uploads' => ['pdf', 'png', 'jpg'], 'inventory_token' => '',
        ],
        'food_ticket' => ['odbc_driver' => '', 'web_url' => '', 'sso_key' => ''],
    ];
    $config = "<?php\\nreturn " . var_export($configArray, true) . ";\\n";
    if (file_put_contents($configPath, $config) === false) {
        throw new RuntimeException('Could not write temporary CI config');
    }
    putenv('ITSM_CONFIG_PATH=' . $configPath);

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['file', '/dev/null', 'a'],
        2 => ['file', '/dev/null', 'a'],
    ];
    $serverEnv = getenv();
    if (!is_array($serverEnv)) {
        $serverEnv = [];
    }
    $serverEnv['ITSM_CONFIG_PATH'] = $configPath;
    $server = proc_open([PHP_BINARY, '-S', $address, '-t', $root], $descriptors, $pipes, $root, $serverEnv);
    if (!is_resource($server)) {
        throw new RuntimeException('Could not start PHP built-in server');
    }
    if (isset($pipes[0]) && is_resource($pipes[0])) {
        fclose($pipes[0]);
    }
    t04_wait_server($portHttp);

    // Healthy fresh-install schema: measure actual DDL in the normal index.php request.
    t04_reset_log($pdo);
    $start = (string) $pdo->query('SELECT NOW(6)')->fetchColumn();
    $response = t04_http('http://' . $address . '/index.php');
    t04_check($response['status'] >= 200 && $response['status'] < 400, 'index.php completed HTTP request (status ' . $response['status'] . ')');
    $healthy = t04_log_window($pdo, $start, $webUser);
    echo 'T04 healthy-schema HTTP request: DDL=' . count($healthy['ddl']) . ', DML=' . count($healthy['dml']) . PHP_EOL;
    foreach ($healthy['ddl'] as $sql) {
        echo 'T04 healthy DDL: ' . substr($sql, 0, 500) . PHP_EOL;
    }
    foreach ($healthy['dml'] as $sql) {
        echo 'T04 healthy DML: ' . substr($sql, 0, 300) . PHP_EOL;
    }

    // Seed a real app session for the independent Request Center entry point.
    $seedUser = $pdo->prepare(
        "INSERT INTO users (username, password_hash, full_name, is_primary_admin, role, is_active)
         VALUES ('rc_t04_probe', 'not-used', 'RC T04 Probe', 1, 'primary_admin', 1)
         ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), is_primary_admin = 1, role = 'primary_admin', is_active = 1"
    );
    $seedUser->execute();
    $userId = (int) $pdo->query("SELECT id FROM users WHERE username = 'rc_t04_probe' LIMIT 1")->fetchColumn();
    t04_check($userId > 0, 'created disposable primary-admin account for request-center.php probe');
    $seed = t04_http('http://' . $address . '/tests/fixtures/request-center-session-seed.php?user_id=' . rawurlencode((string) $userId));
    t04_check($seed['status'] === 200, 'session seed endpoint returned HTTP 200 (status ' . $seed['status'] . ')');
    $session = json_decode($seed['body'], true, 512, JSON_THROW_ON_ERROR);
    $cookie = (string) $session['cookie_name'] . '=' . (string) $session['session_id'];
    t04_check($cookie !== '=', 'created authenticated session for independent Request Center');

    // Healthy independent Request Center request: it must not issue DDL.
    t04_reset_log($pdo);
    $start = (string) $pdo->query('SELECT NOW(6)')->fetchColumn();
    $rcHealthy = t04_http('http://' . $address . '/request-center.php', ['Cookie: ' . $cookie]);
    $rcHealthyLog = t04_log_window($pdo, $start, $webUser);
    t04_check($rcHealthy['status'] === 200, 'independent request-center.php serves healthy schema (status ' . $rcHealthy['status'] . ')');
    t04_check(count($rcHealthyLog['ddl']) === 0, 'independent request-center.php executes zero DDL on healthy schema');

    // A required column is removed on the disposable DB. The independent RC entry point must fail closed.
    $pdo->exec('SET GLOBAL general_log = OFF');
    $pdo->exec('ALTER TABLE tickets DROP COLUMN requesting_unit_id');
    t04_reset_log($pdo);
    $start = (string) $pdo->query('SELECT NOW(6)')->fetchColumn();
    $rcIncomplete = t04_http('http://' . $address . '/request-center.php', ['Cookie: ' . $cookie]);
    $rcIncompleteLog = t04_log_window($pdo, $start, $webUser);
    t04_check($rcIncomplete['status'] === 503, 'independent request-center.php fails closed on missing tickets.requesting_unit_id (status ' . $rcIncomplete['status'] . ')');
    t04_check(count($rcIncompleteLog['ddl']) === 0, 'independent request-center.php executes zero DDL on incomplete schema');
    $column = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets' AND COLUMN_NAME = 'requesting_unit_id'"
    );
    $column->execute();
    t04_check((int) $column->fetchColumn() === 0, 'independent Request Center leaves missing requesting_unit_id untouched');

    // Legacy index.php is still allowed to heal the same incomplete schema during an HTTP request.
    t04_reset_log($pdo);
    $start = (string) $pdo->query('SELECT NOW(6)')->fetchColumn();
    $driftResponse = t04_http('http://' . $address . '/index.php');
    t04_check($driftResponse['status'] >= 200 && $driftResponse['status'] < 400, 'index.php handled incomplete-schema HTTP request (status ' . $driftResponse['status'] . ')');
    $drift = t04_log_window($pdo, $start, $webUser);
    $column->execute();
    $restored = (int) $column->fetchColumn() === 1;
    t04_check($restored, 'legacy index.php restored the missing tickets.requesting_unit_id column');
    t04_check(count($drift['ddl']) >= 1, 'legacy index.php executed DDL when the schema was incomplete');
    foreach ($drift['ddl'] as $sql) {
        echo 'T04 drift DDL: ' . substr($sql, 0, 500) . PHP_EOL;
    }
    // The dropped column has a supporting index in the fresh schema; restore it
    // after the observed web self-heal so the disposable DB is structurally tidy.
    $pdo->exec('SET GLOBAL general_log = OFF');
    $indexCount = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tickets'
           AND INDEX_NAME = 'idx_tickets_requesting_unit'"
    )->fetchColumn();
    if ($indexCount === 0) {
        $pdo->exec('CREATE INDEX idx_tickets_requesting_unit ON tickets (requesting_unit_id)');
    }

    // Exercise the service-catalog schema helper through an HTTP request and record its data side effects.
    t04_reset_log($pdo);
    $start = (string) $pdo->query('SELECT NOW(6)')->fetchColumn();
    $probe = t04_http('http://' . $address . '/tests/fixtures/request-center-ddl-probe.php');
    t04_check($probe['status'] === 200 && str_contains($probe['body'], 'probe ok'), 'ticket_service_ensure_schema probe endpoint completed');
    $service = t04_log_window($pdo, $start, $webUser);
    echo 'T04 ticket_service_ensure_schema HTTP probe: DDL=' . count($service['ddl']) . ', DML=' . count($service['dml']) . PHP_EOL;
    foreach ($service['ddl'] as $sql) {
        echo 'T04 service DDL: ' . substr($sql, 0, 500) . PHP_EOL;
    }
    foreach ($service['dml'] as $sql) {
        echo 'T04 service DML: ' . substr($sql, 0, 300) . PHP_EOL;
    }

    echo "T04 DIAGNOSTIC COMPLETE: this test measures legacy schema healing; it does not certify the zero-DDL policy.\n";
} finally {
    $pdo->exec('SET GLOBAL general_log = OFF');
    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
    }
    $pdo->exec("DROP USER IF EXISTS '{$webUser}'@'%'");
    if (is_file($configPath)) {
        @unlink($configPath);
    }
}
