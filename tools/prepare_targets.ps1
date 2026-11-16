# prepare_targets.ps1 — آماده‌سازی یک سیستم مقصد برای استخراج شناسنامه (روی سرور پنل اجرا می‌شود)
# =================================================================================================
# روش الف (بدون Group Policy): با اعتبارنامهٔ ادمین دامنه، اسکریپت prepare_remote_target.ps1
# را از طریق SMB به C:\ITSM مقصد کپی می‌کند و با schtasks (از RPC به Task Scheduler، نه از
# WMI) اجرا می‌کند؛ سپس WinRM را روی مقصد فعال و با یک نشست CIM بررسی می‌کند.
# اعتبارنامه از متغیرهای محیطی ITSM_SCAN_USER / ITSM_SCAN_PASS خوانده می‌شود (همان قرارداد
# collect_server_inventory.ps1) تا روی خط فرمان دیده نشود؛ فقط schtasks به‌خاطر ماهیتش رمز را
# روی خط فرمان می‌گیرد (فرایند گذرا روی سرور مورداعتماد پنل).
# خروجی: JSON روی stdout. خطا: throw با پیشوند PREPARE_* روی stderr و کد خروج ۱.
param(
    [string] $ComputerName = ''
)

$ErrorActionPreference = 'Stop'
$utf8Encoding = New-Object -TypeName System.Text.UTF8Encoding -ArgumentList $false
[Console]::OutputEncoding = $utf8Encoding

function Fail([string] $code, [string] $detail) {
    throw "$code`: $detail"
}

if ([string]::IsNullOrWhiteSpace($ComputerName)) {
    Fail 'PREPARE_BAD_HOST' 'ComputerName is empty'
}
$user = $env:ITSM_SCAN_USER
$pass = $env:ITSM_SCAN_PASS
if ([string]::IsNullOrWhiteSpace($user) -or [string]::IsNullOrEmpty($pass)) {
    Fail 'PREPARE_NO_CREDENTIALS' 'ITSM_SCAN_USER / ITSM_SCAN_PASS is not set'
}
$cred = New-Object System.Management.Automation.PSCredential($user, (ConvertTo-SecureString $pass -AsPlainText -Force))

# ۱) بررسی کانال SMB (پورت 445) — اگر بسته باشد، آماده‌سازی راه‌دور ممکن نیست.
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
    Fail 'PREPARE_SMB_UNREACHABLE' "TCP 445 on $ComputerName is not reachable; remote preparation is impossible (use Group Policy or manual setup)"
}

# ۲) درایو موقت روی C$ مقصد با اعتبارنامهٔ دامنه و کپی اسکریپت آماده‌سازی.
#    $drive از ابتدا null است تا اگر New-PSDrive شکست خورد، بلوک finally
#    با «متغیر تعریف‌نشده» پیام خطای اصلی را نپوشاند.
$driveName = 'ITSM' + (Get-Random -Minimum 1000 -Maximum 9999)
$drive = $null
try {
    $drive = New-PSDrive -Name $driveName -PSProvider FileSystem -Root "\\$ComputerName\C$" -Credential $cred -ErrorAction Stop
} catch {
    Fail 'PREPARE_TASK_FAILED' "SMB credential connection to \\$ComputerName\C$ failed (check the account is a local administrator on the target): $($_.Exception.Message)"
}
$taskName = 'ITSM-Prepare'
try {
    $remoteDir = "$($drive.Name):\ITSM"
    New-Item -ItemType Directory -Path $remoteDir -Force | Out-Null
    $localScript = Join-Path $PSScriptRoot 'prepare_remote_target.ps1'
    Copy-Item -Path $localScript -Destination (Join-Path $remoteDir 'prepare_remote_target.ps1') -Force

    # ۳) ساخت تسک زمان‌بندی‌شده روی مقصد (schtasks از RPC به Task Scheduler وصل می‌شود).
    $taskCmd = 'powershell -NoProfile -ExecutionPolicy Bypass -File C:\ITSM\prepare_remote_target.ps1'
    # /ru SYSTEM یعنی تسک مستقل از نشست کاربر و بدون نیاز به ورود دوباره اجرا می‌شود.
    $createOut = & schtasks.exe /create /s $ComputerName /u $user /p $pass /tn $taskName /tr $taskCmd /sc once /st 00:00 /ru SYSTEM /rl HIGHEST /f 2>&1
    if ($LASTEXITCODE -ne 0) {
        Fail 'PREPARE_TASK_FAILED' "schtasks /create failed: $createOut"
    }

    # ۴) اجرای تسک. فایل نتیجهٔ اجرای قبلی قبل از اجرا پاک می‌شود؛ وگرنه ممکن است
    #    نتیجهٔ کهنه خوانده شود و آماده‌سازی ناموفق «موفق» گزارش شود.
    $resultPath = Join-Path $remoteDir 'prepare_result.json'
    try { Remove-Item -Path $resultPath -Force -ErrorAction SilentlyContinue } catch { }
    $runOut = & schtasks.exe /run /s $ComputerName /u $user /p $pass /tn $taskName 2>&1
    if ($LASTEXITCODE -ne 0) {
        Fail 'PREPARE_TASK_FAILED' "schtasks /run failed: $runOut"
    }

    # ۵) انتظار برای فایل نتیجهٔ اسکریپت مقصد (حداکثر ۷۵ ثانیه).
    $result = $null
    for ($i = 0; $i -lt 25; $i++) {
        Start-Sleep -Seconds 3
        if (Test-Path -Path $resultPath) {
            try {
                $result = Get-Content -Path $resultPath -Raw -Encoding UTF8 | ConvertFrom-Json
                break
            } catch { }
        }
    }
    if (-not $result) {
        Fail 'PREPARE_TASK_FAILED' 'prepare_result.json was not created within 75 seconds'
    }
    if (-not $result.success) {
        Fail 'PREPARE_TASK_FAILED' "target script reported failure: $($result.messages -join '; ')"
    }

    # ۶) بررسی نهایی: نشست CIM از طریق WinRM (و در صورت نیاز DCOM).
    $verified = $false
    foreach ($proto in @('Wsman', 'Dcom')) {
        try {
            $session = New-CimSession -ComputerName $ComputerName -Protocol $proto -Credential $cred -ErrorAction Stop
            Remove-CimSession -CimSession $session
            $verified = $true
            break
        } catch { }
    }
    if (-not $verified) {
        Fail 'PREPARE_VERIFY_FAILED' 'CIM session could not be established after preparation'
    }

    [ordered]@{
        success  = $true
        winrm    = [bool]$result.winrm
        wmi      = [bool]$result.wmi
        verified = $true
        message  = "WinRM enabled and verified on $ComputerName"
    } | ConvertTo-Json -Depth 4
} finally {
    # ۷) پاک‌سازی: حذف تسک و فایل‌های موقت روی مقصد (اگر درایو ساخته شده باشد).
    try { & schtasks.exe /delete /s $ComputerName /u $user /p $pass /tn $taskName /f 2>&1 | Out-Null } catch { }
    if ($drive) {
        try { Remove-Item -Path "$($drive.Name):\ITSM\prepare_result.json" -Force -ErrorAction SilentlyContinue } catch { }
        try { Remove-Item -Path "$($drive.Name):\ITSM\prepare_remote_target.ps1" -Force -ErrorAction SilentlyContinue } catch { }
        try { Remove-PSDrive -Name $drive.Name -Force -ErrorAction SilentlyContinue } catch { }
    }
}
