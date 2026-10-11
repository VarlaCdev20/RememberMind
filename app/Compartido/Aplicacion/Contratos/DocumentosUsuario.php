<?php

namespace App\Compartido\Aplicacion\Contratos;

/** Almacenamiento y metadatos de la entrada documental; implementación sustituible. */
interface DocumentosUsuario
{
    public function esArchivo(mixed $archivo): bool;

    public function nombreOriginal(object $archivo): string;

    public function guardar(string $usuario, object $archivo, string $prefijo = 'documentos/usuarios'): array;

    public function compensar(string $ruta): void;

    public function rutaAbsoluta(string $ruta): string;
}
