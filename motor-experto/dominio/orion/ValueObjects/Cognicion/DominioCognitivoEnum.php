<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Cognicion;

/** Identifica los cinco dominios cognitivos y sus prefijos de evaluabilidad. */
enum DominioCognitivoEnum: string
{
    /** COG-MEM: aprendizaje y memoria; prefijo de evaluabilidad EV-CM. */
    case MEMORIA = 'COG-MEM';

    /** COG-ATE: atención; prefijo de evaluabilidad EV-CA. */
    case ATENCION = 'COG-ATE';

    /** COG-EJE: función ejecutiva; prefijo de evaluabilidad EV-CE. */
    case FUNCION_EJECUTIVA = 'COG-EJE';

    /** COG-LEN: lenguaje; prefijo de evaluabilidad EV-CL. */
    case LENGUAJE = 'COG-LEN';

    /** COG-VIS: función visuoespacial; prefijo de evaluabilidad EV-CV. */
    case VISUOESPACIAL = 'COG-VIS';

    /**
     * Obtiene el prefijo documental de evaluabilidad de este dominio.
     * @return non-empty-string
     */
    public function prefijoEvaluabilidad(): string
    {
        return match ($this) {
            self::MEMORIA => 'EV-CM',
            self::ATENCION => 'EV-CA',
            self::FUNCION_EJECUTIVA => 'EV-CE',
            self::LENGUAJE => 'EV-CL',
            self::VISUOESPACIAL => 'EV-CV',
        };
    }

}
