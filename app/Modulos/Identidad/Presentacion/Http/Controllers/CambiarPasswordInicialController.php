<?php

namespace App\Modulos\Identidad\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Compartido\Infraestructura\Notificaciones\CorreoNoConfigurado;
use App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class CambiarPasswordInicialController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user()?->fresh()?->estado === 'ACTIVO', 403);
        if ($request->user()->cambio_password_pendiente !== true) {
            return redirect()->route('dashboard');
        }

        return view('identidad::paginas.usuarios.password-inicial');
    }

    public function guardar(Request $request)
    {
        $usuario = $request->user()?->fresh();
        abort_unless($usuario?->estado === 'ACTIVO' && $usuario->cambio_password_pendiente === true, 403);
        try {
            app(ExigirTransporteCorreoReal::class)->verificar();
            $estado = Password::broker(config('fortify.passwords'))->sendResetLink(['correo' => $usuario->correo]);
        } catch (CorreoNoConfigurado|TransportExceptionInterface $error) {
            report($error);

            return back()->withErrors(['correo' => 'No se pudo enviar el enlace. Inténtalo de nuevo o comunícate con Administración.']);
        }
        if ($estado !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['correo' => $estado === Password::RESET_THROTTLED
                ? 'Ya solicitaste un enlace hace poco. Revisa tu correo o espera un minuto antes de intentarlo de nuevo.'
                : 'No se pudo enviar el enlace. Comunícate con Administración.']);
        }
        Auth::guard('web')->logout();
        Auth::forgetGuards();
        Auth::shouldUse('web');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('password.request')->with('status', 'Enlace enviado a tu correo registrado. Abre el mensaje y pulsa el botón para crear tu contraseña. Después podrás iniciar sesión con tu rol.');
    }
}
