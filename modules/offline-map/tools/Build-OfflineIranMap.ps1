[CmdletBinding()]
param(
    [string]$Root = (Split-Path -Parent $PSScriptRoot),
    [string]$SourcePbf = (Join-Path $PSScriptRoot '..\data\source\iran-latest.osm.pbf'),
    [string]$PlanetilerExe = (Join-Path $PSScriptRoot 'bin\planetiler.exe'),
    [string]$OutputPmtiles = (Join-Path $PSScriptRoot '..\data\iran.pmtiles'),
    [string]$ManifestPath = (Join-Path $PSScriptRoot '..\data\build-manifest.json'),
    [string[]]$AdditionalPlanetilerArgs = @(),
    [switch]$AllowLowDiskSpace
)
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
function Stop-Build([string]$Message) { Write-Error $Message; exit 1 }
$SourcePbf = [IO.Path]::GetFullPath($SourcePbf)
$PlanetilerExe = [IO.Path]::GetFullPath($PlanetilerExe)
$OutputPmtiles = [IO.Path]::GetFullPath($OutputPmtiles)
$ManifestPath = [IO.Path]::GetFullPath($ManifestPath)
if (-not (Test-Path -LiteralPath $SourcePbf -PathType Leaf)) { Stop-Build "فایل ورودی پیدا نشد: $SourcePbf" }
if (-not (Test-Path -LiteralPath $PlanetilerExe -PathType Leaf)) { Stop-Build "Planetiler پیدا نشد: $PlanetilerExe. نسخه Windows x64 را از راهنمای رسمی Planetiler دریافت و در tools\bin قرار دهید." }
$sourceInfo = Get-Item -LiteralPath $SourcePbf
if ($sourceInfo.Length -lt 1024) { Stop-Build 'حجم فایل PBF غیرعادی و کمتر از ۱ کیلوبایت است.' }
$driveRoot = [IO.Path]::GetPathRoot($OutputPmtiles)
$drive = [IO.DriveInfo]::new($driveRoot)
$requiredBytes = [Math]::Max(1GB, [double]$sourceInfo.Length * 10)
if (-not $AllowLowDiskSpace -and $drive.AvailableFreeSpace -lt $requiredBytes) {
    Stop-Build ("فضای خالی کافی نیست. حداقل پیشنهادی {0:N2} GB و فضای موجود {1:N2} GB است. فضای SSD را آزاد کنید یا فقط با آگاهی از ریسک از -AllowLowDiskSpace استفاده کنید." -f ($requiredBytes / 1GB), ($drive.AvailableFreeSpace / 1GB))
}
$outDir = Split-Path -Parent $OutputPmtiles
$manifestDir = Split-Path -Parent $ManifestPath
New-Item -ItemType Directory -Force -Path $outDir, $manifestDir | Out-Null
$sourceHash = (Get-FileHash -LiteralPath $SourcePbf -Algorithm SHA256).Hash.ToLowerInvariant()
$toolHash = (Get-FileHash -LiteralPath $PlanetilerExe -Algorithm SHA256).Hash.ToLowerInvariant()
Write-Host 'شروع تولید کاشی‌های برداری ایران'
Write-Host "ورودی: $SourcePbf"
Write-Host ("حجم PBF: {0:N2} MB" -f ($sourceInfo.Length / 1MB))
Write-Host "خروجی: $OutputPmtiles"
Write-Host 'این مرحله فقط بستهٔ کاشی را تولید می‌کند؛ style، فونت، آیکون، جست‌وجو و مرزهای رسمی مراحل جداگانه‌اند.'
$argsList = @("--osm-path=$SourcePbf", "--output=$OutputPmtiles", '--force', '--memory=1g') + $AdditionalPlanetilerArgs
Push-Location (Split-Path -Parent $PlanetilerExe)
try { & $PlanetilerExe @argsList; $exitCode = $LASTEXITCODE }
finally { Pop-Location }
if ($exitCode -ne 0) { Stop-Build "Planetiler با کد خروج $exitCode متوقف شد. manifest موفقیت ساخته نشد." }
if (-not (Test-Path -LiteralPath $OutputPmtiles -PathType Leaf)) { Stop-Build 'Planetiler با کد صفر برگشت، اما فایل خروجی وجود ندارد.' }
$stream = [IO.File]::OpenRead($OutputPmtiles)
try { $magic = New-Object byte[] 7; $read = $stream.Read($magic, 0, 7) }
finally { $stream.Dispose() }
$magicText = [Text.Encoding]::ASCII.GetString($magic, 0, $read)
if ($magicText -ne 'PMTiles') { Stop-Build "هدر خروجی PMTiles معتبر نیست (magic=$magicText). فایل خروجی را معتبر تلقی نکنید." }
$outputInfo = Get-Item -LiteralPath $OutputPmtiles
$outputHash = (Get-FileHash -LiteralPath $OutputPmtiles -Algorithm SHA256).Hash.ToLowerInvariant()
$manifest = [ordered]@{
    schema_version = 1
    package_status = 'tiles-built-style-and-search-not-yet-validated'
    generated_at_utc = [DateTime]::UtcNow.ToString('o')
    region = 'IR'
    source = [ordered]@{ file = [IO.Path]::GetFileName($SourcePbf); bytes = $sourceInfo.Length; sha256 = $sourceHash; dataset = 'OpenStreetMap'; license = 'ODbL-1.0'; attribution = '© OpenStreetMap contributors' }
    generator = [ordered]@{ name = 'Planetiler'; executable = [IO.Path]::GetFileName($PlanetilerExe); executable_sha256 = $toolHash; arguments = $argsList }
    tiles = [ordered]@{ file = [IO.Path]::GetFileName($OutputPmtiles); bytes = $outputInfo.Length; sha256 = $outputHash; format = 'PMTiles' }
    not_included_or_not_verified = @('MapLibre style and local glyphs/sprites','Persian offline search index','official administrative boundaries','municipal districts and public transport completeness','street/building/address coverage completeness')
}
$manifest | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath $ManifestPath -Encoding UTF8
Write-Host 'تولید کاشی‌ها و کنترل هدر PMTiles موفق بود.' -ForegroundColor Green
Write-Host "SHA-256 خروجی: $outputHash"
Write-Host "Manifest: $ManifestPath"
Write-Host 'توجه: این موفقیت به معنی آماده بودن رابط نقشه یا تأیید پوشش داده نیست.'
