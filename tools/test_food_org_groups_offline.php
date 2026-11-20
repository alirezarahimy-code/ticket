<?php
/**
 * آزمون آفلاین — «نمایش بر اساس معاونت / بخش» در گزارش‌های غذا.
 *
 * خواستهٔ کاربر: در چهار گزارش (تجمیعی، غذای تحویلی، سفارش‌های امروز، غایبین
 * نهار) هر فرد زیر معاونت خودش بیاید. فقط ریشه سرگروه باشد، نه ادارهٔ میانی.
 * مدیریت مستقل، واحد مستقل (نمایندگی) و شرکت دوم هم صریحاً نام‌گذاری شوند.
 */
$root = dirname(__DIR__);
$lib = file_get_contents($root . '/lib/org-grouping.php');
$js = file_get_contents($root . '/assets/food-org-groups.js');
$menu = file_get_contents($root . '/assets/food-menu.js');
$html = file_get_contents($root . '/food-ticket-web/index.html');
$order = file_get_contents($root . '/food-order.php');

$ok = 0; $fail = 0;
function check(string $l, bool $p, string $d = ''): void {
    global $ok, $fail;
    if ($p) { $ok++; echo "  ✓ {$l}\n"; return; }
    $fail++; echo "  ✗ {$l}" . ($d !== '' ? " — {$d}" : '') . "\n";
}

echo "═══ ۱) کتابخانهٔ گروه‌بندی سمت سرور ═══\n";
check('فایل lib/org-grouping.php وجود دارد', $lib !== '');
foreach (['org_group_for_unit','org_group_meta_for_users','org_group_sort_key','org_group_type_label','org_group_unit_index','org_group_company_roots'] as $fn) {
    check("تابع {$fn} تعریف شده", strpos($lib, "function {$fn}(") !== false);
}
check('معاونت سرشاخه است', strpos($lib, "=== 'deputy'") !== false);
check('مدیریت مستقل سرشاخه است', strpos($lib, "=== 'management'") !== false);
check('واحد مستقل (نمایندگی) سرشاخه است', strpos($lib, "=== 'liaison'") !== false);
check('شرکت دوم جدا می‌شود', strpos($lib, 'org_group_company_roots') !== false && strpos($lib, "company-") !== false);
check('فرد بی‌واحد گم نمی‌شود', strpos($lib, 'بدون جایگاه') !== false);

echo "\n═══ ۲) واژه‌های صریح چهار نوع سرشاخه ═══\n";
foreach (['معاونت', 'مدیریت مستقل', 'واحد مستقل (نمایندگی)', 'شرکت دوم'] as $label) {
    check("برچسب «{$label}»", strpos($lib, $label) !== false);
}

echo "\n═══ ۳) endpoint نگاشت سازمانی ═══\n";
check('مسیر food-menu/org-map در مجوزها', strpos(file_get_contents($root . '/food-ticket.php'), "'food-menu/org-map'") !== false);
check('تابع food_order_org_map', strpos($order, 'function food_order_org_map(') !== false);
check('کد ملی و کد پرسنلی و شناسه کاربر همه نگاشت می‌شوند',
    strpos($order, "'nat:'") !== false && strpos($order, "'pc:'") !== false && strpos($order, "'uid:'") !== false);
check('فقط کاربران فعال', strpos($order, 'WHERE is_active = 1') !== false);

echo "\n═══ ۴) ماژول مشترک مرورگر ═══\n";
check('assets/food-org-groups.js وجود دارد', $js !== '');
check('FoodOrgGroups در window', strpos($js, 'global.FoodOrgGroups') !== false);
check('کش یک‌دقیقه‌ای دارد', strpos($js, 'CACHE_MS') !== false);
check('در خطا گزارش را خالی نمی‌کند', strpos($js, 'فهرست بدون گروه‌بندی نمایش داده می‌شود') !== false);
// خلاصه/فوتر حذف شد، ولی تضمین «هر ردیف در یک گروه» باید بماند:
// split() هر ردیف را بی‌قید در یکی از سبدها می‌ریزد و سبد «بدون جایگاه» را
// خودش می‌سازد؛ پس شمار ردیف‌های گروه‌ها همیشه = شمار ردیف‌های گزارش.
check('هر ردیف در یک گروه می‌نشیند (split)', strpos($js, 'buckets[key].rows.push(row);') !== false);
check('سبد «بدون جایگاه» ساخته می‌شود', strpos($js, "label: 'بدون جایگاه'") !== false);
check('برچسب نوع از مسیر حذف شده', strpos($js, 'typeLabel(group.type)') === false);
check('tooltip مسیر سازمانی', strpos($js, '__orgPath') !== false);
check('ارقام فارسی/عربی در کد ملی', strpos($js, '۰-۹') !== false);

