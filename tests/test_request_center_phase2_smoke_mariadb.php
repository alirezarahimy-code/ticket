<?php
declare(strict_types=1);

/**
 * Phase-2 smoke test — runs the RC code paths that earlier Phase-2 cuts
 * extracted from index.php into modules/request-center/lib/*.php, through a
 * real HTTP round trip on a disposable CI database:
 *
 *  1. GET index.php?page=dashboard            → 200, contains the dashboard heading (exercises request_center_dashboard_rows()).
 *  2. GET index.php?page=search&q=...         → 200, contains the search results marker (exercises request_center_global_search()).
 *  3. GET index.php?action=export_report      → 200, body starts with PK\x03\x04 (exercises request_center_export_report()).
 *  4. GET index.php?action=download_attachment&id=99999 → 404 (exercises request_center_download_attachment() permission/404 path).
 *
 * php -l alone cannot catch "moved a function but broke its call"; this test does.
 * The database is the disposable CI service only. It does not certify install-time guarantees.
 */

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$rootUser = (string) (getenv('CI_DB_USER') ?: 'root');
$rootPass = (string) (getenv('CI_DB_PASS') ?: '');
$webUser = 'rc_smoke_ci';
$webPass = 'rc-smoke-' . bin2hex(random_bytes(12));
$root = dirname(__DIR__);
$portHttp = 18766;
$address = '127.0.0.1:' . $portHttp;
$configPath = tempnam(sys_get_temp_dir(), 'rc-smoke-config-');
$server = null;
$pipes = [];

function smoke_check(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException('Phase-2 smoke failed: ' . $message);
    }
    echo ($ok ? 'PASS ' : 'FAIL ') . $message . PHP_EOL;
}

function smoke_wait_server(int $port): void
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

function smoke_http(string $url, array $extraHeaders = []): array
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
    echo 'Phase-2 smoke on database: ' . $version . PHP_EOL;

    $pdo->exec("DROP USER IF EXISTS '{$webUser}'@'%'");
    $pdo->exec("CREATE USER '{$webUser}'@'%' IDENTIFIED BY '{$webPass}'");
    $pdo->exec("GRANT ALL PRIVILEGES ON `{$name}`.* TO '{$webUser}'@'%'");

    $configArray = [
        'app' => [
            'name' => 'Request Center Phase-2 Smoke',
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
    $config = "<?php\nreturn " . var_export($configArray, true) . ";\n";
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
    smoke_wait_server($portHttp);

    $seedUser = $pdo->prepare(
        "INSERT INTO users (username, password_hash, full_name, is_primary_admin, role, is_active)
         VALUES ('rc_smoke_probe', 'not-used', 'RC Smoke Probe', 1, 'primary_admin', 1)
         ON DUPLICATE KEY UPDATE is_primary_admin = 1, role = 'primary_admin', is_active = 1"
    );
    $seedUser->execute();
    $userId = (int) $pdo->query("SELECT id FROM users WHERE username = 'rc_smoke_probe' LIMIT 1")->fetchColumn();
    smoke_check($userId > 0, 'seeded disposable primary-admin account');
    $seed = smoke_http('http://' . $address . '/tests/fixtures/request-center-session-seed.php?user_id=' . rawurlencode((string) $userId));
    smoke_check($seed['status'] === 200, 'session seed endpoint returned HTTP 200 (status ' . $seed['status'] . ')');
    $session = json_decode($seed['body'], true);
    if (!is_array($session) || json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('session seed body is not JSON (status ' . $seed['status'] . '): ' . substr((string) $seed['body'], 0, 300));
    }
    $cookie = (string) $session['cookie_name'] . '=' . (string) $session['session_id'];

    // 1) Dashboard (exercises request_center_dashboard_rows + the shared shell render_header/render_footer).
    $dashboard = smoke_http('http://' . $address . '/index.php?page=dashboard', ['Cookie: ' . $cookie]);
    smoke_check($dashboard['status'] === 200, 'dashboard returns HTTP 200 (status ' . $dashboard['status'] . ')');
    smoke_check(str_contains($dashboard['body'], 'داشبورد'), 'dashboard renders the dashboard page heading');

    // 2) Global search (exercises request_center_global_search).
    $search = smoke_http('http://' . $address . '/index.php?page=search&q=smoke', ['Cookie: ' . $cookie]);
    smoke_check($search['status'] === 200, 'global search returns HTTP 200 (status ' . $search['status'] . ')');
    smoke_check(str_contains($search['body'], 'درخواست'), 'global search page renders (ticket slice executed)');

    // 3) Excel export (exercises request_center_export_report). Export permission reports.view is implicit for primary_admin.
    $export = smoke_http('http://' . $address . '/index.php?action=export_report&from=' . urlencode('1405/01/01') . '&to=' . urlencode('1405/01/30'), ['Cookie: ' . $cookie]);
    smoke_check($export['status'] === 200, 'export_report returns HTTP 200 (status ' . $export['status'] . ')');
    smoke_check(str_starts_with($export['body'], 'PK' . "\x03\x04"), 'export_report returns a valid XLSX package (PK magic)');

    // 4) Attachment download with an unknown id (exercises request_center_download_attachment tail path).
    $download = smoke_http('http://' . $address . '/index.php?action=download_attachment&id=99999', ['Cookie: ' . $cookie]);
    smoke_check($download['status'] === 404, 'download_attachment with unknown id returns HTTP 404 (status ' . $download['status'] . ')');

    echo "Phase-2 smoke: ALL ASSERTIONS PASS\n";
} finally {
    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
    }
    $pdo->exec("DROP USER IF EXISTS '{$webUser}'@'%'");
    if (is_file($configPath)) {
        @unlink($configPath);
    }
}
