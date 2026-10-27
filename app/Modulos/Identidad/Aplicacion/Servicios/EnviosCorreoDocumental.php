<?php

namespace App\Modulos\Identidad\Aplicacion\Servicios;

use App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Identidad\Infraestructura\Jobs\EnviarFichaUsuarioJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/** Seguimiento técnico mediante la auditoría existente; no guarda documentos ni destinatarios. */
final class EnviosCorreoDocumental
{
    public const COLA = 'correo-documental';

    public const ESTADOS = [
        'PENDIENTE' => ['Pendiente', 'La solicitud está en cola. Todavía no se ha enviado el correo.'],
        'PREPARANDO' => ['En proceso', 'Se está preparando el documento.'],
        'ENVIANDO' => ['En proceso', 'Se está comunicando con el servidor de correo.'],
        'REINTENTO' => ['Reintento programado', 'El servidor no pudo aceptar el correo. El sistema volverá a intentarlo automáticamente.'],
        'ACEPTADO' => ['Aceptado por el servidor', 'El servidor aceptó el correo. Esto no confirma su recepción ni su lectura; revise también Spam.'],
        'FALLIDO' => ['Falló el envío', 'No se pudo enviar el correo. Revise la cuenta, los permisos o la configuración antes de solicitar otro envío.'],
        'INDETERMINADO' => ['Envío sin confirmar', 'Se perdió la confirmación del servidor. Compruebe si el correo llegó antes de solicitar otro envío para evitar duplicados.'],
    ];

    public function autorizar(User $actor, string $tipo): void
    {
        abort_unless($actor->estado === 'ACTIVO', 403);
        abort_unless(in_array($tipo, ['ficha', 'paquete'], true), 422);
        abort_unless($tipo === 'ficha' ? $actor->can('usuarios.ver') : $actor->canAny(['usuarios.editar', 'usuarios.crear']), 403);
    }

    public function solicitar(User $actor, User $usuario, string $tipo): array
    {
        $this->autorizar($actor, $tipo);
        abort_if(session(RolePreviewService::SESSION_KEY) !== null, 403);
        abort_unless(filter_var($usuario->correo, FILTER_VALIDATE_EMAIL), 422, 'Registre un correo válido para este usuario.');
        app(ExigirTransporteCorreoReal::class)->verificar();
        if (! in_array(config('queue.connections.'.config('queue.default').'.driver'), ['database', 'redis', 'sqs', 'beanstalkd'], true)) {
            throw new \RuntimeException('El envío documental requiere una cola persistente con trabajador de reintentos.');
        }

        return Cache::lock('correo:solicitud:'.hash('sha256', $actor->getKey().'|'.$usuario->getKey().'|'.$tipo), 10)->block(3, function () use ($actor, $usuario, $tipo) {
            $anterior = $this->consulta($actor, $usuario)->where('properties->tipo', $tipo)->latest('id')->first();
            if ($anterior) {
                $estado = $anterior->properties['estado'];
                if (! in_array($estado, ['ACEPTADO', 'FALLIDO', 'INDETERMINADO'], true)
                    || ($estado !== 'FALLIDO' && $anterior->created_at->gt(now()->subMinute()))) {
                    return $this->presentar($anterior) + ['duplicado' => true];
                }
            }
            $solicitud = (string) Str::uuid();
            $this->registrar($actor, $usuario, $solicitud, $tipo, 'PENDIENTE');
            try {
                EnviarFichaUsuarioJob::dispatch($usuario, $actor, $solicitud, $tipo)->onQueue(self::COLA)->afterCommit();
            } catch (\Throwable $e) {
                $this->registrar($actor, $usuario, $solicitud, $tipo, 'FALLIDO');
                throw $e;
            }

            return $this->estado($actor, $usuario, $solicitud) + ['duplicado' => false];
        });
    }

    public function registrar(User $actor, User $usuario, string $solicitud, string $tipo, string $estado, int $intento = 0, ?int $demora = null): void
    {
        if (! isset(self::ESTADOS[$estado])) {
            throw new \InvalidArgumentException('Estado técnico de correo desconocido.');
        }
        $registro = activity('Correo')->causedBy($actor)->performedOn($usuario)->event('correo_estado')
            ->withProperties(['solicitud' => $solicitud, 'tipo' => $tipo, 'estado' => $estado,
                'intento' => $intento, 'proximo_intento' => $demora === null ? null : now()->addSeconds($demora)->toIso8601String()])
            ->log(self::ESTADOS[$estado][0]);
        if ($registro === null) {
            throw new \RuntimeException('No se pudo conservar el estado del correo. Revise la configuración de auditoría.');
        }
    }

    public function estado(User $actor, User $usuario, string $solicitud): array
    {
        $estado = $this->estadoInterno($actor, $usuario, $solicitud);
        $this->autorizar($actor, $estado['tipo']);

        return $estado;
    }

    /** Uso del trabajador: conserva el resultado incluso si se revocó el acceso del solicitante. */
    public function estadoInterno(User $actor, User $usuario, string $solicitud): array
    {
        abort_unless(Str::isUuid($solicitud), 404);
        $registro = $this->consulta($actor, $usuario)->where('properties->solicitud', $solicitud)->latest('id')->firstOrFail();

        return $this->presentar($registro);
    }

    private function consulta(User $actor, User $usuario)
    {
        return Activity::query()->where('log_name', 'Correo')->where('event', 'correo_estado')
            ->where('subject_type', $usuario->getMorphClass())->where('subject_id', $usuario->getKey())
            ->where('causer_type', $actor->getMorphClass())->where('causer_id', $actor->getKey());
    }

    private function presentar(Activity $registro): array
    {
        $p = $registro->properties;
        [$titulo, $mensaje] = self::ESTADOS[$p['estado']];

        return ['solicitud' => $p['solicitud'], 'tipo' => $p['tipo'], 'estado' => $p['estado'], 'titulo' => $titulo,
            'message' => $mensaje, 'intento' => $p['intento'], 'proximo_intento' => $p['proximo_intento'],
            'terminado' => in_array($p['estado'], ['ACEPTADO', 'FALLIDO', 'INDETERMINADO'], true)];
    }
}
