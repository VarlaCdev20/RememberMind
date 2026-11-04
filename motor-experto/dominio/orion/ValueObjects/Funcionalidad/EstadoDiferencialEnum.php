<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Funcionalidad;

/** Conserva el estado declarado de la revisión diferencial; no determina una causa. */
enum EstadoDiferencialEnum: string
{
    /** Revisión ficticia completada sin impedimento interpretativo pendiente declarado. */
    case COMPLETADO_SIN_IMPEDIMENTO = 'COMPLETADO_SIN_IMPEDIMENTO';

    /** La revisión sigue pendiente o no está documentada suficientemente. */
    case PENDIENTE = 'PENDIENTE';

    /** Existe un impedimento interpretativo declarado que todavía no se resolvió. */
    case IMPEDIMENTO_NO_RESUELTO = 'IMPEDIMENTO_NO_RESUELTO';

    /** Existen declaraciones incompatibles sobre la revisión diferencial. */
    case CONTRADICTORIO = 'CONTRADICTORIO';

}
