<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Cognicion;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento (Subcomponentes cognitivos).
 *
 * Identifica los diecinueve subcomponentes y su pertenencia a un dominio primario.
 * Esta correspondencia conserva el alcance de las tareas y no acredita alteración del dominio completo.
 *
 * TÍTULO: Identidad y pertenencia de los 19 subcomponentes.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de conocimiento.
 *
 * RESPONSABILIDAD:
 * Asociar cada código documental a un único dominio primario del perfil.
 *
 * ENTRADAS:
 * Un código MEM-S01…04, ATE-S01…03, EJE-S01…05, LEN-S01…05 o VIS-S01…02.
 *
 * SALIDAS:
 * Un subcomponente tipado y su dominio primario, sin inferencia de rendimiento.
 *
 * RESTRICCIONES:
 * La pertenencia no crea evidencia ni la vuelve independiente; un solapamiento
 * entre tareas no autoriza doble conteo ni alteración de todo un dominio.
 * Los subcomponentes siguen siendo conceptos documentales, no instrumentos
 * aprobados, baremos, permisos de aplicación ni reglas clínicas ejecutables.
 *
 * TRAZABILIDAD:
 * Catálogo O.R.I.O.N. local orion-v1-conceptos.json, consulta 2026-10-08;
 * PaquetesCandidatosCognitivos D-108/D-135 y G09-EXP-20261017 experimental.
 * Reglamento maestro, Fase A, R3, R6 y R8.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
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
     *
     * El mapeo explícito conserva la frontera de EJE-S03 con memoria y evita
     * asignar un mismo subcomponente a dos ejes por semejanza de su nombre.
     *
     * @return DominioCognitivoEnum Dominio documental propietario primario.
     *
     * No recibe parámetros ni lanza excepciones: todos los casos están cubiertos.
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
