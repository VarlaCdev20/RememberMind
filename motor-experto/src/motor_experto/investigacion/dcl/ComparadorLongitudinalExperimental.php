<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

use DateTimeImmutable;

/** Comparabilidad de observaciones ficticias; no interpreta magnitud, progresión, gravedad ni ventanas clínicas. */
final class ComparadorLongitudinalExperimental
{
    public function comparar(?array $anterior, ?array $actual, ?DateTimeImmutable $corte = null): array
    {
        $base = ['anterior' => $anterior, 'actual' => $actual, 'diferencia_interpretada' => null];
        if ($anterior === null || $actual === null) {
            return $base + ['estado' => 'COMPARACION_NO_DISPONIBLE', 'motivos' => ['FALTA_UNA_OBSERVACION']];
        }
        $motivos = [];
        foreach (['caso', 'componente', 'instrumento', 'edicion', 'norma', 'idioma', 'poblacion', 'version_mapeo', 'tipo_valor'] as $campo) {
            if (! is_string($anterior[$campo] ?? null) || $anterior[$campo] === ''
                || ($anterior[$campo] ?? null) !== ($actual[$campo] ?? null)) {
                $motivos[] = 'NO_EQUIVALENTE_'.strtoupper($campo);
            }
        }
        foreach ([$anterior, $actual] as $observacion) {
            if (! is_string($observacion['caso'] ?? null) || ! str_starts_with($observacion['caso'], 'SINTETICO-') || ($observacion['uso'] ?? null) !== 'TEST_ONLY') {
                $motivos[] = 'FUERA_DEL_LABORATORIO';
            }
            if (($observacion['oportunidad_verificada'] ?? null) !== true) {
                $motivos[] = 'OPORTUNIDAD_NO_VERIFICADA';
            }
            if (($observacion['estado'] ?? null) !== 'VIGENTE') {
                $motivos[] = 'FUENTE_NO_UTILIZABLE';
            }
            if (! array_key_exists('valor', $observacion) || $observacion['valor'] === null) {
                $motivos[] = 'VALOR_NO_DISPONIBLE';
            }
            if (! in_array($observacion['tipo_valor'] ?? null, ['string', 'int', 'float', 'bool'], true)
                || get_debug_type($observacion['valor'] ?? null) !== ($observacion['tipo_valor'] ?? null)) {
                $motivos[] = 'TIPO_BRUTO_INCOMPATIBLE';
            }
            if (! is_string($observacion['episodio'] ?? null) || $observacion['episodio'] === '') {
                $motivos[] = 'EPISODIO_NO_IDENTIFICABLE';
            }
        }
        $fechas = [];
        foreach ([$anterior, $actual] as $observacion) {
            $fecha = $observacion['fecha'] ?? null;
            $f = is_string($fecha) ? DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:sP', $fecha) : false;
            if (! $f || $f->format('Y-m-d\TH:i:sP') !== $fecha) {
                $motivos[] = 'FECHA_NO_VERIFICABLE';
            }
            if ($f && $corte && $f > $corte) {
                $motivos[] = 'FUENTE_POSTERIOR_AL_CORTE';
            }
            $fechas[] = $f;
        }
        if ($fechas[0] && $fechas[1] && $fechas[0] >= $fechas[1]) {
            $motivos[] = 'ORDEN_TEMPORAL_INVALIDO';
        }
        if (($anterior['episodio'] ?? null) === ($actual['episodio'] ?? null)) {
            $motivos[] = 'MISMO_EPISODIO';
        }
        if ($motivos) {
            return $base + ['estado' => 'COMPARACION_NO_VALIDA', 'motivos' => array_values(array_unique($motivos))];
        }

        return $base + ['estado' => $anterior['valor'] === $actual['valor'] ? 'INFORMACION_COMPARABLE' : 'CAMBIO_SENALADO_NO_CORROBORADO',
            'motivos' => ['Solo se comparan valores brutos equivalentes; no se acredita cambio cognitivo objetivo.']];
    }
}
