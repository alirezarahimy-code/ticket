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

/*
 * ── چرا bootstrap اجباری نیست ────────────────────────────────────────────
 * این ابزار پیش از هر چیز برای وقتی است که «استخراج کار نمی‌کند». اگر
 * bootstrap را اجباری کنیم، در همان حالتی که MySQL بالا نیست یا config.php
 * خراب است، ابزار با PDOException می‌میرد و کاربر هیچ اطلاعاتی نمی‌گیرد —
 * دقیقاً وقتی که بیشترین نیاز را دارد.
 *
 * پس bootstrap اختیاری است: اگر بالا آمد، از تنظیمات سامانه (نام دامنه،
 * وضعیت روش SMB و مسیر PowerShell) استفاده می‌کنیم؛ اگر نه، با پیش‌فرض‌های
 * امن کار می‌کنیم و صریح می‌گوییم چرا. ابزار تشخیصی باید از خودِ سامانه
 * مقاوم‌تر باشد، نه ضعیف‌تر.
 */
$bootOk = false;
$bootError = '';
$bootFile = $root . '/bootstrap.php';

/*
 * چرا پیش‌بررسی دیتابیس لازم شد — یک باگ واقعی که همین‌جا پیدا شد:
 *
 * lib/logging-csrf.php با set_exception_handler یک هندلر سراسری نصب می‌کند.
 * آن هندلر در CLI پیام «[FATAL] PDOException … | Request-ID: …» می‌دهد و
 * exit(1) می‌کند. نتیجه: اگر bootstrap را داخل try/catch بگذاریم و دیتابیس
 * بالا نباشد، پیام FATAL آن‌ها چاپ می‌شود و catch ما هرگز اجرا نمی‌شود.
 * ابزار تشخیص، خودش می‌مرد و هیچ اطلاعاتی نمی‌داد.
 *
 * پس پیش از لود کردن bootstrap، خودمان اتصال را می‌سنجیم. اگر برقرار نشد،
 * bootstrap اصلاً لود نمی‌شود — چون تنها چیزی که از آن لازم داریم خواندن
 * تنظیمات از دیتابیس است، و همان در دسترس نیست.
 */
$dbProblem = '';
$configFile = $root . '/config.php';
if (is_file($configFile)) {
    try {
        $cfgProbe = @include $configFile;
        if (is_array($cfgProbe)) {
            // ساختار واقعی: $config['database'] = ['host','port','name','user','password']
            // نسخهٔ اول این کد کلیدهای تخت (db_host) را می‌خواند که وجود ندارند،
            // پس شرط سکوت می‌کرد و bootstrap در هر حال لود می‌شد. همان باگ.
            $dbArr = is_array($cfgProbe['database'] ?? null) ? $cfgProbe['database'] : $cfgProbe;
            $dbHost = (string) ($dbArr['host'] ?? 'localhost');
            $dbName = (string) ($dbArr['name'] ?? '');
            $dbUser = (string) ($dbArr['user'] ?? '');
            $dbPass = (string) ($dbArr['password'] ?? '');
            $dbPort = (int) ($dbArr['port'] ?? 3306);
            if ($dbName === '') {
                $dbProblem = 'نام دیتابیس در config.php خوانده نشد';
            } else {
                $probeDsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName . ';charset=utf8mb4';
                new PDO($probeDsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]);
            }
        }
    } catch (Throwable $dbEx) {
        $dbProblem = $dbEx->getMessage();
    }
}

/*
 * این هم یک باگ واقعی: bootstrap.php اگر config.php نباشد، header+exit می‌زند
 * — بی‌هیچ پیامی. یعنی ابزار تشخیص در نصب ناقص، ساکت می‌مرد. الان هر دو شرط
 * را پیش از لود کردن بررسی می‌کنیم.
 */
$configOk = is_file($configFile);
if (!$configOk && $bootError === '') {
    $bootError = 'config.php پیدا نشد — سامانه نصب نشده است (install.php را اجرا کنید)';
}
if (is_file($bootFile) && $dbProblem === '' && $configOk === true) {
    ob_start();
    try {
        require_once $bootFile;
        $bootOk = true;
    } catch (Throwable $e) {
        $bootError = $e->getMessage();
    }
    ob_end_clean();
} elseif ($dbProblem !== '') {
    $bootError = 'دیتابیس پاسخ نداد: ' . $dbProblem;
} else {
    // اگر configOk قبلاً پیام خودش را نگذاشته باشد، این‌جا فقط حالت bootstrap است.
    if ($bootError === '') {
        $bootError = 'bootstrap.php پیدا نشد — این فایل باید در ریشهٔ سامانه باشد';
    }
}

if (!function_exists('setting')) {
    // پیش‌فرض‌های امن وقتی تنظیمات خوانده نمی‌شود: روش SMB روشن و دامنه خالی.
    function setting(string $key, $default = null)
    {
        return $default;
    }
}

$host = trim((string) ($argv[1] ?? ''));
if ($host === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $host)) {
    fwrite(STDERR, "استفاده: php tools/diag_extract.php <نام-کامپیوتر> [DOMAIN\\user]\n");
    exit(2);
}

/**
 * خواندن رمز از کاربر بدون این‌که روی صفحه بماند.
 *
 * ⚠ باگی که همین‌جا بود و در r17-ب اصلاح شد: نسخهٔ اول از shell_exec برای
 * Read-Host استفاده می‌کرد. shell_exec هیچ کنسولی به فرزند نمی‌دهد، پس
 * Read-Host روی ویندوز بلافاصله رشتهٔ خالی برمی‌گرداند — یعنی رمز خالی می‌شد
 * و ابزار به‌اشتباه می‌گفت «اعتبارنامه نادرست». حالا با proc_open و دادن
 * کنسول واقعی (CON) به فرزند، ورودی پنهان درست کار می‌کند.
 */
