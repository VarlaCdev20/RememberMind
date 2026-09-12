<?php

namespace App\Actions\Identidad\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Throwable;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;


    /*
    |--------------------------------------------------------------------------
    | Protección contra intentos repetidos
    |--------------------------------------------------------------------------
    |
    | Se permiten hasta 5 intentos fallidos de contraseña actual
    | dentro de una ventana de 60 segundos.
    |
    */

    private const MAX_INTENTOS = 5;

    private const BLOQUEO_SEGUNDOS = 60;

    private const RATE_LIMIT_PREFIX = 'perfil-password';


    /**
     * Valida y actualiza la contraseña del usuario autenticado.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function update(
        User $user,
        array $input
    ): void {
        /*
        |--------------------------------------------------------------------------
        | 1. Validar estructura del formulario
        |--------------------------------------------------------------------------
        */

        $this->validarFormulario(
            $input
        );


        /*
        |--------------------------------------------------------------------------
        | 2. Construir identificador del limitador
        |--------------------------------------------------------------------------
        */

        $limiterKey = $this->limiterKey(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | 3. Comprobar bloqueo temporal
        |--------------------------------------------------------------------------
        */

        $this->verificarLimiteIntentos(
            $limiterKey
        );


        /*
        |--------------------------------------------------------------------------
        | 4. Verificar contraseña actual
        |--------------------------------------------------------------------------
        */

        $this->verificarPasswordActual(
            user: $user,
            passwordActual: (string) $input['current_password'],
            limiterKey: $limiterKey,
        );


        /*
        |--------------------------------------------------------------------------
        | 5. Impedir reutilizar la contraseña vigente
        |--------------------------------------------------------------------------
        */

        $this->verificarPasswordDiferente(
            user: $user,
            nuevaPassword: (string) $input['password'],
        );


        /*
        |--------------------------------------------------------------------------
        | 6. Persistir cambio
        |--------------------------------------------------------------------------
        */

        $this->guardarNuevaPassword(
            user: $user,
            nuevaPassword: (string) $input['password'],
        );


        /*
        |--------------------------------------------------------------------------
        | 7. Limpiar intentos fallidos
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            $limiterKey
        );


        /*
        |--------------------------------------------------------------------------
        | 8. Registrar auditoría
        |--------------------------------------------------------------------------
        */

        $this->registrarAuditoria(
            $user
        );
    }


    /**
     * ==============================================================
     * VALIDACIÓN DEL FORMULARIO
     * ==============================================================
     */
    private function validarFormulario(
        array $input
    ): void {
        $politica =
            static::passwordPolicy();


        Validator::make(
            $input,

            [
                /*
                 * Contraseña vigente.
                 *
                 * No se utiliza current_password:web aquí porque
                 * necesitamos controlar manualmente los intentos
                 * fallidos con RateLimiter.
                 */
                'current_password' => [
                    'required',
                    'string',
                    'max:255',
                ],


                /*
                 * La política completa vive en
                 * PasswordValidationRules.
                 */
                'password' =>
                $this->passwordRules(),


                /*
                 * La regla "confirmed" del campo password
                 * también compara este valor.
                 *
                 * La declaramos explícitamente para obtener
                 * mensajes de validación más precisos.
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
                | Contraseña actual
                |--------------------------------------------------------------------------
                */

                'current_password.required' =>
                'Ingresa tu contraseña actual.',

                'current_password.string' =>
                'La contraseña actual no es válida.',

                'current_password.max' =>
                'La contraseña actual contiene demasiados caracteres.',


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
            ],
        )->validateWithBag(
            'updatePassword'
        );
    }


    /**
     * ==============================================================
     * IDENTIFICADOR DEL RATE LIMITER
     * ==============================================================
     *
     * El identificador se genera mediante hash para no dejar
     * directamente el código de usuario o IP dentro de la clave
     * almacenada en caché.
     */
    private function limiterKey(
        User $user
    ): string {
        $ip = request()->ip()
            ?: 'sin-ip';


        $fingerprint = hash(
            'sha256',

            implode(
                '|',
                [
                    (string) $user->cod_usu,
                    $ip,
                ]
            )
        );


        return self::RATE_LIMIT_PREFIX
            . ':'
            . $fingerprint;
    }


    /**
     * ==============================================================
     * COMPROBAR BLOQUEO
     * ==============================================================
     *
     * @throws ValidationException
     */
    private function verificarLimiteIntentos(
        string $limiterKey
    ): void {
        if (
            ! RateLimiter::tooManyAttempts(
                $limiterKey,
                self::MAX_INTENTOS
            )
        ) {
            return;
        }


        $segundos = max(
            1,

            RateLimiter::availableIn(
                $limiterKey
            )
        );


        throw ValidationException::withMessages([
            'current_password' =>
            $this->mensajeBloqueo(
                $segundos
            ),
        ])->errorBag(
            'updatePassword'
        );
    }


    /**
     * ==============================================================
     * VERIFICAR CONTRASEÑA ACTUAL
     * ==============================================================
     *
     * @throws ValidationException
     */
    private function verificarPasswordActual(
        User $user,
        string $passwordActual,
        string $limiterKey
    ): void {
        if (
            Hash::check(
                $passwordActual,
                $user->password
            )
        ) {
            return;
        }


        /*
         * El intento solamente se contabiliza
         * cuando la contraseña actual realmente
         * es incorrecta.
         */
        RateLimiter::hit(
            $limiterKey,
            self::BLOQUEO_SEGUNDOS
        );


        /*
         * Después de registrar el intento,
         * comprobamos si se alcanzó el límite.
         *
         * Así el quinto intento ya puede mostrar
         * directamente el bloqueo.
         */
        if (
            RateLimiter::tooManyAttempts(
                $limiterKey,
                self::MAX_INTENTOS
            )
        ) {
            $segundos = max(
                1,

                RateLimiter::availableIn(
                    $limiterKey
                )
            );


            throw ValidationException::withMessages([
                'current_password' =>
                $this->mensajeBloqueo(
                    $segundos
                ),
            ])->errorBag(
                'updatePassword'
            );
        }


        $intentosRestantes = max(
            0,

            self::MAX_INTENTOS
                - RateLimiter::attempts(
                    $limiterKey
                )
        );


        $mensaje =
            'La contraseña actual ingresada no es correcta.';


        if ($intentosRestantes > 0) {
            $mensaje .= sprintf(
                ' %d %s antes del bloqueo temporal.',
                $intentosRestantes,
                $intentosRestantes === 1
                    ? 'intento restante'
                    : 'intentos restantes'
            );
        }


        throw ValidationException::withMessages([
            'current_password' =>
            $mensaje,
        ])->errorBag(
            'updatePassword'
        );
    }


    /**
     * ==============================================================
     * EVITAR REUTILIZACIÓN INMEDIATA
     * ==============================================================
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
            'La nueva contraseña debe ser diferente de tu contraseña actual.',
        ])->errorBag(
            'updatePassword'
        );
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
             * Hash irreversible.
             */
            'password' =>
            Hash::make(
                $nuevaPassword
            ),


            /*
             * Si la cuenta tenía una contraseña
             * temporal, deja de estar pendiente.
             */
            'debe_cambiar_password' =>
            false,


            /*
             * Fecha real del último cambio.
             */
            'password_changed_at' =>
            now(),


            /*
             * Renueva el token persistente.
             *
             * Esto invalida accesos antiguos basados
             * en "recordarme", pero no elimina la
             * sesión actual del usuario.
             */
            'remember_token' =>
            Str::random(60),
        ])->save();
    }


    /**
     * ==============================================================
     * MENSAJE DE BLOQUEO
     * ==============================================================
     */
    private function mensajeBloqueo(
        int $segundos
    ): string {
        if ($segundos === 1) {
            return
                'Se alcanzó el límite de intentos. '
                . 'Intenta nuevamente en 1 segundo.';
        }


        return
            'Se alcanzó el límite de intentos. '
            . "Intenta nuevamente en {$segundos} segundos.";
    }


    /**
     * ==============================================================
     * AUDITORÍA
     * ==============================================================
     *
     * La auditoría nunca almacena:
     *
     * - contraseña actual;
     * - contraseña nueva;
     * - confirmación;
     * - hash;
     * - token de sesión.
     *
     * Además, un problema secundario en Activitylog
     * no debe provocar que el usuario vea un error
     * después de que la contraseña ya fue modificada.
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
                ->causedBy($user)
                ->performedOn($user)
                ->event(
                    'cambio_password'
                )
                ->withProperties([
                    'origen' =>
                    'mi_perfil',
                ])
                ->log(
                    'El usuario actualizó su contraseña desde Mi perfil.'
                );
        } catch (Throwable $exception) {
            /*
             * El cambio de contraseña ya fue realizado.
             *
             * Reportamos el problema de auditoría
             * para revisión técnica, pero no revertimos
             * ni exponemos el error al usuario.
             */
            report(
                $exception
            );
        }
    }
}
