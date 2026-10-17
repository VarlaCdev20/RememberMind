<?php

namespace App\Modulos\Medicacion\Aplicacion\Contratos;

interface ImagenesMedicamentos
{
    public function paraCodigo(string $codigo): ?string;
}
