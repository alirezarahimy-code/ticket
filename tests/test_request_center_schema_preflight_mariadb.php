<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/modules/request-center/RequestCenterRepository.php';

$host = (string) (getenv('CI_DB_HOST') ?: '127.0.0.1');
$port = (string) (getenv('CI_DB_PORT') ?: '3306');
$name = (string) (getenv('CI_DB_NAME') ?: 'persian_ticketing_ci');
$user = (string) (getenv('CI_DB_USER') ?: 'root');
$pass = (string) (getenv('CI_DB_PASS') ?: '');
$dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$repository = new RequestCenterRepository($pdo);
$problems = $repository->schemaProblems();
if ($problems !== []) {
    fwrite(STDERR, "FAIL: Request Center schema preflight\n" . implode("\n", $problems) . "\n");
    exit(1);
}
echo "PASS: Request Center required tables and columns exist on MariaDB " .
    $pdo->query('SELECT VERSION()')->fetchColumn() . "\n";
