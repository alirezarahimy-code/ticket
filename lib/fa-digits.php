<?php
declare(strict_types=1);

/**
 * lib/fa-digits.php — لایهٔ نمایشی سراسری: فارسی‌سازی اعداد در خروجی HTML (۱٫۶۳).
 *
 * چرا این‌جا: قبلاً خروجی همهٔ صفحات با sprintf/number_format/echo ارقام لاتین
 * چاپ می‌شد و سیستم هیچ لایهٔ یکپارچه‌ای نداشت؛ هر ماژول فقط در چند نقطهٔ
 * دستی (مثل تقویم و برنامهٔ غذا) تبدیل می‌کرد. حالا «متنِ قابل نمایش» هنگام
 * ارسال تبدیل می‌شود و «دادهٔ ماشینی» (اتربیوت‌ها، فرم‌ها، JSON، اسکریپت) دست‌نخورده می‌ماند.
 *
 * مرزها (عمدی، نه سهوی):
 *   • فقط متنِ داخل تگ‌ها تبدیل می‌شود؛ مقدار اتربیوت‌ها (id، href، value، csrf…) ثابت می‌ماند.
 *   • محتوای <script>، <style>، <pre>، <code>، <textarea> و کامنت‌های <!-- --> کلاً تبدیل نمی‌شوند.
 *   • توکن‌های ماشینی داخل متن: آدرس‌های IP و ایمیل‌ها لاتین می‌مانند.
 *   • خروجی غیر HTML فقط وقتی تبدیل می‌شود که Content-Type صریحاً text/html یا application/xhtml+xml باشد؛
 *     نبودِ Content-Type صریح (صفحات PHP که پیش‌فرض text/html هستند) هم تبدیل می‌شود.
 *     JSON، XLSX، text/plain (عیب‌یابی/دانلود ماشینی) و همهٔ دانلودها دست‌نخورده می‌مانند.
 *   • اگر display.fa_digits در تنظیمات «۰» شود، لایه غیرفعال است (راه برگشت اضطراری).
 */

/** نگاشت ارقام لاتین به فارسی (کلیدهای strtr با توجه به تطبیق طولانی‌ترین — اینجا همهٔ تک‌کاراکتری‌اند). */
function fa_digits_map(): array
{
    return ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'];
}

/** تبدیل ارقام متن، با حفظ توکن‌های ماشینی (IP/ایمیل). */
function fa_digits_text(string $text): string
{
    if ($text === '' || !preg_match('/[0-9]/', $text)) {
        return $text;
    }
    $tokens = [];
    // NB: در ورودی باینری/UTF-8 نامعتبر، preg_replace_callback مقدار null می‌دهد؛
    // در آن صورت متن اصلی را دست‌نخورده برمی‌گردانیم تا هیچ بایتی پاک/جابجا نشود.
    $swapped = preg_replace_callback(
        '/(?:\d{1,3}(?:\.\d{1,3}){3}|[\w._%+\-]+@[\w\-]+(?:\.[\w\-]+)+)/u',
        static function (array $m) use (&$tokens): string {
            // کلید فقط حروف دارد تا مرحلهٔ نگاشت رقم‌ها به آن دست نزند
            $key = "\x00" . str_repeat('q', count($tokens) + 1) . "\x00";
            $tokens[$key] = $m[0];
            return $key;
        },
        $text
    );
    if (!is_string($swapped)) {
        return $text;
    }
    $text = strtr($swapped, fa_digits_map());
    if ($tokens !== []) {
        $text = strtr($text, $tokens);
    }
    return $text;
}

/** تگ‌های بلوک‌ای که محتوای‌شان نباید تبدیل شود (اسکریپت/استایل/پیش‌نمایش/کد/فرم متن). */
function fa_digits_skip_tags(): array
{
    return ['script', 'style', 'pre', 'code', 'textarea'];
}

/**
 * تبدیل ارقامِ «فقط متن» یک HTML. اسکنر تک‌پاسه با آگاهی از تگ‌ها:
 * کامنت‌ها، تگ‌ها (با شناسایی رشته‌های داخل دابل/سینگل‌کوتیشن)، بلوک‌های اسکیپ
 * و متن‌های آزاد را از هم جدا می‌کند. در صورت هر خطایی، ورودی را دست‌نخورده برمی‌گرداند.
 */
