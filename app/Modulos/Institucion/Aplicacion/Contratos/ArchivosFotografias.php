<?php

namespace App\Modulos\Institucion\Aplicacion\Contratos;

interface ArchivosFotografias
{
    /** Normaliza una entrada de archivo validada y devuelve sus metadatos persistibles. */
    public function preparar(object $archivo): array;

    public function compensar(array $rutas): void;
}
