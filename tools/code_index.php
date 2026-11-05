<?php
/**
 * استخراج‌کنندهٔ نقشهٔ کد — سازندهٔ docs/system-map/BLOCKS.json
 *
 * چرا با token_get_all و نه regex؟ چون regex روی PHP واقعی می‌شکند:
 * آکولاد داخل رشته، heredoc، کامنت، و `}` داخل درون‌یابی رشته همگی
 * شمارش را خراب می‌کنند. توکنایزر خود PHP تنها مرجع درست است.
 *
 * خروجی: برای هر فایل، فهرست «بلوک»ها. دو نوع بلوک داریم:
 *   symbol — تابع، کلاس، متد، interface، trait (لنگر = نام، پایدار)
 *   region — کد رویه‌ای سطح بالا (لنگر = شرط مسیریابی یا کامنت عنوان)
 *
 * هر بلوک یک «اثر انگشت» محتوا دارد. اگر محتوا عوض شود اثر انگشت عوض
 * می‌شود و تست نگهبان، کهنه بودن فهرست را می‌گیرد. این همان قانونی است
 * که خواسته شده: جابه‌جایی یا تغییر محتوا ⇒ آدرس باید به‌روز شود.
 *
 * اجرا:  php tools/code_index.php > docs/system-map/BLOCKS.json
 */

declare(strict_types=1);

const AREA_MAP = [
    'migrations/'      => 'DB',
    'tests/'           => 'TEST',
    'tools/'           => 'TOOL',
    'lib/'             => 'CORE',
    'actions/'         => 'ACT',
    'cron/'            => 'CRON',
    'api/'             => 'API',
    'assets/'          => 'ASSET',
    'food-ticket-web/' => 'WEB',
];

/**
 * طول و برش امن روی UTF-8 بدون وابستگی به mbstring.
 * ابزار باید هم روی سرور (با mbstring) و هم در محیط تحلیل (بدون آن)
 * یکسان کار کند، وگرنه نقشه در دو جا دو جور ساخته می‌شود.
 */
function ci_strlen(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : (int) preg_match_all('/./us', $s);
}

function ci_substr(string $s, int $start, int $len): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($s, $start, $len, 'UTF-8');
    }
    preg_match_all('/./us', $s, $m);
    return implode('', array_slice($m[0], $start, $len));
}

/** ناحیهٔ یک فایل از روی مسیرش. فایل‌های ریشه صفحه‌اند. */
function ci_area(string $path): string
{
    foreach (AREA_MAP as $prefix => $area) {
        if (str_starts_with($path, $prefix)) {
            return $area;
        }
    }
    if (str_ends_with($path, '.sql')) {
        return 'DB';
    }
    return 'PAGE';
}

/** اثر انگشت محتوا — فاصله‌ها نرمال می‌شوند تا قالب‌بندی صرف، فهرست را کهنه نکند. */
function ci_fingerprint(string $text): string
{
    $normal = preg_replace('/[ \t]+/', ' ', $text);
    $normal = preg_replace('/[ \t]*(?:\r\n|\n|\r)[ \t]*/', "\n", (string) $normal);
    return substr(hash('sha256', trim((string) $normal)), 0, 12);
}

/**
 * آیا این خط می‌تواند لنگر یک بلوک رویه‌ای باشد؟
 * برمی‌گرداند: برچسب خوانا، یا null اگر لنگر نیست.
 */
