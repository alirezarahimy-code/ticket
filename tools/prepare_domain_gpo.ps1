# prepare_domain_gpo.ps1 — آماده‌سازی کل دامنه از طریق Group Policy، تمام‌خودکار از داخل پنل
# ===========================================================================================
# روی سرور پنل اجرا می‌شود و هیچ نیازی به نصب RSAT روی پنل یا کار دستی در GPMC ندارد:
#   ۱) یک کنترلر دامنه پیدا می‌کند و با اعتبارنامهٔ واردشده به آن PowerShell Remoting می‌زند
#      (روی DC، ماژول GroupPolicy از پیش موجود است).
#   ۲) GPO می‌سازد/به‌روز می‌کند: فعال‌سازی WinRM، سرویس WinRM، و قواعد فایروال WMI/RPC/WinRM.
#   ۳) GPO را به دامنه لینک می‌کند و با Invoke-GPUpdate همان لحظه روی مقصدها اعمال می‌کند
#      (بدون انتظار برای چرخهٔ ۹۰ دقیقه‌ای).
#
# پارامترها: -Mode Link|Unlink  -TargetHosts "a,b"  -GpoName "..."  -DomainName "corp.local"
# خروجی: JSON روی stdout. خطا: throw با پیشوند PREPARE_*.
param(
    [ValidateSet('Link', 'Unlink')]
    [string] $Mode = 'Link',
    [string] $TargetHosts = '',
    [string] $GpoName = 'ITSM - Remote Inventory (WinRM + WMI)',
    [string] $DomainName = ''
)

$ErrorActionPreference = 'Stop'
$utf8Encoding = New-Object -TypeName System.Text.UTF8Encoding -ArgumentList $false
[Console]::OutputEncoding = $utf8Encoding

function Fail([string] $code, [string] $detail) {
    throw "$code`: $detail"
}

if ([string]::IsNullOrWhiteSpace($GpoName)) {
    $GpoName = 'ITSM - Remote Inventory (WinRM + WMI)'
}

# ۱) اعتبارنامه — همان قرارداد collect_server_inventory.ps1.
$user = $env:ITSM_SCAN_USER
$pass = $env:ITSM_SCAN_PASS
if ([string]::IsNullOrWhiteSpace($user) -or [string]::IsNullOrEmpty($pass)) {
    Fail 'PREPARE_NO_CREDENTIALS' 'ITSM_SCAN_USER / ITSM_SCAN_PASS is not set'
}
$cred = New-Object System.Management.Automation.PSCredential($user, (ConvertTo-SecureString $pass -AsPlainText -Force))

# ۲) شناسایی دامنه و یک کنترلر دامنه در دسترس.
if ([string]::IsNullOrWhiteSpace($DomainName)) {
    try {
        $DomainName = ([System.DirectoryServices.ActiveDirectory.Domain]::GetCurrentDomain()).Name
    } catch {
        Fail 'PREPARE_GPO_NOT_DOMAIN' 'The panel server is not a domain member; Group Policy can only be published from a domain-joined server'
    }
}
$dcHost = $null
try {
    $dcs = @([System.DirectoryServices.ActiveDirectory.Domain]::GetCurrentDomain().DomainControllers)
    foreach ($dc in $dcs) {
        if (Test-Connection -ComputerName $dc.Name -Count 1 -Quiet -ErrorAction SilentlyContinue) {
            $dcHost = $dc.Name
            break
        }
    }
    if (-not $dcHost -and $dcs.Count -gt 0) { $dcHost = $dcs[0].Name }
} catch {
    Fail 'PREPARE_GPO_NOT_DOMAIN' "Cannot enumerate domain controllers: $($_.Exception.Message)"
}
if (-not $dcHost) {
    Fail 'PREPARE_GPO_NOT_DOMAIN' 'No domain controller could be reached from the panel server'
}

