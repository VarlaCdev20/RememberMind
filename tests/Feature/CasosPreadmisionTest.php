<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Admisiones\PreadmisionesPanel;
use App\Frontend\Livewire\Admisiones\PreadmisionWizard;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CasosPreadmisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'admisiones.ver_dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'admisiones.crear', 'guard_name' => 'web']);
        $roleSuper = Role::firstOrCreate(['name' => 'SUPERADMINISTRADOR', 'guard_name' => 'web']);
        $roleSuper->givePermissionTo(['admisiones.ver_dashboard', 'admisiones.crear']);
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

    public function test_estado_y_orden_comparten_la_unica_barra_de_filtros(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');

        $html = $this->actingAs($user)->get(route('admin.admisiones.preadmisiones'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'aria-label="Filtrar preadmisiones"'));
        $this->assertStringNotContainsString('class="rm-pre-states"', $html);
        $this->assertStringContainsString('wire:model.live="estado"', $html);
        $this->assertStringContainsString('wire:model.live="orden"', $html);
        $this->assertStringContainsString('Aprobadas (0)', $html);

        Livewire::test(PreadmisionesPanel::class)
            ->set('estado', 'APROBADA')
            ->set('orden', 'antiguas')
            ->call('limpiarFiltros')
            ->assertSet('estado', '')
            ->assertSet('orden', 'recientes');

        $rechazadas = $this->get(route('admin.admisiones.preadmisiones.rechazadas'))->assertOk()->getContent();
        $this->assertStringContainsString('Filtrar por estado de preadmisión', $rechazadas);
        $this->assertStringContainsString('Rechazadas (0)', $rechazadas);
    }

    public function test_expediente_y_sus_pestanas_se_abren_sin_crear_datos(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');
        $solicitud = Preadmision::create([
            'cod_preadmision' => 'PRE_UI_001',
            'cod_usuario_registro' => $user->cod_usuario,
            'estado' => 'PENDIENTE',
            'fecha_solicitud' => '2026-09-25 09:12:00',
            'nombres' => 'MARIO',
            'apellido_paterno' => 'GUTIERREZ',
            'numero_documento' => '2384912',
            'fecha_nacimiento' => '1946-01-01',
            'motivo_ingreso' => 'CUIDADO_INTEGRAL',
            'prioridad' => 'MEDIA',
        ]);

        $this->actingAs($user);
        $this->get(route('admin.admisiones.preadmisiones', ['solicitud' => $solicitud->cod_preadmision, 'tab' => 'documentos']))
            ->assertOk()
            ->assertSee('Expediente de preadmisión')
            ->assertSee('No hay documentos registrados');

        Livewire::test(PreadmisionesPanel::class)
            ->call('verDetalle', $solicitud->cod_preadmision)
            ->assertSet('modalDetalle', true)
            ->assertSee('Expediente de preadmisión')
            ->call('revisarSolicitud')
            ->assertSet('panelModo', 'revision')
            ->assertSee('Aprobar preadmisión')
            ->call('volverAlResumen')
            ->call('cambiarPanelTab', 'documentos')
            ->assertSee('No hay documentos registrados')
            ->call('cambiarPanelTab', 'historial')
            ->assertSee('Solicitud registrada')
            ->call('cerrarDetalle')
            ->assertSet('modalDetalle', false);
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

    public function test_usuario_que_solo_puede_ver_no_puede_registrar_preadmision(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->givePermissionTo('admisiones.ver_dashboard');

        $this->actingAs($user)
            ->get(route('admin.admisiones.preadmision'))
            ->assertForbidden();
    }

    public function test_creacion_y_persistencia_de_caso_en_base_de_datos(): void
    {
        $usuario = User::factory()->create();
        $caso = Preadmision::create([
            'cod_preadmision' => 'PRE_PRUEBA_001',
            'cod_usuario_registro' => $usuario->cod_usuario,
            'estado' => 'PENDIENTE',
            'fecha_solicitud' => '2026-09-03 08:00:00',
            'nombres' => 'ROBERTO',
            'apellido_paterno' => 'CONDORI',
            'apellido_materno' => 'FLORES',
            'numero_documento' => '4455667',
            'fecha_nacimiento' => '1950-01-01',
            'genero' => 'MASCULINO',
            'estado_civil' => 'SOLTERO',
            'telefono' => '70011223',
            'direccion' => 'LA PAZ, LA PAZ',
            'motivo_ingreso' => 'CUIDADO_INTEGRAL',
            'procedencia' => 'FAMILIAR',
            'tipo_ingreso' => 'REGULAR',
            'permanencia' => 'PERMANENTE',
            'prioridad' => 'ALTA',
            'descripcion_caso' => 'Caso evaluado para ingreso prioritario.',
        ]);

        $this->assertDatabaseHas('preadmisiones', [
            'cod_preadmision' => $caso->cod_preadmision,
            'nombres' => 'ROBERTO',
            'apellido_paterno' => 'CONDORI',
            'descripcion_caso' => 'Caso evaluado para ingreso prioritario.',
            'prioridad' => 'ALTA',
        ]);
    }

    public function test_maquina_de_estados_canonica_de_preadmision_y_ausencia_de_pseudoestado(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('SUPERADMINISTRADOR');
        $personal = Personal::query()->create([
            'cod_personal' => 'PER_PRE_01',
            'cod_usuario' => $usuario->cod_usuario,
            'nombres' => 'ENFERMERA',
            'apellido_paterno' => 'TEST',
            'numero_documento' => 'ENF-PRE-01',
            'profesion' => 'ENFERMERA',
            'estado' => 'ACTIVO',
        ]);

        // 1. Nueva preadmisión = PENDIENTE
        $caso = Preadmision::create([
            'cod_preadmision' => 'PRE_TEST_EST_01',
            'cod_usuario_registro' => $usuario->cod_usuario,
            'estado' => 'PENDIENTE',
            'fecha_solicitud' => now(),
            'nombres' => 'MARIA',
            'apellido_paterno' => 'MAMANI',
            'fecha_nacimiento' => '1945-05-10',
            'motivo_ingreso' => 'EVALUACION',
        ]);

        $this->assertSame('PENDIENTE', $caso->estado);
        $this->assertDatabaseHas('preadmisiones', [
            'cod_preadmision' => 'PRE_TEST_EST_01',
            'estado' => 'PENDIENTE',
        ]);

        // 2. Valoración de enfermería NO cambia el estado de la preadmisión
        $caso->valoracion_enfermeria = [
            'estado_general' => 'BUENO',
            'nivel_conciencia' => 'ALERTA',
            'orientacion_persona' => 'ORIENTADO',
            'orientacion_tiempo' => 'ORIENTADO',
            'orientacion_espacio' => 'ORIENTADO',
            'comunicacion' => 'NORMAL',
            'movilidad' => 'INDEPENDIENTE',
            'riesgo_caida' => 'BAJO',
            'talla' => 160.0,
            'peso' => 65.0,
        ];
        $caso->save();

        $casoFresco = $caso->fresh();
        $this->assertSame('PENDIENTE', $casoFresco->estado);
        $this->assertNotNull($casoFresco->valoracionEnfermeriaRegistro);

        // 3. No aparece PENDIENTE_VALORACION_MEDICA en la persistencia
        $this->assertDatabaseMissing('preadmisiones', [
            'cod_preadmision' => 'PRE_TEST_EST_01',
            'estado' => 'PENDIENTE_VALORACION_MEDICA',
        ]);

        // 4. Aprobación -> APROBADA
        $casoFresco->update([
            'estado' => 'APROBADA',
            'fecha_revision' => now(),
            'cod_usuario_revision' => $usuario->cod_usuario,
        ]);
        $this->assertSame('APROBADA', $casoFresco->fresh()->estado);

        // 5. Rechazo -> RECHAZADA (nuevo caso que pasa a rechazo)
        $casoRechazable = Preadmision::create([
            'cod_preadmision' => 'PRE_TEST_EST_02',
            'cod_usuario_registro' => $usuario->cod_usuario,
            'estado' => 'PENDIENTE',
            'fecha_solicitud' => now(),
            'nombres' => 'PEDRO',
            'apellido_paterno' => 'QUISPE',
            'fecha_nacimiento' => '1948-03-12',
            'motivo_ingreso' => 'EVALUACION',
        ]);
        $casoRechazable->update([
            'estado' => 'RECHAZADA',
            'motivo_rechazo' => 'NO CUMPLE CRITERIOS DE INGRESO',
            'fecha_revision' => now(),
            'cod_usuario_revision' => $usuario->cod_usuario,
        ]);
        $this->assertSame('RECHAZADA', $casoRechazable->fresh()->estado);
    }

    public function test_proteccion_anti_regresion_de_pseudoestado_en_codigo_funcional(): void
    {
        $salida = [];
        $codigoRetorno = 0;
        exec('git grep -n "PENDIENTE_VALORACION_MEDICA" app resources routes', $salida, $codigoRetorno);

        $this->assertSame([], $salida, 'Se detectaron residuos funcionales de PENDIENTE_VALORACION_MEDICA.');
    }

    public function test_vistas_paginacion_y_graficos_usan_solicitudes_reales(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 6));
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);

        foreach (['PENDIENTE', 'APROBADA', 'RECHAZADA'] as $i => $estado) {
            Preadmision::create([
                'cod_preadmision' => 'PRE_GRAF_'.$i,
                'cod_usuario_registro' => $user->cod_usuario,
                'estado' => $estado,
                'nombres' => 'PERSONA SINTETICA',
                'apellido_paterno' => 'PRUEBA',
                'fecha_nacimiento' => '1940-01-01',
                'motivo_ingreso' => 'CUIDADO_PERMANENTE',
                'fecha_solicitud' => $i === 0 ? '2026-09-10 12:00:00' : '2026-10-01 12:00:00',
            ]);
        }

        $panel = Livewire::test(PreadmisionesPanel::class)
            ->assertViewHas('metricas', fn ($m) => $m['total'] === 3 && $m['pendientes'] === 1 && $m['aprobadas'] === 1 && $m['rechazadas'] === 1)
            ->assertViewHas('tendencia', fn ($t) => $t->count() === 6 && $t->last()['cantidad'] === 2)
            ->set('vista', 'tabla')->assertSee('rm-pre-table', false)
            ->set('vista', 'tarjetas')->assertSee('rm-pre-collection--tarjetas', false)
            ->set('vista', 'invalida')->assertSet('vista', 'lista')
            ->set('porPagina', 999)->assertSet('porPagina', 10)
            ->call('filtrarMes', '2026-09')->assertSet('fecha_inicio', '2026-09-01')->assertSet('fecha_fin', '2026-09-30')
            ->assertViewHas('preadmisiones', fn ($p) => $p->total() === 1)
            ->call('limpiarFechas')->assertViewHas('preadmisiones', fn ($p) => $p->total() === 3);

        $panel->call('filtrarMes', 'mes-invalido')->assertSet('fecha_inicio', '');
        $this->assertDatabaseCount('preadmisiones', 3);
        $this->assertDatabaseCount('residentes', 0);
    }

    public function test_formulario_no_permite_saltar_validacion_con_el_nuevo_navegador(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);
        Livewire::test(PreadmisionWizard::class)
            ->call('volverPaso', 5)->assertSet('paso', 1)
            ->call('siguiente')->assertHasErrors(['nombres', 'ci', 'fecha_nac'])->assertSet('paso', 1)
            ->set('paso', 3)->call('volverPaso', 1)->assertSet('paso', 1)
            ->set('paso', 5)->assertSee('Revisa antes de registrar')
            ->assertSee('Editar datos del responsable')
            ->call('volverPaso', 3)->assertSet('paso', 3);
        $this->assertDatabaseCount('preadmisiones', 0);
        $this->assertDatabaseCount('residentes', 0);
    }
}
