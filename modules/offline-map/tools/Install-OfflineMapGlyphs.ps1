[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][string]$GlyphArchive,
    [string]$FontStack = 'Noto Sans Arabic Regular',
    [string]$FontsDir = (Join-Path $PSScriptRoot '..\data\fonts')
)
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$GlyphArchive = [IO.Path]::GetFullPath($GlyphArchive)
$FontsDir = [IO.Path]::GetFullPath($FontsDir)
if (-not (Test-Path -LiteralPath $GlyphArchive -PathType Leaf)) { throw "Glyph ZIP not found: $GlyphArchive" }
$temp = Join-Path $env:TEMP ('map-glyphs-' + [guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Force -Path $temp | Out-Null
try {
    Expand-Archive -LiteralPath $GlyphArchive -DestinationPath $temp -Force
    $stack = Get-ChildItem -LiteralPath $temp -Directory -Recurse | Where-Object { $_.Name -eq $FontStack } | Select-Object -First 1
    if (-not $stack) {
        $allPbf = Get-ChildItem -LiteralPath $temp -Filter '*.pbf' -File -Recurse
        if ($allPbf.Count -gt 0 -and (Split-Path $allPbf[0].DirectoryName -Leaf) -eq $FontStack) { $stack = Get-Item $allPbf[0].DirectoryName }
    }
    if (-not $stack) { throw "Font stack '$FontStack' not found in archive." }
    $requiredRanges = @('0-255.pbf','1536-1791.pbf')
    foreach ($range in $requiredRanges) {
        if (-not (Test-Path (Join-Path $stack.FullName $range))) { throw "Required glyph range missing: $range" }
    }
    $destination = Join-Path $FontsDir $FontStack
    New-Item -ItemType Directory -Force -Path $destination | Out-Null
    Get-ChildItem -LiteralPath $stack.FullName -Filter '*.pbf' -File | Copy-Item -Destination $destination -Force
    $license = Get-ChildItem -LiteralPath $temp -File -Recurse | Where-Object { $_.Name -match '^(OFL|LICENSE)(\.txt)?$' } | Select-Object -First 1
    if ($license) { Copy-Item $license.FullName (Join-Path $destination 'LICENSE.txt') -Force }
    Write-Host "Glyph stack installed: $destination" -ForegroundColor Green
} finally {
    Remove-Item -LiteralPath $temp -Recurse -Force -ErrorAction SilentlyContinue
}
