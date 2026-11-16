# collect_via_smb.ps1 — استخراج شناسنامه با «اجرای محلی روی مقصد» از راه SMB
# ==============================================================================================
# چرا این مسیر لازم است (۱.۶۳.۱-r17):
#
#   مسیر فعلی، WMI/DCOM را از سرور پنل به مقصد می‌برد. خطای رایج آن
#   «Access is denied» (0x80070005) است — نه «RPC server unavailable». تفاوت این
#   دو، تمام تشخیص است: اگر پورت ۱۳۵ بسته بود، پیام «RPC در دسترس نیست» می‌آمد.
#   پس «Access is denied» یعنی بسته به مقصد رسیده و احراز هویت انجام شده، ولی
#   ویندوز توکن مدیر را «فیلتر» کرده است (UAC remote restrictions)، یا حساب روی
#   مقصد در Administrators نیست، یا DCOM/WMI از راه دور مجاز نشده است.
#
#   Total Network Inventory از این مسیر استفاده نمی‌کند. مستندات خودش می‌گوید روش
#   اصلی ویندوز، کپی یک عامل روی ADMIN$ و اجرای آن روی مقصد با سرویس است، و
#   WMI/RPC تنها روش دوم است. Administrator Yar هم همین‌طور. مزیت این روش:
#   مجموعه‌داده روی خودِ مقصد (به‌عنوان SYSTEM) خوانده می‌شود، پس هیچ توکن
#   فیلترشده، هیچ DCOM از راه دور و هیچ سؤال «چه کسی اجازهٔ WMI دارد» باقی
#   نمی‌ماند. تنها چیزی که لازم است: دسترسی ادمین محلی روی مقصد + باز بودن
#   TCP 445 به مقصد (همان شرطی که AdminYar هم دارد).
#
# روش کار — دقیقاً مثل prepare_targets.ps1 که در همین پروژه آزموده شده است:
#   ۱) اتصال SMB به \\<مقصد>\C$ با اعتبارنامهٔ همین نوبت
#   ۲) کپی collect_server_inventory.ps1 در C:\ITSM (هرگز در پوشهٔ مشترک نمی‌ماند)
#   ۳) ساخت و اجرای تسک زمان‌بندی‌شده با /ru SYSTEM  ← کلید کار
#      SYSTEM روی خود مقصد یک توکن کامل دارد، پس WMI محلی همه‌چیز را می‌دهد
#   ۴) خواندن JSON نتیجه از همان مسیر
#   ۵) حذف تسک و فایل‌ها (بی‌اثر و تکراری‌پذیر)
#
# خروجی: JSON روی stdout (همان ساختار collect_server_inventory.ps1) یا خطا با
# پیشوند SMB_* روی stderr و کد خروج ۱.
#
# اجرا: powershell -File collect_via_smb.ps1 -ComputerName PC1
#   اعتبارنامه از ITSM_SCAN_USER / ITSM_SCAN_PASS (متغیر محیطی، نه خط فرمان).
param(
    [string] $ComputerName = '',
    [int] $TimeoutSec = 180
)

$ErrorActionPreference = 'Stop'
$utf8Encoding = New-Object -TypeName System.Text.UTF8Encoding -ArgumentList $false
[Console]::OutputEncoding = $utf8Encoding

function Fail([string] $code, [string] $detail) {
    throw "$code`: $detail"
}

if ([string]::IsNullOrWhiteSpace($ComputerName)) {
    Fail 'SMB_BAD_HOST' 'ComputerName is empty'
}
$user = $env:ITSM_SCAN_USER
$pass = $env:ITSM_SCAN_PASS
if ([string]::IsNullOrWhiteSpace($user) -or [string]::IsNullOrEmpty($pass)) {
    Fail 'SMB_NO_CREDENTIALS' 'ITSM_SCAN_USER / ITSM_SCAN_PASS is not set'
}
$cred = New-Object System.Management.Automation.PSCredential($user, (ConvertTo-SecureString $pass -AsPlainText -Force))

# ۱) کانال SMB (TCP 445). اگر بسته باشد این مسیر هم ممکن نیست و پیام باید صریح باشد،
#    نه «Access is denied» گمراه‌کننده.
$tcp = New-Object System.Net.Sockets.TcpClient
$smbOpen = $false
try {
    $iar = $tcp.BeginConnect($ComputerName, 445, $null, $null)
    $smbOpen = $iar.AsyncWaitHandle.WaitOne(5000) -and $tcp.Connected
} catch {
    $smbOpen = $false
}
$tcp.Close()
if (-not $smbOpen) {
    Fail 'SMB_UNREACHABLE' "TCP 445 on $ComputerName is not reachable"
}

