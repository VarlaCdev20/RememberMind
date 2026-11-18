<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\SeguimientoPedagogico;
use App\Models\User;
use App\Models\ValoracionFuncional;
use App\Models\ValoracionNutricional;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Modulos\Reportes\Aplicacion\Consultas\RoleDashboardDataService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RepartoValoracionesProfesionalesTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(): array
    {
        $usuario = User::factory()->create();
        $personal = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'REPARTO-18', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $residente = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Área de ensayo', 'estado' => 'ACTIVA']);
        $base = ['cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(),
            'fecha_hora' => '2026-11-18 09:30:00', 'estado' => 'VIGENTE'];
        $atencion = Atencion::create($base + ['cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA']);
        $clinica = $base + ['cod_atencion' => $atencion->getKey()];
        $psicologica = ValoracionPsicologica::create($clinica + ['estado_animo' => 'ESTABLE', 'conclusion' => 'Observación sintética']);
        $funcional = ValoracionFuncional::create($clinica + ['necesita_supervision' => false, 'nivel_dependencia' => 'INDEPENDIENTE']);
        $nutricional = ValoracionNutricional::create($clinica + ['apetito' => 'CONSERVADO', 'requerimiento_hidrico' => 1500.25]);
        $pedagogica = SeguimientoPedagogico::create($base + ['atencion' => 'CONSERVADA', 'cambio_desempeno' => false]);
        $original = ValoracionPetersenOriginal::create(array_diff_key($clinica, ['estado' => true]) + [
            'cod_usuario_registro' => $usuario->getKey(), 'solicitud_uuid' => (string) Str::uuid(), 'registrado_en' => now(),
            'tipo_registro' => 'REGISTRO', 'elemento' => 'C01', 'estado_original' => 'S', 'justificacion' => 'Contexto sintético']);
        DB::table('soportes_valoracion_petersen')->insert(['cod_soporte_petersen' => 'SOP-REPARTO-18',
            'cod_valoracion_petersen' => $original->getKey(), 'tipo_soporte' => 'HISTORIA_PREVIA', 'orden' => 0,
            'cod_valoracion_psicologica' => $psicologica->getKey()]);

        return compact('psicologica', 'funcional', 'nutricional', 'pedagogica', 'atencion', 'original');
    }

    public function test_reparte_cuatro_tipos_en_dos_tablas_con_lectura_del_residente(): void
    {
        $datos = $this->escenario();
        $this->assertCount(29, Schema::getColumnListing('valoraciones_profesionales'));
        $this->assertFalse(Schema::hasColumn('valoraciones_profesionales', 'cod_residente'));
        $this->assertCount(30, Schema::getColumnListing('valoraciones_psicopedagogicas'));
        $this->assertCount(95, Schema::getTableListing());
        $this->assertSame(1, ValoracionPsicologica::count());
        $this->assertSame(1, SeguimientoPedagogico::count());
        $this->assertSame(1, ValoracionFuncional::count());
        $this->assertSame(1, ValoracionNutricional::count());
        $this->assertSame('1500.25', (string) $datos['nutricional']->fresh()->requerimiento_hidrico);
        $residente = Residente::with('valoracionesFuncionales')->findOrFail($datos['atencion']->cod_residente);
        $funcional = $residente->valoracionesFuncionales->sole();
        $this->assertSame($datos['funcional']->getKey(), $funcional->getKey());
        $this->assertSame($residente->getKey(), $funcional->toArray()['cod_residente']);
        $this->assertSame($residente->getKey(), $datos['nutricional']->fresh(['residente'])->residente->getKey());
        $this->assertSame(1, ValoracionFuncional::where('cod_residente', $residente->getKey())->update(['estado' => 'HISTORICA']));
        $this->assertSame($residente->getKey(), RegistrosConsumo::consulta('valoraciones_funcionales', 'v')->value('v.cod_residente'));
        $this->assertSame($datos['atencion']->getKey(), $datos['psicologica']->fresh()->atencion->getKey());
        $this->assertFalse($datos['pedagogica']->fresh()->cambio_desempeno);
        $this->assertNull(ValoracionPsicologica::find($datos['pedagogica']->getKey()));
        $this->assertNull(ValoracionNutricional::find($datos['funcional']->getKey()));
        $this->assertSame($datos['atencion']->getKey(), ValoracionPsicologica::with('atencion')->findOrFail($datos['psicologica']->getKey())->atencion->getKey());
    }

    public function test_el_panel_de_cada_especialidad_recupera_el_residente_desde_su_atencion(): void
    {
        $datos = $this->escenario();
        $atencion = $datos['atencion'];
        $autor = $atencion->personal->usuario;
        $nutricional = $datos['nutricional'];
        $otro = Residente::factory()->create();
        $otraAtencion = Atencion::create(['cod_residente' => $otro->getKey(), 'cod_personal' => $atencion->cod_personal,
            'cod_area' => $atencion->cod_area, 'tipo_atencion' => 'CONSULTA', 'fecha_hora' => $atencion->fecha_hora, 'estado' => 'VIGENTE']);
        // Se asigna un episodio correcto en una nueva valoración y se conservan ambos historiales.
        ValoracionNutricional::create(['cod_atencion' => $otraAtencion->getKey(), 'cod_personal' => $nutricional->cod_personal,
            'fecha_hora' => $nutricional->fecha_hora, 'estado' => 'VIGENTE']);
        $servicio = app(RoleDashboardDataService::class);
        $funcional = $servicio->forRole($autor, 'FISIOTERAPEUTA')['panels'][0]['items'];
        $nutricion = $servicio->forRole($autor, 'NUTRICIONISTA')['panels'][0]['items'];

        $this->assertSame([trim($atencion->residente->nombres.' '.$atencion->residente->apellido_paterno)], array_column($funcional, 'label'));
        $this->assertCount(2, $nutricion);
        $this->assertContains(trim($otro->nombres.' '.$otro->apellido_paterno), array_column($nutricion, 'label'));
    }

    public function test_sql_rechaza_otra_especialidad_y_soportes_pedagogicos_como_psicologicos(): void
    {
        $datos = $this->escenario();
        foreach ([
            fn () => DB::table('valoraciones_profesionales')->where('cod_valoracion_profesional', $datos['funcional']->getKey())->update(['apetito' => 'CONSERVADO']),
            fn () => DB::table('valoraciones_psicopedagogicas')->where('cod_valoracion_profesional', $datos['psicologica']->getKey())->update(['atencion' => 'CONSERVADA']),
            fn () => DB::table('soportes_valoracion_petersen')->insert(['cod_soporte_petersen' => 'SOP-PED-INVALIDO',
                'cod_valoracion_petersen' => $datos['original']->getKey(), 'tipo_soporte' => 'HISTORIA_PREVIA', 'orden' => 1,
                'cod_valoracion_psicologica' => $datos['pedagogica']->getKey()]),
        ] as $operacion) {
            try {
                DB::transaction($operacion);
                $this->fail('La base debe rechazar la mezcla de tipos.');
            } catch (QueryException $error) {
                $this->assertContains($error->getCode(), ['23000', '23503', '23514']);
            }
        }
        $this->assertDatabaseCount('soportes_valoracion_petersen', 1);
        $this->assertNull($datos['funcional']->fresh()->apetito);
        $this->assertNull($datos['psicologica']->fresh()->getRawOriginal('atencion'));
    }

    public function test_conservar_residente_en_sql_y_validar_contexto_al_guardar(): void
    {
        $datos = $this->escenario();
        $atencion = $datos['atencion'];
        $otro = Residente::factory()->create();
        $ajena = Atencion::create(['cod_residente' => $otro->getKey(), 'cod_personal' => $atencion->cod_personal,
            'cod_area' => $atencion->cod_area, 'tipo_atencion' => 'CONSULTA', 'fecha_hora' => $atencion->fecha_hora, 'estado' => 'VIGENTE']);
        foreach ([
            fn () => DB::table('atenciones')->where('cod_atencion', $atencion->getKey())->update(['cod_residente' => $otro->getKey()]),
            fn () => DB::table('valoraciones_profesionales')->where('cod_valoracion_profesional', $datos['funcional']->getKey())
                ->update(['cod_atencion' => $ajena->getKey()]),
            fn () => DB::table('valoraciones_profesionales')->where('cod_valoracion_profesional', $datos['funcional']->getKey())
                ->update(['cod_atencion' => null]),
        ] as $operacion) {
            try {
                DB::transaction($operacion);
                $this->fail('El contexto clínico no debe perderse ni cambiar de residente.');
            } catch (QueryException $error) {
                $this->assertContains($error->getCode(), ['23000', '23503', '23514']);
            }
        }

        $comun = ['cod_residente' => $otro->getKey(), 'cod_atencion' => $atencion->getKey(),
            'cod_personal' => $atencion->cod_personal, 'fecha_hora' => $atencion->fecha_hora, 'estado' => 'VIGENTE'];
        foreach ([
            fn () => ValoracionNutricional::create($comun),
            fn () => RegistrosConsumo::insertar('valoraciones_funcionales', [$comun + ['cod_valoracion_funcional' => 'FUNC-AJENA']]),
        ] as $operacion) {
            try {
                $operacion();
                $this->fail('El residente explícito debe coincidir con el de la atención.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('cod_atencion', $error->errors());
            }
        }
        $this->assertSame(2, DB::table('valoraciones_profesionales')->count());
        $this->assertSame($atencion->cod_residente, $datos['funcional']->fresh()->cod_residente);
    }

    public function test_reversion_poblada_conserva_fechas_autoria_decimal_y_referencias(): void
    {
        $datos = $this->escenario();
        $antes = $this->capturar();
        $soportes = DB::table('soportes_valoracion_petersen')->orderBy('cod_soporte_petersen')->get()->toJson();
        $contexto = require database_path('migrations/2026_11_18_000004_derive_professional_assessment_resident.php');
        $contexto->down();
        $this->assertDatabaseHas('valoraciones_profesionales', ['cod_valoracion_profesional' => $datos['funcional']->getKey(),
            'cod_residente' => $datos['atencion']->cod_residente]);
        $migracion = require database_path('migrations/2026_11_18_000003_partition_professional_assessments.php');
        $migracion->down();
        $this->assertCount(51, Schema::getColumnListing('valoraciones_profesionales'));
        $this->assertFalse(Schema::hasTable('valoraciones_psicopedagogicas'));
        $this->assertSame(4, DB::table('valoraciones_profesionales')->count());
        $migracion->up();
        $contexto->up();
        $this->assertSame($antes, $this->capturar());
        $this->assertSame($soportes, DB::table('soportes_valoracion_petersen')->orderBy('cod_soporte_petersen')->get()->toJson());
    }

    public function test_un_contrato_modificado_impide_reversion_parcial(): void
    {
        $this->escenario();
        DB::statement('ALTER TABLE valoraciones_psicopedagogicas ADD COLUMN campo_no_aprobado varchar(10)');
        $contexto = require database_path('migrations/2026_11_18_000004_derive_professional_assessment_resident.php');
        $contexto->down();
        try {
            (require database_path('migrations/2026_11_18_000003_partition_professional_assessments.php'))->down();
            $this->fail('Debe conservarse el esquema actual cuando cambia un contrato.');
        } catch (\LogicException $error) {
            $this->assertStringContainsString('Revierta primero', $error->getMessage());
        }
        $contexto->up();
        $this->assertTrue(Schema::hasTable('valoraciones_profesionales'));
        $this->assertTrue(Schema::hasTable('valoraciones_psicopedagogicas'));
        $this->assertFalse(Schema::hasTable('valoraciones_funcionales'));
        $this->assertSame(1, ValoracionPsicologica::count());
        $this->assertSame(1, ValoracionFuncional::count());
    }

    private function capturar(): array
    {
        return array_map(static fn ($tabla) => DB::table($tabla)->orderBy('cod_valoracion_profesional')->get()->toJson(),
            ['valoraciones_profesionales', 'valoraciones_psicopedagogicas']);
    }
}
