<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Contratos;

/** Conservación y consulta de evidencia técnica; la aplicación no conoce disco ni cifrado. */
interface AlmacenEjecucionesExperimentales
{
    public function guardar(array $entrada, array $resultado, string $autor, string $motor): array;

    public function leer(string $caso, string $evaluacion): array;

    public function listar(string $caso): array;
}
