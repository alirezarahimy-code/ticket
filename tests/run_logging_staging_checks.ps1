[CmdletBinding()]
param(
    [switch]$RunOperational
)
$ErrorActionPreference = 'Stop'
$Root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$Php = if ($env:PHP_EXE) { $env:PHP_EXE } else { (Get-Command php.exe -ErrorAction Stop).Source }
$Python = if ($env:PYTHON_EXE) { $env:PYTHON_EXE } else { (Get-Command python.exe -ErrorAction Stop).Source }

function Invoke-PhpTest {
    param([Parameter(Mandatory=$true)][string]$Path, [string[]]$Arguments = @(), [switch]$RequireExecution)
    $output = @(& $Php $Path @Arguments 2>&1)
    $exitCode = $LASTEXITCODE
    $output | ForEach-Object { Write-Host $_ }
    $wasSkipped = @($output | Where-Object { ([string]$_).StartsWith('SKIP:') }).Count -gt 0
    if ($wasSkipped) {
        if ($RequireExecution) { throw "Required integration test was skipped: $Path" }
        Write-Warning "SKIP (not counted as PASS): $([System.IO.Path]::GetFileName($Path))"
        return 'SKIP'
    }
    if ($exitCode -ne 0) { throw "PHP test failed (exit $exitCode): $Path" }
    return 'PASS'
}

Write-Host '== PHP syntax checks =='
$PhpFiles = Get-ChildItem -Path $Root -Recurse -Filter '*.php' -File | Where-Object {
    $_.FullName -notmatch '\\updates\\' -and
    $_.FullName -notmatch '\\vendor\\' -and
    $_.FullName -notmatch '\\release-archive\\' -and
    $_.FullName -notmatch '\\backup\\' -and
    $_.FullName -notmatch '\\.pytest_cache\\' -and
    $_.FullName -notmatch '\\__pycache__\\'
}
foreach ($File in $PhpFiles) {
    & $Php -l $File.FullName | Out-Null
    if ($LASTEXITCODE -ne 0) { throw "PHP lint failed: $($File.FullName)" }
}
Write-Host ("PASS: {0} PHP files" -f $PhpFiles.Count)

Write-Host '== Installed release acceptance preflight =='
& (Join-Path $Root 'tools/Run-Logging-Acceptance-1.62.83.ps1') -Root $Root -InstalledRoot $Root

Write-Host '== Transaction/commit contract test =='
Invoke-PhpTest -Path (Join-Path $Root 'tools/test_ticket_txn_offline.php') -RequireExecution | Out-Null
Invoke-PhpTest -Path (Join-Path $Root 'tools/test_module_grant_offline.php') -RequireExecution | Out-Null
Invoke-PhpTest -Path (Join-Path $Root 'tests/test_system_update_migration_registry.php') -RequireExecution | Out-Null
Invoke-PhpTest -Path (Join-Path $Root 'tests/test_backup_restore_archive.php') -RequireExecution | Out-Null

Write-Host '== PHP Logging tests =='
$PhpTests = Get-ChildItem -Path (Join-Path $Root 'tests') -Filter 'test_logging_*.php' -File | Sort-Object Name
$PassCount = 0; $SkipCount = 0
foreach ($Test in $PhpTests) {
    Write-Host ("== {0} ==" -f $Test.Name)
    $result = Invoke-PhpTest -Path $Test.FullName
    if ($result -eq 'PASS') { $PassCount++ } else { $SkipCount++ }
}
Write-Host ("PHP tests: {0} PASS; {1} SKIP (SKIP is never counted as PASS)." -f $PassCount, $SkipCount)

Write-Host '== Python Logging/static contract tests via pytest =='
$PythonTestFiles = @(
    Get-ChildItem -Path (Join-Path $Root 'tests') -Filter 'test_logging_*.py' -File
    Get-Item (Join-Path $Root 'tests/test_windows_logging_static.py')
    Get-Item (Join-Path $Root 'tests/test_update_manifest_guard_static.py')
    Get-Item (Join-Path $Root 'tests/test_route_security_static.py')
    Get-Item (Join-Path $Root 'tests/test_migration_registry_seed.py')
    Get-Item (Join-Path $Root 'tests/test_backup_restore_static.py')
) | Sort-Object FullName -Unique
& $Python -m pytest -q @($PythonTestFiles.FullName)
if ($LASTEXITCODE -ne 0) { throw 'Python Logging/static contract tests failed.' }

