<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'nombres' => ['required', 'string', 'max:100'],
            'ap_paterno' => ['required', 'string', 'max:80'],
            'ap_materno' => ['nullable', 'string', 'max:80'],
           'correo' => ['required', 'string', 'email', 'max:120', 'unique:users,correo'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return User::create([
            'nombres' => $input['nombres'],
            'ap_paterno' => $input['ap_paterno'],
            'ap_materno' => $input['ap_materno'] ?? null,
            'correo' => $input['correo'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
