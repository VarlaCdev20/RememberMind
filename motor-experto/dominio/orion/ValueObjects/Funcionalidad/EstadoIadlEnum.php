<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Funcionalidad;

/** Describe el desempeño de una actividad instrumental sin atribuir una causa cognitiva. */
enum EstadoIadlEnum: string
{
    /** Desempeño independiente explícitamente documentado para esta actividad. */
    case INDEPENDIENTE = 'INDEPENDIENTE';

    /** Desempeño con ayuda; su causa física, cognitiva o contextual queda separada. */
    case CON_AYUDA = 'CON_AYUDA';

    /** La actividad no se realizó; no describe por sí misma la capacidad. */
    case NO_REALIZADA = 'NO_REALIZADA';

    /** No existe información suficiente para describir el desempeño. */
    case DESCONOCIDO = 'DESCONOCIDO';

    /** El contexto o la evidencia impiden interpretar el desempeño. */
    case NO_INTERPRETABLE = 'NO_INTERPRETABLE';

    /** Hay descripciones incompatibles que aún no fueron adjudicadas. */
    case CONTRADICTORIO = 'CONTRADICTORIO';

}
