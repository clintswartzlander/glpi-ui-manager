param(
    [string]$Version = '1.0.0'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$release = Join-Path $root 'release'
$packageRoot = Join-Path $release 'uimanager'
$zip = Join-Path $release "uimanager-$Version.zip"

if (Test-Path -LiteralPath $packageRoot) {
    Remove-Item -LiteralPath $packageRoot -Recurse -Force
}
if (Test-Path -LiteralPath $zip) {
    Remove-Item -LiteralPath $zip -Force
}
New-Item -ItemType Directory -Path $packageRoot -Force | Out-Null

foreach ($entry in Get-Content -LiteralPath (Join-Path $PSScriptRoot 'package-files.txt')) {
    $source = Join-Path $root $entry
    Copy-Item -LiteralPath $source -Destination $packageRoot -Recurse
}

Compress-Archive -LiteralPath $packageRoot -DestinationPath $zip -CompressionLevel Optimal
Write-Output $zip
