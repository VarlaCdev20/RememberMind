<?php

namespace App\Exports\Reportes;

use App\Services\Reportes\ReporteDataService;
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
