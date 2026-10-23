<?php

declare(strict_types=1);

/**
 * Gate estático de las 51 colecciones: solo lee inventarios de SQLite en memoria.
 * Una prueba parcial no convierte una ruta en conservada institucionalmente.
 */
$base = __DIR__;
$leerCsv = static function (string $ruta): array {
    $archivo = fopen($ruta, 'rb') ?: throw new RuntimeException("No se puede leer $ruta");
    $cabecera = fgetcsv($archivo) ?: throw new RuntimeException("CSV vacío: $ruta");
    $filas = [];
    while (($valores = fgetcsv($archivo)) !== false) {
        $filas[] = array_combine($cabecera, $valores);
    }
    fclose($archivo);

    return $filas;
};

$rutas = $leerCsv($base.'/matriz-auditoria-rutas.csv');
$campos = $leerCsv($base.'/../orion-conservacion-100/mapa-campos-candidato.csv');
$inventario = json_decode(file_get_contents($base.'/inventario-esquema-sqlite.json'), true, flags: JSON_THROW_ON_ERROR);
$mapa = require $base.'/../orion-conservacion-0_2/mapa-cerrado.php';
if (count($rutas) !== 51 || count($campos) !== 783 || count($mapa['modelos']) !== 39
    || ($inventario['motor'] ?? null) !== 'sqlite' || ($inventario['base'] ?? null) !== ':memory:') {
    throw new RuntimeException('El inventario cambió; regenerar y revisar antes de evaluar rutas.');
}

$exigirColumnas = static function (string $tabla, array $requeridas, array $prohibidas = []) use ($inventario): void {
    $columnas = array_column($inventario['tablas'][$tabla]['columnas'] ?? [], null, 'name');
    if (array_diff($requeridas, array_keys($columnas)) !== [] || array_intersect($prohibidas, array_keys($columnas)) !== []) {
        throw new RuntimeException("Cambió la incompatibilidad física de $tabla; reauditar antes de reutilizar la matriz.");
    }
    foreach ($requeridas as $nombre) {
        if ($columnas[$nombre]['nullable']) {
            throw new RuntimeException("$tabla.$nombre dejó de ser obligatorio; reauditar el contrato.");
        }
    }
};
$exigirColumnas('evidencias_evaluacion', ['cod_mapeo_variable_fuente', 'cod_registro_fuente'], ['cod_valoracion_petersen']);
$exigirColumnas('relaciones_semanticas', ['cod_version_modelo', 'cod_nodo_origen', 'cod_nodo_destino'], ['cod_evaluacion_experta', 'rama']);
$exigirColumnas('hechos_instantanea_experta', ['id_hecho_dominio', 'concepto_dominio'], ['cod_pregunta', 'cod_respuesta']);
$exigirColumnas('motivos_dcl_instantanea', ['cod_clasificacion_dcl_instantanea'], ['cod_captura']);
$exigirColumnas('relaciones_evidencias_evaluacion', ['cod_evidencia_origen', 'cod_evidencia_destino'], ['cod_valoracion_petersen']);
$exigirColumnas('evidencias_criterio_evaluacion', ['cod_evaluacion_criterio'], ['cod_valoracion_petersen', 'cod_soporte_valoracion_petersen']);

$tipos = [
    'm001' => 'evaluación compuesta', 'm002' => 'captura original', 'm003' => 'motivo de exclusión de captura',
    'm004' => 'selector de perfil', 'm005' => 'pregunta declarada sin exigir respuesta',
    'm006' => 'respuesta original tipada', 'm007' => 'comprobación de vínculo de pregunta',
    'm008' => 'contrato de relación adquirido', 'm009' => 'modalidad de contrato adquirido',
    'm010' => 'vínculo interpretativo adquirido', 'm011' => 'medición requerida por perfil',
    'm012' => 'predicado de perfil', 'm013' => 'exclusión de adquisición Petersen',
    'm014' => 'constancia documental adquirida', 'm015' => 'contrato de relación adquirido',
    'm016' => 'modalidad de contrato adquirido', 'm017' => 'grupo de membresías de juicio',
    'm018' => 'membresía de soporte de juicio', 'm019' => 'procedencia de valoración',
    'm020' => 'juicio original Petersen', 'm021' => 'dominio revisado por juicio',
    'm022' => 'soporte original de juicio', 'm023' => 'motivo de exclusión de captura',
    'm024' => 'selector de perfil', 'm025' => 'pregunta declarada sin exigir respuesta',
    'm026' => 'respuesta original tipada', 'm027' => 'comprobación de vínculo de pregunta',
];
$parciales = [
    'm005' => 'B02 SQLite y PostgreSQL; pregunta compartida y sin respuesta',
    'm025' => 'B02 SQLite y PostgreSQL; pregunta compartida y sin respuesta',
    'm006' => '96 respuestas tipadas, SQLite; sin integración del DTO',
    'm026' => '96 respuestas tipadas, SQLite; sin integración del DTO',
    'm008' => 'B01 SQLite; contrato adquirido, NULL y orden',
    'm009' => 'B01 SQLite; modalidad adquirida y orden',
    'm015' => 'B01 SQLite; contrato adquirido, NULL y orden',
    'm016' => 'B01 SQLite; modalidad adquirida y orden',
    'm018' => 'B03 SQLite; membresía y FK compuesta',
];
$modeloCampos = [];
foreach ($campos as $campo) {
    $modeloCampos[$campo['modelo_origen']][$campo['forma']] = ($modeloCampos[$campo['modelo_origen']][$campo['forma']] ?? 0) + 1;
}