# ۲) اتصال به C$ و کپی عامل. نام درایو تصادفی است تا اجرای هم‌زمان چند مقصد
#    روی یک سرور با هم تصادم نکند.
$driveName = 'ITSM' + (Get-Random -Minimum 1000 -Maximum 9999)
$drive = $null
$taskName = 'ITSM-Collect'
$resultName = 'collect_result_' + [guid]::NewGuid().ToString('N') + '.json'
try {
    try {
        $drive = New-PSDrive -Name $driveName -PSProvider FileSystem -Root "\\$ComputerName\C$" -Credential $cred -ErrorAction Stop
    } catch {
        # تفکیک دو خطای کاملاً متفاوت که پیام ویندوز یکی نشانشان می‌دهد:
        #   • حساب اشتباه / رمز اشتباه / نام دامنه غلط  ⇒ کاربر یا رمز مشکل دارد
        #   • حساب درست ولی ادمین محلی نیست            ⇒ مجوز مشکل دارد
        # پیام خام «Access is denied» برای هر دو یکی است، پس خودمان تفکیک می‌کنیم.
        $raw = $_.Exception.Message
        # خطای SMB با کد 5 یعنی اعتبارنامه/ادمین محلی؛ با کد 53/67 یعنی مسیر.
        # پیام PowerShell خام برای کاربر بی‌معنی است، پس علت را صریح می‌گوییم.
        if ($raw -match 'logon failure|unknown user name|bad password|0x8007052e|1326|1907') {
            Fail 'SMB_BAD_CREDENTIALS' "the supplied account could not log on to $ComputerName — check the domain name and the account: $raw"
        }
        if ($raw -match '0x80070005|access is denied|5') {
            Fail 'SMB_NOT_ADMIN' "logged on to $ComputerName but \\$ComputerName\C$ was refused — the account is not a local administrator there: $raw"
        }
        Fail 'SMB_ACCESS_DENIED' "cannot open \\$ComputerName\C$ with the supplied account: $raw"
    }

    $remoteDir = "$($drive.Name):\ITSM"
    try {
        New-Item -ItemType Directory -Path $remoteDir -Force | Out-Null
    } catch {
        Fail 'SMB_ACCESS_DENIED' "cannot create C:\ITSM on $ComputerName (account is not a local administrator): $($_.Exception.Message)"
    }

    $collector = Join-Path $PSScriptRoot 'collect_server_inventory.ps1'
    if (-not (Test-Path -Path $collector)) {
        Fail 'SMB_COLLECTOR_MISSING' "collect_server_inventory.ps1 was not found next to this script"
    }
    $remoteCollector = Join-Path $remoteDir 'collect_server_inventory.ps1'
    $remoteResult = Join-Path $remoteDir $resultName
    Copy-Item -Path $collector -Destination $remoteCollector -Force

    # ۳) تسک با /ru SYSTEM — همان کلید موفقیت. با حساب کاربری معمولی، WMI محلی
    #    هم ممکن است ناقص بماند؛ SYSTEM روی خود مقصد کامل است و به رمز هم نیاز ندارد.
    $taskCmd = 'powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -File C:\ITSM\collect_server_inventory.ps1 > C:\ITSM\' + $resultName
    $createOut = & schtasks.exe /create /s $ComputerName /u $user /p $pass /tn $taskName /tr $taskCmd /sc once /st 00:00 /ru SYSTEM /rl HIGHEST /f 2>&1
    if ($LASTEXITCODE -ne 0) {
        Fail 'SMB_TASK_FAILED' "schtasks /create failed: $createOut"
    }

    # فایل نتیجهٔ کهنه نباید خوانده شود؛ نامش یکتاست ولی پاک‌سازی صریح ارزان است.
    try { Remove-Item -Path $remoteResult -Force -ErrorAction SilentlyContinue } catch { }

    $runOut = & schtasks.exe /run /s $ComputerName /u $user /p $pass /tn $taskName 2>&1
    if ($LASTEXITCODE -ne 0) {
        Fail 'SMB_TASK_FAILED' "schtasks /run failed (Task Scheduler may be blocked by firewall): $runOut"
    }

    # ۴) انتظار برای خروجی جمع‌آوری. اسکن کامل چند ده ثانیه طول می‌کشد.
    $raw = $null
    $deadline = (Get-Date).AddSeconds($TimeoutSec)
    while ((Get-Date) -lt $deadline) {
        Start-Sleep -Seconds 3
        if (Test-Path -Path $remoteResult) {
            # فایل ممکن است هنوز در حال نوشتن باشد: تا وقتی JSON کامل نشده صبر کن.
            try {
                $candidate = Get-Content -Path $remoteResult -Raw -Encoding UTF8
                if (-not [string]::IsNullOrWhiteSpace($candidate)) {
                    $trimmed = $candidate.Trim()
                    if ($trimmed.StartsWith('{') -and $trimmed.EndsWith('}')) {
                        $raw = $trimmed
                        break
                    }
                }
            } catch {
                # فایل قفل است؛ دور بعد.
            }
        }
    }
    if ($null -eq $raw) {
        Fail 'SMB_COLLECT_TIMEOUT' "collect_server_inventory.ps1 did not write a complete result within $TimeoutSec seconds on $ComputerName"
    }

    # ۵) اعتبارسنجی JSON روی سرور پنل، تا خروجی خراب به‌عنوان «موفق» ذخیره نشود.
    try {
        $parsed = $raw | ConvertFrom-Json
    } catch {
        Fail 'SMB_COLLECT_BAD_JSON' "collector output on $ComputerName is not valid JSON: $($_.Exception.Message)"
    }
    if (-not ($parsed.PSObject.Properties.Name -contains 'computer')) {
        Fail 'SMB_COLLECT_BAD_JSON' "collector output on $ComputerName has no computer section"
    }

    # همان JSON، فشرده و بدون تغییر، به پنل برمی‌گردد.
    $raw
} finally {
    # ۶) پاک‌سازی همیشه انجام می‌شود — حتی وقتی خطا رخ داده است. عامل نباید روی
    #    مقصد بماند: هم اثر امنیتی دارد، هم اجرای بعدی را آلوده می‌کند.
    try { & schtasks.exe /delete /s $ComputerName /u $user /p $pass /tn $taskName /f 2>&1 | Out-Null } catch { }
    if ($drive) {
        foreach ($leftover in @($resultName, 'collect_server_inventory.ps1')) {
            try { Remove-Item -Path (Join-Path "$($drive.Name):\ITSM" $leftover) -Force -ErrorAction SilentlyContinue } catch { }
        }
        try { Remove-PSDrive -Name $drive.Name -Force -ErrorAction SilentlyContinue } catch { }
    }
}
