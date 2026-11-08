<?php
declare(strict_types=1);

/**
 * Stable Request Center entry point.
 * The dedicated workspace UI uses the module read interface; legacy operational
 * pages/actions continue through the complete system front controller.
 * Phase 3: POST requests owned by the Request Center registry run through the
 * module runner with the identical legacy POST protocol; anything else is
 * forwarded in-process to the front controller (no redirect, payload intact).
 */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // تقویم/نوبت‌های رسیدگی، POST اختصاصی خودشان را دارند (بدون کلید action)؛
    // مثل مسیر GET مستقیماً به داخل شاخهٔ POST مربوطهٔ ماژول می‌روند.
    if ((string) ($_GET['calendar'] ?? '') === '1') {
        require __DIR__ . '/modules/request-center/Pages/index.php';
        exit;
    }
    require_once __DIR__ . '/modules/request-center/post-handler.php';
    $rcPostAction = (string) ($_POST['action'] ?? '');
    if (request_center_is_post_action($rcPostAction)) {
        request_center_handle_post(); // self-loads bootstrap; ends via redirect/exit
        exit; // unreachable
    }
    require __DIR__ . '/index.php'; // forward in-process; no redirect, payload intact
    exit;
}

if (isset($_GET['page'])) {
    require __DIR__ . '/index.php';
    exit;
}
require __DIR__ . '/modules/request-center/Pages/index.php';
