<?php
declare(strict_types=1);

/**
 * api/system-alarms.php — آژیر سلامت سامانه (فقط‌خواندنی + تأیید بستن مدیر)
 *
 *   GET  → {ok:true, alarms:[...], server_time:int}
 *   POST {action:"ack", keys:["..."]}  (هدر X-CSRF-Token) → تأیید مدیر + SYS-ALARM-ACK در system_logs
 *
 * فقط کاربر دارای نقش مدیریتی (setting system_alarm_roles؛ پیش‌فرض primary_admin,admin,supervisor)
 * و وقتی system_alarm_enabled ≠ 0. خروجی JSON؛ هیچ تغییری روی دادهٔ کسب‌وکار ندارد.
 */

require dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/lib/system-alarms.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$json = static function (int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

$user = function_exists('current_user') ? current_user() : null;
if (!$user) {
    $json(401, ['ok' => false, 'error' => 'unauthorized']);
}
if (!system_alarm_user_can_see($user)) {
    $json(403, ['ok' => false, 'error' => 'forbidden']);
}

$method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    try {
        $json(200, ['ok' => true, 'alarms' => system_alarms_visible(), 'server_time' => time()]);
    } catch (Throwable $e) {
        $json(500, ['ok' => false, 'error' => 'alarm_engine_failed']);
    }
}

if ($method !== 'POST') {
    $json(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

$raw = (string) file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    $payload = [];
}
if ((string) ($payload['action'] ?? '') !== 'ack') {
    $json(400, ['ok' => false, 'error' => 'unknown_action']);
}

// CSRF برای درخواست JSON: هدر X-CSRF-Token باید با توکن نشست هم‌خوان باشد
$expected = function_exists('csrf_token') ? csrf_token() : '';
$provided = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
    $json(419, ['ok' => false, 'error' => 'bad_csrf']);
}

$keys = $payload['keys'] ?? [];
if (!is_array($keys)) {
    $keys = [$keys];
}
$result = system_alarm_acknowledge($keys, $user);
$json(!empty($result['ok']) ? 200 : 400, $result);
