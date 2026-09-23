<?php
namespace App\Actions\Identidad\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
class UpdateUserProfileInformation implements UpdatesUserProfileInformation {
    public function update(User $user,array $input): void { $validated=Validator::make($input,['correo'=>['sometimes','email','max:120',Rule::unique('usuarios','correo')->ignore($user->cod_usuario,'cod_usuario')],'photo'=>['nullable','image','max:2048']])->validateWithBag('updateProfileInformation'); if(isset($validated['photo'])){if($user->foto){Storage::disk('public')->delete($user->foto);} $validated['foto']=$validated['photo']->store('usuarios','public'); unset($validated['photo']);} $user->fill($validated)->save(); }
}
