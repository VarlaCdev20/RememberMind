<?php

namespace App\Modulos\Identidad\Infraestructura\Seguridad;

use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Identidad\Aplicacion\Contratos\EstadoPrevisualizacion;
use Illuminate\Contracts\Session\Session;

final class PrevisualizacionEnSesion implements EstadoPrevisualizacion
{
    public function __construct(private readonly Session $session) {}

    public function obtener(): mixed
    {
        return $this->session->get(RolePreviewService::SESSION_KEY);
    }

    public function guardar(string $rol): void
    {
        $this->session->put(RolePreviewService::SESSION_KEY, $rol);
    }

    public function limpiar(): void
    {
        $this->session->forget(RolePreviewService::SESSION_KEY);
    }
}