function diag_read_password(string $prompt): string
{
    // ۱) متغیر محیطی — برای اجرای خودکار/آزمون. آگاهانه و مستند.
    $envPass = getenv('ITSM_DIAG_PASS');
    if (is_string($envPass) && $envPass !== '') {
        fwrite(STDOUT, $prompt . "(از متغیر محیطی ITSM_DIAG_PASS خوانده شد)\n");
        return $envPass;
    }

    fwrite(STDOUT, $prompt);

    // ۲) ویندوز: PowerShell فرزند با کنسول واقعی تا Read-Host پنهان کار کند.
    if (PHP_OS_FAMILY === 'Windows' && function_exists('proc_open')) {
        $code = '$s = Read-Host -AsSecureString; '
            . '[Runtime.InteropServices.Marshal]::PtrToStringAuto('
            . '[Runtime.InteropServices.Marshal]::SecureStringToBSTR($s))';
        $pipes = [];
        $proc = @proc_open(
            ['powershell', '-NoProfile', '-NonInteractive', '-Command', $code],
            [0 => ['file', 'CON', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        if (is_resource($proc)) {
            $out = (string) stream_get_contents($pipes[1]);
            stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);
            $out = str_replace(["\r", "\n"], '', $out);
            if ($out !== '') {
                fwrite(STDOUT, "\n");
                return $out;
            }
        }
    }

    // ۳) هر جای دیگر: خواندن مستقیم از ورودی استاندارد.
    $line = fgets(STDIN);
    fwrite(STDOUT, "\n");
    return is_string($line) ? trim($line) : '';
}

$user = trim((string) ($argv[2] ?? ''));
$pass = '';
if ($user !== '') {
    $pass = diag_read_password('رمز حساب ' . $user . ': ');
    if ($pass === '') {
        fwrite(STDERR, "رمز خالی بود؛ آزمون بخش ۴ (SMB) ممکن نیست. اگر رمز ندارید، همین دستور را بدون نام کاربری اجرا کنید.\n");
    }
}
$GLOBALS['diagColorEnabled'] = $colorEnabled = (PHP_OS_FAMILY === 'Windows')
    ? (getenv('ANSICON') !== false || getenv('WT_SESSION') !== false || getenv('TERM') !== false || getenv('TERM_PROGRAM') !== false)
    : (function_exists('stream_isatty') ? @stream_isatty(STDOUT) : true);
$domain = trim((string) setting('domain_scan_domain', ''));
if ($user !== '' && strpos($user, '\\') === false && strpos($user, '@') === false && $domain !== '') {
    $user = $domain . '\\' . $user;
}

function diag_line(string $label, bool $ok, string $detail = ''): void
{
    // رنگ فقط وقتی که ترمینال واقعاً رنگ می‌فهمد. در cmd.exe قدیمی، کدهای
    // ANSI به‌صورت «←[32m» روی صفحه می‌افتادند و خروجی برای کاربر ناخوانا
    // می‌شد؛ برای همان کسی که قرار است این خروجی را برای ما بفرستد.
    static $color = null;
    if ($color === null) {
        $color = (bool) ($GLOBALS['diagColorEnabled'] ?? false);
    }
    $mark = $ok ? '✓' : '✗';
    if ($color) {
        $mark = $ok ? "\033[32m✓\033[0m" : "\033[31m✗\033[0m";
    }
    // عرض ستون را ثابت می‌گذاریم تا خروجی در cmd.exe هم تراز بماند.
    fwrite(STDOUT, sprintf("  %s %-26s %s\n", $mark, $label, $detail));
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
$smbScriptCandidates = [
    $root . '/tools/collect_via_smb.ps1',
    $root . '/files/tools/collect_via_smb.ps1',   // ساختار بستهٔ به‌روزرسانی، استخراج‌نشده
    dirname($root) . '/files/tools/collect_via_smb.ps1',
];
$smbScript = '';
foreach ($smbScriptCandidates as $cand) {
    if (is_file($cand)) { $smbScript = $cand; break; }
}
diag_line('اسکریپت SMB', $smbScript !== '', $smbScript !== '' ? $smbScript : 'tools/collect_via_smb.ps1 پیدا نشد');
$smbScriptMissing = ($smbScript === '');
if ($smbScript === '') {
    /*
     * این یکی بی‌صدا کل روش دوم را از کار می‌انداخت: domain_scan_remote_inventory_via_smb()
     * پیش از هر کاری is_file() می‌زند و اگر نباشد، همهٔ مقصدها خطا می‌گیرند
     * بدون این‌که کسی بفهمد علت فایل گم‌شده است. کاربر ۷۱ فایل بسته را دیده،
     * ولی این فایل را روی سرور نداشته و پیام خطا هم چیزی از فایل نگفته بود.
     */
    fwrite(STDOUT, "      ⚠ این فایل حیاتی است: بدون آن، «روش دوم SMB» برای هر مقصدی\n");
    fwrite(STDOUT, "        بی‌صدا شکست می‌خورد و علتش هم در پیام خطا نمی‌آید.\n");
    fwrite(STDOUT, "        از بستهٔ به‌روزرسانی بردارید: files\\tools\\collect_via_smb.ps1 → tools\\\n");
}
// اگر مسیر جایگزینی پیدا شد ولی در tools\ نیست، به سامانه بگوییم چه کند
if ($smbScript !== '' && !is_file($root . '/tools/collect_via_smb.ps1')) {
    fwrite(STDOUT, "      ⚠ فایل هست ولی جای درستی نیست — کپی کنید:\n");
    fwrite(STDOUT, "        copy \"" . $smbScript . "\" \"" . $root . "\\tools\\\"\n");
}
diag_line('تنظیم روش دوم SMB', setting('domain_scan_smb_fallback', '1') !== '0', setting('domain_scan_smb_fallback', '1') !== '0' ? 'روشن' : 'خاموش — در تنظیمات ← اسکن دامنه روشن کنید');
if ($bootOk) {
    diag_line('اتصال به سامانه', true, 'bootstrap خوانده شد');
} else {
    diag_line('اتصال به سامانه', false, 'بدون bootstrap — تنظیمات سامانه خوانده نشد');
    fwrite(STDOUT, "      علت: " . substr($bootError, 0, 200) . "\n");
    fwrite(STDOUT, "      ⚠ بقیهٔ آزمون‌ها کار می‌کنند و همین‌ها معمولاً مشکل را نشان می‌دهند.\n");
    fwrite(STDOUT, "        فقط نام دامنه و وضعیت روش SMB از تنظیمات خوانده نمی‌شود.\n");
}
fwrite(STDOUT, "\n");

/*
 * ۰.۵) آیا این سرور اصولاً عضو دامنه است؟
 *
 * چرا این پرسش اول از همه است: اگر سرویس‌دهندهٔ سامانه عضو دامنه نباشد،
 * ماشین حسابش یک حساب محلی است. ویندوز روی هر مقصد، توکن چنین حسابی را در
 * اتصال شبکه‌ای فیلتر می‌کند (UAC remote restrictions) — این رفتار مستند
 * مایکروسافت است و هیچ کدی در سمت پنل آن را دور نمی‌زند. نتیجهٔ عملی:
 * همان حساب همان ابزاری را که TNI یا ادمین‌یار دارند، اینجا نخواهد داشت و
 * خطای «Access is denied» طبیعی است، نه باگ.
 *
 * توجه: ادمین‌یار برای جمع‌آوری از «همهٔ رایانه‌ها» روش دیگری هم دارد و خودش
 * آن را روش *پیشنهادی* می‌نامد: اجرای خودِ نرم‌افزار روی هر کلاینت (از پوشهٔ
 * شبکه‌ای یا فلش) و ذخیرهٔ نتیجه در یک فایل/بانک مشترک. آن روش به هیچ
 * مجوز از راه دوری نیاز ندارد. برای Ticket همان کار با client-agent انجام
 * می‌شود (بخش ۷.۸ راهنما).
 */
fwrite(STDOUT, "۰.۵) عضویت سرور در دامنه (تعیین‌کننده‌ترین عامل)\n");
if (PHP_OS_FAMILY !== 'Windows') {
    fwrite(STDOUT, "  ! این سرور ویندوز نیست. با PHP روی Linux، مسیر WMI/SMB از این میزبان عملاً معنا ندارد؛\n");
    fwrite(STDOUT, "    برای استخراج ۱۵۰ کلاینت از client-agent روی خود کلاینت‌ها استفاده کنید.\n\n");
} else {
    $csFile = sys_get_temp_dir() . '/itsm_ds_' . bin2hex(random_bytes(4)) . '.ps1';
    @file_put_contents($csFile, 'try { $cs = Get-CimInstance Win32_ComputerSystem -ErrorAction Stop; [ordered]@{ domain = [string]$cs.Domain; part = [bool]$cs.PartOfDomain; user = [string]$cs.UserName } | ConvertTo-Json -Compress } catch { "{}" }');
    $pipes = [];
    $proc = @proc_open([$ps, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $csFile], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $csOut = '';
    if (is_resource($proc)) {
        fclose($pipes[0]);
        $csOut = trim((string) stream_get_contents($pipes[1]));
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
    }
    @unlink($csFile);
    $csData = json_decode($csOut, true);
    $partOfDomain = is_array($csData) && !empty($csData['part']);
    $domainName = is_array($csData) ? trim((string) ($csData['domain'] ?? '')) : '';
    diag_line('عضو دامنه', $partOfDomain, $partOfDomain ? $domainName : ('WORKGROUP' . ($domainName !== '' ? ' (' . $domainName . ')' : '')));

    /*
     * عضویت دامنه به‌تنهایی کافی نیست. خروجی واقعی کاربر نشان داد سرور
     * عضو یک دامنه است و DCOM باز هم «Access is denied» می‌دهد، ولی
     * WinRM خطای 0x80090311 («no logon servers») می‌دهد. آن خطا یعنی
     * ویندوز نتوانست بلیت Kerberos بگیرد — پس DC در دسترس نبوده یا نام
     * کاربری که فرستادیم حساب دامنه نبوده.
     *
     * پس همان‌جا این را هم می‌سنجیم: آیا سرور سامانه خودش DC را می‌بیند؟
     */
    if ($partOfDomain && $domainName !== '') {
        $dcFile = sys_get_temp_dir() . '/itsm_dc_' . bin2hex(random_bytes(4)) . '.ps1';
        /*
         * dwa آزمون، چون Win32_NTDomain روی بعضی نصب‌ها لیست خالی می‌دهد:
         *  ۱) خودِ نام DC را از رکوردهای DNS بپرس (همان کاری که nltest می‌کند)
         *  ۲) اگر نشد، از Win32_NTDomain بخوان
         * هیچ‌جا نام دامنه هاردکد نمی‌شود؛ همان دامنه‌ای که بخش ۰.۵ خواند.
         */
        $dcPs = 'try { '
            . '$dc = ""; '
            . 'try { $r = Resolve-DnsName -Name ("_ldap._tcp.dc._msdcs." + $env:ITSM_DIAG_DOMAIN) -Type SRV -ErrorAction Stop; $dc = @($r | Where-Object { $_.NameTarget } | ForEach-Object { $_.NameTarget.TrimEnd(".") } | Select-Object -First 1) } catch { }; '
            . 'if (-not $dc) { try { $n = Get-CimInstance Win32_NTDomain -ErrorAction Stop | Select-Object -First 1; $dc = ([string]$n.DomainControllerName).TrimStart("\\") } catch { } } '
            . 'if ($dc) { [ordered]@{ dc = [string]$dc } | ConvertTo-Json -Compress } else { "{}" } '
            . '} catch { "{}" }';
        @file_put_contents($dcFile, $dcPs);
        $dcPipes = [];
        $dcEnv = @getenv();
        if (!is_array($dcEnv)) { $dcEnv = null; } else { $dcEnv['ITSM_DIAG_DOMAIN'] = $domainName; }
        $dcProc = @proc_open([$ps, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $dcFile], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $dcPipes, null, $dcEnv);
        $dcOut = '';
        if (is_resource($dcProc)) {
            fclose($dcPipes[0]);
            $dcOut = trim((string) stream_get_contents($dcPipes[1]));
            stream_get_contents($dcPipes[2]);
            fclose($dcPipes[1]);
            fclose($dcPipes[2]);
            proc_close($dcProc);
        }
        @unlink($dcFile);
        $dc = json_decode($dcOut, true);
        $dcName = is_array($dc) ? trim((string) ($dc['dc'] ?? '')) : '';
        $dcName = ltrim($dcName, '\\');
        if ($dcName !== '') {
            diag_line('کنترل‌کنندهٔ دامنه', true, $dcName);
        } else {
            diag_line('کنترل‌کنندهٔ دامنه', false, 'پیدا نشد — همین ریشهٔ خطای 0x80090311 است');
            fwrite(STDOUT, "      ⚠ سرور عضو دامنه است ولی نمی‌تواند DC را ببیند. یعنی نمی‌تواند\n");
            fwrite(STDOUT, "        بلیت Kerberos بگیرد — و هر اتصال دامنه‌ای با همین خطا برمی‌گردد.\n");
            fwrite(STDOUT, "        بررسی کنید: DNS سرور سامانه باید به DC اشاره کند، نه به DNS اینترنت.\n");
        }
    }
    if (!$partOfDomain) {
        fwrite(STDOUT, "      ⚠ این سرور عضو دامنه نیست — و این تقریباً همیشه ریشهٔ «Access is denied» است.\n");
        fwrite(STDOUT, "        • ویندوز توکن حساب محلی را در اتصال شبکه‌ای فیلتر می‌کند (UAC remote restrictions).\n");
        fwrite(STDOUT, "          تنها راه‌های دور زدنش: LocalAccountTokenFilterPolicy=1 روی هر مقصد، یا حساب Administrator داخلی.\n");
        fwrite(STDOUT, "        • راه تمیزتر: همین سامانه را روی یک سرور عضو دامنه اجرا کنید و با یک حساب دامنه‌ای\n");
        fwrite(STDOUT, "          که روی کلاینت‌ها در Administrators است استخراج کنید. آن‌گاه روش SMB این بسته کار می‌کند.\n");
        fwrite(STDOUT, "        • راه بدون هیچ مجوز از راه دور (روشی که خودِ ادمین‌یار پیشنهاد می‌کند):\n");
        fwrite(STDOUT, "          client-agent/Collect-Inventory.ps1 روی هر کلاینت اجرا شود و نتیجه را با توکن به\n");
        fwrite(STDOUT, "          api/inventory.php بفرستد. برای ۱۵۰ کلاینت این قابل‌اعتمادترین راه است.\n");
    }
    fwrite(STDOUT, "\n");
}

/*
 * ۰.۶) با کدام هویت اجرا می‌شوم؟ — و تفاوت حیاتی IIS با XAMPP
 *
 * روی IIS، Apache/PHP زیر AppPoolIdentity اجرا می‌شود: حساب ماشین، بدون
 * رمز، و بدون دسترسی شبکه‌ای به جای دیگر — حتی وقتی از داخل cmd با کاربر
 * ادمین اجرایش کنید، هویت PHP همان است.
 *
 * روی XAMPP (که این سامانه برایش نوشته شده) Apache معمولاً به‌عنوان سرویس اجرا می‌شود و PHP
 * را با همان حساب LocalSystem یا حساب ادمینی اجرا می‌کند که Apache با آن
 * بالا آمده. این یعنی خودِ PHP ممکن است بتواند تکمیل کند — اما به شرطی که
 * حساب سرویس Apache همان حسابی باشد که آزمون می‌دهید.
 *
 * مهم‌تر: اگر ابزار را از cmd اجرا کنید، «حساب اجرای PHP» در cmd همان
 * کاربر cmd است. همان چیزی است که سامانه در زمان اجرا دارد فقط اگر Apache
 * هم با همان حساب بالا آمده باشد. تفاوت این دو، منبع بیشتر سردرگمی‌هاست.
 */
fwrite(STDOUT, "۰.۶) هویت اجرای PHP (تفاوت XAMPP و IIS)\n");
$phpUser = getenv('USERNAME') ?: (getenv('USER') ?: '');
$isWindows = PHP_OS_FAMILY === 'Windows';
diag_line('حساب کاربری', $phpUser !== '', $phpUser !== '' ? $phpUser : 'نامشخص');
diag_line('SAPI', true, PHP_SAPI);

// آیا Apache با LocalSystem بالا آمده؟ روی XAMPP پیش‌فرض همین است.
if ($isWindows) {
    $svcFile = sys_get_temp_dir() . '/itsm_svc_' . bin2hex(random_bytes(4)) . '.ps1';
    /*
     * نام سرویس Apache در XAMPP ثابت نیست: روی ویندوز کلاینت معمولاً
     * Apache2.4 است، ولی اگر با XAMPP Control Panel نصب شده باشد Apache2.4
     * می‌ماند و اگر کسی نسخهٔ ۲.۲ نصب کرده باشد Apache2.2. روی ویندوز
     * Server 2016 — که این کاربر دارد — گاهی نام کاملاً دیگری است.
     * پس به‌جای حدس زدن یک نام، هر سرویسی را می‌گیریم که apache/httpd در
     * نام یا مسیرش باشد.
     */
    $svcPs = 'try { '
        . '$all = @(Get-CimInstance Win32_Service -ErrorAction Stop); '
        . '$hit = $all | Where-Object { $_.Name -match "^(Apache|httpd)" -or $_.DisplayName -match "Apache" -or $_.PathName -match "apache" } | Select-Object -First 1; '
        . 'if ($hit) { [ordered]@{ Name = [string]$hit.Name; StartName = [string]$hit.StartName; State = [string]$hit.State } | ConvertTo-Json -Compress } else { "{}" } '
        . '} catch { "{}" }';
    @file_put_contents($svcFile, $svcPs);
    $svcPipes = [];
    $svcProc = @proc_open([$ps, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $svcFile], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $svcPipes);
    $svcOut = '';
    if (is_resource($svcProc)) {
        fclose($svcPipes[0]);
        $svcOut = trim((string) stream_get_contents($svcPipes[1]));
        stream_get_contents($svcPipes[2]);
        fclose($svcPipes[1]);
        fclose($svcPipes[2]);
        proc_close($svcProc);
    }
    @unlink($svcFile);
    $svc = json_decode($svcOut, true);
    if (is_array($svc) && !empty($svc['Name'])) {
        $startName = trim((string) ($svc['StartName'] ?? ''));
        $isLocalSystem = stripos($startName, 'LocalSystem') !== false;
        diag_line('حساب سرویس Apache', true, $startName !== '' ? $startName : 'نامشخص');
        if ($isLocalSystem) {
            fwrite(STDOUT, "      ⚠ Apache با LocalSystem اجرا می‌شود (پیش‌فرض XAMPP).\n");
            fwrite(STDOUT, "        این حساب روی شبکه به‌عنوان ماشین\$ شناخته می‌شود و به کلاینت‌ها دسترسی ادمین ندارد.\n");
            fwrite(STDOUT, "        پس «استخراج از راه دور» از خودِ پنل کار نمی‌کند — حتی اگر شما با ادمین وارد شوید.\n");
            fwrite(STDOUT, "        درست است: همان چیزی که در پیام خطا دیدید.\n");
        } elseif (stripos($startName, 'NetworkService') !== false || stripos($startName, 'LocalService') !== false) {
            fwrite(STDOUT, "      ⚠ Apache با NetworkService/LocalService اجرا می‌شود — بدون دسترسی شبکه‌ای به کلاینت‌ها.\n");
        }
    } elseif ($svcOut === '{}' || $svcOut === '') {
        /*
         * شاید Apache به‌عنوان سرویس نصب نشده باشد — XAMPP اجازه می‌دهد
         * Apache را فقط از Control Panel اجرا کنید، و در آن حالت هیچ سرویسی
         * وجود ندارد. آن‌وقت هویت Apache همان هویت کاربری است که Control
         * Panel را باز کرده، نه LocalSystem. این تفاوت مهم است و باید
         * صریح گفته شود.
         */
        diag_line('سرویس Apache', false, 'سرویس ویندوزی برای Apache پیدا نشد');
        // لیست واقعی سرویس‌ها را خودمان نشان می‌دهیم تا کاربر دستور تایپ نکند.
        $listFile = sys_get_temp_dir() . '/itsm_svclist_' . bin2hex(random_bytes(4)) . '.ps1';
        @file_put_contents($listFile, 'try { Get-CimInstance Win32_Service -ErrorAction Stop | Where-Object { $_.PathName -match "xampp|apache" } | ForEach-Object { $_.Name + "  |  " + $_.StartName + "  |  " + $_.State } } catch { }');
        $listPipes = [];
        $listProc = @proc_open([$ps, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $listFile], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $listPipes);
        $listOut = '';
        if (is_resource($listProc)) {
            fclose($listPipes[0]);
            $listOut = trim((string) stream_get_contents($listPipes[1]));
            stream_get_contents($listPipes[2]);
            fclose($listPipes[1]);
            fclose($listPipes[2]);
            proc_close($listProc);
        }
        @unlink($listFile);
        if ($listOut !== '') {
            fwrite(STDOUT, "      سرویس‌های مرتبط با xampp/apache روی این سیستم:\n");
            foreach (array_slice(preg_split('/\R/', $listOut), 0, 6) as $svcLine) {
                if (trim($svcLine) !== '') { fwrite(STDOUT, "        " . trim($svcLine) . "\n"); }
            }
        } else {
            fwrite(STDOUT, "      Apache احتمالاً به‌عنوان سرویس نصب نشده است (XAMPP Control Panel آن را\n");
            fwrite(STDOUT, "      مستقیم اجرا می‌کند). در این حالت هویت Apache همان کاربری است که\n");
            fwrite(STDOUT, "      Control Panel را باز کرده — و برای استخراج از راه دور کافی نیست.\n");
        }
        fwrite(STDOUT, "      اگر Apache از Control Panel اجرا می‌شود، برای دیدن هویت واقعی‌اش:\n");
        fwrite(STDOUT, "        Task Manager → Details → ستون User Name را اضافه کنید → httpd.exe\n");
    }
}
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
    /*
     * چهار ترکیب واقعی آزموده می‌شود، نه یکی.
     *
     * خروجی واقعی کاربر این را نشان داد: با نام کاربری خالی، DCOM
     * «Access is denied» داد و من آن را به «توکن فیلتر شده» تعبیر کردم.
     * ولی آزمون نامعتبر بود — رمز خالی بود و هیچ اعتبارنامه‌ای فرستاده
     * نشد. با حساب ادمین دامنه‌ای که خودِ کاربر می‌گوید درست است، همین
     * اتصال باید موفق شود.
     *
     * پس:
     *   بومی    — بدون اعتبارنامه، با هویت خودِ سرور (همان کاری که پنل می‌کند)
     *   صریح    — با اعتبارنامه‌ای که کاربر داده (همان کاری که باید بکند)
     * و هر دو، یک‌بار Dcom و یک‌بار Wsman.
     *
     * رمز از محیط فرآیند می‌رود، نه از خط فرمان: خط فرمان هر فرآیند در
     * Task Manager و WMI خواناست.
     */
    $probe = <<<'PWSH'
param([string]$ComputerName)
$user = $env:ITSM_DIAG_USER
$pass = $env:ITSM_DIAG_PASS_INNER
$out = @()
$hasCred = -not [string]::IsNullOrWhiteSpace($user)
foreach ($mode in @('explicit','implicit')) {
    if ($mode -eq 'explicit' -and -not $hasCred) { continue }
    foreach ($proto in @('Dcom','Wsman')) {
        $row = [ordered]@{ mode = $mode; protocol = $proto; ok = $false; error = '' }
        try {
            $opt = New-CimSessionOption -Protocol $proto
            $args = @{ ComputerName = $ComputerName; SessionOption = $opt; OperationTimeoutSec = 20; ErrorAction = 'Stop' }
            if ($mode -eq 'explicit') {
                $args.Credential = New-Object System.Management.Automation.PSCredential($user, (ConvertTo-SecureString $pass -AsPlainText -Force))
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
}
$out | ConvertTo-Json -Compress -Depth 3
PWSH;
    $scriptFile = sys_get_temp_dir() . '/itsm_diag_' . bin2hex(random_bytes(4)) . '.ps1';
    @file_put_contents($scriptFile, $probe);
    $cmd = [$ps, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $scriptFile, '-ComputerName', $host];
    $cimEnv = @getenv();
    if (!is_array($cimEnv)) { $cimEnv = null; } else {
        $cimEnv['ITSM_DIAG_USER'] = $user;
        $cimEnv['ITSM_DIAG_PASS_INNER'] = $pass;
    }
    $pipes = [];
    $proc = @proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $cimEnv);
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
        if (!is_array($rows) || (isset($rows['protocol']))) { $rows = [$rows]; }
        $anyExplicitOk = false;
        foreach ($rows as $row) {
            $proto = (string) ($row['protocol'] ?? '?');
            $mode = (string) ($row['mode'] ?? 'implicit');
            $ok = !empty($row['ok']);
            $err = trim((string) ($row['error'] ?? ''));
            if ($ok && $mode === 'explicit') { $anyExplicitOk = true; }
            $label = ($mode === 'explicit' ? 'با اعتبارنامه — ' : 'با هویت سرور — ') . $proto;
            diag_line($label, $ok, $ok ? 'موفق' : substr($err, 0, 130));
            if (!$ok && $err !== '') {
                if (stripos($err, 'Access is denied') !== false || stripos($err, '0x80070005') !== false) {
                    if ($mode === 'implicit') {
                        fwrite(STDOUT, "      ⇒ این آزمون با هویت خودِ سرور بود، نه با حساب شما. «Access is denied»\n");
                        fwrite(STDOUT, "        در این حالت طبیعی است و به استخراج پنل ربطی ندارد. خط پایین مهم است.\n");
                    } else {
                        fwrite(STDOUT, "      ⇒ با حساب خودتان هم رد شد. دو علت ممکن: حساب روی همین مقصد در\n");
                        fwrite(STDOUT, "        Administrators محلی نیست، یا DCOM از راه دور برایش مجاز نشده\n");
                        fwrite(STDOUT, "        (Remote Enable روی namespace). فایروال مقصر نیست.\n");
                    }
                } elseif (stripos($err, 'RPC server is unavailable') !== false || stripos($err, '0x800706BA') !== false) {
                    fwrite(STDOUT, "      ⇒ «RPC server is unavailable»: بسته نرسیده است. فایروال مقصد، سرویس WMI/Winmgmt، یا پورت‌های ۱۳۵/۴۹۱۵۲-۶۵۵۳۵.\n");
                } elseif (stripos($err, 'cannot connect') !== false || stripos($err, 'WSMan') !== false) {
                    if (stripos($err, '0x80090311') !== false) {
                        fwrite(STDOUT, "      ⇒ «0x80090311 / no logon servers»: این کامپیوتر نتوانست بلیت Kerberos بگیرد.\n");
                        fwrite(STDOUT, "        یعنی نام دامنه‌ای که فرستادیم با دامنهٔ واقعی نمی‌خواند. با\n");
                        fwrite(STDOUT, "        «دامنهٔ‌\‌حساب» درست اجرا کنید. (بخش ۰.۵ نام واقعی دامنه را چاپ کرد.)\n");
                    } else {
                        fwrite(STDOUT, "      ⇒ WinRM روی مقصد فعال یا شنونده ندارد (winrm quickconfig). مهم نیست اگر Dcom کار می‌کند.\n");
                    }
                }
            }
        }
        if ($user === '') {
            fwrite(STDOUT, "  ! نام کاربری نداده‌اید؛ فقط آزمون «هویت سرور» اجرا شد.\n");
            fwrite(STDOUT, "    برای آزمون واقعی با حساب ادمین دامنه:\n");
            fwrite(STDOUT, "      php tools/diag_extract.php {$host} DOMAIN\\\\user\n\n");
        } elseif (!$anyExplicitOk) {
            fwrite(STDOUT, "  ! هیچ‌کدام از دو پروتکل با حساب شما موفق نشد — پس مسیر WMI از این سرور\n");
            fwrite(STDOUT, "    به این مقصد بسته است. ولی روش SMB (بخش ۴) ممکن است کار کند.\n\n");
        } else {
            fwrite(STDOUT, "  ✓ با حساب شما دست‌کم یک پروتکل موفق شد — مسیر اول از این سرور کار می‌کند.\n\n");
        }
    }
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
param([string]$ComputerName,[string]$User,[string]$OutFile)
# رمز از محیط فرآیند خوانده می‌شود، نه از خط فرمان. خط فرمان یک فرآیند در
# Task Manager و WMI و Sysmon برای هر کاربری قابل خواندن است؛ محتوا‌ی محیط
# فقط برای همان فرآیند و والدش.
$Pass = $env:ITSM_DIAG_SMB_PASS
Remove-Item Env:\ITSM_DIAG_SMB_PASS -ErrorAction SilentlyContinue
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
        if ($drive) {
            # پوشهٔ آزمون روی مقصد پاک می‌شود؛ این ابزار باید هیچ ردپایی نگذارد.
            try { Remove-Item -Path "$($drive.Name):\ITSM" -Recurse -Force -ErrorAction SilentlyContinue } catch { }
            try { Remove-PSDrive -Name $drive.Name -Force -ErrorAction SilentlyContinue } catch { }
        }
    }
}
$result | ConvertTo-Json -Compress | Out-File -FilePath $OutFile -Encoding utf8
PWSH;
    $probeFile = $sandbox . '/smb_probe.ps1';
    @file_put_contents($probeFile, $smbProbe);
    $cmd = [$ps, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $probeFile, '-ComputerName', $host, '-User', $user, '-OutFile', $resultFile];
    // رمز از محیط فرزند می‌رود، نه از خط فرمان.
    $childEnv = null;
    if (function_exists('getenv')) {
        $all = @getenv();
        if (is_array($all)) {
            $childEnv = $all;
            $childEnv['ITSM_DIAG_SMB_PASS'] = $pass;
        }
    }
    $pipes = [];
    $proc = @proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $childEnv);
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
        /*
         * «نتیجه خوانده نشد» پیام بی‌مصرفی است: کاربر نمی‌داند PowerShel
         * اجرا شد یا نه، رمز پذیرفته شد یا نه. سه حالت ممکن را جدا می‌کنیم.
         */
        fwrite(STDOUT, "  ! نتیجهٔ آزمون SMB خوانده نشد — دلیلش را بررسی می‌کنیم:\n");
        if ($smbScriptMissing) {
            fwrite(STDOUT, "      ✗ tools\\collect_via_smb.ps1 روی سرور نیست. (این بخش به آن وابسته نیست،\n");
            fwrite(STDOUT, "        ولی استخراج واقعی هست — بالاتر توضیح داده شد.)\n");
        }
        if (!is_file($probeFile)) {
            fwrite(STDOUT, "      ✗ فایل آزمون موقت ساخته نشد — پوشهٔ temp قابل نوشتن نیست.\n");
            fwrite(STDOUT, "        مسیر: " . sys_get_temp_dir() . "\n");
        } elseif (!is_file($resultFile)) {
            fwrite(STDOUT, "      ✗ PowerShell خروجی را ننوشت. سه علت رایج:\n");
            fwrite(STDOUT, "        • سیاست اجرای گروه (ExecutionPolicy) در سطح ماشین مسدود است\n");
            fwrite(STDOUT, "        • نام کاربری به شکل درست نبود — این تست «" . $user . "» را فرستاد\n");
            fwrite(STDOUT, "        • کنترل حساب کاربری (UAC) اجرای این اسکریپت را بست\n");
            fwrite(STDOUT, "        آزمون دستی همین کار را بکنید و پیام را ببینید:\n");
            fwrite(STDOUT, "          powershell -NoProfile -Command \"Test-Path \\\\" . $host . "\\C$\"\n");
        } else {
            fwrite(STDOUT, "      ✗ فایل نتیجه ساخته شد ولی JSON معتبر نبود (خروجی ناقص).\n");
        }
    } else {
        // اعتبارنامه را جدا می‌سنجیم، پیش از هر چیز: خطای «رمز/دامنه غلط» و
        // «ادمین محلی نیست» در ویندوز پیام یکسان «Access is denied» دارند،
        // ولی درمان‌شان کاملاً متفاوت است.
        if (!empty($smb['tcp445'])) {
            diag_line('TCP 445', true, 'باز');
        } else {
            diag_line('TCP 445', false, 'بسته — فایروال مقصد یا سرویس File and Printer Sharing');
        }
        $cshareOk = !empty($smb['cshare']);
        $cshareDetail = $smb['detail'] ?? '';
        if ($cshareOk) {
            diag_line('بازکردن \\\\' . $host . '\\C$', true, 'موفق — حساب روی این مقصد ادمین محلی است');
        } elseif (preg_match('/logon failure|unknown user|bad password|0x8007052e|1326|1907/i', (string) $cshareDetail)) {
            diag_line('بازکردن \\\\' . $host . '\\C$', false, 'ورود با این حساب ممکن نشد — نام کاربری، رمز یا نام دامنه نادرست است');
            fwrite(STDOUT, "      ⇒ این خطای «مجوز» نیست، خطای «هویت» است. نام دامنه را با بخش ۰.۵ بسنجید.\n");
        } else {
            diag_line('بازکردن \\\\' . $host . '\\C$', false, 'ورود انجام شد ولی C$ رد شد — حساب ادمین محلی نیست');
        }
        diag_line('ساخت C:\\ITSM', !empty($smb['admin']), !empty($smb['admin']) ? 'موفق — یعنی روش دوم SMB قابل اجراست' : 'ناموفق');
        if (trim((string) ($smb['detail'] ?? '')) !== '') {
            fwrite(STDOUT, "      جزئیات: " . substr(trim((string) $smb['detail']), 0, 300) . "\n");
        }
    }
    fwrite(STDOUT, "\n");
}

