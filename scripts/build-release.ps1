param(
    [string]$Version = '1.1.0'
)

$ErrorActionPreference = 'Stop'
$php = Get-Command php -ErrorAction Stop
& $php.Source (Join-Path $PSScriptRoot 'build-release.php') $Version
if ($LASTEXITCODE -ne 0) {
    throw "Release builder failed with exit code $LASTEXITCODE"
}
