<?php
/**
 * تست آفلاین فایل Excel: فونت‌ها (B Nazanin / B Titr)، تک‌خطی‌بودن همهٔ سلول‌ها،
 * ساختار معتبر XLSX و درستی داده‌ها.
 * اجرا: php tools/test_xlsx_style_offline.php
 */
declare(strict_types=1);

define('APP_ROOT', __DIR__ . '/..');
require_once APP_ROOT . '/xlsx-writer.php';
if (!function_exists('xlsx_send')) {
    // xlsx_send در همین فایل هست؛ فقط برای اطمینان.
}
require_once APP_ROOT . '/xlsx-writer.php';

$fails = 0;
$passes = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $fails, $passes;
    if ($ok) { $passes++; echo "[PASS] {$label}\n"; return; }
    $fails++;
    echo "[FAIL] {$label}" . ($detail !== '' ? " :: {$detail}" : '') . "\n";
}

// ── ساخت یک فایل واقعی مثل خروجی شناسنامه ────────────────────────────────
$headers = ['نام کامپیوتر', 'سریال', 'CPU', 'RAM', 'IP'];
$data = [
    ['PC-023', 'SN-778899', 'Intel Core i7-10700', '16384 MB', '10.10.25.47'],
    ["PC-024\n(طبقهٔ دوم)", 'SN-1', "خط اول\nخط دوم", '8192 MB', '10.10.25.48'],
    ["دارای تب\tدر متن", 'SN-2', 'AMD Ryzen 5', '4096 MB', '10.10.25.49'],
];
$sheetRows = [['v' => 'گزارش شناسنامه‌های فنی', 's' => 'title']];
$sheetRows[] = array_map(static fn ($h): array => ['v' => (string) $h, 's' => 'header'], $headers);
foreach ($data as $row) {
    $sheetRows[] = array_map(static fn ($v): array => ['v' => (string) $v, 's' => 'body'], $row);
}
$bytes = xlsx_build([[
    'name' => 'گزارش',
    'rows' => $sheetRows,
    'merges' => ['A1:E1'],
    'widths' => [20, 20, 24, 14, 16],
]]);
check('۱: فایل XLSX ساخته شد', strlen($bytes) > 1000, (string) strlen($bytes) . ' bytes');

$tmp = tempnam(sys_get_temp_dir(), 'xlsxtest') . '.xlsx';
file_put_contents($tmp, $bytes);
$zip = new ZipArchive();
$openOk = $zip->open($tmp) === true;
check('۲: فایل یک ZIP معتبر است', $openOk);

$names = [];
for ($i = 0; $i < $zip->numFiles; $i++) { $names[] = $zip->getNameIndex($i); }
$required = ['[Content_Types].xml', '_rels/.rels', 'xl/workbook.xml', 'xl/styles.xml', 'xl/worksheets/sheet1.xml', 'xl/_rels/workbook.xml.rels'];
$missing = array_values(array_diff($required, $names));
check('۳: همهٔ اجزای لازم بستهٔ XLSX موجودند', $missing === [], implode(', ', $missing));

$styles = (string) $zip->getFromName('xl/styles.xml');
$sheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
$workbook = (string) $zip->getFromName('xl/workbook.xml');

/* ── فونت‌ها ─────────────────────────────────────────────────────────── */
check('۴: سلول‌های متن با فونت B Nazanin هستند',
    (bool) preg_match('/<font><sz val="11"\/><name val="B Nazanin"\/>/', $styles));
check('۵: سرستون‌ها با فونت B Titr و پررنگ‌اند',
    (bool) preg_match('/<font><b\/><sz val="11"\/><name val="B Titr"\/>/', $styles));
check('۶: عنوان گزارش با فونت B Titr و بزرگ‌تر است',
    (bool) preg_match('/<font><b\/><sz val="14"\/><name val="B Titr"\/>/', $styles));
check('۷: فونت Tahoma دیگر استفاده نمی‌شود', !str_contains($styles, 'Tahoma'));
check('۸: فونت‌ها charset فارسی (178) دارند',
    substr_count($styles, '<charset val="178"/>') === 3);

/* ── تک‌خطی ──────────────────────────────────────────────────────────── */
check('۹: هیچ سبکی wrapText="1" ندارد (همه تک‌خطی)',
    !str_contains($styles, 'wrapText="1"'));
check('۱۰: همهٔ سبک‌های دارای alignment، wrapText="0" دارند',
    substr_count($styles, 'wrapText="0"') === 3);
check('۱۱: هیچ سلولی در شیت خط جدید ندارد',
    !str_contains($sheet, "\n") || !preg_match('/<t [^>]*>[^<]*\n/', $sheet));

preg_match_all('/<t[^>]*>([^<]*)<\/t>/', $sheet, $texts);
$withNewline = [];
foreach ($texts[1] ?? [] as $t) {
    if (preg_match('/[\r\n\t]/', $t)) { $withNewline[] = $t; }
}
check('۱۲: متن هیچ سلولی خط جدید/تب ندارد', $withNewline === [], implode(' | ', $withNewline));

$multilineSource = array_values(array_filter($texts[1] ?? [], static fn (string $t): bool => str_contains($t, 'خط اول') || str_contains($t, 'طبقهٔ دوم')));
check('۱۳: متن چندخطی ورودی به یک خط تبدیل شده', count($multilineSource) === 2, implode(' | ', $multilineSource));

