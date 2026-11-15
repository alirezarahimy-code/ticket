<?php
/**
 * تست آفلاین «کارت تغییرات سامانه» و تاریخچهٔ نسخه‌ها (lib/release-notes.php).
 *
 * اجرا: php tools/test_release_notes_offline.php
 *
 * چرا؟  مالک باید بتواند ببیند در هر به‌روزرسانی چه چیزی عوض شده — از داخل خود سامانه.
 * /* و مهم‌تر: تغییرات یک بستهٔ میانی (۱.۶۲.۹۷) باید بعد از نصب نسخهٔ بعدی (۱.۶۳) هم باقی بماند.
 */
declare(strict_types=1);

define('APP_ROOT', sys_get_temp_dir() . '/rn_selftest_' . getmypid());
@mkdir(APP_ROOT, 0777, true);

$fails = 0;
$passes = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $fails, $passes;
    if ($ok) {
        $passes++;
        echo "[PASS] {$label}\n";
        return;
    }
    $fails++;
    echo "[FAIL] {$label}" . ($detail !== '' ? " :: {$detail}" : '') . "\n";
}

if (!function_exists('e')) {
    function e(mixed $x): string { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('csp_nonce')) {
    function csp_nonce(): string { return 'testnonce'; }
}

require dirname(__DIR__) . '/lib/release-notes.php';

/** تنظیم VERSION برای هر سناریو. */
$setVersion = static function (string $v): void {
    file_put_contents(APP_ROOT . '/VERSION', $v . "\n");
};

/* ── ۱) هر دو نسخه در تاریخچه هستند ───────────────────────────────────── */
$history = release_notes_history();
$versions = array_column($history, 'version');
check('تاریخچه شامل ۱.۶۳ است', in_array('1.63', $versions, true), implode(',', $versions));
check('تاریخچه شامل ۱.۶۲.۹۷ است', in_array('1.62.97', $versions, true), implode(',', $versions));
// قاعدهٔ واقعی: نزولی بودن نسخه‌ها — نه «اولی حتماً ۱.۶۳». با هر نسخهٔ تازه
// (۱.۶۳.۱، ۱.۶۴…) این تست باید سبز بماند؛ پین‌کردن به یک نسخهٔ خاص، تست را
// به «نگهبان گذشته» تبدیل می‌کند نه «نگهبان قاعده».
$descending = true;
for ($i = 1; $i < count($versions); $i++) {
    if (version_compare($versions[$i - 1], $versions[$i], '<')) {
        $descending = false;
        break;
    }
}
check('نسخه‌ها نزولی مرتب شده‌اند', $descending, implode(',', $versions));
check('تازه‌ترین نسخه اول می‌آید', ($versions[0] ?? '') === array_values(array_filter(
    $versions, fn ($v) => version_compare($v, '1.62.97', '>=')))[0] ?? '', implode(',', $versions));

foreach ($history as $h) {
    $label = (string) ($h['version'] ?? '?');
    check("نسخهٔ {$label}: عنوان دارد", trim((string) ($h['title'] ?? '')) !== '');
    check("نسخهٔ {$label}: حداقل ۵ آیتم دارد", count((array) ($h['items'] ?? [])) >= 5, 'count=' . count((array) ($h['items'] ?? [])));
    foreach ((array) ($h['items'] ?? []) as $i => $item) {
        if (trim((string) ($item['text'] ?? '')) === '') {
            check("نسخهٔ {$label}: آیتم " . ($i + 1) . " متن دارد", false);
        }
    }
}

/* ── ۲) روی ۱.۶۲.۹۷ (قبل از نصب ۱.۶۳) ───────────────────────────────── */
$setVersion('1.62.97');
$current = release_notes_current();
check('روی ۱.۶۲.۹۷، نسخهٔ جاری همان ۱.۶۲.۹۷ است', ($current['version'] ?? '') === '1.62.97', (string) ($current['version'] ?? ''));
$card = release_notes_dashboard_card();
check('پاپ‌آپ تغییرات روی ۱.۶۲.۹۷ رندر می‌شود', strpos($card, 'release-notes-popup') !== false);
check('پاپ‌آپ ابتدا پنهان است (hidden)', strpos($card, 'id="release-notes-popup"') !== false && strpos($card, ' hidden>') !== false);
check('سقف نمایش دو بار در HTML ست شده', strpos($card, 'data-max-views="2"') !== false);
check('دکمهٔ بستن وجود دارد', strpos($card, 'data-rn-close="1"') !== false);
check('کلید Esc هم می‌بندد', strpos($card, 'Escape') !== false);
check('شمارندهٔ نمایش در localStorage ثبت می‌شود', strpos($card, 'release-notes-popup-seen:') !== false);
check('پس از بستن، دیگر باز نمی‌شود (dismissed)', strpos($card, 'release-notes-popup-dismissed:') !== false);
check('لینک به تب «تغییرات نسخه‌ها» در راهنما', strpos($card, 'page=help&amp;tab=releases') !== false || strpos($card, 'page=help&tab=releases') !== false);
$page97 = release_notes_page_html();
check('صفحهٔ تغییرات روی ۱.۶۲.۹۷ هر دو نسخه را دارد',
    strpos($page97, 'نسخهٔ ۱.۶۲.۹۷') !== false && strpos($page97, 'نسخهٔ ۱.۶۳') !== false);
check('صفحهٔ تغییرات «نسخهٔ نصب‌شده» را نشان می‌دهد', substr_count($page97, 'نسخهٔ نصب‌شده') === 1);
check('صفحهٔ تغییرات محتوای آزمون جامع را دارد', strpos($page97, 'آزمون جامع Worker') !== false);

/* ── ۳) بعد از نصب ۱.۶۳: تغییرات ۹۷ نباید گم شود ─────────────────────── */
$setVersion('1.63');
$current63 = release_notes_current();
check('روی ۱.۶۳، نسخهٔ جاری ۱.۶۳ است', ($current63['version'] ?? '') === '1.63');
$page63 = release_notes_page_html();
check('بعد از ۱.۶۳، بخش ۱.۶۲.۹۷ هنوز در صفحه هست', strpos($page63, 'آزمون جامع Worker') !== false);
$expectedBlocks = count(release_notes_history());
check('بعد از ۱.۶۳، همهٔ بلوک‌های نسخه رندر می‌شوند',
    substr_count($page63, 'release-notes-block"') === $expectedBlocks,
    'blocks=' . substr_count($page63, 'release-notes-block"') . ' / expected=' . $expectedBlocks);
check('بعد از ۱.۶۳، برچسب «نسخهٔ نصب‌شده» یک‌بار است', substr_count($page63, 'نسخهٔ نصب‌شده') === 1);

/* ── ۳-ب) رگرسیون: باگ «بستن کارت کار نمی‌کند» نباید برگردد ──────────── */
// ریشهٔ باگ: روی خودِ عنصرِ کارت `display:flex` گذاشته شده بود؛ این بر `[hidden]`
// (که فقط display:none سطح مرورگر است) غلبه می‌کرد و بستن هیچ اثر بصری نداشت.
$cardSrc = (string) file_get_contents(dirname(__DIR__) . '/lib/release-notes.php');
// فقط قاعدهٔ پایه سنجیده می‌شود، نه @media print.
$baseRule = '';
if (preg_match('/\.release-notes-popup\{([^}]*)\}/', $cardSrc, $m) === 1) {
    $baseRule = $m[1];
}
check(
    'قاعدهٔ پایهٔ پاپ‌آپ display ندارد (باگ بستن)',
    $baseRule !== '' && stripos($baseRule, 'display') === false,
    $baseRule
);
check(
    'قاعدهٔ [hidden] با !important محافظت شده',
    strpos($cardSrc, '.release-notes-popup[hidden]{display:none !important;}') !== false
);
check(
    'سقف نمایش از یک ثابت واحد می‌آید (۲)',
    strpos($cardSrc, 'RELEASE_NOTES_POPUP_MAX_VIEWS = 2') !== false
);

/* ── ۴) نسخهٔ ناشناخته ⇒ آخرین یادداشت (رفتار قبلی حفظ شود) ──────────── */
$setVersion('9.99.99');
$unknown = release_notes_current();
check('نسخهٔ ناشناخته، آخرین یادداشت را برمی‌گرداند', $unknown !== null && ($unknown['version'] ?? '') !== '');

/* ── ۵) escape امنیتی ────────────────────────────────────────────────── */
check(
    'متن آیتم‌ها escape می‌شود (XSS)',
    strpos(release_notes_render_items([['icon' => '◆', 'text' => '<script>bad()</script>']]), '<script>') === false
);

/* ── ۶) مسیر دسترسی در سامانه ────────────────────────────────────────── */
$indexSrc = (string) file_get_contents(dirname(__DIR__) . '/index.php');
check("مسیر صفحه در index.php هست (page=release-notes)", strpos($indexSrc, "if (\$page === 'release-notes')") !== false);
$renderSrc = (string) file_get_contents(dirname(__DIR__) . '/lib/render.php');
check('لینک «تغییرات سامانه» در فوتر هست', strpos($renderSrc, 'index.php?page=release-notes') !== false);
check('کارت داشبورد در index.php صدا زده می‌شود', strpos($indexSrc, 'release_notes_dashboard_card()') !== false);

/* ── ۷) تب «تغییرات نسخه‌ها» در راهنما ──────────────────────────────── */
$helpSrc = (string) file_get_contents(dirname(__DIR__) . '/help-guide.php');
check('تب «تغییرات نسخه‌ها» از فهرست راهنما لینک شده', strpos($helpSrc, 'tab=releases') !== false);

$historyHtml = release_notes_history_html();
check('صفحهٔ تاریخچه هر دو نسخه را دارد',
    strpos($historyHtml, 'نسخهٔ ۱.۶۲.۹۷') !== false && strpos($historyHtml, 'نسخهٔ ۱.۶۳') !== false);
check('صفحهٔ تاریخچه «نسخهٔ نصب‌شده» را یک‌بار نشان می‌دهد', substr_count($historyHtml, 'نسخهٔ نصب‌شده') === 1);
// مجموع آیتم‌های **همهٔ** نسخه‌ها — با هر نسخهٔ تازه خودش را تنظیم می‌کند.
$allItems = 0;
foreach (release_notes_all() as $entry) {
    $allItems += count((array) ($entry['items'] ?? []));
}
check('صفحهٔ تاریخچه همهٔ آیتم‌ها را دارد (کامل، نه خلاصه)',
    substr_count($historyHtml, '<li>') === $allItems,
    'li=' . substr_count($historyHtml, '<li>') . ' / expected=' . $allItems);
check('صفحهٔ تاریخچه به پاپ‌آپ وابسته نیست (localStorage ندارد)',
    strpos($historyHtml, 'localStorage') === false);

/* ── پاک‌سازی و خلاصه ────────────────────────────────────────────────── */
@unlink(APP_ROOT . '/VERSION');
@rmdir(APP_ROOT);

echo "\n" . str_repeat('=', 60) . "\n";
echo 'PASS=' . $passes . ' | FAIL=' . $fails . "\n";
if ($fails > 0) {
    echo "❌ کارت تغییرات یا تاریخچهٔ نسخه‌ها ایراد دارد.\n";
    exit(1);
}
echo "✅ کارت تغییرات سامانه و تاریخچهٔ نسخه‌ها سالم است.\n";
exit(0);
