<?php
declare(strict_types=1);

/**
 * Independent GET router for Request Center pages that still preserve legacy
 * rendering and POST contracts. Writes remain on the established index.php
 * action bridge until their approved behavioral migration is complete.
 */
require dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__) . '/WorkflowService.php';

$user = require_login();
$page = (string) ($_GET['page'] ?? '');
$routes = [
    'services' => __DIR__ . '/services.php',
    'queue' => __DIR__ . '/queue.php',
    'new-ticket' => __DIR__ . '/create.php',
    'ticket' => __DIR__ . '/detail.php',
    'analytics' => __DIR__ . '/analytics.php',
    'reports' => __DIR__ . '/reports.php',
    'supervisor' => __DIR__ . '/supervisor.php',
];

if (!isset($routes[$page])) {
    http_response_code(404);
    header('X-Content-Type-Options: nosniff');
    exit('مسیر مرکز درخواست‌ها پیدا نشد.');
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: private, no-store');
require $routes[$page];
