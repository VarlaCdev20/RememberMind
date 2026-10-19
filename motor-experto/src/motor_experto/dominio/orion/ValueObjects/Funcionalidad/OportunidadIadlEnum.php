<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Funcionalidad;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Compuertas (Contexto de oportunidad en IADL).
 *
 * Describe si existió una oportunidad real de realizar una actividad instrumental.
 * Conserva la sustitución institucional y la falta de oportunidad como condiciones de interpretación.
 *
 * TÍTULO: Oportunidad de ejecución de una actividad instrumental.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Compuertas.
 *
 * RESPONSABILIDAD:
 * Conservar si hubo una oportunidad real y adecuada de realizar la IADL.
 *
 * ENTRADAS:
 * El contexto explícito de oportunidad de una actividad concreta.
 *
 * SALIDAS:
 * Un estado técnico separado de desempeño, capacidad previa y causa de ayuda.
 *
 * RESTRICCIONES:
 * Los literales son propuestas internas de la PoC, no un catálogo ratificado.
 * SIN_OPORTUNIDAD y SUSTITUIDA_POR_INSTITUCION no demuestran deterioro;
 * REAL_Y_ADECUADA tampoco prueba independencia. La oportunidad desconocida
 * permanece explícita y no se rellena suponiendo que la actividad fue ofrecida.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R5; Documento Maestro O.R.I.O.N., sección 3.13.2,
 * frontera BADL/IADL y restricción interpretativa del contexto residencial.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
enum OportunidadIadlEnum: string
{
    /** Hubo una oportunidad real, apropiada y documentada para la actividad. */
    case REAL_Y_ADECUADA = 'REAL_Y_ADECUADA';

    /** No se ofreció o no existió una oportunidad adecuada para ejecutarla. */
    case SIN_OPORTUNIDAD = 'SIN_OPORTUNIDAD';

    /** La institución asumió la actividad por su organización o política de cuidado. */
    case SUSTITUIDA_POR_INSTITUCION = 'SUSTITUIDA_POR_INSTITUCION';

    /** No se conoce si hubo una oportunidad de ejecución interpretable. */
    case DESCONOCIDA = 'DESCONOCIDA';

}
