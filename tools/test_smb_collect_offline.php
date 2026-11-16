<?php
declare(strict_types=1);
/**
 * آزمون آفلاین مسیر SMB — ۱.۶۳.۱-r18
 * ============================================================================
 * این مسیر هرگز روی سرور واقعی اجرا نشده بود و همین سه باگ کشنده داشت.
 * این آزمون بدون شبکه، بدون ویندوز و بدون Access اجرا می‌شود: فقط متن
 * اسکریپت‌ها را می‌سنجد تا همان باگ‌ها برنگردند.
 */
$root = dirname(__DIR__);
$smb = (string) file_get_contents($root . '/tools/collect_via_smb.ps1');
$inv = (string) file_get_contents($root . '/tools/collect_server_inventory.ps1');
$ds  = (string) file_get_contents($root . '/domain-scan.php');

$pass = 0; $fail = 0;
$check = static function (string $label, bool $ok, string $detail = '') use (&$pass, &$fail): void {
    if ($ok) { $pass++; echo "  [PASS] $label\n"; }
    else { $fail++; echo "  [FAIL] $label" . ($detail !== '' ? " — $detail" : '') . "\n"; }
};

echo "۱) باگ کدگذاری — ریشهٔ «دادهٔ سخت‌افزاری دریافت نشد»\n";
// عملگر «>» در PowerShell 5.1 خروجی را UTF-16LE می‌نویسد؛ PHP نمی‌تواند بخواند.
$ck = static fn (string $s): bool => str_contains($s, 'collect_server_inventory.ps1 >')
    || str_contains($s, ".ps1 > C:");
$check('هیچ ریدایرکت «>» برای فایل نتیجه نیست', !$ck($smb));
$check('جمع‌آور پارامتر OutFile دارد', str_contains($inv, '[string] $OutFile'));
$check('جمع‌آور فایل را با UTF8NoBOM می‌نویسد', str_contains($inv, 'UTF8Encoding -ArgumentList $false'));
$check('جمع‌آور WriteAllText دارد (نه Out-File)', str_contains($inv, 'WriteAllText'));
$check('SMB با -OutFile تسک را می‌سازد', str_contains($smb, '-OutFile C:\\ITSM\\'));
$check('SMB از «>» در آرگومان تسک استفاده نمی‌کند', !$ck($smb));

echo "\n۲) لو نرفتن رمز روی خط فرمان\n";
$check('هیچ /p $pass در schtasks نیست', !str_contains($smb, '/p $pass'));
$check('هیچ فراخوانی schtasks.exe نمانده', !str_contains($smb, 'schtasks.exe'));
$check('تسک با Register-ScheduledTask ساخته می‌شود', str_contains($smb, 'Register-ScheduledTask'));
$check('تسک با هویت SYSTEM اجرا می‌شود', str_contains($smb, "-UserId 'SYSTEM'"));
$check('تسک با Start-ScheduledTask اجرا می‌شود', str_contains($smb, 'Start-ScheduledTask'));
$check('تسک با Unregister-ScheduledTask پاک می‌شود', str_contains($smb, 'Unregister-ScheduledTask'));
$check('رمز از محیط فرآیند خوانده می‌شود', str_contains($smb, '$env:ITSM_SCAN_PASS'));

echo "\n۳) ترتیب و پاک‌سازی\n";
$rmPos = strpos($smb, 'Remove-Item -Path $remoteResult');
$regPos = strpos($smb, 'Register-ScheduledTask');
$check('فایل نتیجهٔ کهنه پیش از ساخت تسک پاک می‌شود', $rmPos !== false && $regPos !== false && $rmPos < $regPos);
$check('آزمون JSON پیش از بازگرداندن انجام می‌شود', str_contains($smb, 'ConvertFrom-Json'));
$check('ستون computer بررسی می‌شود', str_contains($smb, "contains 'computer'"));
$check('پاک‌سازی در finally است (حتی با خطا)', str_contains($smb, '} finally {'));

echo "\n۴) پیام‌های خطای تفکیک‌شده\n";
foreach (['SMB_NOT_ADMIN', 'SMB_BAD_CREDENTIALS', 'SMB_UNREACHABLE', 'SMB_TASK_FAILED', 'SMB_COLLECT_TIMEOUT', 'SMB_COLLECT_BAD_JSON'] as $code) {
    $check("کد $code وجود دارد", str_contains($smb, $code));
}
$check('هر سه کد در domain-scan.php نگاشت فارسی دارند',
    str_contains($ds, "'SMB_NOT_ADMIN' =>") && str_contains($ds, "'SMB_BAD_CREDENTIALS' =>"));
$check('نبود فایل SMB در لاگ سیستم ثبت می‌شود', str_contains($ds, 'روش SMB اجرا نشد'));

echo "\n۵) توازن ساختاری PowerShell\n";
$bal = static function (string $s): int {
    $d = 0;
    foreach (str_split($s) as $ch) {
        if ($ch === '{') { $d++; }
        elseif ($ch === '}') { $d--; }
    }
    return $d;
};
$check('collect_via_smb.ps1 آکولاد متوازن', $bal($smb) === 0, 'اختلاف=' . $bal($smb));
$check('collect_server_inventory.ps1 آکولاد متوازن', $bal($inv) === 0, 'اختلاف=' . $bal($inv));

echo "\n۶) پارامترها هم‌خوان‌اند\n";
$check('SMB همان پارامترهای جمع‌آور را صدا می‌زند (OutFile)',
    preg_match('/collect_server_inventory\.ps1 -OutFile/', $smb) === 1);
$check('جمع‌آور OutFile را در param می‌پذیرد',
    preg_match('/param\([^)]*\[string\] \$OutFile/s', $inv) === 1);

echo "\n──────────\nنتیجه: $pass موفق، $fail ناموفق\n";
exit($fail === 0 ? 0 : 1);
