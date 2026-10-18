<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Contratos;

/** Escritura y comprobación del contrato observacional aprobado. */
interface ConservacionObservacional
{
    public function huella(array $registros, array $informe, ?array $conocimiento = null): string;

    public function guardar(string $residente, string $personal, string $solicitud, array $paquete, array $registros, array $informe): string;
}
