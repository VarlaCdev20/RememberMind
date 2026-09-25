<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Medicacion\SaludAdministracionMedicacionPanel;
use App\Models\AdultoMayor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FiltrosMedicacionTest extends TestCase
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

    public function test_panel_filtros_kardex_es_especifico_y_reactivo(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSet('tabActivo', 'kardex')
            // Comprobar presencia de los filtros de Kardex
            ->assertSee('Buscar residente o medicamento...')
            ->assertSee('Todos los estados')
            ->assertSee('Cualquier horario')
            ->assertSee('Vía (Todas)')
            ->assertSee('PRN (Todos)')
            // Filtrar por estado
            ->set('filtroKardexEstado', 'RETRASADA')
            ->assertSet('filtroKardexEstado', 'RETRASADA')
            ->assertSee('Filtros activos:')
            ->assertSee('Estado: Retrasadas')
            ->assertSee('coincidentes')
            ->assertSee('Limpiar filtros')
            // Limpiar chip individual
            ->call('limpiarFiltro', 'filtroKardexEstado')
            ->assertSet('filtroKardexEstado', '')
            // Filtrar por PRN
            ->set('filtroKardexPrn', 'PRN')
            ->assertSet('filtroKardexPrn', 'PRN')
            ->assertSee('PRN: Según necesidad')
            // Reset general
            ->call('resetFilters')
            ->assertSet('filtroKardexPrn', '')
            ->assertSet('filtroKardexEstado', '');
    }

    public function test_panel_filtros_historial_es_especifico_y_reactivo(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('setTab', 'historial')
            ->assertSet('tabActivo', 'historial')
            // Comprobar presencia de filtros propios de Historial
            ->assertSee('Buscar residente o medicamento...')
            ->assertSee('Todos los resultados')
            ->assertSee('Filtrar por medicamento...')
            // Filtrar por resultado
            ->set('filtroHistorialResultado', 'ADMINISTRADA')
            ->assertSet('filtroHistorialResultado', 'ADMINISTRADA')
            ->assertSee('Filtros activos:')
            ->assertSee('Resultado: Administrada')
            ->assertSee('coincidentes')
            ->assertSee('Limpiar filtros')
            // Limpiar chip individual
            ->call('limpiarFiltro', 'filtroHistorialResultado')
            ->assertSet('filtroHistorialResultado', '')
            // Filtrar por vía
            ->set('filtroHistorialVia', 'ORAL')
            ->assertSet('filtroHistorialVia', 'ORAL')
            ->assertSee('Vía: Oral')
            // Reset all
            ->call('resetFilters')
            ->assertSet('filtroHistorialVia', '')
            ->assertSet('filtroHistorialResultado', '');
    }
}
