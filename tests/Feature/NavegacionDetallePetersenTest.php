<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NavegacionDetallePetersenTest extends TestCase
{
    use RefreshDatabase;

    private User $psicologa;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->psicologa = User::factory()->create(['estado' => 'ACTIVO']);
        $this->psicologa->assignRole('PSICOLOGO/A');
        Personal::create(['cod_usuario' => $this->psicologa->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintética', 'numero_documento' => 'NAV-PETERSEN',
            'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->actingAs($this->psicologa);
        app(RegistrarConocimientoPetersen::class)->ejecutar($this->psicologa);
    }

    public function test_botones_de_detalle_abren_la_seccion_real_y_cierran_la_ventana_anterior(): void
    {
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('id="orion-petersen-detalle"', false)
            ->assertSee('Ver qué falta')
            ->call('abrirSignificado')->assertSet('significadoAbierto', true)
            ->call('irASeccion', 'orion-petersen-detalle')
            ->assertOk()->assertSet('significadoAbierto', false)
            ->assertDispatched('orion-navigate', seccion: 'orion-petersen-detalle');

        $this->assertDatabaseCount('evaluaciones_expertas', 0);
        $this->assertDatabaseCount('diagnosticos', 0);
    }

    public function test_destino_desconocido_sigue_rechazado(): void
    {
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('irASeccion', 'destino-no-permitido')
            ->assertStatus(422)->assertNotDispatched('orion-navigate');
    }

    public function test_cuenta_desactivada_no_puede_navegar_desde_una_pantalla_abierta(): void
    {
        $pantalla = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $this->psicologa->update(['estado' => 'INACTIVO']);
        $pantalla->call('irASeccion', 'orion-petersen-detalle')
            ->assertStatus(404)->assertNotDispatched('orion-navigate');
    }

    public function test_permiso_revocado_impide_navegar_desde_una_pantalla_abierta(): void
    {
        $pantalla = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $this->psicologa->roles->each(fn ($rol) => $rol->revokePermissionTo('residentes.ver'));
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $pantalla->call('irASeccion', 'orion-petersen-detalle')
            ->assertStatus(404)->assertNotDispatched('orion-navigate');
    }
}
