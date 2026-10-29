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
        'configuracion_cantidad' => $configuracion,
        'preparacion_circuito_inicial' => (new \App\Modulos\ApoyoDecision\Aplicacion\Consultas\PreparacionCircuitoInstrumental)->resumir(
            config('sistema_experto.perfiles_instrumentales', []),
            (new \MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales)->metodos()),
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
