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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

    public function test_reparte_cuatro_tipos_en_dos_tablas_de_treinta_columnas(): void
    {
        $datos = $this->escenario();
        $this->assertCount(30, Schema::getColumnListing('valoraciones_profesionales'));
        $this->assertCount(30, Schema::getColumnListing('valoraciones_psicopedagogicas'));
        $this->assertCount(95, Schema::getTableListing());
        $this->assertSame(1, ValoracionPsicologica::count());
        $this->assertSame(1, SeguimientoPedagogico::count());
        $this->assertSame(1, ValoracionFuncional::count());
        $this->assertSame(1, ValoracionNutricional::count());
        $this->assertSame('1500.25', (string) $datos['nutricional']->fresh()->requerimiento_hidrico);
        $this->assertSame($datos['atencion']->getKey(), $datos['psicologica']->fresh()->atencion->getKey());
        $this->assertFalse($datos['pedagogica']->fresh()->cambio_desempeno);
        $this->assertNull(ValoracionPsicologica::find($datos['pedagogica']->getKey()));
        $this->assertNull(ValoracionNutricional::find($datos['funcional']->getKey()));
        $this->assertSame($datos['atencion']->getKey(), ValoracionPsicologica::with('atencion')->findOrFail($datos['psicologica']->getKey())->atencion->getKey());
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

    public function test_reversion_poblada_conserva_fechas_autoria_decimal_y_referencias(): void
    {
        $this->escenario();
        $antes = $this->capturar();
        $soportes = DB::table('soportes_valoracion_petersen')->orderBy('cod_soporte_petersen')->get()->toJson();
        $migracion = require database_path('migrations/2026_11_18_000003_partition_professional_assessments.php');
        $migracion->down();
        $this->assertCount(51, Schema::getColumnListing('valoraciones_profesionales'));
        $this->assertFalse(Schema::hasTable('valoraciones_psicopedagogicas'));
        $this->assertSame(4, DB::table('valoraciones_profesionales')->count());
        $migracion->up();
        $this->assertSame($antes, $this->capturar());
        $this->assertSame($soportes, DB::table('soportes_valoracion_petersen')->orderBy('cod_soporte_petersen')->get()->toJson());
    }

    public function test_un_contrato_modificado_impide_reversion_parcial(): void
    {
        $this->escenario();
        DB::statement('ALTER TABLE valoraciones_psicopedagogicas ADD COLUMN campo_no_aprobado varchar(10)');
        try {
            (require database_path('migrations/2026_11_18_000003_partition_professional_assessments.php'))->down();
            $this->fail('Debe conservarse el esquema actual cuando cambia un contrato.');
        } catch (\LogicException $error) {
            $this->assertStringContainsString('Revierta primero', $error->getMessage());
        }
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
