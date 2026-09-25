<?php
namespace App\Actions\Identidad\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
class CreateNewUser implements CreatesNewUsers {
    use PasswordValidationRules;
    public function create(array $input): User { Validator::make($input,['correo'=>['required','email','max:120','unique:usuarios,correo'],'password'=>$this->passwordRules()])->validate(); return User::query()->create(['cod_usuario'=>'USU_'.Str::upper(Str::random(12)),'correo'=>Str::lower($input['correo']),'contrasena'=>$input['password'],'estado'=>'ACTIVO']); }
}
