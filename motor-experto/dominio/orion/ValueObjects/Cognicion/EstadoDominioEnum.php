<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Cognicion;

/** Distingue alteración, preservación, desconocimiento, falta de interpretabilidad y contradicción. */
enum EstadoDominioEnum: string
{
    /** A: alteración objetiva sustentada bajo el contrato experimental. */
    case ALTERACION = 'A';

    /** P: desempeño sin alteración objetiva acreditada mediante soporte positivo. */
    case RENDIMIENTO_ESPERADO = 'P';

    /** U: información ausente o todavía no examinada; nunca se equipara a P. */
    case DESCONOCIDO = 'U';

    /** I: condiciones de tarea o evidencia que impiden interpretación válida. */
    case NO_INTERPRETABLE = 'I';

    /** X: evidencia contradictoria pendiente de adjudicación. */
    case CONTRADICTORIO = 'X';

}
