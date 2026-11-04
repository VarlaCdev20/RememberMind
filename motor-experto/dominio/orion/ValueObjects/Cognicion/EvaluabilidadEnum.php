<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Cognicion;

/** Indica si hay información suficiente para evaluar, separada del resultado cognitivo. */
enum EvaluabilidadEnum: int
{
    /** Nivel 0: no evaluable o información insuficiente; no implica normalidad. */
    case NO_EVALUABLE = 0;

    /** Nivel 1: pre-evaluación o señal que todavía requiere ampliación. */
    case PRE_EVALUACION_SENAL = 1;

    /** Nivel 2: evaluación integrada; el resultado cognitivo permanece separado. */
    case EVALUACION_INTEGRADA = 2;

    /**
     * Compone el código de evaluabilidad con el prefijo propio del dominio.
     * @param DominioCognitivoEnum $dominio
     * @return non-empty-string
     */
    public function codigo(DominioCognitivoEnum $dominio): string
    {
        return $dominio->prefijoEvaluabilidad().'-'.$this->value;
    }
}
