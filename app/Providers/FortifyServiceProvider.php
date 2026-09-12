<?php

namespace App\Providers;

use App\Actions\Identidad\Fortify\CreateNewUser;
use App\Actions\Identidad\Fortify\ResetUserPassword;
use App\Actions\Identidad\Fortify\UpdateUserPassword;
use App\Actions\Identidad\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\LoginResponse as CustomLoginResponse;
use App\Http\Responses\PasswordResetLinkResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Registrar servicios de autenticación.
     */
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Respuesta posterior al login
        |--------------------------------------------------------------------------
        */

        $this->app->singleton(
            LoginResponse::class,
            CustomLoginResponse::class
        );


        /*
        |--------------------------------------------------------------------------
        | Recuperación de contraseña
        |--------------------------------------------------------------------------
        |
        | Tanto un correo existente como uno inexistente producirán
        | la misma respuesta pública.
        |
        | Esto evita revelar qué correos pertenecen a cuentas
        | registradas en RememberMind.
        |
        */

        $this->app->bind(
            SuccessfulPasswordResetLinkRequestResponse::class,
            PasswordResetLinkResponse::class
        );

        $this->app->bind(
            FailedPasswordResetLinkRequestResponse::class,
            PasswordResetLinkResponse::class
        );
    }


    /**
     * Inicializar Fortify.
     */
    public function boot(): void
    {
        $this->configureActions();

        $this->configureAuthentication();

        $this->configureRateLimiting();
    }


    /**
     * ==============================================================
     * ACTIONS DE FORTIFY
     * ==============================================================
     */
    private function configureActions(): void
    {
        /*
         * Registro.
         *
         * Aunque el registro público está desactivado
         * en config/fortify.php, mantenemos la Action
         * registrada para compatibilidad interna.
         */
        Fortify::createUsersUsing(
            CreateNewUser::class
        );


        /*
         * Perfil.
         */
        Fortify::updateUserProfileInformationUsing(
            UpdateUserProfileInformation::class
        );


        /*
         * Cambio de contraseña desde Mi perfil.
         */
        Fortify::updateUserPasswordsUsing(
            UpdateUserPassword::class
        );


        /*
         * Recuperación de contraseña mediante token.
         */
        Fortify::resetUserPasswordsUsing(
            ResetUserPassword::class
        );


        /*
         * Challenge 2FA.
         */
        Fortify::redirectUserForTwoFactorAuthenticationUsing(
            RedirectIfTwoFactorAuthenticatable::class
        );
    }


    /**
     * ==============================================================
     * AUTENTICACIÓN
     * ==============================================================
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(
            function (Request $request): ?User {
                /*
                |--------------------------------------------------------------------------
                | Normalizar correo
                |--------------------------------------------------------------------------
                */

                $correo = Str::lower(
                    trim(
                        (string) $request->input(
                            Fortify::username()
                        )
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | Contraseña recibida
                |--------------------------------------------------------------------------
                */

                $password = (string) $request->input(
                    'password'
                );


                /*
                |--------------------------------------------------------------------------
                | Buscar usuario
                |--------------------------------------------------------------------------
                */

                $user = User::query()
                    ->where(
                        'correo',
                        $correo
                    )
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Credenciales incorrectas
                |--------------------------------------------------------------------------
                |
                | IMPORTANTE:
                |
                | No lanzamos ValidationException aquí.
                |
                | Devolvemos null para que Fortify:
                |
                | - registre el intento fallido;
                | - incremente el rate limiter;
                | - emita el evento Failed;
                | - genere su error de autenticación.
                |
                */

                if (
                    ! $user
                    ||
                    ! Hash::check(
                        $password,
                        $user->password
                    )
                ) {
                    return null;
                }


                /*
                |--------------------------------------------------------------------------
                | Estado institucional
                |--------------------------------------------------------------------------
                |
                | Llegamos aquí solamente cuando la contraseña
                | ya fue comprobada correctamente.
                |
                */

                if (
                    $user->estado !== 'ACTIVO'
                ) {
                    throw ValidationException::withMessages([
                        Fortify::username() =>
                        'Tu cuenta se encuentra inactiva. '
                            . 'Comunícate con administración.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Usuario autorizado
                |--------------------------------------------------------------------------
                */

                return $user;
            }
        );
    }


    /**
     * ==============================================================
     * RATE LIMITING
     * ==============================================================
     */
    private function configureRateLimiting(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        |
        | 5 intentos por minuto por combinación:
        |
        | correo + dirección IP
        |
        */

        RateLimiter::for(
            'login',

            function (Request $request): Limit {
                $correo = Str::lower(
                    trim(
                        (string) $request->input(
                            Fortify::username()
                        )
                    )
                );


                $ip = $request->ip()
                    ?: 'sin-ip';


                $throttleKey = Str::transliterate(
                    $correo
                        . '|'
                        . $ip
                );


                return Limit::perMinute(5)
                    ->by(
                        $throttleKey
                    );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Challenge 2FA
        |--------------------------------------------------------------------------
        |
        | Limitamos por cuenta en proceso de autenticación.
        |
        | Si por alguna razón la sesión no contiene login.id,
        | usamos la IP como fallback.
        |
        */

        RateLimiter::for(
            'two-factor',

            function (Request $request): Limit {
                $loginId =
                    $request
                    ->session()
                    ->get(
                        'login.id'
                    );


                $identifier = $loginId
                    ? 'usuario|' . $loginId
                    : 'ip|' . (
                        $request->ip()
                        ?: 'sin-ip'
                    );


                return Limit::perMinute(5)
                    ->by(
                        Str::transliterate(
                            $identifier
                        )
                    );
            }
        );
    }
}
