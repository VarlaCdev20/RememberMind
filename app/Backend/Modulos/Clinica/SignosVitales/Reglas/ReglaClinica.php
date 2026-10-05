<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Reglas;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;

interface ReglaClinica
{
    /** @param array<string, mixed> $mediciones */
    public function evaluar(array $mediciones): ?ResultadoReglaClinica;
}