function ci_anchor_label(string $line): ?string
{
    $t = trim($line);
    if ($t === '') {
        return null;
    }
    // کامنت عنوان‌دار:  // --- عنوان ---   یا   // === عنوان ===
    if (preg_match('~^//\s*[-=]{2,}\s*(.+?)\s*[-=]{2,}$~u', $t, $m)) {
        return trim($m[1]);
    }
    if (preg_match('~^/\*+\s*[-=]{2,}\s*(.+?)\s*[-=]{2,}~u', $t, $m)) {
        return trim($m[1]);
    }
    // شرط مسیریابی:  if ($page === 'food-ticket' ...)
    if (preg_match('~^\}?\s*(?:else)?if\s*\(.*?\$(?:page|action|route|tab|mode|view|step)\b~u', $t)) {
        if (preg_match_all("~'([a-z0-9_\-\.]{2,})'~i", $t, $mm) && $mm[1]) {
            return 'مسیر: ' . implode('|', array_slice($mm[1], 0, 3));
        }
        return 'شرط مسیریابی';
    }
    if (preg_match("~^case\s+'([^']+)'~u", $t, $m)) {
        return 'case: ' . $m[1];
    }
    // کامنت سطح بالا (ستون صفر) تقریباً همیشه سرفصل یک مرحله از کار است.
    // بدون این، فایل‌های رویه‌ای مثل cron/ یک بلوک غول‌پیکر می‌شوند و
    // آدرس‌دهی هدف خودش را از دست می‌دهد.
    if ($line !== '' && $line[0] === '/' && preg_match('~^//+\s*(.+?)\s*$~u', $t, $m)) {
        $label = trim((string) preg_replace('~[\x{2500}-\x{257F}\-=_]{2,}~u', '', $m[1]));
        if ($label !== '' && ci_strlen($label) >= 3) {
            return ci_substr($label, 0, 60);
        }
    }
    return null;
}

/**
 * امتیاز ریسک یک بلوک. هدف رتبه‌بندی برای بازبینی است، نه قضاوت نهایی.
 * هر نشانه جداگانه برگردانده می‌شود تا در گزارش قابل ردیابی باشد.
 */
function ci_risk(string $code): array
{
    $flags = [];

    // catch خالی یا فقط-کامنت: خطا بلعیده می‌شود
    if (preg_match('~catch\s*\([^)]*\)\s*\{\s*(?://[^\n]*\s*|/\*.*?\*/\s*)*\}~s', $code)) {
        $flags['silent_catch'] = 4;
    }
    // الحاق متغیر داخل SQL
    if (preg_match('~(SELECT|INSERT|UPDATE|DELETE|WHERE|FROM)\b[^;]{0,400}?(\.\s*\$|\{\$|\$\w+\s*\.)~is', $code)) {
        $flags['sql_concat'] = 5;
    }
    // ورودی خام کاربر
    if (preg_match('~\$_(GET|POST|REQUEST|COOKIE)\s*\[~', $code)) {
        $flags['raw_input'] = 2;
    }
    // چاپ متغیر بدون فرار
    if (preg_match('~echo\s+\$|<\?=\s*\$~', $code) && !preg_match('~htmlspecialchars|\be\s*\(|esc_~', $code)) {
        $flags['unescaped_echo'] = 3;
    }
    // سرکوب خطا
    $at = preg_match_all('~(?<![\w"\'])@[a-z_]\w*\s*\(~i', $code);
    if ($at > 0) {
        $flags['error_suppression'] = min(3, $at);
    }
    // عملیات خطرناک فایل/سیستم
    if (preg_match('~\b(exec|shell_exec|system|passthru|proc_open|popen|unlink|rmdir|eval)\s*\(~', $code)) {
        $flags['dangerous_call'] = 4;
    }
    // تغییر حالت بدون تراکنش
    if (preg_match('~\b(INSERT|UPDATE|DELETE)\b~i', $code) && !preg_match('~beginTransaction|START TRANSACTION~i', $code)) {
        $flags['write_no_tx'] = 1;
    }
    // طول بیش از حد
    $lines = substr_count($code, "\n") + 1;
    if ($lines > 300) {
        $flags['very_long'] = 3;
    } elseif ($lines > 120) {
        $flags['long'] = 1;
    }

    return ['score' => array_sum($flags), 'flags' => $flags];
}

