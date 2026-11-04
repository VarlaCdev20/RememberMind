<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Conocimiento;

use DomainException;

/** Identifica un concepto y su significado dentro de una versión. */
final readonly class NodoSemantico
{
    /**
     * Valida la identidad y la descripción mínima del nodo.
     * @param string $id
     * @param string $versionConocimiento
     * @param string $codigo
     * @param string $nombre
     * @param string $tipo
     * @param string $definicion
     * @throws DomainException
     */
    public function __construct(
        /** Identidad del nodo. */ public string $id,
        /** Versión propietaria. */ public string $versionConocimiento,
        /** Código semántico. */ public string $codigo,
        /** Nombre documental. */ public string $nombre,
        /** Tipo documental no inferido. */ public string $tipo,
        /** Definición del concepto. */ public string $definicion,
    ) {
        foreach ([$id, $versionConocimiento, $codigo, $tipo] as $token) {
            if ($token === '' || preg_match('/[\x00-\x20\x7F]/', $token) === 1) {
                throw new DomainException('Identidad de nodo inválida.');
            }
        }
        if (trim($nombre) === '' || trim($definicion) === '') {
            throw new DomainException('El nodo requiere nombre y definición.');
        }
    }

}
