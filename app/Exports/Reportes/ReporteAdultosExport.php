<?php

namespace App\Exports\Reportes;

use App\Backend\Modulos\Reportes\Servicios\ReporteDataService;
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
