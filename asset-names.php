<?php
declare(strict_types=1);

/**
 * نام نمایشی کامپیوترها — نسخهٔ ۱.۳۷.۵
 * ============================================================================
 * مسئلهٔ کاربر: در فهرست‌ها دو نام طولانی و گیج‌کننده دیده می‌شد:
 *      DOMAIN-ADMINIT2            ← شناسهٔ خودکارِ رکورد (asset_tag)
 *      ADMINIT2.DOMAIN.LOCAL       ← نام کامل دامنه (assets.hostname)
 * خواستهٔ کاربر: برای هر کامپیوتر فقط «اسم» دیده شود، مثال: adminit2
 *
 * قاعده‌ها (بدون هیچ تغییری در دادهٔ دیتابیس — فقط نمایش):
 *   • «ADMINIT2.DOMAIN.LOCAL» → «adminit2»        (بخش دامنه حذف می‌شود)
 *   • «DOMAIN-ADMINIT2»      → «adminit2»        (پیشوند خودکارِ رکورد حذف می‌شود)
 *   • «ADMINIT2$»            → «adminit2»        (پسوند $ حساب کامپیوتر در AD)
 *   • «adminit2»             → «adminit2»        (بدون تغییر)
 *   • IP و هر نام دیگری دست‌نخورده می‌ماند.
 *   • نام کامل دامنه گم نمی‌شود: به‌صورت tooltip (title) روی همان نام می‌ماند و
 *     در «شناسنامه رایانه» هم قابل دیدن است.
 *
 * حالت نمایش با تنظیم «ظاهر نام کامپیوترها» در تنظیمات ← اطلاعات سامانه قابل تغییر است:
 *   short (پیش‌فرض، حروف اصلی) = ADMINIT2   |   short_lower = adminit2   |   full = ADMINIT2.DOMAIN.LOCAL
 *
 * ۱.۶۳.۳: پیش‌فرض از short_lower به short عوض شد تا نام دقیقاً همان چیزی
 * باشد که روی کیس/سیستم‌عامل نوشته شده. کوچک‌کردن انتخابی مانده است. ارقام
 * نام هم دیگر فارسی نمی‌شوند (نگاه کنید به lib/fa-digits.php).
 */

/** حالت نمایش نام کامپیوتر: short_lower | short | full */
function asset_hostname_style(): string
{
    // ⚠️ ۱.۶۳.۳ — پیش‌فرض از short_lower به short عوض شد.
    //
    // کاربر نام کامپیوترها را با الگوی «MBA4-01» تا «MBA4-150» روی کیس
    // برچسب زده است و می‌خواهد در شناسنامه **دقیقاً همان چیزی** را ببیند که
    // در سیستم‌عامل/Active Directory هست، نه نسخهٔ کوچک‌شده. کوچک‌کردن
    // حروف، شناسایی چشمی و کپی‌کردن نام را سخت می‌کرد (MBA4-01 ≠ mba4-01
    // برای کسی که برچسب را می‌خواند).
    //
    // کوچک‌کردن همچنان به‌صورت انتخاب در تنظیمات هست؛ فقط پیش‌فرضِ تازه
    // «حفظ حروف اصلی» است. نصب‌هایی که قبلاً short_lower را صریح ذخیره
    // کرده‌اند، همان را حفظ می‌کنند.
    $style = (string) setting('asset_hostname_style', 'short');
    return in_array($style, ['short_lower', 'short', 'full'], true) ? $style : 'short';
}

/**
 * الگوی IPv4 برای تشخیص «IP چسبیده به نام».
 * نکته: بخش آخر را عمداً محدود نمی‌کنیم (۲۵۹/۲۵۵/…) چون هدف «تشخیص»
 * است نه اعتبارسنجی؛ اگر چهار بخش عددیِ نقطه‌دار دیدیم، IP است.
 */
const ASSET_IPV4_PATTERN = '((?:\d{1,3}\.){3}\d{1,3})';

/** آیا این رشته یک آدرس IP خالص است؟ */
function asset_is_ip(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_IP) !== false;
}

