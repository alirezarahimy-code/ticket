<?php
declare(strict_types=1);

/**
 * Stable Request Center entry point.
 * The dedicated workspace UI uses the module read interface; legacy operational
 * pages/actions continue through the complete system front controller.
 */
if (isset($_GET['page'])) {
    require __DIR__ . '/index.php';
    exit;
}
require __DIR__ . '/modules/request-center/Pages/index.php';
