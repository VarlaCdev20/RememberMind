<?php

namespace App\Modulos\Reportes\Infraestructura\Exportaciones\Reportes;

use App\Modulos\Reportes\Aplicacion\Consultas\ReporteDataService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteSaludExport implements WithMultipleSheets
{
    public function __construct(protected ReporteDataService $service) {}

    public function sheets(): array
    {
        return [
            new Sheets\Salud\SaludResumenSheet($this->service),
            new Sheets\Salud\SaludFichasSheet($this->service),
            new Sheets\Salud\SaludMedicacionSheet($this->service),
            new Sheets\Salud\SaludValoracionesSheet($this->service),
        ];
    }
}
