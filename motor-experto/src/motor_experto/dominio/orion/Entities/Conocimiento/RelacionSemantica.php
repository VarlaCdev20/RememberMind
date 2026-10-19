<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Conocimiento;

use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento (Relaciones semánticas).
 *
 * Expresa una relación dirigida entre dos conceptos de la misma versión.
 * Preserva el predicado declarado sin introducir conexiones inversas o implícitas.
 *
 * TÍTULO: Arista explícita entre nodos versionados.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de conocimiento.
 *
 * RESPONSABILIDAD:
 * Vincular origen y destino existentes sin mezclar versiones ni inventar enlaces.
 *
 * ENTRADAS:
 * ID, nodos inmutables de origen/destino y predicado tipado.
 *
 * SALIDAS:
 * Una arista dirigida con versión deducida de sus dos extremos coherentes.
 *
 * RESTRICCIONES:
 * No implica simetría, transitividad, causalidad ni habilitación automática.
 * Los predicados provisionales conservan el estado documental de la Fase A.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R4/R10; normalización de predicados D-123.
 */
final readonly class RelacionSemantica
{
    /** Versión común de ambos extremos. */
    public string $versionConocimiento;

    /**
     * Construye una arista dirigida sin inferencias adicionales.
     *
     * @param string $id Identidad de la relación.
     * @param NodoSemantico $origen Nodo original.
     * @param TipoRelacionSemanticaEnum $tipo Significado de la arista.
     * @param NodoSemantico $destino Nodo de destino.
     * @throws DomainException Por ID inválido o extremos de versiones diferentes.
     */
    public function __construct(
        /** Identidad de la arista. */ public string $id,
        /** Nodo de origen. */ public NodoSemantico $origen,
        /** Predicado tipado. */ public TipoRelacionSemanticaEnum $tipo,
        /** Nodo de destino. */ public NodoSemantico $destino,
    ) {
        if ($id === '' || preg_match('/[\x00-\x20\x7F]/', $id) === 1
            || $origen->versionConocimiento !== $destino->versionConocimiento) {
            throw new DomainException('Relación sin identidad o con versiones incompatibles.');
        }
        $this->versionConocimiento = $origen->versionConocimiento;
    }

}
