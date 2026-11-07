<?php
declare(strict_types=1);

/**
 * T10: launch 50 independent PHP workers against one MariaDB ticket row.
 * All workers wait at a DB barrier, then race the same Claim transition.
 * This verifies WorkflowService row locking and optimistic guards; it does not
 * exercise HTTP permission checks or notification delivery.
 */

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');
$dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$workerPath = __DIR__ . '/fixtures/request-center-claim-worker.php';
$workerCount = 50;
$tag = strtoupper(bin2hex(random_bytes(4)));
$processes = [];
$userIds = [];
$ticketId = 0;
$departmentId = 0;
$categoryId = 0;
$serviceId = 0;

function t10_check(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException('T10 failed: ' . $message);
    }
    echo 'PASS ' . $message . PHP_EOL;
}

try {
    $pdo->exec('DROP TABLE IF EXISTS rc_t10_claim_barrier');
    $pdo->exec(
        'CREATE TABLE rc_t10_claim_barrier (
            id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            ready_count INT UNSIGNED NOT NULL DEFAULT 0,
            release_flag TINYINT UNSIGNED NOT NULL DEFAULT 0
         ) ENGINE=InnoDB'
    );
    $pdo->exec('INSERT INTO rc_t10_claim_barrier (id, ready_count, release_flag) VALUES (1, 0, 0)');

    $department = $pdo->prepare('INSERT INTO departments (name) VALUES (?)');
    $department->execute(['RC T10 ' . $tag]);
    $departmentId = (int) $pdo->lastInsertId();

    $userInsert = $pdo->prepare('INSERT INTO users (username, full_name, role, department_id) VALUES (?, ?, ?, ?)');
    $userInsert->execute(['rc-t10-requester-' . strtolower($tag), 'RC T10 Requester', 'user', $departmentId]);
    $requesterId = (int) $pdo->lastInsertId();
    for ($i = 1; $i <= $workerCount; $i++) {
        $userInsert->execute([
            'rc-t10-agent-' . strtolower($tag) . '-' . $i,
            'RC T10 Agent ' . $i,
            'agent',
            $departmentId,
        ]);
        $userIds[] = (int) $pdo->lastInsertId();
    }

    $category = $pdo->prepare('INSERT INTO categories (name, service_group) VALUES (?, ?)');
    $category->execute(['RC T10 Category ' . $tag, 'it']);
    $categoryId = (int) $pdo->lastInsertId();

    $service = $pdo->prepare('INSERT INTO service_catalog (name, code, category_id, service_group) VALUES (?, ?, ?, ?)');
    $service->execute(['RC T10 Service ' . $tag, 'T10-' . $tag, $categoryId, 'it']);
    $serviceId = (int) $pdo->lastInsertId();

    $ticket = $pdo->prepare(
        'INSERT INTO tickets (subject, description, requester_id, assigned_to, department_id,
                              category_id, service_id, service_group, status)
         VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?)'
    );
    $ticket->execute([
        'RC T10 concurrent claim ' . $tag,
        'Fifty workers compete for one claim transition',
        $requesterId,
        $departmentId,
        $categoryId,
        $serviceId,
        'it',
        'new',
    ]);
    $ticketId = (int) $pdo->lastInsertId();
    t10_check($ticketId > 0, 'created one unassigned new ticket for 50 Claim contenders');

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    foreach ($userIds as $agentId) {
        $process = proc_open(
            [PHP_BINARY, $workerPath, (string) $ticketId, (string) $agentId],
            $descriptors,
            $pipes,
            dirname(__DIR__)
        );
        if (!is_resource($process)) {
            throw new RuntimeException('Could not start Claim worker for agent ' . $agentId);
        }
        if (isset($pipes[0]) && is_resource($pipes[0])) {
            fclose($pipes[0]);
        }
        $processes[] = ['process' => $process, 'pipes' => $pipes, 'agent_id' => $agentId];
    }

    $deadline = microtime(true) + 60;
    $ready = 0;
    do {
        $ready = (int) $pdo->query('SELECT ready_count FROM rc_t10_claim_barrier WHERE id = 1')->fetchColumn();
        if ($ready >= $workerCount) {
            break;
        }
        usleep(50000);
    } while (microtime(true) < $deadline);

    $pdo->exec('UPDATE rc_t10_claim_barrier SET release_flag = 1 WHERE id = 1');
    t10_check($ready === $workerCount, 'all 50 independent workers reached the start barrier (' . $ready . '/' . $workerCount . ')');

    $wins = 0;
    $losses = 0;
    $errors = [];
    foreach ($processes as $item) {
        $stdout = stream_get_contents($item['pipes'][1]);
        $stderr = stream_get_contents($item['pipes'][2]);
        fclose($item['pipes'][1]);
        fclose($item['pipes'][2]);
        $exitCode = proc_close($item['process']);
        if ($exitCode !== 0 || trim((string) $stderr) !== '') {
            $errors[] = 'agent ' . $item['agent_id'] . ' exit=' . $exitCode . ' stderr=' . trim((string) $stderr);
        }
        if (preg_match('/^WIN\s+/m', (string) $stdout)) {
            $wins++;
        } elseif (preg_match('/^LOSE\s+/m', (string) $stdout)) {
            $losses++;
        } else {
            $errors[] = 'agent ' . $item['agent_id'] . ' unexpected output=' . trim((string) $stdout);
        }
    }

    t10_check($errors === [], 'all worker processes completed without runtime errors');
    t10_check($wins === 1 && $losses === 49, 'exactly one of 50 simultaneous Claim transitions won (wins=' . $wins . ', losses=' . $losses . ')');

    $stateStmt = $pdo->prepare('SELECT status, assigned_to, assigned_at FROM tickets WHERE id = ?');
    $stateStmt->execute([$ticketId]);
    $state = $stateStmt->fetch();
    t10_check(
        is_array($state)
            && $state['status'] === 'assigned'
            && in_array((int) $state['assigned_to'], $userIds, true)
            && !empty($state['assigned_at']),
        'ticket has exactly one persisted agent assignment and assigned_at timestamp'
    );

    echo 'PASS: T10 50-worker Claim race on MariaDB ' . $pdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;
} finally {
    foreach ($processes as $item) {
        if (is_resource($item['process'])) {
            @proc_terminate($item['process']);
            foreach ([1, 2] as $index) {
                if (isset($item['pipes'][$index]) && is_resource($item['pipes'][$index])) {
                    @fclose($item['pipes'][$index]);
                }
            }
            @proc_close($item['process']);
        }
    }
    if ($ticketId > 0) {
        $stmt = $pdo->prepare('DELETE FROM tickets WHERE id = ?');
        $stmt->execute([$ticketId]);
    }
    if ($serviceId > 0) {
        $stmt = $pdo->prepare('DELETE FROM service_catalog WHERE id = ?');
        $stmt->execute([$serviceId]);
    }
    if ($categoryId > 0) {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
    }
    if ($userIds !== []) {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $pdo->prepare('DELETE FROM users WHERE id IN (' . $placeholders . ')');
        $stmt->execute($userIds);
    }
    if ($departmentId > 0) {
        $stmt = $pdo->prepare('DELETE FROM departments WHERE id = ?');
        $stmt->execute([$departmentId]);
    }
    $pdo->exec('DROP TABLE IF EXISTS rc_t10_claim_barrier');
}
