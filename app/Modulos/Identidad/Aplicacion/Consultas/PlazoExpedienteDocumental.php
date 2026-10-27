<?php

namespace App\Modulos\Identidad\Aplicacion\Consultas;

use App\Models\DestinatarioSolicitudDocumental;
use Illuminate\Support\Collection;

/** Proyección del efecto de una concesión única; no copia ni modifica el acto original. */
final class PlazoExpedienteDocumental
{
    public function preparar(Collection $destinatarios): void
    {
        $altas = $destinatarios->where('origen_plazo', 'ALTA');
        $concesiones = $altas->isEmpty() ? collect() : DestinatarioSolicitudDocumental::query()
            ->where('origen_plazo', 'ALTA')->whereNotNull('fecha_limite_prorrogada')
            ->whereIn('cod_personal', $altas->pluck('cod_personal')->unique())
            ->where(function ($consulta) use ($altas) {
                foreach ($altas->map(fn ($d) => $d->fecha_limite_original->toDateString())->unique() as $fecha) {
                    $consulta->orWhereDate('fecha_limite_original', $fecha);
                }
            })
            ->with('prorrogante.personal')->get()->groupBy(fn ($d) => $this->clave($d));
        foreach ($destinatarios as $destino) {
            $destino->setRelation('concesionesExpediente', $destino->origen_plazo === 'ALTA'
                ? $concesiones->get($this->clave($destino), collect()) : collect());
        }
    }

    public function resolver(DestinatarioSolicitudDocumental $destino): array
    {
        if (! $destino->relationLoaded('concesionesExpediente')) {
            $this->preparar(collect([$destino]));
        }
        $concesiones = $destino->getRelation('concesionesExpediente');
        $conflicto = $concesiones->count() > 1;
        $concesion = $concesiones->count() === 1 ? $concesiones->first() : null;

        return ['limite' => $conflicto ? null : ($concesion?->fecha_limite_prorrogada ?? $destino->fecha_limite_original),
            'concesion' => $concesion, 'conflicto_plazo' => $conflicto];
    }

    private function clave(DestinatarioSolicitudDocumental $destino): string
    {
        return $destino->cod_personal.'|'.$destino->fecha_limite_original->toDateString();
    }
}