function fa_digits_html(string $html): string
{
    try {
        $skips = fa_digits_skip_tags();
        $out = '';
        $buf = '';
        $len = strlen($html);
        $i = 0;
        while ($i < $len) {
            $ch = $html[$i];
            if ($ch !== '<') {
                $buf .= $ch;
                $i++;
                continue;
            }
            // شروع تگ: ابتدا متن خانه‌شده را تبدیل کن
            $out .= fa_digits_text($buf);
            $buf = '';
            // کامنت؟
            if (substr_compare($html, '<!--', $i, 4) === 0) {
                $end = strpos($html, '-->', $i + 4);
                $end = $end === false ? $len - 3 : $end;
                $out .= substr($html, $i, $end + 3 - $i);
                $i = $end + 3;
                continue;
            }
            // آیا یکی از بلوک‌های اسکیپ باز می‌شود؟
            $skipTag = null;
            foreach ($skips as $tag) {
                $openLen = strlen($tag) + 1; // '<script'
                if (strncasecmp(substr($html, $i, $openLen + 1), '<' . $tag, $openLen) === 0) {
                    $next = $html[$i + $openLen] ?? '>';
                    // حرف مرز باید > یا فاصله/تب/سطر جدید باشد تا '<scanner' اشتباه نگیرد
                    if ($next === '>' || ctype_space($next) || $next === '/') {
                        $skipTag = $tag;
                        break;
                    }
                }
            }
            if ($skipTag !== null) {
                $close = '</' . $skipTag;
                $closeAt = stripos($html, $close, $i);
                if ($closeAt === false) {
                    $out .= substr($html, $i);
                    return $out;
                }
                $closeEnd = strpos($html, '>', $closeAt);
                $closeEnd = $closeEnd === false ? $len - 1 : $closeEnd;
                $out .= substr($html, $i, $closeEnd + 1 - $i);
                $i = $closeEnd + 1;
                continue;
            }
            // کپیِ کامل تگ با آگاهی از کوتیشن‌ها (محتوای اتربیوت‌ها هرگز تبدیل نمی‌شود)
            $out .= '<';
            $j = $i + 1;
            $quote = null;
            while ($j < $len) {
                $c = $html[$j];
                if ($quote !== null) {
                    $out .= $c;
                    if ($c === $quote) {
                        $quote = null;
                    }
                    $j++;
                    continue;
                }
                if ($c === '"' || $c === "'") {
                    $quote = $c;
                    $out .= $c;
                    $j++;
                    continue;
                }
                $out .= $c;
                $j++;
                if ($c === '>') {
                    break;
                }
            }
            $i = $j;
        }
        $out .= fa_digits_text($buf);
        return $out;
    } catch (Throwable) {
        return $html;
    }
}

/**
 * کال‌بک بافر خروجی. فقط برای پاسخ‌های HTML/XML تبدیل می‌کند؛ هر نوع دیگری
 * (JSON، دانلودها و binary) را خط‌به‌خط برمی‌گرداند. هر خطایی ⇒ خروجی اصلی.
 */
function fa_digits_output_callback(string $buffer): string
{
    try {
        if ($buffer === '') {
            return $buffer;
        }
        if (function_exists('headers_list')) {
            $headers = headers_list();
            foreach ($headers as $header) {
                if (stripos($header, 'content-type:') === 0) {
                    $value = strtolower(trim(substr($header, strlen('content-type:'))));
                    // فقط نوع‌های نمایشی HTML مجازند؛ «application/vnd.openxmlformats-
                    // officedocument.spreadsheetml.sheet» ناخواسته شامل «xml» است و text/plain
                    // خروجی ماشینی/عیب‌یابی است — هیچ‌کدام تبدیل نمی‌شوند.
                    $isHtml = str_contains($value, 'text/html') || str_contains($value, 'application/xhtml+xml');
                    if (!$isHtml) {
                        return $buffer; // JSON/آیاکسل/دانلود/… تبدیل نمی‌شود
                    }
                }
            }
        }
        return fa_digits_html($buffer);
    } catch (Throwable) {
        return $buffer;
    }
}
