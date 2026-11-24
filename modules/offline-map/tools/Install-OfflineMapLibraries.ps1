[CmdletBinding()]
param(
    [string]$VendorDir = (Join-Path $PSScriptRoot '..\vendor'),
    [string]$WorkDir = (Join-Path $env:TEMP 'offline-map-npm-install')
)
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$VendorDir = [IO.Path]::GetFullPath($VendorDir)
$WorkDir = [IO.Path]::GetFullPath($WorkDir)
if (-not (Get-Command npm -ErrorAction SilentlyContinue)) {
    throw 'Node.js/npm نصب نیست. Node.js LTS را نصب کنید و دوباره اجرا کنید.'
}
if (Test-Path $WorkDir) { Remove-Item -LiteralPath $WorkDir -Recurse -Force }
New-Item -ItemType Directory -Force -Path $WorkDir, $VendorDir, (Join-Path $VendorDir 'licenses') | Out-Null
Push-Location $WorkDir
try {
    & npm install --prefix $WorkDir --no-save --ignore-scripts --no-audit --no-fund 'maplibre-gl@6.18.0' 'pmtiles@4.5.0'
    if ($LASTEXITCODE -ne 0) { throw "npm install failed with exit code $LASTEXITCODE" }
} finally { Pop-Location }
$maplibre = Join-Path $WorkDir 'node_modules\maplibre-gl\dist'
$pmtiles = Join-Path $WorkDir 'node_modules\pmtiles\dist'
$required = @(
    (Join-Path $maplibre 'maplibre-gl.js'),
    (Join-Path $maplibre 'maplibre-gl.css'),
    (Join-Path $pmtiles 'pmtiles.js')
)
foreach ($file in $required) {
    if (-not (Test-Path -LiteralPath $file -PathType Leaf)) { throw "Expected distribution file is missing: $file" }
}
Copy-Item -LiteralPath $required[0] -Destination (Join-Path $VendorDir 'maplibre-gl.js') -Force
Copy-Item -LiteralPath $required[1] -Destination (Join-Path $VendorDir 'maplibre-gl.css') -Force
Copy-Item -LiteralPath $required[2] -Destination (Join-Path $VendorDir 'pmtiles.js') -Force
$licenseMap = Join-Path $WorkDir 'node_modules\maplibre-gl\LICENSE.txt'
if (-not (Test-Path $licenseMap)) { $licenseMap = Join-Path $WorkDir 'node_modules\maplibre-gl\LICENSE' }
$licensePm = Join-Path $WorkDir 'node_modules\pmtiles\LICENSE'
if (Test-Path $licenseMap) { Copy-Item $licenseMap (Join-Path $VendorDir 'licenses\MapLibre-LICENSE.txt') -Force }
if (Test-Path $licensePm) { Copy-Item $licensePm (Join-Path $VendorDir 'licenses\PMTiles-LICENSE.txt') -Force }
Write-Host 'کتابخانه‌های محلی نصب شدند:' -ForegroundColor Green
$required | ForEach-Object { Write-Host " - $_" }
Write-Host 'نسخه‌ها: MapLibre GL JS 6.18.0 / PMTiles 4.5.0'
Write-Host 'برای اجرای عملیاتی، اینترنت لازم نیست؛ دریافت npm فقط در مرحله آماده‌سازی انجام شد.'
