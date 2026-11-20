<?php

namespace App\Models;

use App\Compartido\Infraestructura\Persistencia\ConservaContextoResidente;

class Derivacion extends ModeloOperativo
{
    use ConservaContextoResidente;

    protected $table = 'derivaciones';

    protected $primaryKey = 'cod_derivacion';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }
}