# ۳) کل کار روی DC اجرا می‌شود (ماژول GroupPolicy آنجا هست و اعتبارنامه هم اعمال می‌شود).
$remote = {
    param([string] $Mode, [string] $GpoName, [string] $DomainName, [string[]] $TargetHosts)

    $ErrorActionPreference = 'Stop'
    Import-Module GroupPolicy -ErrorAction Stop

    $actions = @()
    $existing = $null
    try {
        $existing = Get-GPO -Name $GpoName -Domain $DomainName -ErrorAction Stop
    } catch {
        $existing = $null
    }

    if ($Mode -eq 'Link') {
        if (-not $existing) {
            $null = New-GPO -Name $GpoName -Domain $DomainName -Comment 'Created by ITSM panel: enables WinRM and WMI/RPC firewall rules on domain computers so the panel can extract inventory.'
            $actions += "GPO '$GpoName' created on $DomainName"
        } else {
            $actions += "GPO '$GpoName' already exists (reused)"
        }

        # ۳-الف) Administrative Templates ← System ← WinRM Service: Allow remote server management through WinRM
        $winrmPolicy = 'HKLM\Software\Policies\Microsoft\Windows\WinRM\Service'
        foreach ($pair in @(
            @('AllowAutoConfig', 1),
            @('IPv4Filter', '*'),
            @('IPv6Filter', '*')
        )) {
            $type = if ($pair[1] -is [int]) { 'DWord' } else { 'String' }
            $null = Set-GPRegistryValue -Name $GpoName -Domain $DomainName -Key $winrmPolicy -ValueName $pair[0] -Type $type -Value $pair[1]
        }

        # ۳-ب) قواعد فایروال دامنه: RPC Endpoint Mapper (135)، محدودهٔ Dynamic RPC، و WinRM 5985/5986.
        #      این قواعد فقط در پروفایل دامنه و فقط با ورودی باز می‌شوند.
        $fwKey = 'HKLM\Software\Policies\Microsoft\WindowsFirewall\DomainProfile\FirewallRules'
        $rules = @{
            'ITSM-RPC-EPM'  = 'v2.30|Action=Allow|Active=TRUE|Dir=In|Protocol=6|LPort=RPC-EPMap|Name=ITSM RPC Endpoint Mapper|'
            'ITSM-RPC-DYN'  = 'v2.30|Action=Allow|Active=TRUE|Dir=In|Protocol=6|LPort=49152-65535|Name=ITSM Dynamic RPC (WMI)|'
            'ITSM-WinRM'    = 'v2.30|Action=Allow|Active=TRUE|Dir=In|Protocol=6|LPort=5985|Name=ITSM WinRM HTTP|'
            'ITSM-WinRM-S'  = 'v2.30|Action=Allow|Active=TRUE|Dir=In|Protocol=6|LPort=5986|Name=ITSM WinRM HTTPS|'
        }
        foreach ($name in $rules.Keys) {
            $null = Set-GPRegistryValue -Name $GpoName -Domain $DomainName -Key $fwKey -ValueName $name -Type String -Value $rules[$name]
        }
        $actions += 'WinRM policy and WMI/RPC/WinRM firewall rules written'

        # ۳-ج) فعال‌سازی سرویس WinRM (GPO از Service Control Manager هم پشتیبانی می‌کند؛
        #      کلید رجیستری سرویس را با مقدار Start=2 می‌نویسیم تا روی همه نسخه‌ها کار کند).
        $null = Set-GPRegistryValue -Name $GpoName -Domain $DomainName -Key 'HKLM\System\CurrentControlSet\Services\WinRM' -ValueName 'Start' -Type DWord -Value 2
        $actions += 'WinRM service set to Automatic'

        # ۳-د) لینک به دامنه (اگر از قبل لینک نبود).
        $linked = $false
        try {
            $gpoId = (Get-GPO -Name $GpoName -Domain $DomainName).Id
            $links = @((Get-GPInheritance -Target $DomainName).GpoLinks | Where-Object { $_.GpoId -eq $gpoId })
            $linked = $links.Count -gt 0
        } catch {
            $linked = $false
        }
        if (-not $linked) {
            $null = New-GPLink -Name $GpoName -Domain $DomainName -Target $DomainName -LinkEnabled Yes -ErrorAction Stop
            $actions += "GPO linked to domain $DomainName"
        } else {
            $actions += "GPO was already linked to domain $DomainName"
        }
    } else {
        $linked = $false
        try {
            $gpoId = (Get-GPO -Name $GpoName -Domain $DomainName).Id
            $links = @((Get-GPInheritance -Target $DomainName).GpoLinks | Where-Object { $_.GpoId -eq $gpoId })
            if ($links.Count -gt 0) {
                $null = Remove-GPLink -Name $GpoName -Domain $DomainName -Target $DomainName -ErrorAction Stop
                $actions += "GPO unlinked from $DomainName (GPO itself kept)"
            } else {
                $actions += 'GPO was not linked; nothing to unlink'
            }
        } catch {
            throw "PREPARE_GPO_LINK_FAILED: $($_.Exception.Message)"
        }
    }

    # ۳-ه) اعمال فوری روی مقصدهای مشخص (بدون انتظار برای چرخهٔ ۹۰ دقیقه‌ای).
    $updated = 0
    $warnings = @()
    if ($Mode -eq 'Link' -and $TargetHosts.Count -gt 0) {
        try {
            $null = Invoke-GPUpdate -Computer $TargetHosts -Force -RandomDelayInMinutes 0 -ErrorAction Stop
            $updated = $TargetHosts.Count
            $actions += "gpupdate forced on $updated host(s)"
        } catch {
            # GPO لینک شده؛ اگر gpupdate نرسید، در چرخهٔ بعدی خودکار اعمال می‌شود.
            $warnings += "GPO linked but gpupdate could not reach targets: $($_.Exception.Message)"
            $actions += 'gpupdate deferred to the next policy refresh (~90 minutes) or reboot'
        }
    }

    [ordered]@{
        linked   = ($Mode -eq 'Link')
        updated  = $updated
        warnings = $warnings
        actions  = $actions
    }
}

