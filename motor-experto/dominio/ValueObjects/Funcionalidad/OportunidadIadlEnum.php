<?php

declare(strict_types=1);

namespace MotorExperto\dominio\ValueObjects\Funcionalidad;

/** Indica si se pudo realizar una actividad; no realizarla no demuestra incapacidad. */
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
