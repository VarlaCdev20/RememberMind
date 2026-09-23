<?php
namespace App\Actions\Identidad\Fortify;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
class UpdateUserProfileInformation implements UpdatesUserProfileInformation {
    public function update(User $user,array $input): void {
        $validated=Validator::make($input,[
            'nombres'=>['sometimes','required','string','max:100'],
            'ap_paterno'=>['sometimes','required','string','max:80'],
            'ap_materno'=>['nullable','string','max:80'],
            'correo'=>['sometimes','email','max:120',Rule::unique('usuarios','correo')->ignore($user->cod_usuario,'cod_usuario')],
            'photo'=>['nullable','image','max:2048'],
        ])->validateWithBag('updateProfileInformation');

        DB::transaction(function () use ($user, $validated): void {
            $datosUsuario=Arr::only($validated,['correo']);
            if(isset($validated['photo'])){
                if($user->foto){Storage::disk('public')->delete($user->foto);}
                $datosUsuario['foto']=$validated['photo']->store('usuarios','public');
            }
            if($datosUsuario!==[]){$user->fill($datosUsuario)->save();}

            $identidad=$user->personal ?: $user->contactos()->first();
            if($identidad){
                $identidad->fill([
                    'nombres'=>$validated['nombres'] ?? $identidad->nombres,
                    'apellido_paterno'=>$validated['ap_paterno'] ?? $identidad->apellido_paterno,
                    'apellido_materno'=>$validated['ap_materno'] ?? $identidad->apellido_materno,
                ])->save();
            }
        });
    }
}
