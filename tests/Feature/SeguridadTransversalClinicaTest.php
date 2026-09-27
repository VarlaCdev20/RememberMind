<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\Jornada;
use App\Models\Instrumento;
use App\Models\OpcionPregunta;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguridadTransversalClinicaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_valoracion_profesional_exige_competencia_y_contexto_formal(): void
    {
        [$psicologa, $residente, $atencion] = $this->escenarioPsicologia(true);

        $this->actingAs($psicologa)->postJson(route('admin.valoraciones.store', [$residente, 'psicologia']), [
            'cod_atencion' => $atencion->cod_atencion,
            'estado_animo' => 'ESTABLE',
            'conclusion' => 'Evolución favorable con acompañamiento profesional.',
        ])->assertCreated();

        $this->assertDatabaseHas('valoraciones_psicologicas', [
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $psicologa->personal()->sole()->cod_personal,
            'estado_animo' => 'ESTABLE',
        ]);
    }

    public function test_permiso_aislado_no_sustituye_la_competencia_profesional(): void
    {
        [, $residente, $atencion, $area, $jornada] = $this->escenarioPsicologia(true);
        $nutricionista = User::factory()->create(['nombres' => 'Nora', 'ap_paterno' => 'Nutricionista']);
        $nutricionista->assignRole('NUTRICIONISTA');
        $nutricionista->givePermissionTo('valoraciones_psicologicas.crear');
        AsignacionPersonal::query()->create([
            'cod_asignacion_personal' => 'ASP_NUT_SEG',
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $nutricionista->personal()->sole()->cod_personal,
            'cod_area' => $area->cod_area,
            'fecha_asignacion' => now(),
            'estado' => 'ACTIVA',
        ]);

        $this->actingAs($nutricionista)->postJson(route('admin.valoraciones.store', [$residente, 'psicologia']), [
            'cod_atencion' => $atencion->cod_atencion,
            'estado_animo' => 'ESTABLE',
        ])->assertForbidden();

        $this->assertDatabaseCount('valoraciones_psicologicas', 0);
    }

    public function test_profesional_sin_asignacion_vigente_no_puede_registrar(): void
    {
        [$psicologa, $residente, $atencion] = $this->escenarioPsicologia(false);

        $this->actingAs($psicologa)->postJson(route('admin.valoraciones.store', [$residente, 'psicologia']), [
            'cod_atencion' => $atencion->cod_atencion,
            'estado_animo' => 'ESTABLE',
        ])->assertForbidden();

        $this->assertDatabaseCount('valoraciones_psicologicas', 0);
    }

    public function test_instrumento_calcula_puntaje_desde_la_opcion_activa(): void
    {
        [$psicologa, $residente, $atencion] = $this->escenarioPsicologia(true);
        $instrumento = Instrumento::query()->create([
            'cod_instrumento' => 'INS_SEG_SCORE',
            'codigo' => 'SEG-1',
            'nombre' => 'Escala segura',
            'tipo' => 'COGNITIVO',
            'estado' => 'ACTIVO',
        ]);
        $pregunta = PreguntaInstrumento::query()->create([
            'cod_pregunta' => 'PRE_SEG_SCORE',
            'cod_instrumento' => $instrumento->cod_instrumento,
            'codigo' => 'P1',
            'enunciado' => '¿Respuesta observada?',
            'tipo_respuesta' => 'OPCION',
            'puntaje_maximo' => 2,
            'orden' => 1,
            'estado' => 'ACTIVA',
        ]);
        $opcion = OpcionPregunta::query()->create([
            'cod_opcion' => 'OPC_SEG_SCORE',
            'cod_pregunta' => $pregunta->cod_pregunta,
            'nombre' => 'Adecuada',
            'valor' => 'SI',
            'puntaje' => 2,
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $respuesta = $this->actingAs($psicologa)->postJson(route('admin.instrumentos.aplicar', [$instrumento, $residente]), [
            'cod_atencion' => $atencion->cod_atencion,
            'puntaje_total' => 999,
            'puntaje_maximo' => 999,
            'respuestas' => [[
                'cod_pregunta' => $pregunta->cod_pregunta,
                'cod_opcion' => $opcion->cod_opcion,
                'puntaje' => 999,
            ]],
        ])->assertCreated();

        $this->assertSame(2, (int) $respuesta->json('puntaje_total'));
        $this->assertSame(2, (int) $respuesta->json('puntaje_maximo'));
        $this->assertDatabaseHas('respuestas_instrumento', [
            'cod_aplicacion' => $respuesta->json('cod_aplicacion'),
            'cod_pregunta' => $pregunta->cod_pregunta,
            'puntaje' => 2,
        ]);
    }

    /** @return array{User, Residente, Atencion, Area, Jornada} */
    private function escenarioPsicologia(bool $asignar): array
    {
        $psicologa = User::factory()->create(['nombres' => 'Paula', 'ap_paterno' => 'Psicóloga']);
        $psicologa->assignRole('PSICOLOGO/A');
        $residente = Residente::factory()->create(['estado' => 'ACTIVO']);
        $area = Area::query()->create([
            'cod_area' => 'ARE_PSI_SEG',
            'nombre' => 'Psicología clínica',
            'estado' => 'ACTIVA',
        ]);
        $turno = Turno::query()->create([
            'cod_turno' => 'TUR_PSI_SEG',
            'nombre' => 'Jornada clínica',
            'hora_inicio' => '00:00',
            'hora_cierre' => '23:59',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_PSI_SEG',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);

        if ($asignar) {
            AsignacionPersonal::query()->create([
                'cod_asignacion_personal' => 'ASP_PSI_SEG',
                'cod_jornada' => $jornada->cod_jornada,
                'cod_personal' => $psicologa->personal()->sole()->cod_personal,
                'cod_area' => $area->cod_area,
                'fecha_asignacion' => now(),
                'estado' => 'ACTIVA',
            ]);
        }

        $atencion = Atencion::query()->create([
            'cod_atencion' => 'ATE_PSI_SEG',
            'cod_residente' => $residente->cod_residente,
            'cod_area' => $area->cod_area,
            'cod_personal' => $psicologa->personal()->sole()->cod_personal,
            'tipo_atencion' => 'VALORACION_PSICOLOGICA',
            'fecha_hora' => now(),
            'estado' => 'ABIERTA',
        ]);

        return [$psicologa, $residente, $atencion, $area, $jornada];
    }
}
