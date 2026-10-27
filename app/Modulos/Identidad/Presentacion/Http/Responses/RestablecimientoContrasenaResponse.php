<?php

namespace App\Modulos\Identidad\Presentacion\Http\Responses;

use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\FailedPasswordResetResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse;
use Laravel\Fortify\Fortify;

final class RestablecimientoContrasenaResponse implements FailedPasswordResetResponse, PasswordResetResponse
{
    public function __construct(private readonly string $status) {}

    public function toResponse($request)
    {
        if ($this->status === Password::PASSWORD_RESET) {
            $mensaje = 'Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión con la nueva contraseña.';

            return $request->wantsJson()
                ? response()->json(['message' => $mensaje, 'login_url' => route('login')])
                : redirect()->route('login')->with('status', $mensaje)->with('password_reset_completed', true);
        }
        $mensaje = trans('passwords.token');

        return $request->wantsJson()
            ? response()->json(['message' => $mensaje, 'errors' => [Fortify::email() => [$mensaje]], 'solicitar_enlace_url' => route('password.request')], 422)
            : back()->withInput($request->only(Fortify::email()))->withErrors([Fortify::email() => $mensaje])->with('password_reset_link_invalid', true);
    }
}
