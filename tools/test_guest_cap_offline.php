<?php
declare(strict_types=1);

/**
 * آزمون سقف فیش مهمان (بدون دیتابیس، فقط CLI).
 *   php tools/test_guest_cap_offline.php
 * قاعده: سقف متغیر = ورودها تا آن لحظه − خروج‌های قبل از ساعت قطع.
 * خروج بعد از ساعت قطع سقف را کم نمی‌کند (آن افراد فیش گرفته و غذا خورده‌اند).
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

$cap = static fn (int $entries, int $earlyExits): int => food_guest_variable_cap_from_counts($entries, $earlyExits);

// ۱) ساعت ۱۲:۱۰ — ۴ نفر وارد شده‌اند، هیچ‌کس قبل از ۱۲ خارج نشده ⇒ سقف ۴.
check('۱۲:۱۰ چهار ورودی، بدون خروج قبل از قطع ⇒ سقف ۴', $cap(4, 0) === 4);

// ۲) ۱۲:۳۰ — دو نفر که فیش گرفته‌اند بعد از ۱۲ خارج می‌شوند ⇒ سقف همچنان ۴، و ۲ فیش باقی است.
check('خروج بعد از ۱۲ سقف را کم نمی‌کند ⇒ سقف ۴', $cap(4, 0) === 4);
check('سقف ۴ با ۲ فیش صادرشده ⇒ هنوز مجاز', food_ticket_guest_cap_reached(2, 4, 0, 0, 0) === false);
check('سقف ۴ با ۴ فیش صادرشده ⇒ بسته', food_ticket_guest_cap_reached(4, 4, 0, 0, 0) === true);

// ۳) ۱۲:۴۵ — یک نفر جدید وارد می‌شود ⇒ ورودها ۵ ⇒ سقف ۵ و با ۲ فیش یکی دیگر مجاز است.
check('ورود نفر جدید ۱۲:۴۵ ⇒ سقف ۵', $cap(5, 0) === 5);
check('سقف ۵ با ۲ فیش صادرشده ⇒ مجاز', food_ticket_guest_cap_reached(2, 5, 0, 0, 0) === false);

// ۴) خروج قبل از ساعت قطع سقف را کم می‌کند.
check('یک خروج قبل از ۱۲ از ۴ ورودی ⇒ سقف ۳', $cap(4, 1) === 3);
check('سقف ۳ با ۳ فیش صادرشده ⇒ بسته', food_ticket_guest_cap_reached(3, 3, 0, 0, 0) === true);

// ۵) کسی نیامده ⇒ بسته؛ سقف هرگز منفی نمی‌شود.
check('بدون ورودی ⇒ سقف ۰ و بسته', $cap(0, 0) === 0 && food_ticket_guest_cap_reached(0, 0, 0, 0, 0) === true);
check('خروج بیشتر از ورودی ⇒ سقف صفر (نه منفی)', $cap(0, 2) === 0);

// ۶) تردد در دسترس نیست ⇒ سقف ثابت قدیمی.
check('سقف متغیر نامعلوم، سقف ثابت ۲۰: ۱۹ صادر ⇒ مجاز', food_ticket_guest_cap_reached(19, null, 20, 0, 0) === false);
check('سقف متغیر نامعلوم، سقف ثابت ۲۰: ۲۰ صادر ⇒ بسته', food_ticket_guest_cap_reached(20, null, 20, 0, 0) === true);
check('نه تردد نه سقف ثابت ⇒ بی‌سقف (رفتار قبلی)', food_ticket_guest_cap_reached(999, null, 0, 0, 0) === false);

// ۷) سقف هر کارت همچنان اعمال می‌شود.
check('سقف کارت ۲: صادرشده ۲ ⇒ بسته حتی اگر سقف کل جا باشد', food_ticket_guest_cap_reached(2, 10, 20, 2, 2) === true);
check('سقف کارت ۲: صادرشده ۱ ⇒ مجاز', food_ticket_guest_cap_reached(1, 10, 20, 1, 2) === false);

// ۸) پیش‌فرض‌ها بدون تنظیمات: حالت ثابت و ساعت قطع ۱۲:۰۰.
check('پیش‌فرض ساعت قطع ۱۲:۰۰:۰۰', food_guest_cap_cutoff() === '12:00:00');
check('پیش‌فرض حالت سقف ثابت', food_guest_cap_mode() === 'fixed');

echo "\nنتیجه: $pass موفق، $fail ناموفق\n";
exit($fail === 0 ? 0 : 1);
