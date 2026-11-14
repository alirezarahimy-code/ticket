<?php
/**
 * تست «دریفت» (drift) بستهٔ ۱.۶۲.۹۷ — آیا نصب این بسته چیزی را روی سامانه عقب می‌برد؟
 *
 * اجرا: php tools/test_1_62_97_drift_offline.php
 *
 * چرا این تست؟  بستهٔ ۱.۶۲.۹۷ کل فایل food-ticket.php و food-ticket-web/index.html را
 * حمل می‌کند. اگر نسخهٔ شاخهٔ ما از نسخهٔ تولید (که مالک روی سامانه نصب کرده) قدیمی‌تر
 * باشد، نصب ۹۷ یک «عقب‌گرد بی‌صدا» می‌شود: فیچرها بی‌خبر ناپدید می‌شوند.
 *
 * روشِ سخت‌گیرانه (بدون تشخیص خط‌به‌خطِ شکننده):
 *   فایلِ بسته را می‌گیریم، **بلوک‌های افزودهٔ آزمون را دقیقاً حذف می‌کنیم**، و نتیجه باید
 *   بایت‌به‌بایت با فایل تولید برابر شود. اگر یک کاراکتر جای دیگری هم عوض شده باشد،
 *   تساوی نمی‌شود و تست قرمز می‌دهد. این قوی‌ترین شکل اثبات «هیچ دریفتی نیست» است.
 *
 * مرجع تولید (آنچه واقعاً روی ۱.۶۲.۹۶ نصب است):
 *   food-ticket.php            ← بستهٔ ۱.۶۲.۹۳ (آخرین بسته‌ای که این فایل را لمس کرد)
 *   food-ticket-web/index.html ← بستهٔ ۱.۶۲.۹۵ (آخرین بسته‌ای که این فایل را لمس کرد)
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
chdir(APP_ROOT);

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
    echo "[FAIL] {$label}" . ($detail !== '' ? " :: " . mb_substr($detail, 0, 400) : '') . "\n";
}

$zip97 = glob('downloads/ticket-update-1.62.96-to-1.62.97-*.zip')[0] ?? '';
$zip93 = glob('downloads/ticket-update-1.62.90-to-1.62.93-*.zip')[0] ?? '';
$zip95 = glob('downloads/ticket-update-1.62.94-to-1.62.95-*.zip')[0] ?? '';

check('بستهٔ ۱.۶۲.۹۷ پیدا شد', $zip97 !== '', $zip97);
check('بستهٔ ۱.۶۲.۹۳ (منبع food-ticket.php تولید) پیدا شد', $zip93 !== '', $zip93);
check('بستهٔ ۱.۶۲.۹۵ (منبع index.html تولید) پیدا شد', $zip95 !== '', $zip95);
if ($zip97 === '' || $zip93 === '' || $zip95 === '') {
    echo "\n[SKIP] بدون هر سه بسته، آزمون دریفت معنا ندارد.\n";
    exit(1);
}

function zipFile(string $zip, string $entry): string
{
    $z = new ZipArchive();
    if ($z->open($zip) !== true) {
        throw new RuntimeException('باز کردن ZIP ناموفق: ' . $zip);
    }
    $c = $z->getFromName($entry);
    $z->close();
    if ($c === false) {
        throw new RuntimeException("فایل «{$entry}» در {$zip} نیست.");
    }
    return $c;
}

$shipPhp  = zipFile($zip97, 'files/food-ticket.php');
$shipHtml = zipFile($zip97, 'files/food-ticket-web/index.html');
$prodPhp  = zipFile($zip93, 'files/food-ticket.php');
$prodHtml = zipFile($zip95, 'files/food-ticket-web/index.html');

/* ── ۱) فیچرهای تولید باید در بسته حاضر باشند ────────────────────────── */
$prodOnlyPhp = [
    'cancel-request-cutoff'           => 'مسیر مهلت لغو درخواست غذا',
    'food_ticket_aggregate_last_name' => 'مرتب‌سازی بر اساس نام خانوادگی',
    'user_first_name'                 => 'تفکیک نام/نام خانوادگی در گزارش تجمیعی',
];
foreach ($prodOnlyPhp as $needle => $label) {
    check("بسته فیچر تولید را دارد: {$label}", strpos($shipPhp, $needle) !== false, $needle);
    check("  ↳ و روی تولید ۱.۶۲.۹۵ هم هست (تست بی‌معنا نیست)", strpos($prodPhp, $needle) !== false, $needle);
}
$prodOnlyHtml = ['first_name', 'last_name', 'aggLastName', 'food-ticket-aggregate-fields-v3'];
foreach ($prodOnlyHtml as $needle) {
    check("بسته فیچر تولید را دارد: {$needle}", strpos($shipHtml, $needle) !== false, $needle);
    check("  ↳ و روی تولید ۱.۶۲.۹۵ هم هست", strpos($prodHtml, $needle) !== false, $needle);
}

