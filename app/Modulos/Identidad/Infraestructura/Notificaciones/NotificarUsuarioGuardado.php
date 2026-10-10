<?php

namespace App\Modulos\Identidad\Infraestructura\Notificaciones;

use App\Mail\UsuarioBienvenidaMail;
use App\Mail\UsuarioCredencialesInicialesMail;
use App\Mail\UsuarioPasswordActualizadaMail;
use App\Models\Area;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class NotificarUsuarioGuardado
{
    public function ejecutar(User $actor, User $usuario, bool $esEdicion, ?string $password, ?string $passwordTemporal, ?string $oldPhotoToDelete, string $rol, ?string $codArea): array
    {
        // 1. Eliminar la foto antigua físicamente de forma segura
        if ($oldPhotoToDelete) {
            try {
                \Storage::disk('public')->delete($oldPhotoToDelete);
            } catch (\Exception $ex) {
                Log::error('No se pudo eliminar la foto antigua del storage: '.$ex->getMessage());
            }
        }

        if ($esEdicion) {
            // Notificación por correo de clave actualizada
            $correoNotifEnviado = false;
            $passwordModificada = ($usuario->cod_usuario === $actor->getKey() && ! empty($password));
            if ($passwordModificada) {
                try {
                    \Mail::to($usuario->correo)->send(new UsuarioPasswordActualizadaMail($usuario, $password));
                    $correoNotifEnviado = true;
                } catch (\Exception $e) {
                    Log::error("Error al enviar correo de actualización de clave a {$usuario->correo}: ".$e->getMessage());
                }
            }

            if (function_exists('activity')) {
                $actMsg = "Se actualizaron los datos del usuario: {$usuario->name}.";
                if ($passwordModificada) {
                    $actMsg .= $correoNotifEnviado ? ' Contraseña actualizada y notificada por correo.' : ' Contraseña actualizada, pero falló envío de notificación.';
                }
                activity('Usuarios')
                    ->causedBy($actor)
                    ->performedOn($usuario)
                    ->event('edicion')
                    ->log($actMsg);
            }

            return ['passwordModificada' => $passwordModificada, 'correoNotifEnviado' => $correoNotifEnviado];
        }
        // Enviar bienvenida
        $bienvenidaEnviada = false;
        try {
            $rolDisplay = strtoupper(str_replace('_', ' ', $rol));
            $areaDisplay = 'Sin área';
            if ($codArea) {
                $areaObj = Area::find($codArea);
                if ($areaObj) {
                    $areaDisplay = $areaObj->nombre;
                }
            }
            \Mail::to($usuario->correo)->send(new UsuarioBienvenidaMail($usuario, $rolDisplay, $areaDisplay));
            $bienvenidaEnviada = true;
        } catch (\Exception $e) {
            Log::error("Error al enviar correo de bienvenida a {$usuario->correo}: ".$e->getMessage());
        }

        // Enviar credenciales
        $credencialesEnviadas = false;
        try {
            \Mail::to($usuario->correo)->send(new UsuarioCredencialesInicialesMail($usuario, $passwordTemporal));
            $credencialesEnviadas = true;
        } catch (\Exception $e) {
            Log::error("Error al enviar credenciales a {$usuario->correo}: ".$e->getMessage());
        }

        if (function_exists('activity')) {
            $logMsg = "Se registró un nuevo usuario: {$usuario->name} con rol {$rol}. ".($credencialesEnviadas ? 'Correo de credenciales enviado con éxito.' : 'Error al enviar correo de credenciales.');
            activity('Usuarios')
                ->causedBy($actor)
                ->performedOn($usuario)
                ->event('registro')
                ->log($logMsg);
        }

        return ['bienvenidaEnviada' => $bienvenidaEnviada, 'credencialesEnviadas' => $credencialesEnviadas];
    }
}
