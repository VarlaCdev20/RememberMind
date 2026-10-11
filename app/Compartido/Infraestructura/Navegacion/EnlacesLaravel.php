<?php

namespace App\Compartido\Infraestructura\Navegacion;

use App\Compartido\Aplicacion\Contratos\EnlacesAplicacion;
use Illuminate\Contracts\Routing\UrlGenerator;

final class EnlacesLaravel implements EnlacesAplicacion
{
    public function __construct(private readonly UrlGenerator $urls) {}

    public function destino(string $nombre, mixed $parametros = []): string
    {
        return $this->urls->route($nombre, $parametros);
    }
}
