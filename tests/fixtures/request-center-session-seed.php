<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

$userId = (int) ($_GET['user_id'] ?? 0);
if ($userId <= 0) {
    http_response_code(500);
    exit('missing CI_T04_USER_ID');
}
$_SESSION['user_id'] = $userId;
$currentUser = function_exists('current_user') ? current_user() : null;
$databaseName = (string) db()->query('SELECT DATABASE()')->fetchColumn();
$payload = [
    'cookie_name' => session_name(),
    'session_id' => session_id(),
    'csrf' => csrf_token(),
    'current_user_id' => (int) ($currentUser['id'] ?? 0),
    'database_name' => $databaseName,
];
session_write_close();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($payload, JSON_THROW_ON_ERROR);
