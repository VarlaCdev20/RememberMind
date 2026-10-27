<?php

namespace App\Modulos\Identidad\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modulos\Identidad\Aplicacion\Acciones\Fortify\UpdateUserPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class CambiarPasswordInicialController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user()?->fresh()?->estado === 'ACTIVO', 403);
        if ($request->user()->cambio_password_pendiente !== true) {
            return redirect()->route('dashboard');
        }

        return view('identidad::paginas.usuarios.password-inicial', ['politica' => UpdateUserPassword::passwordPolicy()]);
    }

    public function guardar(Request $request, UpdateUserPassword $accion)
    {
        abort_unless($request->user()?->fresh()?->estado === 'ACTIVO' && $request->user()->cambio_password_pendiente === true, 403);
        try {
            $accion->update($request->user(), $request->only(['current_password', 'password', 'password_confirmation']));
        } catch (ValidationException $error) {
            $error->errorBag = 'updatePassword';
            throw $error;
        }
        $request->session()->regenerate();
        $request->session()->put('password_hash_'.Auth::getDefaultDriver(), $request->user()->getAuthPassword());

        return redirect()->route('dashboard')->with('status', 'Contraseña actualizada. Ya puedes continuar.');
    }
}
