<?php
declare(strict_types=1);

/**
 * Independent Request Center entry point.
 * Legacy page rendering is routed inside the module; old POST actions remain
 * compatible through index.php until their approved migration is complete.
 */
if (isset($_GET['page'])) {
    require __DIR__ . '/modules/request-center/Pages/legacy-router.php';
    exit;
}
require __DIR__ . '/modules/request-center/Pages/index.php';
