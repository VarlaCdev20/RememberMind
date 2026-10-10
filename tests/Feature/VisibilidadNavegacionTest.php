<?php

namespace Tests\Feature;

use App\Backend\Modulos\Identidad\Servicios\SidebarService;
use App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion;
use App\Frontend\Livewire\Medico\Valoraciones\ValoracionMedicaPanel;
use App\Frontend\Livewire\Medico\Clinica\PacientesSeguimientoPanel;
use App\Frontend\Livewire\Administracion\Identidad\TurnosAsignacionesPanel;
use App\Frontend\Livewire\Compartido\Residentes\AdultosMayoresPanel;
use App\Frontend\Livewire\Compartido\Valoraciones\EvaluacionesAreaPanel;
use App\Models\Residente;
use App\Models\Contacto;
use App\Models\ResidenteContacto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Livewire\Livewire;
use Tests\TestCase;

class VisibilidadNavegacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_el_modulo_sin_permiso_desaparece_pero_la_ruta_sigue_protegida(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');
        Role::findByName('ADMINISTRADOR')->revokePermissionTo('alertas.ver');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($usuario->fresh());

        $visibilidad = app(VisibilidadNavegacion::class);
        $this->assertFalse($visibilidad->puedeVerRuta('admin.administracion.alertas'));

        $rutasSidebar = collect(app(SidebarService::class)->getSidebar())
            ->flatMap(fn (array $seccion) => array_filter([
                $seccion['route'] ?? null,
                ...array_column($seccion['items'] ?? [], 'route'),
            ]))->all();
        $this->assertNotContains('admin.administracion.alertas', $rutasSidebar);

        $this->get(route('admin.administracion.dashboard'))
            ->assertOk()
            ->assertDontSee('href="'.route('admin.administracion.alertas').'"', false);
        $this->get(route('admin.administracion.alertas'))->assertForbidden();
    }

    public function test_sin_permiso_del_grupo_administrativo_no_se_ofrecen_sus_rutas(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');
        Role::findByName('ADMINISTRADOR')->revokePermissionTo('admisiones.ver_dashboard');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($usuario->fresh());

        $this->assertFalse(app(VisibilidadNavegacion::class)->puedeVerRuta('admin.administracion.residentes'));
        $this->assertFalse(app(VisibilidadNavegacion::class)->puedeVerRuta('admin.admisiones.preadmisiones'));
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('admin.administracion.dashboard'))->assertForbidden();
    }

    public function test_error_403_no_depende_de_cdns_y_tiene_salida_publica(): void
    {
        $html = view('errors.403')->render();

        $this->assertStringContainsString('No tienes acceso a esta sección', $html);
        $this->assertStringContainsString(route('welcome'), $html);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('unpkg.com', $html);
        $this->assertStringNotContainsString('Tu rol institucional', $html);
    }

    public function test_superadministrador_solo_lector_no_recibe_acciones_medicas(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('SUPERADMINISTRADOR');

        Livewire::actingAs($usuario)->test(ValoracionMedicaPanel::class)
            ->assertDontSee('Nueva valoración')
            ->call('abrirCrear')->assertForbidden();

        Livewire::actingAs($usuario)->test(PacientesSeguimientoPanel::class)
            ->call('nuevaNota', 'RES_INEXISTENTE')->assertForbidden();

        Livewire::actingAs($usuario)->test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])
            ->assertDontSee('Nueva Evaluación')
            ->call('nuevaEvaluacion')->assertForbidden();
    }

    public function test_politica_del_listado_no_ofrece_residentes_al_familiar(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('FAMILIAR');
        $this->actingAs($usuario);

        $this->assertFalse(app(VisibilidadNavegacion::class)->puedeVerRuta('admin.residentes.index', 'residentes.ver'));
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('href="'.route('admin.residentes.index').'"', false);
        $this->get(route('admin.residentes.index'))->assertForbidden();
    }

    public function test_lector_de_turnos_no_puede_ver_ni_invocar_asignaciones(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');
        $this->actingAs($usuario);

        Livewire::actingAs($usuario)->test(TurnosAsignacionesPanel::class)
            ->assertDontSee('Nueva asignación')
            ->assertDontSee('wire:click="abrirAsignarPlaza', false)
            ->call('abrirAsignarPlaza', 'PLAZA_TEST', now()->toDateString())->assertForbidden();
    }

    public function test_perfiles_autorizados_conservan_botones_de_escritura(): void
    {
        $administrador = User::factory()->create(['estado' => 'ACTIVO']);
        $administrador->assignRole('ADMINISTRADOR');
        Livewire::actingAs($administrador)->test(TurnosAsignacionesPanel::class)
            ->assertSee('Nueva asignación');

        $medico = User::factory()->create(['estado' => 'ACTIVO']);
        $medico->assignRole('MEDICO GENERAL/GERIATRA');
        Livewire::actingAs($medico)->test(ValoracionMedicaPanel::class)
            ->assertSee('Nueva valoración');
    }

    public function test_directorio_legacy_oculta_acciones_de_gestion_a_lector(): void
    {
        $residente = Residente::factory()->create();
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('MEDICO GENERAL/GERIATRA');
        $this->actingAs($usuario);

        $this->get(route('admin.adultos-mayores.index'))
            ->assertOk()
            ->assertDontSee('Nueva preadmisión')
            ->assertDontSee('Censo en PDF')
            ->assertDontSee('Editar Ficha');
        $this->get(route('admin.adultos-mayores.edit', $residente->cod_residente))->assertForbidden();

        Livewire::actingAs($usuario)->test(AdultosMayoresPanel::class)
            ->call('editarAdultoMayor', 'RES_INEXISTENTE')->assertForbidden();
    }

    public function test_familiar_no_puede_abrir_directorio_legacy_ni_ficha_ajena(): void
    {
        $residente = Residente::factory()->create();
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('FAMILIAR');
        $this->actingAs($usuario);

        $this->get(route('admin.adultos-mayores.index'))->assertForbidden();
        $this->get(route('admin.adultos-mayores.show', $residente->cod_residente))->assertForbidden();
    }

    public function test_familiar_vinculado_ve_solo_sus_secciones_y_no_accede_a_subrutas_clinicas(): void
    {
        $residente = Residente::factory()->create();
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('FAMILIAR');
        $contacto = Contacto::query()->create([
            'cod_contacto' => 'CTO_PERMISOS',
            'cod_usuario' => $usuario->cod_usuario,
            'nombres' => 'Familiar',
            'apellido_paterno' => 'Vinculado',
            'estado' => 'ACTIVO',
        ]);
        ResidenteContacto::query()->create([
            'cod_residente_contacto' => 'RCO_PERMISOS',
            'cod_residente' => $residente->cod_residente,
            'cod_contacto' => $contacto->cod_contacto,
            'parentesco' => 'HIJA',
            'responsable_principal' => true,
            'contacto_emergencia' => true,
            'autoriza_informacion' => true,
            'autoriza_salida' => false,
            'estado' => 'ACTIVO',
        ]);
        $this->actingAs($usuario);

        $this->get(route('admin.adultos-mayores.show', $residente->cod_residente))
            ->assertRedirect(route('admin.residentes.show', $residente));
        $this->get(route('admin.residentes.show', $residente))
            ->assertOk()
            ->assertSee($residente->nombres)
            ->assertDontSee('Contactos autorizados')
            ->assertDontSee('Prescripciones')
            ->assertDontSee('Expediente JSON')
            ->assertDontSee('Salud resumida')
            ->assertDontSee('Cognitivo resumido')
            ->assertDontSee('Editar datos')
            ->assertDontSee('href="'.route('admin.adultos-mayores.reporte-individual', $residente->cod_residente).'"', false);
        $this->get(route('admin.adultos-mayores.atenciones.index', $residente->cod_residente))->assertForbidden();
        $this->get(route('admin.adultos-mayores.observaciones.index', $residente->cod_residente))->assertForbidden();
        $this->get(route('admin.adultos-mayores.documentos.index', $residente->cod_residente))
            ->assertForbidden();
        $ajeno = Residente::factory()->create();
        $this->get(route('admin.adultos-mayores.documentos.index', $ajeno->cod_residente))->assertForbidden();
    }
}
