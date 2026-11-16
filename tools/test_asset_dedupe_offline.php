<?php
declare(strict_types=1);

/**
 * آزمون منطق واقعی گروه‌بندی شناسنامه‌ها + هویت/بایند INSERT — r20
 *
 * اجرا: php tools/test_asset_dedupe_offline.php
 *
 * برخلاف نسخهٔ قبلی، union-find را دوباره کپی نمی‌کند: خود تابعی را صدا
 * می‌زند که ابزار asset_dedupe.php در محیط تولید استفاده می‌کند.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$root = dirname(__DIR__);
define('APP_ROOT', $root);
if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed { return $default; }
}
require_once $root . '/lib/asset-dedupe.php';
require_once $root . '/inventory.php';

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

function source_insert_sql(string $path): string
{
    $source = (string) file_get_contents($path);
    $marker = "prepare('INSERT INTO assets ";
    $start = strpos($source, $marker);
    if ($start === false) { return ''; }
    $start += strlen("prepare('");
    $end = strpos($source, "')->execute(inventory_asset_insert_params(\$assetTag, \$values))", $start);
    return $end === false ? '' : substr($source, $start, $end - $start);
}

echo "=== آزمون منطق واقعی ادغام و شناسنامه (offline) ===\n";

// همان پیاده‌سازی production: نام، سریال، MAC و اتصال زنجیره‌ای.
$groups = asset_dedupe_group_rows([['k' => 'pc1', 'ids' => '3,7']]);
check('نام یکسان، دو ردیف → یک گروه', count($groups) === 1 && $groups[0]['cnt'] === 2);
check('keeper نام، کمترین id است', ($groups[0]['keeper'] ?? '') === '3');

$groups = asset_dedupe_group_rows([], [['k' => 'SN12345', 'ids' => '11,12,13']]);
check('سریال یکسان با نام‌های متفاوت → گروه سه‌تایی', count($groups) === 1 && $groups[0]['cnt'] === 3);

$groups = asset_dedupe_group_rows([], [], [['k' => 'AABBCCDDEEFF', 'ids' => '21,22']]);
check('MAC یکسان و معتبر با نام/سریال متفاوت → یک گروه', count($groups) === 1 && ($groups[0]['ids'] ?? '') === '21,22');
$macFilter = asset_dedupe_filter_mac_groups([
    ['k' => 'AABBCCDDEEFF', 'ids' => '31,32', 'serial_variants' => 1],
    ['k' => '001122334455', 'ids' => '41,42', 'serial_variants' => 2],
]);
check('تعارض سریال معتبر با MAC مشترک از ادغام خودکار کنار گذاشته می‌شود',
    count($macFilter['rows']) === 1
    && ($macFilter['rows'][0]['ids'] ?? '') === '31,32'
    && $macFilter['conflicts'] === 1
    && ($macFilter['conflict_rows'][0]['ids'] ?? '') === '41,42');

$groups = asset_dedupe_group_rows(
    [['k' => 'pc1', 'ids' => '1,2']],
    [['k' => 'SN999', 'ids' => '2,3']],
    [['k' => 'AABBCCDDEEFF', 'ids' => '3,4']]
);
check('زنجیرهٔ نام→سریال→MAC، چهار ردیف را یک‌بار ادغام می‌کند', count($groups) === 1 && $groups[0]['cnt'] === 4);
check('keeper زنجیره، id=1 است', ($groups[0]['keeper'] ?? '') === '1');

$groups = asset_dedupe_group_rows(
    [['k' => 'pc1', 'ids' => '1,2']],
    [['k' => 'SN111', 'ids' => '5,6']]
);
check('گروه‌های نامرتبط مستقل می‌مانند', count($groups) === 2);

$groups = asset_dedupe_group_rows(
    [['k' => 'pc1', 'ids' => '1,2']],
    [['k' => 'SN111', 'ids' => '1,2,2']]
);
check('تکرار id در ورودی گروه، شمارش را دوباره زیاد نمی‌کند', count($groups) === 1 && $groups[0]['cnt'] === 2);

// ۱۲۵ ردیف: ۳۶ جفت تکراری که فقط از روی MAC پیدا می‌شوند + ۵۳ دستگاه یکتا.
$macRows = [];
for ($i = 1; $i <= 36; $i++) {
    $macRows[] = ['k' => sprintf('0200000000%02X', $i), 'ids' => (2 * $i - 1) . ',' . (2 * $i)];
}
$groups = asset_dedupe_group_rows([], [], $macRows);
$extra = array_sum(array_map(static fn (array $g): int => $g['cnt'] - 1, $groups));
check('سناریوی ۱۲۵ ردیف/۳۶ جفت از مسیر MAC دقیقاً ۳۶ ردیف کم می‌کند', $extra === 36, "extra=$extra");
check('پس از ادغام سناریو، شمارش ۸۹ می‌شود', 125 - $extra === 89);

// توابع واقعی هویت.
$serial = asset_stable_identity('PC-ADMIN', 'SN-ABC-123', 'AA:BB:CC:DD:EE:FF');
$mac = asset_stable_identity('PC-ADMIN', 'TO BE FILLED BY O.E.M.', 'AA-BB-CC-DD-EE-FF');
$name = asset_stable_identity('PC-ADMIN', 'UNKNOWN', 'not-a-mac');
check('سریال شناسهٔ اول است (نه MAC)', $serial['match'] === 'serial' && $serial['key'] === 'sn:SN-ABC-123');
check('MAC پس از سریال خالی/بی‌معنی شناسهٔ دوم است', $mac['match'] === 'mac' && $mac['key'] === 'mac:AABBCCDDEEFF');
check('نام رایانه فقط fallback سوم است', $name['match'] === 'hostname' && $name['key'] === 'pc-admin');
check('سریال بی‌معنی پذیرفته نمی‌شود', asset_identity_serial_value('DEFAULT STRING') === '');
check('MAC صفر/جعلی پذیرفته نمی‌شود', asset_identity_mac_value('00:00:00:00:00:00') === '');
check('MAC معتبر نرمال می‌شود', asset_identity_mac_value('aa-bb-cc-dd-ee-ff') === 'AABBCCDDEEFF');
check('MAC چندپخشی و متنِ شبیه MAC شناسه محسوب نمی‌شوند',
    asset_identity_mac_value('01:BB:CC:DD:EE:FF') === ''
    && asset_identity_mac_value('AA:BB:CC:DD:EE:FF-extra') === '');

$ip10 = asset_identity_key('192.168.1.10');
$ip11 = asset_identity_key('192.168.1.11');
check('IPهای متفاوت به یک کلید کوتاه مثل 192 تبدیل نمی‌شوند', $ip10 === '192.168.1.10' && $ip11 === '192.168.1.11' && $ip10 !== $ip11);
check('پیشوند CLIENT-/DOMAIN- روی IP اکتت‌ها را نگه می‌دارد',
    asset_identity_key('CLIENT-192.168.1.50') === '192.168.1.50'
    && asset_identity_key('DOMAIN-192.168.1.50') === '192.168.1.50');

// دقیقاً SQL و پارامترهایی که هر دو مسیر درج تولید در PHP استفاده می‌کنند.
$insertCases = [
    [$root . '/inventory.php', 'server inventory', array_fill(0, 17, 'sample')],
    [$root . '/api/inventory.php', 'agent API', array_fill(0, 20, 'sample')],
];
foreach ($insertCases as [$path, $label, $values]) {
    $sql = source_insert_sql($path);
    $params = inventory_asset_insert_params('TEST-ASSET', $values);
    check("$label INSERT از helper مشترک بایند می‌شود", $sql !== '' && count($params) === substr_count($sql, '?'), 'sql=' . substr_count($sql, '?') . ', params=' . count($params));
    check("$label فقط یک hostname در ابتدای بایند دارد", ($params[1] ?? null) === 'sample' && ($params[2] ?? null) === 'sample' && ($params[3] ?? null) === 'sample');
}

$dedupeSource = (string) file_get_contents($root . '/tools/asset_dedupe.php');
check('ابزار تولید از همان union-find آزموده‌شده استفاده می‌کند', str_contains($dedupeSource, 'asset_dedupe_group_rows($nameRows, $serialRows, $macRows)'));
check('ابزار تولید MAC را فقط با ۱۲ رقم hex معتبر گروه می‌کند', str_contains($dedupeSource, "REGEXP '^[0-9A-F]{12}$'"));
check('ابزار تولید MACهای junk را حذف می‌کند', str_contains($dedupeSource, "'000000000000', 'FFFFFFFFFFFF', '020000000000'"));
check('ابزار گزارشِ سه منبع نام/سریال/MAC را جدا می‌دهد', str_contains($dedupeSource, 'MAC بدون تعارض سریال: %d گروه'));
check('MAC مشترک با سریال‌های متعارض فقط گزارش می‌شود', str_contains($dedupeSource, 'serial_variants') && str_contains($dedupeSource, 'asset_dedupe_filter_mac_groups'));

$reset = (string) file_get_contents($root . '/tools/asset_reset.php');
check('ریست فقط با --apply حذف می‌کند', str_contains($reset, "in_array('--apply', \$argvList, true)"));
check('ریست پیش از حذف بکاپ می‌گیرد', str_contains($reset, 'بکاپ کامل'));
check('ریست در تراکنش است و در خطا rollback دارد', str_contains($reset, 'beginTransaction') && str_contains($reset, 'rollBack'));
check('ابزار بازگردانی موجود است', is_file($root . '/tools/asset_reset_restore.php'));

$inventorySource = (string) file_get_contents($root . '/inventory.php');
$apiSource = (string) file_get_contents($root . '/api/inventory.php');
check('مسیر سرور کلید hostname_key را در INSERT می‌نویسد', str_contains($inventorySource, 'hostname_key') && str_contains($inventorySource, 'inventory_asset_insert_params($assetTag, $values)'));
check('مسیر API کلید hostname_key را در INSERT می‌نویسد', str_contains($apiSource, 'hostname_key') && str_contains($apiSource, 'inventory_asset_insert_params($assetTag, $values)'));

echo "\n──────────────────────────────\n";
echo "نتیجه: $pass موفق، $fail ناموفق\n";
exit($fail === 0 ? 0 : 1);
