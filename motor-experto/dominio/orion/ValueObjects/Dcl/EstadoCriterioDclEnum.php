<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Dcl;

/** Distingue requisitos cumplidos, incumplidos, pendientes y contradictorios. */
enum EstadoCriterioDclEnum: string
{
    /** Requisito sustentado explícitamente por sus soportes ficticios válidos. */
    case ACREDITADO = 'ACREDITADO';

    /** La condición necesaria no se cumple de forma explícitamente documentada. */
    case NO_ACREDITADO = 'NO_ACREDITADO';

    /** La información o revisión necesaria está ausente o aún pendiente. */
    case PENDIENTE = 'PENDIENTE';

    /** Existen soportes incompatibles pendientes de adjudicación. */
    case CONTRADICTORIO = 'CONTRADICTORIO';

}
