<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Actividad;

final class ActualizarTipoActividad
{
    public function ejecutar(?string $anterior, string $tipo): void
    {
        if ($anterior) {
            Actividad::where('tipo', strtoupper(trim($anterior)))->update(['tipo' => strtoupper(trim($tipo))]);
        }
    }
}
