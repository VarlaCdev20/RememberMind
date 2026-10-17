<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Cognicion;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de hechos (Descriptores de hallazgos).
 *
 * Diferencia observaciones descriptivas, referencias sintéticas e incertidumbre de la evidencia.
 * Una alerta instrumental conserva su alcance y no se convierte automáticamente en alteración de un dominio.
 *
 * TÍTULO: Naturaleza de un hallazgo instrumental experimental.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de hechos.
 *
 * RESPONSABILIDAD:
 * Distinguir un hallazgo descriptivo de una interpretación externa sintética,
 * conservando desconocimiento, falta de interpretabilidad y contradicción.
 *
 * ENTRADAS:
 * Un literal explícito declarado en una evidencia ficticia.
 *
 * SALIDAS:
 * Un descriptor tipado del hallazgo; nunca el estado integrado de un dominio.
 *
 * RESTRICCIONES:
 * Vocabulario propuesto internamente para la PoC; no es un catálogo clínico.
 * Las interpretaciones SIMULADAS requieren soportes y revisión ficticios
 * verificados por el contrato de evidencia. No se interpretan puntuaciones ni baremos.
 * No hay conversión automática desde una alerta G09 a ALTERACION/PRESERVACION,
 * ni desde ausencia de hallazgo a rendimiento esperado.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R8/R9. ALTERACION_OBJETIVA_SIMULADA y
 * RENDIMIENTO_ESPERADO_SIMULADO conservan los literales del puente experimental
 * PTE-DCL-G08-TEST-01 consultado en feature/orion-g09-experimental.
 * Los demás literales son propuestas internas de la PoC, sin ratificación.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
enum HallazgoInstrumentalEnum: string
{
    /** Hallazgo descriptivo aislado; no acredita por sí mismo un estado A/P. */
    case HALLAZGO_DESCRIPTIVO = 'HALLAZGO_DESCRIPTIVO';

    /** Referencia externa ficticia de alteración; no es diagnóstico ni resultado global. */
    case ALTERACION_OBJETIVA_SIMULADA = 'ALTERACION_OBJETIVA_SIMULADA';

    /** Referencia externa ficticia positiva de rendimiento esperado, no falta de hallazgo. */
    case RENDIMIENTO_ESPERADO_SIMULADO = 'RENDIMIENTO_ESPERADO_SIMULADO';

    /** No hay información que permita caracterizar el hallazgo. */
    case DESCONOCIDO = 'DESCONOCIDO';

    /** Existe una entrada, pero sus condiciones impiden interpretarla. */
    case NO_INTERPRETABLE = 'NO_INTERPRETABLE';

    /** Se conservan interpretaciones incompatibles pendientes de adjudicación. */
    case CONTRADICTORIO = 'CONTRADICTORIO';

}