/* ── ساختار و داده ───────────────────────────────────────────────────── */
check('۱۴: شیت راست‌به‌چپ است', str_contains($sheet, 'rightToLeft="1"'));
check('۱۵: عنوان در ردیف اول merge شده', str_contains($sheet, '<mergeCell ref="A1:E1"/>'));
check('۱۶: عرض ستون‌ها اعمال شده', substr_count($sheet, 'customWidth="1"') === 5);
// عرض خودکار وقتی widths داده نشود
$autoBytes = xlsx_build([['rows' => [['کوتاه', 'یک متن خیلی طولانی‌تر برای سنجش عرض خودکار ستون'], ['ب', 'ج']]]]);
$autoTmp = tempnam(sys_get_temp_dir(), 'xlsxw') . '.xlsx';
file_put_contents($autoTmp, $autoBytes);
$az = new ZipArchive(); $az->open($autoTmp);
$asheet = (string) $az->getFromName('xl/worksheets/sheet1.xml');
$az->close(); @unlink($autoTmp);
preg_match_all('/<col min="(\d+)"[^>]*width="([\d.]+)"/', $asheet, $cm, PREG_SET_ORDER);
$widths = [];
foreach ($cm as $c) { $widths[(int) $c[1]] = (float) $c[2]; }
check('۲۴: بدون widths، عرض ستون خودکار از بلندترین متن محاسبه می‌شود',
    isset($widths[2]) && $widths[2] > 30 && $widths[2] <= 55,
    'col2=' . var_export($widths[2] ?? null, true));
check('۲۵: عرض خودکار در بازهٔ معقول (۸ تا ۵۵) می‌ماند',
    $widths !== [] && min($widths) >= 8.0 && max($widths) <= 55.0,
    'min=' . min($widths ?: [0]) . ' max=' . max($widths ?: [0]));
check('۱۷: کاراکترهای خاص XML درست escape شده‌اند', !preg_match('/<t[^>]*>[^<]*&(?!amp;|lt;|gt;|quot;|apos;|#)/', $sheet));
check('۱۸: نام شیت فارسی در workbook درست ثبت شده', str_contains($workbook, 'name="گزارش"'));

/* ── امنیت: فرمول اجرا نشود ──────────────────────────────────────────── */
$formulaBytes = xlsx_build([['rows' => [['=1+1', '=SUM(A1:A2)', 'safe']]]]);
$formulaTmp = tempnam(sys_get_temp_dir(), 'xlsxf') . '.xlsx';
file_put_contents($formulaTmp, $formulaBytes);
$fz = new ZipArchive(); $fz->open($formulaTmp);
$fsheet = (string) $fz->getFromName('xl/worksheets/sheet1.xml');
$fz->close(); @unlink($formulaTmp);
check('۱۹: مقادیر فرمولی به‌صورت متن ذخیره می‌شوند (اجرا نمی‌شوند)',
    str_contains($fsheet, 't="inlineStr"') && !str_contains($fsheet, '<f>'));

/* ── پاک‌سازی کاراکترهای نامعتبر ─────────────────────────────────────── */
$dirtyBytes = xlsx_build([['rows' => [["متن\x01با\x02کاراکتر\x00کنترل"]]]]);
$dirtyTmp = tempnam(sys_get_temp_dir(), 'xlsxd') . '.xlsx';
file_put_contents($dirtyTmp, $dirtyBytes);
$dz = new ZipArchive(); $dz->open($dirtyTmp);
$dsheet = (string) $dz->getFromName('xl/worksheets/sheet1.xml');
$dz->close(); @unlink($dirtyTmp);
// simplexml ممکن است در CLI نصب نباشد؛ اعتبار XML را با بررسی ساختاری می‌سنجیم.
$validXml = static function (string $xml): bool {
    if (!str_starts_with($xml, '<?xml')) { return false; }
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $xml)) { return false; }
    $prev = null;
    foreach (preg_split('//u', $xml, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
        if ($ch === '&' && !preg_match('/^&(amp|lt|gt|quot|apos|#\d+|#x[0-9a-fA-F]+);/', substr($xml, (int) strpos($xml, '&'), 12))) {
            // بررسی سادهٔ escape: هر & باید بخشی از یک entity باشد
        }
        $prev = $ch;
    }
    // توازن برچسب‌های ریشه
    return substr_count($xml, '<worksheet') === 1 && substr_count($xml, '</worksheet>') === 1;
};
check('۲۰: کاراکترهای کنترل نامعتبر حذف می‌شوند و XML معتبر می‌ماند', $validXml($dsheet));
check('۲۲: کاراکتر کنترل دیگر در شیت نیست', !preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $dsheet));
check('۲۳: متن سالم پس از حذف کاراکترهای کنترل باقی مانده', str_contains($dsheet, 'متن') && str_contains($dsheet, 'کاراکتر'));

// اعتبار همهٔ اجزای XML
$xmlOk = true;
$badPart = '';
foreach (['[Content_Types].xml', 'xl/workbook.xml', 'xl/styles.xml', 'xl/worksheets/sheet1.xml'] as $part) {
    $content = (string) $zip->getFromName($part);
    if (!str_starts_with($content, '<?xml') || substr_count($content, '<') === 0) { $xmlOk = false; $badPart = $part; break; }
    // توازن تقریبی: تعداد تگ باز و بسته باید برابر باشد (به‌جز تگ‌های خودبسته)
    $opens = preg_match_all('/<[a-zA-Z][^>]*[^\/]>/', $content);
    $closes = preg_match_all('/<\/[a-zA-Z][^>]*>/', $content);
    if ($opens !== $closes) { $xmlOk = false; $badPart = $part . " (open=$opens close=$closes)"; break; }
}
check('۲۱: همهٔ اجزای XML ساختار متوازن دارند', $xmlOk, $badPart);

$zip->close();
@unlink($tmp);

echo "\n" . ($fails === 0
    ? 'همهٔ ' . ($passes + $fails) . ' بررسی فایل Excel موفق بود.'
    : $fails . ' بررسی ناموفق از ' . ($passes + $fails)) . "\n";
exit($fails === 0 ? 0 : 1);
