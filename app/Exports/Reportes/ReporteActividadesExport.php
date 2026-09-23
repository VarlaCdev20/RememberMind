<?php

namespace App\Exports\Reportes;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteActividadesExport implements WithMultipleSheets
{
    public function __construct(protected ReporteDataService $service) {}

    public function sheets(): array
    {
        return [
            new Sheets\Actividades\ActividadesResumenSheet($this->service),
            new Sheets\Actividades\ActividadesListaSheet($this->service),
        ];
    }
}
