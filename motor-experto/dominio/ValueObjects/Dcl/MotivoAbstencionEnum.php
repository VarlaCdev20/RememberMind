<?php

declare(strict_types=1);

namespace MotorExperto\dominio\ValueObjects\Dcl;

/** Identifica por qué no se puede emitir un patrón orientativo. */
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
