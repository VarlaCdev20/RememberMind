<?php

declare(strict_types=1);

/**
 * Ensayo de lectura: proyecta dos relaciones 0..1 a evaluacion_criterios y
 * reconstruye todas las filas originales. No ejecuta DDL ni escrituras.
 *
 * Uso: php prototipos/orion-reduccion-100/ensayar-criterios.php
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$raiz = dirname(__DIR__, 2);
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $error): void {
    fwrite(STDERR, 'FAIL ensayo de consolidación: '.$error->getMessage().PHP_EOL);
    exit(1);
});

if (! in_array(DB::connection()->getDriverName(), ['pgsql', 'sqlite'], true)) {
    throw new RuntimeException('El ensayo solo admite PostgreSQL o SQLite.');
}
$baseDatos = DB::connection()->getDatabaseName();
if (! app()->environment(['local', 'testing'])
    || ! ($baseDatos === 'remembermind_dev'
        || (bool) preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', $baseDatos)
        || (DB::connection()->getDriverName() === 'sqlite' && $baseDatos === ':memory:'))) {
    throw new RuntimeException('El ensayo solo admite la base local de desarrollo o una base desechable de pruebas.');
}

$tablas = [
    'evaluacion_criterios' => 'cod_evaluacion_criterio',
    'resultados_criterio' => 'cod_resultado_criterio',
    'trazas_inferencia' => 'cod_traza_inferencia',
];
$columnas = [];
$originales = [];
foreach ($tablas as $tabla => $pk) {
    $columnas[$tabla] = Schema::getColumnListing($tabla);
    $originales[$tabla] = array_map(static fn (object $fila): array => (array) $fila,
        DB::table($tabla)->orderBy($pk)->get()->all());
}

$normalizar = static function (array $filas, string $pk): array {
    foreach ($filas as &$fila) {
        ksort($fila, SORT_STRING);
    }
    unset($fila);
    usort($filas, static fn (array $a, array $b): int => strcmp((string) $a[$pk], (string) $b[$pk]));

    return $filas;
};

$base = array_fill_keys(array_column($originales['evaluacion_criterios'], 'cod_evaluacion_criterio'), true);
$porPadre = [];
foreach (['resultados_criterio', 'trazas_inferencia'] as $tabla) {
    foreach ($originales[$tabla] as $fila) {
        $padre = $fila['cod_evaluacion_criterio'];
        if (! isset($base[$padre]) || isset($porPadre[$tabla][$padre])) {
            throw new RuntimeException("$tabla tiene un vínculo huérfano o duplicado.");
        }
        $porPadre[$tabla][$padre] = $fila;
    }
}

$proyeccion = [];
foreach ($originales['evaluacion_criterios'] as $padre) {
    $id = $padre['cod_evaluacion_criterio'];
    $fila = $padre;
    foreach (['resultados_criterio' => 'resultado', 'trazas_inferencia' => 'traza'] as $tabla => $prefijo) {
        $hija = $porPadre[$tabla][$id] ?? null;
        $fila[$prefijo.'_presente'] = $hija !== null;
        foreach ($columnas[$tabla] as $columna) {
            if ($columna !== 'cod_evaluacion_criterio') {
                $fila[$prefijo.'__'.$columna] = $hija[$columna] ?? null;
            }
        }
    }
    $proyeccion[] = $fila;
}

$reconstruidas = array_fill_keys(array_keys($tablas), []);
foreach ($proyeccion as $fila) {
    $padre = [];
    foreach ($columnas['evaluacion_criterios'] as $columna) {
        $padre[$columna] = $fila[$columna];
    }
    $reconstruidas['evaluacion_criterios'][] = $padre;
    foreach (['resultados_criterio' => 'resultado', 'trazas_inferencia' => 'traza'] as $tabla => $prefijo) {
        if (! $fila[$prefijo.'_presente']) {
            continue;
        }
        $hija = ['cod_evaluacion_criterio' => $fila['cod_evaluacion_criterio']];
        foreach ($columnas[$tabla] as $columna) {
            if ($columna !== 'cod_evaluacion_criterio') {
                $hija[$columna] = $fila[$prefijo.'__'.$columna];
            }
        }
        $reconstruidas[$tabla][] = $hija;
    }
}

foreach ($tablas as $tabla => $pk) {
    if ($normalizar($originales[$tabla], $pk) !== $normalizar($reconstruidas[$tabla], $pk)) {
        throw new RuntimeException("La reconstrucción de $tabla no coincide con el origen.");
    }
}

printf("PASS lectura y reconstrucción exacta: %d criterios, %d resultados, %d trazas.\n",
    count($originales['evaluacion_criterios']), count($originales['resultados_criterio']), count($originales['trazas_inferencia']));
echo "Alcance: valores actuales únicamente; no acredita FK entrantes, 4FN, transición, concurrencia ni autorización estructural.\n";
