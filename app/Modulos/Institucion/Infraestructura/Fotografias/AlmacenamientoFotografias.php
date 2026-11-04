<?php

namespace App\Modulos\Institucion\Infraestructura\Fotografias;

use App\Modulos\Institucion\Aplicacion\Contratos\ArchivosFotografias;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AlmacenamientoFotografias implements ArchivosFotografias
{
    public function preparar(object $archivo): array
    {
        $info = @getimagesize($archivo->getRealPath());
        if (! $info || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 12000000
            || ! in_array($info['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw ValidationException::withMessages(['archivo' => 'Selecciona una fotografía JPG, PNG o WebP de hasta 12 megapíxeles.']);
        }
        $imagen = @imagecreatefromstring(file_get_contents($archivo->getRealPath()));
        if (! $imagen) {
            throw ValidationException::withMessages(['archivo' => 'La fotografía no se puede procesar.']);
        }
        try {
            // Reducir antes de orientar evita mantener dos copias de 12 MP en memoria.
            if (max(imagesx($imagen), imagesy($imagen)) > 2560) {
                $escala = 2560 / max(imagesx($imagen), imagesy($imagen));
                $reducida = imagescale($imagen, max(1, (int) round(imagesx($imagen) * $escala)), max(1, (int) round(imagesy($imagen) * $escala)));
                if (! $reducida) throw new \RuntimeException('No se pudo reducir la fotografía.');
                imagedestroy($imagen);
                $imagen = $reducida;
            }
            // EXIF opcional: una imagen válida sin esta cabecera puede devolver false/avisos.
            $exif = $info['mime'] === 'image/jpeg' && function_exists('exif_read_data')
                ? @exif_read_data($archivo->getRealPath(), 'IFD0', true) : false;
            $orientacion = (int) ($exif['IFD0']['Orientation'] ?? 1);
            if (in_array($orientacion, [2, 4, 5, 7], true)) {
                imageflip($imagen, in_array($orientacion, [4, 5], true) ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
            }
            $angulo = match ($orientacion) { 3 => 180, 5, 6, 7 => -90, 8 => 90, default => 0 };
            if ($angulo) {
                $girada = imagerotate($imagen, $angulo, 0);
                if (! $girada) throw new \RuntimeException('No se pudo orientar la fotografía.');
                imagedestroy($imagen);
                $imagen = $girada;
            }
            $principal = $this->codificar($imagen, 2560);
            $miniatura = $this->codificar($imagen, 480);
            $ruta = 'galeria-institucional/'.Str::uuid();
            $rutas = [$ruta.'/imagen.webp', $ruta.'/miniatura.webp'];
            try {
                foreach ([$principal['bytes'], $miniatura['bytes']] as $indice => $bytes) {
                    if (! Storage::disk('local')->put($rutas[$indice], $bytes)) throw new \RuntimeException('No se pudo almacenar la fotografía.');
                }
            } catch (\Throwable $error) {
                try { $this->compensar($rutas); } catch (\Throwable $limpieza) { report($limpieza); }
                throw $error;
            }
            return ['ruta_archivo' => $rutas[0], 'ruta_miniatura' => $rutas[1], 'tipo_archivo' => 'image/webp',
                'tamanio_bytes' => strlen($principal['bytes']), 'ancho' => $principal['ancho'], 'alto' => $principal['alto'],
                'hash_archivo' => hash('sha256', $principal['bytes'])];
        } finally {
            imagedestroy($imagen);
        }
    }

    private function codificar(\GdImage $imagen, int $limite): array
    {
        $escala = min(1, $limite / max(imagesx($imagen), imagesy($imagen)));
        $ancho = max(1, (int) round(imagesx($imagen) * $escala));
        $alto = max(1, (int) round(imagesy($imagen) * $escala));
        $copia = imagecreatetruecolor($ancho, $alto);
        if (! $copia) throw new \RuntimeException('No se pudo preparar la fotografía.');
        imagealphablending($copia, false);
        imagesavealpha($copia, true);
        try {
            if (! imagecopyresampled($copia, $imagen, 0, 0, 0, 0, $ancho, $alto, imagesx($imagen), imagesy($imagen))) {
                throw new \RuntimeException('No se pudo redimensionar la fotografía.');
            }
            ob_start();
            try {
                if (! imagewebp($copia, null, 85)) throw new \RuntimeException('No se pudo codificar la fotografía.');
                $bytes = ob_get_contents();
            } finally { ob_end_clean(); }
            if (! is_string($bytes) || $bytes === '') throw new \RuntimeException('La fotografía procesada está vacía.');
            return ['bytes' => $bytes, 'ancho' => $ancho, 'alto' => $alto];
        } finally { imagedestroy($copia); }
    }

    public function compensar(array $rutas): void
    {
        foreach ($rutas as $ruta) {
            $this->validarRuta($ruta);
            if (Storage::disk('local')->exists($ruta) && ! Storage::disk('local')->delete($ruta)) {
                throw new \RuntimeException('No se pudo limpiar un archivo nuevo de la galería: '.$ruta);
            }
        }
    }

    public function validarRuta(string $ruta): void
    {
        if (! preg_match('~^galeria-institucional/[a-f0-9-]{36}/(?:imagen|miniatura)\.webp$~D', $ruta)) {
            throw new \RuntimeException('Referencia de fotografía inválida.');
        }
    }
}
