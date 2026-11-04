<?php

declare(strict_types=1);

/**
 * Ensayo de solo lectura de una unión tipada de dos vínculos de evidencia.
 * No crea tablas, no cambia datos y no autoriza una migración.
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$raiz = dirname(__DIR__, 2);
require $raiz.'/vendor/autoload.php';
$app = require $raiz.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$conexion = DB::connection();
$base = $conexion->getDatabaseName();
if (! in_array($conexion->getDriverName(), ['pgsql', 'sqlite'], true)
    || ! app()->environment(['local', 'testing'])
    || ! ($base === 'remembermind_dev'
        || (bool) preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', $base)
        || ($conexion->getDriverName() === 'sqlite' && $base === ':memory:'))) {
    throw new RuntimeException('Solo se admite la base local de desarrollo o una base desechable.');
}

$criterios = array_map(static fn (object $fila): array => (array) $fila,
    DB::table('evidencias_criterio_evaluacion')->orderBy('cod_evidencia_criterio')->get()->all());
$soportes = array_map(static fn (object $fila): array => (array) $fila,
    DB::table('evidencias_soporte_condicion')->orderBy('cod_evidencia_soporte_condicion')->get()->all());

$idsEvidencia = array_fill_keys(DB::table('evidencias_evaluacion')->pluck('cod_evidencia_evaluacion')->all(), true);
$idsHecho = array_fill_keys(DB::table('hechos_instantanea_experta')->pluck('cod_hecho_instantanea')->all(), true);
$idsCriterio = array_fill_keys(DB::table('evaluacion_criterios')->pluck('cod_evaluacion_criterio')->all(), true);
$idsCondicion = array_fill_keys(DB::table('evaluaciones_condiciones_regla')->pluck('cod_evaluacion_condicion')->all(), true);
$idsUnion = [];
$filas = [];
foreach ($criterios as $fila) {
    if ((isset($fila['cod_evidencia_evaluacion']) === isset($fila['cod_hecho_instantanea']))
        || ($fila['cod_evidencia_evaluacion'] !== null && ! isset($idsEvidencia[$fila['cod_evidencia_evaluacion']]))
        || ($fila['cod_hecho_instantanea'] !== null && ! isset($idsHecho[$fila['cod_hecho_instantanea']]))
        || ! isset($idsCriterio[$fila['cod_evaluacion_criterio']])) {
        throw new RuntimeException('Participación en criterio con referencia ausente.');
    }
    $id = 'C:'.$fila['cod_evidencia_criterio'];
    if (isset($idsUnion[$id])) {
        throw new RuntimeException('Identidad de participación duplicada.');
    }
    $idsUnion[$id] = true;
    $filas[] = ['tipo' => 'CRITERIO', 'identidad_logica' => $id,
        'cod_evidencia_criterio' => $fila['cod_evidencia_criterio'],
        'cod_evidencia_soporte_condicion' => null,
        'cod_evidencia_evaluacion' => $fila['cod_evidencia_evaluacion'],
        'cod_hecho_instantanea' => $fila['cod_hecho_instantanea'],
        'cod_soporte_sintetico' => $fila['cod_soporte_sintetico'],
        'cod_evaluacion_criterio' => $fila['cod_evaluacion_criterio'],
        'cod_evaluacion_condicion' => null,
        'rol_en_criterio' => $fila['rol_en_criterio'],
        'estado_participacion' => $fila['estado_participacion'],
        'justificacion' => $fila['justificacion'],
        'fecha_hora_vinculacion' => $fila['fecha_hora_vinculacion'],
        'orden_conservacion' => $fila['orden_conservacion']];
}
foreach ($soportes as $fila) {
    if (! isset($idsEvidencia[$fila['cod_evidencia_evaluacion']])
        || ! isset($idsCondicion[$fila['cod_evaluacion_condicion']])) {
        throw new RuntimeException('Soporte de condición con referencia ausente.');
    }
    $id = 'S:'.$fila['cod_evidencia_soporte_condicion'];
    if (isset($idsUnion[$id])) {
        throw new RuntimeException('Identidad de soporte duplicada.');
    }
    $idsUnion[$id] = true;
    $filas[] = ['tipo' => 'CONDICION', 'identidad_logica' => $id,
        'cod_evidencia_criterio' => null,
        'cod_evidencia_soporte_condicion' => $fila['cod_evidencia_soporte_condicion'],
        'cod_evidencia_evaluacion' => $fila['cod_evidencia_evaluacion'],
        'cod_hecho_instantanea' => null, 'cod_soporte_sintetico' => null,
        'cod_evaluacion_criterio' => null,
        'cod_evaluacion_condicion' => $fila['cod_evaluacion_condicion'],
        'rol_en_criterio' => null, 'estado_participacion' => null,
        'justificacion' => null, 'fecha_hora_vinculacion' => null,
        'orden_conservacion' => null];
}

$reconstruir = static function (array $filas, string $tipo): array {
    $columnas = $tipo === 'CRITERIO'
        ? ['cod_evidencia_criterio', 'cod_evaluacion_criterio', 'cod_evidencia_evaluacion',
            'cod_hecho_instantanea', 'cod_soporte_sintetico',
            'rol_en_criterio', 'estado_participacion', 'justificacion', 'fecha_hora_vinculacion', 'orden_conservacion']
        : ['cod_evidencia_soporte_condicion', 'cod_evaluacion_condicion', 'cod_evidencia_evaluacion'];
    $resultado = [];
    foreach ($filas as $fila) {
        if ($fila['tipo'] !== $tipo) {
            continue;
        }
        $original = array_intersect_key($fila, array_flip($columnas));
        ksort($original, SORT_STRING);
        $resultado[] = $original;
    }
    $pk = $columnas[0];
    usort($resultado, static fn (array $a, array $b): int => strcmp($a[$pk], $b[$pk]));

    return $resultado;
};
$normalizar = static function (array $filas, string $pk): array {
    foreach ($filas as &$fila) {
        ksort($fila, SORT_STRING);
    }
    unset($fila);
    usort($filas, static fn (array $a, array $b): int => strcmp($a[$pk], $b[$pk]));

    return $filas;
};
if ($reconstruir($filas, 'CRITERIO') !== $normalizar($criterios, 'cod_evidencia_criterio')
    || $reconstruir($filas, 'CONDICION') !== $normalizar($soportes, 'cod_evidencia_soporte_condicion')) {
    throw new RuntimeException('La reconstrucción exacta de ambos vínculos falló.');
}

echo 'PASS participaciones='.count($criterios).', soportes='.count($soportes)
    .', filas_union_tipadas='.count($filas).PHP_EOL;
echo "Alcance: filas actuales y FK existentes; faltan DDL, 4FN, concurrencia, reversión y aprobación.\n";
