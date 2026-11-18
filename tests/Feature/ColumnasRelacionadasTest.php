<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\EstudioClinico;
use App\Models\Medicamento;
use App\Models\Personal;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\TipoEstudioClinico;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ColumnasRelacionadasTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(): array
    {
        $usuario = User::factory()->create();
        $personal = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Sintética',
            'numero_documento' => 'NORM-18', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $residente = Residente::factory()->create();
        $otro = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Atención', 'estado' => 'ACTIVA']);
        $atencion = Atencion::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA', 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        $ajena = Atencion::create(['cod_residente' => $otro->getKey(), 'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA', 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        $medicamento = Medicamento::create(['nombre_generico' => 'Catálogo sintético', 'control_especial' => false, 'estado' => 'ACTIVO']);
        $prescripcion = Prescripcion::create(['cod_residente' => $residente->getKey(), 'cod_atencion' => $atencion->getKey(),
            'cod_medicamento' => $medicamento->getKey(), 'cod_personal' => $personal->getKey(), 'dosis' => 1,
            'unidad_dosis' => 'UNIDAD', 'via_administracion' => 'ORAL', 'frecuencia' => 'DIARIA',
            'segun_necesidad' => false, 'fecha_hora_prescripcion' => now(), 'estado' => 'ACTIVA']);
        $tipo = TipoEstudioClinico::create(['nombre' => 'Estudio sintético', 'categoria' => 'LABORATORIO',
            'requiere_componentes' => false, 'requiere_informe' => true, 'estado' => 'ACTIVO']);
        $estudio = EstudioClinico::create(['cod_residente' => $residente->getKey(), 'cod_atencion' => $atencion->getKey(),
            'cod_tipo_estudio' => $tipo->getKey(), 'cod_personal' => $personal->getKey(), 'fecha_solicitud' => now(), 'estado' => 'SOLICITADO']);

        return compact('residente', 'otro', 'atencion', 'ajena', 'prescripcion', 'estudio');
    }

    public function test_residente_se_resuelve_por_la_atencion_en_lectura_filtros_y_relaciones(): void
    {
        $datos = $this->escenario();
        $id = $datos['residente']->getKey();
        $this->assertFalse(Schema::hasColumn('prescripciones', 'cod_residente'));
        $this->assertFalse(Schema::hasColumn('estudios_clinicos', 'cod_residente'));
        $this->assertSame($id, $datos['prescripcion']->fresh()->cod_residente);
        $this->assertSame($id, $datos['estudio']->fresh()->cod_residente);
        $this->assertSame($id, $datos['prescripcion']->fresh()->residente->getKey());
        $this->assertCount(1, $datos['residente']->fresh()->prescripciones);
        $this->assertCount(0, $datos['otro']->fresh()->prescripciones);
        $this->assertSame(1, Prescripcion::where('cod_residente', $id)->count());
        $this->assertSame(0, Prescripcion::where('cod_residente', $datos['otro']->getKey())->count());
        $this->assertSame(1, EstudioClinico::where('cod_residente', $id)->count());
        $this->assertSame(1, Prescripcion::whereHas('residente', fn ($q) => $q->whereKey($id))->count());
        $this->assertSame(1, EstudioClinico::whereHas('residente', fn ($q) => $q->whereKey($id))->count());
        $this->assertSame(0, \App\Models\AdministracionMedicacion::whereHas('residente', fn ($q) => $q->whereKey($id))->count());
        $datos['prescripcion']->fresh()->update(['observacion' => 'Seguimiento conservado']);
        $this->assertDatabaseHas('prescripciones', ['cod_prescripcion' => $datos['prescripcion']->getKey(), 'observacion' => 'Seguimiento conservado']);
    }

    public function test_sql_impide_reasignar_el_residente_de_la_atencion_o_sus_hijos(): void
    {
        $datos = $this->escenario();
        foreach ([
            fn () => DB::table('atenciones')->where('cod_atencion', $datos['atencion']->getKey())->update(['cod_residente' => $datos['otro']->getKey()]),
            fn () => DB::table('prescripciones')->where('cod_prescripcion', $datos['prescripcion']->getKey())->update(['cod_atencion' => $datos['ajena']->getKey()]),
            fn () => DB::table('estudios_clinicos')->where('cod_estudio', $datos['estudio']->getKey())->update(['cod_atencion' => $datos['ajena']->getKey()]),
        ] as $operacion) {
            try {
                DB::transaction($operacion);
                $this->fail('El cambio debe rechazarse por SQL.');
            } catch (QueryException $error) {
                $this->assertContains($error->getCode(), ['23514', '23000']);
            }
        }
        $this->assertSame($datos['residente']->getKey(), $datos['prescripcion']->fresh()->cod_residente);
    }

    public function test_modelo_rechaza_el_contexto_de_residente_manipulado(): void
    {
        $datos = $this->escenario();
        $this->expectException(ValidationException::class);
        $datos['prescripcion']->fresh()->update(['cod_residente' => $datos['otro']->getKey()]);
    }

    public function test_reversion_reconstruye_los_contextos_sin_perder_filas(): void
    {
        $datos = $this->escenario();
        $tablas = Schema::getTableListing();
        $migracion = require database_path('migrations/2026_11_18_000002_remove_derived_context_columns.php');
        $migracion->down();
        $this->assertDatabaseHas('prescripciones', ['cod_prescripcion' => $datos['prescripcion']->getKey(), 'cod_residente' => $datos['residente']->getKey()]);
        $this->assertDatabaseHas('estudios_clinicos', ['cod_estudio' => $datos['estudio']->getKey(), 'cod_residente' => $datos['residente']->getKey()]);
        $migracion->up();
        $this->assertSame($tablas, Schema::getTableListing());
        $this->assertSame($datos['residente']->getKey(), $datos['prescripcion']->fresh()->cod_residente);
    }

    public function test_red_semantica_conserva_version_y_rechaza_extremos_de_versiones_distintas(): void
    {
        \Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios::preparar();
        \Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios::guardarGrafo(
            \Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores::red());
        \Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios::version('SIM-V2');
        DB::table('nodos_semanticos')->insert(['cod_nodo_semantico' => 'SIM-N-OTRO', 'cod_version_modelo' => 'SIM-V2',
            'codigo_semantico' => 'OTRO', 'nombre' => 'Concepto sintético', 'tipo_nodo' => 'CONCEPTO', 'definicion' => 'Definición sintética',
            'estado' => 'ACTIVO', 'fecha_hora_creacion' => now(), 'cod_usuario_creacion' => 'SIM-USU']);
        $this->assertFalse(Schema::hasColumn('relaciones_semanticas', 'cod_version_modelo'));
        $this->assertSame('SIM-V1', \App\Models\RelacionSemantica::sole()->cod_version_modelo);
        $this->assertCount(1, \App\Models\VersionModeloExperto::findOrFail('SIM-V1')->relacionesSemanticas);
        foreach ([
            fn () => DB::table('relaciones_semanticas')->update(['cod_nodo_destino' => 'SIM-N-OTRO']),
            fn () => DB::table('nodos_semanticos')->where('cod_nodo_semantico', 'SIM-N-SUB')->update(['cod_version_modelo' => 'SIM-V2']),
        ] as $operacion) {
            try {
                DB::transaction($operacion);
                $this->fail('La versión de una relación existente debe conservarse.');
            } catch (QueryException $error) {
                $this->assertContains($error->getCode(), ['23514', '23000']);
            }
        }
    }

    public function test_mapeos_conservan_la_version_y_la_instantanea_durante_ida_y_vuelta(): void
    {
        \Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios::preparar();
        [$evidencia] = \Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores::evidencia();
        \Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios::guardarGrafo($evidencia);
        \Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios::version('SIM-V2');
        $mapeo = \App\Models\MapeoVariableFuente::sole();
        $this->assertSame('SIM-V1', $mapeo->cod_version_modelo);
        $this->assertFalse(Schema::hasColumn('mapeos_variables_fuente', 'cod_version_modelo'));
        $this->assertCount(1, \App\Models\VersionModeloExperto::findOrFail('SIM-V1')->mapeosVariablesFuente);
        $fuente = (array) DB::table('fuentes_datos_expertas')->where('cod_fuente_dato_experta', $mapeo->cod_fuente_dato_experta)->first();
        $fuente['cod_fuente_dato_experta'] = 'SIM-F-OTRA';
        $fuente['codigo_fuente'] = 'OTRA';
        $fuente['cod_version_modelo'] = 'SIM-V2';
        DB::table('fuentes_datos_expertas')->insert($fuente);
        foreach ([
            fn () => DB::table('mapeos_variables_fuente')->update(['cod_fuente_dato_experta' => 'SIM-F-OTRA']),
            fn () => DB::table('fuentes_datos_expertas')->where('cod_fuente_dato_experta', $mapeo->cod_fuente_dato_experta)->update(['cod_version_modelo' => 'SIM-V2']),
        ] as $operacion) {
            try {
                DB::transaction($operacion);
                $this->fail('El mapeo debe conservar la versión de sus dos extremos.');
            } catch (QueryException $error) {
                $this->assertContains($error->getCode(), ['23514', '23000']);
            }
        }
        $lector = app(\App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Eloquent\CargadorConocimiento::class);
        $antes = $lector->instantanea('SIM-V1');
        $migracion = require database_path('migrations/2026_11_18_000002_remove_derived_context_columns.php');
        $migracion->down();
        $migracion->up();
        $this->assertEquals($antes, $lector->instantanea('SIM-V1'));
    }
}
