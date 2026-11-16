# prepare_remote_target.ps1 — روی سیستم مقصد اجرا می‌شود (با تسک زمان‌بندی‌شده، به‌عنوان SYSTEM)
# =============================================================================================
# هدف: فعال‌سازی WinRM و باز کردن قواعد فایروال WMI/WinRM روی مقصد، تا پنل بتواند از راه
# دور شناسنامهٔ سیستم را بخواند. این اسکریپت را پنل (tools/prepare_targets.ps1) به مقصد کپی
# می‌کند و با schtasks اجرا می‌کند؛ کاربر نهایی هرگز آن را مستقیم اجرا نمی‌کند.
# قابل اجرای مجدد است (idempotent) و نتیجه را در C:\ITSM\prepare_result.json می‌نویسد.
$ErrorActionPreference = 'Continue'
# پیام‌ها در ArrayList نگه‌داری می‌شوند؛ ‎+=‎ روی آرایهٔ داخل [ordered]@{} در PowerShell 5.1
# عنصر اضافه نمی‌کند و فقط یک آرایهٔ تازه می‌سازد که بیرون از شیء گم می‌شود.
$messages = New-Object System.Collections.ArrayList
$result = [ordered]@{ success = $false; winrm = $false; wmi = $false; messages = @() }
$dir = 'C:\ITSM'
try { New-Item -ItemType Directory -Path $dir -Force | Out-Null } catch { }

try {
    # WinRM: فعال‌سازی، سرویس خودکار و شروع. -Force برای اجرای غیرتعاملی در تسک لازم است.
    Enable-PSRemoting -Force -SkipNetworkProfileCheck | Out-Null
    Set-Service -Name WinRM -StartupType Automatic
    Start-Service -Name WinRM -ErrorAction SilentlyContinue
    $result.winrm = $true
} catch {
    [void] $messages.Add("WinRM: $($_.Exception.Message)")
}

try {
    # قواعد فایروال WMI/WinRM (وقتی فایروال خاموش است بی‌اثر ولی بی‌خطر است).
    Enable-NetFirewallRule -DisplayGroup 'Windows Management Instrumentation (WMI-In)' -ErrorAction SilentlyContinue
    Enable-NetFirewallRule -DisplayGroup 'Windows Remote Management' -ErrorAction SilentlyContinue
    Enable-NetFirewallRule -DisplayGroup 'Remote Service Management' -ErrorAction SilentlyContinue
    $result.wmi = $true
} catch {
    [void] $messages.Add("Firewall: $($_.Exception.Message)")
}

try {
    # سرویس WMI (Winmgmt) روشن و خودکار باشد تا DCOM هم کار کند.
    Set-Service -Name Winmgmt -StartupType Automatic -ErrorAction SilentlyContinue
    Start-Service -Name Winmgmt -ErrorAction SilentlyContinue
} catch { }

$result.success = [bool]($result.winrm -and $result.wmi)
$result.messages = @($messages.ToArray())
$result | ConvertTo-Json -Depth 4 | Out-File -FilePath (Join-Path $dir 'prepare_result.json') -Encoding utf8

# کد خروج بازتاب موفقیت است تا اجرای تسک از بیرون قابل تشخیص باشد.
# (تسک زمان‌بندی‌شده کد خروج را نگه می‌دارد، اما پنل از روی prepare_result.json هم می‌خواند.)
if ($result.success) { exit 0 } else { exit 1 }
