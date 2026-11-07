<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/modules/request-center/WorkflowService.php';

$ticketId = (int) ($argv[1] ?? 0);
$agentId = (int) ($argv[2] ?? 0);
if ($ticketId <= 0 || $agentId <= 0) {
    fwrite(STDERR, "invalid worker arguments\n");
    exit(2);
}

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');
$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
$pdo->exec('SET SESSION innodb_lock_wait_timeout = 30');
$pdo->exec('UPDATE rc_t10_claim_barrier SET ready_count = ready_count + 1 WHERE id = 1');

$deadline = microtime(true) + 45;
while (true) {
    $release = (int) $pdo->query('SELECT release_flag FROM rc_t10_claim_barrier WHERE id = 1')->fetchColumn();
    if ($release === 1) {
        break;
    }
    if (microtime(true) > $deadline) {
        fwrite(STDERR, "barrier timeout\n");
        exit(3);
    }
    usleep(10000);
}

try {
    $pdo->beginTransaction();
    $workflow = new RequestCenterWorkflowService($pdo);
    $won = $workflow->transition(
        $ticketId,
        'new',
        'assigned',
        [
            'assigned_to' => $agentId,
            'assigned_at' => RequestCenterSqlExpression::now(),
        ],
        'standard',
        ['assigned_to' => null, 'cancel_requested_at' => null]
    );
    if ($won) {
        $pdo->commit();
        echo "WIN {$agentId}\n";
    } else {
        $pdo->rollBack();
        echo "LOSE {$agentId}\n";
    }
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'ERROR ' . $error->getMessage() . "\n");
    exit(4);
}