/** استخراج بلوک‌های یک فایل PHP با توکنایزر. */
function ci_extract_php(string $path, string $src): array
{
    $tokens = @token_get_all($src);
    if (!is_array($tokens)) {
        return [];
    }
    $lines = preg_split('/\r\n|\n|\r/', $src) ?: [];
    $total = count($lines);

    // گام ۱: نمادها (تابع/کلاس/متد) با شمارش آکولاد روی توکن‌ها
    $symbols = [];
    $depth = 0;
    $pending = null;      // نمادی که منتظر آکولاد بازش هستیم
    $classStack = [];     // پشتهٔ [نام کلاس, عمق]
    $n = count($tokens);

    for ($i = 0; $i < $n; $i++) {
        $tok = $tokens[$i];

        if (is_string($tok)) {
            if ($tok === '{') {
                $depth++;
                if ($pending !== null && $pending['open'] === null) {
                    $pending['open'] = $depth;
                }
            } elseif ($tok === '}') {
                if ($pending !== null && $pending['open'] === $depth) {
                    $pending['line_end'] = ci_token_line($tokens, $i);
                    $symbols[] = $pending;
                    $pending = null;
                }
                while ($classStack && end($classStack)['depth'] === $depth) {
                    array_pop($classStack);
                }
                $depth--;
            } elseif ($tok === ';' && $pending !== null && $pending['open'] === null) {
                // تابع انتزاعی یا اعلان interface — بدنه ندارد
                $pending = null;
            }
            continue;
        }

        [$id, $text, $line] = [$tok[0], $tok[1], $tok[2]];

        // «{$var}» و «${var}» داخل رشته و heredoc: آکولادِ باز یک توکن
        // آرایه‌ای است ولی آکولاد بسته‌اش رشتهٔ ساده. اگر اینجا عمق را زیاد
        // نکنیم، آن «}» عمق را منفی می‌کند و بدنهٔ تابع زودتر از موعد بسته
        // می‌شود. این باگ واقعاً رخ داد و نقشه را خاموش خراب کرده بود.
        if ($id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES) {
            $depth++;
            continue;
        }

        if ($id === T_CLASS || $id === T_INTERFACE || $id === T_TRAIT || (defined('T_ENUM') && $id === T_ENUM)) {
            $name = ci_next_name($tokens, $i);
            if ($name === null) {
                continue; // کلاس ناشناس
            }
            $classStack[] = ['name' => $name, 'depth' => $depth + 1];
            if ($pending === null) {
                $pending = [
                    'kind' => strtolower(token_name($id) === 'T_CLASS' ? 'class' : substr(token_name($id), 2)),
                    'symbol' => $name,
                    'line_start' => $line,
                    'open' => null,
                ];
            }
            continue;
        }

        if ($id === T_FUNCTION) {
            $name = ci_next_name($tokens, $i);
            if ($name === null) {
                continue; // closure
            }
            // متد یا تابع سطح بالا
            $owner = $classStack ? end($classStack)['name'] : null;
            if ($pending === null) {
                $pending = [
                    'kind' => $owner !== null ? 'method' : 'function',
                    'symbol' => $owner !== null ? $owner . '::' . $name : $name,
                    'line_start' => $line,
                    'open' => null,
                ];
            }
        }
    }

    // گام ۲: فقط نمادهای سطح بالا نگه داشته می‌شوند (متدها داخل کلاس‌اند،
    // ولی چون کلاس را هم ثبت کرده‌ایم، متدها را جداگانه نگه می‌داریم تا
    // آدرس‌دهی ریزدانه باشد). همپوشانی کلاس/متد پذیرفته است.
    usort($symbols, static fn(array $a, array $b): int => $a['line_start'] <=> $b['line_start']);

    // گام ۳: جذب docblock پیش از محاسبهٔ پوشش.
    // ترتیب مهم است: اگر بعد از محاسبهٔ پوشش انجام شود، خط docblock در
    // هیچ بلوکی نمی‌افتد و بی‌صدا از نقشه حذف می‌شود.
    $prevEnd = 0;
    foreach ($symbols as $k => $s) {
        $start = ci_absorb_docblock($lines, $s['line_start']);
        if ($s['kind'] !== 'method' && $prevEnd > 0) {
            $start = max($start, $prevEnd + 1);
        }
        // فایل‌های fixture گاهی چند تابع را روی یک خط می‌گذارند؛ بدون این
        // مهار، شروع از پایان جلو می‌زند و محدودهٔ معکوس تولید می‌شود.
        $symbols[$k]['line_start'] = min($start, $s['line_end']);
        if ($s['kind'] !== 'method') {
            $prevEnd = max($prevEnd, $s['line_end']);
        }
    }

    $covered = array_fill(0, $total + 2, false);
    foreach ($symbols as $s) {
        if ($s['kind'] === 'method') {
            continue; // متد داخل کلاس است؛ بلوک کلاس پوششش می‌دهد
        }
        for ($l = $s['line_start']; $l <= $s['line_end'] && $l <= $total; $l++) {
            $covered[$l] = true;
        }
    }

    // نواحی رویه‌ای = مکمل دقیق نمادها. عمداً به‌صورت «مکمل» ساخته می‌شود
    // تا پوشش کامل یک خاصیت ساختاری باشد، نه نتیجهٔ اتفاقیِ چند قاعدهٔ
    // ابتکاری. نسخهٔ قبلی با قاعدهٔ «شکاف سه‌خطی» کار می‌کرد و خطوطی را
    // بی‌صدا جا می‌انداخت.
    $gaps = [];
    $l = 1;
    while ($l <= $total) {
        if ($covered[$l]) {
            $l++;
            continue;
        }
        $from = $l;
        while ($l <= $total && !$covered[$l]) {
            $l++;
        }
        $gaps[] = ['from' => $from, 'to' => $l - 1];
    }

    // هر شکاف روی خطوط لنگر بریده می‌شود تا آدرس‌ها معنادار بمانند.
    $regions = [];
    foreach ($gaps as $g) {
        $cuts = [$g['from']];
        for ($x = $g['from'] + 1; $x <= $g['to']; $x++) {
            if (ci_anchor_label($lines[$x - 1]) !== null) {
                $cuts[] = $x;
            }
        }
        $cuts[] = $g['to'] + 1;
        $cn = count($cuts);
        for ($i = 0; $i < $cn - 1; $i++) {
            $a = $cuts[$i];
            $b = $cuts[$i + 1] - 1;
            if ($b >= $a) {
                $regions[] = ['from' => $a, 'to' => $b];
            }
        }
    }

    // گام ۴: ساخت فهرست نهایی
    $blocks = [];
    foreach ($symbols as $s) {
        $text = ci_slice($lines, $s['line_start'], $s['line_end']);
        $blocks[] = [
            'kind' => $s['kind'],
            'anchor' => $s['symbol'],
            'line_start' => $s['line_start'],
            'line_end' => $s['line_end'],
            'lines' => $s['line_end'] - $s['line_start'] + 1,
            'fingerprint' => ci_fingerprint($text),
            'risk' => ci_risk($text),
        ];
    }
    foreach ($regions as $r) {
        if ($r['to'] < $r['from']) {
            continue;
        }
        $text = ci_slice($lines, $r['from'], $r['to']);
        if (trim($text) === '') {
            continue;
        }
        $label = null;
        for ($l = $r['from']; $l <= min($r['from'] + 2, $r['to']); $l++) {
            $label = ci_anchor_label($lines[$l - 1]);
            if ($label !== null) {
                break;
            }
        }
        $blocks[] = [
            'kind' => 'region',
            'anchor' => $label ?? ('بلوک رویه‌ای ' . $r['from'] . '-' . $r['to']),
            'line_start' => $r['from'],
            'line_end' => $r['to'],
            'lines' => $r['to'] - $r['from'] + 1,
            'fingerprint' => ci_fingerprint($text),
            'risk' => ci_risk($text),
        ];
    }

    usort($blocks, static fn(array $a, array $b): int => $a['line_start'] <=> $b['line_start']);
    return $blocks;
}

