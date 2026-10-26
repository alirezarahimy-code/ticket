<?php
declare(strict_types=1);

/** Minimal CI router: dispatch only authenticated food endpoints; deliberately bypass index.php boot hooks. */
$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';
require_once $root . '/food-ticket.php';

$page = trim((string) ($_GET['page'] ?? ''));
$route = (string) ($_GET['food_api'] ?? '');
$user = current_user() ?? [];
if ($route === '') {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'food_api route required'], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($page === 'food-order') {
    food_order_api_handle($route, $user);
}
if ($page === 'food-ticket') {
    food_ticket_api_handle($route, $user);
}
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['error' => 'food-only router rejects non-food paths'], JSON_UNESCAPED_UNICODE);
exit;
