<?php

namespace Tests\Feature;

use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Livewire\Cuidados\AsignacionTurnoPanel;
use App\Livewire\Cuidados\TurnosEnfermeriaPanel;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Livewire\Livewire;
use Tests\TestCase;

class EnfermeriaV2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_enfermeria_resuelve_turno_y_alcance_desde_asignaciones_v2(): void
    {
        [$enfermera, $residente, $turno] = $this->escenarioAsignado();
        $service = app(TurnoEnfermeriaService::class);

        $this->assertSame($turno->cod_turno, $service->obtenerTurnoActivo($enfermera)?->cod_turno);
        $this->assertTrue($service->esPacienteAsignado($residente, $enfermera, $turno->cod_turno));
        $this->assertSame(
            [$residente->cod_residente],
            $service->obtenerPacientesAsignadosIds($enfermera, $turno->cod_turno)
        );
        $this->assertSame(
            $turno->cod_turno,
            $service->autorizarMutacionPaciente($residente, 'signos_vitales.crear', $enfermera)->cod_turno
        );
    }

    public function test_enfermeria_no_puede_mutar_un_residente_ajeno(): void
    {
        [$enfermera] = $this->escenarioAsignado();
        $ajeno = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_AJENO',
            'nombres' => 'Paciente',
            'apellido_paterno' => 'Ajeno',
            'fecha_nacimiento' => '1940-01-01',
            'estado' => 'ADMITIDO',
        ]);

        $this->expectException(HttpException::class);
        app(TurnoEnfermeriaService::class)
            ->autorizarMutacionPaciente($ajeno, 'signos_vitales.crear', $enfermera);
    }

    public function test_superadministrador_conserva_lectura_pero_no_escribe_como_enfermeria(): void
    {
        [, $residente] = $this->escenarioAsignado();
        $super = User::query()->where('correo', 'admincasaamandita@gmail.com')->firstOrFail();
        $service = app(TurnoEnfermeriaService::class);

        $this->assertTrue($service->esPacienteAsignado($residente, $super));
        $this->expectException(HttpException::class);
        $service->autorizarMutacionPaciente($residente, 'signos_vitales.crear', $super);
    }

    public function test_panel_restaurado_gestiona_turnos_en_la_tabla_v2(): void
    {
        $super = User::query()->where('correo', 'admincasaamandita@gmail.com')->firstOrFail();
        $this->actingAs($super);

        Livewire::test(TurnosEnfermeriaPanel::class)
            ->call('abrirCrear')
            ->set('nombre', 'NOCHE TEST')
            ->set('horaInicio', '20:00')
            ->set('horaFin', '06:00')
            ->set('orden', '9')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('turnos', [
            'nombre' => 'NOCHE TEST',
            'hora_inicio' => '20:00:00',
            'hora_cierre' => '06:00:00',
        ]);
        $this->assertDatabaseMissing('migrations', ['migration' => 'create_turnos_enfermeria_table']);
    }

    public function test_panel_restaurado_asigna_residente_a_jornada_v2_sin_modificar_su_cama(): void
    {
        [$enfermera, , $turno] = $this->escenarioAsignado();
        $super = User::query()->where('correo', 'admincasaamandita@gmail.com')->firstOrFail();
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_ASIGNACION_PANEL',
            'nombres' => 'Julia',
            'apellido_paterno' => 'Flores',
            'fecha_nacimiento' => '1942-05-10',
            'estado' => 'ADMITIDO',
        ]);
        $ocupacionesAntes = $residente->ocupacionesCama()->count();

        $this->actingAs($super);

        Livewire::test(AsignacionTurnoPanel::class)
            ->call('abrirCrear')
            ->set('codAm', $residente->cod_residente)
            ->set('codTurno', $turno->cod_turno)
            ->set('codEnfermero', $enfermera->cod_usuario)
            ->set('fechaInicio', today()->format('Y-m-d'))
            ->set('nivelSupervision', 'ESTANDAR')
            ->set('motivoAsignacion', 'Cobertura asistencial de la jornada actual.')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('asignaciones_residente_jornada', [
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $enfermera->personal->cod_personal,
            'estado' => 'ACTIVA',
        ]);
        $this->assertSame($ocupacionesAntes, $residente->fresh()->ocupacionesCama()->count());
    }

    private function escenarioAsignado(): array
    {
        $enfermera = User::factory()->create();
        $enfermera->assignRole('ENFERMEROS');
        $personal = Personal::query()->create([
            'cod_personal' => 'PER_ENF_TEST',
            'cod_usuario' => $enfermera->cod_usuario,
            'nombres' => 'Elena',
            'apellido_paterno' => 'Rojas',
            'numero_documento' => 'ENF-TEST',
            'profesion' => 'ENFERMERÍA',
            'estado' => 'ACTIVO',
        ]);
        $turno = TurnoEnfermeria::query()->create([
            'cod_turno' => 'TUR_ENF_TEST',
            'nombre' => 'TURNO COMPLETO',
            'hora_inicio' => '00:00:00',
            'hora_cierre' => '23:59:59',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_ENF_TEST',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_ENF_TEST',
            'nombres' => 'Rosa',
            'apellido_paterno' => 'Mamani',
            'fecha_nacimiento' => '1945-01-01',
            'estado' => 'ADMITIDO',
        ]);
        AsignacionResidenteJornada::query()->create([
            'cod_asignacion' => 'ARJ_ENF_TEST',
            'cod_residente' => $residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $personal->cod_personal,
            'nivel_supervision' => 'ESTANDAR',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        return [$enfermera, $residente, $turno];
    }
}
