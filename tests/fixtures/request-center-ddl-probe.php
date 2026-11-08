<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/bootstrap.php';

// CI-only diagnostic endpoint. tests/.htaccess denies HTTP access under Apache.
// The PHP built-in server used by the disposable CI test does not interpret .htaccess.
ticket_service_ensure_schema();

header('Content-Type: text/plain; charset=utf-8');
echo "request-center ddl probe ok\n";
