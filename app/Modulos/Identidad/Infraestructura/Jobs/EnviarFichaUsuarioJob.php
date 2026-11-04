<?php

namespace App\Modulos\Identidad\Infraestructura\Jobs;

use App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Servicios\EnviosCorreoDocumental;
use App\Modulos\Identidad\Infraestructura\Notificaciones\PrepararCorreoDocumental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/** Conserva el nombre del job para poder leer solicitudes antiguas ya serializadas. */
class EnviarFichaUsuarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public bool $failOnTimeout = true;

    protected User $usuario;

    protected User $admin;

    public ?string $solicitud = null;

    public string $tipo = 'ficha';

    public ?string $correoHuella = null;

    public function __construct(User $usuario, User $admin, ?string $solicitud = null, string $tipo = 'ficha')
    {
        $this->usuario = $usuario;
        $this->admin = $admin;
        $this->solicitud = $solicitud ?? (string) Str::uuid();
        $this->tipo = $tipo;
        $this->correoHuella = hash('sha256', (string) $usuario->correo);
    }

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(EnviosCorreoDocumental $envios, PrepararCorreoDocumental $preparador): void
    {
        $this->solicitud ??= (string) Str::uuid();
        $lock = Cache::lock('correo:procesar:'.$this->solicitud, 75);
        if (! $lock->get()) {
            $this->release(10);

            return;
        }
        try {
            // Un job anterior al seguimiento no tenía solicitud. Se incorpora sin alterar su actor.
            try {
                $actual = $envios->estadoInterno($this->admin, $this->usuario, $this->solicitud);
            } catch (ModelNotFoundException $e) {
                $envios->registrar($this->admin, $this->usuario, $this->solicitud, $this->tipo, 'PENDIENTE');
                $actual = ['estado' => 'PENDIENTE', 'terminado' => false];
            }
            if ($actual['terminado']) {
                return;
            }
            if ($actual['estado'] === 'ENVIANDO') {
                $envios->registrar($this->admin, $this->usuario, $this->solicitud, $this->tipo, 'INDETERMINADO');

                return;
            }
            $intento = $this->job?->attempts() ?? 1;
            $fase = 'PREPARANDO';
            try {
                $this->admin->refresh();
                $this->usuario->refresh();
                $this->admin->unsetRelation('roles')->unsetRelation('permissions');
                $envios->autorizar($this->admin, $this->tipo);
                if (! filter_var($this->usuario->correo, FILTER_VALIDATE_EMAIL)
                    || ($this->correoHuella !== null && ! hash_equals($this->correoHuella, hash('sha256', $this->usuario->correo)))) {
                    throw new \RuntimeException('El destinatario cambió o dejó de ser válido.');
                }
                app(ExigirTransporteCorreoReal::class)->verificar();
                $envios->registrar($this->admin, $this->usuario, $this->solicitud, $this->tipo, $fase, $intento);
                $correo = $preparador->preparar($this->usuario, $this->admin, $this->tipo);
                // La generación del adjunto puede tardar: comprobar otra vez antes de transmitirlo.
                $this->admin->refresh()->unsetRelation('roles')->unsetRelation('permissions');
                $this->usuario->refresh();
                $envios->autorizar($this->admin, $this->tipo);
                if ($this->correoHuella !== null && ! hash_equals($this->correoHuella, hash('sha256', $this->usuario->correo))) {
                    throw new \RuntimeException('El destinatario cambió durante la preparación.');
                }
                $fase = 'ENVIANDO';
                $envios->registrar($this->admin, $this->usuario, $this->solicitud, $this->tipo, $fase, $intento);
                // El mailable se envía aquí: nunca se vuelve a encolar ni guarda el PDF en disco público.
                if (Mail::to($this->usuario->correo)->send($correo) === null) {
                    throw new \RuntimeException('El transporte no confirmó el envío.');
                }
            } catch (\Throwable $e) {
                // Únicamente una respuesta SMTP temporal explícita demuestra rechazo y permite reintentar.
                $temporal = $e instanceof TransportExceptionInterface && $e->getCode() >= 400 && $e->getCode() < 500;
                if ($temporal && $intento < $this->tries) {
                    $envios->registrar($this->admin, $this->usuario, $this->solicitud, $this->tipo, 'REINTENTO', $intento, $this->backoff()[$intento - 1]);
                    throw new \RuntimeException('El servidor de correo rechazó temporalmente la solicitud.');
                }
                $estado = $fase === 'ENVIANDO' && ! ($e instanceof TransportExceptionInterface && $e->getCode() >= 400 && $e->getCode() < 600)
                    ? 'INDETERMINADO' : 'FALLIDO';
                $envios->registrar($this->admin, $this->usuario, $this->solicitud, $this->tipo, $estado, $intento);
                Log::warning('Envío documental no confirmado.', ['solicitud' => $this->solicitud, 'estado' => $estado, 'intento' => $intento]);
                $this->fail(new \RuntimeException('No se pudo confirmar el envío documental. Consulte su estado.'));

                return;
            }
            // Fuera del catch de SMTP: si falla la auditoría después del envío, no se repite el mensaje.
            $envios->registrar($this->admin, $this->usuario, $this->solicitud, $this->tipo, 'ACEPTADO', $intento);
        } finally {
            $lock->release();
        }
    }

    public function failed(?\Throwable $exception): void
    {
        if (! $this->solicitud) {
            return;
        }
        $envios = app(EnviosCorreoDocumental::class);
        $actual = $envios->estadoInterno($this->admin, $this->usuario, $this->solicitud);
        if (! $actual['terminado']) {
            $envios->registrar($this->admin, $this->usuario, $this->solicitud, $this->tipo,
                $actual['estado'] === 'ENVIANDO' ? 'INDETERMINADO' : 'FALLIDO', $actual['intento']);
        }
    }
}
