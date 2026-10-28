<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\AplicacionInstrumento;
use Illuminate\Database\Eloquent\Builder;

/** Excluye lotes identificados de desarrollo sin borrar registros ni cambiar su procedencia. */
final class FuentesValoracionOperativa
{
    /** Aísla el lote técnico identificado por documento y marca de admisión exactos. */
    public static function residentes(Builder $query): Builder
    {
        return $query->whereDoesntHave('admisiones', static fn ($q) => $q
            ->where('observacion', 'COHORTE-INFERENCIA-30-V1 · DATOS SINTÉTICOS, USO EXCLUSIVO DE PRUEBAS.')
            ->whereHas('residente', static fn ($r) => $r->whereIn('numero_documento',
                array_map(static fn ($i) => sprintf('COH30-S%02d', $i), range(1, 30)))));
    }

    public static function instrumentoInstitucional(Builder $query): Builder
    {
        foreach (['TEST-%', 'COH30-%', 'SIM-%'] as $patron) {
            $query->whereRaw('UPPER(codigo) NOT LIKE ?', [$patron]);
        }
        foreach (['%TEST%', '%SIMUL%', '%EXPERIMENTAL%'] as $patron) {
            $query->where(fn ($q) => $q->whereNull('version')->orWhereRaw('UPPER(version) NOT LIKE ?', [$patron]));
        }

        return $query;
    }
    public static function limitar(Builder $query): Builder
    {
        foreach (['%[SIM-SE-%', '%[COR-SIM-SE-%', '%[SIM-IADL-%'] as $marca) {
            $query->where(fn ($q) => $q->whereNull('observacion')->orWhereRaw('UPPER(observacion) NOT LIKE ?', [$marca]));
        }

        if ($query->getModel() instanceof AplicacionInstrumento) {
            $query->whereDoesntHave('instrumento', fn ($q) => $q->where('cod_instrumento', 'INST_COMPLETO')->where('version', 'TECNICA_1'));
            $query->whereHas('instrumento', static fn ($q) => self::instrumentoInstitucional($q));
        }

        return $query;
    }
}
