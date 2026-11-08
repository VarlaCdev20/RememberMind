<?php

namespace App\Modulos\Institucion\Aplicacion\Contratos;

use App\Models\FotografiaInstitucional;
use App\Models\User;

interface GestionFotografias
{
    public function viewAny(?User $usuario): bool;

    public function view(?User $usuario, FotografiaInstitucional $foto): bool;

    public function manage(?User $usuario): bool;
}
