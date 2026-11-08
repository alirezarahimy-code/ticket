<?php
declare(strict_types=1);

/**
 * T12 characterization runner: eleven behavioral scenarios for the Request Center
 * lifecycle. Ten compare the pinned Legacy worktree with the Feature branch;
 * one feature-only MariaDB HTTP scenario covers the new supervisor assignment action.
 *
 * THIS IS NOT A PASS GATE YET. Every scenario must run, every difference must
 * be recorded and every FAIL must be dispositioned (matching the sheet).
 * Runs ONLY inside GitHub Actions (GITHUB_ACTIONS=true), against disposable
 * databases rebuilt from install-empty-db.sql. No operational data is touched.
 */

$legacyRoot = rtrim((string) (getenv('T12_LEGACY_ROOT') ?: ''), DIRECTORY_SEPARATOR);
$featureRoot = rtrim((string) (getenv('T12_FEATURE_ROOT') ?: dirname(__DIR__)), DIRECTORY_SEPARATOR);
$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$rootUser = (string) (getenv('CI_DB_USER') ?: 'root');
$rootPass = (string) (getenv('CI_DB_PASS') ?: '');
$mysql = (string) (getenv('ITSM_MYSQL_PATH') ?: 'mariadb');
$schemaFile = dirname(__DIR__) . '/install-empty-db.sql';
$scenarioFilter = array_filter(array_map('intval', explode(',', (string) (getenv('T12_SCENARIOS') ?: '1,2,3,4,5,6,7,8,9,10,11,12,13'))));
$evidenceDir = (string) (getenv('T12_EVIDENCE_DIR') ?: (sys_get_temp_dir() . '/t12-evidence'));

$portBase = 18100 + (getmypid() % 4000);
$apps = [
    'legacy' => ['root' => $legacyRoot, 'db' => 'rc_t12s_legacy', 'port' => $portBase, 'web_user' => 'rc_t12s_legacy_web'],
    'feature' => ['root' => $featureRoot, 'db' => 'rc_t12s_feature', 'port' => $portBase + 1, 'web_user' => 'rc_t12s_feature_web'],
];
$webPass = 'ci-t12-isolated-web-password';
$results = [];
$servers = [];
$configFiles = [];

function t12s_fail(string $m): never { throw new RuntimeException($m); }
function t12s_check(bool $c, string $m): void { if (!$c) { t12s_fail($m); } }
function t12s_ident(string $n): string { return '`' . str_replace('`', '``', $n) . '`'; }
function t12s_log(string $m): void { fwrite(STDOUT, $m . PHP_EOL); }

function t12s_mysql_file(string $mysql, string $host, string $port, string $user, string $pass, string $database, string $file): void
{
    $cmd = [$mysql, '--skip-ssl', '--host=' . $host, '--port=' . $port, '--user=' . $user,
        '--default-character-set=utf8mb4', '--database=' . $database];
    $env = getenv();
    if (!is_array($env)) { $env = []; }
    if ($pass !== '') { $env['MYSQL_PWD'] = $pass; }
    $pipes = [];
    $p = proc_open($cmd, [0 => ['file', $file, 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__), $env);
    t12s_check(is_resource($p), 'mysql client start');
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    $code = proc_close($p);
    t12s_check($code === 0, 'schema import failed: ' . substr((string) $err, 0, 300));
}

function t12s_http(string $url, string $method = 'GET', array $headers = [], string $body = ''): array
{
    $ctx = stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", $headers),
        'content' => $body, 'ignore_errors' => true, 'timeout' => 20, 'follow_location' => 0]]);
    $resp = @file_get_contents($url, false, $ctx);
    $h = $http_response_header ?? [];
    $status = isset($h[0]) && preg_match('/\s(\d{3})\s/', $h[0], $m) ? (int) $m[1] : 0;
    return ['status' => $status, 'headers' => $h, 'body' => is_string($resp) ? $resp : ''];
}

function t12s_start_server(string $root, int $port, string $configPath): mixed
{
    $env = getenv();
    if (!is_array($env)) { $env = []; }
    $env['ITSM_CONFIG_PATH'] = $configPath;
    $logFile = sys_get_temp_dir() . '/t12s-server-' . $port . '.log';
    @unlink($logFile);
    $p = proc_open([PHP_BINARY, '-d', 'display_errors=1', '-d', 'log_errors=1', '-d', 'error_log=' . $logFile,
        '-S', '127.0.0.1:' . $port, '-t', $root],
        [0 => ['file', '/dev/null', 'r'], 1 => ['file', $logFile, 'a'], 2 => ['file', $logFile, 'a']],
        $pipes, $root, $env);
    t12s_check(is_resource($p), 'start PHP server for ' . $root);
    $base = 'http://127.0.0.1:' . $port;
    for ($i = 0; $i < 120; $i++) {
        $r = t12s_http($base . '/index.php');
        if (in_array($r['status'], [200, 302, 303], true)) { return $p; }
        usleep(150000);
    }
    $tail = is_file($logFile) ? substr((string) file_get_contents($logFile), -1200) : '(no server log)';
    proc_terminate($p);
    t12s_fail('PHP server not ready for ' . $root . ' — server log tail: ' . preg_replace('/\s+/', ' ', $tail));
}

function t12s_config(string $baseUrl, string $db, string $webUser, string $webPass, string $host, string $port): string
{
    $config = [
        'app' => ['name' => 'Request Center T12 Scenarios', 'timezone' => 'Asia/Tehran', 'base_url' => $baseUrl . '/', 'key' => str_repeat('b', 64)],
        'database' => ['host' => $host, 'port' => (int) $port, 'name' => $db, 'user' => $webUser, 'password' => $webPass, 'charset' => 'utf8mb4'],
        'ldap' => ['enabled' => false, 'host' => '', 'port' => 389, 'ssl' => false, 'base_dn' => '', 'bind_dn' => '', 'bind_password' => '', 'domain_suffix' => '', 'user_filter' => '', 'default_role' => 'user', 'group_map' => []],
        'security' => ['session_idle_days' => 0, 'max_upload_mb' => 8, 'allowed_uploads' => ['txt', 'pdf'], 'inventory_token' => ''],
        'food_ticket' => ['odbc_driver' => '', 'web_url' => $baseUrl . '/', 'sso_key' => ''],
    ];
    $path = tempnam(sys_get_temp_dir(), 'rc-t12s-cfg-');
    file_put_contents($path, "<?php\nreturn " . var_export($config, true) . ";\n");
    return $path;
}

function t12s_db(string $rootUser, string $rootPass, string $host, string $port, string $db): PDO
{
    return new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $rootUser, $rootPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
}

