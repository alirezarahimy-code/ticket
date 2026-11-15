<?php
declare(strict_types=1);

/**
 * پاک‌سازی نام کامپیوترها در شناسنامه‌های فنی — نسخهٔ ۱.۶۳.۱
 *
 * مسئله: روی شبکه، reverse DNS برای کلاینت‌ها نامی مثل «clint 192.168.1.65»
 * برمی‌گرداند — یعنی اسم کامپیوتر و آدرس IP در یک فیلد. آن مقدار در
 * `assets.hostname` ذخیره شده و در فهرست‌ها و کمبوی «سیستم مرتبط» دیده می‌شود.
 * کاربر هیچ‌وقت IP خودش را نمی‌داند، پس این نمایش گمراه‌کننده است.
 *
 * چه می‌کند؟
 *   • نام خام هر رکورد را با همان قاعدهٔ نمایش (`asset_name_core`) می‌سنجد.
 *   • اگر نام خام «قابل پاک‌سازی» بود (IP چسبیده، دامنه، پیشوند خودکار)،
 *     نام تمیز را در ستون می‌نویسد و `hostname_key` را به‌روز می‌کند.
 *   • اگر نام رکورد فقط یک IP بود، **دست نمی‌زند**: آن رکورد نام واقعی ندارد
 *     و باید از برچسب روی کیس یا اسکن دامنه پر شود.
 *
 * اجرا:
 *   php tools/normalize_asset_hostnames.php                 فقط گزارش (هیچ تغییری)
 *   php tools/normalize_asset_hostnames.php --apply          اعمال تغییرها
 *   php tools/normalize_asset_hostnames.php --apply --ids 10,11,12
 *
 * نکته: این ابزار فقط «نمایش» را تغییر می‌دهد؛ اگر نام اصلی روی برچسب کیس
 * چیز دیگری است، آن را در شناسنامه وارد کنید — تغییر خودکار فقط IP و دامنه
 * را می‌برد و هرگز بخش حرفیِ نام را عوض نمی‌کند.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$configPath = (string) (getenv('ITSM_CONFIG_PATH') ?: dirname(__DIR__) . '/config.php');
if (!is_file($configPath)) {
    fwrite(STDERR, "❌ فایل تنظیمات پیدا نشد: {$configPath}\n"
        . "   این ابزار باید روی همان سروری اجرا شود که سامانه نصب شده است.\n");
    exit(1);
}

require dirname(__DIR__) . '/bootstrap.php';
if (!function_exists('asset_display_name')) {
    require dirname(__DIR__) . '/asset-names.php';
}

$args = array_slice($argv ?? [], 1);
$apply = in_array('--apply', $args, true);
$onlyIds = [];
foreach ($args as $i => $arg) {
    if ($arg === '--ids' && isset($args[$i + 1])) {
        foreach (explode(',', (string) $args[$i + 1]) as $idPart) {
            $idPart = (int) trim($idPart);
            if ($idPart > 0) {
                $onlyIds[] = $idPart;
            }
        }
    }
}

/**
 * آیا این نام خام «آلوده» به IP/دامنه است و ارزش تمیزکردن دارد؟
 * (نامی که فقط IP است دست نمی‌خورد — نام واقعی ندارد.)
 */
function normalize_should_rewrite(string $raw): bool
{
    $raw = trim($raw);
    if ($raw === '') {
        return false;
    }
    if (filter_var($raw, FILTER_VALIDATE_IP) !== false) {
        return false; // فقط IP: چیزی برای نجات نیست
    }
    $core = asset_name_core($raw);
    if ($core === '') {
        return false;
    }
    // اگر هسته همچنان IP است (نام از IP ساخته شده) هم دست نمی‌زنیم
    if (filter_var($core, FILTER_VALIDATE_IP) !== false) {
        return false;
    }
    return $core !== $raw;
}

$rows = db()->query('SELECT id, hostname, asset_tag, ip_address FROM assets ORDER BY id')->fetchAll();
if ($onlyIds) {
    $rows = array_values(array_filter($rows, static fn (array $r): bool => in_array((int) $r['id'], $onlyIds, true)));
}

$planned = [];
$untouchedIpOnly = [];
foreach ($rows as $row) {
    $raw = trim((string) ($row['hostname'] ?? ''));
    if ($raw === '') {
        continue;
    }
    if (filter_var($raw, FILTER_VALIDATE_IP) !== false) {
        $untouchedIpOnly[] = $row;
        continue;
    }
    if (!normalize_should_rewrite($raw)) {
        continue;
    }
    $clean = asset_name_core($raw);
    $planned[] = ['row' => $row, 'from' => $raw, 'to' => $clean];
}

echo "═" . str_repeat("═", 78) . "\n";
echo "  پاک‌سازی نام کامپیوترها — " . ($apply ? "حالت اعمال" : "حالت گزارش (هیچ تغییری نمی‌شود)") . "\n";
echo "═" . str_repeat("═", 78) . "\n\n";

printf("  رکوردهای بررسی‌شده : %d\n", count($rows));
printf("  قابل پاک‌سازی      : %d\n", count($planned));
printf("  فقط IP (رها می‌شود) : %d\n\n", count($untouchedIpOnly));

if (!$planned) {
    echo "  ✅ هیچ نام آلوده‌ای پیدا نشد.\n\n";
} else {
    printf("  %-6s %-42s %s\n", 'شناسه', 'نام کنونی', 'نام تازه');
    echo "  " . str_repeat("─", 76) . "\n";
    foreach ($planned as $item) {
        printf("  %-6d %-42s %s\n", (int) $item['row']['id'], $item['from'], $item['to']);
    }
    echo "\n";
}

if ($untouchedIpOnly) {
    echo "  ⚠️  این رکوردها فقط IP دارند و نام واقعی ندارند — دستی نام‌گذاری شوند\n";
    echo "     (نام روی برچسب کیس، یا با اجرای اسکن دامنه):\n";
    foreach ($untouchedIpOnly as $row) {
        printf("     شناسه %-5d  IP: %-16s  شناسهٔ رکورد: %s\n", (int) $row['id'], (string) $row['hostname'], (string) ($row['asset_tag'] ?? ''));
    }
    echo "\n";
}

if (!$apply) {
    echo "  برای اعمال، همین فرمان را با --apply اجرا کنید.\n";
    exit(0);
}

$changed = 0;
$failed = 0;
foreach ($planned as $item) {
    $id = (int) $item['row']['id'];
    try {
        $syncAvailable = function_exists('asset_sync_identity_key');
        if ($syncAvailable) {
            db()->prepare('UPDATE assets SET hostname = ?, updated_at = NOW() WHERE id = ?')->execute([$item['to'], $id]);
            asset_sync_identity_key($id);
        } else {
            db()->prepare('UPDATE assets SET hostname = ?, hostname_key = ?, updated_at = NOW() WHERE id = ?')
                ->execute([$item['to'], asset_identity_key($item['to']), $id]);
        }
        $changed++;
    } catch (Throwable $e) {
        $failed++;
        fwrite(STDERR, sprintf("  ❌ شناسه %d: %s\n", $id, $e->getMessage()));
    }
}

echo "  ✅ تغییر کرد: {$changed}";
if ($failed > 0) {
    echo " | ❌ ناموفق: {$failed}";
}
echo "\n\n";
exit($failed === 0 ? 0 : 1);
