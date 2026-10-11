<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class RegistrarEventoFichaMedica
{
    public function archivar(User $actor, Residente $adulto_mayor): void
    {
        abort_unless($actor->can('residentes.ver'), 403);
        abort_unless($actor->can('atenciones.anular'), 403);
        Gate::forUser($actor)->authorize('view', $adulto_mayor);
        activity('Ficha Médica')
            ->causedBy($actor)
            ->performedOn($adulto_mayor)
            ->event('archived')
            ->log("Se archivó la ficha médica del residente {$adulto_mayor->cod_residente}.");

    }

    public function restore(User $actor, Residente $adulto_mayor): void
    {
        abort_unless($actor->can('residentes.ver'), 403);
        abort_unless($actor->can('atenciones.editar'), 403);
        Gate::forUser($actor)->authorize('view', $adulto_mayor);
        activity('Ficha Médica')
            ->causedBy($actor)
            ->performedOn($adulto_mayor)
            ->event('restored')
            ->log("Se restauró la ficha médica del residente {$adulto_mayor->cod_residente}.");

    }
}