if ($RunOperational) {
    if ([string]::IsNullOrWhiteSpace($env:ITSM_TEST_DSN) -or $env:ITSM_TEST_ALLOW_DESTRUCTIVE -ne 'YES') {
        throw 'Operational acceptance requires ITSM_TEST_DSN to a dedicated database whose name contains test and ITSM_TEST_ALLOW_DESTRUCTIVE=YES.'
    }
    if ([string]::IsNullOrWhiteSpace($env:LOGGING_INTEGRITY_KEY) -or [string]::IsNullOrWhiteSpace($env:LOGGING_INTEGRITY_BACKUP_DIR)) {
        throw 'Operational acceptance requires LOGGING_INTEGRITY_KEY and LOGGING_INTEGRITY_BACKUP_DIR.'
    }
    Write-Host '== Release preflight (read-only) =='
    Invoke-PhpTest -Path (Join-Path $Root 'cron/verify_logging_release.php') -RequireExecution | Out-Null
    Write-Host '== MySQL/MariaDB migration and integration acceptance =='
    Invoke-PhpTest -Path (Join-Path $Root 'tests/test_logging_mysql_integration.php') -RequireExecution | Out-Null
    Write-Host '== Read-only recursive Windows ACL verification (strict) =='
    $aclResult = & (Join-Path $Root 'tools/verify_logging_acl.ps1') -ApplicationRoot $Root -Strict -ReturnStatus -ScanFiles | Select-Object -Last 1
    if ([int]$aclResult -ne 0) { throw 'Logging ACL verification failed; do not apply ACL changes automatically.' }
    # The historical reconcile_update_audit.php entry point is not present in this source tree.
    # verify_logging_release.php already fails closed when a pending completion marker exists.
    Write-Host '== Update completion-audit state (read-only release preflight) =='
    Write-Host '== Replay worker =='
    Invoke-PhpTest -Path (Join-Path $Root 'cron/replay_logging_spool.php') -RequireExecution | Out-Null
    Write-Host '== Issue rebuild and notification delivery workers =='
    Invoke-PhpTest -Path (Join-Path $Root 'cron/rebuild_logging_issues.php') -RequireExecution | Out-Null
    Invoke-PhpTest -Path (Join-Path $Root 'cron/deliver_logging_issue_notifications.php') -RequireExecution | Out-Null
    Write-Host '== Logging health and notification queue monitor =='
    Invoke-PhpTest -Path (Join-Path $Root 'cron/check_logging_health.php') -RequireExecution | Out-Null
    Write-Host '== Integrity daily export and seven-day reconciliation =='
    $exportScript = Join-Path $Root 'cron/export_logging_integrity.php'
    Invoke-PhpTest -Path $exportScript -RequireExecution | Out-Null
    $backup = (Resolve-Path $env:LOGGING_INTEGRITY_BACKUP_DIR).Path
    $date = (Get-Date).Date.AddDays(-1).ToString('yyyy-MM-dd')
    $baseArchive = Join-Path $backup ("logging-integrity-{0}.jsonl" -f $date)
    if (-not (Test-Path -LiteralPath $baseArchive)) { throw "Daily integrity archive was not produced: $baseArchive" }
    Invoke-PhpTest -Path (Join-Path $Root 'cron/verify_logging_integrity.php') -Arguments @($baseArchive) -RequireExecution | Out-Null
    Get-ChildItem -LiteralPath $backup -Filter ("logging-integrity-{0}.late-*.jsonl" -f $date) -File | ForEach-Object {
        & $Php (Join-Path $Root 'cron/verify_logging_integrity.php') $_.FullName
        if ($LASTEXITCODE -ne 0) { throw "Integrity supplement verification failed: $($_.FullName)" }
    }
    if ([string]::IsNullOrWhiteSpace($env:LOGGING_STAGING_BASE_URL)) { throw 'Operational acceptance requires LOGGING_STAGING_BASE_URL for anonymous storage/configuration HTTP protection checks.' }
    Write-Host '== Anonymous HTTP protection for storage and configuration paths =='
    & (Join-Path $Root 'tools/verify_logging_web_protection.ps1') -BaseUrl $env:LOGGING_STAGING_BASE_URL
}
Write-Host 'Selected Logging checks completed. Any SKIP above remains unverified.'
