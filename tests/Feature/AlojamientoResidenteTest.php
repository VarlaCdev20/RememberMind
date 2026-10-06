<?php

namespace Tests\Feature;

use App\Backend\Modulos\Admisiones\Acciones\CambiarAlojamientoResidente;
use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Frontend\Livewire\Admisiones\AlojamientoResidente;
use App\Models\Admision;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\Habitacion;
use App\Models\OcupacionCama;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\User;
use App\Policies\ResidentePolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlojamientoResidenteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::policy(Residente::class, ResidentePolicy::class);
        foreach (['admisiones.formalizar', 'ocupaciones_cama.gestionar', 'residentes.ver'] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }
        Role::findOrCreate('ADMINISTRADOR', 'web');
    }

    public function test_traslado_preserva_admision_historial_autoria_y_auditoria(): void
    {
        $datos = $this->escenario();
        $previa = $datos['residente']->ocupacionActiva;
        $nueva = $this->cambiar($datos);
        $this->assertDatabaseHas('ocupaciones_cama', ['cod_ocupacion' => $previa->getKey(), 'estado' => 'FINALIZADA', 'motivo_liberacion' => 'Cambio de habitación']);
        $this->assertDatabaseHas('ocupaciones_cama', ['cod_ocupacion' => $nueva->getKey(), 'cod_cama' => $datos['destino']->getKey(), 'cod_admision' => $previa->cod_admision, 'cod_usuario_registro' => $datos['usuario']->getKey(), 'estado' => 'ACTIVA']);
        $this->assertEquals($previa->fresh()->fecha_hora_liberacion, $nueva->fecha_hora_asignacion);
        $this->assertDatabaseCount('admisiones', 1);
        $this->assertDatabaseCount('residentes', 1);
        $this->assertDatabaseCount('historial_estados_residente', 1);
        $this->assertSame(1, OcupacionCama::whereIn('estado', ['ACTIVA', 'ACTIVO'])->count());
        $auditoria = Activity::where('subject_id', $nueva->getKey())->firstOrFail();
        $this->assertSame($previa->cod_cama, $auditoria->properties['cod_cama_anterior']);
        $this->assertSame($nueva->cod_cama, $auditoria->properties['cod_cama_nueva']);
    }

    public function test_cama_ocupada_o_fuera_de_servicio_no_finaliza_la_ocupacion_actual(): void
    {
        $datos = $this->escenario();
        foreach (['MANTENIMIENTO', 'BLOQUEADA'] as $estado) {
            $datos['destino']->update(['estado' => $estado]);
            $this->rechazoSinCambios($datos, 'cod_cama');
        }
        $datos['destino']->update(['estado' => 'ACTIVA']);
        $datos['destino']->habitacion->update(['estado' => 'BLOQUEADA']);
        $this->rechazoSinCambios($datos, 'cod_cama');
        $datos['destino']->habitacion->update(['estado' => 'ACTIVA']);
        $otros = $this->escenario('OTRO');
        $otros['residente']->ocupacionActiva->update(['cod_cama' => $datos['destino']->getKey()]);
        $this->actingAs($datos['usuario']);
        $this->rechazoSinCambios($datos, 'cod_cama', 2);
    }

    public function test_misma_cama_fecha_incoherente_y_estado_inactivo_se_rechazan(): void
    {
        $datos = $this->escenario();
        $this->rechazoSinCambios($datos, 'cod_cama', 1, ['cod_cama' => $datos['origen']->getKey()]);
        $this->rechazoSinCambios($datos, 'fecha_hora', 1, ['fecha_hora' => now()->subDays(2)->toDateTimeString()]);
        $datos['residente']->update(['estado' => 'INACTIVO']);
        $this->rechazoSinCambios($datos, 'residente');
    }

    public function test_la_primera_cama_no_se_asigna_fuera_de_admision_y_se_rechaza_admision_cerrada(): void
    {
        $datos = $this->escenario();
        Admision::query()->update(['estado' => 'FINALIZADA']);
        $this->rechazoSinCambios($datos, 'residente');
        Admision::query()->update(['estado' => 'ACTIVA']);
        OcupacionCama::query()->delete();
        $this->rechazoSinCambios($datos, 'residente', 0, ['cod_ocupacion_anterior' => null]);
    }

    public function test_asignacion_con_admision_vigente_y_ocupacion_historica(): void
    {
        $datos = $this->escenario();
        $datos['residente']->ocupacionActiva->update(['estado' => 'FINALIZADA', 'fecha_hora_liberacion' => now()->subHour()]);
        $nueva = $this->cambiar($datos, ['cod_ocupacion_anterior' => null]);
        $this->assertSame($datos['destino']->getKey(), $nueva->cod_cama);
        $this->assertDatabaseCount('ocupaciones_cama', 2);
        $this->assertDatabaseCount('admisiones', 1);
    }

    public function test_reintento_con_formulario_anterior_no_genera_otra_ocupacion(): void
    {
        $datos = $this->escenario();
        $this->cambiar($datos);
        try {
            $this->cambiar($datos);
            $this->fail('El estado previo del formulario debe revalidarse.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('cod_cama', $exception->errors());
        }
        $this->assertDatabaseCount('ocupaciones_cama', 2);
        $this->assertSame(1, OcupacionCama::where('estado', 'ACTIVA')->count());
    }

    public function test_permiso_y_cuenta_activa_se_verifican_tambien_en_livewire(): void
    {
        $datos = $this->escenario();
        $datos['usuario']->revokePermissionTo('ocupaciones_cama.gestionar');
        Livewire::test(AlojamientoResidente::class)->call('abrir', $datos['residente']->getKey())->assertForbidden();
        $datos['usuario']->givePermissionTo('ocupaciones_cama.gestionar');
        $componente = Livewire::test(AlojamientoResidente::class)->call('abrir', $datos['residente']->getKey());
        $datos['usuario']->update(['estado' => 'INACTIVO']);
        $componente->call('guardar')->assertForbidden();
        $this->assertDatabaseCount('ocupaciones_cama', 1);
    }

    public function test_policy_contextual_no_se_omite_con_permiso(): void
    {
        $datos = $this->escenario();
        Gate::policy(Residente::class, AlojamientoDenegadoPolicy::class);
        try {
            $this->cambiar($datos);
            $this->fail('La Policy debe autorizar el contexto.');
        } catch (AuthorizationException) {
            $this->assertDatabaseCount('ocupaciones_cama', 1);
        }
    }

    public function test_livewire_presenta_destino_guarda_y_emite_actualizacion(): void
    {
        $datos = $this->escenario();
        Livewire::test(AlojamientoResidente::class)
            ->dispatch('abrir-alojamiento-residente', codResidente: $datos['residente']->getKey(), codCama: $datos['destino']->getKey())
            ->assertSet('modalAbierto', true)->assertSee('La admisión se conserva.')
            ->set('hora', now()->format('H:i'))
            ->call('guardar')->assertHasNoErrors()->assertSet('modalAbierto', false)
            ->assertDispatched('alojamiento-residente-actualizado', codResidente: $datos['residente']->getKey());
        $this->assertDatabaseCount('ocupaciones_cama', 2);
    }

    public function test_familiar_con_permiso_operativo_no_puede_trasladar_un_residente(): void
    {
        $datos = $this->escenario();
        Role::findOrCreate('FAMILIAR', 'web');
        $datos['usuario']->syncRoles(['FAMILIAR']);
        Livewire::test(AlojamientoResidente::class)->call('abrir', $datos['residente']->getKey())->assertForbidden();
        $this->assertDatabaseCount('ocupaciones_cama', 1);
    }

    private function escenario(string $sufijo = ''): array
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');
        $usuario->givePermissionTo(['admisiones.formalizar', 'ocupaciones_cama.gestionar', 'residentes.ver']);
        $this->actingAs($usuario);
        $contacto = Contacto::create(['cod_contacto' => 'CTO_'.$sufijo, 'nombres' => 'Ana', 'apellido_paterno' => 'Sintética', 'estado' => 'ACTIVO']);
        $habitacion = Habitacion::create(['cod_habitacion' => 'HAB_'.$sufijo, 'codigo' => 'H-'.$sufijo, 'piso' => '1', 'capacidad' => 2, 'estado' => 'ACTIVA']);
        $origen = Cama::create(['cod_cama' => 'ORI_'.$sufijo, 'codigo' => 'OR-'.$sufijo, 'cod_habitacion' => $habitacion->getKey(), 'estado' => 'ACTIVA']);
        $destino = Cama::create(['cod_cama' => 'DES_'.$sufijo, 'codigo' => 'DE-'.$sufijo, 'cod_habitacion' => $habitacion->getKey(), 'estado' => 'ACTIVA']);
        $solicitud = Preadmision::create(['cod_preadmision' => 'PRE_'.$sufijo, 'cod_contacto' => $contacto->getKey(), 'cod_usuario_registro' => $usuario->getKey(), 'nombres' => 'Rosa', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1945-05-05', 'motivo_ingreso' => 'Acompañamiento', 'fecha_solicitud' => now()->subDay(), 'estado' => 'APROBADA']);
        $residente = app(FormalizarAdmision::class)->ejecutar($solicitud, ['cod_cama' => $origen->getKey(), 'fecha_hora_admision' => now()->subDay()], $usuario);

        return compact('usuario', 'residente', 'origen', 'destino');
    }

    private function cambiar(array $datos, array $cambios = []): OcupacionCama
    {
        return app(CambiarAlojamientoResidente::class)->ejecutar($datos['residente'], array_replace([
            'cod_cama' => $datos['destino']->getKey(), 'cod_ocupacion_anterior' => $datos['residente']->ocupacionActiva?->getKey(),
            'fecha_hora' => now()->toDateTimeString(), 'motivo' => 'Cambio de habitación',
        ], $cambios), $datos['usuario']);
    }

    private function rechazoSinCambios(array $datos, string $campo, int $total = 1, array $cambios = []): void
    {
        try {
            $this->cambiar($datos, $cambios);
            $this->fail('El alojamiento no debía cambiar.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($campo, $exception->errors());
        }
        $this->assertDatabaseCount('ocupaciones_cama', $total);
        if ($total) {
            $this->assertDatabaseHas('ocupaciones_cama', ['cod_ocupacion' => $datos['residente']->ocupacionActiva->getKey(), 'estado' => 'ACTIVA', 'fecha_hora_liberacion' => null]);
        }
    }
}

class AlojamientoDenegadoPolicy
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
