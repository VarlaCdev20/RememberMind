<?php

declare(strict_types=1);

use Database\Seeders\EscenariosDclResidentesSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$opciones = getopt('', ['aplicar', 'base:', 'usuario:', 'area:']);
if (! isset($opciones['aplicar'], $opciones['base'], $opciones['usuario'], $opciones['area'])) {
    fwrite(STDERR, "Uso: php scripts/cargar-escenarios-dcl.php --aplicar --base=remembermind_dev --usuario=COD_USUARIO --area=COD_AREA\n");
    exit(2);
}
if (! $app->environment('local') || DB::getDriverName() !== 'pgsql'
    || DB::connection()->getDatabaseName() !== 'remembermind_dev' || $opciones['base'] !== 'remembermind_dev'
    || ! in_array(DB::connection()->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('El instalador solo acepta la copia local autorizada remembermind_dev.');
}
$tablasAntes = Schema::getTableListing();
// Huellas físicas efímeras, también para los bloques expertos consolidados.
// No se serializan registros clínicos en un informe o archivo de código.
$huella = static function (array $fila): string {
    ksort($fila);

    return hash('sha256', json_encode($fila, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
};
$originales = $claves = $conteosAntes = [];
foreach (Schema::getTables() as $tabla) {
    $nombre = $tabla['name'];
    if (in_array($nombre, ['secuencias_codigos', 'cache', 'cache_locks', 'sessions', 'jobs', 'failed_jobs', 'job_batches'], true)) {
        continue;
    }
    $pk = collect(Schema::getIndexes($nombre))->firstWhere('primary', true)['columns'] ?? null;
    if (! $pk) {
        continue;
    }
    $claves[$nombre] = $pk;
    $originales[$nombre] = [];
    foreach (DB::table($nombre)->cursor() as $fila) {
        $fila = (array) $fila;
        $key = json_encode(array_intersect_key($fila, array_flip($pk)), JSON_THROW_ON_ERROR);
        $originales[$nombre][$key] = $huella($fila);
    }
    $conteosAntes[$nombre] = count($originales[$nombre]);
}
$secuenciasAntes = DB::table('secuencias_codigos')->pluck('ultimo_valor', 'entidad')->all();
$resultado = app(EscenariosDclResidentesSeeder::class)->ejecutar($opciones['base'], $opciones['usuario'], $opciones['area']);
$conservadas = 0;
foreach ($originales as $tabla => $hashes) {
    foreach (DB::table($tabla)->cursor() as $fila) {
        $fila = (array) $fila;
        $key = json_encode(array_intersect_key($fila, array_flip($claves[$tabla])), JSON_THROW_ON_ERROR);
        if (! isset($hashes[$key])) {
            continue;
        }
        if ($hashes[$key] !== $huella($fila)) {
            throw new RuntimeException('Cambió una fila previa de '.$tabla.'. No se certifica la conservación.');
        }
        unset($hashes[$key]);
        $conservadas++;
    }
    if ($hashes !== []) {
        throw new RuntimeException('Faltan filas previas de '.$tabla.'. No se certifica la conservación.');
    }
}
$secuenciasDespues = DB::table('secuencias_codigos')->pluck('ultimo_valor', 'entidad')->all();
foreach ($secuenciasAntes as $entidad => $valor) {
    if (! isset($secuenciasDespues[$entidad]) || $secuenciasDespues[$entidad] < $valor) {
        throw new RuntimeException('Una secuencia desapareció o retrocedió.');
    }
}
if ($tablasAntes !== Schema::getTableListing()) {
    throw new RuntimeException('El inventario de tablas cambió inesperadamente.');
}
$diferencias = [];
foreach ($conteosAntes as $tabla => $cantidad) {
    $delta = DB::table($tabla)->count() - $cantidad;
    if ($delta !== 0) {
        $diferencias[$tabla] = $delta;
    }
}
$resultado['filas_previas_conservadas'] = $conservadas;
$resultado['tablas_fisicas'] = count($tablasAntes);
$resultado['filas_nuevas_por_tabla'] = $diferencias;
$ruta = storage_path('app/private/dcl10-'.date('Ymd-His').'-resultado.json');
file_put_contents($ruta, json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), PHP_EOL;
