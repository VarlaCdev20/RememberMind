<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Entities\Cognicion;

use App\SistemaExperto\Domain\Entities\Hechos\EvidenciaCognitiva;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EstadoDominioEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EvaluabilidadEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Motor de inferencia (Resultados por dominio).
 *
 * Representa el estado declarado de un dominio con su evaluabilidad y soportes.
 * Distingue evidencia suficiente, desconocimiento, falta de interpretabilidad y contradicción.
 *
 * TÍTULO: Resultado de dominio sustentado y no agregado.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Motor de inferencia.
 *
 * RESPONSABILIDAD:
 * Validar un estado declarado del dominio sin deducirlo de tareas aisladas.
 *
 * ENTRADAS:
 * Dominio, A/P/U/I/X, evaluabilidad, memoria, subresultados y soportes seleccionados.
 *
 * SALIDAS:
 * Resultado inmutable con evidencia original y requisitos constructivos explícitos.
 *
 * RESTRICCIONES:
 * A/P exigen evaluación integrada y referencia externa sintética de alcance
 * completo. No se exige línea basal automáticamente ni cantidad clínica fija
 * de instrumentos. El constructor verifica declaraciones; no integra hallazgos.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R2/R3/R4/R6/R8/R9; DCL-PET-20261017-v0.1.
 */
final readonly class ResultadoDominio
{
    /** @var array<string, ResultadoSubcomponente> Subresultados por código, sin promedios. */
    public array $subcomponentes;

    /** @var list<EvidenciaCognitiva> Soportes originales seleccionados. */
    public array $evidencias;

    /**
     * Comprueba un estado explícito y sus soportes, sin asignarlo automáticamente.
     *
     * @param DominioCognitivoEnum $dominio Dominio propietario.
     * @param EstadoDominioEnum $estado Estado declarado.
     * @param EvaluabilidadEnum $evaluabilidad Compuerta declarada por separado.
     * @param MemoriaTrabajo $memoria Única instantánea propietaria.
     * @param list<ResultadoSubcomponente> $subcomponentes Resultados específicos.
     * @param list<string> $evidenciasIds Soportes originales en memoria.
     * @param string $justificacion Razón explícita de interpretación o abstención.
     * @throws DomainException Por alcance, contexto, duplicación o conclusión insuficiente.
     */
    public function __construct(
        /** Dominio propietario. */ public DominioCognitivoEnum $dominio,
        /** Estado explícito del dominio. */ public EstadoDominioEnum $estado,
        /** Suficiencia de evaluación, independiente del estado. */ public EvaluabilidadEnum $evaluabilidad,
        /** Instantánea propietaria. */ public MemoriaTrabajo $memoria,
        array $subcomponentes,
        array $evidenciasIds,
        /** Justificación conservada. */ public string $justificacion,
    ) {
        if (! array_is_list($subcomponentes) || ! array_is_list($evidenciasIds) || trim($justificacion) === '') {
            throw new DomainException('Resultado de dominio sin colecciones o explicación válidas.');
        }
        $partes = [];
        foreach ($subcomponentes as $parte) {
            if (! $parte instanceof ResultadoSubcomponente || $parte->dominio !== $dominio
                || $parte->memoria !== $memoria || isset($partes[$parte->subcomponente->value])) {
                throw new DomainException('Subresultado duplicado, de otro dominio o de otra instantánea.');
            }
            $partes[$parte->subcomponente->value] = $parte;
        }
        $soportes = $ids = $completos = [];
        foreach ($evidenciasIds as $id) {
            if (! is_string($id) || isset($ids[$id])) {
                throw new DomainException('Soporte de dominio mal identificado o duplicado.');
            }
            $e = $memoria->evidencia($id);
            if ($e->dominio !== $dominio) {
                throw new DomainException('Evidencia de otro dominio.');
            }
            $ids[$id] = true;
            $soportes[] = $e;
            if ($e->subcomponente === null) {
                $completos[$e->hallazgo->value] = true;
            }
        }
        $conflicto = isset($completos[HallazgoInstrumentalEnum::CONTRADICTORIO->value])
            || (isset($completos[HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA->value])
                && isset($completos[HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO->value]));
        if (in_array($estado, [EstadoDominioEnum::ALTERACION, EstadoDominioEnum::RENDIMIENTO_ESPERADO], true)) {
            $esperado = $estado === EstadoDominioEnum::ALTERACION
                ? HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA
                : HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO;
            if ($evaluabilidad !== EvaluabilidadEnum::EVALUACION_INTEGRADA || $conflicto
                || isset($completos[HallazgoInstrumentalEnum::NO_INTERPRETABLE->value]) || ! isset($completos[$esperado->value])) {
                throw new DomainException('A/P requiere soporte positivo independiente de alcance completo e integración válida.');
            }
        }
        if ($estado === EstadoDominioEnum::CONTRADICTORIO && ! $conflicto) {
            throw new DomainException('X requiere contradicción de dominio documentada.');
        }
        if ($estado === EstadoDominioEnum::NO_INTERPRETABLE
            && ! isset($completos[HallazgoInstrumentalEnum::NO_INTERPRETABLE->value])) {
            throw new DomainException('I requiere evidencia de dominio no interpretable.');
        }
        $this->subcomponentes = $partes;
        $this->evidencias = $soportes;
    }

    /**
     * Selecciona soportes positivos del estado A/P ya declarado y validado.
     *
     * @return list<EvidenciaCognitiva> Referencias completas; vacío para U/I/X.
     *
     * No recibe parámetros ni lanza excepciones. No calcula el estado.
     */
    public function soportesPositivos(): array
    {
        $hallazgo = match ($this->estado) {
            EstadoDominioEnum::ALTERACION => HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA,
            EstadoDominioEnum::RENDIMIENTO_ESPERADO => HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO,
            default => null,
        };

        return $hallazgo === null ? [] : array_values(array_filter($this->evidencias,
            static fn (EvidenciaCognitiva $e): bool => $e->subcomponente === null && $e->hallazgo === $hallazgo));
    }

}
