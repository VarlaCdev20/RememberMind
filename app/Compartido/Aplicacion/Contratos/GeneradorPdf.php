<?php

namespace App\Compartido\Aplicacion\Contratos;

interface GeneradorPdf
{
    public function contenido(string $plantilla, array $datos): string;
}
