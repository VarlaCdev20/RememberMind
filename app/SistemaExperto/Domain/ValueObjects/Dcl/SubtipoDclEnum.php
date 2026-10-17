<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Dcl;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento (Nodos de salida).
 *
 * Define el vocabulario de los cuatro patrones candidatos PET-01 a PET-04.
 * Representa la distribución experimental amnésica o no amnésica en escenarios sintéticos.
 * Estos patrones orientativos no constituyen un diagnóstico ni una clasificación etiológica NIA-AA.
 *
 * TÍTULO: Cuatro patrones candidatos de Petersen.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Clasificación experimental.
 *
 * RESPONSABILIDAD:
 * Conservar exclusivamente PET-01, PET-02, PET-03 y PET-04 como patrones.
 *
 * ENTRADAS:
 * Un código PET declarado por una futura clasificación sintética válida.
 *
 * SALIDAS:
 * La identidad inmutable de un patrón candidato de investigación.
 *
 * RESTRICCIONES:
 * No clasifica, no diagnostica DCL y no admite residentes reales.
 * No contiene un quinto subtipo para abstención; las salidas no concluyentes
 * pertenecen a MotivoAbstencionEnum. No expresa severidad, etiología,
 * pronóstico, probabilidad ni puntuación global.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R6/R7/R9; contrato experimental
 * DCL-PET-20261017-v0.1, sección 5. Uso exclusivo con escenarios ficticios
 * y cinco dominios y requisitos interpretables bajo el contrato.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
enum SubtipoDclEnum: string
{
    /** PET-01: patrón candidato amnésico de dominio único. */
    case AMNESICO_DOMINIO_UNICO = 'PET-01';

    /** PET-02: patrón candidato amnésico multidominio. */
    case AMNESICO_MULTIDOMINIO = 'PET-02';

    /** PET-03: patrón candidato no amnésico de dominio único. */
    case NO_AMNESICO_DOMINIO_UNICO = 'PET-03';

    /** PET-04: patrón candidato no amnésico multidominio. */
    case NO_AMNESICO_MULTIDOMINIO = 'PET-04';

}