/* ── ۲) اثبات اصلی: حذف بلوک‌های افزوده ⇒ تساوی بایت‌به‌بایت با تولید ── */

// (الف) بلوک بارگذاری کتابخانه در food-ticket.php
$blkRequire = <<<'BLK'
// آزمون جامع worker/چاپ/مدیریت — همان منطقی که ابزار CLI استفاده می‌کند.
if (is_file(__DIR__ . '/food-ticket-selftest.php')) {
    require_once __DIR__ . '/food-ticket-selftest.php';
}

BLK;

// (ب) خط مسیر API در نقشهٔ دسترسی
$blkRoute = "        // آزمون جامع worker/چاپ/مدیریت، در انتهای صفحهٔ «اتصال پایگاه‌ها» — فقط‌خواندنی.\n"
    . "        'worker-selftest' => 'food.db',\n";

// (ج) بلوک هندلر API
$blkHandler = <<<'BLK'
        // آزمون جامع Worker/چاپ/مدیریت — همان کتابخانه‌ای که CLI استفاده می‌کند.
        // فقط‌خواندنی: هیچ فیشی چاپ نمی‌شود و هیچ تنظیمی تغییر نمی‌کند.
        if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'POST'], true) && $route === 'worker-selftest') {
            if (!function_exists('food_ticket_selftest_run')) {
                food_ticket_api_json(['error' => 'کتابخانهٔ آزمون (food-ticket-selftest.php) بارگذاری نشد.'], 500);
            }
            $payload = food_ticket_selftest_run([
                'config' => food_ticket_config(true),
                'source' => 'panel',
                'dry_print' => true,
            ]);
            $payload['text'] = food_ticket_selftest_text($payload);
            $payload['ok'] = (int) ($payload['verdict']['counts']['fail'] ?? 0) === 0;
            food_ticket_api_json($payload);
        }

BLK;

// (د) بلوک بارگذاری «تغییرات این به‌روزرسانی»
$blkNotesRequire = <<<'BLK'
// «تغییرات این به‌روزرسانی» داخل خود پنل (مستقل از index.php/۱.۶۳).
if (is_file(__DIR__ . '/food-ticket-release-notes.php')) {
    require_once __DIR__ . '/food-ticket-release-notes.php';
}
BLK;

// (ه) کلید releaseNotes در payload نسخهٔ پنل
$blkNotesPayload = <<<'BLK'
        // «تغییرات این به‌روزرسانی» — بنر بالای پنل (مستقل از index.php/۱.۶۳).
        'releaseNotes' => function_exists('food_ticket_release_notes_current')
            ? food_ticket_release_notes_current()
            : null,
BLK;

// (و) کلید releaseNotes در payload تنظیمات
$blkNotesCfg = <<<'BLK'
        // «تغییرات این به‌روزرسانی» — پنل این را در نوار بالای صفحه نشان می‌دهد.
        'releaseNotes' => function_exists('food_ticket_release_notes_current')
            ? food_ticket_release_notes_current()
            : null,
BLK;

