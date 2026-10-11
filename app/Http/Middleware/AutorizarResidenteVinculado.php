<?php

namespace App\Http\Middleware;

use App\Models\Residente;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AutorizarResidenteVinculado
{
    public function handle(Request $request, Closure $next): Response
    {
        $parametro = $request->route('adulto_mayor');
        $codigo = $parametro instanceof Residente ? $parametro->cod_residente : $parametro;
        $residente = Residente::query()->findOrFail($codigo);

        Gate::authorize('view', $residente);

        return $next($request);
    }
}
