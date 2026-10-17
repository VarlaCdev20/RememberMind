<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Dcl;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Clasificación experimental (Estados de los requisitos).
 *
 * Expresa si un requisito está acreditado, no acreditado, pendiente o contradictorio.
 * Preserva el estado pendiente para evitar que la falta de información satisfaga el contrato.
 *
 * TÍTULO: Estado documental de un requisito DCL.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Clasificación experimental.
 *
 * RESPONSABILIDAD:
 * Distinguir acreditación positiva, incumplimiento, pendiente y contradicción.
 *
 * ENTRADAS:
 * Un estado textual exacto del requisito en el contrato experimental.
 *
 * SALIDAS:
 * Un estado tipado independiente de la identidad C01–C05 y de A/P/U/I/X.
 *
 * RESTRICCIONES:
 * PENDIENTE no equivale a NO_ACREDITADO ni a ausencia de deterioro.
 * NO_ACREDITADO en C04 significa que la revisión exigida no se cumple,
 * nunca una conclusión etiológica. No acredita soporte por existir el enum.
 * No se convierten automáticamente S/N/U/X/B del código experimental previo:
 * esos son literales técnicos de otro contrato, no los estados documentales.
 *
 * TRAZABILIDAD:
 * Contrato experimental DCL-PET-20261017-v0.1, sección 3, diccionario
 * triestado ampliado; reglamento maestro R1/R7/R9.
 * Se preservan sus cuatro nombres textuales exactos sin aprobación clínica.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
enum EstadoCriterioDclEnum: string
{
    /** Requisito sustentado explícitamente por sus soportes ficticios válidos. */
    case ACREDITADO = 'ACREDITADO';

    /** La condición necesaria no se cumple de forma explícitamente documentada. */
    case NO_ACREDITADO = 'NO_ACREDITADO';

    /** La información o revisión necesaria está ausente o aún pendiente. */
    case PENDIENTE = 'PENDIENTE';

    /** Existen soportes incompatibles pendientes de adjudicación. */
    case CONTRADICTORIO = 'CONTRADICTORIO';

}