/** فایل غیر PHP (js/css): بلوک‌بندی ساده بر اساس تابع/بخش. */
function ci_extract_plain(string $path, string $src): array
{
    $lines = preg_split('/\r\n|\n|\r/', $src) ?: [];
    $total = count($lines);
    if ($total === 0) {
        return [];
    }
    $anchors = [];
    foreach ($lines as $idx => $line) {
        $t = trim($line);
        if (preg_match('~^(?:export\s+)?(?:async\s+)?function\s+([A-Za-z_$][\w$]*)~', $t, $m)
            || preg_match('~^(?:const|let|var)\s+([A-Za-z_$][\w$]*)\s*=\s*(?:async\s*)?(?:function|\()~', $t, $m)
            || preg_match('~^([A-Za-z_$][\w$]*)\s*:\s*(?:async\s*)?function~', $t, $m)) {
            $anchors[] = ['line' => $idx + 1, 'name' => $m[1]];
        } elseif (preg_match('~^(?:async\s+)?def\s+([A-Za-z_]\w*)~', $t, $m)
            || preg_match('~^class\s+([A-Za-z_]\w*)~', $t, $m)) {
            $anchors[] = ['line' => $idx + 1, 'name' => $m[1]];
        } elseif (preg_match('~^/\*+\s*[-=]{2,}\s*(.+?)\s*[-=]{2,}~u', $t, $m)
            || preg_match('~^#\s*[-=]{2,}\s*(.+?)\s*[-=]{2,}$~u', $t, $m)) {
            $anchors[] = ['line' => $idx + 1, 'name' => trim($m[1])];
        } elseif ($line !== '' && $line[0] === '/' && preg_match('~^/\*+\s*(.+?)\s*\*+/$~u', $t, $m)) {
            // کامنت تک‌خطی ستون صفر: سرفصل بخش در CSS و JS.
            $name = trim((string) preg_replace('~[\x{2500}-\x{257F}\-=_]{2,}~u', '', $m[1]));
            if ($name !== '' && ci_strlen($name) >= 3) {
                $anchors[] = ['line' => $idx + 1, 'name' => ci_substr($name, 0, 60)];
            }
        }
    }
    return ci_blocks_from_anchors($lines, $total, $anchors);
}

