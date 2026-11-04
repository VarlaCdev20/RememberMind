<?php

namespace App\Modulos\Cuidados\Aplicacion\Consultas;

use Illuminate\Support\Collection;

/** Proyección de curaciones ya autorizadas; no calcula interpretaciones clínicas. */
final class PrepararEvolucionHerida
{
    public function desde(Collection $curaciones): array
    {
        $filas = $curaciones->sortByDesc(fn ($c) => $c->fecha_hora?->getTimestamp() ?? 0)->map(function ($c) {
            $largo = $this->medida($c->longitud);
            $ancho = $this->medida($c->ancho);

            return [
                'codigo' => $c->getKey(), 'fecha' => $c->fecha_hora?->format('d/m/Y H:i') ?? 'Fecha no registrada',
                'etiqueta' => $c->fecha_hora?->format('d/m/Y') ?? 'Sin fecha',
                'timestamp' => $c->fecha_hora?->toIso8601String(),
                'profesional' => trim(($c->personal?->nombres ?? '').' '.($c->personal?->apellido_paterno ?? '').' '.($c->personal?->apellido_materno ?? '')) ?: 'Profesional no registrado',
                'longitud' => $largo, 'ancho' => $ancho, 'profundidad' => $this->medida($c->profundidad),
                'area' => $largo !== null && $ancho !== null && $c->fecha_hora ? round($largo * $ancho, 2) : null,
                'tejido' => $c->tejido, 'exudado' => $c->exudado, 'olor' => $c->olor, 'dolor' => $c->dolor,
                'procedimiento' => $c->procedimiento, 'materiales' => $c->materiales,
                'respuesta' => $c->respuesta, 'observacion' => $c->observacion,
            ];
        })->values();
        $validas = $filas->whereNotNull('area')->values();
        $ultima = $validas->get(0);
        $anterior = $validas->get(1);
        $inicial = $validas->last();

        return [
            'curaciones' => $filas->all(),
            'serie' => $filas->reverse()->values()->map(fn ($fila) => array_intersect_key($fila, array_flip(['codigo', 'fecha', 'etiqueta', 'timestamp', 'area'])))->all(),
            'resumen' => [
                'ultima' => $ultima['area'] ?? null, 'anterior' => $anterior['area'] ?? null,
                'inicial' => $inicial['area'] ?? null,
                'variacion_desde_inicio' => $validas->count() >= 2 && $inicial['area'] > 0 ? round(($ultima['area'] - $inicial['area']) / $inicial['area'] * 100, 1) : null,
                'variacion' => $anterior && $anterior['area'] > 0 ? round(($ultima['area'] - $anterior['area']) / $anterior['area'] * 100, 1) : null,
                'fecha' => $ultima['fecha'] ?? null,
            ],
        ];
    }

    private function medida(mixed $valor): ?float
    {
        if ($valor === null || $valor === '' || ! is_numeric($valor)) {
            return null;
        }
        $numero = (float) $valor;

        return is_finite($numero) && $numero >= 0 && $numero <= 99999 ? $numero : null;
    }
}
