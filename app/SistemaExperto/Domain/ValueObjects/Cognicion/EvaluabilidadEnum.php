<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Cognicion;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Compuertas (Suficiencia para evaluar).
 *
 * Representa los niveles de no evaluable, pre-evaluación/señal y evaluación integrada.
 * Expresa la posibilidad de interpretar evidencia, separada del estado cognitivo resultante.
 *
 * TÍTULO: Niveles documentales de evaluabilidad cognitiva.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Compuertas.
 *
 * RESPONSABILIDAD:
 * Representar suficiencia para evaluar, separada del resultado cognitivo.
 *
 * ENTRADAS:
 * Nivel explícito 0, 1 o 2 y dominio tipado al solicitar su código compuesto.
 *
 * SALIDAS:
 * Un nivel inmutable y códigos EV-CM/CA/CE/CL/CV con sufijo 0, 1 o 2.
 *
 * RESTRICCIONES:
 * El entero es un nivel de compuerta, no puntuación, severidad ni promedio.
 * PRE_EVALUACION_SENAL no significa falta de referencia basal.
 * EVALUACION_INTEGRADA no exige automáticamente una línea basal previa,
 * no equivale a alteración y no asigna A/P. Los requisitos de evidencia
 * pertenecen al evaluador y al contrato específico versionado.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase A, R1 y R2; Documento Maestro, D-122 para COG-MEM
 * y estados conceptuales específicos de los demás dominios. Su representación
 * no declara aprobada la ejecución clínica de las cuatro formalizaciones.
 *
 * name identifica el caso; value conserva su nivel entero. from() rechaza un
 * nivel ajeno mediante ValueError; tryFrom() devuelve null sin convertirlo en
 * NO_EVALUABLE ni en rendimiento esperado. No se admiten defaults inferenciales.
 */
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
     *
     * Solo representa un nivel ya declarado; no comprueba su suficiencia.
     *
     * @param DominioCognitivoEnum $dominio Eje propietario de la evaluabilidad.
     * @return non-empty-string Código documental completo, por ejemplo EV-CA-1.
     *
     * No lanza excepciones para los argumentos tipados admitidos.
     */
    public function codigo(DominioCognitivoEnum $dominio): string
    {
        return $dominio->prefijoEvaluabilidad().'-'.$this->value;
    }
}
