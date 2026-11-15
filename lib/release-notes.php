<?php
declare(strict_types=1);

/**
 * lib/release-notes.php — «تغییرات این نسخه» برای نمایش عمومی داخل سامانه.
 *
 * قواعد:
 *  - محتوای release_notes_all() تنها منبع "چه عوض شد" برای کاربر نهایی است؛
 *    در هر انتشار جدید یک کلید نسخه جدید اضافه کنید (کلید = CONTENT of VERSION).
 *  - نمایش فقط برای کاربران وارد‌شده (هر نقشی) است؛ هیچ مجوز اضافه‌ای لازم ندارد.
 *  - کارت به‌صورت **پاپ‌آپ** نمایش داده می‌شود و **حداکثر دو بار** — پس از آن
 *    دیگر خودکار باز نمی‌شود. تاریخچهٔ کامل همیشه در «راهنما ← تغییرات نسخه‌ها» هست.
 *
 * چرا پاپ‌آپ و چرا دو بار؟ (تصمیم مالک ۲۰۲۶-۱۱-۱۶)
 *  کارتِ درون‌صفحه‌ای داشبورد با دکمهٔ بستن درست بسته نمی‌شد (CSS `display:flex`
 *  بر `[hidden]` غلبه می‌کرد) و هر بار هم دوباره ظاهر می‌شد. راه‌حل: پاپ‌آپ واقعی
 *  با شمارندهٔ نمایش در localStorage — دو نمایش، بعد تمام.
 */

if (!defined('APP_ROOT')) {
    exit;
}

/** حداکثر تعداد نمایش خودکار پاپ‌آپ برای هر نسخه. */
const RELEASE_NOTES_POPUP_MAX_VIEWS = 2;

