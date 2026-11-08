<?php
declare(strict_types=1);

/**
 * Request Center action registry.
 *
 * The legacy front controller currently supplies the execution context
 * (CSRF validation, authenticated session, shared helpers, logging and the
 * surrounding exception/transaction handling). This file centralizes module
 * action ownership without pretending that the runtime is already standalone.
 *
 * Keep only Request Center actions here. Cross-module actions must stay with
 * their owning module until that module has its own dispatcher.
 */
require __DIR__ . '/supervisor-actions.php';
require __DIR__ . '/ticket-actions.php';
require __DIR__ . '/service-actions.php';
require __DIR__ . '/bulk-actions.php';
