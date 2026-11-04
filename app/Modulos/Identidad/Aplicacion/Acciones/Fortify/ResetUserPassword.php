<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function reset(User $user, array $input): void
    {
        if ($user->estado !== 'ACTIVO') {
            throw ValidationException::withMessages(['correo' => trans('passwords.token')]);
        }
        Validator::make($input, ['password' => $this->passwordRules()])->validate();
        if (Hash::check($input['password'], $user->getAuthPassword())) {
            throw ValidationException::withMessages(['password' => 'La nueva contraseña debe ser diferente.']);
        }
        $datos=['contrasena'=>$input['password']];
        if (array_key_exists('cambio_password_pendiente',$user->getAttributes())) $datos['cambio_password_pendiente']=false;
        $user->forceFill($datos)->save();
        activity('Seguridad')->causedBy($user)->log('Contraseña restablecida.');
    }
}
