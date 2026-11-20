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
 *   • محتوای <script>، <style>، <pre>، <code> و کامنت‌های <!-- --> کلاً تبدیل نمی‌شوند.
 *     (<textarea> از ۱٫۶۳٫۳ فارسی می‌شود: متن تایپیِ کاربر باید همان ارقام نمایشی را نشان دهد؛
 *      ذخیرهٔ canonical در پایگاه‌داده لاتین می‌ماند چون بوت‌استرپ ارقام ورودی را
 *      با fa_digits_normalize_deep() دوباره لاتین می‌کند.)
 *   • توکن‌های ماشینی داخل متن (IP، ایمیل، نام هاست/کامپیوتر مثل MBA4-01 و SRV-DC-01) لاتین می‌مانند.
 *   • خانوادهٔ «کد تیکت» (پیشوند فقط‌حروف + خط‌تیره + ۴+ رقم، مثل SUP-00009) جزء نام هاست نیست و
 *     طبق الزام محصول (همهٔ اعداد فارسی) فارسی چاپ می‌شود: SUP-۰۰۰۰۹.
 *   • خروجی غیر HTML فقط وقتی تبدیل می‌شود که Content-Type صریحاً text/html یا application/xhtml+xml باشد؛
 *     نبودِ Content-Type صریح (صفحات PHP که پیش‌فرض text/html هستند) هم تبدیل می‌شود.
 *     JSON، text/plain (عیب‌یابی/دانلود ماشینی) و همهٔ دانلودهای باینری دست‌نخورده می‌مانند
 *     (خروجی Excel از مسیر دیگری فارسی می‌شود: xlsx-writer ارقام سلول‌ها را فارسی می‌نویسد).
 *   • اگر display.fa_digits در تنظیمات «۰» شود، لایه غیرفعال است (راه برگشت اضطراری).
 */

/** نگاشت ارقام لاتین به فارسی (کلیدهای strtr با توجه به تطبیق طولانی‌ترین — اینجا همهٔ تک‌کاراکتری‌اند). */
function fa_digits_map(): array
{
    return ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'];
}

/** نگاشت عکس: ارقام فارسی و عربی به لاتین (نرمال‌سازی ورودی — پایگاه‌داده canonical لاتین می‌ماند). */
function fa_digits_normalize_map(): array
{
    return ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'];
}

/** تبدیل ارقام فارسی/عربی یک رشته به لاتین. هیچ چیز دیگری را دست نمی‌زند. */
function fa_digits_normalize_digits(string $text): string
{
    if ($text === '') {
        return $text;
    }
    return strtr($text, fa_digits_normalize_map());
}

/**
 * نرمال‌سازی عمیق ورودی HTTP: ارقام فارسی/عربی در همهٔ رشته‌های ورودی
 * (به‌صورت بازگشتی در آرایه‌ها) به لاتین تبدیل می‌شوند تا ذخیره/جست‌وجو/مقایسهٔ
 * اعداد همیشه روی canonical لاتین انجام شود؛ نمایش هنگام خروجی دوباره فارسی می‌شود.
 *
 * کلیدهای شبیه‌رمز/توکن مستثنا می‌شوند تا صحت رمزهایی که با کیبورد فارسی/عربی
 * تایپ و همان‌طور «هنگام ثبت» هش شده‌اند، هنگام ورود حفظ شود.
 *
 * @param mixed $value مقدار یا آرایهٔ ورودی.
 * @return mixed همان ساختار با رشته‌های نرمال‌شده.
 */
