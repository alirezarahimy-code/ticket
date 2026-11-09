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
$serverLogPath = tempnam(sys_get_temp_dir(), 'rc-smoke-server-');
$server = null;
$pipes = [];

function smoke_check(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException('Phase-2 smoke failed: ' . $message);
    }
    echo ($ok ? 'PASS ' : 'FAIL ') . $message . PHP_EOL;
}

function smoke_http_post(string $url, array $form, array $headers = []): array
{
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    $headers[] = 'Connection: close';
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'timeout' => 20,
            'ignore_errors' => true,
            'follow_location' => 0,
            'header' => implode("\r\n", $headers) . "\r\n",
            'content' => http_build_query($form),
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    $headersOut = $http_response_header ?? [];
    $first = (string) ($headersOut[0] ?? '');
    if (!preg_match('/\s([0-9]{3})\s/', $first, $match)) {
        throw new RuntimeException('No HTTP status from POST ' . $url . '; body=' . substr((string) $body, 0, 300));
    }
    $location = '';
    foreach ($headersOut as $h) {
        if (stripos((string) $h, 'location:') === 0) { $location = trim(substr((string) $h, 9)); }
    }
    return ['status' => (int) $match[1], 'body' => (string) $body, 'headers' => $headersOut, 'location' => $location];
}