function release_notes_all(): array
{
    return [
        // ۱.۶۳.۱ — روی خط ۱.۶۳. چهار اصلاح: نقش اعطایی، ارجاع از فهرست کارشناسان،
        // رفع TypeError مرگبار، و خوددرمانی آژیر. ۱.۶۲.۹۸ لغو شد و اصلاحاتش این‌جاست.
        '1.63.1' => [
            'label' => 'نسخهٔ ۱.۶۳.۱',
            'date_fa' => 'آبان ۱۴۰۵',
            'title' => 'نقش، دسترسی، ارجاع — و آژیری که خودش را درمان می‌کند',
            'lead' => 'چهار اصلاح که سه‌تای اولشان به‌هم گره خورده‌اند: نقش‌های کلیدی دیگر نقش کاربر را عوض نمی‌کنند، مسئول تیکت فقط از فهرست کارشناسان خوانده می‌شود، و ارجاع خودکار دیگر با خطای مرگبار نمی‌افتد.',
            'items' => [
                ['icon' => '◆', 'text' => 'انتخاب «کارشناس آی تی» دیگر کاربر را ادمین اصلی نمی‌کند. پیش از این، هر بار که کسی را کارشناس می‌کردید، دسترسی کامل ادمین می‌گرفت — دو مفهوم بی‌ربط که به‌هم بسته شده بودند.'],
                ['icon' => '◆', 'text' => 'افزودن به فهرست «سوپروایز» دیگر نقش را عوض نمی‌کند، و حذف از آن کسی را تنزل نمی‌دهد. یک کارشناس که از فهرست سوپروایزرها حذف می‌شد، کارشناسی‌اش را هم از دست می‌داد.'],
                ['icon' => '◆', 'text' => 'دسترسی سوپروایز فقط چهار کد است: فضای سوپروایزر، صف تصمیم‌ها، بازگشایی، و صف. بقیه از نقش اصلی می‌آید — سوپروایز شدن یعنی «همه‌چیز» نیست.'],
                ['icon' => '◆', 'text' => 'فهرست چهارم «کارشناس پشتیبانی» اضافه شد. مسئول تیکت‌های IT و پشتیبانی فقط از همین دو فهرست خوانده می‌شود، نه از نقش سیستمی.'],
                ['icon' => '◆', 'text' => 'ادمین‌های اصلی کاملاً از ارجاع خودکار حذف شدند. فقط ارجاع دستی — همان‌طور که خواسته بودید.'],
                ['icon' => '◆', 'text' => 'یک کاربر می‌تواند هم‌زمان کارشناس IT و پشتیبانی باشد و هر دو حوزه را ببیند. پیش از این فقط یکی را می‌گرفت.'],
                ['icon' => '◆', 'text' => 'کارشناس برگزیده حالا واقعاً می‌تواند تیکت را باز کند و پاسخ دهد. پیش از این مسئولِ اسمی بود که هیچ تیکتی نمی‌توانست بردارد.'],
                ['icon' => '◆', 'text' => 'خطای مرگبار TypeError در ارجاع خودکار رفع شد. در نسخهٔ قبل، هر درخواست تازه‌ای که به مسیر ارجاع می‌رسید صفحهٔ ۵۰۰ می‌داد.'],
                ['icon' => '◆', 'text' => 'سه هشدار صفحهٔ سلامت خودشان را درمان می‌کنند: پوشه‌های storage ساخته می‌شوند، مهاجرت‌های baseline دیگر هشدار کاذب نمی‌دهند، و یک فایل .bat کرون را یک‌کلیکی ثبت می‌کند.'],
                ['icon' => '◆', 'text' => 'بدون مهاجرت، بدون تغییر جدول، بدون مجوز تازه. اسکیما دست‌نخورده ماند.'],
            ],
        ],
        // ۱.۶۲.۹۷ — بستهٔ غذای مستقل از ۱.۶۳؛ بعد از نصبِ ۱.۶۳ هم در تاریخچه می‌ماند.
        '1.62.97' => [
            'label' => 'نسخهٔ ۱.۶۲.۹۷',
            'date_fa' => 'آبان ۱۴۰۵',
            'title' => 'آزمون جامع Worker، چاپ و مدیریت — داخل خود پنل',
            'lead' => 'یک دکمه در انتهای صفحهٔ «تنظیمات سیستم ← اتصال پایگاه‌ها» که سلامت کل مسیر چاپ را همان‌جا نشان می‌دهد؛ بدون کاغذ، بدون تغییر هیچ تنظیمی.',
            'items' => [
                ['icon' => '◆', 'text' => 'کارت «آزمون جامع» به انتهای صفحهٔ «اتصال پایگاه‌ها» اضافه شد؛ با یک دکمه، ۳۲ بررسی در ۷ بخش همان‌جا اجرا و نمایش داده می‌شود.'],
                ['icon' => '◆', 'text' => 'هفت بخش: تنظیمات و نصب، Worker (زنده‌بودن و بیکاری درست)، صف چاپ، منبع تردد Access، چاپگر و مسیر چاپ، مدیریت سامانه (زمان‌بند و مجوزها)، و آمادگی بازهٔ کاری هفت روز آینده.'],
                ['icon' => '◆', 'text' => 'هیچ فیشی چاپ نمی‌شود و هیچ تنظیمی عوض نمی‌شود: اتصال چاپگر فقط با یک تست TCP و بدون ارسال یک بایت سنجیده می‌شود.'],
                ['icon' => '◆', 'text' => '«درست بیکار» با «خراب» اشتباه نمی‌شود: بیرون از بازهٔ ۱۱ تا ۱۴ و با صف خالی، Worker عمداً بیکار است و کارت همین را توضیح می‌دهد به‌جای اینکه ایراد اعلام کند.'],
                ['icon' => '◆', 'text' => 'کنار هر ایراد، یک جملهٔ راه‌حل فارسی نوشته شده؛ دکمهٔ «کپی گزارش» هم متن کامل را برای ارسال به پشتیبانی کپی می‌کند.'],
                ['icon' => '◆', 'text' => 'همان آزمون از خط فرمان هم در دسترس است و دقیقاً همان گزارش را می‌دهد: php tools\\prewindow_check.php --shared'],
                ['icon' => '◆', 'text' => 'ابزار تأیید نصب: php tools\\verify_1_62_97.php — ده ثانیه‌ای می‌گوید کارت سر جایش است یا نه.'],
                ['icon' => '◆', 'text' => 'مهاجرت ۰۳۳ در اسکیمای نصب تازه ثبت شد؛ دیگر هیچ نصب تازه‌ای هشدار کاذب «مهاجرت اجرا نشده» نمی‌گیرد.'],
            ],
            'footnote' => 'این نسخه مستقل از ۱.۶۳ نصب می‌شود (هر دو پیش‌نیاز ۱.۶۲.۹۶ دارند) و ترتیب نصب هیچ‌کدام دیگری را خراب نمی‌کند.',
        ],
        '1.63' => [
            'label' => 'نسخهٔ ۱.۶۳',
            'date_fa' => 'آبان ۱۴۰۵',
            'title' => 'در این نسخه چه عوض شد؟',
            'lead' => 'خلاصهٔ غیر فنیِ چیزهایی که در این نسخه عوض شد، درست شد یا تمیزتر شد؛ محصولی از مسیر «کاندید» تا نسخهٔ پایدار.',
            'items' => [
                ['icon' => '◆', 'text' => '«مرکز درخواست‌ها» خانهٔ اصلی شما شد: ثبت درخواست جدید، پیگیری، گزارش‌ها و صف کاری همگی از همان‌جا انجام می‌شود و منو و جست‌وجوی سراسری هم به آن وصل‌اند.'],
                ['icon' => '◆', 'text' => 'تقویم نوبت‌ها حالا با تقویم شمسی کار می‌کند: نمای روزانه و ماهانه، کلیک روی هر روز برای دیدن و ثبت نوبت‌ها (حتی چند نوبت با هم — «همه یا هیچ»).'],
                ['icon' => '◆', 'text' => 'نقش «مدیر واحد» روشن و شفاف شد: مدیر واحد فقط درخواست‌های واحد خودش را می‌بیند و اختیارات کارشناسی (شروع پاسخ، تغییر وضعیت و…) را ندارد.'],
                ['icon' => '◆', 'text' => 'بخش‌های «تغییر و مشکل» (مدیریت تغییرات) و «OLA» به مرکز درخواست‌ها منتقل شدند و فقط نقش‌های مجاز آن‌ها را می‌بینند.'],
                ['icon' => '◆', 'text' => 'اعداد در سراسر سامانه فارسی نمایش داده می‌شوند؛ داده‌های ذخیره‌شده دست‌نخورده باقی می‌مانند و خروجی‌های ماشینی (JSON/Excel) همچنان لاتین‌اند.'],
                ['icon' => '◆', 'text' => 'بالای سامانه خلوت و یک‌دست شد: دکمهٔ حالت روز/شب و نام و نقش شما کنار هم روی هر دو پوسته نشستند؛ دکمهٔ «خروج» در مرکز درخواست‌ها به «بازگشت به سامانه» تغییر کرد.'],
                ['icon' => '◆', 'text' => '«روزهای تعطیل هفتگی» حالا همیشه در یک خط نمایش داده می‌شود و چیدمان بخش تنظیمات فشرده‌تر و هماهنگ‌تر شده است.'],
                ['icon' => '◆', 'text' => 'تعمیرات و ثبات: خطاهای ریز رابط برطرف شد، بخش‌های قدیمیِ بدون استفاده پاک شدند و بارگذاری صفحه‌ها سبک‌تر شده است.'],
            ],
            'footnote' => 'اگر در این نسخه به چیزی برخوردید که کار نمی‌کند یا عجیب به نظر می‌رسد، به مدیر سامانه اطلاع بدهید. پرسش و پاسخ‌های رایج در «راهنما» هم به‌روز شده‌اند.',
        ],
    ];
}

