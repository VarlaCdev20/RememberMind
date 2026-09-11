<?php

namespace Tests\Feature;

use App\Livewire\Alertas\CampanaNotificaciones;
use App\Livewire\Medicacion\SaludMedicacionPanel;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\MedicacionAdulto;
use App\Models\User;
use App\Services\Medicacion\AgendaMedicacionService;
use Carbon\Carbon;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AgendaMedicacionTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;
    private AdultoMayor $adulto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('SUPERADMINISTRADOR');
        $this->adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
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
            ->set('nuevo_cod_am', $this->adulto->cod_am)
            ->set('nuevo_nombre', 'Losartán')
            ->set('nuevo_dosis', '50 mg')
            ->set('nuevo_frecuencia', 'Cada 24 horas')
            ->set('nuevo_via', 'ORAL')
            ->set('nuevo_hora', '08:00')
            ->set('nuevo_fecha_inicio', '2026-09-10')
            ->set('nuevo_medico', '')
            ->call('guardarNuevoMedicamento')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('medicacion_adulto', [
            'cod_am' => $this->adulto->cod_am,
            'nombre_medicamento' => 'Losartán',
            'hora_programada' => '08:00',
        ]);
        $this->assertDatabaseCount('administracion_medicacion', 0);
    }

    public function test_agenda_cambia_de_proxima_a_administrada_segun_el_registro_real(): void
    {
        Carbon::setTestNow('2026-09-10 07:30:00');
        $medicacion = $this->crearMedicacion('08:00');

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_am);
        $this->assertSame('PROXIMA', $agenda->first()['estado']);

        AdministracionMedicacion::create([
            'cod_med_adulto' => $medicacion->cod_med_adulto,
            'cod_am' => $this->adulto->cod_am,
            'fecha' => '2026-09-10',
            'hora_programada' => '08:00',
            'hora_real' => '07:35',
            'administrado' => true,
            'registrado_por' => $this->usuario->cod_usu,
        ]);

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_am);
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

    public function test_frecuencia_cada_ocho_horas_genera_todos_los_horarios_del_dia(): void
    {
        Carbon::setTestNow('2026-09-10 07:30:00');
        $medicacion = $this->crearMedicacion('08:00');
        $medicacion->update(['frecuencia' => 'Cada 8 horas']);

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_am);

        $this->assertSame(['00:00', '08:00', '16:00'], $agenda->pluck('hora')->all());
        $this->assertSame(['VENCIDA', 'PROXIMA', 'PENDIENTE'], $agenda->pluck('estado')->all());
    }

    private function crearMedicacion(string $hora): MedicacionAdulto
    {
        return MedicacionAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'nombre_medicamento' => 'Losartán',
            'dosis' => '50 mg',
            'frecuencia' => 'Cada 24 horas',
            'via_administracion' => 'ORAL',
            'hora_programada' => $hora,
            'fecha_inicio' => '2026-09-01',
            'estado' => 'ACTIVA',
            'registrado_por' => $this->usuario->cod_usu,
        ]);
    }
}
