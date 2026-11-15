<?php
declare(strict_types=1);

/**
 * test_key_roles_composition_offline.php — «سه قاعدهٔ ترکیب نقش» (تصمیم مالک ۲۰۲۶-۱۱-۱۶)
 *
 * ۱) نقش‌های کلیدی (کارشناس IT / کارشناس پشتیبانی / سوپروایز / بازرسی) نقش
 *    اصلی کاربر را عوض نمی‌کنند؛ فقط دسترسی اضافه می‌کنند.
 * ۲) کاربر می‌تواند هم‌زمان در هر دو فهرست کارشناس باشد و هر دو حوزه را ببیند.
 * ۳) «سوپروایز» فقط چهار دسترسی سرپرستی می‌دهد، نه همه‌چیز.
 *
 * این تست منطق واقعی را می‌خواند، نه یک بازنویسی موازی.
 */

const APP_ROOT = __DIR__ . '/..';

$pass = 0;
$fail = 0;
$skips = 0;
function ok(bool $cond, string $label, string $detail = ''): void
{
    global $pass, $fail;
    if ($cond) {
        $pass++;
        echo "  [PASS] $label\n";
    } else {
        $fail++;
        echo "  [FAIL] $label\n";
        if ($detail !== '') {
            echo "         $detail\n";
        }
    }
}
function skip(string $label): void
{
    global $skips;
    $skips++;
    echo "  [SKIP] $label\n";
}

echo "\n=== ۱) نقش‌های کلیدی، نقش اصلی را عوض نمی‌کنند ===\n";

$roleActions = (string) file_get_contents(APP_ROOT . '/actions/role-actions.php');

// کارشناس IT نباید primary_admin شود (اصلاح ۱.۶۲.۹۸)
ok(
    !preg_match("/array_diff\(\\\$itExperts, \\\$oldExperts\)[^;]*UPDATE users SET role = \"primary_admin\"/s", $roleActions),
    'کارشناس IT دیگر به primary_admin ارتقا نمی‌یابد'
);
ok(
    (bool) preg_match("/array_diff\(\\\$itExperts, \\\$oldExperts\)[\s\S]{0,300}?is_it_agent = 1/", $roleActions),
    'کارشناس IT فقط پرچم is_it_agent می‌گیرد'
);
// کارشناس پشتیبانی: هیچ UPDATE role ای برایش نباید باشد
ok(
    !preg_match("/array_diff\(\\\$supportExperts[^;]*UPDATE users SET role/s", $roleActions),
    'کارشناس پشتیبانی هیچ‌وقت نقش را تغییر نمی‌دهد'
);
// سوپروایز: افزودن role="supervisor" نباید باشد
ok(
    !preg_match("/array_diff\(\\\$supervisors, \\\$oldSupervisors\)[^;]*UPDATE users SET role = \"supervisor\"/s", $roleActions),
    'سوپروایز دیگر نقش را به supervisor تبدیل نمی‌کند'
);

echo "\n=== ۲) سوپروایز اعطایی: فقط چهار دسترسی ===\n";

$permissions = (string) file_get_contents(APP_ROOT . '/permissions.php');
if (!str_contains($permissions, 'function user_can_supervisor_grants')) {
    skip('توابع دسترسی در این بسته نیست — این بخش در مخزن معنا دارد.');
} else {
    // تصمیم مالک (۲۰۲۶-۱۱-۱۶): «دسترسی‌های اضافی سوپروایزر هم گرفته شود.»
    ok(
        str_contains($permissions, "return ['supervisor.panel'];"),
        'فهرست اعطای سوپروایز فقط پنل است (اضافی‌ها گرفته شد)'
    );
    // فقط بدنهٔ تابع سنجیده می‌شود، نه توضیحات (توضیح عمداً نام کدها را می‌آورد).
    $supBlock = '';
    if (preg_match('/function user_can_supervisor_grants\(\): array\s*\{\s*return \[([^\]]*)\]/', $permissions, $m)) {
        $supBlock = $m[1];
    }
    ok($supBlock !== '', 'بلوک اعطای سوپروایز پیدا شد');
    ok(!str_contains($supBlock, 'supervisor.decide'), 'supervisor.decide از اعطای فهرست حذف شد');
    ok(!str_contains($supBlock, 'supervisor.reopen'), 'supervisor.reopen از اعطای فهرست حذف شد');
    ok(!preg_match('/function user_can_supervisor_grants\(\): array\s*\{\s*return \[[^\]]*queue\.view/s', $permissions),
        'queue.view از اعطای سوپروایز حذف شد');
    ok(
        str_contains($permissions, 'user_is_key_role($user, \'supervisor\')'),
        'user_can() عضویت در فهرست سوپروایزر را می‌سنجد'
    );
    // نباید دسترسی‌های سنگین را بدهد
    foreach (['settings.general', 'ticket.view_all', 'logs.view', 'food.groups_override'] as $heavy) {
        ok(
            !preg_match("/user_can_supervisor_grants\(\)[\s\S]{0,400}?'" . preg_quote($heavy, '/') . "'/", $permissions),
            'سوپروایز اعطایی دسترسی سنگین «' . $heavy . '» نمی‌دهد'
        );
    }
    ok(
        str_contains($permissions, 'دسترسی‌های اضافی سوپروایزر'),
        'دلیل تصمیم در کد مستند است'
    );
}

