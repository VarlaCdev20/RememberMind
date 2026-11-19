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
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MotorExperto\conservacion\CodecInstantanea;

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
    if ($inventario['expertas_presentes'] !== 20 || $inventario['expertas_ausentes'] !== []) {
        throw new RuntimeException('El inventario experto instalado está incompleto.');
    }
    foreach ([...array_keys(BloquesExpertos::DEFINICIONES), 'criterios_dominios_resultado', 'condiciones_regla_experta', 'consecuencias_regla_experta', 'evaluaciones_condiciones_regla', 'condiciones_regla_instantanea'] as $entidad) {
        if (Schema::hasTable($entidad) || Schema::hasView($entidad)) {
            throw new RuntimeException('Una relación retirada permanece como tabla o vista.');
        }
    }
    $referencias = [...TransicionBloquesExpertos::REFERENCIAS,
        ['consecuencias_regla_experta', ['cod_criterio_dominio_resultado'], 'criterios_dominios_resultado', ['cod_criterio_dominio_resultado']],
        ['evaluaciones_condiciones_regla', ['cod_condicion_regla'], 'condiciones_regla_experta', ['cod_condicion_regla_ref']],
        ['evaluaciones_condiciones_regla', ['cod_evaluacion_regla_padre'], 'evaluaciones_reglas', ['cod_evaluacion_regla']],
        ['evidencias_soporte_condicion', ['cod_evaluacion_condicion'], 'evaluaciones_condiciones_regla', ['cod_evaluacion_condicion']],
        ['condiciones_regla_instantanea', ['cod_regla_propietaria'], 'reglas_aplicadas_dcl_instantanea', ['cod_regla_aplicada_dcl']],
        ['condiciones_regla_instantanea', ['cod_condicion_padre'], 'condiciones_regla_instantanea', ['cod_condicion_instantanea']]];
    $fkVerificadas = 0;
    foreach ($referencias as [$tabla, $cols, $entidad, $destinos]) {
        if ($tabla === 'mapeos_variables_fuente' && $cols === ['cod_variable_experta', 'cod_version_modelo']
            && ! Schema::hasColumn($tabla, 'cod_version_modelo')) {
            if (DB::scalar("SELECT count(*) FROM pg_trigger WHERE tgname='norm18_mapeo' AND NOT tgisinternal") !== 1) {
                throw new RuntimeException('Falta la protección SQL del mapeo entre versiones.');
            }

            continue;
        }
        $fks = array_values(array_filter(Schema::getForeignKeys(BloquesExpertos::tablaFisica($tabla)), static fn ($f) => $f['columns'] === $cols));
        if (count($fks) !== 1 || $fks[0]['foreign_table'] !== BloquesExpertos::tablaFisica($entidad) || $fks[0]['foreign_columns'] !== $destinos) {
            throw new RuntimeException('Una FK trasladada no coincide con el contrato.');
        }
        $fkVerificadas++;
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
    echo json_encode(['expertas_presentes' => $inventario['expertas_presentes'], 'tablas_fisicas' => $inventario['tablas_fisicas'],
        'columnas_propietarios' => array_map(static fn ($t) => count(Schema::getColumnListing($t)),
            array_combine(['evaluacion_criterios', 'nodos_semanticos', 'evaluaciones_expertas'], ['evaluacion_criterios', 'nodos_semanticos', 'evaluaciones_expertas'])),
        'filas_recuperadas_eloquent' => $filas, 'versiones_conocimiento_leidas' => $versiones,
        'fk_entrantes_verificadas' => $fkVerificadas,
        'solo_lectura' => true], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} finally {
    DB::rollBack();
}