$salida = [];
foreach ($rutas as $ruta) {
    $modelo = $ruta['modelo'];
    $def = $mapa['modelos'][$modelo] ?? throw new RuntimeException("Modelo sin contrato amplio: $modelo");
    $tabla = $ruta['destino_candidato'];
    $fisica = $inventario['tablas'][$tabla] ?? null;
    $columnas = array_map(static fn (array $columna): string => $columna['name'].':'.$columna['type_name'].($columna['nullable'] ? '?' : '!'), $fisica['columnas'] ?? []);
    $fks = array_map(static fn (array $fk): string => implode('+', $fk['columns']).'→'.$fk['foreign_table'], $fisica['fks'] ?? []);
    $padre = $def['padre'] ?? null;
    $grano = str_starts_with($modelo, 'k_') ? 'definición de conocimiento observada' : ($tipos[$modelo] ?? throw new RuntimeException("Grano no clasificado: $modelo"));
    $propietarioLogico = match (true) {
        $modelo === 'm001' => 'evaluación institucional',
        str_starts_with($modelo, 'k_') => 'evaluación + rama + versión de conocimiento',
        in_array($modelo, ['m003', 'm004', 'm005', 'm006', 'm007'], true) => 'captura instrumental adquirida',
        in_array($modelo, ['m023', 'm024', 'm025', 'm026', 'm027'], true) => 'captura original del soporte Petersen',
        in_array($modelo, ['m009', 'm016'], true) => 'contrato de relación adquirido',
        in_array($modelo, ['m011', 'm012'], true) => 'vínculo interpretativo adquirido',
        $modelo === 'm018' => 'juicio original + soporte original de la misma evaluación',
        $modelo === 'm021' || $modelo === 'm022' => 'juicio original Petersen',
        default => 'evaluación institucional + rama',
    };
    $razones = array_filter(explode('|', $ruta['brechas']));

    // El mapa previo no detectaba cinco incompatibilidades aunque la tabla exista.
    if (in_array($modelo, ['m003', 'm023', 'm013'], true)) {
        $razones[] = 'MOTIVO_DCL_EXIGE_CLASIFICACION_COMO_PROPIETARIO';
    }
    if ($modelo === 'm021') {
        $razones[] = 'RELACION_EVIDENCIAS_EXIGE_DOS_EVIDENCIAS_EXTRAIDAS';
    }
    if ($modelo === 'm001') {
        $razones[] = 'CABECERA_NO_CONSERVA_DOS_RAMAS_NI_RELEASE';
    }
    if ($modelo === 'm018') {
        $razones[] = 'FK_JUICIO_Y_SOPORTE_MISMA_EVALUACION_NO_INSTALADA';
    }
    if ($modelo === 'm017') {
        $razones[] = 'GRUPO_DE_MEMBRESIAS_NO_ES_EVIDENCIA_EXTRAIDA';
    }
    if ($modelo === 'm020') {
        $razones[] = 'JUICIO_ORIGINAL_NO_ES_EVIDENCIA_EXTRAIDA';
    }
    if ($modelo === 'm022') {
        $razones[] = 'SOPORTE_ORIGINAL_NO_ES_EVIDENCIA_EXTRAIDA';
    }
    if ($modelo === 'm019') {
        $razones[] = 'PROCEDENCIA_NO_ES_EVIDENCIA_EXTRAIDA';
    }
    if ($modelo === 'm014') {
        $razones[] = 'CONSTANCIA_NO_ES_EVIDENCIA_EXTRAIDA';
    }
    if ($modelo === 'm002') {
        $razones[] = 'CAPTURA_NO_ES_EVIDENCIA_EXTRAIDA';
    }
    if (in_array($modelo, ['k_criterios_dominios_resultado', 'k_variables_expertas'], true)) {
        $razones[] = 'ALIAS_DE_DEFINICION_NO_CONSERVA_GRANO_ORIGINAL';
    }
    if ($razones === []) {
        $razones[] = 'SIN_ESCRITOR_LECTOR_INTEGRAL_NI_TRANSICION_POBLADA';
    }
    $pruebaRuta = match (true) {
        str_starts_with($modelo, 'k_') => 'Guardar definición y membresía por rama; modificar/archivar la definición vigente; recuperar versión, orden y estado observados; comparar replay histórico',
        in_array($modelo, ['m008', 'm009', 'm015', 'm016'], true) => 'Guardar contrato/modalidad adquiridos por rama; recuperar orden, ausencia/NULL y pertenencia; rechazar FK cruzada y escritura tras cierre',
        in_array($modelo, ['m005', 'm025'], true) => 'Guardar pregunta sin respuesta en una sola aplicación; leer desde ambas ramas; comparar clave tipada, ausencia/NULL, orden y pertenencia instrumental',
        in_array($modelo, ['m006', 'm026'], true) => 'Guardar respuesta original tipada; recuperar valor, opción, ausencia/NULL y orden; comprobar aplicación/pregunta/residente y no duplicación entre ramas',
        in_array($modelo, ['m017', 'm018', 'm020', 'm021', 'm022'], true) => 'Guardar juicio, dominios revisados, soportes y membresías; recuperar identidad, multiplicidad, orden y FK a la misma evaluación; cerrar y comparar',
        in_array($modelo, ['m002', 'm014', 'm019'], true) => 'Guardar original, constancia y procedencia separadas; recuperar campos exactos, autor, corte, ausencia/NULL y FK de evaluación; rechazar sustitución por evidencia extraída',
        in_array($modelo, ['m003', 'm013', 'm023'], true) => 'Guardar motivo bajo su propietario real; recuperar orden y múltiples motivos; rechazar clasificación o captura ajena',
        default => 'Guardar y leer ruta completa en candidato <=110; comparar presencia/NULL/tipo/clave/orden, FK, cierre y DTO íntegro en SQLite y PostgreSQL',
    };

    $salida[] = [
        'modelo' => $modelo,
        'ruta' => $ruta['ruta'],
        'rama' => str_starts_with($ruta['ruta'], '/petersen/') ? 'PETERSEN' : (str_starts_with($ruta['ruta'], '/instrumental/') ? 'INSTRUMENTAL' : 'COMPUESTA'),
        'tipo_grano' => $grano,
        'propietario_modelo' => $padre ?? 'EVALUACION',
        'propietario_logico' => $propietarioLogico,
        'cardinalidad' => $padre === null ? '1:1' : '0:N por propietario; clave de mapa/orden conservados',
        'identidad_en_oraculo' => $padre === null
            ? 'cod_evaluacion_experta + cod_fila'
            : 'cod_evaluacion_experta + cod_fila + cod_propietario + orden + clave_tipo/clave_texto',
        'columnas_escalares_contrato' => $modeloCampos[$modelo]['escalar'] ?? 0,
        'columnas_objeto_contrato' => $modeloCampos[$modelo]['objeto'] ?? 0,
        'columnas_coleccion_contrato' => $modeloCampos[$modelo]['coleccion'] ?? 0,
        'destino_candidato' => $tabla,
        'tabla_instalada' => $fisica === null ? 'NO' : 'SI',
        'columnas_fisicas' => implode('|', $columnas),
        'fk_fisicas' => implode('|', $fks),
        'fk_entrantes' => count($fisica['consumidores_fk'] ?? []),
        'estado' => isset($parciales[$modelo]) ? 'ENSAYO_PARCIAL; BLOQUEADA_INTEGRACION' : 'BLOQUEADA',
        'prueba_parcial' => $parciales[$modelo] ?? 'F02 completo solo en esquema amplio de 42 tablas; no prueba este destino',
        'bloqueo_reproducible' => implode('|', array_unique($razones)),
        'prueba_de_cierre_requerida' => $pruebaRuta,
    ];
}

$archivo = fopen($base.'/matriz-resolucion-51-rutas.csv', 'wb') ?: throw new RuntimeException('No se puede escribir matriz.');
fputcsv($archivo, array_keys($salida[0]));
foreach ($salida as $fila) {
    fputcsv($archivo, array_values($fila));
}
fclose($archivo);

$resumen = [
    'rutas' => count($salida),
    'campos_contrato' => count($campos),
    'modelos' => count($mapa['modelos']),
    'rutas_resueltas_en_candidato_110' => count(array_filter($salida, static fn (array $fila): bool => $fila['estado'] === 'RESUELTA')),
    'rutas_con_ensayo_parcial' => count($parciales),
    'rutas_sin_tabla_candidata_instalada' => count(array_filter($salida, static fn (array $fila): bool => $fila['tabla_instalada'] === 'NO')),
    'rutas_con_bloqueo_individual' => count(array_filter($salida, static fn (array $fila): bool => $fila['bloqueo_reproducible'] !== '')),
];
file_put_contents($base.'/resumen-resolucion-rutas.json', json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
echo json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), "\n";
