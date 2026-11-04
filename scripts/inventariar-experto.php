<?php

declare(strict_types=1);

// Inventario de catálogos y estructura de entrada. No consulta filas de residentes, hechos ni evaluaciones.
require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$conexion = null;
$salidaCodigo = 0;

try {
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $conexion = $app->make('db')->connection();
    $driver = $conexion->getDriverName();
    if (! in_array($driver, ['pgsql', 'sqlite'], true)) {
        throw new RuntimeException('Motor no admitido para inventario de solo lectura.');
    }
    if ($driver === 'sqlite') {
        $conexion->statement('PRAGMA query_only = ON');
    }
    $conexion->beginTransaction();
    if ($driver === 'pgsql') {
        $conexion->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
    }
    $soloLectura = $driver === 'pgsql'
        ? $conexion->selectOne("SELECT current_setting('transaction_read_only') AS habilitada")->habilitada === 'on'
        : (int) $conexion->selectOne('PRAGMA query_only')->query_only === 1;
    if (! $soloLectura) {
        throw new RuntimeException('No se confirmó la protección de solo lectura.');
    }
    $esquema = $conexion->getSchemaBuilder();
    $tablas = ['instrumentos', 'preguntas_instrumento', 'versiones_modelo_experto',
        'reglas_expertas', 'condiciones_regla_experta', 'consecuencias_regla_experta',
        'nodos_semanticos', 'relaciones_semanticas'];
    $disponibles = [];
    foreach ($tablas as $tabla) {
        $disponibles[$tabla] = $esquema->hasTable($tabla);
    }
    $preguntas = $disponibles['preguntas_instrumento']
        ? $conexion->table('preguntas_instrumento')->select('cod_instrumento')->selectRaw('COUNT(*) as cantidad')
            ->groupBy('cod_instrumento')->pluck('cantidad', 'cod_instrumento')->all() : [];
    $instrumentos = $disponibles['instrumentos']
        ? $conexion->table('instrumentos')->orderBy('codigo')
            ->get(['cod_instrumento', 'codigo', 'nombre', 'tipo', 'version', 'estado'])->map(function ($fila) use ($preguntas, $disponibles): array {
                $dato = (array) $fila;
                $dato['preguntas_catalogadas'] = $disponibles['preguntas_instrumento']
                    ? (int) ($preguntas[$fila->cod_instrumento] ?? 0) : null;

                return $dato;
            })->all() : [];
    $versiones = $disponibles['versiones_modelo_experto']
        ? $conexion->table('versiones_modelo_experto')->orderBy('codigo_version')
            ->get(['cod_version_modelo', 'codigo_version', 'estado'])->map(fn ($fila): array => (array) $fila)->all() : [];
    $resumen = [];
    foreach (['reglas_expertas', 'condiciones_regla_experta', 'consecuencias_regla_experta', 'nodos_semanticos', 'relaciones_semanticas'] as $tabla) {
        $resumen[$tabla] = $disponibles[$tabla] ? $conexion->table($tabla)->count() : null;
    }
    $reglasPorVersion = $disponibles['reglas_expertas']
        ? $conexion->table('reglas_expertas')->select(['cod_version_modelo', 'estado', 'estado_revision'])
            ->selectRaw('COUNT(*) as cantidad')->groupBy('cod_version_modelo', 'estado', 'estado_revision')
            ->orderBy('cod_version_modelo')->orderBy('estado')->orderBy('estado_revision')
            ->get()->map(fn ($fila): array => (array) $fila)->all() : [];
    $catalogoReglas = [];
    $tablasCatalogo = ['reglas_expertas', 'versiones_modelo_experto', 'condiciones_regla_experta',
        'consecuencias_regla_experta', 'nodos_semanticos', 'valores_semanticos', 'criterios_dominios_resultado'];
    $catalogoDisponible = count(array_filter($tablasCatalogo, fn ($tabla) => $esquema->hasTable($tabla))) === count($tablasCatalogo)
        && $esquema->hasColumn('nodos_semanticos', 'cod_variable_experta');
    if ($catalogoDisponible) {
        $visibles = \App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarConocimientoAdministrativo::institucionales()
            ->pluck('cod_version_modelo')->all();
        $condiciones = $conexion->table('condiciones_regla_experta as c')
            ->join('nodos_semanticos as n', 'n.cod_variable_experta', '=', 'c.cod_variable_experta')
            ->join('valores_semanticos as v', 'v.cod_valor_semantico', '=', 'c.cod_valor_semantico')
            ->orderBy('n.codigo_semantico')->get(['c.cod_regla_experta', 'n.codigo_semantico as variable', 'v.codigo_valor as valor_esperado'])
            ->groupBy('cod_regla_experta');
        $consecuencias = $conexion->table('consecuencias_regla_experta as c')
            ->join('criterios_dominios_resultado as d', 'd.cod_criterio_dominio_resultado', '=', 'c.cod_criterio_dominio_resultado')
            ->join('nodos_semanticos as n', 'n.cod_nodo_semantico', '=', 'd.cod_nodo_criterio')
            ->join('valores_semanticos as v', 'v.cod_valor_semantico', '=', 'c.cod_valor_semantico')
            ->orderBy('n.codigo_semantico')->get(['c.cod_regla_experta', 'n.codigo_semantico as criterio', 'v.codigo_valor as valor_emitido'])
            ->groupBy('cod_regla_experta');
        $catalogoReglas = $conexion->table('reglas_expertas as r')
            ->join('versiones_modelo_experto as v', 'v.cod_version_modelo', '=', 'r.cod_version_modelo')
            ->orderBy('v.codigo_version')->orderBy('r.codigo_regla')
            ->get(['r.cod_regla_experta', 'r.cod_version_modelo', 'r.codigo_regla', 'r.nombre', 'r.estado', 'r.estado_revision',
                'v.codigo_version', 'v.estado as estado_version'])
            ->map(static function ($regla) use ($visibles, $condiciones, $consecuencias): array {
                $id = $regla->cod_regla_experta;

                return ['codigo' => $regla->codigo_regla, 'nombre' => $regla->nombre, 'version' => $regla->codigo_version,
                    'estado' => $regla->estado, 'revision' => $regla->estado_revision, 'estado_version' => $regla->estado_version,
                    'incluida_en_catalogo_institucional' => in_array($regla->cod_version_modelo, $visibles, true),
                    'condiciones' => ($condiciones[$id] ?? collect())->map(fn ($c) => ['variable' => $c->variable, 'valor_esperado' => $c->valor_esperado])->all(),
                    'consecuencias' => ($consecuencias[$id] ?? collect())->map(fn ($c) => ['criterio' => $c->criterio, 'valor_emitido' => $c->valor_emitido])->all(),
                    'acredita_ejecucion' => false];
            })->all();
    }
    $configuracion = [];
    foreach (['perfiles_instrumentales', 'perfiles_interpretativos', 'contratos_conocimiento', 'lectura_clinica'] as $clave) {
        $valor = config('sistema_experto.'.$clave, []);
        if (! is_array($valor)) {
            throw new RuntimeException('La configuración de catálogos no es una lista o mapa.');
        }
        $configuracion[$clave] = count($valor);
    }
    $resultado = [
        'fecha' => (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->format(DateTimeInterface::ATOM),
        'alcance' => 'CATALOGOS_Y_CONFIGURACION_EFECTIVA_SIN_DATOS_CLINICOS',
        'driver' => $driver,
        'transaccion_solo_lectura' => $soloLectura,
        'tablas_disponibles' => $disponibles,
        'contrato_entradas' => \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto::entradas($conexion),
        'instrumentos' => $instrumentos,
        'versiones_conocimiento' => $versiones,
        'totales_conocimiento' => $resumen,
        'reglas_por_version_y_estado' => $reglasPorVersion,
        'catalogo_reglas' => $catalogoReglas,
        'catalogo_reglas_disponible' => $catalogoDisponible,
        'configuracion_cantidad' => $configuracion,
        'preparacion_circuito_inicial' => (new \App\Modulos\ApoyoDecision\Aplicacion\Consultas\PreparacionCircuitoInstrumental)->resumir(
            config('sistema_experto.perfiles_instrumentales', []),
            (new \MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales)->metodos(),
            app(\App\Modulos\ApoyoDecision\Aplicacion\Servicios\PerfilesInstrumentalesIniciales::class)->estado()),
        'version_cognitiva_fijada_en_config' => filled(config('sistema_experto.version_cognitiva')),
        'limites' => ['No acredita instrumentos administrados, licencias, baremos ni validez clínica.',
            'La estructura de entrada se verifica sin consultar filas clínicas; no certifica datos completos ni preparación clínica.',
            'No ejecuta inferencia, activa reglas ni valida autorización de pantallas.',
            'Un catálogo activo no equivale a una evaluación persistida ni a revisión profesional.'],
    ];
    $conexion->rollBack();
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $error) {
    // Fallo explícito sin imprimir SQL, cadenas de conexión o credenciales.
    fwrite(STDERR, 'Inventario no completado ('.get_class($error).'). No se modificaron catálogos ni se ejecutó inferencia.'.PHP_EOL);
    $salidaCodigo = 1;
} finally {
    if ($conexion !== null && $conexion->transactionLevel() > 0) {
        $conexion->rollBack();
    }
}

exit($salidaCodigo);
