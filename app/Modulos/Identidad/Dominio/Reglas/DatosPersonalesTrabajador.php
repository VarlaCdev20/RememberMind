<?php

namespace App\Modulos\Identidad\Dominio\Reglas;

final class DatosPersonalesTrabajador
{
    public const MAYUSCULAS = ['nombres', 'ap_paterno', 'ap_materno', 'expedido', 'direccion', 'matricula_prof', 'observaciones', 'numero_documento'];
    public const SOLO_LETRAS = 'regex:~^(?=.*\p{L})[\p{L}\p{M}\s\x{0027}\x{2019}-]+$~u';

    public static function normalizar(array $entrada): array
    {
        foreach (self::MAYUSCULAS as $campo) {
            if (isset($entrada[$campo]) && is_string($entrada[$campo])) {
                $entrada[$campo] = mb_strtoupper(trim($entrada[$campo]));
            }
        }
        return $entrada;
    }

    public static function reglasNombre(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100', self::SOLO_LETRAS],
            'ap_paterno' => ['nullable', 'required_without:ap_materno', 'string', 'max:80', self::SOLO_LETRAS],
            'ap_materno' => ['nullable', 'required_without:ap_paterno', 'string', 'max:80', self::SOLO_LETRAS],
        ];
    }

    /** Incluye los 50 años completos; excluye desde el cumpleaños 51. */
    public static function limitesNacimiento(): array
    {
        return ['min' => now()->startOfDay()->subYearsNoOverflow(51)->addDay()->toDateString(),
            'max' => now()->startOfDay()->subYearsNoOverflow(18)->toDateString()];
    }

    public static function reglaNacimiento(): array
    {
        $limites = self::limitesNacimiento();
        return ['required', 'date_format:Y-m-d', 'after_or_equal:'.$limites['min'], 'before_or_equal:'.$limites['max']];
    }
}
