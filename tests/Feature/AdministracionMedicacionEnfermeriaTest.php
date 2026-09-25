<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Medicacion\SaludAdministracionMedicacionPanel;
use App\Models\AdultoMayor;
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

    public function test_vista_medicacion_contiene_cabecera_exacta_y_resumen_compacto(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
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

    public function test_drawer_lateral_se_abre_con_secciones_y_medicamento_clickeable(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSet('drawerDosisAbierto', false)
            ->call('abrirDrawerDosis', 'PARACETAMOL', '08:00', $this->adulto->cod_residente)
            ->assertSet('drawerDosisAbierto', true)
            ->assertSee('Detalle de la Dosis')
            ->assertSee('Paracetamol')
            ->assertSee('Prescripción Médica')
            ->assertSee('Programación del Horario')
            ->assertSee('Seguridad y Alergias')
            ->assertSee('Último Seguimiento')
            ->assertSee('Observación de Enfermería')
            ->assertSee('Administrar')
            ->assertSee('Registrar omisión');
    }

    public function test_ficha_flotante_del_medicamento_se_abre_como_modal_independiente(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('abrirDrawerDosis', 'PARACETAMOL', '08:00', $this->adulto->cod_residente)
            ->assertSet('drawerDosisAbierto', true)
            ->assertSet('modalMedicamentoAbierto', false)
            ->call('abrirModalMedicamento', 'PARACETAMOL')
            ->assertSet('modalMedicamentoAbierto', true)
            ->assertSet('drawerDosisAbierto', true) // El drawer sigue abierto debajo
            ->assertSee('Ficha del medicamento')
            ->assertSee('Concentración')
            ->assertSee('Forma farmacéutica')
            ->assertSee('Información farmacológica ampliada no registrada.')
            ->call('cerrarModalMedicamento')
            ->assertSet('modalMedicamentoAbierto', false)
            ->assertSet('drawerDosisAbierto', true);
    }

    public function test_flujo_omision_justificada_en_drawer(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('abrirDrawerDosis', 'PARACETAMOL', '08:00', $this->adulto->cod_residente)
            ->assertSet('mostrarFormularioOmision', false)
            ->call('mostrarOmisionForm')
            ->assertSet('mostrarFormularioOmision', true)
            ->assertSee('Motivo de Omisión Justificada')
            ->call('cancelarOmisionForm')
            ->assertSet('mostrarFormularioOmision', false);
    }
}