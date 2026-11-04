<?php

namespace App\Compartido\Infraestructura\Documentos;

use App\Compartido\Aplicacion\Contratos\DocumentosUsuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class AlmacenamientoDocumentosUsuario implements DocumentosUsuario
{
    public function esArchivo(mixed $archivo): bool
    {
        return $archivo instanceof UploadedFile;
    }

    public function nombreOriginal(object $archivo): string
    {
        if (! $this->esArchivo($archivo)) {
            throw new InvalidArgumentException('Entrada documental no válida.');
        }

        return $archivo->getClientOriginalName();
    }

    public function guardar(string $usuario, object $archivo, string $prefijo = 'documentos/usuarios'): array
    {
        $this->nombreOriginal($archivo);
        if (! in_array($prefijo, ['documentos/usuarios', 'documentos_personal', 'documentos_personal_privados', 'perfiles'], true)) {
            throw new InvalidArgumentException('Destino documental no válido.');
        }
        if (! preg_match('/^[A-Za-z0-9_-]{1,20}$/D', $usuario)) {
            throw new InvalidArgumentException('Identificador documental no válido.');
        }
        $nombre = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME));
        $disk=$prefijo==='documentos_personal_privados' ? 'local' : 'public';
        $ruta = $archivo->storeAs($prefijo.'/'.$usuario, Str::uuid().'_'.$nombre.'.'.strtolower($archivo->getClientOriginalExtension()), $disk);
        if (! $ruta) {
            throw new RuntimeException('No se pudo almacenar el documento.');
        }

        return ['ruta_archivo' => $ruta, 'tipo_archivo' => $archivo->getMimeType() ?: 'application/octet-stream',
            'hash_archivo' => hash_file('sha256', Storage::disk($disk)->path($ruta))];
    }

    public function compensar(string $ruta): void
    {
        $this->validarRuta($ruta);
        if (! Storage::disk($this->disk($ruta))->delete($ruta)) {
            throw new RuntimeException('No se pudo compensar el archivo documental.');
        }
    }

    public function rutaAbsoluta(string $ruta): string
    {
        $this->validarRuta($ruta);

        return Storage::disk($this->disk($ruta))->path($ruta);
    }

    private function validarRuta(string $ruta): void
    {
        if (! preg_match('~^(documentos/usuarios|documentos_personal|documentos_personal_privados|perfiles)/[A-Za-z0-9_-]{1,20}/[^/]+$~D', $ruta) || str_contains($ruta, '..')) {
            throw new InvalidArgumentException('Ruta documental no válida.');
        }
    }

    private function disk(string $ruta): string { return str_starts_with($ruta,'documentos_personal_privados/') ? 'local' : 'public'; }
}
