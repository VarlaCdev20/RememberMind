<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Medicacion\SaludAdministracionMedicacionPanel;
use App\Models\Alergia;
use App\Models\Residente;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdministracionMedicacionEnfermeriaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private Residente $adulto;

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

        $this->adulto = Residente::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'María Carmen',
            'ap_paterno' => 'Gómez',
        ]);

        $this->actingAs($this->enfermero);
    }

    public function test_vista_medicacion_contiene_cabecera_exacta_y_resumen_compacto(): void
    {
        Alergia::create([
            'cod_alergia' => 'ALE_F4_MED',
            'cod_residente' => $this->adulto->cod_residente,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'sustancia' => 'Sustancia sintética',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertViewHas('residentesConAlergias', 1)
            ->assertSee('Medicación')
            ->assertSee('Administración y seguimiento del turno')
            ->assertSee('administradas')
            ->assertSee('pendientes')
            ->assertSee('retrasada')
            ->assertSee('omitida')
            ->assertSee('Alergia relevante')
            ->assertSee('Kardex')
            ->assertSee('Próximas dosis')
            ->assertSee('Omisiones')
            ->assertSee('Historial');
    }

    public function test_resumen_no_inventa_una_alerta_de_alergia_sin_registros(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertViewHas('residentesConAlergias', 0)
            ->assertDontSee('Alergia relevante');
    }

    public function test_kardex_matriz_horaria_y_cambio_de_tabs(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSet('tabActivo', 'kardex')
            ->assertSee('Matriz Horaria del Turno')
            ->assertSee('Residente')
            ->assertSee('Hab / Cama')
            ->assertSee('07:00')
            ->assertSee('08:00')
            ->assertSee('12:00')
            ->assertSee('María Carmen')
            ->call('setTab', 'proximas')
            ->assertSet('tabActivo', 'proximas')
            ->assertSee('Próximas Dosis del Turno')
            ->call('setTab', 'omisiones')
            ->assertSet('tabActivo', 'omisiones')
            ->assertSee('Registro de Omisiones del Turno')
            ->call('setTab', 'historial')
            ->assertSet('tabActivo', 'historial')
            ->assertSee('Historial de Administración');
    }

    public function test_drawer_rechaza_una_prescripcion_inexistente_sin_inventar_datos(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSet('drawerDosisAbierto', false)
            ->call('abrirDrawerDosis', 'PARACETAMOL', '08:00', $this->adulto->cod_residente)
            ->assertSet('drawerDosisAbierto', false)
            ->assertSet('selectedPrescripcionId', null)
            ->assertSet('dosisDetalle', [])
            ->assertDispatched('swal');
    }

    public function test_ficha_flotante_del_medicamento_se_abre_como_modal_independiente(): void
    {
        Medicamento::create([
            'cod_medicamento' => 'MED_PARACETAMOL_REAL',
            'nombre_generico' => 'Paracetamol',
            'concentracion' => '500 mg',
            'forma_farmaceutica' => 'Comprimido',
            'unidad' => 'mg',
            'via_predeterminada' => 'ORAL',
            'control_especial' => false,
            'estado' => 'ACTIVO',
        ]);

        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSet('modalMedicamentoAbierto', false)
            ->call('abrirModalMedicamento', 'MED_PARACETAMOL_REAL')
            ->assertSet('modalMedicamentoAbierto', true)
            ->assertSee('Ficha del medicamento')
            ->assertSee('Concentración')
            ->assertSee('Forma farmacéutica')
            ->assertSee('Información farmacológica ampliada no registrada.')
            ->call('cerrarModalMedicamento')
            ->assertSet('modalMedicamentoAbierto', false);
    }

    public function test_no_abre_flujo_de_omision_para_prescripcion_inexistente(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('abrirDrawerDosis', 'PARACETAMOL', '08:00', $this->adulto->cod_residente)
            ->assertSet('drawerDosisAbierto', false)
            ->assertSet('mostrarFormularioOmision', false)
            ->assertSet('selectedPrescripcionId', null);
    }
}
