<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\AgendaEnfermeria;
use App\Livewire\Cuidados\FichaPaciente;
use App\Livewire\Cuidados\MisPacientes;
use App\Livewire\Medicacion\MedicacionAdultoModal;
use App\Livewire\Medicacion\SaludMedicacionPanel;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionTurnoAdulto;
use App\Models\MedicacionAdulto;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\AgendaTurnoService;
use App\Services\Identidad\SidebarService;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class SuperadminEnfermeriaSupervisionTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $enfermeroUno;
    private User $enfermeroDos;
    private AdultoMayor $residenteUno;
    private AdultoMayor $residenteDos;
    private TurnoEnfermeria $turnoManana;
    private TurnoEnfermeria $turnoNoche;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-11 10:00:00');
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

        $this->superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $this->superadmin->assignRole('SUPERADMINISTRADOR');
        $this->enfermeroUno = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermeroUno->assignRole('ENFERMEROS');
        $this->enfermeroDos = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermeroDos->assignRole('ENFERMEROS');

        $this->turnoManana = TurnoEnfermeria::create([
            'orden' => 1, 'nombre' => 'Mañana', 'hora_inicio' => '07:00', 'hora_fin' => '15:00', 'estado' => 'ACTIVO',
        ]);
        $this->turnoNoche = TurnoEnfermeria::create([
            'orden' => 2, 'nombre' => 'Noche', 'hora_inicio' => '19:00', 'hora_fin' => '07:00', 'estado' => 'ACTIVO',
        ]);

        $this->residenteUno = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001', 'nombres' => 'Elena', 'ap_paterno' => 'Flores',
        ]);
        $this->residenteDos = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001', 'nombres' => 'Manuela', 'ap_paterno' => 'Quispe',
        ]);

        $this->asignar($this->residenteUno, $this->enfermeroUno, $this->turnoManana);
        $this->asignar($this->residenteDos, $this->enfermeroDos, $this->turnoNoche);

        foreach ([$this->residenteUno, $this->residenteDos] as $residente) {
            MedicacionAdulto::create([
                'cod_am' => $residente->cod_am,
                'nombre_medicamento' => $residente->is($this->residenteUno) ? 'Losartán' : 'Metformina',
                'dosis' => '1 comprimido', 'frecuencia' => 'DIARIA', 'via_administracion' => 'ORAL',
                'hora_programada' => '08:00', 'fecha_inicio' => today(), 'estado' => 'ACTIVO',
            ]);
        }

        AlertaAdulto::create([
            'cod_am' => $this->residenteDos->cod_am, 'origen' => 'SIGNOS',
            'tipo_alerta' => 'SATURACIÓN BAJA', 'nivel' => 'CRITICO',
            'motivo' => 'Control global requerido.', 'estado' => 'ABIERTA',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_superadmin_ve_la_agenda_global_de_distintos_turnos(): void
    {
        $agenda = app(AgendaTurnoService::class)->generar($this->superadmin);

        $this->assertTrue($agenda->contains(fn (array $item) => $item['paciente']?->is($this->residenteUno)));
        $this->assertTrue($agenda->contains(fn (array $item) => $item['paciente']?->is($this->residenteDos)));

        $this->actingAs($this->superadmin);
        Livewire::test(AgendaEnfermeria::class)
            ->assertSee('Agenda institucional de Enfermería')
            ->assertSee('Elena')
            ->assertSee('Manuela')
            ->assertDontSee('Recibir turno');
    }

    public function test_enfermero_conserva_el_alcance_de_sus_pacientes_asignados(): void
    {
        $agenda = app(AgendaTurnoService::class)->generar($this->enfermeroUno);

        $this->assertTrue($agenda->contains(fn (array $item) => $item['paciente']?->is($this->residenteUno)));
        $this->assertFalse($agenda->contains(fn (array $item) => $item['paciente']?->is($this->residenteDos)));

        $this->actingAs($this->enfermeroUno);
        Livewire::test(MisPacientes::class)
            ->assertSee('Elena')
            ->assertDontSee('Manuela');
    }

    public function test_superadmin_consulta_residentes_ficha_alertas_registros_y_reportes_globales(): void
    {
        $this->actingAs($this->superadmin);

        Livewire::test(MisPacientes::class)
            ->assertSee('Supervisión de residentes')
            ->assertSee('Elena')
            ->assertSee('Manuela');
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residenteDos->cod_am])
            ->assertSee('Manuela')
            ->assertSee('SATURACIÓN BAJA');

        foreach (['dashboard', 'agenda', 'registros', 'alertas', 'tareas', 'pase-turno', 'reportes'] as $ruta) {
            $this->get(route('admin.enfermeria.'.$ruta))->assertOk();
        }
    }

    public function test_sidebar_superadmin_expone_la_supervision_clinica_completa(): void
    {
        $this->actingAs($this->superadmin);
        $sidebar = app(SidebarService::class)->getSidebar();
        $seccion = collect($sidebar)->firstWhere('title', 'Supervisión de Enfermería');

        $this->assertNotNull($seccion);
        $rutas = collect($seccion['items'])->pluck('route');
        $this->assertContains('admin.enfermeria.dashboard', $rutas);
        $this->assertContains('admin.enfermeria.agenda', $rutas);
        $this->assertContains('admin.salud-seguimiento.medicacion.index', $rutas);
        $this->assertContains('admin.salud-seguimiento.administracion.index', $rutas);
        $this->assertContains('admin.enfermeria.registros', $rutas);
        $this->assertContains('admin.enfermeria.alertas', $rutas);
        $this->assertContains('admin.enfermeria.reportes', $rutas);
    }

    public function test_enfermeria_puede_administrar_pero_no_crear_ni_modificar_ordenes_medicas(): void
    {
        $this->actingAs($this->enfermeroUno);
        $medicacion = MedicacionAdulto::where('cod_am', $this->residenteUno->cod_am)->firstOrFail();

        $this->assertTrue($this->enfermeroUno->can('administracion_medicacion.registrar'));
        $this->assertFalse($this->enfermeroUno->can('medicacion.crear'));
        $this->assertFalse($this->enfermeroUno->can('salud.medicacion.editar'));

        Livewire::test(SaludMedicacionPanel::class)
            ->call('toggleFormularioCrear')
            ->assertForbidden();

        Livewire::test(SaludMedicacionPanel::class)
            ->call('suspenderMedicamento', $medicacion->cod_med_adulto)
            ->assertForbidden();

        Livewire::test(MedicacionAdultoModal::class)
            ->call('abrirModalMedicacion', $this->residenteUno->cod_am)
            ->assertForbidden();

        $this->assertDatabaseHas('medicacion_adulto', [
            'cod_med_adulto' => $medicacion->cod_med_adulto,
            'estado' => 'ACTIVO',
        ]);
    }

    private function asignar(AdultoMayor $residente, User $enfermero, TurnoEnfermeria $turno): void
    {
        AsignacionTurnoAdulto::create([
            'cod_am' => $residente->cod_am, 'cod_turno' => $turno->cod_turno,
            'cod_usu_enfermero' => $enfermero->cod_usu, 'fecha_inicio' => today(),
            'nivel_supervision' => 'ESTANDAR', 'estado' => 'ACTIVA',
            'motivo_asignacion' => 'Cobertura de prueba', 'asignado_por' => $this->superadmin->cod_usu,
        ]);
    }
}
