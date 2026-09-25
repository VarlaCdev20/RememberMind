<?php
namespace App\Actions\Identidad\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
class ResetUserPassword implements ResetsUserPasswords {
    use PasswordValidationRules;
    public function reset(User $user,array $input): void { Validator::make($input,['password'=>$this->passwordRules()])->validate(); if(Hash::check($input['password'],$user->getAuthPassword())){throw ValidationException::withMessages(['password'=>'La nueva contraseña debe ser diferente.']);} $user->forceFill(['contrasena'=>$input['password']])->save(); activity('Seguridad')->causedBy($user)->log('Contraseña restablecida.'); }
}