/** آیا این رشته یک نام کامل دامنه است (نه IP)؟ */
function asset_is_domain_name(string $value): bool
{
    return $value !== '' && !asset_is_ip($value) && str_contains($value, '.');
}

/**
 * حذف IP از هر جای رشته، بدون آسیب به بقیهٔ نام.
 *
 * چرا: روی شبکهٔ کاربر، reverse DNS برای کلاینت‌ها نامی مثل
 * «clint 192.168.1.65» برمی‌گرداند — یعنی اسم و IP در یک فیلد. پیش‌تر
 * قاعدهٔ «هرچه بعد از اولین نقطه است حذف شود» این را به «clint 192»
 * تبدیل می‌کرد که نه اسم است نه IP: بدتر از خودِ مقدار خام.
 * حالا اول IP برداشته می‌شود و بعد قاعدهٔ دامنه اعمال می‌شود.
 */
function asset_strip_ip(string $value): string
{
    // «ip.corp.local» — IP که یک نام دامنه دنبالش آمده: کل بخش دامنه هم می‌رود
    $stripped = preg_replace('/[\s_-]*' . ASSET_IPV4_PATTERN . '(?:\.\S*)?/', '', $value);
    if (is_string($stripped)) {
        $value2 = $stripped === '' ? '' : $stripped;
    }
    // IP ساده با جداکنندهٔ فاصله/خط‌تیره/زیرخط یا در ابتدا/انتها
    $stripped = preg_replace('/[\s_-]*' . ASSET_IPV4_PATTERN . '(?![\d.])/', '', $value2 ?? '');
    $stripped = is_string($stripped) ? $stripped : $value;
    // جداکننده‌های آویزان در ابتدا/انتها (مثل «pc1-» یا «-pc1»)
    $stripped = trim($stripped, " \t\n\r\0\x0B-_.");
    return trim($stripped);
}

/**
 * هستهٔ نام: حذف IP چسبیده، بخش دامنه، پیشوند خودکار «DOMAIN-» و پسوند «$».
 * هیچ حرفی از نام اصلی حذف نمی‌شود جز این‌ها.
 */
function asset_name_core(string $hostname): string
{
    $name = trim(str_replace(["\u{200C}", "\u{200F}", "\u{200E}"], '', $hostname));
    $name = trim($name, " \t\n\r\0\x0B\"'");
    if ($name === '') {
        return '';
    }
    // آدرس IP خالص یک نام است، نه «نام کامل دامنه» ⇒ دست‌نخورده برمی‌گردد
    // (تنها جایی که IP نگه داشته می‌شود: وقتی چیز دیگری برای نشان‌دادن نباشد)
    if (asset_is_ip($name)) {
        return $name;
    }
    // IP چسبیده به نام (clint 192.168.1.65 ⇒ clint) — پیش از قاعدهٔ دامنه
    $name = asset_strip_ip($name);
    if ($name === '') {
        // فقط IP بود و جداکننده‌ها پاک شدند؛ همان IP اصلی را برگردان
        return trim($hostname);
    }
    // نام کامل دامنه: هرچه بعد از اولین نقطه است حذف می‌شود (ADMINIT2.DOMAIN.LOCAL ⇒ ADMINIT2)
    $dot = strpos($name, '.');
    if ($dot !== false && $dot > 0) {
        $name = substr($name, 0, $dot);
    }
    // پیشوند خودکارِ رکوردهای اسکن دامنه و کلاینت‌ها (چند بار، برای اطمینان)
    for ($i = 0; $i < 3; $i++) {
        if (stripos($name, 'DOMAIN-') === 0) {
            $name = substr($name, 7);
        }
        if (stripos($name, 'CLIENT-') === 0) {
            $name = substr($name, 7);
        }
    }
    // پسوند دامنه بدون نقطه: reverse DNS معمولاً «نام» و «دامنه» را با فاصله
    // می‌دهد (pc1 192.168.1.5 CORP). پس از حذف IP، کلمهٔ آخر اگر کاملاً
    // حرفی و کوتاه باشد و نام بلندتری قبلش باشد، باقی‌ماندهٔ دامنه است.
    $words = preg_split('/\s+/u', $name) ?: [];
    while (count($words) > 1) {
        $last = (string) end($words);
        if (preg_match('/^[A-Za-z]{2,24}$/', $last) === 1) {
            array_pop($words);
            continue;
        }
        break;
    }
    $name = implode(' ', $words);
    // پسوند $ حساب کامپیوتر در Active Directory (مثل PC1$)
    $name = rtrim($name, '$');
    $name = trim($name, " \t\n\r\0\x0B-_");
    // باقی‌ماندهٔ «آی‌پی خط‌تیره‌ای» (192-168-1-65): نام کامپیوتر نیست.
    //
    // این شکل از خودِ سامانه هم می‌آید: هنگام ورود کاربر که نام واقعی را
    // نمی‌دانیم، `CLIENT-192-168-1-25` ساخته می‌شود. قبلاً این تابع آن را به
    // «192.168.1.25» تبدیل و برمی‌گرداند و همان IP در ستون «نام» می‌نشست —
    // یعنی همان چیزی که کاربر می‌دید. حالا رشتهٔ خالی برمی‌گردد تا فراخوان
    // بفهمد «نام واقعی نداریم» و از مسیر Win32_ComputerSystem.Name پر شود.
    if (preg_match('/^(\d{1,3})-\d{1,3}-\d{1,3}-\d{1,3}$/', $name) === 1) {
        return '';
    }
    return $name;
}

