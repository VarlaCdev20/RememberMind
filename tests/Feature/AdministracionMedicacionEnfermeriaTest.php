<?php

namespace Tests\Feature;

use App\Livewire\Medicacion\SaludAdministracionMedicacionPanel;
use App\Models\AdultoMayor;
use App\Models\MedicacionAdulto;
use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdministracionMedicacionEnfermeriaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private AdultoMayor $adulto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Laura',
            'ap_paterno' => 'González',
        ]);
        $this->enfermero->assignRole('SUPERADMINISTRADOR');

        $this->adulto = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'María Carmen',
            'ap_paterno' => 'Gómez',
        ]);

        $this->actingAs($this->enfermero);
    }

    public function test_vista_medicacion_contiene_cabecera_exacta_y_kpis_solicitados(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSee('Medicación')
            ->assertSee('Administración segura, a tiempo, para su bienestar')
            ->assertSee('Hoy, 12 de septiembre de 2026')
            ->assertSee('Turno de mañana · 07:00 - 15:00')
            ->assertSee('Alertas')
            ->assertSee('Por administrar ahora')
            ->assertSee('Próximas dosis')
            ->assertSee('Administradas hoy')
            ->assertSee('Omitidas / atrasadas')
            ->assertSee('Adherencia hoy')
            ->assertSee('89%')
            ->assertSee('8 de 9 administradas');
    }

    public function test_vista_contiene_banner_prioritario_y_filtros(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSee('ALERTA PRIORITARIA')
            ->assertSee('Es hora de administrar')
            ->assertSee('Atender ahora')
            ->assertSee('Justificar demora')
            ->assertSee('Por administrar')
            ->assertSee('Próximas')
            ->assertSee('Administradas')
            ->assertSee('PRN')
            ->assertSee('Suspendidas');
    }

    public function test_tabla_principal_y_agenda_muestran_orden_y_bloques(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSee('Medicamento')
            ->assertSee('Dosis')
            ->assertSee('Vía')
            ->assertSee('Horario')
            ->assertSee('Indicación')
            ->assertSee('Estado actual')
            ->assertSee('Última administración')
            ->assertSee('Responsable')
            ->assertSee('Agenda de administración de hoy')
            ->assertSee('Medicamentos PRN (a demanda)')
            ->assertSee('Historial de administración')
            ->assertSee('Ver todos');
    }

    public function test_drawer_lateral_se_abre_con_modo_lectura_y_confirmacion(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('abrirDrawerDetalle', 'MED_REF_PARACETAMOL', '08:00', 'detalle')
            ->assertSet('drawerAbierto', true)
            ->assertSet('drawerPaso', 'detalle')
            ->assertSee('PANEL LATERAL DE CONSULTA')
            ->assertSee('Detalle de medicación')
            ->assertSee('Precauciones y alertas')
            ->assertSee('Regla de seguridad RememberMind')
            ->call('pasarAAdministrar')
            ->assertSet('drawerPaso', 'administrar')
            ->assertSee('Modo Solo Lectura')
            ->assertSee('Validaciones críticas de seguridad')
            ->assertSee('Confirmar administración')
            ->call('confirmarAdministracion')
            ->assertSet('drawerAbierto', false);
    }
}
