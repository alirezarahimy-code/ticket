[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][string]$Root,
    [Parameter(Mandatory=$true)][string]$InstalledRoot,
    [switch]$CheckInstalledAcl,
    [string]$BaseUrl
)

$ErrorActionPreference = 'Stop'
$ExpectedVersion = '1.62.83'
$SourceRoot = (Resolve-Path -LiteralPath $Root).Path
$InstallPath = (Resolve-Path -LiteralPath $InstalledRoot).Path

function Assert-File {
    param([string]$Base, [string]$Relative)
    $path = Join-Path $Base $Relative
    if (-not (Test-Path -LiteralPath $path -PathType Leaf)) {
        throw "Required release file is missing: $path"
    }
    return $path
}

$versionFile = Assert-File -Base $InstallPath -Relative 'VERSION'
$installedVersion = (Get-Content -LiteralPath $versionFile -Raw).Trim()
if ($installedVersion -ne $ExpectedVersion) {
    throw "Version preflight failed: expected $ExpectedVersion, found '$installedVersion'. No acceptance checks were run."
}

$releaseFiles = @(
    'VERSION',
    'system-update.php',
    'lib/logging-core.php',
    'lib/logging-audit.php',
    'lib/notify-users.php',
    'migrations/026-logging-legacy-result-unknown.sql',
    'migrations/032-schema-updates-baseline.sql',
    'migrations/033-logging-legacy-context-collation.sql',
    'tools/verify_logging_acl.ps1',
    'tools/set_logging_acl.ps1',
    'tools/verify_logging_web_protection.ps1'
)

foreach ($relative in $releaseFiles) {
    $sourceFile = Assert-File -Base $SourceRoot -Relative $relative
    $installedFile = Assert-File -Base $InstallPath -Relative $relative
    $sourceHash = (Get-FileHash -LiteralPath $sourceFile -Algorithm SHA256).Hash
    $installedHash = (Get-FileHash -LiteralPath $installedFile -Algorithm SHA256).Hash
    if ($sourceHash -ne $installedHash) {
        throw "Installed file differs from the selected release source: $relative"
    }
    Write-Output "PASS: release file matches: $relative"
}

$php = if ($env:PHP_EXE) { $env:PHP_EXE } else {
    $command = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($command) {
        $command.Source
    } else {
        # Standard XAMPP layout: <xampp>\htdocs\<app> and <xampp>\php\php.exe.
        $xamppRoot = Split-Path (Split-Path $InstallPath -Parent) -Parent
        $candidate = Join-Path $xamppRoot 'php\php.exe'
        if (Test-Path -LiteralPath $candidate -PathType Leaf) { $candidate } else { $null }
    }
}
if (-not $php -or -not (Test-Path -LiteralPath $php -PathType Leaf)) {
    throw 'PHP CLI was not found. Set PHP_EXE to the full path of php.exe.'
}
foreach ($relative in @('lib/logging-core.php','lib/logging-audit.php','lib/notify-users.php','system-update.php')) {
    $file = Join-Path $InstallPath $relative
    & $php -l $file
    if ($LASTEXITCODE -ne 0) { throw "PHP syntax check failed: $relative" }
}
Write-Output 'PASS: installed Logging and updater PHP syntax.'

if ($CheckInstalledAcl) {
    $verifier = Join-Path $InstallPath 'tools/verify_logging_acl.ps1'
    $aclResult = & $verifier -ApplicationRoot $InstallPath -Strict -ReturnStatus -ScanFiles | Select-Object -Last 1
    if ([int]$aclResult -ne 0) {
        throw 'Installed Logging ACL verification failed. No ACL changes were made.'
    }
    Write-Output 'PASS: installed Logging ACL is restricted (read-only verification).'
}

if (-not [string]::IsNullOrWhiteSpace($BaseUrl)) {
    $webVerifier = Join-Path $InstallPath 'tools/verify_logging_web_protection.ps1'
    & $webVerifier -BaseUrl $BaseUrl
    if ($LASTEXITCODE -ne 0) { throw 'Anonymous HTTP protection verification failed.' }
    Write-Output 'PASS: anonymous HTTP protection checks.'
}

Write-Output "ACCEPTANCE PASS: installed release $ExpectedVersion."
