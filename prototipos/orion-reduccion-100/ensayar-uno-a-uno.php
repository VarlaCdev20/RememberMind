<?php

declare(strict_types=1);

/**
 * Ensayo de solo lectura de cinco fusiones 0..1 candidatas.
 * Proyecta columnas nominales y reconstruye íntegramente las filas originales.
 * Uso: php prototipos/orion-reduccion-100/ensayar-uno-a-uno.php
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

$conexion = DB::connection();
$baseDatos = $conexion->getDatabaseName();
if (! in_array($conexion->getDriverName(), ['pgsql', 'sqlite'], true)
    || ! app()->environment(['local', 'testing'])
    || ! ($baseDatos === 'remembermind_dev'
        || (bool) preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', $baseDatos)
        || ($conexion->getDriverName() === 'sqlite' && $baseDatos === ':memory:'))) {
    throw new RuntimeException('El ensayo solo admite la base local de desarrollo o una base desechable de pruebas.');
}

$normalizar = static function (array $filas, string $pk): array {
    foreach ($filas as &$fila) {
        ksort($fila, SORT_STRING);
    }
    unset($fila);
    usort($filas, static fn (array $a, array $b): int => strcmp((string) $a[$pk], (string) $b[$pk]));

    return $filas;
};

$ensayar = static function (string $padre, string $pkPadre, array $hijas) use ($normalizar): array {
    $tablas = [$padre => $pkPadre];
    foreach ($hijas as $tabla => $configuracion) {
        $tablas[$tabla] = $configuracion['pk'];
    }
    $columnas = [];
    $originales = [];
    foreach ($tablas as $tabla => $pk) {
        $columnas[$tabla] = Schema::getColumnListing($tabla);
        $originales[$tabla] = array_map(static fn (object $fila): array => (array) $fila,
            DB::table($tabla)->orderBy($pk)->get()->all());
    }
    $idsPadre = array_fill_keys(array_column($originales[$padre], $pkPadre), true);
    $porPadre = [];
    foreach ($hijas as $tabla => $configuracion) {
        foreach ($originales[$tabla] as $fila) {
            $id = $fila[$configuracion['fk']];
            if (! isset($idsPadre[$id]) || isset($porPadre[$tabla][$id])) {
                throw new RuntimeException("$tabla tiene un vínculo huérfano o duplicado.");
            }
            $porPadre[$tabla][$id] = $fila;
        }
    }

    $proyeccion = [];
    foreach ($originales[$padre] as $filaPadre) {
        $id = $filaPadre[$pkPadre];
        $fila = $filaPadre;
        foreach ($hijas as $tabla => $configuracion) {
            $prefijo = 'hija__'.$tabla.'__';
            $hija = $porPadre[$tabla][$id] ?? null;
            $fila[$prefijo.'presente'] = $hija !== null;
            foreach ($columnas[$tabla] as $columna) {
                if ($columna !== $configuracion['fk']) {
                    $fila[$prefijo.$columna] = $hija[$columna] ?? null;
                }
            }
        }
        $proyeccion[] = $fila;
    }

    $reconstruidas = array_fill_keys(array_keys($tablas), []);
    foreach ($proyeccion as $fila) {
        $filaPadre = [];
        foreach ($columnas[$padre] as $columna) {
            $filaPadre[$columna] = $fila[$columna];
        }
        $reconstruidas[$padre][] = $filaPadre;
        foreach ($hijas as $tabla => $configuracion) {
            $prefijo = 'hija__'.$tabla.'__';
            if (! $fila[$prefijo.'presente']) {
                continue;
            }
            $hija = [$configuracion['fk'] => $fila[$pkPadre]];
            foreach ($columnas[$tabla] as $columna) {
                if ($columna !== $configuracion['fk']) {
                    $hija[$columna] = $fila[$prefijo.$columna];
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

    return array_map('count', $originales);
};

$grupos = [
    ['evaluacion_criterios', 'cod_evaluacion_criterio', [
        'resultados_criterio' => ['pk' => 'cod_resultado_criterio', 'fk' => 'cod_evaluacion_criterio'],
        'trazas_inferencia' => ['pk' => 'cod_traza_inferencia', 'fk' => 'cod_evaluacion_criterio'],
    ]],
    ['nodos_semanticos', 'cod_nodo_semantico', [
        'variables_expertas' => ['pk' => 'cod_variable_experta', 'fk' => 'cod_nodo_semantico'],
        'criterios_dominios_resultado' => ['pk' => 'cod_criterio_dominio_resultado', 'fk' => 'cod_nodo_criterio'],
    ]],
    ['evaluaciones_expertas', 'cod_evaluacion_experta', [
        'clasificaciones_dcl_instantanea' => ['pk' => 'cod_clasificacion_dcl_instantanea', 'fk' => 'cod_evaluacion_experta'],
    ]],
];

foreach ($grupos as [$padre, $pk, $hijas]) {
    $conteos = $ensayar($padre, $pk, $hijas);
    echo 'PASS '.implode(', ', array_map(static fn (string $tabla, int $cantidad): string => "$tabla=$cantidad",
        array_keys($conteos), array_values($conteos))).PHP_EOL;
}
echo "Alcance: filas actuales únicamente; faltan FK entrantes, 4FN, transición, concurrencia, reversión y aprobación.\n";
