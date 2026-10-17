<?php

namespace App\Modulos\Medicacion\Infraestructura\Archivos;

use App\Modulos\Medicacion\Aplicacion\Contratos\ImagenesMedicamentos;
use Illuminate\Support\Facades\Storage;

final class ImagenMedicamento implements ImagenesMedicamentos
{
    public function paraCodigo(string $codigo): ?string
    {
        if (! preg_match('/\A[A-Za-z0-9_-]{1,20}\z/', $codigo)) {
            return null;
        }

        $disco = Storage::disk('public');
        foreach (['webp' => IMAGETYPE_WEBP, 'png' => IMAGETYPE_PNG, 'jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG] as $extension => $tipo) {
            $ruta = 'imagenes/medicamentos/'.$codigo.'.'.$extension;
            if (! $disco->exists($ruta)) {
                continue;
            }
            // El archivo debe ser una imagen raster real, no contenido ejecutable renombrado.
            $datos = @getimagesize($disco->path($ruta));
            if ($datos !== false && ($datos[2] ?? null) === $tipo) {
                return $disco->url($ruta);
            }
        }

        return null;
    }
}