/** Deterministic synthetic fixtures, identical on both clones. */
function t12s_seed(PDO $pdo): void
{
    $pdo->exec("DELETE FROM org_units"); // no deputy selection requirement in this characterization
    $pdo->exec("INSERT INTO handling_units (id, code, name, is_active) VALUES (1, 'it', 'واحد فناوری', 1), (2, 'support', 'واحد پشتیبانی', 1)");
    $pdo->exec("INSERT INTO departments (id, name, code, is_active) VALUES (1, 'پشتیبانی کل', 'sup', 1), (2, 'فناوری اطلاعات', 'it', 1)");
    $pdo->exec("INSERT INTO categories (id, name, code, service_group, is_active) VALUES
        (1, 'درخواست عمومی', 'gen', 'support', 1),
        (2, 'خدمات فناوری', 'it-gen', 'it', 1)");
    $pdo->exec("INSERT INTO service_catalog (id, name, code, service_group, default_priority, department_id, category_id, handling_unit_id, requires_asset, default_ticket_type, is_active)
        VALUES
        (1, 'خدمت عمومی T12', 't12-support', 'support', 'normal', 1, 1, 2, 0, 'request', 1),
        (2, 'خدمت فناوری T12', 't12-it', 'it', 'normal', 2, 2, 1, 0, 'request', 1)");
    $pdo->exec("INSERT INTO users (id, company_id, username, full_name, role, is_active, auth_source, department_id, handling_unit_id, is_primary_admin, is_it_agent, password_hash)
        VALUES
        (100, 1, 'rc_t12s_admin', 'RC T12s Admin', 'primary_admin', 1, 'local', NULL, NULL, 1, 0, 'x'),
        (101, 1, 'rc_t12s_requester', 'RC T12s Requester', 'user', 1, 'local', 1, NULL, 0, 0, 'x'),
        (102, 1, 'rc_t12s_agent', 'RC T12s Agent', 'agent', 1, 'local', 1, 2, 0, 0, 'x'),
        (103, 1, 'rc_t12s_agent2', 'RC T12s Agent Two', 'agent', 1, 'local', 1, 2, 0, 0, 'x'),
        (104, 1, 'rc_t12s_supmgr', 'RC T12s Support Manager', 'support_manager', 1, 'local', 1, 2, 0, 0, 'x'),
        (105, 1, 'rc_t12s_supervisor', 'RC T12s Supervisor', 'supervisor', 1, 'local', NULL, NULL, 0, 0, 'x'),
        (106, 1, 'rc_t12s_it_agent', 'RC T12s IT Agent', 'agent', 1, 'local', 2, 1, 0, 1, 'x')
        ON DUPLICATE KEY UPDATE username = username");
    $pdo->exec("INSERT INTO assets (id, asset_tag, hostname, owner_user_id, department_id) VALUES (1, 'T12-IT-01', 't12-it-01', 101, 2)");
}

/** One-time setup: dedicated web users + grants (persist across DB rebuilds). */
function t12s_web_users(string $rootUser, string $rootPass, string $host, string $port, array $apps): void
{
    $admin = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $rootUser, $rootPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    foreach ($apps as $app) {
        $u = $app['web_user'];
        $admin->exec("DROP USER IF EXISTS '" . $u . "'@'%'");
        $admin->exec("CREATE USER '" . $u . "'@'%' IDENTIFIED BY '" . 'ci-t12s-web-pass' . "'");
        $admin->exec('GRANT ALL PRIVILEGES ON ' . t12s_ident($app['db']) . ".* TO '" . $u . "'@'%'");
    }
    $admin->exec('FLUSH PRIVILEGES');
}

/** Recreate $db from the canonical schema, import, seed users fixtures (admin per clone + common fixture SQL). */
function t12s_rebuild(string $adminPdo, string $rootUser, string $rootPass, string $host, string $port, string $db, string $schemaFile, string $mysql): void
{
    // drop/create needs CREATE/DROP rights — admin PDO passed as string marker
    $admin = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $rootUser, $rootPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $admin->exec('DROP DATABASE IF EXISTS ' . t12s_ident($db));
    $admin->exec('CREATE DATABASE ' . t12s_ident($db) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    t12s_mysql_file($mysql, $host, $port, $rootUser, $rootPass, $db, $schemaFile);
}

function t12s_session(string $baseUrl, int $userId, string $expectedDb): array
{
    $r = t12s_http($baseUrl . '/tests/fixtures/request-center-t12-session-seed.php?user_id=' . $userId);
    t12s_check($r['status'] === 200, 'session fixture HTTP 200 (got ' . $r['status'] . ')');
    $p = json_decode($r['body'], true);
    t12s_check(is_array($p) && (string) ($p['database_name'] ?? '') === $expectedDb, 'session fixture on own database');
    return ['cookie' => (string) $p['cookie_name'] . '=' . (string) $p['session_id'], 'csrf' => (string) $p['csrf'], 'session_id' => (string) $p['session_id']];
}

function t12s_post(string $baseUrl, array $session, string $action, array $fields = []): array
{
    $fields['action'] = $action;
    $fields['csrf'] = $session['csrf'];
    $body = http_build_query($fields, '', '&');
    return t12s_http($baseUrl, 'POST', ['Cookie: ' . $session['cookie'], 'Content-Type: application/x-www-form-urlencoded'], $body);
}

/** Snapshot tickets matching a where clause; timestamps tolerated within 300s, rest strict. */
function t12s_snapshot_tickets(PDO $pdo, string $where = '1=1'): array
{
    $stmt = $pdo->query("SELECT * FROM tickets WHERE {$where} ORDER BY id");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $t) {
        $out[] = [
            'ticket_no' => (string) ($t['ticket_no'] ?? ''),
            'status' => (string) $t['status'],
            'requester_id' => (int) $t['requester_id'],
            'assigned_to' => $t['assigned_to'] !== null ? (int) $t['assigned_to'] : null,
            'priority' => (string) $t['priority'],
            'department_id' => isset($t['department_id']) ? (int) $t['department_id'] : null,
            'service_group' => (string) ($t['service_group'] ?? ''),
            'category_id' => isset($t['category_id']) ? (int) ($t['category_id'] ?? 0) : null,
            'service_id' => isset($t['service_id']) ? (int) ($t['service_id'] ?? 0) : null,
            'sla_minutes' => isset($t['sla_minutes']) ? (int) $t['sla_minutes'] : null,
            'sla_pause_minutes' => isset($t['sla_pause_minutes']) ? (int) $t['sla_pause_minutes'] : null,
            'ola_policy_id' => isset($t['ola_policy_id']) ? (int) ($t['ola_policy_id'] ?? 0) : null,
            'cancel_requested_by' => isset($t['cancel_requested_by']) ? (int) ($t['cancel_requested_by'] ?? 0) : null,
            'ts' => array_map(fn($k) => $t[$k], ['created_at', 'updated_at', 'assigned_at', 'first_response_at',
                'resolved_at', 'closed_at', 'due_at', 'sla_paused_at', 'ola_response_due_at', 'ola_due_at',
                'ola_escalation_1_at', 'ola_escalation_2_at', 'cancel_requested_at']),
        ];
    }
    return $out;
}

function t12s_snapshot_table(PDO $pdo, string $table, array $cols, string $where = '1=1'): array
{
    $colsSql = implode(', ', $cols);
    $stmt = $pdo->query("SELECT {$colsSql} FROM {$table} WHERE {$where} ORDER BY id");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$row) {
        foreach ($row as $k => $v) {
            if ($k === 'body') { $row[$k] = 'sha1:' . sha1((string) $v); }
        }
    }
    unset($row);
    return $rows;
}

function t12s_snapshot(PDO $pdo, string $ticketWhere = '1=1'): array
{
    $tickets = t12s_snapshot_tickets($pdo, $ticketWhere);
    $ids = [];
    foreach ($tickets as $t) { /* ticket id intentionally not exported; use attach by where clause */ }
    return [
        'tickets' => $tickets,
        'messages' => t12s_snapshot_table($pdo, 'ticket_messages', ['user_id', 'is_internal', 'body']),
        'events' => t12s_snapshot_table($pdo, 'ticket_events', ['event_type', 'from_status', 'to_status']),
        'notifications' => t12s_snapshot_table($pdo, 'notifications', ['user_id', 'notification_type']),
        'ratings' => t12s_snapshot_table($pdo, 'ticket_ratings', ['requester_id', 'agent_id', 'score']),
        'activity_logs' => t12s_snapshot_table($pdo, 'activity_logs', ['action_code']),
    ];
}

function t12s_ts_diff_tol(string $a, string $b, int $tol = 300): bool
{
    $ta = strtotime($a); $tb = strtotime($b);
    if ($ta === false || $tb === false) { return $a === $b; }
    return abs($ta - $tb) <= $tol;
}

function t12s_compare(array $a, array $b, string $scenario = ''): array
{
    $diffs = [];
    $tolerated = [];
    // tickets: same count, per row strict minus tol keys
    if (count($a['tickets']) !== count($b['tickets'])) {
        $diffs[] = 'tickets count differs: legacy=' . count($a['tickets']) . ' feature=' . count($b['tickets']);
    } else {
        foreach ($a['tickets'] as $i => $ta) {
            $tb = $b['tickets'][$i];
            foreach (['ticket_no', 'status', 'requester_id', 'assigned_to', 'priority', 'department_id', 'service_group', 'category_id', 'service_id', 'sla_minutes', 'sla_pause_minutes', 'ola_policy_id', 'cancel_requested_by'] as $k) {
                if (($ta[$k] ?? null) !== ($tb[$k] ?? null)) { $diffs[] = "ticket[{$i}].{$k}: legacy=" . json_encode($ta[$k]) . ' feature=' . json_encode($tb[$k]); }
            }
            foreach ($ta['ts'] as $j => $va) {
                $vb = $tb['ts'][$j];
                $an = $va === null || $va === ''; $bn = $vb === null || $vb === '';
                if ($an !== $bn) {
                    // RC starts first-response SLA on the first public status note;
                    // the legacy path omitted this timestamp, so this exact delta is intentional.
                    if (in_array($scenario, ['07a-status-with-note', '09-bulk-resolve'], true) && $j === 3 && $an && !$bn) {
                        $tolerated[] = $scenario . ': public status-change note correctly records the first-response timestamp';
                        continue;
                    }
                    $diffs[] = "ticket[{$i}].ts[{$j}]: null-ness differs (legacy=" . var_export($va, true) . ', feature=' . var_export($vb, true) . ')';
                } elseif (!$an && !t12s_ts_diff_tol((string) $va, (string) $vb)) {
                    $diffs[] = "ticket[{$i}].ts[{$j}]: >300s delta (legacy={$va}, feature={$vb})";
                }
            }
        }
    }
    foreach (['messages' => ['user_id', 'is_internal', 'body'], 'events' => ['event_type', 'from_status', 'to_status'],
              'notifications' => ['user_id', 'notification_type'], 'ratings' => ['requester_id', 'agent_id', 'score'],
              'activity_logs' => ['action_code']] as $key => $cols) {
        $featureRows = $b[$key];
        if ($key === 'activity_logs') {
            // Phase 3 adds a durable system-actor audit row for automatic closure;
            // the legacy implementation emitted the ticket event but no audit row.
            $featureRows = array_values(array_filter($featureRows, static function (array $row) use (&$tolerated): bool {
                if (($row['action_code'] ?? '') === 'ticket_auto_closed') {
                    $tolerated[] = 'activity_logs: added system audit ticket_auto_closed';
                    return false;
                }
                return true;
            }));
        }
        if (count($a[$key]) !== count($featureRows)) { $diffs[] = "{$key} count differs: legacy=" . count($a[$key]) . ' feature=' . count($featureRows); continue; }
        foreach ($a[$key] as $i => $ra) {
            $rb = $featureRows[$i];
            foreach ($cols as $c) {
                $legacyValue = (string) ($ra[$c] ?? '');
                $featureValue = (string) ($rb[$c] ?? '');
                if ($legacyValue === $featureValue) {
                    continue;
                }

                // Phase 3 enriches event context without changing the event itself:
                // legacy reply/internal-note/bulk events omitted status columns,
                // while RC records the locked from/to state for reconstruction.
                if ($key === 'events' && in_array((string) ($ra['event_type'] ?? ''), ['reply', 'internal_note', 'bulk_status_changed'], true)
                    && in_array($c, ['from_status', 'to_status'], true)
                    && $legacyValue === '' && $featureValue !== '') {
                    $tolerated[] = "{$key}[{$i}].{$c}: additive locked-state context for " . (string) $ra['event_type'];
                    continue;
                }

                // More specific RC audit action codes intentionally replace the
                // legacy generic supervisor_decision/ticket_reopened labels.
                if ($key === 'activity_logs' && $c === 'action_code') {
                    $auditAliases = [
                        'ticket_supervisor_approved' => 'supervisor_decision',
                        'ticket_supervisor_rework' => 'supervisor_decision',
                        'ticket_reopened_by_supervisor' => 'ticket_reopened',
                    ];
                    if (($auditAliases[$featureValue] ?? null) === $legacyValue) {
                        $tolerated[] = "{$key}[{$i}].action_code: {$legacyValue} -> {$featureValue} (more specific RC audit code)";
                        continue;
                    }
                }

                $diffs[] = "{$key}[{$i}].{$c}: legacy=" . json_encode($ra[$c]) . ' feature=' . json_encode($rb[$c]);
            }
        }
    }
    return ['diffs' => $diffs, 'tolerated' => $tolerated];
}

/* ══════════ Scenario harness ══════════ */

function t12s_rebuild_all(array &$apps, string $rootUser, string $rootPass, string $host, string $port, string $mysql): void
{
    foreach ($apps as $side => &$app) {
        $admin = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $rootUser, $rootPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $admin->exec('DROP DATABASE IF EXISTS ' . t12s_ident($app['db']));
        $admin->exec('CREATE DATABASE ' . t12s_ident($app['db']) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        t12s_mysql_file($mysql, $host, $port, $rootUser, $rootPass, $app['db'], dirname(__DIR__) . '/install-empty-db.sql');
        $pdo = t12s_db($rootUser, $rootPass, $host, $port, $app['db']);
        t12s_seed($pdo);
    }
    unset($app);
}

function t12s_sessions(array $apps): array
{
    $map = ['requester' => 101, 'agent' => 102, 'agent2' => 103, 'supmgr' => 104, 'supervisor' => 105, 'admin' => 100];
    $out = [];
    foreach ($apps as $side => $app) {
        $baseUrl = 'http://127.0.0.1:' . (int) $app['port'];
        foreach ($map as $name => $id) {
            $out[$name][$side] = t12s_session($baseUrl, $id, $app['db']);
        }
    }
    return $out;
}

function t12s_clone_log_tail(array $app, int $maxLines = 25): string { $logFile = $app['root'] . '/storage/logs/app.log'; if (!is_file($logFile)) { return '(app.log missing)'; } $lines = @file($logFile, FILE_IGNORE_NEW_LINES) ?: []; $lines = array_slice(array_filter($lines, static fn($l) => trim($l) !== ''), -$maxLines); return implode("\n", $lines); }

function t12s_create_ticket(string $side, array $app, array $sess, array $extra = []): int
{
    $fields = array_replace([
        'subject' => 'T12 characterization subject',
        'description' => 'T12 characterization body\nخط دوم برای مقایسهٔ بدنه',
        'category_id' => 1,
        'service_id' => 1,
        'service_group' => 'support',
        'ticket_type' => 'request',
        'priority' => 'normal',
        'support_location' => 'ساختمان ۱ — اتاق ۱۰۱',
    ], $extra);
    $r = t12s_post($app['base_url'] . '/index.php', $sess['requester'][$side], 'create_ticket', $fields);
    if ($r['status'] !== 302) {
        throw new RuntimeException("S1 create_ticket failed for {$side}: HTTP {$r['status']} body=" . substr(strip_tags($r['body']), 0, 160));
    }
    $location = '';
    foreach ($r['headers'] as $h) { if (stripos((string) $h, 'location:') === 0) { $location = trim(substr((string) $h, 9)); } }
    $pdo = t12s_db(getenv('CI_DB_USER') ?: 'root', getenv('CI_DB_PASS') ?: '', getenv('CI_DB_HOST') ?: '127.0.0.1', getenv('CI_DB_PORT') ?: '3306', $app['db']);
    $cnt = (int) $pdo->query('SELECT COUNT(*) FROM tickets')->fetchColumn();
    $mx = $pdo->query('SELECT MAX(id) FROM tickets')->fetchColumn();
    t12s_log('    [probe] tid_check: HTTP ' . $r['status'] . ' location=' . $location . ' tickets_count=' . $cnt . ' max_id=' . var_export($mx, true));
    $expectedLocation = $side === 'legacy'
        ? 'index.php?page=ticket&id='
        : 'request-center.php?page=ticket&id=';
    if (!str_starts_with($location, $expectedLocation)) {
        throw new RuntimeException("create_ticket for {$side} did not produce its expected ticket route ({$expectedLocation}; location={$location}); app.log tail:\n" . t12s_clone_log_tail($app));
    }
    return (int) ($mx ?? (int) explode('=', $location)[count(explode('=', $location)) - 1]);
}

function t12s_pdo(array $app): PDO
{
    return t12s_db(getenv('CI_DB_USER') ?: 'root', getenv('CI_DB_PASS') ?: '', getenv('CI_DB_HOST') ?: '127.0.0.1', getenv('CI_DB_PORT') ?: '3306', $app['db']);
}

/** Count non-2xx/302/303 is not needed; redirect expected for writes. Returns the HTTP status. */
function t12s_expect_redirect(array $r, string $what): int
{
    t12s_log(sprintf('    %s → HTTP %d', $what, $r['status']));
    return $r['status'];
}

/**
 * Run one scenario on both clones from an identical fresh state.
 * $body($side, $app, $sess, $ticketId) contains ONLY the scenario-specific actions.
 * Returns ['legacy' => snapshot, 'feature' => snapshot, 'extra' => [...]].
 */
function t12s_run_pair(array &$apps, string $rootUser, string $rootPass, string $host, string $port, string $mysql, callable $body): array
{
    // fresh state for this scenario
    t12s_rebuild_all($apps, $rootUser, $rootPass, $host, $port, $mysql);
    foreach ($apps as $side => &$app) { $app['base_url'] = 'http://127.0.0.1:' . (int) $app['port']; }
    unset($app);
    $sess = t12s_sessions($apps);
    $snapshots = [];
    $extras = [];
    foreach ($apps as $side => $app) {
        [$snap, $extra] = $body($side, $app, $sess);
        $snapshots[$side] = $snap;
        $extras[$side] = $extra;
    }
    return ['snapshots' => $snapshots, 'extras' => $extras];
}

function t12s_finish(string $name, callable $body, array &$apps, string $rootUser, string $rootPass, string $host, string $port, string $mysql): array
{
    $pair = t12s_run_pair($apps, $rootUser, $rootPass, $host, $port, $mysql, $body);
    $comparison = t12s_compare($pair['snapshots']['legacy'], $pair['snapshots']['feature'], $name);
    $diffs = $comparison['diffs'];
    $tolerated = $comparison['tolerated'];
    if ($name === '07b-priority-by-manager') {
        $featureTicket = $pair['snapshots']['feature']['tickets'][0] ?? [];
        if (($featureTicket['service_group'] ?? '') !== 'it'
            || ($featureTicket['status'] ?? '') !== 'in_progress'
            || (int) ($featureTicket['assigned_to'] ?? 0) !== 106
            || ($featureTicket['priority'] ?? '') !== 'normal') {
            $diffs[] = '07b: cross-group support-manager edit changed an IT ticket that must remain untouched';
        } else {
            $tolerated[] = '07b: out-of-group support manager could not change the IT ticket; status, assignee and priority remained unchanged';
        }
        $featureActions = array_column($pair['snapshots']['feature']['activity_logs'], 'action_code');
        if (!in_array('post_action_failed', $featureActions, true)) {
            $diffs[] = '07b: denied cross-group write did not leave a failure audit record';
        }
    }
    $evidence = [
        'scenario' => $name,
        'legacy_commit' => '2176d2aa57758a0fbef426636c9943879646b165',
        'feature_root' => 'current checkout',
        'snapshots' => $pair['snapshots'],
        'extras' => $pair['extras'],
        'diffs' => $diffs,
        'tolerated_differences' => $tolerated,
        'result' => $diffs === [] ? 'PASS' : 'DIFF',
    ];
    if (!is_dir((string) getenv('T12_EVIDENCE_DIR') ?: (sys_get_temp_dir() . '/t12-evidence'))) {
        @mkdir((string) (getenv('T12_EVIDENCE_DIR') ?: (sys_get_temp_dir() . '/t12-evidence')), 0775, true);
    }
    file_put_contents(((string) getenv('T12_EVIDENCE_DIR') ?: (sys_get_temp_dir() . '/t12-evidence')) . '/scenario-' . $name . '.json',
        json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    foreach ($tolerated as $t) {
        t12s_log("T12-SCENARIO {$name} ACCEPTED: {$t}");
    }
    if ($diffs === []) {
        t12s_log("T12-SCENARIO {$name}: PASS");
        return ['name' => $name, 'result' => 'PASS', 'diffs' => 0];
    }
    foreach (array_slice($diffs, 0, 12) as $d) { t12s_log("T12-SCENARIO {$name} DIFF: {$d}"); }
    if (count($diffs) > 12) { t12s_log("T12-SCENARIO {$name} DIFF: … +" . (count($diffs) - 12) . " more"); }
    t12s_log("T12-SCENARIO {$name}: FAIL (" . count($diffs) . " diffs)");
    return ['name' => $name, 'result' => 'FAIL', 'diffs' => count($diffs)];
}


function t12s_finish_feature_only(string $name, callable $body, array &$apps, string $rootUser, string $rootPass, string $host, string $port, string $mysql): array
{
    t12s_rebuild_all($apps, $rootUser, $rootPass, $host, $port, $mysql);
    foreach ($apps as &$app) {
        $app['base_url'] = 'http://127.0.0.1:' . (int) $app['port'];
    }
    unset($app);
    $sessions = t12s_sessions($apps);
    [$snapshot, $extra] = $body($apps['feature'], $sessions['supervisor']['feature'], $sessions);
    $diffs = [];
    if ((int) ($extra['http_status'] ?? 0) !== 302) {
        $diffs[] = 'supervisor assignment HTTP request did not complete with the expected redirect';
    }
    $ticket = $snapshot['tickets'][0] ?? [];
    if (($ticket['status'] ?? '') !== 'assigned' || (int) ($ticket['assigned_to'] ?? 0) !== 102 || empty($ticket['ts'][2])) {
        $diffs[] = 'supervisor assignment did not persist assigned status, eligible assignee and assigned_at';
    }
    $eventFound = false;
    foreach ($snapshot['events'] as $event) {
        if (($event['event_type'] ?? '') === 'assignment_changed'
            && ($event['from_status'] ?? '') === 'manager_review'
            && ($event['to_status'] ?? '') === 'assigned') {
            $eventFound = true;
            break;
        }
    }
    if (!$eventFound) {
        $diffs[] = 'assignment_changed lifecycle event was not recorded';
    }
    $auditActions = array_column($snapshot['activity_logs'], 'action_code');
    if (!in_array('ticket_updated', $auditActions, true)) {
        $diffs[] = 'required audit record for supervisor assignment is missing';
    }
    $notificationFound = false;
    foreach ($snapshot['notifications'] as $notification) {
        if (($notification['type'] ?? $notification['notification_type'] ?? '') === 'ticket_assignment_changed') {
            $notificationFound = true;
            break;
        }
    }
    if (!$notificationFound) {
        $diffs[] = 'ticket_assignment_changed notification was not emitted';
    }
    $internalMessageFound = false;
    foreach ($snapshot['messages'] as $message) {
        if ((int) ($message['is_internal'] ?? 0) === 1) {
            $internalMessageFound = true;
            break;
        }
    }
    if (!$internalMessageFound) {
        $diffs[] = 'supervisor internal assignment note was not persisted as internal';
    }
    $evidence = [
        'scenario' => $name,
        'mode' => 'feature-only integration (no legacy equivalent action exists)',
        'feature_snapshot' => $snapshot,
        'extra' => $extra,
        'diffs' => $diffs,
        'result' => $diffs === [] ? 'PASS' : 'FAIL',
    ];
    if (!is_dir((string) getenv('T12_EVIDENCE_DIR') ?: (sys_get_temp_dir() . '/t12-evidence'))) {
        @mkdir((string) (getenv('T12_EVIDENCE_DIR') ?: (sys_get_temp_dir() . '/t12-evidence')), 0775, true);
    }
    file_put_contents(((string) getenv('T12_EVIDENCE_DIR') ?: (sys_get_temp_dir() . '/t12-evidence')) . '/scenario-' . $name . '.json',
        json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($diffs === []) {
        t12s_log("T12-SCENARIO {$name}: PASS (feature-only MariaDB HTTP integration)");
        return ['name' => $name, 'result' => 'PASS', 'diffs' => 0];
    }
    foreach ($diffs as $diff) {
        t12s_log("T12-SCENARIO {$name} DIFF: {$diff}");
    }
    t12s_log("T12-SCENARIO {$name}: FAIL (" . count($diffs) . " diffs)");
    return ['name' => $name, 'result' => 'FAIL', 'diffs' => count($diffs)];
}

/* ════════ Main ════════ */

$evidenceDir = (string) (getenv('T12_EVIDENCE_DIR') ?: (sys_get_temp_dir() . '/t12-evidence'));
if (!is_dir($evidenceDir)) { @mkdir($evidenceDir, 0775, true); }

t12s_check(getenv('GITHUB_ACTIONS') === 'true', 'T12 scenarios run only inside GitHub Actions');
t12s_check($legacyRoot !== '' && is_dir($legacyRoot), 'pinned Legacy worktree exists');
t12s_check(is_dir($featureRoot) && realpath($legacyRoot) !== realpath($featureRoot), 'Legacy and Feature roots are independent');
t12s_check(is_file($schemaFile), 'canonical install-empty-db.sql exists');

$fixtureSource = $featureRoot . '/tests/fixtures/request-center-t12-session-seed.php';
$legacyFixture = $legacyRoot . '/tests/fixtures/request-center-t12-session-seed.php';
if (!is_dir(dirname($legacyFixture))) { mkdir(dirname($legacyFixture), 0775, true); }
copy($fixtureSource, $legacyFixture);
t12s_check(is_file($legacyFixture), 'session fixture copied into Legacy worktree');

t12s_web_users($rootUser, $rootPass, $host, $port, $apps);
// Both databases must exist BEFORE the web servers start: the application
// bootstrap performs a schema check on EVERY request and returns HTTP 500
// while a clone database is absent.
t12s_rebuild_all($apps, $rootUser, $rootPass, $host, $port, $mysql);

$failure = false;
try {
    foreach ($apps as $side => &$app) {
        $configPath = t12s_config('http://127.0.0.1:' . (int) $app['port'], $app['db'], $app['web_user'], 'ci-t12s-web-pass', $host, $port);
        $configFiles[] = $configPath;
        $app['config_path'] = $configPath;
        $servers[$side] = t12s_start_server($app['root'], $app['port'], $configPath);
    }
    unset($app);

    $results = [];
    if (in_array(1, $scenarioFilter, true)) {
        $results[] = t12s_finish('01-create', function (string $side, array $app, array $sess) {
            $tid = t12s_create_ticket($side, $app, $sess);
            return [t12s_snapshot(t12s_pdo($app)), ['ticket_id' => $tid]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }
    if (in_array(2, $scenarioFilter, true)) {
        $results[] = t12s_finish('02-direct-cancel', function (string $side, array $app, array $sess) {
            $tid = t12s_create_ticket($side, $app, $sess);
            $r = t12s_post($app['base_url'] . '/index.php', $sess['requester'][$side], 'cancel_ticket', ['ticket_id' => $tid, 'cancel_reason' => 'ثبت اشتباه T12']);
            return [t12s_snapshot(t12s_pdo($app)), ['cancel_status' => $r['status']]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }
    if (in_array(3, $scenarioFilter, true)) {
        $mkAssertable = static function (string $decision) use (&$apps) {
            return function (string $side, array $app, array $sess) use ($decision) {
                $tid = t12s_create_ticket($side, $app, $sess);
                t12s_pdo($app)->exec("UPDATE tickets SET status = 'assigned', assigned_to = 102, assigned_at = NOW() - INTERVAL 1 HOUR, first_response_at = NOW() - INTERVAL 30 MINUTE WHERE id = " . (int) $tid);
                $r1 = t12s_post($app['base_url'] . '/index.php', $sess['requester'][$side], 'cancel_ticket', ['ticket_id' => $tid, 'cancel_reason' => 'لغو لازم است T12']);
                $fields = ['ticket_id' => $tid, 'decision' => $decision];
                if ($decision === 'reject') { $fields['decision_note'] = 'دلایل کافی نیست T12'; }
                $r2 = t12s_post($app['base_url'] . '/index.php', $sess['agent'][$side], 'decide_ticket_cancellation', $fields);
                return [t12s_snapshot(t12s_pdo($app)), ['request_status' => $r1['status'], 'decision_status' => $r2['status']]];
            };
        };
        $results[] = t12s_finish('03a-cancel-request-reject', $mkAssertable('reject'), $apps, $rootUser, $rootPass, $host, $port, $mysql);
        $results[] = t12s_finish('03b-cancel-request-approve', $mkAssertable('approve'), $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }
    if (in_array(4, $scenarioFilter, true)) {
        $results[] = t12s_finish('04-claim-ordering', function (string $side, array $app, array $sess) {
            $tid = t12s_create_ticket($side, $app, $sess);
            $r1 = t12s_post($app['base_url'] . '/index.php', $sess['agent'][$side], 'claim_ticket', ['ticket_id' => $tid]);
            $r2 = t12s_post($app['base_url'] . '/index.php', $sess['agent2'][$side], 'claim_ticket', ['ticket_id' => $tid]);
            return [t12s_snapshot(t12s_pdo($app)), ['first_status' => $r1['status'], 'second_status' => $r2['status']]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }
    if (in_array(5, $scenarioFilter, true)) {
        $results[] = t12s_finish('05-reopen', function (string $side, array $app, array $sess) {
            $tid = t12s_create_ticket($side, $app, $sess);
            $pdo = t12s_pdo($app);
            $pdo->exec("UPDATE tickets SET status = 'resolved', assigned_to = 102, assigned_at = NOW() - INTERVAL 2 HOUR, first_response_at = NOW() - INTERVAL 90 MINUTE, resolved_at = NOW() - INTERVAL 1 HOUR WHERE id = " . (int) $tid);
            $r = t12s_post($app['base_url'] . '/index.php', $sess['requester'][$side], 'reopen_ticket', ['ticket_id' => $tid, 'reopen_reason' => 'هنوز مشکل وجود دارد T12']);
            return [t12s_snapshot($pdo), ['reopen_status' => $r['status']]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }
    if (in_array(6, $scenarioFilter, true)) {
        $mkReply = static function (string $flag) {
            return function (string $side, array $app, array $sess) use ($flag) {
                $tid = t12s_create_ticket($side, $app, $sess);
                $pdo = t12s_pdo($app);
                $pdo->exec("UPDATE tickets SET status = 'in_progress', assigned_to = 102, assigned_at = NOW() - INTERVAL 1 HOUR, first_response_at = NOW() - INTERVAL 30 MINUTE WHERE id = " . (int) $tid);
                $r = t12s_post($app['base_url'] . '/index.php', $sess['agent'][$side], 'reply', ['ticket_id' => $tid, 'body' => $flag === 'internal' ? 'یادداشت داخلی T12' : 'پاسخ کارشناس T12', 'is_internal' => $flag === 'internal' ? '1' : '0']);
                return [t12s_snapshot($pdo), ['reply_status' => $r['status']]];
            };
        };
        $results[] = t12s_finish('06a-reply-to-requester', $mkReply('public'), $apps, $rootUser, $rootPass, $host, $port, $mysql);
        $results[] = t12s_finish('06b-internal-note', $mkReply('internal'), $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }
    if (in_array(7, $scenarioFilter, true)) {
        $results[] = t12s_finish('07a-status-with-note', function (string $side, array $app, array $sess) {
            $tid = t12s_create_ticket($side, $app, $sess);
            $pdo = t12s_pdo($app);
            $pdo->exec("UPDATE tickets SET status = 'in_progress', assigned_to = 102, assigned_at = NOW() - INTERVAL 1 HOUR WHERE id = " . (int) $tid);
            $r = t12s_post($app['base_url'] . '/index.php', $sess['agent'][$side], 'update_ticket', ['ticket_id' => $tid, 'status' => 'waiting_user', 'update_note' => 'لطفاً اطلاعات تکمیلی ارسال کنید T12']);
            return [t12s_snapshot($pdo), ['update_status' => $r['status']]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
        $results[] = t12s_finish('07b-priority-by-manager', function (string $side, array $app, array $sess) {
            $tid = t12s_create_ticket($side, $app, $sess, [
                'category_id' => 2,
                'service_id' => 2,
                'service_group' => 'it',
                'asset_id' => 1,
            ]);
            $pdo = t12s_pdo($app);
            $pdo->exec("UPDATE tickets SET status = 'in_progress', assigned_to = 106, assigned_at = NOW() - INTERVAL 1 HOUR WHERE id = " . (int) $tid);
            $r = t12s_post($app['base_url'] . '/index.php', $sess['supmgr'][$side], 'update_ticket', ['ticket_id' => $tid, 'priority' => 'urgent']);
            return [t12s_snapshot($pdo), ['priority_status' => $r['status']]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }
    if (in_array(8, $scenarioFilter, true)) {
        $mkDecision = static function (string $decision) {
            return function (string $side, array $app, array $sess) use ($decision) {
                $tid = t12s_create_ticket($side, $app, $sess);
                $pdo = t12s_pdo($app);
                $rows = $pdo->exec("UPDATE tickets SET status = 'resolved', assigned_to = 102, assigned_at = NOW() - INTERVAL 2 HOUR, first_response_at = NOW() - INTERVAL 90 MINUTE, resolved_at = NOW() - INTERVAL 1 HOUR WHERE id = " . (int) $tid);
                $chk = $pdo->query("SELECT id, status, assigned_to, resolved_at FROM tickets WHERE id = " . (int) $tid)->fetch(PDO::FETCH_ASSOC);
                t12s_log('    [fixture] tid=' . (int) $tid . ' update_rows=' . var_export($rows, true) . ' row=' . json_encode($chk, JSON_UNESCAPED_UNICODE));
                $pdo->exec("INSERT INTO ticket_ratings (ticket_id, requester_id, agent_id, score, created_at) VALUES ({$tid}, 101, 102, 5, NOW())");
                $r = t12s_post($app['base_url'] . '/index.php', $sess['supervisor'][$side], 'supervisor_decision', ['ticket_id' => $tid, 'decision' => $decision, 'supervisor_note' => 'تأیید نهایی T12']);
                return [t12s_snapshot($pdo), ['decision_status' => $r['status']]];
            };
        };
        $results[] = t12s_finish('08a-supervisor-approve', $mkDecision('approve'), $apps, $rootUser, $rootPass, $host, $port, $mysql);
        $results[] = t12s_finish('08b-supervisor-rework', function (string $side, array $app, array $sess) {
            $tid = t12s_create_ticket($side, $app, $sess);
            $pdo = t12s_pdo($app);
            $pdo->exec("UPDATE tickets SET status = 'resolved', assigned_to = 102, assigned_at = NOW() - INTERVAL 2 HOUR, first_response_at = NOW() - INTERVAL 90 MINUTE, resolved_at = NOW() - INTERVAL 1 HOUR WHERE id = " . (int) $tid);
            $pdo->exec("INSERT INTO ticket_ratings (ticket_id, requester_id, agent_id, score, created_at) VALUES ({$tid}, 101, 102, 5, (SELECT resolved_at FROM tickets WHERE id = {$tid}))");
            $r = t12s_post($app['base_url'] . '/index.php', $sess['supervisor'][$side], 'supervisor_decision', ['ticket_id' => $tid, 'decision' => 'rework', 'supervisor_note' => 'برگشت برای اصلاح T12']);
            return [t12s_snapshot($pdo), ['rework_status' => $r['status']]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
        // supervisor_reopen: approve first, then reopen
        $results[] = t12s_finish('08c-supervisor-reopen', function (string $side, array $app, array $sess) {
            $tid = t12s_create_ticket($side, $app, $sess);
            $pdo = t12s_pdo($app);
            $pdo->exec("UPDATE tickets SET status = 'resolved', assigned_to = 102, assigned_at = NOW() - INTERVAL 2 HOUR, first_response_at = NOW() - INTERVAL 90 MINUTE, resolved_at = NOW() - INTERVAL 1 HOUR WHERE id = " . (int) $tid);
            $pdo->exec("INSERT INTO ticket_ratings (ticket_id, requester_id, agent_id, score, created_at) VALUES ({$tid}, 101, 102, 5, (SELECT resolved_at FROM tickets WHERE id = {$tid}))");
            $r1 = t12s_post($app['base_url'] . '/index.php', $sess['supervisor'][$side], 'supervisor_decision', ['ticket_id' => $tid, 'decision' => 'approve', 'supervisor_note' => 'تأیید نهایی T12']);
            $r2 = t12s_post($app['base_url'] . '/index.php', $sess['supervisor'][$side], 'supervisor_reopen', ['ticket_id' => $tid, 'supervisor_note' => 'بازگشایی رسمی T12']);
            return [t12s_snapshot($pdo), ['approve_status' => $r1['status'], 'reopen_status' => $r2['status']]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }
    if (in_array(9, $scenarioFilter, true)) {
        $results[] = t12s_finish('09-bulk-resolve', function (string $side, array $app, array $sess) {
            $t1 = t12s_create_ticket($side, $app, $sess);
            $t2 = t12s_create_ticket($side, $app, $sess);
            $pdo = t12s_pdo($app);
            $pdo->exec("UPDATE tickets SET status = 'in_progress', assigned_to = 102, assigned_at = NOW() - INTERVAL 1 HOUR WHERE id IN ({$t1}, {$t2})");
            $r = t12s_post($app['base_url'] . '/index.php', $sess['admin'][$side], 'bulk_update_tickets', ['ticket_ids' => [$t1, $t2], 'bulk_status' => 'resolved', 'bulk_note' => 'پیام مشترک T12']);
            return [t12s_snapshot($pdo), ['bulk_status_code' => $r['status'], 'tickets' => [$t1, $t2]]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }
    if (in_array(10, $scenarioFilter, true)) {
        $mkCron = static function (string $mode) {
            return function (string $side, array $app, array $sess) use ($mode) {
                $tid = t12s_create_ticket($side, $app, $sess);
                $pdo = t12s_pdo($app);
                $pdo->exec("UPDATE tickets SET status = 'resolved', assigned_to = 102, created_at = NOW() - INTERVAL 5 DAY, updated_at = NOW() - INTERVAL 4 DAY, assigned_at = NOW() - INTERVAL 4 DAY, resolved_at = NOW() - INTERVAL 4 DAY WHERE id = " . (int) $tid);
                if ($mode === 'rated') {
                    $pdo->exec("INSERT INTO ticket_ratings (ticket_id, requester_id, agent_id, score, created_at) VALUES ({$tid}, 101, 102, 4, (SELECT resolved_at FROM tickets WHERE id = {$tid}))");
                }
                $pdo->exec("INSERT INTO settings (`key`, `value`) VALUES ('ticket_auto_close_days', '3') ON DUPLICATE KEY UPDATE `value` = '3'");
                $env = getenv();
                if (!is_array($env)) { $env = []; }
                $env['ITSM_CONFIG_PATH'] = $app['config_path'];
                $out = shell_exec((is_executable(PHP_BINARY) ? PHP_BINARY : 'php') . ' ' . escapeshellarg($app['root'] . '/cron/ticket_auto_close.php') . ' 2>&1; echo " EXIT:" $?');
                return [t12s_snapshot($pdo), ['cron_output' => trim((string) $out), 'mode' => $mode]];
            };
        };
        $results[] = t12s_finish('10a-auto-close-rated', $mkCron('rated'), $apps, $rootUser, $rootPass, $host, $port, $mysql);
        $results[] = t12s_finish('10b-auto-close-unrated', $mkCron('unrated'), $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }


    if (in_array(11, $scenarioFilter, true)) {
        $results[] = t12s_finish_feature_only('11-supervisor-assignment', function (array $app, array $supervisorSession, array $sessions) {
            $tid = t12s_create_ticket('feature', $app, $sessions);
            $pdo = t12s_pdo($app);
            $pdo->exec("UPDATE tickets SET status = 'manager_review', assigned_to = NULL, assigned_at = NULL WHERE id = " . (int) $tid);
            $response = t12s_post($app['base_url'] . '/request-center.php', $supervisorSession, 'supervisor_assign_ticket', [
                'ticket_id' => $tid,
                'assigned_to' => 102,
                'assignment_reason' => 'تخصیص کنترل‌شدهٔ T12',
                'assignment_note' => 'یادداشت داخلی آزمون تخصیص T12',
            ]);
            return [t12s_snapshot($pdo), ['http_status' => $response['status'], 'ticket_id' => $tid]];
        }, $apps, $rootUser, $rootPass, $host, $port, $mysql);
    }

    if (in_array(12, $scenarioFilter, true)) {
        // Exercise the four independent Workspace entry routes with real role sessions.
        // This complements the lifecycle POST characterization above; it does not
        // infer UI availability from a successful legacy POST.
        t12s_rebuild_all($apps, $rootUser, $rootPass, $host, $port, $mysql);
        foreach ($apps as &$workspaceApp) {
            $workspaceApp['base_url'] = 'http://127.0.0.1:' . (int) $workspaceApp['port'];
        }
        unset($workspaceApp);
        $workspaceSessions = t12s_sessions($apps);
        $workspaceCases = [
            ['requester', 'requester', 'درخواست‌دهنده'],
            ['agent', 'agent', 'صف شخصی'],
            ['supervisor', 'supervisor', 'فضای کاری سوپروایزر'],
            ['admin', 'admin', 'مدیریت مرکز درخواست‌ها'],
        ];
        $workspaceEvidence = [];
        foreach ($workspaceCases as [$workspace, $sessionKey, $expectedText]) {
            $response = t12s_http(
                $apps['feature']['base_url'] . '/request-center.php?workspace=' . rawurlencode($workspace),
                'GET',
                ['Cookie: ' . $workspaceSessions[$sessionKey]['feature']['cookie']]
            );
            if ($response['status'] !== 200 || !str_contains($response['body'], $expectedText)) {
                t12s_fail('12-independent-four-workspaces-http: ' . $workspace
                    . ' expected HTTP 200 and marker "' . $expectedText . '", got HTTP '
                    . $response['status'] . '; body=' . substr($response['body'], 0, 500));
            }
            $workspaceEvidence[] = $workspace . ':HTTP-' . $response['status'] . ':PASS';
            t12s_log('T12-SCENARIO 12-independent-four-workspaces-http ' . $workspace . ': PASS');
        }
        $results[] = [
            'name' => '12-independent-four-workspaces-http',
            'result' => 'PASS',
            'evidence' => $workspaceEvidence,
        ];
    }

    if (in_array(13, $scenarioFilter, true)) {
        // Execute one write operation from each role through the independent
        // Request Center POST boundary (not index.php's compatibility bridge).
        t12s_rebuild_all($apps, $rootUser, $rootPass, $host, $port, $mysql);
        foreach ($apps as &$independentApp) {
            $independentApp['base_url'] = 'http://127.0.0.1:' . (int) $independentApp['port'];
        }
        unset($independentApp);
        $independentSessions = t12s_sessions($apps);
        $independentApp = $apps['feature'];
        $independentPdo = t12s_pdo($independentApp);
        $independentUrl = $independentApp['base_url'] . '/request-center.php';
        $independentEvidence = [];

        // Requester: ask to cancel an already assigned ticket; the assigned
        // agent then decides the request through the independent POST boundary.
        $requesterTicket = t12s_create_ticket('feature', $independentApp, $independentSessions);
        $independentPdo->exec("UPDATE tickets SET status = 'assigned', assigned_to = 102, assigned_at = NOW() - INTERVAL 1 HOUR, first_response_at = NOW() - INTERVAL 30 MINUTE WHERE id = " . (int) $requesterTicket);
        $requesterResponse = t12s_post($independentUrl, $independentSessions['requester']['feature'], 'cancel_ticket', [
            'ticket_id' => $requesterTicket,
            'cancel_reason' => 'درخواست لغو مستقل درخواست‌دهنده در T12',
        ]);
        $requesterCancel = $independentPdo->query('SELECT status, cancel_requested_at FROM tickets WHERE id = ' . (int) $requesterTicket)->fetch(PDO::FETCH_ASSOC);
        if ($requesterResponse['status'] !== 302 || !$requesterCancel || empty($requesterCancel['cancel_requested_at'])
            || (string) $requesterCancel['status'] !== 'assigned') {
            t12s_fail('13-independent-post requester cancellation request failed: HTTP ' . $requesterResponse['status']
                . ', ticket=' . json_encode($requesterCancel));
        }
        $independentEvidence[] = 'requester:cancel-request:PASS';
        t12s_log('T12-SCENARIO 13-independent-post requester: PASS');

        $agentCancellationResponse = t12s_post($independentUrl, $independentSessions['agent']['feature'], 'decide_ticket_cancellation', [
            'ticket_id' => $requesterTicket,
            'decision' => 'approve',
            'decision_note' => 'تأیید لغو از مسیر مستقل T12',
        ]);
        $requesterFinalStatus = (string) $independentPdo->query('SELECT status FROM tickets WHERE id = ' . (int) $requesterTicket)->fetchColumn();
        if ($agentCancellationResponse['status'] !== 302 || $requesterFinalStatus !== 'cancelled') {
            t12s_fail('13-independent-post agent cancellation decision failed: HTTP ' . $agentCancellationResponse['status']
                . ', status=' . $requesterFinalStatus);
        }
        $independentEvidence[] = 'agent:cancel-decision:PASS';

        // Agent: public reply against a ticket explicitly assigned to this agent.
        $agentTicket = t12s_create_ticket('feature', $independentApp, $independentSessions);
        $independentPdo->exec("UPDATE tickets SET status = 'in_progress', assigned_to = 102, assigned_at = NOW() - INTERVAL 1 HOUR, first_response_at = NOW() - INTERVAL 30 MINUTE WHERE id = " . (int) $agentTicket);
        $agentResponse = t12s_post($independentUrl, $independentSessions['agent']['feature'], 'reply', [
            'ticket_id' => $agentTicket,
            'body' => 'پاسخ عمومی کارشناس از ورودی مستقل T12',
            'is_internal' => '0',
        ]);
        $agentMessage = $independentPdo->query('SELECT is_internal, body FROM ticket_messages WHERE ticket_id = ' . (int) $agentTicket . ' ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if ($agentResponse['status'] !== 302 || !$agentMessage || (int) $agentMessage['is_internal'] !== 0
            || (string) $agentMessage['body'] !== 'پاسخ عمومی کارشناس از ورودی مستقل T12') {
            t12s_fail('13-independent-post agent public reply failed: HTTP ' . $agentResponse['status']);
        }
        $independentEvidence[] = 'agent:public-reply:PASS';
        t12s_log('T12-SCENARIO 13-independent-post agent: PASS');

        // Supervisor: approve a resolved ticket with a current requester rating.
        $supervisorTicket = t12s_create_ticket('feature', $independentApp, $independentSessions);
        $independentPdo->exec("UPDATE tickets SET status = 'resolved', assigned_to = 102, assigned_at = NOW() - INTERVAL 2 HOUR, first_response_at = NOW() - INTERVAL 90 MINUTE, resolved_at = NOW() - INTERVAL 1 HOUR WHERE id = " . (int) $supervisorTicket);
        $independentPdo->exec("INSERT INTO ticket_ratings (ticket_id, requester_id, agent_id, score, created_at) SELECT " . (int) $supervisorTicket . ", 101, 102, 5, resolved_at FROM tickets WHERE id = " . (int) $supervisorTicket);
        $supervisorResponse = t12s_post($independentUrl, $independentSessions['supervisor']['feature'], 'supervisor_decision', [
            'ticket_id' => $supervisorTicket,
            'decision' => 'approve',
            'supervisor_note' => 'تأیید مستقل سوپروایزر در T12',
        ]);
        $supervisorStatus = (string) $independentPdo->query('SELECT status FROM tickets WHERE id = ' . (int) $supervisorTicket)->fetchColumn();
        if ($supervisorResponse['status'] !== 302 || $supervisorStatus !== 'closed') {
            t12s_fail('13-independent-post supervisor decision failed: HTTP ' . $supervisorResponse['status'] . ', status=' . $supervisorStatus);
        }
        $independentEvidence[] = 'supervisor:approve-close:PASS';
        t12s_log('T12-SCENARIO 13-independent-post supervisor: PASS');

        // Primary admin: scoped bulk transition through the module-owned POST runner.
        $adminTicketA = t12s_create_ticket('feature', $independentApp, $independentSessions);
        $adminTicketB = t12s_create_ticket('feature', $independentApp, $independentSessions);
        $independentPdo->exec("UPDATE tickets SET status = 'in_progress', assigned_to = 102, assigned_at = NOW() - INTERVAL 1 HOUR, first_response_at = NOW() - INTERVAL 30 MINUTE WHERE id IN (" . (int) $adminTicketA . ", " . (int) $adminTicketB . ")");
        $adminResponse = t12s_post($independentUrl, $independentSessions['admin']['feature'], 'bulk_update_tickets', [
            'ticket_ids' => [$adminTicketA, $adminTicketB],
            'bulk_status' => 'resolved',
            'bulk_note' => 'عملیات گروهی مدیر از ورودی مستقل T12',
        ]);
        $adminStatuses = $independentPdo->query('SELECT status FROM tickets WHERE id IN (' . (int) $adminTicketA . ', ' . (int) $adminTicketB . ') ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
        if ($adminResponse['status'] !== 302 || $adminStatuses !== ['resolved', 'resolved']) {
            t12s_fail('13-independent-post admin bulk update failed: HTTP ' . $adminResponse['status'] . ', statuses=' . json_encode($adminStatuses));
        }
        $independentEvidence[] = 'admin:bulk-update:PASS';
        t12s_log('T12-SCENARIO 13-independent-post admin: PASS');

        $results[] = [
            'name' => '13-independent-post-four-workspaces',
            'result' => 'PASS',
            'evidence' => $independentEvidence,
        ];
    }

    file_put_contents($evidenceDir . '/summary.json', json_encode(['results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $failed = array_filter($results, fn($r) => $r['result'] !== 'PASS');
    t12s_log('T12 SUMMARY: ' . ($results === [] ? 'no scenarios selected' : json_encode(array_map(fn($r) => $r['name'] . ':' . $r['result'], $results), JSON_UNESCAPED_UNICODE)));
    t12s_log('T12 RESULT: ' . ($failed === [] ? 'ALL SELECTED SCENARIO CONTRACTS PASSED' : count($failed) . ' SCENARIO(S) FAILED'));
    if ($failed !== []) { $failure = true; }
} catch (Throwable $e) {
    fwrite(STDERR, 'T12 SCENARIOS ERROR: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
    $failure = true;
} finally {
    foreach ($servers as $p) { if (is_resource($p)) { @proc_terminate($p); @proc_close($p); } }
    foreach ($configFiles as $f) { @unlink($f); }
    $admin = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $rootUser, $rootPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    foreach ($apps as $app) {
        try { $admin->exec('DROP DATABASE IF EXISTS ' . t12s_ident($app['db'])); } catch (Throwable) {}
        try { $admin->exec("DROP USER IF EXISTS '" . $app['web_user'] . "'@'%'"); } catch (Throwable) {}
    }
}
exit($failure ? 1 : 0);
