<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Area;
use App\Models\FotografiaInstitucional;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Institucion\Aplicacion\Contratos\ArchivosFotografias;
use App\Modulos\Institucion\Aplicacion\Contratos\GestionFotografias;
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
        Validator::make($entrada, [
            'cod_fotografia' => ['nullable', 'string', 'max:20'],
            'archivo_fotografia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'texto_alternativo' => ['nullable', 'string', 'max:255'],
        ])->validate();
        if (! empty($entrada['cod_fotografia']) && ! empty($entrada['archivo_fotografia'])) {
            throw ValidationException::withMessages(['archivo_fotografia' => 'Elige una fotografía de Galería o sube una nueva.']);
        }
        $datos = [
            'nombre' => mb_strtoupper(trim($entrada['nombre']), 'UTF-8'),
            'descripcion' => ($entrada['descripcion'] ?? '') ?: null,
            'estado' => $entrada['estado'],
        ];

        $fotoNueva = null;
        try {
            return DB::transaction(function () use ($actor, $datos, $area, $entrada, &$fotoNueva) {
                $actor = $this->autorizar($actor, $area ? 'areas.editar' : 'areas.crear');
                if ($area) {
                    $area = Area::query()->lockForUpdate()->findOrFail($area->getKey());
                    if ($actor->hasRole('GERENTE') && ! $actor->hasRole('SUPERADMINISTRADOR')) {
                        // La edición gerencial no ejecuta una transición de estado oculta.
                        $datos['estado'] = $area->estado;
                    }
                    if ($datos['estado'] === 'INACTIVO' && in_array($area->estado, ['ACTIVO', 'ACTIVA'], true)) {
                        $this->exigirSinCompromisos($area);
                    }
                }

                $fotoAnterior = $area?->cod_fotografia;
                if (! empty($entrada['archivo_fotografia'])) {
                    try {
                        $resultado = app(GestionarFotografiasInstitucionales::class)->subir($actor, $entrada['archivo_fotografia'], [
                            'titulo' => 'Área: '.$datos['nombre'], 'descripcion' => null, 'fecha_fotografia' => null,
                            'texto_alternativo' => $entrada['texto_alternativo'] ?? null, 'usar_en_bienvenida' => false,
                        ]);
                    } catch (ValidationException $error) {
                        $errores = $error->errors();
                        $errores['archivo_fotografia'] = $errores['archivo'] ?? ['Revisa la fotografía seleccionada.'];
                        unset($errores['archivo']);
                        throw ValidationException::withMessages($errores);
                    }
                    if ($resultado['nueva']) {
                        $fotoNueva = $resultado['foto'];
                    }
                    $datos['cod_fotografia'] = $this->fotografiaDisponible($actor, $resultado['foto']->getKey())->getKey();
                } elseif (array_key_exists('cod_fotografia', $entrada)) {
                    $codigo = $entrada['cod_fotografia'] ?: null;
                    // Una edición de texto conserva también un vínculo archivado previamente.
                    if ($codigo !== $area?->cod_fotografia) {
                        $datos['cod_fotografia'] = $codigo ? $this->fotografiaDisponible($actor, $codigo)->getKey() : null;
                    }
                }

                if ($area) {
                    $area->update($datos);

                    // Log de actividad
                    activity('AreasInstitucionales')
                        ->performedOn($area)
                        ->causedBy($actor)
                        ->withProperties(['cod_fotografia_anterior' => $fotoAnterior, 'cod_fotografia' => $area->cod_fotografia])
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
                        ->withProperties(['cod_fotografia' => $area->cod_fotografia])
                        ->log("El usuario creó el área institucional {$area->nombre}.");

                    $mensaje = 'Área institucional registrada correctamente.';
                }

                return $mensaje;
            });
        } catch (\Throwable $error) {
            if ($fotoNueva !== null) {
                try {
                    app(ArchivosFotografias::class)->compensar([$fotoNueva->ruta_archivo, $fotoNueva->ruta_miniatura]);
                } catch (\Throwable $limpieza) {
                    report($limpieza);
                }
            }
            throw $error;
        }
    }

    private function fotografiaDisponible(User $actor, string $codigo): FotografiaInstitucional
    {
        $policy = app(GestionFotografias::class);
        abort_unless($policy->viewAny($actor), 403);
        $foto = FotografiaInstitucional::whereKey($codigo)->lockForUpdate()->first();
        if (! $foto || ! $policy->view($actor, $foto)) {
            throw ValidationException::withMessages(['cod_fotografia' => 'La fotografía ya no está disponible. Elige otra imagen activa de Galería.']);
        }

        return $foto;
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