/*
 * ۵) وقتی همهٔ پیش‌نیازها درست است و باز هم کار نمی‌کند
 *
 * این بخش برای حالتی نوشته شد که کاربر می‌گوید: سرور عضو دامنه است،
 * حساب ادمین دامنه است، رمز درست است، فایروال‌ها خاموش‌اند. در آن حالت
 * هر پیامی که «پیش‌نیاز را بررسی کنید» بگوید بی‌فایده است. پس اینجا فقط
 * چیزهایی سنجیده می‌شود که با وجود همهٔ آن‌ها باز هم می‌توانند شکسته
 * باشند و در خطا دیده نمی‌شوند.
 */
if (PHP_OS_FAMILY === 'Windows') {
    fwrite(STDOUT, "۵) تحلیل «همه‌چیز درست است، پس چرا؟»\n");
    $deepPs = <<<'PWSH'
$out = [ordered]@{}
$out.latfp = -1
try {
    $v = (Get-ItemProperty -Path 'HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Policies\System' -Name 'LocalAccountTokenFilterPolicy' -ErrorAction Stop).LocalAccountTokenFilterPolicy
    $out.latfp = [int]$v
} catch { $out.latfp = -1 }
$out.wmiService = ''
try {
    $svc = Get-CimInstance Win32_Service -Filter "Name='Winmgmt'" -ErrorAction Stop
    $out.wmiService = [string]$svc.State
} catch { }
$out.identity = [string]([Security.Principal.WindowsIdentity]::GetCurrent().Name)
$out | ConvertTo-Json -Compress
PWSH;
    $deepFile = sys_get_temp_dir() . '/itsm_deep_' . bin2hex(random_bytes(4)) . '.ps1';
    @file_put_contents($deepFile, $deepPs);
    $deepPipes = [];
    $deepProc = @proc_open([$ps, '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass', '-File', $deepFile], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $deepPipes);
    $deepOut = '';
    if (is_resource($deepProc)) {
        fclose($deepPipes[0]);
        $deepOut = trim((string) stream_get_contents($deepPipes[1]));
        stream_get_contents($deepPipes[2]);
        fclose($deepPipes[1]);
        fclose($deepPipes[2]);
        proc_close($deepProc);
    }
    @unlink($deepFile);
    $deep = json_decode($deepOut, true);
    if (is_array($deep)) {
        $latfp = (int) ($deep['latfp'] ?? -1);
        if ($latfp === 1) {
            diag_line('LocalAccountTokenFilterPolicy', true, 'روشن (=1) — حساب محلی هم مدیر کامل می‌ماند');
        } elseif ($latfp === 0) {
            diag_line('LocalAccountTokenFilterPolicy', true, 'صفر — فقط حساب محلی را فیلتر می‌کند، حساب دامنه‌ای را نه');
        } else {
            diag_line('LocalAccountTokenFilterPolicy', true, 'تعریف نشده (پیش‌فرض) — برای حساب دامنه‌ای بی‌اثر است');
        }
        $wmiSvc = trim((string) ($deep['wmiService'] ?? ''));
        if ($wmiSvc !== '') {
            diag_line('سرویس Winmgmt روی سرور', strtolower($wmiSvc) === 'running', $wmiSvc);
        }
        $ident = trim((string) ($deep['identity'] ?? ''));
        if ($ident !== '') {
            $isLocalAcct = (stripos($ident, '\\') === false);
            diag_line('هویت واقعی این پروسه', true, $ident . ($isLocalAcct ? '  (حساب محلی)' : ''));
            if ($isLocalAcct && $user === '') {
                fwrite(STDOUT, "      ⚠ این پروسه با حساب محلی اجرا می‌شود، پس آزمون بخش ۳ به نام شما نبود.\n");
                fwrite(STDOUT, "        با «دامنه\\\\حساب» اجرا کنید تا آزمون واقعی باشد.\n");
            }
        }
    } else {
        fwrite(STDOUT, "  ! تحلیل عمیق اجرا نشد.\n");
    }
    fwrite(STDOUT, "\n");
}

