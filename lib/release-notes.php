<?php
declare(strict_types=1);

/**
 * lib/release-notes.php — «تغییرات این نسخه» برای نمایش عمومی داخل سامانه.
 *
 * قواعد:
 *  - محتوای release_notes_all() تنها منبع "چه عوض شد" برای کاربر نهایی است؛
 *    در هر انتشار جدید یک کلید نسخه جدید اضافه کنید (کلید = CONTENT of VERSION).
 *  - نمایش فقط برای کاربران واردشده (هر نقشی) است؛ هیچ مجوز اضافه‌ای لازم ندارد.
 *  - کارت داشبورد با localStorage به‌ازای هر نسخه قابل‌بستن است.
 *  - متن‌ها غیر فنی و برای کاربر نهایی نوشته می‌شوند؛ جزئیات فنی داخل بستهٔ
 *    به‌روزرسانی (CHANGE-REPORT) می‌ماند.
 */
if (!defined('APP_ROOT')) {
    exit;
}

function release_notes_all(): array
{
    return [
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

/** کارت «تغییرات این نسخه» برای داشبورد؛ با دکمهٔ بستن و localStorage به‌ازای هر نسخه. */
function release_notes_dashboard_card(): string
{
    $notes = release_notes_current();
    if ($notes === null) {
        return '';
    }
    $version = (string) ($notes['version'] ?? '');
    $label = (string) ($notes['label'] ?? ('نسخهٔ ' . $version));
    $title = (string) ($notes['title'] ?? 'تغییرات این نسخه');
    $teaser = release_notes_render_items((array) ($notes['items'] ?? []), 4);

    return '<section class="release-notes-card" id="release-notes-card" data-release-version="' . e($version) . '" hidden>'
        . '<div class="release-notes-card-body">'
        . '<div class="release-notes-card-head"><span class="release-notes-badge">' . e($label) . '</span><strong>' . e($title) . '</strong></div>'
        . '<ul class="release-notes-card-list">' . $teaser . '</ul>'
        . '<a class="button secondary release-notes-card-link" href="index.php?page=release-notes">همهٔ تغییرات این نسخه ←</a>'
        . '</div>'
        . '<button type="button" class="release-notes-dismiss" aria-label="بستن این کارت">✕</button>'
        . '</section>'
        . '<style>'
        . '.release-notes-card{display:flex;gap:.75rem;align-items:flex-start;justify-content:space-between;position:relative;margin:0 0 1rem;padding:.9rem 1rem .9rem 2.4rem;border:1px solid rgba(127,127,127,.35);border-radius:14px;background:transparent;color:inherit;}'
        . '.release-notes-card-head{display:flex;align-items:center;gap:.6rem;margin-bottom:.45rem;flex-wrap:wrap;}'
        . '.release-notes-badge{display:inline-flex;align-items:center;padding:.15rem .7rem;border-radius:999px;font-size:.78rem;font-weight:700;border:1px solid rgba(127,127,127,.4);white-space:nowrap;}'
        . '.release-notes-card-list{list-style:none;margin:.35rem 0 .6rem;padding:0;display:grid;gap:.3rem;font-size:.92rem;line-height:1.9;}'
        . '.release-notes-card-list li{display:flex;gap:.5rem;}'
        . '.release-notes-item-icon{opacity:.65;}'
        . '.release-notes-card-link{text-decoration:none;}'
        . '.release-notes-dismiss{position:absolute;left:.5rem;top:.5rem;border:0;background:transparent;color:inherit;opacity:.6;font-size:1rem;cursor:pointer;border-radius:8px;padding:.25rem .4rem;line-height:1;}'
        . '.release-notes-dismiss:hover{opacity:1;background:rgba(127,127,127,.15);}'
        . '@media print{.release-notes-card{display:none !important;}}'
        . '</style>'
        . '<script nonce="' . e(csp_nonce()) . '">(function(){var card=document.getElementById("release-notes-card");if(!card){return;}var key="release-notes-dismissed:"+(card.getAttribute("data-release-version")||"");try{if(localStorage.getItem(key)==="1"){return;}}catch(_){}card.hidden=false;var btn=card.querySelector(".release-notes-dismiss");if(btn){btn.addEventListener("click",function(){try{localStorage.setItem(key,"1");}catch(_){}card.hidden=true;});}})();</script>';
}

/** متن کامل صفحهٔ «تغییرات این نسخه» (برای کاربران واردشدهٔ هر نقشی). */
function release_notes_page_html(): string
{
    $notes = release_notes_current();
    if ($notes === null) {
        return '<section class="page-heading"><div><span class="eyebrow">تغییرات سامانه</span><h1>تغییرات این نسخه</h1><p class="muted">برای این نسخه یادداشتی ثبت نشده است.</p></div></section>';
    }
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
        . '@media print{.release-notes-page-meta,.release-notes-dismiss{display:none !important;}}'
        . '</style>'
        . '<section class="card form-card"><ul class="release-notes-page-list">' . $items . '</ul>';
    if ($footnote !== '') {
        $html .= '<p class="muted release-notes-footnote" style="margin-top:.75rem">' . e($footnote) . '</p>';
    }
    $html .= '<p style="margin-top:.5rem"><a class="button secondary" href="index.php?page=help">راهنمای سامانه</a> <a class="button secondary" href="index.php">بازگشت به داشبورد</a></p>';
    $html .= '</section>';
    return $html;
}
