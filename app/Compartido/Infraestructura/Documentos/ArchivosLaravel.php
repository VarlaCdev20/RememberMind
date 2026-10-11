<?php

namespace App\Compartido\Infraestructura\Documentos;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use Illuminate\Support\Facades\Storage;

final class ArchivosLaravel implements ArchivosAplicacion
{
    public function existe(string $disco, string $ruta): bool
    {
        return Storage::disk($disco)->exists($ruta);
    }

    public function eliminar(string $disco, string $ruta): bool
    {
        return Storage::disk($disco)->delete($ruta);
    }

    public function hash(string $disco, string $ruta): string|false
    {
        return hash_file('sha256', Storage::disk($disco)->path($ruta));
    }

    public function crearDirectorio(string $disco, string $ruta): bool
    {
        return Storage::disk($disco)->makeDirectory($ruta);
    }

    public function escribir(string $disco, string $ruta, string $contenido): bool
    {
        return Storage::disk($disco)->put($ruta, $contenido);
    }

    public function recibir(object $archivo, string $carpeta, string $disco): string
    {
        $ruta = $archivo->store($carpeta, $disco);

        if ($ruta === false || $ruta === '') {
            throw new \RuntimeException('No se pudo guardar el archivo.');
        }

        return $ruta;
    }

    public function recibirComo(object $archivo, string $carpeta, string $nombre, string $disco): string
    {
        $ruta = $archivo->storeAs($carpeta, $nombre, $disco);

        if ($ruta === false || $ruta === '') {
            throw new \RuntimeException('No se pudo guardar el archivo.');
        }

        return $ruta;
    }

    public function hashEntrada(object $archivo): string|false
    {
        return hash_file('sha256', $archivo->getRealPath());
    }
}