/*
 * جمع‌بندی — از نتیجهٔ واقعی ساخته می‌شود، نه از سه بند ثابت.
 *
 * نسخهٔ قبلی همین‌جا به کاربر می‌گفت «حساب ادمین محلی نیست» و او در پاسخ
 * می‌گفت حسابش ادمین دامنه است و رمزش هم درست است. حق با او بود: بندها
 * ثابت بودند و به آنچه آزمون واقعاً دیده بود کاری نداشتند.
 */
fwrite(STDOUT, "=== جمع‌بندی ===\n");
$verdict = [];
if (PHP_OS_FAMILY !== 'Windows') {
    $verdict[] = 'این سرور ویندوز نیست، پس مسیر WMI/SMB از آن معنا ندارد.';
} else {
    if (!$partOfDomain) {
        $verdict[] = 'سرور عضو دامنه نیست — با حساب محلی، استخراج از راه دور کار نمی‌کند.';
    }
    if ($smbScriptMissing) {
        $verdict[] = 'فایل tools\\collect_via_smb.ps1 روی سرور نیست؛ تا وقتی نباشد روش SMB برای هیچ مقصدی اجرا نمی‌شود. این را اول درست کنید.';
    }
    if ($user === '') {
        $verdict[] = 'نام کاربری و رمز داده نشد، پس بخش‌های ۳ و ۴ آزمون واقعی نبودند. با «دامنه\\حساب» دوباره اجرا کنید.';
    }
    $verdict[] = 'یافته‌های بالا را به ترتیب ببینید: اول بخش ۰ (فایل‌ها)، بعد ۳ (اعتبارنامه)، بعد ۴ (ادمین محلی).';
}
foreach ($verdict as $line) {
    fwrite(STDOUT, '  • ' . $line . "\n");
}
fwrite(STDOUT, "\n");
