<?php

namespace App\Modulos\Clinica\Dominio\Reglas;

use App\Modulos\Clinica\Dominio\ObjetosValor\ResultadoReglaClinica;

interface ReglaClinica
{
    /** @param array<string, mixed> $mediciones */
    public function evaluar(array $mediciones): ?ResultadoReglaClinica;
}
