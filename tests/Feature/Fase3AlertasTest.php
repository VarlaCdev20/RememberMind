<?php

namespace Tests\Feature;

use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Backend\Modulos\Alertas\Servicios\DeteccionAlertasService;
use App\Frontend\Livewire\Compartido\Alertas\AlertasPanel;
use App\Frontend\Livewire\Compartido\Alertas\SaludAlertasPanel;
use App\Frontend\Livewire\Features\Alertas\HistorialAlerta;
use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\EventoAlerta;
use App\Models\Jornada;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Fase3AlertasTest extends TestCase
{
    use RefreshDatabase;

    private function contexto(bool $enfermeria = false): array
    {
        $this->travelTo(Carbon::parse('2026-10-06 10:00:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['nombres' => 'Profesional sintético']);
        $usuario->assignRole($enfermeria ? 'ENFERMEROS' : 'ADMINISTRADOR');
        $usuario->givePermissionTo('alertas.gestionar');
        $residente = Residente::factory()->create();
        $area = Area::create(['cod_area' => 'ARE_F3_ALT', 'nombre' => 'Enfermería sintética', 'estado' => 'ACTIVA']);
        if ($enfermeria) {
            $turno = Turno::create(['cod_turno' => 'TUR_F3_ALT', 'nombre' => 'Mañana', 'orden' => 1, 'hora_inicio' => '07:00:00', 'hora_cierre' => '15:00:00', 'estado' => 'ACTIVO']);
            $jornada = Jornada::create(['cod_jornada' => 'JOR_F3_ALT', 'cod_turno' => $turno->cod_turno, 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
            AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_F3_ALT', 'cod_personal' => $usuario->personal->cod_personal, 'cod_area' => $area->cod_area,
                'cod_jornada' => $jornada->cod_jornada, 'tipo_asignacion' => 'RESPONSABLE', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
            AsignacionResidenteJornada::create(['cod_residente' => $residente->cod_residente, 'cod_personal' => $usuario->personal->cod_personal,
                'cod_jornada' => $jornada->cod_jornada, 'nivel_supervision' => 'DIRECTA', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        }
        $this->actingAs($usuario);

        return [$usuario, $residente];
    }

    private function crearPanel(Residente $residente): void
    {
        Livewire::test(AlertasPanel::class)->call('abrirCrear')->set('codResidente', $residente->cod_residente)
            ->set('tipoAlerta', 'Seguimiento sintético')->set('motivo', 'Revisión pendiente del equipo.')->call('guardarAlerta')->assertHasNoErrors();
    }

    public function test_creacion_manual_tiene_evento_actor_fecha_y_estado(): void
    {
        [$usuario, $residente] = $this->contexto();
        $this->crearPanel($residente);
        $evento = Alerta::sole()->eventos()->sole();
        $this->assertSame($usuario->cod_usuario, $evento->cod_usuario);
        $this->assertSame('CREACION', $evento->tipo_evento);
        $this->assertNull($evento->estado_anterior);
        $this->assertSame('ABIERTA', $evento->estado_nuevo);
        $this->assertNotNull($evento->fecha_hora);
    }

    public function test_fallo_del_evento_inicial_no_deja_alerta_manual_parcial(): void
    {
        [, $residente] = $this->contexto();
        EventoAlerta::creating(fn () => throw new \RuntimeException('Fallo sintético de evento inicial'));
        try {
            $this->crearPanel($residente);
            $this->fail('La creación debe fallar si no se conserva el evento.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo sintético de evento inicial', $e->getMessage());
        }
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_servicio_crea_evento_y_estados_de_intervencion_sin_reescribirlo(): void
    {
        [$usuario, $residente] = $this->contexto(true);
        $servicio = app(AlertasService::class);
        $alerta = $servicio->crear($residente->cod_residente,
            ['origen' => 'MANUAL', 'tipo_alerta' => 'SEGUIMIENTO', 'nivel' => 'CRITICO', 'motivo' => 'Revisión pendiente del equipo.'], $usuario);
        $inicial = $alerta->eventos()->sole();
        $original = $inicial->getAttributes();
        $servicio->registrarIntervencion($alerta, 'Se revisó al residente.', $usuario);
        $this->assertDatabaseHas('eventos_alerta', ['cod_alerta' => $alerta->cod_alerta, 'tipo_evento' => 'INTERVENCION',
            'estado_anterior' => 'ABIERTA', 'estado_nuevo' => 'EN_ATENCION', 'cod_usuario' => $usuario->cod_usuario]);
        $servicio->registrarSeguimiento($alerta->fresh(), 'Se comunicó al equipo.', $usuario);
        $servicio->cerrar($alerta->fresh(), 'Seguimiento concluido.', $usuario);
        $this->assertSame($original, $inicial->fresh()->getAttributes());
        $this->assertSame(4, $alerta->eventos()->count());
    }

    public function test_detector_crea_evento_inicial_sin_duplicarlo_por_reintento(): void
    {
        [$usuario, $residente] = $this->contexto();
        Atencion::create(['cod_area' => 'ARE_F3_ALT', 'cod_residente' => $residente->cod_residente, 'cod_personal' => $usuario->personal->cod_personal,
            'fecha_hora' => now(), 'motivo' => 'incidente sintético', 'observacion' => 'Hecho registrado para seguimiento.', 'estado' => 'REALIZADA']);
        $detector = app(DeteccionAlertasService::class);
        $this->assertSame(1, $detector->detectar($residente->cod_residente));
        $this->assertSame(0, $detector->detectar($residente->cod_residente));
        $this->assertDatabaseCount('alertas', 1);
        $this->assertDatabaseCount('eventos_alerta', 1);
        $this->assertSame($usuario->cod_usuario, EventoAlerta::sole()->cod_usuario);
    }

    public function test_lectura_preventiva_no_persiste_alertas(): void
    {
        $this->contexto();
        Livewire::test(SaludAlertasPanel::class)->assertOk();
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_cuenta_inactiva_no_puede_crear_desde_panel(): void
    {
        [$usuario] = $this->contexto();
        $usuario->update(['estado' => 'INACTIVO']);
        Livewire::test(AlertasPanel::class)->assertForbidden();
        $this->assertDatabaseCount('alertas', 0);
    }

    public function test_detencion_general_de_enfermeria_no_muta_residentes_ajenos(): void
    {
        [$usuario, $propio] = $this->contexto(true);
        $ajeno = Residente::factory()->create();
        foreach ([$propio, $ajeno] as $residente) {
            Atencion::create(['cod_area' => 'ARE_F3_ALT', 'cod_residente' => $residente->cod_residente, 'cod_personal' => $usuario->personal->cod_personal,
                'fecha_hora' => now(), 'motivo' => 'incidente sintético', 'observacion' => 'Seguimiento sintético', 'estado' => 'REALIZADA']);
        }
        app(DeteccionAlertasService::class)->detectar();
        $this->assertDatabaseHas('alertas', ['cod_residente' => $propio->cod_residente]);
        $this->assertDatabaseMissing('alertas', ['cod_residente' => $ajeno->cod_residente]);
    }

    public function test_cambio_http_y_fallo_de_evento_conservan_historial_atomico(): void
    {
        [$usuario, $residente] = $this->contexto();
        $alerta = Alerta::create(['cod_residente' => $residente->cod_residente, 'tipo' => 'SEGUIMIENTO',
            'modulo' => 'MANUAL', 'prioridad' => 'CRITICO', 'estado' => 'ABIERTA']);
        $this->patchJson(route('admin.alertas.estado', $alerta), ['estado' => 'RECONOCIDA', 'descripcion' => 'Revisión administrativa registrada.'])
            ->assertOk();
        $inicial = $alerta->eventos()->sole();
        $this->assertSame($usuario->cod_usuario, $inicial->cod_usuario);
        $this->assertSame('ABIERTA', $inicial->estado_anterior);
        $this->assertSame('RECONOCIDA', $inicial->estado_nuevo);
        $original = $inicial->getAttributes();
        EventoAlerta::creating(fn () => throw new \RuntimeException('Fallo sintético de cambio'));
        try {
            app(AlertasService::class)->cambiarEstado($alerta->fresh(), 'CERRADA', 'Justificación de cierre.', $usuario);
            $this->fail('El cambio debe revertirse con su evento.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo sintético de cambio', $e->getMessage());
        }
        $this->assertSame('RECONOCIDA', $alerta->fresh()->estado);
        $this->assertSame($original, $inicial->fresh()->getAttributes());
        $this->assertSame(1, $alerta->eventos()->count());
    }

    public function test_lectura_http_y_timeline_no_exponen_residente_ajeno_a_enfermeria(): void
    {
        [, $propio] = $this->contexto(true);
        $ajeno = Residente::factory()->create();
        foreach ([$propio, $ajeno] as $residente) {
            $alerta = Alerta::create(['cod_residente' => $residente->cod_residente, 'tipo' => 'SEGUIMIENTO', 'estado' => 'ABIERTA']);
        }
        $this->getJson(route('admin.alertas.index'))->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.cod_residente', $propio->cod_residente);
        Livewire::test(HistorialAlerta::class, ['alertaId' => $alerta->cod_alerta])
            ->assertForbidden();
    }

    public function test_critica_admite_cierre_directo_con_resultado_segun_contrato_actual(): void
    {
        [$usuario, $residente] = $this->contexto();
        $alerta = Alerta::create(['cod_residente' => $residente->cod_residente, 'tipo' => 'SEGUIMIENTO', 'prioridad' => 'CRITICO', 'estado' => 'ABIERTA']);
        app(AlertasService::class)->cerrar($alerta, 'Resultado registrado del seguimiento.', $usuario, coordinacion: true);
        $evento = $alerta->eventos()->sole();
        $this->assertSame('ABIERTA', $evento->estado_anterior);
        $this->assertSame('CERRADA', $evento->estado_nuevo);
        $this->assertSame('CIERRE', $evento->tipo_evento);
    }
}