/** نام نمایشی: قاعده + حالت انتخاب‌شده در تنظیمات. */
function asset_display_name(?string $hostname): string
{
    $raw = trim((string) $hostname);
    if ($raw === '') {
        return '';
    }
    // IP خالص «نام کامپیوتر» نیست؛ شناسه است. طبق قرارداد، آدرس فقط در
    // tooltip می‌آید و در ستون «نام» مقداری جز نام ماشین نمی‌نشیند.
    // (تابع asset_name_core دست‌نخورده می‌ماند، چون کلید هویت از آن ساخته
    //  می‌شود و خالی‌کردنش رکوردهای IP-محور را به هم می‌ریخت.)
    if (asset_is_ip($raw)) {
        return '';
    }
    $core = asset_name_core($raw);
    $style = asset_hostname_style();
    if ($style === 'full') {
        // «نام کامل دامنه» یعنی نام میزبان + دامنه — **نه** رشتهٔ خام.
        // قبلاً همین‌جا `return $raw` بود و نتیجه این بود که هر مقدار آلوده
        // (مثل «client 192.168.1.25» که reverse DNS چپانده) دست‌نخورده به
        // کاربر نشان داده می‌شد و IP هم داخل نام می‌ماند. قاعدهٔ «IP هرگز در
        // نام دیده نمی‌شود» نباید به حالت نمایش وابسته باشد.
        if ($core === '' || asset_is_ip($core)) {
            return $core !== '' ? $core : '';
        }
        // دامنه را از مقدار خام برمی‌داریم (اگر واقعاً دامنه‌ای بوده)، ولی
        // هستهٔ نام را همان هستهٔ تمیزشده می‌گذاریم.
        $domain = '';
        if (preg_match('/^[^\s]+\.[A-Za-z][A-Za-z0-9.-]*$/', $raw) === 1) {
            $dot = strpos($raw, '.');
            if ($dot !== false) {
                $domain = substr($raw, $dot);
            }
        }
        return $core . $domain;
    }
    if ($core === '') {
        // نام واقعی قابل استخراج نیست (مثلاً CLIENT-192-168-1-25 که خودمان
        // ساختیم، یا IP خالص). «نمایش» نباید همان IP را برگرداند، چون کاربر
        // آن را به‌عنوان نام کامپیوتر می‌خواند. رشتهٔ خالی می‌دهیم تا فراخوان
        // جایگزین مناسب بگذارد (asset_name_title / «نام ثبت نشده»).
        return '';
    }
    return $style === 'short_lower' ? (function_exists('mb_strtolower') ? mb_strtolower($core, 'UTF-8') : strtolower($core)) : $core;
}

