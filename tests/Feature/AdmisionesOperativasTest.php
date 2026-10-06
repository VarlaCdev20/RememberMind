<?php

namespace Tests\Feature;

use App\Backend\Modulos\Administracion\Servicios\ConsultaOperativaService;
use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Frontend\Livewire\Admisiones\PreadmisionesPanel;
use App\Models\Admision;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\Habitacion;
use App\Models\OcupacionCama;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdmisionesOperativasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['admisiones.formalizar', 'admisiones.ver_dashboard'] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }
        Role::findOrCreate('ADMINISTRADOR', 'web');
    }

    public function test_rol_administrativo_sin_permiso_no_abre_ni_formaliza_desde_livewire(): void
    {
        $datos = $this->escenario();
        $datos['usuario']->revokePermissionTo('admisiones.formalizar');
        $this->actingAs($datos['usuario']);

        Livewire::test(PreadmisionesPanel::class)->call('abrirAdmision', $datos['solicitud']->getKey())->assertForbidden();
        Livewire::test(PreadmisionesPanel::class)
            ->set('codPreAdmision', $datos['solicitud']->getKey())->call('formalizarAdmision')->assertForbidden();

        $this->assertSinAdmision();
        $this->assertDatabaseHas('preadmisiones', ['cod_preadmision' => $datos['solicitud']->getKey(), 'estado' => 'APROBADA']);
    }

    public function test_accion_rechaza_cuenta_inactiva_aunque_tenga_permiso(): void
    {
        $datos = $this->escenario();
        $datos['usuario']->update(['estado' => 'INACTIVO']);
        try {
            $this->formalizar($datos);
            $this->fail('Una cuenta inactiva no debe formalizar admisiones.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertSinAdmision();
    }

    public function test_accion_rechaza_llamada_directa_sin_permiso(): void
    {
        $datos = $this->escenario();
        $datos['usuario']->revokePermissionTo('admisiones.formalizar');
        try {
            $this->formalizar($datos);
            $this->fail('El rol no sustituye el permiso de formalización.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertSinAdmision();
    }

    public function test_policy_contextual_puede_negar_formalizacion_con_permiso(): void
    {
        $datos = $this->escenario();
        Gate::policy(Preadmision::class, PreadmisionFormalizacionDenegadaPolicy::class);
        $this->actingAs($datos['usuario']);
        Livewire::test(PreadmisionesPanel::class)->call('abrirAdmision', $datos['solicitud']->getKey())->assertForbidden();
        try {
            $this->formalizar($datos);
            $this->fail('El permiso no sustituye la Policy contextual.');
        } catch (AuthorizationException $e) {
            $this->assertTrue($e->response()->denied());
        }
        $this->assertSinAdmision();
    }

    public function test_cama_y_habitacion_habilitadas_comparten_disponibilidad_y_persistencia(): void
    {
        foreach (FormalizarAdmision::ESTADOS_HABILITADOS as $indice => $estado) {
            $datos = $this->escenario((string) $indice, $estado);
            $this->assertSame(1, app(ConsultaOperativaService::class)->resumenAdmision()['camas_disponibles']);
            $residente = $this->formalizar($datos);
            $this->assertSame('ADMITIDO', $residente->estado);
            $this->assertDatabaseHas('admisiones', ['cod_residente' => $residente->getKey(), 'cod_preadmision' => $datos['solicitud']->getKey()]);
            $this->assertDatabaseHas('ocupaciones_cama', ['cod_residente' => $residente->getKey(), 'cod_cama' => $datos['cama']->getKey(), 'estado' => 'ACTIVA']);
            $this->assertDatabaseHas('historial_estados_residente', ['cod_residente' => $residente->getKey(), 'estado_nuevo' => 'ADMITIDO']);
            $this->assertDatabaseHas('consentimientos', ['cod_residente' => $residente->getKey(), 'estado' => 'VIGENTE']);
            $this->assertSame(0, app(ConsultaOperativaService::class)->resumenAdmision()['camas_disponibles']);
        }
    }

    public function test_habitacion_inhabilitada_no_ofrece_cama_ni_deja_persistencia_parcial(): void
    {
        $datos = $this->escenario('', 'DISPONIBLE');
        $datos['habitacion']->update(['estado' => 'INACTIVA']);
        $this->assertSame(0, app(ConsultaOperativaService::class)->resumenAdmision()['camas_disponibles']);
        $this->actingAs($datos['usuario']);
        Livewire::test(PreadmisionesPanel::class)->set('habitacion_id', $datos['habitacion']->getKey())
            ->assertViewHas('camasHabitacion', fn ($camas) => $camas->first()->estado !== 'DISPONIBLE');
        try {
            $this->formalizar($datos);
            $this->fail('Una habitación inhabilitada no permite admitir.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cod_cama', $e->errors());
        }
        $this->assertSinAdmision();
        $this->assertDatabaseCount('contactos', 1);
    }

    public function test_ocupacion_activo_impide_reutilizar_cama_incluso_con_fecha_liberacion(): void
    {
        $datos = $this->escenario();
        $this->formalizar($datos);
        DB::table('ocupaciones_cama')->update(['estado' => 'ACTIVO', 'fecha_hora_liberacion' => now()]);
        $segunda = $this->escenario('2');
        $segunda['cama'] = $datos['cama'];
        $this->assertSame(1, app(ConsultaOperativaService::class)->resumenAdmision()['camas_disponibles']);
        try {
            $this->formalizar($segunda);
            $this->fail('La ocupación activa debe impedir reutilizar una cama.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cod_cama', $e->errors());
        }
        $this->assertDatabaseCount('residentes', 1);
        $this->assertDatabaseCount('admisiones', 1);
        $this->assertDatabaseCount('ocupaciones_cama', 1);
        $this->assertDatabaseHas('preadmisiones', ['cod_preadmision' => $segunda['solicitud']->getKey(), 'estado' => 'APROBADA']);
    }

    public function test_reintento_de_admision_no_duplica_residente_ni_registros(): void
    {
        $datos = $this->escenario();
        $this->formalizar($datos);
        try {
            $this->formalizar($datos);
            $this->fail('Una solicitud ya admitida no puede formalizarse otra vez.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('solicitud', $e->errors());
        }
        foreach (['residentes', 'admisiones', 'ocupaciones_cama', 'residentes_contactos', 'historial_estados_residente', 'consentimientos'] as $tabla) {
            $this->assertDatabaseCount($tabla, 1);
        }
    }

    public function test_historial_conserva_ultima_cama_sin_duplicar_por_traslados(): void
    {
        $datos = $this->escenario();
        $residente = $this->formalizar($datos);
        $admision = Admision::query()->firstOrFail();
        OcupacionCama::query()->update(['estado' => 'FINALIZADA', 'fecha_hora_liberacion' => now()]);
        $otra = Cama::query()->create(['cod_cama' => 'CAM_OTRA', 'cod_habitacion' => $datos['habitacion']->getKey(), 'codigo' => 'C-OTRA', 'estado' => 'ACTIVA']);
        OcupacionCama::query()->create([
            'cod_ocupacion' => 'OCU_ULTIMA', 'cod_cama' => $otra->getKey(), 'cod_residente' => $residente->getKey(),
            'cod_admision' => $admision->getKey(), 'cod_usuario_registro' => $datos['usuario']->getKey(),
            'fecha_hora_asignacion' => now()->addMinute(), 'fecha_hora_liberacion' => now()->addMinutes(2), 'estado' => 'FINALIZADA',
        ]);
        $admision->update(['estado' => 'FINALIZADA']);

        $consulta = app(ConsultaOperativaService::class);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $filas = $consulta->consulta('admisiones', 'historial')['query']->get();
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertCount(1, $filas);
        $this->assertSame('C-OTRA', $filas->first()->cama);
        $this->assertSame($datos['solicitud']->getKey(), $filas->first()->cod_preadmision);
        $resumen = $consulta->resumenAdmision();
        $this->assertSame(1, $resumen['historial']);
        $this->assertSame(1, $resumen['total_admisiones']);
        $this->assertSame(0, $resumen['admitidos']);
    }

    public function test_reporte_pagina_filtra_fechas_documento_y_orden_estable(): void
    {
        $datos = $this->escenario();
        $fecha = now()->subDay()->startOfDay();
        $datos['solicitud']->update(['fecha_solicitud' => $fecha, 'numero_documento' => 'DOC-PRUEBA']);
        for ($indice = 1; $indice <= 11; $indice++) {
            $solicitud = $datos['solicitud']->replicate();
            $solicitud->cod_preadmision = 'PRE_'.str_pad((string) $indice, 2, '0', STR_PAD_LEFT);
            $solicitud->save();
        }
        $this->actingAs($datos['usuario']);
        $this->get(route('admin.administracion.admisiones', ['tab' => 'preparacion']))
            ->assertOk()->assertViewIs('pages.admin.administracion.admisiones')
            ->assertViewHas('porPagina', 10)->assertViewHas('vistaAdmisiones', 'tabla')
            ->assertViewHas('registros', fn ($registros) => $registros->total() === 12 && $registros->count() === 10 && $registros->first()->codigo === 'PRE_11');
        $this->get(route('admin.administracion.admisiones', [
            'tab' => 'preparacion', 'por_pagina' => 20, 'orden' => 'antiguas', 'vista' => 'lista',
            'search' => 'DOC-PRUEBA', 'desde' => $fecha->toDateString(), 'hasta' => $fecha->toDateString(),
        ]))->assertOk()->assertViewHas('registros', fn ($registros) => $registros->total() === 12 && $registros->count() === 12 && $registros->first()->codigo === 'PRE_');
        $this->get(route('admin.administracion.admisiones', ['tab' => 'preparacion', 'desde' => today()->toDateString()]))
            ->assertOk()->assertViewHas('registros', fn ($registros) => $registros->total() === 0);
        $this->assertSinAdmision();
    }

    public function test_reporte_filtra_fecha_de_admision_y_valida_los_controles(): void
    {
        $datos = $this->escenario();
        $residente = $this->formalizar($datos);
        $fecha = today()->subDays(2);
        Admision::query()->update(['fecha_hora_admision' => $fecha]);
        $this->actingAs($datos['usuario']);
        $this->get(route('admin.administracion.admisiones', [
            'tab' => 'admitidos', 'search' => 'H-', 'desde' => $fecha->toDateString(),
            'hasta' => $fecha->toDateString(), 'vista' => 'tarjetas', 'por_pagina' => 50,
        ]))->assertOk()->assertViewHas('porPagina', 50)->assertViewHas('vistaAdmisiones', 'tarjetas')
            ->assertViewHas('registros', fn ($registros) => $registros->total() === 1 && $registros->first()->cod_residente === $residente->getKey());
        $this->get(route('admin.administracion.admisiones', ['tab' => 'admitidos', 'desde' => today()->toDateString()]))
            ->assertOk()->assertViewHas('registros', fn ($registros) => $registros->total() === 0);
        $this->get(route('admin.administracion.admisiones', ['por_pagina' => 100, 'orden' => 'manipulado', 'vista' => 'extra']))
            ->assertSessionHasErrors(['por_pagina', 'orden', 'vista']);
        $this->get(route('admin.administracion.admisiones', ['desde' => today()->toDateString(), 'hasta' => $fecha->toDateString()]))
            ->assertSessionHasErrors(['hasta']);
        $this->assertDatabaseCount('admisiones', 1);
    }

    private function escenario(string $sufijo = '', string $estado = 'ACTIVA'): array
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');
        $usuario->givePermissionTo(['admisiones.formalizar', 'admisiones.ver_dashboard']);
        $contacto = Contacto::query()->create(['cod_contacto' => 'CTO_'.$sufijo, 'nombres' => 'Ana', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        $habitacion = Habitacion::query()->create(['cod_habitacion' => 'HAB_'.$sufijo, 'codigo' => 'H-'.$sufijo, 'capacidad' => 2, 'estado' => $estado]);
        $cama = Cama::query()->create(['cod_cama' => 'CAM_'.$sufijo, 'cod_habitacion' => $habitacion->getKey(), 'codigo' => 'C-'.$sufijo, 'estado' => $estado]);
        $solicitud = Preadmision::query()->create([
            'cod_preadmision' => 'PRE_'.$sufijo, 'cod_contacto' => $contacto->getKey(), 'cod_usuario_registro' => $usuario->getKey(),
            'nombres' => 'Rosa', 'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1945-05-05',
            'motivo_ingreso' => 'Acompañamiento', 'fecha_solicitud' => now(), 'estado' => 'APROBADA',
        ]);

        return compact('usuario', 'contacto', 'habitacion', 'cama', 'solicitud');
    }

    private function formalizar(array $datos): Residente
    {
        return app(FormalizarAdmision::class)->ejecutar($datos['solicitud'], ['cod_cama' => $datos['cama']->getKey()], $datos['usuario']);
    }

    private function assertSinAdmision(): void
    {
        foreach (['residentes', 'admisiones', 'ocupaciones_cama', 'residentes_contactos', 'historial_estados_residente', 'consentimientos'] as $tabla) {
            $this->assertDatabaseCount($tabla, 0);
        }
    }
}

class PreadmisionFormalizacionDenegadaPolicy
{
    public function formalizar(User $usuario, Preadmision $solicitud): bool
    {
        return false;
    }
}
