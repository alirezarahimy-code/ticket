<?php
/**
 * عیب‌یابی شناسنامههای تکراری — فقط خواندنی (هیچ تغییری در دیتابیس نمیدهد).
 * اجرا: php tools/diag_asset_duplicates.php
 * نیاز به bootstrap سامانه دارد (config.php خوانده میشود).
 */
declare(strict_types=1);
$root = dirname(__DIR__);
if (!is_file($root . '/config.php')) { fwrite(STDERR, "config.php پیدا نشد.\n"); exit(1); }
require_once $root . '/config.php';
if (!function_exists('db')) { fwrite(STDERR, "تابع db() در دسترس نیست.\n"); exit(1); }
require_once $root . '/asset-names.php';

function out(string $s = ''): void { echo $s . PHP_EOL; }
function q(string $sql, array $p = []): array { $s = db()->prepare($sql); $s->execute($p); return $s->fetchAll(PDO::FETCH_ASSOC); }

out('═══════════════════════════════════════════════════════════');
out('  عیب‌یاب شناسنامههای تکراری (فقط خواندنی)');
out('═══════════════════════════════════════════════════════════');
$total = (int) db()->query('SELECT COUNT(*) FROM assets')->fetchColumn();
out("تعداد کل رکوردهای assets: {$total}");
out('');

// ۱) تکرار بر اساس hostname دقیق
$exact = q('SELECT hostname, COUNT(*) c, GROUP_CONCAT(id ORDER BY id) ids, GROUP_CONCAT(asset_tag ORDER BY id SEPARATOR " | ") tags FROM assets WHERE hostname IS NOT NULL AND hostname <> "" GROUP BY hostname HAVING c > 1 ORDER BY c DESC, hostname');
out('۱) تکرار بر اساس hostname دقیق: ' . count($exact) . ' گروه');
foreach (array_slice($exact, 0, 20) as $r) { out('   ' . $r['hostname'] . ' → ' . $r['c'] . ' ردیف | id=' . $r['ids'] . ' | tags=' . $r['tags']); }
out('');

// ۲) تکرار بر اساس نام نمایشی نرمالشده (همون چی که تو لیست میبینی)
$all = q('SELECT id, asset_tag, hostname, source, serial_number FROM assets ORDER BY hostname, id');
$groups = [];
foreach ($all as $a) {
    $key = function_exists('mb_strtolower') ? mb_strtolower(asset_name_core((string) ($a['hostname'] ?: $a['asset_tag'])), 'UTF-8') : strtolower(asset_name_core((string) ($a['hostname'] ?: $a['asset_tag'])));
    if ($key === '') continue;
    $groups[$key][] = $a;
}
$dupGroups = array_filter($groups, static fn($g) => count($g) > 1);
out('۲) تکرار بر اساس نام نمایشی (asset_name_core): ' . count($dupGroups) . ' گروه');
$shown = 0;
foreach ($dupGroups as $key => $g) {
    if ($shown++ >= 25) { out('   ... و بقیه'); break; }
    $parts = [];
    foreach ($g as $a) { $parts[] = '#' . $a['id'] . ':' . $a['asset_tag'] . '/' . $a['hostname'] . '/src=' . $a['source']; }
    out('   ' . $key . ' → ' . implode('  ||  ', $parts));
}
out('');

// ۳) پراکندگی منبع
$src = q('SELECT source, COUNT(*) c FROM assets GROUP BY source ORDER BY c DESC');
out('۳) پراکندگی منبع (source):');
foreach ($src as $r) { out('   ' . ($r['source'] ?: '(خالی)') . ' = ' . $r['c']); }
out('');

// ۴) چند ردیف با hostname خالی
$empty = (int) db()->query('SELECT COUNT(*) FROM assets WHERE hostname IS NULL OR hostname = ""')->fetchColumn();
out('۴) رکوردهای بدون hostname: ' . $empty);
out('');

// ۵) source نامعتبر
$col = db()->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'source'")->fetchColumn();
out('۵) تعریف ستون source در دیتابیس زنده: ' . ($col ?: '?'));
$bad = q("SELECT source, COUNT(*) c FROM assets WHERE source NOT IN ('manual','server_inventory','agent','domain') GROUP BY source");
out('   مقادیر نامعتبر: ' . ($bad ? json_encode($bad, JSON_UNESCAPED_UNICODE) : 'هیچ'));
out('');

// ۶) asset_profiles یتیم / خالی
$orphan = (int) db()->query('SELECT COUNT(*) FROM asset_profiles p LEFT JOIN assets a ON a.id = p.asset_id WHERE a.id IS NULL')->fetchColumn();
$missing = (int) db()->query('SELECT COUNT(*) FROM assets a LEFT JOIN asset_profiles p ON p.asset_id = a.id WHERE p.asset_id IS NULL')->fetchColumn();
out('۶) asset_profiles یتیم (بدون assets): ' . $orphan . ' | assets بدون پروفایل: ' . $missing);
out('');

// ۷) تگهای خودکار DOMAIN- و CLIENT-
$domainTags = (int) db()->query("SELECT COUNT(*) FROM assets WHERE asset_tag LIKE 'DOMAIN-%'")->fetchColumn();
$clientTags = (int) db()->query("SELECT COUNT(*) FROM assets WHERE asset_tag LIKE 'CLIENT-%'")->fetchColumn();
out('۷) تگ DOMAIN-*: ' . $domainTags . ' | CLIENT-*: ' . $clientTags);
out('');
out('پایان. هیچ تغییری اعمال نشد.');
