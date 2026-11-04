<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

final class EnviarRecuperacionAccesoPersonal
{
    public function ejecutar(User $actor, string $usuarioId): void
    {
        $usuario = User::findOrFail($usuarioId);
        $actor = app(AutorizarGestionPersonal::class)->autorizar($actor, $usuario);
        abort_unless($actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']), 403);
        abort_unless($usuario->personal, 422, 'La cuenta no tiene una ficha laboral.');
        if ($usuario->estado !== 'ACTIVO') {
            throw ValidationException::withMessages(['recuperacion' => 'Activa el acceso de esta cuenta antes de solicitar la recuperación.']);
        }

        app(ExigirTransporteCorreoReal::class)->verificar();
        $estado = Password::broker(config('fortify.passwords'))->sendResetLink([Fortify::email() => $usuario->correo]);
        if ($estado !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['recuperacion' => __($estado)]);
        }

        activity('personal_institucional')->causedBy($actor)->performedOn($usuario)
            ->event('recuperacion_acceso_personal')->log('Enlace de recuperación solicitado para la cuenta del trabajador.');
    }
}
