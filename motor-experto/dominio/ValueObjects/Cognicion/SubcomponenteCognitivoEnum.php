<?php

declare(strict_types=1);

namespace MotorExperto\dominio\ValueObjects\Cognicion;

/** Asocia cada componente cognitivo a su dominio primario. */
enum SubcomponenteCognitivoEnum: string
{
    /** MEM-S01: aprendizaje y adquisición. */
    case MEM_APRENDIZAJE_ADQUISICION = 'MEM-S01';

    /** MEM-S02: recuerdo diferido libre. */
    case MEM_RECUERDO_DIFERIDO_LIBRE = 'MEM-S02';

    /** MEM-S03: recuerdo con claves. */
    case MEM_RECUERDO_CON_CLAVES = 'MEM-S03';

    /** MEM-S04: reconocimiento. */
    case MEM_RECONOCIMIENTO = 'MEM-S04';

    /** ATE-S01: atención sostenida. */
    case ATE_SOSTENIDA = 'ATE-S01';

    /** ATE-S02: atención selectiva. */
    case ATE_SELECTIVA = 'ATE-S02';

    /** ATE-S03: atención dividida. */
    case ATE_DIVIDIDA = 'ATE-S03';

    /** EJE-S01: control inhibitorio e inhibición. */
    case EJE_CONTROL_INHIBITORIO = 'EJE-S01';

    /** EJE-S02: flexibilidad cognitiva y cambio de conjunto mental. */
    case EJE_FLEXIBILIDAD_COGNITIVA = 'EJE-S02';

    /** EJE-S03: actualización y manipulación en memoria de trabajo. */
    case EJE_ACTUALIZACION_MEMORIA_TRABAJO = 'EJE-S03';

    /** EJE-S04: planificación y resolución de problemas. */
    case EJE_PLANIFICACION_RESOLUCION = 'EJE-S04';

    /** EJE-S05: razonamiento y abstracción. */
    case EJE_RAZONAMIENTO_ABSTRACCION = 'EJE-S05';

    /** LEN-S01: denominación y acceso léxico. */
    case LEN_DENOMINACION_ACCESO_LEXICO = 'LEN-S01';

    /** LEN-S02: fluidez verbal y generación léxica. */
    case LEN_FLUIDEZ_VERBAL = 'LEN-S02';

    /** LEN-S03: comprensión del lenguaje. */
    case LEN_COMPRENSION = 'LEN-S03';

    /** LEN-S04: repetición. */
    case LEN_REPETICION = 'LEN-S04';

    /** LEN-S05: producción verbal y discurso. */
    case LEN_PRODUCCION_DISCURSO = 'LEN-S05';

    /** VIS-S01: percepción y relaciones visuoespaciales. */
    case VIS_PERCEPCION_RELACIONES = 'VIS-S01';

    /** VIS-S02: visuoconstrucción. */
    case VIS_VISUOCONSTRUCCION = 'VIS-S02';

    /**
     * Devuelve la adscripción primaria del subcomponente, sin valorar evidencia.
     * @return DominioCognitivoEnum
     */
    public function dominio(): DominioCognitivoEnum
    {
        return match ($this) {
            self::MEM_APRENDIZAJE_ADQUISICION,
            self::MEM_RECUERDO_DIFERIDO_LIBRE,
            self::MEM_RECUERDO_CON_CLAVES,
            self::MEM_RECONOCIMIENTO => DominioCognitivoEnum::MEMORIA,
            self::ATE_SOSTENIDA,
            self::ATE_SELECTIVA,
            self::ATE_DIVIDIDA => DominioCognitivoEnum::ATENCION,
            self::EJE_CONTROL_INHIBITORIO,
            self::EJE_FLEXIBILIDAD_COGNITIVA,
            self::EJE_ACTUALIZACION_MEMORIA_TRABAJO,
            self::EJE_PLANIFICACION_RESOLUCION,
            self::EJE_RAZONAMIENTO_ABSTRACCION => DominioCognitivoEnum::FUNCION_EJECUTIVA,
            self::LEN_DENOMINACION_ACCESO_LEXICO,
            self::LEN_FLUIDEZ_VERBAL,
            self::LEN_COMPRENSION,
            self::LEN_REPETICION,
            self::LEN_PRODUCCION_DISCURSO => DominioCognitivoEnum::LENGUAJE,
            self::VIS_PERCEPCION_RELACIONES,
            self::VIS_VISUOCONSTRUCCION => DominioCognitivoEnum::VISUOESPACIAL,
        };
    }

}
