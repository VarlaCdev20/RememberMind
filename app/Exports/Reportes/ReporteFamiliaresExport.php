<?php

namespace App\Exports\Reportes;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteFamiliaresExport implements WithMultipleSheets
{
    public function __construct(protected ReporteDataService $service) {}

    public function sheets(): array
    {
        return [
            new Sheets\Familiares\FamiliaresResumenSheet($this->service),
            new Sheets\Familiares\FamiliaresVinculosSheet($this->service),
        ];
    }
}
