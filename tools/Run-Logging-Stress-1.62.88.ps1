[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][string]$Root,
    [Parameter(Mandatory=$true)][string]$BaseUrl,
    [string[]]$Paths = @('/'),
    [ValidateRange(1,10000)][int]$HttpRequests = 1200,
    [ValidateRange(1,20)][int]$HttpConcurrency = 8,
    [ValidateRange(0,20000)][int]$AuditEvents = 2000,
    [ValidateRange(1,8)][int]$AuditWorkers = 4,
    [ValidateRange(5,120)][int]$TimeoutSec = 20,
    [string]$OutputDirectory = (Join-Path $env:TEMP 'ITSM-Logging-Stress'),
    [switch]$AllowRemoteTarget,
    [switch]$ConfirmLoadTest
)

$ErrorActionPreference = 'Stop'
$ExpectedVersion = '1.62.88'

function Get-SpoolSnapshot {
    param([string]$ApplicationRoot)
    $dir = Join-Path $ApplicationRoot 'storage/logs/spool'
    if (-not (Test-Path -LiteralPath $dir -PathType Container)) {
        return [pscustomobject]@{ Files=0; Bytes=0 }
    }
    $files = @(Get-ChildItem -LiteralPath $dir -File -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -match '\.jsonl(\.processing)?$' })
    $bytes = 0L
    foreach ($file in $files) { $bytes += [long]$file.Length }
    return [pscustomobject]@{ Files=$files.Count; Bytes=$bytes }
}

function Get-AppProcesses {
    $items = @(Get-Process -Name httpd,mysqld,php -ErrorAction SilentlyContinue |
        Select-Object ProcessName, Id, CPU, WorkingSet64)
    return $items
}

if (-not $ConfirmLoadTest) {
    throw 'Load test is disabled. After preparing the server and notifying users, rerun with -ConfirmLoadTest.'
}
if ($HttpRequests -gt 10000 -or $HttpConcurrency -gt 20 -or $AuditEvents -gt 20000) {
    throw 'Requested load exceeds the script safety ceiling.'
}

$InstallRoot = (Resolve-Path -LiteralPath $Root).Path
$versionPath = Join-Path $InstallRoot 'VERSION'
if (-not (Test-Path -LiteralPath $versionPath -PathType Leaf)) { throw "VERSION not found: $versionPath" }
$installedVersion = (Get-Content -LiteralPath $versionPath -Raw).Trim()
if ($installedVersion -ne $ExpectedVersion) {
    throw "Version preflight failed: expected $ExpectedVersion, found '$installedVersion'. No load was generated."
}

$workerScript = Join-Path $InstallRoot 'tools/logging_stress_worker.php'
if (-not (Test-Path -LiteralPath $workerScript -PathType Leaf)) { throw "Missing stress worker: $workerScript" }
$php = if ($env:PHP_EXE) { $env:PHP_EXE } else {
    $cmd = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($cmd) { $cmd.Source } else {
        $xamppRoot = Split-Path (Split-Path $InstallRoot -Parent) -Parent
        $candidate = Join-Path $xamppRoot 'php\php.exe'
        if (Test-Path -LiteralPath $candidate -PathType Leaf) { $candidate } else { $null }
    }
}
if (-not $php -or -not (Test-Path -LiteralPath $php -PathType Leaf)) {
    throw 'PHP CLI not found. Set PHP_EXE to the full path of php.exe.'
}
& $php -l $workerScript
if ($LASTEXITCODE -ne 0) { throw 'PHP stress worker syntax check failed; no load was generated.' }

