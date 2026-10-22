<?php

declare(strict_types=1);

use App\Models\ClasificacionDclInstantanea;
use App\Models\ResultadoCriterio;
use App\Models\TrazaInferencia;
use App\Models\VariableExperta;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenInstantaneas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\TransicionBloquesExpertos;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Eloquent\CargadorConocimiento;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\ContextoSimulacionLocal;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MotorExperto\soporte\conservacion\CodecInstantanea;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (DB::getDriverName() !== 'pgsql') {
    throw new RuntimeException('La verificación instalada requiere PostgreSQL.');
}
// La propia conexión impide escrituras accidentales, incluidas las de colaboradores.
DB::beginTransaction();
DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
try {
    $inventario = InventarioExperto::diagnosticar(DB::connection());
    if ($inventario['expertas_presentes'] !== 25 || $inventario['expertas_ausentes'] !== []) {
        throw new RuntimeException('El inventario experto instalado está incompleto.');
    }
    foreach (BloquesExpertos::DEFINICIONES as $entidad => $d) {
        if (Schema::hasTable($entidad) || Schema::hasView($entidad)) {
            throw new RuntimeException('Una relación retirada permanece como tabla o vista.');
        }
    }
    foreach (TransicionBloquesExpertos::REFERENCIAS as [$tabla, $cols, $entidad, $destinos]) {
        $fks = array_values(array_filter(Schema::getForeignKeys($tabla), static fn ($f) => $f['columns'] === $cols));
        if (count($fks) !== 1 || $fks[0]['foreign_table'] !== BloquesExpertos::DEFINICIONES[$entidad]['tabla'] || $fks[0]['foreign_columns'] !== $destinos) {
            throw new RuntimeException('Una FK trasladada no coincide con el contrato.');
        }
    }
    $modelos = [VariableExperta::class => ['nodo', 'dominio', 'version', 'usuarioCreacion'],
        ResultadoCriterio::class => ['evaluacionCriterio', 'valor'], TrazaInferencia::class => ['evaluacionCriterio'],
        ClasificacionDclInstantanea::class => ['evaluacion', 'motivos', 'reglasAplicadas', 'evidenciasIadl']];
    $filas = [];
    foreach ($modelos as $clase => $relaciones) {
        $filas[(new $clase)->entidadLogica()] = $clase::query()->with($relaciones)->get()->count();
    }
    $versiones = 0;
    foreach (VersionModeloExperto::query()->pluck('cod_version_modelo') as $version) {
        app(CargadorConocimiento::class)->instantanea($version);
        $versiones++;
    }
    $simulacionesRecuperadas = 0;
    if (config('sistema_experto.simulacion_local.habilitada') && app()->environment('local')) {
        $contexto = new ContextoSimulacionLocal(DB::getDefaultConnection());
        $almacen = new AlmacenInstantaneas($contexto->conexion, null, new DateTimeZone(config('app.timezone')), $contexto);
        $clasificaciones = ClasificacionDclInstantanea::query()->with('evaluacion.version')->get();
        foreach ($clasificaciones as $fila) {
            $evaluacion = $fila->evaluacion;
            if ($evaluacion->version->codigo_version !== ContextoSimulacionLocal::VERSION) {
                continue;
            }
            $recuperada = $almacen->obtenerClasificacion($evaluacion->id_evaluacion_dominio, $contexto->alias($evaluacion->cod_residente),
                $evaluacion->cod_version_modelo, CodecInstantanea::leerFecha('fecha_corte', $evaluacion), $fila->version_contrato);
            if ($recuperada === null || CodecInstantanea::huella($recuperada) !== $fila->huella_clasificacion) {
                throw new RuntimeException('Una clasificación histórica no coincide con su huella conservada.');
            }
            $simulacionesRecuperadas++;
        }
    }
    echo json_encode(['expertas_presentes' => 25, 'tablas_fisicas' => $inventario['tablas_fisicas'],
        'columnas_propietarios' => array_map(static fn ($t) => count(Schema::getColumnListing($t)),
            array_combine(['evaluacion_criterios', 'nodos_semanticos', 'evaluaciones_expertas'], ['evaluacion_criterios', 'nodos_semanticos', 'evaluaciones_expertas'])),
        'filas_recuperadas_eloquent' => $filas, 'versiones_conocimiento_leidas' => $versiones,
        'fk_entrantes_verificadas' => count(TransicionBloquesExpertos::REFERENCIAS),
        'clasificaciones_sinteticas_recuperadas' => $simulacionesRecuperadas,
        'solo_lectura' => true], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} finally {
    DB::rollBack();
}
