[CmdletBinding()]
param(
    [string]$SourcePbf = (Join-Path $PSScriptRoot '..\data\source\iran-latest.osm.pbf'),
    [string]$PlanetilerExe = (Join-Path $PSScriptRoot 'bin\planetiler.exe'),
    [string]$GlyphArchive = '',
    [switch]$SkipLibraryInstall
)
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$SourcePbf = [IO.Path]::GetFullPath($SourcePbf)
$PlanetilerExe = [IO.Path]::GetFullPath($PlanetilerExe)
$tools = $PSScriptRoot
$data = [IO.Path]::GetFullPath((Join-Path $tools '..\data'))
if (-not (Test-Path -LiteralPath $SourcePbf -PathType Leaf)) { throw "PBF input not found: $SourcePbf" }
if (-not $SkipLibraryInstall) {
    & (Join-Path $tools 'Install-OfflineMapLibraries.ps1')
    if ($LASTEXITCODE -ne 0) { throw "Library installation failed: $LASTEXITCODE" }
}
if ($GlyphArchive) {
    & (Join-Path $tools 'Install-OfflineMapGlyphs.ps1') -GlyphArchive $GlyphArchive
    if ($LASTEXITCODE -ne 0) { throw "Glyph installation failed: $LASTEXITCODE" }
}
if (-not (Test-Path (Join-Path $data 'fonts\Noto Sans Arabic Regular\1536-1791.pbf'))) {
    throw 'Persian glyphs are missing. Provide -GlyphArchive or install them with Install-OfflineMapGlyphs.ps1.'
}
& (Join-Path $tools 'Build-OfflineIranMap.ps1') -SourcePbf $SourcePbf -PlanetilerExe $PlanetilerExe
if ($LASTEXITCODE -ne 0) { throw "Planetiler build failed with exit code $LASTEXITCODE" }
$python = Get-Command python -ErrorAction SilentlyContinue
if (-not $python) { throw 'Python 3.10+ is required for offline search indexing.' }
& python -m pip show osmium *> $null
if ($LASTEXITCODE -ne 0) {
    & python -m pip install -r (Join-Path $tools 'requirements-search.txt')
    if ($LASTEXITCODE -ne 0) { throw 'Could not install pyosmium.' }
}
& python (Join-Path $tools 'build-search-index.py') --pbf $SourcePbf
if ($LASTEXITCODE -ne 0) { throw "Search index build failed with exit code $LASTEXITCODE" }
& (Join-Path $tools 'Validate-OfflineMapOverlays.ps1')
if ($LASTEXITCODE -ne 0) { throw 'GIS overlay validation failed.' }
& (Join-Path $tools 'Test-OfflineIranMapPackage.ps1')
if ($LASTEXITCODE -ne 0) { throw 'Offline map package validation failed.' }
Write-Host ''
Write-Host 'نقشهٔ پایه و شاخص جست‌وجو ساخته و کنترل بسته انجام شد.' -ForegroundColor Green
Write-Host 'پوشش واقعی، مرزهای رسمی و داده‌های حمل‌ونقل باید با منابع مستقل و مجاز تکمیل شوند.'
