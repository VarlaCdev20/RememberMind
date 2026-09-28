<?php

namespace Tests\Feature;

use App\Backend\Modulos\Clinica\Servicios\ContextoClinicoService;
use App\Models\AdultoMayor;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\Jornada;
use App\Models\Prescripcion;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class ContextoClinicoIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_atribuye_una_operacion_al_primer_personal_disponible(): void
    {
        User::factory()->create([
            'nombres' => 'Personal',
            'ap_paterno' => 'Ajeno',
        ]);
        $usuarioSinPersonal = User::factory()->create();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('El usuario autenticado no tiene un perfil de personal activo.');

        app(ContextoClinicoService::class)->personalActivo($usuarioSinPersonal);
    }

    public function test_no_usa_un_area_institucional_arbitraria_como_area_clinica(): void
    {
        $usuario = User::factory()->create([
            'nombres' => 'Marta',
            'ap_paterno' => 'Médica',
        ]);
        Area::query()->create([
            'cod_area' => 'ARE_ADMIN_TEST',
            'nombre' => 'Administración',
            'estado' => 'ACTIVA',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('El personal no tiene un área clínica activa asignada para la jornada vigente.');

        app(ContextoClinicoService::class)->areaAtencion($usuario->personal()->firstOrFail());
    }

    public function test_no_infiere_area_clinica_por_nombre_cuando_no_hay_asignacion(): void
    {
        $usuario = User::factory()->create([
            'nombres' => 'Marta',
            'ap_paterno' => 'Médica',
        ]);
        Area::query()->create([
            'cod_area' => 'ARE_CLINICA_TEST',
            'nombre' => 'Área de atención médica',
            'estado' => 'ACTIVA',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('El personal no tiene un área clínica activa asignada para la jornada vigente.');

        app(ContextoClinicoService::class)->areaAtencion($usuario->personal()->firstOrFail());
    }

    public function test_prioriza_el_area_activa_asignada_al_profesional(): void
    {
        $usuario = User::factory()->create([
            'nombres' => 'Marta',
            'ap_paterno' => 'Médica',
        ]);
        $personal = $usuario->personal()->firstOrFail();
        $areaAsignada = Area::query()->create([
            'cod_area' => 'ARE_ASIGNADA_TEST',
            'nombre' => 'Consulta geriátrica',
            'estado' => 'ACTIVA',
        ]);
        Area::query()->create([
            'cod_area' => 'ARE_MEDICA_TEST',
            'nombre' => 'Área médica general',
            'estado' => 'ACTIVA',
        ]);
        $turno = Turno::query()->create([
            'cod_turno' => 'TUR_CTX_TEST',
            'nombre' => 'Turno clínico',
            'hora_inicio' => '08:00',
            'hora_cierre' => '16:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_CTX_TEST',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        AsignacionPersonal::query()->create([
            'cod_asignacion_personal' => 'ASP_CTX_TEST',
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $personal->cod_personal,
            'cod_area' => $areaAsignada->cod_area,
            'fecha_asignacion' => now(),
            'estado' => 'ACTIVA',
        ]);

        $resuelta = app(ContextoClinicoService::class)->areaAtencion($personal);

        $this->assertTrue($resuelta->is($areaAsignada));
    }

    public function test_una_prescripcion_crea_su_propia_atencion_con_el_responsable_real(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $medico = User::factory()->create([
            'nombres' => 'Mario',
            'ap_paterno' => 'Médico',
        ]);
        $medico->assignRole('MEDICO GENERAL/GERIATRA');
        $area = Area::query()->create([
            'cod_area' => 'ARE_RECETA_TEST',
            'nombre' => 'Área médica de pruebas',
            'estado' => 'ACTIVA',
        ]);
        $turno = Turno::query()->create([
            'cod_turno' => 'TUR_RECETA_TEST',
            'nombre' => 'Turno receta',
            'hora_inicio' => '00:00',
            'hora_cierre' => '23:59',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_RECETA_TEST',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        AsignacionPersonal::query()->create([
            'cod_asignacion_personal' => 'ASP_RECETA_TEST',
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $medico->personal()->firstOrFail()->cod_personal,
            'cod_area' => $area->cod_area,
            'fecha_asignacion' => now(),
            'estado' => 'ACTIVA',
        ]);
        $residente = AdultoMayor::factory()->create();
        $atencionPrevia = Atencion::query()->create([
            'cod_atencion' => 'ATN_PREVIA_TEST',
            'cod_residente' => $residente->cod_residente,
            'cod_area' => $area->cod_area,
            'cod_personal' => $medico->personal()->firstOrFail()->cod_personal,
            'tipo_atencion' => 'SEGUIMIENTO_ENFERMERIA',
            'motivo' => 'Atención previa ajena a la receta',
            'fecha_hora' => now()->subHour(),
            'estado' => 'COMPLETADA',
        ]);

        $this->actingAs($medico)->post(route('admin.adultos-mayores.medicacion.store', $residente), [
            'nombre_medicamento' => 'Paracetamol',
            'dosis' => '500 mg',
            'frecuencia' => 'Cada 8 horas',
            'via_administracion' => 'ORAL',
            'hora_programada' => '08:00',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ])->assertRedirect();

        $prescripcion = Prescripcion::query()->sole();
        $this->assertNotSame($atencionPrevia->cod_atencion, $prescripcion->cod_atencion);
        $this->assertSame($medico->personal()->firstOrFail()->cod_personal, $prescripcion->cod_personal);
        $this->assertDatabaseHas('atenciones', [
            'cod_atencion' => $prescripcion->cod_atencion,
            'cod_area' => $area->cod_area,
            'cod_personal' => $prescripcion->cod_personal,
            'tipo_atencion' => 'PRESCRIPCION_MEDICA',
        ]);
    }
}
