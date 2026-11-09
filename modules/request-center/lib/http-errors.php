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
 * @param array|null $user کاربر جاری (در صورت وجود)؛ بلوک «راهنمای رفع ادمین» فقط برای ادمین اصلی نمایش داده می‌شود
 */
function request_center_error_page(int $status, string $title, string $message, array $problems = [], ?array $user = null): never
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
    $fixBlock = '';
    if ($status === 503) {
        $rcIsAdmin = is_array($user) && function_exists('user_is_primary_admin') && user_is_primary_admin($user);
        if (!$rcIsAdmin) {
            $fixBlock = '<div class="rc-error-fix">'
                . '<p class="rc-error-fix-note">این بخش به دلیل تکمیل‌نبودن ساختار پایگاه‌داده موقتاً غیرفعال است؛ فقط ادمین سامانه می‌تواند آن را رفع کند. همین صفحه را نزد او ببرید.</p>'
                . '</div>';
        } else {
        $fixBlock = '<div class="rc-error-fix">'
            . '<h2>راهنمای رفع این خطا (ادمین — حدود ۵ دقیقه)</h2>'
            . '<ol>'
            . '<li>ابتدا از پایگاه‌داده بکاپ بگیرید:<br>ویندوز (XAMPP):<br><code>C:\xampp\mysql\bin\mariadb-dump.exe -u root -p &lt;dbname&gt; &gt; C:\backup\rc-backup.sql</code><br>لینوکس:<br><code>mariadb-dump -u root -p &lt;dbname&gt; &gt; ~/rc-backup.sql</code></li>'
            . '<li>مهاجرت <code>migrations/035-request-center-appointments.sql</code> را یک‌باره اعمال کنید (idempotent؛ اجرای دوباره بی‌خطر است):<br>ویندوز (XAMPP):<br><code>C:\xampp\mysql\bin\mariadb.exe -u root -p &lt;dbname&gt; &lt; migrations\035-request-center-appointments.sql</code><br>لینوکس:<br><code>mariadb -u root -p &lt;dbname&gt; &lt; migrations/035-request-center-appointments.sql</code></li>'
            . '<li>اگر پیش‌تر نسخهٔ قدیمیِ همان فایل در <code>schema_updates</code> ثبت شده بود، بعد از اعمال دستی فقط همان ردیف را پاک کنید:<br><code>DELETE FROM schema_updates WHERE migration = \'035-request-center-appointments.sql\';</code><br>(خودِ اجرای دستی کافی است؛ این فقط ثبت را هم‌تراز می‌کند.)</li>'
            . '<li>صفحه را دوباره بارگذاری کنید. اگر باز هم ۵۰۳ گرفتید، ستون‌های <code>tickets.rejected_at</code> و جدول <code>request_center_appointments</code> را در دیتابیس بررسی کنید.</li>'
            . '</ol>'
            . '<p class="rc-error-fix-note">این خطا عمداً هیچ تغییری در پایگاه‌داده ایجاد نمی‌کند؛ اعمال مهاجرت فقط با دستور شما انجام می‌شود.</p>'
            . '</div>';
        }
    }

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
        . 'a{display:inline-block;background:#1d4ed8;color:#fff;text-decoration:none;padding:9px 18px;border-radius:9px;margin-inline-end:8px}'
        . 'a.secondary{background:#fff;color:#1d4ed8;border:1px solid #c7d2fe}'
        . 'a:hover{background:#1e40af}'
        . '.rc-error-fix{background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;margin:0 0 16px;color:#573a06;font-size:13px;line-height:2}'
        . '.rc-error-fix h2{font-size:14px;margin:0 0 8px;color:#92400e}'
        . '.rc-error-fix ol{margin:0;padding-inline-start:20px}'
        . '.rc-error-fix code{display:inline-block;background:#fff;border:1px solid #fcd34d;border-radius:6px;padding:2px 7px;font-size:11px;direction:ltr;unicode-bidi:embed;margin:2px 0}'
        . '.rc-error-fix-note{margin:10px 0 0;font-size:11px;color:#92400e;border-top:1px dashed #f5d682;padding-top:8px}'
        . '</style></head><body>'
        . '<main class="rc-error-card" role="alert">'
        . '<p class="rc-error-code">خطای ' . $status . '</p>'
        . '<h1>' . $titleHtml . '</h1>'
        . '<p>' . $messageHtml . '</p>'
        . $problemsBlock
        . $fixBlock
        . '<a class="rc-error-home" href="index.php">بازگشت به سامانهٔ اصلی</a>'
        . '<a class="rc-error-home secondary" href="request-center.php">تلاش دوباره: مرکز درخواست‌ها</a>'
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

