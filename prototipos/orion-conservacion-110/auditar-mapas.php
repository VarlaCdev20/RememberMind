<?php

declare(strict_types=1);
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto;

/** Auditoría estática reproducible: no conecta ni escribe en una base de datos. */
function csv(string $archivo): array
{
    $f = fopen($archivo, 'rb') ?: throw new RuntimeException('Archivo ausente: '.$archivo);
    $encabezado = fgetcsv($f) ?: throw new RuntimeException('CSV sin encabezado: '.$archivo);
    $filas = [];
    while (($valores = fgetcsv($f)) !== false) {
        $filas[] = array_combine($encabezado, $valores);
    }
    fclose($f);

    return $filas;
}

$raiz = dirname(__DIR__, 2);
$origen = $raiz.'/prototipos/orion-conservacion-100/';
$esquema = json_decode(file_get_contents(__DIR__.'/inventario-esquema-sqlite.json'), true, flags: JSON_THROW_ON_ERROR);
if (($esquema['motor'] ?? null) !== 'sqlite' || ($esquema['base'] ?? null) !== ':memory:' || count($esquema['tablas']) !== 120) {
    throw new RuntimeException('Se requiere inventario migrado completo en SQLite desechable.');
}
require $raiz.'/vendor/autoload.php';
$inventario = InventarioExperto::class;
$expertas = array_unique(array_merge($inventario::TABLAS, $inventario::CONSERVACION, $inventario::REVISION_PROFESIONAL));
$tecnicas = ['activity_log', 'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs', 'migrations',
    'model_has_permissions', 'model_has_roles', 'password_reset_tokens', 'permissions', 'personal_access_tokens',
    'role_has_permissions', 'roles', 'secuencias_codigos', 'sessions'];
if (count($expertas) !== 29 || count(array_intersect($expertas, array_keys($esquema['tablas']))) !== 29
    || count(array_intersect($tecnicas, array_keys($esquema['tablas']))) !== 16) {
    throw new RuntimeException('El inventario instalado no coincide con 29 expertas y 16 técnicas.');
}
$tablaOut = fopen(__DIR__.'/inventario-120-tablas.csv', 'wb') ?: throw new RuntimeException('No se pudo crear inventario.');
fputcsv($tablaOut, ['tabla', 'grupo', 'columnas', 'fk_salientes', 'fk_entrantes']);
foreach ($esquema['tablas'] as $tabla => $definicion) {
    fputcsv($tablaOut, [$tabla, in_array($tabla, $expertas, true) ? 'EXPERTA' : (in_array($tabla, $tecnicas, true) ? 'TECNICA' : 'OPERATIVA'),
        count($definicion['columnas']), count($definicion['fks']), count($definicion['consumidores_fk'])]);
}
fclose($tablaOut);
$granos = csv($origen.'mapa-granos-candidato.csv');
$campos = csv($origen.'mapa-campos-candidato.csv');
$modelos = array_unique(array_column($granos, 'modelo_origen'));
if (count($granos) !== 51 || count($modelos) !== 39 || count($campos) !== 783) {
    throw new RuntimeException('La matriz de entrada cambió: verificar el contrato antes de auditar.');
}

$conteosCampos = [];
foreach ($campos as $campo) {
    $modelo = $campo['modelo_origen'];
    $conteosCampos[$modelo][$campo['forma']] = ($conteosCampos[$modelo][$campo['forma']] ?? 0) + 1;
}
$salida = [];
foreach ($granos as $grano) {
    $modelo = $grano['modelo_origen'];
    $tabla = $grano['tabla_candidata'];
    $razones = [];
    if (str_starts_with($grano['estado'], 'BRECHA_')) {
        $razones[] = $grano['estado'];
    }
    if (! isset($esquema['tablas'][$tabla])) {
        $razones[] = 'TABLA_CANDIDATA_NO_INSTALADA';
    }
    if ($tabla === 'relaciones_semanticas' && in_array($modelo, ['m008', 'm009', 'm015', 'm016'], true)) {
        $razones[] = 'DEFINICION_SEMANTICA_NO_ES_RELACION_ADQUIRIDA';
    }
    if ($tabla === 'evidencias_evaluacion' && in_array($modelo, ['m002', 'm014', 'm017', 'm019', 'm020', 'm022'], true)) {
        $razones[] = 'EVIDENCIA_EXTRAIDA_REQUIERE_MAPEO_Y_REGISTRO_FUENTE';
    }
    if ($tabla === 'hechos_instantanea_experta' && in_array($modelo, ['m006', 'm026'], true)) {
        $razones[] = 'HECHO_ORION_SNAP_2_NO_ES_RESPUESTA_BRUTA';
    }
    if (str_starts_with($modelo, 'k_')) {
        $razones[] = 'DEFINICION_REQUIERE_MEMBRESIA_Y_SELLADO_HISTORICOS';
    }
    $salida[] = [
        'modelo' => $modelo,
        'ruta' => $grano['ruta_coleccion'],
        'grano_ensayo' => $grano['tabla_ensayo_0_2'],
        'destino_candidato' => $tabla,
        'variante' => $grano['variante_cerrada_propuesta'],
        'campos_modelo' => array_sum($conteosCampos[$modelo] ?? []),
        'escalares_modelo' => $conteosCampos[$modelo]['escalar'] ?? 0,
        'destino_instalado' => isset($esquema['tablas'][$tabla]),
        'columnas_destino' => count($esquema['tablas'][$tabla]['columnas'] ?? []),
        'fk_entrantes_destino' => count($esquema['tablas'][$tabla]['consumidores_fk'] ?? []),
        'brechas' => implode('|', array_unique($razones)),
    ];
}

$out = fopen(__DIR__.'/matriz-auditoria-rutas.csv', 'wb') ?: throw new RuntimeException('No se pudo crear matriz.');
fputcsv($out, array_keys($salida[0]));
foreach ($salida as $fila) {
    fputcsv($out, array_values($fila));
}
fclose($out);

$resumen = [
    'tablas_fisicas_migradas_en_memoria' => count($esquema['tablas']),
    'tablas_negocio' => count($esquema['tablas']) - count($tecnicas),
    'tablas_operativas' => count($esquema['tablas']) - count($tecnicas) - count($expertas),
    'tablas_expertas' => count($expertas),
    'tablas_tecnicas' => count($tecnicas),
    'modelos_distintos' => count($modelos),
    'rutas_coleccion' => count($granos),
    'rutas_campo' => count($campos),
    'rutas_sin_destino_instalado' => count(array_filter($salida, static fn ($f) => ! $f['destino_instalado'])),
    'rutas_con_brechas' => count(array_filter($salida, static fn ($f) => $f['brechas'] !== '')),
    'estados_originales' => array_count_values(array_column($granos, 'estado')),
];
file_put_contents(__DIR__.'/resumen-auditoria.json', json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
echo json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), "\n";
