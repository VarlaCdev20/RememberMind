<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Conocimiento;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Motor de inferencia (Estados de evaluación).
 *
 * Distingue una regla cumplida, no cumplida, desconocida o bloqueada.
 * Conserva la incertidumbre sin convertir el incumplimiento en evidencia de normalidad.
 *
 * TÍTULO: Estado de evaluación de una regla.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Motor de inferencia.
 *
 * RESPONSABILIDAD:
 * Separar cumplimiento, rechazo verificado, desconocimiento y bloqueo.
 *
 * ENTRADAS:
 * El resultado explícito de una evaluación futura o de una compuerta.
 *
 * SALIDAS:
 * Un estado tipado de evaluación, independiente de la habilitación de la regla.
 *
 * RESTRICCIONES:
 * NO_CUMPLE no significa que el hecho contrario sea verdadero ni prueba
 * preservación cognitiva. La falta de datos debe conservar DESCONOCIDO.
 * BLOQUEADA no atribuye un resultado cognitivo ni sustituye una autorización.
 * El enum no evalúa condiciones ni modifica el motor vigente.
 *
 * TRAZABILIDAD:
 * CUMPLE y NO_CUMPLE conservan los literales del piloto D-130.
 * DESCONOCIDO y BLOQUEADA son propuestas internas de la PoC para R1/R8.
 * La semántica de ausencia del nuevo Domain sigue el reglamento maestro;
 * no se traslada el rechazo existencial del piloto a una conclusión clínica.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
enum EstadoReglaEnum: string
{
    /** La condición o regla quedó sustentada bajo su contrato de evaluación. */
    case CUMPLE = 'CUMPLE';

    /** Rechazo verificado; no se obtiene convirtiendo datos faltantes en false. */
    case NO_CUMPLE = 'NO_CUMPLE';

    /** La información disponible no permite decidir cumplimiento o rechazo. */
    case DESCONOCIDO = 'DESCONOCIDO';

    /** Una compuerta impide evaluar o emitir la consecuencia de la regla. */
    case BLOQUEADA = 'BLOQUEADA';

}
