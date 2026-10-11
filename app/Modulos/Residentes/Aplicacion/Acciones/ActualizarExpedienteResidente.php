<?php

namespace App\Modulos\Residentes\Aplicacion\Acciones;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ActualizarExpedienteResidente
{
    public function ejecutar(User $actor, bool $esEdicion, ?string $id, array $datos, mixed $foto = null): void
    {
        abort_unless($actor->estado === 'ACTIVO' && $actor->can('residentes.gestionar'), 403);
        abort_unless($esEdicion && $id, 403, 'El residente solo puede crearse mediante la admisión formal.');
        if (isset($datos['fecha_nac']) && app(AdultoMayorService::class)->calcularEdad($datos['fecha_nac']) < 60) {
            throw ValidationException::withMessages(['fecha_nac' => 'El adulto mayor debe tener 60 anos o mas para ser registrado.']);
        }
        DB::transaction(fn () => app(AdultoMayorService::class)->actualizarAdultoMayor($id, $datos, $foto));
    }
}
