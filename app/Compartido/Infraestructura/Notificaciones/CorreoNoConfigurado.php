<?php

namespace App\Compartido\Infraestructura\Notificaciones;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class CorreoNoConfigurado extends RuntimeException
{
    public const AVISO = 'El correo del sistema aún no está disponible. No se envió el mensaje. Comunícate con la persona que administra RememberMind para completar la conexión e inténtalo de nuevo.';

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        $campo = $request->routeIs('password.email') ? 'correo' : 'envio';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => self::AVISO,
                'errors' => [$campo => [self::AVISO]],
            ], 503);
        }

        $respuesta = back()->withErrors([$campo => self::AVISO])
            ->withInput($request->only('correo'));

        if ($request->routeIs('password.email')) {
            $respuesta->with('recovery_requested', true);
        }

        return $respuesta;
    }
}
