<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Consultas;

use App\Models\Instrumento;
use Illuminate\Database\Eloquent\Builder;

/** Agrupa únicamente los tipos y códigos que el catálogo actual identifica sin ambigüedad. */
final class InstrumentosDelArea
{
    public function catalogados(string $codArea): Builder
    {
        $query = Instrumento::query();

        return match ($codArea) {
            'ARE_COG' => $query->whereIn('tipo', ['COGNITIVO', 'OBSERVACIONAL']),
            'ARE_FUN' => $query->where('tipo', 'FUNCIONAL'),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public function activos(string $codArea): Builder
    {
        return $this->catalogados($codArea)->where('estado', 'ACTIVO');
    }
}
