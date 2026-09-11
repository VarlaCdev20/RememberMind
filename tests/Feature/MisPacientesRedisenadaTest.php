<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\MisPacientes;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionAdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\MedicacionAdulto;
use App\Models\PlanCuidado;
use App\Models\SeguimientoDiario;
use App\Models\SignosVitalesAdulto;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MisPacientesRedisenadaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private TurnoEnfermeria $turno;
    private AdultoMayor $residenteEstable;
    private AdultoMayor $residenteCritico;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'cod_usu' => 'USU_ENF01',
            'nombres' => 'Elena',
            'ap_paterno' => 'Vargas',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->turno = TurnoEnfermeria::create([
            'cod_turno' => 'TUR_MANANA',
            'nombre' => 'Turno Mañana',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'tipo' => 'MANANA',
            'orden' => 1,
            'estado' => 'ACTIVO',
            'fecha' => today()->toDateString(),
            'activo' => true,
        ]);

        $hab101 = Habitacion::create([
            'nombre' => 'Habitación 101',
            'numero' => '101',
            'codigo' => 'HAB-101',
            'tipo' => 'DOBLE',
            'estado' => 'ACTIVA',
            'capacidad' => 2,
        ]);

        $camaA = Cama::create([
            'cod_habitacion' => $hab101->cod_habitacion,
            'numero' => '1',
            'codigo' => 'CAMA-101A',
            'estado' => 'OCUPADA',
        ]);

        $hab102 = Habitacion::create([
            'nombre' => 'Habitación 102',
            'numero' => '102',
            'codigo' => 'HAB-102',
            'tipo' => 'DOBLE',
            'estado' => 'ACTIVA',
            'capacidad' => 2,
        ]);

        $camaB = Cama::create([
            'cod_habitacion' => $hab102->cod_habitacion,
            'numero' => '2',
            'codigo' => 'CAMA-102B',
            'estado' => 'OCUPADA',
        ]);

        // Residente 1: Estable
        $this->residenteEstable = AdultoMayor::factory()->create([
            'cod_am' => 'AM_ESTABLE',
            'nombres' => 'Pedro',
            'ap_paterno' => 'Gomez',
            'ap_materno' => 'Paredes',
            'ci' => '1234567',
            'fecha_nac' => Carbon::now()->subYears(75)->toDateString(),
            'genero' => 'MASCULINO',
            'cod_est_adul' => 'EST_001',
        ]);

        AsignacionAdultoMayor::create([
            'cod_am' => $this->residenteEstable->cod_am,
            'cod_habitacion' => $hab101->cod_habitacion,
            'cod_cama' => $camaA->cod_cama,
            'fecha_asignacion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        PlanCuidado::create([
            'cod_am' => $this->residenteEstable->cod_am,
            'nivel_cuidado' => 'MODERADO',
            'estado' => 'ACTIVO',
            'fecha_inicio' => today()->subMonth()->toDateString(),
            'creado_por' => $this->enfermero->cod_usu,
        ]);

        // Residente 2: Requiere Atención (tiene alerta crítica)
        $this->residenteCritico = AdultoMayor::factory()->create([
            'cod_am' => 'AM_CRITICO',
            'nombres' => 'Luisa',
            'ap_paterno' => 'Morales',
            'ap_materno' => 'Rios',
            'ci' => '7654321',
            'fecha_nac' => Carbon::now()->subYears(82)->toDateString(),
            'genero' => 'FEMENINO',
            'cod_est_adul' => 'EST_001',
        ]);

        AsignacionAdultoMayor::create([
            'cod_am' => $this->residenteCritico->cod_am,
            'cod_habitacion' => $hab102->cod_habitacion,
            'cod_cama' => $camaB->cod_cama,
            'fecha_asignacion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        AlertaAdulto::create([
            'cod_am' => $this->residenteCritico->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'tipo_alerta' => 'SIGNOS',
            'nivel' => 'CRITICO',
            'origen' => 'SIGNOS',
            'motivo' => 'Presión arterial descompensada severa.',
            'estado' => 'ABIERTA',
            'responsable_id' => $this->enfermero->cod_usu,
        ]);

        // Asignar ambos al enfermero en su turno
        AsignacionTurnoAdulto::create([
            'cod_turno' => $this->turno->cod_turno,
            'cod_am' => $this->residenteEstable->cod_am,
            'cod_usu_enfermero' => $this->enfermero->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'motivo_asignacion' => 'Asignación de turno de prueba',
            'estado' => 'ACTIVA',
        ]);

        AsignacionTurnoAdulto::create([
            'cod_turno' => $this->turno->cod_turno,
            'cod_am' => $this->residenteCritico->cod_am,
            'cod_usu_enfermero' => $this->enfermero->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'motivo_asignacion' => 'Asignación de turno de prueba',
            'estado' => 'ACTIVA',
        ]);

        // Signos para el residente estable
        SignosVitalesAdulto::create([
            'cod_am' => $this->residenteEstable->cod_am,
            'fecha' => today()->toDateString(),
            'hora' => '08:30:00',
            'presion_arterial' => '120/80',
            'frecuencia_cardiaca' => 72,
            'temperatura' => 36.5,
            'saturacion' => 98,
            'registrado_por' => $this->enfermero->cod_usu,
            'estado' => 'VIGENTE',
        ]);

        // Seguimiento para el residente estable
        SeguimientoDiario::create([
            'cod_am' => $this->residenteEstable->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'fecha' => today()->toDateString(),
            'hora' => '09:00:00',
            'estado_general' => 'ESTABLE',
            'alimentacion' => 'COMPLETA',
            'movilidad' => 'INDEPENDIENTE',
            'sueno' => 'NORMAL',
            'incidente' => false,
            'requiere_medico' => false,
            'registrado_por' => $this->enfermero->cod_usu,
        ]);
    }

    public function test_cabecera_institucional_y_vista_por_defecto_tabla(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->assertSee('Mis pacientes')
            ->assertSee('Residentes asignados a tu turno actual')
            ->assertSee('Pedro Gomez')
            ->assertSee('Luisa Morales')
            ->assertSee('HAB-101')
            ->assertSee('HAB-102')
            ->assertSee('75 años')
            ->assertSee('82 años')
            ->assertSee('MODERADO')
            ->assertSee('PA 120/80')
            ->assertSet('vistaModo', 'tabla');
    }

    public function test_filtro_por_estado_clinico(): void
    {
        $this->actingAs($this->enfermero);

        // Al filtrar REQUIERE_ATENCION, solo Luisa debe aparecer
        Livewire::test(MisPacientes::class)
            ->set('filtroEstado', 'REQUIERE_ATENCION')
            ->assertSee('Luisa Morales')
            ->assertDontSee('Pedro Gomez');

        // Al filtrar ESTABLE, solo Pedro debe aparecer
        Livewire::test(MisPacientes::class)
            ->set('filtroEstado', 'ESTABLE')
            ->assertSee('Pedro Gomez')
            ->assertDontSee('Luisa Morales');
    }

    public function test_busqueda_por_nombre_o_habitacion(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->set('search', 'Luisa')
            ->assertSee('Luisa Morales')
            ->assertDontSee('Pedro Gomez')
            ->set('search', '101')
            ->assertSee('Pedro Gomez')
            ->assertDontSee('Luisa Morales');
    }

    public function test_conmutador_de_vista_lista_y_tarjetas(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->assertSet('vistaModo', 'tabla')
            ->set('vistaModo', 'tarjetas')
            ->assertSee('Pedro Gomez')
            ->assertSee('Luisa Morales')
            ->assertSee('Últimos Signos')
            ->assertSee('Tareas de Turno')
            ->assertSee('Seguimiento');
    }

    public function test_apertura_de_modales_de_accion_rapida(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('abrirRegistrarSignos', $this->residenteEstable->cod_am)
            ->assertSet('modalSignos', true)
            ->assertSet('modalCodAm', $this->residenteEstable->cod_am)
            ->call('abrirRegistrarSeguimiento', $this->residenteEstable->cod_am)
            ->assertSet('modalSeguimiento', true)
            ->call('abrirAdministrarMed', $this->residenteEstable->cod_am)
            ->assertSet('modalMed', true)
            ->call('abrirReportarAlerta', $this->residenteEstable->cod_am)
            ->assertSet('modalAlerta', true);
    }
}
