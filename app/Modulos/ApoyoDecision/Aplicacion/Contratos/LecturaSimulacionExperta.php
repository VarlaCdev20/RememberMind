<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Contratos;

use App\Models\Residente;
use App\Models\User;

/** Consulta opcional de instantáneas locales ficticias; nunca ejecuta inferencias. */
interface LecturaSimulacionExperta
{
    /** Devuelve null fuera del lote habilitado; conserva la autorización contextual de lectura. */
    public function consultar(User $usuario, Residente $residente, ?string $id, string $criterio, int $pagina): ?array;
}
