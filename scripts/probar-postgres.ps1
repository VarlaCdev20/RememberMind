param(
    [string]$PostgresBin = (Join-Path $env:ProgramFiles 'PostgreSQL/18/bin'),
    [string]$Filtro = 'AccesoDashboardPorRolTest|AuthenticationTest|BddOperativaV2Test|ContratoPersonalInstitucionalTest|ContratoDocumentacionUsuarioTest|ContratoSeguridadCuentaTest|FichaCuidadosTest|CargaConocimientoOrionTest|ConocimientoPanelTest|RolesVistasPermisosMatrixTest|Fase3AdmisionAtomicidadTest|AlojamientoBloqueoPostgresTest|Fase3ConcurrenciaCamaPostgresTest|PersistenciaExpertaTest|ResultadosExpertoResidenteTest|PaqueteInvestigadoCOGMEMTest|DolorMigrationTest',
    [string]$Informe,
    [string]$Log
)

$ErrorActionPreference = 'Stop'
$repo = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
foreach ($archivo in @('initdb.exe', 'pg_ctl.exe', 'psql.exe')) {
    if (!(Test-Path -LiteralPath (Join-Path $PostgresBin $archivo))) { throw "Falta PostgreSQL: $archivo" }
}
$idPrueba = [guid]::NewGuid().ToString('N')
$baseTemporal = [IO.Path]::GetFullPath([IO.Path]::GetTempPath()).TrimEnd('\', '/')
$cluster = Join-Path $baseTemporal "remembermind-pg-test-$idPrueba"
if (Test-Path -LiteralPath $cluster) { throw 'No se reutiliza un cluster previo.' }
New-Item -ItemType Directory -Path $cluster | Out-Null
$datos = Join-Path $cluster 'data'
$dbPrueba = 'remembermind_test_' + $idPrueba.Substring(0,8) + '_testing'
$runnerPrueba = $dbPrueba + '_runner'
$passwordPrueba = [guid]::NewGuid().ToString('N')
$listenerPrueba = [Net.Sockets.TcpListener]::new([Net.IPAddress]::Loopback, 0)
$listenerPrueba.Start()
$puertoPrueba = $listenerPrueba.LocalEndpoint.Port
$listenerPrueba.Stop()
if (!$Informe) { $Informe = Join-Path $baseTemporal "remembermind-pg-$idPrueba.junit.xml" }
if (!$Log) { $Log = Join-Path $baseTemporal "remembermind-pg-$idPrueba.log" }
$variablesAnteriores = @{}
foreach ($nombre in @('DB_DATABASE','DB_USERNAME','DB_PASSWORD','DB_HOST','DB_PORT','LARAVEL_STORAGE_PATH')) {
    $variablesAnteriores[$nombre] = [Environment]::GetEnvironmentVariable($nombre, 'Process')
}
$iniciado = $false
$puedeEliminar = $true
try {
    & (Join-Path $PostgresBin 'initdb.exe') -D $datos -U remembermind_test_admin -A trust --encoding=UTF8 --locale=C *> (Join-Path $cluster 'initdb.log')
    if ($LASTEXITCODE -ne 0) { throw 'Falló la creación del cluster desechable.' }
    & (Join-Path $PostgresBin 'pg_ctl.exe') -D $datos -l (Join-Path $cluster 'server.log') -o "-h 127.0.0.1 -p $puertoPrueba" -w start
    if ($LASTEXITCODE -ne 0) { throw 'No arrancó el servidor de pruebas.' }
    $iniciado = $true
    @"
CREATE ROLE $runnerPrueba LOGIN PASSWORD '$passwordPrueba' NOSUPERUSER NOCREATEDB NOCREATEROLE;
CREATE DATABASE $dbPrueba OWNER $runnerPrueba;
REVOKE CONNECT ON DATABASE $dbPrueba FROM PUBLIC;
GRANT CONNECT ON DATABASE $dbPrueba TO $runnerPrueba;
"@ | & (Join-Path $PostgresBin 'psql.exe') -h 127.0.0.1 -p $puertoPrueba -U remembermind_test_admin -d postgres -v ON_ERROR_STOP=1
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo crear la base/runner propios.' }
    $env:DB_DATABASE = $dbPrueba
    $env:DB_USERNAME = $runnerPrueba
    $env:DB_PASSWORD = $passwordPrueba
    $env:DB_HOST = '127.0.0.1'
    $env:DB_PORT = [string]$puertoPrueba
    $env:LARAVEL_STORAGE_PATH = Join-Path $cluster 'storage'
    foreach ($subcarpeta in @('app/private','app/public','framework/cache/data','framework/sessions','framework/views','logs')) {
        New-Item -ItemType Directory -Force -Path (Join-Path $env:LARAVEL_STORAGE_PATH $subcarpeta) | Out-Null
    }
    Push-Location -LiteralPath $repo
    try {
        & php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never -c phpunit.postgres.xml --filter $Filtro --log-junit $Informe *> $Log
        $resultadoPruebas = $LASTEXITCODE
        Get-Content -LiteralPath $Log -Tail 30
    } finally { Pop-Location }
    Write-Output "Evidencia: $Informe; $Log"
    if ($resultadoPruebas -ne 0) { throw "Pruebas PostgreSQL fallidas: $resultadoPruebas" }
} finally {
    foreach ($nombre in $variablesAnteriores.Keys) {
        if ($null -eq $variablesAnteriores[$nombre]) {
            Remove-Item -LiteralPath "Env:$nombre" -ErrorAction SilentlyContinue
        } else {
            [Environment]::SetEnvironmentVariable($nombre, [string]$variablesAnteriores[$nombre], 'Process')
        }
    }
    if ($iniciado -or (Test-Path -LiteralPath (Join-Path $datos 'postmaster.pid'))) {
        & (Join-Path $PostgresBin 'pg_ctl.exe') -D $datos -m fast -w stop
        if ($LASTEXITCODE -ne 0) {
            $puedeEliminar = $false
            Write-Warning "No se eliminó el cluster porque no se confirmó su parada: $cluster"
        }
    }
    $resuelto = [IO.Path]::GetFullPath($cluster)
    $esRaizPropia = (Test-Path -LiteralPath $resuelto) -and
        (((Get-Item -LiteralPath $resuelto -Force).Attributes -band [IO.FileAttributes]::ReparsePoint) -eq 0)
    if ($puedeEliminar -and $esRaizPropia -and $resuelto -eq (Join-Path $baseTemporal "remembermind-pg-test-$idPrueba") -and
        [IO.Path]::GetDirectoryName($resuelto) -eq $baseTemporal) {
        Remove-Item -LiteralPath $resuelto -Recurse -Force
    }
}
