<?php

namespace App\Backend\Modulos\Identidad\Servicios;

use App\Backend\Modulos\Administracion\Servicios\ConsultaOperativaService;
use Illuminate\Support\Facades\Route;

class VisibilidadNavegacion
{
    public function __construct(private readonly ConsultaOperativaService $consultaOperativa) {}

    public function puedeVerRuta(string $nombre, ?string $permiso = null): bool
    {
        $usuario = auth()->user();
        $ruta = Route::getRoutes()->getByName($nombre);

        if (! $usuario || $usuario->estado !== 'ACTIVO' || ! $ruta) {
            return false;
        }

        if ($permiso && ! $usuario->can($permiso)) {
            return false;
        }

        // Las vistas operativas validan su permiso en el controlador, además
        // del permiso común del grupo de rutas de Administración.
        if (str_starts_with($nombre, 'admin.administracion.')) {
            $modulo = substr($nombre, strlen('admin.administracion.'));
            $permisoModulo = $modulo === 'residentes.show'
                ? 'residentes.ver'
                : ($this->consultaOperativa->definicion($modulo)['permiso'] ?? null);

            if ($permisoModulo && ! $usuario->can($permisoModulo)) {
                return false;
            }
        }

        foreach ($ruta->gatherMiddleware() as $middleware) {
            if (str_starts_with($middleware, 'permission:')) {
                $alternativas = explode('|', explode(',', substr($middleware, strlen('permission:')))[0]);
                if (! $usuario->canAny($alternativas)) {
                    return false;
                }
            } elseif (str_starts_with($middleware, 'role:')) {
                $alternativas = explode('|', explode(',', substr($middleware, strlen('role:')))[0]);
                if (! $usuario->hasAnyRole($alternativas)) {
                    return false;
                }
            } elseif (str_starts_with($middleware, 'can:')) {
                [$habilidad, $recurso] = array_pad(explode(',', substr($middleware, strlen('can:')), 2), 2, null);
                // Las rutas de listado autorizan una clase; las rutas con modelo
                // concreto necesitan el registro y se evalúan en su propia vista.
                if (! $recurso || ! class_exists($recurso) || ! $usuario->can($habilidad, $recurso)) {
                    return false;
                }
            }
        }

        return true;
    }
}
