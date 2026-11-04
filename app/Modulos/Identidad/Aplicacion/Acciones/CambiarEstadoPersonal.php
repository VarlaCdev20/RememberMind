<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CambiarEstadoPersonal
{
    public function ejecutar(User $actor, User $usuario, ?string $estadoEsperado = null): void
    {
        $actor = User::findOrFail($actor->getKey());
        abort_unless($actor->estado === 'ACTIVO' && !$actor->hasRole('FAMILIAR'), 403);
        abort_if(app(RolePreviewService::class)->isActive($actor), 403);
        $alcance = app(AutorizarGestionPersonal::class);
        if ($alcance->esGerencia($actor)) {
            $alcance->autorizar($actor, $usuario);
            abort_if($estadoEsperado === null, 422, 'Confirma el estado de acceso mostrado antes de cambiarlo.');
        } else {
            abort_unless($actor->can('usuarios.cambiar_estado'), 403);
        }
        DB::transaction(function () use ($actor, $usuario, $estadoEsperado, $alcance): void {
            $superadmins = User::role('SUPERADMINISTRADOR')->where('estado', 'ACTIVO')->orderBy('cod_usuario')->lockForUpdate()->get();
            $destino = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
            if ($alcance->esGerencia($actor)) $alcance->autorizar($actor, $destino);
            abort_if($actor->getKey() === $destino->getKey() || $destino->esCuentaPrincipal(), 403);
            abort_if($destino->hasRole('SUPERADMINISTRADOR') && $destino->estado === 'ACTIVO' && $superadmins->count() <= 1, 422);
            if ($estadoEsperado !== null && $estadoEsperado !== $destino->estado) {
                throw ValidationException::withMessages(['acceso' => 'El acceso cambió desde que abriste la página. Actualiza la consulta antes de confirmar.']);
            }
            $anterior = $destino->estado;
            $destino->update(['estado' => $anterior === 'ACTIVO' ? 'SUSPENDIDO' : 'ACTIVO']);
            activity('Usuarios')->causedBy($actor)->performedOn($destino)->event('acceso_personal')
                ->withProperties(['estado_anterior' => $anterior, 'estado_nuevo' => $destino->estado])
                ->log('Cambió el acceso de la cuenta; no modificó el estado laboral.');
        });
    }
}
