<?php

namespace App\Backend\Modulos\SistemaExperto\Resolucion;

use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use DomainException;

final class ResolvedorSoportesCOGMEM
{
    /** D-131, exclusivamente para COG-MEM y después de EV-CM-2. */
    public function resolver(PaqueteConocimiento $p, MemoriaTrabajo $memoria, string $criterio, array $compuerta, array $reglas): ?array
    {
        if ($compuerta['estado'] !== 'EV-CM-2') {
            return null;
        }
        if ($p->red->nodo($criterio)['codigo_semantico'] !== 'COG-MEM') {
            throw new DomainException('D-131 no se aplica a otro criterio por analogía.');
        }
        $dominio = $p->dominioCriterio($criterio);
        $soportes = ['DIFICULTAD_EVIDENCIADA' => [], 'SIN_DIFICULTAD_EVIDENCIADA' => []];
        foreach ($reglas as $r) {
            if ($r['estado'] !== 'CUMPLE') {
                continue;
            }
            $c = $r['consecuencia'];
            $v = $p->fila('valores_semanticos', $c['cod_valor_semantico']);
            if ($c['cod_criterio_dominio_resultado'] !== $dominio['cod_criterio_dominio_resultado'] || ! array_key_exists($v['codigo_valor'], $soportes)) {
                throw new DomainException('Consecuencia fuera del contrato D-131.');
            }
            foreach ($r['condiciones'] as $condicion) {
                if ($condicion['estado'] !== 'CUMPLE' || ! $condicion['soportes']) {
                    throw new DomainException('Soporte de regla inconsistente.');
                }
                $soportes[$v['codigo_valor']] = array_unique([...$soportes[$v['codigo_valor']], ...$condicion['soportes']]);
            }
        }
        [$dificultad, $sin] = array_values($soportes);
        if (! $dificultad && ! $sin) {
            throw new DomainException('EV-CM-2 sin soporte: inconsistencia de cobertura/integridad.');
        }
        if ($dificultad && $sin) {
            $dEfectivos = $memoria->soportesEfectivos($dificultad);
            $sEfectivos = $memoria->soportesEfectivos($sin);
            if ($dEfectivos === $sEfectivos) {
                throw new DomainException('Soportes opuestos no independientes; no fabricar hallazgos mixtos.');
            }
            $codigo = 'HALLAZGOS_MIXTOS';
        } else {
            $codigo = $dificultad ? 'DIFICULTAD_EVIDENCIADA' : 'SIN_DIFICULTAD_EVIDENCIADA';
        }
        $valores = array_values(array_filter($p->tabla('valores_semanticos'), fn ($v) => $v['cod_dominio_valores'] === $dominio['cod_dominio_valores']
            && $v['codigo_valor'] === $codigo && $v['estado'] === 'ACTIVO' && $v['estado_aprobacion'] === 'APROBADO'));
        if (count($valores) !== 1) {
            throw new DomainException('Salida semántica no autorizada o ambigua.');
        }
        foreach ($soportes as &$ids) {
            sort($ids, SORT_STRING);
        }
        unset($ids);

        return ['criterio' => $criterio, 'valor' => $valores[0]['cod_valor_semantico'], 'codigo' => $codigo, 'soportes' => $soportes];
    }
}
