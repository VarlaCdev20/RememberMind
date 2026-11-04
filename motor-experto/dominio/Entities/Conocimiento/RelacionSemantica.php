<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Entities\Conocimiento;

use MotorExperto\dominio\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use DomainException;

/** Vincula dos conceptos de la misma versión mediante una relación dirigida. */
final readonly class RelacionSemantica
{
    /** Versión común de ambos extremos. */
    public string $versionConocimiento;

    /**
     * Construye una arista dirigida sin inferencias adicionales.
     * @param string $id
     * @param NodoSemantico $origen
     * @param TipoRelacionSemanticaEnum $tipo
     * @param NodoSemantico $destino
     * @throws DomainException
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
