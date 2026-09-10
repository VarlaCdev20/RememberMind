<?php

namespace App\Actions\Identidad\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'nombres' => ['required', 'string', 'max:100'],
            'ap_paterno' => ['required', 'string', 'max:80'],
            'ap_materno' => ['nullable', 'string', 'max:80'],
            'correo' => ['required', 'email', 'max:120', Rule::unique('users')->ignore($user->cod_usu, 'cod_usu')],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['correo'] !== $user->correo &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'nombres' => $input['nombres'],
                'ap_paterno' => $input['ap_paterno'],
                'ap_materno' => $input['ap_materno'] ?? null,
                'correo' => $input['correo'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'nombres' => $input['nombres'],
            'ap_paterno' => $input['ap_paterno'],
            'ap_materno' => $input['ap_materno'] ?? null,
            'correo' => $input['correo'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
