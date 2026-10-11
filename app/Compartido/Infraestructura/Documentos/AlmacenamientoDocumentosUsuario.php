<?php

namespace App\Compartido\Infraestructura\Documentos;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class AlmacenamientoDocumentosUsuario
{
    public function guardar(string $usuario, UploadedFile $archivo, string $prefijo = 'documentos/usuarios'): array
    {
        if (! in_array($prefijo, ['documentos/usuarios', 'documentos_personal', 'perfiles'], true)) {
            throw new InvalidArgumentException('Destino documental no válido.');
        }
        if (! preg_match('/^[A-Za-z0-9_-]{1,20}$/D', $usuario)) {
            throw new InvalidArgumentException('Identificador documental no válido.');
        }
        $nombre = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME));
        $ruta = $archivo->storeAs($prefijo.'/'.$usuario, Str::uuid().'_'.$nombre.'.'.strtolower($archivo->getClientOriginalExtension()), 'public');
        if (! $ruta) {
            throw new RuntimeException('No se pudo almacenar el documento.');
        }

        return ['ruta_archivo' => $ruta, 'tipo_archivo' => $archivo->getMimeType() ?: 'application/octet-stream',
            'hash_archivo' => hash_file('sha256', Storage::disk('public')->path($ruta))];
    }

    public function compensar(string $ruta): void
    {
        $this->validarRuta($ruta);
        if (! Storage::disk('public')->delete($ruta)) {
            throw new RuntimeException('No se pudo compensar el archivo documental.');
        }
    }

    public function rutaAbsoluta(string $ruta): string
    {
        $this->validarRuta($ruta);

        return Storage::disk('public')->path($ruta);
    }

    private function validarRuta(string $ruta): void
    {
        if (! preg_match('~^(documentos/usuarios|documentos_personal|perfiles)/[A-Za-z0-9_-]{1,20}/[^/]+$~D', $ruta) || str_contains($ruta, '..')) {
            throw new InvalidArgumentException('Ruta documental no válida.');
        }
    }
}
