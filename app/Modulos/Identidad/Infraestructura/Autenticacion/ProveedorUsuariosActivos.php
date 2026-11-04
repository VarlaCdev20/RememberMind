<?php

namespace App\Modulos\Identidad\Infraestructura\Autenticacion;

use Illuminate\Auth\EloquentUserProvider;

final class ProveedorUsuariosActivos extends EloquentUserProvider
{
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        if (! config('auth.remember_enabled')) {
            return null;
        }

        $usuario = parent::retrieveByToken($identifier, $token);

        return $usuario?->estado === 'ACTIVO' ? $usuario : null;
    }
}