function smoke_http_multipart_post(string $url, array $form, string $fileField, string $filePath, string $filename, string $mime, array $headers = []): array
{
    $boundary = '---------------------------' . bin2hex(random_bytes(12));
    $body = '';
    foreach ($form as $name => $value) {
        $body .= '--' . $boundary . "\r\n";
        $body .= 'Content-Disposition: form-data; name="' . addcslashes((string) $name, "\\\"") . '"' . "\r\n\r\n";
        $body .= (string) $value . "\r\n";
    }
    $contents = file_get_contents($filePath);
    if ($contents === false) {
        throw new RuntimeException('Could not read multipart smoke upload.');
    }
    $body .= '--' . $boundary . "\r\n";
    $body .= 'Content-Disposition: form-data; name="' . addcslashes($fileField, "\\\"") . '"; filename="' . addcslashes($filename, "\\\"") . '"' . "\r\n";
    $body .= 'Content-Type: ' . $mime . "\r\n\r\n" . $contents . "\r\n";
    $body .= '--' . $boundary . "--\r\n";
    $headers[] = 'Content-Type: multipart/form-data; boundary=' . $boundary;
    $headers[] = 'Content-Length: ' . strlen($body);
    $headers[] = 'Connection: close';
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'timeout' => 20,
            'ignore_errors' => true,
            'follow_location' => 0,
            'header' => implode("\r\n", $headers) . "\r\n",
            'content' => $body,
        ],
    ]);
    $responseBody = @file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];
    $first = (string) ($responseHeaders[0] ?? '');
    if (!preg_match('/\\s([0-9]{3})\\s/', $first, $match)) {
        throw new RuntimeException('No HTTP status from multipart POST ' . $url . '; body=' . substr((string) $responseBody, 0, 300));
    }
    $location = '';
    foreach ($responseHeaders as $header) {
        if (stripos((string) $header, 'location:') === 0) {
            $location = trim(substr((string) $header, 9));
        }
    }
    return ['status' => (int) $match[1], 'body' => (string) $responseBody, 'headers' => $responseHeaders, 'location' => $location];
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
        2 => ['file', $serverLogPath, 'a'],
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

    // Deterministic RC reference rows for the create_ticket round trip.
    // NOTE: contract CI preflight runs BEFORE this smoke test and already owns service_catalog id=1 ('RC Support', service_group 'it').
    // INSERT IGNORE with hard-coded id=1 was therefore silently dropped; use captured ids instead — provider independent of CI seed order.
    $unitInsert = $pdo->prepare("INSERT IGNORE INTO handling_units (code, name, is_active) VALUES ('support', 'واحد پشتیبانی', 1)");
    $unitInsert->execute();
    $supportHandlingUnitId = (int) $pdo->query("SELECT id FROM handling_units WHERE code = 'support' ORDER BY id LIMIT 1")->fetchColumn();
    $departmentInsert = $pdo->prepare("INSERT INTO departments (name, code, is_active) VALUES ('Smoke Support Department', 'smoke-sup', 1)");
    $departmentInsert->execute();
    $smokeDepartmentId = (int) $pdo->lastInsertId();
    $categoryInsert = $pdo->prepare("INSERT INTO categories (name, code, service_group, is_active) VALUES ('Smoke Support Category', CONCAT('smoke-cat-', REPLACE(UUID(), '-', '')), 'support', 1)");
    $categoryInsert->execute();
    $smokeCategoryId = (int) $pdo->lastInsertId();
    $serviceInsert = $pdo->prepare(
        "INSERT INTO service_catalog (name, code, service_group, default_priority, department_id, category_id, handling_unit_id, requires_asset, default_ticket_type, is_active)
         VALUES ('Smoke Support Service', CONCAT('smoke-svc-', REPLACE(UUID(), '-', '')), 'support', 'normal', ?, ?, ?, 0, 'request', 1)"
    );
    $serviceInsert->execute([$smokeDepartmentId, $smokeCategoryId, $supportHandlingUnitId]);
    $supportServiceId = (int) $pdo->lastInsertId();
    echo 'smoke fixtures: handling_unit=' . $supportHandlingUnitId . ' department=' . $smokeDepartmentId . ' category=' . $smokeCategoryId . ' service=' . $supportServiceId . PHP_EOL;

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
    if ($dashboard['status'] !== 200) {
        $dashboardServerDiagnostic = is_file($serverLogPath) ? (string) file_get_contents($serverLogPath) : '';
        $dashboardErrors = $pdo->query('SELECT request_id, context, message, meta_json FROM system_logs ORDER BY id DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
        $dashboardAppLog = is_file($root . '/storage/logs/app.log') ? implode(" | ", array_slice(@file($root . '/storage/logs/app.log', FILE_IGNORE_NEW_LINES) ?: [], -20)) : '';
        echo 'dashboard HTTP diagnostic: body=' . substr((string) $dashboard['body'], 0, 1200)
            . ' system_logs=' . json_encode($dashboardErrors, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . ' app_log=' . $dashboardAppLog
            . ' server=' . substr($dashboardServerDiagnostic, -10000) . PHP_EOL;
    }
    smoke_check($dashboard['status'] === 200, 'dashboard returns HTTP 200 (status ' . $dashboard['status'] . ')');
    smoke_check(str_contains($dashboard['body'], 'داشبورد'), 'dashboard renders the dashboard page heading');

    // 2) Global search (exercises request_center_global_search).
    $search = smoke_http('http://' . $address . '/index.php?page=search&q=smoke', ['Cookie: ' . $cookie]);
    smoke_check($search['status'] === 200, 'global search returns HTTP 200 (status ' . $search['status'] . ')');
    smoke_check(str_contains($search['body'], 'درخواست'), 'global search page renders (ticket slice executed)');

    // 3) Legacy export URLs are compatibility redirects; the independent RC endpoint owns the response.
    $legacyExport = smoke_http('http://' . $address . '/index.php?action=export_report&from=' . urlencode('1405/01/01') . '&to=' . urlencode('1405/01/30'), ['Cookie: ' . $cookie]);
    $legacyExportLocation = '';
    foreach ($legacyExport['headers'] as $header) {
        if (stripos((string) $header, 'location:') === 0) {
            $legacyExportLocation = trim(substr((string) $header, 9));
            break;
        }
    }
    smoke_check($legacyExport['status'] === 302 && str_starts_with($legacyExportLocation, 'request-center.php?action=export_report'), 'legacy export URL redirects to the independent Request Center endpoint (status ' . $legacyExport['status'] . ')');
    $export = smoke_http('http://' . $address . '/request-center.php?action=export_report&from=' . urlencode('1405/01/01') . '&to=' . urlencode('1405/01/30'), ['Cookie: ' . $cookie]);
    if ($export['status'] !== 200) {
        $serverDiagnostic = is_file($serverLogPath) ? (string) file_get_contents($serverLogPath) : '';
        echo "export_report server diagnostic: " . substr($serverDiagnostic, -6000) . PHP_EOL;
    }
    smoke_check($export['status'] === 200, 'independent export_report returns HTTP 200 (status ' . $export['status'] . ')');
    smoke_check(str_starts_with($export['body'], 'PK' . "\x03\x04"), 'independent export_report returns a valid XLSX package (PK magic)');

    // 4) Legacy attachment URLs redirect; the independent endpoint enforces the 404 for an unknown id.
    $legacyDownload = smoke_http('http://' . $address . '/index.php?action=download_attachment&id=99999', ['Cookie: ' . $cookie]);
    $legacyDownloadLocation = '';
    foreach ($legacyDownload['headers'] as $header) {
        if (stripos((string) $header, 'location:') === 0) {
            $legacyDownloadLocation = trim(substr((string) $header, 9));
            break;
        }
    }
    smoke_check($legacyDownload['status'] === 302 && str_starts_with($legacyDownloadLocation, 'request-center.php?action=download_attachment'), 'legacy attachment URL redirects to the independent Request Center endpoint (status ' . $legacyDownload['status'] . ')');
    $download = smoke_http('http://' . $address . '/request-center.php?action=download_attachment&id=99999', ['Cookie: ' . $cookie]);
    smoke_check($download['status'] === 404, 'independent download_attachment with unknown id returns HTTP 404 (status ' . $download['status'] . ')');

    // 5) Phase 3 — POST through the INDEPENDENT entry: no CSRF must yield 419 (legacy contract).
    $noCsrf = smoke_http_post('http://' . $address . '/request-center.php', ['action' => 'create_ticket', 'subject' => 'should-be-rejected'], ['Cookie: ' . $cookie]);
    smoke_check($noCsrf['status'] === 419, 'independent POST without CSRF is rejected with HTTP 419 (status ' . $noCsrf['status'] . ')');

    // 6) Phase 3 — obtain the token from the actual independent creation form,
    // then exercise the complete create_ticket round trip through that entry.
    $createForm = smoke_http('http://' . $address . '/request-center.php?page=new-ticket', ['Cookie: ' . $cookie]);
    if ($createForm['status'] !== 200) {
        $formLog = is_file($root . '/storage/logs/app.log') ? implode(" ⏎ ", array_slice(@file($root . '/storage/logs/app.log', FILE_IGNORE_NEW_LINES) ?: [], -15)) : '';
        $serverDiagnostic = is_file($serverLogPath) ? (string) file_get_contents($serverLogPath) : '';
        echo "creation form diagnostic: app.log=" . $formLog . " | server=" . substr($serverDiagnostic, -4000) . PHP_EOL;
    }
    smoke_check($createForm['status'] === 200, 'independent creation form returns HTTP 200 (status ' . $createForm['status'] . ')');
    $csrf = '';
    $requestToken = '';
    if (preg_match('/name="csrf"\\s+value="([a-f0-9]{64})"/i', $createForm['body'], $csrfMatch) === 1) {
        $csrf = $csrfMatch[1];
    }
    if (preg_match('/name="request_token"\\s+value="([a-f0-9]{64})"/i', $createForm['body'], $requestTokenMatch) === 1) {
        $requestToken = $requestTokenMatch[1];
    }
    smoke_check($csrf !== '', 'independent creation form exposes a session-bound CSRF token');
    smoke_check($requestToken !== '', 'independent creation form exposes a one-time submission token');
    $uploadContents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j5ZkAAAAASUVORK5CYII=', true);
    if (!is_string($uploadContents)) { throw new RuntimeException('Could not decode PNG attachment fixture.'); }
    $uploadPath = tempnam(sys_get_temp_dir(), 'rc-upload-');
    file_put_contents($uploadPath, $uploadContents);
    $create = smoke_http_multipart_post('http://' . $address . '/request-center.php', [
        'action' => 'create_ticket',
        'subject' => 'RC phase-3 independent POST smoke',
        'description' => 'Created via request-center.php POST path (same protocol as legacy)',
        'category_id' => $smokeCategoryId,
        'service_id' => $supportServiceId,
        'service_group' => 'support',
        'ticket_type' => 'request',
        'priority' => 'normal',
        'support_location' => 'smoke-lab',
        'department_id' => $smokeDepartmentId,
        'csrf' => $csrf,
        'request_token' => $requestToken,
    ], 'attachment', $uploadPath, 'rc-phase3-attachment.png', 'image/png', ['Cookie: ' . $cookie]);
    @unlink($uploadPath);
    if ($create['status'] !== 302) {
        $logTail = '';
        $logFile = $root . '/storage/logs/app.log';
        if (is_file($logFile)) {
            $logLines = @file($logFile, FILE_IGNORE_NEW_LINES) ?: [];
            $logTail = " | app.log tail: " . implode(" ⏎ ", array_slice($logLines, -12));
        }
        smoke_check(false, 'independent create_ticket returns HTTP 302 (status ' . $create['status'] . '). Body snippet: ' . substr(strip_tags($create['body']), 0, 300) . $logTail);
    }
    smoke_check($create['status'] === 302, 'independent create_ticket returns HTTP 302 (status ' . $create['status'] . ')');
    if (!str_starts_with($create['location'], 'request-center.php?page=ticket&id=')) {
        $logTail = '';
        $logFile = $root . '/storage/logs/app.log';
        if (is_file($logFile)) {
            $logLines = @file($logFile, FILE_IGNORE_NEW_LINES) ?: [];
            $logTail = " | app.log tail: " . implode(" ⏎ ", array_slice($logLines, -12));
        }
        smoke_check(false, 'independent create_ticket redirects to the ticket page. Location: ' . $create['location'] . $logTail);
    }
    smoke_check(str_starts_with($create['location'], 'request-center.php?page=ticket&id='), 'independent create_ticket redirects to the independent ticket page (Location: ' . $create['location'] . ')');
    $legacyLifecycleRoutes = [
        'new-ticket' => 'index.php?page=new-ticket',
        'ticket' => 'index.php?page=ticket&id=' . (int) (preg_match('/[?&]id=([0-9]+)/', $create['location'], $createdIdMatch) ? $createdIdMatch[1] : 0),
        'queue' => 'index.php?page=queue&queue=overdue',
        'supervisor' => 'index.php?page=supervisor',
        'services' => 'index.php?page=services',
        'reports' => 'index.php?page=reports',
        'analytics' => 'index.php?page=analytics',
    ];
    foreach ($legacyLifecycleRoutes as $legacyLabel => $legacyPath) {
        $legacyResponse = smoke_http('http://' . $address . '/' . $legacyPath, ['Cookie: ' . $cookie]);
        $legacyLocation = '';
        foreach ($legacyResponse['headers'] as $header) {
            if (stripos((string) $header, 'location:') === 0) {
                $legacyLocation = trim(substr((string) $header, 9));
                break;
            }
        }
        smoke_check($legacyResponse['status'] === 302 && str_starts_with($legacyLocation, 'request-center.php?')
            && str_contains($legacyLocation, 'page=' . $legacyLabel),
            'legacy ' . $legacyLabel . ' URL is bridged to the independent Request Center entry (status ' . $legacyResponse['status'] . ')');
    }
    $ticketRow = $pdo->query("SELECT id, subject, ticket_type FROM tickets WHERE subject = 'RC phase-3 independent POST smoke' ORDER BY id DESC LIMIT 1")->fetch();
    smoke_check(is_array($ticketRow), 'independent create_ticket persisted the ticket row');
    smoke_check((string) ($ticketRow['ticket_type'] ?? '') === 'request', 'independent create_ticket stored the requested ticket type');
    $attachmentQuery = $pdo->prepare('SELECT a.id, a.stored_name FROM ticket_attachments a JOIN ticket_messages m ON m.id = a.message_id WHERE m.ticket_id = ? ORDER BY a.id DESC LIMIT 1');
    $attachmentQuery->execute([(int) $ticketRow['id']]);
    $attachmentRow = $attachmentQuery->fetch(PDO::FETCH_ASSOC);
    smoke_check(is_array($attachmentRow), 'independent create_ticket persisted the uploaded attachment');
    if (is_array($attachmentRow)) {
        $attachmentDownload = smoke_http('http://' . $address . '/request-center.php?action=download_attachment&id=' . (int) $attachmentRow['id'], ['Cookie: ' . $cookie]);
        smoke_check($attachmentDownload['status'] === 200 && $attachmentDownload['body'] === $uploadContents, 'authorized independent attachment download returns the original bytes (status ' . $attachmentDownload['status'] . ')');
        // Rebind the same persisted file to an internal note, then verify the
        // actual requester can still view the ticket but cannot fetch that file.
        $requesterSeed = $pdo->prepare(
            "INSERT INTO users (username, password_hash, full_name, is_primary_admin, role, is_active)
             VALUES ('rc_smoke_requester', 'not-used', 'RC Smoke Requester', 0, 'user', 1)
             ON DUPLICATE KEY UPDATE is_primary_admin = 0, role = 'user', is_active = 1"
        );
        $requesterSeed->execute();
        $requesterId = (int) $pdo->query("SELECT id FROM users WHERE username = 'rc_smoke_requester' LIMIT 1")->fetchColumn();
        smoke_check($requesterId > 0, 'seeded a non-staff requester for internal attachment privacy');
        $requesterSession = smoke_http('http://' . $address . '/tests/fixtures/request-center-session-seed.php?user_id=' . rawurlencode((string) $requesterId));
        $requesterSessionData = json_decode($requesterSession['body'], true);
        smoke_check($requesterSession['status'] === 200 && is_array($requesterSessionData), 'requester session seed endpoint returned HTTP 200');
        $requesterCookie = (string) ($requesterSessionData['cookie_name'] ?? '') . '=' . (string) ($requesterSessionData['session_id'] ?? '');
        $pdo->prepare('UPDATE tickets SET requester_id = ? WHERE id = ?')->execute([$requesterId, (int) $ticketRow['id']]);
        $internalMessage = $pdo->prepare('INSERT INTO ticket_messages (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, 1)');
        $internalMessage->execute([(int) $ticketRow['id'], $userId, 'RC internal attachment privacy fixture']);
        $internalMessageId = (int) $pdo->lastInsertId();
        $pdo->prepare('UPDATE ticket_attachments SET message_id = ? WHERE id = ?')->execute([$internalMessageId, (int) $attachmentRow['id']]);
        $requesterDetail = smoke_http('http://' . $address . '/request-center.php?page=ticket&id=' . (int) $ticketRow['id'], ['Cookie: ' . $requesterCookie]);
        smoke_check($requesterDetail['status'] === 200, 'requester can view the ticket containing an internal attachment');
        smoke_check(!str_contains($requesterDetail['body'], 'RC internal attachment privacy fixture'), 'internal note body is not rendered to the requester');
        $internalDownload = smoke_http('http://' . $address . '/request-center.php?action=download_attachment&id=' . (int) $attachmentRow['id'], ['Cookie: ' . $requesterCookie]);
        smoke_check($internalDownload['status'] === 404, 'requester cannot download an attachment belonging to an internal note (status ' . $internalDownload['status'] . ')');
        $storedPath = $root . '/storage/uploads/' . basename((string) $attachmentRow['stored_name']);
        if (is_file($storedPath)) {
            @unlink($storedPath);
        }
    }

    // Failed creation must also return to the independent form, not to the legacy front controller.
    $retryForm = smoke_http('http://' . $address . '/request-center.php?page=new-ticket', ['Cookie: ' . $cookie]);
    $retryToken = '';
    if (preg_match('/name="request_token"\\s+value="([a-f0-9]{64})"/i', $retryForm['body'], $retryMatch) === 1) {
        $retryToken = $retryMatch[1];
    }
    smoke_check($retryForm['status'] === 200 && $retryToken !== '', 'independent form issues a fresh token for the failure-path check');
    $invalidCreate = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'create_ticket',
        'subject' => 'invalid service request smoke',
        'description' => 'Must be rejected because no active service was selected.',
        'service_id' => '0',
        'service_group' => 'support',
        'ticket_type' => 'request',
        'support_location' => 'smoke-lab',
        'csrf' => $csrf,
        'request_token' => $retryToken,
    ], ['Cookie: ' . $cookie]);
    smoke_check($invalidCreate['status'] === 302 && str_starts_with($invalidCreate['location'], 'request-center.php?page=new-ticket'), 'failed independent creation returns to the independent creation form (status ' . $invalidCreate['status'] . ')');

    // 7) Phase 3 — exercise all four independent workspaces and their operational pages.
    $createdTicketId = (int) ($ticketRow['id'] ?? 0);
    $detail = smoke_http('http://' . $address . '/request-center.php?id=' . $createdTicketId, ['Cookie: ' . $cookie]);
    smoke_check($detail['status'] === 200 && str_contains($detail['body'], 'RC phase-3 independent POST smoke'), 'independent ticket detail renders the created request (status ' . $detail['status'] . ')');
    smoke_check(str_contains($detail['body'], 'name="action" value="reply"'), 'independent ticket detail exposes the reply operation');
    $workspaceProbes = [
        ['requester', 'request-center.php?workspace=requester'],
        ['agent', 'request-center.php?workspace=agent'],
        ['supervisor', 'request-center.php?workspace=supervisor'],
        ['admin', 'request-center.php?workspace=admin'],
        ['agent queue', 'request-center.php?page=queue'],
        ['supervisor decisions', 'request-center.php?page=supervisor'],
        ['service catalog', 'request-center.php?page=services'],
        ['reports', 'request-center.php?page=reports'],
        ['analytics', 'request-center.php?page=analytics'],
    ];
    foreach ($workspaceProbes as [$workspaceLabel, $workspacePath]) {
        $workspaceResponse = smoke_http('http://' . $address . '/' . $workspacePath, ['Cookie: ' . $cookie]);
        smoke_check($workspaceResponse['status'] === 200, 'independent ' . $workspaceLabel . ' page returns HTTP 200 (status ' . $workspaceResponse['status'] . ')');
    }

    // Admin Workspace: verify the actual configuration forms and execute their POST commands.
    $adminWorkspace = smoke_http('http://' . $address . '/request-center.php?workspace=admin', ['Cookie: ' . $cookie]);
    smoke_check($adminWorkspace['status'] === 200
        && str_contains($adminWorkspace['body'], 'id="sla-work-hours"')
        && str_contains($adminWorkspace['body'], 'id="ola-policies"')
        && str_contains($adminWorkspace['body'], 'rc-admin-audit-title'),
        'primary-admin workspace renders SLA calendar, OLA policy editor and scoped audit trail');
    smoke_check(!str_contains($adminWorkspace['body'], 'RC internal attachment privacy fixture'),
        'admin audit list does not disclose internal message content');

    $oldWorkSettings = [];
    foreach (['sla.work_start_hour', 'sla.work_end_hour', 'sla.weekend_days'] as $settingKey) {
        $settingQuery = $pdo->prepare('SELECT value FROM settings WHERE `key` = ? LIMIT 1');
        $settingQuery->execute([$settingKey]);
        $oldWorkSettings[$settingKey] = $settingQuery->fetchColumn();
    }
    $workHoursUpdate = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'save_rc_work_hours',
        'work_start_hour' => '9',
        'work_end_hour' => '17',
        'weekend_days' => ['4', '5'],
        'csrf' => $csrf,
    ], ['Cookie: ' . $cookie]);
    $workStartSaved = (string) $pdo->query("SELECT value FROM settings WHERE `key` = 'sla.work_start_hour'")->fetchColumn();
    $workEndSaved = (string) $pdo->query("SELECT value FROM settings WHERE `key` = 'sla.work_end_hour'")->fetchColumn();
    $weekendSaved = (string) $pdo->query("SELECT value FROM settings WHERE `key` = 'sla.weekend_days'")->fetchColumn();
    smoke_check($workHoursUpdate['status'] === 302
        && $workStartSaved === '9' && $workEndSaved === '17' && $weekendSaved === '4,5',
        'admin can update SLA business hours through the independent RC POST boundary');

    $invalidWorkHours = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'save_rc_work_hours',
        'work_start_hour' => '9',
        'work_end_hour' => '17',
        'weekend_days' => ['1', '2', '3', '4', '5', '6', '7'],
        'csrf' => $csrf,
    ], ['Cookie: ' . $cookie]);
    $weekendAfterInvalid = (string) $pdo->query("SELECT value FROM settings WHERE `key` = 'sla.weekend_days'")->fetchColumn();
    smoke_check($invalidWorkHours['status'] === 302 && $weekendAfterInvalid === '4,5',
        'admin SLA calendar rejects a seven-day weekend without persisting invalid settings');

    if (isset($requesterCookie)) {
        $forbiddenWorkHours = smoke_http_post('http://' . $address . '/request-center.php', [
            'action' => 'save_rc_work_hours',
            'work_start_hour' => '10',
            'work_end_hour' => '18',
            'weekend_days' => ['4', '5'],
            'csrf' => (string) ($requesterSessionData['csrf'] ?? $csrf),
        ], ['Cookie: ' . $requesterCookie]);
        $weekendAfterForbidden = (string) $pdo->query("SELECT value FROM settings WHERE `key` = 'sla.weekend_days'")->fetchColumn();
        smoke_check($forbiddenWorkHours['status'] === 302 && $weekendAfterForbidden === '4,5',
            'requester cannot change admin-only SLA calendar settings');
    }

    if (function_exists('ensure_default_ola_policies')) {
        ensure_default_ola_policies();
    }
    $olaPolicy = $pdo->query(
        'SELECT id, name, department_id, priority, response_minutes, resolution_minutes, escalation_1_minutes, escalation_2_minutes, is_active '
        . 'FROM ola_policies WHERE is_active = 1 ORDER BY department_id IS NULL DESC, id LIMIT 1'
    )->fetch(PDO::FETCH_ASSOC);
    smoke_check(is_array($olaPolicy), 'disposable database has an active OLA policy for admin configuration smoke');
    if (is_array($olaPolicy)) {
        $updatedPolicyName = mb_substr((string) $olaPolicy['name'], 0, 130, 'UTF-8') . ' smoke';
        $olaPolicyUpdate = smoke_http_post('http://' . $address . '/request-center.php', [
            'action' => 'update_ola_policy',
            'policy_id' => (string) $olaPolicy['id'],
            'policy_name' => $updatedPolicyName,
            'department_id' => (string) ((int) ($olaPolicy['department_id'] ?? 0)),
            'priority' => (string) $olaPolicy['priority'],
            'response_minutes' => (string) $olaPolicy['response_minutes'],
            'resolution_minutes' => (string) $olaPolicy['resolution_minutes'],
            'escalation_1_minutes' => (string) $olaPolicy['escalation_1_minutes'],
            'escalation_2_minutes' => (string) $olaPolicy['escalation_2_minutes'],
            'is_active' => (string) $olaPolicy['is_active'],
            'csrf' => $csrf,
        ], ['Cookie: ' . $cookie]);
        $policyNameSaved = $pdo->prepare('SELECT name FROM ola_policies WHERE id = ?');
        $policyNameSaved->execute([(int) $olaPolicy['id']]);
        $policyAudit = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE action_code = 'ola_policy_updated' AND target_id IS NULL");
        $policyAudit->execute();
        smoke_check($olaPolicyUpdate['status'] === 302
            && (string) $policyNameSaved->fetchColumn() === $updatedPolicyName
            && (int) $policyAudit->fetchColumn() > 0,
            'primary admin can update an OLA policy and the change is audited');
        if (isset($requesterCookie)) {
            $forbiddenOlaUpdate = smoke_http_post('http://' . $address . '/request-center.php', [
                'action' => 'update_ola_policy',
                'policy_id' => (string) $olaPolicy['id'],
                'policy_name' => 'forbidden requester edit',
                'department_id' => (string) ((int) ($olaPolicy['department_id'] ?? 0)),
                'priority' => (string) $olaPolicy['priority'],
                'response_minutes' => (string) $olaPolicy['response_minutes'],
                'resolution_minutes' => (string) $olaPolicy['resolution_minutes'],
                'escalation_1_minutes' => (string) $olaPolicy['escalation_1_minutes'],
                'escalation_2_minutes' => (string) $olaPolicy['escalation_2_minutes'],
                'is_active' => (string) $olaPolicy['is_active'],
                'csrf' => (string) ($requesterSessionData['csrf'] ?? $csrf),
            ], ['Cookie: ' . $requesterCookie]);
            $policyNameAfterForbidden = $pdo->prepare('SELECT name FROM ola_policies WHERE id = ?');
            $policyNameAfterForbidden->execute([(int) $olaPolicy['id']]);
            smoke_check($forbiddenOlaUpdate['status'] === 302
                && (string) $policyNameAfterForbidden->fetchColumn() === $updatedPolicyName,
                'requester cannot modify an admin-only OLA policy');
        }
    }

    // Restore the original business-hours fixture to avoid affecting later smoke scenarios.
    $restoreSetting = $pdo->prepare('INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
    $deleteSetting = $pdo->prepare('DELETE FROM settings WHERE `key` = ?');
    foreach ($oldWorkSettings as $settingKey => $settingValue) {
        if ($settingValue === false || $settingValue === null) {
            $deleteSetting->execute([$settingKey]);
        } else {
            $restoreSetting->execute([$settingKey, (string) $settingValue]);
        }
    }

    // Agent Workspace: claim, public response, internal note, waiting-user and resolution.
    $agentSeed = $pdo->prepare(
        "INSERT INTO users (username, password_hash, full_name, is_primary_admin, role, is_active, department_id, handling_unit_id, is_it_agent)
         VALUES ('rc_smoke_agent', 'not-used', 'RC Smoke Agent', 0, 'agent', 1, ?, ?, 0)
         ON DUPLICATE KEY UPDATE is_primary_admin = 0, role = 'agent', is_active = 1, department_id = VALUES(department_id), handling_unit_id = VALUES(handling_unit_id), is_it_agent = 0"
    );
    $agentSeed->execute([$smokeDepartmentId, $supportHandlingUnitId]);
    $agentId = (int) $pdo->query("SELECT id FROM users WHERE username = 'rc_smoke_agent' LIMIT 1")->fetchColumn();
    $agentSession = smoke_http('http://' . $address . '/tests/fixtures/request-center-session-seed.php?user_id=' . rawurlencode((string) $agentId));
    $agentSessionData = json_decode($agentSession['body'], true);
    smoke_check($agentSession['status'] === 200 && is_array($agentSessionData), 'agent session is seeded for operational workspace smoke');
    $agentCookie = (string) ($agentSessionData['cookie_name'] ?? '') . '=' . (string) ($agentSessionData['session_id'] ?? '');
    $agentCsrf = (string) ($agentSessionData['csrf'] ?? '');

    $pdo->prepare(
        "UPDATE tickets SET status = 'manager_review', assigned_to = NULL, assigned_at = NULL, first_response_at = NULL,
         resolved_at = NULL, closed_at = NULL, supervisor_approved_at = NULL, supervisor_id = NULL,
         cancel_requested_at = NULL, cancel_requested_by = NULL, cancel_request_reason = NULL,
         department_id = ?, service_group = 'support' WHERE id = ?"
    )->execute([$smokeDepartmentId, (int) $ticketRow['id']]);
    $agentDetailBeforeClaim = smoke_http(
        'http://' . $address . '/request-center.php?page=ticket&id=' . (int) $ticketRow['id'] . '&workspace=agent&agent_view=team',
        ['Cookie: ' . $agentCookie]
    );
    smoke_check($agentDetailBeforeClaim['status'] === 200
        && str_contains($agentDetailBeforeClaim['body'], 'name="action" value="claim_ticket"'),
        'eligible agent sees the Claim operation on an unassigned request');

    $claimResponse = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'claim_ticket', 'ticket_id' => (string) $ticketRow['id'],
        'rc_return_workspace' => 'agent', 'rc_return_agent_view' => 'team', 'csrf' => $agentCsrf,
    ], ['Cookie: ' . $agentCookie]);
    $claimedTicket = $pdo->prepare('SELECT status, assigned_to FROM tickets WHERE id = ?');
    $claimedTicket->execute([(int) $ticketRow['id']]);
    $claimedState = $claimedTicket->fetch(PDO::FETCH_ASSOC);
    smoke_check($claimResponse['status'] === 302
        && (string) ($claimedState['status'] ?? '') === 'assigned'
        && (int) ($claimedState['assigned_to'] ?? 0) === $agentId,
        'agent Claim operation assigns the request through the independent POST boundary');

    $requesterCancelRequest = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'cancel_ticket', 'ticket_id' => (string) $ticketRow['id'],
        'cancel_reason' => 'RC requester cancellation review smoke',
        'csrf' => (string) ($requesterSessionData['csrf'] ?? ''),
        'rc_return_workspace' => 'requester',
    ], ['Cookie: ' . $requesterCookie]);
    $pendingCancellation = $pdo->prepare('SELECT cancel_requested_at, status FROM tickets WHERE id = ?');
    $pendingCancellation->execute([(int) $ticketRow['id']]);
    $pendingCancellationState = $pendingCancellation->fetch(PDO::FETCH_ASSOC);
    smoke_check($requesterCancelRequest['status'] === 302 && !empty($pendingCancellationState['cancel_requested_at'])
        && (string) $pendingCancellationState['status'] === 'assigned',
        'requester cancellation after assignment creates a pending decision without changing the lifecycle state');

    $supervisorRejectCancellation = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'decide_ticket_cancellation', 'ticket_id' => (string) $ticketRow['id'],
        'decision' => 'reject', 'decision_note' => 'RC supervisor rejected cancellation; handling continues',
        'rc_return_workspace' => 'supervisor', 'csrf' => $csrf,
    ], ['Cookie: ' . $cookie]);
    $cancellationAfterDecision = $pdo->prepare('SELECT cancel_requested_at, status FROM tickets WHERE id = ?');
    $cancellationAfterDecision->execute([(int) $ticketRow['id']]);
    $cancellationAfterDecisionState = $cancellationAfterDecision->fetch(PDO::FETCH_ASSOC);
    smoke_check($supervisorRejectCancellation['status'] === 302
        && empty($cancellationAfterDecisionState['cancel_requested_at'])
        && (string) $cancellationAfterDecisionState['status'] === 'assigned',
        'supervisor rejects a pending requester cancellation and safely resumes handling');

    $agentPublicReply = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'reply', 'ticket_id' => (string) $ticketRow['id'],
        'body' => 'RC agent public first response', 'csrf' => $agentCsrf,
        'rc_return_workspace' => 'agent', 'rc_return_agent_view' => 'mine',
    ], ['Cookie: ' . $agentCookie]);
    $statusAfterAgentReply = (string) $pdo->query('SELECT status FROM tickets WHERE id = ' . (int) $ticketRow['id'])->fetchColumn();
    smoke_check($agentPublicReply['status'] === 302 && $statusAfterAgentReply === 'in_progress',
        'agent public response records first response and starts active handling');

    $internalNoteBody = 'RC agent internal note must remain private';
    $agentInternalNote = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'reply', 'ticket_id' => (string) $ticketRow['id'],
        'body' => $internalNoteBody, 'is_internal' => '1', 'csrf' => $agentCsrf,
        'rc_return_workspace' => 'agent',
    ], ['Cookie: ' . $agentCookie]);
    $agentDetailWithInternal = smoke_http(
        'http://' . $address . '/request-center.php?page=ticket&id=' . (int) $ticketRow['id'] . '&workspace=agent',
        ['Cookie: ' . $agentCookie]
    );
    $requesterDetailWithInternal = smoke_http(
        'http://' . $address . '/request-center.php?page=ticket&id=' . (int) $ticketRow['id'] . '&workspace=requester',
        ['Cookie: ' . $requesterCookie]
    );
    smoke_check($agentInternalNote['status'] === 302
        && str_contains($agentDetailWithInternal['body'], $internalNoteBody)
        && !str_contains($requesterDetailWithInternal['body'], $internalNoteBody),
        'internal note is available to the assigned agent and hidden from the requester');

    $agentWait = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'reply', 'ticket_id' => (string) $ticketRow['id'],
        'body' => 'RC agent requests additional information', 'after_status' => 'waiting_user',
        'csrf' => $agentCsrf, 'rc_return_workspace' => 'agent',
    ], ['Cookie: ' . $agentCookie]);
    $statusAfterAgentWait = (string) $pdo->query('SELECT status FROM tickets WHERE id = ' . (int) $ticketRow['id'])->fetchColumn();
    smoke_check($agentWait['status'] === 302 && $statusAfterAgentWait === 'waiting_user',
        'agent can move the request to waiting_user with a public explanation');

    $requesterReply = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'reply', 'ticket_id' => (string) $ticketRow['id'],
        'body' => 'RC requester supplied the requested information',
        'csrf' => (string) ($requesterSessionData['csrf'] ?? ''), 'rc_return_workspace' => 'requester',
    ], ['Cookie: ' . $requesterCookie]);
    $statusAfterRequesterReply = (string) $pdo->query('SELECT status FROM tickets WHERE id = ' . (int) $ticketRow['id'])->fetchColumn();
    smoke_check($requesterReply['status'] === 302 && $statusAfterRequesterReply === 'in_progress',
        'requester reply resumes a request waiting for user information');

    $agentResolve = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'reply', 'ticket_id' => (string) $ticketRow['id'],
        'body' => 'RC agent completed the requested work', 'after_status' => 'resolved',
        'csrf' => $agentCsrf, 'rc_return_workspace' => 'agent',
    ], ['Cookie: ' . $agentCookie]);
    $statusAfterAgentResolve = (string) $pdo->query('SELECT status FROM tickets WHERE id = ' . (int) $ticketRow['id'])->fetchColumn();
    smoke_check($agentResolve['status'] === 302 && $statusAfterAgentResolve === 'resolved',
        'agent can resolve the request after requester response');

    // Requester Workspace: submit a real satisfaction rating after resolution.
    $requesterRating = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'rate_ticket', 'ticket_id' => (string) $ticketRow['id'],
        'score' => '5', 'rating_comment' => 'RC requester rating smoke',
        'csrf' => (string) ($requesterSessionData['csrf'] ?? ''), 'rc_return_workspace' => 'requester',
    ], ['Cookie: ' . $requesterCookie]);
    $ratingCount = $pdo->prepare('SELECT COUNT(*) FROM ticket_ratings WHERE ticket_id = ? AND requester_id = ? AND score = 5');
    $ratingCount->execute([(int) $ticketRow['id'], $requesterId]);
    smoke_check($requesterRating['status'] === 302 && (int) $ratingCount->fetchColumn() === 1,
        'requester can submit a satisfaction rating through the independent workspace');

    // Supervisor Workspace: approve, reopen and reassign through real POST commands.
    $supervisorSeed = $pdo->prepare(
        "INSERT INTO users (username, password_hash, full_name, is_primary_admin, role, is_active, department_id)
         VALUES ('rc_smoke_supervisor', 'not-used', 'RC Smoke Supervisor', 0, 'supervisor', 1, ?)
         ON DUPLICATE KEY UPDATE is_primary_admin = 0, role = 'supervisor', is_active = 1, department_id = VALUES(department_id)"
    );
    $supervisorSeed->execute([$smokeDepartmentId]);
    $supervisorId = (int) $pdo->query("SELECT id FROM users WHERE username = 'rc_smoke_supervisor' LIMIT 1")->fetchColumn();
    $supervisorSession = smoke_http('http://' . $address . '/tests/fixtures/request-center-session-seed.php?user_id=' . rawurlencode((string) $supervisorId));
    $supervisorSessionData = json_decode($supervisorSession['body'], true);
    smoke_check($supervisorSession['status'] === 200 && is_array($supervisorSessionData), 'supervisor session is seeded for decision workspace smoke');
    $supervisorCookie = (string) ($supervisorSessionData['cookie_name'] ?? '') . '=' . (string) ($supervisorSessionData['session_id'] ?? '');
    $supervisorCsrf = (string) ($supervisorSessionData['csrf'] ?? '');

    $supervisorDecision = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'supervisor_decision', 'ticket_id' => (string) $ticketRow['id'],
        'decision' => 'approve', 'supervisor_note' => 'RC supervisor approved after requester rating',
        'rc_return_workspace' => 'supervisor', 'csrf' => $supervisorCsrf,
    ], ['Cookie: ' . $supervisorCookie]);
    $statusAfterSupervisorApproval = (string) $pdo->query('SELECT status FROM tickets WHERE id = ' . (int) $ticketRow['id'])->fetchColumn();
    smoke_check($supervisorDecision['status'] === 302 && $statusAfterSupervisorApproval === 'closed',
        'supervisor can approve and close a resolved, rated request');

    $supervisorReopen = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'supervisor_reopen', 'ticket_id' => (string) $ticketRow['id'],
        'supervisor_note' => 'RC supervisor reopened for verification',
        'rc_return_workspace' => 'supervisor', 'csrf' => $supervisorCsrf,
    ], ['Cookie: ' . $supervisorCookie]);
    $statusAfterSupervisorReopen = (string) $pdo->query('SELECT status FROM tickets WHERE id = ' . (int) $ticketRow['id'])->fetchColumn();
    smoke_check($supervisorReopen['status'] === 302 && $statusAfterSupervisorReopen === 'in_progress',
        'supervisor can reopen a closed request with a recorded reason');

    $secondAgentSeed = $pdo->prepare(
        "INSERT INTO users (username, password_hash, full_name, is_primary_admin, role, is_active, department_id, handling_unit_id, is_it_agent)
         VALUES ('rc_smoke_agent_two', 'not-used', 'RC Smoke Agent Two', 0, 'agent', 1, ?, ?, 0)
         ON DUPLICATE KEY UPDATE is_primary_admin = 0, role = 'agent', is_active = 1, department_id = VALUES(department_id), handling_unit_id = VALUES(handling_unit_id), is_it_agent = 0"
    );
    $secondAgentSeed->execute([$smokeDepartmentId, $supportHandlingUnitId]);
    $secondAgentId = (int) $pdo->query("SELECT id FROM users WHERE username = 'rc_smoke_agent_two' LIMIT 1")->fetchColumn();
    $supervisorAssign = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'supervisor_assign_ticket', 'ticket_id' => (string) $ticketRow['id'],
        'assigned_to' => (string) $secondAgentId, 'assignment_reason' => 'RC supervisor workload balancing',
        'assignment_note' => '', 'rc_return_workspace' => 'supervisor', 'csrf' => $supervisorCsrf,
    ], ['Cookie: ' . $supervisorCookie]);
    $assigneeAfterSupervisorReassign = (int) $pdo->query('SELECT assigned_to FROM tickets WHERE id = ' . (int) $ticketRow['id'])->fetchColumn();
    smoke_check($supervisorAssign['status'] === 302 && $assigneeAfterSupervisorReassign === $secondAgentId,
        'supervisor can reassign an active request to another eligible agent with a reason');

    $agentQueueBulk = smoke_http_post('http://' . $address . '/request-center.php', [
        'action' => 'bulk_update_tickets',
        'ticket_ids' => [(string) $ticketRow['id']],
        'bulk_status' => 'resolved',
        'bulk_note' => 'RC queue bulk resolution smoke',
        'rc_return_queue' => 'all',
        'rc_return_p' => '1',
        'csrf' => $csrf,
    ], ['Cookie: ' . $cookie]);
    $bulkStatus = (string) $pdo->query('SELECT status FROM tickets WHERE id = ' . (int) $ticketRow['id'])->fetchColumn();
    $bulkEventCount = (int) $pdo->query(
        "SELECT COUNT(*) FROM ticket_events WHERE ticket_id = " . (int) $ticketRow['id'] . " AND event_type = 'bulk_status_changed'"
    )->fetchColumn();
    smoke_check($agentQueueBulk['status'] === 302 && $bulkStatus === 'resolved' && $bulkEventCount === 1,
        'agent queue bulk operation resolves the selected request and writes its event');

    // Run the real SLA/OLA maintenance command twice against a disposable ticket.
    $requesterQuery = $pdo->prepare("SELECT id FROM users WHERE role = 'user' AND id <> ? ORDER BY id LIMIT 1");
    $requesterQuery->execute([$userId]);
    $maintenanceRequester = (int) $requesterQuery->fetchColumn();
    if ($maintenanceRequester <= 0) {
        throw new RuntimeException('SLA/OLA smoke requires a separate requester account.');
    }
    $maintenanceAssignee = $userId;
    $maintenanceTicketId = (int) $ticketRow['id'];
    // The preceding requester Workspace rating scenario must not contaminate the independent auto-close fixture.
    $pdo->prepare('DELETE FROM ticket_ratings WHERE ticket_id = ?')->execute([$maintenanceTicketId]);
    $maintenanceFixture = $pdo->prepare('UPDATE tickets SET requester_id = ?, status = "in_progress", assigned_to = ?, first_response_at = NULL, resolved_at = NULL, cancel_requested_at = NULL, sla_paused_at = NULL, due_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE), ola_response_due_at = DATE_SUB(NOW(), INTERVAL 5 MINUTE), ola_due_at = DATE_SUB(NOW(), INTERVAL 10 MINUTE), ola_escalation_1_at = DATE_SUB(NOW(), INTERVAL 15 MINUTE), ola_escalation_2_at = DATE_SUB(NOW(), INTERVAL 20 MINUTE) WHERE id = ?');
    $maintenanceFixture->execute([$maintenanceRequester, $maintenanceAssignee, $maintenanceTicketId]);
    $runMaintenance = static function () use ($root, $serverEnv): array {
        $pipes = [];
        $process = proc_open([PHP_BINARY, $root . '/cron/sla_maintenance.php'], [
            0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
        ], $pipes, $root, $serverEnv);
        if (!is_resource($process)) {
            throw new RuntimeException('Could not start SLA/OLA maintenance process.');
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return ['exit' => proc_close($process), 'stdout' => (string) $stdout, 'stderr' => (string) $stderr];
    };
    $maintenanceFirst = $runMaintenance();
    smoke_check($maintenanceFirst['exit'] === 0, 'SLA/OLA maintenance command executes successfully (exit ' . $maintenanceFirst['exit'] . ')');
    $slaAlerts = (int) $pdo->query('SELECT COUNT(*) FROM ticket_sla_alerts WHERE ticket_id = ' . $maintenanceTicketId)->fetchColumn();
    $olaAlerts = (int) $pdo->query('SELECT COUNT(*) FROM ticket_ola_alerts WHERE ticket_id = ' . $maintenanceTicketId)->fetchColumn();
    if ($slaAlerts !== 1 || $olaAlerts !== 4) {
        $maintenanceRow = $pdo->query('SELECT id, status, due_at, sla_paused_at, first_response_at, ola_response_due_at, ola_due_at, ola_escalation_1_at, ola_escalation_2_at FROM tickets WHERE id = ' . $maintenanceTicketId)->fetch(PDO::FETCH_ASSOC);
        $appLogTail = is_file($root . '/storage/logs/app.log') ? implode(" ⏎ ", array_slice(@file($root . '/storage/logs/app.log', FILE_IGNORE_NEW_LINES) ?: [], -10)) : '';
        echo 'SLA/OLA diagnostic: stdout=' . $maintenanceFirst['stdout'] . ' stderr=' . $maintenanceFirst['stderr']
            . ' sla=' . $slaAlerts . ' ola=' . $olaAlerts . ' row=' . json_encode($maintenanceRow) . ' app.log=' . $appLogTail . PHP_EOL;
    }
    smoke_check($slaAlerts === 1 && $olaAlerts === 4, 'SLA due-soon and all four overdue/escalation OLA alerts are persisted');
    $maintenanceSecond = $runMaintenance();
    $slaAlertsAfter = (int) $pdo->query('SELECT COUNT(*) FROM ticket_sla_alerts WHERE ticket_id = ' . $maintenanceTicketId)->fetchColumn();
    $olaAlertsAfter = (int) $pdo->query('SELECT COUNT(*) FROM ticket_ola_alerts WHERE ticket_id = ' . $maintenanceTicketId)->fetchColumn();
    smoke_check($maintenanceSecond['exit'] === 0 && $slaAlertsAfter === $slaAlerts && $olaAlertsAfter === $olaAlerts, 'repeated SLA/OLA maintenance is idempotent and emits no duplicate alert rows');
    $slaEvents = $pdo->prepare("SELECT COUNT(*) FROM ticket_events WHERE ticket_id = ? AND event_type IN ('sla_alert','ola_alert')");
    $slaEvents->execute([$maintenanceTicketId]);
    smoke_check((int) $slaEvents->fetchColumn() === 5, 'SLA/OLA alert events are recorded exactly once for the ticket');
    $maintenanceDetail = smoke_http('http://' . $address . '/request-center.php?page=ticket&id=' . $maintenanceTicketId, ['Cookie: ' . $cookie]);
    if ($maintenanceDetail['status'] !== 200 || !str_contains($maintenanceDetail['body'], 'هشدار SLA ثبت شد') || !str_contains($maintenanceDetail['body'], 'هشدار OLA ثبت شد')) {
        $visibleEventRows = $pdo->prepare("SELECT event_type, ticket_id, user_id FROM ticket_events WHERE ticket_id = ? AND event_type IN ('sla_alert','ola_alert') ORDER BY id");
        $visibleEventRows->execute([$maintenanceTicketId]);
        echo 'detail event diagnostic: rows=' . json_encode($visibleEventRows->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE)
            . ' canary=' . (str_contains($maintenanceDetail['body'], 'ردگیری و سوابق انجام کار') ? 'event-section-present' : 'event-section-missing')
            . ' sla-label=' . (str_contains($maintenanceDetail['body'], 'هشدار SLA ثبت شد') ? 'yes' : 'no')
            . ' ola-label=' . (str_contains($maintenanceDetail['body'], 'هشدار OLA ثبت شد') ? 'yes' : 'no')
            . ' body-tail=' . substr($maintenanceDetail['body'], -5000) . PHP_EOL;
    }
    smoke_check($maintenanceDetail['status'] === 200 && str_contains($maintenanceDetail['body'], 'هشدار SLA ثبت شد') && str_contains($maintenanceDetail['body'], 'هشدار OLA ثبت شد'), 'independent detail renders SLA/OLA event metadata without Logging UI dependencies (status ' . $maintenanceDetail['status'] . ')');
    $maintenanceNotifications = $pdo->prepare("SELECT COUNT(DISTINCT notification_type) FROM notifications WHERE ticket_id = ? AND notification_type IN ('sla_due_soon','sla_overdue','ola_response_overdue','ola_resolution_overdue','ola_escalation_1','ola_escalation_2')");
    $maintenanceNotifications->execute([$maintenanceTicketId]);
    $notificationTypeCount = (int) $maintenanceNotifications->fetchColumn();
    if ($notificationTypeCount !== 5) {
        $notificationRows = $pdo->query('SELECT user_id, notification_type FROM notifications WHERE ticket_id = ' . $maintenanceTicketId . ' ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        $ticketRecipients = $pdo->query('SELECT requester_id, assigned_to, department_id, service_group FROM tickets WHERE id = ' . $maintenanceTicketId)->fetch(PDO::FETCH_ASSOC);
        $assigneeInfo = $pdo->query('SELECT id, role, is_active, is_primary_admin FROM users WHERE id = ' . $maintenanceAssignee)->fetch(PDO::FETCH_ASSOC);
        echo 'SLA/OLA notification diagnostic: expectedTypes=5 actual=' . $notificationTypeCount
            . ' rows=' . json_encode($notificationRows) . ' ticket=' . json_encode($ticketRecipients)
            . ' assignee=' . json_encode($assigneeInfo) . PHP_EOL;
    }
    smoke_check($notificationTypeCount === 5, 'SLA/OLA notifications reach the assigned staff member exactly by alert type');

    // Inject a notification persistence failure. Alert claims, ticket events and
    // audit rows must roll back together so the next maintenance run can retry.
    $pdo->exec('DELETE FROM ticket_sla_alerts WHERE ticket_id = ' . $maintenanceTicketId);
    $pdo->exec('DELETE FROM ticket_ola_alerts WHERE ticket_id = ' . $maintenanceTicketId);
    $pdo->exec("DELETE FROM ticket_events WHERE ticket_id = " . $maintenanceTicketId . " AND event_type IN ('sla_alert','ola_alert')");
    $pdo->exec("DELETE FROM notifications WHERE ticket_id = " . $maintenanceTicketId . " AND notification_type IN ('sla_due_soon','sla_overdue','ola_response_overdue','ola_resolution_overdue','ola_escalation_1','ola_escalation_2')");
    $pdo->exec('CREATE TRIGGER rc_smoke_fail_notification_insert BEFORE INSERT ON notifications FOR EACH ROW BEGIN IF NEW.ticket_id = '
        . $maintenanceTicketId . " THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RC smoke injected notification outage'; END IF; END");
    $maintenanceFailure = $runMaintenance();
    $failedClaims = (int) $pdo->query('SELECT (SELECT COUNT(*) FROM ticket_sla_alerts WHERE ticket_id = ' . $maintenanceTicketId . ') + (SELECT COUNT(*) FROM ticket_ola_alerts WHERE ticket_id = ' . $maintenanceTicketId . ')')->fetchColumn();
    $failedEvents = (int) $pdo->query("SELECT COUNT(*) FROM ticket_events WHERE ticket_id = " . $maintenanceTicketId . " AND event_type IN ('sla_alert','ola_alert')")->fetchColumn();
    $failedNotifications = (int) $pdo->query("SELECT COUNT(*) FROM notifications WHERE ticket_id = " . $maintenanceTicketId . " AND notification_type IN ('sla_due_soon','sla_overdue','ola_response_overdue','ola_resolution_overdue','ola_escalation_1','ola_escalation_2')")->fetchColumn();
    $pdo->exec('DROP TRIGGER rc_smoke_fail_notification_insert');
    smoke_check($maintenanceFailure['exit'] === 0 && $failedClaims === 0 && $failedEvents === 0 && $failedNotifications === 0,
        'notification outage rolls back SLA/OLA alert claims and events instead of losing a retry');
    $maintenanceRetry = $runMaintenance();
    $retryClaims = (int) $pdo->query('SELECT (SELECT COUNT(*) FROM ticket_sla_alerts WHERE ticket_id = ' . $maintenanceTicketId . ') + (SELECT COUNT(*) FROM ticket_ola_alerts WHERE ticket_id = ' . $maintenanceTicketId . ')')->fetchColumn();
    $retryNotifications = (int) $pdo->query("SELECT COUNT(DISTINCT notification_type) FROM notifications WHERE ticket_id = " . $maintenanceTicketId . " AND notification_type IN ('sla_due_soon','sla_overdue','ola_response_overdue','ola_resolution_overdue','ola_escalation_1','ola_escalation_2')")->fetchColumn();
    smoke_check($maintenanceRetry['exit'] === 0 && $retryClaims === 5 && $retryNotifications === 5,
        'SLA/OLA maintenance retries and delivers all five alert types after notification storage recovers');

    // Automatic close must not commit a terminal transition if its required
    // requester/staff notifications cannot be persisted; the next run retries.
    $pdo->prepare('UPDATE tickets SET status = "resolved", resolved_at = DATE_SUB(NOW(), INTERVAL 30 DAY), updated_at = DATE_SUB(NOW(), INTERVAL 30 DAY), cancel_requested_at = NULL, assigned_to = ? WHERE id = ?')
        ->execute([$maintenanceAssignee, $maintenanceTicketId]);
    $pdo->exec("DELETE FROM ticket_events WHERE ticket_id = " . $maintenanceTicketId . " AND event_type = 'auto_closed'");
    $pdo->exec("DELETE FROM notifications WHERE ticket_id = " . $maintenanceTicketId . " AND notification_type = 'ticket_closed'");
    $runAutoClose = static function () use ($root, $serverEnv): array {
        $pipes = [];
        $process = proc_open([PHP_BINARY, $root . '/cron/ticket_auto_close.php'], [
            0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
        ], $pipes, $root, $serverEnv);
        if (!is_resource($process)) {
            throw new RuntimeException('Could not start automatic ticket-close process.');
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return ['exit' => proc_close($process), 'stdout' => (string) $stdout, 'stderr' => (string) $stderr];
    };
    $pdo->exec('CREATE TRIGGER rc_smoke_fail_auto_close_notification BEFORE INSERT ON notifications FOR EACH ROW BEGIN IF NEW.ticket_id = '
        . $maintenanceTicketId . " AND NEW.notification_type = 'ticket_closed' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RC smoke injected auto-close notification outage'; END IF; END");
    $autoCloseFailure = $runAutoClose();
    $statusAfterAutoCloseFailure = (string) $pdo->query('SELECT status FROM tickets WHERE id = ' . $maintenanceTicketId)->fetchColumn();
    $autoCloseEventsAfterFailure = (int) $pdo->query("SELECT COUNT(*) FROM ticket_events WHERE ticket_id = " . $maintenanceTicketId . " AND event_type = 'auto_closed'")->fetchColumn();
    $autoCloseNotificationsAfterFailure = (int) $pdo->query("SELECT COUNT(*) FROM notifications WHERE ticket_id = " . $maintenanceTicketId . " AND notification_type = 'ticket_closed'")->fetchColumn();
    $pdo->exec('DROP TRIGGER rc_smoke_fail_auto_close_notification');
    smoke_check($autoCloseFailure['exit'] === 0 && $statusAfterAutoCloseFailure === 'resolved'
        && $autoCloseEventsAfterFailure === 0 && $autoCloseNotificationsAfterFailure === 0,
        'automatic close rolls back its status and audit event when required notification persistence fails');
    $autoCloseRetry = $runAutoClose();
    $statusAfterAutoCloseRetry = (string) $pdo->query('SELECT status FROM tickets WHERE id = ' . $maintenanceTicketId)->fetchColumn();
    $autoCloseEventsAfterRetry = (int) $pdo->query("SELECT COUNT(*) FROM ticket_events WHERE ticket_id = " . $maintenanceTicketId . " AND event_type = 'auto_closed'")->fetchColumn();
    $autoCloseNotificationsAfterRetry = (int) $pdo->query("SELECT COUNT(*) FROM notifications WHERE ticket_id = " . $maintenanceTicketId . " AND notification_type = 'ticket_closed'")->fetchColumn();
    smoke_check($autoCloseRetry['exit'] === 0 && $statusAfterAutoCloseRetry === 'closed'
        && $autoCloseEventsAfterRetry === 1 && $autoCloseNotificationsAfterRetry >= 2,
        'automatic close retries after notification storage recovers and persists requester plus staff notices');

    // 8) Phase 3 — a non-RC POST action is forwarded in-process to the front controller (no payload loss, no 419).
    $forwarded = smoke_http_post('http://' . $address . '/request-center.php', ['action' => 'zz_not_an_action', 'csrf' => (string) $session['csrf']], ['Cookie: ' . $cookie]);
    smoke_check($forwarded['status'] === 200 && str_contains($forwarded['body'], 'داشبورد'), 'non-RC POST action is forwarded in-process and renders the dashboard (status ' . $forwarded['status'] . ')');

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
