<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsegurarCuentaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->estado === 'ACTIVO', 403, 'La cuenta de usuario no está activa.');
        if ($request->user()->cambio_password_pendiente === true && !in_array($request->route()?->getName(),['password.inicial','password.inicial.guardar','logout'],true)) {
            if ($request->expectsJson()) abort(403,'Actualiza tu contraseña temporal antes de continuar.');
            return redirect()->route('password.inicial');
        }

        return $next($request);
    }
}
