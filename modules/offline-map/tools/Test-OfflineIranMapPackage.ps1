[CmdletBinding()]
param(
    [string]$DataDir = (Join-Path $PSScriptRoot '..\data'),
    [string]$VendorDir = (Join-Path $PSScriptRoot '..\vendor')
)
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$DataDir = [IO.Path]::GetFullPath($DataDir)
$VendorDir = [IO.Path]::GetFullPath($VendorDir)
$errors = New-Object System.Collections.Generic.List[string]
$warnings = New-Object System.Collections.Generic.List[string]
function Require-File([string]$Path, [string]$Label) {
    if (-not (Test-Path -LiteralPath $Path -PathType Leaf)) { $script:errors.Add("Missing: $Label ($Path)") }
}
$tilePath = Join-Path $DataDir 'iran.pmtiles'
$manifestPath = Join-Path $DataDir 'manifest.json'
$stylePath = Join-Path $DataDir 'style.json'
$schemaPath = Join-Path $PSScriptRoot 'iran-high-zoom-schema.yml'
Require-File $tilePath 'PMTiles archive'
Require-File $manifestPath 'build manifest'
Require-File $stylePath 'MapLibre style'
Require-File $schemaPath 'Custom Iran high-zoom schema'
$searchPath = Join-Path $DataDir 'search.sqlite'
Require-File $searchPath 'Persian offline search index'
Require-File (Join-Path $VendorDir 'maplibre-gl.js') 'MapLibre GL JS'
Require-File (Join-Path $VendorDir 'maplibre-gl.css') 'MapLibre CSS'
Require-File (Join-Path $VendorDir 'pmtiles.js') 'PMTiles protocol library'
$glyphDir = Join-Path $DataDir 'fonts\Noto Sans Arabic Regular'
Require-File (Join-Path $glyphDir '0-255.pbf') 'Latin glyph range'
Require-File (Join-Path $glyphDir '1536-1791.pbf') 'Arabic glyph range'
Require-File (Join-Path $glyphDir '8192-8447.pbf') 'Zero-width joiner glyph range'

