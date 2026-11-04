<?php

namespace App\Modulos\Identidad\Presentacion\Http\Controllers;

use App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal;
use Illuminate\Contracts\Support\Responsable;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Requests\SendPasswordResetLinkRequest;

final class SolicitarRecuperacionContrasenaController extends PasswordResetLinkController
{
    public function store(SendPasswordResetLinkRequest $request): Responsable
    {
        // Comprobar antes del broker mantiene la misma respuesta exista o no la cuenta.
        app(ExigirTransporteCorreoReal::class)->verificar();

        return parent::store($request);
    }
}
