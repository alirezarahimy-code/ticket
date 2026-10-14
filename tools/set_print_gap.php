<?php
declare(strict_types=1);

/*
 * تنظیم فاصلهٔ بالای فیش و تراز چاپ — بدون نیاز به پنل وب.
 * نمونهٔ اجرا (در پوشهٔ ticket):
 *   php tools\set_print_gap.php --win-shift=0 --reverse=esc_e
 *   php tools\set_print_gap.php --top=0 --head=0 --edge=1
 *   php tools\set_print_gap.php --show
 * فقط فایل storage/food_ticket_netprint.json را می‌نویسد؛ هیچ داده یا فایل دیگری حذف نمی‌شود.
 */

if (PHP_SAPI !== 'cli') {
    exit("فقط از خط فرمان اجرا شود.\n");
}

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/food-ticket-netprint.php';

$opts = getopt('', ['top::', 'head::', 'edge::', 'win-shift::', 'reverse::', 'show']);
$file = food_ticket_np_json_file();

if (!isset($opts['show'])) {
    $patch = [];
    if (isset($opts['top']))       { $patch['top_gap_mm'] = (float) $opts['top']; }
    if (isset($opts['head']))      { $patch['head_offset_mm'] = (float) $opts['head']; }
    if (isset($opts['edge']))      { $patch['edge_align'] = filter_var($opts['edge'], FILTER_VALIDATE_BOOLEAN); }
    if (isset($opts['win-shift'])) { $patch['win_top_shift_mm'] = (float) $opts['win-shift']; }
    if (isset($opts['reverse']))   { $patch['reverse_feed_cmd'] = (string) $opts['reverse']; } // esc_e | esc_k | none
    if ($patch === []) {
        exit("هیچ مقداری داده نشده. نمونه: php tools\\set_print_gap.php --top=0 --head=0 --edge=1\n");
    }
    $res = food_ticket_np_write_json($patch);
    if ($res['error'] !== '') {
        exit("خطا در ذخیره: " . $res['error'] . "\n");
    }
    echo "ذخیره شد: " . $res['file'] . "\n";
}

echo "فایل تنظیمات: $file\n";
echo is_file($file) ? file_get_contents($file) . "\n" : "فایل هنوز ساخته نشده است.\n";
