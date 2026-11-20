<?php

namespace App\Models;

use App\Compartido\Infraestructura\Persistencia\SeleccionaDiasRelacionados;

class ProgramacionCuidado extends ModeloOperativo
{
    use SeleccionaDiasRelacionados;

    protected $table = 'programaciones_cuidado';

    protected $primaryKey = 'cod_programacion';

    protected function casts(): array
    {
        return ['fecha_activacion' => 'date', 'fecha_desactivacion' => 'date'];
    }
}
