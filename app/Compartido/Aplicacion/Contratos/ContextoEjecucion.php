<?php

namespace App\Compartido\Aplicacion\Contratos;

use App\Models\User;

/** Identidad confiable y modalidad de la entrada; no depende de HTTP o sesión. */
interface ContextoEjecucion
{
    public function usuario(): ?User;

    public function esLectura(): bool;
}
