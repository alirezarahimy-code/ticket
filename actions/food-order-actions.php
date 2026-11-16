<?php

declare(strict_types=1);

/**
 * Legacy food-order cancellation decision handler.
 *
 * This action belongs to the Food module, not Request Center. It remains in
 * the legacy POST execution context until the Food module receives its own
 * independent action entry point.
 */
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

if ($action === 'food_order_cancel_decide') {
    $reviewer = require_login();
    if (!function_exists('food_order_cancel_request_decide')) {
        throw new RuntimeException('ماژول سفارش غذا در دسترس نیست.');
    }
    $result = food_order_cancel_request_decide(
        (int) ($_POST['request_id'] ?? 0),
        (string) ($_POST['decision'] ?? ''),
        $reviewer,
        (string) ($_POST['note'] ?? '')
    );
    flash('success', (string) ($result['message'] ?? 'انجام شد.'));
    redirect('index.php?page=dashboard#food-cancel-requests');
}