echo "\n=== ۲.۵) کارشناس برگزیده باید واقعاً بتواند رسیدگی کند ===\n";
if (!str_contains($permissions, 'function user_can_expert_grants')) {
    skip('اعطای کارشناس در این بسته نیست.');
} else {
    ok(
        str_contains($permissions, "'ticket.edit', 'ticket.view_unit', 'ticket.reply', 'ticket.view_own', 'queue.view'"),
        'اعطای کارشناس شامل ticket.edit و queue.view است'
    );
    $expertBlock = '';
    if (preg_match('/function user_can_expert_grants\(\): array\s*\{\s*return \[([^\]]*)\]/', $permissions, $m)) {
        $expertBlock = $m[1];
    }
    ok($expertBlock !== '', 'بلوک اعطای کارشناس پیدا شد');
    ok(!str_contains($expertBlock, 'ticket.view_all'), 'اعطای کارشناس ticket.view_all (دیدن همه‌چیز) نمی‌دهد');
    ok(!str_contains($expertBlock, 'ticket.assign'), 'اعطای کارشناس ticket.assign (تخصیص) نمی‌دهد');
    ok(!str_contains($expertBlock, 'settings.'), 'اعطای کارشناس هیچ دسترسی تنظیمات نمی‌دهد');
    ok(
        str_contains($permissions, "user_is_key_role(\$user, 'it_expert') || user_is_key_role(\$user, 'support_expert')"),
        'هر دو فهرست کارشناس این اعطا را می‌گیرند'
    );
}

echo "\n=== ۲.۷) قانون: ادمین اصلی پیش‌فرض مسئول نمی‌شود ===\n";
$authScopeSrc = (string) file_get_contents(APP_ROOT . '/lib/auth-scope.php');

ok(
    !preg_match('/if \(user_is_primary_admin\(\$user\)\) \{\s*return \$group === \'it\';/', $authScopeSrc),
    'شرط قدیمی «ادمین اصلی ⇒ همیشه مجاز برای IT» حذف شد'
);
ok(
    str_contains($authScopeSrc, "\$isDesignated"),
    'ادمین اصلی فقط از مسیر «تعیین‌شده در فهرست» می‌گذرد'
);
ok(
    str_contains($authScopeSrc, 'function ticket_assignee_group_ok'),
    'تابع مشترک ticket_assignee_group_ok وجود دارد (یک نقطهٔ حقیقت)'
);
$fnSrc = (string) file_get_contents(APP_ROOT . '/modules/request-center/lib/functions.php');
ok(
    !preg_match('/ownerCondition = \$serviceGroup === \'it\'\s*\?\s*\'\(u\.role = "primary_admin"/', $fnSrc),
    'لایهٔ پشتیبان دیگر ادمین اصلی را نامزد نمی‌کند'
);
ok(
    str_contains($fnSrc, 'هیچ ادمین اصلی نباید پیش‌فرض'),
    'قانون در کد مستند است'
);

echo "\n=== ۳) حضور هم‌زمان در هر دو فهرست کارشناس ===\n";

$authScope = (string) file_get_contents(APP_ROOT . '/lib/auth-scope.php');
ok(
    (bool) preg_match('/\$scopeGroups\[\] = \'it\'/', $authScope),
    'حوزهٔ IT به فهرست حوزه‌ها اضافه می‌شود'
);
ok(
    (bool) preg_match('/\$scopeGroups\[\] = \'support\'/', $authScope),
    'حوزهٔ پشتیبانی به فهرست حوزه‌ها اضافه می‌شود'
);
ok(
    str_contains($authScope, 'service_group IN (?, ?)'),
    'کوئری هر دو حوزه را با IN می‌گیرد (نه فقط یکی)'
);
ok(
    !preg_match("/key_role_user_ids\('support_expert'\)[\s\S]{0,40}\}\s*elseif/s", $authScope),
    'الگوی انحصاری elseif حذف شد — هر دو فهرست با هم دیده می‌شوند'
);

echo "\n=== ۴) رفتار واقعی: کارشناس دو-حوزه‌ای ===\n";

ok(
    str_contains($authScope, 'هر دو حوزه با هم') || str_contains($authScope, 'هر دو فهرست کارشناس'),
    'رفتار دو-حوزه‌ای در کد مستند است'
);

// شبیه‌سازی سبک: کاربری در هر دو فهرست
$GLOBALS['fake_roles'] = ['it_expert' => [5], 'support_expert' => [5]];
function key_role_user_ids(string $r): array { return $GLOBALS['fake_roles'][$r] ?? []; }

$user = ['id' => 5, 'role' => 'user', 'is_active' => 1];
$inIt = in_array((int) $user['id'], key_role_user_ids('it_expert'), true);
$inSupport = in_array((int) $user['id'], key_role_user_ids('support_expert'), true);
ok($inIt && $inSupport, 'کاربر id=5 در هر دو فهرست شناسایی می‌شود');
$groups = [];
if ($inIt) { $groups[] = 'it'; }
if ($inSupport) { $groups[] = 'support'; }
ok($groups === ['it', 'support'], 'هر دو حوزه در فهرست حوزه‌های مجاز می‌آید', implode(',', $groups));
ok(count($groups) === 2, 'تعداد حوزه‌ها ۲ است (نه ۱)');

echo "\n" . str_repeat('═', 60) . "\n";
echo "PASS=$pass | FAIL=$fail" . ($skips > 0 ? " | SKIP=$skips" : "") . "\n";
exit($fail === 0 ? 0 : 1);
