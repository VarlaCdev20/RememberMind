<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Dcl;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Clasificación y explicación (Motivos de abstención).
 *
 * Nombra la condición que impide emitir un patrón candidato o exige adjudicación.
 * La abstención conserva su explicación y permanece separada de los cuatro patrones PET.
 *
 * TÍTULO: Salidas de abstención o no conclusión de Petersen.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Clasificación y explicación experimental.
 *
 * RESPONSABILIDAD:
 * Identificar por qué no puede emitirse un patrón candidato PET.
 *
 * ENTRADAS:
 * Un literal exacto de abstención, no conclusión o compuerta del contrato.
 *
 * SALIDAS:
 * Un motivo tipado separado de los cuatro patrones y de sus futuros soportes.
 *
 * RESTRICCIONES:
 * No establece prioridades de evaluación ni sustituye razones y evidencias
 * detalladas. Las salidas de requisito y adjudicación son no concluyentes,
 * no nuevos subtipos ni diagnósticos de normalidad. El enum no activa código.
 * SOLO_LABORATORIO_NO_EMITIR conserva el cierre de la compuerta asistencial.
 *
 * TRAZABILIDAD:
 * Contrato experimental DCL-PET-20261017-v0.1, secciones 4–6;
 * reglamento maestro R1/R7/R9. Se utiliza REQUIERE_ADJUDICACION_CONTRATO
 * conforme al cierre documental 03A del 17/10/2026, sin incorporar
 * el literal histórico REQUIERE_ADJUDICACION_C02_SIN_SOPORTE.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
enum MotivoAbstencionEnum: string
{
    /** Contradicciones en requisitos, dominios o soportes pendientes de adjudicación. */
    case ABSTENCION_CONTRADICCION = 'ABSTENCION_CONTRADICCION';

    /** Revisión diferencial o estado agudo relevante que impide la interpretación. */
    case ABSTENCION_EVALUACION_DIFERENCIAL = 'ABSTENCION_EVALUACION_DIFERENCIAL';

    /** Requisitos, dominios interpretables o soportes positivos insuficientes. */
    case ABSTENCION_INFORMACION_INSUFICIENTE = 'ABSTENCION_INFORMACION_INSUFICIENTE';

    /** Condición general no acreditada; no niega DCL fuera de este ejercicio. */
    case SIN_PATRON_CLASIFICABLE_POR_REQUISITO = 'SIN_PATRON_CLASIFICABLE_POR_REQUISITO';

    /** Incoherencia contractual entre C02 y el perfil; requiere adjudicación. */
    case REQUIERE_ADJUDICACION_CONTRATO = 'REQUIERE_ADJUDICACION_CONTRATO';

    /** Compuerta asistencial cerrada: solo laboratorio, sin emisión clínica. */
    case SOLO_LABORATORIO_NO_EMITIR = 'SOLO_LABORATORIO_NO_EMITIR';

}