/** عقب بردن شروع بلوک تا ابتدای docblock چسبیده به آن. */
function ci_absorb_docblock(array $lines, int $start): int
{
    $l = $start - 1;
    while ($l >= 1 && trim($lines[$l - 1]) === '') {
        $l--;
    }
    if ($l < 1 || !str_ends_with(trim($lines[$l - 1]), '*/')) {
        return $start;
    }
    while ($l >= 1) {
        $t = trim($lines[$l - 1]);
        if (str_starts_with($t, '/**') || str_starts_with($t, '/*')) {
            return $l;
        }
        if (!str_starts_with($t, '*') && !str_ends_with($t, '*/')) {
            return $start;
        }
        $l--;
    }
    return $start;
}

/**
 * بلوک‌بندی فایل SQL. لنگر = دستور سطح بالا یا کامنت عنوان‌دار.
 * اسکیما هم بخشی از سامانه است و باید آدرس داشته باشد؛ بیشتر خرابی‌های
 * این پروژه از ناهماهنگی اسکیما با کد آمده‌اند.
 */
function ci_extract_sql(string $path, string $src): array
{
    $lines = preg_split('/\r\n|\n|\r/', $src) ?: [];
    $total = count($lines);
    if ($total === 0) {
        return [];
    }
    $create = '~^(CREATE\s+(?:OR\s+REPLACE\s+)?(?:TABLE|UNIQUE\s+INDEX|INDEX|VIEW|TRIGGER|PROCEDURE|FUNCTION|DATABASE))'
        . '\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"\']?([A-Za-z0-9_]+)~i';
    $change = '~^(ALTER\s+TABLE|INSERT\s+INTO|REPLACE\s+INTO|UPDATE|DELETE\s+FROM|DROP\s+TABLE(?:\s+IF\s+EXISTS)?|TRUNCATE\s+TABLE)'
        . '\s+[`"\']?([A-Za-z0-9_]+)~i';
    $anchors = [];
    foreach ($lines as $i => $line) {
        $t = trim($line);
        if (preg_match($create, $t, $m) || preg_match($change, $t, $m)) {
            $anchors[] = [
                'line' => $i + 1,
                'name' => strtoupper((string) preg_replace('~\s+~', ' ', $m[1])) . ' ' . $m[2],
            ];
        } elseif (preg_match('~^--\s*[-=]{2,}\s*(.+?)\s*[-=]{2,}$~u', $t, $m)) {
            $anchors[] = ['line' => $i + 1, 'name' => trim($m[1])];
        }
    }
    return ci_blocks_from_anchors($lines, $total, $anchors);
}

