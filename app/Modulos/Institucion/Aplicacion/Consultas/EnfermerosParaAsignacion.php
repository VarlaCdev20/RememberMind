<?php

namespace App\Modulos\Institucion\Aplicacion\Consultas;

use App\Models\User;
use Illuminate\Support\Collection;

final class EnfermerosParaAsignacion
{
    public function obtener(): Collection
    {
        return User::role('ENFERMEROS')->leftJoin('personal', 'usuarios.cod_usuario', '=', 'personal.cod_usuario')->where('usuarios.estado', 'ACTIVO')->orderBy('personal.nombres')->select('usuarios.*')->get();
    }
}
