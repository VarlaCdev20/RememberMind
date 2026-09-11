<?php

namespace Tests\Feature;

use App\Livewire\Alertas\CampanaNotificaciones;
use App\Models\{AdultoMayor, AlertaAdulto, AccionAlerta, AsignacionTurnoAdulto, TurnoEnfermeria, User};
use App\Services\Alertas\DeteccionAlertasService;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AlertasNotificacionesTest extends TestCase
{
    use RefreshDatabase;

    private function preparar(array $permisos): array
    {
        $this->seed(EstadoAdultoSeeder::class);
        $user = User::factory()->create();
        foreach ($permisos as $p) {
            $user->givePermissionTo(Permission::findOrCreate($p, 'web'));
        }
        $this->actingAs($user);

        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        return [$user, $adulto];
    }

    public function test_creacion_de_alerta_preventiva_desde_servicio(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver']);

        // El adulto activo recién creado no tiene ficha médica ni valoración funcional
        $creadas = app(DeteccionAlertasService::class)->detectarPreventivas($adulto->cod_am);

        $this->assertGreaterThan(0, $creadas);
        $this->assertDatabaseHas('alertas_adulto', [
            'cod_am' => $adulto->cod_am,
            'origen' => 'FICHA',
            'tipo_alerta' => 'FICHA MEDICA',
            'estado' => 'ABIERTA',
        ]);
        $this->assertDatabaseHas('alertas_adulto', [
            'cod_am' => $adulto->cod_am,
            'origen' => 'VALORACION',
            'tipo_alerta' => 'VALORACION FALTANTE',
            'estado' => 'ABIERTA',
        ]);
    }

    public function test_no_duplicacion_de_alerta_preventiva(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver']);

        // Primera detección
        app(DeteccionAlertasService::class)->detectarPreventivas($adulto->cod_am);
        $conteoInicial = AlertaAdulto::where('cod_am', $adulto->cod_am)->where('origen', 'FICHA')->count();
        $this->assertSame(1, $conteoInicial);

        // Múltiples ejecuciones sucesivas (simulando polling/render frecuente)
        app(DeteccionAlertasService::class)->detectarPreventivas($adulto->cod_am);
        app(DeteccionAlertasService::class)->detectarPreventivas($adulto->cod_am);
        $this->assertSame(1, AlertaAdulto::where('cod_am', $adulto->cod_am)->where('origen', 'FICHA')->count());

        // Cambiar la alerta a EN_ATENCION y volver a detectar: tampoco debe duplicarse
        $alerta = AlertaAdulto::where('cod_am', $adulto->cod_am)->where('origen', 'FICHA')->first();
        $alerta->update(['estado' => 'EN_ATENCION']);

        app(DeteccionAlertasService::class)->detectarPreventivas($adulto->cod_am);
        $this->assertSame(1, AlertaAdulto::where('cod_am', $adulto->cod_am)->where('origen', 'FICHA')->count());
    }

    public function test_contador_de_alertas_abiertas_en_campana(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver']);

        // Crear una alerta ABIERTA, una EN_ATENCION y una CERRADA
        AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'REVISION 1',
            'nivel' => 'MEDIO',
            'motivo' => 'Alerta abierta de prueba',
            'estado' => 'ABIERTA',
        ]);

        AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'REVISION 2',
            'nivel' => 'ALTO',
            'motivo' => 'Alerta en atención de prueba',
            'estado' => 'EN_ATENCION',
        ]);

        AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'REVISION 3',
            'nivel' => 'BAJO',
            'motivo' => 'Alerta cerrada de prueba',
            'estado' => 'CERRADA',
        ]);

        Livewire::test(CampanaNotificaciones::class)
            ->assertSet('conteoAbiertas', 2)
            ->assertSee('2 pendientes')
            ->assertSee('REVISION 1')
            ->assertSee('REVISION 2')
            ->assertDontSee('REVISION 3');
    }

    public function test_enfermero_solo_ve_y_gestiona_alertas_de_pacientes_asignados(): void
    {
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        $this->actingAs($enfermero);

        $asignado = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001', 'nombres' => 'Paciente Asignado']);
        $ajeno = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001', 'nombres' => 'Paciente Ajeno']);
        $turno = TurnoEnfermeria::create([
            'nombre' => 'Turno activo', 'hora_inicio' => '00:00:00', 'hora_fin' => '23:59:59',
            'orden' => 1, 'estado' => 'ACTIVO',
        ]);
        AsignacionTurnoAdulto::create([
            'cod_am' => $asignado->cod_am, 'cod_turno' => $turno->cod_turno,
            'cod_usu_enfermero' => $enfermero->cod_usu, 'fecha_inicio' => today(),
            'estado' => 'ACTIVA', 'asignado_por' => $enfermero->cod_usu,
        ]);

        $alertaAsignada = AlertaAdulto::create([
            'cod_am' => $asignado->cod_am, 'origen' => 'MANUAL', 'tipo_alerta' => 'ALERTA ASIGNADA',
            'nivel' => 'ALTO', 'motivo' => 'Visible para enfermería', 'estado' => 'ABIERTA',
        ]);
        $alertaAjena = AlertaAdulto::create([
            'cod_am' => $ajeno->cod_am, 'origen' => 'MANUAL', 'tipo_alerta' => 'ALERTA AJENA',
            'nivel' => 'ALTO', 'motivo' => 'No debe ser visible', 'estado' => 'ABIERTA',
        ]);

        Livewire::test(CampanaNotificaciones::class)
            ->assertSet('conteoAbiertas', 1)
            ->assertSee('ALERTA ASIGNADA')
            ->assertDontSee('ALERTA AJENA')
            ->call('atenderAlerta', $alertaAsignada->cod_alerta, 'Atención del paciente asignado')
            ->assertDispatched('alerta-atendida');

        Livewire::test(CampanaNotificaciones::class)
            ->call('atenderAlerta', $alertaAjena->cod_alerta, 'Intento fuera de alcance')
            ->assertForbidden();
        $this->assertSame('ABIERTA', $alertaAjena->fresh()->estado);
    }

    public function test_atender_alerta_y_registro_de_accion_alerta(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver', 'alertas.atender']);

        $alerta = AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'SIGNOS VITALES',
            'nivel' => 'CRITICO',
            'motivo' => 'Presión elevada detectada en control',
            'estado' => 'ABIERTA',
        ]);

        Livewire::test(CampanaNotificaciones::class)
            ->call('atenderAlerta', $alerta->cod_alerta, 'Se administra tratamiento indicado por médico de guardia.')
            ->assertDispatched('alerta-atendida');

        $alertaFresca = $alerta->fresh();
        $this->assertSame('EN_ATENCION', $alertaFresca->estado);
        $this->assertSame($user->cod_usu, $alertaFresca->atendido_por);
        $this->assertNotNull($alertaFresca->fecha_atencion);

        $this->assertDatabaseHas('acciones_alerta', [
            'cod_alerta' => $alerta->cod_alerta,
            'accion' => 'Se administra tratamiento indicado por médico de guardia.',
            'responsable_id' => $user->cod_usu,
            'estado' => 'REALIZADA',
        ]);
    }

    public function test_cerrar_alerta_registro_accion_y_actualizacion_contador(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver', 'alertas.cerrar']);

        $alerta = AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'VALORACION INICIAL',
            'nivel' => 'MEDIO',
            'motivo' => 'Falta registrar valoración',
            'estado' => 'ABIERTA',
        ]);

        $component = Livewire::test(CampanaNotificaciones::class);
        $this->assertSame(1, $component->get('conteoAbiertas'));

        $component->call('cerrarAlerta', $alerta->cod_alerta, 'Valoración completada y archivada.')
            ->assertDispatched('alerta-cerrada');

        $alertaFresca = $alerta->fresh();
        $this->assertSame('CERRADA', $alertaFresca->estado);
        $this->assertSame($user->cod_usu, $alertaFresca->cerrado_por);
        $this->assertNotNull($alertaFresca->fecha_cierre);

        $this->assertDatabaseHas('acciones_alerta', [
            'cod_alerta' => $alerta->cod_alerta,
            'accion' => 'Cierre: Valoración completada y archivada.',
            'responsable_id' => $user->cod_usu,
            'estado' => 'REALIZADA',
        ]);

        // Contador de pendientes disminuyó a 0
        $this->assertSame(0, $component->get('conteoAbiertas'));
    }

    public function test_usuario_sin_permiso_no_puede_gestionar_alertas(): void
    {
        // Usuario únicamente con permiso de visualización
        [$user, $adulto] = $this->preparar(['alertas.ver']);

        $alerta = AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'PRUEBA SEGURIDAD',
            'nivel' => 'MEDIO',
            'motivo' => 'Prueba de restricción de permisos',
            'estado' => 'ABIERTA',
        ]);

        // Intento de atender sin permiso
        Livewire::test(CampanaNotificaciones::class)
            ->call('atenderAlerta', $alerta->cod_alerta, 'Intento de atención no autorizada')
            ->assertForbidden();

        // Intento de cerrar sin permiso
        Livewire::test(CampanaNotificaciones::class)
            ->call('cerrarAlerta', $alerta->cod_alerta, 'Intento de cierre no autorizado')
            ->assertForbidden();

        // El estado se mantiene inalterado
        $this->assertSame('ABIERTA', $alerta->fresh()->estado);
        $this->assertSame(0, AccionAlerta::where('cod_alerta', $alerta->cod_alerta)->count());
    }
