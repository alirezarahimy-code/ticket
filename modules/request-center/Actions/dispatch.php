<?php
declare(strict_types=1);

/**
 * Request Center action registry.
 *
 * The Request Center POST runner supplies the execution context
 * (authenticated session, shared helpers, CSRF, logging and the common error
 * boundary). The legacy front controller remains a compatibility caller; it
 * is no longer the exclusive runtime for module-owned lifecycle actions.
 *
 * Keep only Request Center actions here. Cross-module actions must stay with
 * their owning module until that module has its own dispatcher.
 */

// محافظ: رجیستری اکشن‌ها فقط داخل حوزهٔ اجرایی ماژول/کنترلر بارگذاری می‌شود.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

require __DIR__ . '/supervisor-actions.php';
require __DIR__ . '/ticket-actions.php';
require __DIR__ . '/service-actions.php';
require __DIR__ . '/bulk-actions.php';
require __DIR__ . '/admin-actions.php';
require __DIR__ . '/governance-actions.php';
require __DIR__ . '/ola-actions.php';
