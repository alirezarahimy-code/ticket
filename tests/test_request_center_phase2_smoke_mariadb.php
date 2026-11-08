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