function fa_digits_normalize_deep(mixed $value): mixed
{
    if (is_string($value)) {
        return fa_digits_normalize_digits($value);
    }
    if (!is_array($value)) {
        return $value;
    }
    $out = [];
    foreach ($value as $key => $item) {
        if (is_string($key) && preg_match('/(password|passwd|pwd|csrf|token|secret)/iu', $key)) {
            $out[$key] = $item; // کلید حساس: دست‌نخورده
            continue;
        }
        $out[$key] = fa_digits_normalize_deep($item);
    }
    return $out;
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
    // ⚠️ ۱.۶۳.۳ — نام کامپیوتر هم «دادهٔ ماشینی» است، نه متن فارسی.
    //
    // پیش‌تر این لایه هر رقمی در متن را فارسی می‌کرد و نامی مثل «MBA4-01»
    // به «MBA4-۰۱» تبدیل می‌شد. کاربر باید شناسنامه را با برچسب روی کیس
    // تطبیق دهد؛ «۰۱» فارسی با هیچ برچسبی جور در نمی‌آید و کپی/جست‌وجو را
    // هم می‌شکند. نگهبانِ خروجی HTML تگ‌های <code>/<pre> را مستثنا می‌کند،
    // اما نام سیستم جای‌به‌جای رابط داخل <strong> و <option> هم می‌آید و
    // نمی‌شود همه‌شان را code کرد.
    //
    // الگو: نام میزبان ویندوز — شروع با حرف، سپس حروف/ارقام/خط‌تیره/زیرخط،
    // با شرط «حداقل یک رقم یا خط‌تیره» تا کلمهٔ فارسی با یک عدد پایانی
    // (مثل «هفته ۲») اشتباه گرفته نشود. ۱٫۶۳٫۳: «کد تیکت» (پیشوند فقط‌حروف
    // مثل SUP/IT/TK + خط‌تیره + ۴ رقم یا بیشتر) در کال‌بک استثنا می‌شود و فارسی چاپ می‌شود.
    $swapped = preg_replace_callback(
        '/(?:\d{1,3}(?:\.\d{1,3}){3}|[\w._%+\-]+@[\w\-]+(?:\.[\w\-]+)+)'
        . '|(?:[A-Za-z][A-Za-z0-9_]*[\-_.][A-Za-z0-9\-_.]*[A-Za-z0-9]'
        . '|[A-Za-z]{2,}[A-Za-z0-9]*\d[A-Za-z0-9\-_.]*)/u',
        static function (array $m) use (&$tokens): string {
            $match = $m[0];
            // خانوادهٔ «کد تیکت»: پیشوند فقط‌حروف (۱ تا ۱۲ حرف) + خط‌تیره + ۴ رقم یا بیشتر.
            // این‌ها شناسنامه‌های اصلی سامانه‌اند (SUP-00009، IT-00012) و طبق تصمیم محصول
            // («همهٔ اعداد فارسی — حتی شماره تیکت‌ها») فارسی چاپ می‌شوند؛ نام هاست‌های
            // دارای رقم در پیشوند (MBA4-01) یا بدون خط‌تیره (WORKSTATION7) یا تِیل کوتاه‌تر از ۴ رقم
            // (PC-023) همچنان لاتین می‌مانند.
            if (preg_match('/^[A-Za-z]{1,12}-\d{4,}$/', $match) === 1) {
                return $match; // محافظت نمی‌شود؛ ارقامش در مرحلهٔ strtr فارسی می‌شوند
            }
            // کلید فقط حروف دارد تا مرحلهٔ نگاشت رقم‌ها به آن دست نزند
            $key = "\x00" . str_repeat('q', count($tokens) + 1) . "\x00";
            $tokens[$key] = $match;
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

/**
 * تگ‌های بلوک‌ای که محتوای‌شان نباید تبدیل شود (اسکریپت/استایل/پیش‌نمایش/کد).
 * ۱٫۶۳٫۳: <textarea> از این فهرست خارج شد — متن تایپیِ کاربر باید ارقام نمایشی فارسی داشته باشد؛
 * مقدار canonical در پایگاه‌داده لاتین می‌ماند (fa_digits_normalize_deep در بوت‌استرپ).
 */
function fa_digits_skip_tags(): array
{
    return ['script', 'style', 'pre', 'code'];
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
