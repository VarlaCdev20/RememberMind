<?php

namespace App\Modulos\Institucion\Aplicacion\Contratos;

use App\Models\User;

interface GestionFotografias
{
    public function manage(?User $usuario): bool;
}
