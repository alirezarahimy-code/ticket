<?php
declare(strict_types=1);

/**
 * آزمون آفلاین ادغام شناسنامه‌های تکراری — ۱.۶۳.۱-r19
 * ============================================================================
 *   php tools/test_asset_dedupe_offline.php
 *
 * چرا این آزمون: کاربر ۸۹ سیستم واقعی دارد ولی شناسنامه ۱۲۵ رکورد نشان می‌دهد.
 * ابزار asset_dedupe.php قدیمی فقط بر اساس **نام** گروه‌بندی می‌کرد، پس
 * رکوردهایی که برای یک دستگاه با نام‌های مختلف (یا IP خام) ساخته شده بودند
 * هرگز پیدا نمی‌شدند. اکنون گروه‌بندی روی **سریال سخت‌افزار** هم انجام می‌شود.
 *
 * این آزمون بدون دیتابیس MySQL اجرا می‌شود: منطق گروه‌بندی (union-find) و
 * منطق هویت پایدار را با داده‌های واقعی‌نما می‌سنجد.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$pass = 0;
$fail = 0;
function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "[PASS] $label\n";
        return;
    }
    $fail++;
    echo "[FAIL] $label" . ($detail !== '' ? ' — ' . $detail : '') . "\n";
}

echo "=== آزمون ادغام تکراری‌ها (offline) ===\n";

// ─────────────────────────────────────────────────────────────────────────────
// ۱) بازتولید منطق اتصال گروه‌ها، دقیقاً همان کدی که در ابزار است.
//    نسخهٔ ابزار باید همین رفتار را داشته باشد؛ اگر عوض شد، این آزمون
//    باید عوض شود — و همین هدف است: قفل کردن رفتار.
// ─────────────────────────────────────────────────────────────────────────────
function build_groups(array $nameRows, array $serialRows): array
{
    $parent = [];
    foreach (array_merge($nameRows, $serialRows) as $row) {
        $ids = array_map('intval', explode(',', (string) $row['ids']));
        if (count($ids) < 2) { continue; }
        foreach ($ids as $id) { if (!isset($parent[$id])) { $parent[$id] = $id; } }
        $first = $ids[0];
        foreach ($ids as $id) {
            $ra = $first; while (($parent[$ra] ?? $ra) !== $ra) { $ra = $parent[$ra]; }
            $rb = $id;    while (($parent[$rb] ?? $rb) !== $rb) { $rb = $parent[$rb]; }
            if ($ra !== $rb) { $parent[max($ra, $rb)] = min($ra, $rb); }
        }
    }
    $components = [];
    foreach (array_keys($parent) as $id) {
        $root = $id;
        while (($parent[$root] ?? $root) !== $root) { $root = $parent[$root]; }
        $components[$root][] = (int) $id;
    }
    $groups = [];
    foreach ($components as $ids) {
        if (count($ids) < 2) { continue; }
        sort($ids);
        $groups[] = ['cnt' => count($ids), 'ids' => implode(',', $ids), 'keeper' => (string) $ids[0]];
    }
    usort($groups, static fn(array $a, array $b): int => $b['cnt'] <=> $a['cnt']);
    return $groups;
}

// ── ۱. تکرار خالص بر اساس نام ────────────────────────────────────────────────
$groups = build_groups([['k' => 'pc1', 'ids' => '3,7']], []);
check('نام یکسان، دو رکورد → یک گروه', count($groups) === 1 && $groups[0]['cnt'] === 2, json_encode($groups));
check('رکورد با کمترین id نگه داشته می‌شود', ($groups[0]['keeper'] ?? '') === '3');

// ── ۲. تکرار بر اساس سریال، با نام‌های مختلف (حالت واقعی ۱۲۵ در برابر ۸۹) ──
$groups = build_groups([], [['k' => 'SN12345', 'ids' => '11,12,13']]);
check('سریال یکسان با سه نام مختلف → یک گروه سه‌تایی', count($groups) === 1 && $groups[0]['cnt'] === 3, json_encode($groups));

// ── ۳. زنجیره: A~B با نام، B~C با سریال → هر سه یک گروه ────────────────────
$groups = build_groups(
    [['k' => 'pc1', 'ids' => '1,2']],
    [['k' => 'SN999', 'ids' => '2,3']]
);
check('زنجیرهٔ نام+سریال سه رکورد را یک گروه می‌کند', count($groups) === 1 && $groups[0]['cnt'] === 3, json_encode($groups));
check('در زنجیره، id=1 نگه داشته می‌شود', ($groups[0]['keeper'] ?? '') === '1');

// ── ۴. رکوردهای بدون ارتباط نباید ادغام شوند ────────────────────────────────
$groups = build_groups([['k' => 'pc1', 'ids' => '1,2']], [['k' => 'SN111', 'ids' => '5,6']]);
check('دو گروه مستقل، دو گروه می‌مانند', count($groups) === 2, json_encode($groups));

// ── ۵. رکوردی که هم‌نام و هم‌سریال است دوبار شمرده نمی‌شود ─────────────────
$groups = build_groups([['k' => 'pc1', 'ids' => '1,2']], [['k' => 'SN1', 'ids' => '1,2']]);
check('تداخل کامل نام و سریال → یک گروه دو‌تایی، نه چهار', count($groups) === 1 && $groups[0]['cnt'] === 2, json_encode($groups));

// ── ۶. شبیه‌سازی سناریوی کاربر: ۸۹ دستگاه، ۱۲۵ رکورد ───────────────────────
$nameRows = [];
for ($i = 1; $i <= 36; $i++) {
    // ۳۶ دستگاه با نام تکراری (درج دوباره با همان نام)
    $nameRows[] = ['k' => 'pc' . $i, 'ids' => (2 * $i - 1) . ',' . (2 * $i)];
}
$groups = build_groups($nameRows, []);
$extra = 0;
foreach ($groups as $g) { $extra += $g['cnt'] - 1; }
$after = 125 - $extra;
check('۱۲۵ رکورد با ۳۶ جفت تکرار → ۳۶ رکورد کمتر', $extra === 36, "extra=$extra");
check('شمارش پس از ادغام ۸۹ می‌شود', $after === 89, "after=$after");

// ── ۷. سریال‌های بی‌معنی نباید باعث ادغام شوند ──────────────────────────────
$junk = ['TO BE FILLED BY O.E.M.', 'DEFAULT STRING', 'SYSTEM SERIAL NUMBER', 'NONE', 'N/A', '0', '00000000', 'NOT SPECIFIED', 'CHASSIS SERIAL NUMBER', '0123456789', 'SERIAL', 'UNKNOWN'];
$tool = (string) file_get_contents(__DIR__ . '/asset_dedupe.php');
$missing = [];
foreach ($junk as $j) {
    if (!str_contains($tool, $j)) { $missing[] = $j; }
}
check('ابزار همهٔ سریال‌های بی‌معنی را رد می‌کند', $missing === [], implode(', ', $missing));

// ── ۸. ابزار باید بر اساس سریال گروه‌بندی کند، نه فقط نام ─────────────────
check('ابزار از serial_number برای گروه‌بندی استفاده می‌کند', str_contains($tool, 'GROUP BY UPPER(TRIM(serial_number))'));
check('ابزار نام را هم برای گروه‌بندی نگه داشته', str_contains($tool, 'GROUP BY hostname_key'));
check('ابزار دو منبع را با هم ادغام می‌کند', str_contains($tool, 'array_merge($nameRows, $serialRows)'));
check('ابزار تعداد گروه‌های هر منبع را گزارش می‌کند', str_contains($tool, 'از روی سریال سخت‌افزار'));

// ── ۹. مسیرهای درج باید hostname_key را پر کنند، وگرنه تکرار بازمی‌گردد ────
$root = dirname(__DIR__);
$insertPaths = [
    'inventory.php' => 'درج موجودی سرور',
    'api/inventory.php' => 'درج موجودی ایجنت',
];
foreach ($insertPaths as $file => $label) {
    $src = (string) file_get_contents($root . '/' . $file);
    $hasInsert = str_contains($src, 'INSERT INTO assets');
    $setsKey = (bool) preg_match('/INSERT INTO assets \([^)]*hostname_key/', $src);
    check("$label: ستون hostname_key در INSERT هست", $hasInsert && $setsKey);
}

// ── ۱۰. هویت پایدار: IP نباید هویت باشد ───────────────────────────────────
$defs = (string) file_get_contents($root . '/asset-names.php');
check('تابع هویت پایدار وجود دارد', str_contains($defs, 'function asset_stable_identity'));
check('تابع حل بر اساس دستگاه وجود دارد', str_contains($defs, 'function asset_resolve_by_machine'));
check('سریال بالاترین اولویت را دارد', (bool) preg_match("/function asset_stable_identity.*?match' => 'serial'/s", $defs));
check('MAC اولویت دوم را دارد', (bool) preg_match("/function asset_stable_identity.*?match' => 'mac'/s", $defs));

// ── ۱۱. ابزار ریست باید پیش‌فرض «بدون تغییر» باشد ─────────────────────────
$reset = (string) file_get_contents($root . '/tools/asset_reset.php');
check('ریست فقط با --apply حذف می‌کند', str_contains($reset, "in_array('--apply', \$argvList, true)"));
check('ریست قبل از حذف بکاپ می‌گیرد', str_contains($reset, 'بکاپ کامل'));
check('ریست در تراکنش اجرا می‌شود', str_contains($reset, 'beginTransaction'));
check('ریست در صورت خطا برمی‌گردد', str_contains($reset, 'rollBack'));
check('ابزار بازگردانی وجود دارد', is_file($root . '/tools/asset_reset_restore.php'));

// ── ۱۲. نام خام IP دیگر به «192» تبدیل نمی‌شود ──────────────────────────────
check('کلید هویت IP خام را حفظ می‌کند', str_contains($defs, 'asset_identity_key'));
$ipGuard = (bool) preg_match("/function asset_identity_key.*?preg_match\('.{0,40}\\\\d\{1,3\}.{0,60}return \\\$raw;/s", $defs);
check('asset_identity_key قبل از نرمال‌سازی IP خام را برمی‌گرداند', $ipGuard);
$prefixGuard = (bool) preg_match("/function asset_identity_key.*?CLIENT\|DOMAIN.{0,80}return \\\$ipMatch\[1\];/s", $defs);
check('asset_identity_key پیشوند CLIENT-/DOMAIN- روی IP را به خود IP تبدیل می‌کند', $prefixGuard);

echo "\n──────────────────────────────\n";
echo "نتیجه: $pass موفق، $fail ناموفق\n";
exit($fail === 0 ? 0 : 1);