if (Test-Path -LiteralPath $tilePath -PathType Leaf) {
    $stream = [IO.File]::OpenRead($tilePath)
    try { $magic = New-Object byte[] 7; $read = $stream.Read($magic, 0, 7) }
    finally { $stream.Dispose() }
    if ([Text.Encoding]::ASCII.GetString($magic, 0, $read) -ne 'PMTiles') { $errors.Add('iran.pmtiles does not have a PMTiles header.') }
}
if (Test-Path -LiteralPath $searchPath -PathType Leaf) {
    $searchStream = [IO.File]::OpenRead($searchPath)
    try { $sqliteMagic = New-Object byte[] 16; $searchRead = $searchStream.Read($sqliteMagic, 0, 16) }
    finally { $searchStream.Dispose() }
    if ([Text.Encoding]::ASCII.GetString($sqliteMagic, 0, $searchRead) -ne "SQLite format 3`0") { $errors.Add('search.sqlite does not have a valid SQLite header.') }
}
if ((Test-Path -LiteralPath $manifestPath -PathType Leaf) -and (Test-Path -LiteralPath $tilePath -PathType Leaf)) {
    try {
        $manifest = Get-Content -LiteralPath $manifestPath -Raw | ConvertFrom-Json
        if ($manifest.tiles.file -ne 'iran.pmtiles') { $errors.Add('Manifest tiles.file must be iran.pmtiles.') }
        if ([int]$manifest.generator.maxzoom -lt 17 -or -not $manifest.generator.schema_sha256) { $errors.Add('Manifest does not describe the custom high-zoom schema (maxzoom >= 17).') }
        if ($manifest.tiles.format -ne 'PMTiles') { $errors.Add('Manifest tiles.format must be PMTiles.') }
        if ([long]$manifest.tiles.bytes -ne (Get-Item -LiteralPath $tilePath).Length) { $errors.Add('Manifest tile size does not match iran.pmtiles.') }
        $actualHash = (Get-FileHash -LiteralPath $tilePath -Algorithm SHA256).Hash.ToLowerInvariant()
        if ($manifest.tiles.sha256 -ne $actualHash) { $errors.Add('Manifest SHA-256 does not match iran.pmtiles.') }
        if ($manifest.search_index -and (Test-Path -LiteralPath $searchPath -PathType Leaf)) {
            $searchHash = (Get-FileHash -LiteralPath $searchPath -Algorithm SHA256).Hash.ToLowerInvariant()
            if ($manifest.search_index.sha256 -ne $searchHash) { $errors.Add('Manifest SHA-256 does not match search.sqlite.') }
        } else { $errors.Add('Manifest does not describe the offline search index.') }
    } catch { $errors.Add("Manifest cannot be parsed or verified: $($_.Exception.Message)") }
}
if (Test-Path -LiteralPath $schemaPath -PathType Leaf) {
    $schemaText = Get-Content -LiteralPath $schemaPath -Raw
    if ($schemaText -notmatch '(?m)^\\s*maxzoom:\\s*17\\s*
    try {
        $style = Get-Content -LiteralPath $stylePath -Raw | ConvertFrom-Json
        $styleText = Get-Content -LiteralPath $stylePath -Raw
        $styleTextWithoutPmtiles = $styleText -replace '(?i)pmtiles://', ''
        if ($styleTextWithoutPmtiles -match '(?i)(https?:)?//|mapbox://') { $errors.Add('Style contains a remote URL or mapbox:// reference.') }
        if ($styleText -notmatch '(?i)pmtiles://[^"]*iran\.pmtiles') { $errors.Add('Style does not reference the local iran.pmtiles archive through pmtiles://.') }
        if (-not $style.sources -or $style.sources.Count -lt 1) { $errors.Add('Style has no vector sources.') }
        if (-not $style.glyphs) { $errors.Add('Style has no local glyphs path; Persian labels may not render.') }
        if (-not $style.sprite) { $warnings.Add('Style has no sprite path. This is acceptable only if no symbol layers require sprites.') }
    } catch { $errors.Add("Style JSON cannot be parsed: $($_.Exception.Message)") }
}
foreach ($optional in @('search.sqlite','boundaries.pmtiles','transport.pmtiles')) {
    if (-not (Test-Path -LiteralPath (Join-Path $DataDir $optional) -PathType Leaf)) { $warnings.Add("Optional national dataset not installed: $optional") }
}
if ($errors.Count -gt 0) {
    Write-Host 'OFFLINE MAP PACKAGE: FAIL' -ForegroundColor Red
    $errors | ForEach-Object { Write-Host " - $_" -ForegroundColor Red }
} else {
    Write-Host 'OFFLINE MAP BASE PACKAGE: PASS' -ForegroundColor Green
}
if ($warnings.Count -gt 0) {
    Write-Host 'INCOMPLETE COVERAGE / OPTIONAL DATA:' -ForegroundColor Yellow
    $warnings | ForEach-Object { Write-Host " - $_" -ForegroundColor Yellow }
}
if ($errors.Count -gt 0) { exit 1 }
) { $errors.Add('Custom schema must declare maxzoom: 17.') }
    foreach ($layerName in @('roads','buildings','poi','places','admin_boundaries','railways','addresses','transit')) {
        if ($schemaText -notmatch ('(?m)^\\s*- id: ' + [regex]::Escape($layerName) + '\\s*
    try {
        $style = Get-Content -LiteralPath $stylePath -Raw | ConvertFrom-Json
        $styleText = Get-Content -LiteralPath $stylePath -Raw
        $styleTextWithoutPmtiles = $styleText -replace '(?i)pmtiles://', ''
        if ($styleTextWithoutPmtiles -match '(?i)(https?:)?//|mapbox://') { $errors.Add('Style contains a remote URL or mapbox:// reference.') }
        if ($styleText -notmatch '(?i)pmtiles://[^"]*iran\.pmtiles') { $errors.Add('Style does not reference the local iran.pmtiles archive through pmtiles://.') }
        if (-not $style.sources -or $style.sources.Count -lt 1) { $errors.Add('Style has no vector sources.') }
        if (-not $style.glyphs) { $errors.Add('Style has no local glyphs path; Persian labels may not render.') }
        if (-not $style.sprite) { $warnings.Add('Style has no sprite path. This is acceptable only if no symbol layers require sprites.') }
    } catch { $errors.Add("Style JSON cannot be parsed: $($_.Exception.Message)") }
}
foreach ($optional in @('search.sqlite','boundaries.pmtiles','transport.pmtiles')) {
    if (-not (Test-Path -LiteralPath (Join-Path $DataDir $optional) -PathType Leaf)) { $warnings.Add("Optional national dataset not installed: $optional") }
}
if ($errors.Count -gt 0) {
    Write-Host 'OFFLINE MAP PACKAGE: FAIL' -ForegroundColor Red
    $errors | ForEach-Object { Write-Host " - $_" -ForegroundColor Red }
} else {
    Write-Host 'OFFLINE MAP BASE PACKAGE: PASS' -ForegroundColor Green
}
if ($warnings.Count -gt 0) {
    Write-Host 'INCOMPLETE COVERAGE / OPTIONAL DATA:' -ForegroundColor Yellow
    $warnings | ForEach-Object { Write-Host " - $_" -ForegroundColor Yellow }
}
if ($errors.Count -gt 0) { exit 1 }
)) { $errors.Add("Custom schema layer missing: $layerName") }
    }
}
if (Test-Path -LiteralPath $stylePath -PathType Leaf) {
    try {
        $style = Get-Content -LiteralPath $stylePath -Raw | ConvertFrom-Json
        $styleText = Get-Content -LiteralPath $stylePath -Raw
        $styleTextWithoutPmtiles = $styleText -replace '(?i)pmtiles://', ''
        if ($styleTextWithoutPmtiles -match '(?i)(https?:)?//|mapbox://') { $errors.Add('Style contains a remote URL or mapbox:// reference.') }
        if ($styleText -notmatch '(?i)pmtiles://[^"]*iran\.pmtiles') { $errors.Add('Style does not reference the local iran.pmtiles archive through pmtiles://.') }
        if (-not $style.sources -or $style.sources.Count -lt 1) { $errors.Add('Style has no vector sources.') }
        if (-not $style.glyphs) { $errors.Add('Style has no local glyphs path; Persian labels may not render.') }
        if (-not $style.sprite) { $warnings.Add('Style has no sprite path. This is acceptable only if no symbol layers require sprites.') }
    } catch { $errors.Add("Style JSON cannot be parsed: $($_.Exception.Message)") }
}
foreach ($optional in @('search.sqlite','boundaries.pmtiles','transport.pmtiles')) {
    if (-not (Test-Path -LiteralPath (Join-Path $DataDir $optional) -PathType Leaf)) { $warnings.Add("Optional national dataset not installed: $optional") }
}
if ($errors.Count -gt 0) {
    Write-Host 'OFFLINE MAP PACKAGE: FAIL' -ForegroundColor Red
    $errors | ForEach-Object { Write-Host " - $_" -ForegroundColor Red }
} else {
    Write-Host 'OFFLINE MAP BASE PACKAGE: PASS' -ForegroundColor Green
}
if ($warnings.Count -gt 0) {
    Write-Host 'INCOMPLETE COVERAGE / OPTIONAL DATA:' -ForegroundColor Yellow
    $warnings | ForEach-Object { Write-Host " - $_" -ForegroundColor Yellow }
}
if ($errors.Count -gt 0) { exit 1 }
