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

        return $next($request);
    }
}