foreach (['بارگذاری کتابخانه' => $blkRequire, 'خط مسیر API' => $blkRoute, 'هندلر API' => $blkHandler,
          'بارگذاری تغییرات' => $blkNotesRequire, 'releaseNotes در نسخه' => $blkNotesPayload,
          'releaseNotes در تنظیمات' => $blkNotesCfg] as $label => $blk) {
    check("food-ticket.php بلوک «{$label}» را دقیقاً یک‌بار دارد", substr_count($shipPhp, $blk) === 1, 'count=' . substr_count($shipPhp, $blk));
}

$strippedPhp = str_replace([$blkRequire, $blkRoute, $blkHandler, $blkNotesRequire, $blkNotesPayload, $blkNotesCfg], '', $shipPhp);
// بلوک notesRequire یک خط خالی انتهایی جا می‌گذارد؛ تولید آن را ندارد.
$strippedPhp = str_replace(
    "}\n\nif (is_file(__DIR__ . '/food-ticket-templates.php')) {",
    "}\nif (is_file(__DIR__ . '/food-ticket-templates.php')) {",
    $strippedPhp
);
// بلوک‌های releaseNotes در payloadها یک خط خالی جا می‌گذارند؛ تولید آن را ندارد.
// (هر دو بلوک با کامنت متفاوت شروع می‌شوند، پس به متن بعد از آن‌ها لنگر می‌زنیم.)
$strippedPhp = str_replace("\n\n        'attendancePath' => (string) (\$config['attendance_path']", "\n        'attendancePath' => (string) (\$config['attendance_path']", $strippedPhp);
$strippedPhp = str_replace("\n\n        'attendancePath' => (string) (\$config['attendance_path']", "\n        'attendancePath' => (string) (\$config['attendance_path']", $strippedPhp);
$strippedPhp = str_replace("\n\n        'printer' => [", "\n        'printer' => [", $strippedPhp);
check(
    'food-ticket.php: حذف افزوده‌های آزمون ⇒ بایت‌به‌بایت برابر با تولید ۱.۶۲.۹۵',
    $strippedPhp === $prodPhp,
    'len ship=' . strlen($strippedPhp) . ' prod=' . strlen($prodPhp)
);

if ($strippedPhp !== $prodPhp) {
    // برای عیب‌یابی: اولین تفاوت را نشان بده.
    $n = min(strlen($strippedPhp), strlen($prodPhp));
    for ($i = 0; $i < $n; $i++) {
        if ($strippedPhp[$i] !== $prodPhp[$i]) {
            echo '        اولین تفاوت در آفست ' . $i . ":\n";
            echo '        بسته:  ' . str_replace("\n", '⏎', substr($strippedPhp, max(0, $i - 60), 160)) . "\n";
            echo '        تولید: ' . str_replace("\n", '⏎', substr($prodPhp, max(0, $i - 60), 160)) . "\n";
            break;
        }
    }
}

/* ── ۳) همان اثبات برای index.html ──────────────────────────────────── */
// کارت آزمون: تابع‌های ST_LABELS/ST_CLASS/selftestBox + فراخوانی در connections() + دو اکشن کلیک.
$startMarker = '/* ── آزمون جامع Worker / چاپ / مدیریت — در انتهای صفحهٔ «اتصال پایگاه‌ها» ──';
$endMarker = 'function connections(){';
$sPos = strpos($shipHtml, $startMarker);
$ePos = strpos($shipHtml, $endMarker);
check('index.html بلوک کارت آزمون را دارد', $sPos !== false && $ePos !== false && $ePos > $sPos);

$strippedHtml = $shipHtml;
if ($sPos !== false && $ePos > $sPos) {
    // بلوک بین نشانهٔ شروع و تعریف connections() را بردار.
    $strippedHtml = substr($shipHtml, 0, $sPos) . substr($shipHtml, $ePos);
}
// فراخوانی کارت از داخل صفحه را بردار تا خط به حالت تولید برگردد.
$strippedHtml = str_replace('${selftestBox()}', '', $strippedHtml);
// دو اکشن کلیک را بردار.
$strippedHtml = str_replace(
    "if(act==='run-worker-selftest'){runWorkerSelfTest();return}if(act==='copy-worker-selftest'){copyWorkerSelfTest();return}",
    '',
    $strippedHtml
);