/**
 * ساخت بلوک‌های پیوسته از فهرست لنگرها — مشترک بین SQL و فایل‌های متنی.
 * شروع اجباری از خط ۱ تضمین می‌کند مقدمهٔ فایل جا نماند.
 */
function ci_blocks_from_anchors(array $lines, int $total, array $anchors): array
{
    if (!$anchors || $anchors[0]['line'] > 1) {
        array_unshift($anchors, ['line' => 1, 'name' => 'مقدمهٔ فایل']);
    }
    $blocks = [];
    $an = count($anchors);
    foreach ($anchors as $i => $a) {
        $end = $i + 1 < $an ? $anchors[$i + 1]['line'] - 1 : $total;
        if ($end < $a['line']) {
            continue;
        }
        $text = ci_slice($lines, $a['line'], $end);
        $blocks[] = [
            'kind' => 'block',
            'anchor' => $a['name'],
            'line_start' => $a['line'],
            'line_end' => $end,
            'lines' => $end - $a['line'] + 1,
            'fingerprint' => ci_fingerprint($text),
            'risk' => ci_risk($text),
        ];
    }
    return $blocks;
}

function ci_slice(array $lines, int $from, int $to): string
{
    return implode("\n", array_slice($lines, $from - 1, $to - $from + 1));
}

function ci_token_line(array $tokens, int $i): int
{
    for ($k = $i; $k >= 0; $k--) {
        if (is_array($tokens[$k])) {
            return $tokens[$k][2] + substr_count($tokens[$k][1], "\n");
        }
    }
    return 0;
}

function ci_next_name(array $tokens, int $i): ?string
{
    $n = count($tokens);
    for ($k = $i + 1; $k < $n; $k++) {
        $t = $tokens[$k];
        if (is_array($t) && ($t[0] === T_WHITESPACE || $t[0] === T_COMMENT || $t[0] === T_DOC_COMMENT)) {
            continue;
        }
        if (is_string($t) && $t === '&') {
            continue; // تابع با بازگشت ارجاعی
        }
        if (is_array($t) && $t[0] === T_STRING) {
            return $t[1];
        }
        return null;
    }
    return null;
}

/** نقطهٔ ورود: ساخت فهرست کامل. */
function ci_build(array $paths, string $root): array
{
    $files = [];
    $counters = [];
    sort($paths);
    foreach ($paths as $path) {
        $full = $root . '/' . $path;
        if (!is_file($full)) {
            continue;
        }
        $src = (string) file_get_contents($full);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'php') {
            $blocks = ci_extract_php($path, $src);
        } elseif ($ext === 'sql') {
            $blocks = ci_extract_sql($path, $src);
        } else {
            $blocks = ci_extract_plain($path, $src);
        }
        $area = ci_area($path);
        $counters[$area] = ($counters[$area] ?? 0) + 1;
        $fileId = sprintf('%s-%03d', $area, $counters[$area]);
        $out = [];
        foreach ($blocks as $j => $b) {
            $b['id'] = sprintf('%s.%02d', $fileId, $j + 1);
            $out[] = $b;
        }
        $files[] = [
            'id' => $fileId,
            'path' => $path,
            'area' => $area,
            'lines' => substr_count($src, "\n") + 1,
            'file_fingerprint' => ci_fingerprint($src),
            'blocks' => $out,
        ];
    }
    return $files;
}

// اجرای مستقیم از خط فرمان (در php-wasm این بخش اجرا نمی‌شود)
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $root = dirname(__DIR__);
    $list = [];
    exec('cd ' . escapeshellarg($root) . ' && git ls-files "*.php" "*.js" "*.css" "*.sql" "*.py"', $list);
    $paths = array_values(array_filter($list, static function (string $p): bool {
        // updates/ و downloads/ کپی منجمد نسخه‌های منتشرشده‌اند، نه کد زنده.
        return !preg_match('~^(updates|downloads|vendor)/~', $p);
    }));
    $files = ci_build($paths, $root);
    echo json_encode([
        'generated_by' => 'tools/code_index.php',
        'schema' => 1,
        'files' => $files,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), "\n";
}
