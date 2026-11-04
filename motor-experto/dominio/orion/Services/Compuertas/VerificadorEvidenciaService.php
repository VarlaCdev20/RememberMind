<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Services\Compuertas;

use MotorExperto\dominio\orion\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use DomainException;

/** Comprueba procedencia y contexto de cada evidencia; conserva rechazos y dependencias. */
final readonly class VerificadorEvidenciaService
{
    /**
     * Verifica candidatas y conserva cada rechazo sin alterar la memoria.
     * @param MemoriaTrabajo $memoria
     * @param DominioCognitivoEnum $dominio
     * @param list<EvidenciaCognitiva> $candidatas
     * @param list<HechoExperto> $revisionesProcedencia
     * @return array{ admitidas: list<EvidenciaCognitiva>, excluidas: list<array{evidencia: EvidenciaCognitiva, motivos: list<string>}>, dependencias: list<array{primera: EvidenciaCognitiva, segunda: EvidenciaCognitiva, motivos: list<string>}>, procedencias: array<string, array{valor: ?bool, contradictoria: bool, soportes: list<HechoExperto>}> }
     * @throws DomainException
     */
    public function verificar(MemoriaTrabajo $memoria, DominioCognitivoEnum $dominio, array $candidatas, array $revisionesProcedencia = []): array
    {
        if (! array_is_list($candidatas)) {
            throw new DomainException('Las evidencias candidatas deben ser una lista.');
        }
        $conceptos = [];
        foreach ($candidatas as $e) {
            if (! $e instanceof EvidenciaCognitiva) {
                throw new DomainException('Evidencia candidata mal tipada.');
            }
            $e->hecho->exigirContexto($memoria->evaluacionId, $memoria->residenteId, $memoria->versionConocimiento, $memoria->fechaCorte);
            if (($memoria->evidencias[$e->hecho->id] ?? null) !== $e) {
                throw new DomainException('La candidata no es la evidencia original de la memoria.');
            }
            $conceptos[$e->hecho->id] = 'VERIFICACION_PROCEDENCIA:'.$e->hecho->id;
        }
        ksort($conceptos, SORT_STRING);
        $declaraciones = $this->leerDeclaraciones($memoria, $revisionesProcedencia, array_values($conceptos));
        $procedencias = [];
        foreach ($conceptos as $id => $concepto) {
            $procedencias[$id] = $declaraciones[$concepto];
        }
        usort($candidatas, static fn (EvidenciaCognitiva $a, EvidenciaCognitiva $b): int => strcmp($a->hecho->id, $b->hecho->id));
        $admitidas = $excluidas = $vistos = [];
        foreach ($candidatas as $e) {
            $id = $e->hecho->id;
            $motivos = [];
            if (isset($vistos[$id])) {
                $motivos[] = 'EVIDENCIA_DUPLICADA';
            }
            $vistos[$id] = true;
            if ($e->dominio !== $dominio) {
                $motivos[] = 'OTRO_DOMINIO';
            }
            $p = $procedencias[$id];
            if ($p['contradictoria']) {
                $motivos[] = 'PROCEDENCIA_CONTRADICTORIA';
            } elseif ($p['valor'] === null) {
                $motivos[] = 'PROCEDENCIA_DESCONOCIDA';
            } elseif ($p['valor'] === false) {
                $motivos[] = 'PROCEDENCIA_NO_VERIFICADA';
            }
            if ($e->hecho->valor === null || $e->hallazgo === HallazgoInstrumentalEnum::DESCONOCIDO) {
                $motivos[] = 'EVIDENCIA_DESCONOCIDA';
            } elseif ($e->hallazgo === HallazgoInstrumentalEnum::NO_INTERPRETABLE) {
                $motivos[] = 'EVIDENCIA_NO_INTERPRETABLE';
            } elseif ($e->hallazgo === HallazgoInstrumentalEnum::CONTRADICTORIO) {
                $motivos[] = 'CONTRADICCION_PENDIENTE';
            }
            if ($motivos !== []) {
                $excluidas[] = ['evidencia' => $e, 'motivos' => $motivos];
            } else {
                $admitidas[] = $e;
            }
        }
        $dependencias = [];
        foreach ($admitidas as $i => $primera) {
            for ($j = $i + 1; $j < count($admitidas); $j++) {
                $segunda = $admitidas[$j];
                if (! $primera->esIndependienteDe($segunda)) {
                    $motivos = [];
                    if ($primera->hecho->claveOrigen() === $segunda->hecho->claveOrigen()) {
                        $motivos[] = 'ORIGEN_REUTILIZADO';
                    }
                    if ($primera->hecho->episodioId === $segunda->hecho->episodioId) {
                        $motivos[] = 'EPISODIO_REUTILIZADO';
                    }
                    if ($primera->instrumentoId !== null && $primera->instrumentoId === $segunda->instrumentoId) {
                        $motivos[] = 'INSTRUMENTO_REUTILIZADO';
                    }
                    $dependencias[] = ['primera' => $primera, 'segunda' => $segunda, 'motivos' => $motivos];
                }
            }
        }

        return ['admitidas' => $admitidas, 'excluidas' => $excluidas, 'dependencias' => $dependencias, 'procedencias' => $procedencias];
    }

    /**
     * Lee declaraciones booleanas originales sin coerción ni defaults favorables.
     * @param MemoriaTrabajo $memoria
     * @param list<HechoExperto> $hechos
     * @param list<string> $conceptosPermitidos
     * @return array<string, array{valor: ?bool, contradictoria: bool, soportes: list<HechoExperto>}>
     * @throws DomainException
     */
    public function leerDeclaraciones(MemoriaTrabajo $memoria, array $hechos, array $conceptosPermitidos): array
    {
        if (! array_is_list($hechos) || ! array_is_list($conceptosPermitidos)) {
            throw new DomainException('Las declaraciones y sus conceptos deben ser listas.');
        }
        $grupos = [];
        foreach ($conceptosPermitidos as $concepto) {
            if (! is_string($concepto) || $concepto === '' || preg_match('/[\x00-\x20\x7F]/', $concepto) === 1 || isset($grupos[$concepto])) {
                throw new DomainException('Concepto de declaración inválido o duplicado.');
            }
            $grupos[$concepto] = [];
        }
        $vistos = [];
        foreach ($hechos as $hecho) {
            if (! $hecho instanceof HechoExperto) {
                throw new DomainException('Declaración mal tipada.');
            }
            $hecho->exigirContexto($memoria->evaluacionId, $memoria->residenteId, $memoria->versionConocimiento, $memoria->fechaCorte);
            if (($memoria->hechos[$hecho->id] ?? null) !== $hecho || ! array_key_exists($hecho->concepto, $grupos)
                || isset($vistos[$hecho->id]) || ($hecho->valor !== null && ! is_bool($hecho->valor))) {
                throw new DomainException('Declaración ajena, duplicada, desconocida o no booleana.');
            }
            $vistos[$hecho->id] = true;
            $grupos[$hecho->concepto][$hecho->id] = $hecho;
        }
        $resultado = [];
        foreach ($grupos as $concepto => $soportes) {
            ksort($soportes, SORT_STRING);
            $valores = [];
            $incompleta = false;
            foreach ($soportes as $hecho) {
                if ($hecho->valor === null) {
                    $incompleta = true;
                } else {
                    $valores[$hecho->valor ? 1 : 0] = true;
                }
            }
            $contradictoria = count($valores) > 1;
            $valor = $contradictoria || $incompleta || $valores === [] ? null : isset($valores[1]);
            $resultado[$concepto] = ['valor' => $valor, 'contradictoria' => $contradictoria, 'soportes' => array_values($soportes)];
        }

        return $resultado;
    }
}
