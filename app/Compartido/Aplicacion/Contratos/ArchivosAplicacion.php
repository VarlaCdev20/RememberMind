<?php

namespace App\Compartido\Aplicacion\Contratos;

interface ArchivosAplicacion
{
    public function existe(string $disco, string $ruta): bool;

    public function eliminar(string $disco, string $ruta): bool;

    public function hash(string $disco, string $ruta): string|false;

    public function crearDirectorio(string $disco, string $ruta): bool;

    public function escribir(string $disco, string $ruta, string $contenido): bool;

    public function recibir(object $archivo, string $carpeta, string $disco): string;

    public function recibirComo(object $archivo, string $carpeta, string $nombre, string $disco): string;

    public function hashEntrada(object $archivo): string|false;
}
