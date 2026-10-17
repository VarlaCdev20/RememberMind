<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Contratos;

interface IdentidadesConocimiento
{
    public function traducir(array $plantilla, string $versionPlantilla, string $versionReal, bool $existente): array;
}