function release_notes_version_key(): string
{
    $version = trim((string) @file_get_contents(APP_ROOT . '/VERSION'));
    return $version !== '' ? $version : '1.63';
}

/** یادداشتِ نسخهٔ نصب‌شده؛ اگر کلید همان نسخه نبود، آخرین یادداشت موجود برگردانده می‌شود. */
function release_notes_current(): ?array
{
    $all = release_notes_all();
    if (!$all) {
        return null;
    }
    $version = release_notes_version_key();
    if (isset($all[$version])) {
        $notes = $all[$version];
        $notes['version'] = $version;
        return $notes;
    }
    $lastKey = array_key_last($all);
    $notes = $all[$lastKey];
    $notes['version'] = (string) $lastKey;
    return $notes;
}

/**
 * تاریخچهٔ نسخه‌های ثبت‌شده، تازه‌ترین اول.
 * برای صفحهٔ «تغییرات نسخه‌ها» استفاده می‌شود تا تغییرات نسخه‌های قبلی (مثل ۱.۶۲.۹۷)
 * پس از نصب نسخهٔ بعدی هم قابل خواندن بماند.
 *
 * @return array<int,array> هر آیتم: version/label/date_fa/title/lead/items/footnote
 */
function release_notes_history(): array
{
    $all = release_notes_all();
    if (!$all) {
        return [];
    }
    // مقایسهٔ نسخه‌ای، نه ترتیب کلید (۱.۶۲.۹۷ باید زیر ۱.۶۳ بیاید).
    uksort($all, static fn ($a, $b) => version_compare((string) $b, (string) $a));
    $out = [];
    foreach ($all as $version => $notes) {
        if (!is_array($notes)) {
            continue;
        }
        $notes['version'] = (string) $version;
        $out[] = $notes;
    }
    return $out;
}

