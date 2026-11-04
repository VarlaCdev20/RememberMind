<?php

namespace App\Modulos\Identidad\Presentacion\Http\Controllers;

use App\Modulos\Identidad\Aplicacion\Acciones\Fortify\ResetUserPassword;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\NewPasswordController;

/** Conserva el broker, token de un uso y acciones Fortify; valida la forma de la entrada. */
class RestablecerContrasenaController extends NewPasswordController
{
    public function store(Request $request): Responsable
    {
        $maximo = ResetUserPassword::passwordPolicy()['max'];
        $request->validate([
            'token' => ['required', 'string', 'max:256'],
            Fortify::email() => ['required', 'string', 'email', 'max:120'],
            'password' => ['required', 'string', 'max:'.$maximo],
            'password_confirmation' => ['required', 'string', 'max:'.$maximo],
        ]);

        return parent::store($request);
    }
}
