<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Funcionalidad;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de hechos (Desempeño en IADL).
 *
 * Describe cómo se realizó una actividad instrumental o por qué su resultado permanece incierto.
 * Necesitar ayuda o no realizarla requiere contexto antes de atribuir una limitación cognitiva.
 *
 * TÍTULO: Desempeño declarado de una actividad instrumental.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de hechos.
 *
 * RESPONSABILIDAD:
 * Nombrar el desempeño de una IADL sin atribuir automáticamente causa cognitiva.
 *
 * ENTRADAS:
 * Una descripción explícita de desempeño, ausencia de ejecución o incertidumbre.
 *
 * SALIDAS:
 * Un estado técnico de actividad, independiente de oportunidad y causa de ayuda.
 *
 * RESTRICCIONES:
 * Todos los literales son propuestas internas de la PoC, no códigos clínicos
 * oficiales. CON_AYUDA no significa dependencia cognitiva; NO_REALIZADA no
 * significa incapacidad. Capacidad motora, nivel previo y rutina institucional
 * deberán examinarse por separado; este enum no acredita DCL-C03.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R5; Documento Maestro O.R.I.O.N. sección 3.13.2;
 * contrato experimental DCL-PET-20261017-v0.1, secciones 2.2 y 2.4.
 * No se extraen IADL complejas desde Barthel/Katz ni desde campos no equivalentes.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
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
