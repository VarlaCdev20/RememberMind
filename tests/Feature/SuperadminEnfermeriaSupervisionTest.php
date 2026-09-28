<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Cuidados\AgendaEnfermeria;
use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Frontend\Livewire\Enfermeria\Cuidados\MisPacientes;
use App\Frontend\Livewire\Medico\Medicacion\MedicacionAdultoModal;
use App\Frontend\Livewire\Medico\Medicacion\SaludMedicacionPanel;
use App\Models\AdultoMayor;
use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Backend\Modulos\Enfermeria\Servicios\AgendaTurnoService;
use App\Backend\Modulos\Identidad\Servicios\SidebarService;
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
        $this->seed([ RolesAndPermissionsSeeder::class]);

        $this->superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $this->superadmin->assignRole('SUPERADMINISTRADOR');
        $this->enfermeroUno = User::factory()->create([
            'estado' => 'ACTIVO', 'nombres' => 'Enfermera Uno', 'ap_paterno' => 'Prueba',
        ]);
        $this->enfermeroUno->assignRole('ENFERMEROS');
        $this->enfermeroDos = User::factory()->create([
            'estado' => 'ACTIVO', 'nombres' => 'Enfermera Dos', 'ap_paterno' => 'Prueba',
        ]);
        $this->enfermeroDos->assignRole('ENFERMEROS');

        Area::create([
            'cod_area' => 'ARE_ENF_SUP', 'nombre' => 'Enfermería',
            'descripcion' => 'Área clínica de supervisión', 'estado' => 'ACTIVO',
        ]);

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

        foreach ([$this->residenteUno, $this->residenteDos] as $indice => $residente) {
            $nombre = $residente->is($this->residenteUno) ? 'Losartán' : 'Metformina';
            $personal = $residente->is($this->residenteUno)
                ? $this->enfermeroUno->personal
                : $this->enfermeroDos->personal;
            $medicamento = Medicamento::create([
                'cod_medicamento' => 'MED_SUP_'.$indice,
                'nombre_generico' => $nombre,
                'nombre_comercial' => $nombre,
                'concentracion' => '1 comprimido',
                'forma_farmaceutica' => 'COMPRIMIDO',
                'unidad' => 'comprimido',
                'via_predeterminada' => 'ORAL',
                'control_especial' => false,
                'estado' => 'ACTIVO',
            ]);
            $atencion = Atencion::create([
                'cod_atencion' => 'ATN_SUP_'.$indice,
                'cod_residente' => $residente->cod_residente,
                'cod_area' => 'ARE_ENF_SUP',
                'cod_personal' => $personal->cod_personal,
                'tipo_atencion' => 'CONTROL_MEDICO',
                'motivo' => 'Prescripción de supervisión',
                'fecha_hora' => now(),
                'estado' => 'COMPLETADA',
            ]);
            $prescripcion = Prescripcion::create([
                'cod_prescripcion' => 'PRS_SUP_'.$indice,
                'cod_residente' => $residente->cod_residente,
                'cod_atencion' => $atencion->cod_atencion,
                'cod_medicamento' => $medicamento->cod_medicamento,
                'cod_personal' => $personal->cod_personal,
                'dosis' => 1,
                'unidad_dosis' => 'comprimido',
                'frecuencia' => 'DIARIA',
                'via_administracion' => 'ORAL',
                'indicacion' => 'Control diario',
                'segun_necesidad' => false,
                'fecha_hora_prescripcion' => now(),
                'estado' => 'ACTIVA',
            ]);
            HorarioPrescripcion::create([
                'cod_horario_prescripcion' => 'HPR_SUP_'.$indice,
                'cod_prescripcion' => $prescripcion->cod_prescripcion,
                'hora_programada' => '08:00:00',
                'dosis_programada' => 1,
                'estado' => 'ACTIVO',
            ]);
        }

        Alerta::create([
            'cod_residente' => $this->residenteDos->cod_residente,
            'modulo' => 'SIGNOS',
            'tipo' => 'SATURACIÓN BAJA',
            'prioridad' => 'CRITICO',
            'descripcion' => 'Control global requerido.',
            'fecha_hora' => now(),
            'estado' => 'ABIERTA',
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
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residenteDos->cod_residente])
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
        $todasLasRutas = collect($sidebar)->flatMap(fn (array $item) => collect($item['items'] ?? [])->pluck('route'));
        $this->assertSame(1, $todasLasRutas->filter(fn ($ruta) => $ruta === 'admin.enfermeria.dashboard')->count());
        $this->assertContains('admin.enfermeria.agenda', $rutas);
        $this->assertContains('admin.salud-seguimiento.medicacion.index', $rutas);
        $this->assertContains('admin.salud-seguimiento.administracion.index', $rutas);
        $this->assertContains('admin.enfermeria.registros', $rutas);
        $this->assertContains('admin.enfermeria.alertas', $rutas);
        $this->assertContains('admin.enfermeria.reportes', $rutas);
    }

    public function test_superadmin_puede_ver_administraciones_pero_no_crear_administracion_solo_por_su_rol(): void
    {
        $this->actingAs($this->superadmin);

        $this->assertTrue($this->superadmin->can('administraciones_medicacion.ver'));
        $this->assertFalse(app(\App\Policies\AdministracionMedicacionPolicy::class)->create($this->superadmin));
    }

    public function test_enfermeria_puede_administrar_pero_no_crear_ni_modificar_ordenes_medicas(): void
    {
        $this->actingAs($this->enfermeroUno);
        $medicacion = Prescripcion::where('cod_residente', $this->residenteUno->cod_residente)->firstOrFail();

        $this->assertTrue($this->enfermeroUno->can('administraciones_medicacion.crear'));
        $this->assertFalse($this->enfermeroUno->can('prescripciones.crear'));
        $this->assertFalse($this->enfermeroUno->can('prescripciones.editar'));

        Livewire::test(SaludMedicacionPanel::class)
            ->call('toggleFormularioCrear')
            ->assertForbidden();

        Livewire::test(SaludMedicacionPanel::class)
            ->call('suspenderMedicamento', $medicacion->cod_prescripcion)
            ->assertForbidden();

        Livewire::test(MedicacionAdultoModal::class)
            ->call('abrirModalMedicacion', $this->residenteUno->cod_residente)
            ->assertForbidden();

        $this->assertDatabaseHas('prescripciones', [
            'cod_prescripcion' => $medicacion->cod_prescripcion,
            'estado' => 'ACTIVA',
        ]);
    }

    private function asignar(AdultoMayor $residente, User $enfermero, TurnoEnfermeria $turno): void
    {
        $jornada = Jornada::firstOrCreate(
            ['cod_turno' => $turno->cod_turno, 'fecha_jornada' => today()],
            ['cod_jornada' => 'JOR_'.$turno->cod_turno, 'estado' => 'ABIERTA'],
        );
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_'.$residente->cod_residente,
            'cod_residente' => $residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $enfermero->personal->cod_personal,
            'nivel_supervision' => 'ESTANDAR',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
            'observacion' => 'Cobertura de prueba',
        ]);
    }
}
