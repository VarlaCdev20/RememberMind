<?php

namespace App\Modulos\Reportes\Infraestructura\Exportaciones\Reportes;

use App\Modulos\Reportes\Aplicacion\Consultas\ReporteDataService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteAdultosExport implements WithMultipleSheets
{
    public function __construct(protected ReporteDataService $service) {}

    public function sheets(): array
    {
        return [
            new Sheets\Adultos\AdultosResumenSheet($this->service),
            new Sheets\Adultos\AdultosListaSheet($this->service),
        ];
    }
}
