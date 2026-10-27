<?php

namespace App\Modulos\Identidad\Infraestructura\Notificaciones;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Servicios\EnviosCorreoDocumental;

final class EnviarPaqueteDocumental
{
    public function ejecutar(User $actor, User $user): array
    {
        return app(EnviosCorreoDocumental::class)->solicitar($actor, $user, 'paquete');
    }
}
