<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class RegistrarCuentaInstitucionalHttp
{
    public function guardarUsuario(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario->can('usuarios.gestionar'), 403);
        $datos = Validator::make($entrada, ['correo' => ['required', 'email', 'max:120', 'unique:usuarios,correo'], 'password' => ['required', 'string', 'min:12'], 'estado' => ['required', 'in:ACTIVO,INACTIVO'], 'rol' => ['required', 'exists:roles,name']])->validate();
        $usuario = User::query()->create(['cod_usuario' => $this->codigo('USU'), 'correo' => $datos['correo'], 'contrasena' => Hash::make($datos['password']), 'estado' => $datos['estado']]);
        $usuario->assignRole($datos['rol']);

        return $usuario->load('roles');
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
