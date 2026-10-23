<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

/** Prepara una lectura plana de las fuentes ya autorizadas, sin emitir interpretaciones nuevas. */
final class ResumirFuentesCognitivas
{
    public function presentar(?array $capturas, ?array $perfil, ?array $observaciones): array
    {
        $filas = [];
        $aplicaciones = $capturas['capturas'] ?? [];
        foreach ($aplicaciones as $captura) {
            $tipo = match ($captura['instrumento_actual']['tipo'] ?? null) {
                'OBSERVACIONAL' => 'Observación clínica',
                'FUNCIONAL' => 'Aplicación funcional',
                'COGNITIVO' => 'Aplicación cognitiva',
                null => 'Tipo no disponible',
                default => 'Otra aplicación',
            };
            $nombre = $captura['nombre_instrumento'] ?: 'Instrumento sin nombre disponible';
            $version = $captura['version_instrumento'] ?: 'Versión no registrada';
            $clave = hash('sha256', json_encode([$tipo, $nombre, $version], JSON_THROW_ON_ERROR));
            $filas[$clave] ??= ['tipo' => $tipo, 'nombre' => $nombre, 'version' => $version, 'aplicaciones' => 0, 'ultima_fecha' => null];
            $filas[$clave]['aplicaciones']++;
            $fecha = $captura['fecha_hora'] ?? null;
            if (is_string($fecha) && ($filas[$clave]['ultima_fecha'] === null || strcmp($fecha, $filas[$clave]['ultima_fecha']) > 0)) {
                $filas[$clave]['ultima_fecha'] = $fecha;
            }
        }
        $filas = array_values($filas);
        usort($filas, static fn (array $a, array $b): int => strcmp($b['ultima_fecha'] ?? '', $a['ultima_fecha'] ?? ''));

        $seriesComparables = [];
        $generador = new ConsultaCognitivaEnfermeria;
        $capturasParaSeries = ['capturas' => array_values(array_filter($aplicaciones, static fn (array $a): bool =>
            ($a['motivos'] ?? []) === [] && is_array($a['extraccion']['normalizacion']['componentes'] ?? null)
            && isset($a['extraccion']['perfil'], $a['extraccion']['metodo'], $a['extraccion']['normalizacion']['referencia'])))];
        foreach ($perfil['dominios'] ?? [] as $codigo => $dominio) {
            foreach ($generador->series($capturasParaSeries, $codigo, $dominio['componentes'] ?? []) as $serie) {
                if (count(array_unique(array_map(static fn (array $p): string => substr($p['fecha'], 0, 10), $serie['puntos']))) >= 2) {
                    $seriesComparables[] = $serie;
                }
            }
        }
        usort($seriesComparables, static fn (array $a, array $b): int => strcmp(
            $b['puntos'][array_key_last($b['puntos'])]['fecha'],
            $a['puntos'][array_key_last($a['puntos'])]['fecha'],
        ));

        return [
            'filas' => $filas,
            'aplicaciones' => count($aplicaciones),
            'observacionales' => count(array_filter($aplicaciones, static fn (array $a): bool => ($a['instrumento_actual']['tipo'] ?? null) === 'OBSERVACIONAL')),
            'instrumentales' => count(array_filter($aplicaciones, static fn (array $a): bool => ($a['instrumento_actual']['tipo'] ?? null) === 'COGNITIVO')),
            'funcionales' => count(array_filter($aplicaciones, static fn (array $a): bool => ($a['instrumento_actual']['tipo'] ?? null) === 'FUNCIONAL')),
            'controles' => ($observaciones['registros'] ?? null)?->total(),
            'serie_comparable' => $seriesComparables[0] ?? null,
            'series_comparables' => count($seriesComparables),
            'series_comparables_lista' => $seriesComparables,
        ];
    }
}
