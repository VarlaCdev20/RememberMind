<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Conocimiento;

/** Identifica el significado de una relación explícita entre conceptos. */
enum TipoRelacionSemanticaEnum: string
{
    /** Pertenencia de un concepto a un conjunto o bloque, sin peso implícito. */
    case PARTE_DE = 'PARTE_DE';

    /** Adscripción conceptual de un subcomponente a su dominio. */
    case SUBCOMPONENTE_DE = 'SUBCOMPONENTE_DE';

    /** Aporte documental de evidencia; no acredita por sí solo una conclusión. */
    case EVIDENCIA_DE = 'EVIDENCIA_DE';

    /** Manifestación relacionada con un concepto; no equivale a diagnóstico. */
    case MANIFESTACION_DE = 'MANIFESTACION_DE';

    /** Relación entre una tarea o instrumento y lo que evalúa bajo su contrato. */
    case EVALUA = 'EVALUA';

    /** Comparación explícita que requiere contexto y comparabilidad propios. */
    case COMPARADA_CON = 'COMPARADA_CON';

    /** Contexto que modifica la interpretación, sin corrección numérica implícita. */
    case MODIFICA_INTERPRETACION_DE = 'MODIFICA_INTERPRETACION_DE';

    /** Contexto potencialmente invalidante, sujeto a evaluación independiente. */
    case MODIFICA_O_INVALIDA_INTERPRETACION_DE = 'MODIFICA_O_INVALIDA_INTERPRETACION_DE';

    /** Integración declarada de un elemento en otro, sin agregación automática. */
    case SE_INTEGRA_EN = 'SE_INTEGRA_EN';

    /** Asociación documental sin equivalencia ni causalidad inferidas. */
    case RELACIONADA_CON = 'RELACIONADA_CON';

    /** Distinción conceptual explícita; no constituye negación de un hecho. */
    case NO_EQUIVALE_A = 'NO_EQUIVALE_A';

}