/**
 * شناسهٔ نمایشی رکورد (asset_tag): اگر شناسه فقط «DOMAIN-<نام>» خودکار است،
 * نمایش آن بی‌فایده و تکراری است ⇒ رشتهٔ خالی برمی‌گردد تا فقط «نام» دیده شود.
 */
function asset_is_auto_domain_tag(?string $tag, ?string $hostname = null): bool
{
    $tag = trim((string) $tag);
    if ($tag === '' || stripos($tag, 'DOMAIN-') !== 0) {
        return false;
    }
    return true;
}

/**
 * نامی که همیشه چیزی برای نشان‌دادن دارد.
 *
 * `asset_display_name()` وقتی نام واقعی قابل استخراج نیست رشتهٔ خالی می‌دهد
 * (مثلاً رکوردی که فقط `CLIENT-192-168-1-25` دارد). هر جای رابط کاربری که
 * این مقدار را مستقیم چاپ می‌کرد، یک سلول خالی می‌شد. این تابع همان حالت را
 * به یک برچسب صادقانه تبدیل می‌کند — بدون IP و بدون ادعای دروغ.
 *
 * @param string $fallback برچسبی که وقتی نام واقعی نیست نشان داده می‌شود.
 */
function asset_name_or(?string $hostname, string $fallback = 'نام ثبت نشده'): string
{
    $shown = asset_display_name($hostname);
    return $shown !== '' ? $shown : $fallback;
}

/** متن tooltip: نام کامل (اگر با نام نمایشی یکی نباشد). */
function asset_name_title(?string $hostname, ?string $tag = null): string
{
    $raw = trim((string) $hostname);
    $parts = [];
    $shown = asset_display_name($raw);
    if ($raw !== '' && $raw !== $shown) {
        // نام خام فقط وقتی در tooltip می‌آید که خودش IP نباشد؛ وگرنه همان
        // قاعدهٔ «IP هیچ‌جا به‌عنوان نام دیده نمی‌شود» نقض می‌شد.
        if (asset_is_ip($raw)) {
            $parts[] = 'آدرس: ' . $raw;
        } elseif (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $raw) !== 1) {
            $parts[] = $raw;
        }
    }
    $tag = trim((string) $tag);
    if ($tag !== '' && !asset_is_auto_domain_tag($tag)) {
        $parts[] = 'شناسه: ' . $tag;
    }
    return implode(' • ', $parts);
}

/** صفت آمادهٔ HTML برای عنوان (title)؛ اگر چیزی برای نشان‌دادن نبود، رشتهٔ خالی. */
function asset_name_attr(?string $hostname, ?string $tag = null): string
{
    $title = asset_name_title($hostname, $tag);
    return $title === '' ? '' : ' title="' . e($title) . '"';
}

/** برچسب کوتاه رکورد برای جاهایی که شناسه لازم است ولی مزاحم نباشد. */
function asset_tag_label(?string $tag, ?string $hostname = null): string
{
    $tag = trim((string) $tag);
    if ($tag === '') {
        return '';
    }
    if (asset_is_auto_domain_tag($tag, $hostname)) {
        // رکورد خودکارِ اسکن دامنه: «DOMAIN-ADMINIT2» ⇒ «ADMINIT2»
        // (ستون «منبع استخراج» در خروجی، همین را «اسکن/دامنه» نشان می‌دهد)
        $core = asset_name_core($tag);
        return $core !== '' ? $core : $tag;
    }
    return $tag;
}

