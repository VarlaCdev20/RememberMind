<?php

declare(strict_types=1);

namespace MotorExperto\dominio\ValueObjects\Conocimiento;

/** Distingue cumplimiento, incumplimiento, desconocimiento y bloqueo de una regla. */
enum EstadoReglaEnum: string
{
    /** La condición o regla quedó sustentada bajo su contrato de evaluación. */
    case CUMPLE = 'CUMPLE';

    /** Rechazo verificado; no se obtiene convirtiendo datos faltantes en false. */
    case NO_CUMPLE = 'NO_CUMPLE';

    /** La información disponible no permite decidir cumplimiento o rechazo. */
    case DESCONOCIDO = 'DESCONOCIDO';

    /** Una compuerta impide evaluar o emitir la consecuencia de la regla. */
    case BLOQUEADA = 'BLOQUEADA';

}
