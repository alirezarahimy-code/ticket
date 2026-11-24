<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
$user = require_login();
require_once __DIR__ . '/modules/offline-map/MapRepository.php';

$respond = static function (int $status, array $payload): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

if (!user_can($user, 'map.view')) {
    security_log_access_denied($user, 'map.view', 'offline-map');
    $respond(403, ['error' => 'دسترسی به نقشه مجاز نیست.']);
}
$repo = new OfflineMapRepository(db());
if (!$repo->ready()) {
    $respond(503, ['error' => 'ساختار پایگاه‌دادهٔ نقشه نصب نشده است. مهاجرت 038 را اجرا کنید.']);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $q = trim((string) ($_GET['q'] ?? ''));
    if (strlen($q) > 120) {
        $respond(422, ['error' => 'عبارت جست‌وجو بیش از حد طولانی است.']);
    }
    $respond(200, ['features' => $repo->search($q)]);
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: GET, POST');
    $respond(405, ['error' => 'روش درخواست پشتیبانی نمی‌شود.']);
}
require_csrf(false);
$action = (string) ($_POST['action'] ?? 'create');
if ($action === 'delete') {
    if (!user_can($user, 'map.features.manage') && !user_can($user, 'map.admin')) {
        $respond(403, ['error' => 'مجوز مدیریت عوارض نقشه را ندارید.']);
    }
    $deleted = $repo->delete(max(0, (int) ($_POST['id'] ?? 0)), (int) $user['id'], user_can($user, 'map.admin'));
    $respond($deleted ? 200 : 404, ['deleted' => $deleted]);
}
if (!user_can($user, 'map.features.manage') && !user_can($user, 'map.admin')) {
    $respond(403, ['error' => 'مجوز ثبت عوارض نقشه را ندارید.']);
}
$title = trim((string) ($_POST['title'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$longitude = filter_var($_POST['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
$latitude = filter_var($_POST['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
if ($title === '' || mb_strlen($title) > 190 || mb_strlen($description) > 4000
    || $longitude === false || $latitude === false
    || $longitude < 44 || $longitude > 64 || $latitude < 24 || $latitude > 40) {
    $respond(422, ['error' => 'عنوان، توضیحات یا مختصات معتبر نیست.']);
}
try {
    $id = $repo->create($title, $description, [
        'type' => 'Point',
        'coordinates' => [(float) $longitude, (float) $latitude],
    ], (int) $user['id']);
    $respond(201, ['id' => $id, 'message' => 'نقطه با موفقیت ثبت شد.']);
} catch (Throwable $exception) {
    error_log('[offline-map] save failed: ' . $exception->getMessage());
    $respond(500, ['error' => 'ثبت نقطه انجام نشد. گزارش فنی در لاگ سرور ثبت شد.']);
}
