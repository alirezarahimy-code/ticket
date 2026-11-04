<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'itsm-audit-outage-' . bin2hex(random_bytes(5));
mkdir($root, 0750, true);
define('APP_ROOT', $root);
function cfg(string $key, $default = null) { return $default; }
function db(bool $reconnect = false): PDO { throw new PDOException('SQLSTATE[HY000] [2002] connection refused'); }
require dirname(__DIR__) . '/lib/logging-core.php';
require dirname(__DIR__) . '/lib/activity-audit.php';
$_SESSION = ['user_id'=>42, 'audit_user'=>['id'=>42,'username'=>'operator','full_name'=>'Operator Name','role'=>'admin']];
$_SERVER['REMOTE_ADDR'] = '192.0.2.44';
$resultDefaults = activity_default_result('access_denied') === 'denied'
    && activity_default_result('login_failed') === 'failure'
    && activity_default_result('ticket_updated') === 'success'
    && activity_default_result('unknown') === 'unknown';
activity_log([
    'action_code'=>'update_user_role', 'action_label'=>'تغییر نقش کاربر', 'module'=>'permissions',
    'target_type'=>'user', 'target_id'=>9001, 'target_label'=>'کاربر آزمایشی',
    'result'=>'success', 'meta'=>['operation'=>'role-change','changed_fields'=>['role']],
]);
$files = glob($root . '/storage/logs/spool/activity-*.jsonl') ?: [];
$payload = [];
if ($files) {
    $rows = file($files[0], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $record = json_decode((string) ($rows[0] ?? ''), true);
    $payload = is_array($record['payload'] ?? null) ? $record['payload'] : [];
}
$ok = $resultDefaults && count($files) === 1
    && (int) ($payload['user_id'] ?? 0) === 42
    && (string) ($payload['action_code'] ?? '') === 'update_user_role'
    && (string) ($payload['target_type'] ?? '') === 'user'
    && (int) ($payload['target_id'] ?? 0) === 9001
    && (string) ($payload['result'] ?? '') === 'success'
    && isset($payload['meta']['operation']);
echo ($ok ? '[PASS] ' : '[FAIL] ') . 'complete audit event is spooled when DB is unavailable before any identity lookup' . PHP_EOL;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($it as $item) $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
@rmdir($root);
if (!$ok) exit(1);
