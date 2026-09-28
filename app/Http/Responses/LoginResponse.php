<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->hasRole('MEDICO GENERAL/GERIATRA')) {
            return redirect()->intended(route('admin.medico.dashboard'));
        }

        if ($user && $user->hasRole('ENFERMEROS')) {
            return redirect()->intended(route('admin.enfermeria.dashboard'));
        }

        if ($user && $user->hasRole('PSICOLOGO/A')) {
            return redirect()->intended(route('admin.psicologia.dashboard'));
        }

        return redirect()->intended(config('fortify.home'));
    }
}
