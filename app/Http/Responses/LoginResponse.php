<?php

namespace App\Http\Responses;

use App\Modulos\Identidad\Presentacion\Navegacion\VisibilidadNavegacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();
        $visibilidad = app(VisibilidadNavegacion::class);
        $destino = $request->session()->pull('url.intended');

        if ($this->destinoPermitido($destino, $request, $visibilidad)) {
            return redirect()->to($destino);
        }

        foreach ([
            'MEDICO GENERAL/GERIATRA' => 'admin.medico.dashboard',
            'ENFERMEROS' => 'admin.enfermeria.dashboard',
            'PSICOLOGO/A' => 'admin.psicologia.dashboard',
        ] as $rol => $ruta) {
            if ($user?->hasRole($rol) && $visibilidad->puedeVerRuta($ruta)) {
                return redirect()->route($ruta);
            }
        }

        return redirect()->to(config('fortify.home'));
    }

    private function destinoPermitido(mixed $destino, Request $request, VisibilidadNavegacion $visibilidad): bool
    {
        if (! is_string($destino) || $destino === '' || str_starts_with($destino, '//')) {
            return false;
        }
        $url = str_starts_with($destino, '/') ? $request->getSchemeAndHttpHost().$destino : $destino;
        if (! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_USER) !== null) {
            return false;
        }
        $peticionDestino = Request::create($url, 'GET');
        if ($peticionDestino->getSchemeAndHttpHost() !== $request->getSchemeAndHttpHost()) {
            return false;
        }

        try {
            $ruta = app('router')->getRoutes()->match($peticionDestino);
        } catch (NotFoundHttpException|MethodNotAllowedHttpException) {
            // Un enlace guardado que ya no existe se sustituye por el inicio del perfil.
            return false;
        }
        $nombre = $ruta->getName();
        $requiereSesion = collect($ruta->gatherMiddleware())->contains(
            fn ($middleware) => $middleware === 'auth' || str_starts_with($middleware, 'auth:')
        );

        return $nombre !== null && $requiereSesion && $visibilidad->puedeVerRuta($nombre);
    }
}
