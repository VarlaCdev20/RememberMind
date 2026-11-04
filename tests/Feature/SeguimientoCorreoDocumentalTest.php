<?php

namespace Tests\Feature;

use App\Mail\Usuarios\PaqueteDocumentalUsuarioMail;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Servicios\EnviosCorreoDocumental;
use App\Modulos\Identidad\Infraestructura\Jobs\EnviarFichaUsuarioJob;
use App\Modulos\Identidad\Infraestructura\Notificaciones\PrepararCorreoDocumental;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class SeguimientoCorreoDocumentalTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private User $usuario;

    private EnviosCorreoDocumental $envios;

    protected function setUp(): void
    {
        parent::setUp();
        config(['queue.default' => 'database']);
        Queue::fake();
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO', 'correo' => 'destino@example.test']);
        foreach (['usuarios.ver', 'usuarios.editar'] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
            $this->actor->givePermissionTo($permiso);
        }
        $this->actingAs($this->actor);
        $this->envios = app(EnviosCorreoDocumental::class);
    }

    private function solicitar(string $tipo = 'ficha'): EnviarFichaUsuarioJob
    {
        $estado = $this->envios->solicitar($this->actor, $this->usuario, $tipo);
        $this->assertSame('PENDIENTE', $estado['estado']);

        return Queue::pushed(EnviarFichaUsuarioJob::class)->last();
    }

    private function preparador(): PrepararCorreoDocumental
    {
        $mock = Mockery::mock(PrepararCorreoDocumental::class);
        $mock->shouldReceive('preparar')->andReturn((new Mailable)->html('Contenido documental sintético.'));

        return $mock;
    }

    public function test_http_registra_y_recupera_solicitud_sin_afirmar_entrega_ni_duplicar(): void
    {
        $url = route('admin.usuarios.ficha.enviar-correo', $this->usuario);
        $primero = $this->postJson($url)->assertStatus(202)->assertJsonPath('estado', 'PENDIENTE');
        $segundo = $this->postJson($url)->assertStatus(202)->assertJsonPath('duplicado', true);
        $this->assertSame($primero->json('solicitud'), $segundo->json('solicitud'));
        Queue::assertPushed(EnviarFichaUsuarioJob::class, 1);
        $this->getJson($primero->json('estado_url'))->assertOk()->assertJsonPath('terminado', false)->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_paquete_se_encola_una_vez_sin_pdf_en_el_payload(): void
    {
        $job = $this->solicitar('paquete');
        $this->assertSame('paquete', $job->tipo);
        $this->assertStringNotContainsString('%PDF', serialize($job));
        $this->assertNotInstanceOf(ShouldQueue::class,
            new PaqueteDocumentalUsuarioMail($this->usuario, 'pdf-sintetico', 'prueba.pdf'));
        $this->postJson(route('admin.usuarios.documentos.enviar', $this->usuario))->assertStatus(202)->assertJsonPath('duplicado', true);
        Queue::assertPushed(EnviarFichaUsuarioJob::class, 1);
    }

    public function test_aceptacion_guarda_historia_y_repetir_job_no_reenvia(): void
    {
        $job = $this->solicitar();
        $preparador = $this->preparador();
        $job->handle($this->envios, $preparador);
        $this->assertSame('ACEPTADO', $this->envios->estado($this->actor, $this->usuario, $job->solicitud)['estado']);
        $job->handle($this->envios, $preparador);
        $this->assertCount(1, app('mailer')->getSymfonyTransport()->messages());
        $this->assertSame(['PENDIENTE', 'PREPARANDO', 'ENVIANDO', 'ACEPTADO'], Activity::where('log_name', 'Correo')->orderBy('id')->get()->map(fn ($a) => $a->properties['estado'])->all());
        $this->assertStringNotContainsString($this->usuario->correo, Activity::where('log_name', 'Correo')->get()->toJson());
        $this->assertStringNotContainsString('Contenido documental sintético', Activity::where('log_name', 'Correo')->get()->toJson());
    }

    public function test_rechazo_temporal_programa_reintento_y_agota_tres_intentos_sin_falso_exito(): void
    {
        $job = $this->solicitar()->withFakeQueueInteractions();
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new TransportException('Detalle privado: clave-no-publicable', 451));
        foreach ([1 => 30, 2 => 120] as $intento => $demora) {
            $job->job->attempts = $intento;
            try {
                $job->handle($this->envios, $this->preparador());
                $this->fail('Debió solicitar un reintento al trabajador.');
            } catch (\RuntimeException $e) {
                $this->assertStringNotContainsString('clave-no-publicable', $e->getMessage());
            }
            $estado = $this->envios->estado($this->actor, $this->usuario, $job->solicitud);
            $this->assertSame('REINTENTO', $estado['estado']);
            $this->assertSame(now()->addSeconds($demora)->toIso8601String(), $estado['proximo_intento']);
        }
        $job->job->attempts = 3;
        $job->handle($this->envios, $this->preparador());
        $job->assertFailed();
        $this->assertSame('FALLIDO', $this->envios->estado($this->actor, $this->usuario, $job->solicitud)['estado']);
        $this->assertSame(0, Activity::where('properties->estado', 'ACEPTADO')->count());
    }

    public function test_timeout_despues_de_iniciar_envio_no_reintenta_y_no_afirma_fallo_definitivo(): void
    {
        $job = $this->solicitar()->withFakeQueueInteractions();
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new TransportException('Connection timed out'));
        $job->handle($this->envios, $this->preparador());
        $job->assertFailed();
        $this->assertSame('INDETERMINADO', $this->envios->estado($this->actor, $this->usuario, $job->solicitud)['estado']);
        $job->handle($this->envios, $this->preparador());
    }

    public function test_rechazo_permanente_no_se_reintenta(): void
    {
        $job = $this->solicitar()->withFakeQueueInteractions();
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new TransportException('Authentication rejected', 535));
        $job->handle($this->envios, $this->preparador());
        $job->assertFailed();
        $this->assertSame('FALLIDO', $this->envios->estado($this->actor, $this->usuario, $job->solicitud)['estado']);
    }

    public function test_el_worker_reautoriza_al_actor_y_detecta_cambio_del_destinatario(): void
    {
        $job = $this->solicitar()->withFakeQueueInteractions();
        $this->usuario->update(['correo' => 'otra-cuenta@example.test']);
        $preparador = Mockery::mock(PrepararCorreoDocumental::class);
        $preparador->shouldNotReceive('preparar');
        $job->handle($this->envios, $preparador);
        $job->assertFailed();
        $this->assertSame('FALLIDO', $this->envios->estado($this->actor, $this->usuario, $job->solicitud)['estado']);

        $job = $this->solicitar()->withFakeQueueInteractions();
        $this->actor->revokePermissionTo('usuarios.ver');
        $job->handle($this->envios, $preparador);
        $job->assertFailed();
        $this->assertSame('FALLIDO', $this->envios->estadoInterno($this->actor, $this->usuario, $job->solicitud)['estado']);
    }

    public function test_estado_de_otro_actor_otro_usuario_y_permiso_revocado_no_se_exponen(): void
    {
        $estado = $this->envios->solicitar($this->actor, $this->usuario, 'ficha');
        $url = route('admin.usuarios.correo.estado', [$this->usuario, $estado['solicitud']]);
        $otro = User::factory()->create(['estado' => 'ACTIVO']);
        $otro->givePermissionTo('usuarios.ver');
        $this->actingAs($otro)->getJson($url)->assertNotFound();
        $this->actingAs($this->actor)->getJson(route('admin.usuarios.correo.estado', [$otro, $estado['solicitud']]))->assertNotFound();
        $this->actor->revokePermissionTo('usuarios.ver');
        $this->getJson($url)->assertForbidden();
        $this->postJson(route('admin.usuarios.ficha.enviar-correo', $this->usuario))->assertForbidden();
    }

    public function test_trabajo_interrumpido_en_fase_smtp_no_vuelve_a_enviar(): void
    {
        $job = $this->solicitar();
        $this->envios->registrar($this->actor, $this->usuario, $job->solicitud, 'ficha', 'ENVIANDO', 1);
        $preparador = Mockery::mock(PrepararCorreoDocumental::class);
        $preparador->shouldNotReceive('preparar');
        $job->handle($this->envios, $preparador);
        $this->assertSame('INDETERMINADO', $this->envios->estado($this->actor, $this->usuario, $job->solicitud)['estado']);
    }

    public function test_cierre_del_worker_no_sobrescribe_aceptacion_confirmada(): void
    {
        $job = $this->solicitar();
        $job->handle($this->envios, $this->preparador());
        $job->failed(new \RuntimeException('Fallo posterior'));
        $this->assertSame('ACEPTADO', $this->envios->estado($this->actor, $this->usuario, $job->solicitud)['estado']);
    }

    public function test_los_dos_documentos_se_generan_y_se_adjuntan_en_memoria(): void
    {
        $preparador = app(PrepararCorreoDocumental::class);
        foreach (['ficha', 'paquete'] as $tipo) {
            $correo = $preparador->preparar($this->usuario, $this->actor, $tipo);
            $resultado = Mail::to($this->usuario->correo)->send($correo);
            $this->assertNotNull($resultado);
            $adjuntos = $resultado->getSymfonySentMessage()->getOriginalMessage()->getAttachments();
            $this->assertCount(1, $adjuntos);
            $this->assertStringStartsWith('%PDF', $adjuntos[0]->getBody());
        }
    }

    public function test_fallo_preparando_y_transporte_sin_confirmacion_no_se_publican_como_aceptados(): void
    {
        $job = $this->solicitar()->withFakeQueueInteractions();
        $preparador = Mockery::mock(PrepararCorreoDocumental::class);
        $preparador->shouldReceive('preparar')->once()->andThrow(new \RuntimeException('Fallo de PDF privado'));
        $job->handle($this->envios, $preparador);
        $job->assertFailed();
        $this->assertSame('FALLIDO', $this->envios->estado($this->actor, $this->usuario, $job->solicitud)['estado']);
        $job = $this->solicitar()->withFakeQueueInteractions();
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andReturnNull();
        $job->handle($this->envios, $this->preparador());
        $this->assertSame('INDETERMINADO', $this->envios->estado($this->actor, $this->usuario, $job->solicitud)['estado']);
        $this->assertSame(0, Activity::where('properties->estado', 'ACEPTADO')->count());
    }

    public function test_no_registra_solicitud_con_cola_sin_reintentos(): void
    {
        config(['queue.default' => 'sync']);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('cola persistente');
        try {
            $this->envios->solicitar($this->actor, $this->usuario, 'ficha');
        } finally {
            $this->assertSame(0, Activity::where('log_name', 'Correo')->count());
            Queue::assertNothingPushed();
        }
    }
}
