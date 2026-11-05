[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][string]$ApplicationRoot,
    [switch]$Strict,
    [switch]$ReturnStatus,
    [switch]$ScanFiles
)
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path -LiteralPath $ApplicationRoot).Path
$target = Join-Path $root 'storage\logs'
$broadSids = @('S-1-1-0','S-1-5-11','S-1-5-32-545','S-1-5-32-546','S-1-5-4','S-1-5-2','S-1-5-14','S-1-5-113')
$issues = [System.Collections.Generic.List[string]]::new()
if (-not (Test-Path -LiteralPath $target -PathType Container)) {
    $issues.Add("Logging directory is missing: $target")
} else {
    $items = @((Get-Item -LiteralPath $target))
    if ($ScanFiles) {
        $items += @(Get-ChildItem -LiteralPath $target -Force -Recurse -ErrorAction Stop)
    } else {
        $items += @(Get-ChildItem -LiteralPath $target -Directory -Force -ErrorAction Stop)
    }
    foreach ($item in $items) {
        try { $acl = Get-Acl -LiteralPath $item.FullName } catch {
            $issues.Add("Cannot read ACL: $($item.FullName)")
            continue
        }
        foreach ($ace in $acl.Access) {
            if ($ace.AccessControlType -ne [System.Security.AccessControl.AccessControlType]::Allow) { continue }
            try { $sid = $ace.IdentityReference.Translate([System.Security.Principal.SecurityIdentifier]).Value }
            catch { $sid = [string]$ace.IdentityReference }
            $isDomainUsers = $sid -match '^S-1-5-21-(\d+-){3}513$'
            if ($sid -in $broadSids -or $isDomainUsers) {
                $issues.Add("Broad Allow ACE: $sid $($ace.FileSystemRights) at $($item.FullName)")
            }
        }
    }
}
foreach ($issue in $issues) { Write-Warning $issue }
if ($issues.Count -eq 0) { Write-Host 'PASS: no broad Allow ACEs found in the scanned Logging paths.' }
$code = if ($Strict -and $issues.Count -gt 0) { 2 } else { 0 }
if ($ReturnStatus) { Write-Output $code } else { exit $code }
