<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Cognicion;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Motor de inferencia (Estados del perfil cognitivo).
 *
 * Nombra los estados A/P/U/I/X del contrato experimental de Petersen.
 * Mantiene separados el rendimiento sustentado, el desconocimiento y los impedimentos de interpretación.
 *
 * TÍTULO: Estados del perfil cognitivo de Petersen.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Clasificación experimental.
 *
 * RESPONSABILIDAD:
 * Conservar por separado A, P, U, I y X en cada uno de los cinco dominios.
 *
 * ENTRADAS:
 * Un estado explícito del contrato experimental DCL, sin equivalencias implícitas.
 *
 * SALIDAS:
 * Un descriptor de dominio inmutable, válido únicamente para simulación ficticia.
 *
 * RESTRICCIONES:
 * A/P requieren evidencia positiva específica y no nacen del nivel EV-*-2,
 * de una regla G09 cumplida/fallida, de null, false o de listas vacías.
 * U/I/X interrumpen la clasificación futura. El enum solo nombra estados:
 * no acredita soportes, no integra pruebas ni clasifica personas reales.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R2/R6/R7/R8/R9; contrato experimental
 * DCL-PET-20261017-v0.1, sección 3. A/P son descriptores de una evaluación
 * y versión, no diagnósticos ni etiquetas permanentes.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
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
