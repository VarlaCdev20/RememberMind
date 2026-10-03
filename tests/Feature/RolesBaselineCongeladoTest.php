<?php

namespace Tests\Feature;

use App\Backend\Modulos\Identidad\Servicios\SidebarService;
use App\Frontend\Livewire\Superadministrador\Identidad\RolesPermisosPanel;
use App\Models\User;
use App\Policies\PrescripcionPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesBaselineCongeladoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_existen_los_diez_roles_del_baseline_y_no_voluntario(): void
    {
        $this->assertSame([
            'ADMINISTRADOR', 'ENFERMEROS', 'FAMILIAR', 'FISIOTERAPEUTA', 'GERENTE',
            'MEDICO GENERAL/GERIATRA', 'NUTRICIONISTA', 'PEDAGOGO', 'PSICOLOGO/A',
            'SUPERADMINISTRADOR',
        ], Role::query()->orderBy('name')->pluck('name')->all());
        $this->assertFalse(Role::query()->where('name', 'VOLUNTARIO')->exists());
    }

    public function test_superadmin_recibe_todos_los_permisos_sin_sustituir_autoria_clinica(): void
    {
        $user = User::factory()->create(['nombres' => 'Elena', 'ap_paterno' => 'Prueba']);
        $user->assignRole('SUPERADMINISTRADOR');

        $this->assertTrue($user->can('usuarios.gestionar'));
        $this->assertTrue($user->can('roles.editar_permisos'));
        $this->assertTrue($user->can('auditoria.ver'));
        $this->assertTrue($user->can('diagnosticos.ver'));
        $this->assertTrue($user->can('diagnosticos.crear'));
        $this->assertTrue($user->can('prescripciones.crear'));
        $this->assertTrue($user->can('notas_clinicas.crear'));
        $this->assertTrue($user->can('jornadas.gestionar'));
        $this->assertTrue($user->can('personal.gestionar'));
        $this->assertTrue($user->can('admisiones.formalizar'));
        $this->assertTrue(app(PrescripcionPolicy::class)->create($user));
        $this->assertEqualsCanonicalizing(
            Permission::query()->where('guard_name', 'web')->pluck('name')->all(),
            $user->getAllPermissions()->pluck('name')->all()
        );
    }

    public function test_permiso_nuevo_se_asigna_al_superadmin_y_sobrevive_al_resembrado(): void
    {
        $user = $this->usuarioConRol('SUPERADMINISTRADOR');
        $permission = Permission::create(['name' => 'modulo_nuevo.gestionar', 'guard_name' => 'web']);

        $this->assertTrue($user->fresh()->can($permission->name));
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertTrue(Permission::query()->whereKey($permission->id)->exists());
        $this->assertTrue($user->fresh()->can($permission->name));
    }

    public function test_panel_no_puede_quitar_permisos_del_superadmin_con_estado_manipulado(): void
    {
        $user = $this->usuarioConRol('SUPERADMINISTRADOR');
        $rol = Role::findByName('SUPERADMINISTRADOR', 'web');

        Livewire::actingAs($user)
            ->test(RolesPermisosPanel::class)
            ->set('rolSeleccionadoId', $rol->id)
            ->set('permisosSeleccionados', [])
            ->call('guardarPermisos');

        $this->assertSame(
            Permission::query()->where('guard_name', 'web')->count(),
            $rol->fresh()->permissions()->count()
        );
    }

    public function test_gerente_dirige_personal_y_planificacion_sin_competencia_clinica(): void
    {
        $user = $this->usuarioConRol('GERENTE');

        foreach (['personal.gestionar', 'areas.gestionar', 'turnos.gestionar', 'jornadas.ver', 'residentes.ver', 'incidentes.ver'] as $permiso) {
            $this->assertTrue($user->can($permiso), "Falta {$permiso}");
        }
        foreach (['usuarios.gestionar', 'jornadas.gestionar', 'diagnosticos.crear', 'prescripciones.crear', 'notas_clinicas.crear'] as $permiso) {
            $this->assertFalse($user->can($permiso), "Sobra {$permiso}");
        }
        $this->assertFalse(app(PrescripcionPolicy::class)->create($user));
    }

    public function test_administracion_es_operativa_sin_usuarios_rrhh_maestro_o_escritura_clinica(): void
    {
        $user = $this->usuarioConRol('ADMINISTRADOR');

        foreach (['preadmisiones.revisar', 'admisiones.formalizar', 'jornadas.gestionar',
            'asignaciones_personal.gestionar', 'habitaciones.gestionar', 'documentos.gestionar',
            'visitas.gestionar', 'alertas.reconocer', 'alertas.asignar', 'alertas.seguimiento', 'alertas.cerrar'] as $permiso) {
            $this->assertTrue($user->can($permiso), "Falta {$permiso}");
        }
        foreach (['usuarios.gestionar', 'personal.gestionar', 'areas.gestionar', 'turnos.gestionar',
            'diagnosticos.crear', 'prescripciones.crear', 'notas_clinicas.crear'] as $permiso) {
            $this->assertFalse($user->can($permiso), "Sobra {$permiso}");
        }
    }

    public function test_sidebars_reflejan_los_tres_niveles_institucionales(): void
    {
        $super = $this->rutasSidebar('SUPERADMINISTRADOR');
        $this->assertContains('admin.usuarios.index', $super);
        $this->assertContains('admin.roles-permisos.index', $super);
        $this->assertContains('admin.bitacora.index', $super);
        $this->assertContains('admin.admisiones.preadmisiones', $super);
        $this->assertContains('admin.adultos-mayores.index', $super);
        $this->assertContains('admin.salud-seguimiento.ficha.index', $super);

        $gerente = $this->rutasSidebar('GERENTE');
        $this->assertContains('admin.personal-institucional', $gerente);
        $this->assertContains('admin.turnos-asignaciones.index', $gerente);
        $this->assertContains('admin.reportes.institucional.preview', $gerente);
        $this->assertNotContains('admin.usuarios.index', $gerente);

        $administracion = $this->rutasSidebar('ADMINISTRADOR');
        $this->assertContains('admin.admisiones.preadmisiones', $administracion);
        $this->assertContains('admin.administracion.jornadas', $administracion);
        $this->assertContains('admin.administracion.alertas', $administracion);
        $this->assertNotContains('admin.usuarios.index', $administracion);
        $this->assertNotContains('admin.personal-institucional', $administracion);
    }

    public function test_dashboards_tienen_contenido_diferente_por_rol(): void
    {
        $this->actingAs($this->usuarioConRol('SUPERADMINISTRADOR'))->get(route('dashboard'))
            ->assertOk()->assertSee('Superadministración')->assertSee('Residentes activos');

        $this->actingAs($this->usuarioConRol('GERENTE'))->get(route('dashboard'))
            ->assertOk()->assertSee('con visión de cobertura')->assertSee('Personal activo');

        $this->actingAs($this->usuarioConRol('ADMINISTRADOR'))->get(route('dashboard'))
            ->assertRedirect(route('admin.administracion.dashboard'));
        $this->get(route('admin.administracion.dashboard'))
            ->assertOk()->assertSee('Centro de Coordinación Residencial')->assertSee('Preadmisiones pendientes');
    }

    public function test_rutas_backend_separan_sistema_institucion_y_operacion(): void
    {
        $super = $this->usuarioConRol('SUPERADMINISTRADOR');
        $gerente = $this->usuarioConRol('GERENTE');
        $administracion = $this->usuarioConRol('ADMINISTRADOR');

        $this->actingAs($super)->postJson(route('admin.institucional.usuarios.store'), [])->assertUnprocessable();
        $this->actingAs($gerente)->postJson(route('admin.institucional.usuarios.store'), [])->assertForbidden();

        $this->actingAs($gerente)->postJson(route('admin.institucional.personal.store'), [])->assertUnprocessable();
        $this->actingAs($administracion)->postJson(route('admin.institucional.personal.store'), [])->assertForbidden();

        $this->actingAs($administracion)->postJson(route('admin.institucional.jornadas.store'), [])->assertUnprocessable();
        $this->actingAs($gerente)->postJson(route('admin.institucional.jornadas.store'), [])->assertForbidden();
    }

    public function test_cuenta_inactiva_no_puede_continuar_en_rutas_autenticadas(): void
    {
        $user = $this->usuarioConRol('SUPERADMINISTRADOR', 'INACTIVO');

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    }

    private function usuarioConRol(string $rol, string $estado = 'ACTIVO'): User
    {
        $user = User::factory()->create(['estado' => $estado]);
        $user->assignRole($rol);

        return $user;
    }

    private function rutasSidebar(string $rol): array
    {
        $this->actingAs($this->usuarioConRol($rol));
        $sidebar = app(SidebarService::class)->getSidebar();

        return collect($sidebar)->flatMap(function (array $section): array {
            return array_values(array_filter([
                $section['route'] ?? null,
                ...array_column($section['items'] ?? [], 'route'),
            ]));
        })->values()->all();
    }
}