function release_notes_render_items(array $items, int $limit = 0): string
{
    $html = '';
    $count = 0;
    foreach ($items as $item) {
        if ($limit > 0 && $count >= $limit) {
            break;
        }
        $html .= '<li><span class="release-notes-item-icon" aria-hidden="true">' . e((string) ($item['icon'] ?? '•')) . '</span><span>' . e((string) ($item['text'] ?? '')) . '</span></li>';
        $count++;
    }
    return $html;
}

/**
 * پاپ‌آپ «تغییرات این نسخه» برای داشبورد.
 *
 * رفتار (تصمیم مالک ۲۰۲۶-۱۱-۱۶):
 *   • حداکثر RELEASE_NOTES_POPUP_MAX_VIEWS بار برای هر نسخه نمایش داده می‌شود.
 *   • دکمهٔ «بستن» و کلید Esc و کلیک روی پس‌زمینه، آن را می‌بندد.
 *   • شمارندهٔ نمایش در localStorage نگه داشته می‌شود؛ پس از دو بار، دیگر باز نمی‌شود.
 *   • دکمهٔ «همهٔ تغییرات» به «راهنما ← تغییرات نسخه‌ها» می‌رود.
 *
 * نکتهٔ فنی: `dialog` عمداً بدون CSS `display` استفاده شده است. اگر روی خودِ عنصر
 * `display:flex` بگذاریم، بر `[hidden]` غلبه می‌کند و بستن کار نمی‌کند — همان باگی
 * که در نسخهٔ قبل رخ داد. اینجا نمایش/پنهان‌سازی کامل با `hidden` + CSS
 * `[hidden]{display:none!important}` انجام می‌شود.
 */
