<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Alertas\CampanaNotificaciones;
use App\Frontend\Livewire\Medico\Medicacion\SaludMedicacionPanel;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\Turno;
use App\Models\User;
use App\Backend\Modulos\Medicacion\Servicios\AgendaMedicacionService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AgendaMedicacionTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;
    private AdultoMayor $adulto;
    private Atencion $atencion;
    private Jornada $jornada;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class]);
        $this->usuario = User::factory()->create([
            'nombres' => 'Mario',
            'ap_paterno' => 'Médico',
            'estado' => 'ACTIVO',
        ]);
        $this->usuario->assignRole('MEDICO GENERAL/GERIATRA');
        $this->adulto = AdultoMayor::factory()->create(['estado' => 'ADMITIDO']);

        $area = Area::query()->create([
            'cod_area' => 'ARE_MED',
            'nombre' => 'Atención médica',
            'estado' => 'ACTIVA',
        ]);
        $this->atencion = Atencion::query()->create([
            'cod_atencion' => 'ATE_AGENDA',
            'cod_residente' => $this->adulto->cod_residente,
            'cod_area' => $area->cod_area,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'tipo_atencion' => 'CONSULTA',
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
        ]);
        $turno = Turno::query()->create([
            'cod_turno' => 'TUR_AGENDA',
            'nombre' => 'Mañana',
            'hora_inicio' => '07:00',
            'hora_cierre' => '15:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $this->jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_AGENDA',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => '2026-09-10',
            'estado' => 'ABIERTA',
        ]);
        $this->actingAs($this->usuario);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_prescribir_no_crea_una_administracion_u_omision_ficticia(): void
    {
        Carbon::setTestNow('2026-09-10 07:30:00');

        Livewire::test(SaludMedicacionPanel::class, ['adulto' => $this->adulto])
            ->set('nuevo_cod_residente', $this->adulto->cod_residente)
            ->set('nuevo_nombre', 'Losartán')
            ->set('nuevo_dosis', '50 mg')
            ->set('nuevo_frecuencia', 'Cada 24 horas')
            ->set('nuevo_via', 'ORAL')
            ->set('nuevo_hora', '08:00')
            ->set('nuevo_fecha_inicio', '2026-09-10')
            ->set('nuevo_medico', '')
            ->call('guardarNuevoMedicamento')
            ->assertHasNoErrors();

        $prescripcion = Prescripcion::query()->with(['medicamento', 'horarios'])->sole();
        $this->assertSame($this->adulto->cod_residente, $prescripcion->cod_residente);
        $this->assertSame('LOSARTÁN', $prescripcion->medicamento->nombre_generico);
        $this->assertSame('08:00', substr((string) $prescripcion->horarios->sole()->hora_programada, 0, 5));
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_agenda_cambia_de_proxima_a_administrada_segun_el_registro_real(): void
    {
        Carbon::setTestNow('2026-09-10 07:30:00');
        $medicacion = $this->crearMedicacion('08:00');

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_residente);
        $this->assertSame('PROXIMA', $agenda->first()['estado']);

        AdministracionMedicacion::create([
            'cod_administracion' => 'ADM_AGENDA',
            'cod_prescripcion' => $medicacion->cod_prescripcion,
            'cod_horario_prescripcion' => $medicacion->horarios()->value('cod_horario_prescripcion'),
            'cod_residente' => $this->adulto->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'fecha_hora_programada' => '2026-09-10 08:00:00',
            'fecha_hora_administracion' => '2026-09-10 07:35:00',
            'resultado' => 'ADMINISTRADA',
            'estado' => 'FINALIZADO',
        ]);

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_residente);
        $this->assertSame('ADMINISTRADA', $agenda->first()['estado']);
    }

    public function test_campana_muestra_y_cuenta_una_dosis_vencida(): void
    {
        Carbon::setTestNow('2026-09-10 09:00:00');
        $this->crearMedicacion('08:00');

        Livewire::test(CampanaNotificaciones::class)
            ->assertSet('conteoAbiertas', 1)
            ->assertSee('Losartán')
            ->assertSee('Vencida')
            ->assertSee('Programada: 08:00');
    }

    public function test_varios_horarios_v2_generan_todas_las_dosis_del_dia(): void
    {
        Carbon::setTestNow('2026-09-10 07:30:00');
        $medicacion = $this->crearMedicacion('08:00');
        $medicacion->update(['frecuencia' => 'Cada 8 horas']);
        foreach (['00:00', '16:00'] as $indice => $hora) {
            HorarioPrescripcion::query()->create([
                'cod_horario_prescripcion' => 'HPR_EXTRA_'.$indice,
                'cod_prescripcion' => $medicacion->cod_prescripcion,
                'hora_programada' => $hora,
                'dosis_programada' => 50,
                'estado' => 'ACTIVO',
            ]);
        }

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_residente);

        $this->assertSame(['00:00', '08:00', '16:00'], $agenda->pluck('hora')->all());
        $this->assertSame(['VENCIDA', 'PROXIMA', 'PENDIENTE'], $agenda->pluck('estado')->all());
    }

    private function crearMedicacion(string $hora): Prescripcion
    {
        $medicamento = Medicamento::query()->firstOrCreate(
            ['cod_medicamento' => 'MED_AGENDA'],
            ['nombre_generico' => 'Losartán', 'control_especial' => false, 'estado' => 'ACTIVO'],
        );
        $prescripcion = Prescripcion::query()->create([
            'cod_prescripcion' => 'PRE_AGENDA',
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $medicamento->cod_medicamento,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'dosis' => 50,
            'unidad_dosis' => 'mg',
            'frecuencia' => 'Cada 24 horas',
            'via_administracion' => 'ORAL',
            'estado' => 'ACTIVA',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => '2026-09-01 08:00:00',
        ]);
        HorarioPrescripcion::query()->create([
            'cod_horario_prescripcion' => 'HPR_AGENDA',
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'hora_programada' => $hora,
            'dosis_programada' => 50,
            'estado' => 'ACTIVO',
        ]);

        return $prescripcion;
    }
}
