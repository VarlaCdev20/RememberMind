<?php

namespace App\Compartido\Aplicacion\Contratos;

/** URLs de las proyecciones; el caso de uso no conoce el router HTTP. */
interface EnlacesAplicacion
{
    public function destino(string $nombre, mixed $parametros = []): string;
}
