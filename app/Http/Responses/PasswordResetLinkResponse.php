<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class PasswordResetLinkResponse implements
    SuccessfulPasswordResetLinkRequestResponse,
    FailedPasswordResetLinkRequestResponse
{
    /**
     * Mensaje público único.
     *
     * Nunca indicamos si la cuenta existe o no.
     */
    private const MESSAGE =
    'Si el correo corresponde a una cuenta institucional, '
        . 'recibirás las instrucciones para restablecer tu contraseña.';


    /**
     * Fortify proporciona el estado interno del Password Broker.
     *
     * Deliberadamente no utilizamos ese valor para construir
     * el mensaje público, porque podría revelar si el correo
     * está registrado.
     */
    public function __construct(
        string $status
    ) {
        //
    }


    /**
     * Construir respuesta HTTP.
     */
    public function toResponse(
        $request
    ): JsonResponse|RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Petición JSON
        |--------------------------------------------------------------------------
        */

        if (
            $request->wantsJson()
        ) {
            return response()->json(
                [
                    'message' =>
                    self::MESSAGE,
                ],
                200
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Navegador
        |--------------------------------------------------------------------------
        |
        | Regresamos a la página que originó la petición.
        |
        | En nuestro caso normalmente será login.blade.php,
        | donde sessionStorage vuelve a abrir automáticamente
        | el panel "Recuperar acceso".
        |
        */

        return back()
            ->withInput(
                $request->only(
                    Fortify::email()
                )
            )
            ->with(
                'status',
                self::MESSAGE
            );
    }
}
