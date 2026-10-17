<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Entities\Conocimiento;

use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento (Conceptos semánticos).
 *
 * Representa un concepto identificable dentro de una versión de la red semántica.
 * Su existencia describe conocimiento; por sí sola no acredita evidencia ni un hallazgo.
 *
 * TÍTULO: Nodo versionado de conocimiento.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de conocimiento.
 *
 * RESPONSABILIDAD:
 * Preservar identidad y significado de un concepto sin inferir aristas.
 *
 * ENTRADAS:
 * ID, versión, código semántico, nombre, tipo documental y definición.
 *
 * SALIDAS:
 * Un nodo inmutable utilizable por una red de la misma versión.
 *
 * RESTRICCIONES:
 * El tipo es documental y explícito; no se crea un catálogo clínico nuevo.
 * Un nodo no acredita evidencia, habilitación ni resultado cognitivo.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R4/R10; grafo versionado D-123.
 */
final readonly class NodoSemantico
{
    /**
     * Valida la identidad y la descripción mínima del nodo.
     *
     * @param string $id Identidad del nodo.
     * @param string $versionConocimiento Versión propietaria.
     * @param string $codigo Código semántico, distinto del ID local.
     * @param string $nombre Nombre documental.
     * @param string $tipo Tipo documental, por ejemplo CRITERIO o VARIABLE.
     * @param string $definicion Significado explícito.
     * @throws DomainException Si falta identidad o significado.
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
