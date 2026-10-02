<?php

namespace App\Http\Middleware;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventWritesDuringRolePreview
{
    public function __construct(private readonly RolePreviewService $preview)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->preview->isActive($request->user())) {
            return $next($request);
        }

        if ($request->isMethodSafe() || $request->routeIs('role-preview.store', 'role-preview.destroy', 'logout')) {
            return $next($request);
        }

        abort(403, 'Disponible únicamente fuera del modo de previsualización.');
    }
}
