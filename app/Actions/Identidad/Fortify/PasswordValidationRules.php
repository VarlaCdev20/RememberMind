<?php

namespace App\Actions\Identidad\Fortify;

use Closure;
use Illuminate\Contracts\Validation\Rule;

trait PasswordValidationRules
{
    /**
     * Política institucional de contraseñas de RememberMind.
     *
     * Este método es público porque la interfaz utiliza
     * exactamente estos mismos parámetros para mostrar
     * la validación en tiempo real.
     *
     * @return array{
     *     min: int,
     *     max: int,
     *     mayuscula: bool,
     *     minuscula: bool,
     *     numero: bool,
     *     simbolo: bool
     * }
     */
    public static function passwordPolicy(): array
    {
        return [
            'min' => 12,
            'max' => 64,

            'mayuscula' => true,
            'minuscula' => true,
            'numero' => true,
            'simbolo' => true,
        ];
    }

    /**
     * Reglas institucionales de contraseña.
     *
     * Todos los flujos que utilicen este trait
     * deben respetar la misma política.
     *
     * @return array<int, Rule|Closure|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        $politica = static::passwordPolicy();

        return [
            /*
            |--------------------------------------------------------------------------
            | Presencia y tipo
            |--------------------------------------------------------------------------
            */

            'required',
            'string',


            /*
            |--------------------------------------------------------------------------
            | Longitud
            |--------------------------------------------------------------------------
            */

            'min:' . $politica['min'],
            'max:' . $politica['max'],


            /*
            |--------------------------------------------------------------------------
            | Composición
            |--------------------------------------------------------------------------
            */

            function (
                string $attribute,
                mixed $value,
                Closure $fail
            ) use ($politica): void {
                $password = (string) $value;


                /*
                 * Al menos una mayúscula.
                 *
                 * \p{Lu} permite caracteres Unicode:
                 * A-Z, Á, É, Í, Ó, Ú, Ñ, etc.
                 */
                if (
                    $politica['mayuscula']
                    && ! preg_match(
                        '/\p{Lu}/u',
                        $password
                    )
                ) {
                    $fail(
                        'La contraseña debe contener al menos una letra mayúscula.'
                    );
                }


                /*
                 * Al menos una minúscula.
                 */
                if (
                    $politica['minuscula']
                    && ! preg_match(
                        '/\p{Ll}/u',
                        $password
                    )
                ) {
                    $fail(
                        'La contraseña debe contener al menos una letra minúscula.'
                    );
                }


                /*
                 * Al menos un número.
                 */
                if (
                    $politica['numero']
                    && ! preg_match(
                        '/\p{N}/u',
                        $password
                    )
                ) {
                    $fail(
                        'La contraseña debe contener al menos un número.'
                    );
                }


                /*
                 * Al menos un símbolo.
                 *
                 * Consideramos símbolo cualquier carácter
                 * distinto de:
                 *
                 * - letras;
                 * - números;
                 * - espacios.
                 *
                 * Ejemplos válidos:
                 * ! @ # $ % & * ? _ - + =
                 */
                if (
                    $politica['simbolo']
                    && ! preg_match(
                        '/[^\p{L}\p{N}\s]/u',
                        $password
                    )
                ) {
                    $fail(
                        'La contraseña debe contener al menos un símbolo.'
                    );
                }
            },


            /*
            |--------------------------------------------------------------------------
            | Confirmación
            |--------------------------------------------------------------------------
            |
            | Laravel compara automáticamente:
            |
            | password
            | password_confirmation
            |
            */

            'confirmed',
        ];
    }
}
