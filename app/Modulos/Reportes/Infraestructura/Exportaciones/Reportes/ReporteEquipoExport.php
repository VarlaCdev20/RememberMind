<?php

namespace App\Modulos\Reportes\Infraestructura\Exportaciones\Reportes;

use App\Modulos\Reportes\Aplicacion\Consultas\ReporteDataService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteEquipoExport implements WithMultipleSheets
{
    public function __construct(protected ReporteDataService $service) {}

    public function sheets(): array
    {
        return [
            new Sheets\Equipo\EquipoResumenSheet($this->service),
            new Sheets\Equipo\EquipoPersonalSaludSheet($this->service),
            new Sheets\Equipo\EquipoPersonalAdminSheet($this->service),
        ];
    }
}