echo "\n═══ ۵) دکمه در هر چهار گزارش ═══\n";
check('گزارش تجمیعی: data-agg-org-view', strpos($html, 'data-agg-org-view') !== false);
check('گزارش تجمیعی: هر دو فهرست (فیش و غایب) گروهی می‌شوند',
    substr_count($html, 'aggOrgList(') >= 2);
check('غذای تحویلی: دکمهٔ سوم', strpos($menu, "data-delivery-view=\"org\"") !== false);
check('غذای تحویلی: تابع گروهی', strpos($menu, 'deliveredReportByOrgHtml') !== false);
check('سفارش‌های امروز: دکمهٔ سوم', (bool) preg_match("/viewButton\('org', 'نمایش بر اساس معاونت \/ بخش'\)/", $menu));
check('سفارش‌های امروز: تابع گروهی', strpos($menu, 'ordersByOrgHtml') !== false);
check('غایبین نهار: data-absent-org-view', strpos($html, 'data-absent-org-view') !== false);
check('غایبین نهار: تابع گروهی', strpos($html, 'absentByOrgHtml') !== false);
check('همهٔ چهار گزارش متن «نمایش بر اساس معاونت / بخش» را دارند',
    substr_count($html, 'نمایش بر اساس معاونت / بخش') + substr_count($menu, 'نمایش بر اساس معاونت / بخش') >= 4);

echo "\n═══ ۶) بارگذاری ماژول در صفحه ═══\n";
check('script food-org-groups.js در index.html', strpos($html, 'food-org-groups.js') !== false);
check('نسخه در URL', strpos($html, 'food-org-groups.js?v=1.63.3') !== false);

echo "\n═══ ۷) CSS گروه‌ها ═══\n";
$css = file_get_contents($root . '/assets/food-menu.css');
foreach (['.fm-org-group', '.fm-org-group-head', '.fm-org-group-title', '.fm-org-group-count', '.fm-org-food'] as $sel) {
    check("{$sel} در CSS", strpos($css, $sel) !== false);
}
// خواستهٔ کاربر: «بینشون دیگه هیچی نباشه» — نه چیپ خلاصه، نه برچسب نوع.
check('نوار چیپ خلاصه حذف شده', strpos($js, 'fm-org-summary') === false);
check('برچسب نوع کنار عنوان نیست', strpos($js, 'fm-org-group-badge') === false);
check('واحد مستقل فقط نام خودش را می‌نویسد', strpos($lib, "'label'    => \$unit['name']") !== false);

echo "\n═══ ۸) تفکیک غذای هر معاونت ═══\n";
check('تابع foodBreakdown', strpos($js, 'function foodBreakdown(') !== false);
check('foodKey در گزینه‌ها پشتیبانی می‌شود', strpos($js, 'options.foodKey') !== false);
check('غذای تحویلی foodKey می‌دهد', strpos($menu, "foodKey: 'food_name'") !== false);
check('سفارش‌های امروز foodKey می‌دهد', substr_count($menu, "foodKey: 'food_name'") >= 2);
check('تجمیعی foodKey می‌دهد', strpos($html, "foodKey:'food'") !== false);
check('غایبین foodKey می‌دهد', strpos($html, "foodKey:'food'") !== false);
check('async def aggOrgList موجود است', strpos($html, 'function aggOrgList(') !== false);
check('loadOrgMap موجود است', strpos($html, 'function loadOrgMap(') !== false);

echo "\n" . str_repeat('─', 60) . "\n";
echo "نتیجه: {$ok} موفق، {$fail} ناموفق\n";
exit($fail === 0 ? 0 : 1);
