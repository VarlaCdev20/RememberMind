<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Funcionalidad;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Compuertas (Revisión diferencial).
 *
 * Conserva el estado explícito de la revisión diferencial en un escenario ficticio.
 * Permite mantener visible un impedimento antes de declarar un patrón candidato.
 *
 * TÍTULO: Estado de la revisión diferencial experimental.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Compuertas.
 *
 * RESPONSABILIDAD:
 * Describir el estado de una revisión diferencial ficticia sin realizarla.
 *
 * ENTRADAS:
 * Una declaración explícita y trazable de revisión en un escenario sintético.
 *
 * SALIDAS:
 * Un estado técnico que la compuerta contrasta con DCL-C04.
 *
 * RESTRICCIONES:
 * Vocabulario propuesto internamente para la PoC; no es un catálogo clínico.
 * COMPLETADO_SIN_IMPEDIMENTO no diagnostica ni descarta etiologías.
 * Un registro ausente no acredita revisión completa. No se examinan
 * medicación, enfermedades o estados agudos automáticamente en este enum.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R7/R9; contrato experimental DCL-PET-20261017-v0.1,
 * secciones 2.2 y 4: DCL-C04 y abstención por diferencial no resuelto.
 * Ningún literal autoriza revisión o clasificación asistencial.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
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
