<?php
declare(strict_types=1);
/**
 * ۱.۶۲.۳۷ — هویت یکتای شناسنامهها.
 * این فایل مستقل است تا هم در index.php و هم در api/inventory.php (بدون وابستگی به UI) لود شود.
 */
if (!function_exists('asset_name_core')) {
    require_once dirname(__DIR__) . '/asset-names.php';
}
if (function_exists('asset_identity_key')) { return; }

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
    db()->prepare('UPDATE assets SET hostname_key = ? WHERE id = ?')->execute([$key !== '' ? $key : null, $assetId]);
}
