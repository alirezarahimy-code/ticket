[CmdletBinding(SupportsShouldProcess=$true, ConfirmImpact='High')]
param(
    [Parameter(Mandatory=$true)][string]$ApplicationRoot,
    [Parameter(Mandatory=$true)][string]$ServiceAccount,
    [switch]$Apply
)
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path -LiteralPath $ApplicationRoot).Path
$target = Join-Path $root 'storage\logs'
if (-not (Test-Path -LiteralPath $target -PathType Container)) { throw "Logging directory is missing: $target" }
$broadSids = @('S-1-1-0','S-1-5-11','S-1-5-32-545','S-1-5-32-546','S-1-5-4','S-1-5-2','S-1-5-14','S-1-5-113')
foreach ($item in @((Get-Item -LiteralPath $target)) + @(Get-ChildItem -LiteralPath $target -Force -Recurse)) {
    $acl = Get-Acl -LiteralPath $item.FullName
    foreach ($ace in $acl.Access) {
        if ($ace.AccessControlType -ne [System.Security.AccessControl.AccessControlType]::Allow) { continue }
        try { $sid = $ace.IdentityReference.Translate([System.Security.Principal.SecurityIdentifier]).Value }
        catch { $sid = [string]$ace.IdentityReference }
        if ($sid -in $broadSids -or $sid -match '^S-1-5-21-(\d+-){3}513$') {
            throw "Broad allow ACEs were detected at $($item.FullName); review manually before applying ACL changes."
        }
    }
}
try {
    if ($ServiceAccount -match '^S-1-') {
        $account = [System.Security.Principal.SecurityIdentifier]::new($ServiceAccount)
    } else {
        $account = ([System.Security.Principal.NTAccount]::new($ServiceAccount)).Translate([System.Security.Principal.SecurityIdentifier])
    }
} catch { throw "Service account could not be resolved: $ServiceAccount" }
$principals = @(
    [pscustomobject]@{ Sid = $account; Rights = [System.Security.AccessControl.FileSystemRights]::Modify },
    [pscustomobject]@{ Sid = [System.Security.Principal.SecurityIdentifier]::new('S-1-5-32-544'); Rights = [System.Security.AccessControl.FileSystemRights]::FullControl },
    [pscustomobject]@{ Sid = [System.Security.Principal.SecurityIdentifier]::new('S-1-5-18'); Rights = [System.Security.AccessControl.FileSystemRights]::FullControl }
)
$inherit = [System.Security.AccessControl.InheritanceFlags]::ContainerInherit -bor [System.Security.AccessControl.InheritanceFlags]::ObjectInherit
if (-not $Apply) {
    Write-Output "PLAN ONLY: would grant Modify to the service account and FullControl to local Administrators and SYSTEM on $target. No ACL was changed."
    return
}
if ($PSCmdlet.ShouldProcess($target, 'Add restricted Logging ACL entries')) {
    $acl = Get-Acl -LiteralPath $target
    foreach ($principal in $principals) {
        $rule = [System.Security.AccessControl.FileSystemAccessRule]::new($principal.Sid, $principal.Rights, $inherit, [System.Security.AccessControl.PropagationFlags]::None, [System.Security.AccessControl.AccessControlType]::Allow)
        [void]$acl.AddAccessRule($rule)
    }
    Set-Acl -LiteralPath $target -AclObject $acl
    Write-Output 'PASS: restricted Logging ACL entries added. Existing ACEs were not removed.'
}
