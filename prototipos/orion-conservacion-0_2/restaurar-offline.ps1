param(
    [Parameter(Mandatory=$true)][string]$Copia,
    [Parameter(Mandatory=$true)][string]$Registro,
    [Parameter(Mandatory=$true)][string]$HuellaRegistro,
    [Parameter(Mandatory=$true)][string]$Destino,
    [Parameter(Mandatory=$true)][string]$HuellaCompuesta,
    [Parameter(Mandatory=$true)][string]$HuellaInstrumental,
    [Parameter(Mandatory=$true)][string]$HuellaPetersen
)

# Laboratorio OFFLINE. Registro solo de software; entradas por stdin y resultado por stdout.
# No carga Laravel, Composer, .env, SQL, PHP instalado ni archivos del checkout.
$ErrorActionPreference = 'Stop'
$env:PSModulePath = Join-Path $PSHOME 'Modules'
foreach ($digest in @($HuellaRegistro,$HuellaCompuesta,$HuellaInstrumental,$HuellaPetersen)) {
    if ($digest -cnotmatch '^[a-f0-9]{64}$') { throw 'Huella esperada inválida.' }
}
function Confirmar-Archivo([string]$Ruta,[string]$Esperada) {
    if ($Esperada -cnotmatch '^[a-f0-9]{64}$' -or !(Test-Path -LiteralPath $Ruta -PathType Leaf) -or
        (((Get-Item -LiteralPath $Ruta -Force).Attributes -band [IO.FileAttributes]::ReparsePoint) -ne 0) -or
        (Get-FileHash -LiteralPath $Ruta -Algorithm SHA256).Hash.ToLowerInvariant() -cne $Esperada) {
        throw 'Archivo de software ausente, alterado o no regular.'
    }
}
Confirmar-Archivo $Registro $HuellaRegistro
$meta = Get-Content -LiteralPath $Registro -Raw -Encoding UTF8 | ConvertFrom-Json
$origen = [IO.Path]::GetFullPath($Copia).TrimEnd('\','/')
$raiz = [IO.Path]::GetFullPath($Destino).TrimEnd('\','/')
if (Test-Path -LiteralPath $raiz) { throw 'La restauración requiere un destino nuevo.' }
foreach ($texto in @($origen,$raiz,$Registro)) {
    if ($texto.Contains('"') -or $texto -match '[\r\n]') { throw 'Ruta inválida.' }
}
Confirmar-Archivo (Join-Path $origen 'runtime.zip') $meta.huella_runtime_zip
Confirmar-Archivo (Join-Path $origen 'motor.zip') $meta.huella_zip
Confirmar-Archivo (Join-Path $origen 'EntradasCompuestas.php') $meta.huella_adaptador
Add-Type -AssemblyName System.IO.Compression.FileSystem
function Extraer-Nuevo([string]$Archivo,[string]$Carpeta,[string]$Tipo) {
    $zip = [IO.Compression.ZipFile]::OpenRead($Archivo)
    try {
        $vistas = New-Object 'System.Collections.Generic.HashSet[string]' ([StringComparer]::OrdinalIgnoreCase)
        foreach ($entrada in $zip.Entries) {
            $nombre = $entrada.FullName
            if (!$nombre -or $nombre.Contains('\') -or $nombre.Contains(':') -or $nombre.StartsWith('/') -or
                $nombre.Split('/') -contains '..' -or $nombre.Split('/') -contains '.' -or !$vistas.Add($nombre) -or
                (($entrada.ExternalAttributes -shr 16) -band 61440) -eq 40960) { throw 'Ruta de ZIP inválida.' }
            if ($Tipo -eq 'runtime' -and $nombre -cnotmatch '^(?:[A-Za-z0-9_-]+\.(?:exe|dll|txt)|ext/php_zip\.dll|replay-aislado\.php|MANIFIESTO-RUNTIME\.json)$') {
                throw 'Archivo de runtime no permitido.'
            }
            if ($Tipo -eq 'motor' -and $nombre -cnotmatch '^(?:motor-experto/src/[A-Za-z0-9_/-]+\.php|motor-experto/composer\.json|composer\.lock|motor-experto/tests/(?:independencia|orion_independencia)\.php|app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos\.json|MANIFIESTO-MOTOR\.json)$') {
                throw 'Archivo del motor no permitido.'
            }
            $resuelto = [IO.Path]::GetFullPath((Join-Path $Carpeta $nombre))
            if (!$resuelto.StartsWith($Carpeta+[IO.Path]::DirectorySeparatorChar,[StringComparison]::OrdinalIgnoreCase)) { throw 'Ruta fuera del destino.' }
        }
        New-Item -ItemType Directory -Path $Carpeta | Out-Null
        foreach ($entrada in $zip.Entries) {
            $ruta = [IO.Path]::GetFullPath((Join-Path $Carpeta $entrada.FullName))
            [IO.Directory]::CreateDirectory([IO.Path]::GetDirectoryName($ruta)) | Out-Null
            [IO.Compression.ZipFileExtensions]::ExtractToFile($entrada,$ruta,$false)
        }
    } finally { $zip.Dispose() }
}
New-Item -ItemType Directory -Path $raiz | Out-Null
$runtime = Join-Path $raiz 'runtime'
Extraer-Nuevo (Join-Path $origen 'runtime.zip') $runtime 'runtime'
# Motor se extrae en raíz nueva ya creada; el extractor exige crear su propio destino.
$motor = Join-Path $raiz 'release'
Extraer-Nuevo (Join-Path $origen 'motor.zip') $motor 'motor'
$adaptador = Join-Path $motor 'adaptador'
New-Item -ItemType Directory -Path $adaptador | Out-Null
Copy-Item -LiteralPath (Join-Path $origen 'EntradasCompuestas.php') -Destination (Join-Path $adaptador 'EntradasCompuestas.php')
Confirmar-Archivo (Join-Path $adaptador 'EntradasCompuestas.php') $meta.huella_adaptador
$manifest = Get-Content -LiteralPath (Join-Path $runtime 'MANIFIESTO-RUNTIME.json') -Raw -Encoding UTF8 | ConvertFrom-Json
foreach ($propiedad in $manifest.archivos.PSObject.Properties) {
    Confirmar-Archivo (Join-Path $runtime $propiedad.Name) $propiedad.Value
}
Confirmar-Archivo (Join-Path $runtime 'php.exe') $meta.huella_php_ejecutable
if (!$manifest.archivos.'replay-aislado.php') { throw 'Falta el lanzador retenido.' }
# Todos los includes, incluido el lanzador, deben quedar bajo una raíz restaurada.
$lanzador = Join-Path $motor 'replay-aislado.php'
Copy-Item -LiteralPath (Join-Path $runtime 'replay-aislado.php') -Destination $lanzador
Confirmar-Archivo $lanzador $manifest.archivos.'replay-aislado.php'
function Citar-Argumento([string]$Valor) { return '"'+$Valor+'"' }
$inicio = New-Object Diagnostics.ProcessStartInfo
$inicio.FileName = Join-Path $runtime 'php.exe'
$inicio.UseShellExecute = $false
$inicio.CreateNoWindow = $true
$inicio.RedirectStandardInput = $true
$inicio.RedirectStandardOutput = $true
$inicio.RedirectStandardError = $true
$inicio.WorkingDirectory = $motor
$argumentos = @('-n','-d','display_errors=stderr','-d','log_errors=0','-d',('extension_dir='+ (Join-Path $runtime 'ext')),'-d','extension=zip',$lanzador,$motor,
    (Join-Path $origen 'motor.zip'),$meta.huella_manifiesto,$HuellaCompuesta,$HuellaInstrumental,$HuellaPetersen)
$inicio.Arguments = ($argumentos | ForEach-Object { Citar-Argumento $_ }) -join ' '
$proceso = New-Object Diagnostics.Process
$proceso.StartInfo = $inicio
# .NET Framework crea StandardInput con Console.InputEncoding y escribe su preámbulo
# al activar AutoFlush. El transporte es binario: UTF-8 sin BOM evita anteponer bytes.
[Console]::InputEncoding = New-Object Text.UTF8Encoding($false)
if (!$proceso.Start()) { throw 'No se inició el runtime retenido.' }
$errores = $proceso.StandardError.ReadToEndAsync()
$salida = $proceso.StandardOutput.BaseStream.CopyToAsync([Console]::OpenStandardOutput())
try {
    [Console]::OpenStandardInput().CopyTo($proceso.StandardInput.BaseStream)
    $proceso.StandardInput.Close()
    [void]$salida.GetAwaiter().GetResult()
    $proceso.WaitForExit()
    if ($proceso.ExitCode -ne 0) { throw ('Replay rechazado: '+$errores.GetAwaiter().GetResult()) }
} finally { $proceso.Dispose() }
