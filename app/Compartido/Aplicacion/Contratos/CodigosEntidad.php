<?php

namespace App\Compartido\Aplicacion\Contratos;

/** Reserva de identidad compartida; el almacenamiento y bloqueo son infraestructura. */
interface CodigosEntidad
{
    public function siguiente(string $tabla, ?string $conexion = null): string;

    public function registrarExplicito(string $tabla, string $codigo, ?string $conexion = null): void;

    public function serializar(string $tabla, callable $operacion): mixed;
}
