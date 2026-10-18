<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\AplicacionInstrumento;
use Illuminate\Database\Eloquent\Builder;

/** Excluye lotes identificados de desarrollo sin borrar registros ni cambiar su procedencia. */
final class FuentesValoracionOperativa
{
    public static function limitar(Builder $query): Builder
    {
        foreach (['%[SIM-SE-%', '%[COR-SIM-SE-%', '%[SIM-IADL-%'] as $marca) {
            $query->where(fn ($q) => $q->whereNull('observacion')->orWhereRaw('UPPER(observacion) NOT LIKE ?', [$marca]));
        }

        if ($query->getModel() instanceof AplicacionInstrumento) {
            $query->whereDoesntHave('instrumento', fn ($q) => $q->where('cod_instrumento', 'INST_COMPLETO')->where('version', 'TECNICA_1'));
        }

        return $query;
    }
}
