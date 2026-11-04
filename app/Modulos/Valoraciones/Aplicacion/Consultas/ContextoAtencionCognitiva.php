<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Consultas;

use App\Models\Contacto;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/** Identidad del informante explícitamente seleccionado, nunca del responsable por defecto. */
final class ContextoAtencionCognitiva
{
    public function puedeVerInformantes(User $usuario): bool
    {
        return $usuario->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR')
            && $usuario->checkPermissionTo('contactos.ver', 'web')
            && $usuario->checkPermissionTo('residentes_contactos.ver', 'web');
    }

    public function informantes(User $usuario, Residente $residente): Collection
    {
        Gate::forUser($usuario)->authorize('view', $residente);
        abort_unless($this->puedeVerInformantes($usuario), 403);

        return ResidenteContacto::query()->where('cod_residente', $residente->getKey())->where('estado', 'ACTIVO')
            ->whereHas('contacto', fn ($q) => $q->where('estado', 'ACTIVO'))
            ->with('contacto:cod_contacto,nombres,apellido_paterno,apellido_materno')
            ->orderBy('cod_residente_contacto')->limit(100)
            ->get(['cod_residente_contacto', 'cod_contacto', 'parentesco']);
    }

    /** Llamar dentro de la transacción de guardado; evita estado/vínculo obsoleto. */
    public function validarInformante(User $usuario, Residente $residente, string $codigo): void
    {
        Gate::forUser($usuario)->authorize('view', $residente);
        abort_unless($this->puedeVerInformantes($usuario), 403);
        $codigoContacto = ResidenteContacto::query()->whereKey($codigo)->where('cod_residente', $residente->getKey())
            ->value('cod_contacto');
        // La acción ya bloqueó al residente; conservar el orden residente → contacto → vínculo de Social.
        $contacto = $codigoContacto ? Contacto::query()->whereKey($codigoContacto)->where('estado', 'ACTIVO')
            ->lockForUpdate()->first(['cod_contacto']) : null;
        $vinculo = $contacto ? ResidenteContacto::query()->whereKey($codigo)->where('cod_residente', $residente->getKey())
            ->where('cod_contacto', $codigoContacto)->where('estado', 'ACTIVO')->lockForUpdate()->first(['cod_residente_contacto']) : null;
        if (! $vinculo) {
            throw ValidationException::withMessages(['cod_residente_contacto_informante' =>
                'Selecciona un contacto activo vinculado a este residente. Si no está registrado, conserva el relato en las notas sin elegir otra persona.']);
        }
    }
}
