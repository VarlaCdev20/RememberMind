<?php

namespace Tests\Feature;

use App\Backend\Modulos\Administracion\Servicios\DirectorioResidentesService;
use App\Backend\Modulos\Administracion\Servicios\PanelResidenteService;
use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\Habitacion;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use App\Policies\ResidentePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResidentesEspaciosTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::policy(Residente::class, ResidentePolicy::class);
        foreach (['residentes.ver', 'admisiones.formalizar', 'admisiones.ver_dashboard'] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }
        Role::findOrCreate('ADMINISTRADOR', 'web');
        Role::findOrCreate('FAMILIAR', 'web');
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('ADMINISTRADOR');
        $this->usuario->givePermissionTo(['residentes.ver', 'admisiones.formalizar', 'admisiones.ver_dashboard']);
    }

    public function test_directorio_y_metricas_muestran_datos_reales_sin_inventar_pisos_o_estados(): void
    {
        $uno = $this->residente('1', 'Planta baja');
        $dos = $this->residente('2', 'Piso 2');
        DB::table('ocupaciones_cama')->where('cod_residente', $dos['residente']->getKey())->update(['estado' => 'FINALIZADA', 'fecha_hora_liberacion' => now()]);
        $dos['residente']->update(['estado' => 'INACTIVO']);
        Habitacion::query()->create(['cod_habitacion' => 'HAB_SINPISO', 'codigo' => 'SIN-PISO', 'piso' => null, 'estado' => 'ACTIVA']);

        $datos = app(DirectorioResidentesService::class)->datos([]);
        $this->assertSame(2, $datos['registros']->total());
        $this->assertSame(1, $datos['resumenResidentes']['con_cama']);
        $this->assertSame(1, $datos['resumenResidentes']['sin_cama']);
        $this->assertSame(0, $datos['resumenResidentes']['sin_admision']);
        $this->assertSame(0, $datos['resumenResidentes']['sin_responsable']);
        $this->assertSame(['ADMITIDO', 'INACTIVO'], $datos['estadosResidentes']->all());
        $this->assertSame(['Piso 2', 'Planta baja'], $datos['pisosDisponibles']->all());
        $this->assertSame(2, (int) $datos['distribucionPisos']->sum('total'));
        $this->assertSame('Planta baja', $datos['registros']->firstWhere('codigo', $uno['residente']->getKey())->piso);
        $this->assertNull($datos['registros']->firstWhere('codigo', $dos['residente']->getKey())->cod_cama);
        $this->assertDatabaseCount('residentes', 2);
    }

    public function test_filtros_exactos_de_piso_habitacion_estado_y_documento_se_combinan(): void
    {
        $uno = $this->residente('1', 'Planta baja');
        $this->residente('2', 'Piso 2');
        $uno['residente']->update(['numero_documento' => 'DOC-PRUEBA', 'apellido_materno' => 'Materno']);
        $servicio = app(DirectorioResidentesService::class);
        $datos = $servicio->datos(['piso' => 'Planta baja', 'cod_habitacion' => $uno['habitacion']->getKey(), 'estado' => 'ADMITIDO', 'search' => 'DOC-PRUEBA', 'alojamiento' => 'con_cama'], 'tabla', 20);
        $this->assertSame(1, $datos['registros']->total());
        $this->assertSame($uno['residente']->getKey(), $datos['registros']->first()->codigo);
        $this->assertSame(1, $datos['resumenResidentes']['total']);
        $this->assertSame(2, $datos['resumenResidentes']['total_institucional']);
        $this->assertSame(1, (int) $datos['distribucionPisos']->sum('total'));
        $this->assertSame(1, $servicio->datos(['search' => 'materno'])['registros']->total());
        $this->assertSame(0, $servicio->datos(['piso' => 'Piso 2', 'cod_habitacion' => $uno['habitacion']->getKey()])['registros']->total());
    }

    public function test_varios_contactos_principales_no_duplican_residente_ni_metricas(): void
    {
        $datos = $this->residente('1', 'Piso 1');
        $contacto = Contacto::query()->create(['cod_contacto' => 'CTO_EXTRA', 'nombres' => 'Segundo', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        ResidenteContacto::query()->create([
            'cod_residente_contacto' => 'RCO_EXTRA', 'cod_residente' => $datos['residente']->getKey(),
            'cod_contacto' => $contacto->getKey(), 'parentesco' => 'HIJO', 'responsable_principal' => true,
            'contacto_emergencia' => true, 'autoriza_informacion' => false, 'autoriza_salida' => false, 'estado' => 'ACTIVO',
        ]);
        $directorio = app(DirectorioResidentesService::class)->datos([]);
        $this->assertSame(1, $directorio['registros']->total());
        $this->assertSame(1, $directorio['resumenResidentes']['total']);
        $this->assertSame(0, $directorio['resumenResidentes']['sin_responsable']);
        $this->assertNotSame('', $directorio['registros']->first()->responsable);
    }

    public function test_piso_sin_registrar_filtra_alojamiento_real_y_conserva_cama_en_panel(): void
    {
        $datos = $this->residente('1', 'Piso 1');
        $datos['habitacion']->update(['piso' => null]);
        $this->residente('2', 'Piso 2');
        $mapa = app(DirectorioResidentesService::class)->datos(['piso' => '__sin_piso__'], 'camas');
        $this->assertSame(1, $mapa['habitacionesPaginadas']->total());
        $this->assertSame(1, $mapa['resumenResidentes']['con_cama']);
        $this->assertSame(1, $mapa['resumenResidentes']['camas_ocupadas']);
        $panel = app(PanelResidenteService::class)->datos($datos['residente'], 'historial');
        $this->assertSame('C-1', $panel['habitacion']->cama);
        $this->assertCount(1, $panel['historial_alojamiento']);
        $this->actingAs($this->usuario)->get(route('admin.administracion.residentes', ['residente' => $datos['residente']->getKey(), 'panel_tab' => 'historial', 'vista' => 'camas', 'piso' => '__sin_piso__', 'page' => 2]))
            ->assertOk()->assertSee('Trayectoria de alojamiento')->assertSee('C-1')
            ->assertSee('page=2', false);
    }

    public function test_mapa_distingue_ocupada_disponible_y_no_habilitada_con_regla_canonica(): void
    {
        $datos = $this->residente('1', 'Piso 1');
        DB::table('ocupaciones_cama')->where('cod_residente', $datos['residente']->getKey())->update(['estado' => 'ACTIVO', 'fecha_hora_liberacion' => now()]);
        Cama::query()->create(['cod_cama' => 'CAM_LIBRE', 'cod_habitacion' => $datos['habitacion']->getKey(), 'codigo' => 'C-LIBRE', 'estado' => 'DISPONIBLE']);
        Cama::query()->create(['cod_cama' => 'CAM_MANT', 'cod_habitacion' => $datos['habitacion']->getKey(), 'codigo' => 'C-MANT', 'estado' => 'MANTENIMIENTO']);
        $inactiva = Habitacion::query()->create(['cod_habitacion' => 'HAB_INACTIVA', 'codigo' => 'H-INACTIVA', 'piso' => 'Piso 1', 'estado' => 'INACTIVA']);
        Cama::query()->create(['cod_cama' => 'CAM_INACTIVA', 'cod_habitacion' => $inactiva->getKey(), 'codigo' => 'C-INACTIVA', 'estado' => 'DISPONIBLE']);

        $mapa = app(DirectorioResidentesService::class)->datos([], 'camas');
        $camas = $mapa['habitacionesPaginadas']->getCollection()->flatMap(fn ($habitacion) => $habitacion->camas);
        $this->assertSame('ocupado', $camas->firstWhere('cod_cama', $datos['cama']->getKey())->estado_mapa);
        $this->assertSame($datos['residente']->getKey(), $camas->firstWhere('cod_cama', $datos['cama']->getKey())->ocupante->codigo);
        $this->assertSame('disponible', $camas->firstWhere('cod_cama', 'CAM_LIBRE')->estado_mapa);
        $this->assertSame('no_habilitado', $camas->firstWhere('cod_cama', 'CAM_MANT')->estado_mapa);
        $this->assertSame('no_habilitado', $camas->firstWhere('cod_cama', 'CAM_INACTIVA')->estado_mapa);
        $this->assertSame(1, $mapa['resumenResidentes']['camas_disponibles']);
        $this->assertSame(1, $mapa['resumenResidentes']['camas_ocupadas']);
        $this->assertSame(2, $mapa['resumenResidentes']['camas_no_habilitadas']);
        $panel = app(PanelResidenteService::class)->datos($datos['residente'], 'resumen');
        $this->assertSame($datos['cama']->getKey(), $panel['habitacion']->cod_cama);
        $this->assertSame($datos['cama']->codigo, $panel['habitacion']->cama);
    }

    public function test_mapa_respeta_filtro_residente_y_no_coloca_residentes_sin_cama(): void
    {
        $uno = $this->residente('1', 'Piso 1');
        $dos = $this->residente('2', 'Piso 2');
        $servicio = app(DirectorioResidentesService::class);
        $filtrado = $servicio->datos(['search' => $uno['residente']->getKey()], 'camas');
        $this->assertSame(1, $filtrado['habitacionesPaginadas']->total());
        $this->assertSame($uno['residente']->getKey(), $filtrado['habitacionesPaginadas']->first()->camas->first()->ocupante->codigo);
        DB::table('ocupaciones_cama')->where('cod_residente', $dos['residente']->getKey())->update(['estado' => 'FINALIZADA', 'fecha_hora_liberacion' => now()]);
        $sinCama = $servicio->datos(['alojamiento' => 'sin_cama'], 'camas');
        $this->assertSame(1, $sinCama['resumenResidentes']['sin_cama']);
        $this->assertSame(0, $sinCama['habitacionesPaginadas']->total());
        $this->assertNull($sinCama['registros']);
    }

    public function test_mapa_pagina_habitaciones_y_consultas_no_crecen_por_cantidad_de_camas(): void
    {
        for ($indice = 1; $indice <= 12; $indice++) {
            $habitacion = Habitacion::query()->create(['cod_habitacion' => 'HAB_'.$indice, 'codigo' => 'H-'.str_pad((string) $indice, 2, '0', STR_PAD_LEFT), 'piso' => 'Piso 1', 'estado' => 'ACTIVA']);
            Cama::query()->create(['cod_cama' => 'CAM_'.$indice, 'cod_habitacion' => $habitacion->getKey(), 'codigo' => 'C-'.$indice, 'estado' => 'ACTIVA']);
        }
        $servicio = app(DirectorioResidentesService::class);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $pagina = $servicio->datos([], 'camas', 10);
        $cantidad = count(DB::getQueryLog());
        DB::flushQueryLog();
        $completa = $servicio->datos([], 'camas', 20);
        $this->assertSame($cantidad, count(DB::getQueryLog()));
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(16, $cantidad);
        $this->assertSame(12, $pagina['habitacionesPaginadas']->total());
        $this->assertCount(10, $pagina['habitacionesPaginadas']);
        $this->assertSame(10, $pagina['habitacionesPaginadas']->getCollection()->sum(fn ($habitacion) => $habitacion->camas->count()));
        $this->assertCount(12, $completa['habitacionesPaginadas']);
    }

    public function test_directorio_exige_permiso_cuenta_activa_y_policy_global(): void
    {
        $this->get(route('admin.administracion.residentes'))->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['estado' => 'ACTIVO']))->get(route('admin.administracion.residentes'))->assertForbidden();
        $familiar = User::factory()->create(['estado' => 'ACTIVO']);
        $familiar->assignRole('FAMILIAR');
        $familiar->givePermissionTo(['residentes.ver', 'admisiones.ver_dashboard']);
        $this->actingAs($familiar)->get(route('admin.administracion.residentes'))->assertForbidden();
        $this->usuario->update(['estado' => 'INACTIVO']);
        $this->actingAs($this->usuario)->get(route('admin.administracion.residentes'))->assertForbidden();
        $this->assertDatabaseCount('residentes', 0);
    }

    public function test_nueva_vista_conserva_datos_filtros_paginacion_y_policy_del_panel(): void
    {
        $datos = $this->residente('1', 'Piso 1');
        $this->actingAs($this->usuario)->get(route('admin.administracion.residentes'))
            ->assertOk()->assertViewIs('pages.admin.administracion.residentes')
            ->assertViewHas('vistaResidentes', 'tarjetas')->assertViewHas('porPagina', 10)
            ->assertViewHas('registros', fn ($registros) => $registros->total() === 1);
        $this->get(route('admin.administracion.residentes', ['vista' => 'camas', 'piso' => 'Piso 1', 'por_pagina' => 50]))
            ->assertOk()->assertViewHas('registros', null)->assertViewHas('habitacionesPaginadas', fn ($habitaciones) => $habitaciones->total() === 1);
        $this->get(route('admin.administracion.residentes', ['estado' => 'INVENTADO', 'piso' => 'INVENTADO', 'cod_habitacion' => 'INEXISTENTE']))
            ->assertSessionHasErrors(['estado', 'piso', 'cod_habitacion']);
        $this->get(route('admin.administracion.residentes', ['vista' => 'extra', 'alojamiento' => 'extra', 'por_pagina' => 100]))
            ->assertSessionHasErrors(['vista', 'alojamiento', 'por_pagina']);
        Gate::policy(Residente::class, ResidentePanelDenegadoPolicy::class);
        $this->get(route('admin.administracion.residentes', ['residente' => $datos['residente']->getKey()]))->assertForbidden();
        $this->assertDatabaseCount('residentes', 1);
    }

    private function residente(string $codigo, string $piso): array
    {
        $contacto = Contacto::query()->create(['cod_contacto' => 'CTO_'.$codigo, 'nombres' => 'Ana', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        $habitacion = Habitacion::query()->create(['cod_habitacion' => 'HAB_'.$codigo, 'codigo' => 'H-'.$codigo, 'piso' => $piso, 'estado' => 'ACTIVA', 'capacidad' => 3]);
        $cama = Cama::query()->create(['cod_cama' => 'CAM_'.$codigo, 'cod_habitacion' => $habitacion->getKey(), 'codigo' => 'C-'.$codigo, 'estado' => 'ACTIVA']);
        $solicitud = Preadmision::query()->create([
            'cod_preadmision' => 'PRE_'.$codigo, 'cod_contacto' => $contacto->getKey(), 'cod_usuario_registro' => $this->usuario->getKey(),
            'nombres' => 'Rosa '.$codigo, 'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1945-05-05',
            'motivo_ingreso' => 'Acompañamiento', 'fecha_solicitud' => now(), 'estado' => 'APROBADA',
        ]);
        $residente = app(FormalizarAdmision::class)->ejecutar($solicitud, ['cod_cama' => $cama->getKey()], $this->usuario);

        return compact('residente', 'habitacion', 'cama', 'contacto');
    }
}

class ResidentePanelDenegadoPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('residentes.ver');
    }

    public function view(User $usuario, Residente $residente): bool
    {
        return false;
    }
}
