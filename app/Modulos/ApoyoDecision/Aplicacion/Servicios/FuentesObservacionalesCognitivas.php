<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Servicios;

use App\Models\PreguntaInstrumento;
use DomainException;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;

/** Identidades naturales exactas del lote cotejado; no clasifica por nombre ni altera catálogos. */
final class FuentesObservacionalesCognitivas
{
    public function vinculos(): array
    {
        $componentes = [];
        foreach (PerfilConceptualCognitivo::CANTIDADES as $dominio => $cantidad) {
            foreach (range(1, $cantidad) as $i) {
                $componentes[] = substr($dominio, 4).'-S0'.$i;
            }
        }
        $preguntas = PreguntaInstrumento::query()->whereIn('codigo', array_map(static fn ($c) => 'ITEM-'.$c, $componentes))
            ->with('instrumento')->get();
        $vinculos = [];
        foreach ($componentes as $componente) {
            $coincidencias = $preguntas->filter(static fn ($q) => $q->codigo === 'ITEM-'.$componente
                && $q->dominio === substr($componente, 0, 3) && $q->instrumento?->codigo === 'TEST-'.$componente
                && $q->instrumento?->version === 'G08-TEST-v1' && $q->instrumento?->tipo === 'OBSERVACIONAL');
            if ($coincidencias->count() > 1) {
                throw new DomainException('La identidad observacional original es ambigua.');
            }
            if ($coincidencias->isEmpty()) {
                continue;
            }
            $q = $coincidencias->sole();
            $vinculos[$componente] = ['cod_instrumento' => $q->cod_instrumento, 'codigo_instrumento' => $q->instrumento->codigo,
                'version_instrumento' => $q->instrumento->version, 'cod_pregunta' => $q->getKey(), 'campo' => 'valor_texto'];
        }

        return $vinculos;
    }
}
