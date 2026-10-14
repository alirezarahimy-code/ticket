<?php
declare(strict_types=1);

// آزمون آفلاین اتصال کاربران دامین به پرسنل غذا (SQLite درون‌حافظه).
// تطبیق نام، همنام‌ها، کد ملی متفاوت، ادغام، بازگشت تراکنش، و جلوگیری از انتخاب تکراری.

require __DIR__ . '/../food-personnel-link.php';

$failures = 0;
$passes = 0;
function check(string $label, bool $ok): void
{
    global $failures, $passes;
    if ($ok) {
        $passes++;
        echo "[ok] {$label}\n";
    } else {
        $failures++;
        echo "[FAIL] {$label}\n";
    }
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    full_name TEXT NOT NULL,
    first_name TEXT NULL,
    last_name TEXT NULL,
    employee_number TEXT NULL UNIQUE,
    national_code TEXT NULL,
    email TEXT NULL,
    department TEXT NULL,
    is_active INTEGER NOT NULL DEFAULT 1,
    role TEXT NOT NULL DEFAULT "user",
    auth_source TEXT NOT NULL DEFAULT "local",
    manager_user_id INTEGER NULL
)');
$pdo->exec('CREATE TABLE food_orders (id INTEGER PRIMARY KEY AUTOINCREMENT, employee_id INTEGER NOT NULL, food_date TEXT NOT NULL, created_by INTEGER NOT NULL, status TEXT NOT NULL DEFAULT "active", UNIQUE (employee_id, food_date))');
$pdo->exec('CREATE TABLE food_order_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NULL)');

