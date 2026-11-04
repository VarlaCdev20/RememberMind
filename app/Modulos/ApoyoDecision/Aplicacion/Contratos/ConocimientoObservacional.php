<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Contratos;

use App\Models\User;

/** Acceso al paquete literal aprobado, independiente de su representación SQL. */
interface ConocimientoObservacional
{
    public function preparar(User $autor): array;

    public function verificar(string $version): array;

    public function vigente(): ?array;

    public function historico(string $version, ?array $ejecutadas): array;
}
