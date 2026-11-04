<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Dominio\Revision;

use DomainException;

/** Comprueba una sola cadena completa; una cabeza aislada no oculta ciclos desconectados. */
final class CadenaRevisiones
{
    /** @param array<string, string|null> $anteriores ID original => predecesora original. */
    public static function cabeza(array $anteriores): ?string
    {
        if ($anteriores === []) {
            return null;
        }
        $referidas = array_values(array_filter($anteriores, static fn ($id) => $id !== null));
        $cabezas = array_values(array_diff(array_keys($anteriores), $referidas));
        if (count($cabezas) !== 1 || count($referidas) !== count(array_unique($referidas))) {
            throw new DomainException('La cadena de revisiones no conserva una cabeza única.');
        }
        $id = $cabezas[0];
        $vistos = [];
        while ($id !== null) {
            if (isset($vistos[$id]) || ! array_key_exists($id, $anteriores)) {
                throw new DomainException('La cadena de revisiones contiene un ciclo o referencia ajena.');
            }
            $vistos[$id] = true;
            $id = $anteriores[$id];
        }
        if (count($vistos) !== count($anteriores)) {
            throw new DomainException('La cadena de revisiones contiene registros desconectados.');
        }

        return $cabezas[0];
    }
}