function release_notes_dashboard_card(): string
{
    $notes = release_notes_current();
    if ($notes === null) {
        return '';
    }
    $version = (string) ($notes['version'] ?? '');
    $label = (string) ($notes['label'] ?? ('نسخهٔ ' . $version));
    $title = (string) ($notes['title'] ?? 'تغییرات این نسخه');
    $lead = (string) ($notes['lead'] ?? '');
    $teaser = release_notes_render_items((array) ($notes['items'] ?? []), 5);
    $maxViews = RELEASE_NOTES_POPUP_MAX_VIEWS;

    $historyUrl = 'index.php?page=help&tab=releases';
    $nonce = e(csp_nonce());

    return '<div class="release-notes-popup" id="release-notes-popup" data-release-version="' . e($version) . '"'
        . ' data-max-views="' . (int) $maxViews . '" hidden>'
        . '<div class="release-notes-popup-backdrop" data-rn-close="1"></div>'
        . '<div class="release-notes-popup-panel" role="dialog" aria-modal="true" aria-labelledby="release-notes-popup-title">'
        . '<div class="release-notes-popup-head">'
        . '<span class="release-notes-badge">' . e($label) . '</span>'
        . '<h2 id="release-notes-popup-title">' . e($title) . '</h2>'
        . '<button type="button" class="release-notes-popup-close" data-rn-close="1" aria-label="بستن">✕</button>'
        . '</div>'
        . ($lead !== '' ? '<p class="release-notes-popup-lead">' . e($lead) . '</p>' : '')
        . '<ul class="release-notes-popup-list">' . $teaser . '</ul>'
        . '<div class="release-notes-popup-foot">'
        . '<a class="button secondary" href="' . e($historyUrl) . '">همهٔ تغییرات نسخه‌ها ←</a>'
        . '<button type="button" class="button" data-rn-close="1">بستن</button>'
        . '</div>'
        . '</div>'
        . '</div>'
        . '<style>'
        . '.release-notes-popup{position:fixed;inset:0;z-index:9000;}'
        . '.release-notes-popup[hidden]{display:none !important;}'
        . '.release-notes-popup-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.45);backdrop-filter:blur(2px);}'
        . '.release-notes-popup-panel{position:relative;max-width:44rem;margin:6vh auto 0;background:#fff;color:#0f172a;'
        . 'border-radius:16px;padding:1.1rem 1.3rem 1rem;box-shadow:0 24px 60px rgba(15,23,42,.28);max-height:84vh;overflow:auto;}'
        . '.release-notes-popup-head{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.4rem;padding-inline-end:2rem;}'
        . '.release-notes-popup-head h2{font-size:1.05rem;margin:0;}'
        . '.release-notes-badge{display:inline-flex;align-items:center;padding:.15rem .7rem;border-radius:999px;font-size:.78rem;'
        . 'font-weight:700;border:1px solid #0f766e;color:#0f766e;white-space:nowrap;}'
        . '.release-notes-popup-lead{font-size:.92rem;line-height:1.9;margin:.2rem 0 .7rem;color:#334155;}'
        . '.release-notes-popup-list{list-style:none;margin:0;padding:0;display:grid;gap:.45rem;font-size:.93rem;line-height:1.9;}'
        . '.release-notes-popup-list li{display:flex;gap:.55rem;padding:.4rem .55rem;border-radius:10px;}'
        . '.release-notes-popup-list li:nth-child(odd){background:#f6faf9;}'
        . '.release-notes-item-icon{opacity:.6;margin-top:.1rem;color:#0f766e;}'
        . '.release-notes-popup-close{position:absolute;inset-inline-start:1rem;top:1rem;border:0;background:transparent;'
        . 'font-size:1rem;cursor:pointer;opacity:.55;border-radius:8px;padding:.2rem .45rem;line-height:1;}'
        . '.release-notes-popup-close:hover{opacity:1;background:#f1f5f9;}'
        . '.release-notes-popup-foot{display:flex;gap:.6rem;justify-content:flex-start;margin-top:.9rem;flex-wrap:wrap;}'
        . '.release-notes-popup-foot .button{text-decoration:none;}'
        . '@media print{.release-notes-popup{display:none !important;}}'
        . '@media (max-width:600px){.release-notes-popup-panel{margin:2vh .6rem 0;max-height:94vh;}}'
        . '</style>'
        . '<script nonce="' . $nonce . '">(function(){'
        . 'var popup=document.getElementById("release-notes-popup");if(!popup){return;}'
        . 'var version=popup.getAttribute("data-release-version")||"";'
        . 'var maxViews=parseInt(popup.getAttribute("data-max-views")||"2",10)||2;'
        . 'var seenKey="release-notes-popup-seen:"+version;'
        . 'var dismissedKey="release-notes-popup-dismissed:"+version;'
        . 'var seen=0;try{seen=parseInt(localStorage.getItem(seenKey)||"0",10)||0;}catch(_){}'
        . 'var dismissed=false;try{dismissed=localStorage.getItem(dismissedKey)==="1";}catch(_){}'
        . 'if(dismissed||seen>=maxViews){return;}'
        . 'seen=seen+1;try{localStorage.setItem(seenKey,String(seen));}catch(_){}'
        . 'function close(){popup.hidden=true;try{localStorage.setItem(dismissedKey,"1");}catch(_){}'
        . 'document.removeEventListener("keydown",onKey);}'
        . 'function onKey(ev){if(ev.key==="Escape"){close();}}'
        . 'popup.hidden=false;document.addEventListener("keydown",onKey);'
        . 'popup.addEventListener("click",function(ev){'
        . 'var t=ev.target;if(t&&t.closest&&t.closest("[data-rn-close]")){ev.preventDefault();close();}'
        . '});'
        . '})();</script>';
}

