<?php
/**
 * تست آفلاین «هم‌ترازی schema.sql با مهاجرت‌ها» — تا هشدار کاذب «مهاجرت اجرا نشده» برنگردد.
 *
 * اجرا: php tools/test_schema_baseline_offline.php
 *
 * مسئله‌ای که این تست از بازگشتش جلوگیری می‌کند:
 *   گارد مهاجرت‌ها همهٔ فایل‌های migrations/*.sql را با جدول schema_updates مقایسه
 *   می‌کند. اگر نصب تازه از schema.sql ساخته شود و schema.sql آن مهاجرت‌ها را
 *   «ثبت‌شده» اعلام نکند، روی هر نصب تازه هشدار کاذب می‌دهد.
 *
 * این تست سه چیز را تضمین می‌کند:
 *   ۱) هر مهاجرت که اثرش در اسکیما اعمال شده، در baseline ثبت شده باشد
 *   ۲) ستون‌های مهاجرت‌های ساختاری واقعاً در schema.sql باشند
 *   ۳) فایل‌های مهاجرت با آنچه اسکیما اعلام می‌کند در تناقض نباشند
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
    echo "[FAIL] {$label}" . ($detail !== '' ? " :: {$detail}" : '') . "\n";
}

$schema = (string) @file_get_contents('schema.sql');
$empty = (string) @file_get_contents('install-empty-db.sql');
check('schema.sql موجود است', $schema !== '');
check('install-empty-db.sql موجود است', $empty !== '');

/** آیا این مهاجرت در baseline اسکیما ثبت شده؟ */
$seeded = static function (string $schema, string $name): bool {
    return (bool) preg_match("/'" . preg_quote($name, '/') . "'/", $schema);
};

/* ── ۱) مهاجرت‌هایی که اثرشان در اسکیما هست باید ثبت شده باشند ────────── */
$mustSeed = [
    '034-logging-escalation.sql'              => 'ستون‌های escalation_* در اسکیما هست',
    '036-food-meal-leftover-disposition.sql'  => 'ستون‌های leftover_* در اسکیما هست',
    '037-food-auto-receipts-delivered.sql'    => 'backfill تحویل؛ روی نصب تازه کاری ندارد',
    '038-offline-map-features.sql'             => 'جدول عوارض نقشه در اسکیما ساخته شده است',
];
foreach ($mustSeed as $name => $why) {
    check("schema.sql مهاجرت {$name} را ثبت کرده ({$why})", $seeded($schema, $name));
}

/* ── ۲) اگر ثبت شده، اثر ساختاری‌اش هم باید در اسکیما باشد ────────────── */
$structural = [
    '034-logging-escalation.sql'             => ['escalation_count', 'escalation_label'],
    '036-food-meal-leftover-disposition.sql' => ['leftover_staff_quantity', 'leftover_waste_quantity', 'leftover_keep_quantity', 'leftover_other_quantity', 'leftover_other_note'],
    '038-offline-map-features.sql'             => ['offline_map_features', 'geometry_json', 'properties_json'],
];
foreach ($structural as $migration => $columns) {
    foreach ($columns as $col) {
        check("schema.sql ستون {$col} را دارد (اثر {$migration})", strpos($schema, $col) !== false);
        check("install-empty-db.sql ستون {$col} را دارد", strpos($empty, $col) !== false);
    }
}

/* ── ۳) هیچ مهاجرت «ساختاری» بی‌اثر در اسکیما نماند ────────────────────── */
// مهاجرت‌هایی که ALTER/CREATE دارند و در baseline نیستند = هشدار کاذب بالقوه.
$files = glob('migrations/*.sql') ?: [];
$unseededStructural = [];
foreach ($files as $path) {
    $name = basename($path);
    if (stripos($name, 'rollback') !== false) {
        continue;
    }
    if ($seeded($schema, $name)) {
        continue;
    }
    $sql = (string) file_get_contents($path);
    // فقط آن‌هایی که ستون/جدول می‌سازند را حساس می‌دانیم.
    if (preg_match('/\b(CREATE\s+TABLE|ADD\s+COLUMN|ALTER\s+TABLE)\b/i', $sql)) {
        $unseededStructural[] = $name;
    }
}
// قاعدهٔ کلی — بدون استثنا: هر مهاجرت ساختاریِ روی دیسک باید در baseline ثبت شده
// باشد. «ثبت‌نشده ماندن» همان هشدار کاذب است، حتی اگر مهاجرت مال خط دیگری (۱.۶۳)
// باشد؛ چون فایلش به‌هرحال روی همین دیسک نشسته و گارد مهاجرت‌ها آن را می‌بیند.
check(
    'هیچ مهاجرت ساختاریِ ثبت‌نشده‌ای نمانده',
    $unseededStructural === [],
    implode(', ', $unseededStructural)
);

// ۰۳۵: جدولش را install-empty-db می‌سازد ولی ردیف ثبتش نبود => هشدار کاذب.
// فقط وقتی سنجیده می‌شود که فایلش (یا جدولش) در همین درخت باشد؛ در بستهٔ خط ۱.۶۲
// هیچ‌کدام نیست و همان‌جا هم هشدار کاذبی در کار نیست.
$has035File = is_file('migrations/035-request-center-appointments.sql');
$has035Table = strpos($empty, 'request_center_appointments') !== false
    || strpos($schema, 'request_center_appointments') !== false;
if ($has035File || $has035Table) {
    check(
        'install-empty-db.sql مهاجرت ۰۳۵ را ثبت کرده',
        $seeded($empty, '035-request-center-appointments.sql')
    );
    check(
        'schema.sql مهاجرت ۰۳۵ را ثبت کرده',
        $seeded($schema, '035-request-center-appointments.sql')
    );
    check(
        'جدول request_center_appointments واقعاً در اسکیما هست (پس ثبت ۰۳۵ راست است)',
        $has035Table
    );
} else {
    check('۰۳۵ در این درخت نیست (خط ۱.۶۲) — ثبت‌نشدنش هشدار کاذب نمی‌سازد', true);
}

/* ── ۴) هم‌ترازی دو فایل اسکیما در baseline ───────────────────────────── */
$countSchema = preg_match_all("/\\('1\\.(?:62\\.[0-9]+|63(?:\\.[0-9]+)?)', '[0-9]{3}-/", $schema);
check('baseline اسکیما حداقل ۳۶ ردیف دارد', (int) $countSchema >= 36, 'count=' . $countSchema);

/* ── ۵) مهاجرت ۰۳۳ ایمن است (تکرار تست اجرا) ────────────────────────── */
$m033 = (string) @file_get_contents('migrations/033-logging-legacy-context-collation.sql');
check('۰۳۳ عملیات تخریبی ندارد', !preg_match('/\b(DROP|TRUNCATE|DELETE)\b/i', $m033));
check('۰۳۳ فقط UPDATE است', stripos($m033, 'UPDATE log_issues') !== false);
check('۰۳۳ idempotent است', stripos($m033, "WHERE i.context = 'system'") !== false);

echo "\n" . str_repeat('=', 60) . "\n";
echo 'PASS=' . $passes . ' | FAIL=' . $fails . "\n";
if ($fails > 0) {
    echo "❌ هم‌ترازی schema.sql و مهاجرت‌ها ایراد دارد.\n";
    exit(1);
}
echo "✅ schema.sql با مهاجرت‌ها هم‌تراز است — هشدار کاذب «مهاجرت اجرا نشده» بازنمی‌گردد.\n";
exit(0);
