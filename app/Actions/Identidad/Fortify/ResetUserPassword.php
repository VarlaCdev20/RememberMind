<?php

namespace App\Actions\Identidad\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Throwable;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Valida y restablece la contraseña de un usuario
     * mediante el flujo de recuperación de Fortify.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function reset(
        User $user,
        array $input
    ): void {
        /*
        |--------------------------------------------------------------------------
        | 1. Validar nueva contraseña
        |--------------------------------------------------------------------------
        */

        $this->validarPassword(
            $input
        );


        /*
        |--------------------------------------------------------------------------
        | 2. Impedir reutilizar la contraseña vigente
        |--------------------------------------------------------------------------
        */

        $this->verificarPasswordDiferente(
            user: $user,
            nuevaPassword:
                (string) $input['password'],
        );


        /*
        |--------------------------------------------------------------------------
        | 3. Persistir el cambio
        |--------------------------------------------------------------------------
        */

        $this->guardarNuevaPassword(
            user: $user,
            nuevaPassword:
                (string) $input['password'],
        );


        /*
        |--------------------------------------------------------------------------
        | 4. Registrar auditoría
        |--------------------------------------------------------------------------
        */

        $this->registrarAuditoria(
            $user
        );
    }


    /**
     * ==============================================================
     * VALIDACIÓN
     * ==============================================================
     */
    private function validarPassword(
        array $input
    ): void {
        $politica =
            static::passwordPolicy();


        Validator::make(
            $input,

            [
                'password' =>
                    $this->passwordRules(),

                /*
                 * La regla confirmed de password compara
                 * automáticamente este campo.
                 */
                'password_confirmation' => [
                    'required',
                    'string',
                    'max:' . $politica['max'],
                ],
            ],

            [
                /*
                |--------------------------------------------------------------------------
                | Nueva contraseña
                |--------------------------------------------------------------------------
                */

                'password.required' =>
                    'Ingresa una nueva contraseña.',

                'password.string' =>
                    'La nueva contraseña no es válida.',

                'password.min' =>
                    'La nueva contraseña debe contener al menos '
                    . $politica['min']
                    . ' caracteres.',

                'password.max' =>
                    'La nueva contraseña no puede superar los '
                    . $politica['max']
                    . ' caracteres.',

                'password.confirmed' =>
                    'La confirmación no coincide con la nueva contraseña.',


                /*
                |--------------------------------------------------------------------------
                | Confirmación
                |--------------------------------------------------------------------------
                */

                'password_confirmation.required' =>
                    'Confirma la nueva contraseña.',

                'password_confirmation.string' =>
                    'La confirmación de contraseña no es válida.',

                'password_confirmation.max' =>
                    'La confirmación de contraseña contiene demasiados caracteres.',
            ]
        )->validate();
    }


    /**
     * ==============================================================
     * IMPEDIR REUTILIZACIÓN INMEDIATA
     * ==============================================================
     *
     * Aunque el usuario esté utilizando el flujo
     * "Olvidé mi contraseña", no permitimos restablecerla
     * exactamente al mismo valor que ya tenía.
     *
     * @throws ValidationException
     */
    private function verificarPasswordDiferente(
        User $user,
        string $nuevaPassword
    ): void {
        if (
            ! Hash::check(
                $nuevaPassword,
                $user->password
            )
        ) {
            return;
        }


        throw ValidationException::withMessages([
            'password' =>
                'La nueva contraseña debe ser diferente de la contraseña anterior.',
        ]);
    }


    /**
     * ==============================================================
     * GUARDAR NUEVA CONTRASEÑA
     * ==============================================================
     */
    private function guardarNuevaPassword(
        User $user,
        string $nuevaPassword
    ): void {
        $user->forceFill([
            /*
             * Nueva contraseña cifrada mediante el
             * algoritmo configurado por Laravel.
             */
            'password' =>
                Hash::make(
                    $nuevaPassword
                ),


            /*
             * El usuario ya estableció personalmente
             * una contraseña definitiva.
             */
            'debe_cambiar_password' =>
                false,


            /*
             * Fecha real del restablecimiento.
             */
            'password_changed_at' =>
                now(),


            /*
             * Invalida cookies antiguas de "recordarme".
             *
             * Esto es especialmente importante en un
             * restablecimiento por contraseña olvidada.
             */
            'remember_token' =>
                Str::random(60),
        ])->save();
    }


    /**
     * ==============================================================
     * AUDITORÍA
     * ==============================================================
     *
     * Este evento nunca registra:
     *
     * - contraseña anterior;
     * - contraseña nueva;
     * - confirmación;
     * - hash;
     * - token de recuperación;
     * - remember_token.
     *
     * Tampoco marcamos al usuario como "causer" porque
     * este flujo ocurre antes de iniciar sesión.
     */
    private function registrarAuditoria(
        User $user
    ): void {
        if (
            ! function_exists(
                'activity'
            )
        ) {
            return;
        }


        try {
            activity('Seguridad')
                ->performedOn($user)
                ->event(
                    'restablecimiento_password'
                )
                ->withProperties([
                    'origen' =>
                        'recuperacion_password',
                ])
                ->log(
                    'Se restableció la contraseña de la cuenta mediante el flujo de recuperación.'
                );
        } catch (Throwable $exception) {
            /*
             * La contraseña ya fue restablecida.
             *
             * Un fallo secundario de auditoría no debe
             * hacer creer al usuario que la operación
             * principal falló.
             */
            report(
                $exception
            );
        }
    }
}