/**
 * تبِ «تغییرات نسخه‌ها» برای صفحهٔ راهنما — `index.php?page=help&tab=releases`.
 * تاریخچهٔ کامل و همیشگی؛ هیچ وابستگی‌ای به localStorage ندارد.
 */
function release_notes_history_html(): string
{
    $history = release_notes_history();
    if ($history === []) {
        return '<section class="page-heading"><div><span class="eyebrow">تغییرات سامانه</span><h1>تغییرات نسخه‌ها</h1>'
            . '<p class="muted">برای هیچ نسخه‌ای یادداشتی ثبت نشده است.</p></div></section>';
    }

    $currentVersion = (string) (release_notes_current()['version'] ?? '');
    $html = '<style>'
        . '.release-notes-history{max-width:1100px;margin:0 auto;line-height:2;}'
        . '.release-notes-block{border:1px solid #e2e8f0;border-radius:14px;padding:1rem 1.1rem;margin:0 0 1rem;background:#fff;}'
        . '.release-notes-block-head{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.3rem;}'
        . '.release-notes-block h2{font-size:1.05rem;margin:.2rem 0 .1rem;color:#0f766e;}'
        . '.release-notes-block .release-notes-lead{color:#475569;font-size:.9rem;line-height:1.9;margin:.2rem 0 .6rem;}'
        . '.release-notes-page-list{list-style:none;margin:0;padding:0;display:grid;gap:.4rem;}'
        . '.release-notes-page-list li{display:flex;gap:.6rem;line-height:2;padding:.35rem .55rem;border-radius:10px;}'
        . '.release-notes-page-list li:nth-child(odd){background:#f8fafc;}'
        . '.release-notes-page-list .release-notes-item-icon{opacity:.6;margin-top:.1rem;color:#0f766e;}'
        . '.release-notes-footnote{font-size:.85rem;color:#64748b;margin:.6rem 0 0;line-height:1.9;}'
        . '.release-notes-current-tag{font-size:.72rem;font-weight:700;padding:.1rem .5rem;border-radius:999px;border:1px solid #94a3b8;color:#475569;}'
        . '.release-notes-badge{display:inline-flex;align-items:center;padding:.15rem .7rem;border-radius:999px;font-size:.78rem;'
        . 'font-weight:700;border:1px solid #0f766e;color:#0f766e;white-space:nowrap;}'
        . '@media print{.release-notes-badge,.release-notes-current-tag{display:none !important;}}'
        . '</style>'
        . '<div class="release-notes-history">'
        . '<section class="page-heading"><div><span class="eyebrow">راهنما</span><h1>تغییرات نسخه‌ها</h1>'
        . '<p>هر کاری که در هر نسخه عوض شد، درست شد یا اضافه شد — تازه‌ترین بالا. '
        . 'این صفحه همیشه در دسترس است، حتی اگر پاپ‌آپ اعلان را بسته باشید.</p></div>'
        . '<div class="release-notes-page-meta"><small class="muted">' . e((string) count($history)) . ' نسخه ثبت‌شده</small></div></section>';

    foreach ($history as $h) {
        $v = (string) ($h['version'] ?? '');
        $isCurrent = $v === $currentVersion;
        $html .= '<section class="release-notes-block" id="release-notes-' . e(str_replace('.', '-', $v)) . '">'
            . '<div class="release-notes-block-head">'
            . '<span class="release-notes-badge">' . e((string) ($h['label'] ?? ('نسخهٔ ' . $v))) . '</span>'
            . ($isCurrent ? '<span class="release-notes-current-tag">نسخهٔ نصب‌شده</span>' : '<span class="release-notes-current-tag">قبلی</span>')
            . (($h['date_fa'] ?? '') !== '' ? '<small class="muted">' . e((string) $h['date_fa']) . '</small>' : '')
            . '</div>'
            . '<h2>' . e((string) ($h['title'] ?? 'تغییرات این نسخه')) . '</h2>'
            . (($h['lead'] ?? '') !== '' ? '<p class="release-notes-lead">' . e((string) $h['lead']) . '</p>' : '')
            . '<ul class="release-notes-page-list">' . release_notes_render_items((array) ($h['items'] ?? [])) . '</ul>'
            . (($h['footnote'] ?? '') !== '' ? '<p class="release-notes-footnote">' . e((string) $h['footnote']) . '</p>' : '')
            . '</section>';
    }

    $html .= '</div>';
    return $html;
}

