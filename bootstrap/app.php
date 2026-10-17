<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AsegurarCuentaActiva;
use App\Http\Middleware\PreventWritesDuringRolePreview;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            PreventWritesDuringRolePreview::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'cuenta.activa' => AsegurarCuentaActiva::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function ($response, $exception, $request) {
            if ($response->getStatusCode() === 403 && $request->hasHeader('X-Livewire')) {
                $categoria = match (true) {
                    $exception->getMessage() === 'Disponible únicamente fuera del modo de previsualización.' => 'PREVISUALIZACION',
                    $exception->getMessage() === 'La cuenta de usuario no está activa.' => 'CUENTA_INACTIVA',
                    $exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException => 'PERMISO_O_ROL',
                    default => 'RESTRICCION_CONTEXTUAL',
                };
                \Illuminate\Support\Facades\Log::notice('Solicitud Livewire rechazada', [
                    'ruta' => $request->route()?->getName(),
                    'categoria' => $categoria,
                    'cuenta_activa' => $request->user()?->estado === 'ACTIVO',
                ]);
            }

            return $response;
        });
    })->create();
