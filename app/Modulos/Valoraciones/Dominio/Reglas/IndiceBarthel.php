<?php

namespace App\Modulos\Valoraciones\Dominio\Reglas;

final class IndiceBarthel
{
    public static function total(array $items): int
    {
        return $items['alimentacion'] + $items['bano'] + $items['aseo_personal']
            + $items['vestido'] + $items['control_intestinal'] + $items['control_vesical']
            + $items['uso_retrete'] + $items['traslados'] + $items['deambulacion'] + $items['escaleras'];
    }

    public static function clasificar(int $total): string
    {
        return match (true) {
            $total >= 100 => 'Independiente',
            $total >= 91 => 'Dependencia escasa',
            $total >= 61 => 'Dependencia leve',
            $total >= 41 => 'Dependencia moderada',
            $total >= 21 => 'Dependencia severa',
            default => 'Dependencia total',
        };
    }
}
