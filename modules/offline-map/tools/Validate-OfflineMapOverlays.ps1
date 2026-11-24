[CmdletBinding()]
param([string]$OverlayDir = (Join-Path $PSScriptRoot '..\data\overlays'))
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$OverlayDir = [IO.Path]::GetFullPath($OverlayDir)
$manifestPath = Join-Path $OverlayDir 'manifest.json'
if (-not (Test-Path -LiteralPath $manifestPath -PathType Leaf)) { throw "Overlay manifest is missing: $manifestPath" }
$manifest = Get-Content -LiteralPath $manifestPath -Raw | ConvertFrom-Json
$expected = @{
    official_boundaries = 'official-boundaries.geojson'
    municipal_districts = 'municipal-districts.geojson'
    transit = 'transit.geojson'
}
$errors = New-Object System.Collections.Generic.List[string]
foreach ($key in $expected.Keys) {
    $entry = $manifest.datasets.$key
    $path = Join-Path $OverlayDir $expected[$key]
    if (-not (Test-Path -LiteralPath $path -PathType Leaf)) {
        Write-Host "NOT INSTALLED: $($expected[$key])" -ForegroundColor Yellow
        continue
    }
    if (-not $entry) { $errors.Add("Manifest metadata missing for $key"); continue }
    if ($entry.file -ne $expected[$key]) { $errors.Add("Manifest filename mismatch for $key") }
    if ([long]$entry.bytes -ne (Get-Item -LiteralPath $path).Length) { $errors.Add("Byte size mismatch for $key") }
    if ([string]::IsNullOrWhiteSpace([string]$entry.source)) { $errors.Add("Source publisher is required for $key") }
    if ([string]::IsNullOrWhiteSpace([string]$entry.license)) { $errors.Add("License is required for $key") }
    if ([string]::IsNullOrWhiteSpace([string]$entry.updated_at)) { $errors.Add("Dataset date is required for $key") }
    $hash = (Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash.ToLowerInvariant()
    if ($entry.sha256 -ne $hash) { $errors.Add("SHA-256 mismatch for $key") }
    try {
        $geo = Get-Content -LiteralPath $path -Raw | ConvertFrom-Json
        if ($geo.type -ne 'FeatureCollection' -or -not ($geo.features -is [System.Array])) { $errors.Add("Invalid GeoJSON FeatureCollection: $key") }
        if ($geo.features.Count -lt 1) { $errors.Add("GeoJSON has no features: $key") }
    } catch { $errors.Add("Cannot parse GeoJSON $key : $($_.Exception.Message)") }
}
if ($errors.Count) {
    Write-Host 'OVERLAY VALIDATION: FAIL' -ForegroundColor Red
    $errors | ForEach-Object { Write-Host " - $_" -ForegroundColor Red }
    exit 1
}
Write-Host 'OVERLAY VALIDATION: PASS (installed overlays have source metadata and matching checksums)' -ForegroundColor Green