try { $baseUri = [Uri]$BaseUrl } catch { throw "Invalid BaseUrl: $BaseUrl" }
if ($baseUri.Scheme -notin @('http','https') -or -not [string]::IsNullOrEmpty($baseUri.UserInfo) -or $baseUri.Query -or $baseUri.Fragment) {
    throw 'BaseUrl must be an http(s) application URL without credentials, query string, or fragment.'
}
$isLoopback = $baseUri.IsLoopback -or $baseUri.Host -in @('localhost','127.0.0.1','::1')
if (-not $isLoopback -and -not $AllowRemoteTarget) {
    throw 'Remote BaseUrl requires explicit -AllowRemoteTarget. The target will receive real HTTP load.'
}
if ($Paths.Count -lt 1) { throw 'At least one GET path is required.' }
foreach ($path in $Paths) {
    if ([string]::IsNullOrWhiteSpace($path) -or $path -notmatch '^/') { throw "Each path must begin with /: $path" }
    if ($path -match '(?i)(logout|delete|remove|create|update|save|import|export|restore|backup|cron|action=|token=|csrf)') {
        throw "Potentially state-changing or sensitive path rejected: $path"
    }
    if ($path.Contains('|')) { throw 'Paths cannot contain the | character.' }
}

New-Item -ItemType Directory -Path $OutputDirectory -Force | Out-Null
$runId = [Guid]::NewGuid().ToString('N')
$startedAt = Get-Date
$spoolBefore = Get-SpoolSnapshot -ApplicationRoot $InstallRoot
$processesBefore = Get-AppProcesses
Write-Host "Run ID: $runId"
Write-Host "Target: $($baseUri.GetLeftPart([UriPartial]::Path))"
Write-Host "HTTP GET: $HttpRequests requests / $HttpConcurrency workers"
Write-Host "Synthetic audit events: $AuditEvents / $AuditWorkers PHP workers"
Write-Host 'No POST/PUT/DELETE requests will be sent. No ticket or user records will be created.'

$jobs = @()
$baseHttp = [math]::Floor($HttpRequests / $HttpConcurrency)
$extraHttp = $HttpRequests % $HttpConcurrency
for ($w = 1; $w -le $HttpConcurrency; $w++) {
    $quota = [int]($baseHttp + $(if ($w -le $extraHttp) { 1 } else { 0 }))
    if ($quota -le 0) { continue }
    $jobs += Start-Job -ArgumentList $BaseUrl,($Paths -join '|'),$quota,$w,$TimeoutSec -ScriptBlock {
        param($Base,$PathText,$Count,$Worker,$Timeout)
        $pathList = @($PathText -split '\|')
        for ($i=1; $i -le $Count; $i++) {
            $path = $pathList[($i - 1) % $pathList.Count]
            $uri = $Base.TrimEnd('/') + $path
            $watch = [Diagnostics.Stopwatch]::StartNew()
            $status = 0
            $bytes = 0
            $errorText = $null
            try {
                $response = Invoke-WebRequest -UseBasicParsing -Method Get -Uri $uri -TimeoutSec $Timeout -MaximumRedirection 3
                $status = [int]$response.StatusCode
                if ($response.RawContentLength) { $bytes = [int]$response.RawContentLength }
            } catch {
                $errorText = $_.Exception.Message
                try { if ($_.Exception.Response) { $status = [int]$_.Exception.Response.StatusCode } } catch {}
            }
            $watch.Stop()
            [pscustomobject]@{
                Kind='HTTP'; Worker=$Worker; Sequence=$i; Status=$status
                LatencyMs=[int]$watch.ElapsedMilliseconds; Bytes=$bytes; Error=$errorText
            }
        }
    }
}

if ($AuditEvents -gt 0) {
    $baseAudit = [math]::Floor($AuditEvents / $AuditWorkers)
    $extraAudit = $AuditEvents % $AuditWorkers
    for ($w = 1; $w -le $AuditWorkers; $w++) {
        $quota = [int]($baseAudit + $(if ($w -le $extraAudit) { 1 } else { 0 }))
        if ($quota -le 0) { continue }
        $jobs += Start-Job -ArgumentList $php,$workerScript,$runId,$quota,$w -ScriptBlock {
            param($PhpExe,$Script,$Run,$Count,$Worker)
            $output = @(& $PhpExe $Script "--run-id=$Run" "--count=$Count" "--worker=$Worker" 2>&1)
            $exitCode = $LASTEXITCODE
            [pscustomobject]@{
                Kind='PHP'; Worker=$Worker; ExitCode=$exitCode
                Output=($output | ForEach-Object { [string]$_ }) -join [Environment]::NewLine
            }
        }
    }
}

