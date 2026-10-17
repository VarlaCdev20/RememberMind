<?php

/** Operación CLI administrada. Ensayar en una restauración antes de --aplicar. */
use App\Compartido\Infraestructura\Persistencia\RenumerarCodigos;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
ini_set('memory_limit', '768M');
$opciones = getopt('', ['base:', 'directorio:', 'respaldo:', 'aplicar']);
$db = DB::connection();
$directorio = $opciones['directorio'] ?? '';
$respaldo = $opciones['respaldo'] ?? '';
if (($opciones['base'] ?? null) !== $db->getDatabaseName() || ! is_dir($directorio)
    || ! is_file($respaldo) || filesize($respaldo) < 1) {
    throw new RuntimeException('Indica la base exacta, un directorio privado y su respaldo verificado.');
}
$aplicar = array_key_exists('aplicar', $opciones);
if ($aplicar && ! $app->isDownForMaintenance()) {
    throw new RuntimeException('Pausa la aplicación y sus workers antes de aplicar.');
}
$copias = [];
$copiar = function (string $anterior, string $nuevo) use ($aplicar, $directorio, &$copias): void {
    if (str_contains($anterior, '..') || str_contains($nuevo, '..')) {
        throw new RuntimeException('Ruta privada no válida.');
    }
    $disk = Storage::disk('local');
    if (! $disk->exists($anterior)) {
        throw new RuntimeException('Falta el archivo de origen; no convertir.');
    }
    $destino = $aplicar ? $disk->path($nuevo) : $directorio.'/archivos-ensayo/'.$nuevo;
    if (! is_dir(dirname($destino)) && ! mkdir(dirname($destino), 0700, true)) {
        throw new RuntimeException('No se pudo preparar el destino privado.');
    }
    $origen = $disk->path($anterior);
    if (! file_exists($destino) && ! copy($origen, $destino)) {
        throw new RuntimeException('No se pudo copiar el archivo privado.');
    }
    if (hash_file('sha256', $destino) !== hash_file('sha256', $origen) || filesize($destino) !== filesize($origen)) {
        throw new RuntimeException('La copia privada difiere del original.');
    }
    $copias[] = ['anterior' => $anterior, 'nuevo' => $nuevo, 'sha256' => hash_file('sha256', $destino)];
};
$resultado = app(RenumerarCodigos::class)->ejecutar($db, $directorio, ! $aplicar, $copiar);
// No imprimir códigos individuales ni datos institucionales en consola.
echo json_encode(['aplicado' => $aplicar, 'entidades' => $resultado['entidades'], 'identidades' => $resultado['identidades'],
    'relaciones_verificadas' => $resultado['fk'], 'tablas_verificadas' => $resultado['tablas_verificadas'],
    'advertencia' => $resultado['advertencia'] ?? null], JSON_THROW_ON_ERROR), PHP_EOL;
