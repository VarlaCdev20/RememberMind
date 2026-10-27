<?php

namespace App\Modulos\Identidad\Aplicacion\Consultas;

use App\Models\User;
use Illuminate\Support\Facades\Password;

final class ConsultarEnlaceRecuperacion
{
    public function esUtilizable(mixed $correo, mixed $token): bool
    {
        if (! is_string($correo) || ! is_string($token) || ! filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 120 || strlen($token) > 256) {
            return false;
        }
        $usuario = User::query()->where('correo', mb_strtolower(trim($correo)))->where('estado', 'ACTIVO')->first();

        return $usuario !== null && Password::broker(config('fortify.passwords'))->tokenExists($usuario, $token);
    }
}