/* ════ ۱.۶۲.۳۸ — هاتفیکس: توابع هویت اینجا تعریف میشوند (بدون وابستگی به فایل جدید) ════ */
if (!function_exists('asset_identity_key')) {
/**
 * ۱.۶۲.۳۷ — کلید هویت یکتای سیستم.
 * نام دامینهای، پیشوند DOMAIN-، پسوند $ و حالت حروف یکسان درنظر گرفته میشوند.
 */
function asset_identity_key(?string $hostname, ?string $tag = null): string
{
    $raw = trim((string) ($hostname !== null && $hostname !== '' ? $hostname : $tag));
    if ($raw === '') { return ''; }
    $core = asset_name_core($raw);
    // پیشوند خودکار رکوردهای ویندوز کلاینت (CLIENT-*) هم بخشی از هویت نیست.
    for ($i = 0; $i < 3; $i++) {
        if (stripos($core, 'CLIENT-') === 0) { $core = substr($core, 7); }
        if (stripos($core, 'DOMAIN-') === 0) { $core = substr($core, 7); }
    }
    $core = trim($core);
    return function_exists('mb_strtolower') ? mb_strtolower($core, 'UTF-8') : strtolower($core);
}

/**
 * پیدا کردن رکورد موجود بر اساس هویت یکتا — برای جلوگیری از ساخت رکورد تکراری.
 * ترتیب: کلید نرمالشده → hostname دقیق → asset_tag دقیق → سریال.
 * خروجی: id رکورد موجود یا ۰.
 */
function asset_resolve_id(?string $hostname, ?string $tag = null, ?string $serial = null): int
{
    $key = asset_identity_key($hostname, $tag);
    if ($key !== '') {
        $q = db()->prepare('SELECT id FROM assets WHERE hostname_key = ? ORDER BY id ASC LIMIT 1');
        $q->execute([$key]);
        $found = (int) ($q->fetchColumn() ?: 0);
        if ($found > 0) { return $found; }
    }
    if ($hostname !== null && trim((string) $hostname) !== '') {
        $q = db()->prepare('SELECT id FROM assets WHERE hostname = ? ORDER BY id ASC LIMIT 1');
        $q->execute([trim((string) $hostname)]);
        $found = (int) ($q->fetchColumn() ?: 0);
        if ($found > 0) { return $found; }
    }
    if ($tag !== null && trim((string) $tag) !== '') {
        $q = db()->prepare('SELECT id FROM assets WHERE asset_tag = ? ORDER BY id ASC LIMIT 1');
        $q->execute([trim((string) $tag)]);
        $found = (int) ($q->fetchColumn() ?: 0);
        if ($found > 0) { return $found; }
    }
    if ($serial !== null && trim((string) $serial) !== '') {
        $q = db()->prepare('SELECT id FROM assets WHERE serial_number = ? ORDER BY id ASC LIMIT 1');
        $q->execute([trim((string) $serial)]);
        return (int) ($q->fetchColumn() ?: 0);
    }
    return 0;
}

/** بهروزرسانی کلید هویت بعد از تغییر hostname یا ساخت رکورد جدید. */
function asset_sync_identity_key(int $assetId): void
{
    if ($assetId <= 0) { return; }
    $row = db()->prepare('SELECT hostname, asset_tag FROM assets WHERE id = ? LIMIT 1');
    $row->execute([$assetId]);
    $asset = $row->fetch();
    if (!$asset) { return; }
    $key = asset_identity_key($asset['hostname'] ?? null, $asset['asset_tag'] ?? null);
    try {
        db()->prepare('UPDATE assets SET hostname_key = ? WHERE id = ?')->execute([$key !== '' ? $key : null, $assetId]);
    } catch (PDOException $keyError) {
        // ۱.۶۳.۴ — کلید یکتای نام: اگر همین نام قبلاً روی رکورد دیگری ثبت شده باشد،
        // ثبت کلید شکست می‌خورد. این وضعیت نباید کل ذخیرهٔ شناسنامه را از بین ببرد؛
        // رکورد بدون به‌روزرسانی کلید می‌ماند و رخداد برای بررسی ثبت می‌شود.
        if (function_exists('app_error_log')) {
            app_error_log('[asset-names] hostname_key collision for asset ' . $assetId . ' (' . $key . '): ' . $keyError->getMessage());
        }
    }
}
}
