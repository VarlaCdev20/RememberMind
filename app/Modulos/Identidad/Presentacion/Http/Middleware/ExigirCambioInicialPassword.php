<?php

namespace App\Modulos\Identidad\Presentacion\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class ExigirCambioInicialPassword
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = $request->user()?->fresh();
        if (! $usuario || $usuario->cambio_password_pendiente !== true) {
            return $next($request);
        }
        $nombre = $request->route()?->getName() ?? '';
        if (in_array($nombre, ['password.inicial', 'password.inicial.guardar', 'logout'], true) || str_starts_with($nombre, 'password.')) {
            return $next($request);
        }
        if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
            abort(403, 'Actualiza tu contraseña temporal antes de continuar.');
        }

        return redirect()->route('password.inicial');
    }
}