Write-Host 'Load is running...'
$performanceSamples = New-Object System.Collections.Generic.List[object]
$previousCpu = @{}
$deadline = (Get-Date).AddMinutes(15)
do {
    $sampleTime = Get-Date
    foreach ($proc in (Get-AppProcesses)) {
        $cpuSeconds = if ($null -ne $proc.CPU) { [double]$proc.CPU } else { 0.0 }
        $cpuDelta = 0.0
        if ($previousCpu.ContainsKey([int]$proc.Id)) {
            $cpuDelta = [math]::Max(0.0, $cpuSeconds - [double]$previousCpu[[int]$proc.Id])
        }
        $previousCpu[[int]$proc.Id] = $cpuSeconds
        $cpuPercent = [math]::Round(($cpuDelta / 2.0 / [Environment]::ProcessorCount) * 100.0, 2)
        $performanceSamples.Add([pscustomobject]@{
            SampledAt = $sampleTime.ToString('o')
            ProcessName = [string]$proc.ProcessName
            ProcessId = [int]$proc.Id
            CpuSecondsTotal = [math]::Round($cpuSeconds,2)
            CpuPercentApprox = $cpuPercent
            WorkingSetMB = [math]::Round(([double]$proc.WorkingSet64 / 1MB),2)
        })
    }
    $running = @($jobs | Where-Object { $_.State -eq 'Running' -or $_.State -eq 'NotStarted' })
    if ($running.Count -eq 0) { break }
    if ((Get-Date) -ge $deadline) { break }
    Start-Sleep -Seconds 2
} while ($true)
$unfinished = @($jobs | Where-Object { $_.State -eq 'Running' -or $_.State -eq 'NotStarted' })
if ($unfinished.Count -gt 0) {
    $unfinished | Stop-Job
    throw 'One or more load workers exceeded the 15-minute hard timeout.'
}
$results = @()
foreach ($job in $jobs) {
    $results += @(Receive-Job -Job $job -ErrorAction SilentlyContinue)
    if ($job.State -eq 'Failed') { Write-Warning "Worker job failed: $($job.ChildJobs[0].JobStateInfo.Reason)" }
    Remove-Job -Job $job -Force
}

$httpRows = @($results | Where-Object { $_.Kind -eq 'HTTP' })
$phpRows = @($results | Where-Object { $_.Kind -eq 'PHP' })
$httpFailures = @($httpRows | Where-Object { $_.Status -eq 0 -or $_.Status -ge 500 }).Count
$latencies = @($httpRows | ForEach-Object { [double]$_.LatencyMs } | Sort-Object)
$p50 = 0; $p95 = 0; $avg = 0
if ($latencies.Count -gt 0) {
    $p50 = $latencies[[math]::Min($latencies.Count - 1,[math]::Floor(($latencies.Count - 1) * 0.50))]
    $p95 = $latencies[[math]::Min($latencies.Count - 1,[math]::Ceiling(($latencies.Count - 1) * 0.95))]
    $avg = [math]::Round(($latencies | Measure-Object -Average).Average,2)
}

$verifyOutput = @(& $php $workerScript "--run-id=$runId" --verify 2>&1)
$verifyExit = $LASTEXITCODE
$verifyLine = @($verifyOutput | ForEach-Object { [string]$_ } | Where-Object { $_.Trim().StartsWith('{') } | Select-Object -Last 1)
$verification = $null
if ($verifyLine.Count -gt 0) {
    try { $verification = $verifyLine[0] | ConvertFrom-Json } catch {}
}
$spoolAfter = Get-SpoolSnapshot -ApplicationRoot $InstallRoot
$processesAfter = Get-AppProcesses
$finishedAt = Get-Date

