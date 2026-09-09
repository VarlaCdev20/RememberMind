<?php

namespace Tests\Feature;

use App\Models\Preadmision;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasosPreadmisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'admisiones.ver_dashboard', 'guard_name' => 'web']);
        $roleSuper = Role::firstOrCreate(['name' => 'SUPERADMINISTRADOR', 'guard_name' => 'web']);
        $roleSuper->givePermissionTo('admisiones.ver_dashboard');
        Role::firstOrCreate(['name' => 'ENFERMEROS', 'guard_name' => 'web']);
    }

    public function test_invitados_son_redirigidos_al_login_al_intentar_ver_casos(): void
    {
        $response = $this->get(route('admin.admisiones.preadmisiones'));
        $response->assertRedirect('/login');
    }

    public function test_usuario_con_permiso_puede_ver_panel_de_casos(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');
        $response = $this->actingAs($user)->get(route('admin.admisiones.preadmisiones'));
        $response->assertStatus(200);
    }

    public function test_usuario_sin_permiso_recibe_403_al_intentar_ver_casos(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $response = $this->actingAs($user)->get(route('admin.admisiones.preadmisiones'));
        $response->assertStatus(403);
    }

    public function test_wizard_registro_de_caso_responde_200(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');
        $response = $this->actingAs($user)->get(route('admin.admisiones.preadmision'));
        $response->assertStatus(200);
    }

    public function test_creacion_y_persistencia_de_caso_en_base_de_datos(): void
    {
        $caso = Preadmision::create([
            'estado' => 'PREADMISION_ASIGNADA',
            'fecha_solicitud' => '2026-09-03',
            'fecha_asignacion' => now(),
            'nombres' => 'ROBERTO',
            'ap_paterno' => 'CONDORI',
            'ap_materno' => 'FLORES',
            'ci' => '4455667',
            'fecha_nac' => '1950-01-01',
            'genero' => 'MASCULINO',
            'estado_civil' => 'SOLTERO',
            'celular' => '70011223',
            'departamento_residencia' => 'LA PAZ',
            'ciudad_municipio' => 'LA PAZ',
            'familiar_nombres' => 'ELENA',
            'familiar_ap_paterno' => 'CONDORI',
            'familiar_ci' => '9988776',
            'familiar_parentesco' => 'HIJA',
            'familiar_celular' => '70099887',
            'motivo_ingreso' => 'CUIDADO_INTEGRAL',
            'procedencia_ingreso' => 'FAMILIAR',
            'tipo_ingreso' => 'REGULAR',
            'permanencia' => 'PERMANENTE',
            'prioridad' => 'ALTA',
            'descripcion_caso' => 'Caso evaluado para ingreso prioritario.',
            'documentos_iniciales_completos' => true,
            'documentos_institucionales_generados' => false,
        ]);

        $this->assertDatabaseHas('preadmisiones', [
            'cod_pre' => $caso->cod_pre,
            'nombres' => 'ROBERTO',
            'ap_paterno' => 'CONDORI',
            'descripcion_caso' => 'Caso evaluado para ingreso prioritario.',
            'prioridad' => 'ALTA',
        ]);
    }
}
