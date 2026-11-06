[CmdletBinding()]
param([Parameter(Mandatory=$true)][string]$BaseUrl)
$ErrorActionPreference = 'Stop'
$base = [Uri]$BaseUrl
if (-not $base.IsAbsoluteUri -or $base.Scheme -notin @('http','https') -or $base.UserInfo -or $base.Query -or $base.Fragment) {
    throw 'BaseUrl must be an absolute HTTP(S) URL without embedded credentials.'
}
$base = [Uri]($base.AbsoluteUri.TrimEnd('/') + '/')
# Paths are relative to the application URL, which may be hosted under /ticket/.
$paths = @('config.php','storage/','storage/logs/','storage/backups/','uploads/')
$failed = 0
foreach ($path in $paths) {
    $uri = [Uri]::new($base, $path)
    $status = $null
    try {
        $response = Invoke-WebRequest -Uri $uri -Method Get -MaximumRedirection 0 -TimeoutSec 20 -ErrorAction Stop
        $status = [int]$response.StatusCode
    } catch {
        if ($_.Exception.Response -and $_.Exception.Response.StatusCode) {
            $status = [int]$_.Exception.Response.StatusCode
        }
    }
    if ($status -in @(401,403,404,410)) {
        Write-Output "PASS $path => HTTP $status"
    } else {
        Write-Error "FAIL $path => HTTP $status (expected 401/403/404/410)"
        $failed++
    }
}
if ($failed -gt 0) { throw "$failed protected-path HTTP check(s) failed." }
