<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Area;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class GestionarAreasInstitucionales
{
    public function guardarArea(User $actor, array $entrada): string
    {
        $actor = $this->autorizar($actor, ! empty($entrada['isEdit']) ? 'areas.editar' : 'areas.crear');
        $area = ! empty($entrada['isEdit']) ? Area::findOrFail($entrada['areaId'] ?? '') : null;
        $entrada['nombre'] = mb_strtoupper(trim((string) ($entrada['nombre'] ?? '')), 'UTF-8');
        Validator::make($entrada, ['nombre' => ['required', 'string', 'max:80', Rule::unique('areas', 'nombre')->ignore($area?->getKey(), 'cod_area')], 'descripcion' => 'nullable|string', 'estado' => 'required|in:ACTIVO,INACTIVO'])->validate();
        $datos = [
            'nombre' => mb_strtoupper(trim($entrada['nombre']), 'UTF-8'),
            'descripcion' => ($entrada['descripcion'] ?? '') ?: null,
            'estado' => $entrada['estado'],
        ];

        return DB::transaction(function () use ($actor, $datos, $area) {
            if ($area) {
                $area = Area::query()->lockForUpdate()->findOrFail($area->getKey());
                if ($actor->hasRole('GERENTE') && ! $actor->hasRole('SUPERADMINISTRADOR')) {
                    // La edición gerencial no ejecuta una transición de estado oculta.
                    $datos['estado'] = $area->estado;
                }
                if ($datos['estado'] === 'INACTIVO' && in_array($area->estado, ['ACTIVO', 'ACTIVA'], true)) {
                    $this->exigirSinCompromisos($area);
                }
                $area->update($datos);

                // Log de actividad
                activity('AreasInstitucionales')
                    ->performedOn($area)
                    ->causedBy($actor)
                    ->log("El usuario editó el área institucional {$area->nombre}.");

                $mensaje = 'Área institucional actualizada correctamente.';
            } else {
                $area = Area::create($datos + [
                    'cod_area' => app(CodigosEntidad::class)->siguiente('areas'),
                ]);

                // Log de actividad
                activity('AreasInstitucionales')
                    ->performedOn($area)
                    ->causedBy($actor)
                    ->log("El usuario creó la área institucional {$area->nombre}.");

                $mensaje = 'Área institucional registrada correctamente.';
            }

            return $mensaje;
        });
    }

    public function toggleEstado(User $actor, string $codArea): array
    {
        $actor = $this->autorizar($actor, 'areas.cambiar_estado');

        return DB::transaction(function () use ($actor, $codArea) {
            $area = Area::query()->lockForUpdate()->findOrFail($codArea);
            $nuevoEstado = in_array($area->estado, ['ACTIVO', 'ACTIVA'], true) ? 'INACTIVO' : 'ACTIVO';
            if ($nuevoEstado === 'INACTIVO') {
                $this->exigirSinCompromisos($area);
            }
            $area->estado = $nuevoEstado;
            $area->save();

            // Registrar acción en log
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy($actor)
                ->log("Se cambió el estado del área {$area->nombre} a {$nuevoEstado}.");

            return [$area, $nuevoEstado];
        });
    }

    private function autorizar(User $actor, string $permiso): User
    {
        $actor = User::findOrFail($actor->getKey());
        abort_unless($actor->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR') && $actor->can($permiso), 403);
        abort_if(app(RolePreviewService::class)->isActive($actor), 403);

        return $actor;
    }

    private function exigirSinCompromisos(Area $area): void
    {
        if ($area->asignaciones()->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists()
            || $area->planesCuidado()->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE', 'ABIERTO', 'EN_CURSO'])->exists()) {
            throw ValidationException::withMessages(['estado' => 'Antes de desactivar el área, traslada o cierra sus asignaciones y planes activos. No se modificó ningún compromiso.']);
        }
    }
}
