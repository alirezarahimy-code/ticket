<?php
declare(strict_types=1);

/**
 * tools/diag_extract.txt — تشخیص «چرا استخراج از راه دور کار نمی‌کند» در یک نگاه
 * ============================================================================
 * ۱.۶۳.۱-r17
 *
 * مسئله‌ای که این ابزار برای آن ساخته شد: کاربر می‌گوید «TNI و Administrator Yar
 * همین کار را می‌کنند، چرا سامانه نه؟» و پیام سامانه «Access is denied» است.
 * این پیام سه معنی کاملاً متفاوت دارد و تا وقتی معین نشود کدام است، هر تغییری
 * حدس است. این ابزار هر لایه را جدا می‌سنجد و می‌گوید دقیقاً کدام یک شکسته است:
 *
 *   ۱) DNS/نام:  آیا نام مقصد به IP تبدیل می‌شود؟
 *   ۲) TCP 445:  مسیر SMB — شرط روش دوم (TNI-style) و شرط اجرای عامل
 *   ۳) TCP 135:  مسیر RPC/DCOM — شرط WMI از راه دور
 *   ۴) TCP 5985: مسیر WinRM
 *   ۵) اتصال واقعی CIM با هر پروتکل، و پیام دقیق خطا
 *   ۶) SMB با اعتبارنامه: آیا می‌توان C$ را باز کرد؟ (اثبات «ادمین محلی بودن»)
 *
 * اجرا روی سرور پنل (همان‌جا که PHP و PowerShell اجرا می‌شوند):
 *
 *     php tools/diag_extract.php PC1
 *     php tools/diag_extract.php PC1 DOMAIN\\user
 *
 * اگر نام کاربری داده نشود، از تنظیمات domain_scan_domain استفاده می‌شود و رمز
 * به‌صورت تعاملی پرسیده می‌شود (هرگز روی خط فرمان نمی‌آید). خروجی، متن فارسی
 * خوانا برای فرستادن به پشتیبانی است، نه JSON.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("این ابزار فقط از خط فرمان اجرا می‌شود.\n");
}

$root = dirname(__DIR__);
require_once $root . '/bootstrap.php';

$host = trim((string) ($argv[1] ?? ''));
if ($host === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
    fwrite(STDERR, "استفاده: php tools/diag_extract.php <نام-کامپیوتر> [DOMAIN\\user]\n");
    exit(2);
}

$user = trim((string) ($argv[2] ?? ''));
$pass = '';
if ($user !== '') {
    fwrite(STDOUT, 'رمز حساب ' . $user . ': ');
    if (function_exists('shell_exec') && stripos(PHP_OS_FAMILY, 'win') === 0) {
        // ویندوز: خواندن رمز بدون نمایش
        $pass = trim((string) shell_exec('powershell -NoProfile -Command "$s=Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($s))"'));
    } else {
        $pass = trim((string) fgets(STDIN));
    }
    fwrite(STDOUT, "\n");
}
$domain = trim((string) setting('domain_scan_domain', ''));
if ($user !== '' && strpos($user, '\\') === false && strpos($user, '@') === false && $domain !== '') {
    $user = $domain . '\\' . $user;
}

function diag_line(string $label, bool $ok, string $detail = ''): void
{
    $mark = $ok ? '✓' : '✗';
    $color = $ok ? "\033[32m" : "\033[31m";
    fwrite(STDOUT, sprintf("  %s%s\033[0m %-28s %s\n", $color, $mark, $label, $detail));
}

function diag_tcp(string $host, int $port, int $timeoutSec = 4): array
{
    $start = microtime(true);
    $errno = 0;
    $errstr = '';
    $socket = @fsockopen($host, $port, $errno, $errstr, $timeoutSec);
    $ms = (int) round((microtime(true) - $start) * 1000);
    if ($socket === false || !is_resource($socket)) {
        return [false, $ms, $errstr !== '' ? $errstr : 'اتصال برقرار نشد'];
    }
    fclose($socket);
    return [true, $ms, ''];
}

fwrite(STDOUT, "\n=== تشخیص استخراج شناسنامه: {$host} ===\n\n");

// ۰) محیط اجرا
fwrite(STDOUT, "۰) محیط اجرای سامانه\n");
diag_line('سیستم‌عامل سرور', PHP_OS_FAMILY === 'Windows', PHP_OS_FAMILY . ' ' . php_uname('r'));
diag_line('proc_open', function_exists('proc_open'), function_exists('proc_open') ? 'فعال' : 'غیرفعال — در php.ini از disable_functions حذف شود');
diag_line('shell_exec', function_exists('shell_exec'), function_exists('shell_exec') ? 'فعال' : 'غیرفعال');
$ps = function_exists('domain_scan_powershell_path') ? domain_scan_powershell_path() : 'powershell.exe';
diag_line('PowerShell', $ps !== 'powershell.exe' || PHP_OS_FAMILY === 'Windows', $ps);
diag_line('اسکریپت جمع‌آوری', is_file($root . '/tools/collect_server_inventory.ps1'), 'tools/collect_server_inventory.ps1');
diag_line('اسکریپت SMB', is_file($root . '/tools/collect_via_smb.ps1'), 'tools/collect_via_smb.ps1');
diag_line('تنظیم روش دوم SMB', setting('domain_scan_smb_fallback', '1') !== '0', setting('domain_scan_smb_fallback', '1') !== '0' ? 'روشن' : 'خاموش — در تنظیمات ← اسکن دامنه روشن کنید');
fwrite(STDOUT, "\n");

// ۱) نام و DNS
fwrite(STDOUT, "۱) نام و DNS\n");
$ip = @gethostbyname($host);
$ipOk = $ip !== $host && filter_var($ip, FILTER_VALIDATE_IP) !== false;
diag_line('تبدیل نام به IP', $ipOk, $ipOk ? $ip : 'نام به IP تبدیل نشد — DNS یا فایل hosts مقصد را بررسی کنید');
$rebind = @gethostbyaddr($ipOk ? $ip : $host);
diag_line('نام برگشتی از IP', is_string($rebind) && $rebind !== '', (string) $rebind);
fwrite(STDOUT, "\n");

// ۲-۴) پورت‌ها
fwrite(STDOUT, "۲) پورت‌های مسیرهای مختلف\n");
foreach ([
    445 => 'SMB (روش دوم — کپی عامل و اجرای SYSTEM)',
    135 => 'RPC Endpoint Mapper (لازمهٔ DCOM/WMI از راه دور)',
    5985 => 'WinRM HTTP',
] as $port => $label) {
    [$ok, $ms, $err] = diag_tcp($host, $port);
    diag_line('TCP ' . $port, $ok, $ok ? $ms . ' ms — ' . $label : $label . ' — ' . $err);
}
fwrite(STDOUT, "\n");

// ۵) اتصال واقعی CIM با هر پروتکل
fwrite(STDOUT, "۳) اتصال واقعی WMI/CIM (همان کاری که استخراج می‌کند)\n");
if (PHP_OS_FAMILY !== 'Windows') {
    fwrite(STDOUT, "  ! این بخش فقط روی سرور ویندوز معنی دارد.\n\n");
} else {
    $probe = <<<'PWSH'
param([string]$ComputerName,[string]$User,[string]$Pass)
$out = @()
foreach ($proto in @('Dcom','Wsman')) {
    $row = [ordered]@{ protocol = $proto; ok = $false; error = '' }
    try {
        $opt = New-CimSessionOption -Protocol $proto
        $args = @{ ComputerName = $ComputerName; SessionOption = $opt; OperationTimeoutSec = 20; ErrorAction = 'Stop' }
        if (-not [string]::IsNullOrWhiteSpace($User)) {
            $args.Credential = New-Object System.Management.Automation.PSCredential($User, (ConvertTo-SecureString $Pass -AsPlainText -Force))
        }
        $session = New-CimSession @args
        $null = Get-CimInstance -CimSession $session -ClassName Win32_ComputerSystem -ErrorAction Stop
        $row.ok = $true
        Remove-CimSession -CimSession $session -ErrorAction SilentlyContinue
    } catch {
        $row.error = $_.Exception.Message
    }
    $out += [pscustomobject]$row
}
$out | ConvertTo-Json -Compress
PWSH;
    $scriptFile = sys_get_temp_dir() . '/itsm_diag_' . bin2hex(random_bytes(4)) . '.ps1';
    @file_put_contents($scriptFile, $probe);
    $cmd = [$ps, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $scriptFile, '-ComputerName', $host];
    if ($user !== '') {
        $cmd[] = '-User';
        $cmd[] = $user;
        $cmd[] = '-Pass';
        $cmd[] = $pass;
    }
    $env = null;
    $pipes = [];
    $proc = @proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $env);
    $stdout = '';
    $stderr = '';
    if (is_resource($proc)) {
        fclose($pipes[0]);
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
    }
    @unlink($scriptFile);
    $rows = json_decode(trim($stdout), true);
    if (!is_array($rows)) {
        fwrite(STDOUT, "  ! PowerShell خروجی خوانا نداد: " . substr(trim($stderr . ' ' . $stdout), 0, 300) . "\n");
    } else {
        foreach ($rows as $row) {
            $proto = (string) ($row['protocol'] ?? '?');
            $ok = !empty($row['ok']);
            $err = trim((string) ($row['error'] ?? ''));
            diag_line($proto, $ok, $ok ? 'اتصال موفق — مسیر اول کار می‌کند' : substr($err, 0, 150));
            if (!$ok && $err !== '') {
                if (stripos($err, 'Access is denied') !== false || stripos($err, '0x80070005') !== false) {
                    fwrite(STDOUT, "      ⇒ «Access is denied»: بسته رسیده و احراز هویت شده است. علت، فیلتر شدن توکن مدیریتی است (UAC از راه دور) یا اینکه حساب روی همین مقصد در Administrators نیست. فایروال مقصر نیست.\n");
                } elseif (stripos($err, 'RPC server is unavailable') !== false || stripos($err, '0x800706BA') !== false) {
                    fwrite(STDOUT, "      ⇒ «RPC server is unavailable»: بسته نرسیده است. فایروال مقصد، سرویس WMI/Winmgmt خاموش، یا پورت‌های ۱۳۵/۴۹۱۵۲-۶۵۵۳۵ بسته.\n");
                } elseif (stripos($err, 'cannot connect') !== false || stripos($err, 'WSMan') !== false) {
                    fwrite(STDOUT, "      ⇒ WinRM روی مقصد فعال یا شنونده ندارد (winrm quickconfig).\n");
                }
            }
        }
    }
    fwrite(STDOUT, "\n");
}

// ۶) SMB با اعتبارنامه — اثبات ادمین محلی بودن
fwrite(STDOUT, "۴) SMB با اعتبارنامه (شرط روش دوم و اثبات ادمین محلی بودن)\n");
if ($user === '' || $pass === '') {
    fwrite(STDOUT, "  ! نام کاربری/رمز داده نشد؛ این بخش رد شد. برای آزمون کامل:\n");
    fwrite(STDOUT, "      php tools/diag_extract.php {$host} DOMAIN\\\\user\n\n");
} elseif (PHP_OS_FAMILY !== 'Windows') {
    fwrite(STDOUT, "  ! این بخش فقط روی سرور ویندوز معنی دارد.\n\n");
} else {
    $sandbox = sys_get_temp_dir() . '/itsm_smb_' . bin2hex(random_bytes(3));
    @mkdir($sandbox, 0700, true);
    $resultFile = $sandbox . '/smb_result.json';
    $smbProbe = <<<'PWSH'
param([string]$ComputerName,[string]$User,[string]$Pass,[string]$OutFile)
$result = [ordered]@{ tcp445 = $false; cshare = $false; admin = $false; detail = '' }
$tcp = New-Object System.Net.Sockets.TcpClient
try {
    $iar = $tcp.BeginConnect($ComputerName, 445, $null, $null)
    $result.tcp445 = $iar.AsyncWaitHandle.WaitOne(4000) -and $tcp.Connected
} catch { }
$tcp.Close()
if ($result.tcp445) {
    $cred = New-Object System.Management.Automation.PSCredential($User, (ConvertTo-SecureString $Pass -AsPlainText -Force))
    $drive = $null
    try {
        $drive = New-PSDrive -Name ('DIAG' + (Get-Random -Minimum 1000 -Maximum 9999)) -PSProvider FileSystem -Root "\\$ComputerName\C$" -Credential $cred -ErrorAction Stop
        $result.cshare = $true
        $probeDir = "$($drive.Name):\ITSM"
        try {
            New-Item -ItemType Directory -Path $probeDir -Force -ErrorAction Stop | Out-Null
            $result.admin = $true
        } catch { $result.detail = "C$ opens but C:\ITSM cannot be created (not a local administrator): $($_.Exception.Message)" }
    } catch {
        $result.detail = $_.Exception.Message
    } finally {
        if ($drive) { try { Remove-Item -Path "$($drive.Name):\ITSM\diag.txt" -Force -ErrorAction SilentlyContinue } catch { }; try { Remove-PSDrive -Name $drive.Name -Force -ErrorAction SilentlyContinue } catch { } }
    }
}
$result | ConvertTo-Json -Compress | Out-File -FilePath $OutFile -Encoding utf8
PWSH;
    $probeFile = $sandbox . '/smb_probe.ps1';
    @file_put_contents($probeFile, $smbProbe);
    $cmd = [$ps, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $probeFile, '-ComputerName', $host, '-User', $user, '-Pass', $pass, '-OutFile', $resultFile];
    $pipes = [];
    $proc = @proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (is_resource($proc)) {
        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
        if (trim((string) $err) !== '') {
            fwrite(STDOUT, "  ! " . substr(trim((string) $err), 0, 200) . "\n");
        }
    }
    $smb = is_file($resultFile) ? json_decode((string) @file_get_contents($resultFile), true) : null;
    @unlink($probeFile);
    @unlink($resultFile);
    @rmdir($sandbox);
    if (!is_array($smb)) {
        fwrite(STDOUT, "  ! نتیجهٔ آزمون SMB خوانده نشد.\n");
    } else {
        diag_line('TCP 445', !empty($smb['tcp445']), !empty($smb['tcp445']) ? 'باز' : 'بسته — فایروال مقصد یا سرویس File and Printer Sharing');
        diag_line('بازکردن \\\\' . $host . '\\C$', !empty($smb['cshare']), !empty($smb['cshare']) ? 'موفق' : 'ناموفق — حساب روی این مقصد ادمین محلی نیست یا Remote UAC جلوی دسترسی محلی را گرفته');
        diag_line('ساخت C:\\ITSM', !empty($smb['admin']), !empty($smb['admin']) ? 'موفق — یعنی روش دوم SMB قابل اجراست' : 'ناموفق');
        if (trim((string) ($smb['detail'] ?? '')) !== '') {
            fwrite(STDOUT, "      جزئیات: " . substr(trim((string) $smb['detail']), 0, 300) . "\n");
        }
    }
    fwrite(STDOUT, "\n");
}

// جمع‌بندی
fwrite(STDOUT, "=== جمع‌بندی ===\n");
fwrite(STDOUT, "  • اگر TCP 445 باز است و بخش ۴ موفق: روش دوم (SMB/SYSTEM) باید کار کند و استخراج خودکار در r17 از آن استفاده می‌کند.\n");
fwrite(STDOUT, "  • اگر بخش ۴ ناموفق ولی 445 باز است: حساب روی همان مقصد در گروه Administrators محلی نیست. این شرط TNI هم هست؛ هیچ ابزاری بدون آن موفق نمی‌شود.\n");
fwrite(STDOUT, "  • اگر 135 باز است ولی DCOM «Access is denied» می‌دهد: UAC از راه دور توکن را فیلتر می‌کند. با LocalAccountTokenFilterPolicy=1 روی مقصد رفع می‌شود، یا بدون تغییر سیاست، از روش SMB استفاده کنید.\n\n");