$ins = $pdo->prepare('INSERT INTO users (id, username, full_name, first_name, last_name, employee_number, national_code, auth_source) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
// کاربران دامین
$ins->execute([1, 'a.rahimi', 'علی رحیمی', null, null, null, null, 'ldap']);            // همنام دو پرسنل غذا
$ins->execute([2, 'r.ahmadi', 'رضا احمدی', null, null, null, null, 'ldap']);             // یک تطبیق
$ins->execute([3, 'm.karimi', 'مریم کریمی', null, null, null, '3333333333', 'ldap']);    // کد ملی متفاوت
$ins->execute([4, 'h.jafari', 'حسین جعفری', null, null, '900', null, 'ldap']);           // از قبل کد دارد
$ins->execute([5, 'n.azizi', 'نیلوفر عزیزی', null, null, null, null, 'ldap']);           // بدون تطبیق
$ins->execute([6, 'ali.r2', 'عليرضا  كريمي', null, null, null, null, 'ldap']);           // نرمال‌سازی ی/ك عربی
// پرسنل غذا
$ins->execute([10, 'food_employee_1', 'علی رحیمی', 'علی', 'رحیمی', '1001', '1111111111', 'food']);
$ins->execute([11, 'food_employee_2', 'علی رحیمی', 'علی', 'رحیمی', '1002', '2222222222', 'food']);
$ins->execute([12, 'food_employee_3', 'رضا احمدی', 'رضا', 'احمدی', '1003', '', 'food']);
$ins->execute([13, 'food_employee_4', 'مریم کریمی', 'مریم', 'کریمی', '1004', '4444444444', 'food']);
$ins->execute([14, 'food_employee_5', 'علیرضا کریمی', 'علیرضا', 'کریمی', '1005', '', 'food']);

// سفارش‌های نمونه: پرسنل غذای ۱۲ (رضا احمدی) سفارش دارد
$pdo->exec("INSERT INTO food_orders (employee_id, food_date, created_by) VALUES (12, '2026-10-01', 1), (12, '2026-10-02', 1)");
$pdo->exec('INSERT INTO food_order_logs (user_id) VALUES (12)');

// ---------- تطبیق ----------
$cands = fpl_candidates($pdo);
$byId = [];
foreach ($cands as $c) {
    $byId[(int) $c['domain']['id']] = $c;
}
check('همنام دو پرسنل غذا → multi', $byId[1]['status'] === 'multi' && count($byId[1]['matches']) === 2);
check('یک تطبیق بدون کد ملی طرف دامین → single با پیش‌انتخاب', $byId[2]['status'] === 'single' && $byId[2]['chosen'] === 12);
check('کد ملی متفاوت → conflict', $byId[3]['status'] === 'conflict');
check('کاربر با کد پرسنلی → has_code', $byId[4]['status'] === 'has_code');
check('بدون تطبیق → none', $byId[5]['status'] === 'none');
check('نرمال‌سازی «عليرضا  كريمي» با «علیرضا کریمی» → single', $byId[6]['status'] === 'single' && $byId[6]['chosen'] === 14);

// ---------- مجوز انتخاب ----------
check('انتخاب معتبر مجاز است', fpl_pair_is_allowed($cands, 1, 10) && fpl_pair_is_allowed($cands, 1, 11));
check('انتخاب با کد ملی متفاوت مجاز نیست', !fpl_pair_is_allowed($cands, 3, 13));
check('انتخاب رکورد نامرتبط مجاز نیست', !fpl_pair_is_allowed($cands, 2, 10));

// ---------- ادغام ناموفق (تداخل سفارش در یک روز) → بدون تغییر ----------
$pdo->exec("INSERT INTO food_orders (employee_id, food_date, created_by) VALUES (2, '2026-10-01', 1)"); // دامین ۲ همان روز را دارد
$before = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$r = fpl_merge($pdo, 2, 12);
check('تداخل سفارش در یک روز → ادغام شکست می‌خورد', $r['ok'] === false);
$row12 = $pdo->query('SELECT employee_number, username FROM users WHERE id = 12')->fetch();
$row2 = $pdo->query('SELECT employee_number FROM users WHERE id = 2')->fetch();
check('شکست: رکورد پرسنل غذا دست‌نخورده است', $row12['employee_number'] === '1003' && $row12['username'] === 'food_employee_3');
check('شکست: کد پرسنلی به دامین داده نشده', $row2['employee_number'] === null);
check('شکست: تعداد کاربران تغییر نکرده', (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === (int) $before);
$pdo->exec("DELETE FROM food_orders WHERE employee_id = 2");

// ---------- اجرای واقعی ----------
$results = fpl_apply($pdo, ['2' => 12, '1' => 11]);
$okCount = count(array_filter($results, static fn ($x) => $x['ok']));
check('اجرای ادغام موفق برای دو مورد', $okCount === 2);
$d2 = $pdo->query('SELECT employee_number, national_code FROM users WHERE id = 2')->fetch();
check('دامین ۲ کد پرسنلی ۱۰۰۳ گرفت', $d2['employee_number'] === '1003');
check('دامین ۲ کد ملی خالی پرسنل غذا را نگرفت (کد ملی خالی بماند)', $d2['national_code'] === null || $d2['national_code'] === '');
$d1 = $pdo->query('SELECT employee_number, national_code FROM users WHERE id = 1')->fetch();
check('دامین ۱ کد ملی پرسنل غذا ۱۱ را گرفت (۲۲۲۲۲۲۲۲۲۲)', $d1['national_code'] === '2222222222');
check('رکورد پرسنل غذا ۱۲ حذف شد', $pdo->query('SELECT COUNT(*) FROM users WHERE id = 12')->fetchColumn() == 0);
check('سفارش‌ها به دامین ۲ منتقل شدند', (int) $pdo->query('SELECT COUNT(*) FROM food_orders WHERE employee_id = 2')->fetchColumn() === 2);
check('لاگ سفارش به دامین ۲ منتقل شد', (int) $pdo->query('SELECT COUNT(*) FROM food_order_logs WHERE user_id = 2')->fetchColumn() === 1);
check('دامین ۱ با کد ملی ۱۱۱۱ یا ۲۲۲۲ (انتخاب ۱۱) → کد پرسنلی ۱۰۰۲', (string) $pdo->query('SELECT employee_number FROM users WHERE id = 1')->fetchColumn() === '1002');
check('همنام دیگر (۱۰) دست‌نخورده ماند', $pdo->query('SELECT COUNT(*) FROM users WHERE id = 10')->fetchColumn() == 1);
check('پشتیبان ساخته شد', (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name LIKE 'users_backup_fpl_%'")->fetchColumn() === 1);
check('نتیجه‌ها نام پشتیبان دارند', ($results[0]['backup'] ?? '') !== '');

// انتخاب تکراری همان پرسنل غذا برای دو کاربر
$dup = fpl_apply($pdo, ['5' => 14, '6' => 14]);
check('رکورد پرسنل غذای تکراری رد می‌شود', count(array_filter($dup, static fn ($x) => $x['ok'])) <= 1);

// انتخاب ناسازگار در اجرا رد می‌شود
$bad = fpl_apply($pdo, ['3' => 13]);
check('انتخاب با کد ملی متفاوت در اجرا رد می‌شود', count($bad) === 1 && $bad[0]['ok'] === false);

// نام کاربری‌های موقت نباید بماند
check('هیچ نام کاربری موقت fpl_merged_ باقی نماند', (int) $pdo->query("SELECT COUNT(*) FROM users WHERE username LIKE 'fpl_merged_%'")->fetchColumn() === 0);

// ---------- گزارش ----------
$report = fpl_report_rows($pdo);
$cat = [];
foreach ($report as $r) {
    $cat[$r['domain_id']] = $r['category'];
}
check('گزارش: ادغام‌شده‌ها «تطبیق_شده» هستند (دامین ۱ و ۲ و ۶)', $cat['1'] === 'تطبیق_شده' && $cat['2'] === 'تطبیق_شده' && $cat['6'] === 'تطبیق_شده');
check('گزارش: کد از قبل ثبت‌شده «دارای_کد» است (دامین ۴)', $cat['4'] === 'دارای_کد');
check('گزارش: کد ملی متفاوت (دامین ۳)', $cat['3'] === 'کد_ملی_متفاوت');
check('گزارش: بدون تطبیق (دامین ۵)', $cat['5'] === 'بدون_تطبیق');
$csvLines = [];
ob_start();
fpl_report_csv($report);
$csv = ob_get_clean();
check('CSV گزارش BOM و سرستون دارد', str_starts_with($csv, "\xEF\xBB\xBF") && str_contains($csv, 'وضعیت'));
check('فهرست پشتیبان پیدا شد', count(fpl_backup_tables($pdo)) >= 1);

// ---------- کاربر دامین غیرفعال: نه تطبیق، نه گزارش تطبیق ----------
$p2 = new PDO('sqlite::memory:');
$p2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$p2->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, full_name TEXT, first_name TEXT, last_name TEXT, employee_number TEXT, national_code TEXT, email TEXT, department TEXT, role TEXT, is_active INTEGER, auth_source TEXT, manager_user_id INTEGER)');
$p2->exec("INSERT INTO users (id, username, full_name, employee_number, national_code, is_active, auth_source) VALUES (1,'x.off','سعید غیرفعال',NULL,NULL,0,'ldap')");
$p2->exec("INSERT INTO users (id, username, full_name, employee_number, national_code, is_active, auth_source) VALUES (2,'y.on','سعید فعال',NULL,NULL,1,'ldap')");
$p2->exec("INSERT INTO users (id, username, full_name, first_name, last_name, employee_number, national_code, is_active, auth_source) VALUES (10,'food_s1','سعید غیرفعال','سعید','غیرفعال','2001','5555555555',1,'food')");
$p2->exec("INSERT INTO users (id, username, full_name, first_name, last_name, employee_number, national_code, is_active, auth_source) VALUES (11,'food_s2','سعید فعال','سعید','فعال','2002','6666666666',1,'food')");
$c2 = fpl_candidates($p2);
$ids2 = array_map(static fn ($x) => (int) $x['domain']['id'], $c2);
check('کاربر دامین غیرفعال در فهرست تطبیق نیست', !in_array(1, $ids2, true));
check('کاربر دامین فعال همچنان در فهرست است', in_array(2, $ids2, true));
$r2 = fpl_report_rows($p2);
$cat2 = [];
foreach ($r2 as $r) {
    $cat2[$r['domain_id']] = $r['category'];
}
check('گزارش: کاربر غیرفعال «غیرفعال_دامین» است', ($cat2['1'] ?? '') === 'غیرفعال_دامین');

// ---------- اتصال دستی با کد پرسنلی (بدون تطبیق نام) ----------
$pdo->exec("INSERT INTO users (id, username, full_name, first_name, last_name, employee_number, national_code, auth_source, is_active) VALUES (20,'food_150','حسن هوان','ح','هوان','150','',  'food', 1)");
$pdo->exec("INSERT INTO users (id, username, full_name, first_name, last_name, employee_number, national_code, auth_source, is_active) VALUES (21,'food_151','ناشناس','ن','ش','151','1111111111','food', 1)");
$pdo->exec("INSERT INTO users (id, username, full_name, first_name, last_name, employee_number, national_code, auth_source, is_active) VALUES (8,'h.havan','ح هوان',NULL,NULL,NULL,NULL,'ldap', 1)");
$pdo->exec("INSERT INTO users (id, username, full_name, first_name, last_name, employee_number, national_code, auth_source, is_active) VALUES (9,'z.test','زهرا تست',NULL,NULL,NULL,'2222222222','ldap', 1)");
$pdo->exec("INSERT INTO users (id, username, full_name, first_name, last_name, employee_number, national_code, auth_source, is_active) VALUES (30,'food_0077','زهرا تست','زهرا','تست','0077','',  'food', 1)");
$pdo->exec("INSERT INTO users (id, username, full_name, first_name, last_name, employee_number, national_code, auth_source, is_active) VALUES (31,'food_0200','اسم دیگر','ا','د','0200','',  'food', 1)");

$m1 = fpl_apply($pdo, [], ['8' => '۱۵۰']);
check('اتصال دستی با کد فارسی «۱۵۰» موفق است', count($m1) === 1 && $m1[0]['ok'] === true);
check('اتصال دستی: کد پرسنلی ۱۵۰ به h.havan رسید', (string) $pdo->query('SELECT employee_number FROM users WHERE id = 8')->fetchColumn() === '150');
check('اتصال دستی: رکورد پرسنل غذا ۲۰ حذف شد', (int) $pdo->query('SELECT COUNT(*) FROM users WHERE id = 20')->fetchColumn() === 0);
check('پیام نتیجه نام پرسنل غذای متصل‌شده را دارد', str_contains($m1[0]['message'], 'حسن هوان'));
check('ادغام دستی: نام کامل کاربر دامین از پرسنل غذا گرفته شد', (string) $pdo->query('SELECT full_name FROM users WHERE id = 8')->fetchColumn() === 'حسن هوان');
check('ادغام دستی: نام کوچک/خانوادگی از پرسنل غذا آمد', (string) $pdo->query('SELECT first_name || " " || last_name FROM users WHERE id = 8')->fetchColumn() === 'ح هوان');

$m2 = fpl_apply($pdo, [], ['9' => '99999']);
check('کد پرسنلی ناموجود رد می‌شود', count($m2) === 1 && $m2[0]['ok'] === false);

$m3 = fpl_apply($pdo, [], ['9' => '151']);
check('کد ملی متفاوت در اتصال دستی رد می‌شود', count($m3) === 1 && $m3[0]['ok'] === false && (int) $pdo->query('SELECT COUNT(*) FROM users WHERE id = 21')->fetchColumn() === 1);

$m4 = fpl_apply($pdo, [], ['1' => '150']);
check('کاربری که کد پرسنلی دارد، اتصال دستی نمی‌گیرد', count($m4) === 1 && $m4[0]['ok'] === false);

$m5 = fpl_apply($pdo, [], ['9' => '77']);
check('صفرهای ابتدای کد (۷۷ ↔ 0077) تحمل می‌شود', count($m5) === 1 && $m5[0]['ok'] === true);

echo "\nنتیجه: {$passes} موفق، {$failures} ناموفق\n";
exit($failures === 0 ? 0 : 1);
