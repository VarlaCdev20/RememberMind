<?php

declare(strict_types=1);

namespace App\Compartido\Dominio\Calendario;

use InvalidArgumentException;

/** Selección de días ISO; conserva la diferencia histórica entre vacío y sin coincidencias. */
final class SeleccionSemanal
{
    public const TODOS = 'SEM_TODOS';

    private const ALIAS = [
        1 => ['1', 'LUN', 'LUNES', 'MONDAY'], 2 => ['2', 'MAR', 'MARTES', 'TUESDAY'],
        3 => ['3', 'MIE', 'MIERCOLES', 'WEDNESDAY'], 4 => ['4', 'JUE', 'JUEVES', 'THURSDAY'],
        5 => ['5', 'VIE', 'VIERNES', 'FRIDAY'], 6 => ['6', 'SAB', 'SABADO', 'SATURDAY'],
        7 => ['7', 'DOM', 'DOMINGO', 'SUNDAY'],
    ];

    public static function codigo(?string $texto, bool $medicacion): string
    {
        if ($texto === null || $texto === '' || $texto === '0') {
            return self::TODOS;
        }
        $normal = strtr(mb_strtoupper(trim($texto)), ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);
        if ($normal === 'TODOS') {
            return $medicacion ? self::TODOS : self::identidad([]);
        }
        if ($normal === 'NINGUNO') {
            return self::identidad([]);
        }
        if ($normal === '') {
            throw new InvalidArgumentException('La selección contiene únicamente espacios; indique los días o deje el campo vacío.');
        }
        $dias = [];
        foreach (preg_split('/[,|\s;\-\/]+/', $normal, -1, PREG_SPLIT_NO_EMPTY) as $token) {
            if ($token === 'TODOS') {
                continue;
            }
            $encontrado = false;
            foreach (self::ALIAS as $dia => $alias) {
                if (in_array($token, $alias, true)) {
                    $dias[] = $dia;
                    $encontrado = true;
                    break;
                }
            }
            if (! $encontrado) {
                throw new InvalidArgumentException('La selección contiene un día no reconocido.');
            }
        }

        return self::identidad($dias);
    }

    /** Identidad canónica del conjunto; la consulta de días usa la relación, no interpreta el código. */
    public static function identidad(array $dias): string
    {
        $mascara = 0;
        foreach ($dias as $dia) {
            if (! is_int($dia) || $dia < 1 || $dia > 7) {
                throw new InvalidArgumentException('El día ISO debe estar entre 1 y 7.');
            }
            $mascara |= 1 << ($dia - 1);
        }

        return sprintf('SEM_%03d', $mascara);
    }

    public static function preparar(string $tabla, array $datos): array
    {
        if (in_array($tabla, ['horarios_prescripcion', 'programaciones_cuidado'], true) && array_key_exists('dias_semana', $datos)) {
            $datos['cod_patron_semanal'] = self::codigo($datos['dias_semana'], $tabla === 'horarios_prescripcion');
            unset($datos['dias_semana']);
        }

        return $datos;
    }
}
