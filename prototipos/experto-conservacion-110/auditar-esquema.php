<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// Este proceso fuerza SQLite en memoria antes de iniciar Laravel: nunca usa la BDD configurada.
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => '',
    'RM_TEST_PROFILE' => 'sqlite', 'APP_KEY' => 'base64:ABEiM0RVZneImaq7zN3u/wARIjNEVWZ3iJmqu8zd7v8='] as $clave => $valor) {
    putenv($clave.'='.$valor);
    $_ENV[$clave] = $valor;
    $_SERVER[$clave] = $valor;
}

$raiz = dirname(__DIR__, 2);
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
if ($app->configurationIsCached()) {
    throw new RuntimeException('Retire la caché de configuración antes del ensayo; no se iniciará Laravel con destino incierto.');
}
$app->make(Kernel::class)->bootstrap();

if (config('database.default') !== 'sqlite'
    || config('database.connections.sqlite.database') !== ':memory:'
    || DB::connection()->getDatabaseName() !== ':memory:') {
    throw new RuntimeException('El inventario solo puede ejecutarse en SQLite :memory:.');
}

if (Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]) !== 0) {
    throw new RuntimeException('No se pudo construir el esquema desechable: '.Artisan::output());
}

$esquema = DB::connection()->getSchemaBuilder();
$tablas = array_column($esquema->getTables(), 'name');
sort($tablas, SORT_STRING);
$salida = [];
foreach ($tablas as $tabla) {
    $salida[$tabla] = [
        'columnas' => $esquema->getColumns($tabla),
        'fks' => $esquema->getForeignKeys($tabla),
        'indices' => $esquema->getIndexes($tabla),
        'consumidores_fk' => [],
    ];
}
foreach ($salida as $tabla => $definicion) {
    foreach ($definicion['fks'] as $fk) {
        $destino = $fk['foreign_table'];
        if (isset($salida[$destino])) {
            $salida[$destino]['consumidores_fk'][] = [
                'tabla' => $tabla,
                'columnas' => $fk['columns'],
                'columnas_destino' => $fk['foreign_columns'],
            ];
        }
    }
}

echo json_encode(['motor' => 'sqlite', 'base' => ':memory:', 'tablas' => $salida],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
