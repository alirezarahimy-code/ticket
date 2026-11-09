<?php
declare(strict_types=1);

/**
 * صفحات خطای یکدست ماژول Request Center.
 *
 * این فایل عمداً به هیچ کتابخانهٔ چرام سایت (render_header/render_footer) یا
 * تنظیمات پایگاه‌داده وابسته نیست: صفحات خطا ممکن است دقیقاً همان زمانی
 * لازم شوند که وابستگی‌های چرام خودشان ناسالم‌اند (مثلاً اسکیما ناقص است).
 * بنابراین خروجی حداقلی، خودکفا و بدون هرگونه وابستگی بیرونی است.
 *
 * ماتریس کامل و سیاست 403/404/503/500 در docs/error-handling.md (ماژول)
 * توضیح داده شده است.
 */

/**
 * صفحهٔ خطای یکدست ماژول را چاپ می‌کند و خارج می‌شود.
 *
 * @param int   $status   کد وضعیت HTTP (باید در بازهٔ 400 تا 599 باشد؛ در غیر این صورت 500)
 * @param string $title    عنوان کوتاه خطا (فارسی)
 * @param string $message  توضیح کاربرپسند بدون افشای جزئیات فنی
 * @param array<int,string> $problems فهرست اختیاری موارد ناقص (برای 503)؛ هرگز دادهٔ حساس نباشد
 */
function request_center_error_page(int $status, string $title, string $message, array $problems = []): never
{
    $status = ($status >= 400 && $status <= 599) ? $status : 500;

    http_response_code($status);
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
    }

    $escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $titleHtml = $escape($title);
    $messageHtml = $escape($message);
    $problemsHtml = '';
    foreach ($problems as $problem) {
        $problemsHtml .= '<li>' . $escape($problem) . '</li>';
    }
    $problemsBlock = $problemsHtml !== '' ? "<ul class=\"rc-error-problems\">{$problemsHtml}</ul>" : '';

    echo '<!doctype html>'
        . '<html lang="fa" dir="rtl"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . $titleHtml . ' — مرکز درخواست‌ها</title>'
        . '<style>'
        . 'body{font-family:system-ui,-apple-system,"Segoe UI",Tahoma,sans-serif;background:#f5f7fb;margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;box-sizing:border-box}'
        . '.rc-error-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;max-width:560px;width:100%;padding:28px 30px;box-shadow:0 8px 30px rgba(25,55,90,.08)}'
        . '.rc-error-code{font-size:13px;color:#b91c1c;letter-spacing:.12em;font-weight:700;margin:0 0 6px}'
        . 'h1{font-size:20px;margin:0 0 10px;color:#172b4d}'
        . 'p{color:#47566e;margin:0 0 14px;line-height:1.9}'
        . '.rc-error-problems{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 26px;color:#7f1d1d;margin:0 0 16px;line-height:1.9}'
        . 'a{display:inline-block;background:#1d4ed8;color:#fff;text-decoration:none;padding:9px 18px;border-radius:9px}'
        . 'a:hover{background:#1e40af}'
        . '</style></head><body>'
        . '<main class="rc-error-card" role="alert">'
        . '<p class="rc-error-code">خطای ' . $status . '</p>'
        . '<h1>' . $titleHtml . '</h1>'
        . '<p>' . $messageHtml . '</p>'
        . $problemsBlock
        . '<a href="request-center.php">بازگشت به مرکز درخواست‌ها</a>'
        . '</main></body></html>';
    exit;
}

/**
 * گارد دسترسی مستقیم فایل‌های ماژول: بدون هیچ وابستگی، 404 ساده برمی‌گرداند.
 * (در ۵۰۳ باید قابل اجرا باشد؛ پس عمداً به این فایل وابسته نمی‌شود.)
 */
function request_center_direct_access_guard(): never
{
    http_response_code(404);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
    }
    exit;
}

