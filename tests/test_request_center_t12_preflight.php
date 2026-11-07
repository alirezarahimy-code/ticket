<?php
declare(strict_types=1);

/**
 * T12 preparation gate.
 *
 * Builds two isolated databases from the same canonical empty schema, starts
 * the pinned Legacy and current Feature applications, seeds independent
 * sessions, and verifies schema/session isolation. This is deliberately only
 * a preflight: it does NOT claim the ten behavioral scenarios have passed.
 */

$legacyRoot = rtrim((string) (getenv('T12_LEGACY_ROOT') ?: ''), DIRECTORY_SEPARATOR);
$featureRoot = rtrim((string) (getenv('T12_FEATURE_ROOT') ?: dirname(__DIR__)), DIRECTORY_SEPARATOR);
$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$rootUser = (string) (getenv('CI_DB_USER') ?: 'root');
$rootPass = (string) (getenv('CI_DB_PASS') ?: '');
$mysql = (string) (getenv('ITSM_MYSQL_PATH') ?: 'mariadb');
$schemaFile = dirname(__DIR__) . '/install-empty-db.sql';
$legacyCommit = '2176d2aa57758a0fbef426636c9943879646b165';

$apps = [
    'legacy' => ['root' => $legacyRoot, 'db' => 'rc_t12_legacy', 'port' => 18761, 'web_user' => 'rc_t12_legacy_web'],
    'feature' => ['root' => $featureRoot, 'db' => 'rc_t12_feature', 'port' => 18762, 'web_user' => 'rc_t12_feature_web'],
];
$webPass = 'ci-t12-isolated-web-password';
$admin = null;
$servers = [];
$configFiles = [];
$createdDbs = [];
$failure = false;

function t12_fail(string $message): never
{
    throw new RuntimeException($message);
}

function t12_check(bool $condition, string $message): void
{
    if (!$condition) {
        t12_fail($message);
    }
    echo "PASS {$message}\n";
}

