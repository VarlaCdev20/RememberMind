<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalForm;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\PersonalInstitucionalPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\TurnosAsignacionesPanel;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\ReportesAdultoPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class VistasModularesTest extends TestCase
{
    use RefreshDatabase;

    public function test_horario_de_enfermeria_abre_con_su_clasificacion_institucional(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $administrador = User::factory()->create();
        $administrador->assignRole('SUPERADMINISTRADOR');
        $enfermera = User::factory()->create();
        $enfermera->assignRole('ENFERMEROS');
        Personal::create(['cod_usuario' => $enfermera->getKey(), 'nombres' => 'Enfermera sintética',
            'apellido_paterno' => 'Prueba', 'numero_documento' => 'DOC-HORARIO-UI', 'profesion' => 'ENFERMERÍA', 'estado' => 'ACTIVO']);
        $this->actingAs($administrador);

        Livewire::test(PersonalInstitucionalPanel::class)
            ->call('abrirHorariosPersonal', $enfermera->getKey())
            ->assertSet('modalGestionAbierto', true)->assertSet('modalTabInicial', 'horarios')
            ->assertSeeLivewire(\App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios::class)
            ->assertSee('ENFERMEROS')->assertSee('Horarios y turnos');
    }

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
            ->assertSeeLivewire(PersonalInstitucionalForm::class)
            ->call('cerrarModal')
            ->assertSet('modalGestionAbierto', false);

        Livewire::test(TurnosAsignacionesPanel::class)
            ->call('abrirNuevaAsignacion')
            ->assertSet('modalAbierto', true)
            ->assertSee('Nueva asignación institucional')
            ->call('cerrarModal')
            ->assertSet('modalAbierto', false);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
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

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_reporte_individual_usa_signos_reales_y_genera_pdf(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);
        $personal = Personal::create([
            'cod_personal' => 'PER_REPORTE_REAL',
            'cod_usuario' => $user->cod_usuario,
            'nombres' => 'Responsable',
            'apellido_paterno' => 'Reporte',
            'numero_documento' => 'DOC-REPORTE-REAL',
            'profesion' => 'MEDICO',
            'estado' => 'ACTIVO',
        ]);
        $adulto = Residente::factory()->create([
            'nombres' => 'Reporte Real', 'cod_est_adul' => 'EST_001', 'fecha_nac' => '1945-03-12',
        ]);
        SignoVital::create([
            'cod_residente' => $adulto->cod_residente, 'cod_personal' => $personal->cod_personal, 'fecha' => today(), 'hora' => '09:30:00',
            'presion_sistolica' => 128, 'presion_diastolica' => 76,
            'frecuencia_cardiaca' => 72, 'frecuencia_respiratoria' => 18,
            'temperatura' => 36.5, 'saturacion' => 96, 'registrado_por' => $user->cod_usuario,
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
