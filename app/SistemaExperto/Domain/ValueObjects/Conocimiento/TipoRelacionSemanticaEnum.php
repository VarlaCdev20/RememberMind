<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Conocimiento;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento (Vocabulario de relaciones).
 *
 * Nombra el vínculo explícito entre dos conceptos de la red semántica.
 * La selección de predicados es una propuesta de la PoC; no define conexiones nuevas.
 *
 * TÍTULO: Predicados documentales de la red semántica.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de conocimiento.
 *
 * RESPONSABILIDAD:
 * Identificar el significado de una arista sin crear conexiones implícitas.
 *
 * ENTRADAS:
 * Literales documentados en O.R.I.O.N.; no se aceptan sinónimos automáticos.
 *
 * SALIDAS:
 * Un predicado tipado para el contrato de relaciones versionado.
 *
 * RESTRICCIONES:
 * El catálogo definitivo sigue pendiente según D-123: esta selección es una
 * propuesta interna de la PoC, no un catálogo clínico ratificado. No autoriza
 * transitividad, inversión, causalidad ni participación inferencial automática.
 * CANDIDATA es estado de una relación, no parte del nombre del predicado.
 *
 * TRAZABILIDAD:
 * Documento Maestro O.R.I.O.N., D-123: normalización de predicados y familias
 * documentadas. Catálogo local orion-v1-conceptos.json, consulta 2026-10-08.
 * No se altera el grafo ni la persistencia del motor vigente.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
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
