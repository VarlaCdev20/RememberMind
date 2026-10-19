<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Servicios;

use App\Models\VersionModeloExperto;
use DomainException;

/** Contratos del servidor heredados por linaje; no recibe predicados ni mapeos del cliente. */
final class ContratosConocimiento
{
    public function porVersion(string $version): array
    {
        $vistos = [];
        do {
            if (isset($vistos[$version])) {
                throw new DomainException('Ciclo en el linaje del contrato de conocimiento.');
            }
            $vistos[$version] = true;
            $contrato = config('sistema_experto.contratos_conocimiento.'.$version);
            if (is_array($contrato) && is_array($contrato['relaciones'] ?? null)
                && is_array($contrato['extracciones'] ?? null) && is_string($contrato['tipo_regla_resultado'] ?? null)) {
                return $contrato;
            }
            $version = VersionModeloExperto::query()->whereKey($version)->sole()->cod_version_anterior;
        } while ($version !== null);
        throw new DomainException('Falta el contrato semántico del servidor para esta familia de conocimiento. Puede archivar reglas sin inventar ese contrato.');
    }
}