$phpFailures = @($phpRows | Where-Object { $_.ExitCode -ne 0 }).Count
$expectedAudit = $AuditEvents
$actualAudit = if ($verification) { [int]$verification.total } else { -1 }
$uniqueAudit = if ($verification) { [int]$verification.unique_events } else { -1 }
$correlatedAudit = if ($verification) { [int]$verification.correlated } else { -1 }
$successAudit = if ($verification) { [int]$verification.successful } else { -1 }
$auditPass = $AuditEvents -eq 0 -or ($verifyExit -eq 0 -and $actualAudit -eq $expectedAudit -and $uniqueAudit -eq $expectedAudit -and $correlatedAudit -eq $expectedAudit -and $successAudit -eq $expectedAudit)

$report = [ordered]@{
    release = $ExpectedVersion
    run_id = $runId
    started_at = $startedAt.ToString('o')
    finished_at = $finishedAt.ToString('o')
    duration_seconds = [math]::Round(($finishedAt - $startedAt).TotalSeconds,2)
    target = $baseUri.GetLeftPart([UriPartial]::Path)
    http = [ordered]@{
        requested = $HttpRequests; completed = $httpRows.Count; concurrency = $HttpConcurrency
        failures_0_or_5xx = $httpFailures; average_ms = $avg; p50_ms = $p50; p95_ms = $p95
        status_counts = @($httpRows | Group-Object Status | ForEach-Object { [ordered]@{ status=[int]$_.Name; count=$_.Count } })
    }
    logging = [ordered]@{
        requested_synthetic_activity_events = $expectedAudit
        worker_processes = $AuditWorkers
        worker_failures = $phpFailures
        database_verified_count = $actualAudit
        unique_event_ids = $uniqueAudit
        events_with_request_trace_operation_ids = $correlatedAudit
        successful_result_count = $successAudit
        verification_pass = $auditPass
        test_record_marker = "STRESS-$runId"
    }
    spool = [ordered]@{
        before = $spoolBefore; after = $spoolAfter
        files_delta = $spoolAfter.Files - $spoolBefore.Files
        bytes_delta = $spoolAfter.Bytes - $spoolBefore.Bytes
    }
    processes_before = $processesBefore
    processes_after = $processesAfter
    performance_samples = $performanceSamples.ToArray()
    safety = @{
        http_methods = @('GET')
        business_mutations = $false
        automatic_worker_execution = $false
        automatic_cleanup = $false
        synthetic_audit_rows_are_retained = $true
    }
}
$reportPath = Join-Path $OutputDirectory ("logging-stress-$runId.json")
$csvPath = Join-Path $OutputDirectory ("logging-stress-http-$runId.csv")
$performanceCsvPath = Join-Path $OutputDirectory ("logging-stress-processes-$runId.csv")
$report | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath $reportPath -Encoding UTF8
$httpRows | Export-Csv -LiteralPath $csvPath -NoTypeInformation -Encoding UTF8
$performanceSamples.ToArray() | Export-Csv -LiteralPath $performanceCsvPath -NoTypeInformation -Encoding UTF8

Write-Host ''
Write-Host '========== LOGGING STRESS RESULT =========='
Write-Host ("HTTP completed: {0}/{1} | 0/5xx failures: {2} | avg: {3} ms | p95: {4} ms" -f $httpRows.Count,$HttpRequests,$httpFailures,$avg,$p95)
Write-Host ("Audit events: expected={0} | stored={1} | unique={2} | correlated={3} | worker failures={4}" -f $expectedAudit,$actualAudit,$uniqueAudit,$correlatedAudit,$phpFailures)
Write-Host ("Spool: files {0} -> {1} | bytes {2} -> {3}" -f $spoolBefore.Files,$spoolAfter.Files,$spoolBefore.Bytes,$spoolAfter.Bytes)
Write-Host "JSON report: $reportPath"
Write-Host "HTTP CSV: $csvPath"
Write-Host "Process metrics CSV: $performanceCsvPath"
Write-Host "Synthetic audit rows are intentionally retained and marked $($report.logging.test_record_marker)."

if ($httpRows.Count -ne $HttpRequests -or $httpFailures -gt 0 -or $phpFailures -gt 0 -or -not $auditPass) {
    throw 'STRESS TEST FAILED OR INCOMPLETE. Review the JSON/CSV report and application logs.'
}
Write-Host 'STRESS TEST PASS: HTTP load completed and every synthetic Logging event was verified in the database.'
