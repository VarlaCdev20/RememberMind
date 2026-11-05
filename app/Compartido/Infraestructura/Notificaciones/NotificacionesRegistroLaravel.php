<?php

namespace App\Compartido\Infraestructura\Notificaciones;

use App\Compartido\Aplicacion\Contratos\NotificacionesRegistro;
use App\Mail\PersonalInstitucionalBienvenidaMail;
use App\Mail\PreadmisionDocumentosPendientesMail;
use App\Mail\UsuarioPasswordActualizadaMail;
use App\Models\Preadmision;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Fortify;

final class NotificacionesRegistroLaravel implements NotificacionesRegistro
{
    public function bienvenidaPersonal(User $usuario, string $rol, string $area, string $estado, array $pendientes, string $fechaLimite, array $adjuntos, ?string $clave, array $subidos): void
    {
        $correo = new PersonalInstitucionalBienvenidaMail($usuario, $rol, $area, $estado, $pendientes, $fechaLimite, $adjuntos, $clave, $subidos);
        if (filled($clave)) {
            $token = Password::broker(config('fortify.passwords'))->createToken($usuario);
            $correo->urlCambioPassword = route('password.reset', ['token'=>$token, Fortify::email()=>$usuario->getEmailForPasswordReset()]);
        }
        Mail::to($usuario->correo)->send($correo);
    }

    public function pendientesPreadmision(Preadmision $preadmision, array $pendientes, string $fechaLimite): void
    {
        Mail::to($preadmision->familiar_correo)->send(new PreadmisionDocumentosPendientesMail($preadmision, $pendientes, $fechaLimite));
    }

    public function claveActualizada(User $usuario, string $clave): void
    {
        Mail::to($usuario->correo)->send(new UsuarioPasswordActualizadaMail($usuario, $clave));
    }
}
