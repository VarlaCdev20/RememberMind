param(
    [Parameter(Mandatory=$true)][string]$Filtro,
    [Parameter(Mandatory=$true)][string]$Informe,
    [Parameter(Mandatory=$true)][string]$Log
)

$ErrorActionPreference = 'Stop'
$repo = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$baseTemporal = [IO.Path]::GetFullPath([IO.Path]::GetTempPath()).TrimEnd('\', '/')
$identificador = [guid]::NewGuid().ToString('N')
$almacenamientoPrueba = [IO.Path]::GetFullPath((Join-Path $baseTemporal "remembermind-sqlite-test-$identificador"))
if (Test-Path -LiteralPath $almacenamientoPrueba) { throw 'No se reutiliza un almacenamiento anterior.' }
$almacenamientoAnterior = [Environment]::GetEnvironmentVariable('LARAVEL_STORAGE_PATH', 'Process')
try {
    foreach ($subcarpeta in @('app/private','app/public','framework/cache/data','framework/sessions','framework/views','logs')) {
        New-Item -ItemType Directory -Force -Path (Join-Path $almacenamientoPrueba $subcarpeta) | Out-Null
    }
    $env:LARAVEL_STORAGE_PATH = $almacenamientoPrueba
    Push-Location -LiteralPath $repo
    try {
        # phpunit.xml + Tests/Support/EntornoPruebas exigen testing y SQLite :memory:.
        # Storage::fake utiliza ahora una raíz distinta por proceso de ejecución.
        & php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never -c phpunit.xml --filter $Filtro --exclude-group postgresql --log-junit $Informe *> $Log
        $resultadoPruebas = $LASTEXITCODE
        Get-Content -LiteralPath $Log -Tail 20
    } finally { Pop-Location }
    Write-Output "Evidencia: $Informe; $Log"
    if ($resultadoPruebas -ne 0) { throw "Pruebas SQLite fallidas: $resultadoPruebas" }
} finally {
    [Environment]::SetEnvironmentVariable('LARAVEL_STORAGE_PATH', $almacenamientoAnterior, 'Process')
    $resuelto = [IO.Path]::GetFullPath($almacenamientoPrueba)
    if ((Test-Path -LiteralPath $resuelto) -and
        $resuelto -eq (Join-Path $baseTemporal "remembermind-sqlite-test-$identificador") -and
        [IO.Path]::GetDirectoryName($resuelto) -eq $baseTemporal -and
        (((Get-Item -LiteralPath $resuelto -Force).Attributes -band [IO.FileAttributes]::ReparsePoint) -eq 0)) {
        Remove-Item -LiteralPath $resuelto -Recurse -Force
    }
}
