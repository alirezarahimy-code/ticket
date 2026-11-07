<?php
declare(strict_types=1);

/**
 * Compatibility entry point for the independent Request Center workspace.
 *
 * The implementation is owned by modules/request-center/Pages/index.php.
 * Keep this root route stable for existing bookmarks and integrations.
 */
require __DIR__ . '/modules/request-center/Pages/index.php';
