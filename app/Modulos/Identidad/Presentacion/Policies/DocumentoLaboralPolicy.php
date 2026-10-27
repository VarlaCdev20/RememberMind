<?php

namespace App\Modulos\Identidad\Presentacion\Policies;

use App\Models\Documento;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\AutorizarGestionPersonal;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Collection;

final class DocumentoLaboralPolicy
{
    public function view(User $actor, Documento $documento): bool
    {
        $actor = $actor->fresh();
        if (! $actor) {
            return false;
        }

        return $this->puedeVer($actor, $documento, User::with(['personal', 'roles'])->find($documento->cod_usuario));
    }

    /** El detalle histórico autoriza cada documento con consultas acotadas, sin SQL en Blade. */
    public function permisosDocumentos(User $actor, Collection $documentos): array
    {
        $actor = $actor->fresh(['roles']);
        if (! $actor) {
            return [];
        }
        $titulares = User::with(['personal', 'roles'])->whereIn('cod_usuario', $documentos->pluck('cod_usuario')->filter()->unique())->get()->keyBy('cod_usuario');
        $permisos = [];
        foreach ($documentos as $documento) {
            $titular = $titulares->get($documento->cod_usuario);
            $permisos[$documento->getKey()] = [
                'ver' => $this->puedeVer($actor, $documento, $titular),
                'revisar' => $this->puedeRevisar($actor, $documento, $titular),
            ];
        }

        return $permisos;
    }

    private function puedeVer(User $actor, Documento $documento, ?User $titular): bool
    {
        if ($actor->estado !== 'ACTIVO' || $actor->hasRole('FAMILIAR') || ! $documento->cod_usuario
            || $documento->cod_residente || $documento->cod_contacto || $documento->cod_preadmision) {
            return false;
        }
        if (! $titular?->personal) {
            return false;
        }
        if ($actor->getKey() === $titular->getKey()) {
            return true;
        }
        if (! $actor->can('personal_institucional.ver') || ! $actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR'])) {
            return false;
        }

        return $actor->hasRole('SUPERADMINISTRADOR') || ($titular->getRoleNames()->isNotEmpty()
            && ! array_diff($titular->getRoleNames()->all(), AutorizarGestionPersonal::ROLES_TRABAJADOR));
    }

    public function review(User $actor, Documento $documento): bool
    {
        $actor = $actor->fresh(['roles']);
        if (! $actor) {
            return false;
        }

        return $this->puedeRevisar($actor, $documento, User::with(['personal', 'roles'])->find($documento->cod_usuario));
    }

    private function puedeRevisar(User $actor, Documento $documento, ?User $titular): bool
    {
        if (! $actor->hasRole('SUPERADMINISTRADOR')) {
            $roles = $titular?->getRoleNames()->all() ?? [];
            if (! $roles || array_diff($roles, AutorizarGestionPersonal::ROLES_TRABAJADOR)) {
                return false;
            }
        }

        return $this->puedeVer($actor, $documento, $titular) && $actor->can('personal.gestionar')
            && $actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']) && ! app(RolePreviewService::class)->isActive($actor);
    }
}
