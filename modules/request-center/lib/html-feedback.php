<?php
declare(strict_types=1);

/**
 * رندر inline پیام‌های flash برای صفحات «تمام‌صفحهٔ ماژول» (آن‌هایی که از چرام
 * هسته استفاده نمی‌کنند و take_flash() را خودشان باید چاپ کنند).
 *
 * خودکفا است: استایل inline حداقلی دارد و به هیچ CSS بیرونی وابسته نیست.
 * در صفحات با چرام هسته (render_header) استفاده نشود — پیام‌ها آنجا دوبار چاپ می‌شوند.
 */
function request_center_render_flashes(): void
{
    if (!function_exists('take_flash')) {
        return;
    }
    $flashes = take_flash();
    if ($flashes === []) {
        return;
    }
    $escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    static $stylePrinted = false;
    if (!$stylePrinted) {
        $stylePrinted = true;
        echo '<style>.rc-flashes{display:flex;flex-direction:column;gap:8px;margin:0 0 14px}'
            . '.rc-flashes .rc-flash{border:1px solid;border-radius:10px;padding:10px 14px;font-size:13px;line-height:1.9}'
            . '.rc-flash-success{background:#ecfdf3;border-color:#abefc6;color:#027a48}'
            . '.rc-flash-info{background:#eff8ff;border-color:#b2ddff;color:#175cd3}'
            . '.rc-flash-warning{background:#fffaeb;border-color:#fedf89;color:#b54708}'
            . '.rc-flash-error,.rc-flash-danger{background:#fef3f2;border-color:#fecdca;color:#b42318}'
            . '</style>';
    }
    echo '<div class="rc-flashes" aria-live="polite">';
    foreach ($flashes as $flash) {
        $type = (string) ($flash['type'] ?? 'info');
        if (!in_array($type, ['success', 'info', 'warning', 'error', 'danger'], true)) {
            $type = 'info';
        }
        $role = in_array($type, ['error', 'danger'], true) ? 'alert' : 'status';
        echo '<div class="rc-flash rc-flash-' . $escape($type) . '" role="' . $role . '">' . $escape($flash['message'] ?? '') . '</div>';
    }
    echo '</div>';
}
