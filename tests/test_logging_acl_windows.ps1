[CmdletBinding()]
param()
$ErrorActionPreference = 'Stop'
$root = Join-Path ([System.IO.Path]::GetTempPath()) ('itsm-logging-acl-' + [guid]::NewGuid().ToString('N'))
$logs = Join-Path $root 'storage\logs'
$verifier = Join-Path $PSScriptRoot '..\tools\verify_logging_acl.ps1'
$setter = Join-Path $PSScriptRoot '..\tools\set_logging_acl.ps1'
New-Item -ItemType Directory -Path $logs -Force | Out-Null
try {
    # Build a deterministic, isolated ACL without inherited broad principals.
    $acl = Get-Acl -LiteralPath $logs
    $acl.SetAccessRuleProtection($true, $false)
    foreach ($existing in @($acl.Access)) { [void]$acl.RemoveAccessRuleSpecific($existing) }
    $currentSid = [System.Security.Principal.WindowsIdentity]::GetCurrent().User
    $adminSid = [System.Security.Principal.SecurityIdentifier]::new('S-1-5-32-544')
    $systemSid = [System.Security.Principal.SecurityIdentifier]::new('S-1-5-18')
    $inherit = [System.Security.AccessControl.InheritanceFlags]::ContainerInherit -bor [System.Security.AccessControl.InheritanceFlags]::ObjectInherit
    foreach ($sid in @($currentSid, $adminSid, $systemSid)) {
        $rule = [System.Security.AccessControl.FileSystemAccessRule]::new($sid, [System.Security.AccessControl.FileSystemRights]::FullControl, $inherit, [System.Security.AccessControl.PropagationFlags]::None, [System.Security.AccessControl.AccessControlType]::Allow)
        $acl.AddAccessRule($rule)
    }
    Set-Acl -LiteralPath $logs -AclObject $acl
    New-Item -ItemType Directory -Path (Join-Path $logs 'spool') -Force | Out-Null

    # Baseline: strict verifier must accept the isolated ACL.
    $powershell = Join-Path $PSHOME 'powershell.exe'
    & $powershell -NoProfile -ExecutionPolicy Bypass -File $verifier -ApplicationRoot $root -Strict -ScanFiles | Out-Host
    if ($LASTEXITCODE -ne 0) { throw "ACL verifier rejected the controlled baseline (exit $LASTEXITCODE)." }

    # Negative control: put Everyone:Modify only on a nested spool directory. Recursive scan must detect it.
    $spool = Join-Path $logs 'spool'
    $acl = Get-Acl -LiteralPath $spool
    $everyone = [System.Security.Principal.SecurityIdentifier]::new('S-1-1-0')
    $broad = [System.Security.AccessControl.FileSystemAccessRule]::new($everyone, [System.Security.AccessControl.FileSystemRights]::Modify, $inherit, [System.Security.AccessControl.PropagationFlags]::None, [System.Security.AccessControl.AccessControlType]::Allow)
    $acl.AddAccessRule($broad)
    Set-Acl -LiteralPath $spool -AclObject $acl
    $powershell = Join-Path $PSHOME 'powershell.exe'
    & $powershell -NoProfile -ExecutionPolicy Bypass -File $verifier -ApplicationRoot $root -Strict -ScanFiles | Out-Host
    if ($LASTEXITCODE -ne 2) { throw "Recursive ACL verifier did not reject nested Everyone:Modify; expected exit 2, got $LASTEXITCODE." }

    # ACL Apply must refuse to layer service rules over an existing broad ACE and leave it untouched.
    $applyRejected = $false
    try { & $setter -ApplicationRoot $root -ServiceAccount $currentSid.Value -Apply -WhatIf; }
    catch { $applyRejected = $_.Exception.Message -like '*Broad allow ACEs were detected*' }
    if (-not $applyRejected) { throw 'Set ACL did not refuse an existing broad allow ACE.' }
    $afterApply = Get-Acl -LiteralPath $spool
    if (-not @($afterApply.Access | Where-Object { $_.IdentityReference.Translate([System.Security.Principal.SecurityIdentifier]).Value -eq 'S-1-1-0' }).Count) { throw 'Set ACL changed or removed the broad ACE unexpectedly.' }

    # Explicit per-file ACEs must also be detected when ScanFiles is enabled.
    $probeFile = Join-Path $spool 'acl-probe.log'
    Set-Content -LiteralPath $probeFile -Value 'probe'
    $fileAcl = Get-Acl -LiteralPath $probeFile
    $fileBroad = [System.Security.AccessControl.FileSystemAccessRule]::new($everyone, [System.Security.AccessControl.FileSystemRights]::Modify, [System.Security.AccessControl.AccessControlType]::Allow)
    $fileAcl.AddAccessRule($fileBroad)
    Set-Acl -LiteralPath $probeFile -AclObject $fileAcl
    & $powershell -NoProfile -ExecutionPolicy Bypass -File $verifier -ApplicationRoot $root -Strict -ScanFiles | Out-Host
    if ($LASTEXITCODE -ne 2) { throw 'ACL verifier did not reject explicit per-file Everyone:Modify.' }
    $fileAcl = Get-Acl -LiteralPath $probeFile
    [void]$fileAcl.RemoveAccessRuleSpecific($fileBroad)
    Set-Acl -LiteralPath $probeFile -AclObject $fileAcl

    # Restore clean ACL and verify it passes again.
    $acl = Get-Acl -LiteralPath $spool
    [void]$acl.RemoveAccessRuleSpecific($broad)
    Set-Acl -LiteralPath $spool -AclObject $acl
    $powershell = Join-Path $PSHOME 'powershell.exe'
    & $powershell -NoProfile -ExecutionPolicy Bypass -File $verifier -ApplicationRoot $root -Strict -ScanFiles | Out-Host
    if ($LASTEXITCODE -ne 0) { throw "ACL verifier did not accept the restored ACL (exit $LASTEXITCODE)." }

    # Positive control: apply the restricted ACL on a clean tree and verify least privilege.
    & $setter -ApplicationRoot $root -ServiceAccount 'S-1-5-20' -Apply
    $appliedAcl = Get-Acl -LiteralPath $logs
    $serviceAce = @($appliedAcl.Access | Where-Object {
        $_.IdentityReference.Translate([System.Security.Principal.SecurityIdentifier]).Value -eq 'S-1-5-20'
    })
    if (-not $serviceAce.Count) { throw 'Set ACL did not add the service account ACE.' }
    $modify = [System.Security.AccessControl.FileSystemRights]::Modify
    $full = [System.Security.AccessControl.FileSystemRights]::FullControl
    if (($serviceAce[0].FileSystemRights -band $modify) -ne $modify) { throw 'Service account ACE does not include Modify.' }
    if (($serviceAce[0].FileSystemRights -band $full) -eq $full) { throw 'Service account was granted FullControl instead of Modify.' }
    & $powershell -NoProfile -ExecutionPolicy Bypass -File $verifier -ApplicationRoot $root -Strict -ScanFiles | Out-Host
    if ($LASTEXITCODE -ne 0) { throw "ACL verifier rejected the applied restricted ACL (exit $LASTEXITCODE)." }

    Write-Output 'PASS: Windows ACL verifier baseline, broad-ACE rejection, and restoration checks.'
}
finally {
    Remove-Item -LiteralPath $root -Recurse -Force -ErrorAction SilentlyContinue
}
