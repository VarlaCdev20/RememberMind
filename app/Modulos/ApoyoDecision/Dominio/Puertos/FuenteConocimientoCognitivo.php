<?php

namespace App\Modulos\ApoyoDecision\Dominio\Puertos;

interface FuenteConocimientoCognitivo
{
    public function leer(): array;
}
