<?php

namespace App\Modulos\Institucion\Presentacion\Policies;

use App\Models\FotografiaInstitucional;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Institucion\Aplicacion\Contratos\GestionFotografias;

class FotografiaInstitucionalPolicy implements GestionFotografias
{
    public function viewAny(?User $usuario): bool
    {
        return $usuario?->estado === 'ACTIVO' && ($usuario->hasRole('SUPERADMINISTRADOR')
            || ($usuario->hasAnyRole(['GERENTE', 'ADMINISTRADOR']) && $usuario->hasPermissionTo('fotografias_institucionales.ver')));
    }

    public function view(?User $usuario, FotografiaInstitucional $foto): bool
    {
        return $this->viewAny($usuario) && $foto->estado === 'ACTIVA';
    }

    public function manage(?User $usuario): bool
    {
        return $this->viewAny($usuario) && $usuario->hasAnyRole(['GERENTE', 'ADMINISTRADOR'])
            && $usuario->hasPermissionTo('fotografias_institucionales.gestionar')
            && ! app(RolePreviewService::class)->isActive($usuario);
    }
}
