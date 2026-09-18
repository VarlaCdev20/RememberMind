<?php
namespace App\Actions\Identidad\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
class UpdateUserPassword implements UpdatesUserPasswords {
    use PasswordValidationRules;
    public function update(User $user,array $input): void { Validator::make($input,['current_password'=>['required','string'],'password'=>$this->passwordRules()])->validateWithBag('updatePassword'); if(! Hash::check($input['current_password'],$user->getAuthPassword())){throw ValidationException::withMessages(['current_password'=>'La contraseña actual no es correcta.']);} if(Hash::check($input['password'],$user->getAuthPassword())){throw ValidationException::withMessages(['password'=>'La nueva contraseña debe ser diferente.']);} $user->forceFill(['contrasena'=>$input['password']])->save(); activity('Seguridad')->causedBy($user)->log('Contraseña actualizada.'); }
}