// بلوک بنر «تغییرات این به‌روزرسانی» (تابع releaseNotesBanner + loadReleaseNotes)
$bStart = strpos($strippedHtml, '/* ── «تغییرات این به‌روزرسانی» — از API پنل غذا می‌آید');
$bEnd = strpos($strippedHtml, 'function emergencyWindowSettingsForm(){');
if ($bStart !== false && $bEnd !== false && $bEnd > $bStart) {
    $strippedHtml = substr($strippedHtml, 0, $bStart) . substr($strippedHtml, $bEnd);
}
$strippedHtml = str_replace('releaseNotesBanner()+versionBanner()', 'versionBanner()', $strippedHtml);
$strippedHtml = str_replace('loadReleaseNotes();', '', $strippedHtml);
// تابع بارگذاری یک‌بارهٔ بنر هم باید برود (جدا از خود بنر قرار دارد).
$lStart = strpos($strippedHtml, '/* بارگذاری یک‌بارهٔ «تغییرات این به‌روزرسانی»');
if ($lStart !== false) {
    $lEnd = strpos($strippedHtml, 'function render(){', $lStart);
    if ($lEnd !== false && $lEnd > $lStart) {
        $strippedHtml = substr($strippedHtml, 0, $lStart) . substr($strippedHtml, $lEnd);
    }
}
$strippedHtml = str_replace(
    "if(act==='dismiss-release-notes'){const el=document.getElementById('ft-release-notes');const v=el?el.dataset.version:'';try{localStorage.setItem('ft-release-notes-dismissed:'+v,'1')}catch(e){}el&&el.remove();return}",
    '',
    $strippedHtml
);

check(
    'index.html: حذف افزوده‌های آزمون ⇒ بایت‌به‌بایت برابر با تولید ۱.۶۲.۹۵',
    $strippedHtml === $prodHtml,
    'len ship=' . strlen($strippedHtml) . ' prod=' . strlen($prodHtml)
);
if ($strippedHtml !== $prodHtml) {
    $n = min(strlen($strippedHtml), strlen($prodHtml));
    for ($i = 0; $i < $n; $i++) {
        if ($strippedHtml[$i] !== $prodHtml[$i]) {
            echo '        اولین تفاوت در آفست ' . $i . ":\n";
            echo '        بسته:  ' . str_replace("\n", '⏎', substr($strippedHtml, max(0, $i - 60), 160)) . "\n";
            echo '        تولید: ' . str_replace("\n", '⏎', substr($prodHtml, max(0, $i - 60), 160)) . "\n";
            break;
        }
    }
}

/* ── ۴) بسته هر سه فایل لازم را حمل می‌کند ───────────────────────────── */
$z = new ZipArchive();
$z->open($zip97);
$names = [];
for ($i = 0; $i < $z->numFiles; $i++) {
    $names[] = $z->getNameIndex($i);
}
$z->close();
foreach (['files/food-ticket.php', 'files/food-ticket-web/index.html', 'files/food-ticket-selftest.php'] as $need) {
    check("بستهٔ ۹۷ فایل را حمل می‌کند: {$need}", in_array($need, $names, true));
}

/* ── خلاصه ───────────────────────────────────────────────────────────── */
echo "\n" . str_repeat('=', 60) . "\n";
echo 'PASS=' . $passes . ' | FAIL=' . $fails . "\n";
if ($fails > 0) {
    echo "❌ دریفت دارد — این بسته را نصب نکنید تا اصلاح شود.\n";
    exit(1);
}
echo "✅ بدون دریفت: نصب ۱.۶۲.۹۷ هیچ فیچر تولیدی را عقب نمی‌برد.\n";
exit(0);
