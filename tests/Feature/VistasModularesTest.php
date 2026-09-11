<?php

namespace Tests\Feature;

use App\Livewire\Identidad\PersonalInstitucionalPanel;
use App\Livewire\Identidad\TurnosAsignacionesPanel;
use App\Livewire\Identidad\UsuariosPanel;
use App\Livewire\Reportes\ReportesAdultoPanel;
use App\Models\{AdultoMayor, SignosVitalesAdulto, User};
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VistasModularesTest extends TestCase
{
    use RefreshDatabase;

    public function test_formularios_extraidos_abren_y_cierran_en_sus_componentes(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);

        Livewire::test(UsuariosPanel::class)
            ->call('crearUsuario')
            ->assertSet('mostrarFormulario', true)
            ->assertSee('Registro de')
            ->call('cerrarFormulario')
            ->assertSet('mostrarFormulario', false);

        Livewire::test(PersonalInstitucionalPanel::class)
            ->call('abrirModalNuevo')
            ->assertSet('modalGestionAbierto', true)
            ->assertSeeLivewire(\App\Livewire\Identidad\PersonalInstitucionalForm::class)
            ->call('cerrarModal')
            ->assertSet('modalGestionAbierto', false);

        Livewire::test(TurnosAsignacionesPanel::class)
            ->call('abrirNuevaAsignacion')
            ->assertSet('modalAbierto', true)
            ->assertSee('Nueva asignación institucional')
            ->call('cerrarModal')
            ->assertSet('modalAbierto', false);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_reportes_reubicados_generan_un_pdf_real(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);

        foreach (['salud', 'adultos', 'institucional'] as $reporte) {
            $response = $this->get(route('admin.reportes.'.$reporte.'.pdf'))
                ->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_reporte_individual_usa_signos_reales_y_genera_pdf(): void
    {
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);
        $adulto = AdultoMayor::factory()->create([
            'nombres' => 'Reporte Real', 'cod_est_adul' => 'EST_001', 'fecha_nac' => '1945-03-12',
        ]);
        SignosVitalesAdulto::create([
            'cod_am' => $adulto->cod_am, 'fecha' => today(), 'hora' => '09:30:00',
            'presion_sistolica' => 128, 'presion_diastolica' => 76,
            'frecuencia_cardiaca' => 72, 'frecuencia_respiratoria' => 18,
            'temperatura' => 36.5, 'saturacion' => 96, 'registrado_por' => $user->cod_usu,
        ]);

        Livewire::test(ReportesAdultoPanel::class, ['adultoMayor' => $adulto])
            ->assertSet('chartSignos.fc.0', 72)
            ->assertSet('chartSignos.sat.0', 96)
            ->assertSee('Frecuencia Cardíaca');

        $response = $this->get(route('admin.enfermeria.pacientes.ficha.pdf', $adulto))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
