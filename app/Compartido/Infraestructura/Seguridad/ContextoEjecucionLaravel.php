<?php

namespace App\Compartido\Infraestructura\Seguridad;

use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

final class ContextoEjecucionLaravel implements ContextoEjecucion
{
    public function __construct(private readonly Factory $auth, private readonly Container $container) {}

    public function usuario(): ?User
    {
        $usuario = $this->auth->guard()->user();

        return $usuario instanceof User ? $usuario : null;
    }

    public function esLectura(): bool
    {
        // Conserva la excepción GET existente; HEAD/POST no ganan autorización.
        $request = $this->container->make('request');

        return $request instanceof Request && $request->isMethod('get');
    }
}