public function test_polling_detecta_nuevas_alertas_y_dispara_evento_toast_sin_repetir(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver']);

        // Alerta existente inicial
        AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'INICIAL',
            'nivel' => 'BAJO',
            'motivo' => 'Alerta previa existente',
            'estado' => 'ABIERTA',
        ]);

        $component = Livewire::test(CampanaNotificaciones::class);
        $component->assertSet('conteoAbiertas', 1);

        // Se genera una nueva alerta mientras el usuario está en la página
        $nuevaAlerta = AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'NUEVA URGENCIA',
            'nivel' => 'CRITICO',
            'motivo' => 'Caída en pasillo principal',
            'estado' => 'ABIERTA',
        ]);

        // Ciclo de polling
        $component->call('verificarAlertas')
            ->assertSet('conteoAbiertas', 2)
            ->assertDispatched('alerta-nueva', function ($event, $params) {
                $payload = isset($params['titulo']) ? $params : ($params[0] ?? []);
                return isset($payload['titulo']) && str_contains($payload['titulo'], 'CRITICO');
            });

        // Siguiente ciclo de polling sin alertas nuevas: NO debe volver a disparar alerta-nueva
        $component->call('verificarAlertas')
            ->assertNotDispatched('alerta-nueva');
    }
public function test_campana_se_renderiza_en_navbar_para_usuario_autenticado(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver']);

        AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'PRUEBA NAVBAR',
            'nivel' => 'ALTO',
            'motivo' => 'Motivo visible en campana',
            'estado' => 'ABIERTA',
        ]);

        $response = $this->get(route('admin.enfermeria.alertas'));
        $response->assertOk();
        $response->assertSee('Notificaciones y Alertas');
        $response->assertSee('PRUEBA NAVBAR');
    }

    public function test_botones_abren_drawers_laterales_de_graficos_y_ubicacion_sin_redirigir(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver']);

        AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'PRUEBA DRAWER',
            'nivel' => 'CRITICO',
            'motivo' => 'Monitoreo de curvas y ubicación',
            'estado' => 'ABIERTA',
        ]);

        Livewire::test(CampanaNotificaciones::class)
            ->assertSet('drawerGrafico', false)
            ->assertSet('drawerUbicacion', false)
            ->call('verGraficos', $adulto->cod_am)
            ->assertSet('drawerGrafico', true)
            ->assertSet('drawerUbicacion', false)
            ->assertSee('Gráficos de Evolución Clínica')
            ->call('verUbicacion', $adulto->cod_am)
            ->assertSet('drawerUbicacion', true)
            ->assertSet('drawerGrafico', false)
            ->assertSee('Ubicación y Ficha del Residente')
            ->call('cerrarDrawer')
            ->assertSet('drawerGrafico', false)
            ->assertSet('drawerUbicacion', false);
    }
}
