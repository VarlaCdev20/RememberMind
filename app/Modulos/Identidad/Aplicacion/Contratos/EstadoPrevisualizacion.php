<?php

namespace App\Modulos\Identidad\Aplicacion\Contratos;

interface EstadoPrevisualizacion
{
    public function obtener(): mixed;

    public function guardar(string $rol): void;

    public function limpiar(): void;
}
