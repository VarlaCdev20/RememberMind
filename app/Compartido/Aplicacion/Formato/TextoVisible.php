<?php

namespace App\Compartido\Aplicacion\Formato;

/** Presentación únicamente: no modifica las referencias ni el texto persistido. */
final class TextoVisible
{
    public static function sinCodigosInternos(?string $texto): string
    {
        $texto = preg_replace('/\[heridas:[A-Za-z0-9_-]+\]\h*/u', '', $texto ?? '');

        $codigo = '\bCOD_[A-Z][A-Z0-9]*(?:_[A-Z0-9]+)+\b';
        $texto = preg_replace('/\h*\('.$codigo.'\)/u', '', $texto);

        return trim(preg_replace('/'.$codigo.'\h*/u', '', $texto));
    }
}
