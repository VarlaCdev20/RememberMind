<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\Area;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarAreasInstitucionales
{
    public function guardarArea(User $actor, array $entrada): string
    {
        abort_unless(strtoupper((string) $actor->estado) === 'ACTIVO' && $actor->can($entrada['isEdit'] ? 'areas.editar' : 'areas.crear'), 403);
        Validator::make($entrada, ['nombre' => 'required|string|max:80|unique:areas,nombre,'.($entrada['areaId'] ?? 'NULL').',cod_area', 'descripcion' => 'nullable|string', 'estado' => 'required|in:ACTIVO,INACTIVO'])->validate();
        $datos = [
            'nombre' => mb_strtoupper(trim($entrada['nombre']), 'UTF-8'),
            'descripcion' => $entrada['descripcion'] ?: null,
            'estado' => $entrada['estado'],
        ];

        if ($entrada['isEdit']) {
            $area = Area::findOrFail($entrada['areaId']);
            $area->update($datos);

            // Log de actividad
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy($actor)
                ->log("El usuario editó el área institucional {$area->nombre}.");

            $mensaje = 'Área institucional actualizada correctamente.';
        } else {
            $area = Area::create($datos + [
                'cod_area' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('areas'),
            ]);

            // Log de actividad
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy($actor)
                ->log("El usuario creó la área institucional {$area->nombre}.");

            $mensaje = 'Área institucional registrada correctamente.';
        }

        return $mensaje;
    }

    public function toggleEstado(User $actor, string $codArea): array
    {
        abort_unless(strtoupper((string) $actor->estado) === 'ACTIVO' && $actor->can('areas.cambiar_estado'), 403);
        $area = Area::findOrFail($codArea);
        $nuevoEstado = in_array($area->estado, ['ACTIVO', 'ACTIVA'], true) ? 'INACTIVO' : 'ACTIVO';
        $area->estado = $nuevoEstado;
        $area->save();

        // Registrar acción en log
        activity('AreasInstitucionales')
            ->performedOn($area)
            ->causedBy($actor)
            ->log("Se cambió el estado del área {$area->nombre} a {$nuevoEstado}.");

        return [$area, $nuevoEstado];
    }
}
