<?php

namespace Tests\Feature;

use App\Backend\Modulos\Administracion\Servicios\MapaHabitacionesService;
use App\Backend\Modulos\Admisiones\Acciones\CambiarAlojamientoResidente;
use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Frontend\Livewire\Admisiones\HabitacionesPanel;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\Habitacion;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\User;
use App\Policies\ResidentePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MapaHabitacionesTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::policy(Residente::class, ResidentePolicy::class);
        foreach (['habitaciones.ver', 'admisiones.ver_dashboard', 'residentes.ver', 'admisiones.formalizar', 'ocupaciones_cama.gestionar'] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }
        Role::findOrCreate('ADMINISTRADOR', 'web');
        Role::findOrCreate('FAMILIAR', 'web');
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('ADMINISTRADOR');
        $this->usuario->givePermissionTo(['habitaciones.ver', 'admisiones.ver_dashboard', 'residentes.ver', 'admisiones.formalizar', 'ocupaciones_cama.gestionar']);
        $this->actingAs($this->usuario);
    }

    public function test_mapa_representa_ocupacion_real_disponibilidad_y_habitacion_bloqueada(): void
    {
        $residente = $this->escenario();
        Habitacion::create(['cod_habitacion' => 'HAB_BLOQ', 'codigo' => 'HB', 'estado' => 'BLOQUEADA', 'piso' => '2']);
        Cama::create(['cod_cama' => 'CAM_BLOQ', 'codigo' => 'CB-BLOQUEADA', 'cod_habitacion' => 'HAB_BLOQ', 'estado' => 'DISPONIBLE']);
        $mapa = $this->datos();
        $this->assertSame(['ocupado' => 1, 'disponible' => 1, 'no_habilitado' => 1, 'total' => 3], $mapa['resumen']);
        $this->assertSame(2, $mapa['habitaciones']->total());
        $ocupada = $mapa['habitaciones']->firstWhere('cod_habitacion', 'HAB_A')->camas->firstWhere('cod_cama', 'CAM_A');
        $this->assertSame($residente->getKey(), $ocupada->cod_residente);
        $this->assertSame('ocupado', $ocupada->estado_mapa);
        $this->assertDatabaseCount('ocupaciones_cama', 1);
    }

    public function test_filtros_busqueda_sin_mayusculas_y_piso_sin_registrar(): void
    {
        $this->escenario();
        Habitacion::whereKey('HAB_A')->update(['piso' => null]);
        $datos = $this->datos(['search' => 'sintetica', 'piso' => '__sin_piso__', 'disponibilidad' => 'ocupado']);
        $this->assertSame(1, $datos['habitaciones']->total());
        $this->assertCount(1, $datos['habitaciones']->first()->camas);
        $this->assertSame(0, $this->datos(['piso' => '2'])['habitaciones']->total());
        $this->assertSame(1, $this->datos(['disponibilidad' => 'disponible'], 'tabla')['camas']->total());
        $this->assertSame(2, $this->datos(['disponibilidad' => 'disponible'])['resumen']['total']);
    }

    public function test_identidad_y_historial_no_se_envian_con_solo_permiso_fisico(): void
    {
        $this->escenario();
        $this->usuario->revokePermissionTo('residentes.ver');
        $datos = $this->datos(['cama' => 'CAM_A']);
        $this->assertFalse($datos['verPersonas']);
        $this->assertNull($datos['detalle']->ocupante);
        $this->assertNull($datos['detalle']->cod_residente);
        $this->assertEmpty($datos['historial']);
        $this->get(route('admin.administracion.habitaciones', ['cama' => 'CAM_A']))->assertOk()->assertDontSee('Persona Sintetica');
        $this->assertSame(0, $this->datos(['search' => 'sintetica'])['habitaciones']->total());
    }

    public function test_detalle_incluye_caracteristicas_tiempos_y_motivo_real_del_traslado(): void
    {
        $residente = $this->escenario();
        app(CambiarAlojamientoResidente::class)->ejecutar($residente, [
            'cod_cama' => 'CAM_B', 'cod_ocupacion_anterior' => $residente->ocupacionActiva->getKey(),
            'fecha_hora' => now()->toDateTimeString(), 'motivo' => 'Cambio de habitación solicitado',
        ], $this->usuario);
        $datos = $this->datos(['cama' => 'CAM_B']);
        $this->assertStringContainsString('Cambio de habitación solicitado', $datos['origenOcupacion']);
        $this->assertSame('Articulada', $datos['detalle']->tipo);
        $this->assertCount(1, $datos['historial']);
        $previa = $this->datos(['cama' => 'CAM_A']);
        $this->assertSame('FINALIZADA', $previa['historial']->first()->estado);
        $this->assertSame('Cambio de habitación solicitado', $previa['historial']->first()->motivo_liberacion);
        $this->get(route('admin.administracion.habitaciones', ['cama' => 'CAM_B']))->assertOk()->assertSee('Articulada')->assertSee('Cambio de habitación solicitado');
    }

    public function test_vistas_paginacion_validacion_y_cama_inexistente(): void
    {
        $this->escenario();
        foreach (['camas', 'tarjetas', 'lista', 'tabla'] as $vista) {
            $this->get(route('admin.administracion.habitaciones', ['vista' => $vista]))->assertOk()->assertViewIs('pages.admin.administracion.habitaciones')->assertViewHas('vista', $vista);
        }
        $this->get(route('admin.administracion.habitaciones', ['cama' => 'INEXISTENTE']))->assertNotFound();
        $this->get(route('admin.administracion.habitaciones', ['vista' => 'error', 'disponibilidad' => 'error', 'estado' => 'INVENTADO', 'por_pagina' => 100]))->assertSessionHasErrors(['vista', 'disponibilidad', 'estado', 'por_pagina']);
    }

    public function test_cuenta_inactiva_sin_permiso_y_familiar_no_acceden_al_mapa(): void
    {
        $this->usuario->revokePermissionTo('habitaciones.ver');
        $this->get(route('admin.administracion.habitaciones'))->assertForbidden();
        $this->usuario->givePermissionTo('habitaciones.ver');
        $this->usuario->syncRoles(['FAMILIAR']);
        $this->get(route('admin.administracion.habitaciones'))->assertForbidden();
        $this->usuario->syncRoles(['ADMINISTRADOR']);
        $this->usuario->update(['estado' => 'INACTIVO']);
        $this->get(route('admin.administracion.habitaciones'))->assertForbidden();
        $this->assertDatabaseCount('ocupaciones_cama', 0);
    }

    public function test_consultas_no_crecen_con_el_numero_de_habitaciones_de_la_pagina(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Habitacion::create(['cod_habitacion' => 'H_'.$i, 'codigo' => 'H-'.$i, 'estado' => 'ACTIVA']);
            Cama::create(['cod_cama' => 'C_'.$i, 'codigo' => 'C-'.$i, 'cod_habitacion' => 'H_'.$i, 'estado' => 'DISPONIBLE']);
        }
        $this->datos();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $pagina = $this->datos(['por_pagina' => 10]);
        $cantidad = count(DB::getQueryLog());
        DB::flushQueryLog();
        $todas = $this->datos(['por_pagina' => 20]);
        $this->assertSame($cantidad, count(DB::getQueryLog()));
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(10, $cantidad);
        $this->assertCount(10, $pagina['habitaciones']);
        $this->assertCount(12, $todas['habitaciones']);
    }

    public function test_habitacion_sin_camas_se_encuentra_sin_inventar_plazas_fisicas(): void
    {
        Habitacion::create(['cod_habitacion' => 'HAB_VACIA', 'codigo' => 'HV', 'nombre' => 'Sala nueva', 'estado' => 'ACTIVA', 'capacidad' => 3]);
        $mapa = $this->datos(['search' => 'sala nueva']);
        $this->assertSame(1, $mapa['habitaciones']->total());
        $this->assertEmpty($mapa['habitaciones']->first()->camas);
        $this->assertSame(0, $mapa['resumen']['total']);
        $this->assertSame(0, $this->datos(['search' => 'sala nueva', 'disponibilidad' => 'disponible'])['habitaciones']->total());
    }

    public function test_detalle_del_ocupante_revalida_policy_contextual(): void
    {
        $this->escenario();
        Gate::policy(Residente::class, MapaResidenteDenegadoPolicy::class);
        $this->get(route('admin.administracion.habitaciones', ['cama' => 'CAM_A']))->assertForbidden();
        $this->assertDatabaseCount('ocupaciones_cama', 1);
    }

    public function test_mantenimiento_no_deshabilita_cama_ni_habitacion_con_residente(): void
    {
        $this->escenario();
        foreach (['habitaciones.editar', 'camas.editar'] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
            $this->usuario->givePermissionTo($permiso);
        }
        Livewire::test(HabitacionesPanel::class)
            ->call('abrirEditarHabitacion', 'HAB_A')->set('estado', 'BLOQUEADA')->call('guardarHabitacion')->assertHasErrors('estado');
        Livewire::test(HabitacionesPanel::class)
            ->call('editarCama', 'CAM_A')->set('estado', 'MANTENIMIENTO')->call('guardarCama')->assertHasErrors('estado');
        $this->assertDatabaseHas('habitaciones', ['cod_habitacion' => 'HAB_A', 'estado' => 'ACTIVA']);
        $this->assertDatabaseHas('camas', ['cod_cama' => 'CAM_A', 'estado' => 'ACTIVA']);
        $this->assertDatabaseCount('ocupaciones_cama', 1);
    }

    public function test_ocupacion_ofrece_traslado_solo_en_cama_disponible_y_historial_requiere_identidad_autorizada(): void
    {
        $this->escenario();
        Permission::findOrCreate('ocupaciones_cama.ver', 'web');
        $this->usuario->givePermissionTo('ocupaciones_cama.ver');
        $this->get(route('admin.administracion.ocupacion'))->assertOk()->assertSee('Elegir residente para cama CB')->assertDontSee('Elegir residente para cama CA');
        $this->get(route('admin.administracion.habitaciones'))->assertOk()->assertDontSee('Elegir residente para cama CB');
        $this->usuario->revokePermissionTo('residentes.ver');
        $this->get(route('admin.administracion.ocupacion'))->assertOk()->assertDontSee('Persona Sintetica')->assertDontSee('Elegir residente para cama');
        $this->get(route('admin.administracion.ocupacion', ['tab' => 'historial']))->assertForbidden();
        $this->assertDatabaseCount('ocupaciones_cama', 1);
    }

    private function datos(array $filtros = [], string $vista = 'camas'): array
    {
        return app(MapaHabitacionesService::class)->datos($filtros + ['vista' => $vista], $this->usuario);
    }

    private function escenario(): Residente
    {
        Habitacion::create(['cod_habitacion' => 'HAB_A', 'codigo' => 'HA', 'piso' => '1', 'capacidad' => 2, 'estado' => 'ACTIVA', 'observacion' => 'Con ventana']);
        Cama::create(['cod_cama' => 'CAM_A', 'codigo' => 'CA', 'cod_habitacion' => 'HAB_A', 'estado' => 'ACTIVA']);
        Cama::create(['cod_cama' => 'CAM_B', 'codigo' => 'CB', 'cod_habitacion' => 'HAB_A', 'tipo' => 'Articulada', 'estado' => 'DISPONIBLE']);
        $contacto = Contacto::create(['cod_contacto' => 'CTO_A', 'nombres' => 'Ana', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        $solicitud = Preadmision::create(['cod_preadmision' => 'PRE_A', 'cod_contacto' => $contacto->getKey(), 'cod_usuario_registro' => $this->usuario->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Sintetica', 'fecha_nacimiento' => '1945-05-05', 'motivo_ingreso' => 'Acompañamiento', 'fecha_solicitud' => now()->subDay(), 'estado' => 'APROBADA']);

        return app(FormalizarAdmision::class)->ejecutar($solicitud, ['cod_cama' => 'CAM_A', 'fecha_hora_admision' => now()->subDay()->toDateTimeString()], $this->usuario);
    }
}

class MapaResidenteDenegadoPolicy
{
    public function viewAny(User $usuario): bool
    {
        return true;
    }

    public function view(User $usuario, Residente $residente): bool
    {
        return false;
    }
}
