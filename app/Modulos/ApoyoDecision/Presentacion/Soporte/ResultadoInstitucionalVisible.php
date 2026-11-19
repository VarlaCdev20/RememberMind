<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Soporte;

use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ResultadoInstitucionalConservado as R;

/** Lenguaje clínico de pantalla sin alterar el documento ni los valores originales guardados. */
final class ResultadoInstitucionalVisible
{
    public static function narrativa(string $original): string
    {
        return preg_replace_callback('/^(Limitación de [^:\n]+: |Motivo concurrente de abstención: |Registro excluido en la adquisición: )([A-Z][A-Z0-9_]+(?::[^\n.]+)?)\.$/m',
            fn ($m) => $m[1].MotivoPetersenVisible::explicar($m[2]), $original);
    }

    public static function valor(array $h): string
    {
        $v = $h['valor'];
        if ($v === null) { return 'Sin valor registrado'; }
        if (is_bool($v)) { return $v ? 'Sí' : 'No'; }
        if ($h['concepto'] === 'elemento') { return R::NOMBRES[$v] ?? (string) $v; }
        if ($h['concepto'] === 'estado') { return R::ESTADOS[$v] ?? (string) $v; }
        return (string) $v;
    }
}