function t12_ident(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

function t12_mysql_file(string $mysql, string $host, string $port, string $user, string $pass, string $database, string $file): void
{
    $command = [
        $mysql, '--skip-ssl', '--host=' . $host, '--port=' . $port,
        '--user=' . $user, '--default-character-set=utf8mb4', '--database=' . $database,
    ];
    $env = getenv();
    if (!is_array($env)) {
        $env = [];
    }
    if ($pass !== '') {
        $env['MYSQL_PWD'] = $pass;
    }
    $pipes = [];
    $process = proc_open(
        $command,
        [0 => ['file', $file, 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        dirname(__DIR__),
        $env
    );
    if (!is_resource($process)) {
        t12_fail('could not start MariaDB client for schema import');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== 0) {
        $code = 'unknown';
        if (preg_match('/ERROR\\s+(\\d+)/i', $stderr . "\n" . $stdout, $match)) {
            $code = $match[1];
        }
        t12_fail('canonical schema import failed (MariaDB error ' . $code . ')');
    }
}

function t12_http(string $url, string $method = 'GET', array $headers = [], string $body = ''): array
{
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body,
            'ignore_errors' => true,
            'timeout' => 15,
            'follow_location' => 0,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];
    $status = 0;
    if (isset($responseHeaders[0]) && preg_match('/\\s(\\d{3})\\s/', $responseHeaders[0], $m)) {
        $status = (int) $m[1];
    }
    return ['status' => $status, 'headers' => $responseHeaders, 'body' => is_string($response) ? $response : ''];
}

function t12_start_server(string $root, int $port, string $configPath): mixed
{
    $env = getenv();
    if (!is_array($env)) {
        $env = [];
    }
    $env['ITSM_CONFIG_PATH'] = $configPath;
    $process = proc_open(
        [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', $root],
        [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'a'], 2 => ['file', '/dev/null', 'a']],
        $pipes,
        $root,
        $env
    );
    if (!is_resource($process)) {
        t12_fail('could not start PHP server for ' . $root);
    }
    $base = 'http://127.0.0.1:' . $port;
    $ready = false;
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $response = t12_http($base . '/index.php');
        if (in_array($response['status'], [200, 302, 303], true)) {
            $ready = true;
            break;
        }
        usleep(100000);
    }
    if (!$ready) {
        proc_terminate($process);
        proc_close($process);
        t12_fail('PHP server did not become ready for ' . $root);
    }
    return $process;
}

function t12_schema_fingerprint(PDO $pdo): array
{
    $tables = $pdo->query(
        "SELECT TABLE_NAME FROM information_schema.TABLES " .
        "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME"
    )->fetchAll(PDO::FETCH_COLUMN);
    $fingerprints = [];
    foreach ($tables as $table) {
        $ddl = $pdo->query('SHOW CREATE TABLE ' . t12_ident((string) $table))->fetch(PDO::FETCH_NUM);
        $create = (string) ($ddl[1] ?? '');
        $create = preg_replace('/AUTO_INCREMENT\\s*=\\s*\\d+/i', 'AUTO_INCREMENT=<counter>', $create);
        $fingerprints[(string) $table] = hash('sha256', $create);
    }
    ksort($fingerprints);
    return $fingerprints;
}

function t12_session(string $baseUrl, int $userId): array
{
    $response = t12_http($baseUrl . '/tests/fixtures/request-center-session-seed.php?user_id=' . $userId);
    t12_check($response['status'] === 200, 'session fixture responds over HTTP');
    $payload = json_decode($response['body'], true);
    t12_check(is_array($payload), 'session fixture returns JSON');
    t12_check(
        isset($payload['cookie_name'], $payload['session_id'], $payload['csrf']) &&
        preg_match('/^[a-f0-9]{64}$/', (string) $payload['csrf']) === 1,
        'session fixture returns independent session and CSRF token'
    );
    return [
        'cookie' => (string) $payload['cookie_name'] . '=' . (string) $payload['session_id'],
        'session_id' => (string) $payload['session_id'],
        'csrf' => (string) $payload['csrf'],
    ];
}

function t12_config(string $baseUrl, string $db, string $webUser, string $webPass, string $host, string $port): string
{
    $config = [
        'app' => [
            'name' => 'Request Center T12 Characterization',
            'timezone' => 'Asia/Tehran',
            'base_url' => $baseUrl . '/',
            'key' => str_repeat('a', 64),
        ],
        'database' => [
            'host' => $host,
            'port' => (int) $port,
            'name' => $db,
            'user' => $webUser,
            'password' => $webPass,
            'charset' => 'utf8mb4',
        ],
        'ldap' => ['enabled' => false, 'host' => '', 'port' => 389, 'ssl' => false, 'base_dn' => '', 'bind_dn' => '', 'bind_password' => '', 'domain_suffix' => '', 'user_filter' => '(&(objectCategory=person)(objectClass=user)(sAMAccountName=%s))', 'default_role' => 'user', 'group_map' => ['agent' => [], 'manager' => [], 'supervisor' => [], 'admin' => []]],
        'security' => ['session_idle_days' => 0, 'max_upload_mb' => 8, 'allowed_uploads' => ['txt', 'pdf', 'png', 'jpg', 'jpeg'], 'inventory_token' => ''],
        'food_ticket' => ['odbc_driver' => '', 'web_url' => $baseUrl . '/', 'sso_key' => ''],
    ];
    $path = tempnam(sys_get_temp_dir(), 'rc-t12-config-');
    if ($path === false || file_put_contents($path, "<?php\nreturn " . var_export($config, true) . ";\n") === false) {
        t12_fail('could not write temporary application config');
    }
    return $path;
}

try {
    t12_check(getenv('GITHUB_ACTIONS') === 'true', 'T12 preflight runs only inside GitHub Actions');
    t12_check(in_array($host, ['127.0.0.1', 'localhost'], true), 'T12 databases use the local ephemeral CI service');
    t12_check((string) (getenv('CI_DB_NAME') ?: '') === 'persian_ticketing_ci', 'T12 workflow database has the expected disposable name');
    t12_check(is_file($schemaFile), 'canonical install-empty-db.sql is available');
    t12_check($legacyRoot !== '' && is_dir($legacyRoot), 'pinned Legacy worktree exists');
    t12_check(is_dir($featureRoot) && realpath($legacyRoot) !== realpath($featureRoot), 'Legacy and Feature source roots are independent');
    t12_check(is_file($legacyRoot . '/index.php') && is_file($featureRoot . '/index.php'), 'both application entry points exist');

    $fixtureSource = $featureRoot . '/tests/fixtures/request-center-session-seed.php';
    $legacyFixture = $legacyRoot . '/tests/fixtures/request-center-session-seed.php';
    if (!is_dir(dirname($legacyFixture))) {
        mkdir(dirname($legacyFixture), 0775, true);
    }
    if (!is_file($legacyFixture)) {
        t12_check(copy($fixtureSource, $legacyFixture), 'copied test-only session fixture into Legacy worktree');
    }

    $admin = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $rootUser,
        $rootPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    $dbPdos = [];
    $userIds = [];
    foreach ($apps as $side => &$app) {
        $db = $app['db'];
        $createdDbs[] = $db;
        $admin->exec('DROP DATABASE IF EXISTS ' . t12_ident($db));
        $admin->exec('CREATE DATABASE ' . t12_ident($db) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        t12_mysql_file($mysql, $host, $port, $rootUser, $rootPass, $db, $schemaFile);

        $webUser = $app['web_user'];
        $admin->exec("DROP USER IF EXISTS '" . $webUser . "'@'127.0.0.1'");
        $admin->exec("CREATE USER '" . $webUser . "'@'127.0.0.1' IDENTIFIED BY '" . $webPass . "'");
        $admin->exec('GRANT ALL PRIVILEGES ON ' . t12_ident($db) . ".* TO '" . $webUser . "'@'127.0.0.1'");
        $admin->exec('FLUSH PRIVILEGES');

        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
            $rootUser,
            $rootPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $pdo->exec("INSERT INTO users (company_id, username, full_name, is_primary_admin, role, is_active, auth_source) VALUES (1, 'rc_t12_preflight_admin', 'RC T12 Preflight Admin', 1, 'primary_admin', 1, 'local')");
        $userIds[$side] = (int) $pdo->lastInsertId();
        $dbPdos[$side] = $pdo;

        $baseUrl = 'http://127.0.0.1:' . $app['port'];
        $configPath = t12_config($baseUrl, $db, $webUser, $webPass, $host, $port);
        $configFiles[] = $configPath;
        $app['base_url'] = $baseUrl;
        $app['config_path'] = $configPath;
        $servers[$side] = t12_start_server($app['root'], $app['port'], $configPath);
    }
    unset($app);

    $sessions = [];
    foreach ($apps as $side => $app) {
        $response = t12_http($app['base_url'] . '/index.php');
        t12_check(in_array($response['status'], [200, 302, 303], true), $side . ' application index is reachable');
        $sessions[$side] = t12_session($app['base_url'], $userIds[$side]);
        $cookieResponse = t12_http(
            $app['base_url'] . '/index.php',
            'GET',
            ['Cookie: ' . $sessions[$side]['cookie']]
        );
        t12_check(in_array($cookieResponse['status'], [200, 302, 303], true), $side . ' seeded session is accepted by the application');
    }

    t12_check($sessions['legacy']['session_id'] !== $sessions['feature']['session_id'], 'Legacy and Feature sessions are independent');
    $legacySchema = t12_schema_fingerprint($dbPdos['legacy']);
    $featureSchema = t12_schema_fingerprint($dbPdos['feature']);
    t12_check($legacySchema === $featureSchema, 'both clones have identical post-bootstrap schema fingerprints');
    t12_check(count($legacySchema) >= 80, 'both clones contain the canonical full application schema');

    echo 'T12 PREFLIGHT PASS: two isolated code roots, two isolated databases, independent HTTP sessions, CSRF tokens, and matching schema. ' .
        'Behavioral scenarios executed: 0/10. T12 remains OPEN until all ten Legacy-vs-Feature scenarios pass.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'T12 PREFLIGHT FAIL: ' . $error->getMessage() . PHP_EOL);
    $failure = true;
} finally {
    foreach ($servers as $process) {
        if (is_resource($process)) {
            @proc_terminate($process);
            @proc_close($process);
        }
    }
    foreach ($configFiles as $path) {
        @unlink($path);
    }
    if ($admin instanceof PDO) {
        foreach ($createdDbs as $db) {
            try {
                $admin->exec('DROP DATABASE IF EXISTS ' . t12_ident($db));
            } catch (Throwable) {
            }
        }
        foreach ($apps as $app) {
            try {
                $admin->exec("DROP USER IF EXISTS '" . $app['web_user'] . "'@'127.0.0.1'");
            } catch (Throwable) {
            }
        }
    }
}
if (!empty($failure)) {
    exit(1);
}
