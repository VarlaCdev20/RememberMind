<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\User;

/** Alcance adoptado de Gerencia; no concede administración global de cuentas. */
final class AutorizarGestionPersonal
{
    public const ROLES_TRABAJADOR = ['ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'];

    public function esGerencia(User $actor): bool
    {
        return $actor->hasRole('GERENTE') && ! $actor->hasRole('SUPERADMINISTRADOR');
    }

    public function autorizar(?User $actor, ?User $destino = null, array $rolesNuevos = []): User
    {
        $actor = $actor ? User::find($actor->getKey()) : null;
        abort_unless($actor?->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR') && $actor->can('personal.gestionar'), 403);
        abort_if(app(RolePreviewService::class)->isActive($actor), 403);
        if ($this->esGerencia($actor)) {
            $rolesActuales = $destino?->fresh()->getRoleNames()->all() ?? [];
            abort_if($destino && (! $rolesActuales || array_diff($rolesActuales, self::ROLES_TRABAJADOR)), 403);
            abort_if(array_diff($rolesNuevos, self::ROLES_TRABAJADOR), 403);
            if ($destino && $rolesNuevos) {
                sort($rolesActuales);
                sort($rolesNuevos);
                abort_unless($rolesActuales === $rolesNuevos, 403, 'Gerencia no cambia los roles de una cuenta existente.');
            }
        }

        return $actor;
    }
}
