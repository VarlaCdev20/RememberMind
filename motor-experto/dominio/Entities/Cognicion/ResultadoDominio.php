<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Entities\Cognicion;

use MotorExperto\dominio\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\EvaluabilidadEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use DomainException;

/** Conserva el estado declarado de un dominio y sus evidencias. */
final readonly class ResultadoDominio
{
    /** @var array<string, ResultadoSubcomponente> Subresultados por código, sin promedios. */
    public array $subcomponentes;

    /** @var list<EvidenciaCognitiva> Soportes originales seleccionados. */
    public array $evidencias;

    /**
     * Comprueba un estado explícito y sus soportes, sin asignarlo automáticamente.
     * @param DominioCognitivoEnum $dominio
     * @param EstadoDominioEnum $estado
     * @param EvaluabilidadEnum $evaluabilidad
     * @param MemoriaTrabajo $memoria
     * @param list<ResultadoSubcomponente> $subcomponentes
     * @param list<string> $evidenciasIds
     * @param string $justificacion
     * @throws DomainException
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
     * @return list<EvidenciaCognitiva>
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
