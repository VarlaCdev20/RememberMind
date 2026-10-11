<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Contratos;

use MotorExperto\dominio\conocimiento\PaqueteConocimiento;

interface ConocimientoVersionado
{
    public function cargar(string $version, array $contratosRelaciones, array $contratosExtraccion = []): PaqueteConocimiento;

    public function instantanea(string $version): array;
}
