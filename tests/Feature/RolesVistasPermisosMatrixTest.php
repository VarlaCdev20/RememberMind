<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RolesVistasPermisosMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function crearUsuarioConRol(string $rol): User
    {
        $user = User::factory()->create([
            'estado' => 'ACTIVO',
        ]);
        $user->assignRole($rol);
        return $user;
    }

    public function test_superadministrador_accede_directamente_a_dashboard(): void
    {
        $superadmin = $this->crearUsuarioConRol('SUPERADMINISTRADOR');

        $response = $this->actingAs($superadmin)->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_administrador_accede_directamente_a_dashboard(): void
    {
        $admin = $this->crearUsuarioConRol('ADMINISTRADOR');

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_medico_se_redirige_a_dashboard_medico_y_no_enfermeria(): void
    {
        $medico = $this->crearUsuarioConRol('MEDICO GENERAL/GERIATRA');

        $response = $this->actingAs($medico)->get(route('dashboard'));
        $response->assertRedirect(route('admin.medico.dashboard'));
    }

    public function test_enfermero_se_redirige_a_dashboard_enfermeria(): void
    {
        $enfermero = $this->crearUsuarioConRol('ENFERMEROS');

        $response = $this->actingAs($enfermero)->get(route('dashboard'));
        $response->assertRedirect(route('admin.enfermeria.dashboard'));
    }

    public function test_psicologo_se_redirige_a_dashboard_psicologia(): void
    {
        $psicologo = $this->crearUsuarioConRol('PSICOLOGO/A');

        $response = $this->actingAs($psicologo)->get(route('dashboard'));
        $response->assertRedirect(route('admin.psicologia.dashboard'));
    }

    public function test_nutricionista_accede_al_dashboard_general(): void
    {
        $nutricionista = $this->crearUsuarioConRol('NUTRICIONISTA');

        $response = $this->actingAs($nutricionista)->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_administrador_tiene_permisos_para_sus_vistas_asignadas(): void
    {
        $admin = $this->crearUsuarioConRol('ADMINISTRADOR');
        $this->actingAs($admin);

        // Rutas clave del administrador
        $this->get(route('admin.adultos-mayores.index'))->assertOk();
        $this->get(route('admin.habitaciones.index'))->assertOk();
        $this->get(route('admin.turnos-enfermeria.index'))->assertOk();
        $this->get(route('admin.actividades.index'))->assertOk();
        $this->get(route('admin.usuarios.index'))->assertOk();
    }

    public function test_medico_tiene_permisos_y_endpoints_v2_clinicos(): void
    {
        $medico = $this->crearUsuarioConRol('MEDICO GENERAL/GERIATRA');

        $this->assertTrue($medico->can('residentes.ver'));
        $this->assertTrue($medico->can('atenciones.crear'));
        $this->assertTrue($medico->can('prescripciones.crear'));
        $this->assertTrue(Route::has('admin.expediente.index'));
        $this->assertTrue(Route::has('admin.medicacion.index'));
        $this->assertTrue(Route::has('admin.prescripciones.store'));
    }

    public function test_psicologo_tiene_permisos_para_sus_vistas_de_evaluacion(): void
    {
        $psicologo = $this->crearUsuarioConRol('PSICOLOGO/A');
        $this->actingAs($psicologo);

        $this->get(route('admin.psicologia.dashboard'))->assertOk();
        $this->get(route('admin.psicologia.evaluacion.cognitiva'))->assertOk();
        $this->get(route('admin.psicologia.evaluacion.afectiva'))->assertOk();
    }

    public function test_seguridad_clinica_enfermero_no_puede_prescribir_medicacion(): void
    {
        $enfermero = $this->crearUsuarioConRol('ENFERMEROS');
        $this->assertFalse($enfermero->can('prescripciones.crear'));
        $this->assertTrue($enfermero->can('administraciones_medicacion.crear'));

        $medico = $this->crearUsuarioConRol('MEDICO GENERAL/GERIATRA');
        $this->assertTrue($medico->can('prescripciones.crear'));
        $this->assertTrue($medico->can('prescripciones.editar'));
        $this->assertTrue($medico->can('prescripciones.suspender'));
    }
}