/** متن کامل صفحهٔ «تغییرات این نسخه» (برای کاربران وارد‌شدهٔ هر نقشی). */
function release_notes_page_html(): string
{
    $notes = release_notes_current();
    if ($notes === null) {
        return '<section class="page-heading"><div><span class="eyebrow">تغییرات سامانه</span><h1>تغییرات این نسخه</h1><p class="muted">برای این نسخه یادداشتی ثبت نشده است.</p></div></section>';
    }

    // صفحه همیشه «همهٔ نسخه‌ها» را نشان می‌دهد (تازه‌ترین اول) تا تغییرات یک بستهٔ
    // میانی (مثل ۱.۶۲.۹۷) بعد از نصب نسخهٔ بعدی هم گم نشود.
    $history = release_notes_history();
    if (count($history) <= 1) {
        return release_notes_page_single($notes);
    }

    return release_notes_history_html();
}

/** نمایش تک‌نسخه‌ای (وقتی فقط یک نسخه ثبت شده است). */
function release_notes_page_single(array $notes): string
{
    $label = (string) ($notes['label'] ?? '');
    $dateFa = (string) ($notes['date_fa'] ?? '');
    $title = (string) ($notes['title'] ?? 'تغییرات این نسخه');
    $lead = (string) ($notes['lead'] ?? '');
    $items = release_notes_render_items((array) ($notes['items'] ?? []));
    $footnote = (string) ($notes['footnote'] ?? '');

    $html = '<section class="page-heading"><div><span class="eyebrow">تغییرات سامانه</span><h1>' . e($title) . '</h1><p>' . e($lead) . '</p></div>'
        . '<div class="release-notes-page-meta"><span class="release-notes-badge">' . e($label) . '</span>' . ($dateFa !== '' ? '<small class="muted">' . e($dateFa) . '</small>' : '') . '</div></section>'
        . '<style>'
        . '.release-notes-page-meta{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;}'
        . '.release-notes-page-list{list-style:none;margin:0;padding:0;display:grid;gap:.65rem;}'
        . '.release-notes-page-list li{display:flex;gap:.6rem;line-height:2.1;padding:.45rem .6rem;border-radius:10px;}'
        . '.release-notes-page-list li:nth-child(odd){background:rgba(127,127,127,.08);}'
        . '.release-notes-page-list .release-notes-item-icon{opacity:.6;margin-top:.1rem;}'
        . '.release-notes-badge{display:inline-flex;align-items:center;padding:.15rem .7rem;border-radius:999px;font-size:.78rem;'
        . 'font-weight:700;border:1px solid #0f766e;color:#0f766e;white-space:nowrap;}'
        . '@media print{.release-notes-page-meta,.release-notes-dismiss{display:none !important;}}'
        . '</style>'
        . '<section class="card form-card"><ul class="release-notes-page-list">' . $items . '</ul>';
    if ($footnote !== '') {
        $html .= '<p class="muted release-notes-footnote" style="margin-top:.75rem">' . e($footnote) . '</p>';
    }
    $html .= '<p style="margin-top:.5rem"><a class="button secondary" href="index.php?page=help&tab=releases">تغییرات نسخه‌ها</a> <a class="button secondary" href="index.php">بازگشت به داشبورد</a></p>';
    $html .= '</section>';
    return $html;
}
