<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\MisPacientes;
use App\Models\AdultoMayor;
use App\Models\OcupacionCama;
use App\Models\AsignacionResidenteJornada;
use App\Models\Cama;
use App\Models\EstadoAdulto;
use App\Models\Habitacion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModalRegistrarAtencionMisPacientesTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private AdultoMayor $residente;
    private TurnoEnfermeria $turno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([ RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'cod_usu' => 'USU_ENF01',
            'nombres' => 'Enfermero',
            'ap_paterno' => 'Turno',
            'ap_materno' => 'Tarde',
            'correo' => 'enfermero@remembermind.test',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->turno = TurnoEnfermeria::create([
            'cod_turno' => 'TUR_MANANA',
            'nombre' => 'Turno Manana',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'tipo' => 'MANANA',
            'orden' => 1,
            'estado' => 'ACTIVO',
            'fecha' => today()->toDateString(),
            'activo' => true,
        ]);

        $hab = Habitacion::create([
            'nombre' => 'Habitación 201',
            'numero' => '201',
            'codigo' => 'HAB-D01',
            'tipo' => 'DOBLE',
            'estado' => 'ACTIVA',
            'capacidad' => 2,
        ]);

        $cama = Cama::create([
            'cod_habitacion' => $hab->cod_habitacion,
            'numero' => '1',
            'codigo' => 'CAM-D01-01',
            'estado' => 'OCUPADA',
        ]);

        $this->residente = AdultoMayor::factory()->create([
            'cod_am' => 'AM_009',
            'nombres' => 'FLORENCIA BEATRIZ',
            'ap_paterno' => 'QUISPE',
            'ap_materno' => 'GUTIÉRREZ',
            'ci' => '9988776',
            'fecha_nac' => Carbon::now()->subYears(86)->toDateString(),
            'genero' => 'FEMENINO',
            'cod_est_adul' => 'EST_001',
        ]);

        OcupacionCama::create([
            'cod_am' => $this->residente->cod_am,
            'cod_habitacion' => $hab->cod_habitacion,
            'cod_cama' => $cama->cod_cama,
            'fecha_asignacion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        AsignacionResidenteJornada::create([
            'cod_turno' => $this->turno->cod_turno,
            'cod_am' => $this->residente->cod_am,
            'cod_usu_enfermero' => $this->enfermero->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'motivo_asignacion' => 'Turno asignado',
            'estado' => 'ACTIVA',
        ]);
    }

    public function test_boton_registrar_atencion_sin_dropdown_antiguo(): void
    {
        $this->actingAs($this->enfermero);

        $test = Livewire::test(MisPacientes::class)
            ->set('vistaModo', 'tarjetas')
            ->assertSee('+ REGISTRAR ATENCIÓN')
            ->assertDontSee('caret-down');

        $html = $test->html();

        // Verificar que el viejo dropdown flotante ya NO existe
        $this->assertStringNotContainsString('x-show="open"', $html);
        $this->assertStringNotContainsString('rotate-180', $html);
    }

    public function test_modal_central_golden_reference_contiene_las_8_opciones_y_footer(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->set('vistaModo', 'tarjetas')
            ->assertSee('REGISTRAR ATENCIÓN CLÍNICA')
            ->assertSee('Selecciona el tipo de atención que deseas registrar.')
            // 8 opciones
            ->assertSee('Signos vitales')
            ->assertSee('PA, FC, FR, SpO₂, Temperatura, Dolor, etc.')
            ->assertSee('Cuidado de enfermería')
            ->assertSee('Higiene, alimentación, hidratación, movilidad, eliminación, piel.')
            ->assertSee('Medicación')
            ->assertSee('Dosis programadas, PRN y administración.')
            ->assertSee('Evolución de enfermería')
            ->assertSee('Estado general, cambios observados, intervención y seguimiento.')
            ->assertSee('Seguimiento de guardia')
            ->assertSee('Observación, reevaluación y continuidad de cuidados.')
            ->assertSee('Incidente / Caída')
            ->assertSee('Caídas, lesiones, eventos adversos y acciones realizadas.')
            ->assertSee('Dolor / Síntoma')
            ->assertSee('EVA, localización, intensidad e intervención.')
            ->assertSee('Procedimiento / Dispositivo')
            ->assertSee('Curaciones, sondas, catéteres, oxígeno, etc.')
            // Footer
            ->assertSee('Toda la información se registra en el historial clínico del residente, con trazabilidad y fecha/hora automática.')
            ->assertSee('Cancelar');
    }

    public function test_acciones_rapidas_de_cuidado_dolor_y_procedimiento(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(MisPacientes::class)
            ->call('abrirRegistrarCuidado', $this->residente->cod_am)
            ->assertSet('modalCuidado', true)
            ->assertSet('modalCodAm', $this->residente->cod_am)
            ->call('abrirRegistrarDolor', $this->residente->cod_am)
            ->assertSet('modalDolor', true)
            ->call('abrirRegistrarProcedimiento', $this->residente->cod_am)
            ->assertSet('modalProcedimiento', true);
    }
}
