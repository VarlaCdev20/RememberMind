<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Medicacion\SaludAdministracionMedicacionPanel;
use App\Models\AdultoMayor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TabsMedicacionFlujoTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private AdultoMayor $adulto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

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

    public function test_pestana_proximas_dosis_muestra_estado_vacio_sin_datos_ficticios(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('setTab', 'proximas')
            ->assertSet('tabActivo', 'proximas')
            ->assertSee('Próximas Dosis del Turno')
            ->assertSee('Priorización clínica')
            ->assertDontSee('Omeprazol')
            ->assertDontSee('PRS_0001')
            ->call('abrirDrawerDosis', 'PRS_0001', '08:00', $this->adulto->cod_residente)
            ->assertSet('drawerDosisAbierto', false)
            ->assertSet('selectedPrescripcionId', null);
    }

    public function test_pestana_omisiones_no_fabrica_registros(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('setTab', 'omisiones')
            ->assertSet('tabActivo', 'omisiones')
            ->assertSee('Registro de Omisiones del Turno')
            ->assertSee('Justificaciones clínicas')
            ->assertDontSee('Ayuno médico programado')
            ->assertDontSee('ADM_OM_001');
    }

    public function test_pestana_historial_funciona_con_filtros_reactivos(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('setTab', 'historial')
            ->assertSet('tabActivo', 'historial')
            ->assertSee('Historial de Administración')
            ->assertSee('Trazabilidad clínica')
            ->assertSee('Buscar residente...')
            ->set('filtroHistorialResultado', 'OMITIDA')
            ->assertSet('filtroHistorialResultado', 'OMITIDA')
            ->assertDontSee('ADM_H_003')
            ->call('limpiarFiltrosHistorial')
            ->assertSet('filtroHistorialResultado', '');
    }
}
