<?php
declare(strict_types=1);

/**
 * آزمون سقف فیش مهمان بر اساس تعداد داخل ساختمان (بدون دیتابیس، فقط CLI).
 *   php tools/test_guest_cap_offline.php
 * مثال: ۱۲:۲۰ پنج نفر داخلند ⇒ سقف ۵؛ ۱۲:۳۰ یک نفر وارد می‌شود ⇒ سقف ۶.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

require_once dirname(__DIR__) . '/food-ticket-engine.php';

$pass = 0;
$fail = 0;
function check(string $name, bool $ok): void
{
    global $pass, $fail;
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . "\n";
}

// ۱۲:۲۰ — ۵ نفر داخل، ۴ فیش صادر شده ⇒ هنوز یکی مجاز است.
check('۵ داخل و ۴ فیش صادرشده ⇒ مجاز', food_ticket_guest_cap_reached(4, 5, 20, 0, 0) === false);
// ۱۲:۲۰ — ۵ داخل و ۵ فیش ⇒ بسته.
check('۵ داخل و ۵ فیش صادرشده ⇒ بسته', food_ticket_guest_cap_reached(5, 5, 20, 0, 0) === true);
// ۱۲:۳۰ — یک نفر وارد شد، سقف ۶ ⇒ دوباره مجاز.
check('ورود نفر ششم ⇒ سقف ۶ و فیش جدید مجاز', food_ticket_guest_cap_reached(5, 6, 20, 0, 0) === false);
// خروج یک نفر: ۵ فیش صادرشده ولی فقط ۴ داخل ⇒ صدور جدید بسته، فیش‌های قبلی باطل نمی‌شوند.
check('خروج نفر ⇒ صدور جدید بسته می‌شود', food_ticket_guest_cap_reached(5, 4, 20, 0, 0) === true);
// کسی داخل نیست ⇒ هیچ فیش مهمانی.
check('۰ داخل ⇒ بسته (نه بی‌سقف)', food_ticket_guest_cap_reached(0, 0, 20, 0, 0) === true);
// تردد در دسترس نیست ⇒ سقف ثابت قدیمی.
check('بدون تردد، سقف ثابت ۲۰: ۱۹ صادر ⇒ مجاز', food_ticket_guest_cap_reached(19, null, 20, 0, 0) === false);
check('بدون تردد، سقف ثابت ۲۰: ۲۰ صادر ⇒ بسته', food_ticket_guest_cap_reached(20, null, 20, 0, 0) === true);
check('بدون تردد و بدون سقف ثابت ⇒ بی‌سقف (رفتار قبلی)', food_ticket_guest_cap_reached(999, null, 0, 0, 0) === false);
// سقف هر کارت همچنان اعمال می‌شود.
check('سقف کارت ۲: صادرشده ۲ ⇒ بسته حتی اگر کل جا باشد', food_ticket_guest_cap_reached(2, 10, 20, 2, 2) === true);
check('سقف کارت ۲: صادرشده ۱ ⇒ مجاز', food_ticket_guest_cap_reached(1, 10, 20, 1, 2) === false);

echo "\nنتیجه: $pass موفق، $fail ناموفق\n";
exit($fail === 0 ? 0 : 1);