$targetList = @()
if (-not [string]::IsNullOrWhiteSpace($TargetHosts)) {
    $targetList = @($TargetHosts -split '[,\s]+' | Where-Object { $_ -ne '' } | Select-Object -Unique)
}

$result = $null
$remoteError = ''
# تلاش ۱: WinRM با Kerberos (پیش‌فرض دامنه).
try {
    $result = Invoke-Command -ComputerName $dcHost -Credential $cred -ArgumentList $Mode, $GpoName, $DomainName, (,$targetList) -ScriptBlock $remote -ErrorAction Stop
} catch {
    $remoteError = $_.Exception.Message
}
# تلاش ۲: اگر Kerberos برقرار نشد، با Negotiate (NTLM) روی همان DC.
if (-not $result -and $remoteError -notmatch 'PREPARE_GPO_LINK_FAILED') {
    try {
        $result = Invoke-Command -ComputerName $dcHost -Credential $cred -Authentication Negotiate -ArgumentList $Mode, $GpoName, $DomainName, (,$targetList) -ScriptBlock $remote -ErrorAction Stop
    } catch {
        $remoteError = $_.Exception.Message
    }
}
# تلاش ۳: اگر RSAT روی خود پنل نصب است، اجرای محلی (بدون Remoting).
if (-not $result -and (Get-Module -ListAvailable -Name GroupPolicy)) {
    try {
        Import-Module GroupPolicy -ErrorAction Stop
        $result = & $remote $Mode $GpoName $DomainName $targetList
    } catch {
        $remoteError = "$remoteError | local GroupPolicy fallback: $($_.Exception.Message)"
    }
}
if (-not $result) {
    if ($remoteError -match 'PREPARE_GPO_LINK_FAILED') {
        Fail 'PREPARE_GPO_LINK_FAILED' $remoteError
    }
    if ($remoteError -match 'PREPARE_GPO_NOT_DOMAIN') {
        Fail 'PREPARE_GPO_NOT_DOMAIN' $remoteError
    }
    Fail 'PREPARE_GPO_FAILED' "Group Policy operation failed (DC $dcHost): $remoteError"
}
# Invoke-Command برای یک هاست یک شیء برمی‌گرداند، اما اگر خروجی چند شیء باشد
# (یا fallback محلی) نتیجه آرایه می‌شود و $result.linked خطا می‌دهد؛ آخرین شیء
# نتیجهٔ واقعی است.
if ($result -is [array]) {
    $result = $result[-1]
}

[ordered]@{
    success        = $true
    mode           = $Mode
    gpo            = $GpoName
    domain         = $DomainName
    dc             = $dcHost
    linked         = [bool]$result.linked
    gpupdate_hosts = [int]$result.updated
    warnings       = @($result.warnings)
    actions        = @($result.actions)
    message        = if ($Mode -eq 'Link') {
        "GPO '$GpoName' published on $DomainName via $dcHost; applied to $($result.updated) host(s) immediately"
    } else {
        "GPO '$GpoName' unlinked from $DomainName via $dcHost"
    }
} | ConvertTo-Json -Depth 4
